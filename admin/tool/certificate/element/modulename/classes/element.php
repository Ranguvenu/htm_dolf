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
 * @package Bizlms 
 * @subpackage local_certificates
 */

namespace certificateelement_modulename;

defined('MOODLE_INTERNAL') || die();

class element extends \tool_certificate\element {


    /**
     * @var int Show creation date
     */
    const CUSTOMCERT_MODULENAMETYPE_ENGLISH = 1;

    const CUSTOMCERT_MODULENAMETYPE_ARABIC = 2;


    /**
     * This function renders the form elements when adding a certificate element.
     *
     * @param \MoodleQuickForm $mform the edit_form instance
     */
    public function render_form_elements($mform) {

        // Get the possible date options.
        $dateoptions = [];
        $dateoptions[self::CUSTOMCERT_MODULENAMETYPE_ENGLISH] = get_string('modulenameenglish', 'certificateelement_modulename');

        $dateoptions[self::CUSTOMCERT_MODULENAMETYPE_ARABIC] = get_string('modulenamearabic', 'certificateelement_modulename');

        $mform->addElement('select', 'modulenametype', get_string('modulenametype', 'certificateelement_modulename'), $dateoptions);
        $mform->addHelpButton('modulenametype', 'modulenametype', 'certificateelement_modulename');

        parent::render_form_elements($mform);
    }

    /**
     * Handles saving the form elements created by this element.
     * Can be overridden if more functionality is needed.
     *
     * @param \stdClass $data the form data or partial data to be updated (i.e. name, posx, etc.)
     */
    public function save_form_data(\stdClass $data) {
        $data->data = json_encode(['modulenametype' => $data->modulenametype]);
        parent::save_form_data($data);
    }

    /**
     * Handles rendering the element on the pdf.
     *
     * @param \pdf $pdf the pdf object
     * @param bool $preview true if it is a preview, false otherwise
     * @param \stdClass $user the user we are rendering this for
     * @param obj $moduleinfo having information with moduletype and moduleid
     */
    public function render($pdf, $preview, $user, $moduleinfo = false) {

        // Decode the information stored in the database.
        $modulenametype = @json_decode($this->get_data(),true);
        $modulename = \tool_certificate\element_helper::get_modulename($this->get_id(),$user, $moduleinfo, $modulenametype['modulenametype']);
        \tool_certificate\element_helper::render_content($pdf, $this,  $modulename);
    }

    public function get_moduleinfo(){
        global $DB;

        $modinfo = new \stdClass();

        $modinfo->modulename = $this->modulename;
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
        global $COURSE;

        // Decode the information stored in the database.
        $modulenametype = @json_decode($this->get_data(),true);

        $modulename = \tool_certificate\element_helper::get_modulename($this->get_id(), 2, $this->get_moduleinfo(), $modulenametype['modulenametype']);
        return \tool_certificate\element_helper::render_html_content($this, $modulename);
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
            $record->modulenametype = $dateinfo->modulenametype;
        }
        return $record;
    }
}
