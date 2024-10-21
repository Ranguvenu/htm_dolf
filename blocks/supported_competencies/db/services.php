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

$functions = array(
    'block_supported_competencies_get_mysupportedcompetencies' => array(
        'classname' => 'block_supported_competencies_external',
        'methodname' => 'get_mysupportedcompetencies',
        'classpath' => 'blocks/supported_competencies/externallib.php',
        'description' => 'Get user supported competencies',
        'ajax' => true,
        'type' => 'read',
    )
);

