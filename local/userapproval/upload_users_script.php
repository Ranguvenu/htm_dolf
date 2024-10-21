<?php
require_once('../../config.php');
global $DB, $CFG, $OUTPUT,$USER, $PAGE;
$row = 0;
$skip_row_number = array("1");
$uploadrecords = '';
if(($usersfile = fopen("new_user_upload.csv", "r")) !== FALSE) {
	$allqueries='';
    $uploadrecords = '';
    while(($data = fgetcsv($usersfile))!== FALSE) {
    	$row++;	
    	if (in_array($row, $skip_row_number)){
		  continue; 
		}else{
            $uploadingdata = new stdClass();
			$uploadingdata->oldid = $data[0];
            $uploadingdata->firstname = $data[1];
            $uploadingdata->lastname = $data[2];
            $uploadingdata->firstnamearabic = $data[3];
            $uploadingdata->lastnamearabic = $data[4];
            $uploadingdata->middlenameen = $data[5];
            $uploadingdata->middlenamearabic = $data[6];
            $uploadingdata->thirdnameen = $data[7];
            $uploadingdata->thirdnamearabic = $data[8];
            $uploadingdata->dateofbirth = $data[9];
            $uploadingdata->username = $data[10];
            $uploadingdata->password = hash_internal_user_password($data[11]);
            $uploadingdata->email = strtolower($data[12]);
            $uploadingdata->gender = (strtolower($data[13]) == 'male')? 1: 2;;
            $uploadingdata->lang = (strlen(trim($data[14])) > 2 || empty(trim($data[14])))? 'ar' :strtolower(trim($data[14]));;
            $uploadingdata->nationality = (strlen(trim($data[15])) > 2 || empty(trim($data[15])))? 'SA' : strtoupper(trim($data[15]));
            $uploadingdata->phone1 = $data[17];
            if(!empty($data[18])){
                $organization = $DB->get_field_sql("SELECT id FROM {local_organization} where lower(shortname) ='".$data[18]."'");
                $uploadingdata->organization =  $organization;
            }else{
                $uploadingdata->organization = 0;
            }
            $idtype =array('1'=>'id','2'=>'passport','3'=>'saudiid','4'=>'residentialid');
            $idtype = array_keys($idtype, strtolower($data[19]));
            $uploadingdata->id_type = $idtype[0]?$idtype[0]:0;
            $uploadingdata->id_number =    !empty($data[20]) ?$data[20] : 0;

            if(!empty($data[21])){
                $sector = $DB->get_field_sql("SELECT id FROM {local_sector} where lower(code) ='".$data[21]."'");
                $uploadingdata->sector =  $sector;
            }else{
                $uploadingdata->sector  = null;
            }
            if(!empty($data[22])){
                $segment = $DB->get_field_sql("SELECT id FROM {local_segment} where lower(code) ='".$data[22]."'");
                $uploadingdata->segment =  $segment;
            }else{
                $uploadingdata->segment = null;
            }
            if(!empty($data[23])){
                $jobfamily = $DB->get_field_sql("SELECT id FROM {local_jobfamily} where lower(code) ='".$data[23]."'");
                $uploadingdata->jobfamily =  $jobfamily;
            }else{
                $uploadingdata->jobfamily = null;
            }
            if(!empty($data[24])){
                $jobrole = $DB->get_field_sql("SELECT id FROM {local_jobrole_level} where lower(code) ='".$data[24]."'");
                $uploadingdata->jobrole =  $jobrole;
            }else{
                $uploadingdata->jobrole = null;
            }
            $uploadingdata->city =(strlen(trim($data[25])) > 120 || empty(trim($data[25]))) ? 'Riyad' : $data[25];
            $uploadingdata->country = (strlen(trim($data[15])) > 2 || empty(trim($data[16])))? 'SA' : strtoupper(trim($data[15]));
            if(!is_null($data[26]) && !empty($data[26])) {
                $shortname =trim($data[26]);
                $role_id = $DB->get_field('role','id',array('shortname' =>$shortname));
                if($role_id) {
                    $uploadingdata->roleid = (int) $role_id;
                } else {
                    $uploadingdata->roleid = null;
                }
            } else {
                $uploadingdata->roleid = null;
            }
            $uploadingdata->timecreated = time();
            $uploadingdata->usercreated = $USER->id;
            $userinfo = $DB->get_record("user",array('username'=>trim($uploadingdata->username),'deleted' => 0));
            if($userinfo->id > 0) {
            
                $uploadingdata->id =  $DB->get_field('local_users','id',array('userid' =>$userinfo->id));;
                $approveduserid=$userinfo->id;
                $description= get_string('update_descption','local_userapproval',$userinfo);
                if($approveduserid > 0) {
                    $updatecustomrecord = (new \local_userapproval\action\manageuser)->update_custom_user($uploadingdata,$approveduserid);
                    $updaterecord = (new \local_userapproval\action\manageuser)->user_update_user($uploadingdata,$approveduserid,$uploadingdata->roleid); 
                    $insert_user_logs =(new \local_userapproval\action\manageuser)->local_users_logs('updated', 'userapproval', $description, $approveduserid);
                } else {
                    $updaterecord = (new \local_userapproval\action\manageuser)->update_register_user($uploadingdata);
                    $insert_user_logs =(new \local_userapproval\action\manageuser)->local_users_logs('updated', 'userapproval', $description, $approveduserid);
                } 

                $uploadrecords .=  'User ('.$uploadingdata->username.') updated successfully having id('.$approveduserid.')'.'<br>';
            } else {

                $custom_ntionalities = get_string_manager()->get_list_of_countries();
                $localusers_ntionalities =  array_flip((new \local_userapproval\action\manageuser)->get_list_of_nationalities());
                $submitted_nationality = $custom_ntionalities[$uploadingdata->nationality];
                $uploadingdata->nationalitycountryid =  $localusers_ntionalities[$submitted_nationality];
                $userid = (new \local_userapproval\action\manageuser)->create_user($uploadingdata, $uploadingdata->roleid);
                $insertrecord = (new \local_userapproval\action\manageuser)->create_custom_user($uploadingdata,$userid);
                $description= get_string('insert_descption','local_userapproval',$uploadingdata);
                $insert_user_logs =(new \local_userapproval\action\manageuser)->local_users_logs('registered', 'userapproval', $description, $userid);
                $uploadrecords .=  'New user ('.$uploadingdata->username.') created successfully having id('.$userid.')'.'<br>';
                
            }
        
		} 
    }
   fclose($usersfile);
   echo $uploadrecords .'<br>';
}


