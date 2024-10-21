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

import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import Ajax from 'core/ajax';
import {get_string as getString} from 'core/str';
import $ from 'jquery';

export const init = () => {
    
    $('a[data-title="switchroleto,moodle"]').on( "click", function(e) {
        e.preventDefault();

        var params = {};
        var promise = Ajax.call([{
            methodname: 'local_user_customswitchrole',
            args: params
        }]);
        promise[0].done(function(resp) {
            console.log(resp);
            ModalFactory.create({
                title: getString('switchrole', 'local_userapproval'),
                type: ModalFactory.types.CANCEL,
                body: resp.roles
            }).done(function(modal) {
                this.modal = modal;
                modal.getRoot().on(ModalEvents.save, function(e) {

                }.bind(this));
                modal.show();
            }.bind(this));

        }).fail(function() {
            // do something with the exception
             console.log('exception');
        });
    });
};
