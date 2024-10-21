<?php
require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB, $USER;
$systemcontext = context_system::instance();

$PAGE->set_url('/local/trainingprogram/program_completion_script.php');
echo $OUTPUT->header();
$courseids = [];
$postquiz = [];
$prequiz = [];
$sql = " SELECT tpofr.id as offeringid, tpofr.prequiz as preexam,tpofr.postquiz as postexam, tpofr.trainingid
FROM {tp_offerings} as tpofr ORDER BY tpofr.id DESC";
$offerings = $DB->get_records_sql($sql);
foreach($offerings as $offering) {
    if($offering->preexam) {
        $preexam = $DB->record_exists_sql(" SELECT cm.id FROM {course_modules} AS cm 
                    WHERE cm.module= (SELECT id FROM {modules} WHERE name ='quiz') 
                    AND cm.instance = $offering->preexam AND cm.deletioninprogress = 0");
        if(!$preexam) {
            $courseid = $DB->get_field('local_trainingprogram','courseid',['id' => $offering->trainingid]);
            //$DB->execute(" UPDATE {tp_offerings} SET prequiz= NULL WHERE id = $offering->offeringid");
            $courseids[] = $courseid;//$offering->offeringid;
            $prequiz[] = $offering->preexam;
        }
    }
   if($offering->postexam) {
    $postexam = $DB->record_exists_sql(" SELECT cm.id FROM {course_modules} AS cm 
                WHERE cm.module=(SELECT id FROM {modules} WHERE name ='quiz') 
                AND cm.instance = $offering->postexam AND cm.deletioninprogress = 0");
     if(!$postexam) {
        // $DB->execute(" UPDATE {tp_offerings} SET postquiz= NULL WHERE id = $offering->offeringid");
        $courseid = $DB->get_field('local_trainingprogram','courseid',['id' => $offering->trainingid]);
        $courseids[] =  $courseid;//$offering->offeringid;
        $postquiz[] = $offering->postexam;
    }
   }
}
$courseid_array = implode(',',array_unique($courseids));
$prequiz_array =  implode(',',$prequiz);
$postquiz_array =  implode(',',$postquiz);
echo "courseid : ".$courseid_array ."</br>";
echo "prequizid : ".$prequiz_array ."</br>";
echo "postquizid : ".$postquiz_array ."</br>";

/*$sql = " SELECT cmc.* FROM {course_modules_completion} as cmc 
JOIN {course_modules} cm ON cm.id = cmc.coursemoduleid WHERE cm.course IN ($courseid_array)  AND cmc.completionstate > 1 ORDER BY cm.id DESC "; 
$records = $DB->get_records_sql($sql);

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
}*/

echo $OUTPUT->footer();