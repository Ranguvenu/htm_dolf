<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * LearnerScript Reports
 * A Moodle block for creating customizable reports
 * @package blocks
 * @author: Jahnavi Nanduri
 * @date: 2022
 */
namespace block_learnerscript\lsreports;
use block_learnerscript\local\pluginbase;
use block_learnerscript\local\ls;

class plugin_eventfields extends pluginbase {

    public function init() {
        $this->fullname = get_string('eventfields', 'block_learnerscript');
        $this->type = 'advanced';
        $this->form = true;
        $this->reporttypes = array('events');
    }

    public function summary($data) {
        return format_string($data->columname);
    }

    public function colformat($data) {
        $align = (isset($data->align)) ? $data->align : '';
        $size = (isset($data->size)) ? $data->size : '';
        $wrap = (isset($data->wrap)) ? $data->wrap : '';
        return array($align, $size, $wrap);
    }

    public function execute($data, $row, $user, $courseid, $starttime = 0, $endtime = 0) {
        global $DB;
        $event = $DB->get_record('local_events', ['id'=> $row->id]);
        switch ($data->column) {
            case 'startdate':
            case 'enddate':
            case 'registrationstart':
            case 'registrationend':
            case 'timecreated':
            case 'timemodified':
                $row->{$data->column} = !empty($row->{$data->column}) ? userdate($row->{$data->column}, '%d %b %Y') : '--';
            break;
            case 'type':
                if ($row->type == 3) {
                    $row->type = get_string('wrokshop','local_events');
                }
                if ($row->type == 1) {
                    $row->type = get_string('forum','local_events');
                } else if ($row->type == 2) {
                    $row->type = get_string('conference','local_events');
                } else if ($row->type == 3) {
                    $row->type = get_string('wrokshop','local_events');
                } else if ($row->type == 4) {
                    $row->type = get_string('cermony','local_events');
                } else {
                    $row->type = get_string('symposium','local_events');

                }
                $row->{$data->column} = !empty($row->{$data->column}) ? $row->{$data->column} : '--';
            break;
            case 'language':
                $languages = explode(',', $row->{$data->column});
                foreach ($languages as $key => $value) {
                    if ($value == 1) {
                        $langlist[] = get_string('arabic', 'local_events');
                    } else {
                        $langlist[] = get_string('english', 'local_events');
                    }
                }
                $row->{$data->column} = implode(',', $langlist);
                $row->{$data->column} = !empty($row->{$data->column}) ? $row->{$data->column} : '--';
            break;
            case 'audiencegender': 
                $gender = explode(',', $row->{$data->column});
                foreach ($gender as $key => $value) {
                    if ($value == 1) {
                        $list[] = get_string('male', 'local_events');
                    } else {
                        $list[] = get_string('female', 'local_events');
                    }
                }
                $row->{$data->column} = implode(',', $list);
                $row->{$data->column} = !empty($row->{$data->column}) ? $row->{$data->column} : '--';
            break;
            case 'status':
                $current_date = time();
                $event_endttime = ($event->enddate+$event->slot+$event->eventduration);

                $statusarray = array(0 => get_string('active', 'local_events'),
                    1 => get_string('inactive', 'local_events'),
                    2 => get_string('cancelled', 'local_events'),
                    3 => get_string('closed', 'local_events'),
                    4 => get_string('archieved', 'local_events'));
                $row->{$data->column} = ($row->{$data->column} == 0) ? (($event_endttime >= $current_date) ?  $statusarray[0] : $statusarray[1]) : $statusarray[$event->status];
                $row->{$data->column} = !empty($row->{$data->column}) ? $row->{$data->column} : '--';
            break;
            case 'certificate':
                $certificateid = $row->{$data->column};
                if (!empty($certificateid)) {
                    $certname = $DB->get_field_sql("SELECT name FROM {tool_certificate_templates} WHERE id = $certificateid");
                } else {
                    $certname = '';
                }
                $row->{$data->column} = !empty($certname) ? $certname : '--';
            break;
            case 'requiredapproval':
            case 'sendemailtopreregister':
                if ($row->{$data->column} == 1) {
                    $row->{$data->column} = get_string('yes', 'local_events');
                } else {
                    $row->{$data->column} = get_string('no', 'local_events');
                }
                $row->{$data->column} = !empty($row->{$data->column}) ? $row->{$data->column} : '--';
            break;
            case 'eventmanager': 
                $eventmanagerids = $row->{$data->column};
                if (!empty($eventmanagerids)) {
                    $userids = $DB->get_records_sql("SELECT u.id, CONCAT(u.firstname,'',u.lastname) AS fullname FROM {user} u WHERE u.id IN ($eventmanagerids)");
                    foreach ($userids as $userid) {
                        $userlist[] = $userid->fullname;
                    }
                    $users = implode(',', $userlist);
                } else {
                    $users = ' ';
                }
                $row->{$data->column} = !empty($users) ? $users : '--';
            break;
            case 'method':
                if ($row->{$data->column} == 1) {
                    $row->{$data->column} = get_string('virtual', 'local_events');
                } else {
                    $row->{$data->column} = get_string('onsite', 'local_events');
                }
                $row->{$data->column} = !empty($row->{$data->column}) ? $row->{$data->column} : '--';
            break;
            case 'halladdress':
                $hallid = $row->{$data->column};
                if (!empty($hallid)) {
                    $hall = $DB->get_field_sql("SELECT name FROM {hall} WHERE id = $hallid"); 
                } else {
                    $hall = "";
                }
                $row->{$data->column} = !empty($hall) ? $hall : '--';
            break;
            case 'eventduration':
            case 'slot':
                $row->{$data->column} = !empty($row->{$data->column}) ? (new ls)->strTime($row->{$data->column}) : '--';
            break;
            case 'virtualtype':
                if ($row->{$data->column} == 1) {
                    $row->{$data->column} = get_string('zoom', 'local_events');
                } else if ($row->{$data->column} == 2) {
                    $row->{$data->column} = get_string('webex', 'local_events');
                }
                $row->{$data->column} = !empty($row->{$data->column}) ? $row->{$data->column} : '--';
            break;
            case 'zoom':
                $zoomid = $row->{$data->column};
                if (!empty($zoomid)) {
                    $zoom = $DB->get_field_sql("SELECT name FROM {zoom} WHERE id = $zoomid"); 
                } else {
                    $zoom = ' ';
                }
                $row->{$data->column} = !empty($zoom) ? $zoom : '--';
            break;
            case 'webex':
                $webexid = $row->{$data->column};
                if (!empty($webexid)) {
                    $webex = $DB->get_field_sql("SELECT name FROM {webexactivity} WHERE id = $webexid"); 
                } else {
                    $webex = ' ';
                }
                $row->{$data->column} = !empty($webex) ? $webex : '--';
            break;

        }
        return (isset($row->{$data->column}))? $row->{$data->column} : ' -- ';
    }
}
