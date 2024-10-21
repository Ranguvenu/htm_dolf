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
 * TODO describe file settings
 *
 * @package    local_sector
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$settingspage = new admin_settingpage('fasettings', '');
$refundlink = html_writer::link($CFG->wwwroot.'/local/trainingprogram/viewrefundsettings.php', get_string('refundsettings', 'local_trainingprogram'));
$settingspage->add(new admin_setting_heading('local_sector/refundsettings',$refundlink, '', '', PARAM_RAW));
$examsettings = html_writer::link($CFG->wwwroot.'/local/exams/exam_ownedby_settings.php', get_string('exam_ownedby_settings', 'local_sector'));
$settingspage->add(new admin_setting_heading('local_sector/exam_ownedby_settings',$examsettings, '', '', PARAM_RAW));

$promethodlink = html_writer::link($CFG->wwwroot.'/local/trainingprogram/program_method.php', get_string('programmethod', 'local_trainingprogram'));
$settingspage->add(new admin_setting_heading('local_sector/programmethod',$promethodlink, '', '', PARAM_RAW));

$evomethodlink = html_writer::link($CFG->wwwroot.'/local/trainingprogram/evalution_method.php', get_string('evaluationmethod', 'local_trainingprogram'));
$settingspage->add(new admin_setting_heading('local_sector/evalutionmethod',$evomethodlink, '', '', PARAM_RAW));

$fastsettingslink = html_writer::link($CFG->wwwroot.'/local/exams/fastsettings.php', get_string('fastsettings', 'local_exams'));
$settingspage->add(new admin_setting_heading('local_exams/fastsettings',$fastsettingslink, '', '', PARAM_RAW));

$discount_managementlink = html_writer::link($CFG->wwwroot.'/local/trainingprogram/discount_management.php', get_string('discount_management', 'local_trainingprogram'));
$settingspage->add(new admin_setting_heading('local_sector/discount_managementsettings',$discount_managementlink, '', '', PARAM_RAW));

$ADMIN->add('localplugins', $settingspage);
