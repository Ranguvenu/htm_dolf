<?php
require_once(dirname(__FILE__) . '/../../config.php');
global $DB, $CFG, $OUTPUT,$USER, $PAGE;
$PAGE->set_url('/local/trainingprogram/cart.php');
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$programid= optional_param('programid',0,PARAM_INT);
$courseid=$DB->get_field('local_trainingprogram','courseid',array('id'=>$programid));
$PAGE->set_url('/local/trainingprogram/cart.php', array('programid' =>$programid));
$returnurl = new moodle_url('/local/trainingprogram/cart.php', array('programid' =>$programid));
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_trainingprogram/program_cart_view', []);
echo $OUTPUT->footer();