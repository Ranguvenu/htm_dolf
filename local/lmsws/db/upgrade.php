<?php
// This file is part of VPL for Moodle - http://vpl.dis.ulpgc.es/
//
// VPL for Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// VPL for Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with VPL for Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package mod_vpl
 * @copyright 2012 Juan Carlos Rodríguez-del-Pino
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Juan Carlos Rodríguez-del-Pino <jcrodriguez@dis.ulpgc.es>
 */
defined( 'MOODLE_INTERNAL' ) || die();
function xmldb_local_lmsws_upgrade($oldversion = 0) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();
     if ($oldversion < 20191015002) {

       
        $table = new xmldb_table('local_lmsws_fapayload');

        
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('apiname', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('faid', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('req', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('refid', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('typ', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('crat', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('fav', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('restm', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('sts', XMLDB_TYPE_CHAR, '10', null, null, null, null);
        $table->add_field('res', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);


        // Adding keys to table local_lmsws_fapayload.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for local_lmsws_fapayload.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Lmsws savepoint reached.
        upgrade_plugin_savepoint(true, 20191015002, 'local', 'lmsws');
    }

     if ($oldversion < 20191015003) {

        // Define field inid to be added to local_lmsws_fapayload.
        $table = new xmldb_table('local_lmsws_fapayload');
        $field = new xmldb_field('inid', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'typ');

        // Conditionally launch add field inid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Lmsws savepoint reached.
        upgrade_plugin_savepoint(true, 20191015003, 'local', 'lmsws');
    }

        if ($oldversion < 20191017004) {

        // Define field crat to be added to local_lmsws_fapayload.
        $table = new xmldb_table('local_lmsws_fapayload');
        $field = new xmldb_field('crat', XMLDB_TYPE_CHAR, '100', null, null, null, null);
       

         if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        $field =  new xmldb_field('crat', XMLDB_TYPE_NUMBER, '10', null, null, null, null, 'inid');
        // Conditionally launch add field crat.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Lmsws savepoint reached.
        upgrade_plugin_savepoint(true, 20191017004, 'local', 'lmsws');
    }

    if ($oldversion < 20211017022) {

        // Define table cisiuserdetails to be created.
        $table = new xmldb_table('cisiuserdetails');

        // Adding fields to table cisiuserdetails.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('cisiuserid', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
        $table->add_field('createdtime', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
        $table->add_field('updatedtime', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
        $table->add_field('status', XMLDB_TYPE_INTEGER, '3', null, null, null, null);

        // Adding keys to table cisiuserdetails.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for cisiuserdetails.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Lmsws savepoint reached.
        upgrade_plugin_savepoint(true, 20211017022, 'local', 'lmsws');
    }

        if ($oldversion < 201910170015) {

        // Define table externalexam_userdetails to be created.
        $table = new xmldb_table('externalexam_userdetails');

        // Adding fields to table externalexam_userdetails.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('externaluserid', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
        $table->add_field('externalprovidername', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('createdtime', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
        $table->add_field('updatedtime', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
        $table->add_field('status', XMLDB_TYPE_INTEGER, '3', null, null, null, null);

        // Adding keys to table externalexam_userdetails.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for externalexam_userdetails.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Lmsws savepoint reached.
        upgrade_plugin_savepoint(true, 201910170015, 'local', 'lmsws');
    }

   if ($oldversion < 201910170016) {

        // Define table externalexam_userdetails to be dropped.
        $table = new xmldb_table('cisiuserdetails');

        // Conditionally launch drop table for externalexam_userdetails.
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        // Lmsws savepoint reached.
        upgrade_plugin_savepoint(true, 201910170016, 'local', 'lmsws');
    }

    if ($oldversion < 201910170021) {

        // Define table externalexam_userdetails to be renamed to NEWNAMEGOESHERE.
        $table = new xmldb_table('externalexam_userdetails');

        // Launch rename table for externalexam_userdetails.
        $dbman->rename_table($table, 'externalprovider_userdetails');

        // Lmsws savepoint reached.
        upgrade_plugin_savepoint(true, 201910170021, 'local', 'lmsws');
    }

    return true;
}
