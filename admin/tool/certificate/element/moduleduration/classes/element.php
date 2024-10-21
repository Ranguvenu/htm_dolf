<?php
// This file is part of the tool_certificate plugin for Moodle - http://moodle.org/
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
 * This file contains the certificate element date's core interaction API.
 *
 * @package    certificateelement_moduleduration
 * @copyright  2013 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace certificateelement_moduleduration;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/grade/constants.php');

/**
 * The certificate element date's core interaction API.
 *
 * @package    certificateelement_moduleduration
 * @copyright  2013 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class element extends \tool_certificate\element {

    /**
     * @var int Show creation date
     */
    const CUSTOMCERT_MODULEDURATION = -1;


    /**
     * This function renders the form elements when adding a certificate element.
     *
     * @param \MoodleQuickForm $mform the edit_form instance
     */
    public function render_form_elements($mform) {

        // Get the possible date options.
        $dateoptions = [];
        $dateoptions[self::CUSTOMCERT_MODULEDURATION] = get_string('moduleduration', 'certificateelement_moduleduration');

        $mform->addElement('select', 'moduleduration', get_string('moduleduration', 'certificateelement_moduleduration'), $dateoptions);
        $mform->addHelpButton('moduleduration', 'moduleduration', 'certificateelement_moduleduration');

        $mform->addElement('select', 'moduledurationformat', get_string('moduledurationformat', 'certificateelement_moduleduration'), self::get_moduleduration_formats());
        $mform->addHelpButton('moduledurationformat', 'moduledurationformat', 'certificateelement_moduleduration');

        parent::render_form_elements($mform);
    }

    /**
     * Handles saving the form elements created by this element.
     * Can be overridden if more functionality is needed.
     *
     * @param \stdClass $data the form data or partial data to be updated (i.e. name, posx, etc.)
     */
    public function save_form_data(\stdClass $data) {
        $data->data = json_encode(['moduleduration' => $data->moduleduration, 'moduledurationformat' => $data->moduledurationformat]);
        parent::save_form_data($data);
    }

    /**
     * Handles rendering the element on the pdf.
     *
     * @param \pdf $pdf the pdf object
     * @param bool $preview true if it is a preview, false otherwise
     * @param \stdClass $user the user we are rendering this for
     * @param \stdClass $issue the issue we are rendering
     */
    public function render($pdf, $preview, $user, $moduleinfo = false) {
        // Decode the information stored in the database.
        $moduledateinfo = @json_decode($this->get_data(), true) + ['moduleduration' => '', 'moduledurationformat' => ''];

        $moduledates = \tool_certificate\element_helper::get_moduledurationdate($this->get_id(),$user, $moduleinfo);

        \tool_certificate\element_helper::render_content($pdf, $this,
                $this->get_moduleduration_format_string($moduledates->modulestartdate,$moduledates->moduleenddate, $moduledateinfo['moduledurationformat']));
        
    }
    public function get_moduledurationinfo(){
        global $DB;

        $modinfo = new \stdClass();

        $modinfo->modulestartdate = $this->modulestartdate;
        $modinfo->moduleenddate = $this->moduleenddate;
        $modinfo->moduletype = $this->moduletype;
        $modinfo->moduleid = $this->moduleid;
        
       
        return $modinfo;

    }

    /**
     * Render the element in html.
     *
     * This function is used to render the element when we are using the
     * drag and drop interface to position it.
     *
     * @return string the html
     */
    public function render_html() {
        // Decode the information stored in the database.
        $moduledateinfo = @json_decode($this->get_data(), true) + ['moduleduration' => '', 'moduledurationformat' => ''];

        $moduledates = \tool_certificate\element_helper::get_moduledurationdate($this->get_id(),$user, $this->get_moduledurationinfo());
        
        return \tool_certificate\element_helper::render_html_content($this,
            $this->get_moduleduration_format_string($moduledates->modulestartdate,$moduledates->moduleenddate, $dateinfo['moduledurationformat']));
    }

    /**
     * Prepare data to pass to moodleform::set_data()
     *
     * @return \stdClass|array
     */
    public function prepare_data_for_form() {
        $record = parent::prepare_data_for_form();
        if (!empty($this->get_data())) {
            $dateinfo = json_decode($this->get_data());
            $record->moduleduration = $dateinfo->moduleduration;
            $record->moduledurationformat = $dateinfo->moduledurationformat;
        }
        return $record;
    }

    /**
     * Helper function to return all the date formats.
     *
     * @return array the list of date formats
     */
    public static function get_moduleduration_formats() {
        // Hard-code date so users can see the difference between short dates with and without the leading zero.
        // Eg. 06/07/18 vs 6/07/18.
        $date = 1530849658;

        $moduledurationformats = [];

        $strmoduledurationformats = [
            'strftimedate',
            'strftimedatefullshort',
            'strftimedatefullshortwleadingzero',
            'strftimedateshort',
            'strftimedaydate',
            'strftimedayshort',
            'strftimemonthyear',
        ];

        foreach ($strmoduledurationformats as $strmoduledurationformat) {
            $moduledurationformats[$strmoduledurationformat] = self::get_moduleduration_format_string($date,$date, $strmoduledurationformat);
        }

        return $moduledurationformats;
    }

    /**
     * Returns the date in a readable format.
     *
     * @param int $date
     * @param string $moduledurationformat
     * @return string
     */
    protected static function get_moduleduration_format_string($modulestartdate,$moduleenddate,$moduledurationformat) {

        // if ($moduledurationformat == 'strftimedatefullshortwleadingzero') {
        if($moduleenddate > 0) {


            $certificatedate = userdate($modulestartdate, get_string('strftimedatefullshort', 'langconfig'), 99, false).'-'.userdate($moduleenddate, get_string('strftimedatefullshort', 'langconfig'), 99, false);
        } else {
            
            $certificatedate = userdate($modulestartdate, get_string('strftimedatefullshort', 'langconfig'), 99, false);

        }
        // } else if (get_string_manager()->string_exists($moduledurationformat, 'langconfig')) {
        //     $certificatedate = userdate($modulestartdate, get_string($moduledurationformat, 'langconfig')).'_'. userdate($moduleenddate, get_string($moduledurationformat, 'langconfig'));
        // } else {
        //     $certificatedate = userdate($modulestartdate, get_string('strftimedate', 'langconfig')).'_'.userdate($moduleenddate, get_string('strftimedate', 'langconfig'));
        // }
        $certificatedate='Duration. '.$certificatedate.' <span>خلال الفترة</span>';
        return $certificatedate;
    }
}
