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

//const { exists } = require("grunt");


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
                    label: response.title
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
            sectorid=0,allsegments=0,sectorlist=0,selected_sectorlist=0,
            type = el.data('type');
            switch(type){
                case 'segment':
                    sectorid = el.attr('data-sectorid');
                    if(typeof sectorid === 'undefined' || sectorid === '0' || sectorid === 0 || sectorid === ''){
                        var single_sector = el.attr('data-single_sector');
                        if(single_sector == 1) {
                            sectorlist = $("select[name='sectors'] option:selected").map(function(){ return this.value }).get().join(", ");
                        } else {
                            sectorlist = $("select[name='sectors[]'] option:selected").map(function(){ return this.value }).get().join(", ");
                        }
                    }
                    break;
                case 'jobfamily':
                    //sectorid = el.data('segmentid');
                    sectorid = el.attr('data-segmentid');
                    console.log(sectorid);
                    if(typeof sectorid === 'undefined'){
                        selected_sectorlist = $("input[name='newjobfamilyoption']").attr('value');
                        if (!selected_sectorlist) {
                            selected_sectorlist = "";
                        }
                        console.log(selected_sectorlist);
                        // sectorlist =el.attr('data-sectorid');
                        sectorlist = $("select[name='sectors[]'] option:selected").map(function(){ return this.value }).get().join(",");
                        if(sectorlist != 0){
                            allsegments =  1;
                        }
                    }
                    break;
                case 'jobrole':
                    //sectorid = el.data('jobfamilyid');
                    sectorid = el.attr('data-jobfamilyid');
                    break;
            }
            

            Ajax.call([{
                methodname: 'local_trainingprogram_segmentlist',
                args: {query:query, type: type, selected_sectorlist: selected_sectorlist, sectorid: sectorid,sectorlist: sectorlist, allsegments: allsegments}
            }])[0].then(callback).catch(Notification.exception);
        }
    };

});
