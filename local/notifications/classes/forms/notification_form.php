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

/**
 *
 * @package    notifications
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_notifications\forms;
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot .'/local/notifications/lib.php');
use moodleform;
use stdClass;
class notification_form extends moodleform {
    public $formstatus;
    public function __construct($action = null, $customdata = null, $method = 'post', $target = '', $attributes = null, $editable = true, $formdata = null) {

        $this->formstatus = array(
            'generaldetails' => get_string('generaldetails', 'local_notifications'),
            );
        parent::__construct($action, $customdata, $method, $target, $attributes, $editable, $formdata);
    }

    public function definition() {
        global $DB, $PAGE, $USER, $OUTPUT;
        $mform = $this->_form;
        $form_status = $this->_customdata['form_status'];
        $org = $this->_customdata['org'];
        $id = $this->_customdata['id'] > 0 ? $this->_customdata['id'] : 0;
        $context = \context_system::instance();
		
		$moduleid = $this->_customdata['moduleid'];
		$notificationid = $this->_customdata['notificationid'];
		if($id){
       		$formdata = $DB->get_record('local_notification_info', array('id' => $id));
		}else{
			$formdata = new stdClass();
		}     

            
        $notification_type = array();
        $select = array();
        $select[null] = get_string('select_opt', 'local_notifications');
        $notification_type[null] = $select;
        $module_categories = $DB->get_records('local_notification_type', array('parent_module'=>0));
        if($module_categories){
             foreach($module_categories as $module_category){
                $lang= current_language();
                if( $lang == 'ar'){
                    $notifications = $DB->get_records_sql_menu("SELECT id,arabicname as arabicname FROM {local_notification_type} WHERE parent_module = {$module_category->id} AND parent_module <> 0");
                        $notification_type[$module_category->arabicname] = $notifications; 
      

                }else{
                    $notifications = $DB->get_records_sql_menu("SELECT *  FROM {local_notification_type} WHERE parent_module = {$module_category->id} AND parent_module <> 0");
                    $notification_type[$module_category->name] = $notifications;     

                }

            }

        }
        $mform->addElement('selectgroups', 'notificationid', get_string('notification_type', 'local_notifications'), $notification_type,array());
        $mform->addRule('notificationid', null, 'required', null, 'client');  
        $mform->addHelpButton('notificationid','notification_help','local_notifications');


		$strings = get_string('none','local_notifications');
		$notification_selected = $this->_ajaxformdata['notificationid'];
        if($id > 0 || ($notificationid&&is_array($moduleid)&&!empty($moduleid))){
			if($id > 0){
				$notifyid = $DB->get_record('local_notification_info',  array('id'=>$id));
				
				$notif_type = $DB->get_record('local_notification_type', array('id'=>$notifyid->notificationid),'shortname,plugintype,pluginname');
			}else{

				$notif_type = $DB->get_record('local_notification_type', array('id'=>$notificationid),'shortname,plugintype,pluginname');
			}
			$classlib = ''.$notif_type->plugintype.'_' .$notif_type->pluginname.'\notification';

			$lib = new $classlib();
			$strings = $lib->get_string_identifiers($notif_type->shortname);
		}

        $mform->addElement('static', 'string_identifiers', get_string('string_identifiers', 'local_notifications'),  $strings);
        $mform->addHelpButton('string_identifiers', 'strings', 'local_notifications');
        $mform->hideIf('string_identifiers', 'notificationid', 'eq', NULL);

        $mform->addElement('text', 'subject', get_string('english_subject', 'local_notifications'));
        $mform->setType('subject', PARAM_RAW);
        $mform->addRule('subject', null, 'required', null, 'client'); 

        $mform->addElement('editor', 'body', get_string('english_emp_body', 'local_notifications'), array(), array('autosave'=>false));
        $mform->setType('body', PARAM_RAW);

        $mform->addElement('text', 'arabic_subject', get_string('arabic_subject', 'local_notifications'));
        $mform->setType('arabic_subject', PARAM_RAW);
        $mform->addRule('arabic_subject', null, 'required', null, 'client'); 

        $mform->addElement('editor', 'arabic_body', get_string('arabic_emp_body', 'local_notifications'), array(), array('autosave'=>false));
        $mform->setType('arabic_body', PARAM_RAW);

        
        
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->disable_form_change_checker();
    }
    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
		$mform = $this->_form;
        $moduleid = $this->_customdata['moduleid'];
		
        $notificationid = $data['notificationid'];
        $id = $data['id'];

        if ($notification = $DB->get_record('local_notification_info', array('notificationid' => $data['notificationid']), 'id', IGNORE_MULTIPLE)) {

            if ($notification->id != $data['id']) {

                    $errors['notificationid'] = get_string('codeexists', 'local_notifications');
            }
        }
        
        return $errors;
    }
    
}
