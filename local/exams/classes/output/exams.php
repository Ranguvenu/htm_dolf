<?php
namespace local_exams\output;

/**
 * Defines the version of Training program
 *
 * @package    local_trainingprogram
 * @copyright  2022 Naveen kumar <naveen@eabyas.com>
 */
defined('MOODLE_INTERNAL') || die;
use renderable;
use templatable;
use local_exams\output\local_exams_renderer;
use context_system;
/**
 * Exams renderer
 */
class exams implements renderable
{

    public function __construct(){
        global $PAGE;   
        $PAGE->requires->js_call_amd('theme_academy/DataFilter', 'init');
        $PAGE->requires->js_call_amd('local_exams/grievance', 'init');
        $PAGE->requires->js_call_amd('local_exams/exams', 'init');
    }   

    public function export_for_template(\local_exams\output\renderer $output) {
        global $PAGE,$CFG,$DB;  
        $systemcontext = context_system::instance();
        if(!is_siteadmin() && has_capability('local/organization:manage_trainee', $systemcontext)) {
            $data = $output->get_catalog_userexams(true);
            $fform = exams_filters_form($data);
            $data['widthclass'] = 'col-md-4';
            $data['placeholder'] = get_string('search_exams','local_exams');
            $output->get_catalog_userexams();
            $data['mytrainings'] =get_string('myexams','local_exams');
            $data['trainee'] = true;
            $data['template'] = "local_exams/userblock";
            $data['action'] = 'local_exams_view';
	        $data['tabtype'] = 'local_exams';
            
            $data['recommendedentities'] = get_string('recommended_exams', 'local_userapproval');
            $data['recommendedtemplate'] = "local_userapproval/recommendedentities";
            $data['recommendedaction'] = 'local_userapproval_recommendedexams';

            $lang = current_language();
            if ($lang == 'ar') {
                $data['tabtitle'] = get_string('tabtitle','local_exams');
            } else {
                $data['tabtitle'] = get_string('inporgress','local_trainingprogram');
            }
            $data['container'] = 'exams_wrapper';
            $data['actionview'] = ($PAGE->pagelayout == 'mydashboard') ? false : true ;
            $data['completedtabname'] = (!is_siteadmin() && (has_capability('local/organization:manage_trainee',$systemcontext) || has_capability('local/organization:manage_trainer',$systemcontext))) ? get_string('completedtab','local_exams') : get_string('expiredtab','local_exams');
            $data['filters'] = $fform->render();
            return $data;
        } else {
            $data = $output->get_catalog_publishexams(true);
            $fform = exams_filters_form($data);
            $canaction = has_capability('local/exams:create', $systemcontext) || has_capability('local/organization:manage_examofficial', $systemcontext);
            $data['submitid'] = 'form#filteringform';
            $data['widthclass'] = 'col-md-4';
            $data['placeholder'] = get_string('search_exams','local_exams');
            $output->get_catalog_publishexams();
            $data['organizationofficial'] = (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) ? true : false;
            $data['mytrainings'] =get_string('myexams','local_exams');
            $data['template'] = "local_exams/scheduledexams";
            $data['action'] = 'local_exams_view';
            $data['tabtype'] = 'local_exams';
            $lang = current_language();
            if ($lang == 'ar') {
                $data['tabtitle'] = get_string('tabtitle','local_exams');
            } else {
                $data['tabtitle'] = get_string('inporgress','local_trainingprogram');
            }

            $data['container'] = 'exams_wrapper';
            $data['actionview'] = ($PAGE->pagelayout == 'mydashboard') ? false : true ;
            $uploadexamview =  ($data['tabtype'] == 'local_exams') ? true : false;
            $uploadexammurl = $CFG->wwwroot.'/local/exams/bulkupload.php';
            $actions = ['canaction' => $canaction, 'buttonaction' => 'createexam', 'label' => get_string('addexam','local_exams'),'uploadexammurl'=> $uploadexammurl, 'uploadexamview' => $uploadexamview];
            if(!is_siteadmin() && (has_capability('local/organization:manage_trainee',$systemcontext) || has_capability('local/organization:manage_trainer',$systemcontext))){
                $data['completedtabname'] = get_string('completedtab','local_exams');
            }
            $data['filters'] = $fform->render();
            $enrollmentreportID = $DB->get_field('block_learnerscript', 'id', array('type' => 'examenrol'), IGNORE_MULTIPLE);
            $enrollmentreporturl = $enrollmentreportID?$CFG->wwwroot.'/blocks/learnerscript/viewreport.php?id='.$enrollmentreportID:'#';
            $data['enrollmentreporturl'] = $enrollmentreporturl;
            $data['buttonString'] = get_string('examenrol', 'block_learnerscript');
            return $data + $actions;
        }
    }
}
