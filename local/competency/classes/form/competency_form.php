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

namespace local_competency\form;

use context;
use context_system;
use moodle_exception;
use moodle_url;
use core_form\dynamic_form;
use local_competency\competency;
use local_competency\external;

require_once($CFG->libdir . '/formslib.php');

/**
 * Competency modal form
 *
 * @package    local_competency
 * @copyright  2022 e abyas  <info@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class competency_form extends dynamic_form {

    /**
     * Form definition
     */
    protected function definition() {
        global $CFG, $DB;

        $mform = $this->_form;

        $id = $this->optional_param('id', 0, PARAM_INT);



        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        if($id > 0)  {
        
            $is_competence_mapped = competency::is_competence_mapped($id);

            $code = $DB->get_field('local_competencies','code',array('id'=>$id));

            $mform->addElement('static', 'comptency_code', get_string('competency_code', 'local_competency'),$code);
            
            $mform->addElement('hidden', 'code',$code);

         } else {

           $mform->addElement('text', 'code', get_string('competency_code', 'local_competency'), array());
           $mform->setType('code', PARAM_TEXT);
           $mform->addRule('code', null, 'required', null);
        }   

       

        $mform->addElement('text', 'name', get_string('competency_nameeng', 'local_competency'), array());
        $mform->setType('name', PARAM_TEXT);
        
        $mform->addRule('name', null, 'required', null);

        $mform->addElement('text', 'arabicname', get_string('competency_namearabic', 'local_competency'), array());
        $mform->setType('arabicname', PARAM_TEXT);
        $mform->addRule('arabicname', null, 'required', null);
  

        $competencytypes =array();

        if($this->_ajaxformdata['type'] || $id > 0){

            $competencytypes = competency::constcompetencytypes();

        }

        if($id > 0)  {

            $lang  = current_language();


            $is_competence_mapped = competency::is_competence_mapped($id);

            $competency_types = competency::competency_types(); 

            if($is_competence_mapped) {

                $record = $DB->get_record('local_competencies',array('id'=>$id));
               


               $mform->addElement('static', 'comptency_type', get_string('competency_type', 'local_competency'),$competency_types[$record->type],$competency_typesoptions);

               if($lang == 'ar') {

                  $level_display = str_replace("level","المستوى",str_replace(",",", ",$record->level));
               } else {

                 $level_display = str_replace("level","Level ",str_replace(",",", ",$record->level));
               }
     
              
               $mform->addElement('static', 'comptency_level', get_string('competency_level', 'local_competency'),$level_display);

                $mform->addElement('hidden', 'type',$record->type);
                $mform->addElement('hidden', 'level',$record->level);


            } else {


                $competencytypeoptions = [
                        'ajax' => 'local_competency/form_competency_selector',
                        'data-action' => 'competency_types',
                        'noselectionstring' => get_string('noselection', 'local_competency'),
                ];
         
                $mform->addElement('autocomplete', 'type', get_string('competency_type', 'local_competency'),$competencytypes,$competencytypeoptions);
                $mform->setType('type', PARAM_ALPHANUMEXT);
                $mform->addRule('type', null, 'required', null);

                $mform->addElement('text', 'add_type', get_string('title', 'local_competency'), array());
                $mform->setType('add_type', PARAM_TEXT);
                
                $mform->hideif('add_type', 'type', 'neq', competency::OTHER);

                $competencylevels =array();

                if($this->_ajaxformdata['level'] || $id > 0){

                    $competencylevels = competency::constcompetencylevels();

                }   
            
                $competencyleveloptions = [
                        'ajax' => 'local_competency/form_competency_selector',
                        'data-action' => 'competency_levels',
                        'multiple' => true,
                        'noselectionstring' => get_string('noselection', 'local_competency'),
                ];
         
                $mform->addElement('autocomplete', 'level', get_string('competency_level', 'local_competency'),$competencylevels,$competencyleveloptions);
                $mform->setType('level', PARAM_ALPHANUMEXT);
                $mform->addRule('level', null, 'required', null);

                $mform->addElement('text', 'add_level', get_string('title', 'local_competency'), array());
                $mform->setType('add_level', PARAM_TEXT);
                
                $mform->hideif('add_level', 'level', 'neq', competency::OTHER);
            }



        } else {


            $competencytypeoptions = [
                    'ajax' => 'local_competency/form_competency_selector',
                    'data-action' => 'competency_types',
                    'noselectionstring' => get_string('noselection', 'local_competency'),
            ];
     
            $mform->addElement('autocomplete', 'type', get_string('competency_type', 'local_competency'),$competencytypes,$competencytypeoptions);
            $mform->setType('type', PARAM_ALPHANUMEXT);
            $mform->addRule('type', null, 'required', null);

            $mform->addElement('text', 'add_type', get_string('title', 'local_competency'), array());
            $mform->setType('add_type', PARAM_TEXT);
            
            $mform->hideif('add_type', 'type', 'neq', competency::OTHER);

            $competencylevels =array();

            if($this->_ajaxformdata['level'] || $id > 0){

                $competencylevels = competency::constcompetencylevels();

            }   
        

            $competencyleveloptions = [
                    'ajax' => 'local_competency/form_competency_selector',
                    'data-action' => 'competency_levels',
                    'multiple' => true,
                    'noselectionstring' => get_string('noselection', 'local_competency'),
            ];
     
            $mform->addElement('autocomplete', 'level', get_string('competency_level', 'local_competency'),$competencylevels,$competencyleveloptions);
            $mform->setType('level', PARAM_ALPHANUMEXT);
            $mform->addRule('level', null, 'required', null);

            $mform->addElement('text', 'add_level', get_string('title', 'local_competency'), array());
            $mform->setType('add_level', PARAM_TEXT);
            
            $mform->hideif('add_level', 'level', 'neq', competency::OTHER);


        }  

    
        $mform->addElement('editor', 'description', get_string('competency_description', 'local_competency'), null, $editoroptions);
        $mform->setType('description', PARAM_RAW);
    }

    /**
     * Perform some moodle validation.
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {

        global $DB;

        $errors = parent::validation($data, $files);
        if(isset($data['name']) && empty(trim($data['name']))){

            $errors['name'] = get_string('valnamerequired','local_competency');

        }

        if(isset($data['code']) && empty(trim($data['code']))){

            $errors['code'] = get_string('valcoderequired','local_competency');


        }else{

            // Add field validation check for duplicate code.
            if ($competencycode = $DB->get_record('local_competencies', array('code' => $data['code']), 'id,name', IGNORE_MULTIPLE)) {

                if (empty($data['id']) || $competencycode->id != $data['id']) {
                    $errors['code'] = get_string('codetaken', 'local_competency', $competencycode->name);
                }
            }

        }

        if(isset($data['type']) && empty(trim($data['type']))){

            $errors['type'] = get_string('valtyperequired','local_competency');

        }elseif((isset($data['type']) && $data['type'] === competency::OTHER) && (isset($data['add_type']) && empty(trim($data['add_type'])))){

            $errors['add_type'] = get_string('valaddtyperequired','local_competency');

        }elseif((isset($data['type']) && $data['type'] === competency::OTHER) && (isset($data['add_type']) && !empty(trim($data['add_type'])))){

            if ($competencytype = $DB->get_record('local_competencies', array('type' => $data['add_type']), 'id,name', IGNORE_MULTIPLE)) {

                if (empty($data['id']) || $competencytype->id != $data['id']) {
                    $errors['add_type'] = get_string('addtypetaken', 'local_competency');
                }
            }

        }

        if(isset($data['level']) && empty($data['level'])){

            $errors['level'] = get_string('vallevelrequired','local_competency');

        }elseif((isset($data['level']) && $data['level'] === competency::OTHER) && (isset($data['add_level']) && empty(($data['add_level'])))){

            $errors['add_level'] = get_string('valaddlevelrequired','local_competency');
       
        }elseif((isset($data['level']) && $data['level'] === competency::OTHER) && (isset($data['add_level']) && !empty($data['add_level']))){

            if ($competencylevel = $DB->get_record('local_competencies', array('level' => $data['add_level']), 'id,name', IGNORE_MULTIPLE)) {

                if (empty($data['id']) || $competencylevel->id != $data['id']) {
                        $errors['add_level'] = get_string('addleveltaken', 'local_competency');
                }
            }

        }

        return $errors;
    }

    /**
     * Return form context
     *
     * @return context
     */
    protected function get_context_for_dynamic_submission(): context {
        return \context_system::instance();
    }

    /**
     * Check if current user has access to this form, otherwise throw exception
     *
     * @throws moodle_exception
     */
    protected function check_access_for_dynamic_submission(): void {
        if (!competency::can_competency_datasubmit()) {
            throw new moodle_exception('errorcompetencydisabled', 'local_competency');
        }
    }
    /**
     * Process the form submission, used if form was submitted via AJAX
     *
     * @return array
     */
    public function process_dynamic_submission() {

        return competency::competency_datasubmit($this->get_data());
    }

    /**
     * Load in existing data as form defaults (not applicable)
     */
    public function set_data_for_dynamic_submission(): void {

        global $DB;

        if ($id = $this->optional_param('id', 0, PARAM_INT)) {

            $stable = new \stdClass();
            $stable->competencyid = $id;
            $stable->thead = false;
            $stable->start = 0;
            $stable->length = 1;
            
            $data=competency::get_competencies($stable);

            $data->name = $data->formname;

            $data->description = array('text' => $data->description);
            
            $this->set_data($data);
        }
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {

        $id = $this->optional_param('id', 0, PARAM_INT);

        return new moodle_url('/competency/index.php', ['id' => $id]);
    }
}
