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
 * Defines competencies rendering functions.
 *
 * @package    local_competency
 * @copyright  e abyas  <info@eabyas.com>
 */
require_once($CFG->dirroot . '/local/competency/lib.php');
defined('MOODLE_INTERNAL') || die;

use context_system;
use html_table;
use local_competency\competency as competency;
use core_completion\progress;

class local_competency_renderer extends plugin_renderer_base {

    public function global_filter($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        return $this->render_from_template('theme_academy/global_filter', $filterparams);
    }
     /**
     * Display the competency tabs
     * @return string The text to render
     */
    public function get_competencies() {
        global $CFG, $OUTPUT,$PAGE,$USER;

        $context = context_system::instance();

        $stable = new \stdClass();
        $stable->thead = true;
        $stable->start = 0;
        $stable->length = -1;
        $stable->search = '';
        $stable->pagetype ='page';


        if((has_capability('local/competency:managecompetencies', $context)) || (has_capability('local/competency:viewcompetencies', $context)) || (has_capability('local/organization:manage_competencies_official', $context))){

            $filterdata = json_encode(array());

            $options = array('targetID' => 'viewcompetencies','perPage' => 10, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'table');

            $options['targetID']='viewcompetencies';
            $options['methodName']='local_competency_get_competencies';
            $options['templateName']='local_competency/listcompetencies';

            $cardoptions = json_encode($options);
            $cardparams = array(
                'targetID' => 'viewcompetencies',
                'options' => $cardoptions,
                'dataoptions' => $cardoptions,
                'filterdata' => $filterdata,
                'widthclass' => 'col-md-12',
            );
            $fncardparams=$cardparams;
            $competenciesmform = competency_filters_form($cardparams);
  

            $cardparams = $fncardparams+array(
                'addcompetency'=> (has_capability('local/competency:managecompetencies', $context) || has_capability('local/competency:canaddcompetency', $context) || has_capability('local/organization:manage_competencies_official', $context)) ? true : false,
                'uploadcompetency'=> (has_capability('local/competency:managecompetencies', $context) || has_capability('local/competency:canbulkuploadcompetency', $context) || has_capability('local/organization:manage_competencies_official', $context)) ? true : false,
                'contextid' => $context->id,
                'plugintype' => 'local',
                'plugin_name' =>'competency',
                'creatacompetency' => true,
                'cfg' => $CFG,
                'filterform' => $competenciesmform->render(),
                'globalinput'=>$this->global_filter($cardparams));

            return  $this->render_from_template('local_competency/viewcompetencies', $cardparams);

        }
        else{

            return "<div class='alert alert-danger'>" . get_string('nocompetencyspermission', 'local_competency') . "</div>";
        }
    }
    /**
     * Display the competency
     * @return string The text to render
     */
    public function view_competency($competencyid,$objectiveviewtype='allobjectives') {
        global $CFG, $OUTPUT,$PAGE,$USER;

        $context = context_system::instance();

        $stable = new \stdClass();
        $stable->thead = true;
        $stable->start = 0;
        $stable->length = -1;
        $stable->competencyid=$competencyid;

        $getcompetency = competency::get_competencies($stable,$filterdata=null);

        
        if($getcompetency){

            $getcompetency->competencyid=$getcompetency->id;

            $competencytypes=(new competency)::constcompetencytypes();

            $getcompetency->type=($type=$competencytypes[$getcompetency->type]) ? $type : $getcompetency->type;

           $levels=competency::competency_jobrole_levels($getcompetency->id,'list');

           $levelslist=array();

           $getcompetency->description =format_text($getcompetency->description);


            foreach($levels as $key=>$level){

                $levellist=array();


                $levellist['id']=$key;
                $levellist['levelid']=$level;
                $levellist['name']=get_string($level,'local_competency');

                // $levellist['jobroledescription']=array_values((competency::competency_jobroleresponsibility($level)));

                $stable->levelid = $level;

                $leveldescription=competency::get_competency_levelinfo($stable);


                $levellist['leveldescription']=format_text($leveldescription['competencyld']->description);

                $levellist['jobroledescription']=array();


                $stable->thead = false;

                $stable->level = $level;

                
                $exams=(new competency)::get_competency_level_exams_info($stable,$filterdata=null);

                $traingprograms=(new competency)::get_competency_level_trainingprograms_info($stable,$filterdata=null);    

                $achievementslist=array();

                $linkparams=array('class'=>"theme_text_color");

                if($traingprograms){

                    foreach($traingprograms as $traingprogram){

                        $list=array();

                        $courseurl = new moodle_url('/course/view.php', array('id' => $traingprogram->courseid));

                        $enrolurl = new moodle_url('/local/trainingprogram/programcourseoverview.php', array('programid' => $traingprogram->trgprgmid));

                        $list['achievementurl']= (!is_siteadmin() && has_capability('local/organization:manage_competencies_official', $context))? $enrolurl->out(): $courseurl->out();

                        $list['achievementtitle']=$traingprogram->fullname;

                        $list['achievementtype']=get_string('achievementtrainingprogram', 'local_competency');

                        $list['viewtype']=(new competency)::get_user_trainingprograms_check($traingprogram->trgprgmid);

                        $list['enrolurl']=$enrolurl->out();

                        $achievementslist[]=$list;
                    }
                }

                if($exams){

                    foreach($exams as $exam){

                        $list=array();

                        $courseurl = new moodle_url('/course/view.php', array('id' => $exam->courseid));

                        $enrolurl = new moodle_url('/local/exams/exams_qualification_details.php', array('id' => $exam->examid));

                        $list['achievementurl']= (!is_siteadmin() && has_capability('local/organization:manage_competencies_official', $context))? $enrolurl->out(): $courseurl->out();

                        $list['achievementtitle']=$exam->fullname;

                        $list['achievementtype']=get_string('achievementexam', 'local_competency');

                        $list['viewtype']=(new competency)::get_user_exams_check($exam->examid);

                        $list['enrolurl']=$enrolurl->out();

                        $achievementslist[]=$list;

                    }

                }

                $levellist['achievements'] =array_values($achievementslist);

                $levelslist[]=$levellist;

            }

            $getcompetency->level =array_values($levelslist);

            if(has_capability('local/competency:managecompetencies', $context) || has_capability('local/organization:manage_competencies_official', $context)){

                $getcompetency->achievementsviewtype =true;

            }else{

                $getcompetency->achievementsviewtype =false;

            }

            $getcompetency->addleveldescription =false;

            if((has_capability('local/competency:managecompetencies', $context)) || (has_capability('local/organization:manage_competencies_official', $context))){

                $getcompetency->addleveldescription =true;

            }

            return  $this->render_from_template('local_competency/detailedcompetency',(array)$getcompetency);

        }else{

            return "<div class='alert alert-danger'>" . get_string('invalidcompetencyid', 'local_competency') . "</div>";
        }
    }
    public function list_competencies($stable,$filterdata=null) {

        global $USER;
        $systemcontext = context_system::instance();
        $getcompetencies = competency::get_competencies($stable,$filterdata);
        $competencies=array_values($getcompetencies['competencies']);

        $row = array();

        $competencytypes=(new competency)::constcompetencytypes();


        $stable = new \stdClass();
        $stable->thead = true;

        $linkparams=array('class'=>"theme_text_color");
       $lang= current_language();  
        foreach ($competencies as $list) {
            $record = array();

            $record['competencyid']=$list->id;

            $competencyurl = new moodle_url('index.php', array('id' => $list->id));
            if( $lang == 'ar' && !empty($list->arabicname)){
                $listname = $list->arabicname;
            }else{
                $listname = $list->name;
            }
            $record['name']=\html_writer::link($competencyurl->out(),$listname, $linkparams);
            $record['competencyfullname']= $listname;

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

            $stable->competencyid = $list->id;
            
            $competencypc=competency::get_competency_performances($stable);

            $record['noperformance']=$competencypc['competencypccount'];

            if(has_capability('local/competency:managecompetencies', $systemcontext) || has_capability('local/organization:manage_competencies_official', $systemcontext)) {

                $record['action'] = true;
                $record['delete'] = true;
                $record['edit'] = true;
                $record['assignperformance'] = true;

            }else{

                $record['action'] = false;
                $record['delete'] = false;
                $record['edit'] = false;
                $record['assignperformance'] = false;

                if((has_capability('local/competency:caneditcompetency', $systemcontext)) || (has_capability('local/organization:manage_competencies_official', $systemcontext))) {
                    $record['edit'] = true;
                    $record['action'] = true;
                }

                if((has_capability('local/competency:candeletecompetency', $systemcontext)) || (has_capability('local/organization:manage_competencies_official', $systemcontext))) {
                    $record['delete'] = true;
                    $record['action'] = true;
                }

                if((has_capability('local/competency:managecompetencyperformance', $systemcontext)) || (has_capability('local/competency:canaddcompetencyperformance', $systemcontext))  || (has_capability('local/organization:manage_competencies_official', $systemcontext))) {
                    $record['assignperformance'] = true;
                    $record['action'] = true;
                }
            }
            $record['viewperformance'] = ((has_capability('local/competency:managecompetencyperformance', $systemcontext)) || (has_capability('local/competency:viewcompetencyperformance', $systemcontext))  || (has_capability('local/organization:manage_competencies_official', $systemcontext))) ? true : false;

            $row[] = $record;
         }
        return array_values($row);
    }
    public function lis_competency_performances($stable,$filterdata=null) {

        global $USER;

        $systemcontext = context_system::instance();

        $getcompetencies = competency::get_competency_performances($stable,$filterdata);

        $competencies=array_values($getcompetencies['competencypc']);


        $performancecriterias=(new competency)::constperformancecriterias();

        $kpis=(new competency)::constkpis();

        $objectives=(new competency)::constobjectives();

        $row = array();
        $lang= current_language(); 
        foreach ($competencies as $list) {
            $record = array();

            $record['competencyid']=$list->competency;

            $record['id']=$list->id;
            if($lang == 'ar' && !empty($list->criterianamearabic)){
                $record['criterianame']= $list->criterianamearabic;
            }else{
                $record['criterianame']=($criterianame=$performancecriterias[$list->criterianame]) ? $criterianame : $list->criterianame;
            }
            if($lang == 'ar' && !empty($list->kpinamearabic)){
               $record['kpiname']= $list->kpinamearabic;     
            }else{
                $record['kpiname']=($kpiname=$kpis[$list->kpiname]) ? $kpiname : $list->kpiname; 
            }
            if($lang == 'ar' && !empty($list->kpinamearabic)){
                $record['objectiveid']= $list->kpinamearabic;    
            }else{
               $record['objectiveid']=($objectiveid=$objectives[$list->objectiveid]) ? $objectiveid : $list->objectiveid; 
            }

            if((has_capability('local/competency:managecompetencyperformance', $systemcontext)) || (has_capability('local/organization:manage_competencies_official', $systemcontext))) {

                $record['action'] = true;
                $record['delete'] = true;
                $record['edit'] = true;
                $record['assignobjectives'] = true;

            }else{

                $record['action'] = false;
                $record['delete'] = false;
                $record['edit'] = false;
                $record['assignobjectives'] = false;

                if((has_capability('local/competency:caneditcompetencyperformance', $systemcontext))) {
                    $record['edit'] = true;
                    $record['action'] = true;
                }

                if((has_capability('local/competency:candeletecompetencyperformance', $systemcontext))) {
                    $record['delete'] = true;
                    $record['action'] = true;
                }

                if((has_capability('local/competency:managecompetencyobjectives', $systemcontext)) || (has_capability('local/competency:canaddcompetencyobjectives', $systemcontext))) {
                    $record['assignobjectives'] = true;
                    $record['action'] = true;
                }
            }
            $record['viewobjectives'] = ((has_capability('local/competency:managecompetencyobjectives', $systemcontext)) || (has_capability('local/competency:viewcompetencyobjectives', $systemcontext))) ? true : false;

            $row[] = $record;
         }
        return array_values($row);
    }
    /**
     * Display the competency tabs
     * @return string The text to render
     */
    public function get_mycompetencies() {
        global $CFG, $OUTPUT,$PAGE,$USER;

        $context = context_system::instance();

        if((!has_capability('local/competency:managecompetencies', $context)) && (!has_capability('local/competency:viewcompetencies', $context)) && (!has_capability('local/organization:manage_competencies_official', $context))){

            $stable = new \stdClass();
            $stable->thead = true;
            $stable->start = 0;
            $stable->length = -1;
            $stable->search = '';
            $stable->pagetype ='page';


            $filterdata = json_encode(array());


            $supportedoptions = array('targetID' => 'viewmysupportedcompetencies','perPage' => 10, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'table');

            $supportedoptions['targetID']='viewmysupportedcompetencies';
            $supportedoptions['methodName']='local_competency_get_mycompetencies';
            $supportedoptions['templateName']='local_competency/listmycompetencies';



            $currentoptions = array('targetID' => 'viewmycurrentjobrolecompetencies','perPage' => 10, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'table');

            $currentoptions['targetID']='viewmycurrentjobrolecompetencies';
            $currentoptions['methodName']='local_competency_get_mycompetencies';
            $currentoptions['templateName']='local_competency/listmycompetencies';


            $nextoptions = array('targetID' => 'viewmynextjobrolecompetencies','perPage' => 10, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'table');

            $nextoptions['targetID']='viewmynextjobrolecompetencies';
            $nextoptions['methodName']='local_competency_get_mycompetencies';
            $nextoptions['templateName']='local_competency/listmycompetencies';

            $cardoptions = json_encode($currentoptions);

            $nextcardoptions = json_encode($nextoptions);

            $supportedcardoptions = json_encode($supportedoptions);

            $cardparams = array(
                'targetID' => 'viewmycurrentjobrolecompetencies',
                'options' => $cardoptions,
                'nextoptions' =>  $nextcardoptions,
                'dataoptions' => $cardoptions,
                'nextdataoptions' =>  $nextcardoptions,
                'supportedoptions' => $supportedcardoptions,
                'supporteddataoptions' =>  $supportedcardoptions,
                'filterdata' => $filterdata
            );
            $fncardparams=$cardparams;

            $userinfo=competency::userjobrolelevelinfo();


            $currentjob=new \stdClass();
            $currentjob->jobrole=$userinfo->currentjobrole;

            $currentjob->jobrolelevel = (substr($userinfo->currentjobrolelevel,0,6) == 'Level ')? get_string(str_replace("Level ", "level", $userinfo->currentjobrolelevel),'local_competency') : ((substr($userinfo->currentjobrolelevel,0,5) == 'Level') ? get_string(str_replace("Level", "level", $userinfo->currentjobrolelevel),'local_competency') : get_string($userinfo->currentjobrolelevel,'local_competency'));

            if(isset($userinfo->nextjobrole)){

                $nextjob=new \stdClass();
                $nextjob->jobrole=$userinfo->nextjobrole;
                $nextjob->jobrolelevel=get_string($userinfo->nextjobrolelevel,'local_competency');

                $nextjobstr=get_string('nextjobjobrolecompetencies','local_competency',$nextjob);
           
            }else{
                $nextjobstr=false;
            }

            $cardparams = $fncardparams+array(
                'contextid' => $context->id,
                'plugintype' => 'local',
                'plugin_name' =>'competency',
                'cfg' => $CFG,
                'currentjob'=> !empty($currentjob->jobrole) ? get_string('mycurrentjobrolecompetencies','local_competency',$currentjob) : false,
                'nextjob'=>$nextjobstr);

            return  $this->render_from_template('local_competency/viewmycompetencies', $cardparams);
        }else{

            return "<div class='alert alert-danger'>" . get_string('nocompetencyspermission', 'local_competency') . "</div>";
        }

  
    }
    public function lis_mycompetencies_bytype($stable,$filterdata=null) {

        global $USER,$DB,$SESSION;
        $SESSION->lang = ($stable->mlang) ? $stable->mlang : current_language();
        $systemcontext = context_system::instance();
        $getcompetencies = competency::get_mycompetencies($stable,$filterdata);
        $competencies=array_values($getcompetencies['competencies']);

        $competencytypes=(new competency)::constcompetencytypes();

        $row = array();

        $stable = new \stdClass();
        $stable->thead = true;

        $linkparams=array('target'=>"_blank",'class'=>"theme_text_color");

        $lang= current_language();  
    
        foreach ($competencies as $list) {
            $record = array();

            $record['competencyid']=$list->id;

            $competencyurl = new moodle_url('mycompetency.php', array('id' => $list->id));

            if( $SESSION->lang == 'ar' && !empty($list->arabicname)) {
                $listname = $list->arabicname;
            } else {
                $listname = $list->name;
            }
            //$listname = ($lang == 'ar') ? $list->arabicname : $list->name;
           
            $record['name']=\html_writer::link($competencyurl->out(),$listname, $linkparams);

            $record['code']=$list->code;
            $record['competencyfullname']=$list->name;
            $record['description']=strip_tags(format_text($list->description, FORMAT_HTML));

            if($list->level){

                $levels=explode(',',$list->level);


                foreach($levels as $key => $level){


                    $levels[$key]= get_string($level,'local_competency');

                }

                $record['level']=implode(',',$levels);

            }else{

                $record['level']=get_string('na','local_competency');

            }

            $competencytypes=(new competency)::constcompetencytypes();
            $record['competencies'] = [
                'competencytype'=>$competencytypes[$list->type],
                'data'=>[
                    'id'=>$list->id,
                    'code'=>$list->code,
                    'name'=>$listname,
                    'description'=>strip_tags(format_text($list->description, FORMAT_HTML)),
                    'level'=>$record['level']
                ],
           ]; 
            $record['type']=($type=$competencytypes[$list->type]) ? $type : $list->type;

            $row[] = $record;
         }
        return array_values($row);
    }
    /**
     * Display the competency tabs
     * @return string The text to render
     */
    public function get_myallcompetencies() {

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

            $options = array('targetID' => 'viewmyallcompetencies','perPage' => 10, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'table');

            $options['targetID']='viewmyallcompetencies';
            $options['methodName']='local_competency_get_myallcompetencies';
            $options['templateName']='local_competency/listmyallcompetencies';

            $cardoptions = json_encode($options);
            $cardparams = array(
                'targetID' => 'viewmyallcompetencies',
                'options' => $cardoptions,
                'dataoptions' => $cardoptions,
                'filterdata' => $filterdata,
            );
            $fncardparams=$cardparams;
            $competenciesmform = competency_filters_form($cardparams);



            $cardparams = $fncardparams+array(
                'contextid' => $context->id,
                'plugintype' => 'local',
                'plugin_name' =>'competency',
                'cfg' => $CFG,
                'filterform' => $competenciesmform->render());

            return  $this->render_from_template('local_competency/viewmyallcompetencies', $cardparams);
        }else{

            return "<div class='alert alert-danger'>" . get_string('nocompetencyspermission', 'local_competency') . "</div>";
        }
    }
    public function lis_myallcompetencies($stable,$filterdata=null) {

        global $USER;
        $systemcontext = context_system::instance();
        $getcompetencies = competency::get_myallcompetencies($stable,$filterdata);
        $competencies=array_values($getcompetencies['competencies']);

        $competencytypes=(new competency)::constcompetencytypes();

        $row = array();


        $stable = new \stdClass();
        $stable->thead = true;

        $linkparams=array('target'=>"_blank",'class'=>"theme_text_color");

        $lang= current_language();  
    
        foreach ($competencies as $list) {
            $record = array();

            $record['competencyid']=$list->id;

            $competencyurl = new moodle_url('myallcompetency.php', array('id' => $list->id));

            if( $lang == 'ar' && !empty($list->arabicname)){
                $listname = $list->arabicname;
            }else{
                $listname = $list->name;
            }

            $record['name']=\html_writer::link($competencyurl->out(),$listname, $linkparams);
            $record['competencyname'] = $listname;

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
    public function list_objectives_examsinfo($stable,$filterdata=null) {

        global $USER;

        $systemcontext = context_system::instance();
        

        if($stable->allobjectives == 'supported'){

            $getexams = competency::get_supportedcompetency_exams_info($stable,$filterdata);

        }elseif($stable->allobjectives == 'all'){

            $getexams = competency::get_competency_exams_info($stable,$filterdata);

        }else{

           $getexams = competency::get_objectives_exams_info($stable,$filterdata);

        }

        $exams=array_values($getexams['exams']);

        $row = array();


        $stable = new \stdClass();
        $stable->thead = true;
    
        foreach ($exams as $list) {
            $record = array();

            $record['id']=$list->examid;

            $record['competencypcid']=($list->competencypc) ? $list->competencypc : $list->examid;

            $record['name']=$list->examname;

            $record['code']=$list->examcode;

            $record['delete'] = false;

            $courseurl = new moodle_url('/course/view.php', array('id' => $list->courseid));

            $record['objectiveurl']=$courseurl->out();

            if((has_capability('local/competency:candeletecompetencyobjectives', $systemcontext)) || (has_capability('local/competency:managecompetencyobjectives', $systemcontext))) {
                $record['delete'] = true;
            }
            
            $row[] = $record;
         }
        return array_values($row);
    }
    public function list_objectives_trainingprogramsinfo($stable,$filterdata=null) {

        global $USER;

        $systemcontext = context_system::instance();

        if($stable->allobjectives == 'supported'){

            $gettrainingprogramscount = competency::get_supportedcompetency_trainingprograms_info($stable,$filterdata);

        }elseif($stable->allobjectives == 'all'){

            $gettrainingprogramscount = competency::get_competency_trainingprograms_info($stable,$filterdata);

        }else{

            $gettrainingprogramscount = competency::get_objectives_trainingprograms_info($stable,$filterdata);

        }

        $gettrainingprogramscount=array_values($gettrainingprogramscount['trainingprograms']);

        $row = array();


        $stable = new \stdClass();
        $stable->thead = true;
    
        foreach ($gettrainingprogramscount as $list) {
            $record = array();

            $record['id']=$list->trgprgmid;

            $record['competencypcid']=($list->competencypc) ? $list->competencypc : $list->trgprgmid;

            $record['name']=$list->trgprgmname;

            $record['code']=$list->trgprgmcode;

            $record['delete'] = false;

            $courseurl = new moodle_url('/course/view.php', array('id' => $list->courseid));

            $record['objectiveurl']=$courseurl->out();

            if((has_capability('local/competency:candeletecompetencyobjectives', $systemcontext)) || (has_capability('local/competency:managecompetencyobjectives', $systemcontext))) {
                $record['delete'] = true;
            }

            $row[] = $record;
         }
        return array_values($row);
    }
    public function list_objectives_questionsinfo($stable,$filterdata=null) {

        global $USER;

        $systemcontext = context_system::instance();
        
        $getquestions = competency::get_objectives_questions_info($stable,$filterdata);


        $questions=array_values($getquestions['questions']);

        $row = array();


        $stable = new \stdClass();
        $stable->thead = true;
    
        foreach ($questions as $list) {
            $record = array();

            $record['id']=$list->qid;

            $record['competencypcid']=($list->competencypc) ? $list->competencypc : $list->qid;

            $record['name']=mb_convert_encoding(clean_text(html_to_text(html_entity_decode($list->qname))), 'UTF-8');

            $record['code']=get_string('pluginname','qtype_'.$list->qqtype);

            $record['delete'] = false;

            $questionurl = new moodle_url('/question/bank/previewquestion/preview.php', array('id' => $list->qid));

            $record['objectiveurl']=$questionurl->out();

            if((has_capability('local/competency:candeletecompetencyobjectives', $systemcontext)) || (has_capability('local/competency:managecompetencyobjectives', $systemcontext))) {
                $record['delete'] = true;
            }
            
            $row[] = $record;
         }
        return array_values($row);
    }
    public function list_objectives_levelsinfo($stable,$filterdata=null) {

        global $USER;

        $systemcontext = context_system::instance();
        
        $getlevels = competency::get_objectives_levels_info($stable,$filterdata);


        $levels=array_values($getlevels['levels']);

        $row = array();


        $stable = new \stdClass();
        $stable->thead = true;
    
        foreach ($levels as $list) {
            $record = array();

            $record['id']=$list->lvlid;

            $record['competencypcid']=($list->competencypc) ? $list->competencypc : $list->lvlid;

            $record['name']=$list->lvlname;

            $record['code']=$list->lvlname;

            $record['delete'] = false;
            $record['objectiveurl']='#';

            if((has_capability('local/competency:candeletecompetencyobjectives', $systemcontext)) || (has_capability('local/competency:managecompetencyobjectives', $systemcontext))) {
                $record['delete'] = true;
            }
            
            $row[] = $record;
         }
        return array_values($row);
    }
    public function list_allcompetencies_service($stable,$filterdata=null) {

        global $USER;

        $systemcontext = context_system::instance();

        $getcompetencies = competency::get_competencies($stable,$filterdata);

        $competencies=array_values($getcompetencies['competencies']);

        $row = array();

        $competencytypes=(new competency)::constcompetencytypes();


        $stable = new \stdClass();
        $stable->thead = true;

       $lang= current_language();  

        foreach ($competencies as $list) {
            $record = array();

            $record['competencyid']=$list->id;

            if( $lang == 'ar' && !empty($list->arabicname)){

                $listname = $list->arabicname;

            }else{

                $listname = $list->name;

            }

            $record['competencyname']=$listname;

            $record['competencycode']=$list->code;


            if($list->level){

                $levels=explode(',',$list->level);


                foreach($levels as $key => $level){


                    $levels[$key]= get_string($level,'local_competency');

                }

                $record['competencylevels']=implode(',',$levels);

            }else{

                $record['competencylevels']=get_string('na','local_competency');

            }

            $record['competencytype']=($type=$competencytypes[$list->type]) ? $type : $list->type;

            $stable->competencyid = $list->id;
            
            $competencypc=competency::get_competency_performances($stable);

            $record['noperformancecriterias']=$competencypc['competencypccount'];

            $row[] = $record;
         }

        return array_values($row);
    }
     /**
     * Display the competency
     * @return string The text to render
     */
    public function list_detailedcompetencyview_service($competencyid) {
        global $CFG, $OUTPUT,$PAGE,$USER;

        $context = context_system::instance();

        $stable = new \stdClass();
        $stable->thead = true;
        $stable->start = 0;
        $stable->length = -1;
        $stable->competencyid=$competencyid;

        $filterdata= new \stdClass();

        $getcompetency = competency::get_competencies($stable,$filterdata=null);
        
        if($getcompetency){

            $competencytypes=(new competency)::constcompetencytypes();

            $getcompetency->type=($type=$competencytypes[$getcompetency->type]) ? $type : $getcompetency->type;


            $getcompetency->description =format_text($getcompetency->description);


           $levels=competency::competency_jobrole_levels($getcompetency->id,'list');

           $levelslist=array();

            if($levels){

                foreach($levels as $key=>$level){

                    $levellist=array();


                    $levellist['levelid']=$key;

                    $levellist['levelname']=get_string($level,'local_competency');

                    $levellist['jobroledescription']=array();

                    $stable->thead = false;

                    $stable->level = $level;

                    
                    $exams=(new competency)::get_competency_level_exams_info($stable,$filterdata=null);

                    $traingprograms=(new competency)::get_competency_level_trainingprograms_info($stable,$filterdata=null);    

                    $achievementslist=array();


                    if($traingprograms){

                        foreach($traingprograms as $traingprogram){

                            $list=array();

                            $list['traingprogramid']=$traingprogram->trgprgmid;

                            $list['traingprogramtitle']=$traingprogram->fullname;

                            $achievementslist[]=$list;
                        }
                    }

                    if($exams){

                        foreach($exams as $exam){

                            $list=array();


                            $list['examid']=$exam->examid;

                            $list['examtitle']=$exam->fullname;

                            $achievementslist[]=$list;

                        }
                    }

                    $levellist['learningcontents'] =array_values($achievementslist);

                    $levelslist[]=$levellist;

                }
            }

            $getcompetency->level =array_values($levelslist);


            $performances=array();

            $stable->competencyid = $getcompetency->id;
            $stable->thead = false;
            $stable->start = 0;
            $stable->length = -1;

            $getcompetencies = competency::get_competency_performances($stable,$filterdata=null);

            $competencies=array_values($getcompetencies['competencypc']);

            if($competencies){

                    $performancelist=array();

                    $performancecriterias=(new competency)::constperformancecriterias();

                    $kpis=(new competency)::constkpis();

                    $objectives=(new competency)::constobjectives();

                    $row = array();

                    $lang= current_language(); 

                    foreach ($competencies as $list) {

                        $record = array();

                        $record['criteriaid']=$list->id;

                        if($lang == 'ar' && !empty($list->criterianamearabic)){
                            $record['criterianame']= $list->criterianamearabic;
                        }else{
                            $record['criterianame']=($criterianame=$performancecriterias[$list->criterianame]) ? $criterianame : $list->criterianame;
                        }
                        if($lang == 'ar' && !empty($list->kpinamearabic)){
                           $record['kpiname']= $list->kpinamearabic;     
                        }else{
                            $record['kpiname']=($kpiname=$kpis[$list->kpiname]) ? $kpiname : $list->kpiname; 
                        }
                        if($lang == 'ar' && !empty($list->kpinamearabic)){
                            $record['objectiveid']= $list->kpinamearabic;    
                        }else{
                           $record['objectiveid']=($objectiveid=$objectives[$list->objectiveid]) ? $objectiveid : $list->objectiveid; 
                        }

                        $assignobjectiverow = array();

                        $filterdata->competencypcid=$list->id;

                        $getexams = competency::get_objectives_exams_info($stable,$filterdata);


                        $assignobjectiveexams=array_values($getexams['exams']);


                    
                        foreach ($assignobjectiveexams as $assignobjectiveexam) {


                            $assignobjectiverecord = array();

                            $assignobjectiverecord['examid']=$assignobjectiveexam->examid;

                            $assignobjectiverecord['examname']=$assignobjectiveexam->examname;

                            $assignobjectiverecord['examcode']=$assignobjectiveexam->examcode;
                            
                            $assignobjectiverow[] = $assignobjectiverecord;
                         }

                        $gettrainingprogramscount = competency::get_objectives_trainingprograms_info($stable,$filterdata);

                        
                        $gettrainingprograms=array_values($gettrainingprogramscount['trainingprograms']);

                    
                        foreach ($gettrainingprograms as $gettrainingprogram) {

                            $assignobjectiverecord = array();

                            $assignobjectiverecord['trainingprogramid']=$gettrainingprogram->trgprgmid;

                            $assignobjectiverecord['trainingprogramname']=$gettrainingprogram->trgprgmname;

                            $assignobjectiverecord['trainingprogramcode']=$gettrainingprogram->trgprgmcode;


                            $assignobjectiverow[] = $assignobjectiverecord;
                         }

                        $getquestions = competency::get_objectives_questions_info($stable,$filterdata);


                        $questions=array_values($getquestions['questions']);

                    
                        foreach ($questions as $question) {

                            $assignobjectiverecord = array();

                            $assignobjectiverecord['id']=$question->qid;

                            $assignobjectiverecord['name']=mb_convert_encoding(clean_text(html_to_text(html_entity_decode($question->qname))), 'UTF-8');

                            $assignobjectiverecord['code']=get_string('pluginname','qtype_'.$question->qqtype);
                            
                            $assignobjectiverow[] = $assignobjectiverecord;
                         }

                        $record['objectivelearningcontent'] = array_values($assignobjectiverow);

                        $row[] = $record;
                     }

                    $performancelist['performancelist'] =array_values($row);

                    $performances[]=$performancelist;

            }

            $getcompetency->performances =array_values($performances);

            return $getcompetency;


        }else{

            return get_string('invalidcompetencyid', 'local_competency');
        }
    }
}
