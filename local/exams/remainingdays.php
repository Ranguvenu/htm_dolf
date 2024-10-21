<?php
require_once('../../config.php');

// require_once($CFG->dirroot.'/local/notifications/notification.php');

global $DB, $USER, $CFG;
// require_once($CFG->dirroot.'/local/cpd/notification.php');
$emails = new \local_exams\notification();
$from = optional_param('date', 0, PARAM_ALPHANUMEXT);
$type = optional_param('type', 0, PARAM_INT);
if ($type == 1) {
        $emails->exams_before_7_days_notification($from);
} else if ($type == 2) {
        $emails->exams_before_48_hours_notification($from);
} else if ($type == 3) {
        $emails->exams_before_24_hours_notification($from);
} else if ($type == 4) {
        $emails->exams_after_session_notification($from);
} else {
        $emails->exams_send_conclusion_notification($from);
}
