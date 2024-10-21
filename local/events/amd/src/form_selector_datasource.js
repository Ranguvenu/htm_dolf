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
define(['jquery', 'core/ajax', 'core/notification', 'core/str'], function($, Ajax, Notification, Str) {

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
            listid = 0,
            module = 0,
            valselect = 0,
            itemid = 0,
            type = el.data('type');
            switch(type){
                case 'speakerlist':
                    listid = el.data('eventid');
                    module = el.data('finance');
                    break;
                case 'sponsorlist':
                    listid = el.data('eventid');
                    module = el.data('finance');
                    break;
                case 'partnerlist':
                    listid = el.data('eventid');
                    module = el.data('finance');
                    valselect = $("#el_partner option:selected").val();
                    if(!valselect) {
                        valselect = "0";
                    }
                    break;
                case 'agenda_speakerlist':
                    listid = el.data('eventid');
                    break;
                case 'userlist':
                    listid = el.data('eventid');
                    break;
                case 'examhalls':
                    module = 'exam';
                    break;    
            }
            var finance = el.data('finance');
            if (finance == 1) {
                $('body').on('change','input[name="type"]',function(){
                    $('input[name="amount"]').val('');
                });
                $('body').on('change','input[name="expensetype"]',function(){
                    var selected_type = $(this).val();
                    if(selected_type == 3){
                        $('input[name="amount"]').attr('readonly', false);
                        $("select[name='partnerid'] option:selected").prop('selected', false);
                        $("select[name='partnerid']").parent().find('.badge-info').html('');
                        var partnerid = Str.get_string('selectpartner', 'local_events');
                        partnerid.then(function(title) {
                            $("select[name='partnerid']").parent().find('.badge-info').html(title);
                        });
                        
                        $("select[name='speakerid'] option:selected").prop('selected', false);
                        $("select[name='speakerid']").parent().find('.badge-info').html('');
                        var speakerid = Str.get_string('selectspeaker', 'local_events');
                        speakerid.then(function(title) {
                            $("select[name='speakerid']").parent().find('.badge-info').html(title);
                        });

                        $("select[name='sponsorid'] option:selected").prop('selected', false);
                        $("select[name='sponsorid']").parent().find('.badge-info').html('');
                        var sponsorid = Str.get_string('selectsponsor', 'local_events');
                        sponsorid.then(function(title) {
                            $("select[name='sponsorid']").parent().find('.badge-info').html(title);
                        });
                    }

                    $('input[name="amount"]').val('');
                });
                var promise;
                $(selector).change(function () {
                    type = el.data('type');
                    if(type == 'sponsorlist') { 
                        $("select[name='partnerid'] option:selected").prop('selected', false);
                        $("select[name='partnerid']").parent().find('.badge-info').html('');
                        var partnerid = Str.get_string('selectpartner', 'local_events');
                        partnerid.then(function(title) {
                            $("select[name='partnerid']").parent().find('.badge-info').html(title);
                        });
                        
                        $("select[name='speakerid'] option:selected").prop('selected', false);
                        $("select[name='speakerid']").parent().find('.badge-info').html('');
                        var speakerid = Str.get_string('selectspeaker', 'local_events');
                        speakerid.then(function(title) {
                            $("select[name='speakerid']").parent().find('.badge-info').html(title);
                        });

                    }
                    if(type == 'speakerlist') {
                        $("select[name='partnerid'] option:selected").prop('selected', false);
                        $("select[name='partnerid']").parent().find('.badge-info').html('');
                        var partnerid = Str.get_string('selectpartner', 'local_events');
                        partnerid.then(function(title) {
                            $("select[name='partnerid']").parent().find('.badge-info').html(title);
                        });

                        $("select[name='sponsorid'] option:selected").prop('selected', false);
                        $("select[name='sponsorid']").parent().find('.badge-info').html('');
                        var sponsorid = Str.get_string('selectsponsor', 'local_events');
                        sponsorid.then(function(title) {
                            $("select[name='sponsorid']").parent().find('.badge-info').html(title);
                        });
                    }
                    if(type == 'partnerlist') {
                        $("select[name='speakerid'] option:selected").prop('selected', false);
                        $("select[name='speakerid']").parent().find('.badge-info').html('');
                        var speakerid = Str.get_string('selectspeaker', 'local_events');
                        speakerid.then(function(title) {
                            $("select[name='speakerid']").parent().find('.badge-info').html(title);
                        });

                        $("select[name='sponsorid'] option:selected").prop('selected', false);
                        $("select[name='sponsorid']").parent().find('.badge-info').html('');
                        var sponsorid = Str.get_string('selectsponsor', 'local_events');
                        sponsorid.then(function(title) {
                            $("select[name='sponsorid']").parent().find('.badge-info').html(title);
                        });

                        listid = el.data('eventid');
                        var s_value = $(selector +  " option:selected").val();
                        if(s_value!=""){
                            itemid = s_value;
                        } else {
                            itemid = 0;
                        }
                        promise = Ajax.call([{
                            methodname: 'local_events_financeamount',
                            dataType: "text",
                            args: {type:type,listid: listid, itemid:itemid}
                        }]);
                        promise[0].done(function(results) {
                            $('input[name="amount"]').val(results);
                            if(results!='') {
                                $('input[name="amount"]').attr('readonly', true);
                            } else {
                                $('input[name="amount"]').attr('readonly', false);
                            }
                        }).catch(Notification.exception);
                    }  
                });
            }
            Ajax.call([{
                methodname: 'local_events_form_selector',
                args: {query:query, type: type, listid: listid, module: module, valselect:valselect}
            }])[0].then(callback).catch(Notification.exception);
        }
    };

});
