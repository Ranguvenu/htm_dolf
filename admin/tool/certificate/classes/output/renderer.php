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
 * Contains renderer class.
 *
 * @package   tool_certificate
 * @copyright 2017 Mark Nelson <markn@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_certificate\output;

use plugin_renderer_base;

/**
 * Renderer class.
 *
 * @package    tool_certificate
 * @copyright  2017 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {

    /**
     * Renders the verify certificate results.
     *
     * Defer to template.
     *
     * @param \tool_certificate\output\verify_certificate_results $page
     * @return string html for the page
     */
    public function render_verify_certificate_results(verify_certificate_results $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('tool_certificate/verify_certificate_results', $data);
    }

    /**
     * Renders a table.
     *
     * @param \table_sql $table
     * @return string HTML
     */
    public function render_table(\table_sql $table) {

        ob_start();
        $table->out(10, true);
        $tablecontents = ob_get_contents();
        ob_end_clean();

        return $tablecontents;
    }

    public function view_certificates($filter = false){
        global $USER;
        $systemcontext = \context_system::instance();
        $options = array('targetID' => 'view_certificates','perPage' => 10, 'cardClass' => 'w_oneintwo', 'viewType' => 'table');
        $options['methodName']='tool_certificate_view_all_certificates';
        $options['templateName']='tool_certificate/view_certificates'; 
        $options = json_encode($options);
        $dataoptions = json_encode(array('userid' =>$USER->id,'contextid' => $systemcontext->id));
        $filterdata = json_encode(array());

        $context = [
                'targetID' => 'view_certificates',
                'options' => $options,
                'dataoptions' => $dataoptions,
                'filterdata' => $filterdata
        ];
        if($filter){
            return  $context;
        }else{
            return  $this->render_from_template('theme_academy/cardPaginate', $context);
        }
    }
    
    public function global_filter($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        return $this->render_from_template('theme_academy/global_filter', $filterparams);;
    }

    public function listofcertificates($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = \context_system::instance();
        echo $this->render_from_template('tool_certificate/listofcertificates', $filterparams);
    }
}
