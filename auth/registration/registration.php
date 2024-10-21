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
 * Competency view page
 *
 * @package    auth_registration
 * @copyright  2022 e abyas  <info@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use auth_registration\action\registration as registration;
require_once('../../config.php');
require_once($CFG->libdir . '/filelib.php');
global $CFG, $PAGE, $OUTPUT, $DB;


if(isloggedin()) {
    redirect($CFG->wwwroot);
} else {
    $PAGE->set_url('/auth/registration/registration.php');
    $PAGE->set_context(context_system::instance());
    $PAGE->set_title(get_string('organization_registration', 'auth_registration'));
    $PAGE->set_heading(get_string('organization_registration', 'auth_registration'));
    $PAGE->requires->jquery();
    $PAGE->requires->js('/auth/registration/js/formsubmission.js',TRUE);
    $returnurl = new moodle_url('/login/index.php');
    $approvalletterfile=$_FILES;

    $_REQUEST['files']=$_FILES;
   

    $registrationform = new auth_registration\form\organization_registration_form(null, array('form_status' => $_REQUEST['form_status']), 'post', '', null, true,$_REQUEST);
    $registration=$registrationform->render();
  
    if ($registrationform->is_cancelled()) {
       
   
    } else if ($validateddata = $registrationform->get_data()) {
        if($validateddata->form_status == 1){

          
            
            if(isset($approvalletterfile['approval_letter']) && ($approvalletterfile['approval_letter']['name']!="") ){
                $validateddata->approval_letter=registration::approval_letter_store($approvalletterfile);
                $submitdata= registration::organization_registration($validateddata);
                redirect($returnurl);
            }else{
                
                $validateddata->form_status = $validateddata->form_status+1;
                $validateddata->licensekey = $validateddata->licensekey;
           
         
                if($DB->record_exists('organization_draft',array('licensekey'=>$validateddata->licensekey)) && !$DB->record_exists('local_organization',array('licensekey'=>$validateddata->licensekey))) {
                    $orgid=$DB->get_field('organization_draft','id',array('licensekey'=>$validateddata->licensekey));
                    $validateddata->id=$orgid;
                    $validateddata->timecreated=time();
                    $validateddata->usercreated=time();
                    $validateddata->status=1;
                    $validateddata->orgsector =$validateddata->sectors? implode(',', array_filter($validateddata->sectors)) : null;
                    $validateddata->orgsegment = $validateddata->segment? implode(',', array_filter($validateddata->segment)) : null;
                    $org_draft = $DB->update_record('organization_draft', $validateddata);
                }else{
                    $validateddata->timecreated = time();
                    $validateddata->usercreated = time();
                    $validateddata->orgsector = $validateddata->sectors? implode(',', array_filter($validateddata->sectors)) : null;
                    $validateddata->orgsegment = $validateddata->segment? implode(',', array_filter($validateddata->segment)) : null;
                    $validateddata->status = 1;
                    $org_draft = $DB->insert_record('organization_draft', $validateddata);
                }
                $registrationform = new auth_registration\form\organization_registration_form(null, array('form_status' => $validateddata->form_status,'licensekey'=>$validateddata->licensekey), 'post', '', null, true,(array)$validateddata);
                $registration=$registrationform->render();
            }
        }elseif($validateddata->form_status == 2){
           
            if(!empty($validateddata->cancel)&& $validateddata->cancel != '' && empty($validateddata->submit)){
           
                $validateddata->form_status = $validateddata->form_status-1;
                $validateddata->licensekey = $validateddata->licensekey;
 
                $registrationform = new auth_registration\form\organization_registration_form(null, array('form_status' => $validateddata->form_status,'licensekey'=>$validateddata->licensekey), 'post', '', null, true,(array)$validateddata);
                $registration=$registrationform->render();
              
            } elseif(!empty($validateddata->submit)&& $validateddata->submit != '' && empty($validateddata->cancel)){
                $validateddata->form_status = $validateddata->form_status+1;
                $validateddata->licensekey = $validateddata->licensekey;
 
                $registrationform = new auth_registration\form\organization_registration_form(null, array('form_status' => $validateddata->form_status,'licensekey'=>$validateddata->licensekey), 'post', '', null, true,(array)$validateddata);
                $registration=$registrationform->render();

            }
         }elseif($validateddata->form_status == 3){
             
            if(!empty($validateddata->cancel)&& $validateddata->cancel != '' && empty($validateddata->submit)){
           
                $validateddata->form_status = $validateddata->form_status-1;
                $validateddata->licensekey = $validateddata->licensekey;

                $registrationform = new auth_registration\form\organization_registration_form(null, array('form_status' => $validateddata->form_status,'licensekey'=>$validateddata->licensekey), 'post', '', null, true,(array)$validateddata);
                $registration=$registrationform->render();
              
            } elseif(!empty($validateddata->submit)&& $validateddata->submit != '' && empty($validateddata->cancel)){
      
                if(isset($approvalletterfile['approval_letter']) && ($approvalletterfile['approval_letter']['name']!="") ){
                    $organizationdata = $DB->get_record('organization_draft',array('licensekey'=>$validateddata->licensekey));
                    $organizationdata->approval_letter=registration::approval_letter_store($approvalletterfile);
                    if($DB->record_exists('organization_draft',array('licensekey'=>$validateddata->licensekey)) && !$DB->record_exists('local_organization',array('licensekey'=>$validateddata->licensekey))) {
                    $submitdata = registration::organization_registration($organizationdata);

                    }
                }
                if($submitdata){
                    $validateddata->form_status = $validateddata->form_status+1;
                    $validateddata->licensekey = $validateddata->licensekey;

                    $registrationform = new auth_registration\form\organization_registration_form(null, array('form_status' => $validateddata->form_status,'licensekey'=>$validateddata->licensekey), 'post', '', null, true,(array)$validateddata);
                    $registration=$registrationform->render();
                }

            }
         }  
         else{
            $validateddata->form_status = $validateddata->form_status+1;
            $validateddata->licensekey = $validateddata->licensekey;      
            $registrationform = new auth_registration\form\organization_registration_form(null, array('form_status' => $validateddata->form_status,'licensekey'=>$validateddata->licensekey), 'post', '', null, true,(array)$validateddata);
            $registration=$registrationform->render();
        }
    }else{
        $registration=$registrationform->render();
    }
    $context=[
        'org_registration'=>$registration,
    ];
    echo $OUTPUT->header();
     echo $OUTPUT->render_from_template('auth_registration/org_registration',$context);
     echo $OUTPUT->footer();
}



