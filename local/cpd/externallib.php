<?php
/**
 * This file is part of eAbyas
 *
 * Copyright eAbyas Info Solutons Pvt Ltd, India
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author eabyas  <info@eabyas.in>
 * @package Bizlms
 * @subpackage local_classroom
 */
defined('MOODLE_INTERNAL') || die();
global $PAGE, $OUTPUT;
require_once("$CFG->libdir/externallib.php");

class local_cpd_external extends external_api
{

    public function deletecpd_parameters()
    {
        return new external_function_parameters(
            array(
                'action' => new external_value(PARAM_ACTION, 'Action of the event', false),
                'cpdid' => new external_value(PARAM_INT, 'ID of the record', 0),
                'confirm' => new external_value(PARAM_BOOL, 'Confirm', false),
            )
        );
    }

    public static function deletecpd($action, $cpdid, $confirm)
    {
        global $DB,$CFG,$USER;
        try {
            if ($confirm) {
             // notification cpd cancel
             $cpddata=$DB->get_record('local_cpd',array('id'=>$cpdid)); 
             $examdata=$DB->get_record('local_exams',array('id'=>$cpddata->examid));  

             $sqluser = "select distinct u.* from {exam_completions} as mc
                            inner join {user} as u on u.id = mc.userid 
                            inner join {role_assignments} as ra on ra.userid = u.id
                            inner join {role} as r on r.id = ra.roleid where shortname = 'trainee' and mc.examid =".$cpddata->examid;
            $touser = $DB->get_records_sql($sqluser);   
            if(!$touser)
                {$touser=null;}
            $row1=[];
            $row1['RelatedModuleName']=$examdata->exam;
            $myobject=(new \local_cpd\notification);
            $myobject->cpd_notification('cpd_cancel',$touser, $USER,$row1,$waitinglistid=0);
                $DB->delete_records('local_cpd', array('id' => $cpdid));
                // Trigger cpd deleted.
                $eventparams = array('context' => context_system::instance(), 'objectid' => $cpdid);
                $event = local_cpd\event\cpd_deleted::create($eventparams);
                $event->trigger();
                $return = true;
            } else {
                $return = false;
            }
        } catch (dml_exception $ex) {
            print_error('deleteerror', 'local_classroom');
            $return = false;
        }
        return $return;
    }

    public static function deletecpd_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }


    public function evidencestatus_parameters(){
        return new external_function_parameters(
            array(
                'evalid' => new external_value(PARAM_INT, 'ID of the record', 0),
                'confirm' => new external_value(PARAM_BOOL, 'Confirm', false),
                'status' => new external_value(PARAM_INT, 'status of the record', 0),
                'creditedhours'  => new external_value(PARAM_INT, 'creditedhours of the record', 0),
            )
        );
    }

    public static function evidencestatus($evalid, $confirm, $status)
    {
        global $DB, $USER;
        $data = new \stdClass();
        try {
            if ($confirm) {
                // $DB->set_field('local_cpd_evidence', 'status', $status, array('id' => $evalid));
                
                $DB->execute('update {local_cpd_evidence} set status=:status, timemodified=:timemodified, usermodified=:usermodified where id=:id' ,
                         ['status'=>$status,'timemodified'=>time(),'usermodified'=>$USER->id,'id' => $evalid]);

                // Trigger evidence approval.
                $eventparams = array(
                    'context' => context_system::instance(),
                    'objectid' => $evalid
                );
                $sqlcpd = "SELECT ce.status,ce.rejectionreason,e.id as examid,e.exam,ce.userid FROM {local_cpd_evidence} as ce
                inner join {local_cpd} as c on c.id = ce.cpdid
                inner join {local_exams} as e on e.id = c.examid 
                WHERE ce.id =" . ($evalid);
                $cpdevidence = $DB->get_record_sql($sqlcpd);
                // print_r($cpdevidence);
                $data->approve = $cpdevidence->status;
                if ($status == 1) {
                   
                    $event = local_cpd\event\approve_evidence::create($eventparams);
                    $event->trigger();

                    $notificationlib = new \local_cpd\notification();
                    $sql = "SELECT lnt.* FROM {local_notification_type} AS lnt
                WHERE shortname IN ('cpd_evidence_approve') ";
                    $notificationtype = $DB->get_record_sql($sql);
                    $user = $DB->get_record('user', array('id' => $cpdevidence->userid, 'deleted' => 0));             
                    $data->certificatename = $cpdevidence->exam;


                    $notificationlib->cpd_notification('cpd_evidence_approve', $touser = $user, $fromuser = get_admin(), $data, $waitinglistid = 0);
                } else if ($status == 2) {
                        $data->certificatename = $cpdevidence->exam;
                        $data->rejectionreason = $cpdevidence->rejectionreason;
                        $data->examid =  $cpdevidence->examid;
                        $user = $DB->get_record('user', array('id' => $cpdevidence->userid, 'deleted' => 0));
                        (new \local_cpd\notification())->cpd_notification('cpd_evidence_reject', $touser = $user,$fromuser=$USER, $data,$waitinglistid=0);
                    
                }

                $return = true;
            } else {
                $return = false;
            }
        } catch (dml_exception $ex) {
            print_error('statuserror', 'local_cpd');
            $return = false;
        }
        return $return;
    }

    public static function evidencestatus_returns()
    {
        return new external_value(PARAM_BOOL, 'return');
    }

    public function cpd_view_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service', VALUE_OPTIONAL),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
             'contextid' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 1),
             'filterdata' => new external_value(PARAM_RAW, 'filters applied', VALUE_OPTIONAL),
        ]);  
    }

    public function cpd_view($options=false, $dataoptions=false, $offset = 0, $limit = 0, $contextid=1, $filterdata=false) {
        global $DB, $PAGE;
        require_login();
        $systemcontext = context_system::instance();
        // Parameter validation.
        $sitecontext = context_system::instance();
        $PAGE->set_url('/local/cpd/index.php', array());
        $PAGE->set_context($sitecontext);
        $params = self::validate_parameters(
            self::cpd_view_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata,
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);
        $alloptions = json_decode($options);
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $cpd = (new local_cpd\lib)->get_listof_cpd($stable, $filtervalues);
        $totalcount = $cpd['cpdcount'];
        $data = array();
        $action = false;
        if(is_siteadmin() || has_capability('local/cpd:manage', $systemcontext) || has_capability('local/organization:manage_cpd', $systemcontext)) {
            $action = true;
        }
        if($totalcount>0){
            $renderer = $PAGE->get_renderer('local_cpd');
            $data = array_merge($data, $renderer->cpd_list($stable,$filtervalues));
        }
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'action' => $action
        ];
    }

    public function cpd_view_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'action' => new external_value(PARAM_BOOL, 'Taction'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of competencies in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
            'records' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'id' => new external_value(PARAM_INT, 'id'),
                                    'title' => new external_value(PARAM_RAW, 'tilte'),
                                    'code' => new external_value(PARAM_RAW, 'code'), 
                                    'cpdurl' => new external_value(PARAM_RAW, 'cpdurl'),
                                    'validation' => new external_value(PARAM_RAW, 'validation'),
                                    'hourscreated' => new external_value(PARAM_RAW, 'hourscreated'),
                                    'edit' => new external_value(PARAM_RAW, 'edit'),
                                    'delete' => new external_value(PARAM_RAW, 'delete'),
                                )
                            )
            )
        ]);
    }
    public function cpd_usersview_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid'),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);  
    }

    public function cpd_usersview($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE;
        require_login();
        $sitecontext = context_system::instance();
        $PAGE->set_url('/local/cpd/view.php', array());
        $PAGE->set_context($sitecontext);
        // Parameter validation.
        $params = self::validate_parameters(
            self::cpd_usersview_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata,
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);
        $alloptions = json_decode($options);
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $stable->cpdevalid = $alloptions->cpdevalid;
        $tainee = (new local_cpd\lib)->get_listof_cpdusers($stable, $filtervalues);
        $totalcount = $tainee['traineescount'];
        $data = array();
        if($totalcount>0){
            $renderer = $PAGE->get_renderer('local_cpd');
            $data = array_merge($data, $renderer->trainess_list($stable,$filtervalues));
        }
        if($data) {
            $nodata = true;
        } else {
            $nodata = false;
        }
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'norecords' => $nodata,
            'nodata' => get_string('no_data_available','theme_academy')
        ];
    }

    public function cpd_usersview_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of competencies in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
            'records' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'id' => new external_value(PARAM_INT, 'id'),
                                    'username' => new external_value(PARAM_RAW, 'username'),
                                    'userid' => new external_value(PARAM_INT, 'userid'),
                                    'dateclaimed' =>  new external_value(PARAM_RAW, 'dateclaimed'),
                                    'requeststatus' =>  new external_value(PARAM_RAW, 'requeststatus'),
                                    'evidencetype' =>  new external_value(PARAM_RAW, 'evidencetype'),
                                    'statusaction' =>  new external_value(PARAM_BOOL, 'statusaction'),
                                    'userviewurl' =>  new external_value(PARAM_RAW, 'userviewurl'),
                                    'ceid'  => new external_value(PARAM_INT, 'ceid'),
                                    'evdtype'  => new external_value(PARAM_INT, 'evdtype'),
                                    'status'  =>  new external_value(PARAM_RAW, 'status'),
                                    'userid_number' => new external_value(PARAM_RAW, 'userid_number'),
                                    'creditedhours' =>  new external_value(PARAM_RAW, 'creditedhours'),
                                    'cpdid' => new external_value(PARAM_INT, 'cpdid'),
                                    'action' =>  new external_value(PARAM_BOOL, 'action'),
                                )
                            )
                    ),
            'nodata' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
            'norecords' => new external_value(PARAM_BOOL, 'The data for the service', VALUE_OPTIONAL),
        ]);
    }

    public function evidenceuserinfo_parameters() {
        return new external_function_parameters(
            array(
                'ceid' => new external_value(PARAM_INT, 'ceid', 0),
                'userid' => new external_value(PARAM_INT, 'userid', 0),
                'evdtype' => new external_value(PARAM_INT, 'evdtype', 0),
                )
        );

    } 

    public function evidenceuserinfo($ceid, $userid, $evdtype) {
        global $DB;
        require_login();
        $params = self::validate_parameters(self::evidenceuserinfo_parameters(),
                                    ['ceid' => $ceid, 'userid' => $userid]);
        $data = (new local_cpd\lib)->user_info($ceid, $userid, $evdtype);
        return [
            'options' => $data,
        ];
    }

    public function evidenceuserinfo_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
        ]);
    }

    public function cpd_user_evidence_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service', VALUE_OPTIONAL),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid', VALUE_OPTIONAL),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied', VALUE_OPTIONAL),
        ]);
    }

    public function cpd_user_evidence($options=false, $dataoptions=false, $offset = 0, $limit = 0, $contextid=1, $filterdata=false) {
        global $DB, $PAGE, $CFG;
        require_login();
        $sitecontext = context_system::instance();
        $PAGE->set_url('/local/cpd/index.php', array());
        $PAGE->set_context($sitecontext);
        $params = self::validate_parameters(
            self::cpd_user_evidence_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata,
            ]
        );
        $settings = external_settings::get_instance();
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $stable->mlang =  $settings->get_lang();
        $evidence = (new local_cpd\lib)->get_listof_userevidence($stable, $filtervalues);
        $totalcount = $evidence['evidencecount'];
        $data = [];
        if($totalcount >0){
            $renderer = $PAGE->get_renderer('local_cpd');
            $data = array_merge($data, $renderer->evidence_list($stable,$filtervalues));
        }

        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' => $data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'url' => $CFG->wwwroot,
        ];
    }
    public function cpd_user_evidence_returns() {
        return new external_single_structure([
            'url' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of competencies in result set'),
            'records' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'id'),
                        'title' => new external_value(PARAM_RAW, 'title'),
                        'requeststatus' => new external_value(PARAM_RAW, 'requeststatus'),
                        'status' => new external_value(PARAM_RAW, 'status'),
                        'validation' => new external_value(PARAM_RAW, 'validation'),
                        'hourscreated' => new external_value(PARAM_RAW, 'hourscreated'),
                        'hoursclaimed' => new external_value(PARAM_INT, 'hoursclaimed'),
                        'practichrs' => new external_value(PARAM_RAW, 'practichrs'),
                        'kwlghrs' => new external_value(PARAM_RAW, 'manageorg'),
                        'cpdurl' => new external_value(PARAM_RAW, 'cpdurl'),
                        'cpdid' => new external_value(PARAM_RAW, 'cpdid'),
                    )
                )
            )
        ]);
    }

    public function reported_hrs_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid'),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);
    }

    public function reported_hrs($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE, $CFG;
        $systemcontext = context_system::instance();
        $params = self::validate_parameters(
            self::reported_hrs_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata,
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);
        $alloptions = json_decode($options);
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $stable->cpdid = $alloptions->cpdid;
        $stable->userid = $alloptions->userid;
        $data = (new local_cpd\lib)->get_listof_reported_hrs($stable, $filtervalues);
        $totalcount = $data['totalreported_hrs'];
        $action = false;
        if(!is_siteadmin() && has_capability('local/organization:manage_trainee',$systemcontext)) {
            $action = true;;
        }
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'url' => $CFG->wwwroot,
            'action' => $action
        ];
    }

    public function reported_hrs_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'url' => new external_value(PARAM_RAW, 'url'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'action' => new external_value(PARAM_BOOL, 'action'),
            'records' => new external_single_structure(
                    array(
                        'hasreportedhrs' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'id' => new external_value(PARAM_INT, 'id'),
                                    'title' => new external_value(PARAM_RAW, 'title'),
                                    'credithrs' => new external_value(PARAM_RAW, 'credithrs'),
                                    'requeststatus' => new external_value(PARAM_RAW, 'requeststatus'),
                                    'evidencetype' => new external_value(PARAM_RAW, 'evidencetype'),
                                    'evidtype'  => new external_value(PARAM_INT, 'evidtype'),
                                    'approvedate' => new external_value(PARAM_RAW, 'approvedate'),
                                    'action' => new external_value(PARAM_BOOL, 'approvedate'),
                                )
                            )
                        ),
                        'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                        'noreported_hrs' => new external_value(PARAM_BOOL, 'noevidence', VALUE_OPTIONAL),
                        'totalreported_hrs' => new external_value(PARAM_INT, 'totalreported_hrs', VALUE_OPTIONAL),
                        'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                    )
                )
          ]);
    }

    public function user_deleteevidence_parameters() {
        return new external_function_parameters(
            array(
                'evidid' => new external_value(PARAM_INT, 'ID of the record', 0),
                'evidtype' => new external_value(PARAM_INT, 'ID of the evidtype', 0),
                'confirm' => new external_value(PARAM_BOOL, 'Confirm', false),
            )
        );
    }

    public function user_deleteevidence($evidid, $confirm, $evidtype) {
        global $DB;
        try {
            if ($confirm) {
                if ($evidtype == "1") {
                    $DB->delete_records('local_cpd_formal_evidence', array('evidenceid' => $evidid));
                } else {
                    $DB->delete_records('local_cpd_informal_evidence', array('evidenceid' => $evidid)); 
                }
                $DB->delete_records('local_cpd_evidence', array('id' => $evidid));
                $return = true;
            }else {
                $return = false;
            }
        } catch (dml_exception $ex) {
            print_error('deleteerror', 'local_classroom');
            $return = false;
        }
        return $return;
    }
    public function user_deleteevidence_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    public function user_viewevidence_parameters() {
        return new external_function_parameters(
            array(
                'ceid' => new external_value(PARAM_INT, 'ceid', 0),
                'evdtype' => new external_value(PARAM_INT, 'evdtype', 0),
                )
        );
    }

    public function user_viewevidence($ceid, $evdtype ) {
        global $DB, $USER;
        require_login();
        $params = self::validate_parameters(self::user_viewevidence_parameters(),
                                    ['ceid' => $ceid, 'evdtype' => $evdtype]);
                                    
        $data = (new local_cpd\lib)->user_info($ceid, $USER->id, $evdtype);
        return [
            'options' => $data,
        ];
    }

    public function user_viewevidence_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
        ]);
    }

    public function training_programs_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid'),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);
    }

    public function training_programs($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE, $CFG;
        require_login();
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $params = self::validate_parameters(
            self::cpd_user_evidence_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata,
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);
        $alloptions = json_decode($options);
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $stable->cpdid = $alloptions->cpdid;
        $data = (new local_cpd\lib)->get_listof_training_programs($stable, $alloptions->cpdid);
        $totalcount = $data['totalcount'];
        if ((is_siteadmin() ||  has_capability('local/organization:manage_trainee',$systemcontext) 
                            || has_capability('local/organization:manage_cpd', $systemcontext ) 
                            || has_capability('local/cpd:manage', $systemcontext )
                            || has_capability('local/organization:manage_trainingofficial', $systemcontext )
                            || has_capability('local/organization:manage_organizationofficial', $systemcontext ))) {
            $action = true;
        } else {
            $action = false; 
        }
        return [
            'length' => $totalcount,
            'totalcount' => $totalcount,
            'records' => $data,
            'options' => $options,
            'cpdid' => $alloptions->cpdid,
            'dataoptions' => $dataoptions,
            'filterdata' => $filterdata,
            'url' => $CFG->wwwroot,
            'action' => $action
        ];
    }

    public function training_programs_returns() {
        return new external_single_structure([
            'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
            'action' => new external_value(PARAM_BOOL, 'total number of challenges in result set'),
            'records' => new external_single_structure(
                    array(
                        'hasrecords' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'id' => new external_value(PARAM_INT, 'id'),
                                    'programid' => new external_value(PARAM_INT, 'programid'),
                                    'cpdid' => new external_value(PARAM_INT, 'cpdid'),
                                    'courseid'  => new external_value(PARAM_INT, 'courseid'),
                                    'name' => new external_value(PARAM_RAW, 'name'),
                                    'creditedhours' => new external_value(PARAM_RAW, 'creditedhours'),
                                    'dateofcompletion' => new external_value(PARAM_RAW, 'dateofcompletion'),
                                    'isdescription' => new external_value(PARAM_BOOL, 'isdescription'),
                                    'descriptionstring' => new external_value(PARAM_RAW, 'decsriptionstring'),
                                    'description' => new external_value(PARAM_RAW, 'description'),
                                    'action' => new external_value(PARAM_BOOL, 'action'),
                                    'viewaction'  => new external_value(PARAM_BOOL, 'viewaction', VALUE_OPTIONAL),
                                    'programcompleted'  => new external_value(PARAM_BOOL, 'programcompleted'),
                                    
                                )
                            )
                        ),
                        'request_view' => new external_value(PARAM_INT, 'request_view', VALUE_OPTIONAL),
                        'norecords' => new external_value(PARAM_BOOL, 'nousers', VALUE_OPTIONAL),
                        'totalcount' => new external_value(PARAM_INT, 'totalusers', VALUE_OPTIONAL),
                        'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                    )
                ),
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'url' => new external_value(PARAM_RAW, 'url'),
            'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set')
        ]);

    }

    public function form_selector_parameters() {
        return new external_function_parameters([
            'query' => new external_value(PARAM_RAW, 'query'),
            'type' => new external_value(PARAM_RAW, 'type of the organization'),
            'cpdid' => new external_value(PARAM_INT, 'cpdid'),
            
        ]);    
    }

    public function form_selector($query, $type, $cpdid) {
        global $PAGE;
        $params = array( 
            'query' => $query,        
            'type' => $type,
            'cpdid' => $cpdid, 
        );
        $params = self::validate_parameters(self::form_selector_parameters(), $params);
        switch($params['type']) {
            case 'programlist':
                $list = (new local_cpd\lib)->get_trainingprogramslist($params['cpdid'], $params['query']);
            break;
            case 'examlist':
                $list = (new local_cpd\lib)->get_examlist($params['query']);
            break;
            case 'cpdlist':
                $list = (new local_cpd\lib)->get_cpdlist($params['query']);
            break;
        }
        return ['status' => true, 'query' => $query, 'data' => $list];
    }

    public function form_selector_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_INT, 'status: true if success'),
                'query' => new external_value(PARAM_RAW, 'status: true if success'),
                'data' => new external_multiple_structure(new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'status: true if success'),
                        'fullname' => new external_value(PARAM_RAW, 'status: true if success',VALUE_OPTIONAL))
                   )) 
             )
        );
    }

    public function delete_training_program_parameters(){
        return new external_function_parameters(
            array(
                'action' => new external_value(PARAM_ACTION, 'Action of the event', false),
                'programid' => new external_value(PARAM_INT, 'ID of the record', 0),
                'confirm' => new external_value(PARAM_BOOL, 'Confirm', false),
            )
        );
    }

    public static function delete_training_program($action, $programid, $confirm)
    {
        global $DB, $USER;

        $data = new \stdClass();
        $sqltp =  "select c.examid,tp.name from {local_cpd_training_programs} as ctp
 inner join {local_cpd} as c on c.id = ctp.cpdid
 inner join {local_trainingprogram} as tp on tp.id = ctp.programid where ctp.id= $programid";
        //  echo $sqltp;exit;
        $cpdtp = $DB->get_record_sql($sqltp);
        // print_r($cpdtp);exit;
        try {
            if ($confirm) {
                $DB->delete_records('local_cpd_training_programs', array('id' => $programid));
                //Event delete traning Program
                $eventparams = array(
                    'context' => context_system::instance(),
                    'objectid' => $programid
                );
                $event = local_cpd\event\traning_program_delete::create($eventparams);
                $event->trigger();
                $notificationlib = new \local_cpd\notification();
                $data->id = $programid;
                $data->programid = $programid;
                $data->timecreated = time();
                $sql = "SELECT lnt.* FROM {local_notification_type} AS lnt
            WHERE shortname IN ('cpd_training_program_unassign') ";
                $notificationtype = $DB->get_record_sql($sql);
                // $user = $DB->get_record('user', array('id' => $USER->id, 'deleted' => 0));                
                // print_r($cpdtp);
                $data->programname = $cpdtp->name;
                $sqluser = "select distinct u.* from {exam_completions} as mc
inner join {user} as u on u.id = mc.userid 
inner join {role_assignments} as ra on ra.userid = u.id
inner join {role} as r on r.id = ra.roleid where shortname = 'trainee' and mc.examid = $cpdtp->examid";
                $user = $DB->get_records_sql($sqluser);
                $notificationlib->cpd_notification('cpd_training_program_unassign', $touser = $user, $fromuser = $USER, $notificationtype, $waitinglistid = 0, $data);


                $return = true;
            } else {
                $return = false;
            }
        } catch (dml_exception $ex) {
            print_error('deleteerror', 'local_classroom');
            $return = false;
        }
        return $return;
    }

    public static function delete_training_program_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    //Vinod- CPD fake block for exam official - Starts// 

    public function cpdblock_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid'),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);  
    }

    public function cpdblock($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE, $CFG;
        require_login();
        // Parameter validation.
        $sitecontext = context_system::instance();
        $PAGE->set_url('/local/cpd/index.php', array());
        $PAGE->set_context($sitecontext);
        $params = self::validate_parameters(
            self::cpdblock_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata,
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);
        $alloptions = json_decode($options);
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $cpd = (new local_cpd\lib)->get_listof_cpd($stable, $filtervalues);
        $totalcount = $cpd['cpdcount'];
        $data = array();
        if($totalcount > 0){
            $renderer = $PAGE->get_renderer('local_cpd');
            $data = array_merge($data, $renderer->cpd_list($stable,$filtervalues));
        }
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
        ];
    }

    public function cpdblock_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of competencies in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
            'records' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'id'),
                        'title' => new external_value(PARAM_RAW, 'tilte'),
                        'code' => new external_value(PARAM_RAW, 'code'), 
                        'cpdurl' => new external_value(PARAM_RAW, 'cpdurl'),
                        'validation' => new external_value(PARAM_RAW, 'validation'),
                        'hourscreated' => new external_value(PARAM_RAW, 'hourscreated'),
                        'edit' => new external_value(PARAM_RAW, 'edit'),
                        'delete' => new external_value(PARAM_RAW, 'delete'),
                    )
                )
            ),
            'viewmoreurl' => new external_value(PARAM_RAW, 'viewmoreurl', VALUE_OPTIONAL),
        ]);
    }

    //Vinod- CPD fake block for exam official - Starts// 
    public function cpd_orgview_parameters() {
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid'),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);  
    }

    public function cpd_orgview($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $PAGE;
        require_login();
        // Parameter validation.
        $sitecontext = context_system::instance();
        $PAGE->set_url('/local/cpd/index.php', array());
        $PAGE->set_context($sitecontext);
        $params = self::validate_parameters(
            self::cpd_orgview_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata,
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);
        $alloptions = json_decode($options);
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $cpd = (new local_cpd\lib)->get_listof_orgcpd($stable, $filtervalues);
        $totalcount = $cpd['cpdcount'];
        $data = array();
        if($totalcount>0){
            $renderer = $PAGE->get_renderer('local_cpd');
            $data = array_merge($data, $renderer->cpd_list($stable,$filtervalues));
        }
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
        ];
    }

    public function cpd_orgview_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of competencies in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
            'records' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'id' => new external_value(PARAM_INT, 'id'),
                                    'traineename' => new external_value(PARAM_RAW, 'traineename'),
                                    'title' => new external_value(PARAM_RAW, 'tilte'),
                                    'code' => new external_value(PARAM_RAW, 'code'), 
                                    'cpdurl' => new external_value(PARAM_RAW, 'cpdurl'),
                                    'validation' => new external_value(PARAM_RAW, 'validation'),
                                    'hourscreated' => new external_value(PARAM_RAW, 'hourscreated'),
                                )
                            )
            )
        ]);
    }

    public function usercpddetails_parameters() {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'id'),
        ]);  
    }

    public function usercpddetails($id) {
        global $DB, $PAGE, $USER;
        require_login();
        // Parameter validation.
        $sitecontext = context_system::instance();
        $PAGE->set_url('/local/cpd/index.php', array());
        $PAGE->set_context($sitecontext);
        $params = self::validate_parameters(
            self::usercpddetails_parameters(),
            [
                'id' => $id
            ]
        );

        $settings = external_settings::get_instance();


        $stable = new \stdClass();
        $stable->thead = false;
        $stable->type = 'mobile';
        $stable->cpdid = $id;
        $stable->mlang =  $settings->get_lang();
        $mlang = $stable->mlang;
        $data = (new local_cpd\local\cpd)->get_cpdcontent($id, $USER->id,$mlang);
        $reportedhrs = (new local_cpd\lib)->get_listof_reported_hrs($stable);

        return [
            'details' => $data,
            'reportedhrs' => $reportedhrs['hasreportedhrs'],
            'totalreportedhrs' => $reportedhrs['totalreported_hrs'],
        ];
    }

    public function usercpddetails_returns() {

        return new external_single_structure([
          'details' => new external_single_structure(
                array(
                    'title' => new external_value(PARAM_RAW, 'title'),
                    'userrenewaldeadline' => new external_value(PARAM_INT, 'userrenewaldeadline'),
                    'userorignalyearned' => new external_value(PARAM_INT, 'userorignalyearned'),
                    'approvehrs' => new external_value(PARAM_INT, 'approvehrs'),
                    'pendinghrs' => new external_value(PARAM_INT, 'pendinghrs'),
                    'remaininghrs' => new external_value(PARAM_INT, 'remaininghrs'),
                    'current_status' => new external_value(PARAM_RAW, 'current_status'),
                    'description' => new external_value(PARAM_RAW, 'description'),
                    'validation' => new external_value(PARAM_RAW, 'validation'),
                    'hourscreated' => new external_value(PARAM_RAW, 'hourscreated'),
                    'traineecount' => new external_value(PARAM_INT, 'traineecount'),
                    'cpdimg' => new external_value(PARAM_URL, 'cpdimg'),
                )
            ),
           'reportedhrs' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'id'),
                        'title' => new external_value(PARAM_RAW, 'title'),
                        'credithrs' => new external_value(PARAM_INT, 'credithrs'),
                        'userapprovedate' => new external_value(PARAM_INT, 'userapprovedate'),
                        'requeststatus' => new external_value(PARAM_RAW, 'requeststatus'),
                        'evidencetype' => new external_value(PARAM_RAW, 'evidencetype'),
                        'evidtype' => new external_value(PARAM_INT, 'evidtype'),
                        'action' => new external_value(PARAM_INT, 'action'),
                    )
                )
            ),
           'totalreportedhrs' => new external_value(PARAM_INT, 'totalreportedhrs'),

        ]);
    }
}
