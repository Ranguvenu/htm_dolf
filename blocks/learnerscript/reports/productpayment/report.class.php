<?php
// This file is part of Moodle - http://moodle.org/
//
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
 * LearnerScript Reports
 * A Moodle block for creating customizable reports
 * @package blocks
 * @author: eAbyas Info Solutions
 * @date: 2017
 */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\reportbase;
use block_learnerscript\local\querylib;
use block_learnerscript\local\ls as ls;
use stdClass;

class report_productpayment extends reportbase{
    /**
     * [__construct description]
     * @param [type] $report           [description]
     * @param [type] $reportproperties [description]
     */
    public function __construct($report, $reportproperties) {
        parent::__construct($report);
        $this->parent = false;
        $this->courselevel = true;
        $this->components = array('columns', 'filters', 'permissions', 'plot');
        $columns = ['trainingname', 'totalprice', 'refundprice', 'paymentdate'];
        $this->columns = ['productpaymentcolumn' => $columns];
        $this->searchable = array( 'tpr.amount');
        $this->orderable = array('tpr.timecreated', 'tpr.amount');
        $this->defaultcolumn = 'tpr.id';
        $this->excludedroles = array("'student'");
    }
    function init() {
        global $DB;
       
    }

    function count() {
        $this->sql = "SELECT COUNT(DISTINCT telr.productdata) ";
    }

    function select() {
        $this->sql = " SELECT telr.productdata, tpr.timecreated, tpr.amount";
        parent::select();
      }

    function from() {
        $this->sql .= " FROM {tool_product_telr} telr";
    }

    function joins() {
        $this->sql .= " JOIN {tool_product_refund} tpr ON tpr.transactionid = telr.id";
        parent::joins();
    }

    function where() { 
        $this->sql .= "  WHERE 1=1";
        parent::where();

    }
    

    function search() {
        global $DB; 

        
    }

    function filters() {
        global $DB;



    }
    function groupby() {
    }
    /**
     * [get_rows description]
     * @param  array  $users [description]
     * @return [type]        [description]
     */
    public function get_rows($elements) {
        global $DB, $CFG, $USER, $OUTPUT;

        $finalelements = array();
        if (!empty($elements)){
            foreach($elements as $element){
                $elementlist = unserialize(base64_decode($element->productdata))['items'];

                foreach($elementlist as $list){
                    $report = new stdClass();
                    $report->trainingname = $list['name'];
                    $report->totalprice = $list['total'];
                    $report->refundprice = $element->amount;
                    $report->paymentdate = date('d M Y',$element->timecreated);
                }
                $data[] = $report;
            }

            return $data;
        }
        return $finalelements;
    }
}
