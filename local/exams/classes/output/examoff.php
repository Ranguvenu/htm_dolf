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
class examoff implements renderable
{

    public function __construct(){
        global $PAGE;   
        $PAGE->requires->js_call_amd('theme_academy/DataFilter', 'init');
    }   

    public function export_for_template(\local_exams\output\renderer $output) {
        $systemcontext = context_system::instance();
        $data = $output->get_catalog_userexams(true);
        // $canaction = has_capability('local/exams:create', $systemcontext) || has_capability('local/organization:manage_examofficial', $systemcontext);
        $data['submitid'] = 'form#filteringform';
        $data['widthclass'] = 'col-md-4';
        $data['placeholder'] = get_string('search_exams','local_exams');
        $output->get_catalog_userexams();
        $data['template'] = "local_exams/userblock";
        $data['container'] = 'exams_wrapper';
        $data['tabtype'] = 'local_exams';
        $lang = current_language();
        if ($lang == 'ar') {
            $data['tabtitle'] = get_string('tabtitle','local_exams');
        } else {
            $data['tabtitle'] = get_string('inporgress','local_trainingprogram');
        }
        $data['mytrainings'] =get_string('myexams','local_exams');
        $data['organizationofficial'] = (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) ? true : false;
        $data['action'] = 'local_exams_view';
        $data['completedtabname'] = (!is_siteadmin() && (has_capability('local/organization:manage_trainee',$systemcontext) || has_capability('local/organization:manage_trainer',$systemcontext))) ? get_string('completedtab','local_exams') : get_string('expiredtab','local_exams');
        return $data;
    }
}
