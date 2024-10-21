<?php
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;
$PAGE->set_url('/local/organization/partnertypes.php');
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_title(get_string('partnertypes','local_organization'));
$PAGE->set_heading(get_string('partnertypes','local_organization'));
$PAGE->navbar->add(get_string('pluginname','local_organization'), new moodle_url('/local/organization/index.php'));
$PAGE->navbar->add(get_string('partnertypes','local_organization'), new moodle_url('/local/organization/partnertypes.php'));
$renderer = $PAGE->get_renderer('local_organization');
echo $OUTPUT->header();
    (new local_organization\partnertypes)->partnertypesinfo();
echo $OUTPUT->footer();
