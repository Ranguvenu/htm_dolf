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

/**
 * OAuth2 authentication plugin upgrade code
 *
 * @package    auth_registration
 * @copyright  2017 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade function
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_auth_registration_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();


    if ($oldversion < 2019052001) {
    

        $table = new xmldb_table('local_users');
        
        $field = new xmldb_field('firstnamearabic', XMLDB_TYPE_CHAR, '255');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('lastnamearabic', XMLDB_TYPE_CHAR, '255');
        
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2019052001, 'auth', 'registration');
    }

    // if ($oldversion < 2021051706.1) {
    

    //     $table = new xmldb_table('local_users');
    //     $field = new xmldb_field('password', XMLDB_TYPE_INTEGER, '20');
    //     if ($dbman->field_exists($table, $field)) {
    //         $dbman->drop_field($table, $field);
    //     }

    //     $field = new xmldb_field('confirm_password', XMLDB_TYPE_INTEGER, '20');
    //     if ($dbman->field_exists($table, $field)) {
    //         $dbman->drop_field($table, $field);
    //     }

    //     $field = new xmldb_field('organization',XMLDB_TYPE_INTEGER, '20', null, null, null, null);
    //     if ($dbman->field_exists($table, $field)) {
    //         $dbman->change_field_type($table, $field);
    //     }
    //     upgrade_plugin_savepoint(true, 2021051706.1, 'auth', 'registration');
    // }


    if ($oldversion < 2021051706.2) {
    

        $table = new xmldb_table('local_users');
        $field = new xmldb_field('id_type',XMLDB_TYPE_INTEGER, '20', null, null, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_type($table, $field);
        }
        upgrade_plugin_savepoint(true, 2021051706.2, 'auth', 'registration');
    }

    if ($oldversion < 2021051706.3) {
    

        $table = new xmldb_table('local_users');
        $field = new xmldb_field('gender',XMLDB_TYPE_INTEGER, '12', null, null, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_type($table, $field);
        }
        upgrade_plugin_savepoint(true, 2021051706.3, 'auth', 'registration');
    }

    if ($oldversion < 2021051708) {
    

        $table = new xmldb_table('local_users');
        $fieldA = new xmldb_field('middlenameen', XMLDB_TYPE_CHAR, '255',  null, null, null, null,'lastnamearabic');
        $fieldB = new xmldb_field('middlenamearabic', XMLDB_TYPE_CHAR, '255',  null, null, null, null, 'middlenameen');
        $fieldC = new xmldb_field('thirdnameen', XMLDB_TYPE_CHAR, '255',  null, null, null, null,'middlenamearabic');
        $fieldD = new xmldb_field('thirdnamearabic', XMLDB_TYPE_CHAR, '255',  null, null, null, null, 'thirdnameen');
        $fieldE = new xmldb_field('dateofbirth', XMLDB_TYPE_INTEGER, '20',  null, null, null, 0, 'thirdnamearabic');
        if (!$dbman->field_exists($table, $fieldA)) {
            $dbman->add_field($table, $fieldA);
        }    
        if (!$dbman->field_exists($table, $fieldB)) {
            $dbman->add_field($table, $fieldB);
        }
        if (!$dbman->field_exists($table, $fieldC)) {
            $dbman->add_field($table, $fieldC);
        }    
        if (!$dbman->field_exists($table, $fieldD)) {
            $dbman->add_field($table, $fieldD);
        }
        if (!$dbman->field_exists($table, $fieldE)) {
            $dbman->add_field($table, $fieldE);
        }

        upgrade_plugin_savepoint(true, 2021051708, 'auth', 'registration');
    }
    if ($oldversion < 2021051708.1) {

        $dbman = $DB->get_manager();

        $table = new xmldb_table('local_fast_user');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('firstname', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('firstnamearabic', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('middlenamearabic', XMLDB_TYPE_CHAR, '255', null,null, null, null);
        $table->add_field('middlenameen', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('thirdnamearabic', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('thirdnameen', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('lastname', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('lastnamearabic', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('nationalitycountryid', XMLDB_TYPE_INTEGER, '12', null, null, null, '0');
        $table->add_field('nationalitytype', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('insideksa', XMLDB_TYPE_CHAR, '10', null, null, null, null);
        $table->add_field('phonenumber', XMLDB_TYPE_CHAR, '20', null, null, null, null);
        $table->add_field('addresscountryid', XMLDB_TYPE_INTEGER, '12', null, null, null, '0');
        $table->add_field('lang', XMLDB_TYPE_CHAR, '30', null,null, null, null);
        $table->add_field('gender', XMLDB_TYPE_CHAR, '10', null, null, null, null);
        $table->add_field('email', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('confirm_email', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('password', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('confirm_password', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('rolecode', XMLDB_TYPE_INTEGER, '20', null, null, null, '0');
        $table->add_field('rolename', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('username', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('ssoguid', XMLDB_TYPE_INTEGER, '20', null, null, null, '0');
        $table->add_field('ssoidnumber', XMLDB_TYPE_INTEGER, '20', null, null, null, '0');
        $table->add_field('ssodes', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('ssotype', XMLDB_TYPE_INTEGER, '20', null, null, null, '0');
        $table->add_field('gulfidnumber', XMLDB_TYPE_INTEGER, '20', null, null, null, '0');
        $table->add_field('residencynumber', XMLDB_TYPE_INTEGER, '20', null, null, null, '0');
        $table->add_field('id_number', XMLDB_TYPE_INTEGER, '20', null, null, null, '0');
        $table->add_field('passportnumber', XMLDB_TYPE_INTEGER, '20', null, null, null, '0');
        $table->add_field('organizationcommercialregister', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('organizationshortcode', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('usercreated',XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usermodified',XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated',XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified',XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
      

        upgrade_plugin_savepoint(true, 2021051708.1, 'auth', 'registration');
    }
    if ($oldversion < 2021051708.2) {
    

        $table = new xmldb_table('local_fast_user');
        
        $fieldone = new xmldb_field('status', XMLDB_TYPE_INTEGER, '12', null, null, null, null);
        $fieldtwo = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        if (!$dbman->field_exists($table, $fieldone)) {
            $dbman->add_field($table, $fieldone);
        }
        if (!$dbman->field_exists($table, $fieldtwo)) {
            $dbman->add_field($table, $fieldtwo);
        }

        upgrade_plugin_savepoint(true, 2021051708.2, 'auth', 'registration');
    }

    if ($oldversion < 2021051708.4) {
    
        $table = new xmldb_table('local_users');
        $fielA= new xmldb_field('nationality', XMLDB_TYPE_CHAR, '10', null, null, null, null);

        if ($dbman->field_exists($table, $fielA)) {
            $dbman->change_field_type($table, $fielA);
        }
        upgrade_plugin_savepoint(true, 2021051708.4, 'auth', 'registration');
    }
    
    if ($oldversion < 2021051708.9) {
    

        $table = new xmldb_table('local_users');
        $field = new xmldb_field('confirm_password',XMLDB_TYPE_CHAR, '255', null, null, null,null,'password');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table,$field);
        }
        upgrade_plugin_savepoint(true, 2021051708.9, 'auth', 'registration');
    }

    if ($oldversion < 2021051709.2) {
    
        $table = new xmldb_table('local_users');
        $fieldA = new xmldb_field('bulkenrolltype',XMLDB_TYPE_CHAR, '255', null, null, null,null);
        $fieldB =  new xmldb_field('bulkenrollstatus',XMLDB_TYPE_INTEGER, '12', null, null, null, '0');
        $fieldC = new xmldb_field('country_code',XMLDB_TYPE_INTEGER, '20', null, null, null, '0');
        if (!$dbman->field_exists($table, $fieldA)) {
            $dbman->add_field($table,$fieldA);
        }
        if (!$dbman->field_exists($table, $fieldB)) {
            $dbman->add_field($table,$fieldB);
        }
        if (!$dbman->field_exists($table, $fieldC)) {
            $dbman->add_field($table,$fieldC);
        }
        upgrade_plugin_savepoint(true, 2021051709.2, 'auth', 'registration');
    }

    
    if ($oldversion < 2021051709.5) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_fast_user');
        $field1 = new xmldb_field('passportnumber', XMLDB_TYPE_CHAR, '120', null, null, null, null);
        $field2 = new xmldb_field('id_number', XMLDB_TYPE_CHAR, '120', null, null, null, null);
        if ($dbman->field_exists($table, $field1) ) {
            $dbman->change_field_type($table, $field1);
        }
        if ($dbman->field_exists($table, $field2) ) {
            $dbman->change_field_type($table, $field2);
        }
        $table2 = new xmldb_table('local_users');
        $field = new xmldb_field('passportnumber', XMLDB_TYPE_CHAR, '120', null, null, null, null);
        if (!$dbman->field_exists($table2, $field)) {
            $dbman->add_field($table2,$field);
        }
        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2021051709.5, 'auth', 'registration');
    }

    if ($oldversion < 2021051709.6) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_users');
        $field = new xmldb_field('dateofbirth',XMLDB_TYPE_INTEGER, '20', null, null, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table,$field);
        }
        upgrade_plugin_savepoint(true, 2021051709.6, 'auth', 'registration');
    }
    if ($oldversion < 2021051709.7) {
    
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_fast_user');
        $fieldA = new xmldb_field('usercreated',XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null,'0','status');
        $fieldB = new xmldb_field('usermodified',XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null,'0','usercreated');
        $fieldC = new xmldb_field('timecreated',XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null,'0','usermodified');
        $fieldD = new xmldb_field('timemodified',XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null,'0','timecreated');
        if (!$dbman->field_exists($table, $fieldA)) {
            $dbman->add_field($table,$fieldA);
        }
        if (!$dbman->field_exists($table, $fieldB)) {
            $dbman->add_field($table,$fieldB);
        }
        if (!$dbman->field_exists($table, $fieldC)) {
            $dbman->add_field($table,$fieldC);
        }
        if (!$dbman->field_exists($table, $fieldD)) {
            $dbman->add_field($table,$fieldD);
        }
        upgrade_plugin_savepoint(true, 2021051709.7, 'auth', 'registration');
    }

    return true;
}
