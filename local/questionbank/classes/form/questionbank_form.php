<?php
/*
* This file is a part of e abyas Info Solutions.
*
* Copyright e abyas Info Solutions Pvt Ltd, India.
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
* @author e abyas  <info@eabyas.com>
*/
/**
 * Questionbank Info
 *
 * @package    local_questionbank
 * @copyright  e abyas  <info@eabyas.com>
*/
namespace local_questionbank\form;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/csvlib.class.php');

use csv_import_reader;
use core_text;
use moodleform;
use context_user;
use html_table;
use html_writer;
use context_system;
use local_userapproval\userinfo as userinfo;
use local_trainingprogram\local\trainingprogram as tp;
use local_trainingprogram;
use local_hall\hall as hall;


require_once($CFG->libdir.'/formslib.php');
class questionbank_form extends moodleform {

 public function definition() {
        global $DB, $OUTPUT, $USER,$SYSTEM,$CFG;
        $mform    = $this->_form;
        $entitycode = $this->_customdata['entitycode'];
        $context = context_system::instance();
        $mform->_attributes['id'] = 'questionbankform';
        $id = $this->_customdata['id'];
        $edit = $this->_customdata['edit'];
        $form_status = $this->_customdata['form_status'];
        $status = $this->_customdata['st'];

        $contextid = $context->id;
        $mform->addElement('hidden', 'contextid', '', ['class' => 'contextid']);
        $mform->setType('contextid',PARAM_RAW);
        $mform->setDefault('contextid', $contextid);
        $mform->addElement('hidden', 'submit_type', 'form');
        if($form_status == 0){
            $mform->addElement('text', 'workshopname', get_string('workshopname', 'local_questionbank')); 
            $mform->addRule('workshopname', get_string('workshopnameerr','local_questionbank'), 'required', null);
            $mform->setType('workshopname', PARAM_RAW);  

            if(!is_siteadmin()){
                $sql = "AND u.id != $USER->id"; 
            }
             $currentlang= current_language();
            if($currentlang == 'ar') {
                $displaying_name = "
                   CASE
                        WHEN lc.middlenamearabic IS NOT NULL  AND  lc.thirdnamearabic IS NOT NULL  THEN concat(lc.firstnamearabic,' ',lc.middlenamearabic,' ',lc.thirdnamearabic,' ',lc.lastnamearabic)
                        ELSE concat(lc.firstnamearabic,' ',lc.lastnamearabic)
                    END";
            }else {
                $displaying_name = "
                CASE
                        WHEN lc.middlenameen IS NOT NULL  AND  lc.thirdnameen IS NOT NULL THEN concat(u.firstname,' ',lc.middlenameen,' ',lc.thirdnameen,' ',u.lastname)
                        ELSE concat(u.firstname,' ',u.lastname)
                    END";
            }
            $expertsinfo = $DB->get_records_sql_menu("SELECT ra.userid, $displaying_name as username 
                  FROM {role_assignments} as  ra 
                  JOIN {role} as r ON ra.roleid = r.id  AND r.shortname = 'examofficial'
                  JOIN {user} as u ON u.id= ra.userid
                  JOIN {local_users} AS lc ON lc.userid = ra.userid
                  AND u.confirmed=1 AND u.deleted=0 AND u.suspended=0 where 1=1  $sql");
          
            if(is_siteadmin()){
                $expertsselect = $mform->addElement('autocomplete','workshopadmin', get_string('workshopadmin', 'local_questionbank'),$expertsinfo,array( 'placeholder' => get_string('select_examofficials', 'local_questionbank')));
                $mform->addRule('workshopadmin', get_string('workshopadminerr','local_questionbank'), 'required', null);
                $expertsselect->setMultiple(true); 
            }
            
            $mform->addElement('text', 'noofquestions', get_string('noofquestions', 'local_questionbank')); 
            $mform->addRule('noofquestions', get_string('noofquestionserr','local_questionbank'), 'required', null);
            $mform->addRule('noofquestions', get_string('validationerr','local_questionbank'), 'numeric', null, 'server');
            $mform->setType('noofquestions', PARAM_RAW);


            $mform->addElement('text', 'generatecode', get_string('generatecode', 'local_questionbank')); 
            $mform->addRule('generatecode', get_string('generatecodeerr','local_questionbank'), 'required', null);
            $mform->setType('generatecode', PARAM_RAW);
            $mform->addElement('date_selector', 'startdate', get_string('workshopdate','local_questionbank'),array('optional' => false));
            $mform->addRule('startdate','required', 'required', null);
            $timeselector = local_trainingprogram\local\dataprovider::get_timeselector();
            $durselect[] =& $mform->createElement('select', 'hours', '', $timeselector['hours']);
            $durselect[] =& $mform->createElement('select', 'minutes', '', $timeselector['minutes'], false, true);
            $mform->addGroup($durselect, 'starttime', get_string('starttime','local_questionbank'), array(' '), true);
            $mform->addRule('starttime', "required", 'required', null, 'server');

            $mform->addElement('duration', 'duration', get_string('duration','local_trainingprogram'), ['units' =>[MINSECS]]);
            $mform->addRule('duration', 'required', 'required', null);
            $halls = $DB->get_records_sql_menu("SELECT id, name AS fullname FROM {hall} WHERE availability = 1");
            $halloptions = array(
                'multiple' => false,
                'data-type' => 'questionbank',
                'data-id' => $id,
                'onchange' => "(function(e){ require(['local_hall/hallreserve'], function(s) {s.reservation('questionbank');}) }) (event)",
                'class' => 'entityhall',
            );

            $types = array();
            $types[] = $mform->createElement('radio', 'status', '', get_string('active', 'local_questionbank'), 1);
            $types[] = $mform->createElement('radio', 'status', '', get_string('inactive', 'local_questionbank'), 2);
           
            $mform->addGroup($types, 'status',get_string('status', 'local_questionbank'),array('&nbsp;&nbsp;'), false);
            $mform->setType('status', PARAM_INT);
            $mform->addRule('status', get_string('pleaseselectstatus','local_questionbank'), 'required', null, 'server');

            $mform->addElement('autocomplete', 'halladdress', get_string('halladdress','local_questionbank'), [null => get_string('selecthall', 'local_hall')] + $halls, $halloptions);
            $mform->addRule('halladdress', get_string('missinghalladdress', 'local_exams'), 'required', null);
            $mform->hideIf('halladdress', 'duration[number]', 'eq', '0');
            $mform->hideIf('halladdress', 'starttime[hours]', 'eq', '0');
            
            $group = [];
            $group[] =& $mform->createElement('html', get_string('qbfiledsmandatory', 'local_questionbank'));
            $mform->addGroup($group, 'hallalertgroup', '', ' ', false);
            $mform->hideIf('hallalertgroup', 'halladdress', 'neq', '');

            if( $id ) {         
                $reservations = (new \local_hall\hall)->entityreservations($id, 'questionbank');
            }

            $reservationgroup = [];
            $reservationgroup[] =& $mform->createElement('html', '<div id="hallinformation"><table class="generaltable table"><thead class="thead-light"><tr><th scope="col"><b>'.get_string('hall','local_events').'</b></th><th scope="col"><b>'.get_string('date','local_events').'</b></th><th scope="col"><b>'.get_string('seats','local_events').'</b></th></tr></thead><tbody class="entityhalldetails">'. $reservations .'</tbody></table></div>');
            $mform->addGroup($reservationgroup, 'reservationgroup', '', ' ', false);
            $mform->hideIf('reservationgroup', 'halladdress', 'eq', '');

            $submit = get_string('savechanges','local_questionbank');
            
            $cancel= $mform->createElement('cancel','cancelbuton',get_string('cancel'));

        }elseif($form_status ==1){
            $competencytypes = tp::constcompetency_types();
             $competencytypeoptions = [
            'ajax' => 'local_trainingprogram/dynamic_dropdown_ajaxdata',
            'data-type' => 'program_competency',
            'class' => 'el_competencytype',
            'multiple'=>false,
            'onchange' => "(function(e){ require(['local_trainingprogram/dynamic_dropdown_ajaxdata'], function(s) {s.ctype();}) }) (event)",
            ];
            $ctype = array(null => get_string('ctype', 'local_questionbank'))  ;
            $mform->addElement('autocomplete', 'ctype', get_string('competency_type', 'local_trainingprogram'), $ctype+$competencytypes, $competencytypeoptions);
            $mform->setType('ctype', PARAM_ALPHANUMEXT);
            
            $clattributes = array(
                'ajax' => 'local_questionbank/coursetopics',
                'data-type' => 'competencylist',
                'class' => 'el_competencieslist',
                'data-ctype' =>'',
              
            );
            $competencies = array();
            $competencieslist = $_POST['competencylevel'];
            if ($id > 0) {
                $id = $id > 0 ? $id : 0;
                $competencies= $DB->get_records_sql_menu("SELECT loc.id, loc.name as title 
                    FROM {local_competencies} as loc
                    JOIN {local_questionbank} as lot 
                    ON concat(',', lot.competency, ',') LIKE concat('%,',loc.id,',%')
                    WHERE lot.id=:cid",['cid' => $id]
                );
            }
            
            $competencyelemet= $mform->addElement('autocomplete', 'competencylevel',get_string('competencies', 'local_trainingprogram'),$competencies,$clattributes);
            $competencyelemet->setMultiple(true);
            
            $courses = $DB->get_fieldset_sql("SELECT c.id FROM {course} AS c 
                JOIN {course_categories} AS cc on cc.id = c.category AND cc.idnumber != 'trainingprogram' WHERE c.id >1 ");
           
            $coursesselect = $mform->addElement('course','courses', get_string('courses', 'local_questionbank'),  $courses);
           
            $coursesselect->setMultiple(true);

            $submit = get_string('savechanges'); 
            
            $cancel=$mform->createElement('html', '<center><a tabindex="0" role="button" class="btn btn-primary" href="'.$CFG->wwwroot.'/local/questionbank/questionbank.php?edit='.$id.'&form_status=0&formid='.$id.'">'.get_string('cancel','local_questionbank').'</center></a>');
        }
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id',  $id);
        $mform->addElement('hidden', 'form_status');
        $mform->setType('form_status', PARAM_INT);
        $mform->setDefault('form_status',  $form_status);

        $mform->addElement('hidden', 'edit_form');
        $mform->setType('edit_form', PARAM_INT);
        $mform->setDefault('edit_form',  $edit); 

        $mform->addElement('hidden', 'entitycode', $entitycode);
        $mform->setType('entitycode', PARAM_RAW);

        $buttonarray=array();

        $buttonarray[] = $mform->createElement('submit', 'submitbutton',$submit);
        $buttonarray[] =  $cancel;
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);
    }
    function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        $time = ($data['starttime']['hours'] * 3600) + ($data['starttime']['minutes'] * 60);
        $selecteddate = $data['startdate'] +  $time;

        if(is_siteadmin() && empty($data['workshopadmin']) && $data['form_status']== 0){
            $errors['workshopadmin'] = get_string('workshopadminerr', 'local_questionbank');
        }
        if($data['noofquestions'] < 0 ){
            $errors['noofquestions'] = get_string('validquestioncount', 'local_questionbank');
        }
        // if ($data['id'] > 0) {
        //     $existingques_num = $DB->get_field('local_questionbank', 'noofquestions', ['id' => $data['id']]);
        //     if ($data['noofquestions'] < $existingques_num) {
        //         $errors['noofquestions'] = get_string('noofques_cannotbeless_than_previous', 'local_questionbank',$existingques_num );
        //     }
        // }
        if($data['duration'] <= 0  && $data['form_status']== 0){
            $errors['duration'] = get_string('durationerr', 'local_questionbank');
        }
        if($data['starttime']['hours'] <= 0  && $data['form_status']== 0){
            $errors['starttime'] = get_string('starttimerror', 'local_questionbank');
        }
        if($data['id'] > 0){
            $workshop = $DB->get_record('local_questionbank',array('id' => $data['id']));
            $existingtime = $workshop->workshopdate + $workshop->workshopstarttime;
            if(($existingtime !=$selecteddate) &&  ($selecteddate < time())  && $data['form_status']== 0){
                $errors['starttime'] = get_string('starttimerror', 'local_questionbank');
            }
            if(($existingtime !=$selecteddate) && date("Y-m-d",$data['startdate']) < date("Y-m-d") && $data['form_status']== 0) {
                 $errors['startdate'] = get_string('startdateerr', 'local_questionbank');
            }
        }else{
            if(date("Y-m-d",$data['startdate']) < date("Y-m-d") && $data['form_status']== 0) {
                 $errors['startdate'] = get_string('startdateerr', 'local_questionbank');
            }

            if($selecteddate < time()  && $data['form_status']== 0){
                $errors['starttime'] = get_string('starttimerror', 'local_questionbank');
            }
        }
        if(!empty($data['startdate']) && !empty($data['enddate'])){
           if($data['startdate'] > ($data['enddate'])){
              $errors['enddate'] = get_string('enddateerr', 'local_questionbank');
           }
        }
       
        return $errors;
    }
}

       
