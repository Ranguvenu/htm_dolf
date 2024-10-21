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
            var el = $(selector);
            hallid=0;
            type = el.data('type');
            halllocation=0;
            switch(type){
                case 'schedulehalls':
                    city = $("#city option:selected").text();
                    buildingname = $("#buildingname option:selected").val();

                    var offeringtype = $("select[name='type']").val();
                    if (offeringtype == 1) {
                        var halllocation = $('input[name="halllocation1"]:checked').val();
                    } else {
                        var halllocation = $('input[name="halllocation"]:checked').val();
                    }
                    if(!buildingname) {
                        buildingname = 0;
                    }
                    hallid = el.data('hallid');
                    Ajax.call([{
                        methodname: 'local_scheduledhalls',
                        args: {query:query, type: type, hallid: hallid, city: city, buildingname: buildingname, halllocation: halllocation}
                    }])[0].then(callback).catch(Notification.exception);                    
                    break;
                case 'buildingname':
                        city = $("#city option:selected").val();
                        Ajax.call([{
                            methodname: 'local_scheduledhalls',
                            args: {query:query, type: type, hallid: 0, city: city, buildingname: 0, halllocation: halllocation}
                        }])[0].then(callback).catch(Notification.exception);
                    break;
                case 'city':
                        Ajax.call([{
                            methodname: 'local_scheduledhalls',
                            args: {query:query, type: type, hallid: 0, city: 0, buildingname: 0}
                        }])[0].then(callback).catch(Notification.exception);
                    break;
                case 'examhalls':
                        Ajax.call([{
                            methodname: 'local_scheduledhalls',
                            args: {query:query, type: type, hallid: 0, city: 0, buildingname: 0, halllocation: halllocation}
                        }])[0].then(callback).catch(Notification.exception);
                    break;                    
            }           
        },

        city: function() {

            $("select[name='buildingname'] option:selected").prop('selected', false);
            $("select[name='buildingname']").parent().find('.badge-info').html('');

            $("select[name='halladdress'] option:selected").prop('selected', false);
            $("select[name='halladdress']").parent().find('.badge-info').html('');

        },

        buildingname: function() {

            $("select[name='halladdress'] option:selected").prop('selected', false);
            $("select[name='halladdress']").parent().find('.badge-info').html('');

        }

    };

});
