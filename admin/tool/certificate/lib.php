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
 * Customcert module core interaction API
 *
 * @package    tool_certificate
 * @copyright  2013 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Serves certificate issues and other files.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool|null false if file not found, does not return anything if found - just send the file
 */
function tool_certificate_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    global $CFG, $DB;

    require_once($CFG->libdir . '/filelib.php');

    // We are positioning the elements.
    if ($filearea === 'image') {
        if (!\tool_certificate\permission::can_manage_anywhere()) {
            // Shared images are only displayed to the users during editing of a template.
            return false;
        }

        $relativepath = implode('/', $args);
        $fullpath = '/' . $context->id . '/tool_certificate/image/' . $relativepath;

        $fs = get_file_storage();
        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
            return false;
        }

        send_stored_file($file, 0, 0, $forcedownload);
    }

    // Elements can use several fileareas defined in tool_certificate.
    if ($filearea === 'element' || $filearea === 'elementaux') {
        $elementid = array_shift($args);
        $template = \tool_certificate\template::find_by_element_id($elementid);
        $template->require_can_manage();

        $filename = array_pop($args);
        if (!$args) {
            $filepath = '/';
        } else {
            $filepath = '/' . implode('/', $args) . '/';
        }
        $fs = get_file_storage();
        $file = $fs->get_file($context->id, 'tool_certificate', $filearea, $elementid, $filepath, $filename);
        if (!$file) {
            return false;
        }
        send_stored_file($file, null, 0, $forcedownload, $options);
    }

    // Issues filearea.
    if ($filearea === 'issues') {
        $filename = array_pop($args); // File name is actually the certificate code.
        $code = pathinfo($filename, PATHINFO_FILENAME);

        $issue = $DB->get_record('tool_certificate_issues', ['code' => $code], '*', MUST_EXIST);
        $template = \tool_certificate\template::instance($issue->templateid);
        if (!\tool_certificate\permission::can_view_issue($template, $issue) && !\tool_certificate\permission::can_verify()) {
            return false;
        }

        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'tool_certificate', $filearea, $issue->id, '', false);
        if (!$file = reset($files)) {
            return false;
        }
        send_stored_file($file, null, 0, $forcedownload, $options);
    }

    return false;
}

/**
 * Add nodes to myprofile page.
 *
 * @param \core_user\output\myprofile\tree $tree Tree object
 * @param stdClass $user user object
 * @param bool $iscurrentuser
 * @param stdClass $course Course object
 * @return bool
 */
function tool_certificate_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    global $USER;
    if (\tool_certificate\permission::can_view_list($user->id)) {
        if ($USER->id == $user->id) {
            $link = get_string('mycertificates', 'tool_certificate');
        } else {
            $link = get_string('certificates', 'tool_certificate');
        }
        $url = new moodle_url('/admin/tool/certificate/my.php', $iscurrentuser ? [] : ['userid' => $user->id]);
        $node = new core_user\output\myprofile\node('miscellaneous', 'toolcertificatemy', $link, null, $url);
        $tree->add_node($node);
    }
}

/**
 * Handles editing the 'name' of the element in a list.
 *
 * @param string $itemtype
 * @param int $itemid
 * @param string $newvalue
 * @return \core\output\inplace_editable
 */
function tool_certificate_inplace_editable($itemtype, $itemid, $newvalue) {
    $newvalue = clean_param($newvalue, PARAM_TEXT);
    external_api::validate_context(context_system::instance());

    if ($itemtype === 'elementname') {
        // Validate access.
        $element = \tool_certificate\element::instance($itemid);
        $element->get_template()->require_can_manage();
        $element->save((object)['name' => $newvalue]);
        return $element->get_inplace_editable();
    }

    if ($itemtype === 'templatename') {
        // Validate access.
        $template = \tool_certificate\template::instance($itemid);
        $template->require_can_manage();
        $template->save((object)['name' => $newvalue]);
        return $template->get_editable_name();
    }
}

/**
 * Get icon mapping for font-awesome.
 */
function tool_certificate_get_fontawesome_icon_map() {
    return [
        'tool_certificate:download' => 'fa-download',
        'tool_certificate:linkedin' => 'fa-linkedin-square'
    ];
}

/**
 * Display the Certificate link in the course administration menu.
 *
 * @param settings_navigation $navigation The settings navigation object
 * @param stdClass $course The course
 * @param context $context Course context
 */
function tool_certificate_extend_navigation_course($navigation, $course, $context) {
    if (\tool_certificate\permission::can_view_templates_in_context($context)) {
        $certificatenode = $navigation->add(get_string('certificates', 'tool_certificate'),
            null, navigation_node::TYPE_CONTAINER, null, 'tool_certificate');
        $url = new moodle_url('/admin/tool/certificate/manage_templates.php', ['courseid' => $course->id]);
        $certificatenode->add(get_string('managetemplates', 'tool_certificate'), $url, navigation_node::TYPE_SETTING,
            null, 'tool_certificate');
    }
}

/**
 * Hook called to check if template delete is permitted when deleting category.
 *
 * @param \core_course_category $category The category record.
 * @return bool
 */
function tool_certificate_can_course_category_delete(\core_course_category $category): bool {
    // Deletion requires certificates to be present and permission to manage them.
    $certificatescount = \tool_certificate\certificate::count_templates_in_category($category);
    return !$certificatescount || \tool_certificate\permission::can_manage($category->get_context());
}

/**
 * Hook called to check if template move is permitted when deleting category.
 *
 * @param \core_course_category $category The category record.
 * @param \core_course_category $newcategory The new category record.
 * @return bool
 */
function tool_certificate_can_course_category_delete_move(\core_course_category $category,
        \core_course_category $newcategory): bool {
    // Deletion with move requires certificates to move to be present and
    // permission to manage them at destination category.
    $certificatescount = \tool_certificate\certificate::count_templates_in_category($category);
    return !$certificatescount || (\tool_certificate\permission::can_manage($category->get_context())
        && \tool_certificate\permission::can_manage($newcategory->get_context()));
}

/**
 * Hook called to add information that is displayed on category deletion form.
 *
 * @param \core_course_category $category The category record.
 * @return string
 */
function tool_certificate_get_course_category_contents(\core_course_category $category): string {
    if (\tool_certificate\certificate::count_templates_in_category($category)) {
        return get_string('certificatetemplates', 'tool_certificate');
    }
    return '';
}

/**
 * Hook called before we delete a category.
 * Deletes all the templates in the category.
 *
 * @param \stdClass $category The category record.
 */
function tool_certificate_pre_course_category_delete(\stdClass $category): void {
    $context = context_coursecat::instance($category->id);
    $templates = \tool_certificate\persistent\template::get_records(['contextid' => $context->id]);
    foreach ($templates as $template) {
        \tool_certificate\template::instance(0, $template->to_record())
            ->delete();
    }
}

/**
 * Hook called before we delete a category.
 * Moves all the templates in the deleted category to the new category.
 *
 * @param \core_course_category $category The category record.
 * @param \core_course_category $newcategory The new category record.
 */
function tool_certificate_pre_course_category_delete_move(\core_course_category $category,
          \core_course_category $newcategory): void {
    $context = $category->get_context();
    $newcontext = $newcategory->get_context();
    $templates = \tool_certificate\persistent\template::get_records(['contextid' => $context->id]);
    foreach ($templates as $template) {
        \tool_certificate\template::instance(0, $template->to_record())
            ->move_files_to_new_context($newcontext->id);

        $template->set('contextid', $newcontext->id)->update();
    }
}

    function certificates_details($tablelimits, $filtervalues){
        global $DB, $PAGE,$USER,$CFG,$OUTPUT, $SESSION;
        $systemcontext = \context_system::instance();
        $countsql = "SELECT count(tci.id)
                FROM {tool_certificate_issues} AS tci";
    
        $selectsql = "SELECT tci.* FROM {tool_certificate_issues} tci ";
        $formsql = " JOIN {local_users} lu ON lu.userid = tci.userid ";
        if ($filtervalues->programs) {
            $formsql .= " JOIN {tp_offerings} tpof ON tpof.id = tci.moduleid ";
        }
        if ($filtervalues->exams && empty($filtervalues->result)) {
            $formsql .= " JOIN {local_exams} ex ON ex.id = tci.moduleid ";
        }
        if (isset($filtervalues->result) && trim($filtervalues->result) != '') {
            $formsql .= " JOIN {exam_enrollments} en ON en.examid = tci.moduleid AND en.userid = tci.userid 
                    JOIN {local_exam_profiles} ep ON ep.id = en.profileid 
                    JOIN {grade_items} gi ON gi.iteminstance = ep.quizid 
                    JOIN {grade_grades} gg ON gg.userid = en.userid AND gg.itemid=gi.id ";
        }
        $formsql .= " WHERE 1=1 ";

        $searchparams = array();
        if  (isset($filtervalues->search_query) && trim($filtervalues->search_query) != ''){
            $formsql .= " AND ((tci.code LIKE :codesearch) OR (lu.email LIKE :codesearch2))";
            $searchparams = array('codesearch' => '%'.trim($filtervalues->search_query).'%', 'codesearch2' => '%'.trim($filtervalues->search_query).'%');
        }
        if  (isset($filtervalues->entitytype) && trim($filtervalues->entitytype) != ''){

            $formsql .= " AND tci.moduletype = '$filtervalues->entitytype' ";
        }
        if  (isset($filtervalues->username) && trim($filtervalues->username) != ''){

            $formsql .= " AND lu.userid IN ($filtervalues->username) ";
        }
        if  (isset($filtervalues->email) && trim($filtervalues->email) != ''){

            $formsql .= " AND lu.id IN ($filtervalues->email) ";
        }
        if  (isset($filtervalues->programs) && trim($filtervalues->programs) != ''){

            $formsql .= " AND tp.id IN ($filtervalues->programs) ";
        }
        if  (isset($filtervalues->exams) && trim($filtervalues->exams) != ''){

            $formsql .= " AND ex.id IN ($filtervalues->exams) ";
        }
        if  (isset($filtervalues->usersidnumber) && trim($filtervalues->usersidnumber) != ''){

            $formsql .= " AND lu.id IN ($filtervalues->usersidnumber) ";
        }
        if (isset($filtervalues->result) && trim($filtervalues->result) != '') {
            if($filtervalues->result == 'pass'){
                $formsql .= " AND tci.moduletype = 'exams' AND gg.finalgrade >= ep.passinggrade";
            }else{
                $formsql .= " AND tci.moduletype = 'exams' AND ((gg.finalgrade < ep.passinggrade) OR gg.finalgrade IS NULL)";
            }
        }
        $count = $DB->count_records_sql($countsql.$formsql, $searchparams);
        $concatsql=" order by tci.id desc";
        $certicifates_info = $DB->get_records_sql($selectsql.$formsql.$concatsql, $searchparams, $tablelimits->start, $tablelimits->length);

        $list=array();
        $data = array();
        $lang = current_language();
        if ($certicifates_info) {
            foreach ($certicifates_info as $each_certicifate) {
                $userdata = $DB->get_record('local_users', array('userid'=>$each_certicifate->userid));
                $list['moduletype'] = $each_certicifate->moduletype;

                switch ($each_certicifate->moduletype) {
                  case "trainingprogram":
                    $list['moduletype'] = get_string('trainingprogram','tool_certificate');
                    $offringlabel=get_string('offeringid','local_trainingprogram');
                    if( $lang == 'ar'){
                        $tpname='CONCAT(lt.namearabic," </br> ('.$offringlabel.' ",tpf.code,")") as trainingname';
                    }else{
                        $tpname='CONCAT(lt.name," </br> ('.$offringlabel.' ",tpf.code,")") as trainingname';
                    }
                    $sql = "SELECT $tpname FROM {local_trainingprogram} lt JOIN {tp_offerings} tpf ON tpf.trainingid = lt.id WHERE tpf.id = :tpid";
                    $entityname = $DB->get_record_sql($sql, array('tpid' => $each_certicifate->moduleid));

                    $list['entityname'] = $entityname->trainingname;
                    // if( $lang == 'ar'){
                    //     $list['entityname'] = $entityname->namearabic;
                    // }
                    break;
                  case "exams":
                    $list['moduletype'] = get_string('exams','tool_certificate');
                    $sql = "SELECT le.exam, le.examnamearabic FROM {local_exams} le WHERE le.id = :leid";
                    $entityname = $DB->get_record_sql($sql, array('leid' => $each_certicifate->moduleid));
                    $list['entityname'] = $entityname->exam;
                    if( $lang == 'ar'){
                        $list['entityname'] = $entityname->examnamearabic;
                    }
                    break;
                  case "events":
                    $sql = "SELECT le.title, le.titlearabic FROM {local_events} le WHERE le.id = :leid";
                    $entityname = $DB->get_record_sql($sql, array('leid' => $each_certicifate->moduleid));
                    $list['moduletype'] = get_string('events','tool_certificate');
                    $list['entityname'] = $entityname->title;
                    if( $lang == 'ar'){
                        $list['entityname'] = $entityname->titlearabic;
                    }
                    break;
                  case "learningtracks":
                    $list['moduletype'] = get_string('learningtracks','tool_certificate');
                    $sql = "SELECT ll.name, ll.namearabic FROM {local_learningtracks} ll WHERE ll.id = :leid";
                    $entityname = $DB->get_record_sql($sql, array('leid' => $each_certicifate->moduleid));
                    $list['entityname'] = $entityname->name;
                    if( $lang == 'ar'){
                        $list['entityname'] = $entityname->namearabic;
                    }
                  default:
                    echo "";
                }
                $list['contextid'] = $systemcontext->id;
                $list['useremail'] = $userdata->email;
                $list['code'] = $each_certicifate->code;
                $list['exp_date'] = '--';
                $list['issueddate'] =  userdate(($each_certicifate->timecreated), get_string('strftimedatetime','core_langconfig'));
                if($each_certicifate->expires){
                    $list['exp_date'] = userdate(($each_certicifate->expires), get_string('strftimedatetime','core_langconfig'));
                }
                $list['username'] = $userdata->firstname.' '.$userdata->middlenameen.' '.$userdata->thirdnameen.' '.$userdata->lastname;
                if( $lang == 'ar'){
                    $list['username'] = $userdata->firstnamearabic.' '.$userdata->middlenamearabic.' '.$userdata->thirdnamearabic.' '.$userdata->lastnamearabic;
                }
                $list['view_certificate'] = $CFG->wwwroot.'/admin/tool/certificate/view.php?code='.$each_certicifate->code;
                $list['viewcodeurl'] = $CFG->wwwroot.'/admin/tool/certificate/index.php?code='.$each_certicifate->code;
                $list['id'] = $each_certicifate->id;
               $data[] = $list;
            }
        }
        return array('count' => $count, 'data' => $data); 
    }

function tool_certificates_filters_form($filterparams){
    global $CFG;
    require_once($CFG->dirroot . '/local/organization/dynamicfilters_form.php');
    $filters = array(
        'certificate'=>array('tool'=>array('entitytype','usersname', 'usersemail','result','usersidnumber')),
    );
    $mform = new dynamicfilters_form(null, array('filterlist'=>$filters,'filterparams' => $filterparams,'submitid' =>'viewcerticifates','ajaxformsubmit'=>true), 'post', '', null, true,$_REQUEST);
    return $mform;
}

function entitytype_filter($mform){
    global $DB,$USER;
    $systemcontext = context_system::instance();
    $entitytype = [];
    $entitytype[''] = get_string('select');
    $entitytype['trainingprogram'] = get_string('trainingprogram','tool_certificate');
    $entitytype['exams'] = get_string('exams','tool_certificate');
    $entitytype['events'] = get_string('events','tool_certificate');
    $entitytype['learningtracks'] = get_string('learningtracks','tool_certificate');
    $options = array(
        'multiple' => false,
        'noselectionstring' => get_string('entitytype', 'tool_certificate'),
    );
    $statuselement = $mform->addElement('autocomplete', 'entitytype', get_string('entitytype', 'tool_certificate'),$entitytype, ['id' => 'entitytype']);
     $statuselement->setMultiple(false);
}

function usersname_filter($mform){
    global $DB,$USER;
    $systemcontext = context_system::instance();
    $userattributes = array(
        'ajax' => 'local_organization/organization_datasource',
        'data-type' => 'orgusers',
        'id' => 'orgusers',
        'data-org' => 'listofusers',
        'multiple'=>true,
    );
    $mform->addElement('autocomplete', 'username', get_string('username', 'local_userapproval'),[], $userattributes);
}

function usersemail_filter($mform){
    global $DB,$USER;
    $systemcontext = context_system::instance();
    $userattributes = array(
        'ajax' => 'local_organization/organization_datasource',
        'data-type' => 'usersemail',
        'id' => 'usersemail',
        'data-org' => 'listofusers',
        'multiple'=>true,
    );
    $mform->addElement('autocomplete', 'email', get_string('email', 'local_userapproval'),[], $userattributes);
}

function usersidnumber_filter($mform){
    global $DB,$USER;
    $systemcontext = context_system::instance();
    $userattributes = array(
        'ajax' => 'local_organization/organization_datasource',
        'data-type' => 'usersidnumber',
        'id' => 'usersidnumber',
        'data-org' => 'listofusers',
        'multiple'=>true,
    );
    $mform->addElement('autocomplete', 'usersidnumber', get_string('idnumber', 'local_userapproval'),[], $userattributes);
}

function result_filter($mform){
    $result = [];
    $result[''] = '';
    $result['pass'] = get_string('pass','tool_certificate');
    $result['fail'] = get_string('fail','tool_certificate');

    $mform->addElement('autocomplete','result', get_string('result', 'tool_certificate'),$result);
}
function tool_certificate_leftmenunode() {
    global $DB, $USER;
    $systemcontext = context_system::instance();
    $tool_certificates = '';
    if(is_siteadmin() || has_capability('local/organization:assessment_operator_view',$systemcontext)){
        $tool_certificates .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_events', 'class'=>'pull-left user_nav_div certificates'));

                    $referral_url = new moodle_url('/admin/tool/certificate/view_certificates.php');
                    $referral_label = get_string('certificates','tool_certificate');
                    $referral =  html_writer::link($referral_url, '<span class="side_menu_img_icon events_icon"></span><span class="user_navigation_link_text">'.$referral_label.'</span>', array('class'=>'user_navigation_link'));
                    $tool_certificates .= $referral;

        $tool_certificates .= html_writer::end_tag('li');
    }
    return array('6' => $tool_certificates);
}

