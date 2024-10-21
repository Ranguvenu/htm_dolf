<?php

use block_faq\faq;

class block_faq_renderer extends plugin_renderer_base {
    public function getcatalogfaqs($filter = false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'manage_faqs','perPage' => 10, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='block_faq';
        $options['templateName']='block_faq/faqdetails';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'manage_faqs',
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
        echo $this->render_from_template('block_faq/list', $filterparams);
    }


     public function faq_info($data) {
        global $DB, $PAGE, $OUTPUT, $CFG;
        
         $data->title = $data->title;
        $data->description = format_text($data->description,FORMAT_HTML);
        $data->rank=$data->faqrank;
        $faq = $this->render_from_template('block_faq/faq_info', $data);
        return $faq;
    }

}