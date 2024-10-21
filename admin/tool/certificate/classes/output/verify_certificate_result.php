<?php
// This file is part of the tool_certificate plugin for Moodle - http://moodle.org/
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
 * Contains class used to prepare a verification result for display.
 *
 * @package   tool_certificate
 * @copyright 2017 Mark Nelson <markn@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_certificate\output;

use html_table;
use html_table_cell;
use html_table_row;
use html_writer;
use renderable;
use templatable;
use tool_certificate\template;

/**
 * Class to prepare a verification result for display.
 *
 * @package   tool_certificate
 * @copyright 2017 Mark Nelson <markn@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class verify_certificate_result implements templatable, renderable {

    /**
     * @var string The URL to the user's profile.
     */
    public $userprofileurl;

    /**
     * @var string The user's fullname.
     */
    public $userfullname;

    /**
     * @var string The certificate's name.
     */
    public $certificatename;

    /**
     * @var string The time the issue was created.
     */
    public $timecreated;

    /**
     * @var string The timestamp the issue expires on.
     */
    public $expires;

    /**
     * @var string If issue expired based on current time.
     */
    public $expired;

    /**
     * @var string URL of issued certificate.
     */
    public $viewurl;

    /**
     * @var string HTML table of issued certificate info.
     */
    public $table;

    /**
     * Constructor.
     *
     * @param \stdClass $issue
     */
    public function __construct($issue) {

        global $DB;

        $current_lang = current_language();
        $this->viewurl = template::view_url($issue->code);
        $this->userprofileurl = new \moodle_url('/user/view.php', array('id' => $issue->userid));
        
        $username=$DB->get_record('local_users', array('userid'=>$issue->userid));

       
        if($current_lang == 'ar') {
            $this->userfullname =  $username->firstnamearabic  .' '. $username->middlenamearabic .' '. 
            $username->thirdnamearabic .' '. $username->lastnamearabic;
        } else {
            $this->userfullname =  $username->firstname  .' '. $username->middlenameen .' '. $username->thirdnameen  .' '. $username->lastname;
        }

        // $this->certificatename = $issue->certificatename;
        $lang = current_language();
        $type=$DB->get_record('tool_certificate_issues', array('code'=>$issue->code));

        switch ($type->moduletype) {

                  case "trainingprogram":
                    $certificatetype = get_string('trainingprogram','tool_certificate');
                    $offringlabel=get_string('offeringid','local_trainingprogram');
                    if( $lang == 'ar'){
                        $tpname='lt.namearabic as trainingname';
                    }else{
                        $tpname='lt.name as trainingname';
                    }
                    $sql = "SELECT $tpname FROM {local_trainingprogram} lt JOIN {tp_offerings} tpf ON tpf.trainingid = lt.id WHERE tpf.id = :tpid";
                    $entityname = $DB->get_record_sql($sql, array('tpid' =>$type->moduleid));

                    $certificatename = $entityname->trainingname;
                    break;

                  case "exams":
                    $certificatetype = get_string('exams','tool_certificate');

                    $sql = "SELECT le.exam, le.examnamearabic FROM {local_exams} le WHERE le.id = :leid";
                    $entityname = $DB->get_record_sql($sql, array('leid' => $type->moduleid));

                    $certificatename = $entityname->exam;

                    if( $lang == 'ar'){
                        $certificatename = $entityname->examnamearabic;
                    }
                    break;

                  case "events":
                    $sql = "SELECT le.title, le.titlearabic FROM {local_events} le WHERE le.id = :leid";
                    $entityname = $DB->get_record_sql($sql, array('leid' => $type->moduleid));
                    $certificatetype = get_string('events','tool_certificate');
                   $certificatename = $entityname->title;
                    if( $lang == 'ar'){
                        $certificatename = $entityname->titlearabic;
                    }
                    break;

                  case "learningtracks":
                    $certificatetype = get_string('learningtracks','tool_certificate');
                    $sql = "SELECT ll.name, ll.namearabic FROM {local_learningtracks} ll WHERE ll.id = :leid";
                    $entityname = $DB->get_record_sql($sql, array('leid' => $type->moduleid));
                    $certificatename = $entityname->name;
                    if( $lang == 'ar'){
                        $certificatename = $entityname->namearabic;
                    }

                  default:
                    echo "";

        }
        
        $this->certificatename = $certificatename;
        $this->certificatetype = $certificatetype;
        $strftimedatetime = get_string("strftimedate", "langconfig");
        $this->timecreated = userdate($issue->timecreated, $strftimedatetime);
        $this->expires = $issue->expires > 0
            ? userdate($issue->expires, $strftimedatetime)
            : get_string('never');
        $this->expired = ($issue->expires > 0) && ($issue->expires <= time());
    }

    /**
     * Function to export the renderer data in a format that is suitable for a mustache template.
     *
     * @param \renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return \stdClass|array
     */
    public function export_for_template(\renderer_base $output) {
        $result = new \stdClass();

        $table = new html_table();
        $table->attributes['class'] = 'admintable generaltable mb-2';
        $fullnamerow = new html_table_row([
            new html_table_cell(get_string('fullname')),
            new html_table_cell($this->userfullname)
        ]);
        $certificaterow = new html_table_row([
            new html_table_cell(get_string('certificatetitle', 'tool_certificate')),
            new html_table_cell($this->certificatename)
        ]);
        
        $certificatetyperow = new html_table_row([
            new html_table_cell(get_string('certificatetype', 'tool_certificate')),
            new html_table_cell($this->certificatetype)
        ]);

        $issuedrow = new html_table_row([
            new html_table_cell(get_string('issueddate', 'tool_certificate')),
            new html_table_cell($this->timecreated)
        ]);
        $expiresrow = new html_table_row([
            new html_table_cell(get_string('expirydate', 'tool_certificate')),
            new html_table_cell($this->expires)
        ]);
        $statusrow = new html_table_row([
            new html_table_cell(get_string('status')),
            new html_table_cell($this->expired ? get_string('expired', 'tool_certificate') :
                get_string('valid', 'tool_certificate'))
        ]);

        $table->data = [
            $fullnamerow,
            $certificaterow,
            $certificatetyperow,
            $issuedrow,
            $expiresrow,
            $statusrow,
        ];

        $result->table = html_writer::table($table);
        $result->expired = $this->expired;
        $result->viewurl = $this->viewurl;

        return $result;
    }
}
