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

// import ModalForm from 'local_trainingprogram/dynamicform';
import Modalform from 'core_form/modalform';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import Ajax from 'core/ajax';
import Templates from 'core/templates';
import {get_string as getString} from 'core/str';
import $ from 'jquery';

const Selectors = {
    actions: {
        examschedules: '[data-action="examschedules"]',
        editschedule: '[data-action="editschedule"]',
        deleteschedule: '[data-action="deleteschedule"]',
    },
};

export const init = () => {
    document.addEventListener('click', function(e) {
        e.stopImmediatePropagation();
        let examschedules = e.target.closest(Selectors.actions.examschedules);
        if (examschedules) {
            e.preventDefault();
            const title = getString('createhallschedule', 'local_hall');
            const form = new Modalform({
                formClass: 'local_hall\\form\\schedulehallform',
                args: {entityid: examschedules.getAttribute('data-examid'), entity: 'exam'},
                modalConfig: {title},
                returnFocus: examschedules,
            });
            // form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());


            form.addEventListener(form.events.FORM_SUBMITTED, (event) => {                
                if (event.detail) {
                    var type = event.detail.type;
                    var typeid = event.detail.typeid;
                    var errors = event.detail.errors;
                    window.location = M.cfg.wwwroot + '/local/hall/schedulenotices.php?type='+type+'&typeid='+typeid+'&errors='+errors;
                } else {
                    window.location.reload();
                }
            });
            form.show();
        }

           
        let editschedule = e.target.closest(Selectors.actions.editschedule);
        if (editschedule) {
            e.preventDefault();
            const title = getString('updateschedule', 'local_exams');
            const form = new Modalform({
                formClass: 'local_hall\\form\\schedulehallform',
                args: {id: editschedule.getAttribute('data-id'), entity: 'exam'},
                modalConfig: {title},
                returnFocus: editschedule,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }
        let deleteschedule = e.target.closest(Selectors.actions.deleteschedule);
        if (deleteschedule) {
            e.preventDefault();
            let scheduleid = deleteschedule.getAttribute('data-id');
            ModalFactory.create({
                title: getString('deleteexam', 'local_exams'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('scheduledeleteconfirm', 'local_exams')
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('delete', 'local_exams'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.scheduleid = scheduleid;
                    var promise = Ajax.call([{
                        methodname: 'local_exam_delete_schedule',
                        args: params
                    }]);
                    promise[0].done(function(resp) {
                        window.location.reload();
                    }).fail(function() {
                        // do something with the exception
                         console.log('exception');
                    });
                }.bind(this));
                modal.show();
            }.bind(this));
        }
    });
};
