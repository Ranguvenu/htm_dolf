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
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}
require_once($CFG->libdir.'/formslib.php');


/**
 * TODO describe file dynamicfilters_form
 *
 * @package    local_organization
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dynamicfilters_form extends moodleform {
    function definition() {

        global $CFG;
        $mform    = $this->_form;
        $filterlist        = $this->_customdata['filterlist']; // this contains the data of this form
        $filterparams      = $this->_customdata['filterparams'];
        $options           = $filterparams['options'];
        $dataoptions       = $filterparams['dataoptions'];
        $submitid = $this->_customdata['submitid'] ? $this->_customdata['submitid'] : 'filteringform';
        $disablebuttons = isset($this->_customdata['disablebuttons']) ? $this->_customdata['disablebuttons'] : 0;
        $ajaxformsubmit = $this->_customdata['ajaxformsubmit'] ? $this->_customdata['ajaxformsubmit'] : false;
        $this->_form->_attributes['id'] = $submitid;


        $mform->addElement('hidden', 'options', $options);
        $mform->setType('options', PARAM_RAW);

        $mform->addElement('hidden', 'dataoptions', $dataoptions);
        $mform->setType('dataoptions', PARAM_RAW);     
        $mform->addElement('hidden', 'mode', $filterparams['mode']);
        $mform->setType('mode', PARAM_RAW);
       
        foreach ($filterlist as $component => $componenttype) {


            $extractcomponenttype=array_keys($componenttype);

            $advancecomponenttype=explode('/',$extractcomponenttype[0]);


            if(isset($advancecomponenttype[1])){
                $extractcomponenttype[0]=$advancecomponenttype[1];
            }


            $plugin_exist = core_component::get_plugin_directory($extractcomponenttype[0], $component);
    
            if(!empty($plugin_exist)){

                if($extractcomponenttype[0] == $advancecomponenttype[0]){
                    if($extractcomponenttype[0].'_'.$component == 'tool_certificate'){
                        require_once($CFG->dirroot . '/admin/'.$extractcomponenttype[0].'/' . $component . '/lib.php');

                    }else{
                        require_once($CFG->dirroot . '/'.$extractcomponenttype[0].'/' . $component . '/lib.php');
                    }
                }else{

                    require_once($CFG->dirroot . '/'.$advancecomponenttype[0]. '/'.$advancecomponenttype[1].'/' . $component . '/lib.php');
                }

                if(isset($advancecomponenttype[1])){
                    $extractcomponenttype[0]=$advancecomponenttype[0].'/'.$advancecomponenttype[1];
                }


                foreach($componenttype[$extractcomponenttype[0]] as $filterfunction){


                    $functionname = $filterfunction.'_filter';

                    // exists or not
                    if (function_exists($functionname)) 
                    {
                        $functionname($mform);
                    } 
                }
            }
        }
        if(!$disablebuttons){
            if($ajaxformsubmit){

                $buttonarray = array();
                $applyclassarray = array('class' => 'form-submit','onclick' => '(function(e){ require("theme_academy/cardPaginate").filteringData(e,"'.$submitid.'") })(event)');
                $cancelclassarray = array('class' => 'form-submit','onclick' => '(function(e){ require("theme_academy/cardPaginate").resetingData(e,"'.$submitid.'") })(event)');
                $buttonarray[] = &$mform->createElement('button', 'filter_apply', get_string('apply','local_organization'), $applyclassarray);
                $buttonarray[] = &$mform->createElement('button', 'cancel', get_string('reset','local_organization'), $cancelclassarray);
            }else{

                $buttonarray = array();
                $applyclassarray = array('class' => 'form-submit');
                $buttonarray[] = &$mform->createElement('submit', 'filter_apply', get_string('apply','local_organization'), $applyclassarray);
                $buttonarray[] = &$mform->createElement('cancel', 'cancel', get_string('reset','local_organization'), $applyclassarray);


            }
             $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        }
        
       
        $mform->disable_form_change_checker();        
    }
     /**
     * Validation.
     *
     * @param array $data
     * @param array $files
     * @return array the errors that were found
     */
    function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        return $errors;
    }
}
