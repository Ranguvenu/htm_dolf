<?php 
namespace local_trainingprogram\local;
require_once($CFG->dirroot . '/local/trainingprogram/lib.php');

class refundsettings { 

    public const PERCENTAGE=0;
    public const AMOUNT=1;

    public function refundsettingsview() {
        global $PAGE;
        $renderer = $PAGE->get_renderer('local_trainingprogram');
        $filterparams  = $renderer->get_refundsettings(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-12';
        $filterparams['placeholder'] = get_string('searchrefundsettings','local_trainingprogram');
        $globalinput=$renderer->global_filter($filterparams);
        $refundsettings = $renderer->get_refundsettings();
        $fform = trainingprogram_refundsettings_filters_form($filterparams);
        $filterparams['refundsettings'] = $refundsettings;
        $filterparams['filterform'] = $fform->render();
        $filterparams['globalinput'] = $globalinput;
        $renderer->listofrefundsettings($filterparams);
        
    } 

    public function get_listof_refundsettings($stable, $filterdata) {
        global $DB;
        $selectsql = "SELECT rs.*
        FROM {refund_settings} AS rs "; 
        $countsql  = "SELECT COUNT(rs.id)
        FROM {refund_settings}  AS rs";
        $formsql = ' WHERE 1=1 ';
        if (isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
            $formsql .= " AND (rs.type LIKE :typesearch OR rs.entitytype LIKE :entitytypesearch )  ";
            $searchparams = array(

                'typesearch' => '%'.trim($filterdata->search_query).'%',
                'entitytypesearch' => '%'.trim($filterdata->search_query).'%',
            );
        } else {
            $searchparams = array();
        }
        if (!empty($filterdata->type)){ 
            $types = explode(',',$filterdata->type);
             if(!empty($types)){
                $typesquery = array();
                foreach ($types as $type) {
                    $typesquery[] = " CONCAT(',',rs.type,',') LIKE CONCAT('%,','$type',',%') "; 
                }
                $typesqueryparams =implode('OR',$typesquery);
                $formsql .= ' AND ('.$typesqueryparams.') ';
            }
        } 
        if (!empty($filterdata->entitytype)){ 
            $entitytypes = explode(',',$filterdata->entitytype);
             if(!empty($entitytypes)){
                $entitytypequery = array();
                foreach ($entitytypes as $entitytype) {
                    $entitytypequery[] = " CONCAT(',',rs.entitytype,',') LIKE CONCAT('%,','$entitytype',',%') "; 
                }
                $entitytypequeryparams =implode('OR',$entitytypequery);
                $formsql .= ' AND ('.$entitytypequeryparams.') ';
            }
        } 
        if (!empty($filterdata->dedtype)){ 
            $dedtypes = explode(',',$filterdata->dedtype);
             if(!empty($dedtypes)){
                $dedtypesquery = array();
                foreach ($dedtypes as $dedtype) {

                    $dedtypesquery[] =($dedtype == -1) ? " rs.dedtype = 0 " :  " rs.dedtype = 1 "; 
                }
                $dedtypesqueryparams =implode('OR',$dedtypesquery);
                $formsql .= ' AND ('.$dedtypesqueryparams.') ';
            }
        } 
        $params = array_merge($searchparams);
        $totalrefundsettings = $DB->count_records_sql($countsql.$formsql,$params);
        $formsql .=" ORDER BY rs.id DESC";
        $refundsettings = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $refundsettingslist = array();
        $count = 0;
        foreach($refundsettings as $refundsetting) {
            $refundsettingslist[$count]["id"] = $refundsetting->id;
            $refundsettingslist[$count]["type"] = ($refundsetting->type =='cancel') ?  get_string('cancel_offering', 'local_trainingprogram') :  get_string($refundsetting->type, 'local_trainingprogram');
            $refundsettingslist[$count]["entitytype"] =get_string($refundsetting->entitytype, 'local_trainingprogram');
            $refundsettingslist[$count]["dayfrom"] =$refundsetting->dayfrom;
            $refundsettingslist[$count]["dayto"] =$refundsetting->dayto;
            $refundsettingslist[$count]["dedtype"] = ($refundsetting->dedtype == 0) ? get_string('attendancepercnt', 'local_trainingprogram') : get_string('amount', 'local_trainingprogram');
            $refundsettingslist[$count]["dedvalue"] =($refundsetting->dedtype == 0) ?  $refundsetting->dedpercentage.'%': $refundsetting->dedamount;
            if($refundsetting->entitytype == 'exam' && $refundsetting->type =='reschedule') {
                if ($refundsetting->ownedbycisi == 1) {
                    $ownedby = get_string('cisi', 'local_trainingprogram');
                    if ($refundsetting->moreattempts == 1) {
                        $moreattempts = get_string('morethanfirst', 'local_trainingprogram');
                    } else {
                        $moreattempts = get_string('firstattempt', 'local_trainingprogram');
                    }
                } else {
                    $ownedby = get_string('fa', 'local_trainingprogram');
                    $moreattempts = '--';
                }
            } else {
                $ownedby = '--';
                $moreattempts = '--';
            }
            $refundsettingslist[$count]["ownedby"]= $ownedby;
            $refundsettingslist[$count]["moreattempts"]= $moreattempts;
            $refundsettingslist[$count]["action"]=  true;
            $count++;
        }
        $coursesContext = array(
            "hascourses" => $refundsettingslist,
            "nocourses" => false,
            "totalrefundsettings" => $totalrefundsettings,
            "length" => count($refundsettingslist)
        );
        return $coursesContext;
    }

    public static function create_setting($data) {
        global $DB, $USER;
        $data->timecreated =time();
        $data->usercreated =$USER->id;
        $data->dedpercentage =($data->dedtype == 0) ? $data->dedpercentage : null;
        $data->dedamount =($data->dedtype == 1) ? $data->dedamount : null;
        $data->di =$USER->id;
        $data->attemptstatus = (int)$data->attemptstatus;
        
        $DB->insert_record('refund_settings', $data);
    }   

    public static function update_setting($data) {
        global $DB, $USER;
        $data->timemodified =time();
        $data->usermodified =$USER->id;
        $data->dedpercentage =($data->dedtype == 0) ? $data->dedpercentage : null;
        $data->dedamount =($data->dedtype == 1) ? $data->dedamount : null;
        $DB->update_record('refund_settings', $data);
    }   
}
