<?php
require_once($CFG->dirroot . '/local/hall/lib.php');

class local_hall_renderer extends plugin_renderer_base {
    public function get_catalog_halls($filter = false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'manage_halls','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_halls_view';
        $options['templateName']='local_hall/halldetails';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'manage_halls',
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
    public function get_catalog_entitysearch($filter = false)
    {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'entity_search','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_entity_search';
        $options['templateName']='local_hall/entitydetails';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'entity_search',
                'options' => $options,
                'dataoptions' => $dataoptions,
                'filterdata' => $filterdata,
        ];
        if ($filter){
            return  $context;
        } else{
            return  $this->render_from_template('theme_academy/cardPaginate', $context);
        }
    }

    public function get_catalog_reservations($filter = false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'manage_reservations','perPage' => 10, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_reservation_view';
        $options['templateName']='local_hall/reservations';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'manage_reservations',
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
    public function get_catalog_schedule($filter = false) {
        $examid = optional_param('id', 0, PARAM_INT);
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'manage_schedule','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_schedule_view';
        $options['templateName']='local_hall/schedule';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('examid' => $examid));
        $context = [
                'targetID' => 'manage_schedule',
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
    public function get_catalog_hallinfo($filter = false, $hallid=false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'manage_hallinfo','perPage' => 10, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_hallinfo_view';
        $options['templateName']='local_hall/hallinfo';
        $options = json_encode($options);
        $filterdata = json_encode(array('hallid' => $hallid));
        $dataoptions = json_encode(array('hallid' => $hallid));
        $context = [
                'targetID' => 'manage_hallinfo',
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
    public function action_btn($filterview = true) {
        global $DB, $PAGE, $OUTPUT;

        $systemcontext = context_system::instance();

            $data = [
                'actionview' => (is_siteadmin() ||  has_capability('local/organization:manage_hall_manager', $systemcontext) || has_capability('local/organization:manage_event_manager', $systemcontext)) ?  true :  false,
                'actionpermission' => (is_siteadmin() ||  has_capability('local/organization:manage_hall_manager', $systemcontext)) ?  true :  false,
                'filterview' => $filterview
            ];
            $header_btns = $this->render_from_template('local_hall/hallform', $data);
            $PAGE->add_header_action($header_btns);            
            return true;
        
    }
    public function global_filter($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        return $this->render_from_template('theme_academy/global_filter', $filterparams);
    }
    public function listofhalls($filterparams) {
        global $DB, $PAGE, $OUTPUT;

        $filterparams['actionview'] = (is_siteadmin() ||  has_capability('local/organization:manage_hall_manager', $filterparams['systemcontext']) || has_capability('local/organization:manage_event_manager', $filterparams['systemcontext'])) ?  true :  false;

         $filterparams['actionpermission'] = (is_siteadmin() ||  has_capability('local/organization:manage_hall_manager', $filterparams['systemcontext'])) ?  true :  false;
         $filterparams['filterview'] = true;
       
        echo  $this->render_from_template('local_hall/listofhalls', $filterparams);
    }
    public function schedulehalls($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        $hallsmform = schedulehall_filters_form($filterparams);
        $filterparams['filterform'] = $hallsmform->render();
        echo $this->render_from_template('local_hall/schedule', $filterparams);
    }
    public function hallsinfo($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        $hallsmform = hallsinfo_filters_form($filterparams);
        $filterparams['filterform'] = $hallsmform->render();
        echo $this->render_from_template('local_hall/schedule', $filterparams);
    }
    public function schedulehallsdetails($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        $hallsmform = hallsinfo_filters_form($filterparams);
        $filterparams['filterform'] = $hallsmform->render();
        echo $this->render_from_template('local_hall/scheduledetails', $filterparams);
    }    
    public function hall_info($data) {
        global $DB, $PAGE, $OUTPUT, $CFG;
        $hall = $this->render_from_template('local_hall/hall_info', $data);
        return $hall;
    }
    public function hall_data($data) {
        global $DB, $PAGE, $OUTPUT, $CFG;
        $hall = $this->render_from_template('local_hall/hallreservation', ['hallinfo' => $data]);
        return $hall;
    }
    public function get_catalog_hallreservations($hallid = false, $halldate = false, $examid=false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'manage_hallreservations','perPage' => 10, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_hallreservation_view';
        $options['templateName']='local_hall/scheduledetails';
        $options = json_encode($options);
        $filterdata = json_encode(array('hallid' => $hallid, 'halldate' => $halldate, 'examid' => $examid));
        $dataoptions = json_encode(array());
        $context = [
                'targetID' => 'manage_hallreservations',
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

    //Vinod- Hall fake block for communication officer - Starts//

    public function all_halls_block($filter = false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'manage_hall_block','perPage' => 3, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_hall_manage_halls_block';
        $options['templateName']='local_hall/halls_block';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'manage_hall_block',
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
    public function listofhalls_block_data($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        return $this->render_from_template('local_hall/listofhalls_block_data', $filterparams);
    }
    //Vinod- Hall fake block for communication officer - Ends//
    public function entityreservations_renderer($reservatons) 
    {
        global $DB, $PAGE, $OUTPUT;
        return $this->render_from_template('local_hall/entityreservations', ['reservatons' => $reservatons]);
    }

    public function listofentities($filterparams)
    {   
        echo  $this->render_from_template('local_hall/listofhalls', $filterparams);
    }

    public function get_catalog_schedulehalls($filter = false,$hallid=false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'manage_halls','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_halls_schedulehallsview';
        $options['templateName']='local_hall/schedulehall';

        $options = json_encode($options);
        $filterdata = json_encode(array('hallid' => $hallid));
        $dataoptions = json_encode(array('hallid' => $hallid,'contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'manage_halls',
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

        public function get_catalog_schedulehallsdetails($filter = false,$hallid=false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'manage_halls','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName']='local_halls_schedulehallsdetailsview';
        $options['templateName']='local_hall/schedulehalldetails';

        $options = json_encode($options);
        $filterdata = json_encode(array('hallid' => $hallid));
        $dataoptions = json_encode(array('hallid' => $hallid,'contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'manage_halls',
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
    

     public function listofschedulehalls($filterparams) {
        global $DB, $PAGE, $OUTPUT;
       
        echo  $this->render_from_template('local_hall/listofhallsschedule', $filterparams);
    }

}
