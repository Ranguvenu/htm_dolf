<?php
use local_exams\local\cisi_services;
// use local_exams\notification;
use local_exams\local\exams as exams;
use local_notifications\notification;
function useremail_filter($mform,$query='',$searchanywhere=false, $page=0, $perpage=25){
    global $DB,$USER;
    $systemcontext = context_system::instance();
    $userattributes = array(
        'ajax' => 'local_organization/organization_datasource',
        'data-type' => 'usersemail',
        'id' => 'usersemail',
        'data-org' => 'enrollfilter',
        'multiple'=>true,
    );
    $data = data_submitted();
    $email= implode(',',$data->email);
    if($email) {
       $selectedmailrecords = $DB->get_records_sql_menu('SELECT id,email FROM {local_users} WHERE id IN('.$email.')');
    }
    $mform->addElement('autocomplete', 'email', get_string('email', 'local_userapproval'),$selectedmailrecords, $userattributes);
}
function halls_filter($mform,$query='',$searchanywhere=false, $page=0, $perpage=25){
    global $DB,$USER;
    $examid = optional_param('examid', 0, PARAM_INT);
    $systemcontext = context_system::instance();
    $emailslist=array();
    $data=data_submitted();
    $userparam = array();
    $params = array();
    $options = array(
        'ajax' => 'local_hall/hall_datasource',
        'data-type' => 'schedulehalls',
        'data-hallid' => $hallid,
        'class' => 'el_currenthall',
        'data-city' => '',
        'data-buildingname' => '',
        'noselectionstring' => '',
        'placeholder' => get_string('selecthall' , 'local_hall'),
    );
    $select = $mform->addElement('autocomplete', 'halladdress', get_string('hall','local_hall'), [], $options);
}

function examhalls_filter($mform,$query='',$searchanywhere=false, $page=0, $perpage=25)
{
    global $DB,$USER;
    $examid = optional_param('examid', 0, PARAM_INT);
    $systemcontext = context_system::instance();
    $emailslist=array();
    $data=data_submitted();
    $userparam = array();
    $params = array();
    
    $options = array(
        'ajax' => 'local_hall/hall_datasource',
        'data-type' => 'examhalls',
        'data-hallid' => $hallid,
        'class' => 'el_currenthall',
        'data-city' => '',
        'data-buildingname' => '',
        'noselectionstring' => '',
        'placeholder' => get_string('selecthall' , 'local_hall'),
    );
    $select = $mform->addElement('autocomplete', 'halladdress', get_string('hall','local_hall'), [], $options);
}


function examdate_filter($mform,$query='',$searchanywhere=false, $page=0, $perpage=25){
    global $DB,$USER;
    $examid = optional_param('examid', 0, PARAM_INT);
    $systemcontext = context_system::instance();
    $emailslist=array();
    $data=data_submitted();
    $userparam = array();
    $params = array();

    $enddate = $DB->get_field('local_exams', 'enddate', ['id' => $examid]);    
    $dates->startdate = time();
    $dates->enddate = date( 'Y-m-d', $enddate);
    $period = new DatePeriod(
         new DateTime(date( 'Y-m-d', $dates->startdate)),
         new \DateInterval('P1D'),
         new DateTime(date( 'Y-m-d', strtotime($dates->enddate. ' + 1 day')))
    );
    $examslist = [];
    foreach ($period as $value) {
        $key = strtotime($value->format('Y-m-d'));
        $examslist[$key] =  $value->format('Y-m-d');
    }
    $mform->addElement('autocomplete', 'examdate',get_string('selectdate','local_exams'), array(null=>get_string('selectdate','local_hall')) + $examslist, ['class' => 'halldate']);
    $mform->addRule('examdate', get_string('missingexamdates', 'local_exams'), 'required', null);
}
function local_exams_leftmenunode(){
    global $USER;
    $systemcontext = context_system::instance();
    $referralcode = '';
    $lang = current_language();
    if(has_capability('local/organization:manage_examofficial', $systemcontext) || is_siteadmin() || has_capability('local/organization:manage_organizationofficial', $systemcontext) || has_capability('local/organization:manage_communication_officer',$systemcontext) || has_capability('local/exams:view',$systemcontext)) {
        $referralcode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_referralcode', 'class'=>'pull-left user_nav_div referralcode'));
        $referral_url = new moodle_url('/local/exams/index.php');
        $referral_label = get_string('pluginname','local_exams');
        $referral = html_writer::link($referral_url, '<span class="exams_icon side_menu_img_icon"></span><span class="user_navigation_link_text">'.$referral_label.'</span>',
            array('class'=>'user_navigation_link'));
        $referralcode .= $referral;
        $referralcode .= html_writer::end_tag('li');
    } elseif(!is_siteadmin() && has_capability('local/organization:manage_trainee', $systemcontext)) {
        $referralcode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_referralcode', 'class'=>'pull-left user_nav_div referralcode'));
        $referral_url = new moodle_url('/local/exams/index.php?lang='.$lang);
        $referral_label = get_string('myexams','local_exams');
        $referral = html_writer::link($referral_url, '<span class="exams_icon side_menu_img_icon"></span><span class="user_navigation_link_text">'.$referral_label.'</span>',
            array('class'=>'user_navigation_link'));
        $referralcode .= $referral;
        $referralcode .= html_writer::end_tag('li');        
    }

    // if (has_capability('local/trainingprogram:manage_examownedbysettings', $systemcontext)) {
    //     $referralcode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_exam_ownedby_setting', 'class'=>'pull-left user_nav_div exam_ownedby_setting'));
    //     $referral_url = new moodle_url('/local/exams/exam_ownedby_settings.php');
    //     $referral_label = get_string('exam_ownedby_settings', 'local_sector');
    //     $referral = html_writer::link($referral_url, '<span class="systemsettings_icon side_menu_img_icon"></span><span class="user_navigation_link_text">'.$referral_label.'</span>',
    //     array('class'=>'user_navigation_link'));
    //     $referralcode .= $referral;
    //     $referralcode .= html_writer::end_tag('li');
    // }
    // Reports Dashboard for all Roles
    
    if( !is_siteadmin() && has_capability('local/organization:manage_trainee', $systemcontext) ) {
        $reportscode = html_writer::start_tag('li', array('id'=> 'id_leftmenu_referralcode', 'class'=>'pull-left user_nav_div referralcode'));
        $reports_url = new moodle_url('/blocks/reportdashboard/dashboard.php?dashboardurl=Facademy&role=trainee');
        $reports_label = get_string('reports','local_exams');
        $reports = html_writer::link($reports_url, '<span class="reports_icon side_menu_img_icon"></span><span class="user_navigation_link_text">'.$reports_label.'</span>',
            array('class'=>'user_navigation_link'));
        $reportscode .= $reports;
        $reportscode .= html_writer::end_tag('li');
        return array('3' => $referralcode, '1' => $reportscode);
    }

    return array('3' => $referralcode);
}

function exams_front_filters_form($filterparams){
    global $CFG;
    require_once($CFG->dirroot . '/local/organization/dynamicfilters_form.php');
    $filters = array(
       'exams'=>array('local'=>array('sectors')),
    );
    $mform = new dynamicfilters_form(null, array('filterlist'=>$filters,'filterparams' => $filterparams,'submitid' =>'viewexams','ajaxformsubmit'=>true, 'disablebuttons' => 1), 'post', '', null, true,$_REQUEST);
    return $mform;
}

function exams_reservations_form($filterparams){
    global $CFG;
    require_once($CFG->dirroot . '/local/organization/dynamicfilters_form.php');
    $filters = array(
       'exams'=>array('local'=>array('examhalls', 'examduration')),
    );
    $mform = new dynamicfilters_form(null, array('filterlist'=>$filters,'filterparams' => $filterparams,'submitid' =>'viewexams','ajaxformsubmit'=>true), 'post', '', null, true,$_REQUEST);
    return $mform;
}
function exams_filters_form($filterparams){
    global $CFG;
    require_once($CFG->dirroot . '/local/organization/dynamicfilters_form.php');
    $systemcontext = context_system::instance();
    if (!is_siteadmin() && has_capability('local/organization:manage_trainee',$systemcontext) || !is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
    $filters = array(
       'exams'=>array('local'=>array('examtype', 'sectors')),       
    );
    }
    else{
        $filters = array(
       'exams'=>array('local'=>array('examtype', 'sectors')),       
    );
    }
    $mform = new dynamicfilters_form(null, array('filterlist'=>$filters,'filterparams' => $filterparams,'submitid' =>'viewexams','ajaxformsubmit'=>true), 'post', '', null, true,$_REQUEST);
    return $mform;
}
function examusers_filters_form($filterparams){
    global $CFG;
    require_once($CFG->dirroot . '/local/organization/dynamicfilters_form.php');
    $filters = array(
       // 'exams'=>array('local'=>array('examtype','examduration', 'halls', 'sectors')),
       'exams'=>array('local'=>array('attempt')),       
    );
    $mform = new dynamicfilters_form(null, array('filterlist'=>$filters,'filterparams' => $filterparams,'submitid' =>'viewexams','ajaxformsubmit'=>true), 'post', '', null, true,$_REQUEST);
    return $mform;
}
function attempt_filter($mform){
    global $DB,$USER;
    $examid = optional_param('id', 0, PARAM_INT);
    $attempts = $DB->get_field('local_exams', 'noofattempts', ['id'=>$examid]);
    $systemcontext = context_system::instance();
    $x = 1;
    $totalattempts = [];
    while($x <= $attempts) {
        $row[$x] = get_string('filterattemptnumber', 'local_exams', $x);
        ++$x;
    }
    $mform->addElement('select', 'attemptnumber', get_string('attempt', 'local_exams'), ['0' => get_string('selecttype','local_exams')] + $row);
}
function exam_date_filter($mform) {
    $mform->addElement('date_selector', 'startdate', get_string('lc_startdate','local_events'),array('optional'=>true));
    $mform->setType('startdate', PARAM_RAW);
    $mform->addElement('date_selector', 'enddate', get_string('lc_enddate','local_events'),array('optional'=>true));
    $mform->setType('enddate', PARAM_RAW);
}
function examtype_filter($mform){
    global $DB,$USER;
    $systemcontext = context_system::instance();
    $types = $DB->get_records_sql_menu("SELECT DISTINCT type as id, type FROM {local_exams} ");
    $mform->addElement('select', 'type', get_string('type', 'local_exams'), ['0' => get_string('selecttype','local_exams'), 'professionaltest' => get_string('professionaltest', 'local_exams')] + $types);
    // $mform->addRule('type', get_string('missingtype', 'local_exams'), 'required', null);
}
function examduration_filter($mform){
    $systemcontext = context_system::instance();    
    $mform->addElement('date_selector', 'examdatetime', get_string('startfrom', 'local_exams'),array('optional'=>true));
    $mform->setType('examdatetime', PARAM_RAW);
    $mform->addElement('date_selector', 'enddate', get_string('endto', 'local_exams'),array('optional'=>true));
    $mform->setType('enddate', PARAM_RAW);
}
function sectors_filter($mform){
    global $DB,$USER;
    $lang= current_language();

    if( $lang == 'ar'){
        $sectors = $DB->get_records_sql_menu("SELECT id, titlearabic as title FROM {local_sector} ");
    } else{
        $sectors = $DB->get_records_sql_menu("SELECT id, title FROM {local_sector} ");
    }
    $sectorelement =$mform->addElement('autocomplete','sectors', get_string('sectors', 'local_exams'),$sectors, 
                    ['class' => 'el_sectorlist', 
                    'noselectionstring' =>'',
                    'placeholder' => get_string('sector' , 'local_trainingprogram')]);
    $sectorelement->setMultiple(true);
}
function examjobfamily_filter($mform){
    $jfdattributes = array(
        'ajax' => 'local_trainingprogram/sector_datasource',
        'data-type' => 'jobfamily',
        'id' => 'el_jobfamily',
        'data-sectorid' => 1,
        'multiple'=>true
    );
    $mform->addElement('autocomplete', 'jobfamily',get_string('jobfamily', 'local_trainingprogram'),array(), $jfdattributes);
}

function local_exams_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {

    if ($context->contextlevel != CONTEXT_SYSTEM) {
        return false;
    }

    if ($filearea !== 'learningmaterials' && $filearea !== 'enprofile' && $filearea !== 'arprofile'  && $filearea!== 'materialfile') {
        return false;
    }
     $postid = (int)array_shift($args);
    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/local_exams/$filearea/$postid/$relativepath"; 
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }
    // finally send the file
    send_stored_file($file, 0, 0, true, $options); // download MUST be forced - security!
}
function local_exams_output_fragment_listofhallsform($args){
    global $DB,$CFG,$PAGE;
    $args = (object) $args;
    $context = $args->context;
    $categoryid = $args->categoryid;
    $hallid = $args->hallid;
    $typeid = $args->typeid;
    $o = '';

    $formdata = new stdClass();
    $formdata->typeid = $typeid;
    $formdata->halls = $hallid;
    $params = array(
        'categoryid' => $id,
        'parent' => $category->parent,
        'context' => $context,
        'itemid' => $itemid,
        'typeid' => $typeid,
    );
    $mform = new local_exams\form\listofhallsform(null, $params, 'post', '', null, true, $formdata);
    $mform->set_data($formdata);

    if (!empty($args->jsonformdata)) {
        $mform->is_validated();
    }
    ob_start();
    $mform->display();
    $validateddata = $mform->get_data();
    echo "<div class='selecthall'>{$halldata}</div>";
    $o .= ob_get_contents();
    ob_end_clean();
    return $o;
}
function examlearningmaterial_url($itemid = 0, $filearea) {
    global $DB;
    $context = context_system::instance();
    if ($itemid > 0) {
        $sql = "SELECT * FROM {files} WHERE itemid = :logo AND filearea = :filearea AND filename != '.' ORDER BY id DESC";
        $examlearningmaterialrecord = $DB->get_record_sql($sql,array('logo' => $itemid, 'filearea' => $filearea),1);
       
    }
    if (!empty($examlearningmaterialrecord)) {
        $logourl = moodle_url::make_pluginfile_url($examlearningmaterialrecord->contextid, $examlearningmaterialrecord->component,
       $examlearningmaterialrecord->filearea, $examlearningmaterialrecord->itemid, $examlearningmaterialrecord->filepath,
     $examlearningmaterialrecord->filename);
        $logourl = $logourl->out();
    }
    return $logourl;
}
function organizationusers_filter($mform,$query='',$searchanywhere=false, $page=0, $perpage=25){
    global $DB,$USER,$PAGE;
     $systemcontext = context_system::instance();
    $userparam = array();
    $organizations = array();
    $params = array();
    $data = data_submitted();
   // var_dump($data); exit;
    $sql = " SELECT org.id, org.fullname FROM {local_organization} org ";

    $organizations = $DB->get_records_sql_menu($sql);




    $sql = " SELECT org.id, org.fullname FROM {local_organization} org  ";
    if(is_siteadmin() || has_capability('local/organization:manage_trainingofficial',$systemcontext)) {

        $where = " WHERE 1=1 ORDER BY org.fullname ASC ";
       
        
    } elseif(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
        $organization = $DB->get_field('local_users','organization',array('userid'=>$USER->id));
       $where = " WHERE id = $organization ";
    } else {
        $where = " WHERE 1=1 ORDER BY org.fullname ASC ";
    }
    $organizations = [];
    $organizations = $DB->get_records_sql_menu($sql.$where);

    $selectorg = ['' => get_string('select_orgs', 'local_exams')];

    $current_user_role = (new \local_exams\local\exams)->get_user_role($USER->id);
    $options = array(                                                                         
        'multiple' => $current_user_role->shortname == 'assessmentop' ? false : true,
        'noselectionstring' => get_string('organization', 'local_trainingprogram'),
    );
    $finalorgarray = array_merge($selectorg, $organizations);
    $mform->addElement('autocomplete','organizationusers',get_string('organization', 'local_trainingprogram'),$selectorg + $organizations,$options);
    $mform->setType('organizationusers', PARAM_RAW);
    if ($current_user_role->shortname == 'assessmentop') {
        $mform->addRule('organizationusers', get_string('required'), 'required', null, 'client');
    }
}
/*
* Displays financialpayments
* @return  query
*/
function local_exams_product_orders($stable,$filterdata){
    global $DB, $USER;
    $params          = array();
    $currentlang= current_language();

    if($stable->tablename == 'tool_org_order_payments'){
        $reservationlabel=get_string('profile','local_exams');
        if( $currentlang == 'ar'){
            $tpname='CONCAT(exm.examnamearabic," </br> ('.$reservationlabel.' : ",lep.profilecode,")") as trainingname';
        }else{
            $tpname='CONCAT(exm.exam," </br> ('.$reservationlabel.' : ",lep.profilecode,")") as trainingname';
        }
        $tpname .= ' ,tppmnt.payableamount ';
    }else{
        $reservationlabel=get_string('profile','local_exams');
        if( $currentlang == 'ar'){
            $tpname='CONCAT(exm.examnamearabic," </br> ('.$reservationlabel.' : ",lep.profilecode,")") as trainingname';
        }else{
            $tpname='CONCAT(exm.exam," </br> ('.$reservationlabel.' : ",lep.profilecode,")") as trainingname';
        }
    }

    $fromsql = "SELECT tppmnt.id,$tpname,0 as organization,tppmnt.tablename,tppmnt.fieldname,tppmnt.fieldid ,exm.id as trainingid, 0 as availablefrom, 0 as availableto,tppmnt.purchasedseats,tppmnt.orguserid,exm.exam as englishname,exm.examnamearabic as arabicname,exm.code as ofrcode,lep.profilecode as tpcode ";
    if($stable->tablename == 'tool_order_approval_seats'){
        $fromsql .= ", tppmnt.paymentid";
    }
    $sql = " FROM {" .$stable->tablename. "} AS tppmnt
             JOIN {local_exam_profiles} AS lep ON lep.id = tppmnt.fieldid
             JOIN {local_exams} AS exm ON lep.examid=exm.id ";

    if (isset($stable->orguserid) && $stable->orguserid > 0) {
        $organization = $DB->get_field('local_users','organization',array('userid'=>$stable->orguserid));
        $get_all_orgofficials = (new exams())->get_all_orgofficials($USER->id);
        $orgusers = implode(',',$get_all_orgofficials);
        
        $sql .=  " JOIN {local_users} lu ON lu.userid = tppmnt.orguserid ";
        $sql.= " AND ((tppmnt.orguserid IN ($orgusers) AND tppmnt.organization = $organization) OR (tppmnt.orguserid = $USER->id ))  OR tppmnt.organization = $organization AND lu.deleted = 0 AND lu.approvedstatus = 2";
    }
    $sql .= " WHERE tppmnt.tablename='local_exam_profiles' AND tppmnt.fieldname='id'";
    return array('sql'=>$fromsql.$stable->selectparams.$sql,'params'=>$params);
}
/*
* Displays financialpayments
* @return  query
*/
function local_exams_product_userorders($stable,$filterdata){
    global $DB;
    $params          = array();
    $currentlang= current_language();
    // Profiles payments for trainee
    $reservationlabel=get_string('profile','local_exams');
    if( $currentlang == 'ar'){
        $tpname='CONCAT(exm.examnamearabic," </br> ('.$reservationlabel.' : ",lep.profilecode,")") as trainingname';
    }else{
        $tpname='CONCAT(exm.exam," </br> ('.$reservationlabel.' : ",lep.profilecode,")") as trainingname';
    }

    $fromsql = "SELECT tppmnt.id,$tpname,0 as organization,tppmnt.tablename,tppmnt.fieldname,tppmnt.fieldid ,exm.id as trainingid,0 as availablefrom, 0 as availableto,tppmnt.purchasedseats,tppmnt.userid,exm.exam as englishname,exm.examnamearabic as arabicname,exm.code as ofrcode,lep.profilecode as tpcode, exm.sellingprice ";
    $sql = " FROM {" .$stable->tablename. "} AS tppmnt
             JOIN {local_exam_profiles} AS lep ON lep.id = tppmnt.fieldid
             JOIN {local_exams} AS exm ON lep.examid=exm.id
             WHERE tppmnt.tablename='local_exam_profiles' AND tppmnt.fieldname='id' ";

    if (isset($stable->userid) && $stable->userid > 0) {
        $sql .= " AND tppmnt.userid = $stable->userid ";
    }  

    // Attempts payments for trainee
    $reservationlabel=get_string('attempt','local_exams');

    if( $currentlang == 'ar'){
        $tpname='CONCAT(exm.examnamearabic," </br> ('.$reservationlabel.' : ",lea.attemptid,")") as trainingname';
    }else{
        $tpname='CONCAT(exm.exam," </br> ('.$reservationlabel.' : ",lea.attemptid,")") as trainingname';
    }

    $attemptfromsql = " ) UNION ( SELECT tppmnt.id,$tpname,0 as organization,tppmnt.tablename,tppmnt.fieldname,tppmnt.fieldid ,exm.id as trainingid,0 as availablefrom, 0 as availableto,tppmnt.purchasedseats,tppmnt.userid,exm.exam as englishname,exm.examnamearabic as arabicname,exm.code as ofrcode, CONCAT('attempt', ' ', lea.attemptid ) as tpcode, exm.sellingprice ";
    $attemptsql = " FROM {" .$stable->tablename. "} AS tppmnt
             JOIN {local_exam_attempts} AS lea ON lea.id = tppmnt.fieldid
             JOIN {local_exams} AS exm ON lea.examid=exm.id
             WHERE tppmnt.tablename='local_exam_attempts' AND tppmnt.fieldname='id' ";

    if (isset($stable->userid) && $stable->userid > 0) {
        $attemptsql .= " AND tppmnt.userid = $stable->userid ";
    }

    // grievance payments for trainee

    $reservationlabel=get_string('grievance','local_exams');

    if( $currentlang == 'ar'){
        $tpname='CONCAT(exm.examnamearabic," </br> ('.$reservationlabel.' : ",exm.code,")") as trainingname';
    }else{
        $tpname='CONCAT(exm.exam," </br> ('.$reservationlabel.' : ",exm.code,")") as trainingname';
    }
    $grievancefromsql = " ) UNION ( SELECT tppmnt.id,$tpname,0 as organization,tppmnt.tablename,tppmnt.fieldname,tppmnt.fieldid ,exm.id as trainingid,0 as availablefrom, 0 as availableto,tppmnt.purchasedseats,tppmnt.userid,exm.exam as englishname,exm.examnamearabic as arabicname,exm.code as ofrcode, CONCAT('grievance', ' ', leg.examid ) as tpcode, exm.sellingprice ";
    $grievancesql = " FROM {" .$stable->tablename. "} AS tppmnt
             JOIN {local_exam_grievance} AS leg ON leg.id = tppmnt.fieldid
             JOIN {local_exams} AS exm ON leg.examid=exm.id
             WHERE tppmnt.tablename='local_exam_grievance' AND tppmnt.fieldname='id' ";

    if (isset($stable->userid) && $stable->userid > 0) {
        $grievancesql .= " AND tppmnt.userid = $stable->userid ";
    }
    $finalsql = $fromsql.$stable->selectparams.$sql. ' '.$attemptfromsql.$stable->selectparams.$attemptsql. ' '.$grievancefromsql.$stable->selectparams.$grievancesql;

    return array('sql'=>$finalsql, 'params'=>$params);
}
function local_exams_output_fragment_productdata($args){
    global $DB;
    $product = (object) $args;
    $data = [];
    $data['userid'] = $product->userid;
    $data['paymenttype'] = 'telr';
    $data['productid'] = $product->productid;
    $data['entitytype'] = 'exam';
    $data['total'] = $product->total;
    $data['processtype'] = 'reschedule';
    $data['scheduleid'] = $product->scheduleid;
    $data['profileid'] = !empty($product->profileid) ? $product->profileid : 0;
    $data['category'] = $DB->get_field('tool_products', 'category', ['id' => $product->productid]);
    $data['purchasedseats'] = 0;
    $data['productinfo'] = $DB->get_field('tool_products', 'description', ['id' => $product->productid]);

    return base64_encode(serialize($data));
}

// ************* DL-304: IKRAM CODE START ***************************************
/**
 * Get CISI server access details.
 * 
 */
function get_cisi_user_login_details(){
    $cisi_user_login_credentials = get_config('local_lmsws');
    
    if (!$cisi_user_login_credentials->cisiurl) {
        print_error('cisiurl_missing', 'local_exams');
    }
    if (!$cisi_user_login_credentials->clientid) {
        print_error('clientid_missing', 'local_exams');
    }
    if (!$cisi_user_login_credentials->clientsecret) {
        print_error('clientsecret_missing', 'local_exams');
    }
    if (!$cisi_user_login_credentials->cisiusername) {
        print_error('cisiusername_missing', 'local_exams');
    }
    if (!$cisi_user_login_credentials->cisipassword) {
        print_error('cisipassword_missing', 'local_exams');
    }
    if (!$cisi_user_login_credentials->cisiscope) {
        print_error('cisiscope_missing', 'local_exams');
    }
    return $cisi_user_login_credentials;
}
/**
 * This function will fetch exam records for the purchased exam/product.
 * @param (INT) $product id Id of the product purchased.
 * @return purchased Item record.
 */
function get_exam_records_from_productid($productid, $categoryid){
    global $DB;
    $exam_record = [];
    if (!$productid) {
        print_error('productidmissing', 'local_exams');
    }
    if($categoryid == 2) {
        $exam_record = $DB->get_record_sql("SELECT ex.*, ep.profilecode examcode 
        FROM {tool_products} tp 
        JOIN {local_exam_profiles} ep ON tp.referenceid = ep.id 
        JOIN {local_exams} ex ON ex.id = ep.examid 
        WHERE tp.id = '$productid' ");
    } else if($categoryid == 6) {
        $exam_record = $DB->get_record_sql("SELECT ex.*
        FROM {tool_products} tp 
        JOIN {local_exam_attempts} ea ON tp.referenceid = ea.id
        JOIN {local_exams} ex ON ex.id = ea.examid 
        WHERE tp.id = '$productid' ");
    }
    return $exam_record;
}
/**
 * Fetch hall schedule from user selected hall scheduled id
 * @param int $hallscheduleid
 * @return Hall schedules.
 */
function get_exam_hall_schedules($hallscheduleid){
    global $DB;
    if (!$hallscheduleid) {
        print_error('productidmissing', 'local_exams');
    }
    $hall_schedules = $DB->get_record_sql("SELECT h.code venueid, hs.*, lhs.examid
        FROM {hall} h
        JOIN {hallschedule} hs ON hs.hallid = h.id 
        JOIN {local_exam_userhallschedules} lhs ON lhs.hallscheduleid = hs.id
        WHERE lhs.hallscheduleid = :hallscheduleid ", ['hallscheduleid' => $hallscheduleid]);
    return $hall_schedules;
}
/**
 * If Exam is owned by CISI then Data needed to be send to CISI server.
 * This function will map the purchased exam to CISI
 * @param array $exam Array of exams purchased
 * 
 */
function cisi_exams_booking($summary){
    global $DB, $USER;
    $cisiservises = new cisi_services();
    $context = context_system::instance();
    if (!empty($summary)) {
        foreach($summary as $item){
            $no_reply_user = \core_user::get_noreply_user();
            $notification = new notification();
            $exam_officials = $notification->getsystemlevel_role_users('examofficial', 0);
            $errorobj = new stdClass();
            $productid = $item['product_id'];
            // Fetch exam records based on product id.
            // TRK164_12096 $item['category']
            if($item['category'] == 2 || $item['category'] == 6) {

                $exam_record = get_exam_records_from_productid($productid, $item['category']);
                if ($exam_record->ownedby == 'CISI') {
                    try {
                        $cisi_accessdetails = get_cisi_user_login_details();
                        
                    } catch (Exception $e) {
                        \core\notification::error(get_string('exam_booking_failed_at_cisi', 'local_exams'));
                        $data = [
                            'context'=>$context,
                            'objectid' => $exam_record->id,
                            'userid' => $USER->id,
                            'other' => ['message' => $e->getMessage()]
                        ];
                        $event =  \local_exams\event\exam_booking_failed::create($data);
                        $event->trigger();
                        $errorobj->errormessage = $e->getMessage();
                        foreach ($exam_officials as $officials) {
                            $errorobj->firstname = $officials->firstname;
                            $errorobj->trainee_username = $USER->username;
                            $errorobj->examname = $exam_record->exam;
                            send_notification($officials, $errorobj);
                        }
                        
                        return ;
                    }
                    $hallscheduleid = $item['hallscheduleid'];
                    // Fetch Schedules.
                    $hall_schedules = get_exam_hall_schedules($hallscheduleid);
                    $ex_date = userdate($hall_schedules->startdate, '%Y-%m-%d'); // Exam Date
                    $ex_starttime = userdate($hall_schedules->starttime, get_string('strftimetime24', 'langconfig'));
                    $examdate = $ex_date . ' '. $ex_starttime; // Exam Time
                    $authToken = $cisiservises->AuthenticationSigninAuthToken($cisi_accessdetails->clientid, $cisi_accessdetails->clientsecret, $cisi_accessdetails->cisiusername, $cisi_accessdetails->cisipassword, $cisi_accessdetails->cisiscope);
                    if ($authToken->success) {
                        $event = \local_exams\event\cisi_auth_token_created::create(array( 'context'=>$context, 'objectid' => $exam_record->id, 'userid' => $USER->id, 'other' => $authToken));
                        $event->trigger();
                    }else{
                        $event = \local_exams\event\cisi_auth_token_creation_failed::create(array( 'context'=>$context, 'objectid' => $exam_record->id, 'userid' => $USER->id, 'other' => $authToken));
                        $event->trigger();
                        \core\notification::error(get_string('exam_booking_failed_at_cisi', 'local_exams'));
                        $errorobj->errormessage = get_string('exam_booking_failed_at_cisi', 'local_exams');
                        foreach ($exam_officials as $officials) {
                            $errorobj->firstname = $officials->firstname;
                            $errorobj->trainee_username = $USER->username;
                            $errorobj->examname = $exam_record->exam;
                            send_notification($officials, $errorobj);
                        }
                        return ;
                    }
                    $accessToken = $cisiservises->AuthenticationTokenAccessToken($authToken->results->authToken, $cisi_accessdetails->clientid, $cisi_accessdetails->clientsecret);
                    if ($accessToken->success) {
                        $event = \local_exams\event\cisi_access_token_created::create(array( 'context'=>$context, 'objectid' => $exam_record->id, 'userid' => $USER->id, 'other' => $accessToken));
                        $event->trigger();
                    }else{
                        $event = \local_exams\event\cisi_access_token_creation_failed::create(array( 'context'=>$context, 'objectid' => $exam_record->id, 'userid' => $USER->id, 'other' => $accessToken));
                        $event->trigger();
                        \core\notification::error(get_string('exam_booking_failed_at_cisi', 'local_exams'));
                        $errorobj->errormessage = get_string('exam_booking_failed_at_cisi', 'local_exams');
                        foreach ($exam_officials as $officials) {
                            $errorobj->firstname = $officials->firstname;
                            $errorobj->trainee_username = $USER->username;
                            $errorobj->examname = $exam_record->exam;
                            send_notification($officials, $errorobj);
                        }
                        return ;
                    }
                    // Check if user belongs to CISI or not.
                    $user_cisi = new stdClass();
                    $user_cisi = $DB->get_record('externalprovider_userdetails', ['userid' => $USER->id]);
                    if ($user_cisi->externaluserid) {
                        // Intentionally left blank..
                    }else{
                        $dob = $DB->get_field('local_users', 'dateofbirth', ['userid' => $USER->id]);
                        
                        $user_cisi = $cisiservises->cisi_create_user($accessToken->results->accessToken, 'Mr./Ms', $USER->email, $USER->lastname, $USER->firstname, date('Y-m-d',$dob));
                        if ($user_cisi->success) {
                            $user_cisi->externaluserid = $user_cisi->results->id;
                            $event = \local_exams\event\cisi_user_creation_successful::create(array( 'context'=>$context, 'objectid' => $user_cisi->externaluserid, 'userid' => $USER->id, 'other' => $user_cisi));
                            $event->trigger();
    
                        }else{
                            /**
                             * If user is missing in FA but it is registered there on CISI
                             * then record its external userid
                             * 
                             */
                            if (is_numeric($user_cisi->extraInfo)) {
                                $user = new stdClass();
                                $user->externaluserid = $user_cisi->results[0]->extraInfo;
                                $user->userid = $USER->id;
                                $user->externalprovidername = 'cisi';
                                $user->createdtime = time();
                                $user->status = 1;
                                $DB->insert_record('externalprovider_userdetails', $user);
                                $user_cisi->externaluserid = $user_cisi->results[0]->extraInfo;
                                
                            }else{
                                $event = \local_exams\event\cisi_user_creation_failed::create(array( 'context'=>$context, 'objectid' => $USER->id, 'userid' => $USER->id, 'other' => $user_cisi));
                                $event->trigger();
                                \core\notification::error(get_string('exam_booking_failed_at_cisi', 'local_exams'));
                                $errorobj->errormessage = get_string('exam_booking_failed_at_cisi', 'local_exams');
                                foreach ($exam_officials as $officials) {
                                    $errorobj->firstname = $officials->firstname;
                                    $errorobj->trainee_username = $USER->username;
                                    $errorobj->examname = $exam_record->exam;
                                    send_notification($officials, $errorobj);
                                }
                                return;
                            }
                        }
                    }
                    if ($user_cisi->externaluserid) {
                        $hallcode = (new \local_hall\hall())->get_hallcode($hall_schedules->hallid, $hall_schedules->examid);
                        $booking = $cisiservises->cisi_exam_mapping($accessToken->results->accessToken, $user_cisi->externaluserid, $exam_record->examcode, $hallcode, $examdate);
                        if ($booking->success) {
                            \core\notification::success(get_string('cisi_exams_booking_success', 'local_exams'));
                            $event =  \local_exams\event\exam_booking_successful::create(array( 'context'=>$context, 'objectid' => $exam_record->id, 'userid' => $USER->id, 'other' => $booking));
                            $event->trigger();
                        }else{
                            \core\notification::error(get_string('exam_booking_failed_at_cisi', 'local_exams'));
                            $event =  \local_exams\event\exam_booking_failed::create(array( 'context'=>$context, 'objectid' => $exam_record->id, 'userid' => $USER->id, 'other' => $booking));
                            $event->trigger();
                            $errorobj->errormessage = get_string('exam_booking_failed_at_cisi', 'local_exams');
                            foreach ($exam_officials as $officials) {
                                $errorobj->firstname = $officials->firstname;
                                $errorobj->trainee_username = $USER->username;
                                $errorobj->examname = $exam_record->exam;
                                send_notification($officials, $errorobj);
                            }
                            return;
                        }
                    }
                }
                $all_bookings[$productid] = $booking;
            }
            return $all_bookings;


            }
            
    }
    return false;
}

/**
 * Send User Notification
 * 
 */
function send_notification($userto, $errorobj){
    $message = new \core\message\message();
    $message->component = 'local_exams';
    $message->name = get_string('cisi_booking_failed', 'local_exams'); 
    $message->userfrom = core_user::get_noreply_user(); 
    $message->userto = $userto;
    $message->subject = get_string('cisi_exams_booking_failed', 'local_exams');
    $message->fullmessage = get_string('message_error_details', 'local_exams', $errorobj);
    $message->fullmessageformat = FORMAT_MARKDOWN;
    $message->fullmessagehtml = get_string('message_error_details', 'local_exams', $errorobj);
    $message->smallmessage = '';
    $message->notification = 1;
    message_send($message);
}

// Self Schedule CISI Exam
function schedule_cisi_exam_slot($exam_record, $hall_schedules, $user){
    global $DB;
    $cisiservises = new cisi_services();
    $context = context_system::instance();
    $no_reply_user = \core_user::get_noreply_user();
    $notification = new notification();
    $exam_officials = $notification->getsystemlevel_role_users('examofficial', 0);
    $errorobj = new stdClass();
    try {
        $cisi_accessdetails = get_cisi_user_login_details();
        
    } catch (Exception $e) {
        \core\notification::error(get_string('exam_booking_failed_at_cisi', 'local_exams'));
        $data = [
            'context'=>$context,
            'objectid' => $exam_record->id,
            'userid' => $user->id,
            'other' => ['message' => $e->getMessage()]
        ];
        $event =  \local_exams\event\exam_booking_failed::create($data);
        $event->trigger();
        $errorobj->errormessage = $e->getMessage();
        foreach ($exam_officials as $officials) {
            $errorobj->firstname = $officials->firstname;
            $errorobj->trainee_username = $user->username;
            $errorobj->examname = $exam_record->exam;
            send_notification($officials, $errorobj);
        }
        return;
    }
    $ex_date = userdate($hall_schedules->startdate, '%Y-%m-%d'); // Exam Date
    $ex_starttime = userdate($hall_schedules->starttime, get_string('strftimetime24', 'langconfig'));
    $examdate = $ex_date . ' '. $ex_starttime; // Exam Time
    // print_r($examdate);die;
    $authToken = $cisiservises->AuthenticationSigninAuthToken($cisi_accessdetails->clientid, $cisi_accessdetails->clientsecret, $cisi_accessdetails->cisiusername, $cisi_accessdetails->cisipassword, $cisi_accessdetails->cisiscope);
    if ($authToken->success) {
        $event = \local_exams\event\cisi_auth_token_created::create(array( 'context'=>$context, 'objectid' => $exam_record->id, 'userid' => $user->id, 'other' => $authToken));
        $event->trigger();
    }else{
        $event = \local_exams\event\cisi_auth_token_creation_failed::create(array( 'context'=>$context, 'objectid' => $exam_record->id, 'userid' => $user->id, 'relateduserid' => $user->id, 'other' => $authToken));
        $event->trigger();
        \core\notification::error(get_string('exam_booking_failed_at_cisi', 'local_exams'));
        $errorobj->errormessage = get_string('exam_booking_failed_at_cisi', 'local_exams');
        foreach ($exam_officials as $officials) {
            $errorobj->firstname = $officials->firstname;
            $errorobj->trainee_username = $user->username;
            $errorobj->examname = $exam_record->exam;
            send_notification($officials, $errorobj);
        }
        return;
    }
    $accessToken = $cisiservises->AuthenticationTokenAccessToken($authToken->results->authToken, $cisi_accessdetails->clientid, $cisi_accessdetails->clientsecret);
    if ($accessToken->success) {
        $event = \local_exams\event\cisi_access_token_created::create(array( 'context'=>$context, 'objectid' => $exam_record->id, 'userid' => $user->id, 'relateduserid' => $user->id, 'other' => $accessToken));
        $event->trigger();
    }else{
        $event = \local_exams\event\cisi_access_token_creation_failed::create(array( 'context'=>$context, 'objectid' => $exam_record->id, 'userid' => $user->id, 'relateduserid' => $user->id, 'other' => $accessToken));
        $event->trigger();
        \core\notification::error(get_string('exam_booking_failed_at_cisi', 'local_exams'));
        $errorobj->errormessage = get_string('exam_booking_failed_at_cisi', 'local_exams');
        foreach ($exam_officials as $officials) {
            $errorobj->firstname = $officials->firstname;
            $errorobj->trainee_username = $user->username;
            $errorobj->examname = $exam_record->exam;
            send_notification($officials, $errorobj);
        }
        return;
    }
    // Check if user belongs to CISI or not.
    $user_cisi = new stdClass();
    $user_cisi = $DB->get_record('externalprovider_userdetails', ['userid' => $user->id]);
    if ($user_cisi->externaluserid) {
        // Intentionally left blank..
    }else{
        $dob = $DB->get_field('local_users', 'dateofbirth', ['userid' => $user->id]);
        if (!$dob) {
            \core\notification::error('missing_dob', 'local_exams');
            $event = \local_exams\event\cisi_user_creation_failed::create(array( 'context'=>$context, 'objectid' => $user->id, 'userid' => $user->id, 'relateduserid' => $user->id));
            $event->trigger();
            return;
        }
        $user_cisi = $cisiservises->cisi_create_user($accessToken->results->accessToken, 'Mr./Ms', $user->email, $user->lastname, $user->firstname, date('Y-m-d',$dob));
        if ($user_cisi->success) {
            $user_cisi->externaluserid = $user_cisi->results->id;
            $event = \local_exams\event\cisi_user_creation_successful::create(array( 'context'=>$context, 'objectid' => $user_cisi->externaluserid, 'userid' => $user->id, 'relateduserid' => $user->id, 'other' => $user_cisi));
            $event->trigger();

        }else{
            /**
             * If user is missing in FA but it is registered there on CISI
             * then record its external userid
             * 
             */
            if (is_numeric($user_cisi->extraInfo)) {
                $user = new stdClass();
                $user->externaluserid = $user_cisi->results[0]->extraInfo;
                $user->userid = $user->id;
                $user->externalprovidername = 'cisi';
                $user->createdtime = time();
                $user->status = 1;
                $DB->insert_record('externalprovider_userdetails', $user);
                $user_cisi->externaluserid = $user_cisi->results[0]->extraInfo;
                
            }else{
                $event = \local_exams\event\cisi_user_creation_failed::create(array( 'context'=>$context, 'objectid' => $user->id, 'userid' => $user->id, 'relateduserid' => $user->id, 'other' => $user_cisi));
                $event->trigger();
                \core\notification::error(get_string('exam_booking_failed_at_cisi', 'local_exams'));
                $errorobj->errormessage = get_string('exam_booking_failed_at_cisi', 'local_exams');
                foreach ($exam_officials as $officials) {
                    $errorobj->firstname = $officials->firstname;
                    $errorobj->trainee_username = $user->username;
                    $errorobj->examname = $exam_record->exam;
                    send_notification($officials, $errorobj);
                }
                return;
            }
        }
    }
    $booking = [];
    if ($user_cisi->externaluserid) {
        $booking = $cisiservises->cisi_exam_mapping($accessToken->results->accessToken, $user_cisi->externaluserid, $exam_record->examcode, $hall_schedules->venueid, $examdate);
        if ($booking->success) {
            \core\notification::success(get_string('cisi_exams_booking_success', 'local_exams'));
            $event =  \local_exams\event\exam_booking_successful::create(array( 'context'=>$context, 'objectid' => $exam_record->id, 'userid' => $user->id, 'relateduserid' => $user->id, 'other' => $booking));
            $event->trigger();
            return $booking;
        }else{
            \core\notification::error(get_string('exam_booking_failed_at_cisi', 'local_exams'));
            $event =  \local_exams\event\exam_booking_failed::create(array( 'context'=>$context, 'objectid' => $exam_record->id, 'userid' => $user->id, 'relateduserid' => $user->id, 'other' => $booking));
            $event->trigger();
            $errorobj->errormessage = get_string('exam_booking_failed_at_cisi', 'local_exams');
            foreach ($exam_officials as $officials) {
                $errorobj->firstname = $officials->firstname;
                $errorobj->trainee_username = $user->username;
                $errorobj->examname = $exam_record->exam;
                send_notification($officials, $errorobj);
            }
            return;
        }
    }
    return false;
}
// Check if user has already scheduled the hall.
function is_hall_already_scheduled($examid, $profileid, $hallscheduelid, $userid) {
    global $DB;
    $userhall_scheduled_data = $DB->get_record_sql("SELECT lhs.*
        FROM {hall} h
        JOIN {hallschedule} hs ON hs.hallid = h.id 
        JOIN {local_exam_userhallschedules} lhs ON lhs.hallscheduleid = hs.id
        WHERE hs.id = :hallscheduleid AND profileid = :profileid AND examid = :examid AND userid = :userid ", ['hallscheduleid' => $hallscheduelid, 'profileid' => $profileid, 'userid' => $userid, 'examid' => $examid]);
    return $userhall_scheduled_data;
}
function exams_fastexamenroll_filters_form($filterparams){
    global $CFG;
    require_once($CFG->dirroot . '/local/organization/dynamicfilters_form.php');
    $filters = array(
        'exams'=>array('local'=>array('fast_examdatetime','fast_purchaseddatetime','fast_type','fast_centercode','fast_examcode','fast_profilecode','fast_examlanguage')),
    );
    $mform = new dynamicfilters_form(null, array('filterlist'=>$filters,'filterparams' => $filterparams,'submitid' =>'manage_examenrol','ajaxformsubmit'=>true), 'post', '', null, true,$_REQUEST);
    return $mform;
}

function fast_examdatetime_filter($mform){

    $mform->addElement('date_time_selector', 'examdatetimefrom', get_string('exam_datefrom', 'local_exams'),array('optional'=>true));
    $mform->setType('availablefrom', PARAM_RAW);

    $mform->addElement('date_time_selector', 'examdatetimeto', get_string('exam_dateto', 'local_exams'),array('optional'=>true));
    $mform->setType('availableto', PARAM_RAW);

}

function fast_purchaseddatetime_filter($mform){

    $mform->addElement('date_time_selector', 'purchasedatetimefrom', get_string('purchasedatetimefrom', 'local_exams'),array('optional'=>true));
    $mform->setType('availablefrom', PARAM_RAW);

    $mform->addElement('date_time_selector', 'purchasedatetimeto', get_string('purchasedatetimeto', 'local_exams'),array('optional'=>true));
    $mform->setType('availableto', PARAM_RAW);

}

function fast_type_filter($mform){

    $status = [];
    $status['hall_validation'] = get_string('hall_validation','local_exams');
    $status['register'] = get_string('register','local_exams');
    $status['cancel_validation'] = get_string('cancel_validation','local_exams');
    $status['cancel'] = get_string('cancel','local_exams');
    $status['reschedule_validation'] = get_string('reschedule_validation','local_exams');
    $status['reschedule'] = get_string('reschedule','local_exams');

    $programelement =$mform->addElement('autocomplete','type', get_string('type', 'local_exams'),$status,['id' => 'elfasttype']);
    $programelement->setMultiple(true);

}
function fast_centercode_filter($mform,$query='',$searchanywhere=false, $page=0, $perpage=25){
    global $DB;
    $usernamelists=$DB->get_records_sql(
        "SELECT DISTINCT centercode,centercode AS fullname FROM {local_fast_examenrol} ORDER BY username ASC"
    );
    $usernames=[];
    foreach ($usernamelists AS $usernamelist){ 
        $usernames[$usernamelist->centercode] = $usernamelist->fullname;
    }
    $usernameelement = $mform->addElement('autocomplete', 'centercode', get_string('centercode', 'local_exams'),$usernames, ['id' => 'elfastcentercode']);
    $usernameelement->setMultiple(true);
} 
function fast_examcode_filter($mform,$query='',$searchanywhere=false, $page=0, $perpage=25){
    global $DB;
    $usernamelists=$DB->get_records_sql(
        "SELECT DISTINCT examcode,examcode AS fullname FROM {local_fast_examenrol} ORDER BY username ASC"
    );
    $usernames=[];
    foreach ($usernamelists AS $usernamelist){ 
        $usernames[$usernamelist->examcode] = $usernamelist->fullname;
    }
    $usernameelement = $mform->addElement('autocomplete', 'examcode', get_string('examcode', 'local_exams'),$usernames, ['id' => 'elfastexamcode']);
    $usernameelement->setMultiple(true);
}
function fast_profilecode_filter($mform,$query='',$searchanywhere=false, $page=0, $perpage=25){
    global $DB;
    $usernamelists=$DB->get_records_sql(
        "SELECT DISTINCT profilecode,profilecode AS fullname FROM {local_fast_examenrol} ORDER BY username ASC"
    );
    $usernames=[];
    foreach ($usernamelists AS $usernamelist){ 
        $usernames[$usernamelist->profilecode] = $usernamelist->fullname;
    }
    $usernameelement = $mform->addElement('autocomplete', 'profilecode', get_string('profilecode', 'local_exams'),$usernames, ['id' => 'elprofilecode']);
    $usernameelement->setMultiple(true);
} 

function fast_examlanguage_filter($mform){

    $language = [];
    $language['English'] = get_string('english','local_exams');
    $language['Arabic'] = get_string('arabic','local_exams');

    $programelement =$mform->addElement('autocomplete','examlanguage', get_string('examlanguage', 'local_exams'),$language,['id' => 'elfastexamlanguage']);
    $programelement->setMultiple(true);

}
/**
 * Get list of reservations against an Exam
 * @param Int Exam ID
 * @return List of reservations Std Object
 */
function get_exam_schedules(int $examid, $stable = false, $filtervalues = false) {
    global $DB;
    $params = array();

    if ($examid) {
        $sql = "SELECT @row_num := @row_num + 1 AS row_num, hs.id scheduleid, ex.id examid, DATE_FORMAT(FROM_UNIXTIME(hs.startdate), '%W, %d %M %Y') startdate, h.id hallid, h.name hallname, h.city, h.maplocation,h.entrancegate,h.buildingname,DATE_FORMAT(FROM_UNIXTIME(hs.starttime), '%H:%i') hallstarttime, DATE_FORMAT(FROM_UNIXTIME(hs.endtime), '%H:%i') hallendtime, h.code
            FROM (SELECT @row_num := 0) AS r, {hallschedule} hs
            JOIN {hall} h ON h.id = hs.hallid
            JOIN {local_exams} ex ON ex.id = hs.entityid

            WHERE 1=1
        ";
        $formsql .= ' AND hs.entityid ='.$examid;
        if( $filtervalues->{'reservation_datefrom[enabled]'} == 1 && $filtervalues->{'reservation_dateto[enabled]'} == 1 ){
            $start_year = $filtervalues->{'reservation_datefrom[year]'};
            $start_month = $filtervalues->{'reservation_datefrom[month]'};
            $start_day = $filtervalues->{'reservation_datefrom[day]'};
            $filter_starttime_con = mktime(0,0,0, $start_month, $start_day, $start_year);

            $end_year = $filtervalues->{'reservation_dateto[year]'};
            $end_month = $filtervalues->{'reservation_dateto[month]'};
            $end_day = $filtervalues->{'reservation_dateto[day]'};
            $filter_endtime_con=mktime(23,59,59, $end_month, $end_day, $end_year);
            $formsql.= " AND (hs.startdate >= $filter_starttime_con AND hs.startdate < $filter_endtime_con) ";
        } elseif($filtervalues->{'reservation_datefrom[enabled]'} == 1 ){
            $start_year = $filtervalues->{'reservation_datefrom[year]'};
            $start_month = $filtervalues->{'reservation_datefrom[month]'};
            $start_day = $filtervalues->{'reservation_datefrom[day]'};
            $filter_starttime_con = mktime(0,0,0, $start_month, $start_day, $start_year);
            $formsql.= " AND hs.startdate >= '$filter_starttime_con' ";
        } elseif($filtervalues->{'reservation_dateto[enabled]'} == 1 ){
            $start_year = $filtervalues->{'reservation_dateto[year]'};
            $start_month = $filtervalues->{'reservation_dateto[month]'};
            $start_day = $filtervalues->{'reservation_dateto[day]'};
            $filter_endtime_con=mktime(23,59,59, $start_month, $start_day, $start_year);
            $formsql.=" AND hs.startdate <= '$filter_endtime_con' ";
        }
        if  (isset($filtervalues->search_query) && trim($filtervalues->search_query) != ''){
            $formsql .= " AND (
                h.name LIKE :hallname OR 
                h.city LIKE :city  OR 
                h.buildingname LIKE :buildingname OR 
                h.code LIKE :code
            ) ";
            $searchparams = array(
                'hallname' => '%'.trim($filtervalues->search_query).'%',
                'city' => '%'.trim($filtervalues->search_query).'%',
                'buildingname' => '%'.trim($filtervalues->search_query).'%',
                'code' => '%'.trim($filtervalues->search_query).'%',
                  
            );
        }
        $schedules  = $DB->get_records_sql($sql.$formsql, $searchparams, $stable->start,$stable->length);
        foreach ($schedules as $schedule) {
            if (filter_var($schedule->maplocation, FILTER_VALIDATE_URL)) {
                $schedule->maplocation = html_writer::link($schedule->maplocation, '<i class="fa fa-map-marker"></i>');
            }
        }
        $totalrecords  = $DB->get_records_sql($sql.$formsql, $searchparams);
        return ['schedules' => $schedules, 'totalrecords' => count($totalrecords)];
    }
}

/**
 * delete_exam_schedule
 * 
 */
function delete_exam_schedule($scheduleid) {
    global $DB;
    if ($scheduleid) {
        try {
            $status = $DB->delete_records('hallschedule', ['id' => $scheduleid]);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 1);
            
        }
        return $status;
    }
    return false;
}
/**
 * get all the records of reservation mapped to an exam.
 * @param INT examid
 * @return stdClass $reservations
 */
function fetch_reservations_for_exam($examid, $filterdata = null, $stable = false) {
    global $DB;
    $fullname = (new \local_trainingprogram\local\trainingprogram())->user_fullname_case();
    $sql = "SELECT uhs.id reservationid, $fullname,h.name hallname,
    -- IF(uhs.hallscheduleid = 0, '--', (SELECT h.name FROM {hall} h JOIN {hallschedule} hs ON hs.hallid = h.id WHERE hs.id = uhs.hallscheduleid ) )hallname, 
    lc.id_number, 
    ep.profilecode, DATE_FORMAT(FROM_UNIXTIME(hs.startdate), '%W, %d %M %Y') AS 'date', DATE_FORMAT(FROM_UNIXTIME(hs.starttime), '%H:%i') reservationstart, DATE_FORMAT(FROM_UNIXTIME(hs.endtime), '%H:%i') reservationend
            ,IF(uhs.attemptid > 0, (SELECT attemptid FROM {local_exam_attempts} WHERE id = uhs.attemptid AND examid = uhs.examid), 1) attemptid, uhs.examid
            FROM {local_exam_userhallschedules} uhs
            LEFT JOIN {hallschedule} hs ON hs.id = uhs.hallscheduleid
            LEFT JOIN {hall} h ON h.id = hs.hallid
            JOIN {local_exam_profiles} ep ON ep.id = uhs.profileid
            JOIN {local_users} lc ON lc.userid = uhs.userid
            JOIN {user} u ON u.id = lc.userid
            WHERE 1=1
        ";
        $formsql .= ' AND uhs.examid ='.$examid;
        if( $filterdata->{'reservation_datefrom[enabled]'} == 1 && $filterdata->{'reservation_dateto[enabled]'} == 1 ){
            $start_year = $filterdata->{'reservation_datefrom[year]'};
            $start_month = $filterdata->{'reservation_datefrom[month]'};
            $start_day = $filterdata->{'reservation_datefrom[day]'};
            $filter_starttime_con = mktime(0,0,0, $start_month, $start_day, $start_year);

            $end_year = $filterdata->{'reservation_dateto[year]'};
            $end_month = $filterdata->{'reservation_dateto[month]'};
            $end_day = $filterdata->{'reservation_dateto[day]'};
            $filter_endtime_con=mktime(23,59,59, $end_month, $end_day, $end_year);
            $formsql.= " AND (hs.startdate >= $filter_starttime_con AND hs.startdate < $filter_endtime_con) ";
        } elseif($filterdata->{'reservation_datefrom[enabled]'} == 1 ){
            $start_year = $filterdata->{'reservation_datefrom[year]'};
            $start_month = $filterdata->{'reservation_datefrom[month]'};
            $start_day = $filterdata->{'reservation_datefrom[day]'};
            $filter_starttime_con = mktime(0,0,0, $start_month, $start_day, $start_year);
            $formsql.= " AND hs.startdate >= '$filter_starttime_con' ";
        } elseif($filterdata->{'reservation_dateto[enabled]'} == 1 ){
            $start_year = $filterdata->{'reservation_dateto[year]'};
            $start_month = $filterdata->{'reservation_dateto[month]'};
            $start_day = $filterdata->{'reservation_dateto[day]'};
            $filter_endtime_con=mktime(23,59,59, $start_month, $start_day, $start_year);
            $formsql.=" AND hs.startdate <= '$filter_endtime_con' ";
        }
        if  (isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
            $formsql .= " AND (
                lc.firstname LIKE :firstnamesearch OR 
                lc.lastname LIKE :lastnamesearch  OR 
                lc.firstnamearabic LIKE :firstnamearabicsearch OR 
                lc.lastnamearabic LIKE :lastnamearabicsearch  OR
                lc.middlenameen LIKE :middlenameensearch OR
                lc.middlenamearabic LIKE :middlenamearabicsearch OR
                lc.thirdnameen LIKE :thirdnameensearch OR 
                lc.thirdnamearabic LIKE :thirdnamearabicsearch OR 
                lc.id_number LIKE :hallname OR 
                h.name LIKE :id_numbersearch OR
                ep.profilecode LIKE :profilecode
            ) ";
            $searchparams = array(
                'firstnamesearch' => '%'.trim($filterdata->search_query).'%',
                'lastnamesearch' => '%'.trim($filterdata->search_query).'%',
                'firstnamearabicsearch' => '%'.trim($filterdata->search_query).'%',
                'lastnamearabicsearch' => '%'.trim($filterdata->search_query).'%',
                'middlenameensearch' => '%'.trim($filterdata->search_query).'%',
                'middlenamearabicsearch' => '%'.trim($filterdata->search_query).'%',
                'thirdnameensearch' => '%'.trim($filterdata->search_query).'%',
                'thirdnamearabicsearch' => '%'.trim($filterdata->search_query).'%',
                'hallname' => '%'.trim($filterdata->search_query).'%',
                'id_numbersearch' => '%'.trim($filterdata->search_query).'%',
                'profilecode' => '%'.trim($filterdata->search_query).'%',
                  
            );
        }
        $params = array_merge($searchparams);
        $reservations = $DB->get_records_sql($sql.$formsql, $searchparams, $stable->start,$stable->length);
        $i = $stable->start+1;
        foreach ($reservations as $reservation) {
            $reservation->row_num = $i;
            $i++;
        }
        $totalrecords = count($DB->get_records_sql($sql.$formsql, $searchparams));
        return ['reservations' => $reservations, 'totalrecords' => $totalrecords];
}

function exams_reservation_filters_form($filterparams){
    global $CFG;
    require_once($CFG->dirroot . '/local/organization/dynamicfilters_form.php');
    $filters = array(
       'exams'=>array('local'=>array('reservation_date')),
    );
    $mform = new dynamicfilters_form(null, array('filterlist'=>$filters,'filterparams' => $filterparams,'submitid' =>'viewschedules','ajaxformsubmit'=> true, 'disablebuttons' => 0), 'post', '', null, true,$_REQUEST);
    return $mform;
}
function reservation_date_filter($mform){

    $mform->addElement('date_time_selector', 'reservation_datefrom', get_string('reservation_datefrom', 'local_exams'),array('optional'=>true));
    $mform->setType('availablefrom', PARAM_RAW);

    $mform->addElement('date_time_selector', 'reservation_dateto', get_string('reservation_dateto', 'local_exams'),array('optional'=>true));
    $mform->setType('availableto', PARAM_RAW);

}
/**
 * --------------------------------------------------
 * ||                                               ||
 * ||        Exam Certificate Filters               ||
 * ||                                               ||
 * --------------------------------------------------
 */
function exam_certificate_filters_form($filterparams){
    global $CFG;
    require_once($CFG->dirroot . '/local/organization/dynamicfilters_form.php');
    $filters = array(
       'exams'=>array('local'=>array('id_number', 'cer_organization', 'exam', 'exam_center', 'dates', 'certificatestatus')),
    );
    $mform = new dynamicfilters_form(null, array(
        'filterlist'=>$filters,
        'filterparams' => $filterparams, 
        'submitid' =>'view_exam_schedules',
        'ajaxformsubmit'=> true, 
        'disablebuttons' => 0), 'post', '', null, true,$_REQUEST);
    return $mform;
}
function id_number_filter($mform){
    global $DB;
    $str = get_string('enter_user_id_number', 'local_exams');
    $user_idnumber = [];
    $user_idnumber = $DB->get_records_sql_menu("SELECT u.id userid, lc.id_number
                FROM {user} u
                JOIN {local_users} lc ON lc.userid = u.id 
                JOIN {exam_enrollments} ee ON ee.userid = lc.userid
                WHERE (lc.id_number <> '' AND lc.id_number <> 0)");
    $noselectionstring = ['noselectionstring' => $str];
    $mform->addElement('autocomplete', 'idnumber', get_string('user_id_number', 'local_exams'), [], formattributes($str, 'id_idnumber', 'fetch_id_number'));
    $mform->setType('idnumber', PARAM_RAW);
}

function cer_organization_filter($mform){
    global $DB;
    $lang = current_language();
    if ($lang == 'en') {
        $name = ' fullname ';
    }else {
        $name = ' fullnameinarabic ';
    }
    $organizations = [];
    $organizations = $DB->get_records_sql_menu("SELECT id, $name FROM {local_organization} WHERE 1=1 ");
    $str = get_string('enter_organization', 'local_exams');
    $noselectionstring = ['noselectionstring' => $str];
    $mform->addElement('autocomplete', 'organization', get_string('cer_organization', 'local_exams'), [], formattributes($str, 'id_organization', 'fetch_organization'));
    $mform->setType('organization', PARAM_RAW);
}
function exam_filter($mform){
    global $DB;
    $lang = current_language();
    if ($lang == 'en') {
        $name = ' exam ';
    }else {
        $name = ' examnamearabic ';
    }
    $exams = [];
    $exams = $DB->get_records_sql_menu("SELECT id, $name FROM {local_exams} WHERE 1=1 ");
    $str = get_string('enterexam', 'local_exams');
    $noselectionstring = ['noselectionstring' => $str];
    $mform->addElement('autocomplete', 'exam', get_string('exam', 'local_exams'), [], formattributes($str, 'id_exam', 'fetch_exam'));
    $mform->setType('exam', PARAM_RAW);
}
function exam_center_filter($mform){
    global $DB;
    $str = get_string('select_center', 'local_exams');
    $noselectionstring = ['noselectionstring' => $str];
    $mform->addElement('autocomplete', 'exam_center', get_string('exam_center', 'local_exams'), [], formattributes($str, 'id_exam_center', 'fetch_exam_center'));
    $mform->setType('exam_center', PARAM_RAW);
}
function dates_filter($mform){

    $mform->addElement('date_selector', 'examdatetimefrom', get_string('examdatetimefrom', 'local_exams'),array('optional'=>true));
    $mform->setType('examdatetimefrom', PARAM_RAW);

    $mform->addElement('date_selector', 'examdatetimeto', get_string('examdatetimeto', 'local_exams'),array('optional'=>true));
    $mform->setType('examdatetimeto', PARAM_RAW);
}
function certificatestatus_filter($mform){
    global $DB;
    $status = [
        '' => get_string('select_status', 'local_exams'),
        -1 => get_string('all'),
        'issued' => get_string('issued', 'local_exams'),
        'notissued' => get_string('notissued', 'local_exams'),
    ];
    $mform->addElement('select', 'certificate_status', get_string('certificate_status', 'local_exams'), $status);
    $mform->setType('idnumber', PARAM_RAW);
}
/**
 * @param $placeholder Placeholders for the dropdown
 * @param $el_id Id for the element
 * @param $action action for the element if any
 * 
 */
function formattributes($placeholder = '', $el_id = '', $actions = ''){
    return $attr = [
        'ajax' => 'local_exams/filtervalues',
        'data-action' => $actions,
        'multiple' => false,
        'id' => $el_id,
        'optional' =>true,
        'placeholder' => $placeholder,
    ];
}
