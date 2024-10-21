<?php
function local_organization_leftmenunode(){
    $systemcontext = context_system::instance();
    $referralcode = '';
   if(is_siteadmin() || has_capability('local/organization:manage_communication_officer',$systemcontext) || has_capability('local/organization:assessment_operator_view',$systemcontext)){
        $referralcode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_referralcode', 'class'=>'pull-left user_nav_div referralcode'));
        $referral_url = new moodle_url('/local/organization/index.php');
        $referral_label = get_string('orgname','local_organization');

        $referral = html_writer::link($referral_url, '<span class="organizations_icon side_menu_img_icon"></span><span class="user_navigation_link_text">'.$referral_label.'</span>',
            array('class'=>'user_navigation_link'));
        $referralcode .= $referral;
        $referralcode .= html_writer::end_tag('li');
    }
    return array('7' => $referralcode);
}

function orgemail_filter($mform,$query='',$searchanywhere=false,$orgid=0, $page=0, $perpage=25){
    global $DB,$USER,$PAGE;

    $org_array = $PAGE->url->params();
    $org_object = (object) $org_array;
    $orgid = (int)$org_object->orgid;
    $systemcontext = context_system::instance();
    $userattributes = array(
        'ajax' => 'local_organization/organization_datasource',
        'data-type' => 'usersemail',
        'id' => 'usersemail',
        'data-org' => $orgid,
        'multiple'=>true,
    );

    $data = data_submitted();
    $email= implode(',',$data->email);
    if($email) {
       $selectedmailrecords = $DB->get_records_sql_menu('SELECT id,email FROM {local_users} WHERE id IN('.$email.')');
    }

    $mform->addElement('autocomplete', 'email', get_string('email', 'local_userapproval'),$selectedmailrecords, $userattributes);
}
function approvalletter_url($itemid = 0) {
    $context = context_system::instance();
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'auth_registration', 'approval_letter', $itemid);
    foreach($files as $file){
        $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(),$file->get_itemid(), $file->get_filepath(), $file->get_filename(), false);
    }
    return $url->out();
}
function jobrole_filter($mform){
    
    $jrattributes = array(
            'ajax' => 'local_trainingprogram/sector_datasource',
            'data-type' => 'jobrole',
            'id' => 'el_jobrolescmp',
            'data-jobfamilyid' => ''
        );

    $mform->addElement('autocomplete', 'jobrole', get_string('jobrole', 'local_organization'),array(), $jrattributes);
}

function segment_filter($mform){
    
    $segmentdattributes = array(
            'ajax' => 'local_trainingprogram/sector_datasource',
            'data-type' => 'segment',
            'id' => 'el_segmentlistcmp',
            'data-sectorid' => ''
        );

    $mform->addElement('autocomplete', 'segment', get_string('segment', 'local_organization'),array(), $segmentdattributes);
}
function jobfamily_filter($mform){
    
    $jfdattributes = array(
            'ajax' => 'local_trainingprogram/sector_datasource',
            'data-type' => 'jobfamily',
            'id' => 'el_jobfamilycmp',
            'data-segmentid' => ''
        );

    $mform->addElement('autocomplete', 'jobfamily', get_string('jobfamily', 'local_organization'),array(), $jfdattributes);
}

function local_organization_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
if ($filearea) {
    $fs = get_file_storage();
    $filename = $args[1];
    $itemid = $args[0];
    if ($file = $fs->get_file($context->id, 'local_organization', $filearea, $itemid, $filepath = '/', $filename)) {

        send_stored_file($file, 0, 0, $forcedownload);
    } else {
        send_file_not_found();
    }
}
}

function logo_path($itemid = 0) {
    global $DB;
    $context = context_system::instance();
    if ($itemid > 0) {
        $sql = "SELECT * FROM {files} WHERE itemid = :orglogo AND component = 'local_organization' AND filearea = 'orglogo' AND filename != '.' ORDER BY id DESC";
        $orglogo = $DB->get_record_sql($sql, array('orglogo' => $itemid), 1);

    }

    if (!empty($orglogo)) {
    $logourl = moodle_url::make_pluginfile_url($orglogo->contextid, $orglogo->component, $orglogo->filearea, $orglogo->itemid, $orglogo->filepath, $orglogo->filename);

    $logourl = $logourl->out();

    } else {

        return false;

    }
   return $logourl;
}

function partnerlogo_url($itemid = 0) {
    global $DB;
    $context = context_system::instance();

    if ($itemid > 0) {
        $sql = "SELECT * FROM {files} WHERE itemid = :logo AND filearea='partnerimage' AND filename != '.' ORDER BY id DESC";
        $partnerimage = $DB->get_record_sql($sql,array('logo' => $itemid),1);
    }

    if (!empty($partnerimage)) {
        $logourl = moodle_url::make_pluginfile_url($partnerimage->contextid, $partnerimage->component,
       $partnerimage->filearea, $partnerimage->itemid, $partnerimage->filepath,
       $partnerimage->filename);
        $logourl = $logourl->out();
    }
    return $logourl;
}
function  organization_invoice_filters_form($filterparams){
    global $CFG;
    require_once($CFG->dirroot . '/local/organization/dynamicfilters_form.php');
    $filters = array(
        'organization'=>array('local'=>array('orgofficial','learningtype', 'invoicestatus','paymentstatus','fromtodate')),
    );
    $mform = new dynamicfilters_form(null, array('filterlist'=>$filters,'filterparams' => $filterparams,'submitid' =>'viewinvoices','ajaxformsubmit'=>true), 'post', '', null, true,$_REQUEST);
    return $mform;


} 
function orgofficial_filter($mform){
    $orgofficialslist = (new local_organization\organization)->getsystemlevel_role_users('organizationofficial',0);

    $orgofficials=[];
    foreach ($orgofficialslist AS $orgoff){ 
        $orgofficials[0] = get_string('filterselect','local_organization');
        $orgofficials[$orgoff->id] = $orgoff->firstname . ' '.$orgoff->lastname;
    }

    $mform->addElement('autocomplete','orgoff_name', get_string('orgofficial', 'local_organization'), $orgofficials,
                     ['class' => 'el_programlist', 
                     'noselectionstring' =>get_string('filterselect','local_organization'),
                 ]);
     
}
function learningtype_filter($mform){
    $learningtype = array(
    1 => get_string('trainingprogram', 'local_organization'),
    2 => get_string('exam', 'local_organization'),
    3 => get_string('events', 'local_organization'),
 );

$select = array(null => get_string('filterselect','local_organization'));
$learningtypelist = $select + $learningtype;
$mform->addElement('select',  'learningtype', get_string('learningtype','local_organization'), $learningtypelist);
}
function invoicestatus_filter($mform){
    $invoicestatus = array(
    -1 => get_string('inactive', 'local_organization'),
     1 =>  get_string('active', 'local_organization')

     );
    
    $select = array(null => get_string('filterselect','local_organization'));
    $invoicestatuslist = $select + $invoicestatus;
    $mform->addElement('select',  'invoicestatus', get_string('invoicestatus','local_organization'),  $invoicestatuslist);


}
function paymentstatus_filter($mform){
    $paymentstatus = array(
       0 => get_string('approvedin', 'local_organization') ,
       1 => get_string('paidin', 'local_organization'),
       2 => get_string('due', 'local_organization')

    
     );
    
    $select = array(null => get_string('filterselect','local_organization'));
    $paymentstatuslist = $select +  $paymentstatus;
    $mform->addElement('select',  'paymentstatus', get_string('paymentstatus','local_organization'), $paymentstatuslist,['class'=>'form-group row fitem ']);

    // $orderstatus = array(
    //     1 => get_string('approved', 'local_organization') ,
    //     0 => get_string('due', 'local_organization')
 
     
    //   );
     
    //  $select = array(null => get_string('filterselect','local_organization'));
    //  $orderstatuslist = $select +   $orderstatus;
    //  $mform->addElement('select',  'orderstatus', get_string('orderstatus','local_organization'),  $orderstatuslist,['class'=>'form-group row fitem ']);
    //  $mform->hideif('orderstatus', 'paymentstatus', 'eq', 1);
    //  $mform->hideif('orderstatus', 'paymentstatus', 'eq', null);

 


}
function fromtodate_filter($mform){

    $mform->addElement('date_selector', 'fromdate', get_string('fromdate','local_events'),array('optional'=>true));
    $mform->setType('startdate', PARAM_RAW);
    $mform->addElement('date_selector', 'todate', get_string('todate','local_events'),array('optional'=>true));
    $mform->setType('enddate', PARAM_RAW);

}
function  organization_traingingpartner_filters_form($filterparams){
    global $CFG;
    require_once($CFG->dirroot . '/local/organization/dynamicfilters_form.php');
    $filters = array(
        'organization'=>array('local'=>array('trainingpartner','partnertype','sector')),
    );
    $mform = new dynamicfilters_form(null, array('filterlist'=>$filters,'filterparams' => $filterparams,'submitid' =>'vieworganizations','ajaxformsubmit'=>true), 'post', '', null, true,$_REQUEST);
    return $mform;
} 
function trainingpartner_filter($mform){
    $partnerfromgroup=array();
    $partnerfromgroup[] =& $mform->createElement('radio', 'partner', '',get_string('yes', 'local_organization'), 1);
    $partnerfromgroup[] =& $mform->createElement('radio', 'partner', '',get_string('no', 'local_organization'), 2);
    $mform->addGroup($partnerfromgroup, 'trainingpartner', get_string('partner', 'local_organization'), '&nbsp&nbsp', false);
}
function partnertype_filter($mform){
    global $DB;

    $current_lang = current_language();


    if($current_lang == 'ar') {
        $partnerslist=$DB->get_records_sql('SELECT id,arabicname AS fullname FROM {local_org_partnertypes} ORDER BY ID DESC ');

    } else {

        $partnerslist=$DB->get_records_sql('SELECT id,name AS fullname FROM {local_org_partnertypes} ORDER BY ID DESC');

    }
    $partners=[];
    foreach ($partnerslist AS $partner){ 
        $partners[$partner->id] = $partner->fullname;
    }
    $partnerelement =$mform->addElement('autocomplete','partnertype', get_string('type', 'local_organization'),$partners,
                     ['class' => 'el_programlist', 
                     'noselectionstring' =>'',
                 ]);
    $partnerelement->setMultiple(true);

}

function sector_filter($mform){
    global $DB;

    $current_lang = current_language();


    if($current_lang == 'ar') {
        $sectorslist=$DB->get_records_sql('SELECT id,titlearabic AS fullname FROM {local_sector} ');

    } else {

        $sectorslist=$DB->get_records_sql('SELECT id,title AS fullname FROM {local_sector}');

    }

    $sectors=[];
    foreach ($sectorslist AS $sector){ 
        $sectors[$sector->id] = $sector->fullname;
    }

    $sectorelement =$mform->addElement('autocomplete','sector', get_string('sector', 'local_organization'),$sectors,
                     ['class' => 'el_sectorlist', 
                     'noselectionstring' =>'',
                     'placeholder' => get_string('sector' , 'local_organization'),
                 ]);
    $sectorelement->setMultiple(true);
}

