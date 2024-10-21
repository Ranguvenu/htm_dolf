<?php
/*
* This file is a part of e abyas Info Solutions.
*
* Copyright e abyas Info Solutions Pvt Ltd, India.
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
* @author e abyas  <info@eabyas.com>
*/
/**
 * Plugin version and other meta-data are defined here.
 *
 * @package    block_supported_competencies
 * @copyright  e abyas  <info@eabyas.com>
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/blocks/supported_competencies/lib.php'); 

class block_supported_competencies extends block_base {
    public function init() {
        $this->title = get_string('title', 'block_supported_competencies');
    }
	function has_config() {
		return true;
	}
	/**
     * Creates the blocks main content
     *
     * @return string
     */
	public function get_content() {
		if ($this->content !== null) {
			return $this->content;
		}

		global $OUTPUT,$USER,$PAGE,$DB;
		$this->content  =  new stdClass;
		
		$renderer = $PAGE->get_renderer('block_supported_competencies');


		$this->content->text   = '';
			
		return $this->content;
	}

}
