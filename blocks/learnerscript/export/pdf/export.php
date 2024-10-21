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
 * @author: eAbyas Info Solutions
 * @date: 2017
 */
ini_set("memory_limit", "-1");
ini_set('max_execution_time', 6000);
require_once($CFG->dirroot . '/blocks/learnerscript/lib.php');
use block_learnerscript\local\ls;
use tool_brickfield\accessibility;


function export_report($reportclass, $id) {
	global $DB, $CFG;
	$reportdata = $reportclass->finalreport;
	$requestData = $_REQUEST;
	require_once($CFG->libdir . '/pdflib.php');
	$reportname = $DB->get_record('block_learnerscript', array('id' => $id));
	$table = $reportdata->table;
	$matrix = array();
	// $fname == '' ? $filename = 'report' : $filename = $fname . '.pdf';//$fname variable not used anyware
	$reportname->name == $reportdata->name . "_" . Date('d M Y H:i:s', time()) . '.pdf';

	$filters = array();
	foreach ($requestData as $key => $val) {
		if (strpos($key, 'filter_') !== false) {
			$key = explode('_', $key, 2)[1];
			$filters[$key] = $val;
		}
	}
	$finalfilterdata = '';
	$lang = current_language();
    if( $lang == 'ar' ){

    	$style = "text-align:right";

    } else {

		$style = " ";

    }

	foreach ($reportclass->selectedfilters as $k => $filter) {
		$finalfilterdata .= "<div style=\"".$style."\">$k $filter </div>" ;
	}
	if (!empty($table->head)) {
		$countcols = count($table->head);
		$keys = array_keys($table->head);
		$lastkey = end($keys);
	    if( $lang == 'ar' ){
	    	$table->head = array_reverse($table->head);
	    } else {
			$table->head = $table->head;
	    }
		foreach ($table->head as $key => $heading) {
			$matrix[0][$key] = str_replace("\n", ' ', htmlspecialchars_decode(strip_tags(nl2br($heading))));
		}
	}
	if (!empty($table->data)) {
		foreach ($table->data as $rkey => $row) {

		    if( $lang == 'ar' ){
		    	$row = array_reverse($row);
		    	$style = "text-align:right";
		    } else {
				$row = $row;
				$style = " ";
		    }

			foreach ($row as $key => $item) {
				$matrix[$rkey + 1][$key] = str_replace("\n", ' ', htmlspecialchars_decode(strip_tags(nl2br($item))));
			}
		}
	}
	$table = "";
	$table .= "<table border=\"1\" cellpadding=\"5\">";
	$s = count($matrix);
	reset($matrix);
	$first_key = key($matrix);
	$reporttype = $DB->get_field('block_learnerscript',  'type',  array('id' => $id));
	if($matrix){
		if ($reporttype == 'courseprofile' || $reporttype == 'userprofile') {
		} else {
			$table .= "<thead><tr style=\"color:#000000;".$style."\">";
			for ($i = $first_key; $i < ($first_key + 1); $i++) {
				foreach ($matrix[$i] as $col) {
					$table .= "<td>$col</td>";
				}
			}
			$table .= "</tr></thead>";
		}

		$table .= "<tbody>";
		for ($i = ($first_key + 1); $i < count($matrix); $i++) {
			$table .= "<tr style=\"".$style."\">";
			foreach ($matrix[$i] as $col) {
				$table .= "<td>$col</td>";
			}
			$table .= "</tr>";
		}
	}
	$table .= "</tbody></table>";

	/* declaring MYPDF for custom Header and Footer */

	class MYPDF extends TCPDF {

		// Page header
		public function Header() {
			global $DB, $CFG;
			$this->SetY(100);

			$headerData = $this->getHeaderData();
			$this->SetFont('helvetica', 'B', 10);
			$this->Image('pix/header.jpg',0,0,$this->getpageWidth(),0,'JPG');
		}

		// Page footer
		public function Footer() {
			global $DB, $CFG;
			$requestData = $_REQUEST;
			$reportname = $DB->get_record('block_learnerscript', array('id' => $requestData['id']));
			// Position at 15 mm from bottom
			// Set font
			$this->SetFont('helvetica', 'I', 10);
			
			$this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'B');
			$this->SetY(-40);
			$this->image('pix/footer.jpg', 0, '', $this->getpageWidth(), '', 'JPG');			
		}

	}

	$doc = new MYPDF('L', PDF_UNIT, array(210, 297), true, 'UTF-8', false);

	$doc->setPrintHeader(true);
	$doc->setPrintFooter(true);

	// set default header data
	$doc->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE . ' 059', PDF_HEADER_STRING);

	// set header and footer fonts
	$doc->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$doc->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

	// set default monospaced font
	$doc->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	// set margins
	$doc->SetMargins(PDF_MARGIN_LEFT, 65, PDF_MARGIN_RIGHT);
	$doc->SetHeaderMargin(PDF_MARGIN_HEADER);
	$doc->SetFooterMargin(50);

	// set auto page breaks
	$doc->SetAutoPageBreak(TRUE, 42);

	// set image scale factor
	$doc->setImageScale(PDF_IMAGE_SCALE_RATIO);

	// add a page
	$doc->AddPage();

	// set JPEG quality
	$doc->setJPEGQuality(75);

	$head = get_config('block_learnerscript', 'analytics_color');
	$header = (new ls)->pdf_reportheader();

	$headerimgpath = get_reportheader_imagepath();

	$lang = current_language();
    if( $lang == 'ar' ){
    	$x = '250';
    } else {
		$x = '10';
    }

	$doc->writeHTMLCell($w = 0, $h = 50, $x = $x, $y = '10', $header, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
	$doc->writeHTMLCell($w = 100, $h = 10, $x = '135', $y = '50', '<h1><b>' . format_string($reportname->name) . '</b></h1>', $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);

	if( $finalfilterdata ) {

		$doc->writeHTMLCell($w = 0, $h = 10, $x = '10', $y = '35', '<h4 style="'.$style.'">'.get_string('filters','block_learnerscript').'</h4>', $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
		$doc->writeHTMLCell($w = 0, $h = 10, $x = '10', $y = '39', $finalfilterdata, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
	}

	if (COUNT($reportclass->selectedfilters) <= 4) {
		$doc->writeHTMLCell($w = 0, $h = 0, $x = '10', $y = '70', $table, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
	} else {
		$doc->writeHTMLCell($w = 0, $h = 30, $x = '10', $y = '90', $table, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
	}

	$doc->Output($filename, 'I');
}
