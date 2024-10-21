<?php
namespace local_trainingprogram\output;

// use plugin_renderer_base;
/**
 * Defines the version of Training program
 *
 * @package    local_trainingprogram
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
require_once($CFG->dirroot . '/local/trainingprogram/lib.php');
use local_trainingprogram\local\trainingprogram as tp;
/**
 * Renderer class for Training program
 */
class renderer extends plugin_renderer_base
{

    function render_trainingprogram($page)
    {
        $data = $page->export_for_template($this);                                                                                  
        return parent::render_from_template('local_trainingprogram/mainpage', $data);         
    }

    public function  get_catalog_trainingprograms($filter = false) {
           global $DB, $PAGE, $OUTPUT, $CFG, $USER;
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'program_wrapper','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        //$data = [];
        //$data['actionview'] = ($PAGE->pagelayout == 'base') ? true : false ;*/
        $options['methodName']='local_trainingprogram_viewprograms';
        $options['templateName']='local_trainingprogram/training_programs';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('type'=>'web'));
        //$dataoptions = json_encode($data);
        $context = [
                'targetID' => 'program_wrapper',
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
        return $this->render_from_template('theme_academy/global_filter', $filterparams);;
    }

    public function listofenrolledusers($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        echo $this->render_from_template('local_trainingprogram/listofenrolledprogramusers', $filterparams);
    }
    public function programenrollment($from_userstotal, $from_users, $to_userstotal, $to_users, $myJSON, $programid, $roleid, $offeringid,$availableseats,$programprice, $courseid) {
        global $DB, $PAGE, $OUTPUT, $CFG, $USER;

        
        $systemcontext = context_system::instance();

        $offering=$DB->get_record('tp_offerings',array('id'=>$offeringid));
        $offeringstartdate = date('Y-m-d',$offering->startdate);
        $offeringenddate = date('Y-m-d',$offering->enddate);
        $currdate=date('Y-m-d');
        $offeringstarttime = gmdate("H:i",$offering->time);
        $offeringendtime = gmdate("H:i",$offering->endtime);
        $currenttime = date('H:i');
        $offeringtype = (int)$DB->get_field('tp_offerings','type',array('id'=>$offeringid));
        $offeringorganization = (int)$DB->get_field('tp_offerings','organization',array('id'=>$offeringid));
        $lang = current_language();
        if($lang == 'ar'){
            $offeringorganizationname = $DB->get_field_sql("SELECT org.fullnameinarabic FROM {local_organization} AS org JOIN {tp_offerings} AS tpo ON tpo.organization = org.id WHERE tpo.id = $offeringid");
        }else{
            $offeringorganizationname = $DB->get_field_sql("SELECT org.fullname FROM {local_organization} AS org JOIN {tp_offerings} AS tpo ON tpo.organization = org.id WHERE tpo.id = $offeringid");            
        }
        $privateofferingmessage = (is_siteadmin() && $offeringtype == 1 && $offeringorganization > 0) ? true : false ;
        $nonprivateofferingmessage = (is_siteadmin() && $offeringtype != 1 ) ? true : false ;
        $current_date =date('Y-m-d');
        //$enddate =  date('Y-m-d', strtotime($offeringenddate. ' +30 days'));
        $last_date = date("Y-m-t", strtotime($offeringenddate));

        $timestamp = strtotime(date('Y-m-d H:i'));
       
        if(!is_siteadmin() && !has_capability('local/organization:manage_trainingofficial',$systemcontext) && !has_capability('local/organization:manage_communication_officer',$systemcontext)) {
            $programenrollaction = (($offering->trainingmethod == 'elearning'||  (($offering->trainingmethod !='elearning' && $offeringstartdate > $currdate) || ( $offering->trainingmethod !='elearning' && $offeringstartdate  ==  $currdate &&  $offering->time > 0 &&  $offeringstarttime >= $currenttime)))) ? true : false ;
        } else {

            if($offering->trainingmethod != 'elearning') {
                if($offeringtype==0) {
                    if($timestamp <= ($offering->enddate+$offering->endtime)){

                        $programenrollaction = true ;
                    }
                } else {
                    if($current_date<=$last_date){

                        $programenrollaction = true ;
                    }
                }
            } else {
                $programenrollaction = true ;
            }


        }
        
       
        $orgofficialseatsview = (!is_siteadmin() &&  has_capability('local/organization:manage_organizationofficial',$systemcontext)) ? true : false;

        $currentofferingbookings=$DB->get_records_sql("SELECT * FROM {tool_org_order_seats} WHERE tablename= 'tp_offerings' AND fieldname= 'id' AND fieldid= $offeringid");
        $totalseatssql = " SELECT  tp.availableseats AS total
                             FROM {tp_offerings} tp
                            WHERE tp.id = $offering->id";
        $total = $DB->get_field_sql($totalseatssql);

        $offeringavailableseats = (new tp)->get_available_seats($offering->id);
        //$offeringavailableseats=100;
        $offeringavailableseatsview = ($offeringavailableseats > 0) ? true : false;
        if(is_siteadmin() || has_capability('local/organization:manage_trainingofficial',$systemcontext)){
            $totalpurchasedseats = (int) $DB->get_field_sql("SELECT SUM(purchasedseats) FROM {tool_org_order_seats} WHERE tablename = 'tp_offerings' AND fieldname = 'id' AND fieldid = $offeringid");
        } elseif(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {

            $totalpurchasedseats = (int) $DB->get_field_sql("SELECT purchasedseats FROM {tool_org_order_seats} WHERE tablename = 'tp_offerings' AND fieldname = 'id' AND fieldid = $offeringid AND orguserid = $USER->id");
        }


        foreach ($currentofferingbookings AS $currentofferingbooking) {
            $offeringorganization_name= $DB->get_field_sql("SELECT org.fullname FROM {local_organization} AS org JOIN {local_users} AS lou ON lou.organization = org.id WHERE lou.userid = $currentofferingbooking->orguserid");
            $currentofferingbooking->purchasedseats =$currentofferingbooking->purchasedseats;
            $currentofferingbooking->availableseats =$currentofferingbooking->availableseats;
            $currentofferingbooking->orgname = $offeringorganization_name ? $offeringorganization_name :'Organization not-set';
        } 

       

        if(is_siteadmin() || has_capability('local/organization:manage_communication_officer', $systemcontext) || has_capability('local/organization:manage_trainingofficial', $systemcontext)) {
            if($offering->cancelled == 2) {
                $bulkenrollment =false; 
            } else {
                if($offering->trainingmethod == 'elearning') {
                    $bulkenrollment =true;
                } else {
                    if($offeringtype == 0){
                        if($timestamp <= ($offering->enddate+$offering->endtime)){
                            $bulkenrollment = true ;
                        }
                    } else{
                        if($current_date<=$last_date ){
                            $bulkenrollment = true ;
                        }
                    }
                }
            }
        } else if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $systemcontext)){
            $bulkenrollment = (($offering->trainingmethod == 'elearning'||  (($offering->trainingmethod !='elearning' && $offeringstartdate > $currdate) || ( $offering->trainingmethod !='elearning' && $offeringstartdate  ==  $currdate &&  $offering->time > 0 &&  $offeringstarttime >= $currenttime)))) ? true : false ;
        } else {
            $bulkenrollment = false;
        }
        $templatedata = array('from_userstotal' => $from_userstotal, 
                              'from_users' => $from_users, 
                              'to_userstotal' => $to_userstotal, 
                              'to_users' => $to_users, 
                              'myJSON' => $myJSON, 
                              'programid' => $programid, 
                              'roleid' => $roleid, 
                              'offeringid' => $offeringid,
                              'offeringavailableseats'=>$offeringavailableseats,
                              'offeringavailableseatsview'=>$offeringavailableseatsview,
                              'programprice'=>$programprice,
                              'programenrollaction'=>$programenrollaction,
                              'offeringorganizationname'=>$offeringorganizationname,
                              'privateofferingmessage'=>$privateofferingmessage,
                              'totalofferingbookings'=>$totalofferingbookings,
                              'nonprivateofferingmessage'=>$nonprivateofferingmessage,
                              'totalpurchasedseats'=>$totalpurchasedseats,
                              'totalavailableseats'=>$availableseats,
                              'orgofficialseatsview' => $orgofficialseatsview, 
                              'courseid' => $courseid,
                              'bulkenrollment'=>$bulkenrollment
                            );
        $totalofferingbookings=array_values($currentofferingbookings);
        return $this->render_from_template('local_trainingprogram/programenrollment', $templatedata);        
    } 
    public function programsectors_view($data) {
        global $DB, $PAGE, $OUTPUT, $CFG;
        $sectors=$data->sectors;
        
        if($data->newjobfamilyoption && $data->targetgroup != '0' && $data->targetgroup != '-1' && $data->targetgroup != null) {
            $targetgroup = (new \local_trainingprogram\local\trainingprogram)->get_all_job_families_by_sectors($data->newjobfamilyoption,true);
            $jobfamilies = $data->targetgroup.','.implode(',', $targetgroup);
        }  else if($data->newjobfamilyoption) {
            $targetgroup = (new \local_trainingprogram\local\trainingprogram)->get_all_job_families_by_sectors($data->newjobfamilyoption,true);
            $jobfamilies = implode(',', $targetgroup);
        } else {
            $jobfamilies=$data->targetgroup;
        }
      
        list($sectorssql,$sectorsparams) = $DB->get_in_or_equal(explode(',',$sectors));
        $lang= current_language();
        
        $querysql = "SELECT id AS sectorid, title,titlearabic FROM {local_sector} WHERE id $sectorssql";
        $sectorslist= $DB->get_records_sql($querysql,$sectorsparams);
        foreach ($sectorslist AS $sector) {
            if( $lang == 'ar'){
                $sector->title = $sector->titlearabic;
            }else{
                $sector->title =  $sector->title;
            }
           $currentlang= current_language();
           if( $currentlang == 'ar'){


               if($jobfamilies == '-1') {

                $segmentdata = $DB->get_fieldset_sql('select id from {local_segment} where sectorid in ('.$sector->sectorid.')');
                if(COUNT($segmentdata) > 0) {
                 $segmentlist = implode(',', $segmentdata);
                 $jobfamilysql='select id AS jobfamilyid,familynamearabic AS familyname
                       ,code,segmentid from {local_jobfamily} where segmentid in('.$segmentlist.',0)';
                } else {
                    continue;
                }

              } else {


                $jobfamilysql = "select id AS jobfamilyid,familynamearabic AS familyname
                  ,code,segmentid from {local_jobfamily} where  FIND_IN_SET(id,'$jobfamilies') AND (segmentid IN (SELECT id FROM {local_segment} WHERE sectorid = $sector->sectorid) OR segmentid = 0)";
              }


           } else {

              if($jobfamilies == '-1') {

                $segmentdata = $DB->get_fieldset_sql('select id from {local_segment} where sectorid in ('.$sector->sectorid.')');
                if(COUNT($segmentdata) > 0) {
                 $segmentlist = implode(',', $segmentdata);
                 $jobfamilysql='select id AS jobfamilyid,familyname AS familyname
                       ,code,segmentid from {local_jobfamily} where segmentid in('.$segmentlist.',0)';
                } else {
                    continue;
                }


              } else {


                $jobfamilysql = "select id AS jobfamilyid,familyname AS familyname
                 ,code,segmentid from {local_jobfamily} where  FIND_IN_SET(id,'$jobfamilies') AND (segmentid IN (SELECT id FROM {local_segment} WHERE sectorid = $sector->sectorid) OR segmentid = 0)";
              }

           }

           $jobfamilylist = $DB->get_records_sql($jobfamilysql);
            foreach ($jobfamilylist AS $jobfamily) {

                if($jobfamily->segmentid == 0) {

                  $jobfamily->jobfamilyfullname =$jobfamily->familyname .' '.get_string('commontext','local_sector'); 
                } else {
                    $jobfamily->jobfamilyfullname =$jobfamily->familyname; 
                }

            }

            
            
            $sector->jobfamilies=array_values($jobfamilylist);
        }
        $viewdata=[
        'sectorslist'=>array_values($sectorslist),
        'programid'=>$data->id,
        ];
        $result = $this->render_from_template('local_trainingprogram/viewprogramsectors', $viewdata);
        return $result;
    }
    public function get_trainingprograms_cardview($filter = false, $searchquery='', $sector='') {
        $systemcontext = context_system::instance();

        $options = array('targetID' => 'program_container','perPage' => 6, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_trainingprogram_viewtprograms';
        $options['templateName']='local_trainingprogram/program_card';
        $options = json_encode($options);
        $filterdata = json_encode(['sectors'=>$sector,'q' => $searchquery]);
        $dataoptions = json_encode(array('contextid' => $systemcontext->id, 'append' => 1));
        $context = [
                'targetID' => 'program_container',
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

    public function listofcardviewprograms($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        echo $this->render_from_template('local_trainingprogram/training_programs_list', $filterparams);
    }

    public function get_catalog_program_enrolled_users($filter = false) {
        $systemcontext = context_system::instance();
        $programid = optional_param('programid', 0 , PARAM_INT);
        $selectedroleid = optional_param('selectedroleid', 0 , PARAM_INT);
        $offeringid = optional_param('offeringid', 0 , PARAM_INT);
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'manage_enrolled_users','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_trainingprogram_viewprogramenrolledusers';
        $options['templateName']='local_trainingprogram/viewprogramenrolledusers';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id, 'programid' => $programid, 'selectedroleid' => $selectedroleid, 'offeringid' => $offeringid));
        $context = [
                'targetID' => 'manage_enrolled_users',
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

    public function other_programs_course_view($filter = false) {
        $systemcontext = context_system::instance();
        $programid = optional_param('programid', 0 , PARAM_INT);
        $action = optional_param('action', 'booknow' , PARAM_INT);
        $options = array('targetID' => 'manage_other_programs','perPage' => 10, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_trainingprogram_other_programs_course_view';
        $options['templateName']='local_trainingprogram/others_program_course_overview';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id,'programid' => $programid, 'action' => $action));
        $context = [
                'targetID' => 'manage_other_programs',
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

    public function listofothers_program_course_view($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        echo $this->render_from_template('local_trainingprogram/listofothers_program_course_view', $filterparams);
    }

    // public function get_card_view_trainingprograms_list($stable, $filterdata, $searchquery){
    //     global $DB,$CFG, $USER;



    //     $sql = "SELECT lo.id, lo.name,lo.image,lo.description,lo.sectors, lo.sellingprice,lo.duration, lo.hour
    //               FROM {local_trainingprogram} lo  
    //              WHERE lo.published=1 AND lo.id NOT IN (SELECT programid FROM {program_completions} WHERE  completion_status = 1) AND date(FROM_UNIXTIME(lo.availableto)) >= CURDATE() "; 

    //     if($filterdata->sectors){

    //         $sectorid = $DB->get_field('local_sector', 'id', ['code' => $filterdata->sector]);
    //         if($sectorid){
    //             $sql .=' AND FIND_IN_SET('.$sectorid.', lo.sectors)';
    //         }else{
    //             $othersectors = $DB->get_fieldset_sql('select id from {local_sector} where  code not in("finance", "capitalmarket", "banking", "insurance")');
    //             if(empty($othersectors)){
    //                 return [];
    //             }
    //             $sectorlist = implode(',', $othersectors);
    //             $sql .=' AND  lo.sectors not in('.$sectorlist.')';
    //         }
            
    //     }
    //     $programs = $DB->get_records_sql($sql, array(), $stable->start,$stable->length);
    //     $programdata = [];
    //     foreach($programs as $program){
    //       $banking = false;
    //       $capitalmarket = false;
    //       $finance = false;
    //       $insurance = false;
    //         if (!empty($program->image)) {
    //             $programimageurl =trainingprogramlogo_url($program->image);
    //         }else{
    //             $programimageurl ='';
    //         }
    //         if($program->sectors){
    //             $programsectors = $DB->get_fieldset_sql('select code from {local_sector} where id in('.$program->sectors.')');
    //             $listedsectors = ['capitalmarket', 'finance', 'insurance', 'banking'];
    //             foreach($listedsectors as $sector){
    //                 if(in_array($sector, $programsectors)){
    //                     ${$sector} = true;
    //                 }
    //             }
    //         }else{
    //             $programsectors = '';
    //         }
    //         $programdata[] = ['id' => $program->id,
    //                           'title' => $program->name,
    //                           'description' => substr($program->description,0, 200),
    //                           'sellingprice' => $program->sellingprice,
    //                           'imageurl'=> $programimageurl,
    //                           'capitalmarket' => $capitalmarket,
    //                           'finance' => $finance,
    //                           'insurance' => $insurance,
    //                           'banking' => $banking,
    //                           'hours' =>round($program->hour / 86400),
    //                           'durationinmonths' => round(($programdata->duration / 86400)/30),
    //                           'programdetailsurl' => $CFG->wwwroot.'/local/trainingprogram/programcourseoverview.php?programid='.$program->id,
    //                       ];
    //     }
    //     return $programdata;

    // }

    public function generateCouponCode($length) {
        $code = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ@#$&';
        $codeLength = strlen($code);
        $couponCode = '';
        for ($i = 0; $i < $length; $i++) {
            $couponCode .= $code[rand(0, $codeLength - 1)];
        }
        return $couponCode;
    }
    public function listofearlyregistrations($filterparams) {
        global $DB, $PAGE, $OUTPUT,$CFG;
        $systemcontext = context_system::instance();
        $filterparams['createearlyregistrationaction'] = (is_siteadmin() || has_capability('local/trainingprogram:manage_early_registrations',$systemcontext)) ? true : false;
       
        echo $this->render_from_template('local_trainingprogram/listofearlyregistrations', $filterparams);
    }

     public function get_trainingtopics($filter = false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'viewetrainingtopicsdata','perPage' => 25, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_trainingprogram_viewetrainingtopicsdata';
        $options['templateName']='local_trainingprogram/viewetrainingtopicsdata';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'viewetrainingtopicsdata',
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

    public function listoftrainingtopics($filterparams) {
        global $DB, $PAGE, $OUTPUT,$CFG;
        $systemcontext = context_system::instance();
        $filterparams['createtrainingtopics'] = (is_siteadmin() || has_capability('local/organization:manage_trainingofficial',$systemcontext)) ? true : false;
        echo $this->render_from_template('local_trainingprogram/listoftrainingtopics', $filterparams);
    }

    public function get_refundsettings($filter = false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'viewerefundsettingsdata','perPage' => 10, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_trainingprogram_viewrefundsettings';
        $options['templateName']='local_trainingprogram/viewrefundsettings';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'viewerefundsettingsdata',
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
    public  function listofrefundsettings($filterparams) {
        global $DB, $PAGE, $OUTPUT,$CFG;

        $systemcontext = context_system::instance();
        $filterparams['createsetting'] = (is_siteadmin() || has_capability('local/trainingprogram:manage_refundsettings',$systemcontext)) ? true : false;
        echo $this->render_from_template('local_trainingprogram/listofrefundsettings', $filterparams);
    }

    public function get_entitycancellationrequests($filter = false) {
        $systemcontext = context_system::instance();
        $entitytype = optional_param('entitytype','',PARAM_RAW);
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'manage_cellationrequests','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_trainingprogram_entitycancellationrequests';
        $options['templateName']='local_trainingprogram/entitycancellationrequests';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id, 'entitytype' => $entitytype));
        $context = [
                'targetID' => 'manage_cellationrequests',
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

    public function listofcellationrequests($filterparams) {
        global $DB, $PAGE, $OUTPUT,$CFG;
        $systemcontext = context_system::instance();
        echo $this->render_from_template('local_trainingprogram/listofcellationrequests', $filterparams);
    }

    public function get_enrollprogram($filter = false) {
        $systemcontext = context_system::instance();
        $programid     = optional_param('programid',0, PARAM_INT);
        $offeringid     = optional_param('offeringid',0, PARAM_INT);
        $roleid     = optional_param('roleid',0,PARAM_INT);
        $cusers     = optional_param('cusers',0,PARAM_RAW);
        $organization     = optional_param('organization',0,PARAM_INT);
        $orgofficial     = optional_param('orgofficial',0,PARAM_INT);
        $options = array('targetID' => 'enrollprogramusers','perPage' => 25, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_trainingprogram_programenrollmentsviewdata';
        $options['templateName']='local_trainingprogram/enrollprogramusers';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id,'programid'=>$programid,'offeringid'=>$offeringid,'roleid'=>$roleid,'cusers'=>$cusers,'organization'=>$organization,'orgofficial'=>$orgofficial));
        $context = [
                'targetID' => 'enrollprogramusers',
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

       public function listofenrollenrolledprograms($filterparams) {
        global $DB, $PAGE, $OUTPUT,$CFG;
        $systemcontext = context_system::instance();
        echo $this->render_from_template('local_trainingprogram/listofenrollenrolledprograms', $filterparams);
    }


    //renu.. program methods

    public function get_programmethod($filter = false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'programmethoddata','perPage' => 10, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_trainingprogram_programmethod';
        $options['templateName']='local_trainingprogram/allprogrammethods';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'programmethoddata',
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
    public  function listofprogrammethod($filterparams) {
        global $DB, $PAGE, $OUTPUT,$CFG;

        $systemcontext = context_system::instance();
        $filterparams['createsetting'] = (is_siteadmin()) ? true : false;
        echo $this->render_from_template('local_trainingprogram/listofprogrammethod', $filterparams);
    }


      //renu.. evalution methods

      public function get_evalutionmethod($filter = false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'evalutionmethoddata','perPage' => 10, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_trainingprogram_evalutionmethod';
        $options['templateName']='local_trainingprogram/allevalutionmethods';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'evalutionmethoddata',
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
    public  function listofevalutionmethod($filterparams) {
        global $DB, $PAGE, $OUTPUT,$CFG;

        $systemcontext = context_system::instance();
        $filterparams['createsetting'] = (is_siteadmin()) ? true : false;
        echo $this->render_from_template('local_trainingprogram/listofevalutionmethod', $filterparams);
    }


    public function programtopics_view($data) {
        global $DB ;
        $lang = current_language();
        list($topicsql,$topicparams) = $DB->get_in_or_equal(explode(',',$data->trainingtopics));
                      
        if( $lang == 'ar'){
            $topicquerysql = "SELECT id AS topicid,name AS topicname FROM {training_topics} WHERE id  $topicsql";        

        } else {
            $topicquerysql = "SELECT id AS topicid,name AS topicname FROM {training_topics} WHERE id $topicsql ";
           

        } 


        $topicslists = $DB->get_records_sql($topicquerysql,$topicparams);
        foreach($topicslists AS $topicslist) {
                                         
            if( $lang == 'ar'){
                preg_match('/{mlang ar}(.*?){mlang}/',$topicslist->topicname, $match);
                $arabictitle =  $match[1];  

                $topicslist->topicname = $arabictitle;
                
           }else{

               preg_match('/{mlang en}(.*?){mlang}/',$topicslist->topicname, $match);
               $englishtitle =  $match[1];  

               $topicslist->topicname = $englishtitle;

           }

        }
  


        $viewdata = [
            'trainingtopics'=> array_values($topicslists)
        ];
        $result = $this->render_from_template('local_trainingprogram/viewprogramtopics', $viewdata);
        return $result;



    }

    public function get_offering_program_requests($filter = false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'manage_offeringprogramrequests','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_trainingprogram_offeringprogramrequests';
        $options['templateName']='local_trainingprogram/offeringprogramrequests';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'manage_offeringprogramrequests',
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

    public function listofofferingprogramrequests($filterparams) {
        global $DB, $PAGE, $OUTPUT,$CFG;
        $systemcontext = context_system::instance();
        echo $this->render_from_template('local_trainingprogram/listofofferingprogramrequests', $filterparams);
    }

    public function view_currentoffering($rootid,$offeringid,$requesttype) {
        global $DB ;
        $lang = current_language();

        $table = ($requesttype == 'Create') ? 'official_tp_offerings' : 'tp_offerings';
        $offeringrecord = $DB->get_record($table,['id'=>$offeringid]);

        $starttimemeridian = gmdate('a',$offeringrecord->time); 
        $endtimemeridian = gmdate('a',($offeringrecord->time + $offeringrecord->duration)); 

        $starttime = gmdate("h:i",$offeringrecord->time);
        $endttime = gmdate("h:i",($offeringrecord->time + $offeringrecord->duration));

        if($lang == 'ar') {
            $startmeridian = ($starttimemeridian == 'am')? 'صباحا':'مساءً';
            $endmeridian =  ($endtimemeridian == 'am')? 'صباحا':'مساءً';
        } else {
            $startmeridian = ($starttimemeridian == 'am')? 'AM':'PM';
            $endmeridian =  ($endtimemeridian == 'am')? 'AM':'PM';
        }
        
        $tobeupdatestartdate = 0;
        $tobeupdateenddate = 0;
        $tobeupdateseats = 0;
        $tobeupdatedofferingmethod = '';
        if($requesttype == 'Update') {

            $tobeupdaterecord =  $DB->get_record('offering_program_requests',['id'=>$rootid,'referenceid'=>$offeringid]);
            $start = $tobeupdaterecord->startdate+$tobeupdaterecord->starttime;
            $end = $tobeupdaterecord->startdate+$tobeupdaterecord->endtime;
            $duration = abs($start-$end);
            $tobestarttimemeridian = gmdate('a',$tobeupdaterecord->starttime); 
            $tobeendtimemeridian = gmdate('a',($tobeupdaterecord->starttime + $duration)); 
            $tobestarttime = gmdate("h:i",$tobeupdaterecord->starttime);
            $tobeendttime = gmdate("h:i",($tobeupdaterecord->starttime + $duration));

            if($lang == 'ar') {
                $tobestartmeridian = ($tobestarttimemeridian == 'am')? 'صباحا':'مساءً';
                $tobeendmeridian =  ($tobeendtimemeridian == 'am')? 'صباحا':'مساءً';
            } else {
                $tobestartmeridian = ($tobestarttimemeridian == 'am')? 'AM':'PM';
                $tobeendmeridian =  ($tobeendtimemeridian == 'am')? 'AM':'PM';
            }
            $tobeupdatestartdate = ($tobeupdaterecord->startdate > 0) ? userdate($tobeupdaterecord->startdate, get_string('strftimedaydate', 'langconfig')) : 0; 

            $tobeupdatestarttime = ($tobeupdaterecord->starttime > 0) ? $tobestarttime .' '.$tobestartmeridian : '--'; 

            $tobeupdateenddate = ($tobeupdaterecord->enddate > 0) ? userdate($tobeupdaterecord->enddate, get_string('strftimedaydate', 'langconfig')) :0; 


            $tobeupdateendtime = ($tobeupdaterecord->endtime > 0) ? $tobeendttime .' '.$tobeendmeridian : 0; 

            $tobeupdateprice = ($tobeupdaterecord->sellingprice > 0) ? $tobeupdaterecord->sellingprice : 0; 


            if($tobeupdaterecord->trainingmethod == 'online') {
                $tobeupdatedofferingmethod = get_string('scheduleonline','local_trainingprogram');
            } elseif($offeringrecord->trainingmethod == 'offline') {
                $tobeupdatedofferingmethod =get_string('scheduleoffline','local_trainingprogram');
            } else {
                $tobeupdatedofferingmethod = get_string('scheduleelearning','local_trainingprogram');
            }

        }
        $trainertype = false;
        $trainerprovider = '';
        $trainerproviderlogo ='';
        $coffering=$DB->get_records_sql("SELECT toff.trainertype,org.orglogo,org.fullname FROM {tp_offerings} toff JOIN {local_organization} org ON toff.trainerorg=org.id WHERE toff.trainingid =$offeringrecord->trainingid AND toff.id =$offeringid");
        foreach($coffering as $cc){
            if($cc->trainertype) {
                if($cc->trainertype ==1){
                    if(!empty($cc->orglogo)){
                        $sql = "SELECT * FROM {files} WHERE itemid = $cc->orglogo AND component = 'local_organization'  AND filearea='orglogo' AND filename != '.'";
                        $logorecord = $DB->get_record_sql($sql);

                        if (!empty($logorecord)) {
                            $logourl = moodle_url::make_pluginfile_url($logorecord->contextid, $logorecord->component,
                            $logorecord->filearea, $logorecord->itemid, $logorecord->filepath,
                            $logorecord->filename);
                            $logourl = $logourl->out();
                        }
                                
                        $trainerproviderlogo =  $logourl;
                    }
                 $trainerprovider= $cc->fullname;
                }
                if($cc->trainertype ==0){
                   $trainerprovider = 'Individual';  
                }

                $trainertype = true;
            }
        
        }
        $offactuallang=($offeringrecord->languages == '1') ? get_string('english','local_trainingprogram') : get_string('arabic','local_trainingprogram');

        
        if($offeringrecord->halladdress== 0  && $offeringrecord->halllocation=='clientheadquarters'){
            $hallname = "At the Client's headquarters";
        }
       
        else{
            $hallname = $offeringrecord->hallname;
        }
        $programcost = $DB->get_field('local_trainingprogram','price',['id'=>$offeringrecord->trainingid]);
        $courseid = $DB->get_field('local_trainingprogram', 'courseid', array('id' => $offeringrecord->trainingid));
        $traineeroleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
        $trainerroleid = $DB->get_field('role', 'id', array('shortname' => 'trainer'));
        $enrolledtraineessql=" SELECT COUNT(id) FROM {program_enrollments} WHERE programid = $offeringrecord->trainingid AND offeringid = $offeringrecord->id AND courseid = $courseid AND roleid = $traineeroleid AND userid IN (SELECT id FROM {user} WHERE deleted = 0 ) AND enrolstatus = 1";
        $programenrolledcount = $DB->count_records_sql($enrolledtraineessql);
        if( $lang == 'ar'){
            $organizationname=$DB->get_field('local_organization','fullnameinarabic',array('id'=>$offeringrecord->organization));
        }else{
            $organizationname=$DB->get_field('local_organization','fullname',array('id'=>$offeringrecord->organization));
        }
        $alllimitestrainers = 'SELECT count(lu.id) FROM {local_users} as  lu JOIN {program_enrollments} as pe ON lu.userid = pe.userid  WHERE  pe.programid = '.$offeringrecord->trainingid.' AND pe.offeringid = '.$offeringrecord->id.' AND pe.courseid = '.$courseid .' AND pe.roleid = '.$trainerroleid.' AND lu.deleted = 0  ';
        $total_trainers_count = $DB->count_records_sql($alllimitestrainers);       
        if($total_trainers_count > 0) {

            if( $lang == 'ar'){

                $limitestrainerssql = 'SELECT CONCAT(lu.firstnamearabic," ",lu.lastnamearabic) FROM {local_users} as  lu JOIN {program_enrollments} as pe ON lu.userid = pe.userid  WHERE  pe.programid = '.$offeringrecord->trainingid.' AND pe.offeringid = '.$$offeringrecord->id.' AND pe.courseid = '.$courseid .' AND pe.roleid = '.$trainerroleid.' AND lu.deleted = 0 LIMIT 2 ';
    
                $limitestrainerssql = 'SELECT CONCAT(lu.firstnamearabic," ",lu.lastnamearabic) FROM {local_users} as  lu JOIN {program_enrollments} as pe ON lu.userid = pe.userid  WHERE  pe.programid = '.$offeringrecord->trainingid.' AND pe.offeringid = '.$$offeringrecord->id.' AND pe.courseid = '.$courseid .' AND pe.roleid = '.$trainerroleid.' AND lu.deleted = 0 LIMIT '.$total_trainers_count.' OFFSET 2 ';
            }else{
    
                $limitestrainerssql = 'SELECT CONCAT(lu.firstname," ",lu.lastname) FROM {local_users} as  lu JOIN {program_enrollments} as pe ON lu.userid = pe.userid  WHERE  pe.programid = '.$offeringrecord->trainingid.' AND pe.offeringid = '.$$offeringrecord->id.' AND pe.courseid = '.$courseid .' AND pe.roleid = '.$trainerroleid.' AND lu.deleted = 0 LIMIT 2 ';
    
                $limitestrainerssql = 'SELECT CONCAT(lu.firstname," ", lu.lastname) FROM {local_users} as  lu JOIN {program_enrollments} as pe ON lu.userid = pe.userid  WHERE  pe.programid = '.$offeringrecord->trainingid.' AND pe.offeringid = '.$$offeringrecord->id.' AND pe.courseid = '.$courseid .' AND pe.roleid = '.$trainerroleid.' AND lu.deleted = 0 LIMIT '.$total_trainers_count.' OFFSET 2 ';
            }
            $limitedtrainers = $DB->get_fieldset_sql($limitestrainerssql);

        }

        if($offeringrecord->trainingmethod == 'online') {
            $offeringmethod = get_string('scheduleonline','local_trainingprogram');
        } elseif($offeringrecord->trainingmethod == 'offline') {
            $offeringmethod =get_string('scheduleoffline','local_trainingprogram');
        } else {
            $offeringmethod = get_string('scheduleelearning','local_trainingprogram');
        }
        $topssql = "SELECT ofs.sessionid, ats.attendanceid,ats.sessdate,ofs.courseid,ofs.programid  FROM {offering_sessions} AS ofs JOIN {attendance_sessions} AS ats ON ofs.sessionid = ats.id  WHERE ofs.offeringid =:offeringid";

        $tpofferingssessions = $DB->get_records_sql($topssql, ['offeringid' => $offeringrecord->id]);
        $k=1;
        foreach ($tpofferingssessions AS $tpofferingssession) {
            $tpofferingssession->cid=$k++;
            $tpofferingssession->sessiondate = date('jS F Y',$tpofferingssession->sessdate);
        } 
        $viewdata = [
            'code'=>$offeringrecord->code,
            'offeringmethod'=>$offeringmethod,
            'published'=> $offeringrecord->published == 0 ? true :false,
            'cancelledstatus'=> $offeringrecord->cancelled,
            'iscancelled' => ($offeringrecord->cancelled == 2) ? true:false,
            'cancelledstatustext' => ($offeringrecord->cancelled == 2) ? get_string('cancelled','local_trainingprogram'):  (($offeringrecord->cancelled == 1 || $offeringrecord->cancelled == -1) ? get_string('cancelrequestpending','local_trainingprogram') : get_string('cancelrequestrejected','local_trainingprogram')),
            'datedisplay'=>($offeringrecord->trainingmethod !='elearning') ? true : false,
            'canupdatefinanciallstatus'=>($offeringrecord->trainingmethod != 'elearning') ? true : false,
            'startdate'=>($offeringrecord->trainingmethod !='elearning') ? userdate($offeringrecord->startdate, get_string('strftimedaydate', 'langconfig')).' '.$starttime .' '.$startmeridian : '--',
            'enddate'=>($offeringrecord->trainingmethod !='elearning') ? userdate($offeringrecord->enddate, get_string('strftimedaydate', 'langconfig')).' '.$endttime .' '.$endmeridian : '--',
            'trainertype'=>$trainertype,
            'trainerprovider'=>$trainerprovider,
            'trainerproviderlogo'=>$trainerproviderlogo,
            'halldisplayaction' => ($offeringrecord->trainingmethod == 'offline' || $offeringrecord->trainingmethod == 'online') ? true :false,
            'offcourselanguage'=>$offactuallang,
            'availableseats'=>$offeringrecord->availableseats,
            'buildingname'=>$offeringrecord->buildingname,
            'hallname'=>$hallname,
            'city'=>($offeringrecord->city = 1 ) ? 'Riyad':$offeringrecord->city,
            'sellingprice'=>($programcost > 0) ? round($offeringrecord->sellingprice,2) : 0,
            'maplocation'=> $offeringrecord->maplocation,
            'enrolled'=>$programenrolledcount ? $programenrolledcount : 0,       
            'type'=>$offeringrecord->type,
            'privateorg' =>  ($offeringrecord->type == 'Private' || $offeringrecord->type == 'خاص' ) ? true : false,  
            'orgname' => $organizationname ? $organizationname : 'N/A' ,
            'limitedtrainers'=>($total_trainers_count > 0) ? implode(',', $limitedtrainers) : '',
            'hastrainernamelistmore' => ($total_trainers_count) > 1 ? true : false,
            'trainername' => ($total_trainers_count > 0) ? implode(',', $limitedtrainers) : '',
           'sessiondatadisplay' => (($offeringrecord->trainingmethod == 'offline' || $offeringrecord->trainingmethod == 'online') && $requesttype != 'Create' ) ? true :false,
           'sessiondata' => (!empty($tpofferingssessions)) ? array_values($tpofferingssessions) : array(),
           'tobeupdatestartdate'=>($tobeupdaterecord->startdate > 0) ? $tobeupdatestartdate : 0,
           'tobeupdateenddate'=>($tobeupdaterecord->enddate > 0 ) ? $tobeupdateenddate : 0,
           'tobeupdatestarttime'=>($tobeupdaterecord->starttime > 0) ? $tobeupdatestarttime : 0,
           'tobeupdateendtime'=>($tobeupdaterecord->endtime > 0 ) ? $tobeupdateendtime : 0,
           'tobeupdateprice'=>($tobeupdaterecord->sellingprice > 0) ? $tobeupdateprice : 0,
           'viewupdaterequest' => ($requesttype == 'Update' && ($tobeupdaterecord->startdate > 0 || $tobeupdaterecord->enddate > 0 || $tobeupdaterecord->sellingprice > 0 || $tobeupdaterecord->trainingmethod !='' )) ? true : false,
           'tobeupdatestartdatedisplay'=>($tobeupdaterecord->startdate > 0) ? true :false,
           'tobeupdateenddatedisplay'=>($tobeupdaterecord->enddate > 0) ? true :false,

           'tobeupdatestarttimedisplay'=>($tobeupdaterecord->starttime > 0) ? true :false,
           'tobeupdateendtimedisplay'=>($tobeupdaterecord->endtime > 0) ? true :false,

           'pricedisplay'=>($tobeupdaterecord->sellingprice > 0) ? true :false,
           'tobeupdatedofferingmethoddisplay'=>(!is_null($tobeupdaterecord->trainingmethod)) ? true : false,
           'tobeupdatedofferingmethod'=>(!is_null($tobeupdaterecord->seats)) ? $tobeupdatedofferingmethod : '',

        ];
        $result = $this->render_from_template('local_trainingprogram/detailed_offering_overview', $viewdata);
        return $result;
    }

    function render_discountmanagement($page)
    {
        $data = $page->discountdata_for_template($this);                                                                                  
        return parent::render_from_template('local_trainingprogram/discountmanagement_mainpage', $data);         
    }

    public function view_managementdiscountdata($type) {
        global $DB ;
        $currentdate = strtotime(date('Y-m-d'));

        //Coupons
        $created_coupons = $DB->count_records('coupon_management');
        $expired_coupons = $DB->count_records_sql('SELECT COUNT(id) FROM {coupon_management} WHERE coupon_expired_date  <  :cdate',['cdate'=>$currentdate]);
        $available_coupons = $DB->count_records_sql('SELECT COUNT(id) FROM {coupon_management} WHERE coupon_expired_date  >= :cdate  AND coupon_status = :cstatus',['cdate'=>$currentdate,'cstatus'=>1]);
        $used_coupons = $DB->count_records_sql('SELECT COUNT(id) FROM {coupon_management} WHERE coupon_status = :cstatus',['cdate'=>$currentdate,'cstatus'=>2]);
        $maxdiscount = $DB->count_records_sql('SELECT MAX(discount) FROM {coupon_management}');

        // Early registrations
        $ea_created_discounts = $DB->count_records('earlyregistration_management');
        $ea_expired_discounts = $DB->count_records_sql('SELECT COUNT(id) FROM {earlyregistration_management} WHERE earlyregistration_expired_date  <  :cdate',['cdate'=>$currentdate]);
        $ea_available_discounts = $DB->count_records_sql('SELECT COUNT(id) FROM {earlyregistration_management} WHERE earlyregistration_expired_date  >= :cdate  AND earlyregistration_status = :cstatus',['cdate'=>$currentdate,'cstatus'=>1]);
        $eamaxdiscount = $DB->count_records_sql('SELECT MAX(discount) FROM {earlyregistration_management}');
        $abeneficiries = $DB->get_field_sql('SELECT GROUP_CONCAT(beneficiaries)  FROM {earlyregistration_management} WHERE beneficiaries IS NOT NULL');
        $eabeneficiries = !is_null($abeneficiries) ? count(explode(',',$abeneficiries)) : 0;

        // Group Discounts
        $gr_created_discounts = $DB->count_records('groupdiscounts');
        $gr_expired_discounts = $DB->count_records_sql('SELECT COUNT(id) FROM {groupdiscounts} WHERE expired_date  <  :cdate',['cdate'=>$currentdate]);
        $gr_available_discounts = $DB->count_records_sql('SELECT COUNT(id) FROM {groupdiscounts} WHERE expired_date  >= :cdate  AND status = :cstatus',['cdate'=>$currentdate,'cstatus'=>1]);
        $gr_max_discount = ($gr_created_discounts > 0) ? $DB->count_records_sql('SELECT MAX(discount) FROM {groupdiscounts}') : 0;
        $gbeneficiries = $DB->get_field_sql('SELECT GROUP_CONCAT(beneficiaries)  FROM {groupdiscounts} WHERE beneficiaries IS NOT NULL');
        $grbeneficiries = !is_null($gbeneficiries) ? count(explode(',',$gbeneficiries)) : 0;

        $viewdata = [
            'created_coupons'=>$created_coupons,
            'expired_coupons'=>$expired_coupons,
            'available_coupons'=>$available_coupons,
            'used_coupons'=>$used_coupons,
            'c_max_discount'=>$maxdiscount ? $maxdiscount : 0,

            'ea_created_discounts'=>$ea_created_discounts,
            'ea_expired_discounts'=>$ea_expired_discounts,
            'ea_available_discounts'=>$ea_available_discounts,
            'ea_beneficiaries'=>($eabeneficiries > 0) ? $eabeneficiries : 0,
            'ea_max_discount'=>$eamaxdiscount ? $eamaxdiscount : 0,


            'gr_created_discounts'=>$gr_created_discounts,
            'gr_expired_discounts'=>$gr_expired_discounts,
            'gr_available_discounts'=>$gr_available_discounts,
            'gr_beneficiaries'=>($grbeneficiries > 0) ? $grbeneficiries : 0,
            'gr_max_discount'=>$gr_max_discount ?$gr_max_discount : 0,
        ];
        $result = $this->render_from_template('local_trainingprogram/listofdiscountmanagement', $viewdata);
        return $result;
    }
    public function view_discountentity($entityid,$entitytype) {
        global $DB ;
        $currentlang = current_language();
        $currentdate = strtotime(date('Y-m-d'));
        $table = ($entitytype == 'coupon') ? 'coupon_management' : (($entitytype == 'earlyregistration') ? 'earlyregistration_management' : 'groupdiscounts');
        $record = $DB->get_record($table,['id'=>$entityid]);

        $created_at = ($entitytype == 'coupon') ? $record->coupon_created_date : (($entitytype == 'earlyregistration') ? $record->earlyregistration_created_date : $record->timecreated);

        $expired_at = ($entitytype == 'coupon') ? $record->coupon_expired_date : (($entitytype == 'earlyregistration') ? $record->earlyregistration_expired_date : $record->expired_date);

        $discountstatus = ($entitytype == 'coupon') ? $record->coupon_status : (($entitytype == 'earlyregistration') ? $record->earlyregistration_status : $record->status);

        // Programs
        if($currentlang == 'ar') {
            $pdisplaying_name = "namearabic";
        } else {
            $pdisplaying_name = "name";
        }
        if(!is_null($record->programs)) {
            $pquery = "SELECT $pdisplaying_name 
                        FROM {local_trainingprogram} 
                        WHERE id IN ($record->programs)";
          $allprograms=$DB->get_fieldset_sql($pquery);
          $programs = ($allprograms) ? implode(', ', $allprograms):get_string('all','local_trainingprogram');

        } else {
            $programs = get_string('all','local_trainingprogram');
        }
       
        //Exams
        if($currentlang == 'ar') {
            $edisplaying_name = "examnamearabic";
        } else {
            $edisplaying_name = "exam";
        }
       
        if(!is_null($record->exams)) {
            $equery = "SELECT $edisplaying_name
              FROM {local_exams} 
              WHERE id IN ($record->exams)";
            $allexams=$DB->get_fieldset_sql($equery);
            $exams = $allexams ? implode(', ', $allexams):get_string('all','local_trainingprogram');


        } else {
            $exams = get_string('all','local_trainingprogram');
        }

        //Events
        if($currentlang == 'ar') {
            $evdisplaying_name = "titlearabic";
        } else {
            $evdisplaying_name = "title";
        }
        if(!is_null($record->events)) {
            $evquery = "SELECT $evdisplaying_name
                                FROM {local_events} 
                               WHERE id IN ($record->events)";
            $allevents=$DB->get_fieldset_sql($evquery);
            $events = $allevents ? implode(', ', $allevents):get_string('all','local_trainingprogram');
        } else {
            $events = get_string('all','local_trainingprogram');
        }

        //Oragnizations
        if($currentlang == 'ar') {
            $odisplaying_name = "fullnameinarabic";
        } else {
            $odisplaying_name = "fullname";
        }

        if(!is_null($record->organizations)) {
            $ovquery = "SELECT $odisplaying_name 
                                FROM {local_organization} 
                               WHERE id IN ($record->organizations)";
            $allorganizations=$DB->get_fieldset_sql($ovquery);
            $organizations = $allorganizations ? implode(', ', $allorganizations):get_string('all','local_trainingprogram');


        } else {
            $organizations = get_string('all','local_trainingprogram');
        }


        $viewdata = [
            'couponview'=>($entitytype == 'coupon') ? true : false,            
            'couponcode'=>($entitytype == 'coupon') ? $record->code : 0,

            'earlyregistrationview'=>($entitytype == 'earlyregistration') ? true : false,
            'days'=>($entitytype == 'earlyregistration') ? $record->days : 0,

            'groupview'=>($entitytype == 'group') ? true : false,
            'min_enrollments'=>($entitytype == 'group') ? $record->group_count : 0,

            'discount'=>$record->discount.'%',
            'createddate'=> userdate($created_at,get_string('strftimedatemonthabbr', 'core_langconfig')),
            'expiredddate'=> userdate($expired_at,get_string('strftimedatemonthabbr', 'core_langconfig')),
            'status'=>($discountstatus == 2)  ? get_string('applied', 'local_trainingprogram') : (($expired_at >= $currentdate && $discountstatus == 1) ?  get_string('available', 'local_trainingprogram'):get_string('expired', 'local_trainingprogram')),
            'programs'=>wordwrap(format_text($programs,FORMAT_HTML),100,"<br>\n"),
            'exams'=>wordwrap(format_text($exams,FORMAT_HTML),100,"<br>\n"),
            'events'=>wordwrap(format_text($events,FORMAT_HTML),100,"<br>\n"),
            'organizations'=>wordwrap(format_text($organizations,FORMAT_HTML),100,"<br>\n"),
        ];
        $result = $this->render_from_template('local_trainingprogram/view_discountentity', $viewdata);
        return $result;
    }

}
