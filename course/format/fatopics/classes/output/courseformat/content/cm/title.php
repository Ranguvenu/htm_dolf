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
 * Contains the default activity title.
 *
 * This class is usually rendered inside the cmname inplace editable.
 *
 * @package   core_courseformat
 * @copyright 2020 Ferran Recio <ferran@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_fatopics\output\courseformat\content\cm;

use cm_info;
use core_courseformat\output\local\content\cm\title as coretitle;
use core\output\named_templatable;
use core_courseformat\base as course_format;
use core_courseformat\output\local\courseformat_named_templatable;
use core_text;
use renderable;
use section_info;
use stdClass;
use html_writer;

/**
 * Base class to render a course module title inside a course format.
 *
 * @package   core_courseformat
 * @copyright 2020 Ferran Recio <ferran@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class title extends coretitle implements named_templatable, renderable {

    use courseformat_named_templatable;

    /** @var course_format the course format */
    protected $format;

    /** @var section_info the section object */
    private $section;

    /** @var cm_info the course module instance */
    protected $mod;

    /** @var array optional display options */
    protected $displayoptions;

    /**
     * Constructor.
     *
     * @param course_format $format the course format
     * @param section_info $section the section info
     * @param cm_info $mod the course module ionfo
     * @param array $displayoptions optional extra display options
     */
    public function __construct(course_format $format, section_info $section, cm_info $mod, array $displayoptions = []) {
        $this->format = $format;
        $this->section = $section;
        $this->mod = $mod;
        $this->displayoptions = $displayoptions;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output typically, the renderer that's calling this function
     * @return stdClass data context for a mustache template
     */
    public function export_for_template(\renderer_base $output): stdClass {
        global $USER;
        $format = $this->format;
        $mod = $this->mod;
        $displayoptions = $this->displayoptions;

        if (!$mod->is_visible_on_course_page() || !$mod->url) {
            // Nothing to be displayed to the user.
            return new stdClass();
        }
        // Usually classes are loaded in the main cm output. However when the user uses the inplace editor
        // the cmname output does not calculate the css classes.
        if (!isset($displayoptions['linkclasses']) || !isset($displayoptions['textclasses'])) {
            $cmclass = $format->get_output_classname('content\\cm');
            $cmoutput = new $cmclass(
                $format,
                $this->section,
                $mod,
                $displayoptions
            );
            $displayoptions['linkclasses'] = $cmoutput->get_link_classes();
            $displayoptions['textclasses'] = $cmoutput->get_text_classes();
        }
        $is_training_official = (new \local_trainingprogram\local\trainingprogram())->if_user_has_role($USER->id, 'to');
        $approve_btn = '';
        $reject_btn = '';
        // print_object($is_training_official);die;
        if ($is_training_official) {
            if($mod->visible == 0){
                $modulecreator = (new \local_trainingprogram\local\trainingprogram())->get_module_creator($mod->id);
                if ($modulecreator) {
                    if ($modulecreator->shortname == 'editingtrainer') {
                        $approve_btn = $this->create_approve_btn($mod->id);
                        $reject_btn = $this->create_reject_btn($mod->id);
                        $btns = html_writer::div($approve_btn.$reject_btn, 'approve_reject-btns text-center');
                    }
                }
            }
        }
        $data = (object)[
            'url' => $mod->url,
            'instancename' => $mod->get_formatted_name(),
            'uservisible' => $mod->uservisible,
            'icon' => $mod->get_icon_url(),
            'modname' => $mod->modname,
            'pluginname' => get_string('pluginname', 'mod_' . $mod->modname).$btns,
            'linkclasses' => $displayoptions['linkclasses'],
            'textclasses' => $displayoptions['textclasses'],
            'purpose' => plugin_supports('mod', $mod->modname, FEATURE_MOD_PURPOSE, MOD_PURPOSE_OTHER),
        ];

        // File type after name, for alphabetic lists (screen reader).
        if (strpos( core_text::strtolower($data->instancename),core_text::strtolower($mod->modfullname)) === false) {
            $data->altname = get_accesshide(' ' . $mod->modfullname);
        }

        // Get on-click attribute value if specified and decode the onclick - it
        // has already been encoded for display (puke).
        $data->onclick = htmlspecialchars_decode($mod->onclick, ENT_QUOTES);

        return $data;
    }
    public function create_approve_btn($cmid) {
        $str = get_string('approve', 'theme_academy');
        $btn = html_writer::tag('i', '', [
            'title' => $str,
            'class' => "fa fa-check btn btn-outline-primary btn-sm text-nowrap m-1",
            'data-id' => $cmid,
            'data-action' => 'approve'
        ]);
        return $btn;
    }
    public function create_reject_btn($cmid) {
        $str = get_string('reject', 'theme_academy');
        $btn = html_writer::tag('i', '', [
            'title' => $str,
            'class' => " fa fa-ban btn btn-outline-danger btn-sm text-nowrap m-1",
            'data-id' => $cmid,
            'data-action' => 'reject'
        ]);
        return $btn;   
    }
}
