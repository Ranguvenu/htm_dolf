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
 * @package local_notification
 */
defined('MOODLE_INTERNAL') || die();
function xmldb_local_questionbank_install(){
    global $CFG,$DB;
    require_once($CFG->libdir . '/questionlib.php');
    $thiscontext = context_system::instance();

     $edittab = 'categories';
     if ($thiscontext){
                    $contexts = new question_edit_contexts($thiscontext);
                    $contexts->require_one_edit_tab_cap($edittab);
    } else {
                    $contexts = null;
    }
    $defaultcategory = question_make_default_categories($contexts->all());
    $question_category = $DB->get_record_sql("SELECT * FROM {question_categories} where name ='top' and parent= 0 AND contextid=$thiscontext->id");
    
    $thispageurl = new moodle_url($CFG->wwwroot);
    $qcobject = new question_category_object($thiscontext->id, $thispageurl,
    $contexts->having_one_edit_tab_cap('categories'), $param->edit,
                                $question_category->id, $param->delete, $contexts->having_cap('moodle/question:add'));
    $data->workshopname = 'Workshop Categories';
    $data->workshopcategory = 'workshop_categories';
    if ($question_category) {//new category
        $newparent = $question_category->id.','.$thiscontext->id;
        $categoryid=$qcobject->add_category($newparent, $data->workshopname,
                               $question_category->info, $thiscontext->id, $question_category->infoformat,  $data->workshopcategory);
       
    } 
   
    
}
