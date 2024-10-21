<?php
/**
 * This file is part of eAbyas
 *
 * Copyright eAbyas Info Solutons Pvt Ltd, India
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
 * @author eabyas  <info@eabyas.in>
 * @package local_notifications
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Serve the new notification form as a fragment.
 *
 * @param array $args List of named arguments for the fragment loader.
 * @return string
 */
function local_notifications_output_fragment_new_notification_form($args) {
    global $CFG, $DB, $PAGE;
    $args = (object) $args;
    $context = $args->context;
    $id = $args->id;
    $o = '';
	
    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }
    $data = new stdclass();
    if ($id > 0) {
        $data = $DB->get_record('local_notification_info', array('id'=>$id));
   

        $data->body =       array('text'=>$data->body, 'format'=>1);

        $data->arabic_body =       array('text'=>$data->arabic_body, 'format'=>1);
    
		if(!empty($data->moduleid)){
			$args->moduleid=explode(',',$data->moduleid);
		}
		if (!empty($formdata)) {
			$args->moduleid=$formdata['moduleid'];
		}
        $mform = new \local_notifications\forms\notification_form(null, array('form_status' => $args->form_status,'id' => $id,'notificationid'=>$args->notificationid,'moduleid'=>$args->moduleid), 'post', '', null, true, $formdata);
        $mform->set_data($data);
    }else{
    $params = array('form_status' => $args->form_status,'id' => $id,'notificationid'=>$formdata['notificationid'],'moduleid'=>$formdata['moduleid']);
    $mform = new \local_notifications\forms\notification_form(null, $params, 'post', '', null, true, $formdata);
    }

    if (!empty($args->jsonformdata)) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }
    
    $formheaders = array_keys($mform->formstatus);
    $nextform = array_key_exists($args->form_status, $formheaders);
    if ($nextform === false) {
        return false;
    }
    $renderer = $PAGE->get_renderer('local_notifications');
    ob_start();
    $formstatus = array();
    foreach (array_values($mform->formstatus) as $k => $mformstatus) {
        $activeclass = $k == $args->form_status ? 'active' : '';
        $formstatus[] = array('name' => $mformstatus, 'activeclass' => $activeclass);
    }
    $formstatusview = new \local_notifications\output\form_status($formstatus);
    $o .= $renderer->render($formstatusview);
    $mform->display();
    $o .= ob_get_contents();
    ob_end_clean();
 
    return $o;
}
function notifications_filter($mform){
    global $DB,$USER;
    $systemcontext = context_system::instance();
    if ((has_capability('local/notifications:view', context_system::instance()) || is_siteadmin() || has_capability('local/organization:manage_communication_officer', context_system::instance()))) {
        $notificationlist = $DB->get_records_sql_menu("SELECT id,name FROM {local_notification_type} WHERE parent_module > 0 AND shortname <> 'course_module_created'");
    }
    
    $select = $mform->addElement('autocomplete', 'notificationid', '', $notificationlist, array('placeholder' => get_string('notification_type', 'local_notifications')));
    $mform->setType('notificationid', PARAM_RAW);
    $select->setMultiple(true);
}
function local_notifications_leftmenunode(){
    $systemcontext = context_system::instance();
    $notificationsnode = '';
    if(is_siteadmin() || has_capability('local/organization:manage_communication_officer',$systemcontext)){
        $notificationsnode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_notifications', 'class'=>'pull-left user_nav_div notifications'));
        $notifications_url = new moodle_url('/local/notifications/index.php');
        $notifications = html_writer::link($notifications_url, '<span class="notifications_icon side_menu_img_icon"></span><span class="user_navigation_link_text">'.get_string('pluginname','local_notifications').'</span>',array('class'=>'user_navigation_link'));
            $notificationsnode .= $notifications;
        $notificationsnode .= html_writer::end_tag('li');
    }

    return array('16' => $notificationsnode);
}
function notifications_filters_form($filterparams){

    global $CFG;

    require_once($CFG->dirroot . '/local/organization/dynamicfilters_form.php');


    $filters = array(
        'notifications'=>array('local'=>array('notifications')),
        );

    $mform = new dynamicfilters_form(null, array('filterlist'=>$filters,'filterparams' => $filterparams,'submitid' =>'viewnotifications','ajaxformsubmit'=>true), 'post', '', null, true,$_REQUEST);

    return $mform;
}
