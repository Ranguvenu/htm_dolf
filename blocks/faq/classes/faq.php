<?php
namespace block_faq;
use context_system;
use filters_form;
use csv_import_reader;
use moodle_url;
use core_text;
use stdClass;
use html_writer;
use moodle_exception;

class faq
{
     public function faqinfo()
    {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $renderer = $PAGE->get_renderer('block_faq');
        $filterparams  = $renderer->getcatalogfaqs(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-12';
        $filterparams['placeholder'] = get_string('search_faq','block_faq');
        $globalinput = $renderer->global_filter($filterparams);
        $faqdetails = $renderer->getcatalogfaqs();
        $filterparams['faqdetails'] = $faqdetails;
        $filterparams['globalinput'] = $globalinput;
        $renderer->list($filterparams);
    }

    public function getlistoffaq($stable,$filterdata) {
        global $DB, $PAGE, $OUTPUT, $CFG, $SESSION;
        
        require_once($CFG->dirroot . '/lib/classes/string_manager.php');
        $systemcontext = context_system::instance();
      
        $selectsql = "SELECT * FROM {faq} fa WHERE 1=1";
        $countsql  = "SELECT COUNT(id) FROM {faq} fa WHERE 1=1";
        $jsonarray = json_decode($filterdata);
        if (isset($jsonarray->search_query) && trim($jsonarray->search_query) != '') {
            $formsql .= " AND (fa.title LIKE :titlesearch OR fa.description LIKE :descriptionsearch) ";
            $searchparams = array('titlesearch' => '%' . trim($jsonarray->search_query) . '%', 'descriptionsearch' => '%' . trim($jsonarray->search_query) . '%');
        } else {
            $searchparams = array();
        }

        $params = array_merge($searchparams);
        $totalfaqs = $DB->count_records_sql($countsql);
        $formsql .= " ORDER BY fa.faqrank DESC";
        $totalfaqs = $DB->count_records_sql($countsql . $formsql, $params);
        $faqs = $DB->get_records_sql($selectsql . $formsql, $params, $stable->start, $stable->length);
       
        $faqlist = array();
        $count = 0;
    
           
        foreach ($faqs as $faq) {

            $faqlist[$count]["id"] = $faq->id;
            $faqlist[$count]["title"] = format_string($faq->title);
            $desc = format_string($faq->description,FORMAT_HTML);
            $faqlist[$count]["description"] = $desc;
            $faqlist[$count]["rank"] = $faq->faqrank;
            $faqlist[$count]["managefaq"] = true;
            $faqlist[$count]["actions"] = true;
            $count++;
        }
        
        $faqsContext = array(
            "hasfaqs" => $faqlist,
            "nofaqs" => $nofaqs,
            "totalfaqs" => $totalfaqs,
            "length" => count($faqlist)
        );
        return $faqsContext;
    }

    public function add_update_faq($data)
    {

        global $DB, $USER;
        if ($data->id) {
            $data->id = $data->id;
            $data->title = "{mlang en}".$data->title."{mlang}"."{mlang ar}".$data->titlearabic."{mlang}";
            $data->description =$data->description['text'];
            $data->faqrank = $data->faqrank;
            $data->usermodified = $USER->id;
            $data->timemodified = time();
            $record->id = $DB->update_record('faq', $data);
            if ($record->id) {
                return $record;
            } else {
                throw new moodle_exception('Error in Updating');
                return false;
            }
        } else {
           $data->title = "{mlang en}" . $data->title . "{mlang}" . "{mlang ar}".$data->titlearabic .'{mlang}';
            $data->description = $data->description['text'];
            $data->rank = $data->rank;
            $data->usercreated = $USER->id;
            $data->timecreated = time();
            $record->id = $DB->insert_record('faq', $data);
            if ($record->id) {
                return $record;
            } else {
                throw new moodle_exception('Error in Inserting');
                return false;
            }
        }
    }
    public function set_faq($id){
        global $DB;
        $data = $DB->get_record('faq', ['id' => $id], '*', MUST_EXIST);
        return $data;
    }

    public function faq_info($id)
    {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $data = $DB->get_record('faq', ['id' => $id], '*', MUST_EXIST);
        $str = $data->title; 
        //Setting title for enlish title field

        $language = current_language();
        if($language == 'en')
        {
            preg_match('/{mlang en}(.*?){mlang}/', $str, $match);
            $englishtitle =  $match[1];
            $data->title = $englishtitle;
        }
        if($language == 'ar')
        {
            preg_match('/{mlang ar}(.*?){mlang}/', $str, $match);
            $arabictitle =  $match[1];
            $data->title = $arabictitle;
        }
       
        $data->description = $data->description;
        $renderer = $PAGE->get_renderer('block_faq');
        $faq  = $renderer->faq_info($data);
        return $faq;
    }


    public function getlistoffaqser($stable) {
        global $DB, $PAGE, $OUTPUT, $CFG, $SESSION;

        $SESSION->lang = ($stable->isArabic == 'true') ? 'ar':'en';
        
        require_once($CFG->dirroot . '/lib/classes/string_manager.php');
        $systemcontext = context_system::instance();
      
        $selectsql = "SELECT * FROM {faq} fa WHERE 1=1";
        $countsql  = "SELECT COUNT(id) FROM {faq} fa WHERE 1=1";
        $jsonarray = json_decode($filterdata);
        if (isset($jsonarray->search_query) && trim($jsonarray->search_query) != '') {
            $formsql .= " AND (fa.title LIKE :titlesearch OR fa.description LIKE :descriptionsearch) ";
            $searchparams = array('titlesearch' => '%' . trim($jsonarray->search_query) . '%', 'descriptionsearch' => '%' . trim($jsonarray->search_query) . '%');
        } else {
            $searchparams = array();
        }

        $params = array_merge($searchparams);

        $totalfaqs = $DB->count_records_sql($countsql);
        $formsql .= " ORDER BY fa.faqrank DESC";
        $totalfaqs = $DB->count_records_sql($countsql . $formsql, $params);
        $faqs = $DB->get_records_sql($selectsql . $formsql, $params, $stable->start, $stable->length);
       
        $faqlist = array();
        $count = 0;
        foreach ($faqs as $faq) {

            $faqlist[$count]["id"] = $faq->id;
            $faqlist[$count]["questionTitle"] =format_text($faq->title,FORMAT_HTML);
            $faqlist[$count]["questionAnswer"] = format_text($faq->description,FORMAT_HTML);
            $faqlist[$count]["rank"] = $faq->faqrank;

            $count++;
        }
        
        $faqsContext = array(
            "hasfaqs" => $faqlist,
            "nofaqs" => $nofaqs,
            "totalfaqs" => $totalfaqs,
            "length" => count($faqlist)
        );
        return $faqsContext;
    }
   
   

   

    
     
    

   
}
