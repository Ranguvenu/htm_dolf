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
 * Defines plugin library.
 *
 * @package    local_learningtracks
 * @copyright  2022 e abyas  <info@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

function local_learningtracks_leftmenunode(){
    $systemcontext = context_system::instance();
    $referralcode = '';
     if(is_siteadmin()  || has_capability('local/organization:manage_trainee',$systemcontext) || has_capability('local/organization:manage_organizationofficial',$systemcontext)){
        $referralcode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_learningtracks', 'class'=>'pull-left user_nav_div learningtracks'));
        if(!is_siteadmin() && has_capability('local/organization:manage_trainee',$systemcontext)) {
            $referral_url = new moodle_url('/local/learningtracks/learningpath.php');
            $referral_label = get_string('mylearningpath','local_learningtracks');
        } else {
            $referral_url = new moodle_url('/local/learningtracks/index.php');
            $referral_label = get_string('pluginname','local_learningtracks');
        }
        $referral =  html_writer::link($referral_url, '<span class="side_menu_img_icon learningtracks_icon"></span><span class="user_navigation_link_text">'.$referral_label.'</span>', array('class'=>'user_navigation_link'));
        $referralcode .= $referral;
        $referralcode .= html_writer::end_tag('li');
    }

    if(is_siteadmin()  || has_capability('local/organization:manage_organizationofficial',$systemcontext) || 
      (has_capability('local/organization:manage_trainee', $systemcontext))) {
        return array('11' => $referralcode);
    }
}
    
function local_learningtracks_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    if ($context->contextlevel != CONTEXT_SYSTEM) {
        return false;
    }
    if ($filearea !== 'logos' ) {
        return false;
    }

    $itemid = array_shift($args);
    $filename = array_pop($args); 
    if (!$args) {
        $filepath = '/';
    } else {
        $filepath = '/'.implode('/', $args).'/';
    }
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'local_learningtracks', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false;
    }
    send_stored_file($file, 86400, 0, $forcedownload, $options);
}

function tracklogo_url($itemid = 0) {
    global $DB;
    $context = context_system::instance();
    if ($itemid > 0) {
        $sql = "SELECT * FROM {files} WHERE itemid = :logo AND filearea='logos' AND filename != '.' ORDER BY id DESC";
        $logorecord = $DB->get_record_sql($sql,array('logo' => $itemid),1);
    }
    if (!empty($logorecord)) {
        $logourl = moodle_url::make_pluginfile_url($logorecord->contextid, $logorecord->component,
        $logorecord->filearea, $logorecord->itemid, $logorecord->filepath,
        $logorecord->filename);
        $logourl = $logourl->out();
    }
    return $logourl;
}

function ltorganizationusers_filter($mform,$query='',$searchanywhere=false, $page=0, $perpage=25){
    global $DB;
    $userparam = array();
    $organizations = array();
    $params = array();
    $data = data_submitted();
   // var_dump($data); exit;
    $sql = " SELECT org.id, org.fullname FROM {local_organization} org ";

    $organizations = $DB->get_records_sql_menu($sql);
   // var_dump($sql); exit;
    $options = array(                                                                         
        'multiple' => true,                                                  
        'noselectionstring' => get_string('organization', 'local_trainingprogram'),
    );
    $mform->addElement('autocomplete','organizationusers',get_string('organization', 'local_trainingprogram'),$organizations,$options);
    $mform->setType('organizationusers', PARAM_RAW);
}
function learningtracks_filters_form($filterparams){

    global $CFG,$PAGE;
    require_once($CFG->dirroot . '/local/organization/dynamicfilters_form.php');
   $filters = array(
       'trainingprogram'=>array('local'=>array('tp_sector','tp_jobfamily','tp_competencylevel','betweendate')),
   );
   if($PAGE->pagelayout == 'sitefrontpage') {

      $disablebuttons = 1;
   } else {
      $disablebuttons = 0;
   }
  $mform = new dynamicfilters_form(null, array('filterlist'=>$filters,'filterparams' => $filterparams,'submitid' =>'viewprograms','ajaxformsubmit'=>true, 'disablebuttons' => $disablebuttons), 'post', '', null, true,$_REQUEST);
  return $mform;

}