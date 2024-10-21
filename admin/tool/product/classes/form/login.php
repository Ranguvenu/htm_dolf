<?php
namespace tool_product\form;

use core_form\dynamic_form ;
use moodle_url;
use context;
use context_system;

class login extends dynamic_form{

	/**
     * Define the form
     */
    public function definition () {

    	global $CFG, $DB;

    	$mform = $this->_form;

    	$mform->addElement('text','username', get_string('username', 'tool_product'));
        $mform->addRule('username', get_string('required'), 'required', null, 'server');
        $mform->setType('username', PARAM_TEXT);

        $mform->addElement('password','password', get_string('password', 'tool_product'));
        $mform->addRule('password', get_string('required'), 'required', null, 'server');
        $mform->setType('password', PARAM_TEXT);
    }

    /**
     * Returns context where this form is used
     *
     * @return context
     */
    protected function get_context_for_dynamic_submission(): context {
        return \context_system::instance();
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
    	
    }
}
