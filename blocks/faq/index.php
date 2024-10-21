<?php
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;
require_login();
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_capability('block/faq:visible',$systemcontext);
$PAGE->set_url('/blocks/faq/index.php');
$PAGE->set_title(get_string('faq', 'block_faq'));
$PAGE->set_heading(get_string('listoffaq', 'block_faq'));
$PAGE->navbar->add(get_string('listoffaq', 'block_faq'), new moodle_url('/blocks/faq/index.php'));
$renderer = $PAGE->get_renderer('block_faq');
//echo $renderer->action_btn();
echo $OUTPUT->header();
(new block_faq\faq)->faqinfo();
echo $OUTPUT->footer();
