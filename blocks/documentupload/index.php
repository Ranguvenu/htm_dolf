<?php
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;
require_login();
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_capability('block/documentupload:visible',$systemcontext);
$PAGE->set_url('/blocks/documentupload/index.php');
$PAGE->set_title(get_string('documentupload', 'block_documentupload'));
$PAGE->set_heading(get_string('listofdocumentupload', 'block_documentupload'));
$PAGE->navbar->add(get_string('listofdocumentupload', 'block_documentupload'), new moodle_url('/blocks/documentupload/index.php'));
$renderer = $PAGE->get_renderer('block_documentupload');
//echo $renderer->action_btn();
echo $OUTPUT->header();
(new block_documentupload\documentupload)->documentuploadinfo();
echo $OUTPUT->footer();
