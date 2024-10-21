<?php
/**
 * This file is part of eAbyas
 *
 * Copyright eAbyas Info Solutons Pvt Ltd, India
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author eabyas  <info@eabyas.in>
 * @package Exams
 * @subpackage local_users
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
$format = optional_param('format', 'csv', PARAM_ALPHA);
$systemcontext = context_system::instance();
if ($format) {
    $fields = array(
        'old_id' => 'old_id',
        'exam'=>'exam',
        'examnamearabic' => 'examnamearabic',
        'code'=>'code',
        'examprice'=>'examprice',
        'sellingprice'=>'sellingprice',
        'actualprice'=>'actualprice',
        'description'=>'description',
        'targetaudience'=>'targetaudience',
        'sectors'=>'sectors',
        'taxfree' => 'taxfree',
        'jobfamilies' => 'jobfamilies',
        'clevels'=>'clevels',
        'ctype'=>'ctype',
        'competencies'=>'competencies',
        'competencyweights'=>'competencyweights',
        'preparationprograms' => 'preparationprograms',
        'requirements'=>'requirements',
        'additionalrequirements' => 'additionalrequirements',
        'type'=>'type',
        'certificatevalidity'=>'certificatevalidity',
        'ownedby'=>'ownedby',
        'attachedmsg' => 'attachedmsg',
        'noofattempts' => 'noofattempts',
        'appliedon' => 'appliedon',
    );
    switch ($format) {
        case 'csv' : user_download_csv($fields);
    }
    die;
}
function user_download_csv($fields) {
    global $CFG, $DB;
    require_once($CFG->libdir . '/csvlib.class.php');
    $filename = 'SampleExams';
    $csvexport = new csv_export_writer();
    $csvexport->set_filename($filename);
    $csvexport->add_data($fields);
    $exam   = $DB->get_record_sql("SELECT * FROM {local_exams} ORDER BY id DESC LIMIT 1");

    if ($exam) {

        if (!empty($exam->requirements)) {
            $sql = "SELECT code 
                      FROM {local_exams} 
                     WHERE id IN ($exam->requirements) ";
            $requirementslist = $DB->get_fieldset_sql($sql);
            $requirements = implode(':', $requirementslist);
        } else {
            $requirements = NULL;
        }
        if (!empty($exam->programs)) {
            $sql = "SELECT code 
                      FROM {local_trainingprogram} 
                     WHERE id IN ($exam->programs) ";
            $programslist = $DB->get_fieldset_sql($sql);
            $programs = implode(':', $programslist);
        } else {
            $programs = NULL;
        }

        $sectors = $DB->get_fieldset_sql("SELECT code FROM {local_sector} WHERE id IN ($exam->sectors) ");
        if (!empty($exam->competencies)) {
            $competencies = $DB->get_fieldset_sql("SELECT code FROM {local_competencies} WHERE id IN ($exam->competencies) ");            
        }
        $examdatetime = date("d-m-Y", strtotime("+1 day"));
        $enddate = date("d-m-Y", strtotime("+5 day"));

        $userprofiledata = array('3af00d69-3df2-49fa-9ef0-ac5f010b19e7', 'Sample Exam', 'عينة الامتحان', 'sampleexam',1,8500,7500,'Exam related to Culture', 'targetaudience', implode(':', $sectors),0,null,$exam->clevels,str_replace(",",":", $exam->ctype),implode(':', $competencies),'Competencyweights',$programs, $requirements, $exam->additionalrequirements, 'professionaltest', 2, $exam->ownedby,$exam->attachedmessage, $exam->noofattempts, $exam->appliedperiod);
    } else {
        $userprofiledata = array('3af00d69-3df2-49fa-9ef0-ac5f010b19e7', 'Sample Exam', 'عينة الامتحان', 'sampleexam',1,8500,7500,'Exam related to Culture', null,null,0
            ,null,null,null,null,'Competency and it\'s weights',null, null, 'Additional Requirements', 'professionaltest',2,'Academic', NULL, 3, 0);
    }

    $csvexport->add_data($userprofiledata);
    $csvexport->add_data($userprofiledata1);
    $csvexport->download_file();
    die;
}
