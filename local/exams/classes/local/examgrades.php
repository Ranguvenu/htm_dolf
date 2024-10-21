<?php
namespace local_exams\local;

use moodle_exception;

class examgrades {

    public function examusergrades($examid)
    {
        global $DB;
        $examssql = "SELECT le.id, le.courseid
                       FROM  {local_exams} le 
                      WHERE le.old_id !=0 ";
        if ($examid>0) {
            $examssql .= " AND le.id=".$examid;
        }
        $exams = $DB->get_records_sql($examssql);
        foreach ($exams as $exam) {
            self::examquizzes($exam->id, $exam->courseid);
        }
        echo get_string('processcompleted', 'local_exams');
        // return true;
    }

    public function examquizzes($examid, $courseid)
    {
        global $DB;
        $sql = "SELECT lep.quizid, lep.questions
                  FROM {local_exam_profiles} as lep 
                  JOIN {local_exams} le ON le.id = lep.examid 
                  WHERE le.id = ".$examid;
        $quizzes = $DB->get_records_sql($sql);

        foreach ($quizzes as $quiz) {
            $questions = $quiz->questions;
            $gradesql = "SELECT gg.id, gg.finalgrade AS finalgrade, gg.userid
                      FROM {grade_grades} gg
                      JOIN {grade_items} gi ON gg.itemid = gi.id
                     WHERE gi.courseid = {$courseid} AND gi.itemtype = 'mod' AND gi.itemmodule  = 'quiz' AND gi.iteminstance = {$quiz->quizid} ";
            $grades = $DB->get_records_sql($gradesql);

            foreach ($grades as $grade) {
                $usergrade = ($grade->finalgrade/$questions)*100;
                if ($usergrade <= 100) {
                   try{                    
                        $DB->update_record('grade_grades', ['id'=>$grade->id, 'finalgrade'=>$usergrade]);
                        $username = $DB->get_record('local_users', ['userid'=>$grade->userid], 'firstname,lastname');
                        echo get_string('updatedgrade', 'local_exams', ['grade'=>$usergrade, 'firstname'=>$username->firstname, 'lastname'=>$username->lastname]);
                    } catch(moodle_exception $e){
                        print_r($e);
                    }
                }
            }
        }
    }
}
