<?php

class tool_product_renderer extends plugin_renderer_base{

	public function add_to_cart( $item, $category ){

		global $DB;

		$product = $DB->get_record('tool_products', array('referenceid' => $item, 'category' => $category));
		$args = array(
			'product'		=>	$product,
			'is_loggedin'	=>	isloggedin()
		);
		return  $this->render_from_template('tool_product/cart/add', $args);
	}

	public function cart_page(){
		return $this->render_from_template('tool_product/cart/index', array());
	}

	public function checkout_page(){

        global $USER,$CFG,$DB;


        $submitteddata=(array)data_submitted();


        $systemcontext = context_system::instance();


        $sql = "SELECT ra.id
                  FROM {role_assignments} ra, {role} r, {context} c
                 WHERE ra.userid =:userid
                       AND ra.roleid = r.id
                       AND ra.contextid = c.id
                       AND ra.contextid =:contextid AND r.shortname !='trainee' ";

        $roles=$DB->record_exists_sql($sql ,array('userid'=>$USER->id,'contextid'=>$systemcontext->id));


        if(!isset($submitteddata['tablename']) && $roles){

            $data=[
                 'message_title'=>get_string('message_accessdenied', 'tool_product'),
                 'message_body'=>get_string('message_accesspermission', 'tool_product'),
                 'message_url'=>$CFG->wwwroot.'/my/index.php',
                 'message_footer'=>get_string('message_accessdeniedfooter', 'tool_product'),
              ];

            return $this->render_from_template('tool_product/checkout/noaccessdialogbox',$data);

        }else{

            $submitteddata=array_filter($submitteddata);


            return $this->render_from_template('tool_product/checkout/index', array('formdata'=>json_encode($submitteddata)));
        }
		
	}

	public function login_button(){
		return $this->render_from_template('tool_product/login-btn', array());
	}
	/**
     * Display the trainingprogram financialpayments
     * @return string The text to render
     */
    public function get_post_financialpayments() {
        global $CFG, $OUTPUT,$PAGE,$USER;

        global $CFG;
    	require_once($CFG->dirroot . '/admin/tool/product/lib.php');

        $context = context_system::instance();


        if((has_capability('tool/products:managefinancialpayments', $context)) || (has_capability('tool/products:viewfinancialpayments', $context)) || (has_capability('local/organization:manage_communication_officer', $context)) || (has_capability('local/organization:manage_financial_manager',$context))){

            $filterdata = json_encode(array());

            $options = array('targetID' => 'viewfinancialpayments','perPage' => 10, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'table');

            $options['targetID']='viewfinancialpayments';
            $options['methodName']='tool_product_get_post_financialpayments';
            $options['templateName']='tool_product/listfinancialpayments';

            $cardoptions = json_encode($options);
            $cardparams = array(
                'targetID' => 'viewfinancialpayments',
                'options' => $cardoptions,
                'dataoptions' => $cardoptions,
                'filterdata' => $filterdata,
                'tablename'=>'tool_org_order_payments'
            );
            $fncardparams=$cardparams;
            $financialpaymentsmform = financialpayments_filters_form($cardparams);
  

            $cardparams = $fncardparams+array(
                'contextid' => $context->id,
                'plugintype' => 'local',
                'plugin_name' =>'trainingprogram',
                'cfg' => $CFG,
                'filterform' => $financialpaymentsmform->render());

            return  $this->render_from_template('tool_product/viewfinancialpayments', $cardparams);

        }
        else{

            return "<div class='alert alert-danger'>" . get_string('nofinancialpaymentspermission', 'tool_product') . "</div>";
        }
    }
    public function lis_post_financialpayments($stable,$filterdata=null) {

        global $USER,$DB,$CFG;

        $systemcontext = context_system::instance();
        $getfinancialpayments = (new \tool_product\product)::get_post_financialpayments($stable,$filterdata);
        $financialpayments=array_values($getfinancialpayments['payments']);

        krsort($financialpayments);


        $row = array();

        $currentlang= current_language();


        $totalcost=0;

        $usedseats=array();

        $approvedseats=array();

        foreach ($financialpayments as $list) {
            $record = array();

            $record['id']=$list->id;
              
            $record['trainingname']=self::product_viewlink($list);

            if(!is_siteadmin() && has_capability('local/organization:manage_communication_officer',$systemcontext)) {

                $record['costview']=false;

            } else {

                $record['costview']=true;
            }

            $record['cost']=number_format($list->amount);

            $totalcost=$totalcost+$list->amount;

            if( $currentlang == 'ar'){

                $fullname="concat(lc.firstnamearabic,' ',lc.lastnamearabic) as fullname";

                $orgfullname='org.fullnameinarabic as orgname';

            }else{

                $fullname="concat(u.firstname,' ',u.lastname) as fullname";

                $orgfullname='org.fullname as orgname';
            }


            $sql="SELECT u.id,$fullname,$orgfullname 
                FROM {user} AS u 
                JOIN {local_users} lc ON lc.userid = u.id
                JOIN {local_organization} org ON org.id = lc.organization
                WHERE  u.id=:orguserid ";

            $user=$DB->get_record_sql($sql,array('orguserid'=>$list->orguserid));

            $record['fieldid']=$list->fieldid;

            $record['organizationname']=($user) ? $user->orgname : 'NA';

            $record['orgoffcialname']= ($user) ? $user->fullname : 'NA';

            $record['orguserid']= ($user) ? $orguserid : 0;

            $record['mode']=get_string($list->paymenttype,'tool_product');

            $record['duedate']=userdate($list->availablefrom, get_string('strftimedatefullshort', 'langconfig'));

            $record['paymentsupdate']=false;

            if((has_capability('tool/products:managefinancialpayments', $systemcontext) || has_capability('local/organization:manage_financial_manager',$systemcontext)) && $list->paymenttype == 'postpaid'){

                $record['paymentsupdate']=true;

            }

            $record['sendemailactionview']=false;

            if(is_siteadmin() || has_capability('local/organization:manage_communication_officer',$systemcontext) && !empty($user->fullname)) {

                $record['sendemailactionview']=true;

            }

             $record['sendemailurl']= $CFG->wwwroot.'/admin/tool/product/sendemailtoorgofficial.php?id='.$list->id;

            $record['purchasedseats']=$list->purchasedseats;


            if($list->tablename == 'tp_offerings') {

                $offering_starttime = $DB->get_field_sql('SELECT time FROM {tp_offerings} WHERE id = '.$list->fieldid.'');

                $starttime = gmdate("H:i",$offering_starttime);
                $starttimemeridian = gmdate('a',$offering_starttime);

                if($lang == 'ar') {
                    $startmeridian = ($starttimemeridian == 'am')? 'صباحا':'مساءً';
                    
                } else {
                    $startmeridian = ($starttimemeridian == 'am')? 'AM':'PM';
                }

                $record['usedseats']=(new \local_trainingprogram\local\trainingprogram())->get_erolled_seats($list->fieldid,true,$list->orguserid);

                $record['startdate'] = userdate($list->availablefrom, get_string('strftimedatemonthabbr', 'langconfig')).' '.$starttime.''.$startmeridian;

                $endtime = gmdate("H:i",$list->availableto);
                $endtimemeridian = gmdate('a',$list->availableto);

                if($lang == 'ar') {
                    $endtmeridian = ($endtimemeridian == 'am')? 'صباحا':'مساءً';
                    
                } else {
                    $endtmeridian = ($endtimemeridian == 'am')? 'AM':'PM';
                }
                
                $record['enddate'] = userdate($list->availableto, get_string('strftimedatemonthabbr', 'langconfig')).' '.$endtime.''.$endtmeridian;

            } elseif($list->tablename == 'hall_reservations') {

                $record['usedseats']= (new \local_exams\local\exams())->entity_enrolled($list->fieldid, $list->orguserid);

                $starttime = date("h:i",$list->availablefrom);
                $starttimemeridian = date('a',$list->availablefrom);

                if($lang == 'ar') {
                    $startmeridian = ($starttimemeridian == 'am')? 'صباحا':'مساءً';
                    
                } else {
                    $startmeridian = ($starttimemeridian == 'am')? 'AM':'PM';
                }

                $record['startdate'] = userdate($list->availablefrom, get_string('strftimedatemonthabbr', 'langconfig')).' '.$starttime.' '.$startmeridian;
                
                $endtime = gmdate("H:i",$list->availableto);
                $endtimemeridian = gmdate('a',$list->availableto);

                if($lang == 'ar') {
                    $endtmeridian = ($endtimemeridian == 'am')? 'صباحا':'مساءً';
                    
                } else {
                    $endtmeridian = ($endtimemeridian == 'am')? 'AM':'PM';
                }
                
                $record['enddate'] = userdate($list->availableto, get_string('strftimedatemonthabbr', 'langconfig')).' '.$endtime.''.$endtmeridian;

            } elseif($list->tablename == 'local_events') {

                $record['usedseats']= (new \local_events\events())->get_erolled_seats($list->fieldid,$list->orguserid);

                $record['startdate'] = userdate($list->availablefrom, get_string('strftimedatemonthabbr', 'langconfig'));
                
                $record['enddate'] = userdate($list->availableto, get_string('strftimedatemonthabbr', 'langconfig'));

            } else {

                $record['usedseats']= 0;

                $record['startdate'] = userdate($list->availablefrom, get_string('strftimedatemonthabbr', 'langconfig'));

                $record['enddate'] = userdate($list->availableto, get_string('strftimedatemonthabbr', 'langconfig'));
            }



            $storeseats=$usedseats[$list->orguserid][$list->tablename][$list->fieldid];
            
            if(!isset($storeseats)){

                $storeseats=$usedseats[$list->orguserid][$list->tablename][$list->fieldid]=$record['usedseats'];

            }

            $reaminingseats=$storeseats-$record['purchasedseats'];

            if($reaminingseats > 0){

                $record['usedseats']=$record['purchasedseats'];

            }else{

                $record['usedseats']=$storeseats;

            }

            $usedseats[$list->orguserid][$list->tablename][$list->fieldid]=$storeseats-$record['usedseats'];



            $record['approvedseats']=(new \tool_product\product)->approvedseats_check($list->tablename,$list->fieldname, $list->fieldid, $list->orguserid);

            $storeapprovedseats=$approvedseats[$list->orguserid][$list->tablename][$list->fieldid];
            
            if(!isset($storeapprovedseats)){

                $storeapprovedseats=$approvedseats[$list->orguserid][$list->tablename][$list->fieldid]=$record['approvedseats'];

            }

            $reaminingapprovedseats=$storeapprovedseats-$record['purchasedseats'];

            if($reaminingapprovedseats > 0){

                $record['approvedseats']=$record['purchasedseats'];

            }else{

                $record['approvedseats']=$storeapprovedseats;

            }

            $approvedseats[$list->orguserid][$list->tablename][$list->fieldid]=$storeapprovedseats-$record['approvedseats'];


            if( date('Y-m-d',$list->availablefrom) < date('Y-m-d') ) {

                $record['trainingstatus'] = 'alert alert-warning';

            }elseif( date('Y-m-d',$list->availablefrom) > date('Y-m-d') ) {

                $record['trainingstatus'] = 'alert alert-info';

            }elseif( date('Y-m-d',$list->availablefrom) == date('Y-m-d')) {

                $record['trainingstatus'] = 'alert alert-danger';

            }else{

                $record['trainingstatus'] =  'bg-theme_dark';

            }
      
            $row[] = $record;
         }
        $data=array_values($row);

        krsort($data);

        return compact('data', 'totalcost');
    }
    /**
     * Display the trainingprogram orderapproval
     * @return string The text to render
     */
    public function get_orders_approval() {
        global $CFG, $OUTPUT,$PAGE,$USER;

        global $CFG;
        require_once($CFG->dirroot . '/admin/tool/product/lib.php');

        $context = context_system::instance();


        if((has_capability('tool/products:manageorderapproval', $context)) || (has_capability('tool/products:vieworderapproval', $context))){

            $filterdata = json_encode(array());

            $options = array('targetID' => 'vieworderapproval','perPage' => 10, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'table');

            $options['targetID']='vieworderapproval';
            $options['methodName']='tool_product_get_orders_approval';
            $options['templateName']='tool_product/listorderapproval';

            $cardoptions = json_encode($options);
            $cardparams = array(
                'targetID' => 'vieworderapproval',
                'options' => $cardoptions,
                'dataoptions' => $cardoptions,
                'filterdata' => $filterdata,
                'tablename'=>'tool_org_order_payments'
            );
            $fncardparams=$cardparams;
            $orderapprovalmform = orders_approval_filters_form($cardparams);
  

            $cardparams = $fncardparams+array(
                'contextid' => $context->id,
                'plugintype' => 'local',
                'plugin_name' =>'trainingprogram',
                'cfg' => $CFG,
                'filterform' => $orderapprovalmform->render());

            return  $this->render_from_template('tool_product/vieworderapproval', $cardparams);

        }
        else{

            return "<div class='alert alert-danger'>" . get_string('noorderapprovalpermission', 'tool_product') . "</div>";
        }
    }
    public function lis_order_approval($stable,$filterdata=null) {

        global $USER,$DB;

        $systemcontext = context_system::instance();
        $getorderapproval = (new \tool_product\product)::get_orders_approval($stable,$filterdata);
        $orderapproval=array_values($getorderapproval['orders']);

        $row = array();

        $currentlang= current_language();
    
        foreach ($orderapproval as $list) {
            $record = array();

            $record['id']=$list->id;

            $record['trainingname']=self::product_viewlink($list);


            if( $currentlang == 'ar'){

                $fullname="concat(lc.firstnamearabic,' ',lc.lastnamearabic) as fullname";

                $orgfullname='org.fullnameinarabic as orgname';

            }else{

                $fullname="concat(u.firstname,' ',u.lastname) as fullname";

                $orgfullname='org.fullname as orgname';
            }


            $sql="SELECT u.id,$fullname,$orgfullname 
                FROM {user} AS u 
                JOIN {local_users} lc ON lc.userid = u.id
                JOIN {local_organization} org ON org.id = lc.organization
                WHERE  u.id=:orguserid ";

            $user=$DB->get_record_sql($sql,array('orguserid'=>$list->orguserid));      


            $record['organizationname']=($user) ? $user->orgname : 'NA';

            $record['orgoffcialname']= ($user) ? $user->fullname : 'NA';


            $record['duedate']=userdate($list->availablefrom, get_string('strftimedatefullshort', 'langconfig'));

            $record['purchasedseats']=$list->purchasedseats;

            $record['approvalseats']=$list->approvalseats;


            $record['approvalsupdate']=false;

            if(has_capability('tool/products:manageorderapproval', $systemcontext) && ($list->purchasedseats > $list->approvalseats)){

                $record['approvalsupdate']=true;

            }
      
            $row[] = $record;
         }
        return array_values($row);
    }
    public function lis_org_purchases($stable,$filterdata=null) {

        global $USER,$DB,$CFG;

        $traineeeid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));


        $systemcontext = context_system::instance();
        $getorderapproval = (new \tool_product\product)::get_org_purchases($stable,$filterdata);


        $orderapproval=array_values($getorderapproval['orders']);

        $row = array();


        $stable = new \stdClass();
        $stable->thead = true;

    
        foreach ($orderapproval as $list) {

            $list->traineeeid=$traineeeid;

            $record = array();

            $record['id']=$list->id;

            $record['trainingname']=self::product_orgviewlink($list);

            $record['purchasedseats']=$list->purchasedseats;

            $record['approvalseats']=$list->approvalseats;

           $lang= current_language();

            if($list->tablename == 'tp_offerings') {
                $offering_starttime = $DB->get_field_sql('SELECT time FROM {tp_offerings} WHERE id = '.$list->fieldid.'');
                $trainingmethod = $DB->get_field_sql('SELECT trainingmethod FROM {tp_offerings} WHERE id = '.$list->fieldid.'');
                $programid = $DB->get_field_sql('SELECT trainingid FROM {tp_offerings} WHERE id = '.$list->fieldid.'');
                $starttime = gmdate("H:i",$offering_starttime);
                $starttimemeridian = gmdate('a',$offering_starttime);
                if($lang == 'ar') {
                    $startmeridian = ($starttimemeridian == 'am')? ' صباحا':' مساءً';
                    
                } else {
                    $startmeridian = ($starttimemeridian == 'am')? ' AM':' PM';
                }
                $record['availableseats']= (new \local_trainingprogram\local\trainingprogram())->get_available_seats($list->fieldid);
                $usedseats =(new \local_trainingprogram\local\trainingprogram())->get_erolled_seats($list->fieldid);
                $record['usedseats']=html_writer::tag('a', $usedseats,array('href' =>$CFG->wwwroot. '/local/trainingprogram/programenrolleduserslist.php?programid='.$programid));
                $record['startdate'] = userdate($list->availablefrom, get_string('strftimedatemonthabbr', 'langconfig')).' '.$starttime.''.$startmeridian;
                $record['enddate'] =($trainingmethod =='elearning')? '--': userdate($list->availableto, get_string('strftimedatemonthabbr', 'langconfig'));
                $record['type'] = false;
                $record['offeringview'] = true;
            } elseif($list->tablename == 'hall_reservations') {
                $record['availableseats'] = $list->availableseats;
                $record['usedseats']= (new \local_exams\local\exams())->entity_enrolled($list->fieldid, $USER->id);
                $starttime = date("h:i",$list->availablefrom);
                $starttimemeridian = date('a',$list->availablefrom);
                if($lang == 'ar') {
                    $startmeridian = ($starttimemeridian == 'am')? ' صباحا':' مساءً';
                    
                } else {
                    $startmeridian = ($starttimemeridian == 'am')? ' AM':' PM';
                }

                $record['startdate'] = userdate($list->availablefrom, get_string('strftimedatemonthabbr', 'langconfig')).' '.$starttime.' '.$startmeridian;
                $record['enddate'] = userdate($list->availableto, get_string('strftimedatemonthabbr', 'langconfig'));
                $record['type'] = 'exam';
                $record['offeringview'] = false;
            } elseif($list->tablename == 'local_events') {
                $record['availableseats'] = $list->availableseats;
                $record['usedseats']= (new \local_events\events())->get_erolled_seats($list->fieldid, $USER->id);
                $record['startdate'] = userdate($list->availablefrom, get_string('strftimedatemonthabbr', 'langconfig'));
                $record['enddate'] = userdate($list->availableto, get_string('strftimedatemonthabbr', 'langconfig'));
                $record['type'] = false;
                $record['offeringview'] = false;
            } else {
                $record['availableseats'] = $list->availableseats;
                $record['usedseats']= $list->approvalseats - $list->availableseats;
                $record['startdate'] = userdate($list->availablefrom, get_string('strftimedatemonthabbr', 'langconfig'));
                $record['enddate'] = userdate($list->availableto, get_string('strftimedatemonthabbr', 'langconfig'));
                $record['type'] = false;
                $record['offeringview'] = false;
            }
            $record['typeid'] = $list->trainingid;

            $record['enrollbtn']=self::product_enrollseats($list);

            if( date('Y-m-d H:i:s ', $list->availablefrom) >= date('Y-m-d H:i:s') ) {

                $record['timelimit'] = true;

            } else {

                $record['timelimit'] = false;

            }

            $row[] = $record;
         }
        return array_values($row);
    }
    public function product_viewlink($list) {

        global $USER;

        $context=context_system::instance();


        switch ($list->tablename) {
            case 'tp_offerings':
              
                $trainingurl = new moodle_url('/local/trainingprogram/programcourseoverview.php',array('programid'=>$list->trainingid));

                $trainingname= html_writer::link($trainingurl, ucwords($list->trainingname));
                break;

            case 'hall_reservations':

                $trainingurl = new moodle_url('/local/exams/examreservations.php',array('examid'=>$list->trainingid));

                $trainingname= html_writer::link($trainingurl, ucwords($list->trainingname));
                break;

            case 'local_events':

                $trainingurl = new moodle_url('/local/events/view.php',array('id'=>$list->trainingid));

                $trainingname= html_writer::link($trainingurl, ucwords($list->trainingname));
                break;
            
            default:
                $trainingname=ucwords($list->trainingname);
                break;
        }

        return $trainingname;

    }
    public function product_orgviewlink($list) {

        global $USER,$DB;


        switch ($list->tablename) {
            case 'tp_offerings':

                $trainingurl = new moodle_url('/local/trainingprogram/programcourseoverview.php',array('programid'=>$list->trainingid));

                $programcourseid = $DB->get_field('local_trainingprogram', 'courseid', array('id' => $list->trainingid));

                if($programcourseid){

                    $trainingurl = new moodle_url('/course/view.php',array('id'=>$programcourseid));

                }

                $trainingname= html_writer::link($trainingurl, ucwords($list->trainingname));
                break;

            case 'hall_reservations':

                $trainingurl = new moodle_url('/local/exams/examreservations.php',array('examid'=>$list->trainingid));

                $exams = $DB->get_record('local_exams', array('id' => $list->trainingid));

                $moduleid = $DB->get_field('modules', 'id', ['name' => 'quiz']);
                $quizid = $DB->get_field('course_modules', 'id', ['course' => $exams->courseid, 'module' => $moduleid, 'instance' => $exams->quizid]);

                if($quizid){

                    $trainingurl = new moodle_url('/mod/quiz/view.php',array('id'=>$quizid));

                }

                $trainingname= html_writer::link($trainingurl, ucwords($list->trainingname));
                break;

            case 'local_events':

                $trainingurl = new moodle_url('/local/events/view.php',array('id'=>$list->trainingid));

                $trainingname= html_writer::link($trainingurl, ucwords($list->trainingname));
                break;
            
            default:
                $trainingname=ucwords($list->trainingname);
                break;
        }

        return $trainingname;

    }
    public function product_enrollseats($list) {

        global $USER,$DB,$OUTPUT;



        switch ($list->tablename) {

            case 'local_trainingprogram':

                $trainingurl = new moodle_url('/local/trainingprogram/programenrollment.php',array('programid'=>$list->trainingid,'roleid'=>$list->traineeeid,'offeringid'=>$list->fieldid));

                $enrollseatsbtn= html_writer::link($trainingurl,get_string('enrollbtn',  'tool_product'));

               
                break;

            case 'tp_offerings':

                $trainingurl = new moodle_url('/local/trainingprogram/programcourseoverview.php',array('programid'=>$list->trainingid, 'action' => 'enrol'));

                $enrollseatsbtn= html_writer::link($trainingurl,get_string('enrollbtn',  'tool_product'));

                
                break;

            case 'local_exams':

                $trainingurl = new moodle_url('/local/exams/examenrollment.php',array('examid'=>$list->trainingid));

                if($list->totalapprovalseats > 0){

                    $enrollseatsbtn=(new \tool_product\product)->get_button_order_seats($label, $list->tablename, $list->fieldname, $list->fieldid, $list->availableseats,$parentfieldid=0);

                }else{

                    $enrollseatsbtn= html_writer::link($trainingurl,get_string('enrollbtn',  'tool_product'));
                }

                break;
            case 'hall_reservations':
                $trainingurl = new moodle_url('/local/exams/examenrollment.php',array('examid'=>$list->trainingid, 'hallreservationid' => $list->fieldid));

                if($list->totalapprovalseats > 0){echo "aaa";

                    $enrollseatsbtn=(new \tool_product\product)->get_button_order_seats($label, $list->tablename, $list->fieldname, $list->fieldid, $list->availableseats,$parentfieldid=0);

                }else{

                    $enrollseatsbtn= html_writer::link($trainingurl,get_string('enrollbtn',  'tool_product'));
                }

                break;                

            case 'local_events':

                $trainingurl = new moodle_url('/local/events/attendees.php',array('id'=>$list->trainingid));

                if($list->totalapprovalseats > 0){

                    $enrollseatsbtn=(new \tool_product\product)->get_button_order_seats($label, $list->tablename, $list->fieldname, $list->fieldid, $list->availableseats,$parentfieldid=0);

                }else{

                    $enrollseatsbtn= html_writer::link($trainingurl,get_string('enrollbtn',  'tool_product'));
                }

                break;
            default:
                $trainingurl = new moodle_url('#');

                $enrollseatsbtn= html_writer::link($trainingurl,get_string('enrollbtn',  'tool_product'));
                break;
        }

        return $enrollseatsbtn;

    }

    public function get_mypayments($filter = false) {
        $systemcontext = context_system::instance();
        if(!is_siteadmin() && has_capability('local/organization:manage_trainee',$systemcontext)) {
            $options = array('targetID' => 'viewmypayments','perPage' => 10, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'table');
            $options['targetID']='viewmypayments';
            $options['methodName']='tool_product_get_mypayments';
            $options['templateName']='tool_product/mypayments';
            $options = json_encode($options);
            $filterdata = json_encode(array());
            $dataoptions = json_encode(array('contextid' => $systemcontext->id));
            $cardoptions = json_encode($options);
            $context = [
                    'targetID' => 'viewmypayments',
                    'options' => $options,
                    'dataoptions' => $dataoptions,
                    'filterdata' => $filterdata,
            ];
            if($filter) {
                return  $context;
            } else {
                return  $this->render_from_template('theme_academy/cardPaginate', $context);
                
            }
        } else if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
            return  $this->render_from_template('tool_product/viewuserpayments', $systemcontext);
        }
    }

    public function lis_mypayments($stable,$filterdata=null) {
        global $USER,$DB;
        $systemcontext = context_system::instance();
        $getpaymentlist = (new \tool_product\product)::get_listof_mypayments($stable,$filterdata);
        $paymentslist = array_values($getpaymentlist['payments']);
       
        $row = array();
        foreach($paymentslist as $list) {
            $record = array();
            $record['id'] = $list->id;
            $product_data = unserialize(base64_decode($list->productdata));

            $array = array_column($product_data['items'], 'name');
            $productname = implode(', ', $array);
            $record['trainingname'] = $productname;
            if(!is_siteadmin() && has_capability('local/organization:manage_organizationofficial',$systemcontext)) {
                $quantity = true;
            } else{
                $quantity = false;
            }
            /*if($product_data['category']=='wallet') {
                continue;
            }*/
            $record['product_data'] = $product_data;
            $record['quantity'] = $quantity;
            $record['purchasedseats'] = $product_data['formdata']['selectedseats'];//$quantity;
            $record['total_price'] = $product_data['total_price'];
            $record['discount_price'] = $product_data['discount_price'];
            $record['total_discount'] = $product_data['total_discount'];
            $record['taxes'] = $product_data['taxes'];
            $record['total'] = $product_data['total'];
            $record['total_purchases'] = $product_data['total_purchases'];
            $record['timeupdated'] = userdate($list->timeupdated, get_string('strftimedatemonthabbr', 'core_langconfig')); //date('d-m-Y',$list->timeupdated);
            if($list->statuscode == 1) {
                $record['statuscode']  = get_string('pending', 'tool_product');
            } else if($list->statuscode == 2) {
                $record['statuscode']  = get_string('authorised', 'tool_product');
            } else if ($list->statuscode == 3) {
                $record['statuscode']  = get_string('paid', 'tool_product');
            } else if($list->statuscode < 0) {
                $record['statuscode']  = get_string('canceldeclined', 'tool_product');
            }
            $row[] = $record;
          
        }
        //var_dump($row); exit;
        return array_values($row);
    }

    public function get_mywallet() {
        global $USER, $DB;
        $data = $DB->get_record('local_orgofficial_wallet',['userid' => $USER->id]);
        $walletdata = [];
        if($data) {
           $walletdata['amount'] = $data->wallet;
           $walletdata['timecreated'] = userdate($data->timecreated, get_string('strftimedatemonthabbr', 'core_langconfig'));
        }
        return $this->render_from_template('tool_product/mywallet', $walletdata);
    }

    public function list_orgpayments($stable,$filterdata) {
        global $USER,$DB;
        $systemcontext = context_system::instance();
        $getpaymentlist = (new \tool_product\product)::get_listof_orgpayments($stable,$filterdata);
        $paymentslist = array_values($getpaymentlist['payments']);
        $statusarray = array(0 => get_string('pending', 'tool_product'),
        1 => get_string('paid','tool_product'));
        $typearray = ['prepaid' => get_string('prepaid', 'tool_product'),
        'postpaid' => get_string('postpaid', 'tool_product'),
        'telr' => get_string('telr', 'tool_product')];
        $row = array();
        foreach($paymentslist as $list) {
            $record = array();
            $record['id'] = $list->id;
            $record['trainingname'] = format_text($list->trainingname, FORMAT_HTML);
            $record['total_price'] = $list->originalprice;
            $record['discount_price'] = $list->discountprice;
            $record['taxes'] = $list->taxes;
            $record['total'] = $list->amount;
            $record['purchasedseats'] = $list->purchasedseats;
            $record['statuscode']  = $statusarray[$list->paymentapprovalstatus];
            $record['paymentmethod']  = $typearray[$list->paymenttype];
           
            $record['timecreated'] = userdate($list->timecreated, get_string('strftimedatemonthabbr', 'core_langconfig'));
            $row[] = $record;
        }
       // var_dump($list->paymenttype); exit;
        return array_values($row);
    }

}
