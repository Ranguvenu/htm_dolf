<?php
/**
 * This file is part of eAbyas
 *
 * Copyright eAbyas Info Solutons Pvt Ltd, India
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author eabyas  <info@eabyas.in>
 * @package Bizlms 
 * @subpackage local_projects
 */
namespace local_hall\event;

defined('MOODLE_INTERNAL') || die();

class reservation_update extends \core\event\base {
    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'local_hall';
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        global $DB;
        // $firstname=$DB->get_field_sql("SELECT name FROM {local_trainingprogram} where id=$this->objectid");
        // //$trainingprogram_name=$this->trainingname;
        return "The Hall has been reserved"; 
        
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('hallreservedevent', 'local_hall');
    }

    /**
     * Get URL related to the action
     *
     * @return \moodle_url
     */
    public function get_url() {
        global $DB;
        //$project_name=$DB->get_field_sql("SELECT name FROM {local_projects} where id=$this->objectid");
        $url = new \moodle_url('/local/hall/index.php');
        return $url;
    }

    /**
     * Return the legacy event log data.
     *
     * @return array|null
     */
    protected function get_legacy_logdata() {
        // The legacy log table expects a relative path to /local/forum/.
       // $logurl = substr($this->get_url()->out_as_local_url(), strlen('/local/projects/'));

        //return array($this->objectid, 'projects', 'update project', $logurl, $this->objectid, $this->contextinstanceid);
    }

    

    public static function get_objectid_mapping() {
        //return array('db' => 'local_projects', 'restore' => 'local_projects');
    }

    public static function get_other_mapping() {
        // $othermapped = array();
        // $othermapped['projectid'] = array('db' => 'local_projects', 'restore' => 'local_projects');

        // return $othermapped;
    }
}