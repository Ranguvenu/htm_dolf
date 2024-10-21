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
 * @package   theme_academy
 * @copyright 2016 Ryan Wyllie
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings = new theme_academy_admin_settingspage_tabs('themesettingacademy', get_string('configtitle', 'theme_academy'));
    $page = new admin_settingpage('theme_academy_general', get_string('generalsettings', 'theme_academy'));

    // Unaddable blocks.
    // Blocks to be excluded when this theme is enabled in the "Add a block" list: Administration, Navigation, Courses and
    // Section links.
    $default = 'navigation,settings,course_list,section_links';
    $setting = new admin_setting_configtext('theme_academy/unaddableblocks',
        get_string('unaddableblocks', 'theme_academy'), get_string('unaddableblocks_desc', 'theme_academy'), $default, PARAM_TEXT);
    $page->add($setting);

    $setting = new admin_setting_configtext('theme_academy/helpcenter',
        get_string('helpcenter', 'theme_academy'), get_string('helpcenter_desc', 'theme_academy'), PARAM_URL);
    $page->add($setting);

    // Preset.
    $name = 'theme_academy/preset';
    $title = get_string('preset', 'theme_academy');
    $description = get_string('preset_desc', 'theme_academy');
    $default = 'default.scss';

    $context = context_system::instance();
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'theme_academy', 'preset', 0, 'itemid, filepath, filename', false);

    $choices = [];
    foreach ($files as $file) {
        $choices[$file->get_filename()] = $file->get_filename();
    }
    // These are the built in presets.
    $choices['default.scss'] = 'default.scss';
    $choices['plain.scss'] = 'plain.scss';

    $setting = new admin_setting_configthemepreset($name, $title, $description, $default, $choices, 'academy');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Preset files setting.
    $name = 'theme_academy/presetfiles';
    $title = get_string('presetfiles','theme_academy');
    $description = get_string('presetfiles_desc', 'theme_academy');

    $setting = new admin_setting_configstoredfile($name, $title, $description, 'preset', 0,
        array('maxfiles' => 20, 'accepted_types' => array('.scss')));
    $page->add($setting);

    // Background image setting.
    $name = 'theme_academy/backgroundimage';
    $title = get_string('backgroundimage', 'theme_academy');
    $description = get_string('backgroundimage_desc', 'theme_academy');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'backgroundimage');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Login Background image setting.
    $name = 'theme_academy/loginbackgroundimage';
    $title = get_string('loginbackgroundimage', 'theme_academy');
    $description = get_string('loginbackgroundimage_desc', 'theme_academy');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'loginbackgroundimage');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Variable $body-color.
    // We use an empty default value because the default colour should come from the preset.
    $name = 'theme_academy/brandcolor';
    $title = get_string('brandcolor', 'theme_academy');
    $description = get_string('brandcolor_desc', 'theme_academy');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Must add the page after definiting all the settings!
    $settings->add($page);

    // Advanced settings.
    $page = new admin_settingpage('theme_academy_advanced', get_string('advancedsettings', 'theme_academy'));

    // Raw SCSS to include before the content.
    $setting = new admin_setting_scsscode('theme_academy/scsspre',
        get_string('rawscsspre', 'theme_academy'), get_string('rawscsspre_desc', 'theme_academy'), '', PARAM_RAW);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Raw SCSS to include after the content.
    $setting = new admin_setting_scsscode('theme_academy/scss', get_string('rawscss', 'theme_academy'),
        get_string('rawscss_desc', 'theme_academy'), '', PARAM_RAW);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $settings->add($page);

    // Custom settings.
    $page = new admin_settingpage('theme_academy_custom', get_string('customsettings', 'theme_academy'));

    //Logo setting over site
    $name = 'theme_academy/logo';
    $title = get_string('logo', 'theme_academy');
    $description = get_string('logodesc', 'theme_academy');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'logo');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    //Homepage Logo setting over site
    $name = 'theme_academy/sitehomepagelogo';
    $title = get_string('sitehomepagelogo', 'theme_academy');
    $description = get_string('sitehomepagelogodesc', 'theme_academy');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'sitehomepagelogo');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    //hompepage slider image 1
    $name = 'theme_academy/sliderimage1';
    $title = get_string('sliderimage1', 'theme_academy');
    $description = get_string('sliderimage1desc', 'theme_academy');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'sliderimage1');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    //hompepage slider image 2
    $name = 'theme_academy/sliderimage2';
    $title = get_string('sliderimage2', 'theme_academy');
    $description = get_string('sliderimage2desc', 'theme_academy');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'sliderimage2');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    //hompepage slider image 3
    $name = 'theme_academy/sliderimage3';
    $title = get_string('sliderimage3', 'theme_academy');
    $description = get_string('sliderimage3desc', 'theme_academy');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'sliderimage3');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    //hompepage slider image 4
    $name = 'theme_academy/sliderimage4';
    $title = get_string('sliderimage4', 'theme_academy');
    $description = get_string('sliderimage4desc', 'theme_academy');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'sliderimage4');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $settings->add($page);
}
