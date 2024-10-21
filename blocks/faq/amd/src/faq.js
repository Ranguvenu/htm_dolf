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

import TPDynamicForm from 'local_trainingprogram/dynamicform';
import DynamicForm from 'core_form/dynamicform';
import {get_string as getString} from 'core/str';
import ModalFactory from 'core/modal_factory';
import Ajax from 'core/ajax';
import ModalForm from 'core_form/modalform';
import ModalEvents from 'core/modal_events';
import Templates from 'core/templates';


const Selectors = {
    actions: {
        createfaq: '[data-action="createfaq"]',
        deletefaq: '[data-action="deletefaq"]',
        viewfaq: '[data-action="viewfaq"]',

    },
};

export const init = () => {
    document.addEventListener('click', function(e) {
        e.stopImmediatePropagation();

        let element = e.target.closest(Selectors.actions.createfaq);
        if (element) {
            e.preventDefault();
            const title = element.getAttribute('data-id') ?
                getString('editfaq', 'block_faq', element.getAttribute('data-name')) :
                getString('createfaq', 'block_faq');
            const form = new TPDynamicForm({
                formClass: 'block_faq\\faqform',
                args: {id: element.getAttribute('data-id')},
                modalConfig: {title},
                returnFocus: element,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }

        let deletefaq = e.target.closest(Selectors.actions.deletefaq);
        if (deletefaq) {
            const id = deletefaq.getAttribute('data-id');
            ModalFactory.create({
                title: getString('deletefaq', 'block_faq'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('deletefaqconfirm', 'block_faq')
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('delete', 'block_faq'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.id = id;
                    var promise = Ajax.call([{
                        methodname: 'block_delete_faq',
                        args: params
                    }]);
                    promise[0].done(function(resp) {
                        window.location.reload(true);
                    }).fail(function() {
                        // do something with the exception
                         console.log('exception');
                    });
                }.bind(this));
                modal.show();
            }.bind(this));
        }

        let viewfaq = e.target.closest(Selectors.actions.viewfaq);
        if (viewfaq) {
            const id = viewfaq.getAttribute('data-id');
            var params = {};
            params.id = id;
            var promise = Ajax.call([{
                methodname: 'block_faq_info',
                args: params
            }]);
            promise[0].done(function(resp) {
                var faq = JSON.stringify(resp);
                ModalFactory.create({
                    title: getString('viewfaq', 'block_faq'),
                    body: resp.options
                }).done(function(modal) {
                    this.modal = modal;
                    this.modal.setLarge();
                    modal.show();
                }.bind(this));
            }).fail(function() {
                // do something with the exception
                 console.log('exception');
            });
        }
    });
};
