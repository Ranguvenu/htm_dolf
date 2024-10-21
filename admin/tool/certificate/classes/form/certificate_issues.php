<?php
// This file is part of the tool_certificate plugin for Moodle - http://moodle.org/
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
 * Issue new certificate for users.
 *
 * @package    tool_certificate
 * @copyright  2018 Daniel Neis Araujo <daniel@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_certificate\form;

use tool_certificate\template;
use tool_certificate\modal_form;
use tool_certificate\certificate as certificate_manager;
use context_system;
/**
 * Certificate issues form class.
 *
 * @package    tool_certificate
 * @copyright  2018 Daniel Neis Araujo <daniel@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class certificate_issues extends modal_form {

    /** @var template */
    protected $template;

    /**
     * Get template
     *
     * @return template
     */
    protected function get_template() : template {
        if ($this->template === null) {
            $tid = $this->_ajaxformdata['tid'];
            $this->template = template::instance($tid);
        }
        return $this->template;
    }
    
    /**
     * Definition of the form with user selector and expiration time to issue certificates.
     */
    public function definition() {
        global $DB;
        $mform = $this->_form;
        
        $examid = $this->optional_param('examid', 0, PARAM_RAW);
        $userid = $this->optional_param('userid', 0, PARAM_RAW);
        $page = $this->optional_param('page', '', PARAM_TEXT);
        $element = $this->optional_param('element', '', PARAM_TEXT);

        $systemcontext = context_system::instance();

        $template_records = certificate_manager::get_templates_for_dropdown();
        
        $templates = [];
        $defaultcategory = '';
        foreach ($template_records as $temps) {
            if(!is_siteadmin()  && has_capability('local/organization:assessment_operator_view',$systemcontext)){
                if ($temps->categoryname == 'Exam') {
                    $templates[$temps->id] = $temps->name;
                    $defaultcategory= $temps->id;
                }
            }
            else {
                $templates[$temps->id] = $temps->name;

                if($temps->categoryname == 'Exam'){
                    $defaultcategory= $temps->id;
                }
            }
        }
        if ($page == 'exam_certificate') {
            $mform->addElement('hidden', 'tid');
            $mform->setType('tid', PARAM_INT);
            $mform->addElement('hidden', 'examid', $examid);
            $mform->addElement('hidden', 'userid', $userid);
            $mform->addElement('hidden', 'page', $page);
            if ($element == 'addbutton') {
                $str = get_string('singleissueconfirm', 'tool_certificate');
            }elseif ($element == 'issueforselectedusers') {
                $str = get_string('confirm_issueforselectedusers', 'tool_certificate');
            }else {
                $str = get_string('confirm_issueforallusers', 'tool_certificate');
            }

            $mform->addElement('html', '<p>'.$str.'<p>');
        }else{
            $mform->addElement('hidden', 'tid');
            $mform->setType('tid', PARAM_INT);

            $tempId = $this->get_template()->get_id();
            if (!$tempId) {
                $attributes  = array(
                    'id' => 'cer_templates',
                    'multiple' => false,
                    'noselectionstring' => get_string('noselection', 'local_trainingprogram'),

                );

                $select = $mform->addElement('autocomplete', 'cer_templates', get_string('selecttemplates', 'tool_certificate'), ['' => get_string('noselection', 'local_trainingprogram')]+$templates, $attributes);
                $select->setSelected($defaultcategory);
                $mform->addRule('cer_templates', get_string('required'), 'required', null, 'client');
                $lang = current_language();
                if ($lang == 'ar') {
                    $fieldname = 'examnamearabic';
                }else{
                    $fieldname = 'exam';
                }
                $exams = $DB->get_records_sql_menu("SELECT id, $fieldname examname FROM {local_exams} WHERE status=1");
                $selectexams = $mform->addElement('autocomplete', 'examlist', get_string('exams', 'local_exams'), ['' => get_string('noselection', 'local_trainingprogram')]+$exams);
            }
            // Users.
            $options = array(
                'ajax' => 'tool_certificate/form-potential-user-selector',
                'multiple' => true,
                'data-itemid' => $this->get_template()->get_id()
            );
            $selectstr = get_string('selectuserstoissuecertificatefor', 'tool_certificate');
            $mform->addElement('autocomplete', 'users', $selectstr, array(), $options);
        }
        certificate_manager::add_expirydate_to_form($mform, $page);
    }

    /**
     * Check if current user has access to this form, otherwise throw exception
     *
     * Sometimes permission check may depend on the action and/or id of the entity.
     * If necessary, form data is available in $this->_ajaxformdata
     */
    public function require_access() {
        $tempId = $this->get_template();
        if ($tempId->get_id()) {
            if (!$tempId->can_issue_to_anybody()) {
                throw new \moodle_exception('issuenotallowed', 'tool_certificate');
            }
        }
    }

    /**
     * Process the form submission
     *
     * This method can return scalar values or arrays that can be json-encoded, they will be passed to the caller JS.
     *
     * @param \stdClass $data
     * @return int number of issues created
     */
    public function process(\stdClass $data) {
        global $DB;
        $i = 0;
        $courseid = '';
        if (!is_array($data->examid)) {
            if ($data->examid) {
                $courseid = $DB->get_field('local_exams', 'courseid', ['id' => $data->examid]);
            }
            $examarray['moduleid'] = $data->examid;
            $examarray['moduletype'] = 'exams';
            $expirydate = certificate_manager::calculate_expirydate($data->expirydatetype, $data->expirydateabsolute,
                $data->expirydaterelative);
            foreach ($data->users as $userid) {
                if ($this->get_template()->can_issue($userid)) {
                    $result = $this->get_template()->issue_certificate($userid, $expirydate, $examarray, 'tool_certificate',
                $courseid);
                    if ($result) {
                        $i++;
                    }
                }
            }
        }
        else{
            for ($i=0; $i < count($data->examid); $i++) { 
                
                $courseid = $DB->get_field('local_exams', 'courseid', ['id' => $data->examid[$i]]);
                
                $examarray['moduleid'] = $data->examid[$i];
                $examarray['moduletype'] = 'exams';
                $expirydate = certificate_manager::calculate_expirydate($data->expirydatetype, $data->expirydateabsolute, $data->expirydaterelative);
                if ($this->get_template()->can_issue($data->users[$i])) {
                    $result = $this->get_template()->issue_certificate($data->users[$i], $expirydate, $examarray, 'tool_certificate',$courseid);
                }
            }
        }
        return $i;
    }

    /**
     * Load in existing data as form defaults
     *
     * Can be overridden to retrieve existing values from db by entity id and also
     * to preprocess editor and filemanager elements
     */
    public function set_data_for_modal() {
        $tid = $this->_ajaxformdata['tid'] ? $this->_ajaxformdata['tid'] : $this->_ajaxformdata['templateid'];
        $this->set_data(['tid' => $tid]);
    }
}
