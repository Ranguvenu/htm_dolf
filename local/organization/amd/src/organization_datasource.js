define(['jquery', 'core/ajax', 'core/notification'], function($, Ajax, Notification) {

    return /** @alias module:tool_lpmigrate/frameworks_datasource */ {

        /**
         * Process the results for auto complete elements.
         *
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
            org = null,
            type = el.data('type');
            switch(type){
                case 'orgusers':
                    org = el.data('org');
                    break;
                case 'usersemail':
                    org = el.data('org');
                    break; 
                case 'usersidnumber':
                    org = el.data('org');
                    break;
                case 'all_users':
                    org = el.data('org');
                break;  
                case 'organization_list':
                    org = el.data('org');
                break;  
            }
            Ajax.call([{
                methodname: 'local_organization_userslist',
                args: {query:query,type: type, org: org}
            }])[0].then(callback).catch(Notification.exception);
        }
    };
});
