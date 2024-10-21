<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once('../../../config.php');

global $DB, $USER, $CFG;

$sitecontext=context_system::instance();

require_login();

\tool_certificate\certificate::create_events_template();
\tool_certificate\certificate::create_trainingprogram_template();
\tool_certificate\certificate::create_exam_template();
\tool_certificate\certificate::create_learningtracks_template();

?>

