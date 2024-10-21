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
 * @package F-academy
 * @subpackage local_cpd
 */
namespace local_cpd\local;
defined('MOODLE_INTERNAL') || die;

use context_system;
use moodle_url;
use block_contents;
/**
 * local cpd class 
 */     
class cpd 
{
    public function get_cpdcontent($evidid, $userid='',$mlang = NULL) {
        global $DB, $USER, $CFG, $OUTPUT,$SESSION;
        $SESSION->lang = ($mlang) ? $mlang : current_language() ;

        if($userid) {
            $userid = $userid;
        } else {
            $userid = $USER->id;
        }
        $sql = " SELECT  c.id, c.examid, c.validation, c.hourscreated, c.logo, c.description, le.exam, le.examnamearabic 
                   FROM {local_cpd} c
                   JOIN {local_exams} le ON le.id = c.examid AND c.id=:id";
        if (!is_siteadmin() && has_capability('local/organization:manage_trainee', context_system::instance()) ) {
            $sql .= " JOIN {local_cpd_evidence} ce ON ce.cpdid = c.id AND ce.userid = $userid ";
        }
        $cpddata = $DB->get_record_sql($sql,['id' => $evidid]);
        $data = [];
        if (!empty($cpddata)) {
            $lang= current_language();
            if ( $SESSION->lang == 'ar') {
                $title = $cpddata->examnamearabic;
            } else {
                $title = $cpddata->exam;
            }
            $data['title'] = $title;
            $totalhrs = $cpddata->hourscreated;
            $approvedhrs_sql = "SELECT SUM(fe.creditedhours) AS approvhrs
                                  
                                  FROM {local_cpd_evidence} ce
                             LEFT JOIN (SELECT id, evidenceid, creditedhours FROM {local_cpd_formal_evidence}
                                         UNION
                                        SELECT id, evidenceid, creditedhours FROM {local_cpd_informal_evidence}) AS fe 
                                    ON fe.evidenceid = ce.id 
                                 WHERE ce.status = '1' AND ce.cpdid = :evidenceid AND ce.userid = :userid ";
            $cpdapprovehrs = $DB->get_field_sql($approvedhrs_sql, ['evidenceid' => $evidid, 'userid' => $userid], IGNORE_MULTIPLE);
            $programhours = $DB->get_field_sql("SELECT SUM(hoursachieved) FROM {trainingprogram_completion} WHERE userid = {$userid} AND cpdid =". $cpddata->id);
            $trainingprogramhours = !empty($programhours) ? $programhours : 0;
            $approvehrs = $cpdapprovehrs + $trainingprogramhours;
            if(!$approvehrs){
                $approvehrs = 0;
            }
            $pendinghrs_sql = "SELECT SUM(fe.creditedhours) AS pendinghrs
                                 FROM {local_cpd_evidence} ce
                            LEFT JOIN (SELECT id, evidenceid, creditedhours FROM {local_cpd_formal_evidence}
                                        UNION
                                       SELECT id, evidenceid, creditedhours FROM {local_cpd_informal_evidence}) AS fe 
                                   ON fe.evidenceid = ce.id 
                                WHERE ce.status = '0' AND ce.cpdid = :evidenceid AND ce.userid = :userid";
            $pendinghrs = $DB->get_field_sql($pendinghrs_sql, ['evidenceid' => $evidid, 'userid' => $userid], IGNORE_MULTIPLE);

            $array = array('userid' => $userid, 'moduleid' => $cpddata->examid,
            'moduletype'=> 'exams');
            $certificate_date = $DB->get_record('tool_certificate_issues', $array);
            if($certificate_date) {
                if (!is_null($certificate_date->expires)) {
                    $expiresdate =  userdate($certificate_date->expires, get_string('strftimedatefullshort', 'core_langconfig'));                    
                } else {
                    $expiresdate = '--';
                }

                $renewaldeadline = $expiresdate; //date('jS M Y',strtotime($expiresdate. ' + '.$cpddata->validation.' years'));
                $orignalyearned = userdate($certificate_date->timecreated, get_string('strftimedatefullshort', 'core_langconfig'));
            } else {
                $renewaldeadline = '--';
                $orignalyearned = '--';
            }
            $data['renewaldeadline'] = $renewaldeadline;
            $data['orignalyearned'] = $orignalyearned;
            $data['userrenewaldeadline'] = $certificate_date->expires;
            $data['userorignalyearned'] = $certificate_date->timecreated;            
            if(!$pendinghrs){
                $pendinghrs = 0;
            }
            if ($approvehrs !=0 && $approvehrs > $totalhrs) {
                $approvehrs = $totalhrs;
            }
            $hourpercentage = 100 / $totalhrs;
            $data['totalapprovehrs'] = $approvehrs * $hourpercentage;
            $data['totalpendinghrs'] =   $pendinghrs * $hourpercentage;
            $data['totalremaininghrs'] = ($totalhrs - ($approvehrs + $pendinghrs)) * $hourpercentage;

            $data['approvehrs'] = $approvehrs;
            $data['pendinghrs'] = $pendinghrs;
            $remaininghrs = ($totalhrs - ($approvehrs + $pendinghrs));
            if($remaininghrs < 0) {
                $remaininghrs = 0;
            }
            $data['remaininghrs'] = $remaininghrs;
            $current_status = $this->user_current_status($userid, $cpddata->examid);
            $data['current_status'] = $current_status;
            if (!empty($cpddata->description)) {
                $description = format_text($cpddata->description);
            } else { 
                $description = "";
            }
            $data['description'] = $description;
            if ($cpddata->validation == 1) {
                $validation = $cpddata->validation.' '.get_string('year', 'local_cpd');
            } else {
                $validation = $cpddata->validation.' '.get_string('years', 'local_cpd');
            }
            $data['validation'] = $validation;
            $data['hourscreated'] = $cpddata->hourscreated.' '.get_string('hrrequired', 'local_cpd');
            
            $traineessql =  "SELECT COUNT(DISTINCT(ce.userid)) as ccount FROM {local_cpd_evidence} ce JOIN {user} u ON u.id = ce.userid WHERE ce.cpdid = :cpdid";
            $params = array('cpdid' => $cpddata->id);
            $traineecount =  $DB->count_records_sql($traineessql, $params);
            $data['traineecount'] = $traineecount;
            if ($cpddata->logo > 0) {
                $cpdimg = $this->cpd_logo($cpddata->logo); 
                if($cpdimg == false){
                   $cpdimg = $OUTPUT->image_url('eventviewnew', 'local_events');
                }
            } else {
                $cpdimg = $OUTPUT->image_url('eventviewnew', 'local_events');
            }
            $data['cpdimg'] = $cpdimg;
            $data['createcpd'] = false;
            if (!is_siteadmin() && has_capability('local/organization:manage_trainee',context_system::instance()) ) {
                $data['createcpd'] = true;
            }
        }
        return $data;
    }

    public function cpd_logo($cpdlogo = 0) {
        global $DB;
        $context = context_system::instance();
        if ($cpdlogo > 0) {
            $sql = "SELECT * FROM {files} WHERE itemid = :logo AND filearea='logo' AND filename != '.' ORDER BY id DESC";
            $trainingprogramlogorecord = $DB->get_record_sql($sql,array('logo' => $cpdlogo),1);
        }
        if (!empty($trainingprogramlogorecord)) {
            $logourl = moodle_url::make_pluginfile_url($trainingprogramlogorecord->contextid, $trainingprogramlogorecord->component,
           $trainingprogramlogorecord->filearea, $trainingprogramlogorecord->itemid, $trainingprogramlogorecord->filepath,
           $trainingprogramlogorecord->filename);
            $logourl = $logourl->out();
        }
        return $logourl;
    }

    public function user_current_status($userid, $examid) {
        global $DB;
        $array = array('userid' => $userid, 'moduleid' => $examid,
        'moduletype'=> 'exams');
        $exist_recordid = $DB->get_record('tool_certificate_issues', $array);
        if(!empty($exist_recordid)) {
            $from = date_create(userdate(time(), '%Y-%m-%d'));
            $to = date_create(userdate($exist_recordid->expires, '%Y-%m-%d'));
            $remaining_days = date_diff($to, $from);
            $remaining_exp_days = $remaining_days->days;
            //var_dump($remaining_exp_days); exit;
            if($remaining_exp_days > 0) {
                if($remaining_exp_days >= 360) {
                    $status = get_string('goodstanding', 'local_cpd');
                } else if($remaining_exp_days <= 360 && $remaining_exp_days >= 300) {
                    $status = get_string('actionpreferred', 'local_cpd');
                } else if($remaining_exp_days <= 300 && $remaining_exp_days >= 180) {
                    $status = get_string('requiredcloseattention', 'local_cpd');
                } else if($remaining_exp_days <= 180 && $remaining_exp_days >= 90) {
                    $status = get_string('actionrequired', 'local_cpd');
                } else if($remaining_exp_days <= 90) {
                    $status = get_string('immediaterenewalrequired', 'local_cpd');
                }
            } else {
                $status = get_string('renewalrequired', 'local_cpd');
            }
        } else {
            $status = '--';
        }
        return $status;
    }

     //Vinod- CPD fake block for exam official - Starts//
      
    public function cpdfakeblock () {
        global $PAGE;
        $systemcontext = context_system::instance();
        if(!is_siteadmin() && has_capability('local/organization:manage_cpd', $systemcontext)) {
            $bc = new block_contents();
            $bc->title = get_string('manage', 'local_cpd');
            $bc->attributes['class'] = 'cpd_fakeblock';
            $bc->content = $this->cpd_block();
            $PAGE->blocks->add_fake_block($bc, 'content');
        }
    }
    public function cpd_block() {
        global $DB, $PAGE, $OUTPUT, $USER;
        $systemcontext = context_system::instance();
        $renderer = $PAGE->get_renderer('local_cpd');
        $filterparams = $renderer->all_cpd_block(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-6';
        $filterparams['placeholder'] = get_string('searchcpd','local_cpd');
        $globalinput=$renderer->global_filter($filterparams);
        $block = $renderer->all_cpd_block();
        $filterparams['cpd_block_view'] = $block;
        $filterparams['globalinput'] = $globalinput;
        return $renderer->listofcpd_block_data($filterparams);
    }

    //Vinod- CPD fake block for exam official - Ends// 
}
