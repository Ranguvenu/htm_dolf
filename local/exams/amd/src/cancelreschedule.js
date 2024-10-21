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
import Fragment from 'core/fragment';
import Ajax from 'core/ajax';
import Templates from 'core/templates';
import {get_string as getString} from 'core/str';
import $ from 'jquery';

const Selectors = {
    actions: {
        cancelexam: '[data-action="cancelexam"]',
        rescheduleexam: '[data-action="rescheduleexam"]',
        canceluser: '[data-action="canceluser"]',
        trescheduleexam: '[data-action="trescheduleexam"]',
    },
};
const render_template = (template, selector, params, append = false) => {
	if(!append){
		$(selector).empty();
	}
	Templates.renderForPromise(template, params).then(({html, js}) => {
		Templates.appendNodeContents(selector, html, js);
	});	
}
let HomePage = new homepage();
export const init = () => {
    document.addEventListener('click', function(e) {
        // e.stopImmediatePropagation();
        let cancelexam = e.target.closest(Selectors.actions.cancelexam);
        if (cancelexam) {
            e.stopImmediatePropagation();
            const productid = cancelexam.getAttribute('data-productid');
            const userid = cancelexam.getAttribute('data-currentuser');
            const entitytype = cancelexam.getAttribute('data-entitytype');
            const refundtype = cancelexam.getAttribute('data-refundtype');
            const ownedby = cancelexam.getAttribute('data-ownedby');
            
            ModalFactory.create({
                title: getString('cancelexam', 'local_exams'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('cancelexamconfirm', 'local_exams')
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('confirm', 'local_exams'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    Templates.renderForPromise('tool_product/loader', {}).then(({html, js}) => {
                        Templates.appendNodeContents('.modal-content', html, js);
                    });
                    if(ownedby == 'CISI') {
                        modal.hide();
                        HomePage.confirmbox(getString('cannotcancelexam', 'local_exams'));
                    } else { 

                        var parameters = {};
                        parameters.productid = productid;
                        parameters.userid = userid;
                        parameters.entitytype = entitytype;
                        parameters.refundtype = refundtype;
                        parameters.policyconfirm = 1;
                        var promise = Ajax.call([{
                            methodname: 'local_exams_cancelexamcalc',
                            args: parameters
                        }]);
                        promise[0].done(function(resp) {
                            var data = {};
                            data.refundamount = resp.refundamount;
                            data.deductamount = resp.deductamount;
                            data.productprice = resp.productprice;
                            data.isorgofficial = resp.isorgofficial;
                            console.log(data);
                            if(resp.isorgofficial == 1) {
                                if(resp.productprice == 0){
                                    var ceparams = {};
                                    ceparams.userid = userid;
                                    ceparams.productid=productid;
                                    ceparams.examprice = 1;
                                    ceparams.amount = resp.productprice;
                                    ceparams.refundamount = resp.refundamount;
                                    ceparams.newamount =resp.newamount;
                                    ceparams.newinvoiceamount =0;
                                    ceparams.newamount =0;
                                    ceparams.examdate=resp.examdate;
                                    ceparams.policyconfirm = 1;
                                    ceparams.invoicenumber=0;
                                    ceparams.entitytype=entitytype;

                                    var promise = Ajax.call([{
                                        methodname: 'local_exams_canceluser',
                                        args: ceparams
                                    }]);
                                    promise[0].done(function(resp) {
                                        
                                        if(resp.response == 'success') {
                                            window.location.href = resp.returnurl;
                                        } else {
                                            modal.hide();
                                            HomePage.confirmbox(resp.response);
                                        }
                                    }).fail(function() {
                                        console.log('exception');
                                    });
                                }else if ( resp.newinvoiceamount < 50.00  && resp.seats > 0) {
                                    HomePage.confirmbox(getString('invoiceamountcantlower', 'local_exams'));
                                }else if(resp){
                                    ModalFactory.create({
                                        title: getString('cancel', 'local_exams'),
                                        type: ModalFactory.types.SAVE_CANCEL,
                                        body: getString('traineegenerateinvoiceconfirmation', 'local_exams', resp)
                                    }).done(function(modal) {
                                        this.modal = modal;
                                        modal.setSaveButtonText(getString('confirm', 'local_exams'));
                                        modal.getRoot().on(ModalEvents.save, function(e) {
                                            e.preventDefault();
                                            Templates.renderForPromise('tool_product/loader', {}).then(({html, js}) => {
                                                Templates.appendNodeContents('.modal-content', html, js);
                                            });
                                            e.stopImmediatePropagation();
                                            $(e.target).attr('disabled', true);
                                            var ceparams = {};
                                            ceparams.userid = userid;
                                            ceparams.productid=productid;
                                            ceparams.examprice = 1;
                                            ceparams.amount = resp.productprice;
                                            ceparams.refundamount = resp.refundamount;
                                            ceparams.newinvoiceamount =resp.newinvoiceamount;
                                            ceparams.newamount =resp.newamount;
                                            ceparams.examdate=resp.examdate;
                                            ceparams.policyconfirm = 1;
                                            ceparams.invoicenumber=resp.invoicenumber;
                                            ceparams.entitytype=entitytype;

                                            var promise = Ajax.call([{
                                                methodname: 'local_exams_canceluser',
                                                args: ceparams
                                            }]);
                                            promise[0].done(function(resp) {
                                                 
                                                if(resp.response == 'success') {
                                                    window.location.href = resp.returnurl;
                                                } else {
                                                    modal.hide();
                                                    HomePage.confirmbox(resp.response);
                                                }
                                            }).fail(function() {
                                                console.log('exception');
                                            });

                                            
                                        }.bind(this));
                                        modal.show();
                                    }.bind(this));
                                }
                            } else {
                                if(resp.cannotcancel == '') {
                                    HomePage.confirmbox(getString('cannotcancelexam', 'local_exams'));
                                    modal.destroy();
                                }else if (resp.productprice == 0) {
                                    var ceparams = {};
                                    ceparams.userid = userid;
                                    ceparams.productid=productid;
                                    ceparams.examprice = 1;
                                    ceparams.amount = resp.productprice;
                                    ceparams.refundamount = resp.refundamount;
                                    ceparams.newamount =resp.newamount;
                                    ceparams.newinvoiceamount =0;
                                    ceparams.newamount =0;
                                    ceparams.examdate=resp.examdate;
                                    ceparams.policyconfirm = 1;
                                    ceparams.invoicenumber=0;
                                    ceparams.entitytype=entitytype;

                                    var promise = Ajax.call([{
                                        methodname: 'local_exams_canceluser',
                                        args: ceparams
                                    }]);
                                    promise[0].done(function(resp) {
                                        if(resp.response == 'success') {
                                            window.location.href = resp.returnurl;
                                        } else {
                                            modal.hide();
                                            HomePage.confirmbox(resp.response);
                                        }
                                    }).fail(function() {
                                        console.log('exception');
                                    });
                                }else if(resp){
                                    ModalFactory.create({
                                        title: getString('refundpolicies', 'local_exams'),
                                        type: ModalFactory.types.SAVE_CANCEL,
                                        body: getString('refundamountconfirm', 'local_exams', data)
                                    }).done(function(modal) {
                                        this.modal = modal;
                                        modal.setSaveButtonText(getString('confirm', 'local_exams'));
                                        modal.getRoot().on(ModalEvents.save, function(e) {
                                            e.preventDefault();
                                            Templates.renderForPromise('tool_product/loader', {}).then(({html, js}) => {
                                                Templates.appendNodeContents('.modal-content', html, js);
                                            });
                                            e.stopImmediatePropagation();
                                            $(e.target).attr('disabled', true);
                                            var ceparams = {};
                                            ceparams.userid = userid;
                                            ceparams.productid=productid;
                                            ceparams.examprice = 1;
                                            ceparams.amount = resp.productprice;
                                            ceparams.refundamount = resp.refundamount;
                                            ceparams.newinvoiceamount =0;
                                            ceparams.newamount =0;
                                            ceparams.examdate=resp.examdate;
                                            ceparams.policyconfirm = 1;
                                            ceparams.invoicenumber=0;
                                            ceparams.entitytype=entitytype;

                                            var promise = Ajax.call([{
                                                methodname: 'local_exams_canceluser',
                                                args: ceparams
                                            }]);
                                            promise[0].done(function(resp) {
                                                if(resp.response == 'success') {
                                                    window.location.href = resp.returnurl;
                                                } else {
                                                    modal.hide();
                                                    HomePage.confirmbox(resp.response);
                                                }
                                            }).fail(function() {
                                                console.log('exception');
                                            });
                                        }.bind(this));
                                        modal.show();
                                    }.bind(this));
                                }
                            }    
                        }).fail(function(err) {
                            HomePage.confirmbox(err.message);
                            //console.log('exception');
                        });
                    }                    
                }.bind(this));
                modal.show();
            }.bind(this));
        }
        let rescheduleexam = e.target.closest(Selectors.actions.rescheduleexam);
        if (rescheduleexam) {
            const examid = rescheduleexam.getAttribute('data-examid');
            const productid = rescheduleexam.getAttribute('data-productid');
            const userid = rescheduleexam.getAttribute('data-currentuser');
            const entitytype = rescheduleexam.getAttribute('data-entitytype');
            const refundtype = rescheduleexam.getAttribute('data-refundtype');
            const ownedby = rescheduleexam.getAttribute('data-ownedby');
            const currentuserorgoff = rescheduleexam.getAttribute('data-currentuserorgoff');
            const enrolledrole = rescheduleexam.getAttribute('data-enrolledrole');
            const examdate = rescheduleexam.getAttribute('data-examdate');
            const profileid = rescheduleexam.getAttribute('data-profileid');
            const enrolltype = rescheduleexam.getAttribute('data-enrolltype');
            
            ModalFactory.create({
                title: getString('rescheduleexam', 'local_exams'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('rescheduleconfirm', 'local_exams')
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('reschedule', 'local_exams'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    Templates.renderForPromise('tool_product/loader', {}).then(({html, js}) => {
                        Templates.appendNodeContents('.modal-content', html, js);
                    });
                    var params = {};
                    params.productid = productid;
                    params.userid = userid;
                    params.entitytype = entitytype;
                    params.refundtype = refundtype;
                    params.policyconfirm = 1;
                    params.enrolltype = enrolltype;
                    var promise = Ajax.call([{
                        methodname: 'local_exams_cancelexamcalc',
                        args: params
                    }]);
                    promise[0].done(function(resp) {
                        console.log(resp);
                        var data = {};
                        data.refundamount = resp.refundamount;
                        data.deductamount = resp.deductamount;
                        data.productprice = resp.productprice;
                        data.isorgofficial = resp.isorgofficial;
                        data.contextid = resp.contextid;
                        if(enrolltype == 2 || (resp.isorgofficial == 1 && resp.loginuseradmin!=1)) {
                            if (resp.newinvoiceamount < 50.00 && resp.seats > 0) {
                                HomePage.confirmbox(getString('invoiceamountcantlower', 'local_exams'));
                            }else if(resp){
                                ModalFactory.create({
                                    title: getString('reschedule', 'local_exams'),
                                    type: ModalFactory.types.SAVE_CANCEL,
                                    body: getString('orgofficialrescheduledetails', 'local_exams', resp)
                                }).done(function(modal) {
                                    this.modal = modal;
                                    modal.setSaveButtonText(getString('confirm', 'local_exams'));
                                    modal.getRoot().on(ModalEvents.save, function(e) {
                                        window.location = M.cfg.wwwroot + '/local/exams/hallschedule.php?examid='+resp.entityid+'&profileid='+resp.referenceid+'&tuserid='+resp.tuserid+'&type=reschedule&damount='+ resp.deductamount;
                                    }.bind(this));
                                    modal.show();
                                }.bind(this));
                            }
                        } else {
                            if (resp.productprice == 0) {
                                window.location = M.cfg.wwwroot + '/local/exams/index.php';
                            }else if(resp.loginuseradmin==1) {
                                modal.hide();
                                const title = getString('reasonreschedule', 'local_exams');
                                const form = new Modalform({
                                    formClass: 'local_exams\\form\\reasonform',
                                    args: {entitytype: entitytype, userid: userid, productid: productid},
                                    modalConfig: {title},
                                    returnFocus: canceluser,
                                });
                                form.addEventListener(form.events.FORM_SUBMITTED, (event) => {
                                    const data = event.detail;
                                    console.log(data.policy);
                                    if(data.policy>0) {
                                        var policyconfirm = 1;
                                        var canceldataparams = {};
                                        canceldataparams.productid = productid;
                                        canceldataparams.userid = userid;
                                        canceldataparams.entitytype = entitytype;
                                        canceldataparams.refundtype = refundtype;
                                        canceldataparams.policyconfirm = policyconfirm;
                                        canceldataparams.enrolltype = enrolltype;
                                        var promise = Ajax.call([{
                                            methodname: 'local_exams_cancelexamcalc',
                                            args: canceldataparams
                                        }]);
                                        promise[0].done(function(resp) {
                                            var responsedata = {};
                                            responsedata.amount = resp.productprice;
                                            responsedata.refundamount = resp.refundamount;
                                            responsedata.newinvoiceamount = resp.newinvoiceamount;
                                            responsedata.newamount =resp.newamount;
                                            responsedata.invoicenumber = resp.invoicenumber;
                                            responsedata.productid = resp.productid;
                                            responsedata.orgofficialenrolled = resp.orgofficialenrolled;
                                            responsedata.deductamount = resp.deductamount;
            
                                            if (resp.newinvoiceamount < 50.00 && resp.seats > 0) {
                                                HomePage.confirmbox(getString('invoiceamountcantlower', 'local_exams'));
                                            } else {
                                                ModalFactory.create({                               
                                                    type: ModalFactory.types.SAVE_CANCEL,
                                                    body: getString('orgofficialrescheduledetails', 'local_exams',responsedata)
                                                }).done(function(modal) {
                                                    this.modal = modal;
                                                    modal.setSaveButtonText(getString('confirm', 'local_exams'));
                                                    modal.getRoot().on(ModalEvents.save, function(e) {
                                                        e.preventDefault();
                                                        window.location = M.cfg.wwwroot + '/local/exams/hallschedule.php?examid='+resp.entityid+'&profileid='+resp.referenceid+'&tuserid='+resp.tuserid+'&type=reschedule&damount='+ resp.deductamount;
                                                    }.bind(this));
                                                    modal.show();
                                                }.bind(this));
                                            }    
                                        }).fail(function(err) {
                                            HomePage.confirmbox(err.message);
                                        });
                                    } else {
                                        var policyconfirm = 0;
                                        window.location = M.cfg.wwwroot + '/local/exams/hallschedule.php?examid='+examid+'&profileid='+profileid+'&tuserid='+userid+'&type=reschedule';
                                    }
                                });
                                form.show();                                    
                            }else if(resp){
                                ModalFactory.create({
                                    title: getString('reschedule', 'local_exams'),
                                    type: ModalFactory.types.SAVE_CANCEL,
                                    body: getString('rescheduledetails', 'local_exams', resp)
                                }).done(function(modal) {
                                    this.modal = modal;
                                    modal.setSaveButtonText(getString('confirm', 'local_exams'));
                                    modal.getRoot().on(ModalEvents.save, function(e) {
                                        e.preventDefault();
                                        Templates.renderForPromise('tool_product/loader', {}).then(({html, js}) => {
                                            Templates.appendNodeContents('.modal-content', html, js);
                                        });
                                        e.stopImmediatePropagation();
                                        $(e.target).attr('disabled', true);
                                        var damount = btoa(data.deductamount);
                                        window.location = M.cfg.wwwroot + '/local/exams/hallschedule.php?examid='+examid+'&profileid='+profileid+'&tuserid='+userid+'&type=reschedule&status=en&damount='+damount;
                                    }.bind(this));
                                    modal.show();
                                }.bind(this));
                            }
                        }
                    }).fail(function(err) {
                        HomePage.confirmbox(err.message);
                        //console.log('exception');
                    });
                }.bind(this));
                modal.show();
            }.bind(this));
        } 
        let canceluser = e.target.closest(Selectors.actions.canceluser);
        if (canceluser) {
            e.preventDefault();
            e.stopImmediatePropagation()
            
            const userid = canceluser.getAttribute('data-tobecancelleduserid');
            const username = canceluser.getAttribute('data-username');
            const examname = canceluser.getAttribute('data-examname');
            const examprice = canceluser.getAttribute('data-examprice');        
            const examdate = canceluser.getAttribute('data-examdate');
            const entitytype = canceluser.getAttribute('data-entitytype');
            const ownedby = canceluser.getAttribute('data-ownedby');
            const enrolledrole = canceluser.getAttribute('data-enrolledrole');
            const productid = canceluser.getAttribute('data-productid');
            const currentuserorgoff = canceluser.getAttribute('data-currentuserorgoff');
            const enrolltype = canceluser.getAttribute('data-enrolltype');
            const cangenerateinvoice = canceluser.getAttribute('data-cangenerateinvoice');
            
            var displayparams = {};
          
            displayparams.userid = userid;
            displayparams.username = username;
            displayparams.examname = examname;
            displayparams.examprice = examprice;
            ModalFactory.create({
                title: getString('cancelconfirm', 'local_exams'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('cancelconfirmmessage', 'local_exams', displayparams)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('yes', 'local_exams'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    if(ownedby == 'CISI') {
                        modal.hide();
                        HomePage.confirmbox(getString('cannotcancelexam', 'local_exams'));
                    } else { 
                        var params = {};
                        if(examprice == 0 || cangenerateinvoice == 0) {
                            
                            params.userid = userid;
                            params.examprice = 0;
                            params.amount = 0;
                            params.refundamount = 0;
                            params.newinvoiceamount = 0;
                            params.newamount =0;
                            params.productid=productid;
                            params.examdate=examdate;
                            params.policyconfirm=1;
                            params.invoicenumber=0;
                            params.entitytype=entitytype;
                            params.enrolltype=enrolltype;
                            params.cangenerateinvoice=cangenerateinvoice;
                            var promise = Ajax.call([{
                                methodname: 'local_exams_canceluser',
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
                                //console.log('exception');
                            });
                        } else {

                            if (enrolledrole == 'admin') {

                                if(enrolltype == 2) {


                                    if(currentuserorgoff == 1) {

                                        var canceldataparams = {};
                                        canceldataparams.productid = productid;
                                        canceldataparams.entitytype = entitytype;
                                        canceldataparams.userid = userid;
                                        canceldataparams.refundtype = 'cancel';
                                        canceldataparams.policyconfirm = 1;
                                        canceldataparams.enrolltype=enrolltype;
                                        
                                        var promise = Ajax.call([{
                                            methodname: 'local_exams_cancelexamcalc',
                                            args: canceldataparams
                                        }]);
                                        promise[0].done(function(resp) {
                                            var responsedata = {};
                                            responsedata.productprice = resp.productprice;
                                            responsedata.refundamount = resp.refundamount;
                                            responsedata.newinvoiceamount = resp.newinvoiceamount;
                                            responsedata.newamount = resp.newamount;
                                            responsedata.invoicenumber = resp.invoicenumber;
                                            responsedata.productid = resp.productid;
                                            responsedata.orgofficialenrolled = resp.orgofficialenrolled;
                                            responsedata.deductamount = resp.deductamount;
                                            if(responsedata.refundamount==0) {
                                                ModalFactory.create({                               
                                                    type: ModalFactory.types.SAVE_CANCEL,
                                                    body: getString('generateinvoiceconfirmation', 'local_exams',responsedata)
                                                }).done(function(modal) {
                                                    
                                                    this.modal = modal;
                                                    modal.setSaveButtonText(getString('confirm', 'local_exams'));
                                                    modal.getRoot().on(ModalEvents.save, function(e) {
                                                        e.preventDefault();
                                                        Templates.renderForPromise('tool_product/loader', {}).then(({html, js}) => {
                                                            Templates.appendNodeContents('.modal-content', html, js);
                                                        });
                                                        e.stopImmediatePropagation();
    
                                                        $(e.target).attr('disabled', true);
                                                        var ceparams = {};
                                                        
                                                        ceparams.userid = userid;
                                                        ceparams.productid=productid;
                                                        ceparams.examprice = 0;
                                                        ceparams.amount = resp.productprice;
                                                        ceparams.refundamount = 0;
                                                        ceparams.newinvoiceamount =0;
                                                        ceparams.newamount =0;
                                                        ceparams.examdate=resp.examdate;
                                                        ceparams.policyconfirm = 1;
                                                        ceparams.invoicenumber=resp.invoicenumber;
                                                        ceparams.entitytype=entitytype;
                                                        ceparams.enrolltype=enrolltype;
                                                        ceparams.cangenerateinvoice=cangenerateinvoice;
                                                        var promise = Ajax.call([{
                                                            methodname: 'local_exams_canceluser',
                                                            args: ceparams
                                                        }]);
                                                        promise[0].done(function(resp) {
                                                            if(resp.response == 'success') {
                                                                window.location.href = resp.returnurl;
                                                            } else {
                                                                modal.hide();
                                                                HomePage.confirmbox(resp.response);
                                                            }
                                                        }).fail(function() {
                                                            console.log('exception');
                                                        });
                                                    }.bind(this));
                                                    modal.show();
                                                }.bind(this));
                                            } else if(resp.invoicenumber <= 0) {
                                                HomePage.confirmbox(getString('nopendinginvoiceavailable', 'local_exams'));
                                            } else if(resp.newinvoiceamount < 50.00  && resp.seats > 0) {
                                                HomePage.confirmbox(getString('invoiceamountcantlower', 'local_exams'));
                                            } else {
                                                ModalFactory.create({                               
                                                    type: ModalFactory.types.SAVE_CANCEL,
                                                    body: getString('generateinvoiceconfirmation', 'local_exams',responsedata)
                                                }).done(function(modal) {
                                                    this.modal = modal;
                                                    modal.setSaveButtonText(getString('confirm', 'local_exams'));
                                                    modal.getRoot().on(ModalEvents.save, function(e) {
                                                        e.preventDefault();
                                                        Templates.renderForPromise('tool_product/loader', {}).then(({html, js}) => {
                                                            Templates.appendNodeContents('.modal-content', html, js);
                                                        });
                                                        e.stopImmediatePropagation();
                                                        $(e.target).attr('disabled', true);
                                                        var ceparams = {};
                                                        ceparams.userid = userid;
                                                        ceparams.productid=productid;
                                                        ceparams.examprice = 1;
                                                        ceparams.amount = resp.productprice;
                                                        ceparams.refundamount = resp.refundamount;
                                                        ceparams.newinvoiceamount =resp.newinvoiceamount;
                                                        ceparams.newamount =resp.newamount;
                                                        ceparams.examdate=resp.examdate;
                                                        ceparams.policyconfirm = 1;
                                                        ceparams.invoicenumber=resp.invoicenumber;
                                                        ceparams.entitytype=entitytype;
                                                        ceparams.enrolltype=enrolltype;
                                                        ceparams.cangenerateinvoice=cangenerateinvoice;
                                                        var promise = Ajax.call([{
                                                            methodname: 'local_exams_canceluser',
                                                            args: ceparams
                                                        }]);
                                                        promise[0].done(function(resp) {
                                                            if(resp.response == 'success') {
                                                                window.location.href = resp.returnurl;
                                                            } else {
                                                                modal.hide();
                                                                HomePage.confirmbox(resp.response);
                                                            }
                                                        }).fail(function() {
                                                            console.log('exception');
                                                        });
                                                    }.bind(this));
                                                    modal.show();
                                                }.bind(this));
                                            }    
                                        }).fail(function(err) {
                                            HomePage.confirmbox(err.message);
                                        });

                                    } else {


                                        const title = getString('reasoncancel', 'local_exams');
                                        const form = new Modalform({
                                            formClass: 'local_exams\\form\\reasonform',
                                            args: {entitytype: entitytype, userid: userid, productid: productid},
                                            modalConfig: {title},
                                            returnFocus: canceluser,
                                        });
                                        form.addEventListener(form.events.FORM_SUBMITTED, (event) => {
                                            const data = event.detail;
                                            console.log(data.policy);
                                            if(data.policy>0) {
                                                var policyconfirm = 1;
                                            } else {
                                                var policyconfirm = 0;
                                            }
                                            var canceldataparams = {};
                                            canceldataparams.productid = productid;
                                            canceldataparams.entitytype = entitytype;
                                            canceldataparams.userid = userid;
                                            canceldataparams.refundtype = 'cancel';
                                            canceldataparams.policyconfirm = policyconfirm;
                                            canceldataparams.enrolltype=enrolltype;
                                            console.log(canceldataparams);
                                            var promise = Ajax.call([{
                                                methodname: 'local_exams_cancelexamcalc',
                                                args: canceldataparams
                                            }]);
                                            promise[0].done(function(resp) {
                                                var responsedata = {};
                                                responsedata.productprice = resp.productprice;
                                                responsedata.refundamount = resp.refundamount;
                                                responsedata.newinvoiceamount = resp.newinvoiceamount;
                                                responsedata.newamount = resp.newamount;
                                                responsedata.invoicenumber = resp.invoicenumber;
                                                responsedata.productid = resp.productid;
                                                responsedata.orgofficialenrolled = resp.orgofficialenrolled;
                                                responsedata.deductamount = resp.deductamount;
    
                                                if(resp.invoicenumber <= 0) {
                                                    HomePage.confirmbox(getString('nopendinginvoiceavailable', 'local_exams'));
                                                } else if(resp.newinvoiceamount < 50.00  && resp.seats > 0) {
                                                    HomePage.confirmbox(getString('invoiceamountcantlower', 'local_exams'));
                                                } else {
                                                    ModalFactory.create({                               
                                                        type: ModalFactory.types.SAVE_CANCEL,
                                                        body: getString('generateinvoiceconfirmation', 'local_exams',responsedata)
                                                    }).done(function(modal) {
                                                        this.modal = modal;
                                                        modal.setSaveButtonText(getString('confirm', 'local_exams'));
                                                        modal.getRoot().on(ModalEvents.save, function(e) {
                                                            e.preventDefault();
                                                            Templates.renderForPromise('tool_product/loader', {}).then(({html, js}) => {
                                                                Templates.appendNodeContents('.modal-content', html, js);
                                                            });
                                                            e.stopImmediatePropagation();
                                                            $(e.target).attr('disabled', true);
                                                            var ceparams = {};
                                                            ceparams.userid = userid;
                                                            ceparams.productid=productid;
                                                            ceparams.examprice = 1;
                                                            ceparams.amount = resp.productprice;
                                                            ceparams.refundamount = resp.refundamount;
                                                            ceparams.newinvoiceamount =resp.newinvoiceamount;
                                                            ceparams.newamount =resp.newamount;
                                                            ceparams.examdate=resp.examdate;
                                                            ceparams.policyconfirm = policyconfirm;
                                                            ceparams.invoicenumber=resp.invoicenumber;
                                                            ceparams.entitytype=entitytype;
                                                            ceparams.enrolltype=enrolltype;
                                                            ceparams.cangenerateinvoice=cangenerateinvoice;
                                                            var promise = Ajax.call([{
                                                                methodname: 'local_exams_canceluser',
                                                                args: ceparams
                                                            }]);
                                                            promise[0].done(function(resp) {
                                                                if(resp.response == 'success') {
                                                                    window.location.href = resp.returnurl;
                                                                } else {
                                                                    modal.hide();
                                                                    HomePage.confirmbox(resp.response);
                                                                }
                                                            }).fail(function() {
                                                                console.log('exception');
                                                            });
                                                        }.bind(this));
                                                        modal.show();
                                                    }.bind(this));
                                                }    
                                            }).fail(function(err) {
                                                HomePage.confirmbox(err.message);
                                            });
                                        });
                                        form.show();

                                    } 

                                } else {
                                    var ceparams = {};
                                    ceparams.userid = userid;
                                    ceparams.productid=productid;
                                    ceparams.examprice = 1;
                                    ceparams.amount = 0;
                                    ceparams.refundamount = 0;
                                    ceparams.newinvoiceamount =0;
                                    ceparams.newamount =0;
                                    ceparams.examdate=examdate;
                                    ceparams.policyconfirm = 0;
                                    ceparams.invoicenumber=0;
                                    ceparams.entitytype=entitytype;
                                    ceparams.enrolltype=enrolltype;
                                    ceparams.cangenerateinvoice=cangenerateinvoice;
                                    var promise = Ajax.call([{
                                        methodname: 'local_exams_canceluser',
                                        args: ceparams
                                    }]);
                                    promise[0].done(function(resp) {
                                        if(resp.response == 'success') {
                                            window.location.href = resp.returnurl;
                                        } else {
                                            modal.hide();
                                            HomePage.confirmbox(resp.response);
                                        }
                                    }).fail(function() {
                                        console.log('exception');
                                    });

                                }
                                
                            } else {
                                modal.hide();
                                if (enrolledrole=='organizationofficial' && currentuserorgoff==1) { // org enrolled and org cancelling
                                   
                                    var canceldataparams = {};
                                    canceldataparams.productid = productid;
                                    canceldataparams.entitytype = entitytype;
                                    canceldataparams.userid = userid;
                                    canceldataparams.refundtype = 'cancel';
                                    canceldataparams.policyconfirm = 1;
                                    canceldataparams.enrolltype=enrolltype;
                                    
                                    var promise = Ajax.call([{
                                        methodname: 'local_exams_cancelexamcalc',
                                        args: canceldataparams
                                    }]);
                                    promise[0].done(function(resp) {
                                        var responsedata = {};
                                        responsedata.productprice = resp.productprice;
                                        responsedata.refundamount = resp.refundamount;
                                        responsedata.newinvoiceamount = resp.newinvoiceamount;
                                        responsedata.newamount = resp.newamount;
                                        responsedata.invoicenumber = resp.invoicenumber;
                                        responsedata.productid = resp.productid;
                                        responsedata.orgofficialenrolled = resp.orgofficialenrolled;
                                        responsedata.deductamount = resp.deductamount;
                                       
                                        if(responsedata.refundamount==0) {
                                            ModalFactory.create({                               
                                                type: ModalFactory.types.SAVE_CANCEL,
                                                body: getString('generateinvoiceconfirmation', 'local_exams',responsedata)
                                            }).done(function(modal) {
                                                
                                                this.modal = modal;
                                                modal.setSaveButtonText(getString('confirm', 'local_exams'));
                                                modal.getRoot().on(ModalEvents.save, function(e) {
                                                    e.preventDefault();
                                                    Templates.renderForPromise('tool_product/loader', {}).then(({html, js}) => {
                                                        Templates.appendNodeContents('.modal-content', html, js);
                                                    });
                                                    e.stopImmediatePropagation();

                                                    $(e.target).attr('disabled', true);
                                                    var ceparams = {};
                                                    
                                                    ceparams.userid = userid;
                                                    ceparams.productid=productid;
                                                    ceparams.examprice = 0;
                                                    ceparams.amount = resp.productprice;
                                                    ceparams.refundamount = 0;
                                                    ceparams.newinvoiceamount =0;
                                                    ceparams.newamount =0;
                                                    ceparams.examdate=resp.examdate;
                                                    ceparams.policyconfirm = 1;
                                                    ceparams.invoicenumber=resp.invoicenumber;
                                                    ceparams.entitytype=entitytype;
                                                    ceparams.enrolltype=enrolltype;
                                                    ceparams.cangenerateinvoice=cangenerateinvoice;
                                                    var promise = Ajax.call([{
                                                        methodname: 'local_exams_canceluser',
                                                        args: ceparams
                                                    }]);
                                                    promise[0].done(function(resp) {
                                                        if(resp.response == 'success') {
                                                            window.location.href = resp.returnurl;
                                                        } else {
                                                            modal.hide();
                                                            HomePage.confirmbox(resp.response);
                                                        }
                                                    }).fail(function() {
                                                        console.log('exception');
                                                    });
                                                }.bind(this));
                                                modal.show();
                                            }.bind(this));
                                        } else if(resp.invoicenumber <= 0) {
                                            HomePage.confirmbox(getString('nopendinginvoiceavailable', 'local_exams'));
                                        } else if(resp.newinvoiceamount < 50.00  && resp.seats > 0) {
                                            HomePage.confirmbox(getString('invoiceamountcantlower', 'local_exams'));
                                        } else {
                                            ModalFactory.create({                               
                                                type: ModalFactory.types.SAVE_CANCEL,
                                                body: getString('generateinvoiceconfirmation', 'local_exams',responsedata)
                                            }).done(function(modal) {
                                                this.modal = modal;
                                                modal.setSaveButtonText(getString('confirm', 'local_exams'));
                                                modal.getRoot().on(ModalEvents.save, function(e) {
                                                    e.preventDefault();
                                                    Templates.renderForPromise('tool_product/loader', {}).then(({html, js}) => {
                                                        Templates.appendNodeContents('.modal-content', html, js);
                                                    });
                                                    e.stopImmediatePropagation();
                                                    $(e.target).attr('disabled', true);
                                                    var ceparams = {};
                                                    ceparams.userid = userid;
                                                    ceparams.productid=productid;
                                                    ceparams.examprice = 1;
                                                    ceparams.amount = resp.productprice;
                                                    ceparams.refundamount = resp.refundamount;
                                                    ceparams.newinvoiceamount =resp.newinvoiceamount;
                                                    ceparams.newamount =resp.newamount;
                                                    ceparams.examdate=resp.examdate;
                                                    ceparams.policyconfirm = 1;
                                                    ceparams.invoicenumber=resp.invoicenumber;
                                                    ceparams.entitytype=entitytype;
                                                    ceparams.enrolltype=enrolltype;
                                                    ceparams.cangenerateinvoice=cangenerateinvoice;
                                                    var promise = Ajax.call([{
                                                        methodname: 'local_exams_canceluser',
                                                        args: ceparams
                                                    }]);
                                                    promise[0].done(function(resp) {
                                                        if(resp.response == 'success') {
                                                            window.location.href = resp.returnurl;
                                                        } else {
                                                            modal.hide();
                                                            HomePage.confirmbox(resp.response);
                                                        }
                                                    }).fail(function() {
                                                        console.log('exception');
                                                    });
                                                }.bind(this));
                                                modal.show();
                                            }.bind(this));
                                        }    
                                    }).fail(function(err) {
                                        HomePage.confirmbox(err.message);
                                    });
                                } else { // Admin Cancelling
                                    const title = getString('reasoncancel', 'local_exams');
                                    const form = new Modalform({
                                        formClass: 'local_exams\\form\\reasonform',
                                        args: {entitytype: entitytype, userid: userid, productid: productid},
                                        modalConfig: {title},
                                        returnFocus: canceluser,
                                    });
                                    form.addEventListener(form.events.FORM_SUBMITTED, (event) => {
                                        const data = event.detail;
                                        console.log(data.policy);
                                        if(data.policy>0) {
                                            var policyconfirm = 1;
                                        } else {
                                            var policyconfirm = 0;
                                        }
        
                                        var canceldataparams = {};
                                       

                                        canceldataparams.productid = productid;
                                        canceldataparams.entitytype = entitytype;
                                        canceldataparams.userid = userid;
                                        canceldataparams.refundtype = 'cancel';
                                        canceldataparams.policyconfirm = policyconfirm;
                                        canceldataparams.enrolltype=enrolltype;
                                        
                                        var promise = Ajax.call([{
                                            methodname: 'local_exams_cancelexamcalc',
                                            args: canceldataparams
                                        }]);
                                        promise[0].done(function(resp) {
                                            var responsedata = {};
                                            responsedata.productprice = resp.productprice;
                                            responsedata.refundamount = resp.refundamount;
                                            responsedata.newinvoiceamount = resp.newinvoiceamount;
                                            responsedata.newamount = resp.newamount;
                                            responsedata.invoicenumber = resp.invoicenumber;
                                            responsedata.productid = resp.productid;
                                            responsedata.orgofficialenrolled = resp.orgofficialenrolled;
                                            responsedata.deductamount = resp.deductamount;
            
                                            if(resp.trainee == 1) {
                                                ModalFactory.create({
                                                title: getString('refundpolicies', 'local_exams'),
                                                type: ModalFactory.types.SAVE_CANCEL,
                                                body: getString('refundamountconfirm', 'local_exams', responsedata)
                                                }).done(function(modal) {
                                                    this.modal = modal;
                                                    modal.setSaveButtonText(getString('confirm', 'local_exams'));
                                                    modal.getRoot().on(ModalEvents.save, function(e) {
                                                        e.preventDefault();   
                                                        Templates.renderForPromise('tool_product/loader', {}).then(({html, js}) => {
                                                            Templates.appendNodeContents('.modal-content', html, js);
                                                        });
                                                        e.stopImmediatePropagation();  
                                                        $(e.target).attr('disabled', true);   
                                                        var canceluserparams = {};
                                                        console.log(cangenerateinvoice);
                                                        canceluserparams.userid = userid;
                                                        canceluserparams.examprice = 1;
                                                        canceluserparams.amount = resp.productprice;
                                                        canceluserparams.refundamount = resp.refundamount;
                                                        canceluserparams.newinvoiceamount = 0;
                                                        canceluserparams.newamount =0;
                                                        canceluserparams.productid=productid;
                                                        canceluserparams.policyconfirm=policyconfirm;
                                                        canceluserparams.examdate=resp.examdate;
                                                        canceluserparams.invoicenumber=0;
                                                        canceluserparams.entitytype=entitytype;
                                                        canceluserparams.enrolltype=enrolltype;
                                                        canceluserparams.cangenerateinvoice=cangenerateinvoice;
                                                        var promise = Ajax.call([{
                                                            methodname: 'local_exams_canceluser',
                                                            args: canceluserparams
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
                                                            //console.log('exception');
                                                        });
                                                    }.bind(this));
                                                    modal.show();
                                                }.bind(this));
    
                                            } else if(resp.invoicenumber <= 0) {
                                                HomePage.confirmbox(getString('nopendinginvoiceavailable', 'local_exams'));
                                            } else if(resp.newinvoiceamount < 50.00  && resp.seats > 0) {
                                                HomePage.confirmbox(getString('invoiceamountcantlower', 'local_exams'));
                                            } else {
                                                ModalFactory.create({                               
                                                    type: ModalFactory.types.SAVE_CANCEL,
                                                    body: getString('generateinvoiceconfirmation', 'local_exams',responsedata)
                                                }).done(function(modal) {
                                                    this.modal = modal;
                                                    modal.setSaveButtonText(getString('confirm', 'local_exams'));
                                                    modal.getRoot().on(ModalEvents.save, function(e) {
                                                        e.preventDefault();
                                                        Templates.renderForPromise('tool_product/loader', {}).then(({html, js}) => {
                                                            Templates.appendNodeContents('.modal-content', html, js);
                                                        });
                                                        e.stopImmediatePropagation();
                                                        $(e.target).attr('disabled', true);
            
                                                        var ceparams = {};
                                                        ceparams.userid = userid;
                                                        ceparams.productid=productid;
                                                        ceparams.examprice = 1;
                                                        ceparams.amount = resp.productprice;
                                                        ceparams.refundamount = resp.refundamount;
                                                        ceparams.newinvoiceamount =resp.newinvoiceamount;
                                                        ceparams.newamount =resp.newamount;
                                                        ceparams.examdate=resp.examdate;
                                                        ceparams.policyconfirm = policyconfirm;
                                                        ceparams.invoicenumber=resp.invoicenumber;
                                                        ceparams.entitytype=entitytype;
                                                        ceparams.enrolltype=enrolltype;
                                                        ceparams.cangenerateinvoice=cangenerateinvoice;
                                                        var promise = Ajax.call([{
                                                            methodname: 'local_exams_canceluser',
                                                            args: ceparams
                                                        }]);
                                                        promise[0].done(function(resp) {
                                                            if(resp.response == 'success') {
                                                                window.location.href = resp.returnurl;
                                                            } else {
                                                                modal.hide();
                                                                HomePage.confirmbox(resp.response);
                                                            }
                                                        }).fail(function() {
                                                            console.log('exception');
                                                        });
                                                    }.bind(this));
                                                    modal.show();
                                                }.bind(this));
                                            }    
                                        }).fail(function(err) {
                                            HomePage.confirmbox(err.message);
                                        });
                                    });
                                    form.show();
                                }
                            }
                        }
                    }
                }.bind(this));
                modal.show();
            }.bind(this));
        }

        let trescheduleexam = e.target.closest(Selectors.actions.trescheduleexam);
        if (trescheduleexam) {
            const examid = trescheduleexam.getAttribute('data-examid');
            const productid = trescheduleexam.getAttribute('data-productid');
            const entitytype = 'exam';
            const type = trescheduleexam.getAttribute('data-type');
            const profileid = trescheduleexam.getAttribute('data-profileid');
            const damount = atob(trescheduleexam.getAttribute('data-damount'));
            const userid = trescheduleexam.getAttribute('data-userid');
            const contextid = trescheduleexam.getAttribute('data-contextid');
            const scheduleid = trescheduleexam.getAttribute('data-scheduleid');
            console.log(profileid)
            ModalFactory.create({
                title: getString('rescheduleexam', 'local_exams'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('rescheduleconfirm', 'local_exams')
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('reschedule', 'local_exams'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    Templates.renderForPromise('tool_product/loader', {}).then(({html, js}) => {
                        Templates.appendNodeContents('.modal-content', html, js);
                    });
                    var params = {};
                    params.examid = examid;
                    params.profileid = profileid;
                    params.scheduleid = scheduleid;
                    params.type = type;
                    params.tuserid = userid;
                    params.orderid = 0; 
                    params.productid = productid;
                    var promise = Ajax.call([{
                        methodname: 'local_exam_enrouser',
                        args: params
                    }]);
                    promise[0].done(function(resp) {  
                        if(resp.response == 'success') {
                            let products = {};
                            products.userid = userid;
                            products.paymenttype = 'telr';
                            products.productid = productid;
                            products.entitytype = entitytype;
                            products.total = damount;
                            products.scheduleid = scheduleid;
                            products.profileid = profileid;
                            products.processtype = 'reschedule';
                            var producdata = Fragment.loadFragment('local_exams', 'productdata', contextid, products);
                            producdata.done(function(html, js) {
                                let promise = Ajax.call([{
                                    methodname: 'tool_product_telr_begin_trans',
                                    args: {
                                        products: html
                                    }
                                }]);
                                Templates.render('tool_product/loader');
                                promise[0].done((response) => {
                                    console.log(response);
                                    window.location = response.returnurl;
                                }).fail( (error) => {
                                    HomePage.confirmbox(error.message);
                                });
                            });                    
                        } else {
                            modal.hide();
                            HomePage.confirmbox(resp.response);
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
};
