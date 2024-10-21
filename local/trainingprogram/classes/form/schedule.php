<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
namespace local_trainingprogram\form;

use core_form\dynamic_form;
use local_userapproval\action\manageuser;
use moodle_url;
use context;
use context_system;
use local_trainingprogram;
use stdClass;
use local_trainingprogram\local\trainingprogram as tp;
use local_trainingprogram\local\dataprovider as dataprovider;
use coding_exception;
use MoodleQuickForm_autocomplete;



/**
 * TODO describe file schedule
 *
 * @package    local_trainingprogram
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class schedule extends dynamic_form { 

    private $dataprovider;

    public function definition() {
        global $USER, $CFG,$DB;
      

        $mform = $this->_form;


        $context = context_system::instance();
        $contextid = $context->id;

        $id = $this->optional_param('id', 0, PARAM_INT);
        $trainingid = $this->optional_param('trainingid', 0, PARAM_INT);
        $entitycode = $this->optional_param('entitycode', 0, PARAM_RAW);

        $mform->addElement('hidden', 'submit_type', 'form');

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'trainingid', $trainingid);
        $mform->setType('trainingid', PARAM_INT);

        $programrecord = $DB->get_record('local_trainingprogram',array('id'=>$trainingid));

        $mform->addElement('hidden', 'pstartdate', $programrecord->availablefrom);
        $mform->setType('pstartdate', PARAM_INT);

        $mform->addElement('hidden', 'penddate', $programrecord->availableto);
        $mform->setType('penddate', PARAM_INT);
        $programdata=$DB->get_record('local_trainingprogram',array('id'=>$trainingid));

        $mform->addElement('hidden', 'entitycode', $entitycode);
        $mform->setType('entitycode', PARAM_RAW);

        $mform->addElement('hidden', 'courseid', $programdata->courseid);
        $mform->setType('courseid', PARAM_INT);


        $mform->addElement('hidden', 'contextid', '', ['class' => 'contextid']);
        $mform->setType('contextid',PARAM_RAW);
        $mform->setDefault('contextid', $contextid);

        $max_discount = (int) get_config('tool_product','max_discount_percentage');

        $mform->addElement('hidden', 'max_discount', $max_discount );
        $mform->setType('max_discount',PARAM_INT);


        $traineeroleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
        $enrolledtraineessql="SELECT COUNT(id) FROM {program_enrollments} WHERE programid = $trainingid AND offeringid = $id AND courseid = $programdata->courseid AND roleid = $traineeroleid AND enrolstatus = 1";
        $enrolledcount = $DB->count_records_sql($enrolledtraineessql);
        
        $trainingmethod = ($id > 0) ?$DB->get_field('tp_offerings','trainingmethod',['id'=>$id]) : null;
        $offeringrecord = ($id > 0) ?$DB->get_record('tp_offerings',['id'=>$id]) : null;

        $mform->addElement('hidden', 'enrolledcount', $enrolledcount);
        $mform->setType('enrolledcount', PARAM_INT);
        if($id == 0 ){

            if($programdata){

                $programdata->id = 0;
                $programdata->duration = $programdata->hour;
                $sellingprice =  $programdata->sellingprice;
                $actualprice =  $programdata->actualprice;
                $programdata->sellingprice = $sellingprice;
                $programdata->actualprice = $actualprice;
                $this->set_data($programdata);
            }

        }    
        $this->dataprovider = local_trainingprogram\local\dataprovider::getInstance($trainingid);
        $systemcontext = context_system::instance();

        $programfromdate = userdate($programrecord->availablefrom, get_string('strftimedaydate', 'langconfig'));
        $programtodate = userdate($programrecord->availableto, get_string('strftimedaydate', 'langconfig'));

        $mform->addElement('static', '', get_string('programduration', 'local_trainingprogram'),$programfromdate.' - '.$programtodate);
        if($enrolledcount > 0) {
            $starttimemeridian = gmdate('a',$offeringrecord->time); 
            $endtimemeridian = gmdate('a',($offeringrecord->time + $offeringrecord->duration)); 
    
            $starttime = gmdate("h:i",$offeringrecord->time);
            $endttime = gmdate("h:i",($offeringrecord->time + $offeringrecord->duration));
    
    
            if(current_language() == 'ar') {
                $startmeridian = ($starttimemeridian == 'am')? 'صباحا':'مساءً';
                $endmeridian =  ($endtimemeridian == 'am')? 'صباحا':'مساءً';
            } else {
                $startmeridian = ($starttimemeridian == 'am')? 'AM':'PM';
                $endmeridian =  ($endtimemeridian == 'am')? 'AM':'PM';
            }

            $offlang = ((int) $offeringrecord->languages == 1) ? get_string('english', 'local_trainingprogram')  : get_string('arabic', 'local_trainingprogram') ;
            if($offeringrecord->trainingmethod !='elearning') {
                $mform->addElement('static', '', get_string('offeringdate', 'local_trainingprogram'),userdate($offeringrecord->startdate, get_string('strftimedaydate', 'langconfig')).' '.$starttime .' '.$startmeridian.' - '.userdate($offeringrecord->enddate, get_string('strftimedaydate', 'langconfig')).' '.$endttime .' '.$endmeridian);
            }

            if((int)$offeringrecord->type ==1 && (int)$offeringrecord->organization > 0){
                $organizationname =(current_language() == 'ar') ?  $DB->get_field('local_organization','fullnameinarabic',['id'=>(int)$offeringrecord->organization]) :  $DB->get_field('local_organization','fullname',['id'=>(int)$offeringrecord->organization]);
                $mform->addElement('static', '', get_string('offering_language', 'local_trainingprogram'),$organizationname);

            }
    
            $mform->addElement('static', '', get_string('offering_language', 'local_trainingprogram'),$offlang);

        }
        $mform->addElement('date_selector', 'startdate', get_string('startdate','local_trainingprogram'),['optional' => true]);
        $mform->setType('startdate', PARAM_TEXT);
        $mform->disabledIf('startdate',  'trainingmethod',  'eq',  'elearning');
        $mform->hideIf('startdate',  'enrolledcount',  'neq',  0);

        $mform->addElement('date_selector', 'enddate', get_string('enddate','local_trainingprogram'), ['optional' => true]);
        $mform->setType('enddate', PARAM_TEXT);
        $mform->disabledIf('enddate',  'trainingmethod',  'eq',  'elearning');
        $mform->hideIf('enddate',  'enrolledcount',  'neq',  0);

        $starttimeselector = $this->dataprovider::get_timeselector();
        $starttimedurselect[] =& $mform->createElement('select', 'hours', '', $starttimeselector['hours'],array('class'=> 'time_selector'));
        $starttimedurselect[] =& $mform->createElement('select', 'minutes', '', $starttimeselector['minutes'], array('class'=> 'time_selector'), true);
        $mform->addGroup($starttimedurselect, 'starttime', get_string('starttime','local_trainingprogram'), array(' '), true);
        $mform->hideIf('starttime',  'trainingmethod',  'eq',  'elearning');
        $mform->hideIf('starttime',  'enrolledcount',  'neq',  0);



        $endtimeselector = $this->dataprovider::get_timeselector();
        $endtimedurselect[] =& $mform->createElement('select', 'hours', '', $endtimeselector['hours'],array('class'=> 'time_selector'));
        $endtimedurselect[] =& $mform->createElement('select', 'minutes', '', $endtimeselector['minutes'], array('class'=> 'time_selector'), true);

        $mform->addGroup($endtimedurselect, 'endtime', get_string('endtime','local_trainingprogram'), array(''), true);
        $mform->hideIf('endtime',  'trainingmethod',  'eq',  'elearning');
        $mform->hideIf('endtime',  'enrolledcount',  'neq',  0);


        if($id > 0) {
            $class = '';
            $time = $DB->get_field('tp_offerings','duration',array('id' => $id));
            $dur__min = $time/60;
            if($dur__min){
                $hours = floor($dur__min / 60);
                $minutes = ($dur__min % 60);
                $htext = ($hours == 1)?  get_string('hours','local_trainingprogram'):get_string('hour','local_trainingprogram');
                $offering_hours = $hours.' '.$htext;
                $offering_minutes =$minutes.' '.get_string('minutes','local_trainingprogram');
            }
           
            $class = ($trainingmethod == 'elearning') ? 'hidden' : '';
           
        }  else {
            $class =   'hidden' ;
            $offering_hours = '';
            $offering_minutes = '';
        }

        
         $mform->addElement('html', '<div  id="ofeering_duration"  class="row my-3 '.$class.'"><span class="col-md-3">'.get_string('duration','local_trainingprogram').'</span><span class="ml-3" id="offering_hours">'.$offering_hours.' </span> <span class="ml-1" id="offering_minutes">'.$offering_minutes.'</span></div>');
           

        $planguage=$DB->get_record('local_trainingprogram',array('id' => $trainingid));
        $lang=explode(',',$planguage->languages);
        
        if($lang['0']==0 && $lang['1']==1) {

        $availablefromgroup=array();
        $availablefromgroup[] =& $mform->createElement('radio', 'language', '',get_string('arabic', 'local_trainingprogram'), 0);
        $availablefromgroup[] =& $mform->createElement('radio', 'language', '',get_string('english', 'local_trainingprogram'), 1);
         $mform->addGroup($availablefromgroup, 'offeringlanguages', get_string('offeringlanguages', 'local_trainingprogram'), '', false);
        }
        else
        {
           if($lang['1']==1 || $lang['0']==1){
            $availablefromgroup=array();
            $availablefromgroup[] =& $mform->createElement('radio', 'language', '',get_string('english', 'local_trainingprogram'), 1);
            $mform->addGroup($availablefromgroup, 'offeringlanguages', get_string('offeringlanguages', 'local_trainingprogram'), '', false);
           }else if($lang['0']==0 || $lang['1']==0){
            $availablefromgroup=array();
            $availablefromgroup[] =& $mform->createElement('radio', 'language', '',get_string('arabic', 'local_trainingprogram'), 0);
            $mform->addGroup($availablefromgroup, 'offeringlanguages', get_string('offeringlanguages', 'local_trainingprogram'), '', false);
           }
        }
        $mform->setType('language', PARAM_INT);
        $mform->hideIf('offeringlanguages',  'enrolledcount',  'neq',  0);


        $programtype = [];
        $programtype['0'] = get_string('public','local_trainingprogram');
        $programtype['1'] = get_string('private','local_trainingprogram');
        $programtype['2'] = get_string('dedicated','local_trainingprogram');
    
        $select = $mform->addElement('select', 'type', get_string('programtype','local_trainingprogram'),$programtype);
        $mform->setType('type', PARAM_TEXT);
        $mform->disabledIf('type',  'enrolledcount',  'neq',  0);

        $mform->addElement('checkbox', 'offeringpricing', get_string('offeringpricing', 'local_trainingprogram'),get_string('offeringpricing_text', 'local_trainingprogram'),null,[0,1]);
        $mform->setDefault('offeringpricing', 1);
        $mform->hideIf('offeringpricing', 'type', 'neq', '1');
        $mform->hideIf('offeringpricing',  'enrolledcount',  'neq',  0);

        $mform->addElement('text', 'availableseats', get_string('seats','local_trainingprogram'), 'maxlength="100" size="10"');
        $mform->addRule('availableseats', get_string('acceptsnumeric', 'local_trainingprogram'), 'numeric');
        $mform->setType('availableseats', PARAM_TEXT);

        $organization = $this->_ajaxformdata['organization'];
        $organizations = array();
        if (!empty($organization)) {
            $organization = is_array($organization) ? $organization : array($organization);
            $organizations = manageuser::get_user_organization($organization,0);
        }elseif ($id > 0) {
            $organizations = manageuser::get_user_organization(array(),$id,$type = 'offering');
        }
       $orgattributes = array(
        'ajax' => 'local_organization/organization_datasource',
        'data-type' => 'organization_list',
        'data-org' => 1,
        'multiple' => false,
        'id' => 'bulkorgselect',
        'class' => 'femptylabel',
        'placeholder' => get_string('selectorganisation','local_userapproval'),
       );
        $mform->addElement('autocomplete','organization',get_string('organization','local_exams'),$organizations,$orgattributes);       
        $mform->hideIf('organization', 'type', 'neq', '1');
        $mform->hideIf('organization',  'enrolledcount',  'neq',  0);
        //renu..
        //attachment-pdf
         $filemanageroptions = array(
            'accepted_types' => '.pdf',
            'maxbytes' => 0,
            'maxfiles' => 1,
        );

        $mform->addElement('filepicker','attachmentpdf', get_string('officialrfp','local_trainingprogram'),  null,$filemanageroptions
        );   
        $mform->hideIf('attachmentpdf', 'type', 'neq', '1');
        


        //Estimated budget (float text)

        $mform->addElement('text', 'estimatedbudget', get_string('estimatedbudget','local_trainingprogram'), 'maxlength="100" size="10"');
        $mform->addRule('estimatedbudget', get_string('acceptsnumeric', 'local_trainingprogram'), 'numeric');
        $mform->setType('estimatedbudget', PARAM_TEXT);
        $mform->hideIf('estimatedbudget', 'type', 'neq', '1');
      

        //Official Proposal (Attachement pdf)
         $filemanageroptions1 = array(
            'accepted_types' => '.pdf',
            'maxbytes' => 0,
            'maxfiles' => 1,
        );
        $mform->addElement( 'filepicker','officialproposal', get_string('officialproposal','local_trainingprogram'),  null,$filemanageroptions1
           
        );
        $mform->hideIf('officialproposal', 'type', 'neq', '1');
       


          
        //Proposed Cost (Float - Text)
        $mform->addElement('text', 'proposedcost', get_string('proposedcost','local_trainingprogram'), 'maxlength="100" size="10"');
        $mform->addRule('proposedcost', get_string('acceptsnumeric', 'local_trainingprogram'), 'numeric');
        $mform->setType('proposedcost', PARAM_TEXT);
        $mform->hideIf('proposedcost', 'type', 'neq', '1');
        

        //Official P.O (Attachement PDF)
         $filemanageroptions2 = array(
            'accepted_types' => '.pdf',
            'maxbytes' => 0,
            'maxfiles' => 1,
        );
          $mform->addElement( 'filepicker','officialpo', get_string('officialpo','local_trainingprogram'),  null,$filemanageroptions2 
            
        ); 
        $mform->hideIf('officialpo', 'type', 'neq', '1');
        


        //Final PO amount (Float Text)
         $mform->addElement('text', 'finalpoamount', get_string('finalpoamount','local_trainingprogram'), 'maxlength="100" size="10"');
        $mform->addRule('finalpoamount', get_string('acceptsnumeric', 'local_trainingprogram'), 'numeric');
        $mform->setType('finalpoamount', PARAM_TEXT);
        $mform->hideIf('finalpoamount', 'type', 'neq', '1');


        if($programdata->price == 1) {
            $mform->addElement('text', 'sellingprice', get_string('sellingprice','local_trainingprogram'), 'size="10"');
            $mform->addRule('sellingprice', get_string('acceptsnumeric', 'local_trainingprogram'), 'numeric');
            $mform->setType('sellingprice', PARAM_TEXT);
            $mform->disabledIf('sellingprice',  'enrolledcount',  'neq',  0);
            
            $mform->addElement('text', 'actualprice', get_string('actualprice','local_trainingprogram'), ' size="10"');
            $mform->addRule('actualprice', get_string('acceptsnumeric', 'local_trainingprogram'), 'numeric');
            $mform->setType('actualprice', PARAM_TEXT); 
            $mform->disabledIf('actualprice',  'enrolledcount',  'neq',  0);
        
        }
       

     
        $programtype = $this->dataprovider->get_programtype();

        $trainingmethods = [];
        $trainingmethods['online'] = get_string('scheduleonline','local_trainingprogram');
        $trainingmethods['offline'] = get_string('scheduleoffline','local_trainingprogram');
        $trainingmethods['elearning'] = get_string('scheduleelearning','local_trainingprogram');

        $mform->addElement('select','trainingmethod', get_string('trainingmethod', 'local_trainingprogram'), $trainingmethods);
        $mform->disabledIf('trainingmethod',  'enrolledcount',  'neq',  0);

        $virtualplatforms = [];
        $virtualplatforms['1'] = get_string('zoom','local_trainingprogram');
        $virtualplatforms['2'] = get_string('webex','local_trainingprogram');
        $virtualplatforms['3'] = get_string('teams','local_trainingprogram');
        
        $mform->addElement('select', 'meetingtype',get_string('meetingtype','local_trainingprogram'),$virtualplatforms);
        $mform->hideIf('meetingtype', 'trainingmethod', 'eq', 'offline');
        $mform->hideIf('meetingtype', 'trainingmethod', 'eq', 'elearning');
        $mform->disabledIf('meetingtype',  'enrolledcount',  'neq',  0);


        $officials = $this->_ajaxformdata['officials'];
        $allofficials = array();
        if (!empty($officials)) {
            $officials = is_array($officials) ? $officials : array($officials);
            $allofficials = manageuser::get_orgofficial($officials,0);
        }elseif ($id > 0) {
            $allofficials = manageuser::get_orgofficial(array(),$id);
        }
        $trattributes = array(
            'ajax' => 'local_trainingprogram/dynamic_dropdown_ajaxdata',
            'data-type' => 'officials',
            'id' => 'training-officials',
            'data-ctype' => 0,
            'data-programid' =>0,
            'data-offeringid' =>0,
            'multiple'=>true,
            'onchange' => "(function(e){ require(['local_trainingprogram/checkofficialavailibility'], function(s) {s.CheckForOfficialAvailableSlot('training-officials');}) }) (event)"
            );
        $mform->addElement('autocomplete', 'officials',get_string('officials','local_trainingprogram'),$allofficials, $trattributes);
        $mform->hideIf('officials', 'trainingmethod', 'neq', 'online');


        //Classification added..renu
        $classification_options = [];
        $classification_options['1'] = get_string('confidentials','local_trainingprogram');
        $classification_options['2']= get_string('public','local_trainingprogram');
        $mform->addElement('select','classification', get_string('classification', 'local_trainingprogram'),$classification_options);

       

        $halllocation=array();
        $halllocation[] = $mform->createElement('radio', 'halllocation', '', get_string('inside', 'local_trainingprogram'), 'inside');
        $halllocation[] = $mform->createElement('radio', 'halllocation', '', get_string('outside', 'local_trainingprogram'), 'outside', ['id' => 'halllocation-outside']);
        /*$halllocation[] = $mform->createElement('radio', 'halllocation', '', get_string('clientside', 'local_trainingprogram'), 'clientside', ['id' => 'halllocation-clientside']);*/

        $halllocation1=array();
        $halllocation1[] = $mform->createElement('radio', 'halllocation1', '', get_string('inside', 'local_trainingprogram'), 'inside');
        $halllocation1[] = $mform->createElement('radio', 'halllocation1', '', get_string('outside', 'local_trainingprogram'), 'outside');
        $halllocation1[] = $mform->createElement('radio', 'halllocation1', '', get_string('clientside', 'local_trainingprogram'), 'clientside');
      
        $mform->addGroup($halllocation, 'halllocation', get_string('halllocation', 'local_hall'), ['<br/>'], false);
        $mform->setDefault('halllocation', 'inside');
        $mform->addGroup($halllocation1, 'halllocation1', get_string('halllocation', 'local_hall'), ['<br/>'], false);
        $mform->hideIf('halllocation', 'trainingmethod', 'neq', 'offline');
        $mform->hideIf('halllocation', 'availableseats', 'eq', '');

        $mform->hideIf('halllocation1', 'type', 'eq', '0');
        $mform->hideIf('halllocation1', 'trainingmethod', 'neq', 'offline');
        $mform->hideIf('halllocation1', 'type', 'eq', '2');
        $mform->hideIf('halllocation', 'type', 'eq', '1');

          
        $halls =  $DB->get_records_sql_menu("SELECT id, name AS fullname FROM {hall} WHERE availability = 1 ");

        $halloptions = array(
            'multiple' => false,
            'onchange' => "(function(e){ require(['local_hall/hallreserve'], function(s) {s.reservation('tprogram');}) }) (event)",
            'class' => 'entityhall',
            'ajax' => 'local_hall/hall_datasource',
            'data-type' => 'schedulehalls',
            'data-hallid' => 0,
            'noselectionstring' => get_string('selecthallstring', 'local_hall'),
            'placeholder' => get_string('hall','local_hall')            
        );
        $mform->addElement('autocomplete', 'halladdress', get_string('halladress','local_trainingprogram'), [null => get_string('selecthalls', 'local_trainingprogram')/*, 0 => get_string('outside_academy','local_trainingprogram') */]+ $halls, $halloptions);
        $mform->hideIf('halladdress', 'trainingmethod', 'eq', 'online');
        $mform->hideIf('halladdress', 'trainingmethod', 'eq', 'elearning');
        $mform->hideIf('halladdress', 'availableseats', 'eq', '');
        $mform->hideIf('halladdress', 'starttime[hours]', 'eq', 0);
     
        $group = [];
        $group[] =& $mform->createElement('html', '<div class="col-sm-12 mb-2">
                <button type="button" data-action="edithall" data-halllocation = "Outside" data-type = "2" class="btn btn-primary" id="create_hall" style="display:none">'.get_string('addnew', 'local_trainingprogram').'</button>
                </div>'
            );

        $group[] =& $mform->createElement('html', get_string('tpfiledsmandatory', 'local_trainingprogram'));
        $mform->addGroup($group, 'hallalertgroup', '', ' ', false);
        $mform->hideIf('hallalertgroup', 'trainingmethod', 'eq', 'online');
        $mform->hideIf('hallalertgroup', 'trainingmethod', 'eq', 'elearning');
        $mform->hideIf('hallalertgroup', 'halladdress', 'neq', '');
        //$mform->hideIf('hallalertgroup', 'halllocation1', 'eq', 'clientheadquarters');
        if( $id ) {         
            $reservations = (new \local_hall\hall)->entityreservations($id, 'tprogram');
        } else {
            $halladdress = $this->_ajaxformdata['halladdress'];
            $sesskey = $this->_ajaxformdata['sesskey'];
            $reservations = (new \local_hall\hall)->entityreservationsdraft($trainingid, $sesskey, $halladdress, 'tprogram');
        }

        $reservationgroup = [];
        $reservationgroup[] =& $mform->createElement('html', '<div id="hallinformation"><table class="generaltable table"><thead class="thead-light"><tr><th scope="col"><b>'.get_string('hall','local_events').'</b></th><th scope="col"><b>'.get_string('date','local_events').'</b></th><th scope="col"><b>'.get_string('seats','local_events').'</b></th></tr></thead><tbody class="entityhalldetails">'. $reservations .'</tbody></table></div>');
        $mform->addGroup($reservationgroup, 'reservationgroup', '', ' ', false);
        $mform->hideIf('reservationgroup', 'trainingmethod', 'neq', 'offline');
        //$mform->hideIf('reservationgroup', 'halllocation1', 'eq', 'clientheadquarters');
        //$mform->hideIf('reservationgroup', 'halladdress', 'eq', '0');

        //Checkbox
        $mform->addElement('advcheckbox', 'traingagrrement', get_string('traingagrrement', 'local_trainingprogram'), get_string('traingagrrement', 'local_trainingprogram'), array('group' => 1), array(0, 1));

        
        //Training agreement
          $filemanageroptions3 = array(
            'accepted_types' => '.pdf',
            'maxbytes' => 0,    
            'maxfiles' => 1,
        );
        $mform->addElement( 'filepicker','tagrrement', get_string('tagrrement','local_trainingprogram'),  null,$filemanageroptions3); 
        $mform->hideIf('tagrrement', 'traingagrrement', 'neq', '1');

        //Agreed Training Cost
        $mform->addElement('text', 'tcost', get_string('tcost','local_trainingprogram'), 'maxlength="100" size="10"');
        $mform->addRule('tcost', get_string('acceptsnumeric', 'local_trainingprogram'), 'numeric');
        $mform->setType('tcost', PARAM_TEXT);
        $mform->hideIf('tcost', 'traingagrrement', 'neq', '1');
        
      $elinkcheckboxes = array();
        $elinkcheckboxes[] = $mform->createElement('advcheckbox', 'externallinkcheck', null, get_string('externallinkcheck', 'local_trainingprogram'), array(),array(0,1));
        $elinkcheckboxes[] = $mform->createElement('text', 'externallink','',array('class' => 'dynamic_form_id_externallink','placeholder' => get_string('placeholderlink','local_trainingprogram'),'maxlength="100" size="40"'));
        $mform->addGroup($elinkcheckboxes, 'externallink', get_string('externallinkcheck', 'local_trainingprogram'), array(' '), false);
        $mform->disabledif('externallink', 'externallinkcheck', 'neq',1);
        
       
    }

    /**
     * Perform some moodle validation.
     * @param array $data
     * @param array $files
     * @return array
     */
     public function validation($data, $files) {
       global $DB, $CFG;
       $errors = parent::validation($data, $files);
        $draftfiles = file_get_drafarea_files($data['tagrrement']);
        if($data['enrolledcount'] > 0) {
            // $existingseats = $DB->get_field('tp_offerings','availableseats',array('id' => $data['id']));
            if((int)$data['availableseats'] <  $data['enrolledcount']) {
                  $errors['availableseats'] = get_string('cannotbelowerthanexistng', 'local_trainingprogram',$data['enrolledcount']);
            } 
         } else {
         $currdate = date('Y-m-d');
            if($data['trainingmethod'] != 'elearning') {

                if(date("Y-m-d",$data['startdate']) < date("Y-m-d",$data['pstartdate']) || date("Y-m-d",$data['startdate']) > date("Y-m-d",$data['penddate'])) {
                  $errors['startdate'] = get_string('offeringstartlessthanprogram', 'local_trainingprogram');
                }

                if(date("Y-m-d",$data['enddate']) < date("Y-m-d",$data['pstartdate']) || $data['enddate'] > $data['penddate']) {
                    $errors['enddate'] = get_string('offeringendlessthanprogram', 'local_trainingprogram');
                }

                if(date("Y-m-d",$data['startdate']) > date("Y-m-d",$data['enddate'])){
                    $errors['enddate'] = get_string('todatelower', 'local_trainingprogram');
                }

                if(date("Y-m-d",$data['startdate']) < $currdate){
                    $errors['startdate'] = get_string('previousdate', 'local_trainingprogram');
                }
            
            }
            
            if($data['trainingmethod'] == 'offline' && $data['halllocation']=='clientheadquarters') {

                if(empty(trim($data['halladdress']))){
                    $errors['halladdress'] = get_string('halladdressempty', 'local_trainingprogram');
               }
            }
            if(empty(trim($data['availableseats']))){
                $errors['availableseats'] = get_string('seatscannotbeempty', 'local_trainingprogram');
            }
            if(!empty(trim($data['availableseats'])) && !preg_match('/^[0-9]*$/',trim($data['availableseats']))) {
                $errors['availableseats'] = get_string('validseatsrequired', 'local_trainingprogram'); 
            }
            
            if($data['actualprice'] > $data['sellingprice']){
                $errors['sellingprice'] = get_string('sellingpricepricehigher', 'local_trainingprogram');
            }
        
            if(!empty(trim($data['sellingprice'])) && $data['sellingprice'] < 0) {
                $errors['sellingprice'] = get_string('validsellingpricerequired', 'local_trainingprogram'); 
            }
            if(!empty(trim($data['actualprice'])) &&  $data['sellingprice'] < 0) {
                $errors['actualprice'] = get_string('validactualpricerequired', 'local_trainingprogram'); 
            }
            if($data['type'] == 1 && empty($data['organization'])){
               
                $errors['organization'] = get_string('orgrequired', 'local_trainingprogram'); 
            }

            $selectedstarttime = ($data['starttime']['hours'] * 3600) + ($data['starttime']['minutes'] * 60);
            $selectedendtime = ($data['endtime']['hours'] * 3600) + ($data['endtime']['minutes'] * 60);
            $currenttime = (date("H") * 3600) + (date("i") * 60);


            $startdate = date('Y-m-d', $data['startdate']);
            //$currdate = date('Y-m-d');

            if($data['trainingmethod'] != 'elearning') {

                if($startdate == $currdate) {

                    if ($selectedstarttime <= $currenttime) {
                        $errors['starttime'] = get_string('starttimecannotbelessthannow','local_trainingprogram');
                    }  

                    if ($selectedendtime < $currenttime) {
                        $errors['endtime'] = get_string('endtimecannotbelessthannow','local_trainingprogram');
                    }


                }
                if($selectedendtime <= $selectedstarttime) {
                    $errors['endtime'] = get_string('endtimeshowuldhigher','local_trainingprogram');
                }
            }

       }

        if($data['tcost'] < 0) {
            $errors['tcost'] = get_string('negative', 'local_trainingprogram');
               
        }
 
        if($data['finalpoamount'] < 0) {
            $errors['finalpoamount'] = get_string('negative', 'local_trainingprogram');
               
        }

        if($data['proposedcost'] < 0) {
            $errors['proposedcost'] = get_string('negative', 'local_trainingprogram');
               
        }
        if($data['estimatedbudget'] < 0) {
            $errors['estimatedbudget'] = get_string('negative', 'local_trainingprogram');
               
        }
       
        if ($data['traingagrrement'] == 1) {
            if(empty($draftfiles->list)){
                $errors['tagrrement'] = get_string('taisrequired', 'local_trainingprogram');
            }

            if (empty($data['tcost'])) {
                $errors['tcost'] = get_string('tcisrequired', 'local_trainingprogram');
            }
        }
        if (($data['type'] == 0 && $data['trainingmethod'] == 'offline' && $data['halladdress'][0] == 0)) {
            $errors['halladdress'] = get_string('hallrequired', 'local_trainingprogram');
        }
          if($data['externallinkcheck']){
            if(empty($data['externallink'])){
                $errors['externallink'] = get_string('enterextlink', 'local_trainingprogram');
            }
             if (!preg_match('/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i', $data['externallink']) ) {
               $errors['externallink'] = get_string('enterextlinkurl', 'local_trainingprogram');
            }
        }
       return $errors;
     }
    /**
     * Returns context where this form is used
     *
     * @return context
     */
    protected function get_context_for_dynamic_submission(): context {
        return context_system::instance();
    }

    /**
     * Checks if current user has access to this form, otherwise throws exception
     */
    protected function check_access_for_dynamic_submission(): void {
        has_capability('local/trainingprogram:manage', $this->get_context_for_dynamic_submission()) 
        || has_capability('local/organization:manage_trainingofficial', $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/user/profile/definelib.php');
        $data = $this->get_data();
        $context = context_system::instance();
        if (is_array($data->halladdress)) {
            $data->halladdress = $data->halladdress[0];
        }
        if($data) {

            if($data->id <= 0 && !is_siteadmin() &&  has_capability('local/organization:manage_trainingofficial',$context)) {

                (new local_trainingprogram\local\trainingprogram)->add_update_official_schedule_program($data);

            } else {

                (new local_trainingprogram\local\trainingprogram)->add_update_schedule_program($data);
            }
            

            $f2 = $this->save_stored_file('attachmentpdf', $context->id, 'local_trainingprogram', 'attachmentpdf',  $data->attachmentpdf, '/', null, true);
            if($data->officialproposal) {
           
                $f3 = $this->save_stored_file('officialproposal', $context->id, 'local_trainingprogram', 'officialproposal',  $data->officialproposal, '/', null, true);
                    
            }

            if($data->officialpo) {
                  
                $f4 = $this->save_stored_file('officialpo', $context->id, 'local_trainingprogram', 'officialpo',  $data->officialpo, '/', null, true);
                    
            }

            if($data->tagrrement) {
             
                $f5 = $this->save_stored_file('tagrrement', $context->id, 'local_trainingprogram', 'tagrrement',  $data->tagrrement, '/', null, true);
                    
            }
        }
        
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
        if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $ajaxdata = $this->_ajaxformdata;
            $offeringdata = (new local_trainingprogram\local\trainingprogram)->set_schedule_program($id, $ajaxdata);

            $this->set_data($offeringdata);
        }
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $id = $this->optional_param('id', 0, PARAM_INT);
        return new moodle_url('/local/trainingprogram/index.php',
            ['action' => 'editcategory', 'id' => $id]);
    }     
}
