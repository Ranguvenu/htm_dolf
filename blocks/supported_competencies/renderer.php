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
 * Defines levelup target rendering functions.
 *
 * @package    block_supported_competencies
 * @copyright  e abyas  <info@eabyas.com>
 */
defined('MOODLE_INTERNAL') || die;

use context_system;

use block_supported_competencies\local\supported_competencies as supportedcompetencies;

class block_supported_competencies_renderer extends plugin_renderer_base {

    public function mysupported_competencies($display='block'){

        global $CFG, $OUTPUT,$PAGE,$USER;

        $context = context_system::instance();

        if((!has_capability('local/competency:managecompetencies', $context)) && (!has_capability('local/competency:viewcompetencies', $context))){

            $stable = new \stdClass();
            $stable->thead = true;
            $stable->start = 0;
            $stable->length = -1;
            $stable->search = '';
            $stable->pagetype ='page';


            $filterdata = json_encode(array());

            $perPage=10;

            if($display=='block'){

                $perPage=6;

            }

            $supportedoptions = array('targetID' => 'viewmysupportedcompetencies','perPage' => 10, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'table');

            $supportedoptions['targetID']='viewmysupportedcompetencies';
            $supportedoptions['methodName']='block_supported_competencies_get_mysupportedcompetencies';
            $supportedoptions['templateName']='block_supported_competencies/listmysupportedcompetencies';

            $supportedcardoptions = json_encode($supportedoptions);

            $cardparams = array(
                'targetID' => 'viewmysupportedcompetencies',
                'supportedoptions' => $supportedcardoptions,
                'supporteddataoptions' =>  $supportedcardoptions,
                'filterdata' => $filterdata,
            );
            $fncardparams=$cardparams;

            $stable = new \stdClass();
            $stable->supportedcompetencies =true;
            $stable->thead = true;

            $competencies=supportedcompetencies::get_mysupportedcompetencies($stable,null);
            $totalcount=$competencies['competenciescount'];


            $cardparams = $fncardparams+array(
                'contextid' => $context->id,
                'plugintype' => 'blocks',
                'plugin_name' =>'supported_competencies',
                'cfg' => $CFG,
                'totalcount'=>$totalcount,
                'viewmore'=> false,
            );

            if($display =='block'){

                $PAGE->requires->css('/blocks/supported_competencies/css/jquery.dataTables.min.css', true);
            
            }

            if(has_capability('local/organization:manage_trainee', $context)) {

                return  $this->render_from_template('block_supported_competencies/viewmysupportedcompetencies', $cardparams);

            } 

        }
    }
    public function lis_mysupportedcompetencies_bytype($stable,$filterdata=null) {

        global $USER;
        $systemcontext = context_system::instance();
        $getcompetencies = supportedcompetencies::get_mysupportedcompetencies($stable,$filterdata);
        $competencies=array_values($getcompetencies['competencies']);

        $competencytypes=(new supportedcompetencies)::constcompetencytypes();

        $row = array();

        $stable = new \stdClass();
        $stable->thead = true;

        $linkparams=array('target'=>"_blank",'class'=>"theme_text_color");
    
        foreach ($competencies as $list) {
            $record = array();

            $record['competencyid']=$list->id;

            $competencyurl = new moodle_url('/blocks/supported_competencies/index.php', array('id' => $list->id));

            $record['name']=\html_writer::link($competencyurl->out(),$list->name, $linkparams);

            $record['code']=$list->code;


            if($list->level){

                $levels=explode(',',$list->level);


                foreach($levels as $key => $level){


                    $levels[$key]= get_string($level,'local_competency');

                }

                $record['level']=implode(',',$levels);

            }else{

                $record['level']=get_string('na','local_competency');

            }

            $record['type']=($type=$competencytypes[$list->type]) ? $type : $list->type;

            $row[] = $record;
         }
        return array_values($row);
    }
}
