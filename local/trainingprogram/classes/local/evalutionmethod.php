<?php 
namespace local_trainingprogram\local;

require_once($CFG->dirroot . '/local/trainingprogram/lib.php');

class evalutionmethod { 

    public function evalutionmethodview() {
        global $PAGE;
        $renderer = $PAGE->get_renderer('local_trainingprogram');
        $filterparams  = $renderer->get_evalutionmethod(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-12';
        $filterparams['placeholder'] = get_string('searchevalutionmethods','local_trainingprogram');
        $globalinput=$renderer->global_filter($filterparams);
        $evalutionmethod = $renderer->get_evalutionmethod();
        $filterparams['evalutionmethod'] = $evalutionmethod;
        $filterparams['globalinput'] = $globalinput;
        $renderer->listofevalutionmethod($filterparams);
        
    } 

    public function get_listof_evaluation_methods($stable, $filterdata) {
        global $DB;
        $selectsql = "SELECT em.* FROM {evalution_methods} AS em "; 
        $countsql  = "SELECT COUNT(em.id) FROM {evalution_methods}  AS em";
       
        if (isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
            $formsql .= " WHERE em.name LIKE :name  ";
            $searchparams = array(

                'name' => '%'.trim($filterdata->search_query).'%',
               
            );
        } else {
            $searchparams = array();
        }
     
        $params = array_merge($searchparams);
        $totalevaluationmethod = $DB->count_records_sql($countsql.$formsql,$params);
        $formsql .=" ORDER BY em.id DESC";
        $evaluationmethod = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $evaluationmethodlist = array();
        $count = 0;
        foreach($evaluationmethod as $evaluation_method) {
            $evaluationmethodlist[$count]["id"] = $evaluation_method->id;
            $evaluationmethodlist[$count]["name"] = format_string($evaluation_method->name);

            $count++;
        }
        //print_r($evaluationmethodlist);exit;
        $coursesContext = array(
            "hascourses" => $evaluationmethodlist,
            "nocourses" => false,
            "totalevalutionmethod" => $totalevaluationmethod,
            "length" => count($evaluationmethodlist)
        );
        return $coursesContext;

        
    }

    public static function create_evaluation_methods($data) {

        global $DB, $USER;

        $data->name ="{mlang en}".$data->evaluationmethods."{mlang}"."{mlang ar}".$data->evaluationmethodsab."{mlang}";
        $data->timecreated =time();
        $data->usercreated =$USER->id;

        $DB->insert_record('evalution_methods', $data);
    }   

    public static function update_evaluation_methods($data) {
        global $DB, $USER;

        $data->name ="{mlang en}".$data->evaluationmethods."{mlang}"."{mlang ar}".$data->evaluationmethodsab."{mlang}";        $data->timemodified =time();
        $data->usermodified =$USER->id;
       
        $DB->update_record('evalution_methods', $data);
    }   
}
