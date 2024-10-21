<?php
ini_set("memory_limit", "-1");
ini_set('max_execution_time', 6000);
require_once('../../config.php');
global $DB, $CFG;
require_once($CFG->libdir . '/pdflib.php');
require_once($CFG->dirroot . '/local/events/lib.php');
use block_learnerscript\local\ls;
$id = required_param('id', PARAM_INT);
$PAGE->set_context(context_system::instance());
$renderer = $PAGE->get_renderer('local_events');
$event = $renderer->event_check($id);
$eventdata = $renderer->get_eventscontent($id);
define ('PDF_PAGE_FORMAT', 'A4');
define ('PDF_PAGE_ORIENTATION', 'P');

$PAGE->requires->css(new moodle_url('/local/events/styles.css'));

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

$renderer = $PAGE->get_renderer('local_events');
$event = $renderer->get_eventscontent($id);

$agendadetails = $event['event']->agendadata;

$lang = current_language();
if($lang == 'ar') {
    $title = $event['event']->titlearabic;
} else {
    $title = $event['event']->title;
}
// add a page
$pdf->AddPage();

$pdf->Image('pix/pdfimages/mainbg.jpg',5,5,200,60,'JPG');
$heading1= '<p style="color:#fff;font-size:18px;font-weight:bold ">'. $title .'</p>';
$pdf->writeHTMLCell(150,0,10,15,$heading1,0,1,0, true,'', true);
$pdf->writeHTMLCell(30,0,167,20,'<p style="color:#fff; font-size:14px">'. get_string('eventfee','local_events') .'</p>',0,1,0, true,'', true);

$pdf->writeHTMLCell(30,0,164,30,'<h3 style="color:#fff; font-size:25px; text-align:center">'. $event['event_sellingprice'] .'</h3>',0,1,0, true,'', true);

if($lang == 'ar') {
    $pdf->writeHTMLCell(30,0,165,45,'<p style="color:#fff; font-size:14px">'. get_string('sar','local_events') .'</p>',0,1,0, true,'', true);
} else {
    $pdf->writeHTMLCell(30,0,173,45,'<p style="color:#fff; font-size:14px">'. get_string('sar','local_events') .'</p>',0,1,0, true,'', true);
}

//ob_end_clean();
$pdf->Image('pix/pdfimages/hexabg.jpg',10,68,20,18,'JPG');
$pdf->writeHTMLCell(150,0,10,72,'<h5 style="color:#004c98; font-size:14px;font-weight:bold">'. get_string('eventdetails','local_events') .'</h5>',0,1,0, true,'', true);
$pdf->writeHTMLCell(0,0,10,75,'
<table style="width:100%; border: none;" cellspacing="0" cellpadding="0">
        <tr>
            <td width="18%">
                <div>
                    <div class="d-flex align-items-center mb-2">'. get_string('eventstart','local_events') .'</div>
                    <h5 style="font-size: 10px">'. $event['event']->startdate .'</h5>
                </div>
            </td>
            <td width="19%">
                <div class="">
                    <div class="d-flex align-items-center mb-2">'. get_string('eventend','local_events') .'</div>
                    <h1 style="font-size: 10px">'. $event['event']->enddate .'</h1>
                </div>
            </td>
            <td width="35%">
                <div class="">
                    <div class="d-flex align-items-center mb-2">'. get_string('regstart','local_events') .'</div>
                    <h5 style="font-size: 10px">'. $event['event']->reg_startdate .'</h5>
                </div>
            </td>
            <td width="33%">
                <div class="">
                    <div class="d-flex align-items-center mb-2">'. get_string('regend','local_events') .'</div>
                    <h5 style="font-size: 10px">'. $event['event']->reg_enddate .'</h5>
                </div>
            </td>
            
        </tr>
    </table><hr>',0,1,0, true,'', true);

// about the event
$pdf->Image('pix/pdfimages/hexabg.jpg',10,108,20,18,'JPG');
$pdf->writeHTMLCell(150,0,10,112,'<h3 style="color:#004c98; font-size:14px">'. get_string('aboutevent','local_events') .'</h3>',0,1,0, true,'', true);
$pdf->writeHTMLCell(0,0,10,123,'<p style="color:#000; font-size:10px">'. strip_tags($event['event']->description) .'</p>',0,1,0, true,'', true);

// event highlights
$pdf->Image('pix/pdfimages/eventhighlightsbg.jpg',10,145,190,30,'JPG');
$pdf->writeHTMLCell(150,0,15,150,'<h3 style="color:#fff; font-size:14px">'. get_string('language','local_events') .'</h3>',0,1,0, true,'', true);
$pdf->writeHTMLCell(50,0,15,160,'<p style="color:#fff; font-size:10px">'. $event['event']->language .'</p>',0,1,0, true,'', true);
$pdf->writeHTMLCell(150,0,120,150,'<h3 style="color:#fff; font-size:14px">'. get_string('gender','local_events') .'</h3>',0,1,0, true,'', true);
$pdf->writeHTMLCell(80,0,120,160,'<p style="color:#fff; font-size:10px">'. $event['event']->gender.'('. get_string('separatedlocations','local_events') .')</p>',0,1,0, true,'', true);

// event agenda 
$pdf->Image('pix/pdfimages/hexabg.jpg',10,180,20,18,'JPG');
$pdf->writeHTMLCell(150,0,10,185,'<h3 style="color:#004c98; font-size:14px">'. get_string('eventagenda','local_events') .'</h3>',0,1,0, true,'', true);

$agendatable = '';
$count = 1;
foreach($agendadetails as $agenda) {
    $agendatable .= '<table style="width:100%; border: none; vertical-align: middle" cellspacing="0" cellpadding="0">
        <tr>
            <th style="background-color:#004c98; color:#fff; font-weight:bold; font-size:10; ">
                <p style="line-height:2.5;padding-left:10px;padding-right:10px;">&nbsp;&nbsp; '. get_string('eventsagenda','local_events', $count++) .' : '. $agenda['agendatopic'] .' &nbsp;&nbsp;</p>
            </th>
        </tr>
        <tr>
            <td style="background-color:#eee; color:#000;">
                <p style="line-height:2.5"></p>  
                <table style="width:100%; border: none; vertical-align: middle" cellspacing="0" cellpadding="0">  
                    <tr>
                        <td style="width:25%">
                            <table style="border: none; vertical-align: middle" cellspacing="0" cellpadding="0">
                                <tr><td style="width:22px"><img src="pix/pdfimages/calendericon.jpg" width="15" height="15"></td><td><p style="line-height:1.6">'. $agenda['agendaday'] .'</p></td></tr>
                            </table>
                        </td>
                        <td style="width:38%">
                            <table style="border: none; vertical-align: middle" cellspacing="0" cellpadding="0">
                                <tr><td style="width:22px"><img src="pix/pdfimages/eventtopictimeicon.jpg" width="15" height="15"></td><td><p style="line-height:1.6">'. $agenda['agendatime'] .'</p></td></tr>
                            </table>
                        </td>
                        <td style="width:35%">
                            <table style="border: none; vertical-align: middle" cellspacing="0" cellpadding="0">
                                <tr><td style="width:22px"><img src="pix/pdfimages/eventtopicspeakericon.jpg" width="15" height="15"></td><td><p style="line-height:1.6">'. $agenda['agendaspeaker'] .'</p></td></tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <p style="line-height:1"></p>  
            </td>
        </tr>
    </table>
    <p></p>';

}
$pdf->writeHTMLCell(0,0,10,200,$agendatable,0,1,0, true,'', true);

$partnerlength = 200+(COUNT($agendadetails)*35);

$pdf->AddPage();
$partnerlength = 10;

// Event Partners
$pdf->Image('pix/pdfimages/hexabg.jpg',10, $partnerlength-5,20,18,'JPG');
$pdf->writeHTMLCell(0,0,10,$partnerlength,'<h3 style="color:#004c98; font-size:14px">'. get_string('eventpartners','local_events') .'</h3>',0,1,0, true,'', true);

$partners = $event[event]->partnerdata;
$partnery = 10;
$partnerlength = $partnerlength+15;
foreach($partners as $partner) {
    $partnerinfo = $partner[partnerlogo];

    if($partnery > 190) {
        $partnery = 10;
        $partnerlength = $partnerlength + 40;
    }

    if($partnerlength >= 250 && $partnerlength < 297) {
        $pdf->AddPage();
        $partnerlength = 5;
    } elseif($partnerlength > 297) {
        $partnerlength = $partnerlength-297;
        $partnerlength = $partnerlength + 15;
    }

    $pdf->Image($partnerinfo,$partnery,$partnerlength,40,35,'JPG');
    $partnery = $partnery+65;
}

$partnerlength = $partnerlength+40;

if($partnerlength >= 250 && $partnerlength < 297) {
    $pdf->AddPage();
    $partnerlength = 5;
} elseif($partnerlength > 297) {
    $partnerlength = $partnerlength-297;
    $partnerlength = $partnerlength + 15;
}

// Event Platinum Sponsors
$pdf->Image('pix/pdfimages/hexabg.jpg',10,$partnerlength+5,20,18,'JPG');
$pdf->writeHTMLCell(0,0,10,$partnerlength+10,'<h3 style="color:#004c98; font-size:14px">'. get_string('eventsponsorscatplatinum','local_events') .'</h3>',0,1,0, true,'', true);

$partnerlength = $partnerlength+25;
$sponsors = $event[event]->platinum_sponsordata;
$sponsory = 10;
foreach($sponsors as $sponsor) {
    $sponsorinfo = $sponsor[sponsorlogo];

    if($sponsory > 190) {
        $sponsory = 10;
        $partnerlength = $partnerlength + 40;
    }

    if($partnerlength >= 250 && $partnerlength < 297) {
        $pdf->AddPage();
        $partnerlength = 5;
    } elseif($partnerlength > 297) {
        $partnerlength = $partnerlength-297;
        $partnerlength = $partnerlength + 15;
    }

    $pdf->Image($sponsorinfo,$sponsory,$partnerlength,40,35,'JPG');
    $sponsory = $sponsory+65;
}

$partnerlength = $partnerlength+40;

if($partnerlength >= 250 && $partnerlength < 297) {
    $pdf->AddPage();
    $partnerlength = 5;
} elseif($partnerlength > 297) {
    $partnerlength = $partnerlength-297;
    $partnerlength = $partnerlength + 15;
}

// Event Gold Sponsors
$pdf->Image('pix/pdfimages/hexabg.jpg',10,$partnerlength+5,20,18,'JPG');
$pdf->writeHTMLCell(0,0,10,$partnerlength+10,'<h3 style="color:#004c98; font-size:14px">'. get_string('eventsponsorscatgold','local_events') .'</h3>',0,1,0, true,'', true);

$partnerlength = $partnerlength+25;
$sponsors = $event[event]->gold_sponsordata;
$sponsory = 10;
foreach($sponsors as $sponsor) {
    $sponsorinfo = $sponsor[sponsorlogo];

    if($sponsory > 190) {
        $sponsory = 10;
        $partnerlength = $partnerlength + 40;
    }

    if($partnerlength >= 250 && $partnerlength < 297) {
        $pdf->AddPage();
        $partnerlength = 5;
    } elseif($partnerlength > 297) {
        $partnerlength = $partnerlength-297;
        $partnerlength = $partnerlength + 15;
    }

    $pdf->Image($sponsorinfo,$sponsory,$partnerlength,40,35,'JPG');
    $sponsory = $sponsory+65;
}

$partnerlength = $partnerlength+40;

if($partnerlength >= 250 && $partnerlength < 297) {
    $pdf->AddPage();
    $partnerlength = 5;
} elseif($partnerlength > 297) {
    $partnerlength = $partnerlength-297;
    $partnerlength = $partnerlength + 35;
}

// Event Silver Sponsors
$pdf->Image('pix/pdfimages/hexabg.jpg',10,$partnerlength+5,20,18,'JPG');
$pdf->writeHTMLCell(0,0,10,$partnerlength+10,'<h3 style="color:#004c98; font-size:14px">'. get_string('eventsponsorscatsilver','local_events') .'</h3>',0,1,0, true,'', true);

$partnerlength = $partnerlength+25;

$sponsors = $event[event]->silver_sponsordata;
$sponsory = 10;
foreach($sponsors as $sponsor) {
    $sponsorinfo = $sponsor[sponsorlogo];

    if($sponsory > 190) {
        $sponsory = 10;
        $partnerlength = $partnerlength + 40;
    }

    if($partnerlength >= 250 && $partnerlength < 297) {
        $pdf->AddPage();
        $partnerlength = 5;
    } elseif($partnerlength > 297) {
        $partnerlength = $partnerlength-297;
        $partnerlength = $partnerlength + 35;
    }

    $pdf->Image($sponsorinfo,$sponsory,$partnerlength,40,35,'JPG');
    $sponsory = $sponsory+65;
}
$pdf->Output('event.pdf', 'I');
