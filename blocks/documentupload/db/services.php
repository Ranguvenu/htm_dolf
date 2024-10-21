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

     'block_documentupload' => array(
        'classname' => 'block_documentupload_external',
        'methodname' => 'getdocumentupload',
        'classpath'   => 'blocks/documentupload/classes/external.php',
        'description' => 'Get the documents card list',
        'type' => 'read',
        'ajax' => true,
    ),

     'block_documentupload_info' => array(
            'classname'   => 'block_documentupload_external',
            'methodname'  => 'documentupload_info',
            'classpath'   => 'blocks/documentupload/classes/external.php',
            'description' => 'documentupload details',
            'type'        => 'write',
            'ajax' => true,
    ),
      'block_create_documentupload' => array(
        'classname' => 'block_documentupload_external',
        'methodname' => 'create_documentupload',
        'classpath' => 'blocks/documentupload/classes/external.php',
        'description' => 'create new record',
        'ajax' => true,
        'type' => 'write',
     ),
       'block_delete_documentupload' => array(
            'classname'   => 'block_documentupload_external',
            'methodname'  => 'deletedocumentupload',
            'classpath'   => 'blocks/documentupload/classes/external.php',
            'description' => 'delete documentupload',
            'type'        => 'write',
            'ajax' => true,
    ),
    
);


