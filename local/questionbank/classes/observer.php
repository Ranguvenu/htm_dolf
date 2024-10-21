<?php
/**
 * @package    local_questionbank
 * @author  2022 Naveen kumar <naveen@eabyas.com>
 */
namespace local_questionbank;

use dml_exception;

/**
 * Local questionbank observer 
 */
class observer
{
    public static function question_deleted($object){
        global $DB;

        try{
            $existingqcount = $DB->get_record('local_questionbank',array('qcategoryid' =>$object->other['categoryid']));
            $qcount = $existingqcount->qcount-1;
            $updaterecord= $DB->update_record('local_questionbank', array('id'=>$existingqcount->id,'qcount'=>$qcount)); 
            $DB->delete_records('local_qb_questioninfo', ['questionid' => $object->objectid]);
            $DB->delete_records('local_qb_coursetopics', ['questionid' => $object->objectid]);
            $DB->delete_records('local_qb_competencies', ['questionid' => $object->objectid]);
        }
        catch(dml_exception $e){
            print_r($e->debuginfo);
        }
    }
    public static function question_created($object){
        global $DB;

        try{
            
            $existingqcount = $DB->get_record('local_questionbank',array('qcategoryid' =>$object->other['categoryid']));
            $qcount = $existingqcount->qcount+1;
            $updaterecord= $DB->update_record('local_questionbank', array('id'=>$existingqcount->id,'qcount'=>$qcount)); 
            
        }
        catch(dml_exception $e){
            print_r($e->debuginfo);
        }
    }
}