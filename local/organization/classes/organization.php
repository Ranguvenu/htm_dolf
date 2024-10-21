<?php

namespace local_organization;

use context_system;
use filters_form;
use csv_import_reader;
use moodle_url;
use core_text;
use stdClass;
use html_writer;
use moodle_exception;

require_once($CFG->dirroot . '/local/organization/filters_form.php');
require_once($CFG->dirroot . '/local/organization/lib.php');
require_once($CFG->dirroot . '/course/lib.php');
use local_trainingprogram\local\trainingprogram as trainingprogram;
class organization
{
    public function organizationsinfo() {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $renderer = $PAGE->get_renderer('local_organization');
        $filterparams  = $renderer->get_catalog_organizations(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-12';
        $filterparams['placeholder'] = get_string('search_organization','local_organization');
        $globalinput = $renderer->global_filter($filterparams);
        $fform = organization_traingingpartner_filters_form($filterparams);
        $organizationdetails = $renderer->get_catalog_organizations();
        $filterparams['organizationdetails'] = $organizationdetails;
        $filterparams['globalinput'] = $globalinput;
        $filterparams['filterform'] = $fform->render();
        $renderer->listoforganizations($filterparams);
    }
    public function add_update_organization($data)
    {
        global $DB, $USER;
        $systemcontext = context_system::instance();
        $data->fullnameinarabic=trim($data->fullnameinarabic);
        $data->fullname=trim($data->fullname);
        if (isset($data->orglogo)) {
            $data->orglogo = $data->orglogo;
           file_save_draft_area_files($data->orglogo, $systemcontext->id, 'local_organization', 'orglogo', $data->orglogo);
        }
        if (isset($data->tax_certificate)) {
            $data->tax_certificate = $data->tax_certificate;
           file_save_draft_area_files($data->tax_certificate, $systemcontext->id, 'local_organization', 'tax_certificate', $data->tax_certificate);
        }
        $data->autoapproval = !empty($data->autoapproval) ? $data->autoapproval : 0;
         
        if($data->orgfieldofwork=='other'){
            $data->otherfieldofwork="{mlang en}".$data->orgfieldofworken."{mlang}"."{mlang ar}".$data->orgfieldofworkab."{mlang}";
        }
        else{
            $data->otherfieldofwork= '';
        }

        if ($data->id) {
            $data->id = $data->id;
            $orgrecord = $DB->get_record('local_organization',['id'=>$data->id]);
            if($data->orgfieldofwork=='other'){
                $data->otherfieldofwork="{mlang en}".$data->orgfieldofworken."{mlang}"."{mlang ar}".$data->orgfieldofworkab."{mlang}";
            }
            else{
                $data->otherfieldofwork= '';
            }
            $data->usermodified =  $USER->id;
            $data->timemodified = time();
            $data->orgsector =  implode(',', array_filter($data->sectors));
            $data->orglogo = $data->orglogo;
            $data->description =$data->description['text'];
            $data->orgsegment =implode(',', array_filter($data->segment));

            
            if(is_siteadmin()) {
                $data->partnertype = $data->partnertype;
                $data->partner = $data->partner;
                $data->orgrank = $data->orgrank;
                $data->autoapproval = $data->autoapproval;
            } else {

                $data->partnertype = $orgrecord->partnertype;
                $data->partner = $orgrecord->partner;
                $data->orgrank = $orgrecord->orgrank;
                $data->autoapproval = $orgrecord->autoapproval;
            }
            $record->id = $DB->update_record('local_organization', $data);
            if ($record->id) {
                return $record;
            } else {
                throw new moodle_exception('Error in Updating');
                return false;
            }
        } else {
            $data->timecreated = time();
            $data->orgsector = implode(',', array_filter($data->sectors));
            $data->orglogo = $data->orglogo;
            $data->orgsegment = implode(',', array_filter($data->segment));
            $data->description = $data->description['text'];
            $record->id = $DB->insert_record('local_organization', $data);
            $data->timecreated = time();
            $data->id = $record->id;
            $notificationlib = new \local_organization\notification();
            $sql = "SELECT lnt.* FROM {local_notification_type} AS lnt
            WHERE shortname IN ('organization_registration') ";
            $notificationtype = $DB->get_record_sql($sql);
            $user = $DB->get_record('user', array('id' => $USER->id, 'deleted' => 0));
            $notificationlib->organization_notification('organization_registration', $touser = $user, $fromuser = $USER, $notificationtype, $waitinglistid = 0, $data);
            if ($record->id) {
                return $record;
            } else {
                throw new moodle_exception('Error in Inserting');
                return false;
            }
        }
    }
    public function set_organization($id){
        global $DB;
        $data = $DB->get_record('local_organization', ['id' => $id], '*', MUST_EXIST);
       
        return $data;
    }
    public function get_listof_organizations($stable, $filterdata) {
        global $DB, $PAGE, $OUTPUT, $CFG;
        $systemcontext = context_system::instance();
        $selectsql = "SELECT * FROM {local_organization} lo ";
        $countsql  = "SELECT COUNT(lo.id) FROM {local_organization} lo ";
        $formsql  = " WHERE 1=1 ";
        if (isset($filterdata->search_query) && trim($filterdata->search_query) != '') {
            $formsql .= " AND (lo.fullname LIKE :fullnamesearch OR lo.fullnameinarabic LIKE :fullnameinarabic OR lo.shortname LIKE :shortnamesearch OR lo.hrfullname LIKE :hrfullnamesearch  OR lo.hremail LIKE :hremailsearch) ";
            $searchparams = array('fullnamesearch' => '%' . trim($filterdata->search_query) . '%', 'shortnamesearch' => '%' . trim($filterdata->search_query) . '%', 'hrfullnamesearch' => '%' . trim($filterdata->search_query) . '%', 'hremailsearch' => '%' . trim($filterdata->search_query) . '%','fullnameinarabic' => '%' . trim($filterdata->search_query) . '%');
        } else {
            $searchparams = array();
        }

        if (!empty($filterdata->partner)) {
            $partner = (int) $filterdata->partner;
            if( $partner == 2) {
                $formsql .= " AND (lo.partner = 0 || lo.partner IS NULL) ";
            } else {
                $formsql .= " AND lo.partner = 1 ";
            }
        }
        if (!empty($filterdata->partnertype)) {
          
            $formsql .= " AND lo.partnertype IN($filterdata->partnertype) ";
          
        }
        if (!empty($filterdata->sector)) {
          
            $formsql .= " AND lo.orgsector IN($filterdata->sector) ";
          
        }
        $params = array_merge($searchparams);
        $totalorganizations = $DB->count_records_sql($countsql . $formsql, $params);
        $formsql .= " ORDER BY lo.id DESC";
        $organizations = $DB->get_records_sql($selectsql . $formsql, $params, $stable->start, $stable->length);
        $orglist = array();
        $count = 0;
        foreach ($organizations as $organization) {

            $orglist[$count]["id"] = $organization->id;

            $tobeapprovedorgfullname = (current_language() == 'ar') ? $organization->fullnameinarabic : $organization->fullname;
            $approvedorgfullname = html_writer::tag('a', $tobeapprovedorgfullname, array('href' => $CFG->wwwroot . '/local/organization/orguser.php?orgid=' . $organization->id));
            $orglist[$count]["fullname"] = $organization->status == 2 ? $approvedorgfullname : $tobeapprovedorgfullname;
            $orglist[$count]["code"] = $organization->shortname;
            $orglist[$count]["hrfullname"] = $organization->hrfullname;
            $orglist[$count]["hremail"] = $organization->hremail;
            $traineeeid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
            $sql1 = "SELECT COUNT(lu.userid) FROM {local_users} AS lu  JOIN {role_assignments} AS roa ON roa.userid = lu.userid  WHERE  lu.organization = $organization->id AND lu.approvedstatus = 2 AND lu.deleted = 0 AND roa.contextid = $systemcontext->id AND roa.roleid = $traineeeid";
            $notraines = $DB->count_records_sql($sql1);
            $statusrecord = $DB->get_field('local_organization', 'status', array('id' => $organization->id));
            if ($statusrecord == 2) {
                $orglist[$count]["status"] = get_string('approved', 'local_organization');
                $orglist[$count]["actions"] = false;
                $orglist[$count]["statustext"] = "success";
            } elseif ($statusrecord == 1) {
                $orglist[$count]["status"] = get_string('pending', 'local_organization');
                $orglist[$count]["actions"] = true;
                $orglist[$count]["statustext"] = "secondary";
            } else {
                $orglist[$count]["status"] = get_string('rejected', 'local_organization');
                $orglist[$count]["actions"] = false;
                $orglist[$count]["statustext"] = "danger";
            }
            $orglist[$count]["nooftrainees"] = !empty($notraines) ? $notraines : 0;
            $orglist[$count]["manageorg"] = false;
            if (has_capability('local/organization:manage', $systemcontext) ||  has_capability('local/organization:manage_communication_officer',$systemcontext)) {
                $orglist[$count]["manageorg"] = true;
            }
            if (empty($orglist[$count]["hremail"])){       
        
                $orglist[$count]["sendmail"] = false;
            } else{            
                if (is_siteadmin() || has_capability('local/organization:manage_communication_officer',$systemcontext)  ) {
                    $orglist[$count]["sendmail"] = true;
                }
            }    
            $count++;
        }
        $coursesContext = array(
            "hascourses" => $orglist,
            "totalorganizations" => $totalorganizations,
            "length" => count($orglist)
        );
        return $coursesContext;
    }
    public function enrol_orgofficial($data) {
        global $DB, $USER;
        $systemcontext = context_system::instance();

        if ($data->user) {
            list($officialsql, $officialparams) = $DB->get_in_or_equal($data->user);
            $querysql = "SELECT id,firstname  FROM {user} WHERE id $officialsql";
            $officialslist = $DB->get_records_sql($querysql, $officialparams);
            $traineeeid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
            // print_r($officialslist);
            foreach ($officialslist as $official) {
                $updateorganization = $DB->execute("UPDATE {local_users} SET organization = $data->orgid WHERE userid = $official->id");
                if ($DB->record_exists('role_assignments', array('roleid' => $traineeeid, 'userid' => $official->id))) {
                    $deleterecord = $DB->delete_records('role_assignments', array('userid' => (int)$official->id, 'roleid' => $traineeeid));
                }
                $roleid = $DB->get_field('role', 'id', array('shortname' => 'organizationofficial'));
                role_assign($roleid, $official->id, $systemcontext->id);
                $notificationlib = new \local_organization\notification();
                $sql = "SELECT lnt.* FROM {local_notification_type} AS lnt
              WHERE shortname IN ('organization_assigning_official') ";
                $notificationtype = $DB->get_record_sql($sql);
                $sqlorg = "SELECT * FROM {local_organization} 
              WHERE id =" . $data->orgid;
                $orgdata = $DB->get_record_sql($sqlorg);
                $row['organization_official_name'] = $official->firstname;
                $row['fullname'] = $orgdata->fullname;
                $row['fullnameinarabic'] = $orgdata->fullnameinarabic;
                $row['orgid'] = $data->orgid;
                $user = $DB->get_record('user', array('id' => $official->id, 'deleted' => 0));
                //   print_r($user);exit;
                $notificationlib->organization_notification('organization_assigning_official', $touser = $user, $fromuser = $USER, $notificationtype, $waitinglistid = 0, $row);
            }
            return true;
        }
    }
    public function getorgname($orgid) {
        global $DB, $PAGE, $OUTPUT, $CFG;
        $lang = current_language();
        if($lang == 'ar') {

            $orgname = $DB->get_field('local_organization', 'fullnameinarabic', array('id' => $orgid));

        } else {

            $orgname = $DB->get_field('local_organization', 'fullname', array('id' => $orgid));
        }
        
        return $orgname;
    }
    public function assignusersview($orgid)
    {
        global $DB, $PAGE, $OUTPUT;

        $renderer = $PAGE->get_renderer('local_organization');
        $filterparams  = $renderer->get_catalog_assignusers(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['labelclasses'] = 'd-none';
        $filterparams['inputclasses'] = 'form-control';
        $filterparams['widthclass'] = 'col-md-6';
        $filterparams['placeholder'] = get_string('search_enrolled_users', 'local_organization');
        $globalinput = $renderer->global_filter($filterparams);
        $enroledusers_view = $renderer->get_catalog_assignusers();
        $filterparams['enrolledusers_view'] = $enroledusers_view;
        $filterparams['globalinput'] = $globalinput;
        $filterparams['orgid'] = $orgid;
        echo $renderer->listofenrolledusers($filterparams);
    }
    public static function get_listof_orgitems($stable, $filterdata=null, $orgid, $catid) {
        global $DB, $USER;
        $params          = array();
        $itemslist      = array();
        $totalcount = 0;
        $concatsql       = '';
        $countsql = "SELECT COUNT(DISTINCT(item.id)) ";
        if($catid == 1) {
            $fromsql = "SELECT DISTINCT(item.id), item.name AS itemname,  item.namearabic AS namearabic, item.code, item.availablefrom AS startdate, item.availableto AS enddate";
            $sql = " FROM {local_trainingprogram} AS item ";
            $sql .= " JOIN {program_enrollments} AS enrol ON enrol.programid = item.id";

        } else if ($catid == 2) {
            $fromsql = "SELECT DISTINCT(item.id), item.exam AS itemname, item.examnamearabic AS namearabic, item.code, item.examdatetime AS startdate, item.enddate";
            $sql = " FROM {local_exams} AS item ";
            $sql .= " JOIN {exam_enrollments} AS enrol ON enrol.examid = item.id";
        } else if ($catid == 3) {
            $fromsql = "SELECT DISTINCT(item.id), item.title AS itemname, item.titlearabic AS namearabic, item.code, item.startdate, item.enddate";
            $sql = " FROM {local_events} AS item ";
            $sql .= " JOIN {local_event_attendees} AS enrol ON enrol.eventid = item.id";
        }
        $sql .= " JOIN {local_users} AS lu ON enrol.userid = lu.userid";
        $sql .= " WHERE lu.organization = $orgid AND lu.approvedstatus = 2  AND lu.deleted = 0";
        if  (isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
            if($catid == 1) {
                $concatsql .= " AND (item.name LIKE :search1 OR item.code LIKE :search2 OR item.namearabic LIKE :search3)";
            } else if ($catid == 2) {
                $concatsql .= " AND (item.exam LIKE :search1  OR item.code LIKE :search2 OR item.examnamearabic LIKE :search3)";
            } else if ($catid == 3) {
                $concatsql .= " AND (item.title LIKE :search1  OR item.code LIKE :search2 OR item.titlearabic LIKE :search3)";
            }
            $params['search1'] = '%'.trim($filterdata->search_query).'%';
            $params['search2'] = '%'.trim($filterdata->search_query).'%';
            $params['search3'] = '%'.trim($filterdata->search_query).'%';
            //$params = array('firstnamesearch' => '%'.trim($filterdata->search_query).'%');
        }
        $sql .= $concatsql;

        if (isset($stable->itemid) && $stable->itemid > 0) {
            $itemslist = $DB->get_record_sql($fromsql . $sql, $params);
        } else {
            try {
                $totalcount = $DB->count_records_sql($countsql . $sql, $params);
                if ($stable->thead == false) {
                    $sql .= " ORDER BY item.id DESC";
                    $itemslist = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
                }
            } catch (\dml_exception $ex) {
                $totalcount = 0;
            }
        }
        if (isset($stable->itemid) && $stable->itemid > 0) {
            return $itemslist;
        } else {
            return compact('itemslist', 'totalcount');
        }
    }

    public function get_listof_enrolledusers($stable, $filterdata, $dataoptions)
    {
        global $DB, $PAGE, $OUTPUT;
        $orgid = json_decode($dataoptions)->orgid;
        $systemcontext = context_system::instance();
        $currentlang= current_language();

        $fullname = trainingprogram::user_fullname_case();

        $organizationofficialroleid = $DB->get_field('role', 'id', array('shortname' => 'organizationofficial'));
        $traineeeid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
        $selectsql = "SELECT  lc.id,lc.userid,$fullname,lc.firstname,lc.lastname,lc.email,lc.firstnamearabic,lc.lastnamearabic,lc.middlenameen,lc.thirdnameen,lc.middlenamearabic,lc.thirdnamearabic,lc.id_number,lc.phone1,roa.roleid,crole.name
           FROM {local_users} as lc
           JOIN {user} as u ON u.id = lc.userid
           JOIN {role_assignments} AS roa ON roa.userid = lc.userid 
           LEFT JOIN {role} crole ON crole.id = roa.roleid 
            WHERE  lc.organization = $orgid AND lc.approvedstatus = 2 AND lc.deleted = 0 AND roa.contextid = $systemcontext->id  AND roa.roleid IN ($organizationofficialroleid , $traineeeid)";
        $countsql = "SELECT COUNT(lc.id)
           FROM {local_users} as lc
           JOIN {user} as u ON u.id = lc.userid
           JOIN {role_assignments} AS roa ON roa.userid = lc.userid
           LEFT JOIN {role} crole ON crole.id = roa.roleid   
           WHERE  lc.organization = $orgid AND lc.approvedstatus = 2 AND lc.deleted = 0 AND roa.contextid = $systemcontext->id AND roa.roleid IN ($organizationofficialroleid , $traineeeid)";
        if (isset($filterdata->search_query) && trim($filterdata->search_query) != '') {
            $formsql .= " AND (lc.firstname LIKE :firstnamesearch OR 
                            lc.lastname LIKE :lastnamesearch OR
                            lc.firstnamearabic LIKE :firstnamearabicsearch OR
                            lc.lastnamearabic LIKE :lastnamearabicesearch OR
                            lc.middlenameen LIKE :middlenameensearch OR
                            lc.middlenamearabic LIKE :middlenamearabicsearch OR
                            lc.thirdnameen LIKE :thirdnameensearch OR 
                            lc.thirdnamearabic LIKE :thirdnamearabicsearch OR 
                            lc.email LIKE :emailsearch  OR 
                            lc.phone1 LIKE :mobilesearch OR
                            lc.id_number LIKE :id_numbersearch  OR
                            crole.name LIKE :rolenamesearch) ";
            $searchparams = array('firstnamesearch' => '%' . trim($filterdata->search_query) . '%',
                                   'lastnamesearch' => '%' . trim($filterdata->search_query) . '%',
                                   'firstnamearabicsearch' => '%' . trim($filterdata->search_query) . '%', 
                                   'lastnamearabicesearch' => '%' . trim($filterdata->search_query) . '%', 
                                   'middlenameensearch' => '%'.trim($filterdata->search_query).'%',
                                  'middlenamearabicsearch' => '%'.trim($filterdata->search_query).'%',
                                  'thirdnameensearch' => '%'.trim($filterdata->search_query).'%',
                                  'thirdnamearabicsearch' => '%'.trim($filterdata->search_query).'%',
                                   'emailsearch' => '%' . trim($filterdata->search_query) . '%', 
                                   'mobilesearch' => '%' . trim($filterdata->search_query) . '%', 
                                   'id_numbersearch' => '%' . trim($filterdata->search_query) . '%', 
                                   'rolenamesearch' => '%' . trim($filterdata->search_query) . '%'
                               );
        } else {
            $searchparams = array();
        }
        $params = array_merge($searchparams);
        $totalusers = $DB->count_records_sql($countsql . $formsql, $params);
        $formsql .= " ORDER BY lc.id DESC";
        $users = $DB->get_records_sql($selectsql . $formsql, $params, $stable->start, $stable->length);
        $listofusers = array();
        $count = 0;
        foreach ($users as $user) {
            $listofusers[$count]['id'] = $user->id;
            $listofusers[$count]['fullname'] = $user->fullname;
            $listofusers[$count]['email'] = $user->email;
            $listofusers[$count]['id_number'] = $user->id_number;
            $listofusers[$count]['phone'] = $user->phone1;
            $listofusers[$count]['organizationname'] = $DB->get_field('local_organization', 'fullname', array('id' => $orgid));
            $role = $DB->get_record('role', array('id' => trim($user->roleid)));
            $listofusers[$count]['roleid'] = !empty($role->id) ? $role->id : 0;
            $listofusers[$count]['rolename'] = !empty($role->name) ? get_string($role->shortname,'local_organization') : '--';
            $listofusers[$count]['roleshortname'] = !empty($role->shortname) ? $role->shortname : '--';
            if (has_capability('local/organization:assignusers', $systemcontext)) {
                $listofusers[$count]["unassignuser"] = true;
            }elseif(has_capability('local/organization:assessment_operator_view', $systemcontext) && $role->shortname == 'trainee') {
                $listofusers[$count]["unassignuser"] = true;
            }
            else {
                $listofusers[$count]["unassignuser"] = false;
            }
            $listofusers[$count]["orgid"] = $orgid;
            $count++;
        }
        $usersContext = array(
            "hascourses" => $listofusers,
            "nocourses" => $nocourse,
            "totalusers" => $totalusers,
            "length" => COUNT($listofusers),
        );
     
        return $usersContext;
    }
    public function get_listof_authusers($stable, $filterdata, $dataoptions)
    {
        global $DB, $PAGE, $OUTPUT;
        $orgid = json_decode($dataoptions)->orgid;
        $systemcontext = context_system::instance();
        $selectsql = "SELECT lcu.*
          FROM {local_users} as lcu
          WHERE lcu.organization = $orgid AND role = 'auth_user'";
        $countsql = "SELECT COUNT(lcu.id)
          FROM {local_users} as lcu
          WHERE lcu.organization = $orgid AND role = 'auth_user'";
        if (isset($filterdata->search_query) && trim($filterdata->search_query) != '') {
            $formsql .= " AND (lcu.firstname LIKE :firstnamesearch OR lcu.lastname LIKE :lastnamesearch OR lcu.firstnamearabic LIKE :firstnamearabicsearch OR lcu.lastnamearabic LIKE :lastnamearabicesearch)";
            $searchparams = array(
                'firstnamesearch' => '%' . trim($filterdata->search_query) . '%',
                'lastnamesearch' => '%' . trim($filterdata->search_query) . '%',
                'firstnamearabicsearch' => '%' . trim($filterdata->search_query) . '%',
                'lastnamearabicesearch' => '%' . trim($filterdata->search_query) . '%',
        );
        } else {
            $searchparams = array();
        }
        $params = array_merge($searchparams);
        $totalusers = $DB->count_records_sql($countsql . $formsql, $params);
        $formsql .= " ORDER BY lcu.id DESC";
        $users = $DB->get_records_sql($selectsql . $formsql, $params, $stable->start, $stable->length);
        $listofusers = array();
        $count = 0;
        foreach ($users as $user) {
            $listofusers[$count]['id'] = $user->id;
            $listofusers[$count]['authfirstname'] = $user->firstname;
            $listofusers[$count]['authemail'] = $user->email;
            $listofusers[$count]['authid_number'] = $user->id_number;
            $listofusers[$count]['authphone'] = $user->phone1;
            $role = $DB->get_record('role', array('shortname' => trim($user->role)));
            $listofusers[$count]['authroleid'] = '--';
            $listofusers[$count]['authrolename'] = !empty($user->role) ? trim($user->role) : '--';
            if (has_capability('local/organization:assignusers', $systemcontext)) {
                $listofusers[$count]["unassignuser"] = true;
            } else {
                $listofusers[$count]["unassignuser"] = false;
            }
            $listofusers[$count]["orgid"] = $orgid;
            $count++;
        }
        $usersContext = array(
            "authusers" => $listofusers,
            "nocourses" => $nocourse,
            "totalusers" => $totalusers,
            "length" => COUNT($listofusers),
            "roles" => $userroles,
        );
        return $usersContext;
    }
    public function org_info($orgid)
    {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $data = $DB->get_record('local_organization', ['id' => $orgid], '*', MUST_EXIST);

        
        $lang = current_language();
        if($lang == 'ar') {
            $title = 'seg.titlearabic';
        } else {
            $title = 'seg.title';

        }
        if($data->orgsector && $data->orgsegment ){           
            $sectors = $DB->get_fieldset_sql("SELECT $title as title FROM {local_sector} as seg WHERE seg.id IN($data->orgsector) ");
            $orgsector = implode(',',$sectors);

            $segment = $DB->get_fieldset_sql("SELECT $title as title FROM {local_segment} as seg WHERE seg.id IN($data->orgsegment) ");
            $segment = implode(',',$segment);
        }  else {   

            $data->orgsector = !empty($orgsector) ? $orgsector : "";
            $data->orgsegment = !empty($segment) ? $segment : "";
        
        }
        
        $renderer = $PAGE->get_renderer('local_organization');
        $org  = $renderer->org_info($data);
        return $org;
    }

    public function uu_validate_user_upload_columns(csv_import_reader $cir, $stdfields, $profilefields, $returnurl)
    {
        $columns = $cir->get_columns();
       
        if (empty($columns)) {
            $cir->close();
            $cir->cleanup();
            print_error('cannotreadtmpfile', 'error', $returnurl);
        }
        if (count($columns) < 1) {
            $cir->close();
            $cir->cleanup();
            print_error('csvfewcolumns', 'error', $returnurl);
        }
        $processed = array();
        foreach ($columns as $key => $unused) {
            $field = strtolower(str_replace(' ','',$columns[$key]));
            $lcfield = false;
            if (in_array($field, $stdfields)) {
                // standard fields are only lowercase
                $newfield = $field;
            } else {
                $cir->close();
                $cir->cleanup();
                print_error('invalidfieldname', 'error', $returnurl, $field);
            }
            if (in_array($newfield, $processed)) {
                $cir->close();
                $cir->cleanup();
                print_error('duplicatefieldname', 'error', $returnurl, $newfield);
            }
            $processed[$key] = $newfield;
        }
        return $processed;
    }

    public function formatdata_validation($orgid, $data, $linenum, &$formatteddata)
    {
        global $DB, $USER, $REPORT_EXPPORT_FORMATS;
       
        $warnings = array(); // Warnings List
        $errors = array(); // Errors List
        $mfields = array(); // mandatory Fields
        $formatteddata = new stdClass(); //Formatted Data for inserting into DB

        $record = $DB->get_record('local_users', array('email' => $data->email));
        $formatteddata->id = !empty($record->id) ? $record->id : '';
        if (!validate_email($data->email)) {
            $errors[] = '<div class="alert alert-error" role="alert">Invalid email in line no "' . $linenum . '" of uploaded sheet.</div>';
        } else if (empty($record)) {
            $mfields[] = 'email';
            $errors[] = '<div class="alert alert-error" role="alert">Please enter email in line no. "' . $linenum . '" of uploaded sheet.</div>';
        } else if ($record->organization != $orgid && $record->organization != 0) {
            $errors[] = '<div class="alert alert-error" role="alert">Email in line no "' . $linenum . '" is not registered with the current organization.</div>';
        } else if ($record->delete != 0) {
            $errors[] = '<div class="alert alert-error" role="alert">Email in line no "' . $linenum . '" is Deleted.</div>';
        } else if ($record->role != 'auth_user') {
            $errors[] = '<div class="alert alert-error" role="alert">User already having "' . $record->role . '".</div>';
        } else {
            $recordid = $DB->get_field('local_users', 'id', array('email' => trim($data->email), 'organization' => $orgid));
            $formatteddata->email = $data->email;
        }
        $formatteddata->contextlevel = isset($data->contextlevel) ? $data->contextlevel : 10;
        return compact('mfields', 'errors');
    }
    public function get_orgusers($query = null, $org = null, $fields = array())
    {
        global $DB;
        
        $lang = current_language();

        $fullname = trainingprogram::user_fullname_case();
        if ($org == null) {
            return $DB->get_records('local_users');
        }
        $systemcontext = context_system::instance();
        $traineeroleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
        $fields = array('lc.firstname','lc.lastname','lc.firstnamearabic','lc.lastnamearabic','lc.middlenamearabic','lc.thirdnamearabic','lc.middlenameen','lc.thirdnameen', 'lc.id_number');
        $likesql = array();
        $i = 0;
        foreach ($fields as $field) {
            $i++;
            $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
            $sqlparams["queryparam$i"] = "%$query%";
        }
        $sqlfields = implode(" OR ", $likesql);
        $concatsql = " AND ($sqlfields) ";
        $sql = " SELECT u.id, $fullname
                 FROM {user} u 
                 JOIN {local_users} lc  ON lc.userid=u.id
                 WHERE u.id > 2 AND u.deleted = 0 AND lc.deleted = 0  AND lc.organization = '$org' AND u.id IN (SELECT userid FROM {role_assignments} 
                 WHERE roleid = $traineeroleid) $concatsql";
                 
        $order = " ORDER BY u.id DESC limit 50";
        $data = $DB->get_records_sql($sql.$order, $sqlparams);
        $return = array_values(json_decode(json_encode(($data)), true));
        return $return;
    }

    public function get_all_users($query = null, $org = null, $fields = array())
    {
        global $DB;
        
        $lang = current_language();

        $fullname = trainingprogram::user_fullname_case();
        if ($org == null) {
            return $DB->get_records('local_users');
        }
        $systemcontext = context_system::instance();
        $fields = array('lc.firstname','lc.lastname','lc.firstnamearabic','lc.lastnamearabic','lc.middlenamearabic','lc.thirdnamearabic','lc.middlenameen','lc.thirdnameen');
        $likesql = array();
        $i = 0;
        foreach ($fields as $field) {
            $i++;
            $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
            $sqlparams["queryparam$i"] = "%$query%";
        }
        $sqlfields = implode(" OR ", $likesql);
        $concatsql = " AND ($sqlfields) ";
        $sql = " SELECT u.id, $fullname
                 FROM {user} u 
                 JOIN {local_users} lc  ON lc.userid=u.id
                 WHERE u.id > 2 AND u.deleted = 0 AND lc.deleted = 0  $concatsql";
                 
        $order = " ORDER BY u.id DESC limit 50";
        $data = $DB->get_records_sql($sql.$order, $sqlparams);
        $return = array_values(json_decode(json_encode(($data)), true));
        return $return;
    }

    public function get_all_organizations($query = null,$offeringid = 0) {
        global $DB;
        
        $lang = current_language();

        $fields = array('fullname', 'fullnameinarabic','shortname');
        $likesql = array();
        $i = 0;
        foreach ($fields as $field) {
            $i++;
            $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
            $sqlparams["queryparam$i"] = "%$query%";
        }
        $sqlfields = implode(" OR ", $likesql);
        $concatsql = " AND ($sqlfields) ";
        if($lang == 'ar') {
            $sql = " SELECT id,fullnameinarabic as fullname " ;
        } else {
            $sql = " SELECT id,fullname " ;
        }

        if($offeringid){
            $offering = $DB->get_record('tp_offerings',['id'=>$offeringid]);
            if(!empty($offering) && $offering->type == 1 && $offering->organization > 0){
                $sql .= " FROM {local_organization}  WHERE id = $offering->organization  AND visible=1 AND status=2 AND  licensekey REGEXP '^[0-9]+$' AND  CHAR_LENGTH(licensekey) >= 10  $concatsql";
            }  else {
                $sql .= " FROM {local_organization} WHERE visible=1 AND status=2 AND  licensekey REGEXP '^[0-9]+$' AND  CHAR_LENGTH(licensekey) >= 10  $concatsql";
            }
        }  else {
            $sql .= " FROM {local_organization} WHERE visible=1 AND status=2 AND  licensekey REGEXP '^[0-9]+$' AND  CHAR_LENGTH(licensekey) >= 10  $concatsql";
        
        }   
        $order = " ORDER BY id DESC limit 50";
        $data = $DB->get_records_sql($sql.$order, $sqlparams);
        $return = array_values(json_decode(json_encode(($data)), true));
        return $return;

    }

    public function get_usersemail($query = null, $org = null, $fields = array()) {
        global $DB, $USER,$PAGE;
        
        $lang = current_language();
        $systemcontext = context_system::instance();

        $query = trim($query);

        if(!empty($query)) {
            $query = " AND lu.email LIKE '%$query%' OR lu.id_number LIKE '%$query%' ";
        }
        

        $traineeeid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));

        if($org > 0) {

          $official_roleid = $DB->get_field('role', 'id', array('shortname' => 'organizationofficial'));
          $sql="SELECT DISTINCT lu.id, lu.email AS fullname FROM {local_users} AS lu 
                JOIN {role_assignments} AS roa ON roa.userid = lu.userid  
                WHERE (lu.organization = $org OR lu.organization = 0 OR lu.organization IS NULL) AND lu.approvedstatus = 2 AND lu.deleted = 0 AND (roleid = $traineeeid  OR roleid = $official_roleid)";

           $order = " limit 50";

        } elseif($org  == 'listofusers') {


            $userrecordexists = $DB->record_exists('local_users',array('userid'=>$USER->id));

            if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $systemcontext) && $userrecordexists) {

                $roleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
                $orgofficalorganization=   $DB->get_field('local_users','organization',array('userid'=>$USER->id));
                $sql = "SELECT DISTINCT lu.id,lu.email AS fullname FROM {local_users} AS lu 
                    WHERE lu.deleted = 0  AND lu.approvedstatus = 2 
                    AND lu.organization='$orgofficalorganization' 
                    AND lu.userid IN (SELECT userid FROM {role_assignments} 
                    WHERE contextid = $systemcontext->id AND roleid = $traineeeid) ";


            } else {
                    
                $sql = "SELECT DISTINCT lu.id,lu.email AS fullname FROM {local_users} AS lu 
                        WHERE lu.deleted = 0  AND lu.approvedstatus = 2 ";
            }

            $order = " ORDER BY lu.id DESC LIMIT 50";  

        } elseif($org  == 'enrollfilter') {

            $sql =" SELECT DISTINCT u.id,u.email AS fullname FROM {user} AS u JOIN {local_users} lu ON lu.userid = u.id
                        JOIN {role_assignments} AS roa ON roa.userid = u.id
                        WHERE lu.deleted = 0  AND lu.approvedstatus = 2  AND  roa.contextid =$systemcontext->id  AND roa.roleid = $traineeeid AND u.email != '' ";

            $userrecordexists = $DB->record_exists('local_users',array('userid'=>$USER->id));
            if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $systemcontext) && $userrecordexists) {
                $orgofficalorganization=   $DB->get_field('local_users','organization',array('userid'=>$USER->id));
                $sql .= " AND lu.organization = $orgofficalorganization";
            }
            $order = " LIMIT 50 ";  

        } 
      
        $data = $DB->get_records_sql($sql.$query.$order);
        $return = array_values(json_decode(json_encode(($data)), true));
        return $return;
    }

     public function get_usersidnumber($query = null, $org = null, $fields = array()) {
        global $DB;
        
        $lang = current_language();
        $systemcontext = context_system::instance();
        $query = trim($query);
        if(!empty($query)) {
            $query = " AND lu.id_number LIKE '%$query%' ";
        }
        $order = " ORDER BY lu.id DESC limit 50 ";      
        $sql = "SELECT lu.id,lu.id_number AS fullname FROM {local_users} AS lu 
                    WHERE 1=1 ";
        $data = $DB->get_records_sql($sql.$query.$order);
        $return = array_values(json_decode(json_encode(($data)), true));
        return $return;
    }

    
    public static function organization_segment($segments = array(), $segmentid = 0)
    {
        global $DB;
        $segmentlist = array();
        if (!empty($segments)) {
            $params = array();
            list($segmentssql, $segmentparams) = $DB->get_in_or_equal($segments, SQL_PARAMS_NAMED, 'sgmnt');
            $params = array_merge($params, $segmentparams);
            $segmentlist = $DB->get_records_sql_menu("SELECT id,CONCAT(code,' ',title) as title FROM {local_segment} WHERE id $segmentssql", $params);
        } elseif ($segmentid) {
            $segmentlist = $DB->get_records_sql_menu("SELECT id,CONCAT(code,' ',title) as title FROM {local_segment} WHERE id=:id", ['id' => $segmentid]);
        }
        return $segmentlist;
    }

    public static function organization_sector($jobroleid = 0)
    {

        global $DB, $USER;

        $lang = current_language();

        if($lang == 'ar') {

            $sectortitle = 'sect.titlearabic';

        } else {

            $sectortitle = 'sect.title';

        }

        if ($jobroleid) {

            $sector = $DB->get_record_sql('SELECT sect.id,$sectortitle as title
                                            FROM {local_sector} as sect 
                                            JOIN {local_segment} as seg ON seg.sectorid=sect.id 
                                            JOIN  {local_jobfamily} as jbfm ON jbfm.segmentid=seg.id 
                                            JOIN {local_jobrole_level} as jbrl ON jbrl.jobfamily=jbfm.id 
                                            WHERE jbrl.id=:jobroleid', ['jobroleid' => $jobroleid]);
        } else {

            $sector = $DB->get_records_sql_menu("SELECT sect.id,$sectortitle as title FROM {local_sector} as sect");

       
        }

        return $sector;
    }

    public static function organization_form_segment($jobroleid = 0, $segments = array())
    {

        global $DB, $USER;

        
        $lang = current_language();

        $segment = array();

        if($lang == 'ar') {
            $segmenttitle = 'seg.titlearabic';

        } else {

            $segmenttitle = 'seg.title';


        }

        if ($jobroleid) {

            $segment = $DB->get_record_sql('SELECT seg.id,$segmenttitle as title
                                                FROM {local_segment} as seg 
                                                JOIN  {local_jobfamily} as jbfm ON jbfm.segmentid=seg.id 
                                                JOIN {local_jobrole_level} as jbrl ON jbrl.jobfamily=jbfm.id 
                                                 WHERE jbrl.id=:jobroleid', ['jobroleid' => $jobroleid]);
        } 

        $segments = is_array($segments) ? implode(',',$segments): $segments;
        if(!empty($segments) AND $segments !='_qf__force_multiselect_submission'){
            $segment = $DB->get_records_sql_menu("SELECT seg.id,$segmenttitle as title FROM {local_segment} as seg WHERE seg.id IN($segments) ");
        }

        return $segment;
    }

    public  function org_enrolled_users($type = null, $org_id = 0, $params, $total = 0, $offset1 = -1, $perpage = -1, $lastitem = 0)
    {
        global $DB, $USER;

        $lang = current_language();
        $fullname = trainingprogram::user_fullname_case($method = 'enrollment');
        $context = context_system::instance();
        $traineeeid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
        $orfofcroleid = $DB->get_field('role', 'id', array('shortname' => 'organizationofficial'));
        if ($total == 0) {
            $sql = "SELECT lc.id, $fullname";
        } else {
            $sql = "SELECT COUNT(lc.id) as total";
        }
        $sql .= " FROM {user} AS u 
                JOIN {local_users} AS lc ON lc.userid = u.id 
                   WHERE lc.approvedstatus = 2 AND lc.deleted = 0 ";
        if ($lastitem != 0) {
            $sql .= " AND lc.id > $lastitem";
        }
        $sql .= " AND lc.userid <> $USER->id";
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

        } else {
            $searchparams = array();
        }
        if (!empty($params['email'])) {
            $sql .= " AND lc.id IN ({$params['email']})";
        }
        if ($type == 'add') {
            $sql .= " AND lc.organization = 0 AND lc.userid IN (SELECT userid FROM {role_assignments} WHERE (roleid =$traineeeid OR roleid =$orfofcroleid))";
        } elseif ($type == 'remove') {
            $sql .= " AND lc.organization = '$org_id' AND lc.userid IN (SELECT userid FROM {role_assignments} WHERE (roleid =$traineeeid OR roleid =$orfofcroleid))";
        }
        $order = ' ORDER BY lc.id ASC ';
        if ($perpage != -1) {
            $order .= "LIMIT $perpage";
        }

        if ($total == 0) {
            $availableusers = $DB->get_records_sql_menu($sql . $order, $searchparams);
        } else {
            $availableusers = $DB->count_records_sql($sql. $order, $searchparams);
        }
        return $availableusers;
    }

     public static function is_organization_mapped($organizationid) {
        global $DB;

       $sql = " 
                SELECT loorg.id
                FROM {local_organization} as loorg
                JOIN {local_users} as lou ON FIND_IN_SET(loorg.id,lou.organization) > 0 WHERE loorg.id = $organizationid AND lou.deleted = 0  
                UNION ALL 
                SELECT loorg.id
                FROM {local_organization} as loorg
                JOIN {organization_requests} as orgr ON FIND_IN_SET(loorg.id,orgr.orgid) > 0 WHERE loorg.id = $organizationid
                UNION ALL 
                SELECT loorg.id
                FROM {local_organization} as loorg
                JOIN {tp_offerings} as tpo ON FIND_IN_SET(loorg.id,tpo.organization) > 0 WHERE loorg.id = $organizationid
                UNION ALL 
                SELECT loorg.id 
                FROM {local_organization} as loorg
                JOIN {local_learningtracks} as lol ON FIND_IN_SET(loorg.id,lol.organization) > 0 
                WHERE loorg.id = $organizationid 
                 ";
        $organization = $DB->record_exists_sql($sql);
        return ($organization) ? 1 : 0;  
    }

    public function orgitems_catalog($orgid)
    {
        global $DB, $PAGE, $OUTPUT;
        $renderer = $PAGE->get_renderer('local_organization');
        $filterparams  = $renderer->get_orgitems_catalog(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['labelclasses'] = 'd-none';
        $filterparams['inputclasses'] = 'form-control';
        $filterparams['placeholder'] = get_string('search', 'local_organization');
        $globalinput = $renderer->global_filter($filterparams);
        $orgitems_view = $renderer->get_orgitems_catalog();
        $filterparams['orgitems_view'] = $orgitems_view;
        $filterparams['globalinput'] = $globalinput;
        $filterparams['orgid'] = $orgid;
        echo $renderer->listoforgitems($filterparams);
    }

    public function send_hr_mail($data){
        global $DB, $USER, $CFG,$PAGE;
        $hrname = $DB->get_field('local_organization','hrfullname',['id' => $data->organizationid]);
        $touser   = new \stdClass(); 
        $touser->email = $data->hremail;
        $touser->firstname = $hrname;
        $touser->lastname = '';
        $touser->maildisplay = true;
        $touser->mailformat = 1; // 0 (zero) text-only emails, 1 (one) for HTML/Text emails.
        $touser->id = -99;
        $touser->firstnamephonetic = '';
        $touser->lastnamephonetic = '';
        $touser->middlename = '';
        $touser->alternatename = '';
        $fromuser = $USER;
        $subject = $data->subject;
        $textmessage = strip_tags($data->message['text']);
        $htmlmessage = $data->message['text'];
        try{
            $sendmail = email_to_user($touser, $fromuser, $subject, $textmessage, $htmlmessage, '','',true, $fromuser->email, fullname($fromuser));
            return $sendmail;
        } catch(moodle_exception $e){
          print_r($e);
        }
        
    }

    public static function organization_license_validation($licensekey) {

        $curl = curl_init();

        curl_setopt_array($curl, array(

          CURLOPT_URL => 'https://api.wathq.sa/v5/commercialregistration/info/'.$licensekey.'',

          CURLOPT_RETURNTRANSFER => true,

          CURLOPT_ENCODING => '',

          CURLOPT_MAXREDIRS => 10,

          CURLOPT_TIMEOUT => 0,

          CURLOPT_FOLLOWLOCATION => true,

          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

          CURLOPT_CUSTOMREQUEST => 'POST',

          CURLOPT_HTTPHEADER => array(

            'apikey: WZceYjZByDYGcyuSd5KTsFH3QGnpVIj1'

          ),

        ));
        $response = curl_exec($curl);

        curl_close($curl);


        return json_decode($response);

    }
    public static function invoicesummaryinfo (){
        global $DB, $PAGE, $OUTPUT;
 
        $systemcontext = context_system::instance();
        $renderer = $PAGE->get_renderer('local_organization');
        $filterparams  = $renderer->view_invoicesummary(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-6';
        $filterparams['placeholder'] = get_string('search','tool_certificate');
        $globalinput=$renderer->global_filter($filterparams);
        $invoicesummaryinfo = $renderer->view_invoicesummary();
        $fform = organization_invoice_filters_form($filterparams);
        $filterparams['invoices'] = $invoicesummaryinfo;
        $filterparams['filterform'] = $fform->render();
        $filterparams['globalinput'] = $globalinput;
        $renderer->listofinvoices($filterparams);

    }
    public function get_list_invoicesummary($stable,$filterdata){
        global $DB;
        $lang = current_language();
        $systemcontext = context_system::instance(); 
      
        $selectsql = "SELECT DISTINCT tpsi.id, tpsi.productid,tp.category, tpsi.invoice_number AS invoice, tpsi.status AS 'status', tpsi.seats, tpsi.payableamount AS amount, tpsi.timecreated AS createdate,CONCAT(u.firstname, ' ', u.lastname) AS name,tpsi.payment_status, tpsi.type AS invoicetype,toop.orderstatus as orderstatus";
        
        $countsql  = "SELECT COUNT(tpsi.id)";

        $fromsql = " FROM  {tool_product_sadad_invoice} tpsi   
        JOIN {tool_org_order_payments} toop ON tpsi.invoice_number = toop.transactionid
        JOIN  {tool_products} tp ON tpsi.productid = tp.id        
        JOIN {user} u ON u.id = tpsi.userid
        WHERE 1=1";
   
       if(!empty($filterdata->orgoff_name)){      
            $fromsql .= " AND tpsi.userid LIKE '%$filterdata->orgoff_name%' ";
            }
        if(!empty($filterdata->learningtype)){      
                $fromsql .= " AND tp.category LIKE '%$filterdata->learningtype%' ";
                }
        if(!empty($filterdata->invoicestatus)){        
            $fromsql .= " AND tpsi.status = $filterdata->invoicestatus";
        }
       
        if(isset($filterdata->paymentstatus ) && $filterdata->paymentstatus != ''){   
            if($filterdata->paymentstatus == 1){
                $fromsql .= " AND tpsi.payment_status = $filterdata->paymentstatus AND tpsi.status = 1";

            } elseif($filterdata->paymentstatus == 0 ){
                //
                $fromsql .= " AND tpsi.payment_status = $filterdata->paymentstatus AND toop.orderstatus = 1  AND tpsi.status = 1 ";
            }  
            elseif($filterdata->paymentstatus == 2 ){
                //
                $fromsql .= " AND tpsi.payment_status = 0 AND toop.orderstatus = 0 AND tpsi.status = 1 ";
            }        
           
        }
        if ($filterdata->{'fromdate[enabled]'} == 1) {
            $start_year = $filterdata->{'fromdate[year]'};
            $start_month =$filterdata->{'fromdate[month]'};
            $start_day = $filterdata->{'fromdate[day]'};
            $filter_startdate = mktime(0,0,0,$start_month, $start_day, $start_year);
            $fromsql .= " AND tpsi.timecreated >= $filter_startdate ";
        }

        if(!empty($filterdata->{'todate[enabled]'} == 1)){
            $end_year = $filterdata->{'todate[year]'};
            $end_month =$filterdata->{'todate[month]'};
            $end_day = $filterdata->{'todate[day]'};
            $filter_enddate = mktime(23,59,59,$end_month, $end_day, $end_year);
            $fromsql.= " AND tpsi.timecreated <= $filter_enddate";
        }
        



        if (isset($filterdata->search_query) && trim($filterdata->search_query) != '') {
            $fromsql .= " AND (tpsi.invoice_number = :invoicesearch) ";
            $searchparams = array('invoicesearch' =>  trim($filterdata->search_query) , );
        } else {
            $searchparams = array();
        }

        $params = array_merge($searchparams);

        $totalinvoices = $DB->count_records_sql($countsql.$fromsql, $params);
        $fromsql.= " ORDER BY tpsi.id DESC";

       
   
        $invoices = $DB->get_records_sql($selectsql.$fromsql, $params, $stable->start, $stable->length);
      
     

        $invoicelist = array();
        $count = 0;
        
        foreach ( $invoices as $invoice) {
      
            $invoicelist[$count]["invoicenumber"] =  $invoice->invoice;      
            $invoicelist[$count]["invoicetype"] =  ($invoice->invoicetype !=null) ? $invoice->invoicetype :'--';
            $invoicelist[$count]["orgofficial"] =  $invoice->name;  
            
            if($invoice->category == 1){
                $type = get_string('trainingprogram','local_organization');
                $query = "SELECT lt.name AS listitem 
                FROM {tool_product_sadad_invoice} tpsi
                JOIN {tool_products} tp ON tp.id = tpsi.productid
                JOIN {tp_offerings} tpo ON tpo.id = tp.referenceid
                JOIN {local_trainingprogram} lt ON lt.id = tpo.trainingid
                WHERE tp.category = 1 AND tp.id = $invoice->productid";                
            }else if($invoice->category == 2){
                $type = get_string('exam','local_organization');
                $query = "SELECT le.exam AS listitem
                FROM {tool_product_sadad_invoice} tdsi
                JOIN {tool_products} tp ON tp.id = tdsi.productid
                JOIN {local_exam_profiles} lep ON lep.id = tp.referenceid
                JOIN {local_exams} le ON le.id = lep.examid
                WHERE tp.category = 2 AND tp.id = $invoice->productid";
                
            }else if($invoice->category == 3){
                $type = get_string('events','local_organization');
                $query = "SELECT le.title AS listitem
                FROM {tool_product_sadad_invoice} tdsi
                JOIN {tool_products} tp ON tp.id = tdsi.productid
                JOIN {local_events} le ON le.id = tp.referenceid
                WHERE tp.category = 3 AND tp.id = $invoice->productid";
                
            }else if($invoice->category > 3 ){
                $type = "--";
                $query = "SELECT  0 AS listitem
                FROM {tool_product_sadad_invoice} tdsi
                JOIN {tool_products} tp ON tp.id = tdsi.productid
                WHERE tp.category > 3 AND tp.id = $invoice->productid"; 
                
            }else{
                $type ='';
                $query = '';
            }
   
            $invoicelist[$count]["learningtype"] =   $type;
            $invoicelist[$count]["learningitem"] = !empty($query) ? $DB->get_field_sql($query) : '--';
            $invoicelist[$count]["amount"] = !empty($invoice->amount) ? $invoice->amount : '--';
            $invoicelist[$count]["seats"] = $invoice->seats;
            if(!empty($invoice->status)){
                if($invoice->status == 1){
                    $status = get_string('active', 'local_organization');
                    $paystatus  = 'active';

                } else{
                    $status = get_string('inactive', 'local_organization');
                    $paystatus = 'inactive';

                }


              //  $status = ($invoice->status == 1) ? get_string('active', 'block_learnerscript') : get_string('inactive', 'block_learnerscript') ;
            }else{
                $status = '--';
            }
            $invoicelist[$count]["invoicestatus"] =  $status; 


           if($paystatus == 'active' ){
                if(($invoice->orderstatus == 1 && $invoice->payment_status == 1) || ($invoice->orderstatus == 0 && $invoice->payment_status == 1)){
                    
                    $invoicelist[$count]["paymentstatus"] =  get_string('paidin', 'local_organization');
                }elseif($invoice->orderstatus == 1 && $invoice->payment_status == 0){
                
                    $invoicelist[$count]["paymentstatus"] =  get_string('approvedin', 'local_organization');

                }elseif(($invoice->orderstatus == 0 && $invoice->payment_status == 0) ||($invoice->orderstatus == 2 &&      $invoice->payment_status == 0)){
                
                    $invoicelist[$count]["paymentstatus"] =  get_string('due', 'local_organization');

                } 

           } else{
                $invoicelist[$count]["paymentstatus"] =  '---';

           }
       


            
            $count++;
        }
     
        $invoicesContext = array(
            "hasinvoices" => $invoicelist,
            "nodata" => $nodata,
            "totalinvoices" => $totalinvoices,
            "length" => count($invoicelist)
        );
        return $invoicesContext;


          


    }


    public function getsystemlevel_role_users($roleshortname='',$organizationroles=false){

        global $DB;
        $context = context_system::instance();

         $sql = "SELECT u.*
                FROM {role} r 
                JOIN {role_assignments} ra ON ra.roleid = r.id AND ra.contextid = $context->id
                JOIN {user} u ON  u.id =ra.userid 
                JOIN {local_users} AS lc ON lc.userid = u.id
                WHERE u.deleted = 0  AND u.suspended = 0 ";

        if($roleshortname){
     

            $sql.=" AND r.shortname =:shortname ";

            $params['shortname']=$roleshortname;

        }  
        if($organizationroles){

            $sql.= " AND lc.organization =:organization ";

            $params['organization']=$organizationroles;

        }          
        $sql.=" ORDER BY ra.id DESC";

        $systemlevelroleusers = $DB->get_records_sql($sql, $params);    

        return $systemlevelroleusers;
    } 
}
