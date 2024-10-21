<?php 
namespace local_trainingprogram\local;

use stdClass;

/**
 * Sector library file
 */
class sectors
{
    
    public function get_sectors($fields=array()) {
        global $DB;
        !empty($fields) ? $select = implode(',', $fields) : $select = '*';

        return $DB->get_records('local_sector', null, '', $select);
    }

    public function get_segments($sectorid=null, $fields=array()) {
        global $DB;
      


        if($sectorid == null) {
            return $DB->get_records('local_segment');
        }



        $lang = current_language();

        if($lang == 'ar') {

            $segmenttitle = 'seg.titlearabic AS title';

        } else {

            $segmenttitle = 'seg.title AS title';

        }

        $sql = "SELECT seg.id,$segmenttitle,seg.code FROM {local_segment} as seg WHERE FIND_IN_SET('$sectorid',seg.sectorid) ORDER By seg.id DESC";

        $segments= $DB->get_records_sql($sql);

        return $segments;


    }

   public function get_segments_from_sectorslist($sectorslist=null, $fields=array()) {
        global $DB;

        

        if($sectorslist == null) {
            return $DB->get_records('local_segment');
        }

        $lang = current_language();

        if($lang == 'ar') {

            $segmenttitle = 'seg.titlearabic AS title';

        } else {

            $segmenttitle = 'seg.title AS title';

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
        !empty($fields) ? $select = implode(',', $fields) : $select = '*';

        return $DB->get_records_sql("SELECT $select FROM {local_jobfamily} WHERE FIND_IN_SET('$segmentid', segmentid) OR segmentid = 0  ORDER BY id DESC");
    }

    public function get_jobroles($jobfamilyid=null, $fields=array()) {
        global $DB;

        if($jobfamilyid == null) {
            return $DB->get_records('local_jobrole_level');
        }
        !empty($fields) ? $select = implode(',', $fields) : $select = '*';

        return $DB->get_records('local_jobrole_level', ['jobfamily' => $jobfamilyid], '', $select);
        
    }

     public function get_competencies($clid=null, $fields=array()) {
        global $DB;

        if($clid == null) {
            return $DB->get_records('local_competencies');
        }
        !empty($fields) ? $select = implode(',', $fields) : $select = '*';

        return $DB->get_records('local_competencies', ['id' => $clid], '', $select);
        
    }

    

}
