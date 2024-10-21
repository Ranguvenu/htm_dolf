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
use core_form\dynamic_form ;
use moodle_url;
use context;
use context_system;
use local_trainingprogram\local\trainingprogram as tp;
use coding_exception;
use MoodleQuickForm_autocomplete;
use local_userapproval\action\manageuser;


/**
 * TODO describe file trainerassignform
 *
 * @package    local_trainingprogram
 * @copyright  2023 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class trainerassignform extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB, $PAGE;
        $programid = $this->optional_param('programid', 0, PARAM_INT);
        $offeringid = $this->optional_param('offeringid', 0, PARAM_RAW);
        $roleid = $this->optional_param('roleid', 0, PARAM_RAW);

        $corecomponent = new \core_component();

        $mform = $this->_form;
        $id = $this->_customdata['id'];
        $editoroptions = $this->_customdata['editoroptions'];
         
        $systemcontext = context_system::instance();

        $mform->addElement('hidden', 'programid', $programid);
        $mform->setType('programid',PARAM_INT);

        $mform->addElement('hidden', 'offeringid', $offeringid);
        $mform->setType('offeringid',PARAM_RAW);

        $mform->addElement('hidden', 'roleid', $roleid);
        $mform->setType('roleid',PARAM_RAW);

        $mform->addElement('hidden', 'editingroleid', '',array('class'=>'editingtrainer'));
        $mform->setType('roleid',PARAM_RAW);


        $trainerRoleId = $DB->get_field('role', 'id', ['shortname' => 'trainer']);
        $editingtrainerRoleId = $DB->get_field('role', 'id', ['shortname' => 'editingtrainer']);
        $editingtrainerRoleId = $editingtrainerRoleId ? ", $editingtrainerRoleId" : false;
        // print_r($editingtrainerRoleId);die;
         
        $programenrole = $DB->get_record_sql("SELECT * FROM {program_enrollments} WHERE programid = :programid AND offeringid =:offeringid AND roleid IN($trainerRoleId $editingtrainerRoleId) ",
            [
                'programid' => $programid,
                'offeringid' => $offeringid
            ]
        );
        if($programenrole->id >0){
            if($programenrole->trainertype==0){

                $mform->addElement('static', 'assigntrainer', '',get_string('individual', 'local_trainingprogram'), 0);

                $mform->createElement('static', 'assigntrainer', '',get_string('individual', 'local_trainingprogram'), 0);
        
                $userlist = $this->_ajaxformdata['users'];
                $users = array();
                if (!empty($userlist)) {
                    $users = manageuser::get_orgofficial($userlist,0);
                }
                $trattributes = array(
                'ajax' => 'local_trainingprogram/dynamic_dropdown_ajaxdata',
                'data-type' => 'programusers',
                'id' => 'program_users',
                'data-ctype' => 1,
                'data-programid' => $programid,
                'data-offeringid' => $offeringid,
                'onchange'=> "(function(e){require(['local_trainingprogram/assigneditingtrainer'], function(s) {s.users();}) }) (event)",
                'multiple'=>true,
                );
                $mform->addElement('autocomplete', 'users', get_string('trainers', 'local_trainingprogram'),[], $trattributes);
                $mform->hideIf('users', 'assigntrainer', 'eq', '1');
            }

            if($programenrole->trainertype==1){
                $mform->addElement('static', 'assigntrainer', '',get_string('orgainzation', 'local_trainingprogram'), 1);
                $get_org=$DB->get_records_sql("SELECT * FROM {local_organization} WHERE partner=1");

                $allorg=array();

                foreach($get_org as $org){          
                    $id=$org->id;
                    $value = $org->fullname;
                    $allorg[$id] = $value;
                    
                }
                
                $mform->addElement('autocomplete', 'organizations', get_string('orgainzationfld', 'local_trainingprogram'),$allorg);
                $mform->hideIf('organizations', 'assigntrainer', 'eq', '0');

                $oguserlist = $this->_ajaxformdata['oguser'];
                $ogusers = array();
                if (!empty($oguserlist)) {
                    $ogusers = manageuser::get_orgofficial($oguserlist,0);
                }

                $orgtrainers = array(
                'ajax' => 'local_trainingprogram/orgtrainer',
                'id' => 'org_users',
                'data-programid' => $programid,
                'data-offeringid' => $offeringid, 
                'onchange' => "(function(e){require(['local_trainingprogram/assigneditingtrainer'], function(s) {s.orgusers(e);}) }) (event)",
                'noselectionstring' => get_string('noselection', 'local_trainingprogram'),
                );
            
                $mform->addElement('autocomplete', 'oguser', get_string('orgtrainers', 'local_trainingprogram'),$ogusers,$orgtrainers);
                $mform->hideIf('oguser', 'assigntrainer', 'eq', '0');
            }
        
        } else{

            $availablefromgroup=array();
            $availablefromgroup[] =& $mform->createElement('radio', 'assigntrainer', '',get_string('individual', 'local_trainingprogram'), 0);
            $availablefromgroup[] =& $mform->createElement('radio', 'assigntrainer', '',get_string('orgainzation', 'local_trainingprogram'), 1);
            $mform->addGroup($availablefromgroup, 'selectone', '', '', false);
            $userlist = $this->_ajaxformdata['users'];
            $users = array();
            if (!empty($userlist)) {
                $users = manageuser::get_orgofficial($userlist,0);
            }
            $trattributes = array(
            'ajax' => 'local_trainingprogram/dynamic_dropdown_ajaxdata',
            'data-type' => 'programusers',
            'id' => 'program_users',
            'data-ctype' => 1,
            'data-programid' => $programid,
            'data-offeringid' => $offeringid,
            // 'onchange'=> "function(e){ require(['local_trainingprogram/assigneditingtrainer'], function(s) {s.users();}) } (event)",
            'onchange'=> "(function(e){require(['local_trainingprogram/assigneditingtrainer'], function(s) {s.users(e);}) }) (event)",
            'multiple'=>true,
            );
            $mform->addElement('autocomplete', 'users', get_string('trainers', 'local_trainingprogram'),$users, $trattributes);
            //$mform->addRule('users', get_string('selecttrainer','local_trainingprogram'), 'required');
            $mform->hideIf('users', 'assigntrainer', 'eq', '1');
            
            
            $get_org=$DB->get_records_sql("SELECT * FROM {local_organization} WHERE partner=1 ");

            $allorg=array();

            foreach($get_org as $org){          
                $id=$org->id;
                $value = $org->fullname;
                $allorg[$id] = $value;
                
            }
            $mform->addElement('autocomplete', 'organizations', get_string('orgainzationfld', 'local_trainingprogram'),$allorg);
            $mform->hideIf('organizations', 'assigntrainer', 'eq', '0');
           

            $oguserlist = $this->_ajaxformdata['oguser'];
            $ogusers = array();
            if (!empty($oguserlist)) {
                $ogusers = manageuser::get_orgofficial($oguserlist,0);
            }
            $orgtrainers = array(
            'ajax' => 'local_trainingprogram/orgtrainer',
            'onchange' => "(function(e){require(['local_trainingprogram/assigneditingtrainer'], function(s) {s.orgusers(e);}) }) (event)",
            'noselectionstring' => get_string('noselection', 'local_trainingprogram'),
            );
            
            $mform->addElement('autocomplete', 'oguser', get_string('orgtrainers', 'local_trainingprogram'),$ogusers,$orgtrainers);
            $mform->hideIf('oguser', 'assigntrainer', 'eq', '0');
         $PAGE->requires->js_call_amd('local_trainingprogram/assigneditingtrainer', 'init');
        }

    }
    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        global $DB;
        if(isset($data['assigntrainer'])) {
            if((int)$data['assigntrainer'] == 0 && empty($data['users'])) {
                $errors['users'] = get_string('selecttrainer', 'local_trainingprogram');
            }
            if((int)$data['assigntrainer'] == 1 && empty($data['oguser'])) {
                $errors['oguser'] = get_string('selecttrainer', 'local_trainingprogram');
            } 
        }
        $record = (new tp)->useraccessingsameactivity($data);
        if($record['notalowed']) {
            if(isset($data['assigntrainer'])) {
                if((int)$data['assigntrainer'] == 0) {
                    $errors['users'] = $record['errormessage'];
                } else {
                    $errors['oguser'] = $record['errormessage'];
                }
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
        //require_capability('moodle/site:config', $this->get_context_for_dynamic_submission());
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
        $tprogram=$DB->get_record('tp_offerings',array('trainingid'=>$data->programid,'id'=>$data->offeringid));
        if($tprogram) {
            $tprogram->trainertype=(!empty($data->assigntrainer)) ?  $data->assigntrainer:$tprogram->trainertype ;
            $tprogram->trainerorg=$data->organizations;
            $DB->update_record('tp_offerings',$tprogram);
        }
        (new tp)->enroll_trainer($data);
     
        
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $id = $this->optional_param('id', 0, PARAM_INT);
        return new moodle_url('/local/trainingprogram/programenrollment.php?programid='.$program_id.'& roleid='.$roleid);
    }  
    
    
}
