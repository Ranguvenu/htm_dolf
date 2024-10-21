<?php
namespace local_learningtracks\output;

/**
 * Defines the version of Training program
 *
 * @package    local_learningtracks
 * @copyright  2022 Naveen kumar <naveen@eabyas.com>
 */
defined('MOODLE_INTERNAL') || die;
use renderable;
use templatable;
use local_learningtracks_renderer;
use context_system;
/**
 * Training program renderer
 */
class learningtracks implements renderable
{

    public function __construct(){
        global $PAGE;
        $PAGE->requires->js_call_amd('theme_academy/DataFilter', 'init');
        $PAGE->requires->js_call_amd('local_learningtracks/learningtracksform', 'init');
    }
    

    public function export_for_template(local_learningtracks_renderer $output) {
        global $CFG,$PAGE;
        $systemcontext = context_system::instance();
        
        $output->get_content();

        $data  = $output->get_content(true);
        $fform = learningtracks_filters_form($data);
        $data['submitid'] = 'form#filteringform';
        $data['widthclass'] = 'col-md-4';
        $data['placeholder'] = get_string('search_requested','local_learningtracks');
        if(is_siteadmin() || has_capability('local/organization:manage_trainingofficial', $systemcontext)) {
            $canaction = true;
        }
        $data['organizationofficial'] = (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) ? true : false;
        $data['template'] = "local_learningtracks/learningtracks_list";
        $data['action'] = 'local_learningtracks_get_learningtracks';
        $data['container'] = 'manage_learningtracks';
        $data['tabtype'] = 'local_learningtracks';
        if ($data['tabtype'] == 'local_learningtracks'){
            $data['tabtitle'] =get_string('tabtitle','local_learningtracks');
        } else {
            $data['tabtitle'] = get_string('inporgress','local_trainingprogram');
        }
        $actions = ['canaction' => $canaction,  'buttonaction' => 'edittracker', 'label' => get_string('addlearningtrack','local_learningtracks')];
        $data['actionview'] = ($PAGE->pagelayout == 'mydashboard') ? false : true ;
       // $data['filters'] = '';
        $data['completedtabname'] = (!is_siteadmin() && (has_capability('local/organization:manage_trainee',$systemcontext) || has_capability('local/organization:manage_trainer',$systemcontext))) ? get_string('completedtab','local_learningtracks') : get_string('completedtab','local_learningtracks');
        //if($PAGE->pagelayout == 'base'){
            //$data['filters'] = $fform->render(); 
        //}
        return $data + $actions;  
    }
}
