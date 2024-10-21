<?php
require_once('../../config.php');
global $DB, $CFG, $OUTPUT,$USER, $PAGE;
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$returnurl = new moodle_url('/local/userapproval/iamregistration.php');
$PAGE->requires->jquery();
$PAGE->requires->js('/auth/registration/js/formsubmission.js');
$PAGE->set_url('/local/userapproval/iamregistration.php');
$PAGE->set_title(get_string('iamregistration', 'local_userapproval'));
$PAGE->set_heading(get_string('iamregistration', 'local_userapproval'));
$iscompletedprofile = $DB->record_exists_sql("SELECT * FROM {local_users} WHERE userid=:userid AND usersource=:usersource AND email !='' AND phone1 !=''",['userid'=>$USER->id,'usersource'=>'IAM']);
if($iscompletedprofile){
  redirect($CFG->wwwroot);
}
echo $OUTPUT->header();
$registration = new local_userapproval\form\newiamregistration(null, array(''), 'post', '', null, true,(array)data_submitted());

if ($registration->is_cancelled()) {
  redirect($CFG->wwwroot . '/local/userapproval/iamregistration.php');
} else if ($data = $registration->get_data()) {
  $approveduserid = $data->userid;
  $data->id = $DB->get_field('local_users','id',['userid'=>$approveduserid]);
  $data->id_number = $data->idnumber;
  $data->idtype = $data->id_type;
  $manageuser = new \local_userapproval\action\manageuser();
  try{
    $transaction = $DB->start_delegated_transaction();
    $data->nationalitycountryid =  $data->nationalitycountryid ? $data->nationalitycountryid : 2; 
    $data->password = hash_internal_user_password($data->password);
    $data->notify ='yes';
    $updatecustomrecord = $manageuser->update_custom_user($data,$approveduserid);
    $updaterecord = $manageuser->user_update_user($data,$approveduserid);
    $traineeroleid = $DB->get_field('role','id' ,['shortname' => 'trainee']);
    if($data->existinsystem == 0) {
        $orgdata =$manageuser->get_user_org_info($approveduserid);  
        $data->id = 0;
        $data->country_code =966;
        $data->orgcode = $orgdata->orgcode;
        $data->licensekey = $orgdata->licensekey;

        // Checking Fast Settings(User Registration) is Enabled or not
        $accessstatus = (new \local_userapproval\local\fast_service)->access_resisteruser_service();
        if ($accessstatus) {
            $response =   (new \local_userapproval\local\fast_service)->register_user($data);
            if(COUNT($response->errors) > 0 ){
                $error = json_encode($response->errors, JSON_UNESCAPED_UNICODE);
                $e = new moodle_exception($error);
            } 
            if(COUNT($response->messages) > 0 ){
                $messages = json_encode($response->messages, JSON_UNESCAPED_UNICODE);
                $e = new moodle_exception($messages);
            } 
        }
    }
    if(empty($e) || !($e instanceof moodle_exception)) {
      role_assign($traineeroleid, $approveduserid, $systemcontext);
      $transaction->allow_commit();
      redirect($CFG->wwwroot);
    }else{
      $data->status = "-1";
      $failed_masterdata['data'] = $data;
      $failed_masterdata['e'] = $e;
      $transaction->rollback($e);
    }
  } catch(moodle_exception | Exception | dml_transaction_exception $e){
    if(!$transaction->is_disposed()){
      $data->status = "-1";
      $failed_masterdata['data'] = $data;
      $failed_masterdata['e'] = $e;
      $transaction->rollback($e);
    }
  }
  if(!empty($failed_masterdata)){
    (new \local_userapproval\local\fast_service)->add_update_service($failed_masterdata['data']);
    throw new moodle_exception($failed_masterdata['e']->errorcode);
  } 
}
$registration->display();
echo $OUTPUT->footer();


