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
 * TODO describe module tpview
 *
 * @module     local_trainingprogram/tpview
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import TPDynamicForm from 'local_trainingprogram/dynamicform';
import ModalForm from 'core_form/modalform';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import Templates from 'core/templates';
import Ajax from 'core/ajax';
import homepage from 'theme_academy/homepage';
import {get_string as getString} from 'core/str';
import $ from 'jquery';

const Selectors = {
    actions: {
        viewprogramsectors: '[data-action="viewprogramsectors"]',
        currentofferings: '[data-action="currentofferings"]',
        mapcompetencies: '[data-action="mapcompetencies"]',
        competencies: '[data-action="competencies"]',
        programagenda: '[data-action="programagenda"]',
        viewprogramgoal: '[data-action="viewprogramgoal"]',
        deleteprogramgoals: '[data-action="deleteprogramgoals"]',
        addcpd: '[data-action="addcpd"]',
        edittrainigprogram: '[data-action="edittrainigprogram"]',
        deletetrainigprogram: '[data-action="deletetrainigprogram"]',
        showactivities : '[data-action="showactivities"]',
        viewprogramtopic :'[data-action="viewprogramtrainingtopics"]',
        addsections  : '[data-action="sectionsmapping"]'
    },
};

let HomePage = new homepage();
export const init = () => {
    document.addEventListener('click', function(e) {
        e.stopImmediatePropagation();
        let viewprogramsectors = e.target.closest(Selectors.actions.viewprogramsectors);
        if (viewprogramsectors) {
            const programid = viewprogramsectors.getAttribute('data-id');
            var params = {};
            params.programid = programid;
            var promise = Ajax.call([{
                methodname: 'local_trainingprogram_viewprogramsectors',
                args: params
            }]);
            promise[0].done(function(resp) {
                ModalFactory.create({
                    title: getString('viewprogramsectors', 'local_trainingprogram'),
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
        let currentofferings = e.target.closest(Selectors.actions.currentofferings);
        if (currentofferings) {
            e.stopImmediatePropagation();
            const programid = currentofferings.getAttribute('data-id');
            const sorting = currentofferings.getAttribute('data-sorting');
            var options = {};
            options.programid = programid;
            options.sorting = sorting;
            var trigger = $(Selectors.actions.currentofferings);
            ModalFactory.create({
                title: getString('currentofferings', 'local_trainingprogram'),
                body: Templates.render('local_trainingprogram/current_offerings_display',options)
            }, trigger)
            .done(function(modal) {
               this.modal = modal;
               this.modal.setLarge();
                this.modal.getRoot().addClass('currentofferingsmodel');
                modal.show();
                modal.getRoot().on(ModalEvents.hidden, function() {
                modal.destroy();
                }.bind(this));
            });
        }
        let mapcompetencies = e.target.closest(Selectors.actions.mapcompetencies);
        if (mapcompetencies) {
            e.preventDefault();
            const title = getString('mapcompetencies', 'local_trainingprogram');
            const form = new TPDynamicForm({
                formClass: 'local_trainingprogram\\form\\competenciesform',
                args: {id: mapcompetencies.getAttribute('data-id')},
                modalConfig: {title},
                returnFocus: mapcompetencies,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }
        let competencies = e.target.closest(Selectors.actions.competencies);
        if (competencies) {
            const programid = competencies.getAttribute('data-id');
            var options = {};
            options.programid = programid;
            var trigger = $(Selectors.actions.competencies);
            ModalFactory.create({
                title: getString('mapcompetencies', 'local_trainingprogram'),
                body: Templates.render('local_trainingprogram/competencies_display',options)
            }, trigger)
            .done(function(modal) {
                modal.setLarge();
                modal.show();
                modal.getRoot().on(ModalEvents.hidden, function() {
                modal.destroy();
                }.bind(this));
            });
        }
        ///Ramanjaneyulu Added
            let addcpd = e.target.closest(Selectors.actions.addcpd);
            if (addcpd) {
            const programid = addcpd.getAttribute('data-id');
            var options = {};
            options.programid = programid;
            var trigger = $(Selectors.actions.addcpd);
            ModalFactory.create({
                title: getString('addcpd', 'local_trainingprogram'),
                body: Templates.render('local_trainingprogram/addcpd_display',options)
            }, trigger)
            .done(function(modal) {
                modal.setLarge();
                modal.show();
                modal.getRoot().on(ModalEvents.hidden, function() {
                modal.destroy();
                }.bind(this));
            });
        }

           let addtrainigprogram = e.target.closest(Selectors.actions.edittrainigprogram);
           if (addtrainigprogram) {
            e.preventDefault();
            const title = getString('addcpd', 'local_trainingprogram');
            const form = new ModalForm({
                formClass: 'local_trainingprogram\\form\\cpd_form',
                args: {id: addtrainigprogram.getAttribute('data-ctpid'), prgid: addtrainigprogram.getAttribute('data-prgid')},
                modalConfig: {title},
                returnFocus: addtrainigprogram,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }

        let deletetrainigprogram = e.target.closest(Selectors.actions.deletetrainigprogram);
        if (deletetrainigprogram) {
            const programid = deletetrainigprogram.getAttribute('data-id');
            const programname = deletetrainigprogram.getAttribute('data-name');
            ModalFactory.create({
                title: getString('delete', 'local_trainingprogram'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('deleteconfirmcpd', 'local_trainingprogram', programname)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('delete_cpd', 'local_trainingprogram'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.confirm = true;
                    params.programid = programid;
                    var promise = Ajax.call([{
                        methodname: 'local_cpd_delete_training_program',
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
        ///Ramanjaneyulu Added

        let programagenda = e.target.closest(Selectors.actions.programagenda);
        if (programagenda) {
            const programname = programagenda.getAttribute('data-programname');
            const title = getString('viewprogramagenda', 'local_trainingprogram',programname);
            const form = new TPDynamicForm({
                formClass: 'local_trainingprogram\\form\\programagendaform',
                args: {programid: programagenda.getAttribute('data-programid'),programname: programagenda.getAttribute('data-programname')},
                modalConfig: {title},
                returnFocus: programagenda,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }

        let viewprogramgoal = e.target.closest(Selectors.actions.viewprogramgoal);
        if (viewprogramgoal) {
            e.stopImmediatePropagation();
            const programid = viewprogramgoal.getAttribute('data-programid');
            const programname = viewprogramgoal.getAttribute('data-name');
            var options = {};
            options.programid = programid;
            options.programname = programname;
            var trigger = $(Selectors.actions.viewprogramgoal);
            ModalFactory.create({
                title: getString('viewprogramgoal', 'local_trainingprogram',programname),
                body: Templates.render('local_trainingprogram/viewprogramgoalsdisplay',options)
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


        let deleteprogramgoals = e.target.closest(Selectors.actions.deleteprogramgoals);
        if (deleteprogramgoals) {
           // alert('hi');
             e.preventDefault();
             const responsibilityid = deleteprogramgoals.getAttribute('data-id');
            ModalFactory.create({
                title: getString('deletelevelconfirm', 'local_trainingprogram'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('rsponsedeleteconfirm', 'local_trainingprogram')
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('deletetext', 'local_trainingprogram'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.responseid = responsibilityid;
                    var promise = Ajax.call([{
                        methodname: 'deleteprogramgoals',
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

        // SUPPORT TRK164_12418

        let showactivities = e.target.closest(Selectors.actions.showactivities);
        if (showactivities) {
            const offeringcode = showactivities.getAttribute('data-code');
            const title = getString('todo', 'local_trainingprogram',offeringcode);
            const form = new TPDynamicForm({
                formClass: 'local_trainingprogram\\form\\todoactivities_form',
                args: {offeringid: showactivities.getAttribute('data-id'),offeringcode: showactivities.getAttribute('data-code'), evaluationmethods:showactivities.getAttribute('data-evaluationmethods')},
                modalConfig: {title},
                returnFocus: showactivities,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => '');
            form.show();
        }


      
        let viewprogramtopic = e.target.closest(Selectors.actions.viewprogramtopic);
   

        if (viewprogramtopic) {
           

            const programid = viewprogramtopic.getAttribute('data-id');
            var params = {};
            params.programid = programid;
            var promise = Ajax.call([{
                methodname: 'local_trainingprogram_viewprogramtopics',
                args: params
            }]);
            promise[0].done(function(resp) {
                ModalFactory.create({
                    title: getString('viewprogramtopics', 'local_trainingprogram'),
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
        //SUPPORT TRK164_12471 --renu
 
        let sections = e.target.closest(Selectors.actions.addsections);
        if (sections) {
            const offeringcode = sections.getAttribute('data-code');
            const title = getString('addsections', 'local_trainingprogram',offeringcode);
            const form = new TPDynamicForm({
                formClass: 'local_trainingprogram\\form\\addsection_form',
                args: {offeringid: sections.getAttribute('data-id'),offeringcode: sections.getAttribute('data-code')},
                modalConfig: {title},
                returnFocus: sections,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => '');
            form.show();
        }
    });
};
export const load = (tuserid, entityid, referenceid, type) => {

    if (isNaN(tuserid)) {
        var params = {};
        params.entityid = entityid;
        params.referenceid = referenceid;
        params.tuserid = tuserid;
        params.type = type;
        var promise = Ajax.call([{
            methodname: 'local_exams_get_orgorderdetails',
            args: params
        }]);
        promise[0].done(function(orderdetailsresp) {

            console.log(orderdetailsresp);

            if(orderdetailsresp.response == 'success') {

                Templates.renderForPromise('tool_product/loader', {}).then(({html, js}) => {
                    Templates.appendNodeContents('#region-main-box', html, js);
                });

                if(orderdetailsresp.hasprivateandinvoice == 1) {
                  HomePage.confirmbox(getString('privateofferingexistsinvoicemessage', 'local_trainingprogram',orderdetailsresp.existinginvoice_number));
                  setTimeout(function() {
                    window.location = M.cfg.wwwroot + '/local/trainingprogram/index.php';
                  },4000);

                } else {
                    // Org official enrolling users
                    if (orderdetailsresp.returnparams != "" && orderdetailsresp.autoapproval == 1) {
                        event.preventDefault();
                        let promise = Ajax.call([{
                            methodname: 'tool_product_postpaid_payments',
                            args: {
                                products: orderdetailsresp.returnparams
                            }
                        }]);
                        promise[0].done((response) => { 

                            var params = {};
                            params.orderid = response.paymentid;
                            var promise = Ajax.call([{
                                methodname: 'tool_product_get_orderinfo',
                                args: params
                            }]);
                            promise[0].done(function(resp) {
                                if (resp) {
                                    let promises = Ajax.call([{
                                        methodname: 'tool_product_generate_sadadbill',
                                        args: {
                                            products: resp.info
                                        }
                                    }]);
                                    promises[0].done((response) => {
                                        console.log(orderdetailsresp);
                                            // User Enrollment service(start)
                                            var enrolparams = {};
                                            enrolparams.entityid = entityid;
                                            enrolparams.referenceid = referenceid;
                                            enrolparams.type = type;
                                            enrolparams.tuserid = tuserid;
                                            enrolparams.orderid = 0;
                                            enrolparams.discountprice = orderdetailsresp.discountprice;
                                            enrolparams.discounttype = orderdetailsresp.discounttype;
                                            enrolparams.discounttableid = orderdetailsresp.discounttableid;
                                            enrolparams.autoapproval = orderdetailsresp.autoapproval;
                                            var promise = Ajax.call([{
                                                methodname: 'local_trainingprogram_org_enroluser',
                                                args: enrolparams
                                            }]);

                                            promise[0].done(function(resp) {  
                                                if (type == 'program') {
                                                    window.location = M.cfg.wwwroot + '/local/trainingprogram/index.php';
                                                } else {
                                                    window.location = M.cfg.wwwroot + '/local/events/index.php';
                                                }
                                            }).fail(function() {
                                                // do something with the exception
                                                    console.log('exception');
                                            });
                                    }).fail( (error) => {
                                        ModalFactory.create({
                                            title: '',
                                            type: ModalFactory.types.DEFAULT,
                                            body: error.message,
                                            footer: '<button type="button" class="btn btn-primary" data-action="save">Ok</button>'
                                        }).done(function(modal) {
                                            this.modal = modal;
                                            modal.getFooter().find('[data-action="save"]').on('click', function() {
                                                if (type == 'program') {
                                                    window.location = M.cfg.wwwroot + '/local/trainingprogram/programenrollment.php?programid='+entityid+'&offeringid='+referenceid;
                                                } else {
                                                    window.location = M.cfg.wwwroot + '/local/events/attendees.php?id='+entityid;
                                                }
                                                modal.hide();
                                            });
                                            modal.show();
                                        }.bind(this));
                                        // HomePage.confirmbox(error.message);
                                    });
                                }
                            }).fail( (error) => {
                                HomePage.confirmbox(error.message);
                            });
                        }).fail( (error) => {    
                            HomePage.confirmbox(error.error);
                        });
                    } else {
                        let promise = Ajax.call([{
                            methodname: 'tool_product_postpaid_payments',
                            args: {
                                products: orderdetailsresp.returnparams
                            }
                        }]);
                        promise[0].done((response) => {
                            // Please write the program enrollment service(start)
                            var enrolparams = {};
                            enrolparams.entityid = entityid;
                            enrolparams.referenceid = referenceid;
                            enrolparams.type = type;
                            enrolparams.tuserid = tuserid;
                            enrolparams.orderid = response.paymentid;
                            enrolparams.discountprice = orderdetailsresp.discountprice;
                            enrolparams.discounttype = orderdetailsresp.discounttype;
                            enrolparams.discounttableid = orderdetailsresp.discounttableid;
                            enrolparams.autoapproval = orderdetailsresp.autoapproval;
                            var promise = Ajax.call([{
                                methodname: 'local_trainingprogram_org_enroluser',
                                args: enrolparams
                            }]);

                            promise[0].done(function(resp) {  
                                console.log('successfully enrolled');
                                ModalFactory.create({
                                    title: getString('confirm', 'local_exams'),
                                    type: ModalFactory.types.DEFAULT,
                                    body: getString('ordersubmitted', 'tool_product')
                                }).done(function(modal) {
                                    this.modal = modal;
                                    modal.show();
                                    if (type == 'program') {
                                        window.location = M.cfg.wwwroot + '/local/trainingprogram/index.php';
                                    } else {
                                        window.location = M.cfg.wwwroot + '/local/events/index.php';
                                    }
                                }.bind(this));
                            }).fail(function() {
                                // do something with the exception
                                console.log('exception');
                            });

                        }).fail( (error) => {
                            HomePage.confirmbox(error.error);
                        });
                    }
               }
            } else {
                modal.hide();
                HomePage.confirmbox(resp.response);
            }

        }).fail( (error) => {
            HomePage.confirmbox(error.message);
        });
    }
}
