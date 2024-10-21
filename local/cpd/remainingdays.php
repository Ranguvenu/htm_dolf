<?php
require_once('../../config.php');

// require_once($CFG->dirroot.'/local/notifications/notification.php');

global $DB, $USER, $CFG;
// require_once($CFG->dirroot.'/local/cpd/notification.php');
$emails = new \local_cpd\notification();
$from = optional_param('date', 0, PARAM_ALPHANUMEXT);
$type = optional_param('type', 0, PARAM_INT);
// echo $type."test";exit;
if ($type == 1) {
        $emails->cpd_expiration_lt_180days_and_gt_90_days($from);
} else {
        $emails->cpd_expiration_lt_90_days($from);
}
