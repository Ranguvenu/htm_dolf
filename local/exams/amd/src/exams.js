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

import ModalForm from 'local_trainingprogram/dynamicform';
import Modalform from 'core_form/modalform';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import homepage from 'theme_academy/homepage';
import Ajax from 'core/ajax';
import Templates from 'core/templates';
import {get_string as getString} from 'core/str';
import $ from 'jquery';

const Selectors = {
    actions: {
        createexam: '[data-action="createexam"]',
        deleteexam: '[data-action="deleteexam"]',
        publishexam: '[data-action="publishexam"]',
        viewexam: '[data-action="viewexam"]',
        viewsectors: '[data-action="viewsectors"]',
        bookhall: '[data-action="bookhall"]',
        mapcompetencies: '[data-action="mapcompetencies"]',
        competencies: '[data-action="competencies"]',
        addprofile: '[data-action="addprofile"]',
        deleteprofile: '[data-action="deleteprofile"]',
        viewprofile: '[data-action="viewprofile"]',
        enrolusertoexam: '[data-action="enrolusertoexam"]',
        editattempt: '[data-action="editattempt"]',
        deleteattempt: '[data-action="deleteattempt"]',
    },
};

let HomePage = new homepage();
export const init = () => {
    document.addEventListener('click', function(e) {
        // e.stopImmediatePropagation();
        let element = e.target.closest(Selectors.actions.createexam);
        if (element) {
            e.stopImmediatePropagation();
            // e.preventDefault();
            const title = element.getAttribute('data-id') ?
                getString('updateexam', 'local_exams', element.getAttribute('data-name')) :
                getString('createexam', 'local_exams');
            const form = new ModalForm({
                formClass: 'local_exams\\form\\examsform',
                args: {id: element.getAttribute('data-id')},
                modalConfig: {title},
                returnFocus: element,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }
        let deleteexam = e.target.closest(Selectors.actions.deleteexam);
        if (deleteexam) {
            const examid = deleteexam.getAttribute('data-id');
            ModalFactory.create({
                title: getString('deleteexam', 'local_exams'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('deleteallconfirm', 'local_exams')
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('delete', 'local_exams'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.examid = examid;
                    var promise = Ajax.call([{
                        methodname: 'local_deleteexam',
                        args: params
                    }]);
                    promise[0].done(function(resp) {
                        window.location = M.cfg.wwwroot + '/local/exams/index.php';
                    }).fail(function() {
                        // do something with the exception
                         console.log('exception');
                    });
                }.bind(this));
                modal.show();
            }.bind(this));
        }
        let publishexam = e.target.closest(Selectors.actions.publishexam);
        if (publishexam) {
            const examid = publishexam.getAttribute('data-id');
            ModalFactory.create({
                title: getString('publishexam', 'local_exams'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('publishconfirmsg', 'local_exams')
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('publish', 'local_exams'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.examid = examid;
                    var promise = Ajax.call([{
                        methodname: 'local_publishexam',
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
        let viewexam = e.target.closest(Selectors.actions.viewexam);
        if (viewexam) {
            const examid = viewexam.getAttribute('data-id');
            var params = {};
            params.examid = examid;
            var promise = Ajax.call([{
                methodname: 'local_exam_info',
                args: params
            }]);
            promise[0].done(function(resp) {
                ModalFactory.create({
                    title: getString('viewexam', 'local_exams'),
                    body: resp.options
                }).done(function(modal) {
                    this.modal = modal;
                    modal.show();
                    modal.setLarge();
                }.bind(this));
            }).fail(function() {
                // do something with the exception
                 console.log('exception');
            });
        } 
        let viewsectors = e.target.closest(Selectors.actions.viewsectors);
        if (viewsectors) {
            const examid = viewsectors.getAttribute('data-id');
            var params = {};
            params.examid = examid;
            var promise = Ajax.call([{
                methodname: 'local_sectors_info',
                args: params
            }]);
            promise[0].done(function(resp) {
                ModalFactory.create({
                    title: getString('viewsectors', 'local_exams'),
                    body: resp.options
                }).done(function(modal) {
                    this.modal = modal;
                    modal.show();
                }.bind(this));
            }).fail(function() {
                // do something with the exception
                 console.log('exception');
            });
        }
        let bookhall = e.target.closest(Selectors.actions.bookhall);
        if (bookhall) {
            e.preventDefault();
            const title = bookhall.getAttribute('data-id');
            const form = new ModalForm({
                formClass: 'local_exams\\form\\bookhallform',
                args: {id: bookhall.getAttribute('data-id')},
                modalConfig: {title},
                returnFocus: bookhall,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }
        let mapcompetencies = e.target.closest(Selectors.actions.mapcompetencies);
        if (mapcompetencies) {
            e.stopImmediatePropagation();
            // e.preventDefault();
            const title = getString('mapcompetencies', 'local_exams');
            const form = new ModalForm({
                formClass: 'local_exams\\form\\competenciesform',
                args: {id: mapcompetencies.getAttribute('data-id'), type: mapcompetencies.getAttribute('data-type')},
                modalConfig: {title},
                returnFocus: mapcompetencies,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }
        let competencies = e.target.closest(Selectors.actions.competencies);
        if (competencies) {
            e.stopImmediatePropagation();
            const typeid = competencies.getAttribute('data-id');
            const type = competencies.getAttribute('data-type');
            var options = {};
            options.typeid = typeid;
            options.type = type;
            options.hallid = 0;
            options.examdate = 0;
            options.temp = 'competencies_view';
            options.methodName = 'local_competencies_info';
            var trigger = $(Selectors.actions.competencies);
            ModalFactory.create({
            title: getString('mapcompetencies', 'local_exams'),
            body: Templates.render('local_exams/testing',options).done(function(html, js) {
                                        Templates.replaceNodeContents('targetcompetencypc', html, js);
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
        let addprofile = e.target.closest(Selectors.actions.addprofile);
        if (addprofile) {
            //e.stopImmediatePropagation();
            // e.preventDefault();
            const title = addprofile.getAttribute('data-id') ?
                getString('updateexamprofile', 'local_exams', addprofile.getAttribute('data-name')) :
                getString('createexamprofile', 'local_exams');
            const form = new ModalForm({
                formClass: 'local_exams\\form\\examprofiles',
                args: {id: addprofile.getAttribute('data-id'), examid: addprofile.getAttribute('data-examid')},
                modalConfig: {title},
                returnFocus: addprofile,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }
        let deleteprofile = e.target.closest(Selectors.actions.deleteprofile);
        if (deleteprofile) {
            const profileid = deleteprofile.getAttribute('data-profileid');
            ModalFactory.create({
                title: getString('deleteprofile', 'local_exams'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('deleteprofileconfirm', 'local_exams')
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('delete', 'local_exams'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.id = profileid;
                    var promise = Ajax.call([{
                        methodname: 'local_deleteprofile',
                        args: params
                    }]);
                    promise[0].done(function(resp) {
                        window.location = M.cfg.wwwroot + '/local/exams/examdetails.php?id='+deleteprofile.getAttribute('data-examid');
                    }).fail(function() {
                        // do something with the exception
                         console.log('exception');
                    });
                }.bind(this));
                modal.show();
            }.bind(this));
        }
        let viewprofile = e.target.closest(Selectors.actions.viewprofile);
        if (viewprofile) {
            e.stopImmediatePropagation();
            const profileid = viewprofile.getAttribute('data-id');
            var params = {};
            params.profileid = profileid;
            var promise = Ajax.call([{
                methodname: 'local_exam_profileinfo',
                args: params
            }]);
            promise[0].done(function(resp) {
                ModalFactory.create({
                    title: getString('viewprofile', 'local_exams'),
                    body: resp.options
                }).done(function(modal) {
                    this.modal = modal;
                    modal.show();
                    modal.setLarge();
                }.bind(this));
            }).fail(function() {
                // do something with the exception
                 console.log('exception');
            });
        }

        let enrolusertoexam = e.target.closest(Selectors.actions.enrolusertoexam);
        if (enrolusertoexam) {
            const examid = enrolusertoexam.getAttribute('data-examid');
            const profileid = enrolusertoexam.getAttribute('data-profileid');
            const scheduleid = enrolusertoexam.getAttribute('data-scheduleid');
            const type = enrolusertoexam.getAttribute('data-type');
            const tuserid = enrolusertoexam.getAttribute('data-tuserid');
            const damount = enrolusertoexam.getAttribute('data-damount');
            const productid = enrolusertoexam.getAttribute('data-productid');
            const organization = enrolusertoexam.getAttribute('data-organization');
            const orgofficial = enrolusertoexam.getAttribute('data-orgofficial');
            ModalFactory.create({
                title: '',
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('enrolconfirm', 'local_exams')
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('confirm', 'local_exams'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();

                    Templates.renderForPromise('tool_product/loader', {}).then(({html, js}) => {
                        Templates.appendNodeContents('.modal-content', html, js);
                    });

                    if (isNaN(tuserid) && type == 'bulkenrollment') { // Organizational official bulk enrolling users(Bulk reschedule) to exam
                        var enrolparams = {};
                        enrolparams.examid = examid;
                        enrolparams.profileid = profileid;
                        enrolparams.scheduleid = scheduleid;
                        enrolparams.type = type;
                        enrolparams.tuserid = tuserid;
                        enrolparams.orderid = 0;
                        enrolparams.productid = productid;
                        enrolparams.organization = organization;
                        
                        var promise = Ajax.call([{
                            methodname: 'local_exam_enrouser',
                            args: enrolparams
                        }]);
                        promise[0].done(function(resp) {  
                            if(resp.response == 'success') {
                                if(type == 'bulkenrollment') {
                                    window.location = M.cfg.wwwroot + '/local/exams/enrollmentconfirmation.php?examid='+examid+'&profileid='+profileid+'&scheduleid='+scheduleid+'&cusers='+tuserid+'&organization='+organization+'&orgofficial='+orgofficial;
                                }                                
                            } else {
                                modal.hide();
                                HomePage.confirmbox(resp.response);
                            }
                        }).fail(function() {
                            // do something with the exception
                             console.log('exception');
                        });

                    } else if(isNaN(tuserid) && type != 'bulkenrollment') { // Organizational official enrolling users to exam
                        var params = {};
                        params.entityid = examid;
                        params.referenceid = profileid;
                        params.tuserid = tuserid;
                        params.type = 'exam';
                        var promise = Ajax.call([{
                            methodname: 'local_exams_get_orgorderdetails',
                            args: params
                        }]);
                        promise[0].done(function(orgorderdetailsresp) {

                            if(orgorderdetailsresp.response == 'success') {
                                // Org official enrolling users
                                if (orgorderdetailsresp.returnparams != "" && orgorderdetailsresp.autoapproval == 1) {
                                    event.preventDefault();
                                    let promise = Ajax.call([{
                                        methodname: 'tool_product_postpaid_payments',
                                        args: {
                                            products: orgorderdetailsresp.returnparams
                                        }
                                    }]);
                                    promise[0].done((response) => { 
                                        var params = {};
                                        const p_orderid = response.paymentid; 
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
                                                    var enrolparams = {};
                                                    enrolparams.examid = examid;
                                                    enrolparams.profileid = profileid;
                                                    enrolparams.scheduleid = scheduleid;
                                                    enrolparams.type = type;
                                                    enrolparams.tuserid = tuserid;
                                                    enrolparams.orderid = p_orderid;
                                                    enrolparams.productid = productid;
                                                    enrolparams.organization = organization;
                                                    enrolparams.discountprice = orgorderdetailsresp.discountprice;
                                                    enrolparams.discounttype = orgorderdetailsresp.discounttype;
                                                    enrolparams.discounttableid = orgorderdetailsresp.discounttableid;
                                                    enrolparams.autoapproval = orgorderdetailsresp.autoapproval;
                                                                            
                                                    var promise = Ajax.call([{
                                                        methodname: 'local_exam_enrouser',
                                                        args: enrolparams
                                                    }]);
                                                    promise[0].done(function(resp) {
                                                        if(resp.response == 'success') {
                                                            window.location = M.cfg.wwwroot + '/local/exams/index.php';
                                                        } else {
                                                            var promise = Ajax.call([{
                                                                methodname: 'local_exam_revert_enroluser',
                                                                args: enrolparams
                                                            }]);
                                                            modal.hide();
                                                            HomePage.confirmbox(resp.response);
                                                        }
                                                    }).fail(function() {
                                                        // do something with the exception
                                                            console.log('exception');
                                                    });
                                                }).fail( (error) => {
                                                     modal.hide();
                                                    HomePage.confirmbox(error.message);
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
                                            products: orgorderdetailsresp.returnparams
                                        }
                                    }]);
                                    promise[0].done((response) => {
                                        if(response.success == true) {
                                            var enrolparams = {};
                                            enrolparams.examid = examid;
                                            enrolparams.profileid = profileid;
                                            enrolparams.scheduleid = scheduleid;
                                            enrolparams.type = type;
                                            enrolparams.tuserid = tuserid;
                                            enrolparams.orderid = response.paymentid;
                                            enrolparams.productid = productid;
                                            enrolparams.organization = organization;
                                            enrolparams.discountprice = orgorderdetailsresp.discountprice;
                                            enrolparams.discounttype = orgorderdetailsresp.discounttype;
                                            enrolparams.discounttableid = orgorderdetailsresp.discounttableid;
                                            enrolparams.autoapproval = orgorderdetailsresp.autoapproval;
                                            var promise = Ajax.call([{
                                                methodname: 'local_exam_enrouser',
                                                args: enrolparams
                                            }]);
                                            promise[0].done(function(resp) {
                                                if(resp.response == 'success') { 
                                                    ModalFactory.create({
                                                        title: getString('confirm', 'local_exams'),
                                                        type: ModalFactory.types.DEFAULT,
                                                        body: getString('ordersubmitted', 'tool_product')
                                                    }).done(function(modal) {
                                                        this.modal = modal;
                                                        e.preventDefault();
                                                        modal.show();
                                                        window.location = M.cfg.wwwroot + '/local/exams/index.php';
                                                    }.bind(this));
                                                } else {
                                                    var promise = Ajax.call([{
                                                        methodname: 'local_exam_revert_enroluser',
                                                        args: enrolparams
                                                    }]);
                                                    modal.hide();
                                                    HomePage.confirmbox(resp.response);
                                                }
                                            }).fail( (error) => {
                                                var promise = Ajax.call([{
                                                    methodname: 'local_exam_revert_enroluser',
                                                    args: enrolparams
                                                }]);
                                                HomePage.confirmbox(error.message);
                                            });
                                        }
                                    }).fail(function() {
                                        // do something with the exception
                                            console.log('exception');
                                    });
                                }
                            } else {
                                modal.hide();
                                HomePage.confirmbox(resp.response);
                            }

                        }).fail( (error) => {
                            HomePage.confirmbox(error.message);
                        });

                    } else if(damount > 0) {
                        var params = {};
                        params.examid = examid;
                        params.profileid = profileid;
                        params.scheduleid = scheduleid;
                        params.type = type;
                        params.tuserid = tuserid;
                        params.orderid = 0;
                        params.productid = productid;
                        params.organization = organization;
                        var promise = Ajax.call([{
                            methodname: 'local_exam_enrouser',
                            args: params
                        }]);
                        promise[0].done(function(resp) {  
                            if(resp.response == 'success') {
                                let products = {};
                                products.userid = tuserid;
                                products.productid = productid;
                                products.deductamount = damount;
                                products.scheduleid = scheduleid;
                                var promise = Ajax.call([{
                                    methodname: 'local_exams_rescheduleuser',
                                    args: products
                                }]);
                                promise[0].done(function(resp) {
                                    window.location = M.cfg.wwwroot + '/local/exams/index.php';
                                }).fail(function(err) {
                                    HomePage.confirmbox(err.message);
                                    //console.log('exception');
                                });
                            } else {
                                modal.hide();
                                HomePage.confirmbox(resp.response);
                            }
                        }).fail(function() {
                            // do something with the exception
                             console.log('exception');
                        }); 

                    } else {
                        var params = {};
                        params.examid = examid;
                        params.profileid = profileid;
                        params.scheduleid = scheduleid;
                        params.type = type;
                        params.tuserid = tuserid;
                        params.orderid = 0;
                        params.productid = productid;
                        params.organization = organization;
                        var promise = Ajax.call([{
                            methodname: 'local_exam_enrouser',
                            args: params
                        }]);
                        promise[0].done(function(resp) {  
                            if(resp.response == 'success') {
                                window.location = M.cfg.wwwroot + '/local/exams/index.php';
                            } else {
                                modal.hide();
                                HomePage.confirmbox(resp.response);
                            }                      
                           
                        }).fail(function() {
                            // do something with the exception
                             console.log('exception');
                        }); 
                    }
                }.bind(this));
                modal.show();
            }.bind(this));
        }
        let editattempt = e.target.closest(Selectors.actions.editattempt);
        if (editattempt) {
            e.stopImmediatePropagation();
            const title = editattempt.getAttribute('data-id') ?
                getString('updateattempt', 'local_exams') :
                getString('addattempt', 'local_exams');
            const form = new Modalform({
                formClass: 'local_exams\\form\\attemptform',
                args: {id: editattempt.getAttribute('data-id'), examid: editattempt.getAttribute('data-examid')},
                modalConfig: {title},
                returnFocus: editattempt,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }
        let deleteattempt = e.target.closest(Selectors.actions.deleteattempt);
        if (deleteattempt) {
            e.stopImmediatePropagation();
            const attemptid = deleteattempt.getAttribute('data-id');
            const examid = deleteattempt.getAttribute('data-examid');
            ModalFactory.create({
                title: getString('deleteattempt', 'local_exams'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('deleteattemptconfirm', 'local_exams')
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('delete', 'local_exams'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.attemptid = attemptid;
                    var promise = Ajax.call([{
                        methodname: 'local_exam_deleteattempt',
                        args: params
                    }]);
                    promise[0].done(function(resp) {
                        if (resp) {
                            window.location = M.cfg.wwwroot + '/local/exams/examattempts.php?id='+examid;
                        } else {
                            HomePage.confirmbox(getString('attemptpurchasesavailable', 'local_exams'))
                            modal.destroy();
                        }
                    }).fail(function() {
                        // do something with the exception
                         console.log('exception');
                    });
                }.bind(this));
                modal.show();
            }.bind(this));
        } 

        
    });

    $('.profilelanguage').on('change', function() {
        var language = $(this).val();
        var product = $(this).attr('data-product');
        $(".select_btn").attr("data-language", language);
        $("#global_filter").attr("data-examvariation", product);
    });
};
export const load = (args) => {
    console.log(args);
    $("#global_filter").attr("data-examvariation", args);
}
