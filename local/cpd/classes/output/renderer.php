<?php
namespace local_cpd\output;

// use plugin_renderer_base;
/**
 * Defines the version of Training program
 *
 * @package    local_cpd
 * @copyright  2022 Naveen kumar <naveen@eabyas.com>
 */
defined('MOODLE_INTERNAL') || die;
use context_system;
use html_table;
use html_writer;
use plugin_renderer_base;
use moodle_url;
use stdClass;
use single_button;
use core_user;
use local_cpd\lib;
/**
 * Renderer class for Training program
 */
class renderer extends plugin_renderer_base
{

    function render_cpdview($page)
    {
        $data = $page->export_for_template($this);                                                    
        if ((is_siteadmin() || has_capability('local/organization:manage_cpd', context_system::instance())||has_capability('local/cpd:manage', context_system::instance()))) {
            return $this->render_from_template('local_cpd/cpdview', $data);
        } else {
            return $this->render_from_template('local_cpd/cpd_user_view', $data);
        }
    }
    public function get_catalog_cpd($filter = false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'manage_cpd_list','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName'] = 'local_cpd_view';
        $options['templateName'] = 'local_cpd/cpdlist';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'manage_cpd_list',
                'options' => $options,
                'dataoptions' => $dataoptions,
                'filterdata' => $filterdata,
        ];   
        if($filter){
            return  $context;
        } else {
            return  $this->render_from_template('theme_academy/cardPaginate', $context);
        }
    }

    public function cpd_list($stable, $filterdata=null) {
        global $USER, $CFG, $DB;
        $systemcontext = context_system::instance();
        if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
            $getcpdlist = lib::get_listof_orgcpd($stable, $filterdata);
        } else {
            $getcpdlist = lib::get_listof_cpd($stable, $filterdata);
        }
        $cpdlist = array_values($getcpdlist['cpd']);
        $row = array();
        $stable = new \stdClass();
        $stable->thead = true;
        foreach ($cpdlist as $list) {
            //var_dump($list); exit;
            $record = array();
            $record['id'] = $list->id;
            if($list->fullname) {
                $record['traineename'] = $list->fullname;
            } else {
                $record['traineename'] = '';
            }
            $lang = current_language();
            if( $lang == 'ar' ){
                $record['title'] = $list->examnamearabic;
            } else {
                $record['title'] = $list->exam;
            }
            $record['code'] = $list->code;
            if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
                $record['cpdurl'] = $CFG->wwwroot."/local/cpd/view.php?id=".$list->id."&uid=".$list->ucid;
            } else {
                $record['cpdurl'] = $CFG->wwwroot."/local/cpd/view.php?id=".$list->id;
            }
            //$record['cpdurl'] = $CFG->wwwroot."/local/cpd/view.php?id=".$list->id;
            if ($list->validation == 1) {
                $validation = $list->validation.' '.get_string('year', 'local_cpd');
            } else {
                $validation = $list->validation.' '.get_string('years', 'local_cpd');
            }
            $record['validation'] = $validation;
            $record['hourscreated'] = $list->hourscreated.' '.get_string('traninghrs', 'local_cpd');
            $record['edit'] = false;
            $record['delete'] = false;
            if (is_siteadmin() || has_capability('local/organization:manage_cpd', context_system::instance()) || has_capability('local/cpd:edit', context_system::instance())  ) {
                $record['edit'] = true;
            }
            if (is_siteadmin() || has_capability('local/cpd:delete', context_system::instance()) || has_capability('local/organization:manage_cpd', context_system::instance()) ) {
                $record['delete'] = true;
                $record['viewmoreurl'] = $CFG->wwwroot."/local/cpd/index.php";
            }
            $row[] = $record;
        }
        return array_values($row);
    }
    public function hascapability() {
        global $DB;
        $userid = optional_param('uid', 0, PARAM_INT);
       
        if (is_siteadmin() || has_capability('local/cpd:manage', context_system::instance()) || has_capability('local/organization:manage_cpd', context_system::instance()) ) {
            $this->page->set_title(get_string('cpd', 'local_cpd'));
            $this->page->set_heading(get_string('manage', 'local_cpd'));
            $this->page->navbar->add(get_string('manage', 'local_cpd'), new moodle_url('/local/cpd/index.php'));
            $this->page->navbar->add(get_string('view', 'local_cpd'), new moodle_url('/local/cpd/index.php'));
        } else if (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',context_system::instance()) ) {
            $fullname = (new \local_trainingprogram\local\trainingprogram)->user_fullname_case();
            $username = $DB->get_record_sql("SELECT $fullname FROM {user} u JOIN {local_users} lc ON lc.userid =u.id WHERE u.id = $userid");

            $localuserrecord = $DB->get_record('local_users',['userid'=>$userid]);
            $user_fulname= ($localuserrecord)? ((current_language() == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($DB->get_record('user',['id'=>$userid]));
            //var_dump($username->fullname); exit;
            $this->page->set_title(get_string('cpd', 'local_cpd'));
            $this->page->set_heading($user_fulname.' '.get_string('cpd', 'local_cpd'));
            $this->page->navbar->add(get_string('cpd', 'local_cpd'), new moodle_url('/local/cpd/index.php'));
            $this->page->navbar->add(get_string('view', 'local_cpd'), new moodle_url('/local/cpd/index.php'));
        } else if (!is_siteadmin() && has_capability('local/organization:manage_trainingofficial',context_system::instance()) ) {
            $this->page->set_title(get_string('cpd', 'local_cpd'));
            $this->page->set_heading(get_string('cpd', 'local_cpd'));
            $this->page->navbar->add(get_string('cpd', 'local_cpd'), new moodle_url('/local/cpd/index.php'));
            $this->page->navbar->add(get_string('view', 'local_cpd'), new moodle_url('/local/cpd/index.php'));
        } else {
            $this->page->set_title(get_string('my_cpd', 'local_cpd'));
            $this->page->set_heading(get_string('my_cpd', 'local_cpd'));
            $this->page->navbar->add(get_string('my_cpd', 'local_cpd'), new moodle_url('/local/cpd/index.php'));
            $this->page->navbar->add(get_string('view', 'local_cpd'), new moodle_url('/local/cpd/index.php'));
        }
    }
    public function cpd_check($cpdid) {
        global $OUTPUT, $CFG, $DB, $USER, $PAGE;
        $stable = new stdClass();
        $stable->cpdid = $cpdid;
        $stable->thead = false;
        $stable->start = 0;
        $stable->length = 1;
        $cpd = lib::get_listof_cpd($stable);
        $context = context_system::instance();
        if (empty($cpd)) {
            print_error("CPD Not Found!", 'error');
        }
        return $cpd;
    }

    public  function get_cpd_users($filter = false) {
        global $CFG;
        $systemcontext = context_system::instance();
        $evalid = optional_param('id', 0, PARAM_INT);
        $stable = new \stdClass();
        $stable->thead = true;
        $stable->start = 0;
        $stable->length = -1;
        $stable->search = '';
        $stable->pagetype ='page';
        $stable->cpdevalid = $evalid;
        $options = array('targetID' => 'manage_cpd_users','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName'] = 'local_cpd_cpd_usersview';
        $options['templateName'] = 'local_cpd/cpd_evidence_users_details';
        $options['cpdevalid'] = $evalid;
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'manage_cpd_users',
                'options' => $options,
                'dataoptions' => $dataoptions,
                'filterdata' => $filterdata,
        ];
        $fncardparams = $context;
        $statusmform = cpd_users_filters_form($context);
        $traineescount = lib::get_listof_cpdusers($stable, $filterdata);
        $cardparams = $fncardparams+array(
            'evalid' => $evalid,
            'contextid' => $systemcontext->id,
            'plugintype' => 'local',
            'plugin_name' =>'cpd',
            'traineescount' => $traineescount['traineescount'],
            'cfg' => $CFG,
            'filterform' => $statusmform->render());
        if($filter){
            return  $context;
        }else{
            return  $this->render_from_template('local_cpd/viewusers', $cardparams);
        }
    }

     public function user_info($data) {
        global $DB, $PAGE, $OUTPUT, $CFG;
        $user = $this->render_from_template('local_cpd/userdetails', $data);
        return $user;
    }

    public function global_filter($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        return $this->render_from_template('theme_academy/global_filter', $filterparams);
        //return true;
    }

    public function listofusers($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        $usermform = cpd_users_filters_form($filterparams);
        $filterparams['filterform'] = $usermform->render();

        echo $this->render_from_template('local_cpd/listofusers', $filterparams);
    }

    public function get_catalog_evidence($filter = false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'manage_evidence','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName'] = 'local_cpd_user_evidence';
        $options['templateName'] = 'local_cpd/evidencelist';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'manage_evidence',
                'options' => $options,
                'dataoptions' => $dataoptions,
                'filterdata' => $filterdata,
        ];
        if ($filter) {
            return  $context;
        } else {
            return  $this->render_from_template('theme_academy/cardPaginate', $context);
        }
    }

    public function get_catalog_reportedhrs($filter = false) {
        $systemcontext = context_system::instance();
        $cpdid = optional_param('id', 0, PARAM_INT);
        $userid = optional_param('uid', 0, PARAM_INT);
        $options = array('targetID' => 'manage_reported_hrs','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName'] = 'local_cpd_reported_hrs';
        $options['templateName'] = 'local_cpd/reported_hrs';
        $options['cpdid'] = $cpdid;
        $options['userid'] = $userid;
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'manage_reported_hrs',
                'options' => $options,
                'dataoptions' => $dataoptions,
                'filterdata' => $filterdata,
        ];
        $fncardparams = $context;
        $context = $fncardparams+array(
            'createevidence'=> true,
            'contextid' => $systemcontext->id,
            'plugintype' => 'local',
            'plugin_name' =>'cpd',
            );
        if($filter){
            return  $context;
        }else{
            return  $this->render_from_template('local_cpd/reportedhrs_card', $context);
        }
    }

    public function get_catalog_training_programs($filter = false) {
        $systemcontext = context_system::instance();
        $cpdid = optional_param('id', 0, PARAM_INT);
        $createprogram = false;
        if ((is_siteadmin() || has_capability('local/cpd:manage', $systemcontext) || has_capability('local/organization:manage_cpd', $systemcontext )
                            || has_capability('local/organization:manage_trainingofficial', $systemcontext ))) {
            $templatename = 'local_cpd/listof_related_programs';
            $createprogram = true;
            $divclass = "col-md-8";
            $textcolor = "text-black";

        } else {
            $templatename = 'local_cpd/evidence_related_programs';
            $divclass = "col-md-12";
            $textcolor = "";
        }
        $options = array('targetID' => 'manage_training_prorgams','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName'] = 'local_cpd_training_programs';
        $options['templateName'] = $templatename;
        $options['cpdid'] = $cpdid;
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'manage_training_prorgams',
                'options' => $options,
                'dataoptions' => $dataoptions,
                'filterdata' => $filterdata,
        ];
        $fncardparams = $context;
            $context = $fncardparams+array(
                'createprogram' => $createprogram,
                'contextid' => $systemcontext->id,
                'plugintype' => 'local',
                'plugin_name' =>'cpd',
                'divclass' => $divclass,
                'textblack' => $textcolor,
                'cpdid' => $cpdid );
            
        if($filter){
            return  $context;
        } else {
            return  $this->render_from_template('local_cpd/viewtrainingprograms', $context);
        }
    }

    public function listof_related_programs() {
        global $DB, $PAGE, $OUTPUT;
        echo $this->render_from_template('local_cpd/listof_related_programs', '');
    }

    public function action_btn() {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        if (has_capability('local/cpd:create', $systemcontext)) {
            $header_btns = $this->render_from_template('local_cpd/form', null);
            $actionbtns = $PAGE->add_header_action($header_btns);            
            return true;
        } else {
            return false;
        }
    }

    public function evidence_list($stable, $filterdata=null) {
        global $USER, $CFG, $DB,$SESSION;
        $SESSION->lang = ($stable->mlang) ? $stable->mlang : current_language();
        $systemcontext = context_system::instance();
        $getevidence = lib::get_listof_userevidence($stable, $filterdata);
        $evidence = array_values($getevidence['evidence']);
        $row = array();
        $stable = new \stdClass();
        $stable->thead = true;
        $count = 0;
        $lang= current_language();
        foreach ($evidence as $list) {
            $record = array();
            $record['id'] = $list->id;
            if ($SESSION->lang == 'ar' && !empty($list->examnamearabic)) {
                $title = $list->examnamearabic;
            } else {
                $title = $list->exam;
            }
            $record['title'] = $title;
            $status_text = array(0 => get_string('statuspending','local_cpd'), 1 => get_string('approve','local_cpd'), 2 => get_string('statusrejected','local_cpd'));
            $record['requeststatus'] = $status_text[$list->status];
            $current_status = (new \local_cpd\local\cpd)->user_current_status($USER->id, $list->examid);
            $record['status'] = $current_status;
            $record['cpdid'] = $list->cpdid;
            $record['cpdurl'] = $CFG->wwwroot."/local/cpd/view.php?id=".$list->cpdid;
            if ($list->validation == 1) {
                $validation = $list->validation.' '.get_string('year', 'local_cpd');
            } else {
                $validation = $list->validation.' '.get_string('years', 'local_cpd');
            }
            $record['validation'] = $validation;
            $record['hourscreated'] = $list->hourscreated.' '.get_string('traninghrs', 'local_cpd');
            $record['hoursclaimed'] = $list->hourscreated;
            $seconds = $list->hourscreated*3600;
            $practichrs_data = $seconds*0.7;
            $practichrs = round(($practichrs_data)/3600);
            $record['practichrs'] = $practichrs; //round($list->hourscreated/2,2);
            $kwlghrs_data = $seconds*0.3;
            $kwlghrs = round(($kwlghrs_data)/3600);
           // var_dump($kwlghrs); exit;
            $record['kwlghrs'] = $kwlghrs;//round($list->hourscreated/2, 2);
            $row[] = $record;
        }
        return array_values($row);
    }

    public function trainess_list($stable, $filterdata=null) {
        global $USER, $CFG, $DB;
        $systemcontext = context_system::instance();
        $gettrainees = lib::get_listof_cpdusers($stable, $filterdata);
        $trainees = array_values($gettrainees['trainees']);
        $row = array();
        $stable = new \stdClass();
        $stable->thead = true;
        $count = 0;
        foreach ($trainees as $user) {
            $record = array();
            $record["id"] = $user->id;
            $record['cpdid'] = $user->cpdid;
            $localuserrecord = $DB->get_record('local_users',['userid'=>$user->ucid]);
            $record['username'] = ($localuserrecord)? ((current_language() == 'ar') ? $localuserrecord->firstnamearabic.' '.$localuserrecord->middlenamearabic.' '.$localuserrecord->thirdnamearabic.' '.$localuserrecord->lastnamearabic  : $localuserrecord->firstname.' '.$localuserrecord->middlenameen.' '.$localuserrecord->thirdnameen.' '.$localuserrecord->lastname) :fullname($user);
            $record['userid_number'] = $user->id_number;
            $record['userid'] = $user->ucid;
            $record['ceid'] = $user->id;
            $record['evdtype'] = $user->evidencetype;
            $record['dateclaimed'] = userdate($user->timecreated, '%d-%m-%Y');
            //$record['dateclaimed'] = date('d-m-Y', $user->timecreated);
            $status_text = array("0" => get_string('statuspending','local_cpd'), "1" => get_string('approve','local_cpd'), "2" => get_string('statusrejected','local_cpd'));
            $record['requeststatus'] = $status_text[$user->status];
            $current_status = (new \local_cpd\local\cpd)->user_current_status($user->userid, $user->examid);
            $record['status'] = $current_status;
            $evidence_text = array(1 => get_string('formal','local_cpd'), 2 => get_string('informal','local_cpd'));
            $evidencetype = $user->evidencetype;
            $record['evidencetype'] = $evidence_text[$evidencetype];
            if($user->evidencetype == 1){
                $formal_evidence = $DB->get_record('local_cpd_formal_evidence',['evidenceid' => $user->id]);
                $record['creditedhours'] = $formal_evidence->creditedhours;
            } elseif($user->evidencetype == 2){
                $informal_evidence = $DB->get_record('local_cpd_informal_evidence',['evidenceid' => $user->id]);
                $record['creditedhours'] = $informal_evidence->creditedhours;
            }
           
            if ($user->status == 0) {
                $record['statusaction'] = true;
            } else {
                $record['statusaction'] = false;
            }
            $record['action'] = false;
            if ((is_siteadmin() || has_capability('local/organization:manage_cpd', context_system::instance() ) ||has_capability('local/cpd:manage', context_system::instance()))) {
                $record['action'] = true;
            }
            $record['userviewurl'] = $CFG->wwwroot."/local/userapproval/userprofile.php?id=".$user->ucid;
            $row[] = $record;
        }
        return array_values($row);
    }

    //Vinod- CPD fake block for exam official - Starts//

    public function all_cpd_block($filter = false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'manage_cpd_block','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_cpd_manage_cpd_block';
        $options['templateName']='local_cpd/cpd_block';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'manage_cpd_block',
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
    public function listofcpd_block_data($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        return $this->render_from_template('local_cpd/listofcpd_block_data', $filterparams);
    }
    //Vinod- CPD fake block for exam official - Ends//

    public function listofcpd($filterparams) {
        global $CFG, $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        if(is_siteadmin() || has_capability('local/cpd:manage', $systemcontext) ||  has_capability('local/organization:manage_cpd', $systemcontext)) {
           $filterparams['createcpd'] = true;
        }
        echo $this->render_from_template('local_cpd/viewcpdlist', $filterparams);
    }

    public function listofevidence($filterparams) {
        global $CFG, $DB, $PAGE, $OUTPUT;
        $filterparams['createevidence'] = true;
        echo $this->render_from_template('local_cpd/viewevidencelist', $filterparams);
    }

    public function manage_capability() {
        $systemcontext = context_system::instance();
        if (is_siteadmin() || has_capability('local/cpd:manage', $systemcontext) 
        || has_capability('local/organization:manage_cpd', $systemcontext)
        || has_capability('local/cpd:create', $systemcontext) ) {
           return true;
        }/*else {
            print_error(get_string('permissionerror', 'local_events'));
        }*/
    }

    public function get_catalog_orgcpd($filter = false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'org_cpd_list','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName'] = 'local_cpd_orgcpdview';
        $options['templateName'] = 'local_cpd/orgcpdlist';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'org_cpd_list',
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

    public function listoforgcpd($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        echo $this->render_from_template('local_cpd/listoforgcpd', $filterparams);
    }
}
