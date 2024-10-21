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
 * TODO describe file lib
 *
 * @package    local_trainingprogram
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use local_competency\competency;
use local_exams\local\exams as exams;
function local_trainingprogram_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {

    if ($context->contextlevel != CONTEXT_SYSTEM) {
        return false;
    }
    // if($filearea != 'trainingprogramlogo'){
    //     return false;
    // }
    $postid = (int)array_shift($args);
    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/local_trainingprogram/$filearea/$postid/$relativepath";
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }
    // finally send the file
    send_stored_file($file, 0, 0, true, $options); // download MUST be forced - security!
}
function local_organization_output_fragment_curentoffering($args) {
     global $DB,$USER;
    $sql = "SELECT * FROM {tp_offerings}";
        $tp_offerings=$DB->get_records_sql($sql);
         // print_r($tp_offerings);
        $count = 1;
        foreach($tp_offerings as $tp_offerings){
            //$select_children_list[$children_list->id]=$children_list->username;
            $startdate = $tp_offerings->startdate;
            $enddate = $tp_offerings->enddate;
            $out .= "<h3>current Offering". $count."</h3>";
            $out .="<table class = 'generaltable'><thead><th>Startdate</th><th>Enddate</th><th>available Seats</th><th>Hall Address</th><th>Program Type</th></thead><tbody> ";
            $out .= "<tr><td>".$startdate."</td><td>".$enddate."</td><td>".$tp_offerings->availableseats."</td><td>".$tp_offerings->halladdress."</td><td>".$tp_offerings->type."</td></tr></tbody></table>";
            $count++;
        }
        
    return $out;

}
function trainingprogram_filters_form($filterparams){

    global $CFG,$PAGE;
    require_once($CFG->dirroot . '/local/organization/dynamicfilters_form.php');
   $systemcontext = context_system::instance();

    if($PAGE->pagelayout == 'base') {

        // if(!is_siteadmin() &&(has_capability('local/organization:manage_trainee', $systemcontext) || has_capability('local/organization:manage_trainer', $systemcontext))) {
        if(!is_siteadmin() && has_capability('local/organization:manage_trainer', $systemcontext)) {

            $filters = array(
               'trainingprogram'=>array('local'=>array('tp_sector','tp_jobfamily','tp_competencylevel','betweendate','offering_status','offering_type','offering_period','organization')),
            );
        }else if (!is_siteadmin() && (has_capability('local/organization:manage_trainee', $systemcontext) || has_capability('local/organization:manage_organizationofficial', $systemcontext))) {
            $filters = array(
               'trainingprogram'=>array('local'=>array('tp_sector','tp_jobfamily','tp_competencylevel','betweendate','offering_status','offering_type','trainingfavs')),
            );
        }

        else {


            $filters = array(
               'trainingprogram'=>array('local'=>array('tp_sector','tp_jobfamily','tp_competencylevel','betweendate','trainingprogram_name','offering_status','offering_creator','offering_type','offering_period','organization')),
            );
        }
      
        

    } else {

        $filters = array(
         'trainingprogram'=>array('local'=>array('tp_sector','tp_jobfamily','tp_competencylevel','betweendate')),
       );

    }
   
   if($PAGE->pagelayout == 'sitefrontpage') {

      $disablebuttons = 1;
   } else {
      $disablebuttons = 0;
   }
  $mform = new dynamicfilters_form(null, array('filterlist'=>$filters,'filterparams' => $filterparams,'submitid' =>'viewprograms','ajaxformsubmit'=>true, 'disablebuttons' => $disablebuttons), 'post', '', null, true,$_REQUEST);
  return $mform;

}

function trainingprogram_sort_filters_form($filterparams){

    global $CFG,$PAGE;
    require_once($CFG->dirroot . '/local/organization/dynamicfilters_form.php');
   $systemcontext = context_system::instance();
   $filters = array(
               'trainingprogram'=>array('local'=>array('trainingofferingsort','startdatesort','alphabatesort')),
            );
   if($PAGE->pagelayout == 'sitefrontpage') {

      $disablebuttons = 1;
   } else {
      $disablebuttons = 0;
   }
  $mform = new dynamicfilters_form(null, array('filterlist'=>$filters,'filterparams' => $filterparams,'submitid' =>'viewprograms1','ajaxformsubmit'=>true, 'disablebuttons' => $disablebuttons), 'post', '', null, true,$_REQUEST);
  return $mform;

}

function trainingprogram_tp_offerings_filters_form($filterparams){

    global $CFG,$PAGE;

    $program_array = $PAGE->url->params();
    $program_object = (object) $program_array;
    $selectedroleid = (int)$program_object->selectedroleid;

    require_once($CFG->dirroot . '/local/organization/dynamicfilters_form.php');
     $systemcontext = context_system::instance();
    if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext))
    { 
       
       $filters = array(
         'trainingprogram'=>array('local'=>array('tp_currentofferings')),
       );
    } else {
         if(!empty($selectedroleid) AND !is_null($selectedroleid)) {

            $filters = array(
               'trainingprogram'=>array('local'=>array('tp_currentofferings','tp_organization')),
            );

         } else {


            $filters = array(
               'trainingprogram'=>array('local'=>array('tp_currentofferings','tp_role','tp_organization')),
            );


         }
      

    }      
   
  $mform = new dynamicfilters_form(null, array('filterlist'=>$filters,'filterparams' => $filterparams,'submitid' =>'viewtpprograms','ajaxformsubmit'=>true), 'post', '', null, true,$_REQUEST);
  return $mform;

}

function trainingprogram_coupon_management_filters_form($filterparams){

    global $CFG;

   require_once($CFG->dirroot . '/local/organization/dynamicfilters_form.php');
   $filters = array(
       'trainingprogram'=>array('local'=>array('couponstatus','couponexpired_date')),
   );
  $mform = new dynamicfilters_form(null, array('filterlist'=>$filters,'filterparams' => $filterparams,'submitid' =>'viewcoupons','ajaxformsubmit'=>true), 'post', '', null, true,$_REQUEST);
  return $mform;

}
function trainingprogram_early_registration_management_filters_form($filterparams){

    global $CFG;

   require_once($CFG->dirroot . '/local/organization/dynamicfilters_form.php');
   $filters = array(
       'trainingprogram'=>array('local'=>array('earlyregistrationstatus','earlyregistrationexpired_date')),
   );
  $mform = new dynamicfilters_form(null, array('filterlist'=>$filters,'filterparams' => $filterparams,'submitid' =>'viewearlyregistrations','ajaxformsubmit'=>true), 'post', '', null, true,$_REQUEST);
  return $mform;

}
function tp_sector_filter($mform){
    global $DB;

    $current_lang = current_language();


    if($current_lang == 'ar') {
        $sectorslist=$DB->get_records_sql('SELECT id,titlearabic AS fullname FROM {local_sector} ');

    } else {

        $sectorslist=$DB->get_records_sql('SELECT id,title AS fullname FROM {local_sector}');

    }

    $sectors=[];
    foreach ($sectorslist AS $sector){ 
        $sectors[$sector->id] = $sector->fullname;
    }

    $sectorelement =$mform->addElement('autocomplete','sectors', get_string('sectors', 'local_trainingprogram'),$sectors,
                     ['class' => 'el_sectorlist', 
                     'noselectionstring' =>'',
                     'placeholder' => get_string('sector' , 'local_trainingprogram'),
                     'onchange' => "(function(e){ require(['local_trainingprogram/dynamic_dropdown_ajaxdata'], function(s) {s.sectorschanged();}) }) (event)",

                 ]);
    $sectorelement->setMultiple(true);

}
function trainingfavs_filter($mform){
    global $DB,$USER;
    $mform->addElement('advcheckbox', 'favourites', get_string('favourites', 'local_trainingprogram'),  array(), array(0, 1));

}
function trainingofferingsort_filter($mform){
        $systemcontext = context_system::instance();
        $trainingprgoroffering=array();
        $trainingprgoroffering[] =& $mform->createElement('radio', 'tporoff', '',get_string('trainingprogram', 'local_trainingprogram'), 1);
        if (is_siteadmin() || (has_capability('local/organization:manage_trainingofficial', $systemcontext) ) || (has_capability('local/organization:manage_communication_officer', $systemcontext) )){
           $trainingprgoroffering[] =& $mform->createElement('radio', 'tporoff', '',get_string('offering', 'local_trainingprogram'), 2); 
        }
        $mform->addGroup($trainingprgoroffering, 'tporoff', get_string('sorttporoff', 'local_trainingprogram'), '&nbsp&nbsp', false);
        $mform->setDefault('tporoff', 1);
       

      }  
function startdatesort_filter($mform){
        $startdatesort=array();
        $startdatesort[] =& $mform->createElement('radio', 'startdatesort', '',get_string('asc', 'local_trainingprogram'), 1);
        $startdatesort[] =& $mform->createElement('radio', 'startdatesort', '',get_string('desc', 'local_trainingprogram'), 2);
        $mform->addGroup($startdatesort, 'startdatesort', get_string('startdatead', 'local_trainingprogram'), '&nbsp&nbsp', false);
        $mform->setDefault('startdatesort', 0);
        
 }
 function alphabatesort_filter($mform){
        $alphabatesort=array();
        $alphabatesort[] =& $mform->createElement('radio', 'startdatesort', '',get_string('ascending', 'local_trainingprogram'), 3);
        $alphabatesort[] =& $mform->createElement('radio', 'startdatesort', '',get_string('descending', 'local_trainingprogram'), 4);
        $mform->addGroup($alphabatesort, 'alphabatesort', get_string('alphabatesort', 'local_trainingprogram'), '&nbsp&nbsp', false);
       $mform->setDefault('alphabatesort', 0);
        //$mform->disabledIf('alphabatesort', 'tporoff', 'eq', 2);
 }
function tp_jobfamily_filter($mform){
    global $DB;

    $jfdattributes = array(
        'ajax' => 'local_trainingprogram/sector_datasource',
        'data-type' => 'jobfamily',
        'data-sectorid' =>0,
        'multiple'=>true,
        'noselectionstring' =>'',
        'placeholder' => get_string('jobfamily' , 'local_trainingprogram')
    );
    $mform->addElement('autocomplete', 'targetgroup',get_string('jobfamily', 'local_trainingprogram'), array(), $jfdattributes);

}
function tp_competencylevel_filter($mform){
    $clattributes = array(
        'ajax' => 'local_trainingprogram/dynamic_dropdown_ajaxdata',
        'data-type' => 'program_competencylevel',
        'data-ctype' =>'All',
        'data-programid' =>1,
        'data-offeringid' =>1,
        'noselectionstring' =>'',
        'placeholder' => get_string('competencies' , 'local_trainingprogram')
    );
    $competencyelemet= $mform->addElement('autocomplete', 'program_competencylevel',get_string('competencies', 'local_trainingprogram'),array(),$clattributes);
    $competencyelemet->setMultiple(true);
}

function betweendate_filter($mform){

    $systemcontext = context_system::instance();
    $mform->addElement('date_selector', 'availablefrom', get_string('availablefrom', 'local_trainingprogram'),array('optional'=>true));
    $mform->setType('availablefrom', PARAM_RAW);

    $mform->addElement('date_selector', 'availableto', get_string('availableto', 'local_trainingprogram'),array('optional'=>true));
    $mform->setType('availableto', PARAM_RAW);

}
function trainingprogram_name_filter($mform){
    global $DB;

    $current_lang = current_language();


    if($current_lang == 'ar') {
        $programslist=$DB->get_records_sql('SELECT id,namearabic AS fullname FROM {local_trainingprogram} ORDER BY ID DESC ');

    } else {

        $programslist=$DB->get_records_sql('SELECT id,name AS fullname FROM {local_trainingprogram} ORDER BY ID DESC');

    }

    $programs=[];
    foreach ($programslist AS $program){ 
        $programs[$program->id] = $program->fullname;
    }

    $programelement =$mform->addElement('autocomplete','training_name', get_string('training_name', 'local_trainingprogram'),$programs,
                     ['class' => 'el_programlist', 
                     'noselectionstring' =>'',
                 ]);
    $programelement->setMultiple(true);

}
function offering_period_filter($mform){
    global $DB;

    $current_lang = current_language();

    $type = [];
    $type = ['0' => get_string('public','local_trainingprogram'), '1' => get_string('private','local_trainingprogram'), '2' => get_string('dedicated','local_trainingprogram')];

    $programelement =$mform->addElement('autocomplete','offering_period', get_string('offering_type', 'local_trainingprogram'),$type,['noselectionstring' =>'']);
    $programelement->setMultiple(true);

}
function organization_filter($mform){
    global $DB;

    $current_lang = current_language();

    $displayname = ($current_lang == 'ar') ? 'lo.fullnameinarabic' :'lo.fullname';
    $organisationlist=$DB->get_records_sql("SELECT lo.id,$displayname AS fullname FROM {local_organization} lo JOIN {tp_offerings} tpo ON tpo.organization =lo.id WHERE tpo.organization > 0");
          $organisations=[];
          $organisations[''] = get_string('selectorganisation','local_trainingprogram');
          foreach ($organisationlist AS $organisation){
            if(current_language() == 'ar') {
                 $organisations[$organisation->id]=$organisation->fullname;
            } else {
                 $organisations[$organisation->id]=$organisation->fullname;
            }
         }

    $programelement =$mform->addElement('autocomplete','organization',get_string('specificorg','local_trainingprogram'),$organisations,$organisations);
    $programelement->setMultiple(true);

}
function offering_status_filter($mform){
    global $DB;

    $current_lang = current_language();

    $status = [];
    $status['inprogress'] = get_string('active','local_trainingprogram');
    $status['financially_closed'] = get_string('financially_closed','local_trainingprogram');
    $status['cancelled'] = get_string('cancelled','local_trainingprogram');
    $status['completed'] = get_string('completed','local_trainingprogram');

    $programelement =$mform->addElement('autocomplete','offering_status', get_string('offering_status', 'local_trainingprogram'),$status,['noselectionstring' =>'']);
    $programelement->setMultiple(true);

}
function offering_creator_filter($mform){
    global $DB;

   $current_lang = current_language();


    $userlist=$DB->get_records_sql('SELECT DISTINCT(cosu.id) as userid,cosu.firstname,cosu.lastname,cosu.email  FROM {user} AS cosu JOIN {tp_offerings} AS tpo ON tpo.usercreated = cosu.id  ORDER BY cosu.id DESC');

    $users=[];
    foreach ($userlist AS $user){ 
        $is_reocrd_exists = $DB->record_exists('local_users',['userid' => $user->userid]);
        if($is_reocrd_exists) {
            $localuserrecord = $DB->get_record('local_users',['userid'=>$user->userid]);
            $users[$user->userid] =  (current_language() == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname;
        } else {

            $fullname = $user->firstname.' '.$user->lastname;
            $users[$user->userid]  =  format_text($fullname,FORMAT_HTML);
        }
    }
    $programelement =$mform->addElement('autocomplete','offering_creator', get_string('offering_creator', 'local_trainingprogram'),$users,['noselectionstring' =>'']);
    $programelement->setMultiple(true);

}
function offering_type_filter($mform){
    global $DB;

    $current_lang = current_language();

    $type = [];
    $type['online'] = get_string('scheduleonline','local_trainingprogram');
    $type['offline'] = get_string('scheduleoffline','local_trainingprogram');
    $type['elearning'] = get_string('scheduleelearning','local_trainingprogram');

    $programelement =$mform->addElement('autocomplete','offering_type', get_string('trainingtype', 'local_trainingprogram'),$type,['noselectionstring' =>'']);
    $programelement->setMultiple(true);

}

function tp_currentofferings_filter($mform){
    global $DB,$OUTPUT,$USER,$PAGE;

    $offering_array = $PAGE->url->params();
    $offering_object = (object) $offering_array;
    $programid = (int)$offering_object->programid;
    $tp_offeringslist=$DB->get_records_sql("SELECT id,code FROM {tp_offerings} WHERE trainingid = $programid  ORDER BY id ASC");
    $tp_offerings=[];
    $lang = current_language();
    $k=1;
    foreach ($tp_offeringslist AS $tp_offering){
        $tp_offerings[$tp_offering->id]=$tp_offering->code;
        
    }
    $sectorelement =$mform->addElement('autocomplete','tp_offerings', get_string('current_offerings', 'local_trainingprogram'),$tp_offerings, ['class' => 'el_tp_offeringslist']);
    $sectorelement->setMultiple(true);

}
function tp_role_filter($mform){
    global $DB,$PAGE,$OUTPUT;

    $tp_oroleslists=$DB->get_records_sql("SELECT id,name,shortname FROM {role} WHERE (shortname = 'trainee' OR shortname = 'trainer')");
    $tp_roles=[];
    foreach ($tp_oroleslists AS $tp_oroleslist){
        $tp_roles[$tp_oroleslist->id]=get_string($tp_oroleslist->shortname,'local_trainingprogram');
    }
    $sectorelement =$mform->addElement('autocomplete','tp_role', get_string('roles', 'local_trainingprogram'),$tp_roles, ['class' => 'el_tp_roles']);
    $sectorelement->setMultiple(true);

}

function tp_organization_filter($mform){
    global $DB,$PAGE,$OUTPUT;

    $tp_organizationslists=$DB->get_records_sql("SELECT id,fullname FROM {local_organization} ORDER BY fullname ASC");
    $tp_organization=[];
    //$tp_organization[''] = get_string('selecttp_organization','local_trainingprogram');
    foreach ($tp_organizationslists AS $tp_organizationslist){
        $tp_organization[$tp_organizationslist->id]=$tp_organizationslist->fullname;
    }
    $sectorelement =$mform->addElement('autocomplete','tp_organization', get_string('organization', 'local_trainingprogram'),$tp_organization, ['class' => 'el_tp_organization']);
    $sectorelement->setMultiple(true);

}
/*
* Displays a node in left side menu
* @return  [type] string  link for the leftmenu
*/
function local_trainingprogram_leftmenunode() {
    global $USER, $DB, $CFG;
    $systemcontext = context_system::instance();
    $reports = '';

    $is_user_enrolled_to_offering  = (new local_trainingprogram\local\trainingprogram)->is_current_user_enrolled_to_offering();
    $referralcode = html_writer::start_tag('li', array('id'=> 'id_leftmenu_referralcode', 'class'=>'pull-left user_nav_div referralcode'));
    $referral_url = new moodle_url('/local/trainingprogram/index.php');

    if(is_siteadmin()
        || (has_capability('local/organization:manage_trainingofficial',$systemcontext)) 
        || has_capability('local/organization:manage_organizationofficial',$systemcontext) 
        || has_capability('local/organization:manage_trainee',$systemcontext)  
        || has_capability('local/organization:manage_trainer',$systemcontext) 
        || has_capability('local/organization:manage_communication_officer',$systemcontext) 
        || has_capability('local/organization:manage_financial_manager',$systemcontext) 
        || has_capability('local/organization:training_supervisor',$systemcontext) 
        || has_capability('local/organization:manage_eventmanager',$systemcontext)){

        $referral_label = (is_siteadmin() || has_capability('local/organization:manage_trainingofficial',$systemcontext) 
        || has_capability('local/organization:manage_organizationofficial',$systemcontext)  || has_capability('local/organization:manage_communication_officer',$systemcontext) || has_capability('local/organization:manage_financial_manager',$systemcontext) ) ? get_string('trainings','local_trainingprogram') : get_string('my_trainingprograms','local_trainingprogram');

        $referral = html_writer::link($referral_url, '<span class="trainings_icon side_menu_img_icon"></span><span class="user_navigation_link_text">'.$referral_label.'</span>',
        array('class'=>'user_navigation_link'));
        $referralcode .= $referral;

        $referralcode .= html_writer::end_tag('li');

        $financialpaymentreferralcode = '';
        $settingsreferalcode = '';
        $cancellationreferalcode = '';
        $create_update_referalcode = '';
        if(is_siteadmin()) {

            $financialpaymentreferralcode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_financialpayments', 'class'=>'pull-left user_nav_div financialpayments'));
            $financialpayments_url = new moodle_url('/admin/tool/product/orderapproval.php');
            $financialpayments_label = get_string('orderapproval','tool_product');

            $financialpayments = html_writer::link($financialpayments_url, '<span class="orderapprovals_icon side_menu_img_icon"></span><span class="user_navigation_link_text">'.$financialpayments_label.'</span>',
                array('class'=>'user_navigation_link'));
            $financialpaymentreferralcode .= $financialpayments;
            $financialpaymentreferralcode .= html_writer::end_tag('li');


            $financialpaymentreferralcode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_financialpayments', 'class'=>'pull-left user_nav_div financialpayments'));
            $financialpayments_url = new moodle_url('/admin/tool/product/financialpayments.php');
            $financialpayments_label = get_string('financialpayments','tool_product');

            $financialpayments = html_writer::link($financialpayments_url, '<span class="financialpayments_icon side_menu_img_icon"></span><span class="user_navigation_link_text">'.$financialpayments_label.'</span>',
                array('class'=>'user_navigation_link'));
            $financialpaymentreferralcode .= $financialpayments;
            $financialpaymentreferralcode .= html_writer::end_tag('li');

            $settingsreferalcode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_financialpayments', 'class'=>'pull-left user_nav_div financialpayments'));
            $settings_url = new moodle_url('/admin/settings.php', ['section' => 'fasettings']);
            $settings_label = get_string('settings','local_trainingprogram');

            $settings = html_writer::link($settings_url, '<span class="systemsettings_icon side_menu_img_icon"></span><span class="user_navigation_link_text">'.$settings_label.'</span>',
                array('class'=>'user_navigation_link'));
            $settingsreferalcode .= $settings;
            $settingsreferalcode .= html_writer::end_tag('li');
        }elseif (!is_siteadmin() && has_capability('local/trainingprogram:manage_examownedbysettings', $systemcontext)) {
            $settingsreferalcode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_financialpayments', 'class'=>'pull-left user_nav_div financialpayments'));
            $settings_url = new moodle_url('/local/exams/exam_ownedby_settings.php');
            $settings_label = get_string('exam_ownedby_settings','local_sector');

            $settings = html_writer::link($settings_url, '<span class="systemsettings_icon side_menu_img_icon"></span><span class="user_navigation_link_text">'.$settings_label.'</span>',
                array('class'=>'user_navigation_link'));
            $settingsreferalcode .= $settings;
            $settingsreferalcode .= html_writer::end_tag('li');
        }
         else if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext) ) {
            $financialpaymentreferralcode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_financialpayments', 'class'=>'pull-left user_nav_div financialpayments'));
            $financialpayments_url = new moodle_url('/admin/tool/product/payments.php');
            $financialpayments_label = get_string('payments','tool_product');

            $financialpayments = html_writer::link($financialpayments_url, '<span class="financialpayments_icon side_menu_img_icon"></span><span class="user_navigation_link_text">'.$financialpayments_label.'</span>',
                array('class'=>'user_navigation_link'));
            $financialpaymentreferralcode .= $financialpayments;
            $financialpaymentreferralcode .= html_writer::end_tag('li');
        }  else if(!is_siteadmin() && has_capability('local/organization:manage_trainee',$systemcontext)) {
            $financialpaymentreferralcode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_financialpayments', 'class'=>'pull-left user_nav_div financialpayments'));
            $financialpayments_url = new moodle_url('/admin/tool/product/mypayments.php?mode=paid');
            $financialpayments_label = get_string('mypayments','tool_product');

            $financialpayments = html_writer::link($financialpayments_url, '<span class="financialpayments_icon side_menu_img_icon"></span><span class="user_navigation_link_text">'.$financialpayments_label.'</span>',
                array('class'=>'user_navigation_link'));
            $financialpaymentreferralcode .= $financialpayments;
            $financialpaymentreferralcode .= html_writer::end_tag('li');
        } elseif (!is_siteadmin() && (has_capability('local/organization:manage_communication_officer',$systemcontext))) {

            $financialpaymentreferralcode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_financialpayments', 'class'=>'pull-left user_nav_div financialpayments'));
            $financialpayments_url = new moodle_url('/admin/tool/product/financialpayments.php');
            $financialpayments_label = get_string('comfinancialpayments','tool_product');

            $financialpayments = html_writer::link($financialpayments_url, '<span class="financialpayments_icon side_menu_img_icon"></span><span class="user_navigation_link_text">'.$financialpayments_label.'</span>',
                array('class'=>'user_navigation_link'));
            $financialpaymentreferralcode .= $financialpayments;
            $financialpaymentreferralcode .= html_writer::end_tag('li');


        } elseif (!is_siteadmin() && has_capability('local/organization:manage_financial_manager',$systemcontext)) {
            
            $financialpaymentreferralcode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_financialpayments', 'class'=>'pull-left user_nav_div financialpayments'));
            $financialpayments_url = new moodle_url('/admin/tool/product/financialpayments.php');
            $financialpayments_label = get_string('financialpayments','tool_product');
            $financialpayments = html_writer::link($financialpayments_url, '<span class="financialpayments_icon side_menu_img_icon"></span><span class="user_navigation_link_text">'.$financialpayments_label.'</span>',
            array('class'=>'user_navigation_link'));
            $financialpaymentreferralcode .= $financialpayments;
            $financialpaymentreferralcode .= html_writer::end_tag('li');

            $cancellationreferalcode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_entitycancellations', 'class'=>'pull-left user_nav_div financialpayments'));
            $entitytype = 'offering' ;
            $lablename = get_string('offeringcancellationrequests','local_trainingprogram');
            $cancellation_url = new moodle_url('/local/trainingprogram/entitycancellationrequests.php?entitytype='.$entitytype);
            $cancellation_label =$lablename;
            $cancellation = html_writer::link($cancellation_url, '<span class="trainerrequest_icon side_menu_img_icon"></span><span class="user_navigation_link_text">'.$cancellation_label.'</span>',
            array('class'=>'user_navigation_link'));
            $cancellationreferalcode .= $cancellation;
            $cancellationreferalcode .= html_writer::end_tag('li');

        }elseif (!is_siteadmin() && ( has_capability('local/organization:training_supervisor',$systemcontext) || has_capability('local/organization:manage_eventmanager',$systemcontext))) {
            
            $cancellationreferalcode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_entitycancellations', 'class'=>'pull-left user_nav_div financialpayments'));
            $entitytype = (!is_siteadmin() && has_capability('local/organization:manage_exammanager',$systemcontext)) ? 'profile' : ((!is_siteadmin() && has_capability('local/organization:training_supervisor',$systemcontext)) ?  'offering' : 'event');
            $lablename = (!is_siteadmin() && has_capability('local/organization:manage_exammanager',$systemcontext)) ?  get_string('profilecancellationrequests','local_trainingprogram') : ((!is_siteadmin() && has_capability('local/organization:training_supervisor',$systemcontext)) ? get_string('offeringcancellationrequests','local_trainingprogram') : get_string('eventcancellationrequests','local_trainingprogram'));
            $cancellation_url = new moodle_url('/local/trainingprogram/entitycancellationrequests.php?entitytype='.$entitytype);
            $cancellation_label =$lablename;
            $cancellation = html_writer::link($cancellation_url, '<span class="trainerrequest_icon side_menu_img_icon"></span><span class="user_navigation_link_text">'.$cancellation_label.'</span>',
            array('class'=>'user_navigation_link'));
            $cancellationreferalcode .= $cancellation;
            $cancellationreferalcode .= html_writer::end_tag('li');

            if (!is_siteadmin() &&  has_capability('local/organization:training_supervisor',$systemcontext)) {
                $create_update_referalcode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_entitycancellations', 'class'=>'pull-left user_nav_div financialpayments'));
                $lablename =  get_string('offering_program_requests','local_trainingprogram');
                $create_update_url = new moodle_url('/local/trainingprogram/offering_program_requests.php');
                $create_update_label =$lablename;
                $create_update = html_writer::link($create_update_url, '<span class="trainerrequest_icon side_menu_img_icon"></span><span class="user_navigation_link_text">'.$create_update_label.'</span>',
                array('class'=>'user_navigation_link'));
                $create_update_referalcode .= $create_update;
                $create_update_referalcode .= html_writer::end_tag('li');
    
            }
           
        } 
        if(is_siteadmin() || has_capability('local/organization:manage_trainingofficial',$systemcontext) ||  has_capability('local/organization:manage_communication_officer',$systemcontext) || has_capability('local/organization:manage_organizationofficial',$systemcontext) || has_capability('local/organization:manage_trainee',$systemcontext) || has_capability('local/organization:manage_trainer',$systemcontext)  || has_capability('local/organization:manage_financial_manager',$systemcontext) ){

            $code = $referralcode;
        }
       return array('1' => $code, '5' => $financialpaymentreferralcode,'6' => $settingsreferalcode,'7' => $cancellationreferalcode,'8' => $create_update_referalcode);
    }
    
}

function trainingprogramlogo_url($itemid = 0) {
    global $DB;
    $context = context_system::instance();
    if ($itemid > 0) {
        $sql = "SELECT * FROM {files} WHERE itemid = :logo AND filearea='trainingprogramlogo' AND filename != '.' ORDER BY id DESC";
        $trainingprogramlogorecord = $DB->get_record_sql($sql,array('logo' => $itemid),1);
    }
    if (!empty($trainingprogramlogorecord)) {
        $logourl = moodle_url::make_pluginfile_url($trainingprogramlogorecord->contextid, $trainingprogramlogorecord->component,
       $trainingprogramlogorecord->filearea, $trainingprogramlogorecord->itemid, $trainingprogramlogorecord->filepath,
       $trainingprogramlogorecord->filename);
        $logourl = $logourl->out();
    }
    return $logourl;
}
/*
* Displays financialpayments
* @return  query
*/
function local_trainingprogram_product_orders($stable,$filterdata){
    global $DB,$USER;
    $params      = array();
    $currentlang = current_language();

    if($stable->tablename == 'tool_org_order_payments'){
        $offringlabel = get_string('offeringid','local_trainingprogram');
        if( $currentlang == 'ar'){
            $tpname = 'CONCAT(tp.namearabic," </br> ('.$offringlabel.' ",tpofr.code,")") as trainingname';
        }else{
            $tpname = 'CONCAT(tp.name," </br> ('.$offringlabel.' ",tpofr.code,")") as trainingname';
        }
        $tpname .= ' ,tppmnt.payableamount';
    }else{
        if( $currentlang == 'ar'){
            $tpname = 'tp.namearabic as trainingname';
        }else{
            $tpname = 'tp.name as trainingname';
        }
    }

    $fromsql = "SELECT tppmnt.id,$tpname,tpofr.organization,tppmnt.tablename,tppmnt.fieldname,tppmnt.fieldid,tp.id as trainingid, (tpofr.startdate + tpofr.time) as availablefrom,tpofr.enddate as availableto,tppmnt.purchasedseats,tppmnt.orguserid,tp.name as englishname,tp.namearabic as arabicname,tpofr.code as ofrcode,tp.code as tpcode ";

    if($stable->tablename == 'tool_order_approval_seats'){
        $fromsql .= ", tppmnt.paymentid ";
    }

    $sql = " FROM {" .$stable->tablename. "} AS tppmnt
             JOIN {tp_offerings} AS tpofr ON tppmnt.fieldid=tpofr.id 
             JOIN {local_trainingprogram} AS tp ON tpofr.trainingid=tp.id ";
   
    if (isset($stable->orguserid) && $stable->orguserid > 0) {
        $organization = $DB->get_field('local_users','organization', array('userid' => $stable->orguserid));
        $get_all_orgofficials = (new exams())->get_all_orgofficials($USER->id);
        $orgusers = implode(',',$get_all_orgofficials);
        
        $sql .=  " JOIN {local_users} lu ON lu.userid = tppmnt.orguserid ";
        $sql.= " AND ((tppmnt.orguserid IN ($orgusers) AND tppmnt.organization = $organization) OR (tppmnt.orguserid = $USER->id ))  OR tppmnt.organization = $organization AND lu.deleted = 0 AND lu.approvedstatus = 2";
    }
    $sql .= " WHERE tppmnt.tablename='tp_offerings' AND tppmnt.fieldname='id' ";
    return array('sql'=>$fromsql.$stable->selectparams.$sql, 'params' => $params);
}
/*
* Displays financialpayments
* @return  query
*/
function local_trainingprogram_product_userorders($stable,$filterdata){

    global $DB;

    $params          = array();

    $currentlang= current_language();



    $offringlabel=get_string('offeringid','local_trainingprogram');

    if( $currentlang == 'ar'){

        $tpname='CONCAT(tp.namearabic," </br> ('.$offringlabel.' ",tpofr.code,")") as trainingname';

    }else{

        $tpname='CONCAT(tp.name," </br> ('.$offringlabel.' ",tpofr.code,")") as trainingname';
    }

    $fromsql = "SELECT tppmnt.id,$tpname,tpofr.organization,tppmnt.tablename,tppmnt.fieldname,tppmnt.fieldid,tp.id as trainingid, (tpofr.startdate + tpofr.time) as availablefrom,tpofr.enddate as availableto,tppmnt.purchasedseats,tppmnt.userid,tp.name as englishname,tp.namearabic as arabicname,tpofr.code as ofrcode,tp.code as tpcode, tp.sellingprice";


    $sql = " FROM {" .$stable->tablename. "} AS tppmnt
             JOIN {tp_offerings} AS tpofr ON tppmnt.fieldid=tpofr.id 
             JOIN {local_trainingprogram} AS tp ON tpofr.trainingid=tp.id
             WHERE tppmnt.tablename='tp_offerings' AND tppmnt.fieldname='id' ";
    


    if (isset($stable->userid) && $stable->userid > 0) {
        $sql .= " AND tppmnt.userid = $stable->userid ";
 
    }

    return array('sql'=>$fromsql.$stable->selectparams. $sql,'params'=>$params);
}
function tprogramuseremail_filter($mform){
    global $DB,$USER;
    $systemcontext = context_system::instance();
    $userattributes = array(
        'ajax' => 'local_organization/organization_datasource',
        'data-type' => 'usersemail',
        'id' => 'usersemail',
        'data-org' => 'enrollfilter',
        'multiple'=>true,
    );
    $data = data_submitted();
    $email= implode(',',$data->email);
    if($email) {
    $selectedmailrecords = $DB->get_records_sql_menu('SELECT id,email FROM {local_users} WHERE id IN('.$email.')');
    }
    $mform->addElement('autocomplete', 'email', get_string('email', 'local_userapproval'),$selectedmailrecords, $userattributes);
}
function tprogramorganizationusers_filter($mform){
    global $DB,$USER,$PAGE;

    $offering_array=$PAGE->url->params();
    $offering_object = (object) $offering_array;
    $offeringid = (int)$offering_object->offeringid;
    $programid = (int)$offering_object->programid;
    $offeringtype = $DB->get_field('tp_offerings','type',array('id'=>$offeringid));
    $offeringorganization = $DB->get_field('tp_offerings','organization',array('id'=>$offeringid));
    $systemcontext = context_system::instance();
    $userparam = array();
    $organizations = array();
    $params = array();
    $data = data_submitted();
    $sql = " SELECT org.id, org.fullname FROM {local_organization} org ";
    if(is_siteadmin() || has_capability('local/organization:manage_trainingofficial',$systemcontext)) {

        if($offeringtype == 1 && $offeringorganization > 0) {
            $where = " WHERE id = $offeringorganization ";
        } else {
            $where = " WHERE 1=1 ORDER BY org.fullname ASC ";
        }
        
    } elseif(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
        $organization = $DB->get_field('local_users','organization',array('userid'=>$USER->id));
       $where = " WHERE id = $organization ";
    } else {
        $where = " WHERE 1=1 ORDER BY org.fullname ASC ";
    }
    $organizations = $DB->get_records_sql_menu($sql.$where);
    $options = array(                                                                         
        'multiple' => true,                                                  
        'noselectionstring' => get_string('organization', 'local_trainingprogram'),
    );
    $mform->addElement('autocomplete','organizationusers',get_string('organization', 'local_trainingprogram'),$organizations,$options);
    $mform->setType('organizationusers', PARAM_RAW);
}

function couponstatus_filter($mform){
    global $DB,$USER;
    $systemcontext = context_system::instance();
    $statuslist = [];
    $statuslist['1'] = get_string('available','local_trainingprogram');
    $statuslist['2'] = get_string('expired','local_trainingprogram');
    $statuslist['3'] = get_string('applied','local_trainingprogram');

    $options = array(
        'multiple' => true,
        'noselectionstring' => get_string('couponstatus', 'local_trainingprogram'),
    );
    $statuselement = $mform->addElement('autocomplete', 'couponstatus', get_string('couponstatus', 'local_trainingprogram'),$statuslist, ['id' => 'cstatuslist']);
     $statuselement->setMultiple(true);
} 

function couponexpired_date_filter($mform){
    global $DB,$USER;
    $systemcontext = context_system::instance();

    $mform->addElement('date_selector', 'expired_date', get_string('expired_date', 'local_trainingprogram'),array('optional'=>true));
    $mform->setType('availablefrom', PARAM_RAW);
} 

function earlyregistrationstatus_filter($mform){
    global $DB,$USER;
    $statuslist = [];
    $statuslist['1'] = get_string('available','local_trainingprogram');
    $statuslist['2'] = get_string('expired','local_trainingprogram');
    $options = array(
        'multiple' => true,
        'noselectionstring' => get_string('earlyregistrationstatus', 'local_trainingprogram'),
    );
    $statuselement = $mform->addElement('autocomplete', 'earlyregistrationstatus', get_string('registration_status', 'local_trainingprogram'),$statuslist, ['id' => 'elstatuslist']);
    $statuselement->setMultiple(true);
} 

function earlyregistrationexpired_date_filter($mform){
    global $DB,$USER;
    $systemcontext = context_system::instance();

    $mform->addElement('date_selector', 'expired_date', get_string('expired_date', 'local_trainingprogram'),array('optional'=>true));
    $mform->setType('availablefrom', PARAM_RAW);
}

function trainingprogram_refundsettings_filters_form($filterparams){

    global $CFG;
   require_once($CFG->dirroot . '/local/organization/dynamicfilters_form.php');
   $filters = array(
       'trainingprogram'=>array('local'=>array('refund_type','refund_entitytype','refund_dedtype')),
   );
  $mform = new dynamicfilters_form(null, array('filterlist'=>$filters,'filterparams' => $filterparams,'submitid' =>'refundsettings','ajaxformsubmit'=>true), 'post', '', null, true,$_REQUEST);
  return $mform;

} 

function refund_type_filter($mform){
   global $DB,$USER;
    $systemcontext = context_system::instance();
    $typelist = [];
    $typelist['cancel'] = get_string('cancel_offering','local_trainingprogram');
    $typelist['replace'] = get_string('replace','local_trainingprogram');
    $options = array(
        'multiple' => true,
    );
    $typeselement = $mform->addElement('autocomplete', 'type', get_string('refund_type', 'local_trainingprogram'),$typelist, ['id' => 'eltypelist']);
    $typeselement->setMultiple(true);
}

function refund_entitytype_filter($mform){
   global $DB,$USER;
    $systemcontext = context_system::instance();
    $entitytypelist = [];
    $entitytypelist['trainingprogram'] = get_string('trainingprogram','local_trainingprogram');
    $entitytypelist['exam'] = get_string('exam','local_trainingprogram');
    $entitytypelist['event'] = get_string('event','local_trainingprogram');
    $options = array(
        'multiple' => true,
    );
    $entitytypeselement = $mform->addElement('autocomplete', 'entitytype', get_string('refund_entitytype', 'local_trainingprogram'),$entitytypelist, ['id' => 'elentitytype']);
    $entitytypeselement->setMultiple(true);
}

function refund_dedtype_filter($mform){
   global $DB,$USER;
    $systemcontext = context_system::instance();
    $debtypelist = [];
    $debtypelist[-1] = get_string('attendancepercnt','local_trainingprogram');
    $debtypelist[1] = get_string('amount','local_trainingprogram');
    $options = array(
        'multiple' => true,
    );
    $debtypeelement = $mform->addElement('autocomplete', 'dedtype', get_string('refund_dedtype', 'local_trainingprogram'),$debtypelist, ['id' => 'eldebtype']);
    $debtypeelement->setMultiple(true);
}

function trainingprogram_groupdiscounts_filters_form($filterparams){

    global $CFG;

   require_once($CFG->dirroot . '/local/organization/dynamicfilters_form.php');
   $filters = array(
       'trainingprogram'=>array('local'=>array('discountgroupstatus','discountgroupexpired_date')),
   );
  $mform = new dynamicfilters_form(null, array('filterlist'=>$filters,'filterparams' => $filterparams,'submitid' =>'groupsdiscount','ajaxformsubmit'=>true), 'post', '', null, true,$_REQUEST);
  return $mform;

}
function discountgroupstatus_filter($mform){
    global $DB,$USER;
    $statuslist = [];
    $statuslist['1'] = get_string('available','local_trainingprogram');
    $statuslist['2'] = get_string('expired','local_trainingprogram');
    $options = array(
        'multiple' => true,
        'noselectionstring' => get_string('status', 'local_trainingprogram'),
    );
    $statuselement = $mform->addElement('autocomplete', 'discountgroupstatus', get_string('groupdiscount_status', 'local_trainingprogram'),$statuslist, ['id' => 'groupstatuslist']);
    $statuselement->setMultiple(true);
} 

function discountgroupexpired_date_filter($mform){
    global $DB,$USER;
    $systemcontext = context_system::instance();

    $mform->addElement('date_selector', 'expired_date', get_string('expired_date', 'local_trainingprogram'),array('optional'=>true));
    $mform->setType('availablefrom', PARAM_RAW);
}

function training_agreement_url($itemid = 0) {
    global $DB;
    $context = context_system::instance();
    if ($itemid > 0) {
        $sql = "SELECT * FROM {files} WHERE itemid = :logo AND filearea='tagrrement' AND filename != '.' ORDER BY id DESC";
        $trainingprogramlogorecord = $DB->get_record_sql($sql,array('logo' => $itemid),1);
    }
    if (!empty($trainingprogramlogorecord)) {
       $agreementurl = moodle_url::make_pluginfile_url($trainingprogramlogorecord->contextid, $trainingprogramlogorecord->component,
       $trainingprogramlogorecord->filearea, $trainingprogramlogorecord->itemid, $trainingprogramlogorecord->filepath,
       $trainingprogramlogorecord->filename);
       $agreementurl = $agreementurl->out();
    }
    return $agreementurl;
}



