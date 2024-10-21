<?php

namespace local_organization;

use context_system;
use filters_form;
use stdClass;

class partnertypes
{
    public function partnertypesinfo()
    {
        global $DB, $PAGE;
        $systemcontext = context_system::instance();
        $renderer = $PAGE->get_renderer('local_organization');
        $filterparams  = $renderer->organization_partnertypes(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['widthclass'] = 'col-md-12';
        $filterparams['placeholder'] = get_string('serchpartnertypes','local_organization');
        $globalinput=$renderer->global_filter($filterparams);
        $earlyregistration = $renderer->organization_partnertypes();
        $filterparams['details'] = $earlyregistration;
        $filterparams['globalinput'] = $globalinput;
        $renderer->list($filterparams);
    }

    public function getlistofpartnertypes($stable,$filterdata)
    {
        global $DB;
        $lang = current_language();
        $systemcontext = context_system::instance();
      
        $selectsql = "SELECT * FROM {local_org_partnertypes} pt WHERE 1=1";
        $countsql  = "SELECT COUNT(id) FROM {local_org_partnertypes} pt WHERE 1=1";

        if (isset($filterdata->search_query) && trim($filterdata->search_query) != '') {
            $formsql .= " AND (pt.name LIKE :namesearch OR pt.arabicname LIKE :descriptionsearch) ";
            $searchparams = array('namesearch' => '%' . trim($filterdata->search_query) . '%', 'descriptionsearch' => '%' . trim($filterdata->search_query) . '%');
        } else {
            $searchparams = array();
        }

        $params = array_merge($searchparams);

        $totalpartnertypes = $DB->count_records_sql($countsql.$formsql, $params);
        $formsql .= " ORDER BY pt.id DESC";
        $partnertypes = $DB->get_records_sql($selectsql.$formsql, $params, $stable->start, $stable->length);
       
        $list = $this->partnertypes($partnertypes);
        
        $typesContext = array(
            "hastypes" => $list,
            "notypes" => $notypes,
            "totaltypes" => $totalpartnertypes,
            "length" => count($list)
        );
        return $typesContext;
    }

    public function partnertypes($partnertypes, $isarabic=false)
    {
        global $SESSION, $CFG;

        require_once($CFG->dirroot . '/local/organization/lib.php');

        $list = array();
        $count = 0;

        $SESSION->lang =($isarabic == 'true') ? 'ar':'en';

        foreach ($partnertypes as $type) { 

            $list[$count]["id"] = $type->id;

            if ($SESSION->lang == 'ar') {
                $list[$count]["Name"] = $type->arabicname;
            } else {
                $list[$count]["Name"] = $type->name;
            }
            $list[$count]["description"] = strip_tags(format_text($type->description,FORMAT_HTML));
            $list[$count]["Desc"] = strip_tags(format_text($type->description,FORMAT_HTML));
            $list[$count]["AttachmentId"] = (!empty($type->partnerimage)) ? partnerlogo_url($type->partnerimage) : null;

            $count++;
        }

        return $list;
    }

    public function get_partnertypes($isarabic=false)
    {
        global $DB;
        $records = $DB->get_records_sql("SELECT * FROM {local_org_partnertypes}");
        $partners = $this->partnertypes($records, $isarabic);

        return $partners;
    }

    public function get_partners($isarabic=false, $id=false)
    {
        global $DB, $SESSION,$CFG;
        require_once($CFG->dirroot . '/local/organization/lib.php');
        $SESSION->lang = ($isarabic == 'true') ?'ar':'en';
        $sql = "SELECT * FROM {local_organization} WHERE status = 2 AND partnertype IS NOT NULL ";
        $concatsql = '';
        if (!empty($id)) {
            $concatsql = " AND partnertype =". $id;
        }
        $records = $DB->get_records_sql($sql.$concatsql);
        $data = [];
        foreach($records as $record) {
            $row = [];
            $row['id'] = $record->id;
            if ($SESSION->lang == 'ar') {
                $row['Name'] = $record->fullnameinarabic;
                $row['PartnerTypeName'] = $DB->get_field('local_org_partnertypes', 'arabicname', ['id' => $record->partnertype]);
            } else {
                $row['Name'] = $record->fullname;
                $row['PartnerTypeName'] = $DB->get_field('local_org_partnertypes', 'name', ['id' => $record->partnertype]);
            }
            $row['Rank'] = $record->orgrank;
            $row['Desc'] = strip_tags(format_text($record->description, FORMAT_HTML));
            $row['AttachmentId'] = logo_path($record->orglogo);
            $row['PartnerTypeId'] = $record->partnertype;
            $data[] = $row;
        }

        return $data;
    }


    public function partnertypes_info($id)
    {
        global $DB, $PAGE;
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $renderer = $PAGE->get_renderer('local_organization');
        $data = $DB->get_record('local_org_partnertypes', ['id' => $id], '*', MUST_EXIST);
        $types  = $renderer->partnertypes_info($data);
        return $types;
    }
}
