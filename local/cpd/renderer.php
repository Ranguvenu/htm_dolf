<?php
/**
 * This file is part of eAbyas
 *
 * Copyright eAbyas Info Solutons Pvt Ltd, India
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
 * @author eabyas  <info@eabyas.in>
 * @package F-academy
 * @subpackage local_cpd
 */
defined('MOODLE_INTERNAL') || die;
class local_cpd_renderer extends plugin_renderer_base {

    public function get_cpdContent($evidid) {
        global $DB, $USER, $CFG, $OUTPUT;
        $sql = "SELECT ce.id as evidid, ce.status, ce.evidencetype, c.id, c.title, c.validation, c.hourscreated, c.logo, c.description, ce.id AS evidid FROM {cpd} c";
        if ((!is_siteadmin() || !has_capability('local/cpd:manage', context_system::instance()))) {
            $sql .= " JOIN {cpd_evidence} ce ON ce.cpdid = c.id AND ce.userid = $USER->id ";
        } else {
            $sql .= " JOIN {cpd_evidence} ce ON ce.cpdid = c.id";
        }
        $sql .= " WHERE c.id = :id GROUP BY ce.cpdid";
        $cpddata = $DB->get_record_sql($sql,['id' => $evidid]);
    
        $data = [];
        if ($cpddata) {
            $totalhrs = $cpddata->hourscreated;

            $approvedhrs_sql = "SELECT SUM(fe.creditedhours) AS approvhrs, 
            CASE ce.evidencetype WHEN '1' THEN 'formal' ELSE 'informal' END AS evd_type
            FROM
            {cpd_evidence} ce
                LEFT JOIN (
                SELECT id, evidenceid, creditedhours FROM {cpd_formal_evidence}
                UNION
                SELECT id, evidenceid, creditedhours FROM {cpd_informal_evidence}
                ) AS fe ON fe.evidenceid = ce.id WHERE 
                ce.status = '1' AND ce.cpdid = $evidid AND ce.userid = $USER->id ";
                $approvehrs = $DB->get_records_sql($approvedhrs_sql);
               
                $approarray = [];
                    foreach($approvehrs as $hrs) {
                        if ($hrs->approvhrs) {
                            $approarray['approarray'] = $hrs->approvhrs;
                           
                        } else {
                            $approarray['approarray'] = '0';
                        }
                    }
                    $totalapprovehrs = $approarray['approarray'];
            $pendinghrs_sql = "SELECT SUM(fe.creditedhours) AS pendinghrs,
            CASE ce.evidencetype WHEN '1' THEN 'formal' ELSE 'informal' END AS evd_type
            FROM
            {cpd_evidence} ce
                LEFT JOIN (
                SELECT id, evidenceid, creditedhours FROM {cpd_formal_evidence}
                UNION
                SELECT id, evidenceid, creditedhours FROM {cpd_informal_evidence}
                ) AS fe ON fe.evidenceid = ce.id WHERE 
                 ce.status = '0' AND ce.cpdid = $evidid AND ce.userid = $USER->id";
                $pendinghrs = $DB->get_records_sql($pendinghrs_sql);
                $pendingarray = [];
                    foreach($pendinghrs as $hrs) {
                        if ($hrs->pendinghrs) {
                            $pendingarray['pendingarray'] = $hrs->pendinghrs;
                        } else {
                            $pendingarray['pendingarray'] = '0';
                        }
                    }
                $totalpendinghrs = $pendingarray['pendingarray'];
                
            $data['totalapprovehrs'] = $totalapprovehrs;
            $data['totalpendinghrs'] =   $totalpendinghrs;
            $data['totalremaininghrs'] = $totalhrs - $totalapprovehrs;
           
            $data['title'] = $cpddata->title;
            if (!empty($cpddata->description)) {
                $description = strip_tags(html_entity_decode($cpddata->description));
            } else { 
                $description = "";
            }
            $data['description'] = $description;
            if ($cpddata->validation == 1) {
                $validation = $cpddata->validation.' '.get_string('year', 'local_cpd');
            } else {
                $validation = $cpddata->validation.' '.get_string('years', 'local_cpd');
            }
            $data['validation'] = $validation;
            $data['hourscreated'] = $cpddata->hourscreated.' '.get_string('hrrequired', 'local_cpd');
            
            $traineessql =  "SELECT COUNT(DISTINCT(ce.userid)) as ccount FROM {cpd_evidence} ce JOIN {user} u ON u.id = ce.userid WHERE ce.cpdid = :cpdid";
            $params = array('cpdid' => $cpddata->id);
            $traineecount =  $DB->count_records_sql($traineessql, $params);
            $data['traineecount'] = $traineecount;

            if ($cpddata->logo > 0) {
                $cpdimg = (new local_cpd\lib)->cpd_logo($cpddata->logo);
                if($cpdimg == false){
                   $cpdimg = $OUTPUT->image_url('eventviewnew', 'local_events');
                }
            } else {
                $cpdimg = $OUTPUT->image_url('eventviewnew', 'local_events');
            }
            $data['cpdimg'] = $cpdimg->out();
        }
        if ((is_siteadmin() || has_capability('local/cpd:manage', context_system::instance()))) {
        return $this->render_from_template('local_cpd/cpdview', $data);
        } else {
        return $this->render_from_template('local_cpd/cpd_user_view', $data);
        }
    }

    public function user_info($data) {
        global $DB, $PAGE, $OUTPUT, $CFG;
        $user = $this->render_from_template('local_cpd/userdetails', $data);
        return $user;
    }

    public function global_filter($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        echo $this->render_from_template('theme_academy/global_filter', $filterparams);
        //return true;
    }

    public function listofusers($filterparams) {
        global $DB, $PAGE, $OUTPUT;
        echo $this->render_from_template('local_cpd/listofusers', $filterparams);
    }

    public function get_catalog_cpd($filter = false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'manage_cpd_list','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName'] = 'local_cpd_view';
        $options['templateName'] = 'local_cpd/cpdlist';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'manage_cpd_list',
                'options' => $options,
                'dataoptions' => $dataoptions,
                'filterdata' => $filterdata,
        ];

        $fncardparams = $context;
            $context = $fncardparams+array(
                'createcpd'=> (has_capability('local/cpd:manage', $systemcontext) || has_capability('local/cpd:create',$systemcontext)) ? true : false,
                'contextid' => $systemcontext->id,
                'plugintype' => 'local',
                'plugin_name' =>'cpd',
                );
            return  $this->render_from_template('local_cpd/viewcpdlist', $context);
        if($filter){
            return  $context;
        }else{
            return  $this->render_from_template('theme_academy/cardPaginate', $context);
        }
    }

    public  function get_cpd_users($filter = false) {
        $systemcontext = context_system::instance();
        $evalid = optional_param('id', 0, PARAM_INT);
        $options = array('targetID' => 'manage_cpd_users','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName'] = 'local_cpd_cpd_usersview';
        $options['templateName'] = 'local_cpd/cpd_evidence_users_details';
        $options['cpdevalid'] = $evalid;
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'manage_cpd_users',
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

    public function get_catalog_evidence($filter = false) {
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'manage_evidence','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName'] = 'local_cpd_user_evidence';
        $options['templateName'] = 'local_cpd/evidencelist';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'manage_evidence',
                'options' => $options,
                'dataoptions' => $dataoptions,
                'filterdata' => $filterdata,
        ];
        $fncardparams = $context;
        $context = $fncardparams+array(
            'createevidence'=> true,
            'contextid' => $systemcontext->id,
            'plugintype' => 'local',
            'plugin_name' =>'cpd',
            );
        return  $this->render_from_template('local_cpd/viewevidencelist', $context);
        if($filter){
            return  $context;
        }else{
            return  $this->render_from_template('theme_academy/cardPaginate', $context);
        }
    }

    public function get_catalog_reportedhrs($filter = false) {
        $systemcontext = context_system::instance();
        $cpdid = optional_param('id', 0, PARAM_INT);
        $options = array('targetID' => 'manage_reported_hrs','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName'] = 'local_cpd_reported_hrs';
        $options['templateName'] = 'local_cpd/reported_hrs';
        $options['cpdid'] = $cpdid;
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'manage_reported_hrs',
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

    public function get_catalog_training_programs($filter = false) {
        $systemcontext = context_system::instance();
        $cpdid = optional_param('id', 0, PARAM_INT);
        $createprogram = false;
        if ((is_siteadmin() || has_capability('local/cpd:manage', context_system::instance()))) {
            $templatename = 'local_cpd/listof_related_programs';
            $createprogram = true;

        } else {
            $templatename = 'local_cpd/evidence_related_programs';
        }
        $options = array('targetID' => 'manage_training_prorgams','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName'] = 'local_cpd_training_programs';
        $options['templateName'] = $templatename;
        $options['cpdid'] = $cpdid;
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'manage_training_prorgams',
                'options' => $options,
                'dataoptions' => $dataoptions,
                'filterdata' => $filterdata,
        ];
        $fncardparams = $context;
            $context = $fncardparams+array(
                'createprogram' => $createprogram,
                'contextid' => $systemcontext->id,
                'plugintype' => 'local',
                'plugin_name' =>'cpd',
                'cpdid' => $cpdid );
            return  $this->render_from_template('local_cpd/viewtrainingprograms', $context);
        if($filter){
            return  $context;
        } else {
            return  $this->render_from_template('theme_academy/cardPaginate', $context);
        }
    }

    public function listof_related_programs() {
        global $DB, $PAGE, $OUTPUT;
        echo $this->render_from_template('local_cpd/listof_related_programs', '');
    }

    public function action_btn() {
        global $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        if (has_capability('local/cpd:create', $systemcontext)) {
            $header_btns = $this->render_from_template('local_cpd/form', null);
            $actionbtns = $PAGE->add_header_action($header_btns);            
            return true;
        } else {
            return false;
        }
    }
}
