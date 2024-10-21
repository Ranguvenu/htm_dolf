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
namespace local_exams\form;

use core_form\dynamic_form;
use moodle_url;
use context;
use context_system;
use local_exams\local\exams as exam;
use local_trainingprogram\local\trainingprogram as program;

/**
 * TODO describe file replaceuserform
 *
 * @package    local_exams
 * @copyright  2023 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class replaceuserform extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB;
        $corecomponent = new \core_component();
        $mform = $this->_form;
        $productid = $this->optional_param('productid', 0, PARAM_INT);
        $userid = $this->optional_param('userid', 0, PARAM_INT);
        $username = $this->optional_param('username', null, PARAM_RAW);
        $useridnumber = $this->optional_param('useridnumber', 0, PARAM_RAW);
        $costtype = $this->optional_param('costtype',0, PARAM_INT);
        $entitytype = $this->optional_param('entitytype',null, PARAM_RAW);
        $policyconfirm = $this->optional_param('policyconfirm',0, PARAM_INT);
        
        if($entitytype == 'exam') {
            $fieldid = (int)$DB->get_field('tool_products','referenceid',['id'=>$productid]);
            $profilerecord =$DB->get_record('local_exam_profiles',['id'=>$fieldid]);
            $rootid =(int) $profilerecord->examid;
            $type = 'exam';
           
        } else if($entitytype == 'trainingprogram') {
            $fieldid = (int)$DB->get_field('tool_products','referenceid',['id'=>$productid]);
            $offeringrecord =$DB->get_record('tp_offerings',['id'=>$fieldid]);
            $rootid =(int) $offeringrecord->trainingid;
            $type = 'program';         
        } else {
            $rootid = $fieldid = (int)$DB->get_field('tool_products','referenceid',['id'=>$productid]);
            $type = 'event';   
        }
        $profileid = (int)$DB->get_field('tool_products','referenceid',['id'=>$productid]);
        $profilerecord =$DB->get_record('local_exam_profiles',['id'=>$profileid]);
        $examid =(int) $profilerecord->examid;

        $mform->addElement('hidden', 'productid', $productid);
        $mform->setType('productid',PARAM_INT);

        $mform->addElement('hidden', 'rootid', $rootid);
        $mform->setType('rootid',PARAM_INT);
        
        $mform->addElement('hidden', 'fieldid',$fieldid);
        $mform->setType('fieldid',PARAM_INT);

        $mform->addElement('hidden', 'fromuserid',$userid);
        $mform->setType('fromuserid',PARAM_INT);

        $mform->addElement('hidden', 'username',$username);
        $mform->setType('username',PARAM_RAW);

        $mform->addElement('hidden', 'useridnumber',$useridnumber);
        $mform->setType('useridnumber',PARAM_RAW);

        $mform->addElement('hidden', 'costtype',$costtype);
        $mform->setType('costtype',PARAM_INT);

        $mform->addElement('hidden', 'entitytype',$entitytype);
        $mform->setType('entitytype',PARAM_RAW);

        $mform->addElement('hidden', 'policyconfirm',$policyconfirm);
        $mform->setType('policyconfirm',PARAM_INT);
        
        $fromuser =$username.'('.$useridnumber.')'; 

        $mform->addElement('static', 'from_user', get_string('replacefrom', 'local_exams'),$fromuser);

        $userattributes = array(
        'ajax' => 'local_exams/getuserstoreplace',
        'data-type' => $type,
        'data-replacinguserid' => $userid,
        'data-rootid' => $rootid,
        'data-fieldid' => $fieldid,
        'multiple'=>false,
        );
        $mform->addElement('autocomplete', 'touserid', get_string('replaceto', 'local_exams'),[], $userattributes);
        $mform->addRule('touserid', get_string('selectusertoreplace', 'local_exams'), 'required');

    }
    public function validation($data, $files) {
        global $DB, $CFG;
        $errors = array();
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
        
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $USER,$DB;
        $data =$this->get_data();
        $context = context_system::instance();
        if($data->entitytype == 'exam') {
            $courseid = (int)$DB->get_field('local_exams','courseid',['id'=>$data->rootid]);
            $enrollmentrecord =$DB->get_record('exam_enrollments',['examid'=>$data->rootid,'profileid'=>$data->fieldid,'courseid'=>$courseid,'userid'=>$data->fromuserid]);                
            $enrolleduseroleinfo = $DB->get_record_sql('SELECT rol.* FROM {role} rol 
                                                        JOIN {role_assignments} rola ON rola.roleid = rol.id
                                                        WHERE rola.userid =:userid and contextid =:contextid',['userid'=>$enrollmentrecord->usercreated,'contextid'=>$context->id]);
    
          $enrollinguserid = ($enrolleduseroleinfo->shortname == 'organizationofficial') ? $enrollmentrecord->usercreated :(($enrollmentrecord->orgofficial > 0) ? $enrollmentrecord->orgofficial : $USER->id);
        } else if($data->entitytype == 'trainingprogram') {
            $courseid = (int)$DB->get_field('local_trainingprogram','courseid',['id'=>$data->rootid]);
            $enrollmentrecord =$DB->get_record('program_enrollments',['programid'=>$data->rootid,'offeringid'=>$data->fieldid,'courseid'=>$courseid,'userid'=>$data->fromuserid]);                
            $enrolleduserroleinfo = $DB->get_record_sql('SELECT rol.* FROM {role} rol 
                                                        JOIN {role_assignments} rola ON rola.roleid = rol.id
                                                        WHERE rola.userid =:userid and contextid =:contextid',['userid'=>$enrollmentrecord->usercreated,'contextid'=>$context->id]);
           $enrollinguserid = ($enrolleduserroleinfo->shortname == 'organizationofficial') ? $enrollmentrecord->usercreated :(($enrollmentrecord->orgofficial > 0) ? $enrollmentrecord->orgofficial : $USER->id);
        } else {
            $enrolleduserid =(int) $DB->get_field('local_event_attendees','usercreated',['eventid'=>$data->rootid,'userid'=>$data->fromuserid]);                
            $enrolleduseroleinfo = $DB->get_record_sql('SELECT rol.* FROM {role} rol 
                                                        JOIN {role_assignments} rola ON rola.roleid = rol.id
                                                        WHERE rola.userid =:userid and contextid =:contextid',['userid'=>$enrolleduserid,'contextid'=>$context->id]);
            $enrollinguserid =($enrolleduseroleinfo->shortname == 'organizationofficial') ? $enrolleduserid : $USER->id;
        }
        $data->userid = $USER->id;
        $data->enrollinguserid = $enrollinguserid;
        return $data;
    }
    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        
    }
    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        global $USER,$DB;
        $productid = $this->optional_param('productid', 0, PARAM_INT);
        $entitytype = $this->optional_param('entitytype',null, PARAM_RAW);
        if($entitytype == 'exam') {
            $profileid = (int)$DB->get_field('tool_products','referenceid',['id'=>$productid]);
            $profilerecord =$DB->get_record('local_exam_profiles',['id'=>$profileid]);
            $examid =(int) $profilerecord->examid;
            $url = new moodle_url('/local/exams/examusers.php',
            ['id' => $examid]);
        } else if($entitytype == 'trainingprogram') {
            $offeringid = (int)$DB->get_field('tool_products','referenceid',['id'=>$productid]);
            $offeringrecord =$DB->get_record('tp_offerings',['id'=>$offeringid]);
            $programid =(int) $offeringrecord->trainingid;
            $url = new moodle_url('/local/trainingprogram/programenrolleduserslist.php',
            ['programid' => $programid]);

        } else {
            $eventid = (int)$DB->get_field('tool_products','referenceid',['id'=>$productid]);
            $url = new moodle_url('/local/events/attendees.php',
            ['id' => $eventid]);
        }
        return $url ;
    }    
}
