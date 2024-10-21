<?php 
namespace tool_certificate\local;
use moodle_exception;
use stdClass;
use dml_exception;
use html_writer;
use moodle_url;
use context_system;
use context_user;
use core_user;
use filters_form;

class certificate
{

    public function certificates_view() {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $renderer = $PAGE->get_renderer('tool_certificate');
        $filterparams  = $renderer->view_certificates(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-6';
        $filterparams['placeholder'] = get_string('search','tool_certificate');
        $globalinput=$renderer->global_filter($filterparams);
        $certicifates = $renderer->view_certificates();
        $fform = tool_certificates_filters_form($filterparams);
        $filterparams['certicifates'] = $certicifates;
        $filterparams['filterform'] = $fform->render();
        $filterparams['globalinput'] = $globalinput;
        $renderer->listofcertificates($filterparams);

    } 
}