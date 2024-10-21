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

import Ajax from 'core/ajax';
import Templates from 'core/templates';
import {get_string as getString} from 'core/str';
import $ from 'jquery';
import cardPaginate from 'theme_academy/cardPaginate';

export const init = () => {
    $('select[name="startdate[day]"]').on('change', function(e) {
        $('.entityhall .badge-info').html('');
        $('.entityhall .badge-info').html('Select Hall');
        $("select[name='halladdress']").val("0");
        var params = {};
        params.sessionkey = $("input[name='sesskey']").val();
        params.type = $("select[name='halladdress']").data('type');
        params.id = $("select[name='halladdress']").data('id');
        var promise = Ajax.call([{
            methodname: 'remove_reservations',
            args: params
        }]);
        promise[0].done(function(resp) {
            $(".entityhalldetails").html('');
            console.log('Successfully reservations removed');
        }).fail(function() {
             console.log('exception');
        });
    });
    $('select[name="startdate[month]"]').on('change', function(e) {
        $('.entityhall .badge-info').html('');
        $('.entityhall .badge-info').html('Select Hall');
        $("select[name='halladdress']").val("0");        
        var params = {};
        params.sessionkey = $("input[name='sesskey']").val();
        params.type = $("select[name='halladdress']").data('type');
        params.id = $("select[name='halladdress']").data('id');
        var promise = Ajax.call([{
            methodname: 'remove_reservations',
            args: params
        }]);
        promise[0].done(function(resp) {
            $(".entityhalldetails").html('');
            console.log('Successfully reservations removed');
        }).fail(function() {
             console.log('exception');
        });
    });
    $('select[name="startdate[year]"]').on('change', function(e) {
        $('.entityhall .badge-info').html('');
        $('.entityhall .badge-info').html('Select Hall');
        $("select[name='halladdress']").val("0");        
        var params = {};
        params.sessionkey = $("input[name='sesskey']").val();
        params.type = $("select[name='halladdress']").data('type');
        params.id = $("select[name='halladdress']").data('id');
        var promise = Ajax.call([{
            methodname: 'remove_reservations',
            args: params
        }]);
        promise[0].done(function(resp) {
            $(".entityhalldetails").html('');
            console.log('Successfully reservations removed');
        }).fail(function() {
             console.log('exception');
        });
    });
};

require(['jquery'], function($) {
    $(document).ready(function(){
        $(window).on('load', function(){
            var pgeid = $('.pagelayout-base').attr("id");
            if(pgeid == 'page-local-trainingprogram-index' || pgeid == 'page-local-events-addevent' || pgeid == 'page-local-questionbank-questionbank') { 
                if(pgeid == 'page-local-trainingprogram-index') {
                    var type = 'tprogram';
                }
                if( pgeid == 'page-local-events-addevent') {
                    var type = 'event';
                } 
                if(pgeid == 'page-local-questionbank-questionbank') {
                    var type = 'questionbank';
                }
                var params = {};
                params.sessionkey = $("input[name='sesskey']").val();
                params.type = type;
                var promise = Ajax.call([{
                    methodname: 'remove_reservations',
                    args: params
                }]);
                promise[0].done(function(resp) {
                    // $(".entityhalldetails").html('');
                    console.log('Successfully reservations removed');
                }).fail(function() {
                     console.log('eventexception');
                });
            }
        });
    });
});

