<?php

use local_organization\organization;

class local_organization_renderer extends plugin_renderer_base {
    public function get_catalog_organizations($filter = false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'manage_organizations','perPage' => 10, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_organization_view';
        $options['templateName']='local_organization/orgdetails';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'manage_organizations',
                'options' => $options,
                'dataoptions' => $dataoptions,
                'filterdata' => $filterdata,
        ];
        if($filter){
            return  $context;
        }else{
            return  $this->render_from_template('theme_academy/cardPaginate', $context);
        }
    }

    public function global_filter($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        return $this->render_from_template('theme_academy/global_filter', $filterparams);
    }
    public function listoforganizations($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        $filterparams['createaction'] = is_siteadmin() ? true : false;
        $filterparams['invoicesummary'] = is_siteadmin() ? true : false;
        echo $this->render_from_template('local_organization/listoforganizations', $filterparams);
    }
    public function get_catalog_assignusers($filter = false) {
        $orgid = optional_param('orgid', 0 , PARAM_INT);
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'manage_ord_enroll_users','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_enrolledusers_view';
        $options['templateName']='local_organization/orglist_users';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id, 'orgid' => $orgid));
        $context = [
                'targetID' => 'manage_ord_enroll_users',
                'options' => $options,
                'dataoptions' => $dataoptions,
                'filterdata' => $filterdata,
        ];
        if($filter){
            return  $context;
        }else{
            return  $this->render_from_template('theme_academy/cardPaginate', $context);
        }
    }

    public function listofenrolledusers($filterparams) {
        global $DB, $PAGE, $OUTPUT, $CFG;
         $systemcontext = context_system::instance();

        $orgid=$filterparams['orgid'];
        $assign_org_officials = false;
        $assigtrainee = false;
        if (is_siteadmin() || has_capability('local/organization:assignusers', context_system::instance()) ){
            $assign_org_officials = true;
            $assigtrainee = true;
        }
        else if (has_capability('local/organization:assessment_operator_view', $systemcontext)) {
            $assigtrainee = true;
        }
        $viewitems = false;
        if(!is_siteadmin() && has_capability('local/organization:manage_communication_officer', context_system::instance()) ){
            $viewitems = true;
        }
        $organizationofficialroleid = $DB->get_field('role', 'id', array('shortname' => 'organizationofficial'));
        $traineeeid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));

        $sql1="SELECT COUNT(lu.userid) FROM {local_users} AS lu  JOIN {role_assignments} AS roa ON lu.userid = roa.userid  WHERE  lu.organization = $orgid AND lu.approvedstatus = 2 AND lu.deleted = 0  AND roa.contextid = $systemcontext->id AND roa.roleid = $organizationofficialroleid";

        $sql2="SELECT COUNT(lu.userid) FROM {local_users} AS lu  JOIN {role_assignments} AS roa ON roa.userid = lu.userid  WHERE  lu.organization = $orgid AND lu.approvedstatus = 2 AND lu.deleted = 0  AND roa.contextid = $systemcontext->id AND roa.roleid = $traineeeid";
        $orgofficialusers =$DB->count_records_sql($sql1);

        $traineeusers =$DB->count_records_sql($sql2);
        $stable = new stdClass();
        $stable->thead = false;
        $stable->start = 0;
        $stable->length = 1;
        $trining_count_sql =  (new organization)->get_listof_orgitems($stable, '',$orgid,1);
        $trainingprogram_count = $trining_count_sql['totalcount'];
        $events_count_sql = (new organization)->get_listof_orgitems($stable, '', $orgid,3);
        $events_count = $events_count_sql['totalcount'];
        $exams_count_sql = (new organization)->get_listof_orgitems($stable, '', $orgid,2);
        $exams_count = $exams_count_sql['totalcount'];
        //var_dump($events_count_sql); exit;
        $programid = $programid;
        $filterparams['organizationofficialroleid'] = $DB->get_field('role', 'id', array('shortname' => 'organizationofficial'));
         $filterparams['traineeid']=$DB->get_field('role', 'id', array('shortname' => 'trainee'));
         $filterparams['orgofficialusers']=$orgofficialusers ? $orgofficialusers : 0;
         $filterparams['traineeusers']=$traineeusers ? $traineeusers : 0;
         $filterparams['orgid']=$orgid;
         $filterparams['url']=$CFG->wwwroot;
         $filterparams['assign_org_officials']=$assign_org_officials;
         $filterparams['assigtrainee']=$assigtrainee;
         $filterparams['viewitems']=$viewitems;
         $filterparams['trainingprogramcount'] = $trainingprogram_count;
         $filterparams['eventscount'] = $events_count;
         $filterparams['examscount'] = $exams_count;
         $filterparams['traineeenrollurl']=$CFG->wwwroot.'/local/organization/orgenrol.php?orgid='.$orgid;
        
        echo $this->render_from_template('local_organization/listoforgenrolledusers', $filterparams);
    }
    
    public function get_catalog_authusers($filter = false) {
        $orgid = optional_param('orgid', 0 , PARAM_INT);
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'manage_authusers','perPage' => 10, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_authusers_view';
        $options['templateName']='local_organization/auth_users';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id, 'orgid' => $orgid));
        $context = [
                'targetID' => 'manage_authusers',
                'options' => $options,
                'dataoptions' => $dataoptions,
                'filterdata' => $filterdata,
        ];
        if($filter){
            return  $context;
        }else{
            return  $this->render_from_template('theme_academy/cardPaginate', $context);
        }
    }
    // public function action_btn() {
    //     global $DB, $PAGE, $OUTPUT;
    //     $systemcontext = context_system::instance();
    //     if (has_capability('local/organization:create', $systemcontext)) {
    //         $header_btns = $this->render_from_template('local_organization/createorg', null);
    //         $actionbtns = $PAGE->add_header_action($header_btns);            
    //         return true;
    //     } else {
    //         return false;
    //     }
    // }
    public function orgenrol($from_userstotal, $from_users, $to_userstotal, $to_users, $myJSON, $org_id) {
        global $DB, $PAGE, $OUTPUT, $CFG;
        echo $this->render_from_template('local_organization/orgenrol', array('from_userstotal' => $from_userstotal, 'from_users' => $from_users, 'to_userstotal' => $to_userstotal, 'to_users' => $to_users, 'myJSON' => $myJSON, 'org_id' => $org_id));        
    }
    
    public function org_info($data) {
        global $DB, $PAGE, $OUTPUT, $CFG;
        require_once($CFG->dirroot . '/local/organization/lib.php');
        if(!empty($data->approval_letter)) {
            $data->approvalurl=approvalletter_url($data->approval_letter);
        }
        $data->description = format_text($data->description,FORMAT_HTML);
        if($currentlang == 'ar') {                 
            list($sectorsql,$ectorparams) = $DB->get_in_or_equal(explode(',',$data->orgsector));
            $querysql = "SELECT titlearabic FROM {local_sector} WHERE id $sectorsql";
            $sectorslists= $DB->get_fieldset_sql($querysql,$ectorparams);

            list($segmentsql,$segmentparams) = $DB->get_in_or_equal(explode(',',$data->orgsegment));
            $segquerysql = "SELECT titlearabic FROM {local_segment} WHERE id $segmentsql";
            $segmentslists= $DB->get_fieldset_sql($segquerysql,$segmentparams);

        } else{
            list($sectorsql,$ectorparams) = $DB->get_in_or_equal(explode(',',$data->orgsector));
            $querysql = "SELECT title FROM {local_sector} WHERE id $sectorsql";
            $sectorslists= $DB->get_fieldset_sql($querysql,$ectorparams);

            list($segmentsql,$segmentparams) = $DB->get_in_or_equal(explode(',',$data->orgsegment));
            $segquerysql = "SELECT title  FROM {local_segment} WHERE id $segmentsql";
            $segmentslists= $DB->get_fieldset_sql($segquerysql,$segmentparams);
        }
        $data->sectors = (COUNT($sectorslists) > 0) ? implode(',', $sectorslists): ''; 
        $data->segments = (COUNT($segmentslists) > 0) ? implode(',', $segmentslists): ''; 
        if($data->orgfieldofwork == 'investmentbanking') {
            $data->orgfieldofwork= get_string('investmentbanking','local_organization');
        } elseif($data->orgfieldofwork == 'realestate') {

            $data->orgfieldofwork= get_string('realestate','local_organization');
       
        } elseif($data->orgfieldofwork == 'insurance') {

            $data->orgfieldofwork= get_string('insurance','local_organization');

        } else {

            $data->orgfieldofwork= get_string('fieldworkother','local_organization');

        }
        if ($data->status == 1) {
            $data->status = get_string('pending','local_organization');
        } else if($data->status == 2) {
             $data->status = get_string('approved','local_organization');
        } else {
            $data->status= get_string('rejected','local_organization');
        }
        $data->discount =$data->discount_percentage; 
        $data->timecreated=userdate($data->timecreated,get_string('strftimedatemonthabbr', 'core_langconfig'));
        $org = $this->render_from_template('local_organization/org_info', $data);
        return $org;
    }

    public function get_orgitems_catalog($filter = false) {
        $orgid = optional_param('id', 0 , PARAM_INT);
        $catid = optional_param('cat', 0 , PARAM_INT);
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'manage_orgitems','perPage' => 10, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_organization_orgitems_list';
        $options['templateName']='local_organization/orgitems_list';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id, 'orgid' => $orgid, 'catid' => $catid));
        $context = [
                'targetID' => 'manage_orgitems',
                'options' => $options,
                'dataoptions' => $dataoptions,
                'filterdata' => $filterdata,
        ];
        if($filter){
            return  $context;
        }else{
            return  $this->render_from_template('theme_academy/cardPaginate', $context);
        }
    }

    public function listoforgitems($filterparams) {
        global $DB, $PAGE, $OUTPUT, $CFG;
        $systemcontext = context_system::instance();
        echo $this->render_from_template('local_organization/listoforgitems', $filterparams);
    }

    public function listof_orgitems($itemslist, $orgid, $catid) {
        global $DB;
        $row = array();
        $count = 0;
        foreach ($itemslist as $list) {
            $record = array();
            $record['id'] = $list->id;
            $currentlang= current_language();
            if($currentlang == 'ar') {
                $name = $list->namearabic;
            } else {
                $name = $list->itemname;
            } 
            if($catid == 1) {
                $sql = " SELECT COUNT(DISTINCT(lu.userid)) FROM {program_enrollments} pe JOIN {local_users} lu ON lu.userid = pe.userid WHERE pe.programid = $list->id AND lu.organization = $orgid";
                $enrolledcount = $DB->count_records_sql($sql);
            } else if($catid == 2) {
                $sql = " SELECT COUNT(DISTINCT(lu.userid)) FROM {exam_enrollments} ee JOIN {local_users} lu ON lu.userid = ee.userid WHERE ee.examid = $list->id AND lu.organization = $orgid";
                $enrolledcount = $DB->count_records_sql($sql);
            } else if($catid == 3) {
                $sql = " SELECT COUNT(DISTINCT(lu.userid)) FROM {local_event_attendees} ea JOIN {local_users} lu ON lu.userid = ea.userid WHERE ea.eventid = $list->id AND lu.organization = $orgid";
                $enrolledcount = $DB->count_records_sql($sql);
            }
            $record['enrolledcount'] = $enrolledcount;
            $record['name'] = $name;
            $record['code'] = $list->code;
            $record['startdate'] = userdate($list->startdate, get_string('strftimedatefullshort', 'core_langconfig'));
            $record['enddate'] = userdate($list->startdate, get_string('strftimedatefullshort', 'core_langconfig'));
            $count++;
            $row[] = $record;
        }
        return array_values($row);
    }    

    public function list($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        $filterparams['createaction'] = is_siteadmin() ? true : false;
        echo $this->render_from_template('local_organization/list', $filterparams);
    }


     public function partnertypes_info($data) {

        $data->description = strip_tags(format_text($data->description,FORMAT_HTML));
        $type = $this->render_from_template('local_organization/partnertypes_info', $data);
        return $type;
    }

    public function organization_partnertypes($filter = false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'partnertypes','perPage' => 25, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_organization_partnertypes';
        $options['templateName']='local_organization/partnertypesdetails';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'partnertypes',
                'options' => $options,
                'dataoptions' => $dataoptions,
                'filterdata' => $filterdata,
        ];
        if($filter){
            return  $context;
        }else{
            return  $this->render_from_template('theme_academy/cardPaginate', $context);
        }
    }
    public function view_invoicesummary($filter = false) {
        global $DB,$USER;
        $systemcontext = \context_system::instance();
        $options = array('targetID' => 'view_invoicesummary','perPage' => 10, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'table');
        $options['methodName']='local_organization_viewinvoices';
        $options['templateName']='local_organization/view_invoices'; 
        $options = json_encode($options);
        $dataoptions = json_encode(array('userid' =>$USER->id,'contextid' => $systemcontext->id));
        $filterdata = json_encode(array());

        $context = [
                'targetID' => 'view_invoicesummary',
                'options' => $options,
                'dataoptions' => $dataoptions,
                'filterdata' => $filterdata
        ];
        if($filter){
            return  $context;
        }else{
            return  $this->render_from_template('theme_academy/cardPaginate', $context);
        }

    }
    public function listofinvoices($filterparams) {
        global $DB, $PAGE, $OUTPUT;

        echo $this->render_from_template('local_organization/listofinvoices', $filterparams);

    }


   
}
