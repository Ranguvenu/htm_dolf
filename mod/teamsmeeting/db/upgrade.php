<?php
// This file is part of the Zoom plugin for Moodle - http://moodle.org/
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
 * This file keeps track of upgrades to the zoom module
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations. The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do.  The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package    mod_zoom
 * @copyright  2015 UC Regents
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Execute zoom upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_teamsmeeting_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.
    $table = new xmldb_table('teamsmeeting');

    if ($oldversion < 2022110901.1) {
        // Add intro.
        $introfield = new xmldb_field('intro', XMLDB_TYPE_TEXT, null, null, null, null, null, 'course');
        if (!$dbman->field_exists($table, $introfield)) {
            $dbman->add_field($table, $introfield);
        }
        // Add introformat.
        $introformatfield = new xmldb_field('introformat', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'intro');
        if (!$dbman->field_exists($table, $introformatfield)) {
            $dbman->add_field($table, $introformatfield);
        }

        $subjectfield = new xmldb_field('subject', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if ( $dbman->field_exists($table, $subjectfield) ) {
            $dbman->change_field_type($table, $subjectfield);
        }

        upgrade_mod_savepoint(true, 2022110901.1, 'teamsmeeting');
    }

    if($oldversion < 2022110901.5){
        $isrecuringfield = new xmldb_field('isrecuring', XMLDB_TYPE_INTEGER, '4', null, null, null, '0', 'subject');
        if (!$dbman->field_exists($table, $isrecuringfield)) {
            $dbman->add_field($table, $isrecuringfield);
        }
        upgrade_mod_savepoint(true, 2022110901.5, 'teamsmeeting');
    }

    if($oldversion < 2022110901.6){
        $durationfield = new xmldb_field('duration', XMLDB_TYPE_INTEGER, '12', null, null, null, '0', 'end_time');
        if (!$dbman->field_exists($table, $durationfield)) {
            $dbman->add_field($table, $durationfield);
        }
        upgrade_mod_savepoint(true, 2022110901.6, 'teamsmeeting');
    }

    if ($oldversion < 2022110902.0) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('mod_teams_attendance');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('course', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null , '0');
        $table->add_field('module', XMLDB_TYPE_INTEGER, '12',  null, XMLDB_NOTNULL, null,'0');
        $table->add_field('onlinemeetingid', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null);
        $table->add_field('email', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null,null);
        $table->add_field('totaltime', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null,'0');
        $table->add_field('reportid', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null,null);
        $table->add_field('role', XMLDB_TYPE_TEXT, '10', null, XMLDB_NOTNULL, null,null);     
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
      
        $tabletwo = new xmldb_table('teams_attendance_intervals');
        $tabletwo->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $tabletwo->add_field('attendanceid', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null , '0');
        $tabletwo->add_field('joindatetime', XMLDB_TYPE_INTEGER, '12',  null, XMLDB_NOTNULL, null,'0');
        $tabletwo->add_field('leavedatetime', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null,'0');
        $tabletwo->add_field('duration', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null,'0');
        $tabletwo->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        if (!$dbman->table_exists($table)) {
           $dbman->create_table($table);
        }
        if (!$dbman->table_exists($tabletwo)) {
            $dbman->create_table($tabletwo);
        }
        upgrade_mod_savepoint(true, 2022110902,'teamsmeeting');
    }

    if($oldversion < 2022110902.1){
        $subjectfield = new xmldb_field('subject', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if ( $dbman->field_exists($table, $subjectfield) ) {
            $dbman->change_field_type($table, $subjectfield);
        }
        upgrade_mod_savepoint(true, 2022110902.1, 'teamsmeeting');
    }

    if($oldversion < 2022110902.3){
        $meetingstable = new xmldb_table('teamsmeeting');
        $isreportgeneratedfield = new xmldb_field('isreportgenerated', XMLDB_TYPE_INTEGER, '12', null, false, null, '0', 'metadata');
        if (!$dbman->field_exists($meetingstable, $isreportgeneratedfield)) {
            $dbman->add_field($meetingstable, $isreportgeneratedfield);
        }
        upgrade_mod_savepoint(true, 2022110902.3, 'teamsmeeting');
    }
      
   
    return true;
}
