<?php
ini_set("memory_limit", "-1");
ini_set('max_execution_time', 6000);
require_once('../../../config.php');
global $DB, $CFG;
require_once($CFG->libdir . '/pdflib.php');
$invoice = required_param('invoice', PARAM_RAW);
$PAGE->set_context(context_system::instance());
define ('PDF_PAGE_FORMAT', 'A4');
define ('PDF_PAGE_ORIENTATION', 'P');

$invoiceinfo = unserialize(base64_decode($invoice));
$description = base64_decode($invoiceinfo['description']);
$name = base64_decode($invoiceinfo['name']);

class MYPDF extends TCPDF {
    public function Header() {
        global $DB, $CFG;
        $id = required_param('id', PARAM_INT);
        $this->SetFont('helvetica', 'B', 9);
        $this->SetTextColor(167, 147, 68);
    }
    public function Footer() {
        $this->SetY(-10);
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(0, 10, 'Page', 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

$pdf = new MYPDF('P', PDF_UNIT, array(210, 297), true, 'UTF-8', false);
$pdf->SetFont("Helvetica", 'N', 10);
$pdf->SetFont("helveticaneueltarabiclight", 'N', 10);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$tagvs = array(
'divp' => array(0 => array('h' =>0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)),
'p' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)),
'h1' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)),
'h2' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)),
'h3' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)),
'h4' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)),
'h5' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)),
'br' => array(0 => array('h' => 1, 'n' => 0), 1 => array('h' => 1, 'n' => 0))); 
$pdf->setHtmlVSpace($tagvs);

$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// add a page
$pdf->AddPage();

$heading= '<span style="font-size:10px;">'.get_string('invoicetax','tool_product').'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span>&nbsp;&nbsp;&nbsp;'.get_string('invoicetaxar','tool_product').'</span>';
$pdf->writeHTMLCell(150,0,80,10,$heading,0,1,0, true,'', true);

// logo
$pdf->Image('pix/invoiceimages/logo.jpeg',25,28,20,20,'JPG');

$timedetails .= '<table style="width:100%; border: none; vertical-align: middle;font-size:8px;" cellspacing="0" cellpadding="0">
<tr><td>'. get_string('issuedate','tool_product')  .'</td><td>'.$invoiceinfo['pdftimecreated'].'</td><td>'.get_string('issuedatear','tool_product').'</td></tr>
<tr><td>'. get_string('supplydate','tool_product')  .'</td><td>'.$invoiceinfo['pdftimecreated'].'</td><td>'.get_string('supplydatear','tool_product').'</td></tr>
<tr><td>'. get_string('duedate','tool_product')  .'</td><td>'.$invoiceinfo['duedate'].'</td><td>'.get_string('duedatear','tool_product').'</td></tr>
<tr><td>'. get_string('customer','tool_product')  .'</td><td>'.$invoiceinfo['organizationname'].'</td><td>'.get_string('customerar','tool_product').'</td></tr>    
    </table>
    <p></p>';

$pdf->writeHTMLCell(0,0,120,30,$timedetails,0,1,0, true,'', true);

$invoice= '<span style="font-size:8px;">'.get_string('invoicear','tool_product').'<br><div>'. get_string('invoice','tool_product').'</span>';
$pdf->writeHTMLCell(200,0,10,60,$invoice,0,1,0, true,'', true);

$invoicenumber= '<span style="font-size:8px;">'.$invoiceinfo["transactionid"].'</span>';
$pdf->writeHTMLCell(200,0,28,62,$invoicenumber,0,1,0, true,'', true);

$purcahseinfo= '<table><tr><td>'.get_string('soldto','tool_product').'</td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<td>'.get_string('soldto_ar','tool_product').' </td></tr>
<td style="font-size:8px;">'.$invoiceinfo['organizationname'].'</td></tr>
<tr><td style="font-size:8px;">'.$invoiceinfo['orgnamear'].'</td></tr>
<tr><td style="font-size:8px;">'.$invoiceinfo['username'].' -- '.$invoiceinfo['usernamear'].'</td></tr><table>';
$pdf->writeHTMLCell(150,0,10,75,$purcahseinfo,0,1,0, true,'', true);

$pdf->Image('pix/invoiceimages/envelope.jpg',11,94,3,3,'JPG');

$email= '<span style="font-size:8px;">'.$invoiceinfo['email'].'</span>';
$pdf->writeHTMLCell(150,0,14,93.5,$email,0,1,0, true,'', true);

$vatinfo= '<table><tr><td style="font-size:8px;">'.get_string('vatnumber','tool_product').'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>--</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>'.get_string('vatnumberar','tool_product').'</span></td></tr></table>';
$pdf->writeHTMLCell(150,0,10,98,$vatinfo,0,1,0, true,'', true);

$orderinfo = '<table class="table" style="width:100%; border: none; vertical-align: middle" cellspacing="0" cellpadding="3">
<thead class="thead-light">
  <tr style="font-size:6px;text-align:centerpadding-left:10px;padding-right:10px;border: 1px groove #777;background-color:#f3f3f3">
    <th scope="col" style="border: 1px groove #777;width:3%;">#</th>
    <th style="border: 1px groove #777;width:10%;">'.get_string('itemcode', 'tool_product').'<div>'.get_string('itemcodear', 'tool_product').'</div></th>
    <th style="border: 1px groove #777;width:20%;">'.get_string('description', 'tool_product').'<div>'.get_string('descriptionar', 'tool_product').'</div></th>
    <th style="border: 1px groove #777;width:8%;">'.get_string('qty', 'tool_product').'<div>'.get_string('qtyar', 'tool_product').'</div></th>
    <th style="border: 1px groove #777;width:8%;">'.get_string('unitprice', 'tool_product').'<div>'.get_string('unitpricear', 'tool_product').'</div></th>
    
    <th style="border: 1px groove #777;width:8%;">'.get_string('discount', 'tool_product').'<div>'.get_string('discountar', 'tool_product').'</div></th>
    <th style="border: 1px groove #777;width:8%;">'.get_string('discountamount', 'tool_product').'<div>'.get_string('discountamountar', 'tool_product').'</div></th>
    <th style="border: 1px groove #777;width:8%;">'.get_string('vat', 'tool_product').'<div>'.get_string('vatar', 'tool_product').'</div></th>
    <th style="border: 1px groove #777;width:9%;">'.get_string('vatamount', 'tool_product').'<div>'.get_string('vatamountar', 'tool_product').'</div></th>
    <th style="border: 1px groove #777;width:9%;">'.get_string('amount', 'tool_product').'<div>'.get_string('amountar', 'tool_product').'</div></th>        
  </tr>
</thead>
<tbody>
  <tr style="font-size:6px;">
    <td scope="row" style="border: 1px groove #777;width:3%;">1</td>
    <td style="border: 1px groove #777;width:10%;">'.$invoiceinfo['itemcode'].'</td>
    <td style="border: 1px groove #777;width:20%;">'.$name.'</td>
    <td style="border: 1px groove #777;width:8%;">'.$invoiceinfo['purchasedseats'].' '.get_string('unit', 'tool_product').'/'.get_string('unitar', 'tool_product').'</td>
    <td style="border: 1px groove #777;width:8%;">'.$invoiceinfo['unitprice'].' SAR </td>
    <td style="border: 1px groove #777;width:8%;">'.$invoiceinfo['discount_percentage'].' %</td>
    <td style="border: 1px groove #777;width:8%;">'.$invoiceinfo['discount_price'].' SAR </td>
    <td style="border: 1px groove #777;width:8%;">'.$invoiceinfo['taxes_percentage'].' %</td>
    <td style="border: 1px groove #777;width:9%;">'.$invoiceinfo['taxes'].' SAR </td>
    <td style="border: 1px groove #777;width:9%;">'.$invoiceinfo['finaltotalamout'].' SAR </td>
  </tr>
</tbody>
</table>';

$pdf->writeHTMLCell(0,0,10,110,$orderinfo,0,1,0, true,'', true);
$pdf->Output('event.pdf', 'I');
