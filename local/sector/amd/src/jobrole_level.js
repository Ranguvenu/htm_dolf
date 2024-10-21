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
import ModalForm from 'core_form/modalform';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import Templates from 'core/templates';
import Ajax from 'core/ajax';
import {get_string as getString} from 'core/str';
import $ from 'jquery';
import homepage from 'theme_academy/homepage';

const Selectors = {
    actions: {
        createJobrole: '[data-action="createjobrole_level"]',
        deletejobrole_level: '[data-action="deletejobrole_level"]',
        responsibilityview: '[data-action="responsibilityview"]',
        createJobrole_resp: '[data-action="createjobrole_resp"]',
        deleteresponsibility: '[data-action="deleteresponsibility"]',
    },
};
let HomePage = new homepage();
export const init = () => {
    document.addEventListener('click', function(e) {
        let element = e.target.closest(Selectors.actions.createJobrole);

        e.stopImmediatePropagation();
        if (element) {
            e.preventDefault();
            const title = element.getAttribute('data-id') ?
                getString('editlevel', 'local_sector', element.getAttribute('data-name')) :
                getString('addlevel', 'local_sector');
            const form = new ModalForm({
                formClass: 'local_sector\\form\\jobrole_level',
                args: {id: element.getAttribute('data-id'),jobfamilyid: element.getAttribute('data-jobfamilyid')},
                modalConfig: {title},
                returnFocus: element,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }

        let deletejobrole_level = e.target.closest(Selectors.actions.deletejobrole_level);
        if (deletejobrole_level) {
             e.preventDefault();
             const roleid = deletejobrole_level.getAttribute('data-id');
             const rolename = deletejobrole_level.getAttribute('data-name');
            ModalFactory.create({
                title: getString('deletelevelconfirm', 'local_sector'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('leveldeleteconfirm', 'local_sector',rolename)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('deletetext', 'local_sector'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.elementid = roleid;
                    params.elementtype = 'jobrole';
                    var promise = Ajax.call([{
                        methodname: 'local_sector_candeleteelement',
                        args: params
                    }]);
                    promise[0].done(function(resp) {
                        if(resp.candelete == 1) {
                            modal.hide();
                            HomePage.confirmbox(getString('remove_dependency', 'local_sector')); 
                        } else {
                            var params = {};
                            params.roleid = roleid;
                            var promise = Ajax.call([{
                                methodname: 'local_sector_deletejobrole_level',
                                args: params
                            }]);
                            promise[0].done(function(resp) {
                                window.location.reload(true);
                            }).fail(function() {
                                console.log('exception');
                            });
                        }
                    }).fail(function() {
                        console.log('exception');
                    });    
                }.bind(this));
                modal.show();
            }.bind(this));
        }
        let responsibilityview = e.target.closest(Selectors.actions.responsibilityview);
        if (responsibilityview) {
            e.stopImmediatePropagation();
            const roleid = responsibilityview.getAttribute('data-roleid');
            const jobfamilyid = responsibilityview.getAttribute('data-jobfamilyid');
            var options = {};
            options.roleid = roleid;
            options.jobfamilyid = jobfamilyid;
            var trigger = $(Selectors.actions.responsibilityview);
            ModalFactory.create({
                title: getString('responsibilityview', 'local_sector'),
                body: Templates.render('local_sector/responsibilityview_display',options)
            }, trigger)
            .done(function(modal) {
               this.modal = modal;
               this.modal.setLarge();
                modal.show();
                modal.getRoot().on(ModalEvents.hidden, function() {
                modal.destroy();
                }.bind(this));
            });
        }

        createJobrole_resp = e.target.closest(Selectors.actions. createJobrole_resp);
        if (createJobrole_resp) {
            e.preventDefault();
            const title = createJobrole_resp.getAttribute('data-id') ?
                getString('editsector', 'local_sector', createJobrole_resp.getAttribute('data-name')) :
                getString('addresp', 'local_sector');
            const form = new ModalForm({
                formClass: 'local_sector\\form\\jobrole_resp',
                args: {id: createJobrole_resp.getAttribute('data-id'),jobfamilyid: createJobrole_resp.getAttribute('data-jobfamilyid'),roleid: createJobrole_resp.getAttribute('data-roleid')},
                modalConfig: {title},
                returnFocus: createJobrole_resp,
            });
         
        
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }

        let deleteresponsibility = e.target.closest(Selectors.actions.deleteresponsibility);
        if (deleteresponsibility) {
             e.preventDefault();
             const responsibilityid = deleteresponsibility.getAttribute('data-id');
            ModalFactory.create({
                title: getString('deletelevelconfirm', 'local_sector'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('rsponsedeleteconfirm', 'local_sector')
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('deletetext', 'local_sector'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.responseid = responsibilityid;
                    var promise = Ajax.call([{
                        methodname: 'local_sector_deleteresponsibility',
                        args: params
                    }]);
                    promise[0].done(function(resp) {
                        window.location.reload(true);
                    }).fail(function() {
                        console.log('exception');
                    });
                }.bind(this));
                modal.show();
            }.bind(this));
        }

         
    });
};
