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
ini_set("memory_limit", "-1");
ini_set('max_execution_time', 6000);
require_once('../../config.php');
global $DB, $CFG;
require_once($CFG->libdir . '/pdflib.php');
$lang = optional_param('lang',false,PARAM_RAW);
$programcode = required_param('programcode', PARAM_RAW);
$offeringcode = optional_param('offeringcode',false,PARAM_RAW);
$programid =(int) $DB->get_field_sql("SELECT id FROM {local_trainingprogram} WHERE code = '$programcode'");
$programinfo =$DB->get_record('local_trainingprogram',['id'=>$programid]);
$isArabic = ($lang == 'ar') ?'true':'false'; 
$program = (new local_trainingprogram\local\trainingprogram)->get_programinfo($programid,$isArabic,$offeringcode);
$programname = ($lang == 'ar') ? $programinfo->namearabic : $programinfo->name;
$programdetailsURL = $program->detailsPageURL;
 $releasedate = userdate($programinfo->availablefrom, get_string('strftimedatemonthabbr', 'core_langconfig'));
$durationindays=round($programinfo->duration / 86400).' Days'; 

$title = ($lang == 'ar') ? 'titlearabic' : 'title';
if($programinfo->sectors) {
    $setorsquery = "SELECT  $title from {local_sector} where id IN($programinfo->sectors)";
    $sectors = $DB->get_fieldset_sql($setorsquery);
    $sectorslist = implode(', ',$sectors);  
} else {
    $sectorslist = '';
}
if($DB->record_exists('program_goals',['programid'=>$programid])) {
    $goalsquery = "SELECT  * from {program_goals} where programid = $programid";
    $totalgoals = $DB->get_records_sql($goalsquery);
    $allgoals =[];
    foreach ($totalgoals AS $goal){
       $allgoals[] =  format_text($goal->programgoal,FORMAT_HTML);
    }
    $goals = implode(', ',$allgoals);  
} else {
    $goals = '';
}
$competencytitle = ($lang == 'ar') ? 'arabicname' : 'name';
if($programinfo->sectors) {
    $competencyquery = "SELECT  $competencytitle from {local_competencies} where id in($programinfo->competencyandlevels)";
    $competencies = $DB->get_fieldset_sql($competencyquery);
    $competencylist = implode(', ',$competencies); 
    $competencysize = COUNT($competencies); 
} else {
    $competencylist = '';
    $competencysize = COUNT($competencylist); 
}
$prerequirementsprogramstitle = ($lang == 'ar') ? 'namearabic' : 'name';
if($programinfo->prerequirementsprograms) {
    $setorsquery = "SELECT  $prerequirementsprogramstitle from {local_trainingprogram} where id in($programinfo->prerequirementsprograms)";
    $programs = $DB->get_fieldset_sql($setorsquery);
    $prerequirementsprograms = implode(', ',$programs);  
    $prerequirementsprogramssize = COUNT($programs);
} else {
    $prerequirementsprograms = '';
    $prerequirementsprogramssize = COUNT($prerequirementsprograms);
}

$postrequirementsprogramstitle = ($lang == 'ar') ? 'namearabic' : 'name';
if($programinfo->postrequirementsprograms) {
    $postsquery = "SELECT  $postrequirementsprogramstitle from {local_trainingprogram} where id in($programinfo->prerequirementsprograms)";
    $postprograms = $DB->get_fieldset_sql($postsquery);
    $postrequirementsprograms = implode(', ',$postprograms);  
    $postrequirementsprogramssize = COUNT($postprograms);
} else {
    $postrequirementsprograms = '';
    $postrequirementsprogramssize = COUNT($postrequirementsprograms);
}

if($programinfo->targetgroup != null && !empty($programinfo->targetgroup) && $programinfo->targetgroup != ''){

    if($programinfo->targetgroup == '-1') {

     $jobfamilies = (new local_trainingprogram\local\trainingprogram)->get_all_job_families_by_sectors($programinfo->sectors);
     $programinfo->targetgroup = implode(',', $jobfamilies);

    } else {
      $programinfo->targetgroup = $programinfo->targetgroup;
    } 
    if($programinfo->targetgroup != null && !empty($programinfo->targetgroup) && $programinfo->targetgroup != ''){   

        $jobfamilytitle = ($lang == 'ar') ? 'familynamearabic' : 'familyname';
        $alljobbfamiliesquery = "select $jobfamilytitle from {local_jobfamily} where id IN($programinfo->targetgroup)";
        $alljobbfamilies = $DB->get_fieldset_sql($alljobbfamiliesquery);
        $jobfamilieslist = implode(', ',$alljobbfamilies);
    } else {
        $jobfamilieslist = '';
    }
} else {

    $jobfamilieslist = '';
}    

$PAGE->set_context(context_system::instance());
define ('PDF_PAGE_FORMAT', 'A4');
define ('PDF_PAGE_ORIENTATION', 'P');
$PAGE->requires->css(new moodle_url('/local/events/styles.css'));
class MYPDF extends \TCPDF  {
    public function Header() {
        global $DB, $CFG;
        $lang = optional_param('lang',false,PARAM_RAW);
        $programcode = required_param('programcode', PARAM_RAW);
        $offeringcode = optional_param('offeringcode',false,PARAM_RAW);
        $this->SetFont('helveticaneueltarabiclight', 'regular', 9);
        $this->SetFont('helveticaneueltarabic', 'B', 9);
        $this->SetTextColor(167, 147, 68);
    }
    public function Footer() {
        $this->SetY(-10);
        $this->SetFont('helveticaneueltarabiclight', 'regular', 8);
        $this->Cell(0, 10, 'Page', 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}
$pdf = new MYPDF('P', PDF_UNIT, array(210, 350), true, 'UTF-8', false);
$pdf->SetFont("helveticaneueltarabiclight", 'N', 10);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$tagvs = array(
'divp' => array(0 => array('h' =>0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)),
'p' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)),
'h1' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)),
'h2' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)),
'h3' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)),
'h4' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)),
'h5' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)),
'br' => array(0 => array('h' => 1, 'n' => 0), 1 => array('h' => 1, 'n' => 0))); 
$pdf->setHtmlVSpace($tagvs);

$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
// add a page
$pdf->AddPage();

$pdf->Image('pix/pdfimages/mainbg.jpg',5,5,200,60,'JPG');
$heading1= '<p style="color:#fff; font-size:14px;font-weight:bold ">'.$programname.'</p><div><p style="color:#fff;font-size:12px;font-weight:bold">('. get_string('program_code','local_trainingprogram').': '.$programinfo->code.')</p></div><div style="color:#fff;font-size:12px;font-weight:bold "></div><div><p style="color:#fff;font-size:10px;font-weight:bold">'. get_string('sectors','local_trainingprogram').': '.$sectorslist .'</p></div><div style="color:#fff;font-size:12px;font-weight:bold "></div>';
$pdf->writeHTMLCell(150,0,10,15,$heading1,0,1,0, true,'', true);
$pdf->writeHTMLCell(30,0,167,20,'<p style="color:#fff; font-size:12px;font-weight:bold ">'. get_string('programfee','local_trainingprogram').'</p>',0,1,0, true,'', true);

$pdf->writeHTMLCell(30,0,164,30,'<h3 style="color:#fff; font-size:25px; text-align:center">'. number_format($programinfo->sellingprice).'</h3>',0,1,0, true,'', true);
if($lang == 'ar') {
    $pdf->writeHTMLCell(30,0,165,45,'<p style="color:#fff; font-size:14px">'. get_string('sa_currency','local_trainingprogram') .'</p>',0,1,0, true,'', true);
} else {
    $pdf->writeHTMLCell(30,0,173,45,'<p style="color:#fff; font-size:14px">'. get_string('sa_currency','local_trainingprogram') .'</p>',0,1,0, true,'', true);
}

$html1= '<diV></div><diV></div><diV></div>
<img src="'.$program->imgDataUrl .'" width = "600px" height = "200px" border="0" />
<diV></div>';
$pdf->writeHTML($html1, true, false, true, false, '');

// if(!empty($program->imgDataUrl)) {

//     $hexabgimglength = 138;
// } else {
//     $hexabgimglength = 0;
// }

// $pdf->Image('pix/pdfimages/hexabg.jpg',10,$hexabgimglength,20,18,'JPG');
$html2= '<h2 style="color:#004c98; font-size:14px;font-weight:bold ">'.get_string('program_description','local_trainingprogram').'</h2><diV></div>
'.$program->brief .'
<diV></div>';
$pdf->writeHTML($html2, true, false, true, false, '');


$html3= '<h2 style="color:#004c98; font-size:14px;font-weight:bold ">'.get_string('programgoals','local_trainingprogram').'</h2><diV></div>
'.$goals .'
<diV></div>';
$pdf->writeHTML($html3, true, false, true, false, '');

$html4 = '<h2 style="color:#004c98; font-size:14px;font-weight:bold ">'.get_string('lisofofferings','local_trainingprogram').'</h2><diV></div>';
foreach($program->plans as $currentoffering) {
    $html4 .= '<div><span style="font-weight:bold">'.get_string('total_seats','local_trainingprogram').': '. $currentoffering->total_seats .'</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="font-weight:bold">'. get_string('available_seats','local_trainingprogram') .': '.$currentoffering->available_seats .'</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="font-weight:bold">'. get_string('enrolledseats','local_trainingprogram') .': '.$currentoffering->enrolled_seats .'</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="font-weight:bold">'. get_string('type','local_trainingprogram') .':'.$currentoffering->trainingmethod.'</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="font-weight:bold">'. get_string('price','local_trainingprogram') .': '.$currentoffering->price.'</span></div><div><span style="font-weight:bold">'. get_string('startdate','local_trainingprogram').': '.$currentoffering->startdate.' '.$currentoffering->starttime.'</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span></span>&nbsp;&nbsp;<span></span style="font-weight:bold">'. get_string('enddate','local_trainingprogram') .': '.$currentoffering->enddate.' '.$currentoffering->endtime.'</span></div><div></div>';
 }

$pdf->writeHTML($html4, true, false, true, false, '');

$html5= '<h2 style="color:#004c98; font-size:14px;font-weight:bold ">'.get_string('jobfamilies','local_trainingprogram').'</h2><diV></div>
'.$jobfamilieslist .'
<diV></div>';
$pdf->writeHTML($html5, true, false, true, false, '');

$html6= '<h2 style="color:#004c98; font-size:14px;font-weight:bold ">'.get_string('flyer_prerequirementsprograms','local_trainingprogram').'</h2><diV></div>
'.$prerequirementsprograms .'
<diV></div>';
$pdf->writeHTML($html6, true, false, true, false, '');

$html7= '<h2 style="color:#004c98; font-size:14px;font-weight:bold ">'.get_string('flyer_postrequirementsprograms','local_trainingprogram').'</h2><diV></div>
'.$postrequirementsprograms .'
<diV></div>';
$pdf->writeHTML($html7, true, false, true, false, '');

$html8= '<h2 style="color:#004c98; font-size:14px;font-weight:bold ">'.get_string('listofcompetencies','local_trainingprogram').'</h2><diV></div>
'.$competencylist .'
<diV></div>';
$pdf->writeHTML($html8, true, false, true, false, '');

$html9= '<h2 style="color:#004c98; font-size:14px;font-weight:bold ">'.get_string('programdetailsURL','local_trainingprogram').'</h2><diV></div>
'.$programdetailsURL .'
<diV></div>';
$pdf->writeHTML($html9, true, false, true, false, '');

$pdf->Output('trainingflyer.pdf', 'i');
