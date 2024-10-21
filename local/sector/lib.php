<?php

function local_sector_leftmenunode(){
  $systemcontext = context_system::instance();
  $lang = current_language();
  $referralcode = '';
 if(is_siteadmin()){
      $referralcode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_referralcode', 'class'=>'pull-left user_nav_div referralcode'));
      $referral_url = new moodle_url('/local/sector/index.php?lang='.$lang);
      $referral_label = get_string('pluginname','local_sector');
      $referral = html_writer::link($referral_url, '<span class="sectors_icon side_menu_img_icon"></span><span class="user_navigation_link_text">'.$referral_label.'</span>',
      array('class'=>'user_navigation_link'));
      $referralcode .= $referral;
      $referralcode .= html_writer::end_tag('li');
  }
 
  return array('4' => $referralcode);
}
function get_listof_sectors($stable, $filterdata) {
   global $CFG,$DB,$OUTPUT,$USER,$PAGE;
   $selectsql= "SELECT se.id as sectorid,se.title as sector, sg.title as segment, jf.familyname as jobfamily
   FROM {local_sector} as se   
   JOIN {local_segment} as sg   
   ON se.id = sg.sectorid  
   JOIN {local_jobfamily} as jf   
   ON sg.id = jf.segmentid";

   
   $countsql="SELECT count(se.id)
   FROM {local_sector} as se   
   JOIN {local_segment} as sg   
   ON se.id = sg.sectorid  
   JOIN {local_jobfamily} as jf   
   ON sg.id = jf.segmentid";
   $formsql = " WHERE 1=1 ";
if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
        $formsql .= " AND  se.title LIKE :search";
        $searchparams = array('search' => '%'.trim($filterdata->search_query).'%');
    }else{
        $searchparams = array();
    }
    $params = array_merge($searchparams);
    $totalsectors= $DB->count_records_sql($countsql.$formsql, $params);
    $formsql .=" ORDER BY se.id DESC";
    $sectors = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
      $sectorlist = array();
    $count = 0;
    
     foreach ($sectors as $sector) {
        $sectorlist[$count]["sector_id"] = $sector->sectorid;
        $sectorlist [$count]["sector"] = $sector->sector;
        $sectorlist [$count]["segment"] = $sector->segment;
        $sectorlist [$count]["jobfamily"] = $sector->jobfamily;
        $count++;
        }
$coursesContext = array(
        "hascourses" => $sectorlist,   
        "totalsectors" => $totalsectors,
        "length" => count($sectorlist)
        );
    return $coursesContext;

}
function get_jobrole_level_view($stable, $filterdata,$jobfamilyid) {
    global $CFG,$DB,$OUTPUT,$USER,$PAGE;
    $jobid= new stdClass();
    $jobid->id=$jobfamilyid->jobid;
    $selectsql= "SELECT jl.id,jl.title, jl.jobfamily, jl.* FROM {local_jobrole_level} AS jl ";
    $countsql="SELECT count(jl.id) FROM {local_jobrole_level} AS jl ";
    $formsql  = " WHERE jl.jobfamily= $jobid->id ";
    if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
        $formsql .= " AND (jl.title LIKE :stitleearch OR jl.code LIKE :codesearch)";
        $searchparams = array('stitleearch' => '%'.trim($filterdata->search_query).'%','codesearch' => '%'.trim($filterdata->search_query).'%');
    }else{
        $searchparams = array();
    }
    $params = array_merge($searchparams);
    $totalsectors= $DB->count_records_sql($countsql.$formsql, $params);
    $formsql .=" ORDER BY jl.id DESC";
    $jobfamily = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
    $sectorlist = array();
    $count = 0;
    $lang= current_language();
    foreach ($jobfamily as $jobfamilies) {
        $sectorlist [$count]["id"] = $jobfamilies->id;  
        $sectorlist [$count]["jobfamily"] = $jobfamilies->jobfamily;
        if( $lang == 'ar' && !empty($jobfamilies->titlearabic)){
            $title = $jobfamilies->titlearabic;
        }else{
            $title = $jobfamilies->title;
        }
        $sectorlist [$count]["title"] = $title;
        $sectorlist [$count]["code"] = $jobfamilies->code;
        $sectorlist [$count]["timemodified"] =userdate($jobfamilies->timemodified, get_string('strftimedatemonthabbr', 'core_langconfig'));         
        $username=get_complete_user_data('id',$jobfamilies->usermodified);
        $sectorlist [$count]["usermodified"] =$username->firstname ;
        $count++;
    }
 $coursesContext = array(
         "hascourses" => $sectorlist,    
         "totalsectors" => $totalsectors,
         "length" => count($sectorlist)
         );
     return $coursesContext;
 
}

function local_sector_output_fragment_display_responsibility_info($args){
    global $CFG, $DB, $OUTPUT,$PAGE;
    $args = (object) $args;

    $PAGE->requires->js_call_amd('local_sector/jobrole_responsibility_table', 'RespDatatable', array());
    $PAGE->requires->js_call_amd('local_sector/responsibilityview', 'load', array());
    $sql = "SELECT jresp.id,jrole.title,jresp.responsibility FROM {local_jobrole_responsibility} AS jresp JOIN {local_jobrole_level} AS jrole  ON jrole.id = jresp.roleid WHERE jresp.roleid = $args->roleid";

    
    $jobroleinfo = $DB->get_records_sql($sql);
    $table= new \html_table();
    $table->id = 'jobrole';
    $table->head=array(get_string('rolename','local_sector'),get_string('responsibility','local_sector'),get_string('action','local_sector'));
    $table->width=array('30%','50%','20%');
    if($jobroleinfo){
      foreach ($jobroleinfo as $jobrole){
        $deleteaction= '<div class="d-flex action_btns"><a href="javascript:void(0);" onclick="(function(e){ require(\'local_sector/responsibilityview\').deleteConfirm({action:\'deleteteResponse\',contextid:1, component:\'local_sector\', plugintype:\'local\', pluginname:\'sector\', responseid: '.$jobrole->id.'}) })(event)" data-toggle = "tooltip" title = "delete" ><span class="bg-danger text-white"><i class="fa fa-trash-o" aria-hidden="true"></i></span></a></a></div>';

           $roledescription = format_text($jobrole->responsibility,FORMAT_HTML);
           $table->data[]=array($jobrole->title,$roledescription,$deleteaction);
      }
    }
    else{
      $table->data = "No Records Found";
    }
    $data = \html_writer::table($table);

    return $data;
}

function display_lisfof_responsibilities($stable, $filterdata) {
    global $DB, $PAGE, $OUTPUT, $CFG;
    $systemcontext = context_system::instance();
    $lang  = current_language();
    $roleid = $filterdata->roleid;
    $jobfamilyid = $filterdata->jobfamilyid;
    $selectsql = "SELECT ljr.id AS responsibilityid,ljr.jobid,ljr.roleid,ljr.responsibility,loj.familyname,loj.familynamearabic,lojl.title,lojl.titlearabic
    FROM  {local_jobrole_responsibility} AS ljr 
    JOIN {local_jobfamily} AS loj ON loj.id=ljr.jobid
    JOIN {local_jobrole_level} AS lojl ON lojl.id=ljr.roleid
    WHERE ljr.jobid = $jobfamilyid AND ljr.roleid = $roleid  ";

    $countsql  = "SELECT COUNT(ljr.id)  
    FROM  {local_jobrole_responsibility} AS ljr 
    JOIN {local_jobfamily} AS loj ON loj.id=ljr.jobid
    JOIN {local_jobrole_level} AS lojl ON lojl.id=ljr.roleid
    WHERE ljr.jobid = $jobfamilyid AND ljr.roleid = $roleid  ";
    if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
        $searchquery = trim($filterdata->search_query);
        $formsql .= " AND (lojl.title LIKE '%$searchquery%' OR loj.familyname LIKE '%$searchquery%' OR  ljr.responsibility LIKE '%$searchquery%') ";
    } 
    $formsql .=" ORDER BY ljr.id DESC";

    $responsibilities = $DB->get_records_sql($selectsql.$formsql,array(),$stable->start,$stable->length);
    $totalresponsibilities = $DB->count_records_sql($countsql.$formsql);
    $responsibilitylist = array();
    $count = 0;
      $i=1;
    foreach($responsibilities as $responsibility) {

            $responsibilitylist[$count]['id'] = $responsibility->responsibilityid;
            $responsibilitylist[$count]['roleid'] = $responsibility->roleid;
            $responsibilitylist[$count]['jobid'] = $responsibility->jobid;
            if($lang == 'ar') {
              $responsibilitylist[$count]['jobrolename'] = $responsibility->titlearabic;
               $responsibilitylist[$count]['jobfamilyname'] = $responsibility->familynamearabic;
            } else {
               $responsibilitylist[$count]['jobrolename'] = $responsibility->title; 
               $responsibilitylist[$count]['jobfamilyname'] = $responsibility->familyname;
            }
            $responsibility = format_text($responsibility->responsibility,FORMAT_HTML);
            $responsibilitylist[$count]['responsibility'] = wordwrap(strip_tags($responsibility),100,"<br>\n");

        $count++;
    }
    $coursesContext = array(
        "hascourses" => $responsibilitylist,
        "nocourses" => $nocourse,
        "totalresponsibilities" => $totalresponsibilities,
        "length" => count($responsibilitylist),
    );
    return $coursesContext;
}

function local_sector_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {

    if ($context->contextlevel != CONTEXT_SYSTEM) {
        return false;
    }
    if($filearea != 'jobfamilycareerpath'){
        return false;
    }
    $postid = (int)array_shift($args);
    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/local_sector/$filearea/$postid/$relativepath";
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }
    // finally send the file
    send_stored_file($file, 0, 0, true, $options); // download MUST be forced - security!
}

/*function careerpath_url($itemid = 0) {
    global $DB;
    $context = context_system::instance();
    if ($itemid > 0) {
        $sql = "SELECT * FROM {files} WHERE itemid = :logo AND filearea='jobfamilycareerpath' AND filename != '.' ORDER BY id DESC";
        $careerpathlogorecord = $DB->get_record_sql($sql,array('logo' => $itemid),1);
    }
    if (!empty($careerpathlogorecord)) {
        $logourl = moodle_url::make_pluginfile_url($careerpathlogorecord->contextid, $careerpathlogorecord->component,
       $careerpathlogorecord->filearea, $careerpathlogorecord->itemid, $careerpathlogorecord->filepath,
       $careerpathlogorecord->filename);
        $logourl = $logourl->out();
    }
    return $logourl;
}*/
