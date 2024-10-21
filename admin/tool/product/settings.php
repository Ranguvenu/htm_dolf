<?php

defined('MOODLE_INTERNAL') || die();

global $PAGE;

if ($hassiteconfig) {

    $settings = new admin_settingpage('telrpaymentgateway', new lang_string('telrpaymentgateway', 'tool_product'));
    $ADMIN->add('tools', $settings);
    //--- settings ------------------------------------------------------------------------------------------
    $settings->add(new admin_setting_configtext('tool_product/storeid', get_string('storeid', 'tool_product'), get_string('storeid_desc', 'tool_product'), '', PARAM_TEXT));
    $settings->add(new admin_setting_configpasswordunmask('tool_product/authkey', get_string('authkey', 'tool_product'), get_string('authkey_desc', 'tool_product'), '', PARAM_TEXT));
    $settings->add(new admin_setting_configpasswordunmask('tool_product/refund_authkey', get_string('authkey', 'tool_product'), get_string('refund_authkey_desc', 'tool_product'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('tool_product/sadad_storeid', get_string('sadad_storeid', 'tool_product'), get_string('sadad_storeid_desc', 'tool_product'), '', PARAM_TEXT));
    $settings->add(new admin_setting_configpasswordunmask('tool_product/sadad_authkey', get_string('sadad_authkey', 'tool_product'), get_string('sadad_authkey_desc', 'tool_product'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configpasswordunmask('tool_product/sadad_remotekey', get_string('sadad_remotekey', 'tool_product'), get_string('sadad_remotekey_desc', 'tool_product'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configcheckbox('tool_product/testmode', get_string('testmode', 'tool_product'), '', 0));
    $settings->add(new admin_setting_configcheckbox('tool_product/sadad_testmode', get_string('testmode', 'tool_product'), '', 0));


}


if ($hassiteconfig) {

    $taxsettings = new admin_settingpage('tax', get_string('tax', 'tool_product'));
    $ADMIN->add('tools', $taxsettings);
    $taxsettings->add(new \tool_product\tax_percentage_settings('tool_product/tax_percentage',
            get_string('tax_percentage', 'tool_product'),
            get_string('tax_percentage', 'tool_product'),
            0,
            PARAM_INT,5));
    $taxsettings->add(new \tool_product\max_discount_percentage_settings('tool_product/max_discount_percentage',
            get_string('max_discount_percentage', 'tool_product'),
            get_string('max_discount_percentage', 'tool_product'),
            0,
            PARAM_INT,5));

}
