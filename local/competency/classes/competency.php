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
 * @package    local_competency
 * @copyright  2022 e abyas  <info@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_competency;

use coding_exception;
use context_helper;
use context_system;
use core\invalid_persistent_exception;
use core\message\message;
use core_user;
use dml_exception;
use moodle_exception;
use moodle_url;
use required_capability_exception;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Class containing helper methods for processing data requests.
 *
 * @copyright  2018 Jun Pataleta
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class competency {

    const CORECOMPETENCIES = 'corecompetencies';
    const TECHNICALCOMPETENCIES = 'technicalcompetencies';
    const BEHAVIORALCOMPETENCIES = 'behavioralcompetencies';

    const PERFORMANCECRITERIA1= 'performancecriteria1';
    const PERFORMANCECRITERIA2 = 'performancecriteria2';
    const PERFORMANCECRITERIA3 = 'performancecriteria3';
    const PERFORMANCECRITERIA4 = 'performancecriteria4';
    const PERFORMANCECRITERIA5 = 'performancecriteria5';

    const KPI1= 'kpi1';
    const KPI2 = 'kpi2';
    const KPI3 = 'kpi3';
    const KPI4 = 'kpi4';
    const KPI5 = 'kpi5';

    const OBJECTIVE1= 'objective1';
    const OBJECTIVE2 = 'objective2';
    const OBJECTIVE3 = 'objective3';
    const OBJECTIVE4 = 'objective4';
    const OBJECTIVE5 = 'objective5';

    const OTHER = 'other';

    const LEVEL1= 'level1';
    const LEVEL2 = 'level2';
    const LEVEL3 = 'level3';
    const LEVEL4 = 'level4';
    const LEVEL5 = 'level5';

 

    public static function constcompetencytypes() {

        $competencytypes = array(
            self::CORECOMPETENCIES => get_string('corecompetencies','local_competency'),
            self::TECHNICALCOMPETENCIES => get_string('technicalcompetencies','local_competency'),
            self::BEHAVIORALCOMPETENCIES => get_string('behavioralcompetencies','local_competency')
            );


        return $competencytypes;
    }

    public static function constperformancecriterias() {

        $performancecriterias = array(
                    self::PERFORMANCECRITERIA1 => get_string('performancecriteria1','local_competency'),
                    self::PERFORMANCECRITERIA2 => get_string('performancecriteria2','local_competency'),
                    self::PERFORMANCECRITERIA3 => get_string('performancecriteria3','local_competency'),
                    self::PERFORMANCECRITERIA4 => get_string('performancecriteria4','local_competency'),
                    self::PERFORMANCECRITERIA5 => get_string('performancecriteria5','local_competency')
                    );


        return $performancecriterias;
    }

    public static function constkpis() {

        $kpis = array(
            self::KPI1 => get_string('kpi1','local_competency'),
            self::KPI2 => get_string('kpi2','local_competency'),
            self::KPI3 => get_string('kpi3','local_competency'),
            self::KPI4 => get_string('kpi4','local_competency'),
            self::KPI5 => get_string('kpi5','local_competency')
            );


        return $kpis;
    }

    public static function constobjectives() {

        $objectives = array(
                    self::OBJECTIVE1 => get_string('objective1','local_competency'),
                    self::OBJECTIVE2 => get_string('objective2','local_competency'),
                    self::OBJECTIVE3 => get_string('objective3','local_competency'),
                    self::OBJECTIVE4 => get_string('objective4','local_competency'),
                    self::OBJECTIVE5 => get_string('objective5','local_competency')
                    );


        return $objectives;
    }
    public static function constcompetencylevels() {

        $kpis = array(
            self::LEVEL1 => get_string('level1','local_competency'),
            self::LEVEL2 => get_string('level2','local_competency'),
            self::LEVEL3 => get_string('level3','local_competency'),
            self::LEVEL4 => get_string('level4','local_competency'),
            self::LEVEL5 => get_string('level5','local_competency')
            );


        return $kpis;
    }

    public static function userjobrolelevelinfo() {

        global $DB, $USER;

        $currentlang= current_language();

        if( $currentlang == 'ar'){

            $titlefield='jbrl.titlearabic';

        }else{

            $titlefield='jbrl.title';
        }

        $levels = array(1=>'level1',2=>'level2',3=>'level3',4=>'level4',5=>'level5');


        $userinfo=$DB->get_record_sql("SELECT jbrl.id as currentjobroleid,$titlefield as currentjobrole,jbrl.level as currentjobrolelevel,jbrl.jobfamily,ud.jobrole_level 
                                       FROM {local_users} as ud
                                       JOIN {local_jobrole_level} as jbrl ON jbrl.id=ud.jobrole
                                       WHERE ud.userid=:userid",array('userid'=>$USER->id));


        $nextleveluserinfo=$DB->get_record_sql("SELECT jbrl.id as nextjobroleid,$titlefield as nextjobrole,jbrl.level as nextjobrolelevel  
                                                FROM {local_jobrole_level} as jbrl
                                                WHERE jbrl.jobfamily =:jobfamily AND jbrl.level=:level",array('jobfamily'=>$userinfo->jobfamily,'level'=> $levels[$userinfo->jobrole_level+1]));

                                        
        if($nextleveluserinfo){

            $userinfo=(object)array_merge((array)$userinfo,(array)$nextleveluserinfo);

        }

        return $userinfo;
    }

    public static function can_competency_datasubmit() {

        $context = \context_system::instance();

        if((has_capability('local/competency:managecompetencies', $context)) || (has_capability('local/competency:canaddcompetency', $context)) || (has_capability('local/organization:manage_competencies_official', $context))){

            return true;
        }

        return false;
    }
    public static function can_competencyperformance_datasubmit() {

        $context = \context_system::instance();

        if((has_capability('local/competency:managecompetencies', $context)) || (has_capability('local/competency:canaddcompetencyperformance', $context)) || (has_capability('local/organization:manage_competencies_official', $context))){

            return true;
        }

        return false;
    }
    public static function can_competencyobjective_datasubmit() {

        $context = \context_system::instance();

        if((has_capability('local/competency:managecompetencies', $context)) || (has_capability('local/competency:canaddcompetencyobjectives', $context)) || (has_capability('local/organization:manage_competencies_official', $context))){

            return true;
        }

        return false;
    }
    public static function can_competencylevel_datasubmit() {

        $context = \context_system::instance();

        if((has_capability('local/competency:managecompetencies', $context)) || (has_capability('local/competency:canaddcompetencyleveldescription', $context)) || (has_capability('local/organization:manage_competencies_official', $context))){

            return true;
        }

        return false;
    }
    public static function competency_datasubmit($formdata) {
        global $DB, $USER;
        
        try {

            $formdata->description=$formdata->description['text'];

            if(isset($formdata->type) && $formdata->type == self::OTHER){

                $formdata->type = $formdata->add_type;
            }

            $formdata->jobroleid =0;

            if(isset($formdata->level) && $formdata->level == self::OTHER){

                $formdata->level = $formdata->add_level;
            }


            if(isset($formdata->level) && !empty($formdata->level)){

                $formdata->level=is_array($formdata->level) ?implode(',',array_filter($formdata->level)):$formdata->level;

            }else{
                $formdata->level=NULL;
            }

            if(isset($formdata->oldid) && !empty($formdata->oldid)){

                $formdata->oldid=$formdata->oldid;

            }

            if($formdata->id > 0){

                $competencyleveldc=$DB->record_exists('local_cmtncy_level', ['competencyid' => $formdata->id]);

                //print_r($competencyleveldc);

                if($competencyleveldc){

                    $stable = new \stdClass();
                    $stable->competencyid = $formdata->id;
                    $stable->thead = false;
                    $stable->start = 0;
                    $stable->length = 1;
                    
                    $data=competency::get_competencies($stable);


                    if(isset($formdata->level) && !empty($formdata->level)){

                        $levelarrayone=is_array($formdata->level) ? $formdata->level : explode(',',$formdata->level) ;


                        $levelarraytwo=explode(',',$data->level);


                        $levelarrayresult=array_diff($levelarrayone,$levelarraytwo);


                        if(empty($levelarrayresult)){

                            $levelarrayresult=array_diff($levelarraytwo,$levelarrayone);

                        }

                        if(!empty($levelarrayresult)){

                            foreach($levelarrayresult as $levelid){


                                $ldcid = $DB->delete_records('local_cmtncy_level', ['competencyid' => $formdata->id,'levelid'=>$levelid]);

                            }

                        }

                    }else{

                        $ldcid = $DB->delete_records('local_cmtncy_level', ['competencyid' => $formdata->id]);

                    }

                }

                $formdata->timemodified=time();
                $formdata->usermodified=$USER->id;

                $id=$DB->update_record('local_competencies', $formdata);
                $params = array(
                    'context' => context_system::instance(),        
                    'objectid' => $formdata->id                         
                    );
                $event = \local_competency\event\competency_updated::create($params)->trigger();

                return  $formdata->id;

            }else{

                $formdata->timecreated=time();
                $formdata->usercreated=$USER->id;

                $id=$DB->insert_record('local_competencies', $formdata);
                $params = array(
                    'context' => context_system::instance(),        
                    'objectid' => $id                         
                    );
                $event = \local_competency\event\competency_created::create($params)->trigger();

                return  $id;
            }

        } catch (dml_exception $e) {
            print_error($e);

        }
        return true;
    }
    public static function competencylevel_datasubmit($formdata) {
        global $DB, $USER;
        
        try {

            $formdata->description=$formdata->description['text'];

            if($formdata->id > 0){

                $formdata->timemodified=time();
                $formdata->usermodified=$USER->id;

                $id=$DB->update_record('local_cmtncy_level', $formdata);
                $params = array(
                    'context' => context_system::instance(),        
                    'objectid' => $formdata->id                         
                    );
                $event = \local_competency\event\competencylevel_updated::create($params)->trigger();

                return  $formdata->id;

            }else{

                $formdata->timecreated=time();
                $formdata->usercreated=$USER->id;

                $id=$DB->insert_record('local_cmtncy_level', $formdata);
                $params = array(
                    'context' => context_system::instance(),        
                    'objectid' => $id                         
                    );
                $event = \local_competency\event\competencylevel_created::create($params)->trigger();

                return  $id;
            }

        } catch (dml_exception $e) {
            print_error($e);

        }
        return true;
    }
    public static function competencyobjective_datasubmit($formdata) {
        global $DB, $USER,$CFG;

        try {


            if(isset($formdata->objjobrolelevels) && !empty($formdata->objjobrolelevels)){

                $learningitemtype='jobrolelevel';

                $formdata->jobrolelevelids=implode(',',array_unique(array_filter($formdata->objjobrolelevels)));

            }else{
                $formdata->jobrolelevelids=NULL;
            }

            if(isset($formdata->objexams) && !empty($formdata->objexams)){

                $learningitemtype='exam';

                $learningitemid=array_unique(array_filter($formdata->objexams));

                $formdata->examids=implode(',',$learningitemid);

            }else{
                $formdata->examids=NULL;
            }

            if(isset($formdata->objtrainingprograms) && !empty($formdata->objtrainingprograms)){

                $learningitemtype='trainingprogram';

                $learningitemid=array_unique(array_filter($formdata->objtrainingprograms));

                $formdata->trainingprogramids=implode(',',$learningitemid);

            }else{
                $formdata->trainingprogramids=NULL;
            }

            if(isset($formdata->objquestions) && !empty($formdata->objquestions)){

                $learningitemtype='question';

                $learningitemid=array_unique(array_filter($formdata->objquestions));

                $formdata->questionids=implode(',',$learningitemid);

            }else{
                $formdata->questionids=NULL;
            }

            if(isset($formdata->oldid) && !empty($formdata->oldid)){

                $formdata->oldid=$formdata->oldid;

            }

            $sql ="SELECT cpcbj.id,cpcbj.examids,cpcbj.trainingprogramids,cpcbj.questionids,cpcbj.jobrolelevelids FROM {local_competencypc_obj} AS cpcbj 
                WHERE cpcbj.competencypc=:competencypc AND cpcbj.competency=:competency"; 

            $competencypcobj=$DB->get_record_sql($sql, ['competencypc' => $formdata->competencypc,'competency' => $formdata->competency]);


            if($competencypcobj){

                $formdata->id=$competencypcobj->id;

                if(isset($formdata->objtrainingprograms) && !empty($formdata->objtrainingprograms)){

                    $formdata->trainingprogramids=implode(',',array_unique(array_filter(array_merge(explode(',',$competencypcobj->trainingprogramids),$formdata->objtrainingprograms))));

                }else{
                    $formdata->trainingprogramids=$competencypcobj->trainingprogramids;
                }

                if(isset($formdata->objexams) && !empty($formdata->objexams)){
                    
                    $formdata->examids=implode(',',array_unique(array_filter(array_merge(explode(',',$competencypcobj->examids),$formdata->objexams))));

                }else{
                    $formdata->examids=$competencypcobj->examids;
                }

                if(isset($formdata->objquestions) && !empty($formdata->objquestions)){

                    $formdata->questionids=implode(',',array_unique(array_filter(array_merge(explode(',',$competencypcobj->questionids),$formdata->objquestions))));

                }else{
                    $formdata->questionids=$competencypcobj->questionids;
                }

                if(isset($formdata->objjobrolelevels) && !empty($formdata->objjobrolelevels)){


                    $formdata->jobrolelevelids=implode(',',array_unique(array_filter(array_merge(explode(',',$competencypcobj->jobrolelevelids),$formdata->objjobrolelevels))));

                }else{
                    $formdata->jobrolelevelids=$competencypcobj->jobrolelevelids;
                }

                $formdata->timemodified=time();
                $formdata->usermodified=$USER->id;

                $id=$DB->update_record('local_competencypc_obj', $formdata);
                $params = array(
                    'context' => context_system::instance(),        
                    'objectid' => $formdata->id                         
                    );
                $event = \local_competency\event\competency_obj_updated::create($params)->trigger();
                $formdata->competency_link=$CFG->wwwroot.'local/competency/index.php?id='.$formdata->competency;

                if($learningitemtype !='jobrolelevel'){

                    $formdata->learningitemtype=$learningitemtype;

                    $formdata->learningitemid=$learningitemid;

                   $formdata->competency_name=$DB->get_field('local_competencies','name',array('id'=>$formdata->competency));

                   (new \local_competency\notification())->competency_notification('competency_adding_learning_item', $touser=get_admin(),$fromuser=get_admin(),$formdata,$waitinglistid=0);
                }


            }else{

                $formdata->timecreated=time();
                $formdata->usercreated=$USER->id;

                $id=$DB->insert_record('local_competencypc_obj', $formdata);
                $params = array(
                    'context' => context_system::instance(),        
                    'objectid' => $id                         
                    );
                $event = \local_competency\event\competency_obj_created::create($params)->trigger();


                if($learningitemtype !='jobrolelevel'){


                    $formdata->learningitemtype=$learningitemtype;

                    $formdata->learningitemid=$learningitemid;

                    $formdata->competency_name=$DB->get_field('local_competencies','name',array('id'=>$formdata->competency));

                    (new \local_competency\notification())->competency_notification('competency_adding_learning_item', $touser=get_admin(),$fromuser=get_admin(),$formdata,$waitinglistid=0);
                }



            }

        } catch (dml_exception $e) {
            print_error($e);

        }
        return true;
    }
    public static function competencypc_datasubmit($formdata) {
        global $DB, $USER;

        try {

            if(isset($formdata->criterianame) && $formdata->criterianame == self::OTHER){

                $formdata->criterianame = $formdata->add_criterianame;
            }

            if(isset($formdata->kpiname) && $formdata->kpiname == self::OTHER){

                $formdata->kpiname = $formdata->add_kpiname;
            }

            if(isset($formdata->objectiveid) && $formdata->objectiveid == self::OTHER){

                $formdata->objectiveid = $formdata->add_objectiveid;
            }

            if(isset($formdata->oldid) && !empty($formdata->oldid)){

                $formdata->oldid=$formdata->oldid;

            }

            if($formdata->id > 0){

                $formdata->timemodified=time();
                $formdata->usermodified=$USER->id;

                $id=$DB->update_record('local_competency_pc', $formdata);
                $params = array(
                    'context' => context_system::instance(),        
                    'objectid' => $formdata->id                        
                    );
                $event = \local_competency\event\competencypc_updated::create($params)->trigger();

                return  $formdata->id;

            }else{

                $formdata->timecreated=time();
                $formdata->usercreated=$USER->id;

                $id=$DB->insert_record('local_competency_pc', $formdata);
                $params = array(
                    'context' => context_system::instance(),        
                    'objectid' => $id                         
                    );
                $event = \local_competency\event\competencypc_created::create($params)->trigger();

                return  $id;
            }

        } catch (dml_exception $e) {
            print_error($e);

        }
        return true;
    }
    public static function get_competencies($stable,$filterdata=null) {
        global $DB, $USER;

        $params          = array();
        $competencies      = array();
        $competenciescount = 0;
        $concatsql       = '';

         $currentlang= current_language();

        if($currentlang == 'ar'){

            $titlefield='cmt.arabicname';

        }else{

            $titlefield='cmt.name';
        }

        if (!empty($filterdata->search_query)) {

            $concatsql .= " AND (cmt.name LIKE :search1 OR cmt.arabicname LIKE :search2 OR cmt.code LIKE :search3) ";

            $params = array('search1' => '%'.trim($filterdata->search_query).'%','search2' => '%'.trim($filterdata->search_query).'%','search3' => '%'.trim($filterdata->search_query).'%');
        }

        if (isset($stable->competencyid) && $stable->competencyid > 0) {
            $concatsql .= " AND cmt.id = :competencyid";
            $params['competencyid'] = $stable->competencyid;
        }

        $countsql = "SELECT COUNT(cmt.id) ";
        


        if (isset($stable->questionid) && $stable->questionid > 0) {

            $params['questionid'] = $stable->questionid;

            $fromsql = "SELECT cmt.*,cmt.name as formname,$titlefield as name ,q.questiontext,qbcmt.questionbankid ";

            $sql = " FROM {local_competencies} AS cmt
                     JOIN {local_questioncompetencies} AS qbcmt ON FIND_IN_SET(cmt.id,qbcmt.competency) > 0
                     JOIN {question} q ON q.id=qbcmt.questionid
                     WHERE q.id=:questionid ";

        }else{

            $fromsql = "SELECT cmt.*,cmt.name as formname,$titlefield as name ";

            $sql = " FROM {local_competencies} AS cmt
                    WHERE cmt.id > 0 ";

        }

        $sql .= $concatsql;

        if(!empty($filterdata->type)){

            if(!empty($filterdata->type)){
                $typelist = explode(',',$filterdata->type);
            }
    
            list($relatedtypelistsql, $relatedtypelistparams) = $DB->get_in_or_equal($typelist, SQL_PARAMS_NAMED, 'typelist');
            $params = array_merge($params,$relatedtypelistparams);
            $sql .= " AND cmt.type $relatedtypelistsql";

        }

        
        if(!empty($filterdata->competencytype)){

            if(!empty($filterdata->competencytype)){
                $typelist = explode(',',$filterdata->competencytype);
            }
    
            list($relatedtypelistsql, $relatedtypelistparams) = $DB->get_in_or_equal($typelist, SQL_PARAMS_NAMED, 'typelist');
            $params = array_merge($params,$relatedtypelistparams);
            $sql .= " AND cmt.type $relatedtypelistsql";

        }

        if(!empty($filterdata->level)){

            if(!empty($filterdata->level)){
                $levellist = explode(',',$filterdata->level);
            }
    
            list($relatedlevellistsql, $relatedlevellistparams) = $DB->get_in_or_equal($levellist, SQL_PARAMS_NAMED, 'levellist');
            $params = array_merge($params,$relatedlevellistparams);
            $sql .= " AND cmt.level $relatedlevellistsql";

        }

        if (isset($stable->competencyid) && $stable->competencyid > 0) {
            $competencies = $DB->get_record_sql($fromsql . $sql, $params);
        } else {
            try {
                
                $competenciescount = $DB->count_records_sql($countsql . $sql, $params);

                if ($stable->thead == false) {
                    $sql .= " ORDER BY cmt.id DESC";

                    $competencies = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
                }
            } catch (dml_exception $ex) {
                $competenciescount = 0;
            }
        }
        if (isset($stable->competencyid) && $stable->competencyid > 0) {
            return $competencies;
        } else {
            return compact('competencies', 'competenciescount');
        }
    }
    public static function get_competency_levelinfo($stable,$filterdata=null) {
        global $DB, $USER;

        $params          = array();
        $competencyld      = array();
        $competencyldcount = 0;
        $concatsql       = '';

        if (!empty($filterdata->search_query)) {

            $concatsql .= " AND (cmt.name LIKE :search1 OR cmld.level LIKE :search2 OR cmld.description LIKE :search3) ";

            $params = array('search1' => '%'.trim($filterdata->search_query).'%','search2' => '%'.trim($filterdata->search_query).'%','search3' => '%'.trim($filterdata->search_query).'%');
        }

        if (isset($stable->competencyid) && $stable->competencyid > 0) {
            $concatsql .= " AND cmld.competencyid = :competencyid";
            $params['competencyid'] = $stable->competencyid;
        }

        if (isset($stable->levelid) && !empty($stable->levelid)) {
            $concatsql .= " AND cmld.levelid = :levelid";
            $params['levelid'] = $stable->levelid;
        }

        if (isset($stable->id) && $stable->id > 0) {
            $concatsql .= " AND cmld.id = :id";
            $params['id'] = $stable->id;
        }


        $countsql = "SELECT COUNT(cmld.id) ";


        $fromsql = "SELECT cmld.*,cmt.name ";
        $sql = " FROM {local_cmtncy_level} AS cmld
                     JOIN {local_competencies} AS cmt ON cmt.id=cmld.competencyid
                    WHERE cmld.id > 0 "; 
        

        $sql .= $concatsql;

        if ((isset($stable->id) && $stable->id > 0) || (isset($stable->competencyid) && $stable->competencyid > 0 && isset($stable->levelid) && !empty($stable->levelid))) {

            $competencyld = $DB->get_record_sql($fromsql . $sql, $params);

        } else {

            try {

                $competencyldcount = $DB->count_records_sql($countsql . $sql, $params);
                if ($stable->thead == false) {
                    $sql .= " ORDER BY cmld.id DESC";

                    $competencyld = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
                }

            } catch (dml_exception $ex) {

                $competencyldcount = 0;

            }

        }
        if (isset($stable->id) && $stable->id > 0) {
            return $competencyld;
        } else {
            return compact('competencyld', 'competencyldcount');
        }
    }
    public static function get_competency_performances($stable,$filterdata=null) {
        global $DB, $USER;

        $params          = array();
        $competencies      = array();
        $competenciescount = 0;
        $concatsql       = '';

        $currentlang= current_language();

        if($currentlang == 'ar'){

            $cmtitlefield='cmt.arabicname';

            $crtitlefield='cmpc.criterianamearabic';

            $kptitlefield='cmpc.kpinamearabic';

            $objtitlefield='cmpc.objectiveidarabic';

        }else{

            $cmtitlefield='cmt.name';

            $crtitlefield='cmpc.criterianame';

            $kptitlefield='cmpc.kpiname';

            $objtitlefield='cmpc.objectiveid';
        }


        if (!empty($filterdata->search_query)) {

            $concatsql .= " AND (cmt.name LIKE :search1 OR cmt.arabicname LIKE :search2 OR cmpc.criterianame LIKE :search3 OR cmpc.criterianamearabic LIKE :search4 OR cmpc.kpiname LIKE :search5 OR cmpc.kpinamearabic LIKE :search6 OR cmpc.objectiveid LIKE :search7 OR cmpc.objectiveidarabic LIKE :search8) ";

            $params = array('search1' => '%'.trim($filterdata->search_query).'%','search2' => '%'.trim($filterdata->search_query).'%','search3' => '%'.trim($filterdata->search_query).'%','search4' => '%'.trim($filterdata->search_query).'%','search5' => '%'.trim($filterdata->search_query).'%','search6' => '%'.trim($filterdata->search_query).'%','search7' => '%'.trim($filterdata->search_query).'%','search8' => '%'.trim($filterdata->search_query).'%');
        }

        if (isset($stable->competencyid) && $stable->competencyid > 0) {
            $concatsql .= " AND cmpc.competency = :competencyid";
            $params['competencyid'] = $stable->competencyid;
        }

        if (isset($stable->id) && $stable->id > 0) {
            $concatsql .= " AND cmpc.id = :id";
            $params['id'] = $stable->id;
        }


        $countsql = "SELECT COUNT(cmpc.id) ";


        if (isset($stable->questionid) && $stable->questionid > 0) {

            $params['questionid'] = $stable->questionid;

            $fromsql = "SELECT cmpc.*,$cmtitlefield as competencyname,$crtitlefield,$kptitlefield,$objtitlefield ,q.questiontext";

            $sql = " FROM {local_competencypc_obj} AS cmpobj
                     JOIN {local_competency_pc} AS cmpc ON cmpc.id=cmpobj.competencypc 
                     JOIN {local_competencies} AS cmt ON cmt.id=cmpc.competency
                     JOIN {local_questioncompetencies} AS qbcmt ON FIND_IN_SET(cmt.id,qbcmt.competency) > 0
                     JOIN {question} q ON q.id=qbcmt.questionid
                     WHERE q.id=:questionid AND FIND_IN_SET(q.id,cmpobj.questionids) > 0";

        }else{

            $fromsql = "SELECT cmpc.*,$cmtitlefield as competencyname,$crtitlefield,$kptitlefield,$objtitlefield ";
            $sql = " FROM {local_competency_pc} AS cmpc
                     JOIN {local_competencies} AS cmt ON cmt.id=cmpc.competency
                    WHERE cmpc.id > 0 "; 
        }

        $sql .= $concatsql;

        if (isset($stable->id) && $stable->id > 0) {
            $competencypc = $DB->get_record_sql($fromsql . $sql, $params);
        } else {
            try {

                $competencypccount = $DB->count_records_sql($countsql . $sql, $params);
                if ($stable->thead == false) {
                    $sql .= " ORDER BY cmpc.id DESC";

                    $competencypc = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
                }
            } catch (dml_exception $ex) {
                $competencypccount = 0;
            }
        }
        if (isset($stable->id) && $stable->id > 0) {
            return $competencypc;
        } else {
            return compact('competencypc', 'competencypccount');
        }
    }
    public static function get_competency_objective($stable,$filterdata=null) {
        global $DB, $USER;

        $params          = array();
        $competencies      = array();
        $competenciescount = 0;
        $concatsql       = '';

        $currentlang= current_language();

         if($currentlang == 'ar'){

            $crtitlefield='cmpc.criterianamearabic';

            $kptitlefield='cmpc.kpinamearabic';

            $objtitlefield='cmpc.objectiveidarabic';

        }else{

           $crtitlefield='cmpc.criterianame';

           $kptitlefield='cmpc.kpiname';

           $objtitlefield='cmpc.objectiveid';
        }


        if (!empty($filterdata->search_query)) {

            $concatsql .= " AND (cmpc.criterianame LIKE :search3 OR cmpc.criterianamearabic LIKE :search4 OR cmpc.kpiname LIKE :search5 OR cmpc.kpinamearabic LIKE :search6 OR cmpc.objectiveid LIKE :search7 OR cmpc.objectiveidarabic LIKE :search8) ";

            $params = array('search3' => '%'.trim($filterdata->search_query).'%','search4' => '%'.trim($filterdata->search_query).'%','search5' => '%'.trim($filterdata->search_query).'%','search6' => '%'.trim($filterdata->search_query).'%','search7' => '%'.trim($filterdata->search_query).'%','search8' => '%'.trim($filterdata->search_query).'%');
        }


        if (isset($stable->competencyid) && $stable->competencyid > 0) {
            $concatsql .= " AND cmpc.competency = :competencyid";
            $params['competencyid'] = $stable->competencyid;
        }


        if (isset($stable->id) && $stable->id > 0) {
            $concatsql .= " AND cmobj.id = :id";
            $params['id'] = $stable->id;
        }


        $countsql = "SELECT COUNT(cmobj.id) ";
        $fromsql = "SELECT cmobj.* ,$crtitlefield as criterianame,$kptitlefield as kpiname,$objtitlefield as objectiveid ";
        $sql = " FROM {local_competency_pc} AS cmpc
                 JOIN {local_competency_obj} AS cmobj ON cmpc.objectiveid=cmobj.objective
                WHERE cmobj.id > 0 "; 

        $sql .= $concatsql;

        if (isset($stable->id) && $stable->id > 0) {
            $competencyobj = $DB->get_record_sql($fromsql . $sql, $params);
        } else {
            try {

                $competencyobjcount = $DB->count_records_sql($countsql . $sql, $params);
                if ($stable->thead == false) {
                    $sql .= " ORDER BY cmobj.id DESC";

                    $competencyobj = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
                }
            } catch (dml_exception $ex) {
                $competencyobjcount = 0;
            }
        }
        if (isset($stable->id) && $stable->id > 0) {
            return $competencyobj;
        } else {
            return compact('competencyobj', 'competencyobjcount');
        }
    }

     public static function competency_types($searchparams='',$limitfrom=0, $limitnum=0) {

        global $DB, $USER;

        $competencytypes = self::constcompetencytypes();


        list($relatedcompetencytypessql, $relatedcompetencytypesparams) = $DB->get_in_or_equal(array_flip($competencytypes), SQL_PARAMS_NAMED, 'competencytypes',false);
        $params = $relatedcompetencytypesparams;

        $sql = "SELECT cmt.type,cmt.type as fullname FROM {local_competencies} AS cmt
                WHERE cmt.type $relatedcompetencytypessql ";

        if (!empty($searchparams)) {

            $sql .= " AND (cmt.type LIKE :search )";
            $params['search'] = '%' . $searchparams . '%';

        }

        $competencietypes=$DB->get_records_sql_menu($sql,$params,$limitfrom,$limitnum);

        if (!empty($searchparams)) {

            return $competencietypes;
        }

        $competencietypes[self::OTHER] = get_string('other','local_competency');
            
        return array_merge($competencytypes,$competencietypes);
    }

    public static function competency_levels($searchparams,$limitfrom=0, $limitnum=0) {

        global $DB, $USER;

        $competencylevels = self::constcompetencylevels();

        return $competencylevels;


        // list($relatedcompetencylevelssql, $relatedcompetencylevelsparams) = $DB->get_in_or_equal(array_flip($competencylevels), SQL_PARAMS_NAMED, 'competencylevels',false);
        // $params = $relatedcompetencylevelsparams;


        // $sql = "SELECT cmt.level,cmt.level as fullname FROM {local_competencies} AS cmt
        //         WHERE cmt.level $relatedcompetencytypessql ";

        // if (!empty($searchparams)) {

        //     $sql .= " AND (cmt.level LIKE :search )";
        //     $params['search'] = '%' . $searchparams . '%';

        // }

        // $competencielevels=$DB->get_records_sql_menu($sql,$params,$limitfrom,$limitnum);

        // if (!empty($searchparams)) {

        //     return $competencielevels;
        // }

        // $competencielevels[self::OTHER] = get_string('other','local_competency');
            
        // return array_merge($competencylevels,$competencielevels);
            
    }
    public function delete_competency($competencyid) {

        global $DB;

        if ($competencyid > 0) {

            $id =$DB->delete_records('local_competencies', ['id' => $competencyid]);
            $params = array(
                'context' => context_system::instance(),        
                'objectid' => $id                         
                );
            $event = \local_competency\event\competency_deleted::create($params)->trigger();
            $competencypc=$DB->record_exists('local_competency_pc', ['competency' => $competencyid]);

            if($competencypc){

                $pcid = $DB->delete_records('local_competency_pc', ['competency' => $competencyid]);
                $params = array(
                    'context' => context_system::instance(),        
                    'objectid' => $pcid                         
                    );
                $event = \local_competency\event\competencypc_deleted::create($params)->trigger();
            }

            $competencypcobj=$DB->record_exists('local_competencypc_obj', ['competency' => $competencyid]);

            if($competencypcobj){

                $objid = $DB->delete_records('local_competencypc_obj', ['competency' => $competencyid]);
                $params = array(
                    'context' => context_system::instance(),        
                    'objectid' => $objid                         
                    );
                $event = \local_competency\event\competency_obj_deleted::create($params)->trigger();
            }

            $competencyleveldc=$DB->record_exists('local_cmtncy_level', ['competencyid' => $competencyid]);

            if($competencyleveldc){

                $ldcid = $DB->delete_records('local_cmtncy_level', ['competencyid' => $competencyid]);
            }

            return true;
        } 

        return false;
    }
    public static function competency_criteria($searchparams,$limitfrom=0, $limitnum=0) {

        global $DB, $USER;

        $currentlang= current_language();

         if($currentlang == 'ar'){

          $crtitlefield='cmt.criterianamearabic';

        }else{

           $crtitlefield='cmt.criterianame';

        }

        $performancecriterias = self::constperformancecriterias();;

        list($relatedperformancecriteriassql, $relatedperformancecriteriasparams) = $DB->get_in_or_equal(array_flip($performancecriterias), SQL_PARAMS_NAMED, 'performancecriterias',false);
        $params = $relatedperformancecriteriasparams;

        $sql = "SELECT cmt.criterianame,$crtitlefield as fullname FROM {local_competency_pc} AS cmt
                WHERE cmt.criterianame $relatedperformancecriteriassql ";

        if (!empty($searchparams)) {

            $sql .= " AND ($crtitlefield LIKE :search )";
            $params['search'] = '%' . $searchparams . '%';

        }

        $competenciepccriterias=$DB->get_records_sql_menu($sql,$params,$limitfrom,$limitnum);

        if (!empty($searchparams)) {
            
            return $competenciepccriterias;
        }

        $competenciepccriterias[self::OTHER] = get_string('other','local_competency');
            
        return array_merge($performancecriterias,$competenciepccriterias);
            
    }

    public static function competency_kpi($searchparams,$limitfrom=0, $limitnum=0) {

        global $DB, $USER;

        $kpis = self::constkpis();

        $currentlang= current_language();

         if($currentlang == 'ar'){

            $kptitlefield='cmt.kpinamearabic';

        }else{

           $kptitlefield='cmt.kpiname';

        }


        list($relatedkpissql, $relatedkpisparams) = $DB->get_in_or_equal(array_flip($kpis), SQL_PARAMS_NAMED, 'kpis',false);
        $params = $relatedkpisparams;

        $sql = "SELECT cmt.kpiname,$kptitlefield as fullname FROM {local_competency_pc} AS cmt
                WHERE cmt.kpiname $relatedkpissql ";

        if (!empty($searchparams)) {

            $sql .= " AND ($kptitlefield LIKE :search )";
            $params['search'] = '%' . $searchparams . '%';

        }

        $competenciepckpis=$DB->get_records_sql_menu($sql,$params,$limitfrom,$limitnum);

        if (!empty($searchparams)) {
            
            return $competenciepckpis;
        }

        $competenciepckpis[self::OTHER] = get_string('other','local_competency');
            
        return array_merge($kpis,$competenciepckpis);
            
    }
    public static function competency_jobroleresponsibility($jobroleid) {

        global $DB, $USER;

   

        $jobroleresponsibilities= $DB->get_records_sql_menu('SELECT jbrspn.id,jbrspn.responsibility FROM {local_jobrole_responsibility} as jbrspn JOIN {local_jobrole_level} as jbrl on jbrl.id=jbrspn.roleid WHERE jbrl.id=:jobroleid',['jobroleid' => $jobroleid]);

        

        return $jobroleresponsibilities;
            
    }
    public static function competency_jobrole_levels($competencyid,$type='string') {

        global $DB, $USER;

        if($type=='string'){

            $jobrole= $DB->get_field_sql('SELECT cmtc.level 
                                                        FROM {local_competencies} as cmtc 
                                                     WHERE cmtc.id=:competencyid',['competencyid' => $competencyid]);
        }else{

            $jobrolelevel= $DB->get_field_sql('SELECT cmtc.level 
                                                        FROM {local_competencies} as cmtc 
                                                     WHERE cmtc.id=:competencyid',['competencyid' => $competencyid]);

            $jobrole= explode(',',$jobrolelevel);

        }

        return $jobrole;
            
    }
    public static function competency_objective($searchparams,$limitfrom=0, $limitnum=0) {

        global $DB, $USER;

        $currentlang= current_language();

        if($currentlang == 'ar'){

            $objtitlefield='cmt.objectiveidarabic';


        }else{

           $objtitlefield='cmt.objectiveid';
        }

        $objectives = self::constobjectives();


        list($relatedobjectivessql, $relatedobjectivesparams) = $DB->get_in_or_equal(array_flip($objectives), SQL_PARAMS_NAMED, 'objectives',false);
        $params = $relatedobjectivesparams;

        $sql = "SELECT cmt.objectiveid,$objtitlefield as fullname FROM {local_competency_pc} AS cmt
                WHERE cmt.objectiveid $relatedobjectivessql ";

        if (!empty($searchparams)) {

            $sql .= " AND ($objtitlefield LIKE :search )";
            $params['search'] = '%' . $searchparams . '%';

        }

        $competenciepcobjectives=$DB->get_records_sql_menu($sql,$params,$limitfrom,$limitnum);

        if (!empty($searchparams)) {
            
            return $competenciepcobjectives;
        }


        $competenciepcobjectives[self::OTHER] =get_string('other','local_competency');
            
        return array_merge($objectives,$competenciepcobjectives);
    }
    public static function get_mycompetencies($stable,$filterdata=null) {
        global $DB, $USER;

        $params          = array();
        $competencies      = array();
        $competenciescount = 0;
        $concatsql       = '';

        $currentlang= current_language();

        if($currentlang == 'ar'){

           $cmtitlefield='cmt.arabicname';

        }else{

           $cmtitlefield='cmt.name';
        }

        $userinfo=self::userjobrolelevelinfo();


        if($stable->nextlevel){


            $nextrolecompetencies=$DB->get_field_sql("SELECT GROUP_CONCAT(competencies) FROM {local_jobrole_level} WHERE jobfamily =:jobfamily and level=:level ",array('jobfamily'=>$userinfo->jobfamily,'level'=>$userinfo->nextjobrolelevel));


            if(!empty($nextrolecompetencies)){

                $competencylist = explode(',',$nextrolecompetencies);

                list($relatedcompetencylistsql, $relatedcompetencylistparams) = $DB->get_in_or_equal($competencylist, SQL_PARAMS_NAMED, 'competencylist');
                $params = array_merge($params,$relatedcompetencylistparams);
                $concatsql .= " AND cmt.id $relatedcompetencylistsql";


            }else{

                $concatsql .= "AND cmt.id =0 ";
            }


        }elseif($stable->supportedcompetencies){

            $concatsql .= " AND (EXISTS (SELECT trgprgm.id FROM {local_trainingprogram} AS trgprgm
                                        JOIN {program_enrollments} AS penrl ON penrl.programid=trgprgm.id WHERE FIND_IN_SET(cmt.id,trgprgm.competencyandlevels) > 0 AND penrl.userid=:penrluserid) > 0 OR EXISTS (SELECT exm.id FROM {local_exams} AS exm
                                        JOIN {exam_enrollments} AS exmnrl ON exmnrl.examid=exm.id WHERE FIND_IN_SET(cmt.id,exm.competencies) > 0 AND exmnrl.userid=:exmnrluserid) > 0)";

             $params['penrluserid'] = $USER->id;  
             $params['exmnrluserid'] = $USER->id;                          

        }else{


            $currentrolecompetencies=$DB->get_field('local_jobrole_level','competencies', array('id'=>$userinfo->currentjobroleid));

            if(!empty($currentrolecompetencies)){

                $competencylist = explode(',',$currentrolecompetencies);

                list($relatedcompetencylistsql, $relatedcompetencylistparams) = $DB->get_in_or_equal($competencylist, SQL_PARAMS_NAMED, 'competencylist');
                $params = array_merge($params,$relatedcompetencylistparams);
                $concatsql .= " AND cmt.id $relatedcompetencylistsql";


            }else{

                $concatsql .= "AND cmt.id =0 ";
            }

        }

        if (!empty($filterdata->search_query)) {

            $squery = trim($filterdata->search_query);

            $concatsql .= " AND (cmt.name LIKE '%$squery%' OR cmt.arabicname LIKE '%$squery%' OR cmt.code LIKE '%$squery%') ";

        }


        if (isset($stable->competencyid) && $stable->competencyid > 0) {
            $concatsql .= " AND cmt.id = :competencyid";
            $params['competencyid'] = $stable->competencyid;
        }

        $countsql = "SELECT COUNT(cmt.id) ";
        $fromsql = "SELECT cmt.*,$cmtitlefield as name ";
        $sql = " FROM {local_competencies} AS cmt
                WHERE cmt.id > 0 ";


        $sql .= $concatsql;


        if(!empty($filterdata->type)){

            if(!empty($filterdata->type)){
                $typelist = explode(',',$filterdata->type);
            }
    
            list($relatedtypelistsql, $relatedtypelistparams) = $DB->get_in_or_equal($typelist, SQL_PARAMS_NAMED, 'typelist');
            $params = array_merge($params,$relatedtypelistparams);
            $sql .= " AND cmt.type $relatedtypelistsql";

        }

        if(!empty($filterdata->level)){

            if(!empty($filterdata->level)){
                $levellist = explode(',',$filterdata->level);
            }
    
            list($relatedlevellistsql, $relatedlevellistparams) = $DB->get_in_or_equal($levellist, SQL_PARAMS_NAMED, 'levellist');
            $params = array_merge($params,$relatedlevellistparams);
            $sql .= " AND cmt.level $relatedlevellistsql";

        }

        if (isset($stable->competencyid) && $stable->competencyid > 0) {
            $competencies = $DB->get_record_sql($fromsql . $sql, $params);
        } else {
            try {

                $competenciescount = $DB->count_records_sql($countsql . $sql, $params);
                if ($stable->thead == false) {
                    $sql .= " ORDER BY cmt.id DESC";

                    $competencies = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
                }
            } catch (dml_exception $ex) {
                $competenciescount = 0;
            }
        }
        if (isset($stable->competencyid) && $stable->competencyid > 0) {
            return $competencies;
        } else {
            return compact('competencies', 'competenciescount');
        }
    }
    public static function get_myallcompetencies($stable,$filterdata=null) {
        global $DB, $USER;

        $params          = array();
        $competencies      = array();
        $competenciescount = 0;
        $concatsql       = '';


        $currentlang= current_language();

        if($currentlang == 'ar'){

           $cmtitlefield='cmt.arabicname';

        }else{

           $cmtitlefield='cmt.name';
        }

        if (!empty($filterdata->search_query)) {

            $concatsql .= " AND (cmt.name LIKE :search1 OR cmt.arabicname LIKE :search2 OR cmt.code LIKE :search3) ";

            $params = array('search1' => '%'.trim($filterdata->search_query).'%','search2' => '%'.trim($filterdata->search_query).'%','search3' => '%'.trim($filterdata->search_query).'%');
        }

        if (isset($stable->competencyid) && $stable->competencyid > 0) {
            $concatsql .= " AND cmt.id = :competencyid";
            $params['competencyid'] = $stable->competencyid;
        }

        $countsql = "SELECT COUNT(cmt.id) ";
        $fromsql = "SELECT cmt.*,$cmtitlefield as name ";
        $sql = " FROM {local_competencies} AS cmt
                 WHERE cmt.id > 0 ";

        $sql .= $concatsql;


        if(!empty($filterdata->type)){

            if(!empty($filterdata->type)){
                $typelist = explode(',',$filterdata->type);
            }
    
            list($relatedtypelistsql, $relatedtypelistparams) = $DB->get_in_or_equal($typelist, SQL_PARAMS_NAMED, 'typelist');
            $params = array_merge($params,$relatedtypelistparams);
            $sql .= " AND cmt.type $relatedtypelistsql";

        }

        if(!empty($filterdata->level)){

            if(!empty($filterdata->level)){
                $levellist = explode(',',$filterdata->level);
            }
    
            list($relatedlevellistsql, $relatedlevellistparams) = $DB->get_in_or_equal($levellist, SQL_PARAMS_NAMED, 'levellist');
            $params = array_merge($params,$relatedlevellistparams);
            $sql .= " AND cmt.level $relatedlevellistsql";

        }

        if (isset($stable->competencyid) && $stable->competencyid > 0) {
            $competencies = $DB->get_record_sql($fromsql . $sql, $params);
        } else {
            try {
                $competenciescount = $DB->count_records_sql($countsql . $sql, $params);
                if ($stable->thead == false) {
                    $sql .= " ORDER BY cmt.id DESC";

                    $competencies = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
                }
            } catch (dml_exception $ex) {
                $competenciescount = 0;
            }
        }
        if (isset($stable->competencyid) && $stable->competencyid > 0) {
            return $competencies;
        } else {
            return compact('competencies', 'competenciescount');
        }
    }
    public static function get_objectives_exams_info($stable,$filterdata=null) {
        global $DB, $USER;

        $params          = array();
        $exams      = array();
        $examscount = 0;
        $concatsql       = '';

        $currentlang= current_language();

        if($currentlang == 'ar'){

           $examtitlefield='exm.examnamearabic';

        }else{

           $examtitlefield='exm.exam';
        }


        if (!empty($filterdata->search_query)) {

            $concatsql .= " AND (exm.exam LIKE :search1 OR exm.examnamearabic LIKE :search2 OR exm.code LIKE :search3) ";

            $params = array('search1' => '%'.trim($filterdata->search_query).'%','search2' => '%'.trim($filterdata->search_query).'%','search3' => '%'.trim($filterdata->search_query).'%');
        }

        $countsql = "SELECT COUNT(exm.id) ";
        $fromsql = "SELECT exm.id as examid,$examtitlefield as examname,exm.code as examcode,cpcbj.competencypc,exm.courseid ";
        $sql = " FROM {local_exams} AS exm
                JOIN {local_competencypc_obj} AS cpcbj ON FIND_IN_SET(exm.id,cpcbj.examids) > 0 
                WHERE cpcbj.competencypc=$filterdata->competencypcid ";

        $sql .= $concatsql;

        if (isset($stable->competencypcid) && $stable->competencypcid > 0) {

            $fromsql = "SELECT exm.id as examid,$examtitlefield as fullname,exm.courseid ";

            $exams = $DB->get_records_sql($fromsql . $sql, $params);

        } else {

            try {
        
                $examscount = $DB->count_records_sql($countsql . $sql, $params);

                if ($stable->thead == false) {
                    $sql .= " ORDER BY exm.id DESC";

                    $exams = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
                }
            } catch (dml_exception $ex) {
                $examscount = 0;
            }
        }
        if (isset($stable->competencypcid) && $stable->competencypcid > 0) {

            return $exams;

        } else {

            return compact('exams', 'examscount');

        }
    }
    public static function get_objectives_trainingprograms_info($stable,$filterdata=null) {
        global $DB, $USER;

        $params          = array();
        $trainingprograms      = array();
        $trainingprogramscount = 0;
        $concatsql       = '';

        $currentlang= current_language();

        if($currentlang == 'ar'){

           $programtitlefield='trgprgm.namearabic';

        }else{

           $programtitlefield='trgprgm.name';
        }

        if (!empty($filterdata->search_query)) {

            $concatsql .= " AND (trgprgm.name LIKE :search1 OR trgprgm.namearabic LIKE :search2 OR trgprgm.code LIKE :search3) ";

            $params = array('search1' => '%'.trim($filterdata->search_query).'%','search2' => '%'.trim($filterdata->search_query).'%','search3' => '%'.trim($filterdata->search_query).'%');
        }

        $countsql = "SELECT COUNT(trgprgm.id) ";
        $fromsql = "SELECT trgprgm.id as trgprgmid,$programtitlefield as trgprgmname,trgprgm.code as trgprgmcode,cpcbj.competencypc,trgprgm.courseid ";

        $sql = " FROM {local_trainingprogram} AS trgprgm
                JOIN {local_competencypc_obj} AS cpcbj ON FIND_IN_SET(trgprgm.id,cpcbj.trainingprogramids) > 0
                WHERE cpcbj.competencypc=$filterdata->competencypcid ";

        $sql .= $concatsql;



        if (isset($stable->competencypcid) && $stable->competencypcid > 0) {

            $fromsql = "SELECT trgprgm.id as trgprgmid,$programtitlefield as fullname,trgprgm.courseid ";

            $trainingprograms = $DB->get_records_sql($fromsql . $sql, $params);

        } else {

            try {
                $trainingprogramscount = $DB->count_records_sql($countsql . $sql, $params);
                if ($stable->thead == false) {
                    $sql .= " ORDER BY trgprgm.id DESC";

                    $trainingprograms = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
                }
            } catch (dml_exception $ex) {

                $trainingprogramscount = 0;

            }

        }
        if (isset($stable->competencypcid) && $stable->competencypcid > 0) {

            return $trainingprograms;

        } else {

            return compact('trainingprograms', 'trainingprogramscount');
        }
        
    }
    public static function get_objectives_questions_info($stable,$filterdata=null) {
        global $DB, $USER;

        $params          = array();
        $questions      = array();
        $questionscount = 0;
        $concatsql       = '';


        if (!empty($filterdata->search_query)) {

            $concatsql .= " AND (q.questiontext LIKE :search1 OR q.qtype LIKE :search2) ";

            $params = array('search1' => '%'.trim($filterdata->search_query).'%','search2' => '%'.trim($filterdata->search_query).'%');
        }

        $countsql = "SELECT COUNT(q.id) ";
        $fromsql = "SELECT q.id as qid,q.questiontext as qname,q.qtype as qqtype,cpcbj.competencypc ";
        $sql = " FROM {question} AS q
                JOIN {local_competencypc_obj} AS cpcbj ON FIND_IN_SET(q.id,cpcbj.questionids) > 0 
                WHERE cpcbj.competencypc=$filterdata->competencypcid ";

        $sql .= $concatsql;

        if (isset($stable->competencypcid) && $stable->competencypcid > 0) {

            $fromsql = "SELECT q.id as qid,CONCAT(q.qtype,' ',q.questiontext)  as fullname ";

            $questions = $DB->get_records_sql($fromsql . $sql, $params);

        } else {

            try {
        
                $questionscount = $DB->count_records_sql($countsql . $sql, $params);

                if ($stable->thead == false) {
                    $sql .= " ORDER BY q.id DESC";

                    $questions = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
                }
            } catch (dml_exception $ex) {
                $questionscount = 0;
            }
        }
        if (isset($stable->competencypcid) && $stable->competencypcid > 0) {

            return $questions;

        } else {

            return compact('questions', 'questionscount');

        }
    }
    public static function get_objectives_levels_info($stable,$filterdata=null) {
        global $DB, $USER;

        $params          = array();
        $levels      = array();
        $levelscount = 0;
        $concatsql       = '';


        if (!empty($filterdata->search_query)) {

            $concatsql .= " AND (jbrl.level LIKE :search1) ";

            $params = array('search1' => '%'.trim($filterdata->search_query).'%');
        }

        $countsql = "SELECT COUNT(jbrl.id) ";
        $fromsql = "SELECT jbrl.id as lvlid,jbrl.level as lvlname,cpcbj.competencypc ";
        $sql = " FROM {local_jobrole_level} as jbrl
                JOIN {local_competencypc_obj} AS cpcbj ON FIND_IN_SET(jbrl.id,cpcbj.jobrolelevelids) > 0 
                WHERE cpcbj.competencypc=$filterdata->competencypcid ";

        $sql .= $concatsql;

        if (isset($stable->competencypcid) && $stable->competencypcid > 0) {

            $fromsql = "SELECT jbrl.id as lvlid,jbrl.level as fullname ";

            $levels = $DB->get_records_sql($fromsql . $sql, $params);

        } else {

            try {
        
                $levelscount = $DB->count_records_sql($countsql . $sql, $params);

                if ($stable->thead == false) {
                    $sql .= " ORDER BY jbrl.id DESC";

                    $levels = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
                }
            } catch (dml_exception $ex) {
                $levelscount = 0;
            }
        }
        if (isset($stable->competencypcid) && $stable->competencypcid > 0) {

            return $levels;

        } else {

            return compact('levels', 'levelscount');

        }
    }
    public static function competency_obj_exams($searchparams,$limitfrom=0, $limitnum=0,$parentid,$parentchildid) {

        global $DB, $USER;

        $currentlang= current_language();

        if($currentlang == 'ar'){

           $examtitlefield='exm.examnamearabic';

        }else{

           $examtitlefield='exm.exam';
        }


        $sql = "SELECT exm.id as examid,$examtitlefield as fullname
                FROM {local_exams} AS exm 
                WHERE concat(',',exm.competencies,',' ) like '%,$parentid,%' AND EXISTS (SELECT cpcbj.examids FROM {local_competencypc_obj} AS cpcbj WHERE cpcbj.competencypc=:competencypc AND cpcbj.competency=:competency AND FIND_IN_SET(exm.id,cpcbj.examids) > 0) = 0 ";   

        $params=array('competencypc'=>$parentchildid,'competency'=>$parentid);       


        if (!empty($searchparams)) {

            $sql .= " AND (exm.exam LIKE :search1 OR exm.examnamearabic LIKE :search2 OR exm.code LIKE :search3) ";

            $params['search1'] = '%'.trim($searchparams).'%';
            $params['search2'] = '%'.trim($searchparams).'%';
            $params['search3'] = '%'.trim($searchparams).'%';

        }

        $objexams=$DB->get_records_sql_menu($sql,$params,$limitfrom,$limitnum);
            
        return $objexams;
    }
    public static function competency_obj_jobrolelevels($searchparams,$limitfrom=0, $limitnum=0,$parentid,$parentchildid) {

        global $DB, $USER;


        $sql = "SELECT jbrl.id,jbrl.level 
                    FROM {local_jobrole_level} as jbrl
                    JOIN {local_competencies} as cmtc ON FIND_IN_SET(jbrl.id,cmtc.jobroleid) > 0
                    WHERE cmtc.id=$parentid  AND EXISTS (SELECT cpcbj.jobrolelevelids FROM {local_competencypc_obj} AS cpcbj WHERE cpcbj.competencypc=:competencypc AND cpcbj.competency=:competency AND FIND_IN_SET(jbrl.id,cpcbj.jobrolelevelids) > 0) = 0 ";

        $params=array('competencypc'=>$parentchildid,'competency'=>$parentid);       


        if (!empty($searchparams)) {

            $sql .= " AND (jbrl.level LIKE :search1) ";

            $params['search1'] = '%'.trim($searchparams).'%';

        }

        $objjobrolelevels=$DB->get_records_sql_menu($sql,$params,$limitfrom,$limitnum);
            
        return $objjobrolelevels;
    }
    public static function competency_obj_trainingprograms($searchparams,$limitfrom=0, $limitnum=0,$parentid,$parentchildid) {

        global $DB, $USER;

        $currentlang= current_language();

        if($currentlang == 'ar'){

           $programtitlefield='trgprgm.namearabic';

        }else{

           $programtitlefield='trgprgm.name';
        }

        $sql = "SELECT trgprgm.id as trgprgmid,$programtitlefield as fullname
                FROM {local_trainingprogram} AS trgprgm
                WHERE concat(',',trgprgm.competencyandlevels,',' ) like '%,$parentid,%' AND EXISTS (SELECT cpcbj.trainingprogramids FROM {local_competencypc_obj} AS cpcbj WHERE cpcbj.competencypc=:competencypc AND cpcbj.competency=:competency AND FIND_IN_SET(trgprgm.id,cpcbj.trainingprogramids) > 0) = 0 ";   

        $params=array('competencypc'=>$parentchildid,'competency'=>$parentid);      

        if (!empty($searchparams)) {

            $sql .= " AND (trgprgm.name LIKE :search1 OR trgprgm.namearabic LIKE :search2 OR trgprgm.code LIKE :search3) ";

            $params['search1'] = '%'.trim($searchparams).'%';
            $params['search2'] = '%'.trim($searchparams).'%';
            $params['search3'] = '%'.trim($searchparams).'%';

        }

        $objtrainingprograms=$DB->get_records_sql_menu($sql,$params,$limitfrom,$limitnum);
            
        return $objtrainingprograms;
    }
    public static function competency_obj_questions($searchparams,$limitfrom=0, $limitnum=0,$parentid,$parentchildid) {

        global $DB, $USER;

        $sql = "SELECT q.id, q.questiontext,q.qtype FROM {question} q JOIN {question_versions} qv ON qv.questionid = q.id JOIN {question_bank_entries} qbe on qbe.id = qv.questionbankentryid JOIN {question_categories} qc ON qc.id = qbe.questioncategoryid LEFT JOIN {user} uc ON uc.id = q.createdby WHERE q.parent = 0 AND qv.version = (SELECT MAX(v.version) FROM {question_versions} v JOIN {question_bank_entries} be ON be.id = v.questionbankentryid WHERE be.id = qbe.id) AND ((qbe.questioncategoryid IN (SELECT qcategoryid FROM {local_questionbank} WHERE FIND_IN_SET($parentid,competency)))) AND EXISTS (SELECT cpcbj.questionids FROM {local_competencypc_obj} AS cpcbj WHERE cpcbj.competencypc=:competencypc AND cpcbj.competency=:competency AND FIND_IN_SET(q.id,cpcbj.questionids) > 0) = 0 ";


        // $sql = "SELECT qbcmt.questionid,q.qtype,q.questiontext
        //         FROM {local_questioncompetencies} AS qbcmt
        //         JOIN {question} q ON q.id=qbcmt.questionid
        //         WHERE concat(',',qbcmt.competency,',' ) like '%,$parentid,%' AND EXISTS (SELECT cpcbj.questionids FROM {local_competencypc_obj} AS cpcbj WHERE cpcbj.competencypc=:competencypc AND cpcbj.competency=:competency AND FIND_IN_SET(qbcmt.questionid,cpcbj.questionids) > 0) = 0 ";   

        $params=array('competencypc'=>$parentchildid,'competency'=>$parentid);      




        if (!empty($searchparams)) {

            $sql .= " AND (q.questiontext LIKE :search1 OR q.qtype LIKE :search2) ";

            $params['search1'] = '%'.trim($searchparams).'%';
            $params['search2'] = '%'.trim($searchparams).'%';

        }

        $sql.=" ORDER BY q.qtype ASC, q.name ASC";

        $objquestions=$DB->get_records_sql($sql,$params,$limitfrom,$limitnum);

        $objquestionsarray=array();

        foreach($objquestions as $objquestion){

            $objquestionsarray[$objquestion->id]=get_string('pluginname','qtype_'.$objquestion->qtype).' '.mb_convert_encoding(clean_text(html_to_text(html_entity_decode($objquestion->questiontext))), 'UTF-8');
        }
            
        return $objquestionsarray;
            
    }
    public static function competency_const_exams($exams) {

        global $DB, $USER;

        $currentlang= current_language();

        if($currentlang == 'ar'){

           $examtitlefield='exm.examnamearabic';

        }else{

           $examtitlefield='exm.exam';
        }

        list($relatedexamslistsql, $relatedexamslistparams) = $DB->get_in_or_equal($exams, SQL_PARAMS_NAMED, 'examslist');
        $params = $relatedexamslistparams;

        $sql = "SELECT exm.id as examid,$examtitlefield as fullname
                FROM {local_exams} AS exm
                WHERE exm.id $relatedexamslistsql";        

        $objexams=$DB->get_records_sql_menu($sql,$params);
            
        return $objexams;
    }
    public static function competency_const_objjobrolelevels($jobrolelevels) {

        global $DB, $USER;

        list($relatedjobrolelevelslistsql, $relatedjobrolelevelslistparams) = $DB->get_in_or_equal($jobrolelevels, SQL_PARAMS_NAMED, 'jobrolelevelslist');
        $params = $relatedjobrolelevelslistparams;

        $sql = "SELECT jbrl.id,jbrl.level as fullname
                FROM {local_jobrole_level} AS jbrl
                WHERE jbrl.id $relatedjobrolelevelslistsql";        

        $objjobrolelevels=$DB->get_records_sql_menu($sql,$params);

            
        return $objjobrolelevels;
    }
    public static function competency_const_trainingprograms($trainingprograms) {

        global $DB, $USER;

        $currentlang= current_language();

        if($currentlang == 'ar'){

           $programtitlefield='trgprgm.namearabic';

        }else{

           $programtitlefield='trgprgm.name';
        }
       
        
        list($relatedtrainingprogramslistsql, $relatedtrainingprogramslistparams) = $DB->get_in_or_equal($trainingprograms, SQL_PARAMS_NAMED, 'trainingprogramslist');

        $params = $relatedtrainingprogramslistparams;

        $sql = "SELECT trgprgm.id as trgprgmid,$programtitlefield as fullname
                FROM {local_trainingprogram} AS trgprgm
                WHERE trgprgm.id $relatedtrainingprogramslistsql "; 

        $objtrainingprograms=$DB->get_records_sql_menu($sql,$params);
            
        return $objtrainingprograms;
    }
    public static function competency_const_questions($questions) {

        global $DB, $USER;
       
        
        list($relatedquestionslistsql, $relatedquestionslistparams) = $DB->get_in_or_equal($questions, SQL_PARAMS_NAMED, 'questionslist');

        $params = $relatedquestionslistparams;

        $sql = "SELECT qbcmt.questionid,q.qtype,q.questiontext
                FROM {local_questioncompetencies} AS qbcmt
                JOIN {question} q ON q.id=qbcmt.questionid
                WHERE qbcmt.questionid $relatedquestionslistsql "; 

        $objquestions=$DB->get_records_sql($sql,$params);

        $objquestionsarray=array();

        foreach($objquestions as $objquestion){

            $objquestionsarray[$objquestion->questionid]=get_string('pluginname','qtype_'.$objquestion->qtype).' '.mb_convert_encoding(clean_text(html_to_text(html_entity_decode($objquestion->questiontext))), 'UTF-8');
        }
            
        return $objquestionsarray;
        
    }
    public function delete_competencypc($competencypcid,$competencyid) {

        global $DB;

        if ($competencypcid > 0) {


            $competencypc=$DB->record_exists('local_competency_pc', ['id' => $competencypcid,'competency'=>$competencyid]);

            if($competencypc){


                $DB->delete_records('local_competency_pc', ['id' => $competencypcid,'competency'=>$competencyid]);
                $params = array(
                    'context' => context_system::instance(),        
                    'objectid' => $competencypcid                         
                    );
                $event = \local_competency\event\competencypc_deleted::create($params)->trigger();
            }

            $competencypcobj=$DB->record_exists('local_competencypc_obj', ['competencypc' => $competencypcid,'competency'=>$competencyid]);

            if($competencypcobj){

                $objid = $DB->delete_records('local_competencypc_obj', ['competencypc' => $competencypcid,'competency'=>$competencyid]);
                $params = array(
                    'context' => context_system::instance(),        
                    'objectid' => $objid                         
                    );
                $event = \local_competency\event\competency_obj_deleted::create($params)->trigger();
            
            }

            return true;
        } 

        return false;
    }
    public function delete_competencypcobjective($competencypcid,$competencypcobjectiveid,$competencypcobjectivetype) {

        global $DB,$USER;

        if ($competencypcid > 0) {


            $sql ="SELECT cpcbj.id,cpcbj.examids,cpcbj.trainingprogramids,cpcbj.questionids,cpcbj.jobrolelevelids FROM {local_competencypc_obj} AS cpcbj 
                WHERE cpcbj.competencypc=:competencypc ";

            if($competencypcobjectivetype == 'level'){

                $sql.=" AND FIND_IN_SET($competencypcobjectiveid,cpcbj.jobrolelevelids) > 0";

            }elseif($competencypcobjectivetype == 'question'){

                $sql.=" AND FIND_IN_SET($competencypcobjectiveid,cpcbj.questionids) > 0";

            }elseif($competencypcobjectivetype == 'exam'){

                $sql.=" AND FIND_IN_SET($competencypcobjectiveid,cpcbj.examids) > 0";

            }else{

                $sql.=" AND FIND_IN_SET($competencypcobjectiveid,cpcbj.trainingprogramids) > 0 ";
            }


            $competencypcobj=$DB->get_record_sql($sql, ['competencypc' => $competencypcid]);

            if($competencypcobj){

                if($competencypcobjectivetype == 'level'){

                    $setfield=" jobrolelevelids=:objectives";

                    $objectives=implode(',',array_filter(array_diff(explode(',',$competencypcobj->jobrolelevelids), [$competencypcobjectiveid])));

                }elseif($competencypcobjectivetype == 'question'){

                    $learningitemtype='question';

                    $learningitemid=$competencypcobjectiveid;

                    $setfield=" questionids=:objectives";

                    $objectives=implode(',',array_filter(array_diff(explode(',',$competencypcobj->questionids), [$competencypcobjectiveid])));

                }elseif($competencypcobjectivetype == 'exam'){

                    $learningitemtype='exam';

                    $learningitemid=$competencypcobjectiveid;

                    $setfield=" examids=:objectives";

                    $objectives=implode(',',array_filter(array_diff(explode(',',$competencypcobj->examids), [$competencypcobjectiveid])));

                }else{

                    $learningitemtype='trainingprogram';

                    $learningitemid=$competencypcobjectiveid;

                    $setfield=" trainingprogramids=:objectives";

                    $objectives=implode(',',array_filter(array_diff(explode(',',$competencypcobj->trainingprogramids), [$competencypcobjectiveid])));

                }

                $DB->execute('update {local_competencypc_obj} set '.$setfield.', timemodified=:timemodified, usermodified=:usermodified where id=:competencypcobjid' ,
                         ['objectives'=>$objectives,'timemodified'=>time(),'usermodified'=>$USER->id,'competencypcobjid' => $competencypcobj->id]);
                $params = array(
                            'context' => context_system::instance(),        
                            'objectid' => $competencypcobj->id                         
                            );

                $event = \local_competency\event\competency_obj_deleted::create($params)->trigger();
                $competenctid=$DB->get_field('local_competencypc_obj','competency',array('id'=>$competencypcobj->id ));


                if($competencypcobjectivetype != 'level'){     

                    $data->learningitemtype=$learningitemtype;

                    $data->learningitemid=$learningitemid;

                    $data->competency_name=$DB->get_field('local_competencies','name',array('id'=>$competenctid));

                    (new \local_competency\notification())->competency_notification('competency_removing_learning_item', $touser=get_admin(),$fromuser=get_admin(),$data,$waitinglistid=0);

                }
                

            }

            return true;
        } 

        return false;
    }
    public static function get_competency_exams_info($stable,$filterdata=null) {

        global $DB, $USER;

        $params          = array();
        $exams      = array();
        $examscount = 0;
        $concatsql       = '';

        $currentlang= current_language();

        if($currentlang == 'ar'){

           $examtitlefield='exm.examnamearabic';

        }else{

           $examtitlefield='exm.exam';
        }

        if (!empty($filterdata->search_query)) {

            $concatsql .= " AND (exm.exam LIKE :search1 OR exm.examnamearabic LIKE :search2 OR exm.code LIKE :search3) ";

            $params = array('search1' => '%'.trim($filterdata->search_query).'%','search2' => '%'.trim($filterdata->search_query).'%','search3' => '%'.trim($filterdata->search_query).'%');
        }

        $countsql = "SELECT COUNT(exm.id) ";
        $fromsql = "SELECT exm.id as examid,$examtitlefield as examname,exm.code as examcode,exm.courseid ";
        $sql = " FROM {local_exams} AS exm 
                WHERE concat(',',exm.competencies,',' ) like '%,$stable->competencyid,%'  "; 

        $sql .= $concatsql;

        try {
    
            $examscount = $DB->count_records_sql($countsql . $sql, $params);

            if ($stable->thead == false) {
                $sql .= " ORDER BY exm.id DESC";

                $exams = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
            }
        } catch (dml_exception $ex) {
            $examscount = 0;
        }
        
        return compact('exams', 'examscount');
     
    }
    public static function get_competency_trainingprograms_info($stable,$filterdata=null) {

        global $DB, $USER;

        $params          = array();
        $trainingprograms      = array();
        $trainingprogramscount = 0;
        $concatsql       = '';


        $currentlang= current_language();

        if($currentlang == 'ar'){

           $programtitlefield='trgprgm.namearabic';

        }else{

           $programtitlefield='trgprgm.name';
        }


        if (!empty($filterdata->search_query)) {

            $concatsql .= " AND (trgprgm.name LIKE :search1 OR trgprgm.namearabic LIKE :search2 OR trgprgm.code LIKE :search3) ";

            $params = array('search1' => '%'.trim($filterdata->search_query).'%','search2' => '%'.trim($filterdata->search_query).'%','search3' => '%'.trim($filterdata->search_query).'%');
        }

        $countsql = "SELECT COUNT(trgprgm.id) ";
        $fromsql = "SELECT trgprgm.id as trgprgmid,$programtitlefield as trgprgmname,trgprgm.code as trgprgmcode,trgprgm.courseid ";

        $sql = " FROM {local_trainingprogram} AS trgprgm
                WHERE concat(',',trgprgm.competencyandlevels,',' ) like '%,$stable->competencyid,%'";

        $sql .= $concatsql;
     

        try {
            $trainingprogramscount = $DB->count_records_sql($countsql . $sql, $params);
            if ($stable->thead == false) {
                $sql .= " ORDER BY trgprgm.id DESC";

                $trainingprograms = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
            }
        } catch (dml_exception $ex) {

            $trainingprogramscount = 0;

        }

        return compact('trainingprograms', 'trainingprogramscount');
    }
    public function update_competency_trainingprogram_status($competencytp) {

        global $DB, $USER;

        $competency_tp         = new stdClass();
        $competency_tp->trainingprogramid     = $competencytp->programid;
        $competency_tp->competencyid     = $competencytp->competencyid;
        $competency_tp->competencypcid     = $competencytp->competencypcid;
        $competency_tp->userid     = $competencytp->userid;
        $competency_tp->realuser     = ($USER->realuser) ? $USER->realuser :0;

        $completions=$DB->get_record('local_cmtncypc_completions',array('trainingprogramid'=>$competency_tp->trainingprogramid,'competencyid'=>$competency_tp->competencyid,'competencypcid'=>$competency_tp->competencypcid,'userid'=>$competency_tp->userid),'id,completion_status');
        
        try {

            $competency_tp->completion_status = $competencytp->completion_status;
            $competency_tp->completiondate = time();

            if($completions){

                $competency_tp->id   = $completions->id;

                $competency_tp->usermodified   = $USER->id;
                $competency_tp->timemodified   = time();


                $DB->update_record('local_cmtncypc_completions', $competency_tp);

            }else{

                $competency_tp->usercreated   = $USER->id;
                $competency_tp->timecreated   = time();

                $competency_tp->id=$DB->insert_record('local_cmtncypc_completions', $competency_tp);

            }

            self::set_user_competencypc_completions($competency_tp);

        } catch (dml_exception $ex) {
            print_error($ex);
        }
        return true;
    } 
    public function update_competency_exam_status($competencyexam) {

        global $DB, $USER;
        
        $competency_exam         = new stdClass();
        $competency_exam->examid     = $competencyexam->examid;
        $competency_exam->competencyid     = $competencyexam->competencyid;
        $competency_exam->competencypcid     = $competencyexam->competencypcid;
        $competency_exam->userid     = $competencyexam->userid;

        $completions=$DB->get_record('local_cmtncypc_completions',array('examid'=>$competency_exam->examid,'competencyid'=>$competency_exam->competencyid,'competencypcid'=>$competency_exam->competencypcid,'userid'=>$competency_exam->userid),'id,completion_status');
        
        try {

            $competency_exam->completion_status = $competencyexam->completion_status;
            $competency_exam->completiondate = time();

            if($completions){

                $competency_exam->id   = $completions->id;

                $competency_exam->usermodified   = $USER->id;
                $competency_exam->timemodified   = time();


                $DB->update_record('local_cmtncypc_completions', $competency_exam);

            }else{

                $competency_exam->usercreated   = $USER->id;
                $competency_exam->timecreated   = time();

                $competency_exam->id=$DB->insert_record('local_cmtncypc_completions', $competency_exam);

            }

            self::set_user_competencypc_completions($competency_exam);

        } catch (dml_exception $ex) {
            print_error($ex);
        }
        return true;
    }
    public static function get_user_competencypc_completions($competencobj) {

        global $DB, $USER;

        $ttlcnt_cmpc_cmpln = array();
  
        $sql = "SELECT CASE WHEN examids='' THEN 0 ELSE LENGTH(examids)-LENGTH(REPLACE(examids,',',''))+1 END as totalexamscount,CASE WHEN trainingprogramids='' THEN 0 ELSE LENGTH(trainingprogramids)-LENGTH(REPLACE(trainingprogramids,',',''))+1 END as totalprogramscount FROM {local_competencypc_obj} WHERE competencypc=:competencypcid AND competency=:competencyid ";


        $params = array('competencypcid'=>$competencobj->competencypcid,'competencyid'=>$competencobj->competencyid);
    
        try {

            $competencypc_completions = $DB->get_record_sql($sql, $params);


            if($competencypc_completions){

                $ttlcnt_cmpc_cmpln=$competencypc_completions->totalexamscount+$competencypc_completions->totalprogramscount;

            }
            
        } catch (dml_exception $ex) {

            $ttlcnt_cmpc_cmpln = 0;

        }

        return $ttlcnt_cmpc_cmpln;
    }
    public static function set_user_competencypc_completions($competencobj) {

        global $DB, $USER;

        $set_cmpc_cmpln=false;

    
        try {

            $get_cmpc_cmpln=self::get_user_competencypc_completions($competencobj);


            if($get_cmpc_cmpln){

                $sql = "SELECT COUNT(id) FROM {local_cmtncypc_completions} WHERE competencyid=:competencyid AND userid=:userid AND completion_status=:completionstatus ";

                $params = array('competencyid'=>$competencobj->competencyid,'userid'=>$competencobj->userid,'completionstatus'=>$competencobj->completion_status);

                $user_cmpc_cmpln = $DB->count_records_sql($sql, $params);

                if($user_cmpc_cmpln > 0){

                    $sql = "SELECT CASE WHEN examids='' THEN 0 ELSE LENGTH(examids)-LENGTH(REPLACE(examids,',',''))+1 END as totalexamscount,CASE WHEN trainingprogramids='' THEN 0 ELSE LENGTH(trainingprogramids)-LENGTH(REPLACE(trainingprogramids,',',''))+1 END as totalprogramscount FROM {local_competencypc_obj} WHERE competency=:competencyid ";

                    $params = array('competencyid'=>$competencobj->competencyid);

                    $competencypc_completions = $DB->get_record_sql($sql, $params);

                    if($competencypc_completions){

                        $ttlcnt_cmpc_cmpln=$competencypc_completions->totalexamscount+$competencypc_completions->totalprogramscount;


                        if($ttlcnt_cmpc_cmpln > 0 && $user_cmpc_cmpln == $ttlcnt_cmpc_cmpln){

                            self::update_competency_completion_status($competencobj);
                        }

                    }
                }

            }
            
        } catch (dml_exception $ex) {

            $set_cmpc_cmpln=false;

        }

        return $set_cmpc_cmpln;
    }
    public static function update_competency_completion_status($competency) {

        global $DB, $USER;
        
        $usercompetency         = new stdClass();
        $usercompetency->competencyid     = $competency->competencyid;
        $usercompetency->userid     = $competency->userid;
        $usercompetency->realuser     = ($USER->realuser) ? $USER->realuser :0;


        $completions=$DB->get_record('local_cmtncy_completions',array('competencyid'=>$usercompetency->competencyid,'userid'=>$usercompetency->userid),'id,completion_status');
        
        try {

            $usercompetency->completion_status = $competency->completion_status;
            $usercompetency->completiondate = time();

            if($completions){

                $usercompetency->id   = $completions->id;

                $usercompetency->usermodified   = $USER->id;
                $usercompetency->timemodified   = time();


                $DB->update_record('local_cmtncy_completions', $usercompetency);

                if($usercompetency->completion_status == 1){

                    $usercompetency->competency_name=$DB->get_field('local_competencies','name',array('id'=>$usercompetency->competencyid));
                   
                    $touser=$DB->get_record('user',array('id'=>$competency->userid));

                    $localuserrecord = $DB->get_record('local_users',['userid'=>$competency->userid]);

                    $usercompetency->competency_userfulname= ($localuserrecord)? ((current_language() == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',['id'=>$competency->userid]));

                    (new \local_competency\notification())->competency_notification('competency_completions', $touser,$fromuser=get_admin(), $usercompetency,$waitinglistid=0);
                }

            }else{

                $usercompetency->usercreated   = $USER->id;
                $usercompetency->timecreated   = time();
                $usercompetency->id=$DB->insert_record('local_cmtncy_completions', $usercompetency);             
                if($usercompetency->completion_status == 1){

                    $usercompetency->competency_name=$DB->get_field('local_competencies','name',array('id'=>$usercompetency->competencyid));
                   
                    $localuserrecord = $DB->get_record('local_users',['userid'=>$competency->userid]);

                    $usercompetency->competency_userfulname= ($localuserrecord)? ((current_language() == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',['id'=>$competency->userid]));

                    (new \local_competency\notification())->competency_notification('competency_completions', $touser,$fromuser=get_admin(), $usercompetency,$waitinglistid=0);
                    
                }
               
            }                                                                                                                                                                                              

        } catch (dml_exception $ex) {
            print_error($ex);
        }
        return true;
    }
    public static function get_supportedcompetency_exams_info($stable,$filterdata=null) {

        global $DB, $USER;

        $params          = array();
        $exams      = array();
        $examscount = 0;
        $concatsql       = '';

        $currentlang= current_language();

        if($currentlang == 'ar'){

           $examtitlefield='exm.examnamearabic';

        }else{

           $examtitlefield='exm.exam';
        }


        if (!empty($filterdata->search_query)) {

            $concatsql .= " AND (exm.exam LIKE :search1 OR exm.examnamearabic LIKE :search2 OR exm.code LIKE :search3) ";

            $params = array('search1' => '%'.trim($filterdata->search_query).'%','search2' => '%'.trim($filterdata->search_query).'%','search3' => '%'.trim($filterdata->search_query).'%');
        }
        
        $countsql = "SELECT COUNT(exm.id) ";
        $fromsql = "SELECT exm.id as examid,$examtitlefield as examname,exm.code as examcode,exm.courseid ";
        $sql = " FROM {local_exams} AS exm 
                 JOIN {exam_enrollments} AS exmnrl ON exmnrl.examid=exm.id 
                 WHERE concat(',',exm.competencies,',' ) like '%,$stable->competencyid,%' AND exmnrl.userid=:exmnrluserid "; 

        $params['exmnrluserid'] = $USER->id; 

        $sql .= $concatsql;

        try {
    
            $examscount = $DB->count_records_sql($countsql . $sql, $params);

            if ($stable->thead == false) {
                $sql .= " ORDER BY exm.id DESC";

                $exams = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
            }
        } catch (dml_exception $ex) {
            $examscount = 0;
        }
        
        return compact('exams', 'examscount');
     
    }
    public static function get_supportedcompetency_trainingprograms_info($stable,$filterdata=null) {

        global $DB, $USER;

        $params          = array();
        $trainingprograms      = array();
        $trainingprogramscount = 0;
        $concatsql       = '';

        $currentlang= current_language();

        if($currentlang == 'ar'){

           $programtitlefield='trgprgm.namearabic';

        }else{

           $programtitlefield='trgprgm.name';
        }

        if (!empty($filterdata->search_query)) {

            $concatsql .= " AND (trgprgm.name LIKE :search1 OR trgprgm.namearabic LIKE :search2 OR trgprgm.code LIKE :search3) ";

            $params = array('search1' => '%'.trim($filterdata->search_query).'%','search2' => '%'.trim($filterdata->search_query).'%','search3' => '%'.trim($filterdata->search_query).'%');
        }

        $countsql = "SELECT COUNT(trgprgm.id) ";
        $fromsql = "SELECT trgprgm.id as trgprgmid,$programtitlefield as trgprgmname,trgprgm.code as trgprgmcode,trgprgm.courseid ";

        $sql = " FROM {local_trainingprogram} AS trgprgm
                 JOIN {program_enrollments} AS penrl ON penrl.programid=trgprgm.id
                 WHERE concat(',',trgprgm.competencyandlevels,',' ) like '%,$stable->competencyid,%' AND penrl.userid=:penrluserid ";

        $params['penrluserid'] = $USER->id; 

        $sql .= $concatsql;
     

        try {
            $trainingprogramscount = $DB->count_records_sql($countsql . $sql, $params);
            if ($stable->thead == false) {
                $sql .= " ORDER BY trgprgm.id DESC";

                $trainingprograms = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
            }
        } catch (dml_exception $ex) {

            $trainingprogramscount = 0;

        }

        return compact('trainingprograms', 'trainingprogramscount');
    }
    public static function get_user_exams_check($examid) {

        global $DB, $USER;

        $params          = array();
        $examscount = 0;
   
        $countsql = "SELECT COUNT(exm.id) ";
   
        $sql = " FROM {local_exams} AS exm 
                 JOIN {exam_enrollments} AS exmnrl ON exmnrl.examid=exm.id 
                 WHERE exm.id=:exmid AND exmnrl.userid=:exmnrluserid "; 

        $params['exmnrluserid'] = $USER->id; 
        $params['exmid'] = $examid;


        try {
    
            $examscount = $DB->count_records_sql($countsql . $sql, $params);

        } catch (dml_exception $ex) {

            $examscount = 0;

        }
        
        return $examscount;
     
    }
    public static function get_user_trainingprograms_check($programid) {

        global $DB, $USER;

        $params          = array();
        $trainingprogramscount = 0;

        $countsql = "SELECT COUNT(trgprgm.id) ";


        $sql = " FROM {local_trainingprogram} AS trgprgm
                 JOIN {program_enrollments} AS penrl ON penrl.programid=trgprgm.id
                 WHERE trgprgm.id=:prgmid AND penrl.userid=:penrluserid ";

        $params['penrluserid'] = $USER->id; 
        $params['prgmid'] = $programid;

        try {
            $trainingprogramscount = $DB->count_records_sql($countsql . $sql, $params);

        } catch (dml_exception $ex) {

            $trainingprogramscount = 0;

        }

        return $trainingprogramscount;
    }
    public static function get_questionexperts($questionid) {

        global $DB, $USER;

        $currentlang= current_language();

        $fullname = (new \local_trainingprogram\local\trainingprogram())->user_fullname_case();

        $params =array('questionid'=>$questionid);

        $sql = "SELECT u.id,$fullname
                FROM {local_questioncompetencies} AS qbcmt
                JOIN {local_qb_experts} qexp ON qexp.questionbankid=qbcmt.questionbankid
                JOIN {user} AS u ON u.id=qexp.expertid
                JOIN {local_users} AS lc ON lc.userid=u.id
                WHERE qbcmt.questionid =:questionid "; 

        $questionexperts=$DB->get_records_sql_menu($sql,$params);
            
        return $questionexperts;
    }
    public static function get_competency_level_exams_info($stable,$filterdata=null) {
        global $DB, $USER;

        $params          = array();
        $exams      = array();
        $examscount = 0;
        $concatsql       = '';

        $currentlang= current_language();

        if($currentlang == 'ar'){

           $examtitlefield='exm.examnamearabic';

        }else{

           $examtitlefield='exm.exam';
        }

        if (!empty($filterdata->search_query)) {

            $concatsql .= " AND (exm.exam LIKE :search1 OR exm.examnamearabic LIKE :search2 OR exm.code LIKE :search3) ";

            $params = array('search1' => '%'.trim($filterdata->search_query).'%','search2' => '%'.trim($filterdata->search_query).'%','search3' => '%'.trim($filterdata->search_query).'%');
        }

        $level =  $stable->level;

        $countsql = "SELECT COUNT(exm.id) ";
        $fromsql = "SELECT exm.id as examid,$examtitlefield as examname,exm.code as examcode,exm.courseid ";
        $sql = " FROM {local_exams} AS exm
                WHERE exm.competencies LIKE '%$stable->competencyid%' AND FIND_IN_SET('$level', exm.clevels) > 0";

        $sql .= $concatsql;

        if (isset($stable->level) && $stable->level) {

            $fromsql = "SELECT exm.id as examid,$examtitlefield as fullname,exm.courseid ";

            $exams = $DB->get_records_sql($fromsql . $sql, $params);

        } else {

            try {
        
                $examscount = $DB->count_records_sql($countsql . $sql, $params);

                if ($stable->thead == false) {
                    $sql .= " ORDER BY exm.id DESC";

                    $exams = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
                }
            } catch (dml_exception $ex) {
                $examscount = 0;
            }
        }
        if (isset($stable->level) && $stable->level) {

            return $exams;

        } else {

            return compact('exams', 'examscount');

        }
    }
    public static function get_competency_level_trainingprograms_info($stable,$filterdata=null) {
        global $DB, $USER;

        $params          = array();
        $trainingprograms      = array();
        $trainingprogramscount = 0;
        $concatsql       = '';

         $currentlang= current_language();

        if($currentlang == 'ar'){

           $programtitlefield='trgprgm.namearabic';

        }else{

           $programtitlefield='trgprgm.name';
        }

        if (!empty($filterdata->search_query)) {

            $concatsql .= " AND (trgprgm.name LIKE :search1 OR trgprgm.namearabic LIKE :search2 OR trgprgm.code LIKE :search3) ";

            $params = array('search1' => '%'.trim($filterdata->search_query).'%','search2' => '%'.trim($filterdata->search_query).'%','search3' => '%'.trim($filterdata->search_query).'%');
        }

        $level = $stable->level;

        $countsql = "SELECT COUNT(trgprgm.id) ";
        $fromsql = "SELECT trgprgm.id as trgprgmid,$programtitlefield as trgprgmname,trgprgm.code as trgprgmcode,trgprgm.courseid ";

        $sql = " FROM {local_trainingprogram} AS trgprgm
                 WHERE trgprgm.competencyandlevels LIKE '%$stable->competencyid%' AND  FIND_IN_SET('$level', trgprgm.clevels) > 0";

        $sql .= $concatsql;


        if (isset($stable->level) && $stable->level) {

            $fromsql = "SELECT trgprgm.id as trgprgmid,$programtitlefield as fullname,trgprgm.courseid ";
            $trainingprograms = $DB->get_records_sql($fromsql . $sql, $params);

        } else {

            try {
                $trainingprogramscount = $DB->count_records_sql($countsql . $sql, $params);
                if ($stable->thead == false) {
                    $sql .= " ORDER BY trgprgm.id DESC";


                    $trainingprograms = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
                }
            } catch (dml_exception $ex) {

                $trainingprogramscount = 0;

            }

        }
        if (isset($stable->level) && $stable->level) {

            return $trainingprograms;

        } else {

            return compact('trainingprograms', 'trainingprogramscount');
        }
        
    }
    public function update_competency_trainingprogram_delete($trainingprogramid) {

        global $DB;

        if ($trainingprogramid > 0) {

            $sql ="SELECT cpcbj.id,cpcbj.trainingprogramids FROM {local_competencypc_obj} AS cpcbj 
                WHERE FIND_IN_SET($trainingprogramid,cpcbj.trainingprogramids) > 0 "; 

            $competencypcobjs=$DB->get_records_sql($sql);

            foreach($competencypcobjs as $competencypcobj){

                if($competencypcobj){


                    $setfield=" trainingprogramids=:objectives";

                    $objectives=implode(',',array_filter(array_diff(explode(',',$competencypcobj->trainingprogramids), [$trainingprogramid])));

                    
                    $DB->execute('update {local_competencypc_obj} set '.$setfield.', timemodified=:timemodified, usermodified=:usermodified where id=:competencypcobjid' ,
                         ['objectives'=>$objectives,'timemodified'=>time(),'usermodified'=>$USER->id,'competencypcobjid' => $competencypcobj->id]);
                     $params = array(
                        'context' => context_system::instance(),        
                        'objectid' => $competencypcobj->id                         
                        );
                    $event = \local_competency\event\competency_obj_deleted::create($params)->trigger();

                }
            }


            return true;
        } 

        return false;
    }
    public function update_competency_exam_delete($examid) {

        global $DB;

        if ($examid > 0) {

            $sql ="SELECT cpcbj.id,cpcbj.examids FROM {local_competencypc_obj} AS cpcbj 
                WHERE FIND_IN_SET($examid,cpcbj.examids) > 0 "; 

            $competencypcobjs=$DB->get_records_sql($sql);

            foreach($competencypcobjs as $competencypcobj){

                if($competencypcobj){


                    $setfield=" examids=:objectives";

                    $objectives=implode(',',array_filter(array_diff(explode(',',$competencypcobj->examids), [$examid])));

                    
                    $DB->execute('update {local_competencypc_obj} set '.$setfield.', timemodified=:timemodified, usermodified=:usermodified where id=:competencypcobjid' ,
                         ['objectives'=>$objectives,'timemodified'=>time(),'usermodified'=>$USER->id,'competencypcobjid' => $competencypcobj->id]);
                     $params = array(
                        'context' => context_system::instance(),        
                        'objectid' => $competencypcobj->id                         
                        );
                    $event = \local_competency\event\competency_obj_deleted::create($params)->trigger();

                }
            }

            return true;
        } 

        return false;
    }

    public static function is_competence_mapped($competencyid) {
        global $DB;

       $sql = " SELECT loc.id
                FROM {local_competencies} as loc
                JOIN {local_trainingprogram} as lot ON FIND_IN_SET(loc.id,lot.competencyandlevels) > 0 WHERE loc.id = $competencyid
                UNION ALL 
                SELECT loc.id 
                FROM {local_competencies} as loc
                JOIN {local_exams} as loe ON FIND_IN_SET(loc.id,loe.competencies) > 0 
                WHERE loc.id = $competencyid 
                UNION ALL 
                SELECT loc.id 
                FROM {local_competencies} as loc
                JOIN {local_questionbank} as loq ON FIND_IN_SET(loc.id,loq.competency) > 0 
                WHERE loc.id = $competencyid
                UNION ALL
                SELECT loc.id 
                FROM {local_competencies} as loc
                JOIN {local_learningtracks} as lol ON FIND_IN_SET(loc.id,lol.competency) > 0 
                WHERE loc.id = $competencyid 
                UNION ALL
                SELECT loc.id 
                FROM {local_competencies} as loc
                JOIN {local_jobrole_level} as lol ON FIND_IN_SET(loc.id,lol.competencies) > 0 
                WHERE loc.id = $competencyid 
                 ";
        $competency = $DB->record_exists_sql($sql);
        return ($competency) ? 1 : 0;  
    }
     public static function is_competence_trainee() {
        global $DB,$USER;

        $systemcontext = context_system::instance();

        if((!is_siteadmin() && has_capability('local/organization:manage_trainee', $systemcontext))) {
            return true;
        } else {
            throw new required_capability_exception($systemcontext, 'local/competency:viewtraineecompetencies', 'nopermissions', '');            
        }



        // $sql = "SELECT ra.id
        //           FROM {role_assignments} ra, {role} r, {context} c
        //          WHERE ra.userid =:userid
        //                AND ra.roleid = r.id
        //                AND ra.contextid = c.id
        //                AND ra.contextid =:contextid AND r.shortname ='trainee' ";

        // $roles=$DB->record_exists_sql($sql ,array('userid'=>$USER->id,'contextid'=>$systemcontext->id));
        // if(empty($roles)){

        //     throw new required_capability_exception($systemcontext, 'local/competency:viewtraineecompetencies', 'nopermissions', '');

        // }
    }  
    public static function competencyexams_datasubmit($data) {

        global $DB, $USER;

        try {

            foreach($data->exams as $examid){

                $sql = "SELECT exm.id,exm.ctype,exm.competencies
                        FROM {local_exams} AS exm 
                        WHERE exm.id = $examid ";  
                                         
                $exam = $DB->get_record_sql($sql); 

                $row=array();

                $row['id'] = $exam->id;

                $ctype=array_unique(array_filter(array_merge(explode(',',$exam->ctype),[$data->type])));

                $row['ctype'] = implode(',',$ctype);


                $competencies=array_unique(array_filter(array_merge(explode(',',$exam->competencies),[$data->competencyid])));

                $row['competencies'] = implode(',',$competencies);


                $DB->update_record('local_exams', $row);
            }

        } catch (dml_exception $e) {
            print_error($e);

        }
        return true;
    }
    public static function competencyprograms_datasubmit($data) {

        global $DB, $USER;

        try {

            foreach($data->trainingprograms as $trainingprogramid){


                $sql = "SELECT trgprgm.id,trgprgm.competencyandlevels
                        FROM {local_trainingprogram} AS trgprgm 
                        WHERE trgprgm.id = '$trainingprogramid' ";  
                                         
                $program = $DB->get_record_sql($sql);

                $row=array();

                $row['id'] = $program->id;

                $competencyandlevels=array_unique(array_filter(array_merge(explode(',',$program->competencyandlevels),[$data->competencyid])));

                $row['competencyandlevels'] = implode(',',$competencyandlevels);

                $DB->update_record('local_trainingprogram', $row);
            }

        } catch (dml_exception $e) {
            print_error($e);

        }
        return true;
    }
    public static function competencyquestions_datasubmit($data) {

        global $DB, $USER;

        try {

            foreach($data->questions as $question){


                $sql = "SELECT qbcmt.id,qbcmt.competency
                    FROM {local_questioncompetencies} AS qbcmt
                    JOIN {question} q ON q.id=qbcmt.questionid
                    WHERE q.id = $question->id ";  
                                     
                $questiondata = $DB->get_record_sql($sql);


                if($questiondata){

                    $row=array();

                    $row['id'] = $questiondata->id;

                    $competency=array_unique(array_filter(array_merge(explode(',',$questiondata->competency),[$data->competencyid])));

                    $row['competency'] = implode(',',$competency);

                    $DB->update_record('local_questioncompetencies', $row);

                }else{

                    $row=array();

                    $row['questionbankid'] = $question->category;

                    $row['questionid'] = $question->id;

                    $row['competency'] = $data->competencyid;

                    $row['usercreated'] = $USER->id;

                    $row['timemodified'] = time();

                    $DB->insert_record('local_questioncompetencies', $row);
                }


                 $sql = "SELECT qb.id,qb.competency
                    FROM {local_questionbank} AS qb
                    WHERE qb.qcategoryid = $question->category";  
                                     
                $questionbankdata = $DB->get_record_sql($sql);

                if($questionbankdata){

                    $row=array();

                    $row['id'] = $questionbankdata->id;

                    $competency=array_unique(array_filter(array_merge(explode(',',$questionbankdata->competency),[$data->competencyid])));

                    $row['competency'] = implode(',',$competency);

                    $DB->update_record('local_questionbank', $row);

                }

                $competencies = new stdClass;
                $competencies->questionbankid = $question->category;
                $competencies->questionid = $question->id;
                $competencies->competency = $data->competencyid;
                if(!$DB->record_exists('local_questioncompetencies', 
                    ['questionbankid' => $competencies->questionbankid, 
                     'questionid' => $competencies->questionid, 
                     'competency' => $competencies->competency])){
                  
                    $competencies->timecreated = time();
                    $competencies->usercreated = $USER->id;
                    $DB->insert_record('local_questioncompetencies', $competencies);
                }
            }

        } catch (dml_exception $e) {
            print_error($e);

        }
        return true;
    }
    public function get_competencies_jobroleid($stable) {
        global $DB, $PAGE, $OUTPUT, $CFG,$SESSION;
        $SESSION->lang = ($stable->isArabic == 'true') ?'ar':'en';
        $competencies_list = $DB->get_field('local_jobrole_level','competencies',array('id'=>$stable->JobRoleId));
        if(!empty($competencies_list)){
            $selectsql = "SELECT  lc.id as competencyid,lc.*
            FROM {local_competencies} as lc   
            WHERE lc.id IN ($competencies_list) "; 

            $countsql  = "SELECT  count(lc.id)
            FROM {local_competencies} as lc   
            WHERE lc.id IN ($competencies_list)";

            $totalcompetenciescount = $DB->count_records_sql($countsql);
            $formsql .=" ORDER BY lc.id DESC";

            $totalcompetencies = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
            $totalcompetencieslist = array();
            $alltypes = (new competency)::get_allcompetencytypes($stable);
            $count = 0;
            foreach( $totalcompetencies as $totalcompetency) {
               $key =  array_search($totalcompetency->type, array_column($alltypes, 'type'));    
               $totalcompetencieslist [$count] ['typeId'] =   $alltypes[$key]['value'];
               $totalcompetencieslist [$count] ['typeName'] =  $alltypes[$key]['name'];
               $totalcompetencieslist [$count] ['code'] =   $totalcompetency->code;
               $totalcompetencieslist [$count] ['name'] = ($SESSION->lang=='ar') ? $totalcompetency->arabicname:$totalcompetency->name; 
                $totalcompetencieslist [$count] ['description'] =  strip_tags(format_text($totalcompetency->description, FORMAT_HTML));
                $totalcompetencieslist [$count] ['value']    = $totalcompetency->id;
                $totalcompetencieslist [$count] ['parentValue']    = $stable->JobRoleId;
              

              $count++;

            }
        }else{
            $totalcompetencieslist = array();
        }
        $coursesContext = array(
            "competencies" =>$totalcompetencieslist,
            "nocourses" => $nocourse,
            "totalcompetencies" =>  $totalcompetenciescount,
            "length" => count($totalcompetencieslist)

        );
        return $coursesContext;     
        
    }
      public function get_jobrole($JobRoleId,$isArabic) {
        global $DB, $PAGE, $OUTPUT, $CFG,$SESSION;
        $SESSION->lang = ($isArabic == 'true') ?'ar':'en';
        $selectsql = "SELECT  lc.id as competencyid,lc.*
        FROM {local_jobrole_level} as lc   
        WHERE lc.id = $JobRoleId "; 
        $jobroles = $DB->get_records_sql($selectsql);
        $jobroleslist = array();
        $count = 0;
        foreach($jobroles as $jobrole){
      
           $jobroleslist [$count]['name'] = ($SESSION->lang=='ar') ? $jobrole->titlearabic : $jobrole->title;      
            $jobroleslist [$count] ['description'] = strip_tags(format_text($jobrole->description, FORMAT_HTML));
            $jobroleslist [$count] ['value'] = $jobrole->id;
            $jobroleslist [$count] ['code'] = $jobrole->code;
            $jobroleslist[$count] ['parentvalue'] = ($jobrole->jobfamily > 0) ? $jobrole->jobfamily:0;
            $responsibility = $DB->get_fieldset_sql( "SELECT responsibility FROM {local_jobrole_responsibility} WHERE roleid = $JobRoleId");
            $responsibility = strip_tags(format_text(implode(',',$responsibility),FORMAT_HTML));
            $jobroleslist[$count]['responsibilities'] = $responsibility; 
            $count++;

        }
        $coursesContext = array(
            "jobroles" =>$jobroleslist,
            "nocourses" => $nocourse,
            "totalcompetencies" =>  $jobroleslist,
            "length" => count($jobroleslist)

        );
        return $coursesContext;     
        
    }
        public function get_competenciesinfo($stable) {
        global $DB, $PAGE, $OUTPUT, $CFG,$SESSION;
        $SESSION->lang = ($stable->isArabic == 'true')?'ar':'en';
        $selectsql = "SELECT  lc.id as competencyid,lc.*
        FROM {local_competencies} as lc   
        WHERE lc.id = $stable->competencyid "; 
        $competencies = $DB->get_records_sql($selectsql);
        $competencieslist = array();
        $count = 0;
        $alltypes = (new competency)::get_allcompetencytypes($stable);
        foreach($competencies as $competency){
            $key =  array_search($competency->type, array_column($alltypes, 'type'));   
            $competencieslist [$count] ['typeId'] = $alltypes[$key]['value'];
            $competencieslist [$count] ['typeName'] = $alltypes[$key]['name'];
            $competencieslist [$count] ['code'] = $competency->code;            
            $competencieslist [$count]['name'] = ($SESSION->lang=='ar') ? $competency->arabicname:$competency->name;            
            $competencieslist [$count] ['description'] = strip_tags(format_text($competency->description, FORMAT_HTML));
            $competencieslist [$count] ['value'] = ($competency->id > 0) ? $competency->id:0;
            $competencieslist[$count] ['parentValue'] = null ; 
            $count++;

        }
        $coursesContext = array(
            "typeinfo" =>$competencieslist,
            "nocourses" => $nocourse,
            "totalcompetencies" =>  $competencieslist,
            "length" => count($competencieslist)

        );
        return $coursesContext;     
        
    }
    public function get_competencylevel($stable) {
        global $DB, $PAGE, $OUTPUT, $CFG,$SESSION;
        $SESSION->lang = ($stable->isArabic == 'true') ?'ar':'en';
        $competencylevels=$DB->get_field('local_competencies','level',['id'=>$stable->competencyid]);
        $competencylevels = explode(',',$competencylevels);
        $count = 0;
        $totalcompetencylevels = [];
        foreach ($competencylevels as $competencylevel) {
            $totalcompetencylevels[] = $competencylevel; 
        }
        $levelslist = array();
        foreach( $totalcompetencylevels as $totalcompetencylevel) {
          $levelslist[$count]['levelId'] =str_replace("level",'',$totalcompetencylevel);
          $levelslist[$count]['code'] =str_replace("level",'',$totalcompetencylevel);
          $levelslist[$count]['name'] =get_string($totalcompetencylevel.'_text','local_competency');
          $description = $DB->get_field('local_cmtncy_level','description',['competencyid'=>$stable->competencyid,'levelid'=>$totalcompetencylevel]);
          $levelslist [$count] ['description'] = ($description)?format_text($description, FORMAT_HTML) : null;
          $levelslist [$count] ['value'] = $levelslist[$count]['levelId'];
          $levelslist [$count]['parentValue']= $stable->competencyid;

          $count++;

        }

        $coursesContext = array(
            "levels" =>$levelslist,
            "nocourses" => $nocourse,
            "totallevels" => count($levelslist)

        );
        return $coursesContext;     
        
    }  
     public function get_competencytypes($stable) {

        global $DB, $PAGE, $OUTPUT, $CFG,$SESSION;
        $SESSION->lang = ($stable->language == 'true') ?'ar':'en';
        $current_language = $stable->language;
        $selectsql = "SELECT  lc.type,lc.*
        FROM {local_competencies} as lc   
        WHERE 1=1 GROUP BY lc.type "; 

        $formsql .=" ORDER BY lc.id DESC";
        $totalcompetencies = $DB->get_records_sql($selectsql.$formsql, $params);
        $totalcompetencieslist = array();
        $count = 0;
        foreach( $totalcompetencies as $totalcompetency) {
           $totalcompetencieslist [$count] ['value'] =   $count;  
          $totalcompetencieslist [$count] ['type'] =   $totalcompetency->type;  

          $count++;

        }
        $coursesContext = array(
            "competencies" =>$totalcompetencieslist,
            "nocourses" => $nocourse,
            "length" => count($totalcompetencieslist)

        );
        return $coursesContext;     
        
    }

    public function competency_search($stable)
    {
        global $DB,$SESSION;
        $SESSION->lang = ($stable->isarabic == 'true') ?'ar':'en';
        $data = (new \local_hall\hall)->entities($stable->keyword, $SESSION->lang);
        
        return $data;
    }
    public function detailed_competencyview($competencyid, $stable) {
        global $DB, $PAGE, $OUTPUT, $CFG, $SESSION,$USER;

        $SESSION->lang = ($stable->mlang) ? $stable->mlang : current_language();
        $competencyrecord = $DB->get_record('local_competencies',['id'=>$competencyid]);

        $traineeroleid = $DB->get_field('role','id',['shortname'=>'trainee']);

        if($competencyrecord) {

          $competency=new \stdClass();
          if($SESSION->lang == 'ar'){
            $competency->name = $competencyrecord->arabicname;
          } else {
            $competency->name = $competencyrecord->name;
          }       
     
          $competency->code = $competencyrecord->code;
          if($competencyrecord->level){
                $levels=explode(',',$competencyrecord->level);
                foreach($levels as $key => $level){
                    $levels[$key]= get_string($level,'local_competency');
                }
               $competency->level =implode(',',$levels);
            }else{
                $competency->level=get_string('na','local_competency');
            }
          $competencytypes=  (new competency)->constcompetencytypes();
          $competency->type = $competencytypes[$competencyrecord->type];
          $competency->description = strip_tags(format_text($competencyrecord->description, FORMAT_HTML));

            $enprograms = $DB->get_records_sql("SELECT * FROM  {local_trainingprogram}
                                               WHERE  FIND_IN_SET('$competencyid',competencyandlevels)
                                               AND id IN (SELECT programid  FROM {program_enrollments} WHERE userid = $USER->id AND roleid = $traineeroleid )");
            foreach ($enprograms AS $program) {

                $program->id = $program->id;
                $program->courseid = $program->courseid;           
                $program->name = ($SESSION->lang == 'ar') ? $program->namearabic : $program->name;            
                $program->code =$program->code ;
                $program->starttime =$program->availablefrom ;
                $program->endtime =$program->availableto ;
                $program->description =strip_tags(format_text($program->description, FORMAT_HTML));
                $languages = explode(',',$program->languages);
                $actuallang =array();
                foreach ( $languages AS $language) {
                    $actuallang[]=($language == '0') ? get_string('arabic','local_trainingprogram') : get_string('english','local_trainingprogram');
                }
                $program->langauge = $actuallang ? implode(',',$actuallang) :''; 

                list($sectorsql,$sectorparams) = $DB->get_in_or_equal(explode(',',$program->sectors));
           
               $sectorquerysql = "SELECT * FROM {local_sector} WHERE id $sectorsql";
                  
                $sectors= $DB->get_records_sql($sectorquerysql,$sectorparams);
                foreach ($sectors AS $sector) {

                    $sector->name =($SESSION->lang == 'ar')? $sector->titlearabic : $sector->title ;
                    $sector->description = ($sector->description) ? $sector->description : null;
                    $sector->code = $sector->code;
                    $sector->value = $sector->id;
                } 
                if(!empty($sectors)){
                    $program->sectorsList = array_values($sectors);
                } else {
                    $program->sectorsList = array();
                }

            } 
            if(!empty($enprograms)){
                $competency->enrolledprograms = array_values($enprograms);
            } else {
                $competency->enrolledprograms = array();
            }
            $enexams = $DB->get_records_sql("SELECT * FROM  {local_exams} 
                                               WHERE  FIND_IN_SET('$competencyid',competencies)
                                               AND id IN (SELECT examid  FROM {exam_enrollments} WHERE userid = $USER->id)");
            foreach ($enexams AS $exam) {

                $exam->id = $exam->id;
                $exam->courseid = $exam->courseid;
                $exam->name = ($SESSION->lang == 'ar')?$exam->examnamearabic : $exam->exam ;
                $exam->code =$exam->code ;
                $exam->starttime =$exam->examdatetime ;
                $exam->endtime =$exam->enddate ;
                 $exam->description =strip_tags(format_text($exam->programdescription, FORMAT_HTML));
                $languages = explode(',',$exam->language);
                $actuallang =array();
                foreach ( $languages AS $language) {
                    $actuallang[]=($language == '0') ? get_string('arabic','local_trainingprogram') : get_string('english','local_trainingprogram');
                }
                $exam->langauge = $actuallang ? implode(',',$actuallang) :'';  

                
                list($sectorsql,$sectorparams) = $DB->get_in_or_equal(explode(',',$exam->sectors));
           
               $sectorquerysql = "SELECT * FROM {local_sector} WHERE id $sectorsql";
                  
                $sectors= $DB->get_records_sql($sectorquerysql,$sectorparams);
                foreach ($sectors AS $sector) {

                    $sector->name =($SESSION->lang == 'ar')? $sector->titlearabic : $sector->title ;
                    $sector->description = ($sector->description) ? $sector->description : null;
                    $sector->code = $sector->code;
                    $sector->value = $sector->id;
                } 
                if(!empty($sectors)){
                    $exam->sectorsList = array_values($sectors);
                } else {
                    $exam->sectorsList = array();
                }

            } 
            if(!empty($enexams)){
                $competency->enrolledexams = array_values($enexams);
            } else {
                $competency->enrolledexams = array();
            }
        $levls = $DB->get_field('local_competencies','level',['id'=>$competencyid]);
        $levls = explode(',',$levls);
     
        $count = 0;
        $competencyinfo = [];
        $ctypesarray = [];
        foreach ($levls as $level) {
            $ctypesarray[] = $level; 
        }
        foreach($ctypesarray as $clevel) {
            $row = [];
            $row['levelname'] = get_string($clevel,'local_competency');
            $leveldescription = $DB->get_field_sql('SELECT description FROM {local_cmtncy_level} WHERE competencyid =:competencyid AND levelid=:level',['competencyid'=>$competencyid,'level'=>$clevel]);
            $row['leveldescription'] =($leveldescription) ? strip_tags(format_text($leveldescription,FORMAT_HTML)) : null;
            $clevels['mappedprograms'] = [];
            $clevels['mappedexams'] = [];
            $programs= $DB->get_records_sql(" SELECT *  FROM  {local_trainingprogram} WHERE   FIND_IN_SET('$competencyid',competencyandlevels) AND clevels = '$clevel' ");
             $traineeroleid = $DB->get_field('role','id',['shortname'=>'trainee']);
            foreach($programs as $program) {
                if($program->clevels == $clevel) {
                    $programsdata = [];
                    $programsdata['id'] = $program->id;
                    $programsdata['courseid'] = $program->courseid;
                    $programsdata['name'] =($SESSION->lang == 'ar') ? $program->namearabic : $program->name;
                    $programsdata['code'] =$program->code ;
                    $programsdata['starttime'] =$program->availablefrom ;
                    $programsdata['endtime'] =$program->availableto ;
                    $isenrolled = $DB->record_exists_sql('SELECT id FROM {program_enrollments} WHERE programid = :programid AND courseid =:courseid AND roleid =:roleid AND userid =:userid',['programid'=>$program->id,'courseid'=>$program->courseid,'roleid'=>$traineeroleid,'userid'=>$USER->id]);
                    $programsdata['isenrolled'] = ($isenrolled) ? true : false;
                    $programsdata['description'] =strip_tags(format_text($program->description, FORMAT_HTML));
                    $languages = explode(',',$program->languages);
                    $actuallang =array();
                    foreach ( $languages AS $language) {
                        $actuallang[]=($language == '0') ? get_string('arabic','local_trainingprogram') : get_string('english','local_trainingprogram');
                    }
                    $programsdata['langauge'] = $actuallang ? implode(',',$actuallang) :'';

                    list($sectorsql,$sectorparams) = $DB->get_in_or_equal(explode(',',$program->sectors));
                   $sectorquerysql = "SELECT * FROM {local_sector} WHERE id $sectorsql";
                      
                    $sectors= $DB->get_records_sql($sectorquerysql,$sectorparams);
                    foreach ($sectors AS $sector) {

                        $sector->name =($SESSION->lang == 'ar')? $sector->titlearabic : $sector->title ;
                        $sector->description = ($sector->description) ? $sector->description : null;
                        $sector->code = $sector->code;
                        $sector->value = $sector->id;
                    } 
                    if(!empty($sectors)){
                        $programsdata['sectorsList'] = array_values($sectors);
                    } else {
                        $programsdata['sectorsList'] = array();
                    } 


                    $clevels['mappedprograms'][] = $programsdata;
                }
            }
             $exams= $DB->get_records_sql(" SELECT *  FROM  {local_exams} WHERE   FIND_IN_SET('$competencyid',competencies) AND clevels = '$clevel' ");
             foreach($exams as $exam) {
                if($exam->clevels == $clevel) {
                    $examsdata = [];
                    $examsdata['id'] = $exam->id;
                    $examsdata['courseid'] = $exam->courseid;
                    $examsdata['name'] = ($SESSION->lang == 'ar') ? $exam->examnamearabic : $exam->exam ;
                    $examsdata['code'] =$exam->code ;
                    $isenrolled = $DB->record_exists_sql('SELECT id FROM {exam_enrollments} WHERE examid = :examid AND courseid =:courseid AND userid =:userid',['examid'=>$exam->id,'courseid'=>$exam->courseid,'userid'=>$USER->id]);
                    $examsdata['isenrolled'] = ($isenrolled) ? true : false;
                    $examsdata['starttime'] =$exam->examdatetime ;
                    $examsdata['endtime'] =$exam->enddate ;
                    $examsdata['description'] =strip_tags(format_text($exam->description, FORMAT_HTML));
                    $languages = explode(',',$exam->languages);
                    $actuallang =array();
                    foreach ( $languages AS $language) {
                        $actuallang[]=($language == '0') ? get_string('arabic','local_trainingprogram') : get_string('english','local_trainingprogram');
                    }
                    $examsdata['langauge'] = $actuallang ? implode(',',$actuallang) :'';

                    list($sectorsql,$sectorparams) = $DB->get_in_or_equal(explode(',',$exam->sectors));
                   $sectorquerysql = "SELECT * FROM {local_sector} WHERE id $sectorsql";
                      
                    $sectors= $DB->get_records_sql($sectorquerysql,$sectorparams);
                    foreach ($sectors AS $sector) {

                        $sector->name =($SESSION->lang == 'ar')? $sector->titlearabic : $sector->title ;
                        $sector->description = ($sector->description) ? $sector->description : null;
                        $sector->code = $sector->code;
                        $sector->value = $sector->id;
                    } 
                    if(!empty($sectors)){
                        $examsdata['sectorsList'] = array_values($sectors);
                    } else {
                        $examsdata['sectorsList'] = array();
                    } 


                    $clevels['mappedexams'][] = $examsdata;
                }
            }
            $competenciesdata[] = $row+$clevels;
        }
        $competency->levels= !empty($competenciesdata) ? $competenciesdata : [];
        return $competency;

        }
    } 

    public function get_my_competenciesdata($stable,$filterdata) {
        global $DB, $PAGE, $OUTPUT, $CFG,$SESSION;
        $SESSION->lang = ($stable->mlang) ? $stable->mlang : current_language();
        $getcompetencies = competency::get_mycompetencies($stable,$filterdata);
        $competencies=array_values($getcompetencies['competencies']);
        $competencytypes=(new competency)::constcompetencytypes();
        $count = 0;
        $competencyinfo = [];
        $ctypesarray = [];
        $lang= current_language();  
        foreach ($competencies as $competency) {
            $ctypesarray[] = $competency->type; 
        }
        $ctypesarray = array_unique($ctypesarray);
        foreach($ctypesarray as $ctype) {
            $row = [];
            $row['type'] = $competencytypes[$ctype];
            $ctypes['data'] = [];
            foreach($competencies as $competency) {
                if($competency->type == $ctype) {
                    $type = [];
                    if ($SESSION->lang == 'ar') {
                        $type['name'] = $competency->arabicname;
                    } else {
                        $type['name'] = $competency->name;
                    }

                   // $type['name'] = ($SESSION->lang == 'ar') ? $competency->arabicname : $competency->name ;
                    $type['typeId'] = 0;
                    $type['description'] = strip_tags(format_text($competency->description, FORMAT_HTML));
                    $type['id'] = $competency->id;
                    $type['code'] = $competency->code;
                    // $type['level'] =  get_string($competency->level,'local_competency');
                    $type['level'] =  get_string('level1','local_competency');
                    $ctypes['data'][] = $type;
                }
            }
            $competenciesdata[] = $row+$ctypes;
        }
        $competencyinfo['competencies'] = !empty($competenciesdata) ? $competenciesdata : [];
        return $competencyinfo;
    
    }
    public function get_allcompetencytypes($stable) {
        global $DB, $PAGE, $OUTPUT, $CFG,$SESSION;
        $SESSION->lang = ($stable->isArabic == 'true') ?'ar':'en';
        $selectsql = "SELECT  DISTINCT lc.type
        FROM {local_competencies} as lc  ORDER BY lc.type ASC"; 
        $competencytyps= $DB->get_records_sql($selectsql);
        $alltypes = [];
        $count = 0;
        $i= 1;
        $competencytypes=(new competency)::constcompetencytypes();
        foreach($competencytyps as $competencytype){
            $alltypes[$count]['value'] = $i++;
            $alltypes[$count]['type'] = $competencytype->type;
            $alltypes[$count]['name'] = $competencytypes[$competencytype->type];
            $count++;
        }
        return $alltypes;     
        
    }

    public function get_competencybytypeinfobyid($stable) {
        global $DB, $PAGE, $OUTPUT, $CFG,$SESSION;
        $SESSION->lang = ($stable->isArabic == 'true') ?'ar':'en';
        $selectsql = "SELECT  DISTINCT lc.type
        FROM {local_competencies} as lc  ORDER BY lc.type ASC"; 
        $competencytyps= $DB->get_records_sql($selectsql);
        $alltypes = [];
        $count = 0;
        $i= 1;
        $competencytypes=(new competency)::constcompetencytypes();
        foreach($competencytyps as $competencytype){
            $alltypes[$count]['id'] = $i++;
            $alltypes[$count]['name'] = $competencytypes[$competencytype->type];
            $alltypes[$count]['typeName'] = $competencytype->type;
            $count++;
        }
        $typeid = (int)$stable->TypeID;
        $key =  array_search($typeid, array_column($alltypes, 'id'));    
       
        if($key !== false){
           $mainData = array(
            "value" =>$typeid,
            "name" =>$alltypes[$key]['name'],
            "typename" =>$alltypes[$key]['typeName'],
          ); 
        } else {

            $mainData = array();
        }
        return $mainData;     
        
    }

    public function get_competencydatabytypeid($stable,$type) {
        global $DB, $PAGE, $OUTPUT, $CFG,$SESSION;
        $SESSION->lang = ($stable->isArabic == 'true') ?'ar':'en';


        $selectsql = "SELECT * FROM {local_competencies} WHERE type = '$type' "; 
        $countsql  = "SELECT COUNT(id) FROM {local_competencies} WHERE type = '$type' ";
        
        if(isset($stable->query) && trim($stable->query) != ''){
            $formsql .= " AND name LIKE :namesearch OR arabicname LIKE :arabicsearch OR code LIKE :codesearch";
            $searchparams = array(
                'namesearch' => '%'.trim($stable->query).'%',
                'arabicsearch' => '%'.trim($stable->query).'%',
                'codesearch' => '%'.trim($stable->query).'%',
             );
        } else {
            $searchparams = array();
        }
        $formsql .=" ORDER BY id DESC";
        $params = array_merge($searchparams);
        $totalcompetenciescount = $DB->count_records_sql($countsql.$formsql,$params);
        $competencies = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $competencieslist = array();
        $count = 0;
        $competencytypes=(new competency)::constcompetencytypes();
        foreach( $competencies as $competency) {
           $competencieslist [$count] ['typeID'] =   $stable->TypeID;
           $competencieslist [$count] ['typeName'] =    $competencytypes[$type];
           $competencieslist [$count] ['code'] =   $competency->code;
           $competencieslist [$count] ['name'] = ($SESSION->lang=='ar') ? $competency->arabicname:$competency->name; 
           $competencieslist [$count] ['description'] =  strip_tags(format_text($competency->description, FORMAT_HTML));
           $competencieslist [$count] ['value']    = $competency->id;
           $competencieslist [$count] ['parentValue']    = $stable->TypeID;
           $count++;

        }
        $coursesContext = array(
            "competencies" =>$competencieslist,
            "nocourses" => $nocourse,
            "totalcompetencies" =>  $totalcompetenciescount,
            "length" => count($totalcompetencieslist)

        );
        return $coursesContext;  
        
    }

            
      
}
