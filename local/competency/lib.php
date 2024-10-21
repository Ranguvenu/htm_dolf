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
 * @package    local_competency
 * @copyright  2022 e abyas  <info@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();
use local_competency\competency;

function competency_filters_form($filterparams){

    global $CFG;

    require_once($CFG->dirroot . '/local/organization/dynamicfilters_form.php');

    $filters = array(
        /*'organization'=>array('local'=>array('sector','segment','jobfamily','jobrole')),*/
        'competency'=>array('local'=>array('competency_type'))
        );

    $mform = new dynamicfilters_form(null, array('filterlist'=>$filters,'filterparams' => $filterparams,'submitid' =>'viewcompetency','ajaxformsubmit'=>true), 'post', '', null, true,$_REQUEST);

    return $mform;
}
function competency_level_filter($mform){

    $competencyleveloptions = [
            'ajax' => 'local_competency/form_competency_selector',
            'data-action' => 'competency_levels',
    ];
 
    $mform->addElement('autocomplete', 'level', get_string('competency_level', 'local_competency'),[],$competencyleveloptions);
    $mform->setType('level', PARAM_ALPHANUMEXT);

}
// function competency_type_filter($mform){
    
//     $competencytypeoptions = [
//             'ajax' => 'local_competency/form_competency_selector',
//             'data-action' => 'competency_types',
//     ];

//     $mform->addElement('autocomplete', 'type', get_string('competency_type', 'local_competency'),[],$competencytypeoptions);
//     $mform->setType('type', PARAM_ALPHANUMEXT);
// }

function competency_type_filter($mform){
    global $DB;

    $current_lang = current_language();

    $competencytypes = competency::constcompetencytypes();

    $typelists=$DB->get_records_sql('SELECT DISTINCT type AS id,type AS fullname FROM {local_competencies}');

    $types=[];
    foreach ($typelists AS $typelist){ 
        $types[$typelist->id] = (in_array($typelist->fullname,array_flip($competencytypes))) ?  $competencytypes[$typelist->fullname] :$typelist->fullname ;
    }

    $typeelement =$mform->addElement('autocomplete','type', get_string('competency_type', 'local_trainingprogram'),$types,['noselectionstring' =>'','placeholder' => get_string('competency_type' , 'local_trainingprogram') ]);
    $typeelement->setMultiple(true);

}
function local_competency_leftmenunode(){
    $systemcontext = context_system::instance();
    $lang = current_language();
    $referralcode = '';
    if(is_siteadmin() || has_capability('local/organization:manage_competencies_official', $systemcontext)){

        $referralcode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_referralcode', 'class'=>'pull-left user_nav_div referralcode'));
        $referral_url = new moodle_url('/local/competency/index.php?lang='.$lang);
        $referral_label = get_string('managecompetencies','local_competency');


        $referral = html_writer::link($referral_url, '<span class="competencies_icon side_menu_img_icon"></span><span class="user_navigation_link_text">'.$referral_label.'</span>',
            array('class'=>'user_navigation_link'));
        $referralcode .= $referral;
        $referralcode .= html_writer::end_tag('li');

    }elseif (has_capability('local/organization:manage_trainee',$systemcontext)) {
        

        $referralcode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_referralcode', 'class'=>'pull-left user_nav_div referralcode'));

        $referral_url = new moodle_url('/local/competency/mycompetency.php?lang='.$lang);
        $referral_label = get_string('mycompetencies','local_competency');


        $referral = html_writer::link($referral_url, '<span class="competencies_icon side_menu_img_icon"></span><span class="user_navigation_link_text">'.$referral_label.'</span>',
            array('class'=>'user_navigation_link'));
        $referralcode .= $referral;
        $referralcode .= html_writer::end_tag('li');

    }
   
    return array('2' => $referralcode);
}
