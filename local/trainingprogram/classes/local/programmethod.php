<?php 
namespace local_trainingprogram\local;

require_once($CFG->dirroot . '/local/trainingprogram/lib.php');

class programmethod { 

    public function programmethodview() {
        global $PAGE;
        $renderer = $PAGE->get_renderer('local_trainingprogram');
        $filterparams  = $renderer->get_programmethod(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-12';
        $filterparams['placeholder'] = get_string('searchprogrammethods','local_trainingprogram');
        $globalinput=$renderer->global_filter($filterparams);
        $programmethod = $renderer->get_programmethod();
        $filterparams['programmethod'] = $programmethod;
        $filterparams['globalinput'] = $globalinput;
        $renderer->listofprogrammethod($filterparams);
        
    } 

    public function get_listof_program_methods($stable, $filterdata) {
        global $DB;
        $selectsql = "SELECT pm.* FROM {program_methods} AS pm "; 
        $countsql  = "SELECT COUNT(pm.id) FROM {program_methods}  AS pm";
       
        if (isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
            $formsql .= " WHERE pm.name LIKE :name  ";
            $searchparams = array(

                'name' => '%'.trim($filterdata->search_query).'%',
               
            );
        } else {
            $searchparams = array();
        }
     
        $params = array_merge($searchparams);
        $totalprogrammethod = $DB->count_records_sql($countsql.$formsql,$params);
        $formsql .=" ORDER BY pm.id DESC";
        $programmethod = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start,$stable->length);
        $programmethodlist = array();
        $count = 0;
        foreach($programmethod as $program_method) {
            $programmethodlist[$count]["id"] = $program_method->id;
            $programmethodlist[$count]["name"] = format_string($program_method->name);
            $programmethodlist[$count]["action"]=  true;
            $count++;
        }

        $coursesContext = array(
            "hascourses" => $programmethodlist,
            "nocourses" => false,
            "totalprogrammethod" => $totalprogrammethod,
            "length" => count($programmethodlist)
        );
        return $coursesContext;

        
    }

    public static function create_program_methods($data) {
        global $DB, $USER;
       
        $data->name ="{mlang en}".$data->programmethods."{mlang}"."{mlang ar}".$data->programmethodsab."{mlang}";
        $data->timecreated =time();
        $data->usercreated =$USER->id;

        $DB->insert_record('program_methods', $data);
    }   

    public static function update_program_methods($data) {
        global $DB, $USER;

        $data->name ="{mlang en}".$data->programmethods."{mlang}"."{mlang ar}".$data->programmethodsab."{mlang}";
        $data->timemodified =time();
        $data->usermodified =$USER->id;
       
        $DB->update_record('program_methods', $data);
    }   
}
