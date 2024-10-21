<?php
namespace tool_product;

use context_system;
use stdClass;
use tool_product\product;

class traineewallet
{
    public const TRAINING_PROGRAM = 1;
    public const EXAMS = 2;
    public const EVENTS = 3;
    public const GRIEVANCE = 4;
    public const LEARNINGTRACKS = 5;
    public const EXAMATTEMPT = 6;

    public function userwalletpayments($data, $productid)
    {
        global $DB, $USER;
        $product = $DB->get_record('tool_products', ['id' => $productid]);
        $category=$product->category;
        switch ($category) {
            case self::TRAINING_PROGRAM:
                $data->tablename='tp_offerings';
            break;
            case self::EXAMS:
                $data->tablename='local_exam_profiles';
            break;
            case self::EVENTS:
                $data->tablename='local_events';
            break;
            case self::GRIEVANCE:
                $data->tablename='local_exam_grievance';
            break;
            case self::LEARNINGTRACKS:
                $data->tablename='local_learningtracks';
            break;
            case self::EXAMATTEMPT:
                $data->tablename='local_exam_attempts';
            break;                
        }
        $data->fieldname = 'id';
        $data->fieldid = $product->referenceid;
        $data->userid = $USER->id;
        (new product)->insert_update_user_order_payments($data,$product);
    }

    public function get_traineewalletdetails($stable,$filterdata)
    {
        global $DB, $USER;
        $systemcontext = context_system::instance();
        $lang = current_language();
        $countsql  = "SELECT COUNT(tw.id) ";
        $selectsql ="SELECT tw.*, CONCAT(lu.firstname, ' ', lu.lastname) as trainee "; 
        $fromsql = "FROM {trainee_wallet} as tw
                    JOIN {local_users} as lu ON lu.userid = tw.userid 
                   WHERE 1=1  ";

        if (isset($filterdata->search_query) && trim($filterdata->search_query) != '') {
            $fromsql .= " AND (lu.firstname LIKE :firstnamesearch OR lu.lastname LIKE :lastnamesearch OR lu.firstnamearabic LIKE :firstnamearabicsearch OR lu.lastnamearabic LIKE :llastnamearabicsearch OR lu.middlenameen LIKE :middlenameensearch OR lu.middlenamearabic LIKE :middlenamearabicsearch OR lu.thirdnameen LIKE :thirdnameensearch OR lu.thirdnamearabic LIKE :thirdnamearabicsearch OR lu.email LIKE :email  OR lu.id_number LIKE :idnumber) ";
             $searchparams = array(
                                      'firstnamesearch' => '%'.trim($filterdata->search_query).'%',
                                      'lastnamesearch' => '%'.trim($filterdata->search_query).'%', 
                                      'firstnamearabicsearch' => '%'.trim($filterdata->search_query).'%', 
                                      'llastnamearabicsearch' => '%'.trim($filterdata->search_query).'%', 
                                      'middlenameensearch' => '%'.trim($filterdata->search_query).'%', 
                                      'middlenamearabicsearch' => '%'.trim($filterdata->search_query).'%', 
                                      'thirdnameensearch' => '%'.trim($filterdata->search_query).'%', 
                                      'thirdnamearabicsearch' => '%'.trim($filterdata->search_query).'%', 
                                      'email' => '%'.trim($filterdata->search_query).'%',
                                      'idnumber' => '%'.trim($filterdata->search_query).'%');
        } else {
            $searchparams = array();
        }     
        $params = array_merge($searchparams);
        $totalrecords = $DB->count_records_sql($countsql.$fromsql, $params);
        $fromsql .=" ORDER BY tw.id DESC ";
        $records = $DB->get_records_sql($selectsql.$fromsql, $params, $stable->start,$stable->length);

        $data = [];
        foreach($records as $record) {
            $row = [];
            $row['id'] = $record->id;
            $row['user'] = $record->trainee;
            $row['walletamount'] = $record->wallet;
            $data[] = $row;
        }

        $coursesContext = array(
            "walletpayments" => $data,
            "nocourses" => $nocourse,
            "totalcount" => $totalrecords,
            "length" => COUNT($data)
        );

        return $coursesContext;
    }
    
    public function trainee_walletamount($walletinfo)
    {
        global $DB, $USER;
        $data = new stdClass();
        $data->id = $walletinfo['walletid'];
        $data->wallet = $walletinfo['walletamount'] - $walletinfo['deductedamount'];
        $data->usermodified = $USER->id;
        $data->timemodified = time();
        $id = $DB->update_record('trainee_wallet', $data);

        $eventparams = array('context' => \context_system::instance(),'objectid'=>$id,'other'=>array('paymentstatus'=>'deduct'));
        $event = \tool_product\event\trainee_wallet::create($eventparams);// ... code that may add some record snapshots
        $event->trigger();

        return true;
    }
}