/**
 * Add a create new group modal to the page.
 *
 * @module     core_group/AjaxForms
 * @class      AjaxForms
 * @package
 * @copyright  2017 Damyon Wiese <damyon@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 define([
    'core/str',
    'core/modal_factory',
    'core/modal_events',
    'core/ajax',
    'core/templates',
    'jquery',
    'theme_academy/cardPaginate',
    'theme_academy/homepage',
    'jqueryui',
], function(Str, ModalFactory, ModalEvents, Ajax, Templates, $,Cardpaginate, HomePage) {
    //var learningtrack;
    return {
        init: function(args) {
            this.AssignUsers(args);
        },
        myPayments: function() {
            var targetid = 'orgpayments_tabdata';
            var options = {targetID: targetid,
                        templateName: 'tool_product/orgpayments',
                        methodName: 'tool_product_get_orgpayments',
                        perPage: 5,
                        cardClass: 'col-md-6 col-12',
                        viewType: 'table'};

            var dataoptions = {tabname: 'orgpayments'};
            var filterdata = {};

            Cardpaginate.reload(options, dataoptions,filterdata);
        },
        load: function () {

        }
    };
});
