<?php
/**
 * This script is run on user returning from Telr.
 * It checks the state of the transaction and enrols the user or emails the admin.
 *
 * @package    tool_product_telr
 * @copyright  2020 Andrew J Said
 * @author     Andrew J Said - based on code by Eugene Venter and others
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Disable moodle specific debug messages and any errors in output,
// comment out when debugging or better look into error log!

define('NO_DEBUG_DISPLAY', true);

// @codingStandardsIgnoreLine This script does not require login.
require("../../../config.php");
require_once("lib.php");
require_once($CFG->libdir.'/enrollib.php');
require_once($CFG->libdir . '/filelib.php');
//require_login();

$context = context_system::instance();
$id = required_param('id', PARAM_RAW);

// if(strpos($id, '?sadad_invoice')){
//     $idt = str_replace('?sadad_invoice', '&sadad_invoice', $id); 
//     redirect($CFG->wwwroot.'/admin/tool/product/confirm.php?id='.$idt.'');
// }
$invoice = optional_param('sadad_invoice', 0,  PARAM_INT);
$PAGE->set_context($context);

$pd = $DB->get_record("tool_product_telr_pending", array('id'=>$id), "*", MUST_EXIST);

if($pd->status != 1) {
    redirect(new moodle_url('/my/index.php'));
}

$telrid = (new tool_product\orders)->post_process($pd, $invoice);

if(core_useragent::is_moodle_app()){

    $forcedurlscheme = get_config('tool_mobile', 'forcedurlscheme');
    if (!empty($forcedurlscheme)) {
        $urlscheme = $forcedurlscheme;
    }
    $location = "$urlscheme://$CFG->wwwroot?redirect=$CFG->wwwroot/$CFG->admin/tool/product/confirm.php?id=$id";
    // For iOS 10 onwards, we have to simulate a user click.
    // If we come from the confirmation page, we should display a nicer page.
    $isios = core_useragent::is_ios();
    if ($confirmed or $isios) {
        $PAGE->set_context(context_system::instance());
        $PAGE->set_heading($COURSE->fullname);
        $params = array('service' => $serviceshortname, 'passport' => $passport, 'urlscheme' => $urlscheme, 'confirmed' => $confirmed);
        $PAGE->set_url("/$CFG->admin/tool/product/confirm.php", $params);

        echo $OUTPUT->header();
        if ($confirmed) {
            $confirmedstr = get_string('confirmed');
            $PAGE->navbar->add($confirmedstr);
            $PAGE->set_title($confirmedstr);
            echo $OUTPUT->notification($confirmedstr, \core\output\notification::NOTIFY_SUCCESS);
            echo $OUTPUT->box_start('generalbox centerpara boxwidthnormal boxaligncenter');
            echo $OUTPUT->single_button(new moodle_url('/course/'), get_string('courses'));
            echo $OUTPUT->box_end();
        }

        $notice = get_string('clickheretolaunchtheapp', 'tool_mobile');
        echo html_writer::link($location, $notice, array('id' => 'launchapp'));
        echo html_writer::script(
            "window.onload = function() {
                document.getElementById('launchapp').click();
            };"
        );
        echo $OUTPUT->footer();
    } else {
        // For Android a http redirect will do fine.
        header('Location: ' . $location);
        die;
    }

} else {
    $record = $DB->get_record('tool_product_telr',['id'=>$telrid]);
    $product_data = unserialize(base64_decode($record->productdata));
    if($product_data['entitytype'] == 'exam') {
        if((!is_siteadmin() && has_capability('local/organization:manage_trainee',$context))) {
            redirect(new moodle_url('/admin/tool/product/ordersummary.php', array('id'=> $pd->id, 'telrid' => $telrid)));
        } else {
            redirect(new moodle_url('/local/exams/examusers.php', array('id'=> $product_data['examid'])));
        }
    }elseif($invoice){
        redirect(new moodle_url('/admin/tool/product/financialpayments.php'));
    }else{
        redirect(new moodle_url('/admin/tool/product/ordersummary.php', array('id'=> $pd->id, 'telrid' => $telrid)));
    }
    
}
