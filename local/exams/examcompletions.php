<?php
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;

$cmid = optional_param('cmid', 0, PARAM_INT);
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_url('/local/exams/index.php');
$PAGE->set_title(get_string('exams', 'local_exams'));
$PAGE->set_heading(get_string('exams', 'local_exams'));


echo $OUTPUT->header();

$records = $DB->get_records_sql("SELECT cm.* FROM {course_modules_completion} as cm WHERE cm.coursemoduleid = $cmid AND cm.completionstate > 1 ");

foreach($records as $record) {

    $contextmodule = context_module::instance($record->coursemoduleid);

        // Trigger an event for course module completion changed.
        $event = \core\event\course_module_completion_updated::create(array(
            'objectid' => $record->id,
            'context' => $contextmodule,
            'relateduserid' => $record->userid,
            'other' => array(
                'relateduserid' => $record->userid,
                'overrideby' => 0,
                'completionstate' => $record->completionstate
            )
        ));
        $event->add_record_snapshot('course_modules_completion', $record);
        $event->trigger();    
    }
echo $OUTPUT->footer();