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

/** Configurable Reports
 * A Moodle block for creating customizable reports
 * @package blocks
 * @author: Juan leyva <http://www.twitter.com/jleyvadelgado>
 * @date: 2009
 */
require_once("../../config.php");
require_once("lib.php");
require_once("$CFG->libdir/excellib.class.php");

 global $DB, $CFG;

$url = new moodle_url('/local/userapproval/rawdata.php', array('id' => $id));

$PAGE->set_url($url);

require_login();
$context = context_system::instance();
require_capability('local/userapproval:view', $context);

// Buffering any output. This prevents some output before the excel-header will be send.
ob_start();
ob_end_clean();

$matrix = sectorsinfo();

$downloadfilename = clean_filename('rawdata.xls');
    /// Creating a workbook
$workbook = new MoodleExcelWorkbook($downloadfilename);
    /// Sending HTTP headers
    $workbook->send($downloadfilename);
    /// Adding the worksheet
     $worksheet = array();
    // ------------------ Sector Worksheet ------------------------
    $filename = 'sector.xls';
    $worksheet[0] = $workbook->add_worksheet($filename);
    
    foreach ($matrix as $ri => $col) {
        foreach ($col as $ci => $cv) {
            //Formatting by sowmya
            $format = array('border'=>1);
            if($ri == 1){
                $format['bold'] = 1;
                $format['bg_color'] = '#2c4e86';
                $format['color'] = '#FFFFFF';
            }
            if(is_numeric($cv)){
                $format['align'] = 'center';
                 $worksheet[0]->write_number($ri, $ci, $cv, $format);
            } else {
                 $worksheet[0]->write_string($ri, $ci, $cv, $format);
            }
        }
    }
        // ------------------ Segment Worksheet ------------------------
    //$workbook->close();
    $filename1 = 'segment.xls';

    $worksheet[1] = $workbook->add_worksheet($filename1);
    $matrix = segmentinfo();

    foreach ($matrix as $ri => $col) {
        foreach ($col as $ci => $cv) {
            //Formatting by sowmya
            $format = array('border'=>1);
            if($ri == 1){
                $format['bold'] = 1;
                $format['bg_color'] = '#2c4e86';
                $format['color'] = '#FFFFFF';
            }
            if(is_numeric($cv)){
                $format['align'] = 'center';
                 $worksheet[1]->write_number($ri, $ci, $cv, $format);
            } else {
                $worksheet[1]->write_string($ri, $ci, $cv, $format);
            }
        }
    }
    // ------------------ Jobfamily Worksheet ------------------------
    $filename2 = 'jobfamily.xls';

    $worksheet[2] = $workbook->add_worksheet($filename2);
    $matrix = jobfamily();

    foreach ($matrix as $ri => $col) {
        foreach ($col as $ci => $cv) {
            //Formatting by sowmya
            $format = array('border'=>1);
            if($ri == 1){
                $format['bold'] = 1;
                $format['bg_color'] = '#2c4e86';
                $format['color'] = '#FFFFFF';
            }
            if(is_numeric($cv)){
                $format['align'] = 'center';
                 $worksheet[2]->write_number($ri, $ci, $cv, $format);
            } else {
                $worksheet[2]->write_string($ri, $ci, $cv, $format);
            }
        }
    }
    // ------------------ Jobrole Worksheet ------------------------
    $filename3 = 'jobrole.xls';

    $worksheet[3] = $workbook->add_worksheet($filename3);
    $matrix = jobrole();

    foreach ($matrix as $ri => $col) {
        foreach ($col as $ci => $cv) {
            //Formatting by sowmya
            $format = array('border'=>1);
            if($ri == 1){
                $format['bold'] = 1;
                $format['bg_color'] = '#2c4e86';
                $format['color'] = '#FFFFFF';
            }
            if(is_numeric($cv)){
                $format['align'] = 'center';
                 $worksheet[3]->write_number($ri, $ci, $cv, $format);
            } else {
                $worksheet[3]->write_string($ri, $ci, $cv, $format);
            }
        }
    }

     // ------------------ Organization Worksheet ------------------------
    $filename3 = 'organization.xls';

    $worksheet[4] = $workbook->add_worksheet($filename3);
    $matrix = organizationinfo();

    foreach ($matrix as $ri => $col) {
        foreach ($col as $ci => $cv) {
            //Formatting by sowmya
            $format = array('border'=>1);
            if($ri == 1){
                $format['bold'] = 1;
                $format['bg_color'] = '#2c4e86';
                $format['color'] = '#FFFFFF';
            }
            if(is_numeric($cv)){
                $format['align'] = 'center';
                 $worksheet[4]->write_number($ri, $ci, $cv, $format);
            } else {
                $worksheet[4]->write_string($ri, $ci, $cv, $format);
            }
        }
    }
      // ------------------ Competency Mail Worksheet ------------------------
    $filename5 = 'competencytype.xls';

    $worksheet[5] = $workbook->add_worksheet($filename5);
    $matrix = competencytype();

    foreach ($matrix as $ri => $col) {
        foreach ($col as $ci => $cv) {
            //Formatting by sowmya
            $format = array('border'=>1);
            if($ri == 1){
                $format['bold'] = 1;
                $format['bg_color'] = '#2c4e86';
                $format['color'] = '#FFFFFF';
            }
            if(is_numeric($cv)){
                $format['align'] = 'center';
                 $worksheet[5]->write_number($ri, $ci, $cv, $format);
            } else {
                $worksheet[5]->write_string($ri, $ci, $cv, $format);
            }
        }
    }
      // ------------------ Competency Mail Worksheet ------------------------
    $filename6 = 'competencies.xls';

    $worksheet[6] = $workbook->add_worksheet($filename6);
    $matrix = competencies();

    foreach ($matrix as $ri => $col) {
        foreach ($col as $ci => $cv) {
            //Formatting by sowmya
            $format = array('border'=>1);
            if($ri == 1){
                $format['bold'] = 1;
                $format['bg_color'] = '#2c4e86';
                $format['color'] = '#FFFFFF';
            }
            if(is_numeric($cv)){
                $format['align'] = 'center';
                 $worksheet[6]->write_number($ri, $ci, $cv, $format);
            } else {
                $worksheet[6]->write_string($ri, $ci, $cv, $format);
            }
        }
    }
      // ------------------ Competency Mail Worksheet ------------------------
    $filename7 = 'roles.xls';

    $worksheet[7] = $workbook->add_worksheet($filename7);
    $matrix = roles();

    foreach ($matrix as $ri => $col) {
        foreach ($col as $ci => $cv) {
            //Formatting by sowmya
            $format = array('border'=>1);
            if($ri == 1){
                $format['bold'] = 1;
                $format['bg_color'] = '#2c4e86';
                $format['color'] = '#FFFFFF';
            }
            if(is_numeric($cv)){
                $format['align'] = 'center';
                 $worksheet[7]->write_number($ri, $ci, $cv, $format);
            } else {
                $worksheet[7]->write_string($ri, $ci, $cv, $format);
            }
        }
    }
   
    $workbook->close();


function sectorsinfo(){
    global $DB;
    $sql = "SELECT * FROM {local_sector} WHERE 1=1";
    $sectorinfo = $DB->get_records_sql($sql);      
    $data = array();
    foreach($sectorinfo as $sector){
      $row = array();
      $row[] = $sector->title;
      $row[] = $sector->code;
      $data[] = $row;
    }
    $table = new html_table();
    $table->head = array('Title','Code');
    $table->data= $data;

    $matrix = array();
    $thead =array();
 
    
    if (!empty($table->head)) {
    $countcols = count($table->head);
    $keys = array_keys($table->head);
    $lastkey = end($keys);
    foreach ($table->head as $key => $heading) {
        $matrix[0][0] = $reportname;
        $matrix[1][$key] = str_replace("\n", ' ', htmlspecialchars_decode(strip_tags(nl2br($heading))));
    }
    }

    if (!empty($table->data)) {
        foreach ($table->data as $rkey => $row) {
            foreach ($row as $key => $item) {
                $matrix[$rkey + 2][$key] = str_replace("\n", ' ', htmlspecialchars_decode(strip_tags(nl2br($item))));
            }
        }
    }
    return $matrix;
}
function segmentinfo(){
    global $DB;
    $sql = "SELECT sg.id as sectorid,se.title as sector,se.code as sectorcode, sg.title as segment,sg.code as segmentcode
            FROM {local_sector} as se   
            JOIN {local_segment} as sg   
            ON se.id = sg.sectorid  ";
    $sectorinfo = $DB->get_records_sql($sql);      
    $data = array();
    foreach($sectorinfo as $sector){
      $row = array();
      $row[] = $sector->segment;
      $row[] = $sector->segmentcode;
      $row[] = $sector->sectorcode;
      $data[] = $row;
    }
    $table = new html_table();
    $table->head = array('Segment Title','Segment Code','Sector code');
    $table->data= $data;

    $matrix = array();
    $thead =array();
 
    
    if (!empty($table->head)) {
    $countcols = count($table->head);
    $keys = array_keys($table->head);
    $lastkey = end($keys);
    foreach ($table->head as $key => $heading) {
        $matrix[0][0] = $reportname;
        $matrix[1][$key] = str_replace("\n", ' ', htmlspecialchars_decode(strip_tags(nl2br($heading))));
    }
    }

    if (!empty($table->data)) {
        foreach ($table->data as $rkey => $row) {
            foreach ($row as $key => $item) {
                $matrix[$rkey + 2][$key] = str_replace("\n", ' ', htmlspecialchars_decode(strip_tags(nl2br($item))));
            }
        }
    }
    return $matrix;
}
function jobfamily(){
    global $DB;
    $sql = "SELECT jf.id,jf.familyname,jf.code,sg.id as sectorid,se.title as sector,se.code as sectorcode, sg.title as segment,sg.code as segmentcode
            FROM {local_jobfamily}  as jf
            JOIN {local_segment} as sg  ON sg.id = jf.segmentid
            JOIN {local_sector} as se ON se.id = sg.sectorid    
               
            ";
    $sectorinfo = $DB->get_records_sql($sql);      
    $data = array();
    foreach($sectorinfo as $sector){
      $row = array();
      $row[] = $sector->familyname;
      $row[] = $sector->code;
      $row[] = $sector->segment;
      $row[] = $sector->segmentcode;
      $row[] = $sector->sectorcode;
      $data[] = $row;
    }
    $table = new html_table();
    $table->head = array('Jobfamily Name','Jobfamily Code','Segment Title','Segment Code','Sector code');
    $table->data= $data;

    $matrix = array();
    $thead =array();
 
    
    if (!empty($table->head)) {
    $countcols = count($table->head);
    $keys = array_keys($table->head);
    $lastkey = end($keys);
    foreach ($table->head as $key => $heading) {
        $matrix[0][0] = $reportname;
        $matrix[1][$key] = str_replace("\n", ' ', htmlspecialchars_decode(strip_tags(nl2br($heading))));
    }
    }

    if (!empty($table->data)) {
        foreach ($table->data as $rkey => $row) {
            foreach ($row as $key => $item) {
                $matrix[$rkey + 2][$key] = str_replace("\n", ' ', htmlspecialchars_decode(strip_tags(nl2br($item))));
            }
        }
    }
    return $matrix;
}

function jobrole(){
    global $DB;
    $sql = "SELECT jr.id,jr.title as jobroletitle,jr.code as jobrolecode,jf.familyname,jf.code,sg.id as sectorid,se.title as sector,se.code as sectorcode, sg.title as segment,sg.code as segmentcode
            FROM {local_jobrole_level} as jr
            JOIN  {local_jobfamily}  as jf ON jf.id = jr.jobfamily
            JOIN {local_segment} as sg  ON sg.id = jf.segmentid
            JOIN {local_sector} as se ON se.id = sg.sectorid    
            ";
    $sectorinfo = $DB->get_records_sql($sql);      
    $data = array();
    foreach($sectorinfo as $sector){
      $row = array();
      $row[] = $sector->jobroletitle;
      $row[] = $sector->jobrolecode;
      $row[] = $sector->familyname;
      $row[] = $sector->code;
      $row[] = $sector->segment;
      $row[] = $sector->segmentcode;
      $row[] = $sector->sectorcode;
      $data[] = $row;
    }
    $table = new html_table();
    $table->head = array('Jobrole Title','Jobrole code','Jobfamily Name','Jobfamily Code','Segment Title','Segment Code','Sector code');
    $table->data= $data;

    $matrix = array();
    $thead =array();
 
    
    if (!empty($table->head)) {
    $countcols = count($table->head);
    $keys = array_keys($table->head);
    $lastkey = end($keys);
    foreach ($table->head as $key => $heading) {
        $matrix[0][0] = $reportname;
        $matrix[1][$key] = str_replace("\n", ' ', htmlspecialchars_decode(strip_tags(nl2br($heading))));
    }
    }

    if (!empty($table->data)) {
        foreach ($table->data as $rkey => $row) {
            foreach ($row as $key => $item) {
                $matrix[$rkey + 2][$key] = str_replace("\n", ' ', htmlspecialchars_decode(strip_tags(nl2br($item))));
            }
        }
    }
    return $matrix;
}
function organizationinfo(){
    global $DB;
    $sql = "SELECT fullname,shortname FROM {local_organization} WHERE visible=1 AND status = 2";
    $orginfo = $DB->get_records_sql($sql);      
    $data = array();
    foreach($orginfo as $org){
      $row = array();
      $row[] = $org->fullname;
      $row[] = $org->shortname;
      $data[] = $row;
    }
    $table = new html_table();
    $table->head = array('Organization Name',' Organization Code');
    $table->data= $data;

    $matrix = array();
    $thead =array();
 
    
    if (!empty($table->head)) {
    $countcols = count($table->head);
    $keys = array_keys($table->head);
    $lastkey = end($keys);
    foreach ($table->head as $key => $heading) {
        $matrix[0][0] = $reportname;
        $matrix[1][$key] = str_replace("\n", ' ', htmlspecialchars_decode(strip_tags(nl2br($heading))));
    }
    }

    if (!empty($table->data)) {
        foreach ($table->data as $rkey => $row) {
            foreach ($row as $key => $item) {
                $matrix[$rkey + 2][$key] = str_replace("\n", ' ', htmlspecialchars_decode(strip_tags(nl2br($item))));
            }
        }
    }
    return $matrix;
}
function competencytype(){
    global $DB;
    $sql = "SELECT id,type FROM {local_competencies} WHERE 1=1 GROUP BY type";
    $competencytype = $DB->get_records_sql($sql);      
    $data = array();
    foreach($competencytype as $type){
      $row = array();
      $row[] = $type->type;
      $data[] = $row;
    }
    $table = new html_table();
    $table->head = array('Type');
    $table->data= $data;

    $matrix = array();
    $thead =array();
 
    
    if (!empty($table->head)) {
    $countcols = count($table->head);
    $keys = array_keys($table->head);
    $lastkey = end($keys);
    foreach ($table->head as $key => $heading) {
        $matrix[0][0] = $reportname;
        $matrix[1][$key] = str_replace("\n", ' ', htmlspecialchars_decode(strip_tags(nl2br($heading))));
    }
    }

    if (!empty($table->data)) {
        foreach ($table->data as $rkey => $row) {
            foreach ($row as $key => $item) {
                $matrix[$rkey + 2][$key] = str_replace("\n", ' ', htmlspecialchars_decode(strip_tags(nl2br($item))));
            }
        }
    }
    return $matrix;
}
function competencies(){
    global $DB;
    $sql = "SELECT id,name,code FROM {local_competencies} WHERE 1=1 ";
    $competencytype = $DB->get_records_sql($sql);      
    $data = array();
    foreach($competencytype as $type){
      $row = array();
      $row[] = $type->name;
      $row[] = $type->code;
      $data[] = $row;
    }
    $table = new html_table();
    $table->head = array('Name','Code');
    $table->data= $data;

    $matrix = array();
    $thead =array();
 
    
    if (!empty($table->head)) {
    $countcols = count($table->head);
    $keys = array_keys($table->head);
    $lastkey = end($keys);
    foreach ($table->head as $key => $heading) {
        $matrix[0][0] = $reportname;
        $matrix[1][$key] = str_replace("\n", ' ', htmlspecialchars_decode(strip_tags(nl2br($heading))));
    }
    }

    if (!empty($table->data)) {
        foreach ($table->data as $rkey => $row) {
            foreach ($row as $key => $item) {
                $matrix[$rkey + 2][$key] = str_replace("\n", ' ', htmlspecialchars_decode(strip_tags(nl2br($item))));
            }
        }
    }
    return $matrix;
}
function roles(){
    global $DB;
    $sql = "SELECT id,name,shortname FROM {role} WHERE 1=1 ";
    $competencytype = $DB->get_records_sql($sql);      
    $data = array();
    foreach($competencytype as $type){
      $row = array();
      $row[] = $type->name;
      $row[] = $type->shortname;
      $data[] = $row;
    }
    $table = new html_table();
    $table->head = array('Name','Shortname');
    $table->data= $data;

    $matrix = array();
    $thead =array();
 
    
    if (!empty($table->head)) {
    $countcols = count($table->head);
    $keys = array_keys($table->head);
    $lastkey = end($keys);
    foreach ($table->head as $key => $heading) {
        $matrix[0][0] = $reportname;
        $matrix[1][$key] = str_replace("\n", ' ', htmlspecialchars_decode(strip_tags(nl2br($heading))));
    }
    }

    if (!empty($table->data)) {
        foreach ($table->data as $rkey => $row) {
            foreach ($row as $key => $item) {
                $matrix[$rkey + 2][$key] = str_replace("\n", ' ', htmlspecialchars_decode(strip_tags(nl2br($item))));
            }
        }
    }
    return $matrix;
}