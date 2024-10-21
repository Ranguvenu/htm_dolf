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

namespace theme_academy\output;


use moodle_url;
use html_writer;
use get_string;

use context_system;
use core_component;

defined('MOODLE_INTERNAL') || die;

/**
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package    theme_academy
 * @copyright  2012 Bas Brands, www.basbrands.nl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_renderer extends \core_renderer {

    
    public function edit_button(moodle_url $url) {
        if ($this->page->theme->haseditswitch) {
            return;
        }
        $url->param('sesskey', sesskey());
        if ($this->page->user_is_editing()) {
            $url->param('edit', 'off');
            $editstring = get_string('turneditingoff');
        } else {
            $url->param('edit', 'on');
            $editstring = get_string('turneditingon');
        }
        $button = new \single_button($url, $editstring, 'post', ['class' => 'btn btn-primary']);
        return $this->render_single_button($button);
    }

    /**
     * Renders the "breadcrumb" for all pages in academy.
     *
     * @return string the HTML for the navbar.
     */
    public function navbar(): string {
        $newnav = new \theme_academy\academynavbar($this->page);
        return $this->render_from_template('core/navbar', $newnav);
    }

    /**
     * Renders the context header for the page.
     *
     * @param array $headerinfo Heading information.
     * @param int $headinglevel What 'h' level to make the heading.
     * @return string A rendered context header.
     */
    public function context_header($headerinfo = null, $headinglevel = 1): string {
        global $DB, $USER, $CFG, $SITE,$PAGE;
        require_once($CFG->dirroot . '/user/lib.php');
        $context = $this->page->context;
        $heading = null;
        $imagedata = null;
        $subheader = null;
        $userbuttons = null;

        // Make sure to use the heading if it has been set.
        if (isset($headerinfo['heading'])) {
            $heading = $headerinfo['heading'];
        } else {
            $heading = $this->page->heading;
        }

        // The user context currently has images and buttons. Other contexts may follow.
        if ((isset($headerinfo['user']) || $context->contextlevel == CONTEXT_USER) && $this->page->pagetype !== 'my-index') {
            if (isset($headerinfo['user'])) {
                $user = $headerinfo['user'];
            } else {
                // Look up the user information if it is not supplied.
                $user = $DB->get_record('user', array('id' => $context->instanceid));
            }

            // If the user context is set, then use that for capability checks.
            if (isset($headerinfo['usercontext'])) {
                $context = $headerinfo['usercontext'];
            }

            // Only provide user information if the user is the current user, or a user which the current user can view.
            // When checking user_can_view_profile(), either:
            // If the page context is course, check the course context (from the page object) or;
            // If page context is NOT course, then check across all courses.
            $course = ($this->page->context->contextlevel == CONTEXT_COURSE) ? $this->page->course : null;

            if (user_can_view_profile($user, $course)) {
                // Use the user's full name if the heading isn't set.
                if (empty($heading)) {
                    $heading = fullname($user);
                }

                $imagedata = $this->user_picture($user, array('size' => 100));

                // Check to see if we should be displaying a message button.
                if (!empty($CFG->messaging) && has_capability('moodle/site:sendmessage', $context)) {
                    $userbuttons = array(
                        'messages' => array(
                            'buttontype' => 'message',
                            'title' => get_string('message', 'message'),
                            'url' => new moodle_url('/message/index.php', array('id' => $user->id)),
                            'image' => 'message',
                            'linkattributes' => \core_message\helper::messageuser_link_params($user->id),
                            'page' => $this->page
                        )
                    );

                    if ($USER->id != $user->id) {
                        $iscontact = \core_message\api::is_contact($USER->id, $user->id);
                        $contacttitle = $iscontact ? 'removefromyourcontacts' : 'addtoyourcontacts';
                        $contacturlaction = $iscontact ? 'removecontact' : 'addcontact';
                        $contactimage = $iscontact ? 'removecontact' : 'addcontact';
                        $userbuttons['togglecontact'] = array(
                                'buttontype' => 'togglecontact',
                                'title' => get_string($contacttitle, 'message'),
                                'url' => new moodle_url('/message/index.php', array(
                                        'user1' => $USER->id,
                                        'user2' => $user->id,
                                        $contacturlaction => $user->id,
                                        'sesskey' => sesskey())
                                ),
                                'image' => $contactimage,
                                'linkattributes' => \core_message\helper::togglecontact_link_params($user, $iscontact),
                                'page' => $this->page
                            );
                    }

                    $this->page->requires->string_for_js('changesmadereallygoaway', 'moodle');
                }
            } else {
                $heading = null;
            }
        }

        $current_lang = current_language();
        $is_trainingprogram =  $DB->record_exists_sql('SELECT id FROM {local_trainingprogram} WHERE courseid = '.$this->page->course->id.'');
        $prefix = null;
        if ($context->contextlevel == CONTEXT_MODULE) {
            if ($this->page->course->format === 'singleactivity') {
                //$heading = $this->page->course->fullname;
                if($current_lang == 'ar' && $is_trainingprogram) {

                    $heading = $DB->get_field_sql('SELECT namearabic FROM {local_trainingprogram} WHERE courseid = '.$this->page->course->id.'');
                } else {

                    $heading = $this->page->course->fullname;
                }
            } else {
                //$heading = $this->page->cm->get_formatted_name();
                if($current_lang == 'ar' && $is_trainingprogram) {

                    $heading = $DB->get_field_sql('SELECT namearabic FROM {local_trainingprogram} WHERE courseid = '.$this->page->course->id.'');
                } else {
                    $heading = $this->page->cm->get_formatted_name();
                }
                $imagedata = $this->pix_icon('monologo', '', $this->page->activityname, ['class' => 'activityicon']);
                $purposeclass = plugin_supports('mod', $this->page->activityname, FEATURE_MOD_PURPOSE);
                $purposeclass .= ' activityiconcontainer';
                $purposeclass .= ' modicon_' . $this->page->activityname;
                $imagedata = html_writer::tag('div', $imagedata, ['class' => $purposeclass]);
                $prefix = get_string('modulename', $this->page->activityname);
            }
           
        }


        $contextheader = new \context_header($heading, $headinglevel, $imagedata, $userbuttons, $prefix);

        return $this->render_context_header($contextheader);
    }

     /**
      * Renders the header bar.
      *
      * @param context_header $contextheader Header bar object.
      * @return string HTML for the header bar.
      */
    protected function render_context_header(\context_header $contextheader) {
        global $USER;
        // Generate the heading first and before everything else as we might have to do an early return.
        if (!isset($contextheader->heading)) {
            $heading = $this->heading($this->page->heading, $contextheader->headinglevel, 'h2');
        } else {
            $heading = $this->heading($contextheader->heading, $contextheader->headinglevel, 'h2');
        }

        // All the html stuff goes here.
        $html = html_writer::start_div('page-context-header');

        // Image data.
        if (isset($contextheader->imagedata)) {
            // Header specific image.
            $html .= html_writer::div($contextheader->imagedata, 'page-header-image mr-2');
        }

        // Headings.
        if (isset($contextheader->prefix)) {
            $prefix = html_writer::div($contextheader->prefix, 'text-muted text-uppercase small line-height-3');
            $heading = $prefix . $heading;
        }
        $html .= html_writer::tag('div', $heading, array('class' => 'page-header-headings'));
        if (isloggedin()) {
            $trainerid = $USER->id;
            $roleobj = (new \local_exams\local\exams())->get_user_role($USER->id);
            if ($roleobj->shortname == 'editingtrainer') {
                $this->add_restrictions_to_course_update_options($trainerid, $roleobj);
            }
            // Check if current user is Training official
            $is_training_official = $this->if_user_has_role($USER->id, 'to');
            if ($is_training_official) {
                $this->add_restrictions_to_course_update_options($trainerid, $is_training_official);
            }

        }
        // Buttons.
        if (isset($contextheader->additionalbuttons)) {
            $html .= html_writer::start_div('btn-group header-button-group');
            foreach ($contextheader->additionalbuttons as $button) {
                if (!isset($button->page)) {
                    // Include js for messaging.
                    if ($button['buttontype'] === 'togglecontact') {
                        \core_message\helper::togglecontact_requirejs();
                    }
                    if ($button['buttontype'] === 'message') {
                        \core_message\helper::messageuser_requirejs();
                    }
                    $image = $this->pix_icon($button['formattedimage'], $button['title'], 'moodle', array(
                        'class' => 'iconsmall',
                        'role' => 'presentation'
                    ));
                    $image .= html_writer::span($button['title'], 'header-button-title');
                } else {
                    $image = html_writer::empty_tag('img', array(
                        'src' => $button['formattedimage'],
                        'role' => 'presentation'
                    ));
                }
                $html .= html_writer::link($button['url'], html_writer::tag('span', $image), $button['linkattributes']);
            }
            $html .= html_writer::end_div();
        }
        $html .= html_writer::end_div();
        return $html;
    }

    /**
     * See if this is the first view of the current cm in the session if it has fake blocks.
     *
     * (We track up to 100 cms so as not to overflow the session.)
     * This is done for drawer regions containing fake blocks so we can show blocks automatically.
     *
     * @return boolean true if the page has fakeblocks and this is the first visit.
     */
    public function firstview_fakeblocks(): bool {
        global $SESSION;

        $firstview = false;
        if ($this->page->cm) {
            if (!$this->page->blocks->region_has_fakeblocks('side-pre')) {
                return false;
            }
            if (!property_exists($SESSION, 'firstview_fakeblocks')) {
                $SESSION->firstview_fakeblocks = [];
            }
            if (array_key_exists($this->page->cm->id, $SESSION->firstview_fakeblocks)) {
                $firstview = false;
            } else {
                $SESSION->firstview_fakeblocks[$this->page->cm->id] = true;
                $firstview = true;
                if (count($SESSION->firstview_fakeblocks) > 100) {
                    array_shift($SESSION->firstview_fakeblocks);
                }
            }
        }
        return $firstview;
    }

    /**
    * Added by Rizwana from lib/outputrenderers.php for calling custom logo function
    *
    * Whether we should display the main logo.
    * @deprecated since Moodle 4.0
    * @todo final deprecation. To be removed in Moodle 4.4 MDL-73165.
    * @param int $headinglevel The heading level we want to check against.
    * @return bool
    */
    public function should_display_main_logo($headinglevel = 1) {
        // debugging('should_display_main_logo() is deprecated and will be removed in Moodle 4.4.', DEBUG_DEVELOPER);
        // Only render the logo if we're on the front page or login page and the we have a logo.
        $logo = $this->get_custom_logo();
        if($headinglevel == 1 && !empty($logo)){
        return true;
        }
        
        
        
        //---------> commented for we dont need of compact logo by Rizwana
        // if ($headinglevel == 1 && !empty($logo)) {
        // if ($this->page->pagelayout == 'frontpage' || $this->page->pagelayout == 'login') {
        // return true;
        // }
        // }
        
        
        
        return false;
    }

    /**
     * Added by Rizwana from lib/outputrenderers.php for calling custom logo function
     * Whether we should display the logo in the navbar.
     *
     * We will when there are no main logos, and we have compact logo.
     *
     * @return bool
     */
    public function should_display_navbar_logo() {
        $logo = $this->get_custom_logo();
        return !empty($logo) && !$this->should_display_main_logo();
    }

    /**
     * Whether we should display the main logo.
     *
     * @author Rizwana Shaik
     * @return string url
     */
    public function get_custom_logo() {
        $logopath = "";
        if($this->page->theme->setting_file_url('logo', 'logo')) {
            $logopath = $this->page->theme->setting_file_url('logo', 'logo');
        }
        
        // if(empty($logopath)) {
        //     $default_logo = $this->image_url('default_logo', 'theme_magic');
        //     $logopath = $default_logo;
        // }
        return $logopath;
    }

    /**
     * Displays Leftmenu links added from respective plugins using the function in lib.php as "plugintype_pluginname_leftmenunode()
     * The links are injected in the left menu.
     *
     * @return HTML
     */
    public function left_navigation_quick_links(){
        global $DB, $CFG, $USER, $PAGE;
        $systemcontext = context_system::instance();
        $core_component = new core_component();
        $block_content = '';
        $local_pluginlist = $core_component::get_plugin_list('local');
        $block_pluginlist = $core_component::get_plugin_list('block');

        $block_content .= html_writer::start_tag('ul', array('class'=>'pull-left row-fluid user_navigation_ul'));
         //======= Dasboard link ========//  
        $dasboard_link = $CFG->wwwroot;
        if( is_siteadmin() || has_capability('block/learnerscript:viewreports', $systemcontext) ){

            $dasboard_link = $CFG->wwwroot."/blocks/reportdashboard/dashboard.php?dashboardurl=Facademy";
    
        }

        $block_content .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_dashboard', 'class'=>'pull-left user_nav_div dashboard'));
        $button1 = html_writer::link($dasboard_link, '<span class="side_menu_img_icon dashboard_home_icon"></span><span class="user_navigation_link_text">'.get_string('leftmenu_dashboard', 'theme_academy').'</span>', array('class'=>'user_navigation_link'));
        $block_content .= $button1;
        $block_content .= html_writer::end_tag('li');


            /*
            // static nav menus 
            // trainings
            $block_content .= html_writer::start_tag('li', array('id'=> '', 'class'=>'pull-left user_nav_div'));
            $block_content .=  html_writer::link("#", '<span class="side_menu_img_icon trainings_icon"></span><span class="user_navigation_link_text">Trainings</span>', array('class'=>'user_navigation_link'));
            $block_content .= html_writer::end_tag('li');
            // competencies
            $block_content .= html_writer::start_tag('li', array('id'=> '', 'class'=>'pull-left user_nav_div'));
            $block_content .=  html_writer::link("#", '<span class="side_menu_img_icon competencies_icon"></span><span class="user_navigation_link_text">Competencies</span>', array('class'=>'user_navigation_link'));
            $block_content .= html_writer::end_tag('li');
            // exams
            $block_content .= html_writer::start_tag('li', array('id'=> '', 'class'=>'pull-left user_nav_div'));
            $block_content .=  html_writer::link("#", '<span class="side_menu_img_icon exams_icon"></span><span class="user_navigation_link_text">Exams</span>', array('class'=>'user_navigation_link'));
            $block_content .= html_writer::end_tag('li');
            // sectors
            $block_content .= html_writer::start_tag('li', array('id'=> '', 'class'=>'pull-left user_nav_div'));
            $block_content .=  html_writer::link("#", '<span class="side_menu_img_icon sectors_icon"></span><span class="user_navigation_link_text">Sectors</span>', array('class'=>'user_navigation_link'));
            $block_content .= html_writer::end_tag('li');
            // financialpayments
            $block_content .= html_writer::start_tag('li', array('id'=> '', 'class'=>'pull-left user_nav_div'));
            $block_content .=  html_writer::link("#", '<span class="side_menu_img_icon financialpayments_icon"></span><span class="user_navigation_link_text">Financial & Payments</span>', array('class'=>'user_navigation_link'));
            $block_content .= html_writer::end_tag('li');
            // events
            $block_content .= html_writer::start_tag('li', array('id'=> '', 'class'=>'pull-left user_nav_div'));
            $block_content .=  html_writer::link("#", '<span class="side_menu_img_icon events_icon"></span><span class="user_navigation_link_text">Events</span>', array('class'=>'user_navigation_link'));
            $block_content .= html_writer::end_tag('li');
            // organizations
            $block_content .= html_writer::start_tag('li', array('id'=> '', 'class'=>'pull-left user_nav_div'));
            $block_content .=  html_writer::link("#", '<span class="side_menu_img_icon organizations_icon"></span><span class="user_navigation_link_text">Organizations</span>', array('class'=>'user_navigation_link'));
            $block_content .= html_writer::end_tag('li');
            // questionbank
            $block_content .= html_writer::start_tag('li', array('id'=> '', 'class'=>'pull-left user_nav_div'));
            $block_content .=  html_writer::link("#", '<span class="side_menu_img_icon questionbank_icon"></span><span class="user_navigation_link_text">Question Bank</span>', array('class'=>'user_navigation_link'));
            $block_content .= html_writer::end_tag('li');
            // cpd
            $block_content .= html_writer::start_tag('li', array('id'=> '', 'class'=>'pull-left user_nav_div'));
            $block_content .=  html_writer::link("#", '<span class="side_menu_img_icon cpd_icon"></span><span class="user_navigation_link_text">CPD</span>', array('class'=>'user_navigation_link'));
            $block_content .= html_writer::end_tag('li');
            // halls
            $block_content .= html_writer::start_tag('li', array('id'=> '', 'class'=>'pull-left user_nav_div'));
            $block_content .=  html_writer::link("#", '<span class="side_menu_img_icon halls_icon"></span><span class="user_navigation_link_text">Halls</span>', array('class'=>'user_navigation_link'));
            $block_content .= html_writer::end_tag('li');
            // reports
            $block_content .= html_writer::start_tag('li', array('id'=> '', 'class'=>'pull-left user_nav_div'));
            $block_content .=  html_writer::link("#", '<span class="side_menu_img_icon reports_icon"></span><span class="user_navigation_link_text">Reports</span>', array('class'=>'user_navigation_link'));
            $block_content .= html_writer::end_tag('li'); */
            //=======Leader Dasboard link ========// 
//             $gamificationb_plugin_exist = $core_component::get_plugin_directory('block', 'gamification');
//             $gamificationl_plugin_exist = $core_component::get_plugin_directory('local', 'gamification');
//             if($gamificationl_plugin_exist && $gamificationb_plugin_exist && (has_capability('local/gamification:view
// ',$systemcontext) || is_siteadmin() )){
//                 $block_content .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_gamification_leaderboard', 'class'=>'pull-left user_nav_div notifications'));
//                 $gamification_url = new moodle_url('/blocks/gamification/dashboard.php');
//                 $gamification = html_writer::link($gamification_url, '<i class="fa fa-trophy"></i><span class="user_navigation_link_text">'.get_string('leftmenu_gmleaderboard','theme_academy').'</span>',array('class'=>'user_navigation_link'));
//                 $block_content .= $gamification;
//                 $block_content .= html_writer::end_tag('li');
//             }

            $pluginnavs = array();
            foreach($local_pluginlist as $key => $local_pluginname){
                if(file_exists($CFG->dirroot.'/local/'.$key.'/lib.php')){
                    require_once($CFG->dirroot.'/local/'.$key.'/lib.php');
                    $functionname = 'local_'.$key.'_leftmenunode';
                    if(function_exists($functionname)){
                        $data = $functionname();
                        foreach($data as  $key => $val){
                            $pluginnavs[$key][] = $val;
                        }
                    }
                }
            }
            // ksort($pluginnavs);
            // foreach($pluginnavs as $pluginnav){
            //     foreach($pluginnav  as $key => $value){
            //             $data = $value;
            //             $block_content .= $data;
            //     }
            // }

            foreach($block_pluginlist as $key => $local_pluginname){
                 if(file_exists($CFG->dirroot.'/blocks/'.$key.'/lib.php')){
                    require_once($CFG->dirroot.'/blocks/'.$key.'/lib.php');
                    $functionname = 'block_'.$key.'_leftmenunode';
                    if(function_exists($functionname)){
                    // $block_content .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_dashboard', 'class'=>'pull-left user_nav_div dashboard row-fluid '));
                        $data = $functionname();
                        foreach($data as  $key => $val){
                            $pluginnavs[$key][] = $val;
                        }
                    // $block_content .= html_writer::end_tag('li');
                    }
                }
            }
            $tool_certificate = $core_component::get_plugin_directory('tool', 'certificate');
            if($tool_certificate){
                if(file_exists($CFG->dirroot.'/admin/tool/certificate/lib.php')){
                    require_once($CFG->dirroot.'/admin/tool/certificate/lib.php');
                    $functionname = 'tool_certificate_leftmenunode';
                    if(function_exists($functionname)){
                        $data = $functionname();
                        foreach($data as  $key => $val){
                            $pluginnavs[$key][] = $val;
                        }
                    }
                }
            }
            ksort($pluginnavs);   
            foreach($pluginnavs as $pluginnav){
                foreach($pluginnav  as $key => $value){
                        $data = $value;
                        $block_content .= $data;
                }
            }

            /*Guidelines Link*/
            //if(is_siteadmin()){
                // $block_content .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_adminstration', 'class'=>'pull-left user_nav_div adminstration'));
                //     $admin_url = new moodle_url('/guideline.php');
                //     $admin = html_writer::link($admin_url, '<span class="guideline_icon leftmenu_icon"></span><span class="user_navigation_link_text">'.get_string('leftmenu_guideline','theme_academy').'</span>',array('class'=>'user_navigation_link'));
                //     $block_content .= $admin;
                // $block_content .= html_writer::end_tag('li');
            //}

            /*coe Link*/
            //if(is_siteadmin()){
                // $block_content .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_adminstration', 'class'=>'pull-left user_nav_div adminstration'));
                //     $admin_url = new moodle_url('/coe.php');
                //     $admin = html_writer::link($admin_url, '<span class="coe_icon leftmenu_icon"></span><span class="user_navigation_link_text">'.get_string('leftmenu_coe','theme_academy').'</span>',array('class'=>'user_navigation_link'));
                //     $block_content .= $admin;
                // $block_content .= html_writer::end_tag('li');
           // }  

            /*Site Administration Link*/
            if(is_siteadmin()){
                $block_content .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_adminstration', 'class'=>'pull-left user_nav_div adminstration'));
                    $admin_url = new moodle_url('/admin/search.php');
                    $admin = html_writer::link($admin_url, '<span class="side_menu_img_icon systemsettings_icon"></span><span class="user_navigation_link_text">'.get_string('leftmenu_adminstration','theme_academy').'</span>',array('class'=>'user_navigation_link'));
                    $block_content .= $admin;
                $block_content .= html_writer::end_tag('li');
            }
        $block_content .= html_writer::end_tag('ul');
        
        return $block_content;
    }
    function get_newfont_path(){
        //$return = new moodle_url('/theme/academy/fonts/roboto/roboto.css');
        $return = new moodle_url('/theme/academy/fonts/helveticfont.css');
        return $return;
    }
    public function about_link() {
        global $CFG;
        $url = "https://fa.gov.sa/ar/AboutUs";

        return $url;
    }
    public function cart_link() {
        global $CFG;
        $url=$CFG->wwwroot.'/admin/tool/product/cart.php?action=addcart';
        
        return $url;
    }
    
    public function contact_link() {
        global $CFG;
        $url=$CFG->wwwroot.'/theme/academy/contactus.php';
        
        return $url;
    }
    public function individual_registration_url() {
        global $CFG;
        $url=$CFG->wwwroot.'/auth/registration/register.php';
      
        return $url;
    }
    public function training_programs_link() {
        global $CFG;
        $url = "https://fa.gov.sa/ar/services/Pages/Programs.aspx";
        return $url;
    }
    public function learning_path_link() {
        global $CFG;
        $url=$CFG->wwwroot.'/local/learningtracks/learningpath.php';
        // $url = "https://fa.gov.sa/ar/services/Pages/Events.aspx";
        return $url;
    }
    public function exams_link() {
        global $CFG;
        $url = "https://fa.gov.sa/ar/services/Pages/Exams.aspx";
       
        return $url;
    }
    public function events_link() {
        global $CFG;
        $url = "https://fa.gov.sa/ar/services/Pages/Events.aspx";
        
        return $url;
    }
    /**
     * Get all slider images for homepage
     *
     * @author Kamesh
     * @return Array sliders
     */
    public function gethomepage_slider_images() {
        $sliders=[];
        if($this->page->theme->setting_file_url('sliderimage1', 'sliderimage1')) {
            $sliders[]['url'] = $this->page->theme->setting_file_url('sliderimage1', 'sliderimage1');
        }
        if($this->page->theme->setting_file_url('sliderimage2', 'sliderimage2')) {
            $sliders[]['url'] = $this->page->theme->setting_file_url('sliderimage2', 'sliderimage2');
        }
        if($this->page->theme->setting_file_url('sliderimage3', 'sliderimage3')) {
            $sliders[]['url'] = $this->page->theme->setting_file_url('sliderimage3', 'sliderimage3');
        }
        if($this->page->theme->setting_file_url('sliderimage4', 'sliderimage4')) {
            $sliders[]['url'] = $this->page->theme->setting_file_url('sliderimage4', 'sliderimage4');
        }
        if(!count($sliders)){
            $sliders[]['url'] = "theme/academy/pix/homepage/sliderimg.png";
        }
        foreach($sliders as $key => $slider){
            $sliders[$key]['slideid']= $key;
        }
        return $sliders;
    }
    
     /**
     * Whether we should display the Site logo
     *
     * @author Kamesh
     * @return string url
     */
    public function get_homepage_logo() {
        $logopath = "";
        if($this->page->theme->setting_file_url('sitehomepagelogo', 'sitehomepagelogo')) {
            $logopath = $this->page->theme->setting_file_url('sitehomepagelogo', 'sitehomepagelogo');
        }
        return $logopath;
    }
    /**
     * Get Current Language
     *
     * @author Kamesh
     * @return string url
     */
    public function getcurrent_lang() {
       $langs = get_string_manager()->get_list_of_translations();
       $strlang = get_string('language');
       $currentlang = current_language();
        if (isset($langs[$currentlang])) {
        $currentlang = $langs[$currentlang];
        } else {
            $currentlang = $strlang;
        }
       return $currentlang;
    }

    public function view_homepage_header_navicons() {
        global $CFG,$USER;
        $systemcontext = context_system::instance();
        $view_navbar = (!is_siteadmin() && has_capability('local/organization:manage_trainee',$systemcontext)) ? true : false;
        return $view_navbar;
    }

    public function nav_link_home_url() {
        global $CFG;
        $url="https://fa.gov.sa/";
        return $url;
    }
    public function generateenlangurl(){
        $langurl = $this->generatelangurl("en");
        return $langurl;
    }
    public function generatearlangurl(){
        $langurl = $this->generatelangurl("ar");
        return $langurl;
    }
    public function generatelangurl($lang){
        $langurl = "?";
        $main_url = "";
        if(!empty($_GET)){
            $count = 0;
            foreach($_GET as $key=>$param){
               if($key=='lang'){
                 continue;
               }
               $main_url.=($count==0?"?":"&").$key.'='.$param; 
               $langurl = "&";
               $count++;
            }
        }
        $main_url = $main_url.$langurl."lang=".$lang;
        return $main_url;
    }
    public function language_nav_link(){
        $lang_url = $this->generatelangurl('en');
        $icon = '<span class="text-light bold"><strong>EN</strong></span>';
        if(current_language()=='en'){
            $lang_url = $this->generatelangurl('ar');
            $icon = '<span class="euro_icon"></span>';
        }

       return '<a href="'.$lang_url.'">'.$icon.'</a>'; 
    }
    public function currentlang(){
        return current_language(); 
    }
    public function is_eng_lang(){
        return current_language()=='en'?true:false; 
    }
    public function favicon(){
        global $CFG;
        return $CFG->wwwroot."/theme/academy/pix/favicon.ico";
    }

    public function cartview(){
        global $USER,$DB;
        $systemcontext = context_system::instance();
        $traineeroleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
        return (user_has_role_assignment($USER->id,$traineeroleid,$systemcontext->id)) ? true : false;
    }

    public function helpcenter_link() {
        global $CFG,$DB,$USER;
        $systemcontext = context_system::instance();
        $getidnumber = $DB->get_record('course_modules',array('idnumber'=>"helporg"));
        //$url =  $CFG->wwwroot.'/mod/resource/view.php?id='.$getidnumber->id.'&forceview=1';
        $url =  $CFG->wwwroot.'/theme/academy/help.php';
        $traineeroleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
        $orgofficialroleid = $DB->get_field('role', 'id', array('shortname' => 'organizationofficial'));
        //$url = (!is_siteadmin() && (user_has_role_assignment($USER->id,$traineeroleid,$systemcontext->id) ||  has_capability('local/organization:manage_organizationofficial',$systemcontext))) ? $url : get_config('theme_academy','helpcenter');
        //$url = get_config('theme_academy','helpcenter');
        return $url;
    }

    public function helpcenterview(){
        global $USER,$DB;
        $systemcontext = context_system::instance();
        $traineeroleid = $DB->get_field('role', 'id', array('shortname' => 'trainee'));
        return (!is_siteadmin() && (user_has_role_assignment($USER->id,$traineeroleid,$systemcontext->id) ||  has_capability('local/organization:manage_organizationofficial',$systemcontext))) ? true : false;
    }
    /**
     * Returns the CSS classes to apply to the body tag.
     *
     * @since Moodle 2.5.1 2.6
     * @param array $additionalclasses Any additional classes to apply.
     * @return string
     */
    public function body_css_classes(array $additionalclasses = array()) {
        return 'newdesignlayout '.$this->page->bodyclasses . ' ' . implode(' ', $additionalclasses);
    }
    /**
     * Add update restrictions for trainers on course view page.
     * 
     */
    public function add_restrictions_to_course_update_options($trainerid, $roleobj) {
        global $USER, $PAGE;
        if (!$trainerid) {
            $trainerid = $USER->id;
        }
        echo html_writer::tag('div', '', ['class' => 'user_role', 'data-role' => $roleobj->shortname, 'style' => 'display:none']);
        $aproove = get_string('approve', 'theme_academy');
        $reject = get_string('reject', 'theme_academy');
        $PAGE->requires->js_call_amd('theme_academy/restricttabs', 'init', [$aproove, $reject]);
    }
    /**
     * Check if the user has the specified role
     * @param $userid (INT)
     * @return (object)
     */
    public function if_user_has_role($userid, $role_shortname) {
        global $DB;
        if (!$userid || $userid == '') {
            print_error('missinguserid', 'local_exams');
        }
    
        if (!$role_shortname || $role_shortname == '') {
            print_error('missingroleshortname', 'theme_academy');
        }
        $user_assigned_role = $DB->get_record_sql(" SELECT u.id, r.shortname 
            FROM {user} u
            JOIN {role_assignments} ra ON ra.userid = u.id
            JOIN {context} ctx ON ctx.id = ra.contextid AND ctx.contextlevel = 10
            JOIN {role} r ON r.id = ra.roleid
            WHERE u.id = :userid  AND r.shortname = :shortname
            ORDER BY ra.id DESC
        ", ['userid' => $userid, 'shortname' => $role_shortname]);
        return $user_assigned_role;
    }

    public function user_menu($user = null, $withlinks = null) {
        global $USER, $CFG, $DB;
        require_once($CFG->dirroot . '/user/lib.php');
        // require_once($CFG->dirroot . "/lib/outputcomponents.php");

        if (is_null($user)) {
            $user = $USER;
        }

        // Note: this behaviour is intended to match that of core_renderer::login_info,
        // but should not be considered to be good practice; layout options are
        // intended to be theme-specific. Please don't copy this snippet anywhere else.
        if (is_null($withlinks)) {
            $withlinks = empty($this->page->layout_options['nologinlinks']);
        }

        // Add a class for when $withlinks is false.
        $usermenuclasses = 'usermenu';
        if (!$withlinks) {
            $usermenuclasses .= ' withoutlinks';
        }

        $returnstr = "";

        // If during initial install, return the empty return string.
        if (during_initial_install()) {
            return $returnstr;
        }

        $loginpage = $this->is_login_page();
        $loginurl = get_login_url();

        // Get some navigation opts.
        $opts = user_get_user_navigation_info($user, $this->page);

        $croles = (new \local_userapproval\action\manageuser())->get_currentroles();
        $crolescount = COUNT($croles);
        if ($crolescount <= 1) {
            foreach($opts->navitems as $key => $opts->navitem) {
                if ($opts->navitem->title == 'Switch role to...') {
                    unset($opts->navitems[$key]);
                }
            }
        }
        $customrolecontext = context_system::instance();
        if ((!empty($user->access['rsw'][$customrolecontext->path]))) {
            if ($role = $DB->get_record('role', array('id' => $user->access['rsw'][$customrolecontext->path]))) {
                $opts->metadata['asotherrole'] = true;
                $opts->metadata['rolename'] = '('.role_get_name($role, $customrolecontext).')';
            }
        }

        if (!empty($opts->unauthenticateduser)) {
            $returnstr = get_string($opts->unauthenticateduser['content'], 'moodle');
            // If not logged in, show the typical not-logged-in string.
            if (!$loginpage && (!$opts->unauthenticateduser['guest'] || $withlinks)) {
                $returnstr .= " (<a href=\"$loginurl\">" . get_string('login') . '</a>)';
            }

            return html_writer::div(
                html_writer::span(
                    $returnstr,
                    'login nav-link'
                ),
                $usermenuclasses
            );
        }

        $avatarclasses = "avatars";
        $avatarcontents = html_writer::span($opts->metadata['useravatar'], 'avatar current');
        $usertextcontents = $opts->metadata['userfullname'];

        // Other user.
        if (!empty($opts->metadata['asotheruser'])) {
            $avatarcontents .= html_writer::span(
                $opts->metadata['realuseravatar'],
                'avatar realuser'
            );
            $usertextcontents = $opts->metadata['realuserfullname'];
            $usertextcontents .= html_writer::tag(
                'span',
                get_string(
                    'loggedinas',
                    'moodle',
                    html_writer::span(
                        $opts->metadata['userfullname'],
                        'value'
                    )
                ),
                array('class' => 'meta viewingas')
            );
        }

        // Role.
        if (!empty($opts->metadata['asotherrole'])) {
            $role = \core_text::strtolower(preg_replace('#[ ]+#', '-', trim($opts->metadata['rolename'])));
            $usertextcontents .= html_writer::span(
                $opts->metadata['rolename'],
                'meta role role-' . $role
            );
        }

        // User login failures.
        if (!empty($opts->metadata['userloginfail'])) {
            $usertextcontents .= html_writer::span(
                $opts->metadata['userloginfail'],
                'meta loginfailures'
            );
        }

        // MNet.
        if (!empty($opts->metadata['asmnetuser'])) {
            $mnet = strtolower(preg_replace('#[ ]+#', '-', trim($opts->metadata['mnetidprovidername'])));
            $usertextcontents .= html_writer::span(
                $opts->metadata['mnetidprovidername'],
                'meta mnet mnet-' . $mnet
            );
        }

        $returnstr .= html_writer::span(
            html_writer::span($usertextcontents, 'usertext mr-1') .
            html_writer::span($avatarcontents, $avatarclasses),
            'userbutton'
        );

        // Create a divider (well, a filler).
        $divider = new \action_menu_filler();
        $divider->primary = false;

        $am = new \action_menu();
        $am->set_menu_trigger(
            $returnstr,
            'nav-link'
        );
        $am->set_action_label(get_string('usermenu'));
        $am->set_nowrap_on_items();
        if ($withlinks) {
            $navitemcount = count($opts->navitems);
            $idx = 0;
            foreach ($opts->navitems as $key => $value) {

                switch ($value->itemtype) {
                    case 'divider':
                        // If the nav item is a divider, add one and skip link processing.
                        $am->add($divider);
                        break;

                    case 'invalid':
                        // Silently skip invalid entries (should we post a notification?).
                        break;

                    case 'link':
                        // Process this as a link item.
                        $pix = null;
                        if (isset($value->pix) && !empty($value->pix)) {
                            $pix = new pix_icon($value->pix, '', null, array('class' => 'iconsmall'));
                        } else if (isset($value->imgsrc) && !empty($value->imgsrc)) {
                            $value->title = html_writer::img(
                                $value->imgsrc,
                                $value->title,
                                array('class' => 'iconsmall')
                            ) . $value->title;
                        }

                        $al = new \action_menu_link_secondary(
                            $value->url,
                            $pix,
                            $value->title,
                            array('class' => 'icon')
                        );
                        if (!empty($value->titleidentifier)) {
                            $al->attributes['data-title'] = $value->titleidentifier;
                        }
                        $am->add($al);
                        break;
                }

                $idx++;

                // Add dividers after the first item and before the last item.
                if ($idx == 1 || $idx == $navitemcount - 1) {
                    $am->add($divider);
                }
            }
        }

        return html_writer::div(
            $this->render($am),
            $usermenuclasses
        );
    }
}
