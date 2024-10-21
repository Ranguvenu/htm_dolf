<?php
// This file is part of Moodle - http://moodle.org/
// Moodle is free software: you can redistribute it and/or modify
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
 * @package    local_userapproval
 * @copyright  2022 eAbyas Info Solutions<info@eabyas.com>
 * @author     Vinod Kumar  <vinod.p@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use local_userapproval\action\manageuser as manageuser;
use html_writer;
use moodle_url;
use context_system;
require_once($CFG->dirroot.'/local/userapproval/lib.php');
require_once($CFG->dirroot.'/local/organization/lib.php');
require_once($CFG->dirroot.'/user/lib.php');

class local_userapproval_renderer extends plugin_renderer_base {

    public function get_catalog_manageusers($filter = false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'viewusersdata','perPage' => 25, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_users_view';
        $options['templateName']='local_userapproval/userdetails';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'viewusersdata',
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
    public function listofusers($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $filterparams['createuseraction'] = (is_siteadmin() || has_capability('local/organization:manage_communication_officer',$systemcontext) || has_capability('local/organization:manage_organizationofficial',$systemcontext)) ? true : false;
        $filterparams['uploadusersaction'] = (is_siteadmin() || has_capability('local/organization:manage_communication_officer',$systemcontext) || has_capability('local/organization:manage_organizationofficial',$systemcontext)) ? true : false;
        $filterparams['fastenrollaction'] = (is_siteadmin() || has_capability('local/organization:manage_communication_officer',$systemcontext)) ? true : false;
        $filterparams['is_siteadmin'] = is_siteadmin() ? true : false;
        echo $this->render_from_template('local_userapproval/listofusers', $filterparams);
    }
    public function registration_view($data) {
        global $DB, $PAGE, $OUTPUT, $CFG;
    // show requested trainee data ..renu verma
        $context = context_system::instance();

  

         if ($data->gender == 1) {
            $data->gender=get_string('male','local_userapproval');
        } elseif ($data->gender == 2) {
            $data->gender=get_string('female','local_userapproval');
        } else {
            $data->gender=get_string('others','local_userapproval');
        }
        if ($data->id_type == 1) {
            $data->id_type=get_string('id','local_userapproval');
        } elseif($data->id_type == 2) {
            $data->id_type=get_string('passport','local_userapproval');
        } elseif($data->id_type == 3) {
            $data->id_type=get_string('saudiid','local_userapproval');
        } else {
            $data->id_type=get_string('residentialid','local_userapproval');
        }

        if ($data->approvedstatus == 1) {
            $data->approvedstatus = get_string('pending','local_userapproval');
         } else if($data->approvedstatus == 2) {
             $data->approvedstatus = get_string('approved','local_userapproval');
        } 
        else if($data->approvedstatus == 3) {
            $data->approvedstatus= get_string('rejected','local_userapproval');
        }
        else{
            $data->approvedstatus=get_string('assignalerady','local_userapproval');;
        }

        if(current_language() == 'ar') {

            $data->sector= $DB->get_field('local_sector','titlearabic',array('id'=>$data->sector));
            $data->fullname=$data->firstnamearabic.' '.$data->middlenamearabic.' '.$data->thirdnamearabic.' '.$data->lastnamearabic;
            $data->organization= $DB->get_field('local_organization','fullnameinarabic',array('id'=>$data->organization));
            $data->segment= $DB->get_field('local_segment','titlearabic',array('id' =>$data->segment));
            $data->jobfamily= $DB->get_field('local_jobfamily','familynamearabic',array('id'=>$data->jobfamily));

        } else {

            $data->sector= $DB->get_field('local_sector','title',array('id'=>$data->sector));
            $data->fullname=$data->firstname.' '.$data->middlenameen.' '.$data->thirdnameen.' '.$data->lastname;
            $data->organization= $DB->get_field('local_organization','fullname',array('id'=>$data->organization));
            $data->segment= $DB->get_field('local_segment','title',array('id' =>$data->segment));
            $data->jobfamily= $DB->get_field('local_jobfamily','familyname',array('id'=>$data->jobfamily));
        }
       
        $nationalities =(is_numeric($data->nationality)) ?  (new manageuser)->get_list_of_nationalities() : get_string_manager()->get_list_of_countries();

        $countries =(is_numeric($data->country)) ?  (new manageuser)->get_list_of_nationalities() : get_string_manager()->get_list_of_countries();

        $languages = get_string_manager()->get_list_of_languages();
        $data->lang=$languages[$data->lang];
        $data->nationality=$nationalities[$data->nationality];
        $data->country=$countries[$data->country]; 
        $data->timecreated=userdate($data->timecreated,get_string('strftimedatemonthabbr', 'core_langconfig'));
        
        if($data->requesttype){
        $data->requestpage=true;
        if ($data->requesttype=='Trainer'){
             $data->trainer=true;
         $trainerrequestdetail =$DB->get_record('local_trainer_request',array('userid'=>$data->userid,'id'=>$data->requestid));
                 $itemid =$trainerrequestdetail->qualifications;
                $fs = get_file_storage();
                $files = $fs->get_area_files($context->id, 'local_userapproval', 'qualification', $itemid);
             $trainer_document_string =  get_string('trainer_document','local_userapproval');
                foreach($files as $file){
                    $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(),
                    $file->get_itemid(), $file->get_filepath(), $file->get_filename(), false);

                    $downloadurl = $url->get_scheme() . '://' . $url->get_host() . ':' . $url->get_port() . $url->get_path();

                     $shortenurl= $downloadurl;

                    $trainerrequestdetail->qualifications=$shortenurl ;
                }
            $data->qualifications=$trainerrequestdetail->qualifications ;
            $data->yearsofexperience=$trainerrequestdetail->yearsofexperience;
            $data->fieldoftraining=$trainerrequestdetail->fieldoftraining?$trainerrequestdetail->fieldoftraining:0;
            $data->certificates=$trainerrequestdetail->certificates;

        }
          if ($data->requesttype=='Expert'){
         $expertrequestdetail =$DB->get_record('local_expert_request',array('userid'=>$data->userid,'id'=>$data->requestid));
             $data->trainer=false;
             $itemid =$expertrequestdetail->qualifications;
                $fs = get_file_storage();
                $files = $fs->get_area_files($context->id, 'local_userapproval', 'qualification', $itemid);
                $expert_document_string =  get_string('expert_document','local_userapproval');
                foreach($files as $file){
                     $filename = $file->get_filename();
                    $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(),
                    $file->get_itemid(), $file->get_filepath(), $file->get_filename(), false);

                    $downloadurl = $url->get_scheme() . '://' . $url->get_host() . ':' . $url->get_port() . $url->get_path();

                    $shortenurl= $downloadurl;
                   
                    $expertrequestdetail->qualifications=$shortenurl ;
                }
             
            $data->qualifications=$expertrequestdetail->qualifications ;   
            $data->yearsofexperience=$expertrequestdetail->yearsofexperience;
            $data->fieldoftraining=$expertrequestdetail->fieldoftraining?$expertrequestdetail->fieldoftraining:0;
            $data->fieldofexperience=$expertrequestdetail->fieldofexperience;
            $data->certificates=$expertrequestdetail->certificates;
            $data->fieldoftrainingothers=$expertrequestdetail->fieldoftrainingothers;
 
   
        
    }
}

        $result = $this->render_from_template('local_userapproval/viewregistration', $data);
        return $result;
    }

    public function get_orgrequest_cardview($filter = false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'vieworgrequestdata','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
         $options['methodName']='local_users_orgrequest';
        $options['templateName']='local_userapproval/org_request_block';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'vieworgrequestdata',
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

    public function listoforgrequest_data($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        return $this->render_from_template('local_userapproval/listoforgrequest_data', $filterparams);
    }

     public function get_catalog_totalorgrequests($filter = false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'vieworgpendingrequestsdata','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
         $options['methodName']='local_users_totalorgpendingrequests';
        $options['templateName']='local_userapproval/orgpendingrequests';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'vieworgpendingrequestsdata',
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

    public function listofpendingorgrequests($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        echo $this->render_from_template('local_userapproval/listofpendingorgrequests', $filterparams);
    }
    public function userprofileview($userid) {
        global $CFG, $PAGE,$USER, $OUTPUT, $DB;
        $currentlang = current_language();
        $context = context_system::instance();
        $selectsql= "SELECT u.*,lu.organization,lu.bannerimage,lu.id as localuserid,u.id as  registereduserid, lu.certificates, lu.id_number, lu.id_type,lu.linkedinprofile,lu.sector,lu.segment, lu.jobfamily,lu.jobrole,lu.dateofbirth,lu.gender,lu.firstnamearabic,lu.lastnamearabic,lu.middlenameen,lu.middlenamearabic,lu.thirdnameen,lu.thirdnamearabic
           FROM {user} as u  
           LEFT JOIN {local_users} as lu ON u.id = lu.userid  
           WHERE u.id=$userid";
        $result=$DB->get_record_sql($selectsql);
       
        $getuserwallet=(new manageuser)::get_user_wallet($userid);
        if( $result->organization > 0 ) {
            $user_organization = $result->organization;
            $result->organization = $user_organization;
        }
        $certificates_count = $DB->count_records('tool_certificate_issues',['userid' => $userid]);
        $result->certificates = $certificates_count ? $certificates_count : 0;
       
        if ($result->gender == 1) {
        $result->gender=get_string('male','local_userapproval');
        } elseif ($result->gender == 2) {
        $result->gender=get_string('female','local_userapproval');
        } else {
        $result->gender=get_string('others','local_userapproval');
        }
        $countries = get_string_manager()->get_list_of_countries();
        $result->country=$countries[$result->country];
        $organizations= $DB->get_records('local_organization',array('id'=>$result->organization));
        foreach($organizations as $organization){
          $result->organization=$organization->fullname;
        }  
        $result->picture=profileimage_url($result->picture);
        $result->bannerimage=bannerimage_url($result->bannerimage);
        $countries = get_string_manager()->get_list_of_countries();
        $user_idnumber= $result->id_number;
        $result->id_number = $user_idnumber;
         if ($result->id_type == 1) {
            $result->id_type=get_string('id','local_userapproval');
        } elseif($result->id_type == 2) {
            $result->id_type=get_string('passport','local_userapproval');
        } elseif($result->id_type == 3) {
            $result->id_type=get_string('saudiid','local_userapproval');
        } else {
            $result->id_type=get_string('residentialid','local_userapproval');
        }
        $is_reocrd_exists = $DB->record_exists('local_users',['userid' => $userid]);

        if($is_reocrd_exists) {
            if($currentlang == 'ar'){
                if($result->sector) {
                    $sectornamesql = 'SELECT titlearabic FROM {local_sector} WHERE id = '.$result->sector.'';  
                    $result->sectorname = $DB->get_field_sql($sectornamesql); 
                }
                if($result->segment) {
                    $segmentnamesql = 'SELECT titlearabic FROM {local_segment} WHERE id = '.$result->segment.'';  
                    $result->segment = $DB->get_field_sql($segmentnamesql); 
                }

                if($result->jobfamily) {
                    $jobfamilynamesql = 'SELECT familynamearabic FROM {local_jobfamily} WHERE id = '.$result->jobfamily.'';  
                    $result->jobfamilyname = $DB->get_field_sql($jobfamilynamesql); 
                }

                if($result->jobrole) {
                  $jobrolenamesql = 'SELECT titlearabic FROM {local_jobrole_level} WHERE id = '.$result->jobrole;
                  $result->jobrolename = $DB->get_field_sql($jobrolenamesql);  
                }
                $result->userfullname=$result->firstnamearabic.' '.$result->middlenamearabic.' '.$result->thirdnamearabic.' '.$result->lastnamearabic;
            } else {
                if($result->sector) {
                    $sectornamesql = 'SELECT title FROM {local_sector} WHERE id = '.$result->sector.'';  
                    $result->sectorname = $DB->get_field_sql($sectornamesql); 
                }
                if($result->segment) {
                    $segmentnamesql = 'SELECT title FROM {local_segment} WHERE id = '.$result->segment.'';  
                    $result->segmentname = $DB->get_field_sql($segmentnamesql); 
                }

                if($result->jobfamily) {

                    $jobfamilynamesql = 'SELECT familyname FROM {local_jobfamily} WHERE id ='.$result->jobfamily; 
                     $result->jobfamilyname = $DB->get_field_sql($jobfamilynamesql);
                } 
                if($result->jobrole) {
                    $jobrolenamesql = 'SELECT title FROM {local_jobrole_level} WHERE id = '.$result->jobrole.''; 
                    $result->jobrolename = $DB->get_field_sql($jobrolenamesql); 
                }
                $result->userfullname=$result->firstname.' '.$result->middlenameen.' '.$result->thirdnameen.' '.$result->lastname;
            }
           
        } else {

            $fullname = $result->firstname.' '.$result->lastname;
            $result->userfullname =  format_text($fullname,FORMAT_HTML);
            $result->jobfamilyname = '-';
            $result->jobrolename = '-';
        }
        $result->dateofbirth = ($result->dateofbirth) ? userdate($result->dateofbirth,get_string('strftimedatemonthabbr', 'core_langconfig')) : '';
        $result->isorgofficial = (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $context)) ? true : false;
        $result->issiteadmin = (is_siteadmin()) ? true : false;

        $recommendedprograms = $this->recommendedprograms();
        $recommendedexams = $this->recommendedexams();

        $result->loginas = (!is_siteadmin() && has_capability('local/organization:manage_trainee', $context) && !$USER->realuser) ? true : false;

        $traineeroleid = (int) $DB->get_field('role','id',['shortname'=>'trainee']);
         $trainerroleid = (int) $DB->get_field('role','id',['shortname'=>'trainer']);
        $expertroleid = (int) $DB->get_field('role','id',['shortname'=>'expert']);
        $traineerequest =  $DB->get_field('local_trainer_request','id',['status'=>1,'userid'=>$userid]);
        $expertrequest =  $DB->get_field('local_expert_request','id',['status'=>1,'userid'=>$userid]);
    
        if($traineerequest && $expertrequest ) {
            $pendingstatus = true;
        } else {
            $pendingstatus = false;
        }
        if($is_reocrd_exists) {
            $organizationdetails = $DB->get_record_sql('SELECT org.* FROM {local_organization} org 
                                                JOIN {local_users} lou ON lou.organization = org.id
                                                WHERE lou.userid = '.$userid.' AND lou.organization IS NOT NULL AND lou.organization > 0');
           $organizationdetails->name = ($currentlang == 'ar') ? $organizationdetails->fullnameinarabic :   $organizationdetails->fullname;
           if(!empty($organizationdetails->approval_letter)) {
              $organizationdetails->approvalurl=approvalletter_url($organizationdetails->approval_letter);
           }
           if($currentlang == 'ar') {                 
                list($sectorsql,$ectorparams) = $DB->get_in_or_equal(explode(',',$organizationdetails->orgsector));
                $querysql = "SELECT titlearabic FROM {local_sector} WHERE id $sectorsql";
                $sectorslists= $DB->get_fieldset_sql($querysql,$ectorparams);

                list($segmentsql,$segmentparams) = $DB->get_in_or_equal(explode(',',$organizationdetails->orgsegment));
                $segquerysql = "SELECT titlearabic FROM {local_segment} WHERE id $segmentsql";
                $segmentslists= $DB->get_fieldset_sql($segquerysql,$segmentparams);

            } else{
                list($sectorsql,$ectorparams) = $DB->get_in_or_equal(explode(',',$organizationdetails->orgsector));
                $querysql = "SELECT title FROM {local_sector} WHERE id $sectorsql";
                $sectorslists= $DB->get_fieldset_sql($querysql,$ectorparams);

                list($segmentsql,$segmentparams) = $DB->get_in_or_equal(explode(',',$organizationdetails->orgsegment));
                $segquerysql = "SELECT title  FROM {local_segment} WHERE id $segmentsql";
                $segmentslists= $DB->get_fieldset_sql($segquerysql,$segmentparams);
            }
            $organizationdetails->sectors = (COUNT($sectorslists) > 0) ? implode(',', $sectorslists): ''; 
            $organizationdetails->segments = (COUNT($segmentslists) > 0) ? implode(',', $segmentslists): ''; 
            $organizationdetails->orgid =(int) $organizationdetails->id; 
        } else {

            $organizationdetails = [];
        }

        $behalfofuser = ($USER->realuser) ? $this->get_behalfofuser_profile($USER->realuser) : '';


        $data=[
            'result'=>$result,
            'organizationdetails'=>$organizationdetails,
            'recommendedexams'=>$recommendedexams,
            'recommendedprograms'=> $recommendedprograms,
            'wallet'=>($getuserwallet) ? $getuserwallet->wallet : 0,
            'walletimage'=>$CFG->wwwroot.'/local/userapproval/pix/wallet.png',
            'walletoption'=>(!is_siteadmin() && (has_capability('local/organization:manage_trainee', $context) || has_capability('local/organization:manage_organizationofficial', $context)) && $USER->id == $userid) ? true : false,
            'organizationofficial'=>(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $context) && $USER->id == $userid) ? true : false,
            'fieldexpertizeactionview'=> (!is_siteadmin() && (has_capability('local/organization:manage_trainee', $context) || has_capability('local/organization:manage_trainer', $context) || has_capability('local/organization:manage_expert', $context)) && (!user_has_role_assignment($userid, $trainerroleid, SYSCONTEXTID) || !user_has_role_assignment($userid, $expertroleid, SYSCONTEXTID))) ? true : false,
            'userid'=>$userid,
            'customizeprofileview'=>(!is_siteadmin() && $USER->id == $userid) ? true : false,
            'tabview'=> (!is_siteadmin() &&  has_capability('local/organization:manage_trainee', $context)) ? true : false,
            'myorganizationtabview'=> (!is_siteadmin() &&  has_capability('local/organization:manage_organizationofficial', $context)) ? true : false,
            'pendingstatus'=>$pendingstatus,
            'behalfofuser'=>($USER->realuser) ? ((user_has_role_assignment($USER->realuser, $traineeroleid, SYSCONTEXTID)) ? $behalfofuser : ''): '',
            'behalfuser'=>($USER->realuser) ? ((user_has_role_assignment($USER->realuser, $traineeroleid, SYSCONTEXTID)) ? true : false): false,
        ];
        echo $this->render_from_template('local_userapproval/profile', $data); 
    } 


  public function get_behalfofuser_profile($userid) {
        global $CFG, $PAGE,$USER, $OUTPUT, $DB;
        $currentlang = current_language();
        $context = context_system::instance();
        $selectsql= "SELECT u.*,lu.organization,lu.bannerimage,lu.id as localuserid,u.id as  registereduserid, lu.certificates, lu.id_number, lu.id_type,lu.linkedinprofile,lu.sector,lu.segment, lu.jobfamily,lu.jobrole,lu.dateofbirth,lu.gender,lu.firstnamearabic,lu.lastnamearabic,lu.middlenameen,lu.middlenamearabic,lu.thirdnameen,lu.thirdnamearabic
           FROM {user} as u  
           LEFT JOIN {local_users} as lu ON u.id = lu.userid  
           WHERE u.id=$userid";
        $result=$DB->get_record_sql($selectsql);
       
        $getuserwallet=(new manageuser)::get_user_wallet($userid);
        if( $result->organization > 0 ) {
            $user_organization = $result->organization;
            $result->organization = $user_organization;
        }
        $certificates_count = $DB->count_records('tool_certificate_issues',['userid' => $userid]);
        $result->certificates = $certificates_count ? $certificates_count : 0;
       
        if ($result->gender == 1) {
        $result->gender=get_string('male','local_userapproval');
        } elseif ($result->gender == 2) {
        $result->gender=get_string('female','local_userapproval');
        } else {
        $result->gender=get_string('others','local_userapproval');
        }
        $countries = get_string_manager()->get_list_of_countries();
        $result->country=$countries[$result->country];
        $organizations= $DB->get_records('local_organization',array('id'=>$result->organization));
        foreach($organizations as $organization){
          $result->organization=$organization->fullname;
        }  
        $result->picture=profileimage_url($result->picture);
        $result->bannerimage=bannerimage_url($result->bannerimage);
        $countries = get_string_manager()->get_list_of_countries();
        $user_idnumber= $result->id_number;
        $result->id_number = $user_idnumber;
         if ($result->id_type == 1) {
            $result->id_type=get_string('id','local_userapproval');
        } elseif($result->id_type == 2) {
            $result->id_type=get_string('passport','local_userapproval');
        } elseif($result->id_type == 3) {
            $result->id_type=get_string('saudiid','local_userapproval');
        } else {
            $result->id_type=get_string('residentialid','local_userapproval');
        }
        $is_reocrd_exists = $DB->record_exists('local_users',['userid' => $userid]);

        if($is_reocrd_exists) {
            if($currentlang == 'ar'){
                if($result->sector) {
                    $sectornamesql = 'SELECT titlearabic FROM {local_sector} WHERE id = '.$result->sector.'';  
                    $result->sectorname = $DB->get_field_sql($sectornamesql); 
                }
                if($result->segment) {
                    $segmentnamesql = 'SELECT titlearabic FROM {local_segment} WHERE id = '.$result->segment.'';  
                    $result->segment = $DB->get_field_sql($segmentnamesql); 
                }

                if($result->jobfamily) {
                    $jobfamilynamesql = 'SELECT familynamearabic FROM {local_jobfamily} WHERE id = '.$result->jobfamily.'';  
                    $result->jobfamilyname = $DB->get_field_sql($jobfamilynamesql); 
                }

                if($result->jobrole) {
                  $jobrolenamesql = 'SELECT titlearabic FROM {local_jobrole_level} WHERE id = '.$result->jobrole;
                  $result->jobrolename = $DB->get_field_sql($jobrolenamesql);  
                }
                $result->userfullname=$result->firstnamearabic.' '.$result->middlenamearabic.' '.$result->thirdnamearabic.' '.$result->lastnamearabic;
            } else {
                if($result->sector) {
                    $sectornamesql = 'SELECT title FROM {local_sector} WHERE id = '.$result->sector.'';  
                    $result->sectorname = $DB->get_field_sql($sectornamesql); 
                }
                if($result->segment) {
                    $segmentnamesql = 'SELECT title FROM {local_segment} WHERE id = '.$result->segment.'';  
                    $result->segmentname = $DB->get_field_sql($segmentnamesql); 
                }

                if($result->jobfamily) {

                    $jobfamilynamesql = 'SELECT familyname FROM {local_jobfamily} WHERE id ='.$result->jobfamily; 
                     $result->jobfamilyname = $DB->get_field_sql($jobfamilynamesql);
                } 
                if($result->jobrole) {
                    $jobrolenamesql = 'SELECT title FROM {local_jobrole_level} WHERE id = '.$result->jobrole.''; 
                    $result->jobrolename = $DB->get_field_sql($jobrolenamesql); 
                }
                $result->userfullname=$result->firstname.' '.$result->middlenameen.' '.$result->thirdnameen.' '.$result->lastname;
            }
           
        } else {

            $fullname = $result->firstname.' '.$result->lastname;
            $result->userfullname =  format_text($fullname,FORMAT_HTML);
            $result->jobfamilyname = '-';
            $result->jobrolename = '-';
        }

        $result->dateofbirth = ($result->dateofbirth) ? userdate($result->dateofbirth,get_string('strftimedatemonthabbr', 'core_langconfig')) : '';
        $result->isorgofficial = (!is_siteadmin() && has_capability('local/organization:manage_organizationofficial', $context)) ? true : false;
        $result->issiteadmin = (is_siteadmin()) ? true : false;

        $trainerroleid = (int) $DB->get_field('role','id',['shortname'=>'trainer']);
        $expertroleid = (int) $DB->get_field('role','id',['shortname'=>'expert']);
        $traineerequest =  $DB->get_field('local_trainer_request','id',['status'=>1,'userid'=>$userid]);
        $expertrequest =  $DB->get_field('local_expert_request','id',['status'=>1,'userid'=>$userid]);
    
        if($traineerequest && $expertrequest ) {
            $pendingstatus = true;
        } else {
            $pendingstatus = false;
        }
        if($is_reocrd_exists) {
            $organizationdetails = $DB->get_record_sql('SELECT org.* FROM {local_organization} org 
                                                JOIN {local_users} lou ON lou.organization = org.id
                                                WHERE lou.userid = '.$userid.' AND lou.organization IS NOT NULL AND lou.organization > 0');

           $organizationdetails->name = ($currentlang == 'ar') ? $organizationdetails->fullnamearabic :   $organizationdetails->fullname;
           if(!empty($organizationdetails->approval_letter)) {
              $organizationdetails->approvalurl=approvalletter_url($organizationdetails->approval_letter);
           }
           if($currentlang == 'ar') {                 
                list($sectorsql,$ectorparams) = $DB->get_in_or_equal(explode(',',$organizationdetails->orgsector));
                $querysql = "SELECT titlearabic FROM {local_sector} WHERE id $sectorsql";
                $sectorslists= $DB->get_fieldset_sql($querysql,$ectorparams);

                list($segmentsql,$segmentparams) = $DB->get_in_or_equal(explode(',',$organizationdetails->orgsegment));
                $segquerysql = "SELECT titlearabic FROM {local_segment} WHERE id $segmentsql";
                $segmentslists= $DB->get_fieldset_sql($segquerysql,$segmentparams);

            } else{
                list($sectorsql,$ectorparams) = $DB->get_in_or_equal(explode(',',$organizationdetails->orgsector));
                $querysql = "SELECT title FROM {local_sector} WHERE id $sectorsql";
                $sectorslists= $DB->get_fieldset_sql($querysql,$ectorparams);

                list($segmentsql,$segmentparams) = $DB->get_in_or_equal(explode(',',$organizationdetails->orgsegment));
                $segquerysql = "SELECT title  FROM {local_segment} WHERE id $segmentsql";
                $segmentslists= $DB->get_fieldset_sql($segquerysql,$segmentparams);
            }
            $organizationdetails->sectors = (COUNT($sectorslists) > 0) ? implode(',', $sectorslists): ''; 
            $organizationdetails->segments = (COUNT($segmentslists) > 0) ? implode(',', $segmentslists): ''; 
            $organizationdetails->orgid =(int) $organizationdetails->id; 
        } else {

            $organizationdetails = [];
        }

        return $result;
    } 
    public function recommendedexam($filter=false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'recommendedentities','perPage' => 10, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'list');
         $options['methodName']='local_userapproval_recommendedexams';
        $options['templateName']='local_userapproval/recommendedentities';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id, 'type' => 'exam'));
        $context = [
                'targetID' => 'recommendedentities',
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


    public function recommendedprogram($filter=false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'recommendedprogram','perPage' => 10, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'list');
         $options['methodName']='local_userapproval_recommendedprograms';
        $options['templateName']='local_userapproval/recommendedprograms';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id, 'type' => 'program'));
        $context = [
                'targetID' => 'recommendedprogram',
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
    
    public function listofexams($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        return $this->render_from_template('local_userapproval/listofentities', $filterparams);
    }

    public function listofprograms($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        return $this->render_from_template('local_userapproval/listofprograms', $filterparams);
    }

    public function recommendedexams()
    {
        global $DB;

        $filterparams  = $this->recommendedexam(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['globalinput'] = $this->global_filter($filterparams);
        $filterparams['entitydetails'] = $this->recommendedexam();
        $filterparams['q'] = $searchquery;
        $filterparams['widthclass'] = 'col-md-12';
        $filterparams['inputclasses'] = 'examssearchinput';
        $filterparams['placeholder'] = get_string('search_exam','local_exams');
        $data = $this->listofexams($filterparams);

        return $data;
    }

    public function recommendedprograms()
    {
        global $DB;
        $filterparams  = $this->recommendedprogram(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['globalinput'] = $this->global_filter($filterparams);
        $filterparams['programdetails'] = $this->recommendedprogram();
        $filterparams['q'] = $searchquery;
        $filterparams['widthclass'] = 'col-md-12';
        $filterparams['inputclasses'] = 'examssearchinput';
        $filterparams['placeholder'] = get_string('search_exam','local_exams');
        $data = $this->listofprograms($filterparams);

        return $data;
    }

    public function get_trainingofficalsrequests_view($filter=false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'trainingofficalsview','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'list');
        $options['methodName']='local_trainer_expert_request_view';
        $options['templateName']='local_userapproval/trainer_expert_request';
    
        $options = json_encode($options);

        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'trainingofficalsview',
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

    public function listof_trainer_expert_requests($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        echo $this->render_from_template('local_userapproval/listof_trainer_expert_requests', $filterparams);
    }    

    public function admin_assin_role_users($filterparams=false) {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'assignadmin','perPage' => 10, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_userapproval_assign_by_admin';
        $options['templateName']='local_userapproval/assign_by_admin';
        
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'assignadmin',
                'options' => $options,
                'dataoptions' => $dataoptions,
                'filterdata' => $filterdata,
        ];
        if($filterparams){
        
            return  $context;
        }else{
            return  $this->render_from_template('theme_academy/cardPaginate', $context);
        }
    }


    public function listofadminassign($filterparams) {
        global $DB, $PAGE, $OUTPUT;

   
        echo  $this->render_from_template('local_userapproval/assign_by_admin_list', $filterparams);
    }

    //Ramnajneyulu Added 

        public function get_catalog_fast_userenrol($filter = false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'manage_userenrol','perPage' => 10, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_fast_userapprovalenrolview';
        $options['templateName']='local_userapproval/fastuserenrol';

        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'manage_userenrol',
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

     public function listoffuserenrol($filterparams) {
        global $DB, $PAGE, $OUTPUT;
       
        echo  $this->render_from_template('local_userapproval/listofusers', $filterparams);
    }   
    public function get_switchrolescontent($roles) {
        global $DB, $PAGE, $OUTPUT;
        return $this->render_from_template('local_userapproval/customswitchroles', ['roles' => $roles]);
    }  


    public function individual_requestdata_page($filterparams=false) {
        global $CFG, $PAGE,$USER, $OUTPUT, $DB;

        $systemcontext = context_system::instance();
        $options = array('targetID' => 'individualrequestdata','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_userapproval_individualrequestdata';
        $options['templateName']='local_userapproval/requesteddata';
        
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'individualrequestdata',
                'options' => $options,
                'dataoptions' => $dataoptions,
                'filterdata' => $filterdata,
        ];
        if($filterparams){
            return  $context;
        }else{
            return $this->render_from_template('theme_academy/cardPaginate', $context);
        }   
    }

    
    public function listofrequesteddata($filterparams) {
        global $DB, $PAGE, $OUTPUT,$USER;

            $trainer=$DB->get_record_sql("SELECT id FROM {local_trainer_request} AS trainer WHERE trainer.userid=$USER->id AND trainer.status in(1,2,5) ORDER BY trainer.id DESC ");
            $expert= $DB->get_record_sql("SELECT id FROM {local_expert_request} AS expert WHERE expert.userid=$USER->id  AND expert.status in(1,2,5) ORDER BY expert.id DESC");
           
           
            $trainee_status=[];
          
         
            if(empty($trainer) && empty($expert)){
               
                $traineerequest_button=true;
            
            }
            else
            {
                
                $traineerequest_button=false;
    
            }

            $filterparams['hidebutton']=$traineerequest_button;
            $filterparams['statushidebutton']=$trainee_status;


        echo  $this->render_from_template('local_userapproval/listallrequests', $filterparams);
    }
}
