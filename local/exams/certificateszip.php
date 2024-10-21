<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
require_once("../../config.php");
global $USER, $DB, $CFG;
require_login();

$entityid = optional_param('entityid', 0, PARAM_INT);
$type = optional_param('type', 'exam', PARAM_TEXT);
$referenceid = optional_param('referenceid', 0, PARAM_INT);

$fs = get_file_storage();
$zip = new ZipArchive();

// Create a temp file & open it.
$tmpfile = tempnam('.', '');
$zip->open($tmpfile, ZipArchive::CREATE);

$context = context_system::instance();
$PAGE->set_context($context);

switch($type) {
    case 'exam':
        $userids = (new local_exams\local\exams)->get_exam_users($entityid,$referenceid);
        $moduletype = 'exams';
    break;
    case 'program':
        if($referenceid == 0){
            $sql = "SELECT id FROM {tp_offerings} WHERE trainingid = ".$entityid;
            $offeringids = $DB->get_fieldset_sql($sql);
            $referenceid = implode(',', $offeringids);
        } else {
            $referenceid =(int) $referenceid;
        }

        $userids = (new local_exams\local\exams)->get_program_users($entityid,$referenceid);
        $moduletype = 'trainingprogram';
       // $sql = "SELECT id FROM {tp_offerings} WHERE trainingid = ".$entityid;
       // $offeringids = $DB->get_fieldset_sql($sql);
       // $entityid = implode(',', $offeringids);
    break;
    case 'event':
        $userids = (new local_exams\local\exams)->get_event_users($entityid,$referenceid);
        $moduletype = 'events';
    break;    
}

$users = implode(',', $userids);
if ($users) {

        if($moduletype == 'trainingprogram') {
            $entityidmap = " ci.moduleid IN ($referenceid) ";
        } else {
            $entityidmap = " ci.moduleid IN ($entityid) ";
        }
        $sql = "SELECT f.id AS fid, f.userid AS fuserid, f.contextid AS fcontextid, f.filename AS ffilename,
        ctx.id AS ctxid, ctx.contextlevel AS ctxcontextlevel, ctx.instanceid AS ctxinstanceid, ci.id AS ciid,
        ci.userid AS ciuserid, ci.templateid AS cicertificateid, ci.code AS cicode,
        ci.timecreated AS citimecreated
                FROM {files} f
                INNER JOIN {context} ctx ON ctx.id = f.contextid
                LEFT JOIN {tool_certificate_issues} ci ON ci.id = f.itemid
                WHERE f.userid = ci.userid AND f.userid IN ($users) AND f.component = 'tool_certificate' AND
                    f.mimetype = 'application/pdf' AND ci.moduletype='{$moduletype}' AND $entityidmap
                ORDER BY ci.timecreated DESC";
    $tempdirname  = "";
    $certificates = $DB->get_records_sql($sql);

    if (!$certificates) {
        echo $OUTPUT->header();
        echo get_string('nocertificatesavailable', 'local_exams');    
        echo $OUTPUT->footer();
    } else {
        $dirname = "UserID_".$USER->id."_certificates_can_delete";
        make_temp_directory($dirname);
        $tempdirname = "$CFG->tempdir/".$dirname;

        foreach ($certificates as $certdata) {
            $fileinfo = array(
            'component' => 'tool_certificate',     // Usually table name.
            'filearea' => 'issues',                // Usually table name
            'itemid' => $certdata->ciid,          // Usually ID of row in table.
            'contextid' => $certdata->ctxid,     // ID of context.
            'filepath' => '/',                   // Any path beginning and ending in /.
            'filename' => $certdata->ffilename); // Any filename.

            // Get file.
            $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'], $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);

            // Read contents.
            if ($file) {
                $contents = $file->get_content();
                file_put_contents($tempdirname."/".$certdata->ffilename, $contents);
            } else {
                // File doesn't exist - Print error message.
                print_error(get_string('download_certificates_nofilefound', 'block_download_certificates'));
            }
            $zip->addFile($tempdirname."/".$certdata->ffilename, $certdata->ffilename);
        }
        $zip->close();
    }

    foreach ($certificates as $certdata) {
        unlink($tempdirname."/".$certdata->ffilename);
    }
    
    rmdir($tempdirname);
    
    // Filename = FirstnameLastname & current date.
    $zipfilename = $USER->firstname.$USER->lastname.'_'.date("dmy").'_'.'allyourcertificates.zip';
    
    header("Content-Disposition: attachment; filename=\"" . basename($zipfilename) . "\"");
    header('Content-type: application/zip');
    readfile($tmpfile);
    unlink($tmpfile);


} else {
    echo $OUTPUT->header();
    echo get_string('nocertificatesavailable', 'local_exams');    
    echo $OUTPUT->footer();
}
