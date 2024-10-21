<?php 
namespace local_sector\local;

use stdClass;
require_once($CFG->dirroot . '/local/sector/lib.php');
class sectors
{
    
    public function get_sectors($fields = array(), $limitfrom =0, $limitnum=0,$filtervalues=null) {
        global $DB;
        !empty($fields) ? $select = implode(',', $fields) : $select = '*';

        if(empty($filtervalues)){
            return $DB->get_records('local_sector', null, ' id DESC',$select, $limitfrom, $limitnum);
        }else{
            // $search_vl = $DB->sql_compare_text('title');
            // $title = '%'.$DB->sql_like_escape($filtervalues->search_query).'%';
            // $params = [ $DB->sql_compare_text('title')   => $title];
            $sel = " (title LIKE '%$filtervalues->search_query%' OR titlearabic LIKE '%$filtervalues->search_query%') ";
            return $DB->get_records_select('local_sector', $sel, NULL, ' id DESC', $fields='*', $limitfrom, $limitnum);
            //return $DB->get_records('local_sector', ['title' => $title], ' id DESC',$select, $limitfrom, $limitnum);
        }
    }

    public function get_segments($sectorid=null, $fields=array()) {
        global $DB;
      
        if($sectorid == null) {
            return $DB->get_records('local_segment');
        }

        !empty($fields) ? $select = implode(',', $fields) : $select = '*';

        return $DB->get_records('local_segment', ['sectorid' => $sectorid], ' id DESC', $select);
    }

    public function get_segments_from_sectorslist($sectorslist=null, $fields=array()) {
        global $DB;

        
        if($sectorslist == null) {
            return array();
        }

        $lang = current_language();

        if($lang == 'ar') {

            $segmenttitle = 'CONCAT(seg.code," ",seg.titlearabic) AS title';

        } else {

            $segmenttitle = 'CONCAT(seg.code," ",seg.title) AS title';

        }
        $sectorsdata = is_array($sectorslist)?array_filter($sectorslist):explode(',', $sectorslist);

        if(!empty($sectorsdata)) {

            $ctypequery = array();
            foreach ($sectorsdata as $sector) {
                $ctypequery[] = " seg.sectorid  = '$sector' "; 
            }
            $ctypequeeryparams =implode('OR',$ctypequery);
            $formsql = ' AND ('.$ctypequeeryparams.') ';

            $sql = "SELECT seg.id,$segmenttitle,seg.code FROM {local_segment} as seg
                  WHERE 1=1 ";

            $segments = $DB->get_records_sql($sql.$formsql);

           return $segments;
       }

    }
    

    public function get_jobfamilies($segmentid=null, $fields=array()) {
        global $DB;

        $segmentid = (int) $segmentid;
        

        if($segmentid == null) {
            return $DB->get_records('local_jobfamily');
        }

        return $DB->get_records_sql("SELECT id,code, familyname,familynamearabic,description,shared FROM {local_jobfamily} WHERE FIND_IN_SET('$segmentid', segmentid) OR segmentid = 0  ORDER BY id DESC");
    }

    public function get_jobroles($jobfamilyid=null, $fields=array()) {
        global $DB;

        if($jobfamilyid == null) {
            return $DB->get_records('local_jobrole_level');
        }
        !empty($fields) ? $select = implode(',', $fields) : $select = '*';

        return $DB->get_records('local_jobrole_level', ['jobfamily' => $jobfamilyid], ' id DESC', $select);
        
    }

     public function get_main_sectors($stable) {
        global $DB, $PAGE, $OUTPUT, $CFG, $SESSION;
        $current_language = $stable->language;
    
        $SESSION->lang = ($stable->language == 'true') ?'ar':'en';
        $selectsql = "SELECT * FROM {local_sector} ls WHERE 1=1 "; 
        $countsql  = "SELECT COUNT(ls.id) FROM {local_sector} ls WHERE 1=1 ";

              
        $totalsectorscount = $DB->count_records_sql($countsql);
        $formsql .=" ORDER BY ls.id DESC";
        $totalsectors = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $totalsectorslist = array();
        $count = 0;
        foreach( $totalsectors as  $totalsector) {
   
          $totalsectorslist [$count] ['name'] = ($SESSION->lang=='ar')?$totalsector->titlearabic:$totalsector->title;     
          $totalsectorslist [$count] ['code'] =  $totalsector->code;
          $totalsectorslist [$count] ['value'] =  $totalsector->id;
          $totalsectorslist [$count] ['description'] = ($totalsector->description)?strip_tags(format_text($totalsector->description, FORMAT_HTML)):null;
          $totalsectorslist [$count] ['parentValue'] = ($totalsector->parentvalue) ? $totalsector->parentvalue:null;


          $count++;

        }
        $coursesContext = array(
            "sectors" => $totalsectorslist,
            "nocourses" => $nocourse,
            "totalsectors" =>  $totalsectorscount,
            "length" => count($totalsectorslist)

        );
        return $coursesContext;     
        
    }
    public function get_jobfamilies_sectorid($stable) {
        global $DB, $PAGE, $OUTPUT, $CFG ,$SESSION;
        $SESSION->lang = ($stable->isArabic == 'true') ?'ar':'en';

          if(!empty($stable->sectorId) || ($stable->segmentId)) {

            if($stable->sectorId && !$stable->segmentId){
                $segmentssql = "SELECT id  
                            FROM {local_segment}
                            WHERE FIND_IN_SET('$stable->sectorId',sectorid)";
                $segments=$DB->get_fieldset_sql($segmentssql);

                $selectsql = "SELECT *  FROM {local_jobfamily} AS lo WHERE 1=1 ";
                $countsql = "SELECT COUNT(lo.id) FROM {local_jobfamily} AS lo WHERE 1=1 ";
            
                $jobfamilyquery = array();
                foreach ($segments as $segment) {
                    $jobfamilyquery[] = " (CONCAT(',',lo.segmentid,',') LIKE CONCAT('%,',$segment,',%') OR segmentid = 0)"; 
                }
                $jobfamilyparams =implode('OR',$jobfamilyquery);
                $formsql .= ' AND ('.$jobfamilyparams.') ';

            } 
            if(!$stable->sectorId && $stable->segmentId){

                $selectsql = "SELECT *  FROM {local_jobfamily} AS lo WHERE 1=1 AND segmentid IN ($stable->segmentId,0) ";
                $countsql = "SELECT COUNT(lo.id) FROM {local_jobfamily} AS lo WHERE 1=1 AND segmentid IN ($stable->segmentId,0) ";
            
            } 
            if($stable->sectorId && $stable->segmentId){

                $isgivensegmentmappedtothesector = $DB->record_exists_sql('SELECT * FROM {local_segment} WHERE  id=:segmentid AND sectorid=:sectorid',['segmentid'=>$stable->segmentId,'sectorid'=>$stable->sectorId]);

                if($isgivensegmentmappedtothesector) {

                    $selectsql = "SELECT *  FROM {local_jobfamily} AS lo WHERE 1=1 AND segmentid IN ($stable->segmentId,0) ";
                    $countsql = "SELECT COUNT(lo.id) FROM {local_jobfamily} AS lo WHERE 1=1 AND segmentid IN ($stable->segmentId,0) ";

                }
            }
            $totaljobfamiliescount = $DB->count_records_sql($countsql.$formsql);
            $formsql .=" ORDER BY lo.id DESC";
            $totaljobfamilies = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
            $totaljobfamilieslist = array();
            $count = 0;
            foreach( $totaljobfamilies as $totaljobfamily) {
              $sectorcode = ($stable->sectorId) ? $DB->get_field('local_sector','code',['id'=>$stable->sectorId]) :  $DB->get_field_sql('SELECT sec.code FROM {local_sector} AS sec JOIN {local_segment} AS seg ON seg.sectorid = sec.id WHERE seg.id = '.$stable->segmentId.'');        
              $totaljobfamilieslist [$count] ['name'] = ($SESSION->lang=='ar') ? $totaljobfamily->familynamearabic : $totaljobfamily->familyname;
              $totaljobfamilieslist [$count] ['code'] =   ($totaljobfamily->segmentid == 0) ? $sectorcode.$totaljobfamily->code  : $totaljobfamily->code;
              $totaljobfamilieslist [$count] ['value'] =   $totaljobfamily->id;  
              $totaljobfamilieslist [$count] ['parentvalue']    = ($stable->segmentId) ? $stable->segmentId : $stable->sectorId;
              $totaljobfamilieslist [$count] ['description'] =  strip_tags(format_text($totaljobfamily->description, FORMAT_HTML));
              $totaljobfamilieslist[$count] ['iscommon'] = ($totaljobfamily->segmentid == 0) ? true:false;
              $count++;

            }
            $coursesContext = array(
                "pageData" =>$totaljobfamilieslist,
                "nocourses" => $nocourse,
                "totaljobfamilies" =>  $totaljobfamiliescount,
                "length" => count($totaljobfamilieslist)

            );


            return $coursesContext;     


        }    
           
        
    }
    public function get_jobrole_jobfamilyid($stable,$jobfamilyid) {
        global $DB, $PAGE, $OUTPUT, $CFG,$SESSION;
        $SESSION->lang = ($stable->language == 'true') ?'ar':'en';

        $current_language = $stable->language;
        if($jobfamilyid > 0){
  
        
            $selectsql = "SELECT  jrl.id as jobroleid,jrl.*
            FROM {local_jobrole_level} as jrl   
            WHERE jrl.jobfamily =  $jobfamilyid "; 
            
    
            $countsql  = "SELECT count(jrl.id)
            FROM {local_jobrole_level} as jrl       
             WHERE jrl.jobfamily =  $jobfamilyid";
            } else{
             
                $selectsql = "SELECT  jrl.id as jobroleid,jrl.*         
                FROM {local_jobrole_level} as jrl"; 
        
                $countsql  = "SELECT count(jrl.id)
                FROM {local_jobrole_level} as jrl   
                 ";
        
            }             
        $totaljobrolescount = $DB->count_records_sql($countsql);
        $formsql .=" ORDER BY jrl.id DESC";
        $totaljobroles = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $totaljobroleslist = array();
        $count = 0;
        
        foreach( $totaljobroles  as  $totaljobrole ) {    

            $totaljobroleslist  [$count] ['name'] = ($SESSION->lang=='ar') ? $totaljobrole->titlearabic :$totaljobrole->title;
            $totaljobroleslist  [$count] ['code'] =   $totaljobrole->code;
            $totaljobroleslist  [$count] ['value'] =   $totaljobrole->id;  
            $totaljobroleslist  [$count] ['parentvalue']    = $totaljobrole->jobfamily;
            $totaljobroleslist  [$count] ['description'] =  strip_tags(format_text($totaljobrole->description, FORMAT_HTML));
             $jobresp = $DB->get_records_sql(
          "SELECT jrr.id ,jrr.responsibility AS responsibility
          FROM {local_jobrole_responsibility} AS jrr WHERE roleid = $totaljobrole->jobroleid");
          $jobresps = array_values($jobresp);
          foreach( $jobresps as  $jobresp ){
            $jobresp->responsibility =  strip_tags(format_text( $jobresp->responsibility, FORMAT_HTML));
          }
          

        $totaljobroleslist  [$count] ['responsibilities'] = $jobresps ;  
        
      
          $count++;

        }
        $coursesContext = array(
            "jobroles" =>$totaljobroleslist,
            "nocourses" => $nocourse,
            "totaljobroles" =>$totaljobrolescount,
            "length" => count( $totaljobroleslist )
           
        );

  
  
        return $coursesContext; 
           
        
    }
    public function getjobfamily($isarabic,$jobfamilyid) {
        global $DB, $PAGE, $OUTPUT, $CFG,$SESSION;
        $SESSION->lang = ($isarabic == 'true') ?'ar':'en';
        $current_language = $stable->language;
        if($jobfamilyid > 0){
            $selectsql = "SELECT  jf.*
            FROM {local_jobfamily} as jf   
            WHERE jf.id = $jobfamilyid "; 
    
            $countsql  = "SELECT count(jf.id)
            FROM {local_jobfamily} as jf   
            WHERE jf.id = $jobfamilyid";
            } else{
                $selectsql = "SELECT  jf.*
                FROM {local_jobfamily} as jf   
                 "; 
        
                $countsql  = "SELECT count(jf.id)
                FROM {local_jobfamily} as jf   
                ";
    
            }
         
         $totaljobfamiliescount = $DB->count_records_sql($countsql);
        $formsql .=" ORDER BY jf.id DESC";
        $jobfamilies = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $jobfamilylist = array();
        $count = 0;
      
        foreach($jobfamilies as $jobfamily){
           
            $jobfamilylist [$count]['name'] = ($SESSION->lang == 'ar') ? $jobfamily->familynamearabic: $jobfamily->familyname;         
            $jobfamilylist [$count] ['description'] = strip_tags(format_text($jobfamily->description, FORMAT_HTML));
            $jobfamilylist [$count] ['value'] = $jobfamily->id;
            $jobfamilylist [$count] ['code'] = $jobfamily->code;
            $jobfamilylist[$count] ['parentvalue'] =   ($jobfamily->segmentid > 0) ? $jobfamily->segmentid:null;
            $jobfamilylist[$count] ['iscommon'] = ($jobfamily->segmentid == 0) ? true:false;
            $count++;

        }
        $coursesContext = array(
            "jobfamily" =>$jobfamilylist,
            "nocourses" => $nocourse,
            "totaljobfamily" =>$totaljobfamiliescount,
            "length" => count($jobfamilylist)
           
        );
        
        return $coursesContext; 
       
    }
    public function get_maindata($stable) {
        global $DB, $PAGE, $OUTPUT, $CFG,$SESSION;
        $SESSION->lang = ($stable->isArabic == 'true') ?'ar':'en';  

        if(!empty($stable->sectorId) || ($stable->segmentId)) {

            if($stable->sectorId && !$stable->segmentId){
                $tablename = '{local_sector}';
                $fieldname = $stable->sectorId;
            } 
            if((!$stable->sectorId && $stable->segmentId)  || ($stable->sectorId && $stable->segmentId)){
                $tablename = '{local_segment}';
                $fieldname = $stable->segmentId;
        
            } 
            $selectsql = "SELECT  ls.*
                FROM $tablename AS ls   
                WHERE ls.id = $fieldname"; 

            $formsql .=" ORDER BY ls.id DESC";
            $sectors = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
            $sectorlist = array();
            $count = 0;
          
            foreach($sectors as $sector){
                $sectorlist [$count]['name'] = ($SESSION->lang=='ar') ? $sector->titlearabic : $sector->title;      
                $sectorlist [$count] ['description'] = ($sector->description)?strip_tags(format_text($sector->description, FORMAT_HTML)):null;
                $sectorlist [$count] ['value'] = $sector->id;
                $sectorlist [$count] ['code'] = $sector->code;
                $sectorlist[$count] ['parentvalue'] = ($stable->segmentId) ? $sector->sectorid : null;       
                $count++;

            }
            $coursesContext = array(
            "mainData" =>$sectorlist,
            "nocourses" => $nocourse,
            "length" => count($sectorlist)
           
           );
           return $coursesContext; 


        }

       

        
       
    }

    public function getsectorinfo($isArabic,$sectorid) {
        global $DB, $PAGE, $OUTPUT, $CFG,$SESSION;

        $sectordata = $DB->get_record('local_sector',['id'=>$sectorid]);
        $SESSION->lang =($isArabic == 'true')?'ar':'en';

        if($sectordata) {

          $sector=new \stdClass();

            $sector->value = $sectordata->id;
            $sector->code = $sectordata->code;
            $sector->name =  ($SESSION->lang == 'ar') ? $sectordata->titlearabic : $sectordata->title;
            $sector->description = $sectordata->description ? strip_tags(format_text($sectordata->description,FORMAT_HTML)) : null;
            $sector->parentvalue = null;
            return $sector;

        }  

    } 

    public function getsegmentsbysectorid($stable) {
        global $DB, $PAGE, $OUTPUT, $CFG, $SESSION;

        $SESSION->lang =($stable->isArabic == 'true')?'ar':'en';
        $selectsql = "SELECT * FROM {local_segment} lo "; 
        $countsql  = "SELECT COUNT(lo.id) FROM {local_segment} lo  ";
        $formsql = " WHERE FIND_IN_SET('$stable->parentId',lo.sectorid) ";
        if(isset($stable->query) && trim($stable->query) != ''){
            $formsql .= " AND lo.title LIKE :search OR lo.titlearabic LIKE :arabicsearch";
            $searchparams = array(
                'search' => '%'.trim($stable->query).'%',
                'arabicsearch' => '%'.trim($stable->query).'%',
             );
        } else {
            $searchparams = array();
        }
        $formsql .= " ORDER BY lo.id DESC";
        $params = array_merge($searchparams);
        $totalsegments = $DB->count_records_sql($countsql.$formsql,$params);
        $segements = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $segmentslist = array();
        $count = 0;
        foreach($segements as $segement) {
               $segmentslist[$count]['name'] =  ($SESSION->lang == 'ar') ? $segement->titlearabic: $segement->title;
               $segmentslist[$count]['code'] = $segement->code;
               $segmentslist[$count]['description'] =strip_tags(format_text($segement->description,FORMAT_HTML));
                $segmentslist[$count]['value'] = $segement->id;
                $segmentslist[$count]['parentvalue'] = $segement->sectorid;

            $count++;
        }
        $coursesContext = array(
            "segments" => $segmentslist,
            "nocourses" => $nocourse,
            "totalsegments" => $totalsegments,
            "length" => count($segmentslist)

        );
        return $coursesContext;
    
    } 

    public function get_jobfamilycareerpathsimages($isArabic,$JobFamilyCode = null) {
        global $DB, $PAGE, $OUTPUT, $CFG, $SESSION;

        $SESSION->lang =($isArabic == 'true')?'ar':'en';
        $selectsql = "SELECT * FROM {local_jobfamily} lo "; 
        $countsql  = "SELECT COUNT(lo.id) FROM {local_jobfamily} lo  ";
        $formsql = " WHERE  1=1 ";
        if($JobFamilyCode){
          $code = trim($JobFamilyCode);
          $formsql .= " AND lo.code = '$code' ";
        }
        $formsql .= " ORDER BY lo.id DESC";

        $totaljobfamilies = $DB->count_records_sql($countsql.$formsql);
        
        $jobfamilies = $DB->get_records_sql($selectsql.$formsql);
        $jobfamilylist = array();
        $count = 0;
        foreach($jobfamilies as $jobfamily) {
            
               $jobfamilylist[$count]['id'] =  $jobfamily->id;
               $jobfamilylist[$count]['familyCode'] = $jobfamily->code;
               $jobfamilylist[$count]['Rank'] = null;
                if ($jobfamily->careerpath > 0) {
                    if($SESSION->lang=='ar')
                    {
                        $sql = "SELECT * FROM {files} WHERE itemid = $jobfamily->careerpath_ar AND filearea='jobfamilycareerpath' AND filename != '.'";
                        $careerpathlogorecords = $DB->get_records_sql($sql);
                        //echo $sql . $careerpathlogorecords;exit;
                    }
                    else
                    {
                        $sql = "SELECT * FROM {files} WHERE itemid = :logo AND filearea='jobfamilycareerpath' AND filename != '.'";
                        $careerpathlogorecords = $DB->get_records_sql($sql,array('logo' => $jobfamily->careerpath));

                    }
                    
                    $careerpathlogorecords = $DB->get_records_sql($sql,array('logo' => $jobfamily->careerpath));
                }
                foreach ($careerpathlogorecords AS $careerpathlogorecord) {

                    $logourl = \moodle_url::make_pluginfile_url($careerpathlogorecord->contextid, $careerpathlogorecord->component,
                    $careerpathlogorecord->filearea, $careerpathlogorecord->itemid, $careerpathlogorecord->filepath,
                    $careerpathlogorecord->filename);
                    $careerpathlogorecord->url = $logourl->out();
                }
                if(!empty($careerpathlogorecords)){
                   $jobfamilylist[$count]['creerPathImages'] = array_values($careerpathlogorecords);
                } else {
                   $jobfamilylist[$count]['creerPathImages']= array();
                }
            $count++;
        }
        return $jobfamilylist;
    }   
}
