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

/** LearnerScript Reports
  * A Moodle block for creating customizable reports
  * @package blocks
  * @subpackage learnerscript
  * @author: sowmya<sowmya@eabyas.in>
  * @date: 2016
  */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\pluginbase;
use block_learnerscript\local\reportbase;
use context_system;
use moodle_url;
use html_writer;

class plugin_productinvoicepc extends pluginbase{
    public function init(){
		$this->fullname = get_string('productinvoicepc','block_learnerscript');
		$this->type = 'undefined';
		$this->form = true;
		$this->reporttypes = array('productinvoice');
	}
	public function summary($data){
		return format_string($data->columname);
	}
	public function colformat($data){
		$align = (isset($data->align))? $data->align : '';
		$size = (isset($data->size))? $data->size : '';
		$wrap = (isset($data->wrap))? $data->wrap : '';
		return array($align,$size,$wrap);
	}
	public function execute($data,$row,$user,$courseid,$starttime=0,$endtime=0){
		global $DB;

		switch ($data->column) {

            case 'listitem':
                if($row->category == 1){
                    $query = "SELECT lt.name AS listitem 
                    FROM {tool_product_sadad_invoice} tpsi
                    JOIN {tool_products} tp ON tp.id = tpsi.productid
                    JOIN {tp_offerings} tpo ON tpo.code = tp.code
                    JOIN {local_trainingprogram} lt ON lt.id = tpo.trainingid
                    WHERE tp.category = 1 AND tp.id = $row->productid";
                }else if($row->category == 2){
                    $query = "SELECT le.exam AS listitem
                    FROM mdl_tool_product_sadad_invoice tdsi
                    JOIN mdl_tool_products tp ON tp.id = tdsi.productid
                    JOIN mdl_local_exam_profiles lep ON lep.profilecode = tp.code
                    JOIN mdl_local_exams le ON le.id = lep.examid
                    WHERE tp.category = 2 AND tp.id = $row->productid";

                }else if($row->category == 3){
                    $query = "SELECT le.title AS listitem
                    FROM mdl_tool_product_sadad_invoice tdsi
                    JOIN mdl_tool_products tp ON tp.id = tdsi.productid
                    JOIN mdl_local_events le ON le.code = tp.code
                    WHERE tp.category = 3 AND tp.id = $row->productid";
                    
                }else if($category > 3){
                    $query = "SELECT  0 AS listitem
                    FROM mdl_tool_product_sadad_invoice tdsi
                    JOIN mdl_tool_products tp ON tp.id = tdsi.productid
					WHERE tp.category > 3 AND tp.id = $row->productid 
                    ";
                }else{
                    $query = '';
                }
               $listitem  = !empty($query) ? $DB->get_field_sql($query) : '--';
               $row->{$data->column} = $listitem ? $listitem : '--';

            break;

            case 'type':
                if($row->category == 1){
                    $type = "Training program";
                    
                }else if($row->category == 2){
                    $type = "Exam";
                    
                }else if($row->category == 3){
                    $type = "Event";
                    
                }else if($row->category > 3 ){
                    $type = "--";
                    
                }else{
					$type ='';
				}
				$row->{$data->column} = !empty($type) ? $type : '--' ;

				break;
            
            case 'paymenttype':
                if(isset($row->paymenttype) && !empty($row->paymenttype)){
                    $types = get_string($row->paymenttype, 'block_learnerscript');
                }else{
                    $types = '--';
                }
                $row->{$data->column} = $types;
                break;
            case 'paymentstatus':

                if(isset($row->payment_status) && !empty($row->payment_status)){
                    $paymentstatus =  ($row->payment_status == 1) ? get_string('paid', 'block_learnerscript') : get_string('unpaid', 'block_learnerscript');
                }else{
                    $paymentstatus = '--';
                }
                
                $row->{$data->column} = $paymentstatus;
                break;
            case 'status':
                if(!empty($row->status)){
                    $status = ($row->status == 1) ? get_string('active', 'block_learnerscript') : get_string('inactive', 'block_learnerscript');
                }else{
                    $status = '--';
                }
                $row->{$data->column} = $status;
                break;
            case 'amount':
                $amount = !empty($row->amount) ? $row->amount : '--';
                $row->{$data->column} = $amount;

                break;


			}

		return (isset($row->{$data->column}))? $row->{$data->column} : '';
	}
}
