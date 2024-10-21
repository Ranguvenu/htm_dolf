<?php
namespace local_exams\local;

class cancelentities
{
    public const TRAINING_PROGRAM = 1;
    public const EXAMS = 2;
    public const EVENTS = 3;
    public const GRIEVANCE = 4;
    public const LEARNINGTRACKS = 5;
    public const EXAMATTEMPT = 6;

    private $status;

    public function cancelentity($productid,$examtype = false)
    {
        global $DB, $USER;
        $policyinfo = '';
        $product = $DB->get_record('tool_products', ['id' => $productid]);
        switch($product->category){
            case self::TRAINING_PROGRAM :

            break;
            case self::EXAMS :
                $this->status = self::removeexam($product,$examtype);
                break;
            case self::EVENTS :

            break;
        }
        return $this->status;
    }

    public static function removeexam($product,$examtype = false)
    {
        global $DB, $USER;
        $sql = "SELECT lep.examid, le.ownedby
                  FROM {local_exam_profiles} lep 
                  JOIN {local_exams} le ON le.id = lep.examid
                 WHERE lep.id = $product->referenceid ";
        $examinfo = $DB->get_record_sql($sql);

        if ($examinfo->ownedby == 'FA') {
            return true;
        } else {
            return false;
        }

    }
    public function trainee_refundinfo($entitytype, $productid, $refundtype = false, $userid, $userunenrol=false, $refundpolicy=false,$actiontype=null)
    {
        global $DB;
        $params = [];
        $params['productid'] = $productid;
        $params['userid'] = $userid;

        $product = $DB->get_record('tool_products', ['id' => $productid]);
        $data = (new \local_exams\local\exams)->userexamrefundinfo($params);
        $policies = new \local_exams\local\policies($entitytype, $data->examdate, $refundtype);
        
        if ($userunenrol) {
            /*if($actiontype=="absent")
            {
                
                $data = $policies->refund_amount($userid, $productid, false, $refundpolicy,$actiontype);
            }
            else
            {
                $data = $policies->refund_amount($userid, $productid, false, $refundpolicy);
                (new \local_exams\local\exams)->exam_unenrollmet($product->referenceid, $userid,$refundtype);
            }*/
            $data = $policies->refund_amount($userid, $productid, false, $refundpolicy);
                (new \local_exams\local\exams)->exam_unenrollmet($product->referenceid, $userid,$refundtype);
           
        } else {
            $data = $policies->refund_details($userid, $productid);
        }

        return $data;
    }
    public function enrolledrole($entitytype, $productid, $refundtype, $userid)
    {
        global $DB;
        $params = [];
        $params['productid'] = $productid;
        $params['userid'] = $userid;
        $product = $DB->get_record('tool_products', ['id' => $productid]);
        $data = (new \local_exams\local\exams)->userexamrefundinfo($params);
        $policies = new \local_exams\local\policies($entitytype, $data->examdate, $refundtype);
        $rolesdata = $policies->enrolled_by($userid, $productid);

        return $rolesdata;
    }
}
