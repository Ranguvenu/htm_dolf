<?php
namespace local_trainingprogram\form;

use core_form\dynamic_form ;
use moodle_url;
use context;
use context_system;
use stdClass;
use \core_availability\tree;
use \availability_group\condition;
use local_trainingprogram\local\trainingprogram as tp;
require_once($CFG->dirroot.'/group/lib.php'); 
require_once($CFG->dirroot.'/course/lib.php');

class addsection_form extends dynamic_form { 
    public function definition() {
        global $USER, $CFG,$DB, $OUTPUT;
        $corecomponent = new \core_component();
        $offeringid = $this->optional_param('offeringid', 0, PARAM_INT);
        $offeringcode = $this->optional_param('offeringcode', 0, PARAM_RAW);
        $mform = $this->_form;
        $methods = [];
        $mform->addElement('hidden', 'offeringid', $offeringid);
        $mform->setType('offeringid',PARAM_INT);

        $mform->addElement('hidden', 'offeringcode', $offeringcode);
        $mform->setType('offeringcode',PARAM_RAW);

        $offeringrecord =$DB->get_record('tp_offerings',array('id'=>$offeringid));
        $courseid = $DB->get_field('local_trainingprogram' ,'courseid',['id' =>$offeringrecord->trainingid]);

        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid',PARAM_INT);

        $sections1 = [];
        $sections2 = [];
        $offering_section = $DB->get_records_sql(" SELECT tp.sections,cs.id,cs.name FROM {tp_offerings} tp JOIN {course_sections} cs ON cs.id = tp.sections WHERE tp.id = $offeringid ");
                 
                foreach($offering_section as $offering_s){
                    $sections2[$offering_s->id]=$offering_s->id;
                } 
        if(empty($sections2)) {
            $section_sql= " SELECT cs.id, cs.name FROM mdl_course_sections cs WHERE cs.id NOT IN (SELECT sections FROM mdl_tp_offerings too JOIN mdl_local_trainingprogram tp ON tp.id = too.trainingid WHERE tp.courseid = $courseid) AND cs.course = $courseid AND cs.section <> 0 ";
            $sections = $DB->get_records_sql($section_sql);
            if($sections) {
                 $sections1[''] = get_string('selectsections','local_trainingprogram');
                foreach($sections as $section){
                    $sections1[$section->id]=($section->name)?$section->name:$section->id;
                }
            }
        } else {

            $sections1=$sections2;
           
        }

        
     
        $mform->addElement('select', 'sections', get_string('sections', 'local_trainingprogram'),$sections1);
     
       
       
    }
    public function validation($data, $files) {
        global $DB, $CFG;
        $errors = array();
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
         has_capability('local/trainingprogram:manage', $this->get_context_for_dynamic_submission()) 
        || has_capability('local/organization:manage_trainingofficial', $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/user/profile/definelib.php');
        $data = $this->get_data();

      

        $offeringrecord =$DB->get_record('tp_offerings',array('id'=>$data->offeringid));

        $offeringrecord->sections=$data->sections;

        $DB->update_record('tp_offerings',$offeringrecord);
 

        $group = $DB->get_record_sql("SELECT * FROM {groups} WHERE idnumber = '$data->offeringcode'");
        
        $section = $DB->get_record_sql("SELECT * FROM {course_sections} WHERE id = $data->sections");

        

        if($group){ 
            $groupid = $group->id; 
            $json = \core_availability\tree::get_root_json(array( \availability_group\condition::get_json($groupid)), \core_availability\tree::OP_AND, false); 
        } 


        $groupdata = new stdClass; 
        $groupdata->availability = json_encode($json); 

        
        course_update_section($data->courseid, $section, $groupdata);

    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;

        if ($offeringid = $this->optional_param('offeringid', 0, PARAM_INT)) {
            $data = $DB->get_records('tp_offerings', ['id' => $offeringid]);
            $formdata = new \stdClass();
            $this->set_data($formdata);
        }
        
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        return new moodle_url('/local/trainingprogram/index.php');
    }    
}
