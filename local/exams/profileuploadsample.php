<?php
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
$format = optional_param('format', '', PARAM_ALPHA);
$context = context_system::instance();
$sql = "SELECT * FROM {local_exam_profiles} ORDER BY id DESC LIMIT 1";
$params=null;
$getdata = $DB->get_record_sql($sql);
$fields = array(
    'OldId' => 'OldId',
    'ProfileCode' => 'ProfileCode',
    'ExamCode' => 'ExamCode',
    'ProfileStartdate' => 'ProfileStartdate',
    'ProfileEnddate' => 'ProfileEnddate',
    'ProfileDuration' => 'ProfileDuration',
    'Seats' => 'Seats',
    'PassingPercentage' => 'PassingPercentage',
    'Active' => 'Active',
    'DecisionFlow' => 'DecisionFlow',
    'Published' => 'Published',
    'Language' => 'Language',
    'NumberOfQuestions' => 'NumberOfQuestions',
    'TrialQuestions' => 'TrialQuestions',
    'MaterialURL' => 'MaterialURL',
    'Audience' => 'Audience',
    'NDA' => 'NDA',
    'Instructions' => 'Instructions',
    'HasCertificate' => 'HasCertificate',
    'ShowPreExamPage' => 'ShowPreExamPage',
    'ShowRemainingDuration' => 'ShowRemainingDuration',
    'ShowSuccessRequirements' => 'ShowSuccessRequirements',
    'ShowNumberofQuestions' => 'ShowNumberofQuestions',
    'ShowExamDuration' => 'ShowExamDuration',
    'AllowExaminertowriteacommentoneachquestion' => 'AllowExaminertowriteacommentoneachquestion',
    'AllowExaminertowriteacommentaftersubmission' => 'AllowExaminertowriteacommentaftersubmission',
    'ShowExamResult'=>'ShowExamResult',
    'ShowExamGrade' => 'ShowExamGrade',
    'ShowCompetenciesResult' => 'ShowCompetenciesResult',
    'ShowResultForEachCompetency' => 'ShowResultForEachCompetency',
    'ExaminerMustFillEvaluationFormAfterExamSubmission' => 'ExaminerMustFillEvaluationFormAfterExamSubmission',
    'Notifytheexaminerbeforeexamendsby' => 'Notifytheexaminerbeforeexamendsby',
);
require_once($CFG->libdir . '/csvlib.class.php');
$filename = clean_filename(get_string('profileupload','local_exams'));
$csvexport = new csv_export_writer();
$csvexport->set_filename($filename);
$csvexport->add_data($fields);
if($getdata) {
    $getcode = $DB->get_record('local_exams',['id'=>$getdata->examid]);
    if($getdata->registrationstartdate==0) {
        $startdate = date('m/d/Y');
    } else {
        $startdate = date('m/d/Y', $getdata->registrationstartdate);
    }
    if($getdata->registrationenddate==0) {
        $endate = date('m/d/Y', strtotime("+2 days"));
    } else {
        $enddate = date('m/d/Y', $getdata->registrationenddate);
    }

    $tprogram1 = array('abc-25458-cde-kjasdhf',$getdata->profilecode,$getcode->code,$startdate, $enddate,$getdata->duration,$getdata->seatingcapacity,$getdata->passinggrade,$getdata->activestatus,$getdata->decision,$getdata->publishstatus,$getdata->language,$getdata->questions,$getdata->trailquestions,$materialurl,$getdata->targetaudience,$getdata->nondisclosure,$getdata->instructions,$getdata->hascertificate,$getdata->preexampage,$getdata->showremainingduration,$getdata->successrequirements,$getdata->showquestions,$getdata->showexamduration,$getdata->commentsoneachque,$getdata->commentsaftersub,$getdata->showexamresult,$getdata->showexamgrade,$getdata->competencyresult,$getdata->resultofeachcompetency,$getdata->evaluationform,$getdata->notifybeforeexam);
} else {
    $tprogram1 = array('HGF00410041','HGF0041','10/1/2022','10/25/2022','100','35',
    '100','1','1','0','ar','50','3','https://drive.google.com/..','1','LONGTEXT WILL BE WRITTEN AS HTML','LONGTEXT WILL BE WRITTEN AS HTML','1','1','1','1','1','1','1','1','1','1','120');
}
    
$csvexport->add_data($tprogram1);
$csvexport->download_file();
die;
