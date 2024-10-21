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
defined('MOODLE_INTERNAL') || die();

use tool_product\product as product;
use tool_product\telr as telr;
use tool_product_external;


function xmldb_block_documentupload_upgrade($oldversion) {
    global $DB, $CFG;
    $dbman = $DB->get_manager();
    if ($oldversion < 2022041901.57) {

        $table = new xmldb_table('documentupload');
        $field = new xmldb_field('mediatype');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '11');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
       upgrade_plugin_savepoint(true, 2022041901.57, 'block', 'documentupload');
    }

    if ($oldversion < 2022041901.58) {

        $table = new xmldb_table('documentupload');
        $field = new xmldb_field('description');
        $field->set_attributes(XMLDB_TYPE_TEXT, '', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
       upgrade_plugin_savepoint(true, 2022041901.58, 'block', 'documentupload');
    }

     if ($oldversion < 2022041901.62) {

        $table = new xmldb_table('documentupload');
        $field1 = new xmldb_field('timemodified');
        $field1->set_attributes(XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }

        $field2 = new xmldb_field('timecreated');
        $field2->set_attributes(XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }
       upgrade_plugin_savepoint(true, 2022041901.62, 'block', 'documentupload');
    }

     if ($oldversion < 2022041901.63) {

        $table = new xmldb_table('documentupload');
        $field1 = new xmldb_field('langauge');
        $field1->set_attributes(XMLDB_TYPE_BINARY, '', null, null, null, null);
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }

       upgrade_plugin_savepoint(true, 2022041901.63, 'block', 'documentupload');
    }


    if ($oldversion < 2022041901.64) {
        $table = new xmldb_table('documentupload');
        $field = new xmldb_field('langauge', XMLDB_TYPE_INTEGER,'11');
        $dbman->change_field_type($table, $field);
        upgrade_plugin_savepoint(true, 2022041901.64, 'block', 'documentupload');

    }

    if ($oldversion < 2022041901.65) {

        $table = new xmldb_table('documentupload');
        $field = new xmldb_field('description');
        $field->set_attributes(XMLDB_TYPE_TEXT, '', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
       upgrade_plugin_savepoint(true, 2022041901.65, 'block', 'documentupload');
    }
    if ($oldversion < 2022041901.66) {
        $table = new xmldb_table('documentupload');
        $field = new xmldb_field('arabicdocument');
        $field->set_attributes(XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('documentupload');
        $field = new xmldb_field('document', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if ( $dbman->field_exists($table, $field) ) {
            $dbman->change_field_type($table, $field);
        }

        upgrade_plugin_savepoint(true, 2022041901.66, 'block', 'documentupload');
    }



    if ($oldversion < 2022041901.74) {

        $table = new xmldb_table('documentupload');
        $field1 = new xmldb_field('docrank');
        $field1->set_attributes(XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }
       upgrade_plugin_savepoint(true, 2022041901.74, 'block', 'documentupload');
    }

    if ($oldversion < 2022041901.76) {

        $table = new xmldb_table('documentupload');
        $field1 = new xmldb_field('video', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }

        $field2 = new xmldb_field('videoar', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }
       upgrade_plugin_savepoint(true, 2022041901.76, 'block', 'documentupload');
    }
    return true;

}
