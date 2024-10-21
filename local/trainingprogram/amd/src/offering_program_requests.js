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
 * TODO describe module offering_program_requests
 *
 * @module     local_trainingprogram/offering_program_requests
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import homepage from 'theme_academy/homepage';
import Ajax from 'core/ajax';
import Templates from 'core/templates';
import {get_string as getString} from 'core/str';
const Selectors = {
    actions: {
        add_update_delete_entity: '[data-action="add_update_delete_entity"]',
        viewcurrentoffering :'[data-action="viewcurrentoffering"]', 
    },
};
let HomePage = new homepage();
export const init = () => {
    document.addEventListener('click', function(e) {
        let add_update_delete_entity = e.target.closest(Selectors.actions.add_update_delete_entity);
        if (add_update_delete_entity) {
            e.preventDefault();
            e.stopImmediatePropagation()
            const rootid = add_update_delete_entity.getAttribute('data-rootid');
            const entityid = add_update_delete_entity.getAttribute('data-entityid');
            const entitycode = add_update_delete_entity.getAttribute('data-code');
            const etype = add_update_delete_entity.getAttribute('data-entitytype');
            const requesttype = add_update_delete_entity.getAttribute('data-requesttype');
            const requestby = add_update_delete_entity.getAttribute('data-requestby')
            const requestbyname = add_update_delete_entity.getAttribute('data-requestbyname')
            const actiontype = add_update_delete_entity.getAttribute('data-actiontype')
            var displayparams = {};
            displayparams.entitycode = entitycode;
            displayparams.requestbyname = requestbyname;
            if(requesttype =='Create') {
                var headermessage = getString('createofferingconfirm', 'local_trainingprogram');
                var bodymessage = getString('createofferingconfirmmessage', 'local_trainingprogram', displayparams);
            } else if(requesttype =='update') {
                var headermessage = getString('updateofferingconfirm', 'local_trainingprogram');
                var bodymessage = getString('updateofferingconfirmmessage', 'local_trainingprogram', displayparams);
            } else {
                if(etype == 'Offering') {
                    var headermessage = getString('deleteofferingconfirm', 'local_trainingprogram');
                    var bodymessage = getString('deleteofferingconfirmmessage', 'local_trainingprogram', displayparams);
                } else {
                    var headermessage = getString('deleteprogramconfirm', 'local_trainingprogram');
                    var bodymessage = getString('deleteprogramconfirmmessage', 'local_trainingprogram', displayparams);
                }
            }
            ModalFactory.create({
                title: headermessage,
                type: ModalFactory.types.SAVE_CANCEL,
                body: bodymessage
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('yes', 'local_trainingprogram'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    Templates.renderForPromise('tool_product/loader', {}).then(({html, js}) => {
                        Templates.appendNodeContents('.modal-content', html, js);
                    });
                    var params = {};
                    params.rootid = rootid;
                    params.entityid = entityid;
                    params.entitycode = entitycode;
                    params.etype = etype;
                    params.requestby = requestby;
                    params.requesttype = requesttype;
                    params.actiontype =actiontype;
                    var promise = Ajax.call([{
                        methodname: 'local_trainingprogram_offering_program_action',
                        args: params
                    }]);
                    promise[0].done(function(resp) {
                        if(resp.response == 'success') {
                            window.location.href = resp.returnurl;
                        } else {
                            modal.hide();
                            HomePage.confirmbox(resp.response);
                        }
                    }).fail(function(err) {
                        HomePage.confirmbox(err.message);
                    });
                }.bind(this));
                modal.show();
            }.bind(this));
        }

        let viewcurrentoffering = e.target.closest(Selectors.actions.viewcurrentoffering);
        if (viewcurrentoffering) {
            const rootid = viewcurrentoffering.getAttribute('data-rootid');
            const offeringid = viewcurrentoffering.getAttribute('data-offeringid');
            const requesttype = viewcurrentoffering.getAttribute('data-requesttype');
            var params = {};
            params.rootid = rootid;
            params.offeringid = offeringid;
            params.requesttype = requesttype;
            var promise = Ajax.call([{
                methodname: 'local_trainingprogram_viewcurrentoffering',
                args: params
            }]);
            promise[0].done(function(resp) {
                ModalFactory.create({
                    title: getString('view_offering', 'local_trainingprogram'),
                    type: ModalFactory.types.DEFAULT,
                    body: resp.options,
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

