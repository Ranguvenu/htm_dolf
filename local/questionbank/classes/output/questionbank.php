<?php
namespace local_questionbank\output;

/**
 * Defines the version of Training program
 *
 * @package    local_trainingprogram
 * @copyright  2022 Naveen kumar <naveen@eabyas.com>
 */
defined('MOODLE_INTERNAL') || die;
use renderable;
use templatable;
use context_system;
use local_questionbank\output\renderer;
/**
 * Training program renderer
 */
class questionbank implements renderable
{

    public function __construct(){
        global $PAGE;
        $PAGE->requires->js_call_amd('local_questionbank/questionBank', 'init');
        // $PAGE->requires->js_call_amd('local_trainingprogram/schedule', 'init', array());
        $PAGE->requires->js_call_amd('theme_academy/Datafilter', 'init');
    }
    

    public function export_for_template(\local_questionbank\output\renderer $output) {
        $systemcontext = context_system::instance();
        $output->get_questionsinfo();
        $data  = $output->get_questionsinfo(true);
        $data['submitid'] = 'form#filteringform';
        $data['widthclass'] = 'col-md-4';
        $data['placeholder'] = get_string('search_questionbank_workshops','local_questionbank');
        $canaction=(is_siteadmin() || has_capability('local/questionbank:assignreviewer', $systemcontext)) ? true : false;
        $data['template'] = "local_questionbank/questionbankinfo";
        $data['container'] = 'questionbank_container';
        $data['action'] = 'local_questionbank_view';
        // $data['completedtabname'] = (!is_siteadmin() && (has_capability('local/organization:manage_trainee',$systemcontext) || has_capability('local/organization:manage_trainer',$systemcontext))) ? get_string('completed','local_trainingprogram') : get_string('expired','local_trainingprogram');
         $data['completedtabname'] = get_string('completed','local_questionbank');
         $data['inprogresstabname'] = true;
         $actions = [ 'buttonaction' => 'createprogram', 'label' => get_string('createtp','local_trainingprogram')];                                                                   
        return $data;
    }
}
