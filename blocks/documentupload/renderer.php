<?php

use block_documentupload\documentupload;

class block_documentupload_renderer extends plugin_renderer_base {
    public function getcatalogdocumentupload($filter = false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'manage_documentupload','perPage' => 10, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='block_documentupload';
        $options['templateName']='block_documentupload/documentuploaddetails';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'manage_documentupload',
                'options' => $options,
                'dataoptions' => $dataoptions, 
                'filterdata' => $filterdata,
        ];
        
        if($filter){
            return  $context;
        }else{
            return  $this->render_from_template('theme_academy/cardPaginate', $context);
        }
        
    }

     public function global_filter($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        return $this->render_from_template('theme_academy/global_filter', $filterparams);
    }

    public function list($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        $filterparams['createaction'] = is_siteadmin() ? true : false;
        echo $this->render_from_template('block_documentupload/list', $filterparams);
    }


     public function documentupload_info($data) {
        global $DB, $PAGE, $OUTPUT, $CFG;
        require_once($CFG->dirroot . '/blocks/documentupload/lib.php');
       /* if(!empty($data->document_url)) {
            $data->document=document_url($data->document_url);
        }
        $data->title = $data->title;
        print_r($data);exit;*/
        $documentupload = $this->render_from_template('block_documentupload/documentupload_info', $data);
        return $documentupload;
    }

}