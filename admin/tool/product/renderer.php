<?php
use local_exams\local\exams;
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

        $traineeroleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));


        // $sql = "SELECT ra.id
        //           FROM {role_assignments} ra, {role} r, {context} c
        //          WHERE ra.userid =:userid
        //                AND ra.roleid = r.id
        //                AND ra.contextid = c.id
        //                AND ra.contextid =:contextid AND r.shortname !='trainee' ";

        // $roles=$DB->record_exists_sql($sql ,array('userid'=>$USER->id,'contextid'=>$systemcontext->id));


        if(!isset($submitteddata['tablename']) && !user_has_role_assignment($USER->id,$traineeroleid,$sitecontext->id)){

            $data=[
                 'message_title'=>get_string('message_accessdenied', 'tool_product'),
                 'message_body'=>get_string('message_accesspermission', 'tool_product'),
                 'message_url'=>$CFG->wwwroot.'/my/index.php',
                 'message_footer'=>get_string('message_accessdeniedfooter', 'tool_product'),
              ];

            return $this->render_from_template('tool_product/checkout/noaccessdialogbox',$data);

        } else{

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
    	require_once($CFG->dirroot . '/admin/tool/product/lib.php');

        $context = context_system::instance();
        $mode = optional_param('mode', 1, PARAM_INT);

        if((has_capability('tool/products:managefinancialpayments', $context)) || (has_capability('tool/products:viewfinancialpayments', $context)) || (has_capability('local/organization:manage_communication_officer', $context)) || (has_capability('local/organization:manage_financial_manager',$context))){

            $filterdata = json_encode(array());

            $options = array('targetID' => 'viewfinancialpayments','perPage' => 10, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'table');

            $options['targetID']='viewfinancialpayments';
            $options['methodName']='tool_product_get_post_financialpayments';
            $options['templateName']='tool_product/listfinancialpayments';
            $options['mode'] = $mode;

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
            $record['name']=$list->trainingname;
            $record['trainingname']=self::product_viewlink($list);

            if(!is_siteadmin() && has_capability('local/organization:manage_communication_officer',$systemcontext)) {

                $record['costview']=false;

            } else {

                $record['costview']=true;
            }

            $record['cost']=number_format($list->payableamount);

            $totalcost=$totalcost+$list->amount;
            $fullname = (new \local_trainingprogram\local\trainingprogram())->user_fullname_case();
            $orgfullname=($currentlang == 'ar') ? 'org.fullnameinarabic as orgname' : 'org.fullname as orgname';

            $sql="SELECT u.id,$fullname,$orgfullname 
                FROM {user} AS u 
                JOIN {local_users} lc ON lc.userid = u.id
                JOIN {local_organization} org ON org.id = lc.organization
                WHERE  u.id=:orguserid ";

            $user=$DB->get_record_sql($sql,array('orguserid'=>$list->orguserid));

            $record['fieldid']=$list->fieldid;

            $record['organizationname']=($user) ? $user->orgname : 'NA';

            

            if($user){

                $record['orgoffcialname']= ($user) ? $user->fullname : 'NA';

            }else{

                $sql="SELECT u.id,concat(u.firstname,' ',u.lastname) as fullname
                FROM {user} AS u 
                WHERE  u.id=:userid ";

                $user=$DB->get_record_sql($sql,array('userid'=>$list->orguserid));

                $record['orgoffcialname']= ($user) ? $user->fullname : fullname($user);
            }

            $record['orguserid']= ($user) ? $list->orguserid : 0;

            $record['mode']=get_string($list->paymenttype,'tool_product');

            $record['duedate']= !empty($list->availablefrom) ? userdate($list->availablefrom, get_string('strftimedatefullshort', 'langconfig')) : '--';
            $record['paymentduedate']=$list->availablefrom;

            $record['paymentsupdate']=false;

            if((has_capability('tool/products:managefinancialpayments', $systemcontext) || has_capability('local/organization:manage_financial_manager',$systemcontext)) && $list->paymenttype == 'postpaid'){

                $record['paymentsupdate']=true;

            }

            $record['sendemailactionview']=false;

            if(is_siteadmin() || has_capability('local/organization:manage_communication_officer',$systemcontext) || has_capability('local/organization:manage_financial_manager',$systemcontext) && !empty($user->fullname)) {

                $record['sendemailactionview']=true;

            }

             $record['sendemailurl']= $CFG->wwwroot.'/admin/tool/product/sendemailtoorgofficial.php?id='.$list->id;

            $record['purchasedseats']=$list->purchasedseats;
            $approvalseats = $DB->get_field('tool_order_approval_seats', 'approvalseats', ['paymentid' => $list->id]);
            $record['approvalseats']=number_format($approvalseats);


            if($list->tablename == 'tp_offerings') {

                $tpoffering = $DB->get_record_sql('SELECT * FROM {tp_offerings} WHERE id = '.$list->fieldid.'');
                $record['referenceid']=$tpoffering->trainingid;

                $starttime = gmdate("H:i",$tpoffering->time);
                $starttimemeridian = gmdate('a',$tpoffering->time);

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
                $record['type'] = 'trainingprogram';

            } elseif($list->tablename == 'local_exam_profiles') {

                $record['usedseats']= (new \local_exams\local\exams())->entity_enrolled($list->fieldid, $list->orguserid);
                $record['referenceid']= $DB->get_field('local_exam_profiles', 'examid', ['id' => $list->fieldid]);

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
                $record['type'] = 'exam';

            } elseif($list->tablename == 'local_events') {
                $record['referenceid'] = $list->fieldid;
                $record['usedseats']= (new \local_events\events())->get_erolled_seats($list->fieldid,$list->orguserid);

                $record['startdate'] = userdate($list->availablefrom, get_string('strftimedatemonthabbr', 'langconfig'));
                
                $record['enddate'] = userdate($list->availableto, get_string('strftimedatemonthabbr', 'langconfig'));
                $record['type'] = 'event';

            } else {
                $record['referenceid'] = $list->fieldid;
                $record['usedseats']= 0;

                $record['startdate'] = userdate($list->availablefrom, get_string('strftimedatemonthabbr', 'langconfig'));

                $record['enddate'] = userdate($list->availableto, get_string('strftimedatemonthabbr', 'langconfig'));
                $record['type'] = '';                
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

            //$record['approvedseats']=(new \tool_product\product)->approvedseats_check($list->tablename,$list->fieldname, $list->fieldid, $list->orguserid);

            $record['approvedseats'] = number_format($approvalseats);

            $storeapprovedseats=$approvedseats[$list->orguserid][$list->tablename][$list->fieldid];
            // if(!isset($storeapprovedseats)){

            //     $storeapprovedseats=$approvedseats[$list->orguserid][$list->tablename][$list->fieldid]=$record['approvedseats'];

            // }

            // $reaminingapprovedseats=$storeapprovedseats-$record['purchasedseats'];

            // if($reaminingapprovedseats > 0){

            //     $record['approvedseats']=$record['purchasedseats'];

            // }else{

            //     $record['approvedseats']=$storeapprovedseats;

            // }
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
            $record['paymentstartdate'] = $list->availablefrom;
            $record['paymentenddate'] = $list->availableto;
            $record['transactionid'] = !empty($list->transactionid) ? $list->transactionid : '--';
      
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
    public function  lis_order_approval($stable,$filterdata=null) {

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

            $fullname = (new \local_trainingprogram\local\trainingprogram())->user_fullname_case();
            $orgfullname= ($currentlang == 'ar') ? 'org.fullnameinarabic as orgname' : 'org.fullname as orgname';
            $sql="SELECT u.id,$fullname,$orgfullname 
                FROM {user} AS u 
                JOIN {local_users} lc ON lc.userid = u.id
                JOIN {local_organization} org ON org.id = lc.organization
                WHERE  u.id=:orguserid ";

            $user=$DB->get_record_sql($sql,array('orguserid'=>$list->orguserid));      
            $record['organizationname']=($user) ? $user->orgname : 'NA';
            if($user){
                $record['orgoffcialname']= ($user) ? $user->fullname : 'NA';
            }else{
                $sql="SELECT u.id,concat(u.firstname,' ',u.lastname) as fullname
                FROM {user} AS u 
                WHERE  u.id=:userid ";

                $user=$DB->get_record_sql($sql,array('userid'=>$list->orguserid));

                $record['orgoffcialname']= ($user) ? $user->fullname : fullname($user);
            }


            if ($list->tablename == 'local_exam_profiles') {
                $record['duedate']='--';   
            } elseif($list->tablename == 'tp_offerings') {
                $offering = $DB->get_record('tp_offerings',['id'=>$list->fieldid]);

                $record['duedate']= ( $offering->trainingmethod == 'elearning') ? '--' :userdate($list->availablefrom, get_string('strftimedatefullshort', 'langconfig'));


            } else {
                $record['duedate']=userdate($list->availablefrom, get_string('strftimedatefullshort', 'langconfig'));
            }


            $record['offercode']=$list->offercode;

            $record['purchasedseats']=$list->purchasedseats;

            $record['approvalseats']=$list->approvalseats;

            $availabledate = date('Y-m-d',$list->availablefrom);
            $currdate = date('Y-m-d');


            $record['approvalsupdate']=false;

            $orderstatus = $DB->get_field('tool_org_order_payments', 'orderstatus', ['id'=> $list->paymentid]);
            if(has_capability('tool/products:manageorderapproval', $systemcontext) && $list->approvalseats <= 0 && $orderstatus == 0 ){

            // if(has_capability('tool/products:manageorderapproval', $systemcontext) && ($list->purchasedseats > $list->approvalseats) && ($availabledate >= $currdate )){
            // $orderstatus = $DB->get_field('tool_org_order_payments', 'orderstatus', ['id'=> $list->paymentid]);
            // if(has_capability('tool/products:manageorderapproval', $systemcontext) && $list->approvalseats <= 0 ){

                $record['approvalsupdate']=true;

            }
            $productdata= $DB->get_record('tool_org_order_payments', ['id' => $list->paymentid]);
            $record['productdata'] = base64_encode(serialize($productdata));
            $record['type'] = $list->tablename;
            if ($record['type'] == 'tp_offerings') {
                $record['ordername'] = get_string('programname', 'tool_product');
            }elseif ($record['type'] == 'local_exam_profiles' || $record['type'] == 'local_exam_attempts') {
                $record['ordername'] = get_string('examname', 'tool_product');
            }elseif ($record['type'] == 'local_events') {
                $record['ordername'] = get_string('eventname', 'tool_product');
            }else{
                $record['ordername'] = get_string('trainingname', 'tool_product');
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
        // print_r($getorderapproval);die;
        $orderapproval=array_values($getorderapproval['orders']);

        $row = array();
        $stable = new \stdClass();
        $stable->thead = true;
        foreach ($orderapproval as $list) {
            $list->traineeeid=$traineeeid;
            $record = array();
            $record['id']=$list->id;
            $record['trainingname']=self::product_orgviewlink($list);
            $record['name'] = $list->trainingname;
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
                $record['purchasedseats']= (new \tool_product\product)->purchasedseats_check('tp_offerings','id', $list->fieldid);

                $record['approvalseats']= (new \tool_product\product)->approvedseats_check('tp_offerings','id', $list->fieldid);

                $record['availableseats']= (new \local_trainingprogram\local\trainingprogram())->get_available_seats($list->fieldid);
                $record['courseid'] = $DB->get_field('local_trainingprogram', 'courseid', array('id' => $list->trainingid));
                $usedseats =(new \local_trainingprogram\local\trainingprogram())->get_erolled_seats($list->fieldid);
                $record['enrolledseats'] = $usedseats;
                $record['usedseats']=$usedseats; //html_writer::tag('a', $usedseats,array('href' =>$CFG->wwwroot. '/local/trainingprogram/programenrolleduserslist.php?programid='.$programid.'&offeringid='.$list->fieldid));
                $record['startdate'] = ($trainingmethod =='elearning')? '--': userdate($list->availablefrom, get_string('strftimedatemonthabbr', 'langconfig'));
                $record['enddate'] =($trainingmethod =='elearning')? '--': userdate($list->availableto, get_string('strftimedatemonthabbr', 'langconfig'));
                $record['type'] = false;
                $record['offeringview'] = true;

                $record['timelimit'] = (($trainingmethod =='elearning' && $record['availableseats'] > 0) || ($trainingmethod !='elearning' && $list->availablefrom >= time()))? true : false;
                $record['action'] = (($trainingmethod =='elearning' && $record['availableseats'] > 0) || ($trainingmethod !='elearning'  && $list->availablefrom >= time())) ? self::product_enrollseats($list) :( ($record['availableseats'] <= 0) ? get_string('no_seatsavailable','tool_product') : get_string('completed','tool_product'));
                $record['enrolledusersurl'] = $CFG->wwwroot.'/local/trainingprogram/programenrolleduserslist.php?programid='.$list->trainingid.'&offeringid='.$list->fieldid;
                $record['userid'] = $USER->id;
                $record['hidefavexamsview'] = true;
                $record['trainingid'] = $list->trainingid;
                $component='local_trainingprogram';
                $componenttype ="trainingprogram";
                 $record['checkfavornot'] =(new exams)->checkfavourites($programid,$USER->id,$component);

            } elseif($list->tablename == 'local_exam_profiles') {
                $record['purchasedseats'] =(new \tool_product\product)->purchasedseats_check('local_exam_profiles','id', $list->fieldid); //$list->purchasedseats; 
                $record['approvalseats'] = (new \tool_product\product)->approvedseats_check('local_exam_profiles','id', $list->fieldid); // $list->approvalseats; 
                $record['availableseats'] = (new exams())->availableseats($list->fieldid);
                $record['usedseats']= (new exams())->entity_enrolled($list->fieldid, $USER->id);
                $record['enrolledseats'] = (new exams())->entity_enrolled($list->fieldid, $USER->id);
                $record['courseid'] = $DB->get_field('local_exams', 'courseid', array('id' => $list->trainingid));
                $record['startdate'] = '--';
                $record['enddate'] = '--';
                $record['type'] = 'exam';
                $record['offeringview'] = false;

                $record['timelimit'] = true;
                $record['action'] = self::product_enrollseats($list);
                $record['enrolledusersurl'] = $CFG->wwwroot.'/local/exams/examusers.php?id='.$list->trainingid.'&profileid='.$list->fieldid;
                $record['userid'] = $USER->id;
                $record['trainingid'] = $list->trainingid;
                $record['hidefavexamsview'] = false;
               $component='local_exams';
               $componenttype ="exams";
                 $record['checkfavornot'] =(new exams)->checkfavourites($list->trainingid,$USER->id,$component);
            } elseif($list->tablename == 'local_events') {
              //    var_dump($list); exit;
                $record['purchasedseats']=  (new \tool_product\product)->purchasedseats_check('local_events','id', $list->fieldid);//$list->purchasedseats;
               
                $eventrecord =  $DB->get_record('local_events',array('id'=>$list->fieldid));
                $hallseatingcapacity = ((int)$eventrecord->halladdress > 0) ? (int)$DB->get_field('hall','seatingcapacity',array('id'=>$eventrecord->halladdress)) : 0;
                $record['approvalseats']= (new \tool_product\product)->approvedseats_check('local_events','id', $list->fieldid); //$list->approvalseats;
                $record['availableseats'] =($eventrecord->price == 1 || ($eventrecord->price == 0 && $hallseatingcapacity > 0 )) ? $list->availableseats: '--';
                $record['usedseats']= (new \local_events\events())->get_erolled_seats($list->fieldid, $USER->id);
                $record['enrolledseats']= (new \local_events\events())->get_erolled_seats($list->fieldid, $USER->id);

                $record['startdate'] = userdate($list->availablefrom, get_string('strftimedatemonthabbr', 'langconfig'));
                $record['enddate'] = userdate($list->availableto, get_string('strftimedatemonthabbr', 'langconfig'));
                $record['type'] = false;
                $record['offeringview'] = false;
                $record['courseid'] = 0;
                $record['enrolledusersurl'] = $CFG->wwwroot.'/local/events/attendees.php?id='.$list->fieldid;
                $record['timelimit'] =  (date('Y-m-d H:i:s ', $list->availablefrom) >= date('Y-m-d H:i:s')) ? true : false;
                $record['action'] = (date('Y-m-d H:i:s ', $list->availablefrom) >= date('Y-m-d H:i:s')) ? self::product_enrollseats($list) : get_string('completed','tool_product');
                $record['userid'] = $USER->id;
                $record['trainingid'] = $list->fieldid;
                $record['hidefavexamsview'] = true;
                $component='local_events';
                $componenttype ="events";
                 $record['checkfavornot'] =(new exams)->checkfavourites($list->fieldid,$USER->id,$component);
            } else {

                $record['purchasedseats']=$list->purchasedseats;

                $record['approvalseats']=$list->approvalseats;
                $record['availableseats'] = $list->availableseats;
                $record['usedseats']= $list->approvalseats - $list->availableseats;
                $record['enrolledseats']= $list->approvalseats - $list->availableseats;
                $record['startdate'] = userdate($list->availablefrom, get_string('strftimedatemonthabbr', 'langconfig'));
                $record['enddate'] = userdate($list->availableto, get_string('strftimedatemonthabbr', 'langconfig'));
                $record['type'] = false;
                $record['offeringview'] = false;
                $record['courseid'] = 0;
                $record['timelimit'] =  (date('Y-m-d H:i:s ', $list->availablefrom) >= date('Y-m-d H:i:s')) ? true : false;
                $record['action'] = (date('Y-m-d H:i:s ', $list->availablefrom) >= date('Y-m-d H:i:s')) ? self::product_enrollseats($list) : get_string('completed','tool_product');
                $record['enrolledusersurl'] = '#';
            }
            $record['typeid'] = $list->trainingid;

            $record['datebegin'] = $list->availablefrom;
            $record['dateend'] = $list->availableto;
            $record['referenceid'] = $list->fieldid;
            $record['entityid'] = $list->trainingid;

            $record['enrollbtn']=self::product_enrollseats($list);
            $record['checkcomponent'] = $component;
            $record['checkcomponenttype'] = $componenttype;
            $row[] = $record;
         }
        return array_values($row);
    }
    public function product_viewlink($list) {

        global $USER, $DB;

        $context=context_system::instance();


        switch ($list->tablename) {
            case 'tp_offerings':
            
                $trainingurl = new moodle_url('/local/trainingprogram/programcourseoverview.php',array('programid'=>$list->trainingid));
                if(!is_siteadmin() && has_capability('local/organization:manage_trainee', $context)) {
                    $check_completion = $DB->record_exists('program_completions',['programid' => $list->trainingid, 'offeringid' => $list->fieldid, 'userid' => $USER->id, 'completion_status' => 1] );
                    if($check_completion){
                        $trainingurl='#';
                    } else {
                        $trainingurl= '#';//$trainingurl;
                    }
                } else {
                    $trainingurl= $trainingurl;
                }
                $trainingname= html_writer::link($trainingurl, ucwords($list->trainingname));
                break;

            case 'local_exam_profiles':
                // if(has_capability('local/organization:manage_examofficial', $context)){
                //     $examurl = new moodle_url('/local/exams/examdetails.php',array('id'=>$list->trainingid,'profileid'=>$list->fieldid));
                // } else {
                //     $examurl = new moodle_url('/local/exams/exams_qualification_details.php',array('id'=>$list->trainingid));
                // }
                if(!is_siteadmin() && has_capability('local/organization:manage_trainee', $context)) {
                    $courseid = $DB->get_field('local_exams','courseid',['id' => $list->trainingid]);
                    $usergrades = $DB->get_record_sql(
                    " SELECT ep.quizid,ep.passinggrade,ep.id AS profileid, gg.finalgrade FROM {local_exam_profiles} ep 
                    JOIN {local_exam_userhallschedules} eu ON eu.profileid = ep.id AND eu.examid = ep.examid
                    JOIN {grade_items} gi ON gi.iteminstance = ep.quizid 
                    JOIN {grade_grades} gg ON gg.itemid = gi.id  AND gi.itemmodule = 'quiz' AND gg.userid = eu.userid
                    WHERE eu.examid =:examid AND eu.userid=:userid AND gi.courseid =:courseid  
                    AND ep.passinggrade <= gg.finalgrade AND eu.examdate !=0 ORDER BY eu.id DESC limit 1",
                    ['examid'=>$list->trainingid,'userid'=>$USER->id,'courseid'=>$courseid]);
                    if($usergrades) {
                        $examurl = '#';
                    } else {
                        $userhalls = $DB->get_record_sql('SELECT eu.examdate,eu.hallscheduleid, eu.profileid FROM 
                        {local_exam_userhallschedules} eu 
                        WHERE eu.examid =:examid AND eu.userid=:userid
                        ORDER BY eu.id DESC limit 1',['examid'=>$list->trainingid,'userid'=>$USER->id]);
                        if($userhalls->examdate == 0 && $userhalls->hallscheduleid == 0) {
                            $examurl = '#'; //new moodle_url('/local/exams/hallschedule.php',array('examid'=>$list->trainingid, 'profileid' => $userhalls->profileid, 'tuserid' => $USER->id,'status' => 'en'));
                        } else {
                            $examurl = '#'; //new moodle_url('/local/exams/examdetails.php',array('id'=>$list->trainingid, 'profileid' => $userhalls->profileid ));//new moodle_url('/local/exams/exams_qualification_details.php',array('id'=>$list->trainingid));
                        }
                    }
                }
                $trainingname= html_writer::link($examurl, ucwords($list->trainingname));
                break;

            case 'local_events':

                $trainingurl = new moodle_url('/local/events/view.php',array('id'=>$list->trainingid));
                if(!is_siteadmin() && has_capability('local/organization:manage_trainee', $context)) {
                    $event = $DB->get_record_sql( " SELECT le.enddate, le.slot, le.eventduration FROM {local_events} AS le 
                    JOIN {local_event_attendees} AS ea ON ea.eventid = le.id WHERE le.id = $list->trainingid AND ea.userid = $USER->id ");
                    $current_date = time();
                    $eventendttime = ($event->enddate+$event->slot+$event->eventduration);
                    //if($current_date > $eventendttime) {
                        $trainingurl = '#';
                   // }
                } else {
                    $trainingurl = $trainingurl;
                }
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

                $offeringcode = $DB->get_field('tp_offerings','code',['id'=>$list->fieldid]);

                $trainingdisplayname = ucwords($list->trainingname).'</br>'.'('. get_string('offering_code','tool_product').$offeringcode.')';

                $trainingname= html_writer::link($trainingurl, $trainingdisplayname);
                break;

            case 'local_exam_profiles':

                $trainingurl = new moodle_url('/local/exams/examdetails.php',array('id'=>$list->trainingid));

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

                $programprice = $DB->get_field_sql('SELECT tp.price FROM {local_trainingprogram} tp JOIN {tp_offerings} tpo ON tp.id = tpo.trainingid WHERE tpo.id = '.$list->fieldid.'');

                $traineeroleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));

                $trainingurl =  new moodle_url('/local/trainingprogram/programenrollment.php',array('programid'=>$list->trainingid,'roleid'=>$traineeroleid,'offeringid'=>$list->fieldid));

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
            case 'local_exam_profiles':
                $trainingurl = new moodle_url('/local/exams/examenrollment.php',array('examid'=>$list->trainingid, 'profileid' => $list->fieldid));

                if($list->totalapprovalseats > 0){

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
        $filtermode = !empty($filter['mode']) ? $filter['mode'] : 'paid';
        global $CFG, $OUTPUT,$PAGE,$USER;

        require_once($CFG->dirroot . '/admin/tool/product/lib.php');
        $systemcontext = context_system::instance();
        if(!is_siteadmin() && has_capability('local/organization:manage_trainee',$systemcontext)) {
    
            $filterdata = json_encode(array('mode'=> $filtermode));

            $options = array('targetID' => 'viewfinancialpayments','perPage' => 10, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'table');

            $options['targetID']='viewfinancialpayments';
            $options['methodName']='tool_product_get_mypayments';
            $options['templateName']='tool_product/listmyfinancialpayments';
            $options['mode'] = $filtermode;

            $cardoptions = json_encode($options);
            $cardparams = array(
                'targetID' => 'viewfinancialpayments',
                'options' => $cardoptions,
                'dataoptions' => $cardoptions,
                'filterdata' => $filterdata,
                'tablename'=>'tool_org_order_payments',
                'mode' => $filtermode,
            );
            $fncardparams=$cardparams;
            $financialpaymentsmform = myfinancialpayments_filters_form($cardparams);
            $cardparams = $fncardparams+array(
                'contextid' => $context->id,
                'plugintype' => 'local',
                'plugin_name' =>'trainingprogram',
                'cfg' => $CFG,
                'filterform' => $financialpaymentsmform->render());

            return  $this->render_from_template('tool_product/viewuserfinancialpayments', $cardparams);

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
            $approvedseats = $DB->get_field('tool_order_approval_seats', 'approvalseats', ['paymentid' => $list->id]);
            $record['id'] = $list->id;
            $record['trainingname'] = $list->trainingname;

            if ($approvedseats > 0) {
                $approval_price = $list->originalprice/$list->purchasedseats;
                $total_price = $approval_price*$approvedseats;
                $approved_discount_price = $list->discountprice/$list->purchasedseats;
                $discount_price = $approved_discount_price*$approvedseats;
                $approved_tax_price = $list->taxes/$list->purchasedseats;
                $tax_price = $approved_tax_price*$approvedseats;
                $record['total_price'] =  ($total_price) ? ($this->is_decimal($total_price) ? number_format($total_price,2) : number_format($total_price)) : 0;
                $record['discount_price'] = ($discount_price) ? ($this->is_decimal($discount_price)? number_format($discount_price,2) : number_format($discount_price)) : 0;
                $record['taxes'] = ($tax_price) ? ($this->is_decimal($tax_price)? number_format($tax_price,2) : number_format($tax_price)) : 0;
                $price = str_replace(',', '', $record['total_price']);
                $final_price = $price+$record['discount_price']+$record['taxes'];
                $record['total'] = ($final_price) ? ($this->is_decimal($final_price)? number_format($final_price,2) : number_format($final_price)) : 0;
                $statuscode  = $statusarray[1];  // Paid
            } else {
                $record['total_price'] =  ($list->originalprice) ? ($this->is_decimal($list->originalprice)? number_format($list->originalprice,2) : number_format($list->originalprice)) : 0;
                $record['discount_price'] = ($list->discountprice) ? ($this->is_decimal($list->discountprice)? number_format($list->discountprice,2) : number_format($list->discountprice)) : 0;
                $record['taxes'] = ($list->taxes) ? ($this->is_decimal($list->taxes)? number_format($list->taxes,2) : number_format($list->taxes)) : 0;

                $record['total'] = ($list->amount) ? ($this->is_decimal($list->amount)? number_format($list->amount,2) : number_format($list->amount)) : 0;
                $statuscode  = $statusarray[0];  // Pending
            }
            $record['purchasedseats'] = $list->purchasedseats;
            $record['approvalseats'] =($list->paymenttype =='telr' || $list->paymenttype =='prepaid') ? $list->purchasedseats : (($approvedseats) ? $approvedseats : 0);
            $record['paymentmethod']  = $typearray[$list->paymenttype];
            $record['statuscode']  = ($typearray[$list->paymenttype] == 'Prepaid') ? $statusarray[1] : $statuscode;           
            $record['timecreated'] = userdate($list->timecreated, get_string('strftimedatemonthabbr', 'langconfig'));
            $record['paymentdate'] = $list->timecreated;
            $row[] = $record;
        }

        return array_values($row);
    }
    public function lis_my_financialpayments($stable,$filterdata=null) {

        global $USER,$DB,$CFG;

        $systemcontext = context_system::instance();
        $getfinancialpayments = (new \tool_product\product)::get_my_financialpayments($stable,$filterdata);
        $financialpayments=array_values($getfinancialpayments['payments']);

        krsort($financialpayments);


        $row = array();

        $currentlang= current_language();


        $totalcost=0;


        if(!is_siteadmin() && has_capability('local/organization:manage_trainee',$systemcontext)) {
            foreach ($financialpayments as $list) {
            // print_object($financialpayments);die;
                $record = array();

                $record['id']=$list->id;
                  
                $record['trainingname']= ucwords($list->trainingname);//self::product_viewlink($list);

                $record['taxes']=($list->taxes) ? ($this->is_decimal($list->taxes)? number_format($list->taxes,2) : number_format($list->taxes)) : 0;

                $record['discountprice']=($list->discountprice) ? ($this->is_decimal($list->discountprice)? number_format($list->discountprice,2) : number_format($list->discountprice)) : 0;

                $record['originalprice']=($list->originalprice) ? ($this->is_decimal($list->originalprice)? number_format($list->originalprice,2) : number_format($list->originalprice)) : 0;

               $record['finalprice']=($list->amount) ? ($this->is_decimal($list->amount)? number_format($list->amount,2) : number_format($list->amount)) : 0;

                //$record['finalprice'] = ($list->sellingprice) ? ($this->is_decimal($list->sellingprice)? number_format($list->sellingprice,2) : number_format($list->sellingprice)) : 0;

                $record['paymentstatus']=get_string('paid','tool_product');

                $totalcost=$totalcost+$record['finalprice'];
               
                if($list->tablename == 'local_exam_profiles') {
                    $examid = $DB->get_field('local_exam_profiles', 'examid', ['id' => $list->fieldid]);
                    $examshedulesql = "SELECT id
                              FROM {local_exam_userhallschedules} 
                             WHERE examid =:examid
                                   AND userid =:userid";
                    $recordexists = $DB->record_exists_sql($examshedulesql,['examid'=>$examid,'userid'=>$list->userid]);
                    if($recordexists) {
                        $examsheduledate = $DB->get_field('local_exam_userhallschedules','examdate',['examid'=>$examid,'userid'=>$list->userid]);
                    }
                    $record['duedate'] = ($examsheduledate) ? userdate($examsheduledate, get_string('strftimedatefullshort', 'langconfig')) : '-';

                } elseif($list->tablename == 'tp_offerings') {

                    $offeringrecord = $DB->get_record('tp_offerings',['id'=>$list->fieldid]);
                    $record['duedate'] = ($offeringrecord->trainingmethod == 'elearning')? '--' :userdate($list->availablefrom, get_string('strftimedatefullshort', 'langconfig'));
                } else {
                    $record['duedate'] = userdate($list->availablefrom, get_string('strftimedatefullshort', 'langconfig'));
                }

                $record['paymentdate'] = userdate($list->timecreated, get_string('strftimedatefullshort', 'langconfig'));
                if( date('Y-m-d',$list->availablefrom) < date('Y-m-d') ) {
                    $record['trainingstatus'] = 'alert alert-warning';
                }elseif( date('Y-m-d',$list->availablefrom) > date('Y-m-d') ) {
                    $record['trainingstatus'] = 'alert alert-info';
                }elseif( date('Y-m-d',$list->availablefrom) == date('Y-m-d')) {
                    $record['trainingstatus'] = 'alert alert-danger';
                }else{
                    $record['trainingstatus'] =  'bg-theme_dark';
                }
                $record['entityid'] =  $list->trainingid;
                $record['referenceid'] =  $list->fieldid;
                $record['name'] =  $list->trainingname;
                $record['type'] = $list->tablename;
                if ($record['type'] == 'tp_offerings') {
                    $record['productname'] = get_string('programname', 'tool_product');
                }elseif ($record['type'] == 'local_exam_profiles' || $record['type'] == 'local_exam_attempts' || $record['type'] == 'local_exam_grievance' || $record['type'] == 'exam') {
                    $record['productname'] = get_string('examname', 'tool_product');
                }elseif ($record['type'] == 'local_events') {
                    $record['productname'] = get_string('eventname', 'tool_product');
                }else{
                    $record['productname'] = get_string('trainingname', 'tool_product');
                }
                $record['purchasedseats'] = $list->purchasedseats;
                $record['invoicetype'] = ($list->purchasedseats > 0) ?  get_string('purchased', 'tool_product') : get_string('reschedule', 'tool_product');
                $record['orderinfo'] = base64_encode(serialize($record));
                $row[] = $record;
            }
        }else{

            $usedseats=array();

            $approvedseats=array();

            foreach ($financialpayments as $list) {
                $record = array();

                $record['id']=$list->id;

                if(!is_siteadmin() && has_capability('local/organization:manage_communication_officer',$systemcontext)) {

                    $record['costview']=false;

                } else {

                    $record['costview']=true;
                }

                $record['cost']=($list->amount) ? ($this->is_decimal($list->amount)? number_format($list->amount,2) : number_format($list->amount)) : 0;
                $record['total']=($list->amount) ? (int)($this->is_decimal($list->amount)? number_format($list->amount,2) : number_format($list->amount)) : 0;
                $record['discount_price']=$list->discountprice;
                $record['taxes']=$list->taxes;
                //$record['cost'] = ($list->sellingprice) ? ($this->is_decimal($list->sellingprice)? number_format($list->sellingprice,2) : number_format($list->sellingprice)) : 0;

                $totalcost=$totalcost+$record['finalprice'];

                $fullname = (new \local_trainingprogram\local\trainingprogram())->user_fullname_case();
                $orgfullname=($currentlang == 'ar') ? 'org.fullnameinarabic as orgname' : 'org.fullname as orgname';

                $sql="SELECT u.id,u.firstname,' ',u.lastname,$fullname,$orgfullname, u.email, org.fullnameinarabic as orgnamear, org.fullname as orgnameen, concat(lc.firstname,' ',lc.lastname) as usernameen, concat(lc.firstnamearabic,' ',lc.lastnamearabic) as usernamear 
                    FROM {user} AS u 
                    JOIN {local_users} lc ON lc.userid = u.id
                    JOIN {local_organization} org ON org.id = lc.organization
                    WHERE  u.id=:userid ";

                $user=$DB->get_record_sql($sql,array('userid'=>$list->userid));
                $record['fieldid']=$list->fieldid;
                $record['organizationname']=($user) ? $user->orgname : '--';
                $record['orgnamear']=($user) ? $user->orgnamear : '--';
                $record['organizationcode']=($user) ? $user->orgname : '--';
                if($user){
                    $record['username']= ($user) ? $user->fullname : 'NA';
                    $record['orgoffcialname']= ($user) ? $user->fullname : '--'; // For Invoice PDF
                    $record['email']=($user) ? $user->email : 'NA';
                    $record['usernamear']=!empty($user) ? $user->usernamear : '--';
                }else{

                    $sql="SELECT u.id,concat(u.firstname,' ',u.lastname) as fullname, u.email, concat(lu.firstnamearabic,' ',lu.lastnamearabic) as usernamear 
                    FROM {user} AS u
                    LEFT JOIN {local_users} lu ON lu.userid = u.id 
                    WHERE  u.id=:userid ";

                    $user=$DB->get_record_sql($sql,array('userid'=>$list->userid));

                    $record['username']= ($user) ? $user->fullname : fullname($user);
                    $record['orgoffcialname']= ($user) ? $user->fullname : '--'; // For Invoice PDF
                    $record['email']=($user) ? $user->email : '--';
                    $record['usernamear']=!empty($user) ? $user->usernamear : '--';
                }
                $record['userid']= ($user) ? $list->userid : 0;

                $record['mode']=get_string($list->paymenttype,'tool_product');

                if($list->tablename == 'local_exam_profiles') {

                    $examid = $DB->get_field('local_exam_profiles', 'examid', ['id' => $list->fieldid]);
                    $examshedulesql = "SELECT id
                              FROM {local_exam_userhallschedules} 
                             WHERE examid =:examid
                                   AND userid =:userid";
                    $recordexists = $DB->record_exists_sql($examshedulesql,['examid'=>$examid,'userid'=>$list->userid]);
                    if($recordexists) {
                        $examsheduledate = $DB->get_field('local_exam_userhallschedules','examdate',['examid'=>$examid,'userid'=>$list->userid]);
                    }
                    $record['duedate'] = ($examsheduledate) ? userdate($examsheduledate, get_string('strftimedatefullshort', 'langconfig')) : '-';

                } elseif($list->tablename == 'tp_offerings') {

                    $offeringrecord = $DB->get_record('tp_offerings',['id'=>$list->fieldid]);
                    $record['duedate'] = ($offeringrecord->trainingmethod == 'elearning')? '--' :userdate($list->availablefrom, get_string('strftimedatefullshort', 'langconfig'));

                } else {

                    $record['duedate'] = userdate($list->availablefrom, get_string('strftimedatefullshort', 'langconfig'));
                }

                $record['paymentsupdate']=false;


                $record['sendemailactionview']=false;

                $record['purchasedseats']=$list->purchasedseats;
                $storeseats=$usedseats[$list->userid][$list->tablename][$list->fieldid];
                
                if(!isset($storeseats)){

                    $storeseats=$usedseats[$list->userid][$list->tablename][$list->fieldid]=$record['usedseats'];

                }

                $record['usedseats']=$record['purchasedseats'];
                $record['approvedseats']=$record['purchasedseats'];
                $record['cisi_enrolment'] = 'N/A';
                $ownedby = $DB->get_field('local_exams', 'ownedby', ['id' => $list->trainingid]);
                if ($ownedby == 'CISI') {
                    $traineeid = $record['userid'];
                    $sql = "SELECT id logid, objectid examid, action, userid FROM {logstore_standard_log} WHERE objectid = '$list->trainingid' AND userid = '$traineeid' AND target = 'exam_booking' AND action = 'successful'   ORDER BY id DESC LIMIT 1 ";
                    $cisi_enrolment = $DB->get_record_sql($sql);
                    if ($cisi_enrolment->action == 'successful') {
                        $record['cisi_enrolment'] = get_string('yes');
                    }else{
                        $record['cisi_enrolment'] = get_string('no');
                    }
                }

                if( date('Y-m-d',$list->availablefrom) < date('Y-m-d') ) {

                    $record['trainingstatus'] = 'alert alert-warning';

                }elseif( date('Y-m-d',$list->availablefrom) > date('Y-m-d') ) {

                    $record['trainingstatus'] = 'alert alert-info';

                }elseif( date('Y-m-d',$list->availablefrom) == date('Y-m-d')) {

                    $record['trainingstatus'] = 'alert alert-danger';

                }else{

                    $record['trainingstatus'] =  'bg-theme_dark';

                }
                $record['entityid'] =  $list->trainingid;
                $record['referenceid'] =  $list->fieldid;
                $record['name'] =  base64_encode(format_text($list->trainingname));;
                $telrtranscations = $DB->get_record('tool_product_telr', ['id' => $list->telrid]);
                $record['transcationref']=$telrtranscations->transactionref;
                $record['transcationnumber']=$telrtranscations->transactioncode;
                $record['transactionid']=$telrtranscations->transactioncode;
                $record['type'] = $list->tablename;
                if ($record['type'] == 'tp_offerings') {
                    $record['productname'] = get_string('programname', 'tool_product');
                    $record['pname'] = true;

                }elseif ($record['type'] == 'local_exam_profiles' || $record['type'] == 'local_exam_attempts' || $record['type'] == 'local_exam_grievance' || $record['type'] == 'exam') {
                    $record['productname'] = get_string('examname', 'tool_product');
                }elseif ($record['type'] == 'local_events') {
                    $record['productname'] = get_string('eventname', 'tool_product');
                }else{
                    $record['productname'] = get_string('trainingname', 'tool_product');
                }

                $record['timecreated'] = userdate($list->timecreated, get_string('strftimedatemonthabbr', 'core_langconfig'));
                $record['pdftimecreated'] = userdate($list->timecreated, get_string('strftimedatefullshort', 'core_langconfig'));
                $product = $DB->get_record('tool_products', ['id'=>$list->productid]);
                $entity = (new \tool_product\product)::entityinformation($product);
                $record['entityoldid']= !empty($entity['entity']->oldid) ? $entity['entity']->oldid : 0;
                $record['description']= !empty($entity['entity']->description) ? base64_encode(format_text($entity['entity']->description)) : '--';
                $record['unitprice'] = !empty($list->originalprice/$record['purchasedseats']) ? (int)($list->originalprice/$record['purchasedseats']) : 0;
                $discountpercentage = (int)(($record['discount_price']/$record['unitprice'])*100);
                $record['discount_percentage'] = !empty($discountpercentage) ? $discountpercentage : 0;
                $taxesprcentage = (int)(($record['taxes']/$record['unitprice'])*100);
                $record['taxes_percentage'] = !empty($taxesprcentage) ? $taxesprcentage : 0;
                $record['orderinfo'] = base64_encode(serialize($record));
                $record['invoicetype'] = ($list->purchasedseats > 0) ?  get_string('purchased', 'tool_product') : get_string('reschedule', 'tool_product');
                $record['trainingname']=self::product_viewlink($list);
                if(is_siteadmin() || has_capability('local/organization:manage_financial_manager',$systemcontext)) {
                    $record['invoiceaction']=true;
                } else {
                    $record['invoiceaction']=false;
                }
               
                if(is_siteadmin() || has_capability('local/organization:manage_financial_manager',$systemcontext)) {
               
                    $p = $DB->get_record_sql("SELECT * FROM {tool_products} as product JOIN {tp_offerings} as tpo ON product.referenceid=tpo.id WHERE product.id=$list->productid");
                        if($p->tagrement==1){ 
                           
                            $record['tagreement'] =  true;      
                            $itemid=$p->tagrrement;
                                $fs = get_file_storage();
                                $files = $fs->get_area_files($systemcontext->id, 'local_trainingprogram', 'tagrrement', $itemid);
                                foreach($files as $file){
                                    $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(),
                                    $file->get_itemid(), $file->get_filepath(), $file->get_filename(), false);
                                    $downloadurl = $url->get_scheme() . '://' . $url->get_host() . ':' . $url->get_port() . $url->get_path();
                                
                                
                                    $record['trainingagreement'] = $downloadurl;
                                }
                        }
               
                }   
                
                $row[] = $record;
            }
        }
        $data=array_values($row);

        krsort($data);

        return compact('data', 'totalcost');
    }

    public function is_decimal($val){
 
        return is_numeric( $val ) && floor( $val ) != $val;

    }
    public function get_traineepayments($filter = false) {

        global $CFG, $OUTPUT,$PAGE,$USER;
        require_once($CFG->dirroot . '/admin/tool/product/lib.php');
        $mode = optional_param('mode', 1, PARAM_INT);
        $context = context_system::instance();


        if((has_capability('tool/products:managefinancialpayments', $context)) || (has_capability('tool/products:viewfinancialpayments', $context)) || (has_capability('local/organization:manage_communication_officer', $context)) || (has_capability('local/organization:manage_financial_manager',$context))){

            $filterdata = json_encode(array());

            $options = array('targetID' => 'viewfinancialpayments','perPage' => 10, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'table');

            $options['targetID']='viewfinancialpayments';
            $options['methodName']='tool_product_get_mypayments';
            $options['templateName']='tool_product/listtraineefinancialpayments';
            $options['mode'] = $mode;

            $cardoptions = json_encode($options);
            $cardparams = array(
                'targetID' => 'viewfinancialpayments',
                'options' => $cardoptions,
                'dataoptions' => $cardoptions,
                'filterdata' => $filterdata,
                'tablename'=>'tool_user_order_payments',
                'mode' => $mode
            );
            $fncardparams=$cardparams;
            $financialpaymentsmform = financialpayments_filters_form($cardparams);


            $cardparams = $fncardparams+array(
                'contextid' => $context->id,
                'plugintype' => 'local',
                'plugin_name' =>'trainingprogram',
                'cfg' => $CFG,
                'filterform' => $financialpaymentsmform->render());

            return  $this->render_from_template('tool_product/viewtraineefinancialpayments', $cardparams);

        }
        else{

            return "<div class='alert alert-danger'>" . get_string('nofinancialpaymentspermission', 'tool_product') . "</div>";
        }
    }
}
