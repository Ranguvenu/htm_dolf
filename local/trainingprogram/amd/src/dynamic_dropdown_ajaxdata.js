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
 * TODO describe module dynamic_dropdown_ajaxdata
 *
 * @module     local_trainingprogram/dynamic_dropdown_ajaxdata
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/ajax', 'core/notification', 'core/str'], function($, Ajax, Notification, Str) {

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
            ctype = 0;
            programid = 0;
            offeringid = 0;
            type = el.data('type');
            levels = 0;
            sectors = 0;
            switch(type){
                case 'program_competencylevel':
                    if($("select[name='ctype[]']").val() === undefined ) {
                        ctype = el.attr('data-ctype');
                        levels = $("select[name='clevels']").val();
                    } else {
                        var ctype = $("select[name='ctype[]']  option:selected").map(function() {
                            return $(this).val();
                        }).get().join(',');
                        levels = $("select[name='clevels']").val();
                    }
                    ctype = JSON.stringify(ctype);
                    programid = el.data('programid');
                    offeringid = el.data('offeringid');
                    sectors = sectors;
                    levels = levels;
                break;
                case 'programusers':
                    ctype = el.data('ctype');
                    programid = el.data('programid');
                    offeringid = el.data('offeringid');
                break;
                case 'orgofficial':
                    ctype = el.data('ctype');
                    programid = $("select[name='organization']").val();
                    offeringid = el.data('offeringid');
                break;

                case 'officials':
                    ctype = el.data('ctype');
                    programid = el.data('programid');
                    offeringid = el.data('offeringid');
                break;

                case 'loginasusers':
                    ctype = el.data('ctype');
                    programid = el.data('programid');
                    offeringid = el.data('offeringid');
                break;
                
                case 'program_competency':
                    if($("select[name='clevels']").val() === undefined ) {
                        levels = $("select[name='clevels']").val();
                    } else {
                        levels = $("select[name='clevels']").val();
                    }
                    levels = levels;
                break;
                case 'levels':
                break;  
                case 'allentities':
                    ctype = el.data('ctype');
                    programid = el.data('programid');
                    offeringid = el.data('offeringid');
                break;          
            }
            console.log(programid);
            Ajax.call([{
                methodname: 'local_trainingprogram_ajaxdatalist',
                args: {query:query,type: type,ctype: ctype, programid: programid, offeringid: offeringid, sectors: sectors, level: levels}
            }])[0].then(callback).catch(Notification.exception);
        },

        organizationchanged: function() {
            $("select[name='orgofficial'] option:selected").prop('selected', false);
            $("select[name='orgofficial']").parent().find('.badge-info').html('');
     
        },
        sectorschanged: function() {

            $("select[name='segment'] option:selected").prop('selected', false);
            $("select[name='segment']").parent().find('.badge-info').html('');

            $("select[name='segment[]'] option:selected").prop('selected', false);
            $("select[name='segment[]']").parent().find('.badge-info').html('');

            $("select[name='targetgroup[]'] option:selected").prop('selected', false);
            $("select[name='targetgroup[]']").parent().find('.badge-info').html('');            

            var segment = Str.get_string('segment', 'local_trainingprogram');
            segment.then(function(title) {
                $("select[name='segment']").parent().find('.badge-info').html(title);
            });

            $("select[name='jobfamily'] option:selected").prop('selected', false);
            $("select[name='jobfamily']").parent().find('.badge-info').html('');

            var jobfamily = Str.get_string('jobfamily', 'local_trainingprogram');
            jobfamily.then(function(title) {
                $("select[name='jobfamily']").parent().find('.badge-info').html(title);
            });

            $("select[name='jobrole'] option:selected").prop('selected', false);
            $("select[name='jobrole']").parent().find('.badge-info').html('');
            
            var jobrole = Str.get_string('jobrole', 'local_trainingprogram');
            jobrole.then(function(title) {
                $("select[name='jobrole']").parent().find('.badge-info').html(title);
            });

            $("select[name='segments[]'] option:selected").prop('selected', false);
            $("select[name='segments[]']").parent().find('.badge-info').html('');

            $("input[name='alltargetgroup").prop("checked", false);


            /***** FA-53 ************************************** */
            var dropdown = document.getElementById("program_sectors");
            var selectedValues = [];
            var promise;
            var selectedSector;
            var is_already_added;
            if(dropdown) {
                for (var i = 0; i < dropdown.options.length; i++) {
                    if (dropdown.options[i].selected) {
                        selectedSector = dropdown.options[i].value;
                        is_already_added = document.querySelector('.newjobfamilyoptions_'+selectedSector);
                        if (!is_already_added) {
                            promise = Ajax.call([{
                                methodname: 'local_trainingprogram_newjobfamily_options',
                                args: {sector_id: selectedSector}
                            }]);
                            promise[0].done(function(resp) {
                                $('.newjobfamilyoption').append(resp.str);
                            }).fail(function(exception) {
                                console.log(exception);
                            });
                        }
                    }
                }
            }
            /******************************************* */
        },

        clevels: function() {

            $("select[name='ctype[]'] option:selected").prop('selected', false);
            $("select[name='ctype[]']").parent().find('.badge-info').html('');

            $("select[name='competencylevel[]'] option:selected").prop('selected', false);
            $("select[name='competencylevel[]']").parent().find('.badge-info').html('');

        },

        ctype: function() {

            var level = $("select[name='clevels']").val();
            var competencies = $("select[name='competencylevel[]']").find("option").map(function() { return this.value; }).get().join(',');
            var ctypes = $("select[name='ctype[]']").map(function() {
                return $(this).val();
            }).get().join(',');

            var promise = Ajax.call([{
                methodname: 'local_trainingprogram_competencies',
                args: {level:level, ctypes:ctypes, competencies: competencies}
            }]);

            promise[0].done(function(resp) {
                resp.forEach(myFunction);
                function myFunction(item, index) {                
                    $("select[name='competencylevel[]'] option[value='"+ item.id +"']").prop('selected', false);
                    $("select[name='competencylevel[]']").parent().find('.form-autocomplete-selection').find('span[data-value="'+ item.id +'"]').remove();
                }
            }).fail(function() {
                // do something with the exception
                 console.log('exception');
            });

        },

        segmentschanged: function() {
            
            $("select[name='jobfamily'] option:selected").prop('selected', false);
            $("select[name='jobfamily']").parent().find('.badge-info').html('');

            var jobfamily = Str.get_string('jobfamily', 'local_trainingprogram');
            jobfamily.then(function(title) {
                $("select[name='jobfamily']").parent().find('.badge-info').html(title);
            });

            $("select[name='jobrole'] option:selected").prop('selected', false);
            $("select[name='jobrole']").parent().find('.badge-info').html('');

            var jobrole = Str.get_string('jobrole', 'local_trainingprogram');
            jobrole.then(function(title) {
                $("select[name='jobrole']").parent().find('.badge-info').html(title);
            });

        },

        jfamilychanged: function() {

            $("select[name='jobrole'] option:selected").prop('selected', false);
            $("select[name='jobrole']").parent().find('.badge-info').html('');
        
            var jobrole = Str.get_string('jobrole', 'local_trainingprogram');
            jobrole.then(function(title) {
                $("select[name='jobrole']").parent().find('.badge-info').html(title);
            });

        }

    };
});
