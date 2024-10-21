<?php
// This file is part of Moodle - http://moodle.org/
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
 * @package    auth_registration
 * @copyright  2022 eAbyas Info Solutions<info@eabyas.com>
 * @author     Vinod Kumar  <vinod.p@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
global $DB, $CFG, $OUTPUT,$USER, $PAGE;
use \auth_registration\form\individual_registration_form as registration_form;

if(isloggedin()) {
    redirect($CFG->wwwroot);
} else {
   $systemcontext = context_system::instance();
   $PAGE->set_context($systemcontext);
   $returnurl = new moodle_url('/auth/registration/register.php');
   $PAGE->set_url('/auth/registration/register.php');
   $PAGE->requires->jquery();
   $PAGE->requires->js('/auth/registration/js/formsubmission.js');
   $iamloginurl = get_auth_plugin('iam')->get_login_url();
    
   $individualregform = new registration_form(null, array(''), 'post', '', null, true,(array)data_submitted());
   if ($individualregform->is_cancelled()) {
      redirect($CFG->wwwroot . '/login/index.php');
   } else if ($data = $individualregform->get_data()) {
      $data->country_code = str_replace('+', '', $data->country_code);
      if ($data->regtype == 0  && !empty($data->saudid)){
         redirect($iamloginurl,'',0);
      } else{
     $records = new auth_registration\action\manageuser();
     $insert_user_record = $records->create_custom_user($data);
     $description= get_string('insert_descption','auth_registration',$data);
     $insert_user_logs =$records->local_users_logs('registered', 'registration', $description);
      $data=[
         'message_title'=>get_string('message_title', 'auth_registration'),
         'message_heading'=>get_string('message_heading', 'auth_registration'),
         'message_body'=>get_string('message_body', 'auth_registration'),
         'message_url'=>$CFG->wwwroot.'/login/index.php',
         'message_footer'=>get_string('message_footer', 'auth_registration'),
      ];
     echo $OUTPUT->render_from_template('auth_registration/successdialogbox',$data);
   }
   }
   echo $OUTPUT->header();
   $context=[
      'individualregform'=>$individualregform->render(),
   ];
   echo $OUTPUT->render_from_template('auth_registration/registration',$context);

}
echo $OUTPUT->footer();


