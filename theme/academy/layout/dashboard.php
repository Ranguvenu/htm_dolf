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
 * A two column layout for the academy theme.
 *
 * @package   theme_academy
 * @copyright 2016 Damyon Wiese
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
$systemcontext = context_system::instance();
if( is_siteadmin() || has_capability('block/learnerscript:viewreports', $systemcontext) ){
    redirect($CFG->wwwroot.'/blocks/reportdashboard/dashboard.php?dashboardurl=Facademy');
}
global $DB,$USER;
user_preference_allow_ajax_update('drawer-open-nav', PARAM_ALPHA);
require_once($CFG->libdir . '/behat/lib.php');

$PAGE->requires->js_call_amd('local_hall/hall', 'init');
// $is_there_my_org_requests = (new local_userapproval\action\manageuser)->my_org_requests();
if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $systemcontext)) {
   (new local_userapproval\action\manageuser)->orgrequestsfakeblock();
}
(new local_events\events)->eventsfakeblock();
// $is_user_enrolled_to_tracks = (new local_learningtracks\learningtracks)->is_current_user_enrolled_to_learningtracks();
// if(!is_siteadmin() && has_capability('local/organization:manage_trainee', $systemcontext) && $is_user_enrolled_to_tracks) {
//    (new local_learningtracks\learningtracks)->learningtrackfakeblock(); 
// }
(new local_questionbank\local\createquestion)->questionbankfakeblock();

// $is_user_enrolled_to_offering  = (new local_trainingprogram\local\trainingprogram)->is_current_user_enrolled_to_offering();

if(!is_siteadmin() && (has_capability('local/organization:manage_trainingofficial',$systemcontext)|| has_capability('local/organization:manage_trainee',$systemcontext) || has_capability('local/organization:manage_trainer',$systemcontext))){
     $PAGE->requires->js_call_amd('local_trainingprogram/tpform', 'init');
     
    (new local_trainingprogram\local\trainingprogram)->programsfakeblock();    
} 

if(!is_siteadmin() && (has_capability('local/organization:manage_trainee', $systemcontext) || has_capability('local/organization:manage_examofficial', $systemcontext) || has_capability('local/organization:manage_organizationofficial', $systemcontext))) {
    (new local_exams\local\exams)->examsfakeblock();
}

if(!is_siteadmin() && (has_capability('local/organization:manage_communication_officer', $systemcontext) || has_capability('local/organization:manage_hall_manager', $systemcontext))) {
    (new local_hall\hall)->hallfakeblock();
}

(new local_cpd\local\cpd)->cpdfakeblock();

$bc = new block_contents();
$renderer = $PAGE->get_renderer('block_supported_competencies');
$bc->title = get_string('title', 'block_supported_competencies');
$bc->attributes['class'] = 'supportedcompetenciesfakeblock';
$content = $renderer->mysupported_competencies();
if($content){
  if(!is_siteadmin() && has_capability('local/organization:manage_trainee',$systemcontext)) {
    $bc->content = $renderer->mysupported_competencies();
    $PAGE->blocks->add_fake_block($bc, 'content');
  }
}
// Add block button in editing mode.
$addblockbutton = $OUTPUT->addblockbutton();

if (isloggedin()) {
    $navdraweropen = false; //(get_user_preferences('drawer-open-nav', 'true') == 'true');
} else {
    $navdraweropen = false;
}

$extraclasses = [];
if ($navdraweropen) {
    $extraclasses[] = 'drawer-open-left';
}

$is_loggedin = isloggedin();
$is_loggedin = empty($is_loggedin) ? false : true;

$bodyattributes = $OUTPUT->body_attributes($extraclasses);
//$blockshtml = $OUTPUT->blocks('side-pre');
$maincontentblockshtml = $OUTPUT->blocks('maincontent');
$rightblockshtml = $OUTPUT->blocks('rightregion');
$firstrow_firstblocks = $OUTPUT->blocks('firstrow-first');
$firstrow_secondblocks = $OUTPUT->blocks('firstrow-second');
$firstrow_thirdblocks = $OUTPUT->blocks('firstrow-third');
$firstrow_forthblocks = $OUTPUT->blocks('firstrow-forth');
$secondrow_firstblocks = $OUTPUT->blocks('secondrow-first');
$secondrow_secondblocks = $OUTPUT->blocks('secondrow-second');
$thirdrow_blocks = $OUTPUT->blocks('thirdrow');
$hasblocks = strpos($rightblockshtml, 'data-block=') !== false;
$secondarynavigation = false;
$overflow = '';
// if ($PAGE->has_secondary_navigation()) {
//     $tablistnav = $PAGE->has_tablist_secondary_navigation();
//     $moremenu = new \core\navigation\output\more_menu($PAGE->secondarynav, 'nav-tabs', true, $tablistnav);
//     $secondarynavigation = $moremenu->export_for_template($OUTPUT);
//     $overflowdata = $PAGE->secondarynav->get_overflow_menu_data();
//     if (!is_null($overflowdata)) {
//         $overflow = $overflowdata->export_for_template($OUTPUT);
//     }
// }

$primary = new core\navigation\output\primary($PAGE);
$renderer = $PAGE->get_renderer('core');
$primarymenu = $primary->export_for_template($renderer);
$buildregionmainsettings = !$PAGE->include_region_main_settings_in_header_actions()  && !$PAGE->has_secondary_navigation();
// If the settings menu will be included in the header then don't add it here.
$regionmainsettingsmenu = $buildregionmainsettings ? $OUTPUT->region_main_settings_menu() : false;

$header = $PAGE->activityheader;
$headercontent = $header->export_for_template($renderer);

$templatecontext = [
    'sitename' => format_string($SITE->fullname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    //'sidepreblocks' => $blockshtml,
    'maincontentblocks' => $maincontentblockshtml,
    'rightblocks' => $rightblockshtml,
    'hasblocks' => $hasblocks,
    'bodyattributes' => $bodyattributes,
    'navdraweropen' => $navdraweropen,
    // 'primarymoremenu' => $primarymenu['moremenu'],
    // 'secondarymoremenu' => $secondarynavigation ?: false,
    'mobileprimarynav' => $primarymenu['mobileprimarynav'],
    'usermenu' => $primarymenu['user'],
    'langmenu' => $primarymenu['lang'],
    'regionmainsettingsmenu' => $regionmainsettingsmenu,
    'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu),
    'headercontent' => $headercontent,
    'overflow' => $overflow,
    'addblockbutton' => $addblockbutton,
    'isloggedin' => $is_loggedin,
    'issiteadmin' => is_siteadmin(),
    'firstrow_firstblocks' =>$firstrow_firstblocks,
    'firstrow_secondblocks' =>$firstrow_secondblocks,
    'firstrow_thirdblocks' =>$firstrow_thirdblocks,
    'firstrow_forthblocks' =>$firstrow_forthblocks,
    'secondrow_firstblocks' =>$secondrow_firstblocks,
    'secondrow_secondblocks' =>$secondrow_secondblocks,
    'thirdrow_blocks' =>$thirdrow_blocks,
];

//$nav = $PAGE->flatnav;
//$templatecontext['flatnavigation'] = $nav;
//$templatecontext['firstcollectionlabel'] = $nav->get_collectionlabel();

echo $OUTPUT->render_from_template('theme_academy/newlayoutdesigns/dashboard', $templatecontext);

