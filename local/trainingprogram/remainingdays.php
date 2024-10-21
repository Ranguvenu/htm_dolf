<?php 
require_once('../../config.php');

// require_once($CFG->dirroot.'/local/notifications/notification.php');

global $DB, $USER, $CFG;
// require_once($CFG->dirroot.'/local/cpd/notification.php');
        $emails = new \local_trainingprogram\notification();
        $from = optional_param('date', 0, PARAM_ALPHANUMEXT);
$type = optional_param('type', 0, PARAM_INT);
if ($type == 1) {
        $emails->trainingprogram_before_7_days_notification($from);
} else if ($type == 2) {
        $emails->trainingprogram_before_48_hours_notification($from);
} else if ($type == 3) {
        $emails->trainingprogram_before_24_hours_notification($from);
} else if ($type == 4) {
        $emails->trainingprogram_after_session_notification($from);
} else {
        $emails->trainingprogram_send_conclusion_notification($from);
} 
?>