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
    $('input[type="radio"][name="reservationtype"]').on('change', function(e) {
        let options = {
            'targetID': 'manage_reservations',
            'perPage': 10, 
            'cardClass': 'col-md-6 col-12', 
            'viewType': 'card',
            'methodName':'local_reservation_view',
            'templateName': 'local_hall/reservations'
        };
        let dataoptions = {
            'contextid' : 1
        };
        let filterdata = {};

        let reservationtype;
        if(e.target.value){
            reservationtype = e.target.value;
            filterdata.type = reservationtype;
            cardPaginate.reload(options, dataoptions,filterdata);
        }
    });
};