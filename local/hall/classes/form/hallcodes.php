<?php
namespace local_hall\form;

use core_form\dynamic_form;
use moodle_url;
use context;
use context_system;
use local_hall;

class hallcodes extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB;
        $systemcontext = context_system::instance();
        $corecomponent = new \core_component();

        $mform = $this->_form;
        $id = $this->_customdata['id'];
        $editoroptions = $this->_customdata['editoroptions'];
         
        $systemcontext = context_system::instance();

        $mform->addElement('hidden', 'hallid', $id);
        $mform->setType('hallid',PARAM_INT);

        $records =$DB->get_records_sql("SELECT DISTINCT ownedby FROM {local_exams} WHERE ownedby IS NOT NULL AND ownedby !=''");
        foreach($records AS $record ) {
            $elementlable =str_replace(' ','_',$record->ownedby);
            
            $naminglable = format_text($record->ownedby, FORMAT_HTML);


            $mform->addElement('text',$elementlable,$naminglable,array('class' => 'hallcodes','size="40"'));
            $mform->setType($elementlable,PARAM_RAW);
        }
    }
    public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;

        return $errors;
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
     * Checks if current user has access to this form, otherwise throws exception
     */
    protected function check_access_for_dynamic_submission(): void {
        //require_capability('moodle/site:config', $this->get_context_for_dynamic_submission());
         has_capability('local/hall:managehall', $this->get_context_for_dynamic_submission()) 
        || has_capability('local/organization:manage_hall_manager', $this->get_context_for_dynamic_submission());
    }
    

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/user/profile/definelib.php');
        $data = $this->get_data();
        (new local_hall\hall)->update_hallcodes($data);
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
        if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $hallcodes = $DB->get_field('hall', 'hallcodes', ['id'=> $id]);
            $hallinfo = json_decode($hallcodes);
            if ($hallinfo) {
                $this->set_data($hallinfo);
            } else {
                $this->set_data(['hallid'=>$id]);
            }

        }
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $id = $this->optional_param('id', 0, PARAM_INT);
        return new moodle_url('/local/hall/index.php',
            ['action' => 'editcategory', 'id' => $id]);
    }    
}
