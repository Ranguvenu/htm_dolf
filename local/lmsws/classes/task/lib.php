<?php

date_default_timezone_set('Asia/Kolkata');

require_once(dirname(__FILE__).'/../../../../config.php');
//require_once($CFG->wwwroot.'/phpexcel/PHPExcel.php');
//require_once($CFG->libdir.'/phpmailer/moodle_phpmailer.php');
require_once($CFG->libdir . '/../local/lmsws/lib/class.phpmailer.php');
require_once ($CFG->libdir . '/../local/lmsws/lib/class.smtp.php');

//@@@@@@@@@@@@@@@@@@  Function Related to excel files @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
function fill_header(&$objPHPExcel, $header, $color='F28A8C'){

	//Find the range of cells for which styling has to be applied
	$first_letter = PHPExcel_Cell::stringFromColumnIndex(0);
	$last_letter = PHPExcel_Cell::stringFromColumnIndex(count($header)-1);
	$header_range = "{$first_letter}1:{$last_letter}1";

	//fill $header into excel starting from cell A1
	$objPHPExcel->getActiveSheet()->fromArray($header, NULL, 'A1');

	//Color the header
	$objPHPExcel->getActiveSheet()->getStyle($header_range)->getFill()->applyFromArray(array(
			'type' => PHPExcel_Style_Fill::FILL_SOLID,
			'startcolor' => array(
					'rgb' => $color
			)
	));

	//Bold the header
	$objPHPExcel->getActiveSheet()->getStyle($header_range)->getFont()->setBold(true);
}

function fill_data( &$objPHPExcel, $data){
	//Start filling data from row 2 since row 1 has heading
	$row_count = 2;

	foreach ($data as $row){
		$objPHPExcel->getActiveSheet()->fromArray((array)$row, null, "A" . $row_count++);
	}

}

function fillexcel( &$objPHPExcel, $header, $data){

	//fill the header data and colour it with any convenient color
	fill_header($objPHPExcel, $header);

	//Fill the actual data into excel
	fill_data($objPHPExcel, $data);

}

/*
 * fill the excel object with all data and return the object again
 */
function create_excel_obj( &$objPHPExcel , $header, $result){
	fillexcel( $objPHPExcel, $header, $result);
	return $objPHPExcel;
}

//@@@@@@@@@@@@@@@@@@  Functions Related to email @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

function get_mail_obj_ready(){
	global $CFG;
	$mail = new PHPMailer;
	$mail->isSMTP();

	//contains both hostname and port address. Separate it out
	$host_port = explode(':', $CFG->smtphosts);
	$mail->Host = $host_port[0];
	$mail->Port = /*465;*/$host_port[1];
	$mail->SMTPAuth = true;
	$mail->Username = $CFG->smtpuser;
	$mail->Password = $CFG->smtppass;
	$mail->SMTPSecure = $CFG->smtpsecure;//'ssl';//'tls';
	$mail->setFrom($CFG->noreplyaddress, 'fa Admin');
	$mail->isHTML(true);
	$mail->SMTPAutoTLS = false;
	$mail->Host = gethostbyname('smtp.gmail.com');
	$mail->SMTPDebug = 2;
	/*$mail->SMTPOptions = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
		    )
		);*/
	$mail->AltBody = 'Please use HTML supported mailing client';
	return $mail;
}

function send_mail($mail){
	if(!$mail->send()) {
		echo 'Message could not be sent.';
		echo 'Mailer Error: ' . $mail->ErrorInfo;
	}else{
		echo 'Message has been sent';
	}
}
