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

import ModalForm from 'core_form/modalform';
import ModalFactory from 'core/modal_factory';
import Ajax from 'core/ajax';
import {get_string as getString} from 'core/str';
import Templates from 'core/templates';
import ModalEvents from 'core/modal_events';
import $ from 'jquery';

const Selectors = {
    actions: {
        edittrack: '[data-action="edittracker"]',
        competencylist: '[data-action="competencylist"]',
        addlearningitems: '[data-action="addlearningitems"]',
        mapcompetencies: '[data-action="mapcompetencies"]',
        deletetrack: '[data-action="deletetrack"]',
        deleteitems: '[data-action="delete-items"]'
    },
};
export const init = () => {
    document.addEventListener('click', function(e) {
            e.stopImmediatePropagation();
            let element = e.target.closest(Selectors.actions.edittrack);
            if (element) {
                e.preventDefault();
                const title = element.getAttribute('data-id') ?
                getString('editlearningpath', 'local_learningtracks', element.getAttribute('data-name')) :
                getString('addlearningpath', 'local_learningtracks');
            const form = new ModalForm({
                formClass: 'local_learningtracks\\form\\learningtrackform',
                args: {id: element.getAttribute('data-id')},
                modalConfig: {title},
                returnFocus: element,
            });
                form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
                form.show();
            }

            let viewcompetencies = e.target.closest(Selectors.actions.competencylist);
            if (viewcompetencies) {
            const trackid = viewcompetencies.getAttribute('data-id');
            const addaction = viewcompetencies.getAttribute('data-addaction');
                var options = {};
                options.trackid = trackid;
                options.addaction = addaction;
                var trigger = $(Selectors.actions.viewcompetencies);
                ModalFactory.create({
                    title: getString('competency', 'local_learningtracks'),
                    body: Templates.render('local_learningtracks/competencies_display',options)
                }, trigger)
                .done(function(modal) {
                    modal.setLarge();
                    modal.show();
                    modal.getRoot().on(ModalEvents.hidden, function() {
                    modal.destroy();
                    }.bind(this));
                });
            }

            let addlearningitems = e.target.closest(Selectors.actions.addlearningitems);
            if (addlearningitems) {
                e.preventDefault();
                const title = getString('assignitems', 'local_learningtracks');
            const form = new ModalForm({
                formClass: 'local_learningtracks\\form\\learning_items_form',
                args: {id: addlearningitems.getAttribute('data-id'), trackid: addlearningitems.getAttribute('data-trackid')},
                modalConfig: {title},
                returnFocus: addlearningitems,
            });
                form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
                form.show();
            }
            let mapcompetencies = e.target.closest(Selectors.actions.mapcompetencies);
            if (mapcompetencies) {
                e.preventDefault();
                const title = getString('mapcompetencies', 'local_learningtracks');
                const form = new ModalForm({
                    formClass: 'local_learningtracks\\form\\competenciesform',
                    args: {id: mapcompetencies.getAttribute('data-id')},
                    modalConfig: {title},
                    returnFocus: mapcompetencies,
                });
                form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
                form.show();
            }
            let deletetrack = e.target.closest(Selectors.actions.deletetrack);
            if(deletetrack) {
                const trackid = deletetrack.getAttribute('data-id');
                const trackname = deletetrack.getAttribute('data-name');
                const usercount = deletetrack.getAttribute('data-usercount');
                if (usercount > 0) {
                    ModalFactory.create({
                        title: getString('confirmdelete', 'local_learningtracks'),
                        body: getString('userunenroll', 'local_learningtracks')
                    }).done(function(modal) {
                        modal.show();
                    }.bind(this));
                } else {
                    ModalFactory.create({
                        title: getString('confirmdelete', 'local_learningtracks'),
                        type: ModalFactory.types.SAVE_CANCEL,
                        body: getString('deleteconfirm', 'local_learningtracks', trackname)
                    }).done(function(modal) {
                        this.modal = modal;
                        modal.setSaveButtonText(getString('delete', 'local_learningtracks'));
                        modal.getRoot().on(ModalEvents.save, function(e) {
                            e.preventDefault();
                            var params = {};
                            params.confirm = true;
                            params.trackid = trackid;
                            var promise = Ajax.call([{
                                methodname: 'local_learningtracks_deletetrack',
                                args: params
                            }]);
                            promise[0].done(function() {
                               // window.location.reload(true);
                               window.location.href = M.cfg.wwwroot + '/local/learningtracks/index.php';
                            }).fail(function() {
                                // do something with the exception
                            });
                        }.bind(this));
                        modal.show();
                    }.bind(this));
                }
            }

            let deleteitems = e.target.closest(Selectors.actions.deleteitems);
            if(deleteitems) {
                const trackid = deleteitems.getAttribute('data-id');
                const trackname = deleteitems.getAttribute('data-name');
                const usercount = deleteitems.getAttribute('data-usercount');
                //if (usercount > 0) {
                    /*ModalFactory.create({
                        title: getString('confirmdelete', 'local_learningtracks'),
                        body: getString('userunenroll', 'local_learningtracks')
                    }).done(function(modal) {
                        modal.show();
                    }.bind(this));*/
               // } else {
                    ModalFactory.create({
                        title: getString('confirmdelete', 'local_learningtracks'),
                        type: ModalFactory.types.SAVE_CANCEL,
                        body: getString('deleteconfirm', 'local_learningtracks', trackname)
                    }).done(function(modal) {
                        this.modal = modal;
                        modal.setSaveButtonText(getString('delete', 'local_learningtracks'));
                        modal.getRoot().on(ModalEvents.save, function(e) {
                            e.preventDefault();
                            var params = {};
                            params.confirm = true;
                            params.trackid = trackid;
                            var promise = Ajax.call([{
                                methodname: 'local_learningtracks_deleteitems',
                                args: params
                            }]);
                            promise[0].done(function() {
                                window.location.reload(true);
                            }).fail(function() {
                                // do something with the exception
                            });
                        }.bind(this));
                        modal.show();
                    }.bind(this));
                //}
            }
    });
};
