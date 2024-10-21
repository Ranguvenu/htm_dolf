define(['jquery', 'core/ajax', 'core/notification'], function($, Ajax, Notification) {

    return /** @alias module:tool_lpmigrate/frameworks_datasource */ {
        /**
         * Process the results for auto complete elements.
         * @param {String} selector The selector of the auto complete element.
         * @param {Array} results An array or results.
         * @return {Array} New array of results.
         */
        processResults: function(selector, results) {
            var options = [];

            $.each(results.data, function(index, response) {
                options.push({
                    value: response.id,
                    label: response.fullname
                });
            });
            return options;
        },
        /**
         * Source of data for Ajax element.
         *
         * @param {String} selector The selector of the auto complete element.
         * @param {String} query The query string.
         * @param {Function} callback A callback function receiving an array of results.
         */
        /* eslint-disable promise/no-callback-in-promise */
        transport: function(selector, query, callback) {
            var el = $(selector),
            type = el.data('type');
            switch(type){
                case 'program_competencylevel':
                    ctype = el.data('ctype');
                    programid = el.data('programid');
                break;
                case 'programusers':
                    programid = el.data('programid');
                break;
            }
            Ajax.call([{
                methodname: 'local_exams_ajaxdatalist',
                args: {type: type,ctype: ctype, programid: programid}
            }])[0].then(callback).catch(Notification.exception);
        }
    };
});
