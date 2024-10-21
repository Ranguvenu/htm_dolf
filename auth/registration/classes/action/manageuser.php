<?php
// This file is part of Moodle - http://moodle.org/
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
/**
 * @package    auth_registration
 * @copyright  2022 eAbyas Info Solutions<info@eabyas.com>
 * @author     Vinod Kumar  <vinod.p@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace auth_registration\action;
require_once($CFG->dirroot.'/user/lib.php');
defined('MOODLE_INTERNAL') or die;
use html_writer;
use moodle_url;
use context_system;
use tabobject;
use user_create_user;
use context_user;
use core_user;
class manageuser{       
    function create_custom_user($data){
        global $DB, $USER, $CFG;      
        $userrecord   = new \stdClass(); 
        $userrecord->firstname = ucfirst($data->firstname);
        $userrecord->lastname =ucfirst($data->lastname);
        $userrecord->gender = $data->gender;
        $userrecord->lang = $data->lang;
        $userrecord->nationality = $data->nationality;
        $userrecord->id_type = $data->id_type;
        $userrecord->id_number = $data->id_number;
        $userrecord->organization = $data->organization;
        $userrecord->sector = $data->sectors;
        $userrecord->segment = $data->segment;
        $userrecord->jobfamily = $data->jobfamily;
        $userrecord->jobrole = $data->jobrole;
        $userrecord->jobrole_level = $data->jobrole_level;
        $userrecord->username = $data->username;
        $userrecord->email = $data->email;
        $userrecord->password = hash_internal_user_password($data->password);
        $userrecord->confirm_password =$data->confirm_password;
        $userrecord->phone1 = $data->phone1;
        $userrecord->country_code = $data->country_code;
        $userrecord->country = $data->country;
        $userrecord->city = ucfirst($data->city);
        $userrecord->timecreated =time();
        $userrecord->approvedstatus =1;
        $userrecord->dateofbirth = $data->dateofbirth;
        $userrecord->firstnamearabic = $data->firstnamearabic;
        $userrecord->lastnamearabic = $data->lastnamearabic;
        $userrecord->middlenameen =$data->middlenameen;
        $userrecord->middlenamearabic =$data->middlenamearabic;
        $userrecord->thirdnameen =$data->thirdnameen;
        $userrecord->thirdnamearabic =$data->thirdnamearabic;

        try{
            $createdid =$DB->insert_record('local_users', $userrecord);
      

         if($data->organization) {
            (new manageuser)->organization_request_record($data->organization,$createdid);
         }
         return $createdid;
        } catch(moodle_exception $e){
          print_r($e);
        }
    }
    function local_users_logs($event, $module, $description){
    
        global $DB, $USER, $CFG;       
        $log_data               = new \stdClass();
        $log_data->event        = $event;
        $log_data->module       = $module;
        $log_data->description  = $description;
        $log_data->timecreated  = time();

        try{
          $result = $DB->insert_record('local_users_logs', $log_data);
         return $result;
        } catch(moodle_exception $e){
          print_r($e);
        }
    }

    public static function org_sector($jobroleid=0) {

        global $DB, $USER;

        $current_lang = current_language();

        if($jobroleid){

            if($current_lang == 'ar') {

                $sql = 'SELECT sect.id,sect.titlearabic as title
                                            FROM {local_sector} as sect 
                                            JOIN {local_segment} as seg ON seg.sectorid=sect.id 
                                            JOIN  {local_jobfamily} as jbfm ON jbfm.segmentid=seg.id 
                                            JOIN {local_jobrole_level} as jbrl ON jbrl.jobfamily=jbfm.id 
                                            WHERE jbrl.id=:jobroleid';
            } else {

                $sql = 'SELECT sect.id,sect.title as title
                                            FROM {local_sector} as sect 
                                            JOIN {local_segment} as seg ON seg.sectorid=sect.id 
                                            JOIN  {local_jobfamily} as jbfm ON jbfm.segmentid=seg.id 
                                            JOIN {local_jobrole_level} as jbrl ON jbrl.jobfamily=jbfm.id 
                                            WHERE jbrl.id=:jobroleid';
            }

            

          $sector= $DB->get_record_sql($sql,['jobroleid' => $jobroleid]);

        } else {
             if($current_lang == 'ar') {

                $sector= $DB->get_records_sql_menu("SELECT id,titlearabic as title FROM {local_sector}");


             } else {

                $sector= $DB->get_records_sql_menu("SELECT id,title as title FROM {local_sector}");


             }


            $sector = array(null => get_string('choosesector', 'auth_registration')) + $sector ;
        }


        return $sector;
            
    } 
    public static function org_segment($jobroleid=0,$segments= array(),$userid=0) {

        global $DB, $USER;

        $current_lang = current_language();

        $segment=array();

        if($jobroleid){

            if($current_lang == 'ar') {

                $segment= $DB->get_record_sql('SELECT seg.id,seg.titlearabic as title
                                                FROM {local_segment} as seg 
                                                JOIN  {local_jobfamily} as jbfm ON jbfm.segmentid=seg.id 
                                                JOIN {local_jobrole_level} as jbrl ON jbrl.jobfamily=jbfm.id 
                                                 WHERE jbrl.id=:jobroleid',['jobroleid' => $jobroleid]);

            } else {

                $segment= $DB->get_record_sql('SELECT seg.id,seg.title as title
                                                FROM {local_segment} as seg 
                                                JOIN  {local_jobfamily} as jbfm ON jbfm.segmentid=seg.id 
                                                JOIN {local_jobrole_level} as jbrl ON jbrl.jobfamily=jbfm.id 
                                                 WHERE jbrl.id=:jobroleid',['jobroleid' => $jobroleid]);


            }

           

        }elseif(!empty($segments)){

            $params = array();

            list($segmentssql, $segmentparams) = $DB->get_in_or_equal($segments, SQL_PARAMS_NAMED, 'sgmnt');
            $params = array_merge($params, $segmentparams);
            if($current_lang == 'ar') {
            
               $segment= $DB->get_records_sql_menu("SELECT id,titlearabic as title FROM {local_segment} WHERE id $segmentssql",$params);
            } else {

                $segment= $DB->get_records_sql_menu("SELECT id,title as title FROM {local_segment} WHERE id $segmentssql",$params);
            }

        }elseif($userid){

            if($current_lang == 'ar') {

                $segment= $DB->get_records_sql_menu('SELECT seg.id, seg.titlearabic as title
                                                FROM {local_segment} as seg 
                                                JOIN  {local_jobfamily} as jbfm ON jbfm.segmentid=seg.id 
                                                JOIN {local_jobrole_level} as jbrl ON jbrl.jobfamily=jbfm.id 
                                                JOIN {local_users} as cmtc ON cmtc.jobrole=jbrl.id
                                                 WHERE cmtc.id=:userid',['userid' => $userid]);
            } else {

                $segment= $DB->get_records_sql_menu('SELECT seg.id,seg.title as title
                                                FROM {local_segment} as seg 
                                                JOIN  {local_jobfamily} as jbfm ON jbfm.segmentid=seg.id 
                                                JOIN {local_jobrole_level} as jbrl ON jbrl.jobfamily=jbfm.id 
                                                JOIN {local_users} as cmtc ON cmtc.jobrole=jbrl.id
                                                 WHERE cmtc.id=:userid',['userid' => $userid]);


           }


        }

        return $segment;   
    }
    
    public static function org_jobfamily($jobroleid=0,$jobfamilies= array(),$userid=0) {

        global $DB, $USER;
        $current_lang = current_language();

        if($jobroleid){

            if($current_lang == 'ar') {
 
                $jobfamily= $DB->get_record_sql('SELECT jbfm.id,jbfm.familynamearabic as title 
                                                    FROM {local_jobfamily} as jbfm 
                                                    JOIN {local_jobrole_level} as jbrl ON jbrl.jobfamily=jbfm.id 
                                                     WHERE jbrl.id=:jobroleid',['jobroleid' => $jobroleid]);
           } else {


               $jobfamily= $DB->get_record_sql('SELECT jbfm.id,jbfm.familyname as title 
                                                    FROM {local_jobfamily} as jbfm 
                                                    JOIN {local_jobrole_level} as jbrl ON jbrl.jobfamily=jbfm.id 
                                                     WHERE jbrl.id=:jobroleid',['jobroleid' => $jobroleid]);

           }

        }elseif(!empty($jobfamilies)){

            $params = array();

            list($jobfamiliessql, $jobfamiliesparams) = $DB->get_in_or_equal($jobfamilies, SQL_PARAMS_NAMED, 'jobfml');
            $params = array_merge($params, $jobfamiliesparams);
            

            if($current_lang == 'ar') {

                $jobfamily= $DB->get_records_sql_menu("SELECT id,familynamearabic as title FROM {local_jobfamily} WHERE id $jobfamiliessql",$params);
            } else {
                
                $jobfamily= $DB->get_records_sql_menu("SELECT id,familyname as title FROM {local_jobfamily} WHERE id $jobfamiliessql",$params);

            }

        }elseif($userid){


            if($current_lang == 'ar') {


               $jobfamily= $DB->get_records_sql_menu('SELECT jbfm.id,jbfm.familynamearabic as title 
                                                    FROM {local_jobfamily} as jbfm 
                                                    JOIN {local_jobrole_level} as jbrl ON jbrl.jobfamily=jbfm.id 
                                                       JOIN {local_users} as cmtc ON cmtc.jobrole=jbrl.id
                                                 WHERE cmtc.id=:userid',['userid' => $userid]);
            } else {


                $jobfamily= $DB->get_records_sql_menu('SELECT jbfm.id,jbfm.familyname as title 
                                                    FROM {local_jobfamily} as jbfm 
                                                    JOIN {local_jobrole_level} as jbrl ON jbrl.jobfamily=jbfm.id 
                                                       JOIN {local_users} as cmtc ON cmtc.jobrole=jbrl.id
                                                 WHERE cmtc.id=:userid',['userid' => $userid]);


            } 


        }

        return $jobfamily;
            
    }
    public static function org_jobrole($jobroleid=0,$jobroles= array(),$userid=0) {

        global $DB, $USER;

        $current_lang = current_language();

        if($jobroleid){
            if($current_lang == 'ar') {

                $jobrole= $DB->get_record_sql('SELECT id,titlearabic as title,level,description FROM {local_jobrole_level} WHERE id=:jobroleid',['jobroleid' => $jobroleid]);
            } else {

                $jobrole= $DB->get_record_sql('SELECT id,title as title,level,description FROM {local_jobrole_level} WHERE id=:jobroleid',['jobroleid' => $jobroleid]);
            }

        }elseif(!empty($jobroles)){

            $params = array();

            list($jobrolessql, $jobrolesparams) = $DB->get_in_or_equal($jobroles, SQL_PARAMS_NAMED, 'jobrl');
            $params = array_merge($params, $jobrolesparams);
            
            if($current_lang == 'ar') {

                $jobrole= $DB->get_records_sql_menu("SELECT id,titlearabic as title FROM {local_jobrole_level} WHERE id $jobrolessql",$params);

            } else {

                $jobrole= $DB->get_records_sql_menu("SELECT id,title as title FROM {local_jobrole_level} WHERE id $jobrolessql",$params);


            }

        }elseif($userid){

            if($current_lang == 'ar') {

            $jobrole= $DB->get_records_sql_menu('SELECT jbrl.id,jbrl.titlearabic as title 
                                                    FROM {local_jobrole_level} as jbrl
                                                     JOIN {local_users} as cmtc ON cmtc.jobrole=jbrl.id
                                                 WHERE cmtc.id=:userid',['userid' => $userid]);
            } else {


                $jobrole= $DB->get_records_sql_menu('SELECT jbrl.id,jbrl.title as title 
                                                    FROM {local_jobrole_level} as jbrl
                                                     JOIN {local_users} as cmtc ON cmtc.jobrole=jbrl.id
                                                 WHERE cmtc.id=:userid',['userid' => $userid]);


            }

        }

        return $jobrole;
            
    }


    public function organization_request_record($organization,$userid) {
        global $DB, $USER;
        $userrecord   = new \stdClass(); 
        $userrecord->orgid =$organization;
        $userrecord->userid =$userid;
        $userrecord->timecreated =time();
        $userrecord->usercreated =$USER->id; 
        if (!$DB->record_exists('organization_requests',  array('orgid'=>$organization,'userid'=>$userid))) {
            try{
                $orgrequestresult =$DB->insert_record('organization_requests', $userrecord);
            } catch(moodle_exception $e){
              print_r($e);
            } 
        }
    }
}
