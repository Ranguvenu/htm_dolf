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
        addgrievance: '[data-action="addgrievance"]',
        viewgrievance: '[data-action="viewgrievance"]',
        grievancestatus: '[data-action="grievancestatus"]',
        grievancedetails: '[data-action="grievancedetails"]',
        examschedules: '[data-action="examschedules"]',
    },
};

export const init = () => {
    document.addEventListener('click', function(e) {
        e.stopImmediatePropagation();
        let grievance = e.target.closest(Selectors.actions.addgrievance);
        if (grievance) {
            e.preventDefault();
            const title = getString('submitgrievance', 'local_exams');
            const form = new Modalform({
                formClass: 'local_exams\\form\\grievanceform',
                args: {examid: grievance.getAttribute('data-examid'), userid: grievance.getAttribute('data-userid'), profileid: grievance.getAttribute('data-profileid')},
                modalConfig: {title},
                returnFocus: grievance,
            });
            // form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.addEventListener(form.events.FORM_SUBMITTED, (event) => {
                Templates.render('tool_product/add_order_seats',{product_attributes:event.detail.returnparams})
                .then(function(html, js) {

                    $('[data-region="grievance-order-summary"]').html(html);

                    $('[data-region="grievance-order-summary"]').append(event.detail.returnurlbtn);

                    Templates.runTemplateJS(js);    

                    return;
                })
                .always(function() {
                    return;
                })
                .fail();

            });
            form.show();
        }

        let viewgrievance = e.target.closest(Selectors.actions.viewgrievance);
        if (viewgrievance) {
            const examid = viewgrievance.getAttribute('data-examid');
            const userid = viewgrievance.getAttribute('data-userid');
            var params = {};
            params.examid = examid;
            params.userid = userid;
            params.temp = 'grievance_list';
            params.methodName = 'local_exams_grievance_info';
            var trigger = $(Selectors.actions.viewgrievance);
            ModalFactory.create({
            title: getString('viewgrievance', 'local_exams'),
            body: Templates.render('local_exams/grievance_cardview',params).done(function(html, js) {
                        Templates.replaceNodeContents('targetgrievance', html, js);
                    })
            }, trigger)
            .done(function(modal) {
                modal.show();
                modal.setLarge();
                modal.getRoot().on(ModalEvents.hidden, function() {
                modal.hide();
                modal.destroy();
            }.bind(this));
            });
        }

        let grievancestatus = e.target.closest(Selectors.actions.grievancestatus);
        if (grievancestatus) {
            const grievanceid = grievancestatus.getAttribute('data-id');
            //const header = grievancestatus.getAttribute('data-title');
            const status = grievancestatus.getAttribute('data-status');
            if(status =="2") {
                var confirm_msg = getString('approveconfirm', 'local_exams');
                var buttonstring = getString('approve','local_exams');
            } else {
                var confirm_msg = getString('rejectconfirm', 'local_exams');
                var buttonstring = getString('reject', 'local_exams');
            }
            ModalFactory.create({
                title: '',
                type: ModalFactory.types.SAVE_CANCEL,
                body: confirm_msg
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(buttonstring);
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.confirm = true;
                    params.grievanceid = grievanceid;
                    params.status = status;
                    var promise = Ajax.call([{
                        methodname: 'local_exams_grievancestatus',
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
        }

        let grievancedetails = e.target.closest(Selectors.actions.grievancedetails);
        if (grievancedetails) {
            const greivanceid = grievancedetails.getAttribute('data-id');
            const examid = grievancedetails.getAttribute('data-examid');
            const examname = grievancedetails.getAttribute('data-name');
            var params = {};
            params.greivanceid = greivanceid;
            params.examid = examid;
            var promise = Ajax.call([{
                methodname: 'local_exams_view_grievance',
                args: params
            }]);
            promise[0].done(function(resp) {
                ModalFactory.create({
                    title: getString('view', 'local_exams', examname),
                    body: resp.options
                }).done(function(modal) {
                    this.modal = modal;
                    modal.show();
                }.bind(this));
            }).fail(function() {
                // do something with the exception
            });
        }

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



    });
};
