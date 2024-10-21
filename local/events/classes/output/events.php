<?php
namespace local_events\output;

/**
 * Defines the version of Training program
 *
 * @package    local_events
 * @copyright  2022 Naveen kumar <naveen@eabyas.com>
 */
defined('MOODLE_INTERNAL') || die;
use renderable;
use templatable;
use local_events_renderer;
use context_system;
/**
 * Training program renderer
 */
class events implements renderable
{

    public function __construct(){
        global $PAGE;
        // $PAGE->requires->js_call_amd('local_events/tp', 'init');
        // $PAGE->requires->js_call_amd('local_events/schedule', 'init', array());
       // $PAGE->requires->js_call_amd('local_events/eventsform', 'init');
        $PAGE->requires->js_call_amd('local_exams/cancelreschedule', 'init');
        $PAGE->requires->js_call_amd('theme_academy/DataFilter', 'init');
    }
    

    public function export_for_template(local_events_renderer $output) {
        global $CFG,$PAGE,$DB;
        $systemcontext = context_system::instance();
        
        $output->get_catalog_events();        

        $data  = $output->get_catalog_events(true);
        $fform = events_filters_form($data);
        $data['submitid'] = 'form#filteringform';
        $data['widthclass'] = 'col-md-4';
        $data['placeholder'] = get_string('searchevents', 'local_events');
        if(is_siteadmin() || has_capability('local/organization:manage_event_manager', $systemcontext)) {
            $canaction = true;
           $data['link'] = $CFG->wwwroot."/local/events/addevent.php";
        }
        $data['mytrainings'] =get_string('myevents','local_events');
        //$data['organizationofficial'] = false;
        $data['organizationofficial'] = (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) ? true : false;
        $data['template'] = "local_events/eventdetails";
        $data['action'] = 'local_events_view_events';
        $data['container'] = 'content_wrapper';
        $data['tabtype'] = 'local_events';
        $lang = current_language();
        if ($lang == 'ar') {
            $data['tabtitle'] = get_string('tabtitle','local_events');
        } else {
            $data['tabtitle'] = get_string('inporgress','local_trainingprogram');
        }
        $actions = ['canaction' => $canaction,  'label' => get_string('addnew','local_events')];
        $data['actionview'] = ($PAGE->pagelayout == 'mydashboard') ? false : true ;
        $data['filters'] = $fform->render();
        $data['completedtabname'] = (!is_siteadmin() && (has_capability('local/organization:manage_trainee',$systemcontext) || has_capability('local/organization:manage_trainer',$systemcontext))) ? get_string('completedtab','local_events') : get_string('expiredtab','local_events');
        $enrollmentreportID = $DB->get_field('block_learnerscript', 'id', array('type' => 'eventenrol'), IGNORE_MULTIPLE);
        $enrollmentreporturl = $enrollmentreportID?$CFG->wwwroot.'/blocks/learnerscript/viewreport.php?id='.$enrollmentreportID:'#';
        $data['enrollmentreporturl'] = $enrollmentreporturl;
        $data['buttonString'] = get_string('eventenrol', 'block_learnerscript');
        return $data + $actions;  
    }
}
