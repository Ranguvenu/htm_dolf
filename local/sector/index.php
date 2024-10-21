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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    local_sector
 * @copyright  Kristian
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/formslib.php');

global $CFG, $PAGE, $OUTPUT, $DB;
$PAGE->requires->jquery();
require_login();
$systemcontext = context_system::instance();
require_capability('local/sector:manage', $systemcontext);
$PAGE->set_url(new moodle_url('/local/sector/index.php'));
$PAGE->set_context(context_system::instance());

$PAGE->set_title(get_string('sector', 'local_sector'));
$PAGE->set_heading(get_string('sector', 'local_sector'));
$PAGE->navbar->add(get_string('sector', 'local_sector'), new moodle_url('/local/sector/index.php'));
echo $OUTPUT->header();
$sectorrender= $PAGE->get_renderer('local_sector');
$sectordata = (new local_sector\controller)->get_sectors();
$filterparams= $sectorrender->get_sectors_view(true);
$filter_content=$OUTPUT->render_from_template('theme_academy/global_filter', $filterparams);
echo $OUTPUT->render_from_template('local_sector/form',['filter_content'=>$filter_content] );
echo $sectorrender->get_sectors_view();
echo $OUTPUT->footer();



