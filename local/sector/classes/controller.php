<?php  
namespace local_sector;

use stdClass;
class controller
{
    private $usermodified;

    public function __construct(){
        global $USER;
        $this->usermodified = $USER->id;
    }

    public function get_sectors() {
        global $DB;
        $sectors = $DB->get_records_sql("SELECT DISTINCT jf.id as jobfamily1, se.id,se.title as sector, sg.title as segment, jf.familyname as jobfamily
                                           FROM {local_sector} as se   
                                           JOIN {local_segment} as sg ON sg.sectorid = se.id   
                                           JOIN {local_jobfamily} as jf ON jf.segmentid = sg.id");
        foreach($sectors as $sector) {
            $sectordata[] =['sector' => $sector->sector,
                            'segment'=> $sector->segment,
                            'jobfamily'=> $sector->jobfamily];
        };
        return $sectordata;
    }

    public function create_sectors($data) {
        global $DB;
        if(!is_object($data)){
            $data=(object)$data;
        }
        $record = ['title' => $data->title,
                   'titlearabic' => $data->titlearabic, 
                   'code' => $data->code,
                   'timemodified'=>time(),
                   'usermodified'=>$this->usermodified];
        try{
            $sectorid = $DB->insert_record('local_sector', $record);
        } catch(dml_exception $e){
            echo $e->message;
        }
        return $sectorid;
    }

    public function update_sectors($data) {
        global $DB;
        if(!is_object($data)){
            $data=(object)$data;
        }
        $record = ['id'=>$data->id,
                   'title' => $data->title, 
                   'titlearabic' => $data->titlearabic, 
                   'code' => $data->code,
                   'timemodified'=>time(),
                   'usermodified'=>$this->usermodified];
        $DB->update_record('local_sector', $record);
    }

    public function delete_sector($sectorid){
        global $DB;
        $segments = $DB->get_fieldset_select('local_segment', 'id', 'sectorid='.$sectorid.'');

        foreach($segments as $segment){
            $this->delete_segment($segment);
        }
        $DB->delete_records('local_sector',array('id'=>$sectorid));
    }

    public function create_segment($data) {
        global $DB;
        if(!is_object($data)){
            $data=(object)$data;
        }
        $record = ['title' => $data->title, 
                   'titlearabic' => $data->titlearabic, 
                   'code' => $data->code,
                   'sectorid'=>$data->sectorid ,
                   'description'=>$data->description['text'],
                   'timemodified'=>time(),
                   'usermodified'=>$this->usermodified];
        $DB->insert_record('local_segment', $record);
    }

    public function update_segment($data) {
        global $DB;
        if(!is_object($data)){
            $data=(object)$data;
        }
        $record = ['id'=>$data->id,
                   'title' => $data->title,
                   'titlearabic' => $data->titlearabic,  
                   'code' => $data->code, 
                    'description'=>$data->description['text'],
                   'timemodified'=>time(),
                   'usermodified'=>$this->usermodified];
        $DB->update_record('local_segment', $record);
    }

    public function delete_segment($segmentid) {
       global $DB;
       $jobfamilies = $DB->get_fieldset_select('local_jobfamily', 'id', 'segmentid='.$segmentid.'');
       foreach($jobfamilies as $jobfamily){
            $this->delete_jobfamily($jobfamily);
       }
       $DB->delete_records('local_segment',array('id'=>$segmentid));
    }

    public function create_jobfamily($data) {
        global $DB;

        if(!is_object($data)){
            $data=(object)$data;
        }

       if(!empty($data->segments) && !is_null($data->segments)){

            $segmentid =implode(',',$data->segments);
        } else {

            $segmentid = 0;
        }
       
    

        $record = ['familyname' => $data->familyname,
                   'familynamearabic' => $data->familynamearabic, 
                   'code' => $data->code,
                   'description'=>$data->description['text'],
                   'segmentid'=> $segmentid,
                   'careerpath'=> $data->careerpath,
                   'careerpath_ar'=> $data->careerpath_ar,
                   'shared' =>$data->shared,
                   'timemodified'=>time(),
                   'usermodified'=>$this->usermodified];
        $DB->insert_record('local_jobfamily', $record);
    }

    public function update_jobfamily($data) {
        global $DB;
        if(!is_object($data)){
            $data=(object)$data;
        }
        if(!empty($data->segments) && !is_null($data->segments)){

            $segmentid =implode(',',$data->segments);
        } else {

            $segmentid = 0;
        }

        // if(empty($data->sectors)) {
        //     $datashared = 0;
        // } else {
        //     $datashared = 1;
        // }
        $record = ['id'=>$data->id,
                   'familyname' => $data->familyname, 
                   'familynamearabic' => $data->familynamearabic,
                   //'segmentid'=>$data->segment ? implode(',',$data->segment):0,
                   'code' => $data->code, 
                   'segmentid'=> $segmentid,
                   'description'=>$data->description['text'],
                   'careerpath'=> $data->careerpath,
                   'careerpath_ar'=> $data->careerpath_ar,
                    'shared' =>$data->shared,
                   'timemodified'=>time(),
                   'usermodified'=>$this->usermodified];
        $DB->update_record('local_jobfamily', $record);
    }
    public function delete_jobfamily($jobfamilyid){
        global $DB;
         $jobroles = $DB->get_fieldset_select('local_jobrole_level','id', 'jobfamily='.$jobfamilyid.'');
         foreach($jobroles as $jobrole){
            $this->delete_jobrole($jobrole);
         }
         $DB->delete_records('local_jobfamily', array('id'=>$jobfamilyid));
    }
    public function create_jobrole_level($data) {
        global $DB;
        if(!is_object($data)){
            $data=(object)$data;
        }
        $record = ['title' => $data->title, 
                   'titlearabic' => $data->titlearabic,  
                   'code' => $data->code,
                   'description'=>$data->description['text'],
                   'jobfamily'=>$data->jobid,
                   'level'=>$data->clevels,
                   'ctypes' => implode(',', array_filter($data->ctype)),
                   'competencies' => implode(',', array_filter($data->competencylevel)),
                   'timemodified'=>time(),
                   'usermodified'=>$this->usermodified];
        $DB->insert_record('local_jobrole_level', $record);
    }

    public function update_jobrole_level($data) {
        global $DB;
        if(!is_object($data)){
            $data=(object)$data;
        }
        $record = ['id'=>$data->id,
                   'title' => $data->title,
                   'titlearabic' => $data->titlearabic,  
                   'code' => $data->code,
                   'description'=>$data->description['text'],
                   'jobfamily'=>$data->jobid,
                   'level'=>$data->clevels,
                   'ctypes' => implode(',', array_filter($data->ctype)),
                   'competencies' => implode(',', array_filter($data->competencylevel)),
                   'timemodified'=>time(),
                   'usermodified'=>$this->usermodified];
        $DB->update_record('local_jobrole_level', $record);
    }

    public function delete_jobrole($roleid) {
        global $DB;
        $DB->delete_records('local_jobrole_responsibility',array('roleid'=>$roleid));
        $DB->delete_records('local_jobrole_level',array('id'=>$roleid));
    }
    public function create_jobrole_resp($data) {
        global $DB;

        if(!is_object($data)){
            $data=(object)$data;
        }
        $record = ['jobid' => $data->jobid,
                   'roleid' => $data->roleid,
                   'responsibility'=>$data->responsibility['text'],
                   'timecreated'=>time(),
                   'timemodified'=>time(),
                   'usercreated'=>$this->usermodified];

        $DB->insert_record('local_jobrole_responsibility', $record);

    }

    public function update_jobrole_resp($data) {
        global $DB;
        if(!is_object($data)){
            $data=(object)$data;
        }
       $record = ['id'=>$data->id,
                  'jobid' => $data->jobid, 
                  'roleid' => $data->roleid,
                  'responsibility'=>$data->responsibility['text'],
                  'timecreated'=>time(),
                  'timemodified'=>time(),
                  'usercreated'=>$this->usermodified];
        
        $DB->update_record('local_jobrole_responsibility', $record);
    }

    public function is_sector_mapped($sectorid){
       global $DB;
       $sql =  "
                SELECT los.id
                FROM {local_sector} as los 
                JOIN {local_segment} as seg ON seg.sectorid=los.id 
                JOIN {local_jobfamily} as jbfm ON jbfm.segmentid=seg.id 
                JOIN {local_jobrole_level} as jbrl ON jbrl.jobfamily=jbfm.id 
                JOIN {local_competencies} as cmtc ON FIND_IN_SET(jbrl.id,cmtc.jobroleid) > 0
                WHERE los.id = $sectorid
                UNION ALL 
                SELECT los.id
                FROM {local_sector} as los
                JOIN {local_organization} as loorg ON FIND_IN_SET(los.id,loorg.orgsector) > 0 WHERE los.id = $sectorid
                UNION ALL 
                SELECT los.id
                FROM {local_sector} as los
                JOIN {local_users} as lou ON FIND_IN_SET(los.id,lou.sector) > 0 WHERE los.id = $sectorid AND lou.deleted = 0
                UNION ALL 
                SELECT los.id
                FROM {local_sector} as los
                JOIN {local_trainingprogram} as lot ON FIND_IN_SET(los.id,lot.sectors) > 0 WHERE los.id = $sectorid
                UNION ALL 
                SELECT los.id 
                FROM {local_sector} as los
                JOIN {local_exams} as loe ON FIND_IN_SET(los.id,loe.sectors) > 0 
                WHERE los.id = $sectorid";           
        $sector = $DB->record_exists_sql($sql);
        return ($sector) ? 1 : 0;


    }

    public function is_segment_mapped($segmentid){
       global $DB;
       $sql =  "
                SELECT los.id
                FROM {local_segment} as los 
                JOIN {local_jobfamily} as jbfm ON jbfm.segmentid=los.id 
                JOIN {local_jobrole_level} as jbrl ON jbrl.jobfamily=jbfm.id 
                JOIN {local_competencies} as cmtc ON FIND_IN_SET(jbrl.id,cmtc.jobroleid) > 0
                WHERE los.id = $segmentid
                UNION ALL 
                SELECT los.id
                FROM {local_segment} as los
                JOIN {local_organization} as loorg ON FIND_IN_SET(los.id,loorg.orgsegment) > 0 WHERE los.id = $segmentid
                UNION ALL 
                SELECT los.id
                FROM {local_segment} as los
                JOIN {local_users} as lou ON FIND_IN_SET(los.id,lou.segment) > 0 WHERE los.id = $segmentid AND lou.deleted = 0
                UNION ALL 
                SELECT los.id 
                FROM {local_segment} as los
                JOIN {local_jobfamily} as jbfm ON jbfm.segmentid=los.id 
                JOIN {local_trainingprogram} as lot ON FIND_IN_SET(jbfm.id,lot.targetgroup) > 0 WHERE los.id = $segmentid";           
        $segment = $DB->record_exists_sql($sql);
        return ($segment) ? 1 : 0;
    }

    public function is_jobfamily_mapped($jobfamilyid){
       global $DB;
       $sql =  "
                SELECT loj.id
                FROM {local_jobfamily} as loj 
                JOIN {local_jobrole_level} as jbrl ON jbrl.jobfamily=loj.id 
                JOIN {local_competencies} as cmtc ON FIND_IN_SET(jbrl.id,cmtc.jobroleid) > 0
                WHERE loj.id = $jobfamilyid
                UNION ALL 
                SELECT loj.id
                FROM {local_jobfamily} as loj
                JOIN {local_users} as lou ON FIND_IN_SET(loj.id,lou.jobfamily) > 0 WHERE loj.id = $jobfamilyid AND lou.deleted = 0
                UNION ALL 
                SELECT loj.id 
                FROM {local_jobfamily} as loj
                JOIN {local_trainingprogram} as lot ON FIND_IN_SET(loj.id,lot.targetgroup) > 0 WHERE loj.id = $jobfamilyid";           
        $jobfamily = $DB->record_exists_sql($sql);
        return ($jobfamily) ? 1 : 0;
    }

    public function is_jobrole_mapped($jobroleid){
       global $DB;
       $sql =  "
                SELECT loj.id
                FROM {local_jobrole_level} as loj 
                JOIN {local_competencies} as cmtc ON FIND_IN_SET(loj.id,cmtc.jobroleid) > 0
                WHERE loj.id = $jobroleid
                UNION ALL 
                SELECT loj.id
                FROM {local_jobrole_level} as loj
                JOIN {local_users} as lou ON FIND_IN_SET(loj.id,lou.jobrole) > 0 WHERE loj.id = $jobroleid AND lou.deleted = 0";           
        $jobrole = $DB->record_exists_sql($sql);
        return ($jobrole) ? 1 : 0;
    }

    public static function competencies_data($jobroleid = 0, $competencies = array()) {

        global $DB, $USER;
        $competency = array();
        $lang = current_language();

        if($lang == 'ar') {
            $comp_name = 'loc.arabicname';

        } else {

            $comp_name = 'loc.name';

        }

        $competencies = is_array($competencies) ? implode(',',$competencies): $competencies;

        if(!empty($competencies)){


          $competency= $DB->get_records_sql_menu("SELECT loc.id,$comp_name as title FROM {local_competencies} as loc WHERE FIND_IN_SET(loc.id,'$competencies')");

        
        }elseif(!empty($jobroleid)){

            $competency= $DB->get_records_sql_menu("SELECT loc.id, $comp_name as title 
                                                        FROM {local_competencies} as loc
                                                        JOIN {local_jobrole_level} as lot 
                                                        ON concat(',', lot.competencies, ',') LIKE concat('%,',loc.id,',%')
                                                        WHERE lot.id=:jobroleid",['jobroleid' => $jobroleid]);

        }

       
        return $competency;

    }


     public static function segments_list($segments = array(), $jobfamilyid = 0)
    {

        global $DB, $USER;

        $segment = array();
        
        $lang = current_language();

        if($lang == 'ar') {
            $segmenttitle = 'seg.titlearabic as title';

        } else {

            $segmenttitle = 'seg.title as title';


        }

        if (!empty($segments)) {

            $params = array();

            list($jobfamiliessql, $jobfamiliesparams) = $DB->get_in_or_equal($segments);
            $params = array_merge($params, $jobfamiliesparams);


            $segment = $DB->get_records_sql_menu("SELECT seg.id,$segmenttitle,seg.code FROM {local_segment} seg  WHERE seg.id $jobfamiliessql",$params);

           
            

        }elseif($jobfamilyid){


            $segments = $DB->get_field_sql('SELECT segmentid FROM {local_jobfamily} WHERE id  = '.$jobfamilyid.'');

            $segments = explode(',',$segments);

            $params = array();

            list($jobfamiliessql, $jobfamiliesparams) = $DB->get_in_or_equal($segments);
            $params = array_merge($params, $jobfamiliesparams);


            $segment = $DB->get_records_sql_menu("SELECT seg.id,$segmenttitle,seg.code FROM {local_segment} seg  WHERE seg.id $jobfamiliessql",$params);


        }

        return $segment;
    }

    
    
}
