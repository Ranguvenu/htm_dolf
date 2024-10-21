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
/**
 * Course list block caps.
 *
 * @author eabyas  <info@eabyas.in>
 * @package    Bizlms
 * @subpackage block_courselister
 */

defined('MOODLE_INTERNAL') || die;

$functions = array(

     'block_faq' => array(
        'classname' => 'block_faq_external',
        'methodname' => 'getfaq',
        'classpath'   => 'blocks/faq/classes/external.php',
        'description' => 'Get the faqs list',
        'type' => 'read',
        'ajax' => true,
    ),

     'block_faq_info' => array(
            'classname'   => 'block_faq_external',
            'methodname'  => 'faq_info',
            'classpath'   => 'blocks/faq/classes/external.php',
            'description' => 'faq details',
            'type'        => 'write',
            'ajax' => true,
    ),
      'block_create_faq' => array(
        'classname' => 'block_faq_external',
        'methodname' => 'create_faq',
        'classpath' => 'blocks/faq/classes/external.php',
        'description' => 'create new record',
        'ajax' => true,
        'type' => 'write',
     ),
       'block_delete_faq' => array(
            'classname'   => 'block_faq_external',
            'methodname'  => 'deletefaq',
            'classpath'   => 'blocks/faq/classes/external.php',
            'description' => 'delete faq',
            'type'        => 'write',
            'ajax' => true,
    ),
    
);


