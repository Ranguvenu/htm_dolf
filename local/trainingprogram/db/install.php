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
 * @package local_notification
 */
defined('MOODLE_INTERNAL') || die();
function xmldb_local_trainingprogram_install(){
    global $CFG,$DB;
    /*notifictaions content*/
    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes
    $time = time();  
    $category = array('name' => get_string('cat_name','local_trainingprogram'), 'idnumber' => get_string('cat_idnumber','local_trainingprogram'), 'description' => get_string('cat_description','local_trainingprogram'),'parent' => '0','sortorder'=> 30000,'visible' => 1, 'depth' => 3, 'timemodified' => $time);
    $category = core_course_category::create($category);

    if (!defined('BEHAT_SITE_RUNNING') && !(defined('PHPUNIT_TEST') && PHPUNIT_TEST)) {
        \tool_certificate\certificate::create_trainingprogram_template();
    }


}
