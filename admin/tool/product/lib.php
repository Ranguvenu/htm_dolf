<?php
function financialpayments_filters_form($filterparams){

    global $CFG;

    require_once($CFG->dirroot . '/local/organization/dynamicfilters_form.php');

    $filters = array(
       'product'=>array('admin/tool'=>array('org_order_trainingprogram','org_order_exam','org_order_event','org_order_paymentmode','org_order_organization','org_offering_type','org_user_idnumber','org_order_offering_status','org_order_offering_location','org_order_betweendaterange','individual_prg_evnt_exams'))
    );
    $mform = new dynamicfilters_form(null, array('filterlist'=>$filters,'filterparams' => $filterparams,'submitid' =>'viewpayments','ajaxformsubmit'=>true), 'post', '', null, true,$_REQUEST);
   return $mform;

}
function myfinancialpayments_filters_form($filterparams){

    global $CFG;

    require_once($CFG->dirroot . '/local/organization/dynamicfilters_form.php');

    $filters = array(
       'product'=>array('admin/tool'=>array('org_order_betweendaterange'))
    );
    $mform = new dynamicfilters_form(null, array('filterlist'=>$filters,'filterparams' => $filterparams,'submitid' =>'viewpayments','ajaxformsubmit'=>true), 'post', '', null, true,$_REQUEST);
   return $mform;

}
function orders_approval_filters_form($filterparams){

    global $CFG;

    require_once($CFG->dirroot . '/local/organization/dynamicfilters_form.php');

    $filters = array(
       'product'=>array('admin/tool'=>array('org_order_trainingprogram','org_order_exam','org_order_event','org_order_organization','org_order_betweendaterange'))
    );
    $mform = new dynamicfilters_form(null, array('filterlist'=>$filters,'filterparams' => $filterparams,'submitid' =>'viewpayments','ajaxformsubmit'=>true), 'post', '', null, true,$_REQUEST);
   return $mform;

}
function org_order_trainingprogram_filter($mform){
    global $DB;

    $currentlang= current_language();

    if( $currentlang == 'ar'){

        $tpname='tp.namearabic as trainingname';

    }else{

        $tpname='tp.name as trainingname';
    }


    $sql = "SELECT tp.id,$tpname  
             FROM {tp_offerings} AS tpofr  
             JOIN {local_trainingprogram} AS tp ON tpofr.trainingid=tp.id WHERE tp.published = 1 GROUP by tp.id ORDER BY tp.id DESC";         

    $trainingprograms = $DB->get_records_sql_menu($sql);
    $trainingprogramsoptions = array(
        'class' => 'dynamic_form_id_trainingprograms',
        'multiple' => true,
        'noselectionstring' => get_string('noselection', 'tool_product'),

    );


    $groupelemnts=array();
    $groupelemnts[] = $mform->createElement('html', \html_writer::tag('h4', get_string('trainingprograms', 'tool_product'),array('class' => 'dynamic_form_id_trainingprograms heading_label')));
    $trainingprogramelement =$groupelemnts[] = $mform->createElement('autocomplete','trainingprograms','',$trainingprograms, $trainingprogramsoptions);
    $mform->addGroup($groupelemnts, 'trainingprograminfo','', array(''), false);
 

}
function org_order_exam_filter($mform){
    global $DB;

    $currentlang= current_language();

    if( $currentlang == 'ar'){

        $tpname='le.examnamearabic as trainingname';

    }else{

        $tpname='le.exam as trainingname';
    }


    $sql = "SELECT le.id, $tpname  
            FROM {local_exams} le WHERE le.status = 1 GROUP by le.id ORDER BY le.id DESC";    
     

    $exams = $DB->get_records_sql_menu($sql);
    $examsoptions = array(
        'class' => 'dynamic_form_id_exams',
        'multiple' => true,
        'noselectionstring' => get_string('noselection', 'tool_product'),

    );


    $groupelemnts=array();
    $groupelemnts[] = $mform->createElement('html', \html_writer::tag('h4', get_string('exams', 'tool_product'),array('class' => 'dynamic_form_id_exams heading_label')));
    $examinfoelement =$groupelemnts[] = $mform->createElement('autocomplete','exams','',$exams, $examsoptions);
    $mform->addGroup($groupelemnts, 'examinfo','', array(''), false);
  

}
function org_order_event_filter($mform){
    global $DB;

    $currentlang= current_language();

    if( $currentlang == 'ar'){

        $tpname='evnt.titlearabic as trainingname';

    }else{

        $tpname='evnt.title as trainingname';
    }


    $sql = "SELECT evnt.id,$tpname  
             FROM {local_events} AS evnt GROUP by evnt.id ORDER BY evnt.id DESC";         

    $events = $DB->get_records_sql_menu($sql);
    $eventsoptions = array(
        'class' => 'dynamic_form_id_events',
        'multiple' => true,
        'noselectionstring' => get_string('noselection', 'tool_product'),

    );


    $groupelemnts=array();
    $groupelemnts[] = $mform->createElement('html', \html_writer::tag('h4', get_string('events', 'tool_product'),array('class' => 'dynamic_form_id_events heading_label')));
    $eventinfoelement =$groupelemnts[] = $mform->createElement('autocomplete','events','',$events, $eventsoptions);
    $mform->addGroup($groupelemnts, 'eventinfo','', array(''), false);


}
function org_order_organization_filter($mform){
    global $DB;

    $currentlang= current_language();

    if( $currentlang == 'ar'){

        $tpname='org.fullnameinarabic';

    }else{

        $tpname='org.fullname';
    }


    $sql = "SELECT org.id,$tpname  
                 FROM  {local_organization} AS org WHERE org.status = 2
                 GROUP by org.id ORDER BY org.id DESC";

    $organizations = $DB->get_records_sql_menu($sql);
    $organizationsoptions = array(
        'class' => 'dynamic_form_id_organization',
        'multiple' => true,
        'noselectionstring' => get_string('noselection', 'tool_product'),

    );

    $groupelemnts=array();
    $groupelemnts[] = $mform->createElement('html', \html_writer::tag('h4', get_string('organization', 'tool_product'),array('class' => 'dynamic_form_id_organization heading_label')));

    $organizationelement =$groupelemnts[] = $mform->createElement('autocomplete','organization','',$organizations, $organizationsoptions);

    $mform->addGroup($groupelemnts, 'organizationinfo','', array(''), false);
    
 

}
function org_order_paymentmode_filter($mform){
    global $DB;


    $paymentmodes = array('prepaid'=>get_string('prepaid','tool_product'),'postpaid'=>get_string('postpaid','tool_product'),'telr'=>get_string('telr','tool_product'),);
    $paymentmodesoptions = array(
        'class' => 'dynamic_form_id_paymentmode',
        'multiple' => true,
        'noselectionstring' => get_string('noselection', 'tool_product'),

    );

    $groupelemnts=array();
    $groupelemnts[] = $mform->createElement('html', \html_writer::tag('h4', get_string('paymentmode', 'tool_product'),array('class' => 'dynamic_form_id_paymentmode heading_label')));

    $paymentmodeelement =$groupelemnts[] = $mform->createElement('autocomplete','paymentmode','',$paymentmodes, $paymentmodesoptions);
    $mform->addGroup($groupelemnts, 'paymentmodeinfo','', array(''), false);
  

}
function org_order_betweendaterange_filter($mform){
    global $DB;


    $groupelemnts=array();
    $groupelemnts[] = $mform->createElement('html', \html_writer::tag('h4', get_string('betweendaterangefrom', 'tool_product'),array('class' => 'dynamic_form_id_betweendaterangefrom heading_label')));

    $groupelemnts[] = $mform->createElement('date_selector','betweendaterangefrom','', ['optional'=>true,'class' => 'dynamic_form_id_betweendaterangefrom']);

    $mform->addGroup($groupelemnts, 'betweendaterangefrominfo','', array(''), false);

    $groupelemnts=array();

    $groupelemnts[] = $mform->createElement('html', \html_writer::tag('h4', get_string('betweendaterangeto', 'tool_product'),array('class' => 'dynamic_form_id_betweendaterangeto heading_label')));

    $groupelemnts[] = $mform->createElement('date_selector','betweendaterangeto','', ['optional'=>true,'class' => 'dynamic_form_id_betweendaterangeto']);

    $mform->addGroup($groupelemnts, 'betweendaterangetoinfo','', array(''), false);

}

function org_order_offering_status_filter($mform){
    global $DB;

    $current_lang = current_language();

    $status = [];
    $status['completed'] = get_string('completed','local_trainingprogram');
    $status['financially_closed'] = get_string('financially_closed','local_trainingprogram');

    $offeringstatusoptions = array(
        'class' => 'dynamic_form_id_offeringstatus',
        'multiple' => true,
        'noselectionstring' => get_string('noselection', 'tool_product'),

    );


    $groupelemnts=array();
    $groupelemnts[] = $mform->createElement('html', \html_writer::tag('h4', get_string('offering_status', 'local_trainingprogram'),array('class' => 'dynamic_form_id_offeringstatus heading_label')));

    $offeringstatuselement =$groupelemnts[] = $mform->createElement('autocomplete','offeringstatus','',$status,$offeringstatusoptions);
    $mform->addGroup($groupelemnts, 'offeringstatusinfo','', array(''), false);

}

function org_offering_type_filter($mform){
    global $DB;

    $current_lang = current_language();

    $type = [];
    $type['online'] = get_string('online','local_trainingprogram');
    $type['offline'] = get_string('offline','local_trainingprogram');
    $type['elearning'] = get_string('elearning','local_trainingprogram');


    $offeringtypeoptions = array(
        'class' => 'dynamic_form_id_offeringtype',
        'multiple' => true,
        'noselectionstring' => get_string('noselection', 'tool_product'),

    );

    $groupelemnts=array();
    $groupelemnts[] = $mform->createElement('html', \html_writer::tag('h4', get_string('offering_type', 'local_trainingprogram'),array('class' => 'dynamic_form_id_offeringtype heading_label')));

    $offeringtypeelement =$groupelemnts[] = $mform->createElement('autocomplete','offeringtype','',$type,$offeringtypeoptions);
    $mform->addGroup($groupelemnts, 'offeringtypeinfo','', array(''), false);

}
function org_order_offering_location_filter($mform){
    global $DB;

    $current_lang = current_language();

    $location = [];
    $location[1] = get_string('inside','local_trainingprogram');
    $location[2] = get_string('outside','local_trainingprogram');
    $location[3] = get_string('clientside','local_trainingprogram');


    $offeringlocationoptions = array(
        'class' => 'dynamic_form_id_offeringlocation',
        'multiple' => true,
        'noselectionstring' => get_string('noselection', 'tool_product'),

    );

    $groupelemnts=array();
    $groupelemnts[] = $mform->createElement('html', \html_writer::tag('h4', get_string('offeringlocation', 'local_trainingprogram'),array('class' => 'dynamic_form_id_offeringlocation heading_label')));

    $offeringlocationeelement =$groupelemnts[] = $mform->createElement('autocomplete','offeringlocation','',$location,$offeringlocationoptions);
    $mform->addGroup($groupelemnts, 'offeringlocationinfo','', array(''), false);

}
function org_user_idnumber_filter($mform){
    global $DB;
    $systemcontext = context_system::instance();
    $useridnumbersoptions = array(
        'ajax' => 'local_organization/organization_datasource',
        'data-type' => 'usersidnumber',
        'id' => 'usersidnumber',
        'data-orgid' => 0,
        'multiple'=>true,
    );
    $groupelemnts=array();
    $groupelemnts[] = $mform->createElement('html', \html_writer::tag('h4', get_string('useridnumber', 'tool_product'),array('class' => 'dynamic_form_id_useridnumber heading_label')));

    $useridnumbersnelement =$groupelemnts[] = $mform->createElement('autocomplete','useridnumber','',[], $useridnumbersoptions);
    $mform->addGroup($groupelemnts, 'useridnumberinfo','', array(''), false);

}
function individual_prg_evnt_exams_filter($mform){
    global $DB;


    $prgevntexams = array(''=>get_string('select','tool_product'),'1'=>get_string('programsonly','tool_product'),'2'=>get_string('eventsonly','tool_product'),'3'=>get_string('examsonly','tool_product'),);

    $groupelemnts=array();
    $groupelemnts[] = $mform->createElement('html', \html_writer::tag('h4', get_string('pee', 'tool_product'),array('class' => 'dynamic_form_id_paymentmode heading_label')));

    $paymentmodeelement =$groupelemnts[] = $mform->createElement('select','prgevntexams','',$prgevntexams);
    $mform->addGroup($groupelemnts, 'prgevntexams','', array(''), false);
  

}
