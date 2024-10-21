<?php 
namespace local_trainingprogram\form;

use core_form\dynamic_form ;
use moodle_url;
use context;
use context_system;
use local_trainingprogram\local\trainingprogram as tp;
use local_trainingprogram\local\dataprovider as dataprovider;
use local_competency\competency;
require_once($CFG->libdir . '/formslib.php');


\MoodleQuickForm::registerElementType('segment_autocomplete',
    $CFG->dirroot . '/local/trainingprogram/classes/form/segment_autocomplete.php',
    '\\local_trainingprogram\\form\\segment_autocomplete');
/**
 * Defines the version of Training program
 *
 * @package    local_trainingprogram
 * @copyright  2022 Naveen kumar <naveen@eabyas.com>
 */

/**
 * Training program form
 */
class trainingprogram extends dynamic_form
{

    /**
     * Define the form
     */
    public function definition () {
        global $CFG, $DB;

        $mform = $this->_form;

        $mform->addElement('hidden', 'newjobfamilyoption', '',array('class'=>'new_jobfamily_option'));
        $mform->setType('newjobfamilyoption',PARAM_RAW);

        $id = $this->optional_param('id', 0, PARAM_INT);
        $courseid = $this->optional_param('courseid', 0, PARAM_INT);

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);


        //$mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text','name', get_string('title', 'local_trainingprogram'),'size="80"');
        $mform->addRule('name', get_string('required'), 'required', null, 'server');
        $mform->setType('name', PARAM_TEXT);

        $mform->addElement('text','namearabic', get_string('titlearabic', 'local_trainingprogram'),'size="80"');
        $mform->addRule('namearabic', get_string('required'), 'required', null, 'server');
        $mform->setType('namearabic', PARAM_TEXT);

        // $pnature = $DB->get_record('local_trainingprogram', array('id' => $id));

        // if (!empty($pnature->id)) {

        //     if ($pnature->programnature==0) {
        //     $preparatory = get_string('preparatory', 'local_trainingprogram');
        //     $mform->addElement('static', '', get_string('programnature', 'local_trainingprogram'),$preparatory);
        // }
        //     if ($pnature->programnature==1) {
        //         $regular = get_string('regular', 'local_trainingprogram');
        //         $mform->addElement('static', '', get_string('programnature', 'local_trainingprogram'),$regular);
        //     }
        // }

        // else{
        $programnature=array();
        $programnature[] =& $mform->createElement('radio', 'pnature', '',get_string('preparatory', 'local_trainingprogram'), 0);
        $programnature[] =& $mform->createElement('radio', 'pnature', '',get_string('regular', 'local_trainingprogram'), 1);
        $elem = $mform->addGroup($programnature, 'programnature', get_string('programnature', 'local_trainingprogram'), '&nbsp&nbsp', false);
        $mform->addRule('programnature', 'This field is required.', 'required', null, 'client');
        $mform->setType('pnature', PARAM_INT);
        //}

        $topicattributes = array(
         'multiple'=>true,
        );
        $current_lang = current_language();
        $topicslists=$DB->get_records_sql('SELECT id,name FROM {training_topics}');
        $trainingtopics=[];
        foreach ($topicslists AS $topic){
          $trainingtopics[$topic->id]=format_text($topic->name,'FORMAT_HTML');
        }
        $mform->addElement('autocomplete', 'trainingtopics',get_string('trainingtopics', 'local_trainingprogram'),$trainingtopics,$topicattributes);
        $trainingmethodsattributes = array(
         'multiple'=>true,
        );
        $trainingmethods =[];
        $trainingmethods[null] = null;
        $trainingmethods['online'] = get_string('scheduleonline','local_trainingprogram');
        $trainingmethods['offline'] = get_string('scheduleoffline','local_trainingprogram');
        $trainingmethods['elearning'] = get_string('scheduleelearning','local_trainingprogram');

        $mform->addElement('autocomplete','trainingtype', get_string('trainingtypes', 'local_trainingprogram'),$trainingmethods,$trainingmethodsattributes);

        $filemanageroptions = array(
            'accepted_types' => array(get_string('png_format', 'local_trainingprogram'), 
                get_string('jpg_format', 'local_trainingprogram'),get_string('jpeg_format', 'local_trainingprogram')),
            'maxbytes' => 0,
            'maxfiles' => 1,
        );
        $mform->addElement('filepicker','image',get_string('image', 'local_trainingprogram'),null,$filemanageroptions);
        $mform->addRule('image', get_string('required'), 'required', null);

        $availablefromgroup=array();
        $availablefromgroup[] =& $mform->createElement('radio', 'cost', '',get_string('paid', 'local_trainingprogram'), 1);
        $availablefromgroup[] =& $mform->createElement('radio', 'cost', '',get_string('free', 'local_trainingprogram'), 0);
        $mform->addGroup($availablefromgroup, 'price', get_string('price', 'local_trainingprogram'), '&nbsp&nbsp', false);
        $mform->setDefault('cost', 0);

         // $mform->addElement('advcheckbox', 'ratingtime', get_string('ratingtime', 'forum'), 'Label displayed after checkbox', array('group' => 1), array(0, 1));

        $mform->addElement('text','sellingprice',  get_string('sellingprice', 'local_trainingprogram'));
        // $mform->addRule('sellingprice', get_string('required'), 'required', null, 'server');
        $mform->addRule('sellingprice', get_string('numeric'), 'numeric');
        $mform->addElement('text','actualprice', get_string('actualprice', 'local_trainingprogram'));
        // $mform->addRule('actualprice', get_string('required'), 'required', null, 'server');
        $mform->addRule('actualprice', get_string('numeric'), 'numeric');

        $mform->hideif('sellingprice', 'cost', 'eq', 0);
        $mform->hideif('actualprice', 'cost', 'eq', 0);

        $taxfreeformgroup=array();
        $taxfreeformgroup[] =& $mform->createElement('radio', 'tax', '',get_string('yes', 'local_trainingprogram'), 0);
        $taxfreeformgroup[] =& $mform->createElement('radio', 'tax', '',get_string('no', 'local_trainingprogram'), 1);
        $mform->addGroup($taxfreeformgroup, 'tax_free', get_string('tax_free', 'local_trainingprogram'), '&nbsp&nbsp', false);
        $mform->setDefault('tax', 0);
        $mform->hideif('tax_free', 'cost', 'eq', 0);

        $mform->addElement('editor','description', get_string('description', 'local_trainingprogram'));
        $mform->addRule('description', get_string('required'), 'required', null, 'server');
        $mform->setType('description', PARAM_RAW);

        /*$mform->addElement('editor','program_goals', get_string('program_goals', 'local_trainingprogram'));
        $mform->addRule('program_goals', get_string('required'), 'required', null, 'server');
        $mform->setType('program_goals', PARAM_RAW);*/

        $languages = [];
        $languages['0'] = get_string('arabic','local_trainingprogram');
        $languages['1'] = get_string('english','local_trainingprogram');

        $langoptions = array(
            'class' => 'el_languages',
            'multiple' => true,
            'noselectionstring' => get_string('noselection', 'local_trainingprogram'),

        );

        $mform->addElement('autocomplete','language', get_string('language', 'local_trainingprogram'),$languages, $langoptions);
        $mform->addRule('language', get_string('selectlang','local_trainingprogram'), 'required');
      
        
        //$languageselect = $mform->addElement('autocomplete','language', get_string('language', 'local_trainingprogram'), array_values(dataprovider::$languages));
        // $languageselect = $mform->addElement('autocomplete','language', get_string('language', 'local_trainingprogram'),$languages);
        // $mform->addRule('language', get_string('selectlang','local_trainingprogram'), 'required');
        // $languageselect->setMultiple(true);


        // $programmethods = [];
        // $programmethods['0'] = get_string('lecture','local_trainingprogram');
        // $programmethods['1'] = get_string('case_studies','local_trainingprogram');
        // $programmethods['2'] = get_string('dialogue_teams','local_trainingprogram');
        // $programmethods['3'] = get_string('exercises_assignments','local_trainingprogram');
        
        //renu........program method dynamic
           $current_lang = current_language();
           $programmethodslists=$DB->get_records_sql('SELECT id,name FROM {program_methods}');
           $programmethods=[];
           foreach ($programmethodslists AS $program_methods){
             $programmethods[$program_methods->id]=format_text($program_methods->name,'FORMAT_HTML');
           }

           $programmethodsoptions = array(
                'class' => 'el_programmethods',
                'multiple' => true,
                'noselectionstring' => get_string('noselection', 'local_trainingprogram'),
           );
        $mform->addElement('autocomplete','programmethod', get_string('programmethod', 'local_trainingprogram'), $programmethods, $programmethodsoptions);

        
        ////  Evalution methods Criteria

        $current_lang = current_language();
        $evaluationmethodcriteria=[];   
        $evaluationmethodcriteria['0'] = get_string('pre_exam','local_trainingprogram');
        $evaluationmethodcriteria['1'] = get_string('post_exam','local_trainingprogram');
        $evaluationmethodsoptions = array(
            'class' => 'el_programmethods',
            'multiple' => true,
            'noselectionstring' => get_string('noselection', 'local_trainingprogram'),
        );
        $mform->addElement('autocomplete','evaluationmethod', get_string('evaluationmethodcriteria', 'local_trainingprogram'),$evaluationmethodcriteria, $evaluationmethodsoptions);


        // Dynamic Evalution methods

        $current_lang = current_language();
        $dynamicevaluationmethods=$DB->get_records_sql('SELECT id,name FROM {evalution_methods}');
        $dynamic_evaluationmethods=[];   
         foreach ($dynamicevaluationmethods AS $evalution_methods_dynamic){
            $dynamic_evaluationmethods[$evalution_methods_dynamic->id]=format_text($evalution_methods_dynamic->name,'FORMAT_HTML');
        }

        $evaluationmethodsoptions1 = array(
            'class' => 'el_programmethods',
            'multiple' => true,
            'noselectionstring' => get_string('noselection', 'local_trainingprogram'),

        );
        $mform->addElement('autocomplete','dynamicevaluationmethod', get_string('evaluationmethod', 'local_trainingprogram'),$dynamic_evaluationmethods, $evaluationmethodsoptions1);

      

        //$evaluationmethod = $mform->addElement('autocomplete','evaluationmethod', get_string('evaluationmethod', 'local_trainingprogram'), dataprovider::$evaluationmethods);
        // $evaluationmethod = $mform->addElement('autocomplete','evaluationmethod', get_string('evaluationmethod', 'local_trainingprogram'), $evaluationmethods);
        // $evaluationmethod->setMultiple(true);
        // $checkboxes = array();
        // $checkboxes[] = $mform->createElement('advcheckbox', 'attendancecmpltn', null, get_string('attendancepercnt', 'local_trainingprogram'), array(),array(1));
        // $checkboxes[] = $mform->createElement('text', 'attendancepercnt','',$defaultattpercentage,array('class' => 'dynamic_form_id_attendancepercnt'));
        // $mform->addGroup($checkboxes, 'attendance', get_string('attendancecmpltn', 'local_trainingprogram'), array(' '), false);
        // $mform->disabledif('attendancepercnt', 'attendancecmpltn', 'neq',1);
       
        $mform->addElement('advcheckbox','attendancecmpltn', get_string('attendancecmpltn', 'local_trainingprogram'));
        $mform->setDefault('attendancecmpltn', 1);
 
        $mform->addElement('text','attendancepercnt', get_string('attendancepercnt', 'local_trainingprogram'));
        $mform->setDefault('attendancepercnt', 80);
        $mform->disabledif('attendancepercnt', 'attendancecmpltn', 'eq', 0);
       
        $mform->addElement('duration','duration', get_string('duration', 'local_trainingprogram'), ['units' =>[DAYSECS]]);
        $mform->addRule('duration', get_string('selectduration','local_trainingprogram'), 'required');

        $mform->addElement('duration','hour', get_string('hour', 'local_trainingprogram'), ['units' =>[HOURSECS]]);
        $mform->addRule('hour', get_string('selecthour','local_trainingprogram'), 'required');


         if($id > 0) {

            $offeringscountsql='SELECT COUNT(id) FROM {tp_offerings} WHERE trainingid = '.$id.'';
            $offeringscount = $DB->count_records_sql($offeringscountsql);

            $programrecord = $DB->get_record_sql('SELECT * FROM {local_trainingprogram} WHERE id = '.$id.'');

            if($offeringscount > 0) {

               $programtodate = userdate($programrecord->availablefrom, get_string('strftimedaydate', 'langconfig'));
               $mform->addElement('static', '', get_string('availablefrom', 'local_trainingprogram'),$programtodate);
        

                $mform->addElement('hidden', 'offeringscount', $offeringscount);
                $mform->setType('offeringscount', PARAM_INT);

                 $mform->addElement('hidden', 'availablefrom', $programrecord->availablefrom);
                $mform->setType('availablefrom', PARAM_INT);

                $mform->addElement('hidden', 'existedenddate',$programrecord->availableto);
                $mform->setType('existedenddate', PARAM_INT);
                
                // Disabling the nature section if any offerings are available
                $elem->freeze();

            } else {

                $mform->addElement('date_selector','availablefrom', get_string('availablefrom', 'local_trainingprogram'));
            }

        } else {

            $mform->addElement('date_selector','availablefrom', get_string('availablefrom', 'local_trainingprogram'));
            $mform->addRule('availablefrom', get_string('required'), 'required', null, 'server');
        }    

     

        $mform->addElement('date_selector','availableto', get_string('availableto', 'local_trainingprogram'));
        $mform->addRule('availableto', get_string('required'), 'required', null, 'server');


        $sectoroptions = array(
            'multiple' => true,
            'class' => 'el_sectorlist',
            'id' => 'program_sectors',
            'onchange' => "(function(e){ require(['local_trainingprogram/dynamic_dropdown_ajaxdata'], function(s) {s.sectorschanged();}) }) (event)",
            'noselectionstring' => get_string('noselection', 'local_trainingprogram'),
        );

        $currentlang= current_language();

        if( $currentlang == 'ar'){

            $sectors = $DB->get_records_sql_menu("SELECT id,titlearabic AS fullname FROM {local_sector} WHERE  titlearabic <> '' AND titlearabic IS NOT NULL ");

        } else {

           $sectors = $DB->get_records_sql_menu("SELECT id,title AS fullname FROM {local_sector} ORDER BY title ASC");
        }

        $sectorelement =$mform->addElement('autocomplete','sectors', get_string('sectors', 'local_trainingprogram'),$sectors, $sectoroptions);
        $sectorelement->setMultiple(true);
        $mform->addRule('sectors', get_string('missingsectors', 'local_exams'), 'required', null);

        // Jobfamilies dropdown #FA-53
       
        $jobfamiliesattributes = array(
            'ajax' => 'local_trainingprogram/sector_datasource',
            'data-type' => 'newjobfamilyoption',
            'data-sectorid' => 0,
            'noselectionstring' => get_string('noselection', 'local_trainingprogram'),
            'multiple'=>true,
        );
       // $jobfamilieselement =$mform->addElement('advcheckbox','alltargetgroup', '',[],$jobfamiliesattributes);
       // $jobfamilieselement->setMultiple(true);
        $mform->addElement('html','<div class="notapplied" id="notapplied">');
        $mform->addElement('advcheckbox', 'notappliedtargetgroup', get_string('notapplied', 'local_trainingprogram'),null,null,[0,1]);
        $mform->setType('notappliedtargetgroup', PARAM_BOOL);
        $mform->hideIf('notappliedtargetgroup', 'alltargetgroup', 'checked');
           
        $mform->addElement('html','</div>');

        $mform->addElement('html','<div class ="alltargetgroup" id="alltargetgroup">');
        $mform->addElement('advcheckbox', 'alltargetgroup', get_string('all_jobfamilies', 'local_trainingprogram'),null,null,[0,1]);
        $mform->setType('alltargetgroup', PARAM_BOOL);
        $mform->hideIf('alltargetgroup', 'notappliedtargetgroup', 'checked');
           
        $mform->addElement('html','</div>');
        // #FA-53
        
        $mform->addElement('html','<div class ="newjobfamilyoption" id="newjobfamilyoption">');

        if($id>0) {
            $sectors = $DB->get_field('local_trainingprogram','sectors',['id' => $id]);
            $sectorslist = explode(',',$sectors);
            $newjobfamilies = $DB->get_field('local_trainingprogram','newjobfamilyoption',['id' => $id]);
            if($newjobfamilies) {
                $newjobfamilies_list = explode(',',$newjobfamilies);
                foreach($sectorslist as $sectorlist) {
                    if (!in_array($sectorlist, $newjobfamilies_list)) {
                        $sec = tp::generate_newjobfamily_options($sectorlist);
                        $mform->addElement('html',$sec);
                    }
                }
            }
            $newjob_families = $DB->get_fieldset_sql(" SELECT s.id FROM {local_trainingprogram} ltp 
            JOIN {local_sector} s ON  concat(',', ltp.newjobfamilyoption, ',') LIKE concat('%,',s.id,',%') WHERE ltp.id = $id");
            if($newjob_families) {
                foreach($newjob_families as $newjobs) {
                    $newjobfamilies_element =  tp::generate_newjobfamily_options($newjobs, true);
                    $mform->addElement('html',$newjobfamilies_element);
                }
            }
        }
        $mform->addElement('html','</div>');

        $jobfamilies = array();

        $jobfamilieslist = $this->_ajaxformdata['targetgroup'];

        if (!empty($jobfamilieslist)) {

            $jobfamilieslist = is_array($jobfamilieslist)?$jobfamilieslist:array($jobfamilieslist);

            $jobfamilies = tp::trainingprogram_jobfamily(0,$jobfamilieslist,$id);

        } elseif ($id > 0) {

            $jobfamilies = tp::trainingprogram_jobfamily(0,array(),$id);
            
        }
  
        $jfdattributes = array(
            'ajax' => 'local_trainingprogram/sector_datasource',
            'data-type' => 'jobfamily',
            'data-sectorid' => 0,
            'id' => 'jobfamily_dropdown',
            'noselectionstring' => get_string('noselection', 'local_trainingprogram'),
            'multiple'=>true,

        );
        $mform->addElement('autocomplete', 'targetgroup',get_string('targetgroup', 'local_trainingprogram'),$jobfamilies, $jfdattributes);
        $mform->hideIf('targetgroup', 'alltargetgroup', 'checked');
        $mform->hideIf('targetgroup', 'notappliedtargetgroup', 'checked');

        $clevels = [];
        $clevels[''] = '';
        $clevels['level1'] =  get_string('level1','local_competency');
        $clevels['level2'] =  get_string('level2','local_competency');
        $clevels['level3'] = get_string('level3','local_competency');
        $clevels['level4'] =  get_string('level4','local_competency');
        $clevels['level5'] = get_string('level5','local_competency') ; 

        $leveloptions = [
           
            'onchange' => "(function(e){ require(['local_trainingprogram/dynamic_dropdown_ajaxdata'], function(s) {s.clevels();}) }) (event)",
            'noselectionstring' => get_string('noselection', 'local_trainingprogram'),
        ];
        
        $mform->addElement('autocomplete', 'clevels', get_string('clevels', 'local_exams'),$clevels,$leveloptions);
        $mform->setType('clevels', PARAM_ALPHANUMEXT);
        $mform->addRule('clevels', get_string('missinglevel', 'local_exams'), 'required', null);

        //competencytype
        $competencytypes = tp::constcompetency_types();

        $competencytypeoptions = [
            'ajax' => 'local_trainingprogram/dynamic_dropdown_ajaxdata',
            'data-type' => 'program_competency',
            'class' => 'el_competencytype',
            'multiple'=>true,
            'onchange' => "(function(e){ require(['local_trainingprogram/dynamic_dropdown_ajaxdata'], function(s) {s.ctype();}) }) (event)",
            'noselectionstring' => get_string('noselection', 'local_trainingprogram'),
        ];
        $mform->addElement('autocomplete', 'ctype', get_string('competency_type', 'local_trainingprogram'),$competencytypes,$competencytypeoptions);
        $mform->setType('ctype', PARAM_ALPHANUMEXT);
        $mform->addRule('ctype', get_string('missingcompetencytype', 'local_exams'), 'required', null);

        $competencies = array();
        $competencieslist = $this->_ajaxformdata['competencylevel'];
             
        if (!empty($competencieslist)) {

            $competencies = tp::trainingprogram_competencylevels($competencieslist ,$id);

        } elseif ($id > 0) {

            $competencies = tp::trainingprogram_competencylevels(array(),$id);

        }

        $clattributes = array(
            'ajax' => 'local_trainingprogram/dynamic_dropdown_ajaxdata',
            'data-type' => 'program_competencylevel',
            'class' => 'el_competencieslist',
            'data-ctype' =>'',
            'data-programid' =>1,
            'data-offeringid' =>1,
            'noselectionstring' => get_string('noselection', 'local_trainingprogram'),
        );

        $competencyelemet= $mform->addElement('autocomplete', 'competencylevel',get_string('competencies', 'local_trainingprogram'),$competencies,$clattributes);
        $competencyelemet->setMultiple(true);
        $mform->addRule('competencylevel', get_string('missingcompetencylevel', 'local_exams'), 'required', null);

        $preandpostrequirementprogramtributes = array(
          'multiple'=>true,
        );
        $current_lang = current_language();
        $programlists=$DB->get_records_sql('SELECT id,name,namearabic FROM {local_trainingprogram} WHERE published = 1');
        $pre_post_programs=[];
        foreach ($programlists AS $program){
          $pre_post_programs[$program->id]= ($current_lang == 'ar') ? $program->namearabic:$program->name;
        }
        $mform->addElement('autocomplete', 'prerequirementsprograms',get_string('prerequirementsprograms', 'local_trainingprogram'),$pre_post_programs,$preandpostrequirementprogramtributes);

        $mform->addElement('autocomplete', 'postrequirementsprograms',get_string('postrequirementsprograms', 'local_trainingprogram'),$pre_post_programs,$preandpostrequirementprogramtributes);

            //addd classification..renu
        $classification_options = [];
        $classification_options['1'] = get_string('confidentials','local_trainingprogram');
        $classification_options['2']= get_string('public','local_trainingprogram');

        
        $mform->addElement('select','classification', get_string('classification', 'local_trainingprogram'),$classification_options);
        

        $mform->addElement('advcheckbox', 'termsconditions', get_string('termsconditions', 'local_trainingprogram'), get_string('enabletandc', 'local_trainingprogram'), array('group' => 1), array(0, 1));
        $mform->addElement('editor','termsconditionsarea', get_string('termsconditionsarea', 'local_trainingprogram'));
        $mform->setType('termsconditionsarea', PARAM_RAW);
        $mform->hideIf('termsconditionsarea', 'termsconditions', 'neq', '1');
        $elinkcheckboxes = array();
        $elinkcheckboxes[] = $mform->createElement('advcheckbox', 'externallinkcheck', null, get_string('externallinkcheck', 'local_trainingprogram'), array(),array(0,1));
        $elinkcheckboxes[] = $mform->createElement('text', 'externallink','',array('class' => 'dynamic_form_id_externallink','placeholder' => get_string('placeholderlink','local_trainingprogram'),'maxlength="100" size="40"'));
        $mform->addGroup($elinkcheckboxes, 'externallink', get_string('externallinkcheck', 'local_trainingprogram'), array(' '), false);
        $mform->disabledif('externallink', 'externallinkcheck', 'neq',1);

          

    }
        /**
     * Perform some moodle validation.
     *
     * @param array $datas
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);

        if($data['cost']){
            if($data['sellingprice'] == '' || trim($data['sellingprice']) == 0){
                $errors['sellingprice'] = get_string('validsellingpricerequired', 'local_trainingprogram');
            }
            if($data['actualprice'] == '' || trim($data['actualprice']) == 0 ){
                $errors['actualprice'] = get_string('validactualpricerequired', 'local_trainingprogram');
            }
            if($data['actualprice'] > $data['sellingprice']){
                $errors['sellingprice'] = get_string('sellingpricepricehigher', 'local_trainingprogram');
            }


            if(!empty(trim($data['sellingprice'])) && $data['sellingprice'] < 0 /*!preg_match('/^[0-9]*$/',trim($data['sellingprice']))*/ ) {
                 $errors['sellingprice'] = get_string('validsellingpricerequired', 'local_trainingprogram'); 
            }
            if(!empty(trim($data['actualprice'])) &&  $data['sellingprice'] < 0 /*!preg_match('/^[0-9]*$/',trim($data['actualprice']))*/) {
                 $errors['actualprice'] = get_string('validactualpricerequired', 'local_trainingprogram'); 
            }

        }
        if($data['termsconditions']  == 1 && empty($data['termsconditionsarea']['text'])){
            $errors['termsconditionsarea'] = get_string('required');
        }

        if($data['availablefrom'] > $data['availableto']){
            $errors['availablefrom'] = get_string('fromdatelower', 'local_trainingprogram');
        }

        if($data['availableto'] < $data['availablefrom']){
            $errors['availableto'] = get_string('todatelower', 'local_trainingprogram');
        }


        // if($data['offeringscount'] <=0 && date("Y-m-d",$data['availablefrom']) < date("Y-m-d")) {
        //     $errors['availablefrom'] = get_string('availablefromcantbepastdate', 'local_trainingprogram');
        // }

        if(date("Y-m-d",$data['availableto']) < date("Y-m-d")) {
            $errors['availableto'] = get_string('availabletocantbepastdate', 'local_trainingprogram');
        }

        if($data['offeringscount'] > 0 && date("Y-m-d",$data['availableto'])  < date("Y-m-d",$data['existedenddate'])) {


            $errors['availableto'] = get_string('cannotbelowerthanexisteddate', 'local_trainingprogram',userdate($data['existedenddate'], get_string('strftimedaydate', 'langconfig')));
        }
        
    
        if($data['attendancecmpltn']){

            if(empty($data['attendancepercnt'])){

                $errors['attendancepercnt'] = get_string('reqattendancepercnt', 'local_trainingprogram');

            }elseif(!empty($data['attendancepercnt'])&& !preg_match('/^-?(\d\d?(\.\d\d?)?|100(\.00?)?)$/', $data['attendancepercnt'])) {

                $errors['attendancepercnt']  = get_string('invalidpercentageformat', 'local_trainingprogram');
            }
        }

        if($data['externallinkcheck']){

            if(empty($data['externallink'])){
                $errors['externallink'] = get_string('enterextlink', 'local_trainingprogram');
            }

            if (!preg_match('/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i', $data['externallink']) ) {
               $errors['externallink'] = get_string('enterextlinkurl', 'local_trainingprogram');
            }
        }

        if($data['duration'] == 0) {
            $errors['duration'] = get_string('duration_err_nonzero', 'local_trainingprogram');
        } 

        if($data['duration'] < 0 ){
            $errors['duration'] = get_string('requiredvalidvalue', 'local_trainingprogram');
        }

        $duration_in_days  = $data['duration'] / 86400;
        // if($duration_in_days > 15) {
        //     $errors['duration'] = get_string('duration_err_notmorethan15', 'local_trainingprogram');
        // } 
        if($data['hour'] == 0 ){
            $errors['hour'] = get_string('hour_err_nonzero', 'local_trainingprogram');
        }

        if($data['hour'] < 0 ){
            $errors['hour'] = get_string('requiredvalidvalue', 'local_trainingprogram');
        }

        // if ($data['hour'] > 82800) {
        //     $errors['hour'] = get_string('hoursrestriction', 'local_trainingprogram');
        // }

        if($data ['id'] == 0) {

            // if (date("Y-m-d", $data['availablefrom']) < date("Y-m-d", time()))  {
            //    $errors['availablefrom'] = get_string('startdterror', 'local_trainingprogram');
            // }
            if (date("Y-m-d", $data['availableto']) < date("Y-m-d", $data['availablefrom']))  {
               $errors['availableto'] = get_string('enddterror', 'local_trainingprogram');
            }
            if (date("Y-m-d", $data['availablefrom']) > date("Y-m-d", $data['availableto'])) {
               $errors['availablefrom'] = get_string('stratdateexceedenddterror', 'local_trainingprogram');
            }
        }

        if(empty($data['language'])) {
           $errors['language']= get_string('selectlanguage','local_trainingprogram');
        }
        if(empty($data['ctype'])) {
            $errors['ctype'] = get_string('ctypenotbeempty','local_exams');                
        }
        if(empty($data['competencylevel'])) {
            $errors['competencylevel'] = get_string('competenciesnotbeempty','local_exams');
        }     
        if(empty($data['sectors'])) {
            $errors['sectors'] = get_string('sectorsnotbeempty','local_exams');
        }  
       
        return $errors;
    }

    /**
     * Returns context where this form is used
     *
     * @return context
     */
    protected function get_context_for_dynamic_submission(): context {
        return context_system::instance();
    }

    /**
     * Checks if current user has access to this form, otherwise throws exception
     */
    protected function check_access_for_dynamic_submission(): void {
        // require_capability('local/trainingprogram:manage', $this->get_context_for_dynamic_submission());
        has_capability('local/trainingprogram:manage', $this->get_context_for_dynamic_submission()) 
        || has_capability('local/organization:manage_trainingofficial', $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     */
    public function process_dynamic_submission() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/user/profile/definelib.php');

        $data = $this->get_data();
        $context = context_system::instance();
        if($data->id > 0) {
            $id = (new tp)->update_program($data);
            $this->save_stored_file('image', $context->id, 'local_trainingprogram', 'trainingprogramlogo',  $data->image, '/', null, true);
            
        } else {
            $id = (new tp)->add_new($data);
            if($id){
                $this->save_stored_file('image', $context->id, 'local_trainingprogram', 'trainingprogramlogo',  $data->image, '/', null, true);
            }
        }
        

    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
        if ($id = $this->optional_param('id', 0, PARAM_INT)) {
            $data = $DB->get_record('local_trainingprogram', ['id' => $id], '*', MUST_EXIST);
            $context = context_system::instance();
            $draftitemid = file_get_submitted_draft_itemid('trainingprogramlogo');
            file_prepare_draft_area($draftitemid, $context->id, 'local_trainingprogram', 'trainingprogramlogo', $data->image, null);
            $data->image = $draftitemid;
            $data->description = ['text' => $data->description];
            $data->program_goals = ['text' => $data->program_goals];
            if($data->sellingprice > 0 && $data->actualprice > 0){
                $data->cost = 1;
            } else {
                $data->cost = 0;
            }
            $data->tax=$data->tax_free;
            $data->programmethod=$data->methods;

            if($data->targetgroup == '-1') {
                $data->alltargetgroup = 1;
                $data->targetgroup = '';
            }
            if($data->targetgroup == '0') {
                $data->notappliedtargetgroup = 1;
                $data->alltargetgroup = '';
                $data->targetgroup = '';
            }
            $data->language=$data->languages;
            $data->evaluationmethod=$data->evaluationmethods;
            $data->classification=$data->classification;
            $data->competencylevel=$data->competencyandlevels;
           
            if(!empty($data->competencyandlevels)){
            $sql = "SELECT cmt.type,cmt.type as fullname FROM {local_competencies} AS cmt WHERE cmt.id IN ($data->competencyandlevels)";
            $competencietypes=$DB->get_records_sql_menu($sql);
            $data->ctype=$competencietypes;
            }

            $data->pnature=$data->programnature;
             $data->termsconditions=$data->termsconditions;
             $data->termsconditionsarea=['text' => $data->termsconditionsarea];
             
           
            //$data->trainingmethod=$data->trainingmethods;
            
            $this->set_data($data);
        }
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $id = $this->optional_param('id', 0, PARAM_INT);
        return new moodle_url('/local/trainingprogram/index.php',
            ['action' => 'createprogram', 'id' => $id]);
    }



}
