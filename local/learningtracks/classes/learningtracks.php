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
/**
 * Class containing helper methods for processing data requests.
 *
 * @package    local_learningtracks
 * @copyright  2022 e abyas  <info@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_learningtracks;

use coding_exception;
use context_helper;
use context_system;
use core_user;
use dml_exception;
use moodle_exception;
use moodle_url;
use required_capability_exception;
use stdClass;
use local_trainingprogram\local\trainingprogram as tp;
use local_exams\local\exams;
use local_trainingprogram_external;
use tool_product\product as product;

use local_competency\competency as competency;


defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/local/notifications/notification.php');
require_once($CFG->dirroot . '/local/learningtracks/lib.php');

/**
 * Class containing helper methods for processing data requests.
 *
 * @copyright  2018 Jun Pataleta
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class learningtracks {

    public static function get_listof_learningtracks($stable, $filterdata=null) {
        global $DB, $USER;

        $context = context_system::instance();

        $params          = array();
        $tracks      = array();
        $trackscount = 0;
        $concatsql       = '';
        if (isset($stable->trackid) && $stable->trackid > 0) {
            $concatsql .= " AND lt.id = :trackid";
            $params['trackid'] = $stable->trackid;
        }
        if($filterdata->status !='1' ){

            $concatsql .= " AND lt.id NOT IN (SELECT trackid
                                FROM {local_lts_enrolment}
                                WHERE status = 0
                                GROUP BY trackid
                                HAVING COUNT(*) = 0)";
        }
        if($filterdata->status=='1'){

            $concatsql .= " AND lt.id IN (SELECT trackid
                                FROM {local_lts_enrolment}
                                WHERE status = 0
                                GROUP BY trackid
                                HAVING COUNT(*) = 0)";
        }

        if (!empty($filterdata->search_query)) {

            $concatsql .= " AND (lt.name LIKE :search1 OR lt.namearabic LIKE :search2) ";

            $params = array('search1' => '%'.trim($filterdata->search_query).'%','search2' => '%'.trim($filterdata->search_query).'%');
        }

        $countsql = "SELECT COUNT(lt.id) ";
        $fromsql = "SELECT lt.* ";
        
        $sql = " FROM {local_learningtracks} AS lt WHERE lt.id > 0 ";    

         if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $context)) {

            $usersql="SELECT u.id,lc.organization 
                FROM {user} AS u 
                JOIN {local_users} lc ON lc.userid = u.id
                JOIN {local_organization} org ON org.id = lc.organization
                WHERE  u.id=:orguserid ";

            $user=$DB->get_record_sql($usersql,array('orguserid'=>$USER->id));  

            $sql.= " AND (lt.organization=$user->organization OR  lt.organization=0 ) ";

         }
        $sql .= $concatsql;
        if (isset($stable->trackid) && $stable->trackid > 0) {
            $tracks = $DB->get_record_sql($fromsql . $sql, $params);
        } else {
            try {
                $trackscount = $DB->count_records_sql($countsql . $sql, $params);
                if ($stable->thead == false) {
                    $sql .= " ORDER BY lt.id DESC";
                    $tracks = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
                }
            } catch (dml_exception $ex) {
                $trackscount = 0;
            }
        }
        if (isset($stable->trackid) && $stable->trackid > 0) {
            return $tracks;
        } else {
            return compact('tracks', 'trackscount');
        }
    }
    public function get_organizationslist($query = null) {
        global $DB;

        $currentlang= current_language();

        if( $currentlang == 'ar'){

            $titlefield='lo.fullnameinarabic';

            $fields = array('lo.fullnameinarabic', 'lo.shortname');

        }else{

            $titlefield='lo.fullname';

            $fields = array('lo.fullname', 'lo.shortname');
        }

        $likesql = array();
        $i = 0;
        foreach ($fields as $field) {
            $i++;
            $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
            $sqlparams["queryparam$i"] = "%$query%";
        }
        $sqlfields = implode(" OR ", $likesql);
        $concatsql = " AND ($sqlfields) ";
        $allobject[] = new \stdClass();
        $allobject[0]->id = 0;
        $allobject[0]->fullname = get_string('all', 'local_learningtracks');
        $allobjectarr = array('id' => 0 , 'fullname'=> get_string('all', 'local_learningtracks'));
        
        $sql = "SELECT lo.id AS id, CONCAT($titlefield, ' ', lo.shortname) AS fullname 
                FROM {local_organization} lo WHERE 1 = 1 $concatsql ";

        $sqldata = $DB->get_records_sql($sql, $sqlparams);
        $data = array_merge($allobject,$sqldata);
        $list = array_values(json_decode(json_encode(($data)), true));

        return $list;
    }
    public function get_competencylist($query = null) {
        global $DB;

        $currentlang= current_language();

        if( $currentlang == 'ar'){

            $titlefield='lc.arabicname';

            $fields = array('lc.arabicname', 'lc.code');

        }else{

            $titlefield='lc.name';

            $fields = array('lc.name', 'lc.code');
        }

        $likesql = array();
        $i = 0;
        foreach ($fields as $field) {
            $i++;
            $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
            $sqlparams["queryparam$i"] = "%$query%";
        }
        $sqlfields = implode(" OR ", $likesql);
        $concatsql = " AND ($sqlfields) ";
        $data = $DB->get_records_sql("SELECT lc.id, CONCAT($titlefield, ' ', lc.code) AS fullname FROM {local_competencies} lc WHERE 1=1 $concatsql", $sqlparams);
        return $data;
    }
    public function get_examlist($trackid, $query = null) {
        global $DB;

        $currentlang= current_language();

        if( $currentlang == 'ar'){

            $titlefield='le.examnamearabic';

            $fields = array('le.examnamearabic', 'le.code');

        }else{

            $titlefield='le.exam';

            $fields = array('le.exam', 'le.code');
        }

        
        $likesql = array();
        $i = 0;
        foreach ($fields as $field) {
            $i++;
            $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
            $sqlparams["queryparam$i"] = "%$query%";
        }
        $sqlfields = implode(" OR ", $likesql);
        $concatsql = " AND ($sqlfields) ";

        $regstartdatecase= '
                        CASE 

                           WHEN lep.registrationstartdate <> 0 THEN date(FROM_UNIXTIME(lep.registrationstartdate)) >= CURDATE() 
                           ELSE lep.registrationstartdate =0
                        END';
        if($trackid) {
            $sql = "SELECT le.id AS id, CONCAT($titlefield, ' ', le.code) AS fullname FROM {local_exams} le
                        JOIN {local_exam_profiles} lep ON  lep.examid = le.id
                        WHERE lep.activestatus = 1 AND lep.publishstatus = 1 AND lep.decision = 1 AND $regstartdatecase
                        AND le.id NOT IN (SELECT li.itemid FROM {local_learning_items} li
            WHERE li.itemid = le.id AND li.itemtype = 2 AND li.trackid = $trackid) $concatsql AND le.status = 1 ";
        } else {
            $sql = "SELECT le.id AS id, CONCAT($titlefield, ' ', le.code) AS fullname FROM {local_exams} le
                        JOIN {local_exam_profiles} lep ON  lep.examid = le.id
                        WHERE lep.activestatus = 1 AND lep.publishstatus = 1 AND lep.decision = 1 AND $regstartdatecase $concatsql AND le.status = 1";
        }
        $data = $DB->get_records_sql($sql, $sqlparams);
        return $data;
    }
    public function get_programlistt($trackid, $orgid = null, $query = null) {
        global $DB;

        $currentlang= current_language();

        if( $currentlang == 'ar'){

            $titlefield='lp.namearabic';

            $fields = array('lp.namearabic', 'lp.code');

        }else{

            $titlefield='lp.name';

            $fields = array('lp.name', 'lp.code');
        }

       
        $likesql = array();
        $i = 0;
        foreach ($fields as $field) {
            $i++;
            $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
            $sqlparams["queryparam$i"] = "%$query%";
        }
        $sqlfields = implode(" OR ", $likesql);
        $concatsql = " AND ($sqlfields) ";
        if($trackid) {
            $sql = " SELECT lp.id AS id, CONCAT($titlefield, ' ', lp.code) AS fullname FROM {local_trainingprogram} lp
            JOIN {tp_offerings} tpo ON lp.id = tpo.trainingid
            WHERE lp.id NOT IN (SELECT li.itemid FROM {local_learning_items} li
            WHERE li.itemid = lp.id AND li.itemtype = 1 AND li.trackid = $trackid) $concatsql";
            if($orgid > 0) {
                $sql .= " AND ((tpo.organization = $orgid AND tpo.type = 1) OR tpo.type = 2)";
            } else {
                $sql .= " AND tpo.type = 2 AND lp.published = 1 ";
            }
           /* $sql .= "AND 
            (CASE
                        WHEN tpo.trainingmethod !='elearning'  THEN (date(FROM_UNIXTIME(tpo.startdate)) > CURDATE() || (date(FROM_UNIXTIME(tpo.startdate)) = CURDATE() && tpo.time > 0 &&  FROM_UNIXTIME(tpo.startdate+tpo.time) >= NOW()))
                        ELSE (tpo.startdate = 0 OR tpo.startdate IS NULL OR tpo.enddate = 0 OR tpo.enddate = null )
            END)
    
           ";*/
        } else {
            $sql = " SELECT lp.id AS id, CONCAT($titlefield, ' ', lp.code) AS fullname FROM {local_trainingprogram} lp
            JOIN {tp_offerings} tpo ON lp.id = tpo.trainingid AND tpo.type = 2 $concatsql AND lp.published = 1 /*AND (date(FROM_UNIXTIME(tpo.startdate)) > CURDATE() || (date(FROM_UNIXTIME(tpo.startdate)) = CURDATE() && tpo.time > 0 &&  FROM_UNIXTIME(tpo.startdate+tpo.time) >= NOW()))*/ ";

        }
        $data = $DB->get_records_sql($sql, $sqlparams);
        return $data;
    }

    public function add_update_track($data) {
        global $DB,$USER,$CFG;
		$systemcontext = context_system::instance();
        if(isset($data->description)) {
           $data->description =  $data->description['text'];
        }
        if (isset($data->logo)) {
			$data->logo = $data->logo;
			file_save_draft_area_files($data->logo, $systemcontext->id, 'local_learningtracks', 'logos', $data->logo);
		}
        $data->points = 0;
        $data->tags = 0;
        $data->competency = implode(',', $data->competency);
		if ($data->id) {
            $data->timemodified = time();
			$DB->update_record('local_learningtracks', $data);
            // Trigger learning track updated.
            $eventparams = array('context' => context_system::instance(),'objectid'=>$data->id);
            $event = event\learning_track_updated::create($eventparams);
            $event->trigger();
             // notification learningtrack update
            $trackinfo = $DB->get_record('local_learningtracks', array('id' => $data->id));
             $row=[];
             $row['learningTrackName']=$trackinfo->name;
             $row['updated']='updated';
            $myobject=(new \local_learningtracks\notification);
            $myobject->learningtracks_notification('learningtrack_update',$touser=null, get_admin(),$row,$waitinglistid=0);
            // notification learningtrack onchange
            $sql="SELECT u.* FROM {user} u
				 JOIN {local_lts_enrolment} le ON le.userid = u.id
                 WHERE le.trackid = $data->id AND u.confirmed = 1 AND u.suspended = 0 AND u.deleted = 0 AND u.id > 2";
                  $touser = $DB->get_records_sql($sql);
            $thispageurl = new moodle_url('/local/learningtracks/view.php?id='.$data->id);
             $row1=[];
             $row1['RelatedModuleName']=$trackinfo->name;
             $row1['RelatedModulesLink']=$thispageurl;
            $myobject=(new \local_learningtracks\notification);
            $myobject->learningtracks_notification('learningtrack_onchange',$touser, get_admin(),$row1,$waitinglistid=0);
			//------------------------------------------------------
            return $data->id;
		} else {
            $data->status = 0;
            $data->timecreated = time();
            $id=$DB->insert_record('local_learningtracks', $data);
            // Trigger learning track added.
            $eventparams = array('context' => context_system::instance(),'objectid'=>$id);
            $event = event\learning_track_added::create($eventparams);
            $event->trigger();
           // notification learningtrack add
            $trackinfo = $DB->get_record('local_learningtracks', array('id' => $id));
            $row=[];
            $row['learningTrackName']=$trackinfo->name;
            $row['created']='created';
            $myobject=(new \local_learningtracks\notification);
            $myobject->learningtracks_notification('learningtrack_create',$touser=null,get_admin(),$row,$waitinglistid=0);

            return $id;
		}
    }

    public function set_data($id) {
    	global $DB;
        $data = $DB->get_record('local_learningtracks', ['id' => $id], '*', MUST_EXIST);
		$row['id'] = $data->id;
		$row['name'] = $data->name;
        $row['namearabic'] = $data->namearabic;
		$row['code'] = $data->code;
		$row['organization'] = $data->organization;
		$row['competency'] = explode(',', $data->competency);
		$row['logo'] = $data->logo;
		$row['requiredsequence'] = $data->requiredsequence;
		$row['description'] = ['text' => $data->description];
        $row['points'] = $data->points;
		$row['tags'] = $data->tags;
        $row['requiredapproval'] = $data->requiredapproval;
        return $row;
    }

    public function competency_list($stable, $filterdata) {
        global $DB, $PAGE;
        $systemcontext = context_system::instance();
        $formsql       = '';
        $competencies =  $DB->get_field('local_learningtracks', 'competency', ['id' => $filterdata->trackid]);

        $currentlang= current_language();

        if( $currentlang == 'ar'){

            $titlefield='le.arabicname';

            $fields = array('le.arabicname', 'le.code');

        }else{

            $titlefield='le.name';

            $fields = array('le.name', 'le.code');
        }

        $selectsql = "SELECT le.* ,$titlefield as fullname"; 
        $countsql  = "SELECT COUNT(le.id) ";

        $sql = " FROM {local_competencies} le WHERE 1=1 ";    


        if($competencies){


            $competencies=implode(',',array_filter(explode(',',$competencies)));

            $formsql .= " AND id IN ($competencies)";

        }else{

            $formsql .= " AND id =0 ";
        }

        if (!empty($filterdata->search_query) && $competencies) {

            $formsql .= " AND (le.name LIKE :search1 OR le.arabicname LIKE :search2 OR le.code LIKE :search3) ";

            $searchparams = array('search1' => '%'.trim($filterdata->search_query).'%','search2' => '%'.trim($filterdata->search_query).'%','search3' => '%'.trim($filterdata->search_query).'%');
        }

        $params = array_merge($searchparams);
        $sql .= $formsql;
        $totalcompetency = $DB->count_records_sql($countsql.$sql, $params);
        $formsql .=" ORDER BY le.id DESC";
        $addedcompetencies = $DB->get_records_sql($selectsql.$sql, $params, $stable->start,$stable->length);
        if ($addedcompetencies) {
            $constcompetencytypes = tp::constcompetency_types();

        $stable = new \stdClass();
        $stable->thead = true;
     
        foreach ($addedcompetencies AS $competenciestype) {

            $competenciestype->type = $constcompetencytypes[$competenciestype->type];

            $stable->competencyid = $competenciestype->id;

            $competencypc=competency::get_competency_performances($stable);
            
            $competenciestype->noperformance=$competencypc['competencypccount'];

        }   
            $nodata = true;
        } else {
            $nodata = false;
        }
        $competencyContext = array(
            "acompetencies" => $addedcompetencies,
            "nodata" => $nodata,
            "totalcount" => $totalcompetency,
            "length" => $totalcompetency
        );        
        return $competencyContext;
    }

    public function add_learning_items($data) {
        global $DB,$USER,$CFG;
        if ($data->itemtype == 1) {
            $itemids = $data->program;
        } else {
            $itemids = $data->exam;
        }
        
        if($data->id > 0) { 

            $data->timemodified = time();
                 // notification learningtrack onchange
            $sql="SELECT u.* FROM {user} u
    				 JOIN {local_lts_enrolment} le ON le.userid = u.id
                     WHERE le.trackid = $data->trackid AND u.confirmed = 1 AND u.suspended = 0 AND u.deleted = 0 AND u.id > 2";
            $touser = $DB->get_records_sql($sql);
            $thispageurl = new moodle_url('/local/learningtracks/view.php?id='.$data->trackid);
            $trackinfo = $DB->get_record('local_learningtracks', array('id' => $data->trackid));
            $row1=[];
            $row1['RelatedModuleName']=$trackinfo->name;
            $row1['RelatedModulesLink']=$thispageurl;
            $myobject=(new \local_learningtracks\notification);
            $myobject->learningtracks_notification('learningtrack_onchange',$touser, get_admin(),$row1,$waitinglistid=0);
           

            return $DB->update_record('local_learning_items', $data);

        } else {
            foreach($itemids as $ids) {
                $data->itemid = $ids;
                $data->timecreated = time();
                $id=$DB->insert_record('local_learning_items', $data);
                 // notification learningtrack onchange
                 $sql="SELECT u.* FROM {user} u
				 JOIN {local_lts_enrolment} le ON le.userid = u.id
                 WHERE le.trackid = $data->trackid AND u.confirmed = 1 AND u.suspended = 0 AND u.deleted = 0 AND u.id > 2";
                  $touser = $DB->get_records_sql($sql);
                 $thispageurl = new moodle_url('/local/learningtracks/view.php?id='.$data->trackid);
                $trackinfo = $DB->get_record('local_learningtracks', array('id' => $data->trackid));
                $row1=[];
                $row1['RelatedModuleName']=$trackinfo->name;
                $row1['RelatedModulesLink']=$thispageurl;
                $myobject=(new \local_learningtracks\notification);
                $myobject->learningtracks_notification('learningtrack_onchange',$touser, get_admin(),$row1,$waitinglistid=0);
                    // Trigger learning items to track.
                $eventparams = array('context' => context_system::instance(),'objectid'=>$id);
                $event = event\add_items_totrack::create($eventparams);
                $event->trigger();

                $enrolledrecords=$this->map_enrolled_records($ids, $data->itemtype, $data->orgid);

            } 
        }
    }
    public static function get_listof_learningitems($trackid, $stable, $itemtype = '') {
        global $DB, $USER;
        $systemcontext = context_system::instance();
        $params           = array();
        $learningitems = array();
        $concatsql        = '';
        $countsql = "SELECT COUNT(li.id) ";
        $fromsql  = "SELECT li.*";
        $sql      = " FROM {local_learning_items} AS li
                      JOIN {local_learningtracks} AS lt ON li.trackid = lt.id ";

        $sql .= "  WHERE li.trackid = :trackid ";
        if($itemtype) {
            $sql .= " AND li.itemtype = $itemtype"; 
        }              
        $params['trackid'] = $trackid;
        $sql .= $concatsql;
        try {
            $learningitemscount = $DB->count_records_sql($countsql . $sql, $params);
            if ($stable->thead == false) {
                $sql .= " ORDER BY li.id DESC";
                $learningitems = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
            }
        }
        catch (dml_exception $ex) {
            $learningitemscount = 0;
        }
        return compact('learningitems', 'learningitemscount');
    }

    public static function get_listof_users($trackid, $stable) {
        global $DB, $USER;
        $context = context_system::instance();
        $params           = array();
        $users = array();
        $concatsql        = '';
        $countsql = "SELECT COUNT(u.id) ";
        $fromsql  = "SELECT u.* ";
        $sql      = " FROM {user} u
				 JOIN {local_lts_enrolment} le ON le.userid = u.id ";
        if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$context)) {
            $organization = $DB->get_field('local_users','organization',array('userid'=>$USER->id));
            $sql .=  " JOIN {local_users} lu ON le.userid = lu.userid";
            $sql.= " AND lu.organization = $organization";
        } 
        $sql .= "  WHERE le.trackid = :trackid AND u.confirmed = 1 AND u.suspended = 0 AND u.deleted = 0 AND u.id > 2 ";
        $sql .= $concatsql;
        $params['trackid'] = $trackid;
        $sql .= $concatsql;
        try {
            $userscount = $DB->count_records_sql($countsql . $sql, $params);
            if ($stable->thead == false) {
                $sql .= " ORDER BY u.id ASC";
                $users = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
            }
        }
        catch (dml_exception $ex) {
            $userscount = 0;
        }
        return compact('users', 'userscount');
    }

    public static function get_listof_learningpath($stable, $filterdata) {
        global $CFG,$DB,$OUTPUT,$USER,$PAGE;
        $context = context_system::instance();
        $formsql = '';
        $selectsql = "SELECT lt.id, lt.name, lt.* FROM {local_learningtracks} AS lt ";

        $countsql = "SELECT count(lt.id) FROM {local_learningtracks} AS lt ";   


        if (!empty($filterdata->search_query)) {

            $formsql .= " AND (lt.name LIKE :search1 OR lt.namearabic LIKE :search2) ";

            $searchparams = array('search1' => '%'.trim($filterdata->search_query).'%','search2' => '%'.trim($filterdata->search_query).'%');
        }

        $params = array_merge($searchparams);
        $totallearningpath= $DB->count_records_sql($countsql.$formsql, $params);
        $formsql .=" ORDER BY lt.id DESC";
        $learningpath = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $learningpathlist = array();
        $count = 0;
        foreach ($learningpath as $learningpaths) {
            $learningpathlist[$count]["id"] = $learningpaths->id;  
            $learningpathlist[$count]["name"] = $learningpaths->name;
            $learningpathlist[$count]["description"] = format_text($learningpaths->description);
            $itemid = $learningpaths->logo;
            $fs = get_file_storage();
            $files = $fs->get_area_files($context->id, 'local_learningtracks', 'logos', $itemid);
            foreach($files as $file){
               $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(),
               $file->get_itemid(), $file->get_filepath(), $file->get_filename(), false);
               $download_url = $url->get_port() ? $url->get_scheme() . '://' . $url->get_host() . $url->get_path() . ':' . $url->get_port() : $url->get_scheme() . '://' . $url->get_host() . $url->get_path();
               $learningpaths->logo = $download_url;
            }
            $learningpathlist[$count]["course"] = [];
            $learningpathlist [$count]["logo"] = $learningpaths->logo;
            $totallearningitems=$DB->get_records('local_learning_items',array('trackid'=>$learningpaths->id));
            foreach($totallearningitems as $totallearningitem){
                $coursecount=count($totallearningitem->id);
                if($totallearningitem->itemtype=="2"){
                    $exams=$DB->get_records('local_exams',array('id'=>$totallearningitem->itemid));
                    foreach($exams as $exam){
                        $itemid = $exam->learningmaterial;
                        $fs = get_file_storage();
                        $files = $fs->get_area_files($context->id, 'local_exams', 'learningmaterials', $itemid);
                        foreach($files as $file){
                           $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(),
                           $file->get_itemid(), $file->get_filepath(), $file->get_filename(), false);
                           $download_url = $url->get_port() ? $url->get_scheme() . '://' . $url->get_host() . $url->get_path() . ':' . $url->get_port() : $url->get_scheme() . '://' . $url->get_host() . $url->get_path();                        
                           $exam->learningmaterial = $download_url ;
                        }
                        $learningpathlist[$count]["course"][] = ['name' => $exam->exam,'description'=>format_text($exam->programdescription),'image'=>$exam->learningmaterial,'coursecount'=>$coursecount,'code'=>$exam->code];
                    }                   
                   
                } else if($totallearningitem->itemtype=="1"){
                    $trainingprograms=$DB->get_records('local_trainingprogram',array('id'=>$totallearningitem->itemid));
                    foreach($trainingprograms as $trainingprogram){
                        $itemid = $trainingprogram->image;
                        $fs = get_file_storage();
                        $files = $fs->get_area_files($context->id, 'local_trainingprogram', 'trainingprogramlogo', $itemid);
                        foreach($files as $file){
                        $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(),
                        $file->get_itemid(), $file->get_filepath(), $file->get_filename(), false);
                        $download_url = $url->get_port() ? $url->get_scheme() . '://' . $url->get_host() . $url->get_path() . ':' . $url->get_port() : $url->get_scheme() . '://' . $url->get_host() . $url->get_path();
                        $trainingprogram->image= $download_url ;
                        }
                        $learningpathlist[$count]["course"][] = ['name' => $trainingprogram->name,'description'=>format_text($trainingprogram->description),'image'=>$trainingprogram->image,'coursecount'=>$coursecount,'code'=>$trainingprogram->code];  
                    }
                }
            }
            $count++;
        }
        $nocourse = false;
        $coursesContext = array(
            "hascourses" => $learningpathlist,
            "nocourses" => $nocourse,     
            "totallearningpath" => $totallearningpath,
            "length" => count($learningpathlist)
            );
        return $coursesContext;
    }

    public static function learningtrack_enrolled_users($trackid, $params, $lastitem=0) {
        global $DB, $USER;
        $systemcontext = context_system::instance();
        $traineerole = $DB->get_field('role', 'id', array('shortname' => 'trainee'));

        $fullname = (new local_trainingprogram\local\trainingprogram())->user_fullname_case($method = 'enrollment');

        $fromsql = "SELECT distinct u.id,$fullname";
        $countsql = "SELECT count(u.id) as total";
        $sql = " FROM {user} AS u 
                JOIN {local_users} lc ON lc.userid = u.id
                WHERE  u.id > 2 AND u.deleted = 0  AND lc.deleted=0 AND lc.approvedstatus=2";
        if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
            $organization = $DB->get_field('local_users','organization',array('userid'=>$USER->id));
            $sql.= " AND lc.organization = $organization";
        }        
        if($lastitem!=0){
            $sql.=" AND u.id > $lastitem";
        }
        $sql .=" AND u.id <> $USER->id";
        if (!empty($params['query'])) {
            $sql .= " AND (lc.firstname LIKE :firstnamesearch OR lc.lastname LIKE :lastnamesearch OR lc.firstnamearabic LIKE :firstnamearabicsearch OR lc.lastnamearabic LIKE :llastnamearabicsearch OR lc.middlenameen LIKE :middlenameensearch OR lc.middlenamearabic LIKE :middlenamearabicsearch OR lc.thirdnameen LIKE :thirdnameensearch OR lc.thirdnamearabic LIKE :thirdnamearabicsearch OR lc.email LIKE :email  OR lc.id_number LIKE :idnumber) ";
            $searchparams = array(
                                  'firstnamesearch' => '%'.trim($params['query']).'%',
                                  'lastnamesearch' => '%'.trim($params['query']).'%', 
                                  'firstnamearabicsearch' => '%'.trim($params['query']).'%', 
                                  'llastnamearabicsearch' => '%'.trim($params['query']).'%', 
                                  'middlenameensearch' => '%'.trim($params['query']).'%', 
                                  'middlenamearabicsearch' => '%'.trim($params['query']).'%', 
                                  'thirdnameensearch' => '%'.trim($params['query']).'%', 
                                  'thirdnamearabicsearch' => '%'.trim($params['query']).'%', 
                                  'email' => '%'.trim($params['query']).'%',
                                  'idnumber' => '%'.trim($params['query']).'%');

            
        }  else {
            $searchparams = array();
        }
        if (!empty($params['email'])) {
             $sql.=" AND u.id IN ({$params['email']})";
        }

        $order = ' ORDER BY u.id ASC ';
        $sql .= " AND u.id IN (SELECT roa.userid 
                                          FROM {role_assignments}  roa 
                                         WHERE roa.roleid=:roleid1) ";
        $existingsql = " SELECT le.userid 
                             FROM {local_lts_enrolment} AS le 
                            WHERE  le.trackid =:trackid";

        $availablesql .= " AND u.id NOT IN ($existingsql)";

        $params = array_merge(['trackid' => $trackid, 'roleid1' => $traineerole], $searchparams);
        $ausers = $DB->get_records_sql($fromsql . $sql . $availablesql .$order,$params);

        foreach($ausers as $auser){
            $availableusers[] = ['id' => $auser->id, 'fullname' => $auser->fullname];
        };

        $enrolledsql  .= " AND u.id IN ($existingsql)";
        $eusers = $DB->get_records_sql($fromsql . $sql. $enrolledsql .$order,$params);
        foreach($eusers as $euser){
            $enrolledusers[] = ['id' => $euser->id, 
                                'fullname' => $euser->fullname];
        };

        $enrolleduserscount = $DB->count_records_sql($countsql . $sql . $availablesql,$params);
        $availableuserscount = $DB->count_records_sql($countsql . $sql . $enrolledsql,$params);

        return compact('availableusers', 'enrolledusers', 'availableuserscount', 'enrolleduserscount');
    }
    public function learningtrack_enrollmet($trackid, $userid, $roleid, $examlist, $programlist) {
        global $DB,$USER;

        $learning_track = $DB->get_record('local_learningtracks', array('id' => $trackid));
        $row = array();
        $row['trackid'] = $trackid;
        $row['userid'] = $userid;
        $row['realuser'] =  ($USER->realuser) ? $USER->realuser :0;
        $row['status'] = 0;
        $row['enrolmentdate'] = time();
        $row['timecreated'] = time();
        $row['usercreated'] = $USER->id; 
          try {

               $ltsenrolmentid = $DB->get_field('local_lts_enrolment','id', ['trackid' => $trackid,'userid' => $userid]);

                if (!$ltsenrolmentid) {

                    $ltsenrolmentid = $DB->insert_record('local_lts_enrolment', $row);
                    // notification
                    $touser = $DB->get_record('user', array('id' => $userid));
                    $row=[];
                    $row['learningTrackName']=$learning_track->name;
                    $myobject=(new \local_learningtracks\notification);
                    $myobject->learningtracks_notification('learningtrack_enroll',$touser, get_admin(),$row,$waitinglistid=0);

                }
               //----------------------------------------------------------------
                if ($ltsenrolmentid) {

                    if(!empty($examlist)) {
                     
                        foreach($examlist as $item) {

                            $selectsql = " SELECT  le.id
                                    FROM {local_exams} le
                                    JOIN {hall_reservations} as hr ON hr.typeid=le.id AND hr.type='exam'
                                    WHERE hr.id = ".$item;
                            $examid = $DB->get_field_sql($selectsql);

                            if($this->is_enrolled($examid, PRODUCT::EXAMS, $userid)){
                                continue;
                            }
                          
                            $reservationid = $DB->get_field('tool_products', 'referenceid', ['id' => $item, 'category' => product::EXAMS]);
                            
                            (new \local_exams\local\exams)->exam_enrollmet($reservationid, $userid);

                            $isexamcompleted = $DB->record_exists_sql("SELECT id
                                                    FROM {exam_completions} 
                                                     WHERE examid = $examid
                                                    AND userid = $userid AND completion_status IN(1,2)");
                            
                            $lts_item = [];
                            $lts_item['trackid'] = $trackid;
                            $lts_item['itemid'] = $examid;
                            $lts_item['itemtype'] = product::EXAMS;
                            $lts_item['userid'] = $userid;
                            $lts_item['realuser'] = ($USER->realuser) ? $USER->realuser :0;
                            $lts_item['usercreated'] = $USER->id;
                            $lts_item['timecreated'] = time();
                            $lts_item['enrolmentdate'] = time();

                            if($isexamcompleted) {
                                $completiondate = $DB->get_field_sql("SELECT completiondate  FROM {exam_completions}  
                                                                    WHERE examid = $examid
                                                                    AND userid = $userid AND completion_status IN(1,2)");
                               $lts_item['status'] = 1 ;
                               $lts_item['completiondate'] = $completiondate;
                            }

                            $DB->insert_record('local_lts_item_enrolment', $lts_item);

                            $iscompleted = $DB->record_exists_sql("SELECT id
                                  FROM {completiondate} 
                                 WHERE examid = $examid
                                    AND userid = $userid AND completion_status IN(1,2)");
                            if($iscompleted) {
                                $lts_enrollment =$DB->get_record('local_lts_enrolment',['trackid' =>$trackid,'userid'=>$userid]);
                                $event = \local_learningtracks\event\learningtracks_completion_updated::create(array(
                                    'objectid' => $lts_enrollment->id,
                                    'context' => \context_system::instance(),
                                    'relateduserid' => $userid,
                                    'other' => array(
                                        'relateduserid' => $userid,
                                        'learningtrackid' => $trackid,
                                        'completion_status' => 1,
                                    )
                                ));
                                $event->add_record_snapshot('local_lts_enrolment', $lts_enrollment);
                                $event->trigger();

                            }
                        }
                    }
                    if(!empty($programlist)) {
                    
                        foreach($programlist as $item) {

                            $programid = $DB->get_field('tp_offerings', 'trainingid', ['id' => $item]);

                            if($this->is_enrolled($programid, PRODUCT::TRAINING_PROGRAM, $userid)){
                                continue;
                            }

                            $offeringid = $DB->get_field('tool_products', 'referenceid', ['id' => $item, 'category' => product::TRAINING_PROGRAM]);

                            (new \local_trainingprogram\local\trainingprogram)->program_enrollment($offeringid,$userid);


                            $isprogramcompleted = $DB->record_exists_sql("SELECT id
                                                    FROM {program_completions} 
                                                     WHERE programid = $programid
                                                    AND userid = $userid AND completion_status IN(1,2)");

                            $lts_item = [];
                            $lts_item['trackid'] = $trackid;
                            $lts_item['itemid'] = $programid;
                            $lts_item['itemtype'] = product::TRAINING_PROGRAM;
                            $lts_item['userid'] = $userid;
                            $lts_item['realuser'] = ($USER->realuser) ? $USER->realuser :0;
                            $lts_item['usercreated'] = $USER->id;
                            $lts_item['timecreated'] = time();
                            $lts_item['enrolmentdate'] = time();
                            if($isprogramcompleted) {
                                $completiondate = $DB->get_field_sql("SELECT completiondate  FROM {program_completions}  
                                                                    WHERE programid = $programid
                                                                    AND userid = $userid AND completion_status IN(1,2)");
                               $lts_item['status'] = 1 ;
                               $lts_item['completiondate'] = $completiondate;
                            }

                            $DB->insert_record('local_lts_item_enrolment', $lts_item);


                            $iscompleted = $DB->record_exists_sql("SELECT id
                                  FROM {program_completions} 
                                 WHERE programid = $programid
                                    AND userid = $userid AND completion_status IN(1,2)");
                            if($iscompleted) {
                                $lts_enrollment =$DB->get_record('local_lts_enrolment',['trackid' =>$trackid,'userid'=>$userid]);
                                $event = \local_learningtracks\event\learningtracks_completion_updated::create(array(
                                    'objectid' => $lts_enrollment->id,
                                    'context' => \context_system::instance(),
                                    'relateduserid' => $userid,
                                    'other' => array(
                                        'relateduserid' => $userid,
                                        'learningtrackid' => $trackid,
                                        'completion_status' => 1,
                                    )
                                ));
                                $event->add_record_snapshot('local_lts_enrolment', $lts_enrollment);
                                $event->trigger();

                            }
                        }
                    }
                }
            } catch (dml_exception $ex) {
                print_error($ex);
            }            
    }
    public function program_enrolment($programid, $offeringid, $roleid, $userid) {
        global $DB,$USER;

        $learning_items = $DB->get_records('local_learning_items', array('itemtype' => product::TRAINING_PROGRAM,'itemid'=>$programid));

        if($learning_items){

            foreach($learning_items as $learning_item){

                $ltsenrolmentid = $DB->get_field('local_lts_enrolment','id', ['trackid' => $learning_item->trackid,'userid' => $userid]);

                if (!$ltsenrolmentid) {

                    $learning_track = $DB->get_record('local_learningtracks', array('id' => $learning_item->trackid));

                    $isprogramcompleted = $DB->record_exists_sql("SELECT id
                              FROM {program_completions} 
                             WHERE programid = $learning_item->itemid
                                AND userid = $userid AND completion_status IN(1,2)");

                    $row = array();
                    $row['trackid'] = $learning_item->trackid;
                    $row['userid'] = $userid;
                    $row['realuser'] =($USER->realuser > 0) ? $USER->realuser :0;
                    if($isprogramcompleted) {
                            $completiondate = $DB->get_field_sql("SELECT completiondate  FROM {program_completions}  
                                                                WHERE programid = $learning_item->itemid
                                                                AND userid = $userid AND completion_status IN(1,2)");
                       $row['status'] = 1 ;
                       $row['completiondate'] = $completiondate;
                    } else {
                        $row['status'] = 0;
                         $row['completiondate'] = null;
                    }
                    $row['enrolmentdate'] = time();
                    $row['timecreated'] = time();
                    $row['usercreated'] = $USER->id; 

                    $ltsenrolmentid = $DB->insert_record('local_lts_enrolment', $row);

                     // notification
                    $touser = $DB->get_record('user', array('id' => $userid));
                    $row=[];
                    $row['learningTrackName']=$learning_track->name;
                    $myobject=(new \local_learningtracks\notification);
                    $myobject->learningtracks_notification('learningtrack_enroll',$touser, get_admin(),$row,$waitinglistid=0);
                }

                if ($ltsenrolmentid) {


                    $ltsitemenrolmentexist = $DB->record_exists('local_lts_item_enrolment', ['trackid' => $learning_item->trackid,'itemid'=>$learning_item->itemid,'itemtype' => product::TRAINING_PROGRAM,'userid' => $userid]);

                    if (!$ltsitemenrolmentexist) {

                        $isprogramcompleted = $DB->record_exists_sql("SELECT id
                              FROM {program_completions} 
                             WHERE programid = $learning_item->itemid
                                AND userid = $userid AND completion_status IN(1,2)");

                        $lts_item = [];
                        $lts_item['trackid'] = $learning_item->trackid;
                        $lts_item['itemid'] = $learning_item->itemid;
                        $lts_item['itemtype'] = product::TRAINING_PROGRAM;
                        $lts_item['userid'] = $userid;
                        $lts_item['realuser'] =($USER->realuser > 0) ? $USER->realuser :0;
                        $lts_item['usercreated'] = $USER->id;
                        $lts_item['timecreated'] = time();
                        $lts_item['enrolmentdate'] = time();
                        if($isprogramcompleted) {
                            $completiondate = $DB->get_field_sql("SELECT completiondate  FROM {program_completions}  
                                                                WHERE programid = $learning_item->itemid
                                                                AND userid = $userid AND completion_status IN(1,2)");
                           $lts_item['status'] = 1 ;
                           $lts_item['completiondate'] = $completiondate;
                        }
                        $DB->insert_record('local_lts_item_enrolment', $lts_item);

                        $iscompleted = $DB->record_exists_sql("SELECT id
                              FROM {program_completions} 
                             WHERE programid = $learning_item->itemid
                                AND userid = $userid AND completion_status IN(1,2)");
                        if($iscompleted) {
                            $lts_enrollment =$DB->get_record('local_lts_enrolment',['trackid' =>$learning_item->trackid,'userid'=>$userid]);
                            $event = \local_learningtracks\event\learningtracks_completion_updated::create(array(
                                'objectid' => $lts_enrollment->id,
                                'context' => \context_system::instance(),
                                'relateduserid' => $userid,
                                'other' => array(
                                    'relateduserid' => $userid,
                                    'learningtrackid' => $learning_item->trackid,
                                    'completion_status' => $lts_enrollment->status,
                                )
                            ));
                            $event->add_record_snapshot('local_lts_enrolment', $lts_enrollment);
                            $event->trigger();

                        }
                    }
                }
            }
        }

    }

    public function exam_enrolment($examid, $profileid, $roleid, $userid) {
        global $DB,$USER;

        $learning_items = $DB->get_records('local_learning_items', array('itemtype' => product::EXAMS,'itemid'=>$examid));

        if($learning_items){

            foreach($learning_items as $learning_item){

                $ltsenrolmentid = $DB->get_field('local_lts_enrolment','id', ['trackid' => $learning_item->trackid,'userid' => $userid]);

                if (!$ltsenrolmentid) {

                    $learning_track = $DB->get_record('local_learningtracks', array('id' => $learning_item->trackid));

                    $isexamcompleted = $DB->record_exists_sql("SELECT id
                              FROM {exam_completions} 
                             WHERE examid = $learning_item->itemid
                                AND userid = $userid AND completion_status IN(1,2)");

                    $row = array();
                    $row['trackid'] = $learning_item->trackid;
                    $row['userid'] = $userid;
                    $row['realuser'] =($USER->realuser > 0) ? $USER->realuser :0;
                    if($isexamcompleted) {
                        $completiondate = $DB->get_field_sql("SELECT completiondate  FROM {exam_completions}  
                                                                WHERE examid = $learning_item->itemid
                                                                AND userid = $userid AND completion_status IN(1,2)");
                       $row['status'] = 1 ;
                       $row['completiondate'] = $completiondate;
                    } else {
                        $row['status'] = 0;
                         $row['completiondate'] = null;
                    }
                    $row['enrolmentdate'] = time();
                    $row['timecreated'] = time();
                    $row['usercreated'] = $USER->id; 

                    $ltsenrolmentid = $DB->insert_record('local_lts_enrolment', $row);

                     // notification
                    $touser = $DB->get_record('user', array('id' => $userid));
                    $row=[];
                    $row['learningTrackName']=$learning_track->name;
                    $myobject=(new \local_learningtracks\notification);
                    $myobject->learningtracks_notification('learningtrack_enroll',$touser, get_admin(),$row,$waitinglistid=0);
                }

                if ($ltsenrolmentid) {

                    $ltsitemenrolmentexist = $DB->record_exists('local_lts_item_enrolment', ['trackid' => $learning_item->trackid,'itemid'=>$learning_item->itemid,'itemtype' => product::EXAMS,'userid' => $userid]);

                    if (!$ltsitemenrolmentexist) {


                        $isexamcompleted = $DB->record_exists_sql("SELECT id
                                                    FROM {exam_completions} 
                                                     WHERE examid = $learning_item->itemid
                                                    AND userid = $userid AND completion_status IN(1,2)");

                        $lts_item = [];
                        $lts_item['trackid'] = $learning_item->trackid;
                        $lts_item['itemid'] = $learning_item->itemid;
                        $lts_item['itemtype'] = product::EXAMS;
                        $lts_item['userid'] = $userid;
                        $lts_item['realuser'] =($USER->realuser > 0) ? $USER->realuser :0;
                        $lts_item['usercreated'] = $USER->id;
                        $lts_item['timecreated'] = time();
                        $lts_item['enrolmentdate'] = time();
                        if($isexamcompleted) {
                            $completiondate = $DB->get_field_sql("SELECT completiondate  FROM {exam_completions}  
                                                                WHERE examid = $learning_item->itemid
                                                                AND userid = $userid AND completion_status IN(1,2)");
                           $lts_item['status'] = 1 ;
                           $lts_item['completiondate'] = $completiondate;
                        }
                        $DB->insert_record('local_lts_item_enrolment', $lts_item);

                        $iscompleted = $DB->record_exists_sql("SELECT id
                              FROM {exam_completions} 
                             WHERE examid = $learning_item->itemid
                                AND userid = $userid AND completion_status IN(1,2)");
                        if($iscompleted) {
                            $lts_enrollment =$DB->get_record('local_lts_enrolment',['trackid' =>$learning_item->trackid,'userid'=>$userid]);
                            $event = \local_learningtracks\event\learningtracks_completion_updated::create(array(
                                'objectid' => $lts_enrollment->id,
                                'context' => \context_system::instance(),
                                'relateduserid' => $userid,
                                'other' => array(
                                    'relateduserid' => $userid,
                                    'learningtrackid' => $learning_item->trackid,
                                    'completion_status' => $lts_enrollment->status,
                                )
                            ));
                            $event->add_record_snapshot('local_lts_enrolment', $lts_enrollment);
                            $event->trigger();

                        }

                    }
                }
            }
        }
    }
    public function program_unenrol($programid, $offeringid, $roleid, $userid) {
        global $DB,$USER;

        $sql = 'SELECT pe.id FROM {program_enrollments} as pe 
                 WHERE pe.programid=:programid AND pe.userid=:userid ';

        $enrolledrecord = $DB->get_record_sql($sql, ['programid' => $programid,'userid' => $userid]);

        if(!$enrolledrecord){

            $learning_items = $DB->get_records('local_learning_items', array('itemtype' => product::TRAINING_PROGRAM,'itemid'=>$programid));

            if($learning_items){

                foreach($learning_items as $learning_item){

                    $ltsenrolmentid = $DB->get_field('local_lts_enrolment','id', ['trackid' => $learning_item->trackid,'userid' => $userid]);


                    if($ltsenrolmentid){

                        $ltsitemenrolmentid = $DB->get_field('local_lts_item_enrolment','id', ['trackid' => $learning_item->trackid,'itemtype' => product::TRAINING_PROGRAM,'itemid' => $learning_item->itemid,'userid' => $userid]);


                        if ($ltsitemenrolmentid) {

                            $DB->delete_records('local_lts_item_enrolment',['id' =>$ltsitemenrolmentid]);

                        }
                        $countitem_enrolment=$DB->count_records('local_lts_item_enrolment',['trackid' => $learning_item->trackid,'itemtype' => product::TRAINING_PROGRAM,'itemid' => $learning_item->itemid,'userid' => $userid]);
                        if($countitem_enrolment == 0 || !$countitem_enrolment){

                            //$DB->delete_records('local_lts_enrolment', array('trackid' => $learning_item->trackid));
                            $DB->delete_records('local_lts_enrolment',  ['id' =>$ltsenrolmentid]);
                        }
                        
                    }

                }
            }
        }

    }

    public function exam_unenrol($examid, $profileid,$roleid, $userid) {
        global $DB,$USER;

        $sql = 'SELECT ee.id FROM {exam_enrollments} as ee 
                     WHERE ee.examid=:examid AND ee.userid=:userid ';

        $enrolledrecord = $DB->get_record_sql($sql, ['examid' => $examid,'userid' => $userid]);

        if(!$enrolledrecord){

            $learning_items = $DB->get_records('local_learning_items', array('itemtype' => product::EXAMS,'itemid'=>$examid));

            if($learning_items){

                foreach($learning_items as $learning_item){

                    $ltsenrolmentid = $DB->get_field('local_lts_enrolment','id', ['trackid' => $learning_item->trackid,'userid' => $userid]);

                    if($ltsenrolmentid){

                        $ltsitemenrolmentid = $DB->get_field('local_lts_item_enrolment','id', ['trackid' => $learning_item->trackid,'itemtype' => product::EXAMS,'itemid' => $learning_item->itemid,'userid' => $userid]);

                        if ($ltsitemenrolmentid) {

                            $DB->delete_records('local_lts_item_enrolment',['id' =>$ltsitemenrolmentid]);

                        }

                        $countitem_enrolment=$DB->count_records('local_lts_item_enrolment', ['trackid' => $learning_item->trackid,'itemid'=>$learning_item->itemid,'itemtype' => product::EXAMS,'userid' => $userid]);

                        if($countitem_enrolment == 0 || !$countitem_enrolment){
                            //$DB->delete_records('local_lts_enrolment', array('trackid' => $learning_item->trackid));
                            $DB->delete_records('local_lts_enrolment', ['id' =>$ltsenrolmentid]);
                        }
                        
                    }

                }
            }      
        }

    }

    public function get_learningpath_list() {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $renderer = $PAGE->get_renderer('local_learningtracks');
        $filterparams  = $renderer->get_learningpath_cardview(true);
        $filterparams['submitid'] = 'form#filteringform';
        $learningpath_programs = $renderer->get_learningpath_cardview();
        $filterparams['learningpath_programs_cards'] = $learningpath_programs;
        $filterparams['inputclasses'] = 'form-control learningpathsearchinput';
        $filterparams['widthclass'] = 'col-md-12';
        $filterparams['placeholder'] =  get_string('search_skill','local_exams');
        $filterparams['filterinput'] = $renderer->global_filter($filterparams);
        $renderer->listofcardviewlearningpath($filterparams);
    }

    public static function get_enrolled_learningpath($stable, $filterdata) {
        global $DB, $USER;
        $params          = array();
        $tracks      = array();
        $trackscount = 0;
        $concatsql       = '';
        if (isset($stable->trackid) && $stable->trackid > 0) {
            $concatsql .= " AND lt.id = :trackid";
            $params['trackid'] = $stable->trackid;
        }
        $countsql = " SELECT COUNT(lt.id) ";
        $fromsql = " SELECT lt.* ";
        $sql = " FROM {local_lts_enrolment} le";
        $sql .= " JOIN {local_learningtracks} lt ON lt.id = le.trackid WHERE le.userid = $USER->id ";
        $sql .= $concatsql;
        if (isset($stable->trackid) && $stable->trackid > 0) {
            $tracks = $DB->get_record_sql($fromsql . $sql, $params);
        } else {
            try {
                $trackscount = $DB->count_records_sql($countsql . $sql, $params);
                if ($stable->thead == false) {
                    $sql .= " ORDER BY lt.id DESC";
                    $tracks = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
                }
            } catch (dml_exception $ex) {
                $trackscount = 0;
            }
        }
        if (isset($stable->trackid) && $stable->trackid > 0) {
            return $tracks;
        } else {
            return compact('tracks', 'trackscount');
        }
    }

    public function update_competency($data) {
        global $DB,$USER,$CFG;
        $row['id'] = $data->id;
        $row['competency'] = implode(',', $data->competency);
        // notification learningtrack onchange
        $sql="SELECT u.* FROM {user} u
				 JOIN {local_lts_enrolment} le ON le.userid = u.id
                 WHERE le.trackid = $data->id AND u.confirmed = 1 AND u.suspended = 0 AND u.deleted = 0 AND u.id > 2";
                  $touser = $DB->get_records_sql($sql);
                 $thispageurl = new moodle_url('/local/learningtracks/view.php?id='.$data->id);
        $trackinfo = $DB->get_record('local_learningtracks', array('id' => $data->id));
        $row1=[];
        $row1['RelatedModuleName']=$trackinfo->name;
        $row1['RelatedModulesLink']=$thispageurl;
       $myobject=(new \local_learningtracks\notification);
       $myobject->learningtracks_notification('learningtrack_onchange',$touser, get_admin(),$row1,$waitinglistid=0);
        return $DB->update_record('local_learningtracks', $row);
    }

    public static function track_organizations($organization = array(),$trackid = 0) {
        global $DB;
         if($organization["0"] == '0') {
            $organizations = [0 => get_string('all', 'local_learningtracks')];
        } elseif(!empty($organization)){
            list($orgsql, $orgparams) = $DB->get_in_or_equal($organization);
            $organizations = $DB->get_records_sql_menu("SELECT id, CONCAT(fullname, ' ', shortname) AS fullname FROM {local_organization} WHERE  id $orgsql",$orgparams);
              
        } elseif($trackid) {
            $org = $DB->get_records_sql_menu("SELECT lo.id, CONCAT(lo.fullname, ' ', lo.shortname) AS fullname FROM {local_organization} lo 
            JOIN {local_learningtracks} lt ON concat(',', lt.organization, ',') LIKE concat('%,',lo.id,',%')
            WHERE lt.id = :trackid",['trackid' => $trackid]);
            /*if(empty($org)) {
                $organizations = ['0' => get_string('all', 'local_learningtracks')];
            } else {
                $organizations =  $org;
            }*/
            $organizations =  $org;
        }
        return $organizations;
    }

    public static function get_learningtrack_entities($trackid) {
        global $DB, $CFG;

        $lowestseats=0;
        
        $programitems = $DB->get_fieldset_sql("SELECT itemid FROM {local_learning_items} WHERE trackid = $trackid AND itemtype = 1 ");
        if($programitems) {
            $programs = array();
           foreach($programitems as $trainingprogram){
                $tpdata = [];
                $programname=$DB->get_field('local_trainingprogram','name', array('id'=>$trainingprogram));

                if(!$programname){
                    continue;
                }

                $getofferings = \local_trainingprogram\local\trainingprogram::get_offerings($trainingprogram, true,true);
                $tpdata['currentofferings'] =$getofferings['tpofferings'];
                $tpdata['title'] = $programname;
                $tpdata['entity'] = 'tp';
                $tpdata['entityid'] = $trainingprogram;
                $programs[] = $tpdata;

                if($lowestseats == 0 || ($lowestseats > $getofferings['tpofferinglowestseats'] && $getofferings['tpofferinglowestseats'] > 0 )){

                    $lowestseats=$getofferings['tpofferinglowestseats'];

                }
           }
        }
          
        $examitems = $DB->get_fieldset_sql("SELECT itemid FROM {local_learning_items} WHERE trackid = $trackid AND itemtype = 2 ");
        if($examitems) {
            $exams = [];
            foreach($examitems as $examid){
                $examdata = [];
                $examtitle=$DB->get_field('local_exams','exam', array('id'=>$examid));

                if(!$examtitle){
                    continue;
                }

                $getexamreservations=\local_exams\local\exams::get_examreservations($examid,array('examid'=>$examid),true);
                $examdata['examhallreservation'] = $getexamreservations['examhallreservation'];
                $examdata['title'] = $examtitle;
                $examdata['entity'] = 'exam';
                $examdata['entityid'] = $examid;
                $exams[] = $examdata;


                  if($lowestseats == 0 || ($lowestseats > $getexamreservations['reservationlowestseats'] && $getexamreservations['reservationlowestseats'] >0 )){

                    $lowestseats=$getexamreservations['reservationlowestseats'];
                }
            }
        }


        $filterparams['programs'] = $programs;//array_values($tpoffering);
        $filterparams['exams'] = $exams;
        $filterparams['lowestseats'] = $lowestseats;
        return $filterparams;
    }

    public static function track_competency($competency = array(),$trackid = 0) {
        global $DB;

         $currentlang= current_language();

        if( $currentlang == 'ar'){

            $titlefield='lc.arabicname';

        }else{

            $titlefield='lc.name';

        }


         if(!empty($competency)){
            list($cmpsql, $cmpparams) = $DB->get_in_or_equal($competency);
            $competencylist = $DB->get_records_sql_menu("SELECT lc.id AS id, CONCAT($titlefield, ' ', lc.code) AS fullname FROM {local_competencies} lc WHERE  lc.id $cmpsql",$cmpparams); 
        } elseif($trackid) {
            $competencylist = $DB->get_records_sql_menu("SELECT lc.id AS id, CONCAT($titlefield, ' ', lc.code) AS fullname FROM {local_competencies} lc 
            JOIN {local_learningtracks} lt ON concat(',', lt.competency, ',') LIKE concat('%,',lc.id,',%')
            WHERE lt.id = :trackid",['trackid' => $trackid]);
        }
        return $competencylist;
    }

    public function is_enrolled($item, $itemtype, $userid) {
        global $DB;
        if($itemtype == product::TRAINING_PROGRAM){

            if((new \local_trainingprogram\local\trainingprogram)->is_enrolled($item, $userid)){
                return true;
            }

        }else if($itemtype == product::EXAMS){

            if((new \local_exams\local\exams)->is_enrolled($item, $userid)){
                return true;
            }
        }
    }
    public function map_enrolled_records($item, $itemtype, $orgid=null) {
        global $DB;
        if($itemtype == product::TRAINING_PROGRAM){

            $sql = 'SELECT pe.id,pe.userid,pe.programid,pe.offeringid 
                 FROM {program_enrollments} as pe                        
                 JOIN {tp_offerings} as tpo ON pe.offeringid = tpo.id 
                 WHERE pe.programid=:programid ';
                if($orgid > 0) {
                    $sql .= ' AND tpo.organization = '.$orgid;
                } else {
                    $sql .= ' AND tpo.type = 2';
                }
            $enrolledrecords = $DB->get_records_sql($sql, ['programid' => $item]);
            
            foreach($enrolledrecords as $enrolledrecord){

                $this->program_enrolment($enrolledrecord->programid,$enrolledrecord->offeringid, $roleid=5,$enrolledrecord->userid);

            }
      

        }else if($itemtype == product::EXAMS){


            $sql = 'SELECT ee.id,ee.userid,ee.examid,ee.profileid FROM {exam_enrollments} as ee 
                     WHERE ee.examid=:examid';

            $enrolledrecords = $DB->get_records_sql($sql, ['examid' => $item]);

            foreach($enrolledrecords as $enrolledrecord){

                $this->exam_enrolment($enrolledrecord->examid,$enrolledrecord->profileid, $roleid=5,$enrolledrecord->userid);

            }

        }
    }

    public function learningtrackfakeblock() { 
        global $DB, $PAGE, $OUTPUT, $CFG, $USER;
        $systemcontext = context_system::instance();
        $bc = new \block_contents();
        if(!is_siteadmin() && (has_capability('local/organization:manage_trainee', $systemcontext) )) {
            $bc->title = get_string('mylearningtracks','local_learningtracks');
            $bc->attributes['class'] = 'my_training';
            $renderer = $PAGE->get_renderer('local_learningtracks');
            $bc->content =  (new learningtracks)->trainee_learningtrack_data();
            $PAGE->blocks->add_fake_block($bc, 'content');
        }
    }

    public function trainee_learningtrack_data() {
        global $DB,$OUTPUT, $PAGE,$CFG;
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = 0;
        $stable->length = 5;
        $data = array();
        $tracks = learningtracks::get_enrolled_learningpath($stable, null); 
        $renderer = $PAGE->get_renderer('local_learningtracks');
        $data['mytracks'] = array_merge($data, $renderer->my_learning_tracks($tracks['tracks']));
        $data['viewmoreurl'] = $CFG->wwwroot."/local/learningtracks/learningpath.php";
        $data['viewmoreurldisplay'] =COUNT($data['mytracks']) > 0 ? true :false;
        return $OUTPUT->render_from_template('local_learningtracks/learningtrack_block', $data);
    }

    public function get_listof_enrolledcourses($trackid, $userid, $status='', $itemid='', $itemtype=''){

        global $DB;

        $usercount = 0;
  

        $countsql =" SELECT count(li.id) ";

        $sql = " SELECT li.* ";

        $fromsql = " FROM {local_lts_item_enrolment} li WHERE trackid = $trackid AND userid = $userid";


        if($status || $status == '0'  ) {
            $fromsql .= ($status == 1) ?  " AND li.status IN(1,2)" : " AND li.status = $status";
        } 
        $order = ' ORDER BY li.id ASC';
        if($itemid) {
            $fromsql .= " AND li.itemid = $itemid  AND li.itemtype = $itemtype";
            $usercount = $DB->get_record_sql($sql.$fromsql.$order);
        } else {
            $usercount = $DB->count_records_sql($countsql.$fromsql.$order);
        }
            
        return $usercount;
    }

    public function completed_items_count($trackid) {
        global $DB;
        $count_sql = " SELECT COUNT(ltiten.itemid) FROM {local_lts_item_enrolment} as ltiten 
        WHERE ltiten.trackid= $trackid and ltiten.status=1 ";
        $count = $DB->count_records_sql($count_sql);
        return $count;
    }

    public function trackitem_completion_status($trackid, $itemid, $itemtype) {
        global $DB;
        $sql = " SELECT ltiten.itemid,ltiten.itemtype,COUNT(ltiten.itemid) enroluerscount,(SELECT COUNT(ltiten1.itemid) FROM {local_lts_item_enrolment} as ltiten1 where ltiten1.trackid=ltiten.trackid AND ltiten1.itemid=ltiten.itemid and ltiten1.status IN(1,2)) as compltuerscount FROM {local_lts_item_enrolment} as ltiten 
        WHERE ltiten.trackid=$trackid AND ltiten.itemid = $itemid AND ltiten.itemtype = $itemtype 
        GROUP BY ltiten.itemid, ltiten.itemtype";
        $record = $DB->get_record_sql($sql);
        return $record;
    }

    public function is_current_user_enrolled_to_learningtracks() {
        global $DB,$USER;

        $sql = 'SELECT le.id FROM {local_lts_enrolment} as le 
                JOIN {local_learningtracks} as lt ON lt.id = le.trackid 
                WHERE le.userid =:userid ';
        $enrolled = $DB->record_exists_sql($sql, ['userid' => $USER->id]);
        if($enrolled){
            return true;
        }
        return false;

    }
    public static function get_total_learningitems($trackid) {
        global $DB, $USER;
        $params           = array();
        $countsql = "SELECT COUNT(li.id) ";
        $sql      = " FROM {local_learning_items} AS li
                      JOIN {local_learningtracks} AS lt ON li.trackid = lt.id WHERE lt.id = :trackid";             
        $params['trackid'] = $trackid;
        
        $learningitemscount = $DB->count_records_sql($countsql . $sql, $params);
     
    
        return $learningitemscount;
    }

    public static function learningtracksinfo($trackid, $isArabic) {
        global $DB, $SESSION;
        $SESSION->lang = ($isArabic == 'true') ? 'ar' : 'en';
        $track = $DB->get_record('local_learningtracks', ['id' => $trackid]);
        $row = [];
        $row['id'] = $track->id;
        $row['titleAr'] = $track->namearabic;
        $row['titleEn'] = $track->name;
        $row['code'] = $track->code;
        $row['expirationTimeInYears'] = NULL;
        $row['name'] = $track->namearabic;

        // For Learning type Exams
        $trackitems = $DB->get_fieldset_sql("SELECT itemid FROM {local_learning_items} WHERE itemtype = 2 AND trackid=".$trackid );
        $examitems = [];
        foreach($trackitems as $trackitem) {
            $exam = $DB->get_record('local_exams', ['id' => $trackitem]);
            $items = [];
            if ($SESSION->lang == 'ar') {
                $items['name'] = $exam->examnamearabic;
            } else {
                $items['name'] = $exam->exam;
            }
            $items['description'] = format_text($exam->programdescription, FORMAT_HTML);
            $items['value'] = $exam->id;
            $examitems[] = $items;
        }
        $row['certificatesExams'] = $examitems;

        // For Learning type Training programs
        $trackitems = $DB->get_fieldset_sql("SELECT itemid FROM {local_learning_items} WHERE itemtype = 1 AND trackid=".$trackid );
        $programitems = [];
        foreach($trackitems as $trackitem) {
            $program = $DB->get_record('local_trainingprogram', ['id' => $trackitem]);
            $items = [];
            if ($SESSION->lang == 'ar') {
                $items['name'] = $program->namearabic;
            } else {
                $items['name'] = $program->name;
            }
            $items['description'] = format_text($program->description, FORMAT_HTML);
            $items['value'] = $program->id;
            $items['attachmentId'] = $program->image;
            $$programitems[] = $items;
        }
        $row['certificatesTrainingCourses'] = $$programitems;

        return $row;
    }
    
    public static function get_alllearningtracks($stable) {
        global $DB, $PAGE, $OUTPUT, $CFG, $SESSION;
        $SESSION->lang =($stable->isArabic == 'true')?'ar':'en';
        $selectsql = "SELECT * FROM {local_learningtracks} lo "; 
        $countsql  = "SELECT COUNT(lo.id) FROM {local_learningtracks} lo  ";
        $formsql = " ORDER BY lo.id DESC";
        $params = array_merge($searchparams);
        $totallearningtracks = $DB->count_records_sql($countsql.$formsql,$params);
        $learningtracks = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $learningtrackslist = array();
        $count = 0;
        foreach($learningtracks as $learningtrack) {
  
               $learningtrackslist[$count]['id'] =$learningtrack->id;
                $learningtrackslist[$count]['Name'] =($SESSION->lang == 'ar')?$learningtrack->namearabic : $learningtrack->name ;
                $learningtrackslist[$count]['Description'] =strip_tags(format_text($learningtrack->description, FORMAT_HTML));
               $learningtrackslist[$count]['detailsPageURL'] =$CFG->wwwroot.'/local/learningtracks/view.php=id'.$learningtrack->id;
               $learningtrackslist[$count]['Logo']= (!empty($learningtrack->logo)) ? tracklogo_url($learningtrack->logo) : get_string('no_logo','local_learningtracks');

               $trackscount = $DB->count_records_sql('SELECT COUNT(id) FROM {local_learning_items} WHERE trackid=:trackid',['trackid'=>$learningtrack->id]);
               $learningtrackslist[$count]['LearningItems'] = $trackscount;
              
            $count++;
        }
        $coursesContext = array(
            "learningtracks" => $learningtrackslist,
            "nocourses" => $nocourse,
            "totallearningtracks" => $totallearningtracks,
            "length" => count($learningtrackslist)

        );
        return $coursesContext;
    
    }

     public function get_current_track_status($trackid,$userid) {
        global $DB,$USER;

    
        $record = $DB->record_exists_sql( "SELECT * FROM {local_lts_enrolment} 
                     WHERE  trackid=$trackid AND userid=$userid and status IN(1,2)");

        return $record;
    }

    public function alltracks() {
        global $PAGE;
        $renderer = $PAGE->get_renderer('local_learningtracks');
        $filterparams  = $renderer->get_content(true);
        $filterparams['widthclass'] = 'col-md-12';
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-12';
        $filterparams['placeholder'] = get_string('search_requested','local_learningtracks');
        $globalinput=$renderer->global_filter($filterparams);
        $trackdetails = $renderer->get_content();
        $filterparams['trackdetails'] = $trackdetails;
        $filterparams['globalinput'] = $globalinput;
        $renderer->listoftracks($filterparams);
        
    }

    public function get_listof_tracks($stable, $filterdata) {
        global $DB, $CFG, $USER;
        $systemcontext = context_system::instance();
    
        $selectsql = "SELECT * FROM {local_learningtracks} ll "; 
        $countsql  = "SELECT COUNT(ll.id) FROM {local_learningtracks} ll  ";

        $formsql =" WHERE 1=1 ";
        if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
            $organization = $DB->get_field('local_users','organization',array('userid'=>$USER->id));
            $formsql .= " AND ll.id IN (SELECT lts.trackid FROM {local_lts_enrolment} lts 
                          LEFT JOIN {user} u ON lts.userid = u.id 
                          LEFT JOIN {local_users} lu ON lu.userid = u.id ";
            $formsql .= " WHERE lts.trackid = ll.id AND u.confirmed = 1 AND u.deleted = 0 AND u.id > 2 )";
        } 
        if  (isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
            $formsql .= " AND (ll.name LIKE :namesearch OR 
                            ll.code LIKE :codesearch 
                        ) ";
            $searchparams = array(
                'namesearch' => '%'.trim($filterdata->search_query).'%',
                'codesearch' => '%'.trim($filterdata->search_query).'%',
           );
        } else {
            $searchparams = array();
        }
        $params = array_merge($searchparams);
        $totaltracks = $DB->count_records_sql($countsql.$formsql,$params);         
        $formsql .=" ORDER BY ll.id DESC";
        $tracks = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $trackslist = array();
        $count = 0;
        $lang= current_language();
        
        foreach($tracks as $track) {

            $trackitems =  (new learningtracks)->get_listof_learningitems($track->id, $stable);
            $learningitems_count = $trackitems['learningitemscount'];
            $trackusers = (new learningtracks)->get_listof_users($track->id, $stable);
            $enrollcount = $trackusers['userscount'];
            $completed_count = (new learningtracks)->completed_items_count($track->id);

            if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext) &&$enrollcount <=0 ){
              unset($track);
              continue;
            }
            $trackslist[$count]['code'] = $track->code;
            $trackslist[$count]["id"] = $track->id;
            if( $lang == 'ar' && !empty($track->namearabic)){
                $trackslist[$count]["name"] = $track->namearabic;
            }else{
                $trackslist[$count]["name"] = $track->name;
            }
            $trackslist[$count]['completed_count'] =  $completed_count;
            $trackslist[$count]['nolearningitems'] =  $learningitems_count;
            $trackslist[$count]['enrollcount'] =  $enrollcount;
            $statusarry = array(0 =>get_string('pending','local_learningtracks'), 1 => get_string('approve','local_learningtracks'), 2 => get_string('completed','local_learningtracks'), 3 => get_string('pending','local_learningtracks'));
            $trackslist[$count]['status'] = $statusarry[$track->status];
            $trackslist[$count]['action'] = false;
            $trackslist[$count]['delete'] = false;
            $trackslist[$count]['edit'] = false;
            $trackslist[$count]['view'] = false;
      
            if(is_siteadmin() || has_capability('local/learningtracks:editlearningtracks', $systemcontext)) {
                $trackslist[$count]['edit'] = true;
                $trackslist[$count]['action'] = true;
            }
            if(is_siteadmin() || has_capability('local/learningtracks:deletelearningtracks', $systemcontext)) {
                $trackslist[$count]['delete'] = true;
                $trackslist[$count]['action'] = true;
            }
            $trackslist[$count]['viewurl'] = $CFG->wwwroot."/local/learningtracks/view.php?id=".$track->id;
            if(is_siteadmin() || has_capability('local/learningtracks:viewlearningtracks', $systemcontext)) {
                $trackslist[$count]['view'] = true;
                $trackslist[$count]['action'] = true;
               
            }
            $count++;

        }
        
        $coursesContext = array(
            "hascourses" => $trackslist,
            "totaltracks" => $totaltracks,
            "length" => count($trackslist)
        );
        return $coursesContext;
    }

}
