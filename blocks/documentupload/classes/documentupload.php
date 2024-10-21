<?php

namespace block_documentupload;

use context_system;
use filters_form;
use csv_import_reader;
use moodle_url;
use core_text;
use stdClass;
use html_writer;
use moodle_exception;

class documentupload
{
     public function documentuploadinfo()
    {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $renderer = $PAGE->get_renderer('block_documentupload');
        $filterparams  = $renderer->getcatalogdocumentupload(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-12';
        $filterparams['placeholder'] = get_string('search_documentupload','block_documentupload');
        $globalinput = $renderer->global_filter($filterparams);
        $documentuploaddetails = $renderer->getcatalogdocumentupload();
        $filterparams['documentuploaddetails'] = $documentuploaddetails;
        $filterparams['globalinput'] = $globalinput;
        $renderer->list($filterparams);
    }

    public function getlistofdocumentupload($stable,$filterdata) {
        global $DB, $PAGE, $OUTPUT, $CFG,$SESSION;       
        $lang = current_language();
        require_once($CFG->dirroot . '/blocks/documentupload/lib.php');
        $systemcontext = context_system::instance();
        $selectsql = "SELECT * FROM {documentupload} du WHERE 1=1";
        $countsql  = "SELECT COUNT(id) FROM {documentupload} du WHERE 1=1";
        $jsonarray = json_decode($filterdata);
        if (isset($jsonarray->search_query) && trim($jsonarray->search_query) != '') {
            $formsql .= " AND (du.title LIKE :titlesearch) ";
            $searchparams = array('titlesearch' => '%' . trim($jsonarray->search_query) . '%');
        } else {
            $searchparams = array();
        }

        $params = array_merge($searchparams);
        $totaldocumentupload = $DB->count_records_sql($countsql);
        $formsql .= " ORDER BY du.id DESC";
        $totaldocumentupload = $DB->count_records_sql($countsql . $formsql, $params);
        $documentupload = $DB->get_records_sql($selectsql . $formsql, $params, $stable->start, $stable->length);
        $uploadlist = array();
        $count = 0;
        foreach ($documentupload as $uploaddata) {
            
            $uploadlist[$count]["id"] = $uploaddata->id;
            $uploadlist[$count]["title"] = format_string($uploaddata->title);
            //$uploadlist[$count]["document"] = document_path($uploaddata->document);
            if($uploaddata->mediatype == "1")
            {
               if($lang=="en")
               {
                    $uploadlist[$count]["document"] = document_path($uploaddata->document);
               }
               else
               {
                    $uploadlist[$count]["document"] = document_path($uploaddata->arabicdocument);
               }
                
            }
            else
            {
                
                if($lang=="en")
                {
                    $uploadlist[$count]["document"] = document_path($uploaddata->video);
                }
                else
                {
                    $uploadlist[$count]["document"] = document_path($uploaddata->videoar);
                }
            }

           
            
            $uploadlist[$count]["mediatype"] = $uploaddata->mediatype;
            $uploadlist[$count]["managedocumentupload"] = true;
            $uploadlist[$count]["actions"] = true;
            $count++;
        }
        $documentuploadContext = array(
            "hasdocumentupload" => $uploadlist,
            "nodocumentupload" => $nodocumentupload,
            "totaldocumentupload" => $totaldocumentupload,
            "length" => count($uploadlist)
        );
        return $documentuploadContext;
    }



    public function add_update_documentupload($data)
    {

        global $DB, $USER;
        $systemcontext = context_system::instance();
        if (isset($data->document)) {
            $data->document = $data->document;
           file_save_draft_area_files($data->document, $systemcontext->id, 'block_documentupload', 'documentupload', $data->document);
        }
        if (isset($data->arabicdocument)) {
            $data->arabicdocument = $data->arabicdocument;
           file_save_draft_area_files($data->arabicdocument, $systemcontext->id, 'block_documentupload', 'documentupload', $data->arabicdocument);
        }


        if(isset($data->video))
        {
            $data->video = $data->video;
           file_save_draft_area_files($data->video, $systemcontext->id, 'block_documentupload', 'documentupload', $data->video);
        }
        if(isset($data->videoar))
        {
            $data->videoar = $data->videoar;
           file_save_draft_area_files($data->videoar, $systemcontext->id, 'block_documentupload', 'documentupload', $data->videoar);
        }
        if ($data->id) {
            $data->id = $data->id;
            $data->usermodified =  $USER->id;
            $data->timemodified = time();
            $data->title = "{mlang en}".$data->title."{mlang}"."{mlang ar}".$data->titlearabic."{mlang}";
            if($data->media==1)
            {
               $data->document = $data->document;
               $data->arabicdocument = $data->arabicdocument;
            }
            else
            {
                $data->video = $data->video;
                $data->videoar = $data->videoar;
            }
            $data->docrank = $data->docrank;
            $data->mediatype = $data->media;
            $data->description = $data->description['text'];;
            $record->id = $DB->update_record('documentupload', $data);
            if ($record->id) {
                return $record;
            } else {
                throw new moodle_exception('Error in Updating');
                return false;
            }
        } else {
            $data->timecreated = time();
            $data->title = "{mlang en}".$data->title."{mlang}"."{mlang ar}".$data->titlearabic."{mlang}";
            if($data->media==1)
            {
               
                $data->document = $data->document;
                $data->arabicdocument = $data->arabicdocument;
               
            }
            else
            {
                $data->video = $data->video;
                $data->videoar = $data->videoar;
            }
            $data->docrank = $data->docrank;
            $data->mediatype = $data->media;
            $data->description = $data->description['text'];
            $record->id = $DB->insert_record('documentupload', $data);
            if ($record->id) {
                return $record;
            } else {
                throw new moodle_exception('Error in Inserting');
                return false;
            }
        }
    }
    public function set_documentupload($id){
        global $DB;
        $data = $DB->get_record('documentupload', ['id' => $id], '*', MUST_EXIST);
        $row['id'] = $data->id;
        $row['title'] = $data->title;
        $row['docrank'] = $data->docrank;
        $draftitemid = file_get_submitted_draft_itemid('document');
        file_prepare_draft_area($draftitemid, $systemcontext->id, 'block_documentupload', 'document', $data->document, null);
        $row['document']  = $draftitemid;
       
        return $row;
    }

    public function documentupload_info($id)
    {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $data = $DB->get_record('documentupload', ['id' => $id], '*', MUST_EXIST);
        $renderer = $PAGE->get_renderer('block_documentupload');
        $documentupload  = $renderer->documentupload_info($data);
        return $documentupload;
    }


    public function getlistofdocumentuploadser($stable) {
        global $DB, $PAGE, $OUTPUT, $CFG,$SESSION;
        require_once($CFG->dirroot . '/blocks/documentupload/lib.php');
        $SESSION->lang = ($stable->isArabic== "true") ? 'ar' : 'en';
        $systemcontext = context_system::instance();
        $selectsql = "SELECT * FROM {documentupload} du WHERE 1=1";
        $countsql  = "SELECT COUNT(id) FROM {documentupload} du WHERE 1=1";
        $jsonarray = json_decode($filterdata);
        if (isset($stable->mediaType) && trim($stable->mediaType) != '') {
            $formsql = " AND du.mediatype = $stable->mediaType ";
        }
        $totaldocumentupload = $DB->count_records_sql($countsql);
        $sortorder = " ORDER BY du.id DESC";
        $totaldocumentupload = $DB->count_records_sql($countsql . $formsql, $params);
        $documentupload = $DB->get_records_sql($selectsql .$formsql .$sortorder, $stable->start, $stable->length);
        $uploadlist = array();
        $count = 0;
        foreach ($documentupload as $uploaddata) {
            $uploadlist[$count]["id"] = $uploaddata->id;
            $uploadlist[$count]["title"] = format_string($uploaddata->title);
            if($uploaddata->mediatype==1)
            {
                if($SESSION->lang=="en")
                {
                    $uploadlist[$count]["videoUrl"] = ($uploaddata->document !=null ) ? document_path($uploaddata->document): "";
                }
                else
                {
                    $uploadlist[$count]["videoUrl"] = ($uploaddata->arabicdocument !=null ) ? document_path($uploaddata->arabicdocument): "";
                }
            }
            else
            {
                if($SESSION->lang=="en")
                {
                    $uploadlist[$count]["videoUrl"] = ($uploaddata->video !=null ) ? document_path($uploaddata->video): "";
                }
                else
                {
                    $uploadlist[$count]["videoUrl"] = ($uploaddata->videoar !=null ) ? document_path($uploaddata->videoar): "";
                }
            }



            //$uploadlist[$count]["videoUrl"] = document_path($uploaddata->document);
            $desc = format_string($uploaddata->description,FORMAT_HTML);
            $uploadlist[$count]["thumbnailUrl"] = "";
            $uploadlist[$count]["rank"] = $uploaddata->docrank;
            $uploadlist[$count]["isYoutube"] = false;
            $uploadlist[$count]["managedocumentupload"] = true;
            $uploadlist[$count]["actions"] = true;
            $count++;
        }
       
        return $uploadlist;
    }

   
   

   

    
     
    

   
}
