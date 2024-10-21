<?php

define('CLI_SCRIPT', true);
//require_once('../../../../config.php');
require_once 'lib.php';


global $DB,$USER,$CFG,$PAGE;
$PAGE->set_context(context_system::instance());



$failurereports = $DB->get_records_sql('SELECT @a:=@a+1 id,count(*) as count,apiname FROM {local_lmsws_fapayload},(SELECT @a:= 0) AS a where sts="F" group by apiname ORDER by id');



if(count($failurereports)!=0){

$mail = get_mail_obj_ready();


$from='df@faglobal.com';
$from_name='Admin';


	 $mail->Subject = get_string('email_notification_subject','local_lmsws'). date("d-M-Y");

   $mailcontent = new stdClass();
   $mailcontent->date = date("d-M-Y");
   $mailcontent->toname = get_string('tonamee','local_lmsws');
   $mailcontent->path = $CFG->wwwroot;
	  
	 $body = "<h3>Dear ".get_string('tonamee','local_lmsws').",</h3>
    <p>Greetings from fa team !!</p>
            <p>Here is the summary of FA fa API Failed Data as on ".date("d-M-Y")."</p>
            <table width='785' border='0' cellspacing='0' cellpadding='0' style='border:1px solid #cccccc; border-collapse: collapse;'>
              <tr style='background-color: #dddddd;'>
                <td style='border: 1px solid #dddddd;text-align: left;padding: 5px;'>Sl.No.</td>
                <td style='border: 1px solid #dddddd;text-align: left;padding: 5px;'> APIName</td>
                 <td style='border: 1px solid #dddddd;text-align: left;padding: 5px;'> Failed Data Count</td>
              </tr>";//get_string('mailbody','local_lmsws',$mailcontent,true);
              $i=1;
	foreach ($failurereports as  $failurereport) {
         $body.="<tr>
                <td style='border: 1px solid #dddddd;text-align: left;padding: 5px;'>".$i."</td>
                <td style='border: 1px solid #dddddd;text-align: left;padding: 5px;'>".$failurereport->apiname."</td>
                <td style='border: 1px solid #dddddd;text-align: left;padding: 5px;'>".$failurereport->count."</td>
              </tr>";  $i++;   
    }         
           $body.='</table>
           <p></p>
           <p>Ref Link:<a href="'.$CFG->wwwroot.'/local/lmsws/fa_payload_view.php">FA API Failure Data Details</a></p>
            <h3>Happy learning!</h3>
            <p>Warm regards,</p><p> fa Team<p>';//get_string('mailfooter','local_lmsws',$mailcontent,true);
	 $mail->Body = $body;
 
	
	 //send mail
   $supportuser = core_user::get_support_user();
   $subject = get_string('email_notification_subject','local_lmsws'). date("d-M-Y");
   $message = $body;
   $messagehtml = $message;//text_to_html($message, false, false, true);
   $from = new StdClass();
   $from->email='df@faglobal.com';
   $from->firstname='Admin';
   $from->lastname='fa';
   $from->name='Admin';


   $admin =  $DB->get_record('user',array('id'=>2));
   email_to_user($admin, $from, $subject, $message,$messagehtml);


}
