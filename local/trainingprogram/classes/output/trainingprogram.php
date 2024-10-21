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
namespace local_trainingprogram\output;

/**
 * Defines the version of Training program
 *
 * @package    local_trainingprogram
 * @copyright  2022 Naveen kumar <naveen@eabyas.com>
 */
defined('MOODLE_INTERNAL') || die;
use renderable;
use templatable;
use local_trainingprogram\output\renderer AS render;
use context_system;
/**
 * Training program renderer
 */
class trainingprogram implements renderable
{

    public function __construct(){
        global $PAGE;
         
          $PAGE->requires->js_call_amd('local_trainingprogram/schedule', 'init', array());
  
        
    }
    

    public function export_for_template(render $output) {
           global $DB, $PAGE, $OUTPUT, $CFG, $USER;
        $systemcontext = context_system::instance();
        $output->get_catalog_trainingprograms();        
        $data  = $output->get_catalog_trainingprograms(true);
        $fform = trainingprogram_filters_form($data);
        $sfform = trainingprogram_sort_filters_form($data);
        $data['submitid'] = 'form#filteringform';
        $data['widthclass'] = 'col-md-4';
        $data['placeholder'] = get_string('sreach_program','local_trainingprogram');
        $canaction=(is_siteadmin() || has_capability('local/organization:manage_trainingofficial',$systemcontext)) ? true : false;
        $data['organizationofficial'] = (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) ? true : false;
        $data['mytrainings'] =get_string('mytrainings','local_trainingprogram');
        $data['template'] = "local_trainingprogram/training_programs";
        $data['action'] = 'local_trainingprogram_viewprograms';
        $data['tabtype'] = 'local_trainingprogram';
        if ($data['tabtype'] == 'local_trainingprogram'){
            $data['tabtitle'] =get_string('tabtitle','local_trainingprogram');
        } else {
            $data['tabtitle'] = get_string('inporgress','local_trainingprogram');
        }
        $data['container'] = 'program_wrapper';
        $uploadprogramview =  ($data['tabtype'] == 'local_trainingprogram') ? true : false;
        $uploadofferingview =  ($data['tabtype'] == 'local_trainingprogram') ? true : false;

        $trainingtopicsview =  ($data['tabtype'] == 'local_trainingprogram' && (is_siteadmin() || has_capability('local/organization:manage_trainingofficial',$systemcontext))) ? true : false;

        $refundsettingsview =  ($data['tabtype'] == 'local_trainingprogram' && (is_siteadmin() || has_capability('local/organization:manage_trainingofficial',$systemcontext))) ? true : false;
        $uploadprogramurl = $CFG->wwwroot.'/local/trainingprogram/uploadprogram.php';
        $uploadofferingurl = $CFG->wwwroot.'/local/trainingprogram/uploadofferings.php';
        $topicsurl = $CFG->wwwroot.'/local/trainingprogram/trainingtopics.php';
        $redundsettingsurl = $CFG->wwwroot.'/local/trainingprogram/viewrefundsettings.php';
        $actions = ['canaction' => $canaction, 'buttonaction' => 'createprogram', 'label' => get_string('createtp','local_trainingprogram'),'uploadprogramurl'=> $uploadprogramurl, 'uploadprogramview' => $uploadprogramview, 'uploadofferingview' => $uploadofferingview ,'uploadofferingurl'=> $uploadofferingurl,'trainingtopicsview'=>$trainingtopicsview,'topicsurl'=>$topicsurl,'refundsettingsview'=>$refundsettingsview,'redundsettingsurl'=>$redundsettingsurl];
        $data['actionview'] = ($PAGE->pagelayout == 'base') ? true : false ;
        if (!is_siteadmin() && has_capability('local/organization:manage_trainee',$systemcontext)) {

            $data['completedtabname'] = get_string('completedtab','local_trainingprogram');
            $data['trainee'] = true;

        } else {

            $data['completedtabname'] = get_string('expiredtab','local_trainingprogram');

        }

        $data['recommendedentities'] = get_string('recommended_programs', 'local_userapproval');
        $data['recommendedtemplate'] = 'local_userapproval/recommendedprograms';
        $data['recommendedaction'] = 'local_userapproval_recommendedprograms';
        $enrollmentreportID = $DB->get_field('block_learnerscript', 'id', array('type' => 'programenrol'), IGNORE_MULTIPLE);
        $enrollmentreporturl = $enrollmentreportID?$CFG->wwwroot.'/blocks/learnerscript/viewreport.php?id='.$enrollmentreportID:'#';
        $data['enrollmentreporturl'] = $enrollmentreporturl;
        $data['buttonString'] = get_string('programenrol', 'block_learnerscript');
        if($PAGE->pagelayout == 'base'){
            $data['sortfilters'] = $sfform->render(); 
            $data['filters'] = $fform->render(); 
            
        }
         
                                                                
        return $data + $actions;  
    }
}
