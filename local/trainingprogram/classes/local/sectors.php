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

    public function get_segments($sectorid=null, $query = null,$fields=array()) {
        global $DB;
      


        if($sectorid == null) {
            return $DB->get_records('local_segment');
        }



        $lang = current_language();

        if($lang == 'ar') {

            $segmenttitle = 'seg.titlearabic AS title';
            $searchtitle = 'seg.titlearabic ';

        } else {

            $segmenttitle = 'seg.title AS title';

            $searchtitle = 'seg.title ';

        }

        if(!empty($query)) {
            $query = " AND $searchtitle LIKE '%$query%' ";
        }

        $sql = "SELECT seg.id,$segmenttitle,seg.code FROM {local_segment} as seg WHERE FIND_IN_SET('$sectorid',seg.sectorid) ";

        $order = " ORDER BY id DESC limit 100";

        $segments= $DB->get_records_sql($sql.$query.$order );

        return $segments;


    }

   public function get_segments_from_sectorslist($sectorslist=null , $query = null, $fields=array()) {
        global $DB;

        

        if($sectorslist == null) {
            return $DB->get_records('local_segment');
        }

        $lang = current_language();

        if($lang == 'ar') {

            $segmenttitle = 'seg.titlearabic AS title';

            $searchtitle = 'seg.titlearabic ';

        } else {

            $segmenttitle = 'seg.title AS title';

            $searchtitle = 'seg.title ';

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


            if(!empty($query)) {
                $query = " AND $searchtitle LIKE '%$query%' ";
            }

            $order = " ORDER BY id DESC limit 100";
      
            $segments = $DB->get_records_sql($sql.$formsql.$query.$order);

           return $segments;
       }

    }

    public function get_jobfamilies($segmentid=null,$query = null, $fields=array()) {
        global $DB;

        $segmentid = (int) $segmentid;

        $lang = current_language();

        if($segmentid == null) {
            return $DB->get_records('local_jobfamily');
        }
        !empty($fields) ? $select = implode(',', $fields) : $select = '*';

        if($lang == 'ar') {

        
            $searchtitle = 'familynamearabic';

        } else {


            $searchtitle = 'familyname';

        }

        $sql = "SELECT $select FROM {local_jobfamily} WHERE (FIND_IN_SET('$segmentid', segmentid) OR segmentid = 0) ";

        if(!empty($query)) {
            $query = " AND $searchtitle LIKE '%$query%' ";
        }

        $order = " ORDER BY id DESC limit 100";

        return $DB->get_records_sql($sql.$query.$order);
    }

    public function get_jobroles($jobfamilyid=null,$query = null, $fields=array()) {
        global $DB;

        $lang = current_language();

        if($jobfamilyid == null) {
            return $DB->get_records('local_jobrole_level');
        }
        !empty($fields) ? $select = implode(',', $fields) : $select = '*';

        if($lang == 'ar') {
            $searchtitle = 'titlearabic';
        } else {
            $searchtitle = 'title';
        }

         $sql = "SELECT $select FROM {local_jobrole_level} WHERE FIND_IN_SET('$jobfamilyid', jobfamily) ";

        if(!empty($query)) {
            $query = " AND $searchtitle LIKE '%$query%' ";
        }

        $order = " ORDER BY id DESC limit 100";
        $data = $DB->get_records_sql($sql.$query.$order);
        $other = new stdClass();
        $other->id = -1;
        $other->title = '<span id="createnew" data-action="createnew">other';
        $other->code = 'other';
        
        return $data+['-1' => $other];
        
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
