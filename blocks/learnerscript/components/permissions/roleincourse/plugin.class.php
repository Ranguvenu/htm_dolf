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

/** LearnerScript
 * A Moodle block for creating customizable reports
 * @package blocks
 * @author: eAbyas Info Solutions
 * @date: 2017
 */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\ls;
use block_learnerscript\local\pluginbase;
use block_learnerscript\local\querylib;
use block_learnerscript\local\permissionslib;
use context_helper;

class plugin_roleincourse extends pluginbase {

    public $role;

    public function init() {
        $this->form = true;
        $this->unique = false;
        $this->fullname = get_string('roleincourse', 'block_learnerscript');
        $this->reporttypes = array('sql','statistics', 'halls', 'organization', 'userapproval', 'trainingprograms', 'exams', 'events', 'offerings', 'learningtrackinfo', 'questionbankinfo', 'cpd', 'competencies', 'usercompetencies', 'examgrades', 'prepostexams', 'userevents', 'usercpd', 'myexams', 'myprograms', 'compprograms', 'compexams', 'examprofiles', 'programenrol', 'examenrol', 'eventenrol','productlog','evolutioncomments','transaction','revenue', 'trainingprovider','traineerefundpayments');
    }

    public function summary($data) {
        global $DB;
        // $data->roleid = $DB->get_field('role', 'id', array('shortname' => $data->rolename));
        $contextname = context_helper::get_level_name($data->contextlevel);
        return $data->rolename . ' at ' . $contextname .' level';
    }

    public function execute($userid, $context, $data) {
        global $CFG, $DB;
        
        $permissions = (isset($this->reportclass->componentdata['permissions'])) ? $this->reportclass->componentdata['permissions'] : array();

        if (!empty($this->role)) {

            $currentroleid = $DB->get_field('role', 'id', array('shortname' => $this->role));
       
            $rolepermissions = array();
            $return = [];
            foreach ($permissions['elements'] as $p) {
                $currentroleid = $DB->get_field('role', 'id', ['shortname' => $this->role]);
                if ($p['pluginname'] == 'roleincourse' && isset($p['formdata']->contextlevel) && $p['formdata']->rolename == $this->role) {
                   $permissionslib = new permissionslib($p['formdata']->contextlevel, $currentroleid, $userid);
                   if($permissionslib->has_permission()){
                        $return[] = true;
                   }
                }
            }
            return in_array(true, $return);
        }
        return false;
    }
}
