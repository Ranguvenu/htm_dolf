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


function xmldb_block_faq_upgrade($oldversion) {
    global $DB, $CFG;
    $dbman = $DB->get_manager();

    if($oldversion < 2022041901.46){

        $faqtable = new xmldb_table('faq');
        $faqtable->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $faqtable->add_field('title', XMLDB_TYPE_CHAR, '255',  null, null, null, null, null, null);
        $faqtable->add_field('description', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, null, null);
        $faqtable->add_field('faqrank', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');
        $faqtable->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $faqtable->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $faqtable->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $faqtable->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $faqtable->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
   
        // Conditionally launch create table for tool_dataprivacy_contextlist.

        if (!$dbman->table_exists($faqtable)) {
             $dbman->create_table($faqtable);
        }


        upgrade_plugin_savepoint(true, 2022041901.46, 'block', 'faq');
    }
     if ($oldversion < 2022041901.8) {

        $table = new xmldb_table('faq');
        $field1 = new xmldb_field('title', XMLDB_TYPE_CHAR, '255',  null, null, null, null, null, null);
        $field2 = new xmldb_field('description', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, null, null);
        $field3 = new xmldb_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $field4 = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $field5 = new xmldb_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $field6 = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        if ($dbman->field_exists($table, $field1)) {
            $dbman->change_field_type($table, $field1);
        }
        if ($dbman->field_exists($table, $field2)) {
            $dbman->change_field_type($table, $field2);
        }
        $renamefield= new xmldb_field('rank',XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, null,null);
        if ($dbman->field_exists($table, $renamefield)) {
            $dbman->rename_field($table, $renamefield, 'faqrank');
        }
        if (!$dbman->field_exists($table, $field3)) {
            $dbman->add_field($table, $field3);
        }
        if (!$dbman->field_exists($table, $field4)) {
            $dbman->add_field($table, $field4);
        }
        if (!$dbman->field_exists($table, $field5)) {
            $dbman->add_field($table, $field5);
        }
        if (!$dbman->field_exists($table, $field6)) {
            $dbman->add_field($table, $field6);
        }

        upgrade_plugin_savepoint(true, 2022041901.8, 'block', 'faq'); 

    }

    return true;
}
