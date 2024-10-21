<?php
require_once('../../config.php');
require_once($CFG->dirroot . '/blocks/documentupload/lib.php');
global $DB,$PAGE,$OUTPUT;
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$id = optional_param('id', null, PARAM_INT);
 $sql = "SELECT document from {documentupload} WHERE id=:id";
    $params = [
            'id' => $id,
        ];
$getdocumentid = $DB->get_record_sql($sql,$params);
$document = ['document' => document_path($getdocumentid->document)];
//print_r($document);exit;
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('block_documentupload/pdffile',$document);
echo $OUTPUT->footer();