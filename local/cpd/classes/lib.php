<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or localify
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
 *
 * @package    local_cpd
 * @category   external
 * @copyright  eAbyas <www.eabyas.in>
 */

namespace local_cpd;

use context_system;
use moodle_url;
use stdClass;

require_once($CFG->dirroot . '/local/organization/filters_form.php');
defined('MOODLE_INTERNAL') || die;
class lib
{
    public function create_update_cpd($data)
    {
        global $DB, $USER;
        $systemcontext = context_system::instance();
        if (isset($data->logo)) {
            $data->logo = $data->logo;
            file_save_draft_area_files($data->logo, $systemcontext->id, 'local_cpd', 'logo', $data->logo);
        }
        if (isset($data->description)) {
            $data->description = $data->description['text'];
        }
        if ($data->id) {
            $data->timemodified = time();
            $DB->update_record('local_cpd', $data);
            // Trigger cpd updated.
            $eventparams = array(
                'context' => context_system::instance(),
                'objectid' => $data->id
            );
            $event = event\cpd_updated::create($eventparams);
            $event->trigger();
            $data->orgsector = $data->sectors;
            $data->orgsegment = $data->segment;
            $data->timecreated = time();
            //$data->id = $id;
            $notificationlib = new \local_cpd\notification();
            $sql = "SELECT lnt.* FROM {local_notification_type} AS lnt
            WHERE shortname IN ('cpd_update') ";
            $notificationtype = $DB->get_record_sql($sql);
            $sqluser = "select distinct u.* from {exam_completions} as mc
            inner join {user} as u on u.id = mc.userid 
            inner join {role_assignments} as ra on ra.userid = u.id
            inner join {role} as r on r.id = ra.roleid where shortname = 'trainee' and mc.examid = $data->examid";
            $user = $DB->get_records_sql($sqluser);
            $notificationlib->cpd_notification('cpd_update', $touser = $user, $fromuser = $USER, $notificationtype, $waitinglistid = 0, $data);

            return $data->id;
        } else {
            $data->timecreated = time();
            $data->timemodified = 0;
            $id = $DB->insert_record('local_cpd', $data);
            // Trigger cpd created.
            $eventparams = array(
                'context' => context_system::instance(),
                'objectid' => $id
            );
            $event = event\cpd_created::create($eventparams);
            $event->trigger();
            $data->id = $id;
            $notificationlib = new \local_cpd\notification();
            $sql = "SELECT lnt.* FROM {local_notification_type} AS lnt
            WHERE shortname IN ('cpd_create') ";
            $notificationtype = $DB->get_record_sql($sql);
            $sqluser = "select distinct u.* from {exam_completions} as mc
            inner join {user} as u on u.id = mc.userid 
            inner join {role_assignments} as ra on ra.userid = u.id
            inner join {role} as r on r.id = ra.roleid where shortname = 'trainee' and mc.examid = $data->examid";
            $user = $DB->get_records_sql($sqluser);
            $notificationlib->cpd_notification('cpd_create', $touser = $user, $fromuser = $USER, $notificationtype, $waitinglistid = 0, $data);

            return $id;
        }
    }
    public static function get_listof_cpd($stable, $filtervalues = null) {
        global $DB, $CFG;
        $params          = array();
        $cpd      = array();
        $cpdcount = 0;
        $concatsql       = '';

        if (!empty($filtervalues->search_query) && trim($filtervalues->search_query) != ''){
            $concatsql .= " AND (le.exam LIKE :firstnamesearch OR le.examnamearabic LIKE :arabicnamesearch OR c.code LIKE :codesearch) ";
            $searchparams = array('firstnamesearch' => '%'.trim($filtervalues->search_query).'%', 
            'arabicnamesearch' => '%'.trim($filtervalues->search_query).'%', 'codesearch' => '%'.trim($filtervalues->search_query).'%');
        } else {
            $searchparams = array();
        }
        if (isset($stable->cpdid) && $stable->cpdid > 0) {
            $concatsql .= " AND c.id = $stable->cpdid";
           //$params['cpdid'] = $stable->cpdid;
        }
        $countsql = "SELECT COUNT(c.id) ";
        $fromsql = " SELECT c.*, le.id AS examid, le.exam, le.examnamearabic ";
        $sql = " FROM {local_cpd} c JOIN {local_exams} le ON le.id = c.examid WHERE 1=1";
        $sql .= $concatsql;
        $params = array_merge($searchparams);
        if (isset($stable->cpdid) && $stable->cpdid > 0) {
            $cpd = $DB->get_record_sql($fromsql . $sql, $params);
        } else {
            try {
                $cpdcount = $DB->count_records_sql($countsql . $sql, $params);
                if ($stable->thead == false) {
                    $sql .= " ORDER BY c.id DESC";
                    $cpd = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
                }
            } catch (\dml_exception $ex) {
                $cpdcount = 0;
            }
        }
        if (isset($stable->cpdid) && $stable->cpdid > 0) {
            return $cpd;
        } else {
            return compact('cpd', 'cpdcount');
        }
    }

    public function create_update_evidence($data)
    {
        global $DB, $USER;
        $systemcontext = context_system::instance();
        $data->userid = $USER->id;
        $data->realuser = ($USER->realuser) ? $USER->realuser :0;
        $data->usermodified = $USER->id;
        if ($data->id) {
            $data->timemodified = time();
            $DB->update_record('local_cpd_evidence', $data);
            // Trigger evidence updated.
            $eventparams = array(
                'context' => context_system::instance(),
                'objectid' => $data->id
            );
            $event = event\evidence_updated::create($eventparams);
            $event->trigger();
            return $data->id;
        } else {
            $data->status = 0;
            $data->timecreated = time();
            $data->timemodified = 0;
            $data->id = $DB->insert_record('local_cpd_evidence', $data);
            if ($data->evidencetype == 1) {
                $formalevidence = $_SESSION['formalid'];
                if ($formalevidence) {
                    if (isset($formalevidence->attachment)) {
                        $formalevidence->attachment = $formalevidence->attachment;
                        file_save_draft_area_files($formalevidence->attachment, $systemcontext->id, 'local_cpd', 'logo', $formalevidence->attachment);
                    }
                    if (isset($formalevidence->comment)) {
                        $formalevidence->comment = $formalevidence->comment['text'];
                    }
                    if (isset($formalevidence->relationtocpd)) {
                        $formalevidence->relationtocpd = $formalevidence->relationtocpd;
                    }
                    $formalevidence->evidenceid = $data->id;
                    $formalevidence->usercreated = $USER->id;
                    $formalevidence->timecreated = time();
                    $formalevidence->timemodified = 0;
                    $id = $DB->insert_record('local_cpd_formal_evidence', $formalevidence);
                }
                unset($_SESSION['formalid']);
            } else if ($data->evidencetype == 2) {
                $informalevidence = $_SESSION['informalid'];
                if ($informalevidence) {
                    if (isset($informalevidence->attachment)) {
                        $informalevidence->attachment = $informalevidence->attachment;
                        file_save_draft_area_files($informalevidence->attachment, $systemcontext->id, 'local_cpd', 'logo', $informalevidence->attachment);
                    }
                    if ($informalevidence->type == 4) {
                        $informalevidence->author = $informalevidence->organizer;
                        $informalevidence->editiondate = $informalevidence->activitydate4;
                    }
                    if ($informalevidence->type == 5) {
                        $informalevidence->author = $informalevidence->publisher;
                        $informalevidence->editiondate = $informalevidence->activitydate5;
                    }
                    if (isset($informalevidence->relationtocpd)) {
                        $informalevidence->relationtocpd = $informalevidence->relationtocpd;
                    }
                    if (isset($informalevidence->whatlearned)) {
                        $informalevidence->whatlearned = $informalevidence->whatlearned['text'];
                    }
                    $informalevidence->evidenceid = $data->id;
                    $informalevidence->usercreated = $USER->id;
                    $informalevidence->timecreated = time();
                    $informalevidence->timemodified = 0;
                    $id = $DB->insert_record('local_cpd_informal_evidence', $informalevidence);
                }
                unset($_SESSION['informalid']);
            }
            // Trigger evidence created.
            $eventparams = array('context' => context_system::instance(), 'objectid' => $data->id);
            $event = event\evidence_created::create($eventparams);
            $event->trigger();
            $notificationlib = new \local_cpd\notification();
            $data->id = $id;
            $sql = "SELECT lnt.* FROM {local_notification_type} AS lnt
            WHERE shortname IN ('cpd_evidence_submit') ";
            $notificationtype = $DB->get_record_sql($sql);
            $exam = $DB->get_record('local_exams', array('id' => $data->cpdid));
            $data->certificatename = $exam->exam;
           // $user = $DB->get_record('user', array('id' => get_admin()->id, 'deleted' => 0));
            $notificationlib->cpd_notification('cpd_evidence_submit', $touser = $USER, $fromuser = get_admin(), $notificationtype, $waitinglistid = 0, $data);

            return  $data->id;
        }
    }


    
    public function get_filters() {
        global $DB;
        $filters = array(0 => get_string('statuspending','local_cpd'), 1 => get_string('approve','local_cpd'), 2 => get_string('statusrejected','local_cpd'));
        foreach($filters as $key => $filter) {
            $filtersinfo[] = ['id' => $key, 'name' => $filter];
        };
        return $filtersinfo;        
    }


    public static function get_listof_userevidence($stable, $filterdata = null) {
        global $DB, $USER, $SESSION;
        $SESSION->lang = ($stable->mlang) ? $stable->mlang : current_language() ;
        $params          = array();
        $evidence      = array();
        $evidencecount = 0;
        $concatsql       = '';

       /* if (!empty($filterdata->search_query)) {
            $fields = array(
                "le.exam","c.code","le.examnamearabic"
            );
            $fields = implode(" LIKE :search1 OR ", $fields);
            $fields .= " LIKE :search2 ";
            $fields .= " LIKE :search3 ";
            $params['search1'] = '%' . $filterdata->search_query . '%';
            $params['search2'] = '%' . $filterdata->search_query . '%';
            $params['search3'] = '%' . $filterdata->search_query . '%';
            $concatsql .= " AND ($fields) ";
        }*/
        if (!empty($filterdata->search_query) && trim($filterdata->search_query) != ''){
            $concatsql .= " AND (le.exam LIKE :firstnamesearch OR le.examnamearabic LIKE :arabicnamesearch OR c.code LIKE :codesearch) ";
            $searchparams = array('firstnamesearch' => '%'.trim($filterdata->search_query).'%', 
            'arabicnamesearch' => '%'.trim($filterdata->search_query).'%', 'codesearch' => '%'.trim($filterdata->search_query).'%');
        } else {
            $searchparams = array();
        }

        if (isset($stable->evidenceid) && $stable->evidenceid > 0) {
            $concatsql .= " AND ce.id = :evidenceid";
            $params['evidenceid'] = $stable->evidenceid;
        }
        $countsql = " SELECT COUNT(DISTINCT(ce.cpdid)) ";
        $fromsql = " SELECT ce.id, ce.cpdid, ce.userid, ce.evidencetype, ce.status, ce.dateofachievement,le.exam, le.examnamearabic, c.examid, c.validation, c.hourscreated";
        $sql = " FROM {local_cpd_evidence} ce";
        $sql .=  " JOIN {local_cpd} c ON ce.cpdid = c.id ";
        $sql .=  " JOIN {local_exams} le ON le.id = c.examid ";
        $sql .= " WHERE ce.userid = $USER->id";
        $sql .=  $concatsql ;
        $params = array_merge($searchparams);
        if (isset($stable->evidenceid) && $stable->evidenceid > 0) {
            $evidence = $DB->get_record_sql($fromsql . $sql, $params);
        } else {
            try {
                $evidencecount = $DB->count_records_sql($countsql . $sql, $params);
                if ($stable->thead == false) {
                    $sql .= " GROUP BY ce.cpdid ";
                    $sql .= " ORDER BY ce.id DESC";
                    $evidence = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
                }
            } catch (\dml_exception $ex) {
                $evidencecount = 0;
            }
        }
        if (isset($stable->evidenceid) && $stable->evidenceid > 0) {
            return $evidence;
        } else {
            return compact('evidence', 'evidencecount');
        }
    }

    public static function get_listof_cpdusers($stable, $filterdata = null) {
        global $DB;
        $params          = array();
        $trainees      = array();
        $traineescount = 0;
        $concatsql       = '';
        if (isset($stable->evidenceid) && $stable->evidenceid > 0) {
            $concatsql .= " AND ce.id = :evidenceid";
            $params['evidenceid'] = $stable->evidenceid;
        }
        $countsql = "SELECT COUNT(ce.id) ";
        $fromsql = "  SELECT ce.*, u.firstname, u.id as ucid, u.lastname, c.examid, lu.id_number";
        $sql = " FROM {local_cpd_evidence} ce ";
        $sql .=  " JOIN {user} u ON u.id = ce.userid ";
        $sql .=  " JOIN {local_users} lu ON lu.	userid = u.id ";
        $sql .=  " JOIN {local_cpd} c ON c.id = ce.cpdid ";
        $sql .=  " LEFT JOIN (SELECT moduleid, moduletype,userid, DATEDIFF(DATE_FORMAT(FROM_UNIXTIME(expires),'%Y-%m-%d'), CURDATE()) as expdays FROM {tool_certificate_issues} ) AS le ON le.moduleid = c.examid ";
        $sql .= " WHERE ce.cpdid = $stable->cpdevalid  AND le.moduletype = 'exams' AND le.userid = u.id";
        $sql .= $concatsql;
        if (isset($filterdata->status)) {
            $sql .= " AND ce.status LIKE '%$filterdata->status%' ";
        }
        if (isset($filterdata->user_status)) {
            if($filterdata->user_status == 1) {
                $sql .= " AND le.expdays >= 360";
            }
            if($filterdata->user_status == 2) {
                $sql .= " AND le.expdays <= 360 AND le.expdays >= 300";
            }
            if($filterdata->user_status == 3) {
                $sql .= " AND le.expdays <= 300 AND le.expdays >= 180";
            }
            if($filterdata->user_status == 4) {
                $sql .= " AND le.expdays <= 180  AND le.expdays >= 90";
            }
            if($filterdata->user_status == 5) {
                $sql .= " AND le.expdays <= 90";
            }
        }

        if (!empty($filterdata->id_number)){

             $idnumbers = explode(',', $filterdata->id_number);
             if(!empty($idnumbers)){

                $id_numberquery = array();
                foreach ($idnumbers as $idnumber) {
                    $id_numberquery[] = " ce.userid  = $idnumber "; 
                }
                $idnumparams =implode('OR',$id_numberquery);
                $sql .= ' AND ('.$idnumparams.') ';
            }
        }  
        if (isset($stable->evidenceid) && $stable->evidenceid > 0) {
            $trainees = $DB->get_record_sql($fromsql . $sql, $params);
        } else {
            try {
                $traineescount = $DB->count_records_sql($countsql . $sql, $params);
                if ($stable->thead == false) {
                    $sql .= " ORDER BY ce.id DESC";
                    $trainees = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
                }
            } catch (\dml_exception $ex) {
                $traineescount = 0;
            }
        }
        if (isset($stable->evidenceid) && $stable->evidenceid > 0) {
            return $trainees;
        } else {
            return compact('trainees', 'traineescount');
        }
    }


    public function user_info($ceid, $userid, $evdtype) {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $params = array('ceid' => $ceid, 'userid' => $userid);
        $evidenceuserssql = " SELECT  u.id, u.firstname, u.lastname, fe.*, ce.dateofachievement, ce.status, ce.id AS ceid, ce.rejectionreason, fe.type FROM {local_cpd_evidence} ce JOIN {user} u ON u.id = ce.userid ";
        if ($evdtype == 1) {
            $evidenceuserssql .= " LEFT JOIN {local_cpd_formal_evidence} fe ON fe.evidenceid = ce.id ";
        } else if ($evdtype == 2) {
            $evidenceuserssql .= " LEFT JOIN {local_cpd_informal_evidence} fe ON fe.evidenceid = ce.id ";
        }
        $evidenceuserssql .= " WHERE ce.id = :ceid AND ce.userid = :userid";
        $evidenceusers = $DB->get_record_sql($evidenceuserssql, $params);
        $data = [];
        $localuserrecord = $DB->get_record('local_users',['userid'=>$userid]);
        $data['id'] =  $evidenceusers->ceid;
        $data['username'] = ($localuserrecord)? ((current_language() == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($evidenceusers);
        $evidence_text = array(1 => get_string('formal','local_cpd'), 2 => get_string('informal','local_cpd'));
        $data['evidencetype'] = $evidence_text[$evdtype];
        if ($evidenceusers->status == 0) {
            $data['status'] = true;
        } else {
            $data['status'] = false;
        }
        if ($evdtype == 1) {
            $data['formalevidence'] = true;
            $data['name'] = $evidenceusers->name;
            $data['institutename'] = $evidenceusers->institutename;
            $data['institutelink'] = $evidenceusers->institutelink;
            $data['cityname'] = $evidenceusers->cityname;
            /*$data['startdate'] = date('d/m/Y',$evidenceusers->startdate);
            $data['enddate'] = date('d/m/Y',$evidenceusers->enddate);*/
            $data['startdate'] = userdate($evidenceusers->startdate, '%d/%m/%Y');
            $data['enddate'] = userdate($evidenceusers->enddate, '%d/%m/%Y');
            $data['noofdays'] = $evidenceusers->noofdays;
            $data['creditedhours'] = $evidenceusers->creditedhours;
        }  else if ($evdtype == 2) {
            if($evidenceusers->type == 1 || $evidenceusers->type == 2 || $evidenceusers->type == 3) {
                $data['isauthor'] = true;
            }
            if($evidenceusers->type == 4) {
                $data['organization'] = true;
            }
            if($evidenceusers->type == 5) {
                $data['ispublisher'] = true;
            }
            $data['informalevidence'] = true;
            $data['title'] = $evidenceusers->title;
            $data['institutelink'] = $evidenceusers->institutelink;
            $data['author'] = $evidenceusers->author;
            /*$data['editiondate'] = date('d/m/Y', $evidenceusers->editiondate);*/
            $data['editiondate'] = userdate($evidenceusers->editiondate, '%d/%m/%Y');
            if($evidenceusers->whatlearned) {
                $data['whatlearned'] = format_text($evidenceusers->whatlearned, FORMAT_HTML);
            } else {
                $data['whatlearned'] = '--'; 
            }
            $data['creditedhours'] = $evidenceusers->creditedhours;
            $statusarray = array(0 => get_string('no', 'local_events'),
            1 => get_string('yes', 'local_events'));
            $publishedstatus = $statusarray[$evidenceusers->published];
            $data['published'] = $publishedstatus;
            $data['wordcount'] = $evidenceusers->wordcount;
            $data['pagecount'] = $evidenceusers->pagecount;
        }
        if($evidenceusers->relationtocpd) {
            $data['relationtocpd'] = format_text($evidenceusers->relationtocpd, FORMAT_HTML);
        } else {
            $data['relationtocpd'] = '--';
        }
        if($evidenceusers->rejectionreason) {
            $data['rejectionreason'] = format_text($evidenceusers->rejectionreason, FORMAT_HTML);
        } else {
            $data['rejectionreason'] = '--'; 
        }
        if ((is_siteadmin() || has_capability('local/organization:manage_cpd', context_system::instance() ) ||has_capability('local/cpd:manage', context_system::instance()))) {
            $data['action'] = true;
        }
        if($evidenceusers->attachment) {
            $attachment = (new \local_cpd\local\cpd)->cpd_logo($evidenceusers->attachment); 
        } else {
            $attachment = ''; 
        }
        $data['attachment'] = $attachment;
        if($evidenceusers->comment) {
            $data['comment'] = format_text($evidenceusers->comment, FORMAT_HTML);
        } else {
            $data['comment'] = '--';
        }
        $data['dateofachievement'] = userdate($evidenceusers->dateofachievement, '%d/%m/%Y');
        /*$data['dateofachievement'] = date('d/m/Y',$evidenceusers->dateofachievement);*/
        $renderer = $PAGE->get_renderer('local_cpd');
        $org  = $renderer->user_info($data);
     
        return $org;
    }

    public function get_listof_reported_hrs($stable, $filterdata=false) {
        global $DB, $CFG, $USER, $SESSION;
        $SESSION->lang = ($stable->mlang) ? $stable->mlang : current_language();
        $systemcontext = context_system::instance();
        $selectsql = " SELECT ce.* FROM {local_cpd_evidence} ce";
        $countsql = " SELECT COUNT(DISTINCT(ce.id)) FROM {local_cpd_evidence} ce ";
        $formsql = '';
        if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
            $organization = $DB->get_field('local_users','organization',array('userid' => $USER->id));
            $formsql .= " JOIN {local_users} AS lc ON lc.userid = ce.userid ";
            $formsql .= "  WHERE 1=1  ";
            $formsql .= " AND lc.organization = $organization ";
            
        } else {

           $formsql .= " WHERE 1=1 "; 
           $formsql .= " AND ce.userid = $USER->id ";
        }
       
        $formsql .= "  AND  ce.cpdid = $stable->cpdid ";
        $searchparams = array();
       
        $params = array_merge($searchparams);
        $totalevidence = $DB->count_records_sql($countsql.$formsql, $params);
        $formsql .=" ORDER BY ce.id DESC";

        if ($stable->type == 'mobile') {
            $evidencelists = $DB->get_records_sql($selectsql.$formsql, $params);
        } else {
            $evidencelists = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        }

        $evidencedetails = [];
        if ($evidencelists) {
            $count = 0;
            foreach($evidencelists as $list) {
                $evidencedetails[$count]['id'] = $list->id;
                if ($list->evidencetype == "1") {
                    $formal_learning = $DB->get_record('local_cpd_formal_evidence', ['evidenceid' => $list->id]);
                    $title = $formal_learning->name;
                    $credithrs = $formal_learning->creditedhours;
                } else {
                    $informal_learning = $DB->get_record('local_cpd_informal_evidence', ['evidenceid' => $list->id]);
                    $title = $informal_learning->title;
                    $credithrs = $informal_learning->creditedhours;
                }
                $evidencedetails[$count]['title'] = format_text($title,FORMAT_HTML);
                $evidencedetails[$count]['credithrs'] = $credithrs;
                $evidencedetails[$count]['userapprovedate'] = $list->timecreated;
                $evidencedetails[$count]['approvedate'] = userdate($list->timecreated, '%d %M %Y');
                //$evidencedetails[$count]['approvedate'] = date('d M Y', $list->timecreated);
                $status_text = array(0 => get_string('statuspending','local_cpd'), 1 => get_string('approve','local_cpd'), 2 => get_string('statusrejected','local_cpd'));
                $evidencedetails[$count]['requeststatus'] = $status_text[$list->status];
                $evidencedetails[$count]['status'] = '';
                $evidence_text = array(1 => get_string('formal','local_cpd'), 2 => get_string('informal','local_cpd'));
                $evidencedetails[$count]['evidencetype'] = $evidence_text[$list->evidencetype];
                $evidencedetails[$count]['evidtype'] = $list->evidencetype;
                $action = false;
                if(!is_siteadmin() && has_capability('local/organization:manage_trainee',$systemcontext)) {
                    $action = true;;
                }
                $evidencedetails[$count]['action'] = $action;
                $count++;
            }
            $noreported_hrs = false;
            $pagination = false;
        } else {
            $noreported_hrs = true;
            $pagination = false;
        }
        $evidenceContext = array(
            "hasreportedhrs" => $evidencedetails,
            "noreported_hrs" => $noreported_hrs,
            "totalreported_hrs" => $totalevidence,
            "length" => count($evidencedetails),
        );
        return $evidenceContext;
    }

    public function get_listof_training_programs($stable, $cpdid) {
        global $DB,$USER;
        $selectsql = " SELECT tp.*, lp.id AS programid, lp.name, lp.namearabic,lp.description, lp.courseid, lp.availableto, lp.availablefrom
        FROM {local_cpd_training_programs} tp";
        $countsql = " SELECT COUNT(tp.id) FROM {local_cpd_training_programs} tp";
        $formsql = " JOIN {local_trainingprogram} lp ON lp.id = tp.programid";
        $formsql .= " WHERE tp.cpdid = $cpdid";
        $searchparams = array();
        $params = array_merge($searchparams);
        $totalcount = $DB->count_records_sql($countsql.$formsql, $params);
        $formsql .=" ORDER BY tp.id DESC";
        $programlists = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $programdetails = [];
        if ($programlists) {
            $count = 0;
            foreach($programlists as $list) {
                $lang= current_language();
                $programdetails[$count]['id'] = $list->id;
                $programdetails[$count]['programid'] = $list->programid;
                $programdetails[$count]['cpdid'] = $list->cpdid;
                $programdetails[$count]['courseid'] = $list->courseid;
                if ( $lang == 'ar' && !empty($list->namearabic)){
                    $name = $list->namearabic;
                } else {
                    $name = $list->name;
                }
                $programdetails[$count]['name'] = $name;
               // $programdetails[$count]['dateofcompletion'] = date('d M Y', $list->availableto);
                $description = format_text($list->description);
                $programdetails[$count]['creditedhours'] = $list->creditedhours;
                $todayDate = time();
                $programcompleted = $DB->get_record('trainingprogram_completion',['programid' => $list->programid, 'cpdid' => $list->cpdid]);
                if($programcompleted) {
                    $programdetails[$count]['programcompleted'] = true;
                   // $programdetails[$count]['dateofcompletion'] = date('d M Y', $programcompleted->timecreated);
                    $programdetails[$count]['dateofcompletion'] = userdate($programcompleted->timecreated, '%d %M %Y');
                } else {
                    $programdetails[$count]['programcompleted'] = false;
                    $programdetails[$count]['dateofcompletion'] = '--';
                }
                $isdescription = '';
                if (empty($description)) {
                   $isdescription = false;
                } else {
                    $isdescription = true;
                    if (strlen($description) > 50) {
                        $decsriptionCut = substr($description, 0, 20);
                        $decsriptionstring = format_text($decsriptionCut);
                    } else {
                        $decsriptionstring = "";
                    }
                }
                $action = false;
                $programdetails[$count]['isdescription'] = $isdescription;
                $programdetails[$count]['descriptionstring'] = $decsriptionstring;
                $programdetails[$count]['description'] = $description;
                $action = false;
                //$viewaction = false;
                if ((is_siteadmin() || has_capability('local/organization:manage_cpd', context_system::instance() ) 
                    || has_capability('local/cpd:manage', context_system::instance())
                    || has_capability('local/organization:manage_trainingofficial', context_system::instance()))) {
                    $action = true;
                } else if(!is_siteadmin() && has_capability('local/organization:manage_trainee', context_system::instance() )) {
                        $programenrolled = $DB->record_exists( 'program_enrollments',['programid'=>$list->programid,'userid'=>$USER->id]);
                        if($programenrolled){
                            $viewaction = true;
                        }else {
                            $viewaction = false;
                        }
                }
                $programdetails[$count]['action'] = $action;
                $programdetails[$count]['viewaction'] = $viewaction;
                $count++;
            }
            $norecords = false;
            $pagination = false;
        } else {
            $norecords = true;
            $pagination = false;
        }
        $evidenceContext = array(
            "hasrecords" => $programdetails,
            "norecords" => $norecords,
            "totalcount" => $totalcount,
            "length" => count($programdetails),
        );
        return $evidenceContext;
    }

    public function get_trainingprogramslist($cpdid = null, $query = null) {
        global $DB;
        $lang= current_language();
        $fields = array("lp.name","lp.namearabic");
        $likesql = array();
        $i = 0;
        foreach ($fields as $field) {
            $i++;
            $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
            $sqlparams["queryparam$i"] = "%$query%";
        }
        $sqlfields = implode(" OR ", $likesql);
        $concatsql = " AND ($sqlfields) ";
        if ($cpdid == null) {
        
            if( $lang == 'ar' ){
               $title      = " lp.namearabic";
            } else {
                $title      = " lp.name";
            }
            $sql .= "SELECT lp.id AS id, $title  AS fullname  FROM {local_trainingprogram} lp  WHERE lp.published=1 $concatsql";
        } else {

            if( $lang == 'ar' ){
               $title      = " lp.namearabic";
            } else {
                $title      = " lp.name";
            }
            $sql .= "SELECT lp.id AS id, $title  AS fullname FROM {local_trainingprogram} lp
            WHERE lp.id NOT IN (SELECT clp.programid FROM {local_cpd_training_programs} clp 
                        WHERE clp.programid = lp.id AND clp.cpdid = $cpdid) AND lp.published=1 $concatsql";
        }
        $data = $DB->get_records_sql($sql, $sqlparams);
        $return = array_values(json_decode(json_encode(($data)), true));
        return $return;
    }

    public function get_examlist($query = null) {
        global $DB;
        $fields = array("le.exam","le.examnamearabic");
        $likesql = array();
        $i = 0;
        foreach ($fields as $field) {
            $i++;
            $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
            $sqlparams["queryparam$i"] = "%$query%";
        }
        $sqlfields = implode(" OR ", $likesql);
        $concatsql = " AND ($sqlfields) ";

        $lang = current_language();
        if( $lang == 'ar' ){
            $title      = " le.examnamearabic";
        } else {
            $title      = " le.exam";
        }

        $fields      = " SELECT le.id AS id, $title AS fullname";
        $accountssql = " FROM {local_exams} le
                         WHERE le.id NOT IN( SELECT c.examid FROM {local_cpd} c WHERE c.examid = le.id) AND le.status =1 AND le.certificatevalidity > 0  AND le.id IN (SELECT examid FROM {exam_completions} WHERE completion_status IN (1,2)) $concatsql";
        $data = $DB->get_records_sql($fields.$accountssql, $sqlparams);
        $return = array_values(json_decode(json_encode(($data)), true));
        return $return;
    }

    public function get_cpdlist($query = null) {
        global $DB, $USER;
        $fields = array("le.exam","le.examnamearabic");
        $likesql = array();
        $i = 0;
        foreach ($fields as $field) {
            $i++;
            $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
            $sqlparams["queryparam$i"] = "%$query%";
        }
        $sqlfields = implode(" OR ", $likesql);
        $concatsql = " AND ($sqlfields) ";
        $cpd_evidence = $DB->get_field('local_cpd_evidence', 'userid',['userid' =>  $USER->id]);
        $lang = current_language();
        if($cpd_evidence) {
            if ($lang == 'ar') {
                $cpd_sql = "  SELECT c.id as id, le.examnamearabic AS fullname FROM {local_cpd} as c";
            } else {
                $cpd_sql = " SELECT c.id as id, le.exam as fullname FROM {local_cpd} as c";
            }
            //$cpd_sql = " SELECT c.id as id, le.exam as fullname FROM {local_cpd} as c
            $cpd_sql .= " JOIN {local_exams} le ON le.id = c.examid JOIN {exam_completions} ec ON ec.examid = le.id
                          WHERE c.id NOT IN  (SELECT ce.cpdid
                          FROM {local_cpd_evidence} ce
                          LEFT JOIN (SELECT id, evidenceid, creditedhours FROM {local_cpd_formal_evidence}
                                 UNION
                                SELECT id, evidenceid, creditedhours FROM {local_cpd_informal_evidence}) AS fe 
                          ON fe.evidenceid = ce.id 
                          WHERE ce.status = 1 AND ce.cpdid = c.id 
                          AND ce.userid = $USER->id GROUP BY ce.cpdid HAVING SUM(fe.creditedhours) >= c.hourscreated
                          ) AND ec.userid = $USER->id AND ec.completion_status in(1,2) AND le.certificatevalidity > 0 $concatsql";
        } else {
            //$cpd_sql = " SELECT c.id as id, le.exam as fullname FROM {local_cpd} as c
            if ($lang == 'ar') {
                $cpd_sql = "  SELECT c.id as id, le.examnamearabic AS fullname FROM {local_cpd} as c";
            } else {
                $cpd_sql = " SELECT c.id as id, le.exam as fullname FROM {local_cpd} as c";
            }
            $cpd_sql .= " JOIN {local_exams} le ON le.id = c.examid JOIN {exam_completions} ec ON ec.examid = le.id
                          WHERE le.certificatevalidity > 0 AND ec.userid = $USER->id AND ec.completion_status in (1,2) $concatsql";
        }
        $cpdlist = $DB->get_records_sql($cpd_sql, $sqlparams);
        return $cpdlist;
    }

    public function create_update_trainingprograms($data)
    {
        global $DB, $USER;
        $systemcontext = context_system::instance();
        $programids = (array)$data->programid;
        if ($data->id) {
            $data->timemodified = time();
           $DB->update_record('local_cpd_training_programs', $data);
            // Trigger program training maping.
            $eventparams = array(
                'context' => context_system::instance(),
                'objectid' => $data->id
            );
            $event = event\traning_program_update::create($eventparams);
            $event->trigger();
            return $data->id;
        } else {
            foreach ($programids as $ids) {
                $data->programid = $ids;
                $data->timecreated = time();
                $isprogramcompleted = $DB->record_exists_sql('SELECT * FROM {program_completions} WHERE programid = :programid AND completion_status = :status',['programid'=>$data->programid,'status'=> 1]);
                if($isprogramcompleted) {

                   $programcompletiondata = $DB->get_record('program_completions',['programid'=>$data->programid]);

                    $trainingcompletion   = new \stdClass(); 
                    $trainingcompletion->cpdid = $data->cpdid;
                    $trainingcompletion->programid = $data->programid;
                    $trainingcompletion->userid = $programcompletiondata->userid;
                    $trainingcompletion->hoursachieved = $data->creditedhours;
                    $trainingcompletion->timecreated = $programcompletiondata->completiondate;
                    $createdid =$DB->insert_record('trainingprogram_completion',$trainingcompletion);
                   
                }
                $id = $DB->insert_record('local_cpd_training_programs', $data);
                // Trigger program training maping.
                $eventparams = array(
                    'context' => context_system::instance(),
                    'objectid' => $id
                );
                $event = event\traning_program_mapping::create($eventparams);
                $event->trigger();
                $notificationlib = new \local_cpd\notification();
                $data->id = $id;
                $sql = "SELECT lnt.* FROM {local_notification_type} AS lnt
            WHERE shortname IN ('cpd_training_program_assign') ";
                $notificationtype = $DB->get_record_sql($sql);
                // $user = $DB->get_record('user', array('id' => $USER->id, 'deleted' => 0));
                $sqltp =  "select c.examid,tp.name from {local_cpd_training_programs} as ctp
 inner join {local_cpd} as c on c.id = ctp.cpdid
 inner join {local_trainingprogram} as tp on tp.id = ctp.programid where c.id= $data->cpdid";
                $cpdtp = $DB->get_record_sql($sqltp);
                $data->programname = $cpdtp->name;
                $sqluser = "select distinct u.* from {exam_completions} as mc
inner join {user} as u on u.id = mc.userid 
inner join {role_assignments} as ra on ra.userid = u.id
inner join {role} as r on r.id = ra.roleid where shortname = 'trainee' and mc.examid = $cpdtp->examid";
                $user = $DB->get_records_sql($sqluser);
                $notificationlib->cpd_notification('cpd_training_program_assign', $touser = $user, $fromuser = $USER, $notificationtype, $waitinglistid = 0, $data);
            }
        }
    }

    public function set_cpddata($id) {
        global $DB;
        $systemcontext = context_system::instance();
        $data = $DB->get_record('local_cpd', ['id' => $id], '*', MUST_EXIST);
        $row['id'] = $data->id;
		$row['examid'] = $data->examid;
		$row['code'] = $data->code;
        $draftitemid = file_get_submitted_draft_itemid('logo');
        file_prepare_draft_area($draftitemid, $systemcontext->id, 'local_cpd', 'logo', $data->logo, null);
        $row['logo']  = $draftitemid;
        $row['validation'] = $data->validation;
		$row['hourscreated'] = $data->hourscreated;
		$row['description'] = ['text' => $data->description];
        return $row;
    }

    public function cpdinfo() {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $renderer = $PAGE->get_renderer('local_cpd');
        $filterparams  = $renderer->get_catalog_cpd(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-6';
        $filterparams['placeholder'] = get_string('searchcpd','local_cpd');
        $globalinput = $renderer->global_filter($filterparams);
        $cpddetails = $renderer->get_catalog_cpd();
        $filtersinfo = $this->get_filters();
        $filterparams['cpddetails'] = $cpddetails;
        $filterparams['filtersinfo'] = $filtersinfo;
        $filterparams['globalinput'] = $globalinput;
        $renderer->listofcpd($filterparams);
    }

    public function mycpd_evidenceinfo() {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $renderer = $PAGE->get_renderer('local_cpd');
        $filterparams  = $renderer->get_catalog_evidence(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-6';
        $filterparams['placeholder'] = get_string('searchcpd','local_cpd');
        $globalinput = $renderer->global_filter($filterparams);
        $evidencedetails = $renderer->get_catalog_evidence();
        $filtersinfo = $this->get_filters();
        $filterparams['evidencedetails'] = $evidencedetails;
        $filterparams['filtersinfo'] = $filtersinfo;
        $filterparams['globalinput'] = $globalinput;
        $renderer->listofevidence($filterparams);
    }

    public static function cpd_examslist($exams = array(),$cpdid = 0) {
        global $DB, $USER;
        $lang = current_language();
        if(!empty($exams)){
            list($examssql, $examsparams) = $DB->get_in_or_equal($exams);

            if( $lang == 'ar' ){
                $exam_list = $DB->get_records_sql_menu("SELECT id, examnamearabic as fullname FROM {local_exams} WHERE id $examssql ",$examsparams);
            } else {
                $exam_list = $DB->get_records_sql_menu("SELECT id, exam AS fullname FROM {local_exams} WHERE id $examssql ",$examsparams);
            }

        } elseif($cpdid){

            if( $lang == 'ar' ){
                   $exam_list = $DB->get_records_sql_menu(" SELECT e.id, e.examnamearabic AS fullname FROM {local_exams} e
               JOIN {local_cpd} c ON concat(',', c.examid, ',') LIKE concat('%,',e.id,',%')
               WHERE c.id = :cpdid",['cpdid' => $cpdid]);
            } else {
               $exam_list = $DB->get_records_sql_menu(" SELECT e.id, e.exam AS fullname FROM {local_exams} e
               JOIN {local_cpd} c ON concat(',', c.examid, ',') LIKE concat('%,',e.id,',%')
               WHERE c.id = :cpdid",['cpdid' => $cpdid]);
            }


        }
        return $exam_list;
    }

    public static function cpd_cpdlist($cpdlist = array(),$evidid = 0) {
        global $DB;
        $lang = current_language();
        if(!empty($cpdlist)) {
            list($cpdsql, $cpdparams) = $DB->get_in_or_equal($cpdlist);
            if( $lang == 'ar' ){
                $sql = " SELECT c.id AS id, e.examnamearabic AS fullname FROM {local_exams} e ";
            } else {
                $sql = " SELECT c.id AS id, e.exam AS fullname FROM {local_exams} e ";
            }
            $sql .= "JOIN {local_cpd} c ON c.examid = e.id WHERE c.id $cpdsql";
            $cpd_list = $DB->get_records_sql_menu($sql, $cpdparams);
        } else if($evidid) {
            if( $lang == 'ar' ){
                $sql = " SELECT c.id AS id, e.examnamearabic AS fullname FROM {local_exams} e ";
            } else {
                $sql = " SELECT c.id AS id, e.exam AS fullname FROM {local_exams} e ";
            }
            $sql .=  " JOIN {local_cpd} c ON concat(',', c.examid, ',') LIKE concat('%,',e.id,',%')
            JOIN {local_cpd_evidence} ce ON c.id = ce.cpdid
            WHERE ce.id = :evidid";
            $cpd_list = $DB->get_records_sql_menu($sql, ['evidid' => $evidid]);
        }
        return $cpd_list;
    }

    public function cpd_completion($evalid, $cpdid, $userid, $creditedhours) {
        global $DB;
        $cpddata = $DB->get_record('local_cpd', array('id' => $cpdid));
        $totalhrs = $cpddata->hourscreated;
        $data['cpdid'] = $cpdid;
        $data['userid'] = $userid;
        $data['realuser'] = ($USER->realuser) ? $USER->realuser :0;
        $data['evidenceid'] = $evalid;
        $cpd_completion_exist = $DB->get_record('local_cpd_completion', ['cpdid' => $cpdid, 'userid' => $userid]);
        if($cpd_completion_exist) {
            $data['id'] = $cpd_completion_exist->id;
            $hourcompleted = $cpd_completion_exist->hourcompleted;
            $approvehrs = $hourcompleted + $creditedhours;
            if ($approvehrs >= $totalhrs) {
                $data['hourcompleted'] = $totalhrs;
                $data['status'] = 1;
                $data['completiondate'] = time();
            } else {
                $data['hourcompleted'] = $approvehrs;
                $data['status'] = 0;
            }
            $data['timemodified'] = time();
            $cpd_completion = $DB->update_record('local_cpd_completion', $data);
        } else {
            $approvehrs = $creditedhours;
            if ($approvehrs >= $totalhrs) {
                $data['hourcompleted'] = $totalhrs;
                $data['status'] = 1;
                $data['completiondate'] = time();
            } else {
                $data['hourcompleted'] = $approvehrs;
                $data['status'] = 0;
            }
            $data['timecreated'] = time();
            $cpd_completion = $DB->insert_record('local_cpd_completion', $data);
        }
        $check_cpd_completed =  $DB->record_exists('local_cpd_completion', ['id' => $cpd_completion, 'status' => 1]);
        if($check_cpd_completed) {
            $array = array('userid' => $userid, 'moduleid' => $cpddata->examid,
                'moduletype'=> 'exams');
            $issue = $DB->get_record('tool_certificate_issues', $array);
            $issue->expires = strtotime('+'.$cpddata->validation.' years', $issue->expires);
            // $DB->update_record('tool_certificate_issues',$issue);
            // Regenerate certificate
            //$issue = $DB->get_record('tool_certificate_issues', ['id' => $issue->id], '*', MUST_EXIST);
            // Make sure the user has the required capabilities.
            $template = \tool_certificate\template::instance($issue->templateid);
            if ($template->can_issue($issue->userid)) {
                // Regenerate the issue file.
                $template->create_issue_file($issue, true);
                // Update issue userfullname data.
                if ($user = $DB->get_record('user', ['id' => $issue->userid])) {
                    $issuedata = @json_decode($issue->data, true);
                    $issuedata['userfullname'] = fullname($user);
                    $issue->data = json_encode($issuedata);
                    $DB->update_record('tool_certificate_issues', $issue);
                }
            }
        }
        return $cpd_completion;
    }

    public function cpd_logs($evalid, $cpdid, $userid, $creditedhours, $evidencetype) {
        global $DB;
        $data = [];
        $data['cpdid'] = $cpdid;
        $data['userid'] = $userid;
        $data['realuser'] = ($USER->realuser) ? $USER->realuser :0;
        $data['hoursachieved'] = $creditedhours;
        $data['source'] = $evidencetype;
        $data['dateachieved'] = time();
        $data['timecreated'] = time();
        $cpd_log = $DB->insert_record('local_cpd_hours_log', $data);
        return $cpd_log;
    }

    public function orgcpdinfo() {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $renderer = $PAGE->get_renderer('local_cpd');
        $filterparams  = $renderer->get_catalog_orgcpd(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-6';
        $filterparams['placeholder'] = get_string('searchcpd','local_cpd');
        $globalinput = $renderer->global_filter($filterparams);
        $cpddetails = $renderer->get_catalog_orgcpd();
        $filtersinfo = $this->get_filters();
        $filterparams['cpddetails'] = $cpddetails;
        $filterparams['filtersinfo'] = $filtersinfo;
        $filterparams['globalinput'] = $globalinput;
        $renderer->listoforgcpd($filterparams);
    }

    public static function get_listof_orgcpd($stable, $filtervalues = null) {
        global $DB, $CFG, $USER;
        $params          = array();
        $cpd      = array();
        $cpdcount = 0;
        $concatsql       = '';
        $organization = $DB->get_field('local_users','organization',array('userid'=>$USER->id));

         if (!empty($filtervalues->search_query)) {
            $fields = array(
                "le.exam","c.code"
            );
            $fields = implode(" LIKE :search1 OR ", $fields);
            $fields .= " LIKE :search2 ";
            $params['search1'] = '%' . $filtervalues->search_query . '%';
            $params['search2'] = '%' . $filtervalues->search_query . '%';
            $concatsql .= " AND ($fields) ";
        }

        if (isset($stable->cpdid) && $stable->cpdid > 0) {
            $concatsql .= " AND c.id = :cpdid";
            $params['cpdid'] = $stable->cpdid;
        }
        $countsql = "SELECT COUNT(DISTINCT(c.id)) ";
        $fromsql = " SELECT c.*, le.id AS examid, le.exam, le.examnamearabic, u.id AS ucid, CONCAT(u.firstname,' ',u.lastname) AS fullname ";
        $sql = " FROM {local_cpd} c JOIN {local_exams} le ON le.id = c.examid";
        $sql .= " JOIN {local_cpd_evidence} ce ON ce.cpdid = c.id";
        $sql .= " JOIN {user} u ON u.id = ce.userid";
        $sql .= " JOIN {local_users} AS lc ON lc.userid = u.id ";
        $sql .= "  WHERE 1=1 ";
        $sql.= " AND lc.organization = $organization ";
        $sql .= $concatsql;
        if (isset($stable->cpdid) && $stable->cpdid > 0) {
            $cpd = $DB->get_record_sql($fromsql . $sql, $params);
        } else {
            try {
                $cpdcount = $DB->count_records_sql($countsql . $sql, $params);
                if ($stable->thead == false) {
                    $sql .= " GROUP BY c.id ";
                    $sql .= " ORDER BY c.id DESC";
                    $cpd = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
                }
            } catch (\dml_exception $ex) {
                $cpdcount = 0;
            }
        }
        if (isset($stable->cpdid) && $stable->cpdid > 0) {
            return $cpd;
        } else {
            return compact('cpd', 'cpdcount');
        }
    }

    public function evidence_rejection_update($data) {
        global $DB, $USER;
        $evidence = $DB->get_record('local_cpd_evidence',['id' => $data->evedid]);
        $evidence->status = 2;
        $evidence->usermodified = $USER->id;
        $evidence->timemodified = time();
        $evidence->rejectionreason = $data->reason['text'];
        // var_dump($evidence); exit;
        $record = $DB->update_record('local_cpd_evidence', $evidence);
        if(!empty($data->reason['text'])) {
            $sqlcpd = "SELECT ce.status,e.id as examid,e.exam,ce.userid FROM {local_cpd_evidence} as ce
            JOIN {local_cpd} as c on c.id = ce.cpdid
            JOIN {local_exams} as e on e.id = c.examid 
            WHERE ce.id =" . ($data->evedid);
            $cpdevidence = $DB->get_record_sql($sqlcpd);       
            $data->certificatename = $cpdevidence->exam;
            $data->rejection_reason = $data->reason['text'];
            $data->examid = $cpdevidence->examid;
            $user = $DB->get_record('user', array('id' => $cpdevidence->userid, 'deleted' => 0));
            (new \local_cpd\notification())->cpd_notification('cpd_evidence_reject', $touser = $user,$fromuser=$USER, $data,$waitinglistid=0);
        }
        return $record;
    }

}
