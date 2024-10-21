<?php 

/*function document_url($itemid = 0) {
    $context = context_system::instance();
    $doc = get_file_storage();
    $document = $fs->get_area_files($context->id, 'auth_registration', 'approval_letter', $itemid);
    foreach($document as $docu){
        $url = moodle_url::make_pluginfile_url($docu->get_contextid(), $docu->get_component(), $docu->get_filearea(), 
                                                $docu->get_itemid(), $docu->get_filepath(), $docu->get_filename(), false);
    }
    return $url->out();
}*/
function block_documentupload_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {

    // if ($context->contextlevel != CONTEXT_SYSTEM) {
    //     return false;
    // }
    // Make sure the filearea is one of those used by the plugin.
    if ($filearea !== 'documentupload') {
        return false;
    }
    $itemid = array_shift($args);
    $filename = array_pop($args);
    if (!$args) {

        $filepath = '/';

    } else {

        $filepath = '/'.implode('/', $args).'/';

    }
    $filedata = get_file_storage();
    $file = $filedata->get_file($context->id, 'block_documentupload', $filearea, $itemid, $filepath, $filename);
    if (!$file) {

        return false;
    }
    send_stored_file($file, 86400, 0, $forcedownload, $options);

}

function document_path($itemid = 0) {
    global $DB;
    $context = context_system::instance();
    if ($itemid > 0) {
        $sql = "SELECT * FROM {files} WHERE itemid = :document AND component = 'block_documentupload' AND filearea = 'documentupload' AND filename != '.' ORDER BY id DESC";
        $documentdata = $DB->get_record_sql($sql, array('document' => $itemid), 1);

    }

    if (!empty($documentdata)) {
    $documenturl = moodle_url::make_pluginfile_url($documentdata->contextid, $documentdata->component, $documentdata->filearea, $documentdata->itemid, $documentdata->filepath, $documentdata->filename);

    $documenturl = $documenturl->out();

    } else {

        return false;

    }
   return $documenturl;
}