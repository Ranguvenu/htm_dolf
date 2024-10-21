import TPDynamicForm from 'local_trainingprogram/dynamicform';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import homepage from 'theme_academy/homepage';
import Ajax from 'core/ajax';
import {get_string as getString} from 'core/str';

const Selectors = {
    actions: {
        
        replaceprogramuser: '[data-action="replaceprogramuser"]',
        cancelprogramuser: '[data-action="cancelprogramuser"]',
    },
};

let HomePage = new homepage();
export const init = () => {
    document.addEventListener('click', function(e) {

        let replaceprogramuser = e.target.closest(Selectors.actions.replaceprogramuser);
        if (replaceprogramuser) {
            e.preventDefault();
            e.stopImmediatePropagation()
            const programid = replaceprogramuser.getAttribute('data-programid');
            const offeringid = replaceprogramuser.getAttribute('data-offeringid');
            const userid = replaceprogramuser.getAttribute('data-userid');
            const username = replaceprogramuser.getAttribute('data-username');
            const programname = replaceprogramuser.getAttribute('data-programname');
            const programprice = replaceprogramuser.getAttribute('data-programprice');
            const useridnumber = replaceprogramuser.getAttribute('data-useridnumber');
            const replacementfee = replaceprogramuser.getAttribute('data-replacementfee');
            const isadmin = replaceprogramuser.getAttribute('data-currentuserisadmin');
            var displayparams = {};
            displayparams.programid = programid;
            displayparams.offeringid = offeringid;
            displayparams.userid = userid;
            displayparams.useridnumber = useridnumber;
            displayparams.username = username;
            displayparams.programname = programname;
            displayparams.programprice = programprice;
            displayparams.isadmin = isadmin;
            ModalFactory.create({
                title: getString('replaceconfirm', 'local_trainingprogram'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('replaceconfirmmessage', 'local_trainingprogram', displayparams)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('replace', 'local_trainingprogram'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    if(programprice == 0 || isadmin == 1) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        modal.hide();
                        const title = getString('replaceuser', 'local_trainingprogram');
                        const form = new TPDynamicForm({
                            formClass: 'local_trainingprogram\\form\\replaceuserform',
                            args: {programid: replaceprogramuser.getAttribute('data-programid'),offeringid: replaceprogramuser.getAttribute('data-offeringid'),userid: replaceprogramuser.getAttribute('data-userid'),username: replaceprogramuser.getAttribute('data-username'),useridnumber: replaceprogramuser.getAttribute('data-useridnumber'),programprice: replaceprogramuser.getAttribute('data-programprice')},
                            modalConfig: {title},
                            returnFocus: replaceprogramuser,
                        });
                        form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
                        form.show();
                    } else {
                        ModalFactory.create({                               
                            type: ModalFactory.types.SAVE_CANCEL,
                            body: getString('replacementproceedmessage', 'local_trainingprogram',replacementfee)
                        }).done(function(modal) {
                            this.modal = modal;
                            modal.setSaveButtonText(getString('confirm', 'local_trainingprogram'));
                            modal.getRoot().on(ModalEvents.save, function(e) {
                                e.preventDefault();
                                e.stopImmediatePropagation();
                                modal.hide();
                                const title = getString('replaceuser', 'local_trainingprogram');
                                const form = new TPDynamicForm({
                                    formClass: 'local_trainingprogram\\form\\replaceuserform',
                                    args: {programid: replaceprogramuser.getAttribute('data-programid'),offeringid: replaceprogramuser.getAttribute('data-offeringid'),userid: replaceprogramuser.getAttribute('data-userid'),username: replaceprogramuser.getAttribute('data-username'),useridnumber: replaceprogramuser.getAttribute('data-useridnumber'),programprice: replaceprogramuser.getAttribute('data-programprice'),replacementfee: replaceprogramuser.getAttribute('data-replacementfee')},
                                    modalConfig: {title},
                                    returnFocus: replaceprogramuser,
                                });
                                form.addEventListener(form.events.FORM_SUBMITTED, (event) => {
                                    event.preventDefault();
                                    e.preventDefault();
                                    let productdata;
                                    productdata = event.detail;
                                    var replaceparams = {};
                                    replaceparams.programid = productdata.programid;
                                    replaceparams.offeringid = productdata.offeringid;
                                    replaceparams.productid=productdata.productid;
                                    replaceparams.fromuserid = productdata.fromuserid;
                                    replaceparams.touserid = productdata.touserid;
                                    replaceparams.replacementfee = productdata.replacementfee;
                                    var promise = Ajax.call([{
                                        methodname: 'local_trainingprogram_replaceprogramuser',
                                        args: replaceparams
                                    }]);
                                    promise[0].done(function(resp) {
                                        window.location = M.cfg.wwwroot + '/local/trainingprogram/programenrolleduserslist.php?programid='+programid;
                                    }).fail(function(err) {
                                        HomePage.confirmbox(err.message);
                                        //console.log('exception');
                                    });
                                });
                                form.show();
                            }.bind(this));
                            
                            modal.show();
                        }.bind(this));
                    } 
                    
                }.bind(this));
                modal.show();
            }.bind(this));
        }
        let cancelprogramuser = e.target.closest(Selectors.actions.cancelprogramuser);
        if (cancelprogramuser) {
            e.preventDefault();
            e.stopImmediatePropagation()
            const programid = cancelprogramuser.getAttribute('data-programid');
            const offeringid = cancelprogramuser.getAttribute('data-offeringid');
            const userid = cancelprogramuser.getAttribute('data-userid');
            const username = cancelprogramuser.getAttribute('data-username');
            const programname = cancelprogramuser.getAttribute('data-programname');
            const programprice = cancelprogramuser.getAttribute('data-programprice'); 
            const remainingdays = cancelprogramuser.getAttribute('data-remainingdays');  
            const programdate = cancelprogramuser.getAttribute('data-programdate'); 
            const isadmin = cancelprogramuser.getAttribute('data-isadmin');   
                
            var displayparams = {};
            displayparams.programid = programid;
            displayparams.offeringid = offeringid;
            displayparams.userid = userid;
            displayparams.username = username;
            displayparams.programname = programname;
            displayparams.programprice = programprice;
            ModalFactory.create({
                title: getString('cancelconfirm', 'local_trainingprogram'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('cancelconfirmmessage', 'local_trainingprogram', displayparams)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('yes', 'local_trainingprogram'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    if(programprice == 0) {
                        params.programid = programid;
                        params.offeringid = offeringid;
                        params.userid = userid;
                        params.programprice = 0;
                        params.amount = 0;
                        params.refundamount = 0;
                        params.newinvoiceamount = 0;
                        params.productid=0;
                        params.programdate=programdate;
                        params.policyconfirm=0;
                        params.invoicenumber=0;
                        params.isadmin=isadmin;
                        params.traineeenrolled=0;
                        var promise = Ajax.call([{
                            methodname: 'local_trainingprogram_cancelprogramuser',
                            args: params
                        }]);
                        promise[0].done(function(resp) {
                            window.location = M.cfg.wwwroot + '/local/trainingprogram/programenrolleduserslist.php?programid='+programid;
                        }).fail(function(err) {
                            HomePage.confirmbox(err.message);
                            //console.log('exception');
                        });
                    } else {
                        var canceldataparams = {};
                        canceldataparams.programid = programid;
                        canceldataparams.offeringid = offeringid;
                        canceldataparams.userid = userid;
                        canceldataparams.programdate = programdate;
                        
                        var promise = Ajax.call([{
                            methodname: 'local_trainingprogram_getdataforprogramcancellation',
                            args: canceldataparams
                        }]);
                        promise[0].done(function(resp) {
                            var responsedata = {};
                            responsedata.amount = resp.amount;
                            responsedata.refundamount = resp.refundamount;
                            responsedata.newinvoiceamount = resp.newinvoiceamount;
                            responsedata.invoicenumber = resp.invoicenumber;
                            responsedata.productid = resp.productid;
                            responsedata.orgofficialenrolled = resp.orgofficialenrolled;

                            console.log(resp);

                            if(resp.traineeenrolled == 1 && isadmin == 1) {
                                const title = getString('reasoncancel', 'local_trainingprogram');
                                const form = new Modalform({
                                    formClass: 'local_trainingprogram\\form\\reasonform',
                                    args: {entitytype: 'program', entityid: programid, userid: userid, productid: resp.productid},
                                    modalConfig: {title},
                                    returnFocus: cancelprogramuser,
                                });
                                form.addEventListener(form.events.FORM_SUBMITTED, (event) => {
                                    const data = event.detail;
                                    console.log(data.policy);
                                    if(data.policy > 0) {
                                        var policyconfirm = 1;
                                    } else {
                                        var policyconfirm = 0;
                                    }
                                    var cancelprogramuserparams = {};
                                    cancelprogramuserparams.programid = programid;
                                    cancelprogramuserparams.offeringid = offeringid;
                                    cancelprogramuserparams.userid = userid;
                                    cancelprogramuserparams.programprice = 1;
                                    cancelprogramuserparams.amount = responsedata.amount;
                                    cancelprogramuserparams.refundamount = responsedata.refundamount;
                                    cancelprogramuserparams.newinvoiceamount = 0;
                                    cancelprogramuserparams.productid=responsedata.productid;
                                    cancelprogramuserparams.policyconfirm=policyconfirm;
                                    cancelprogramuserparams.programdate=programdate;
                                    cancelprogramuserparams.invoicenumber=0;
                                    cancelprogramuserparams.isadmin=1;
                                    cancelprogramuserparams.traineeenrolled=1;
                                    var promise = Ajax.call([{
                                        methodname: 'local_trainingprogram_cancelprogramuser',
                                        args: cancelprogramuserparams
                                    }]);
                                    promise[0].done(function(resp) {
                                        window.location = M.cfg.wwwroot + '/local/trainingprogram/programenrolleduserslist.php?programid='+programid;
                                    }).fail(function(err) {
                                        HomePage.confirmbox(err.message);
                                        //console.log('exception');
                                    });
                                });
                                form.show();
                            } else if(resp.traineeenrolled == 1 && isadmin == 0) {

                                var cancelprogramuserparams = {};
                                cancelprogramuserparams.programid = programid;
                                cancelprogramuserparams.offeringid = offeringid;
                                cancelprogramuserparams.userid = userid;
                                cancelprogramuserparams.programprice = 1;
                                cancelprogramuserparams.amount = responsedata.amount;
                                cancelprogramuserparams.refundamount = responsedata.refundamount;
                                cancelprogramuserparams.newinvoiceamount = 0;
                                cancelprogramuserparams.productid=responsedata.productid;
                                cancelprogramuserparams.policyconfirm=1;
                                cancelprogramuserparams.programdate=programdate;
                                cancelprogramuserparams.invoicenumber=0;
                                cancelprogramuserparams.isadmin=0;
                                cancelprogramuserparams.traineeenrolled=1;
                                var promise = Ajax.call([{
                                    methodname: 'local_trainingprogram_cancelprogramuser',
                                    args: params
                                }]);
                                promise[0].done(function(resp) {
                                    window.location = M.cfg.wwwroot + '/local/trainingprogram/programenrolleduserslist.php?programid='+programid;
                                }).fail(function(err) {
                                    HomePage.confirmbox(err.message);
                                    //console.log('exception');
                                });


                            } else if(resp.orgofficialenrolled == 0) {

                                var ceparams = {};
                                ceparams.programid = programid;
                                ceparams.offeringid = offeringid;
                                ceparams.userid = userid;
                                ceparams.productid=resp.productid;
                                ceparams.programprice = 1;
                                ceparams.amount = 0;
                                ceparams.refundamount = 0;
                                ceparams.newinvoiceamount =0;
                                ceparams.programdate=programdate;
                                ceparams.policyconfirm = 0;
                                ceparams.invoicenumber=0;
                                ceparams.isadmin=isadmin;
                                ceparams.traineeenrolled=0;
                                var promise = Ajax.call([{
                                    methodname: 'local_trainingprogram_cancelprogramuser',
                                    args: ceparams
                                }]);
                                promise[0].done(function(resp) {
                                    window.location = M.cfg.wwwroot + '/local/trainingprogram/programenrolleduserslist.php?programid='+programid;
                                }).fail(function() {
                                    console.log('exception');
                                });

                            } else if(resp.invoicenumber <= 0) {
                                HomePage.confirmbox(getString('nopendinginvoiceavailable', 'local_trainingprogram'));
                            } else if(resp.newinvoiceamount < 50.00) {
                                HomePage.confirmbox(getString('invoiceamountcantlower', 'local_trainingprogram'));
                            } else {
                                ModalFactory.create({                               
                                    type: ModalFactory.types.SAVE_CANCEL,
                                    body: getString('generateinvoiceconfirmation', 'local_trainingprogram',responsedata)
                                }).done(function(modal) {
                                    this.modal = modal;
                                    modal.setSaveButtonText(getString('confirm', 'local_trainingprogram'));
                                    modal.getRoot().on(ModalEvents.save, function(e) {
                                        e.preventDefault();
                                        var ceparams = {};
                                        ceparams.programid = programid;
                                        ceparams.offeringid = offeringid;
                                        ceparams.userid = userid;
                                        ceparams.productid=resp.productid;
                                        ceparams.programprice = 1;
                                        ceparams.amount = resp.amount;
                                        ceparams.refundamount = resp.refundamount;
                                        ceparams.newinvoiceamount =resp.newinvoiceamount;
                                        ceparams.programdate=programdate;
                                        ceparams.policyconfirm = 0;
                                        ceparams.invoicenumber=resp.invoicenumber;
                                        ceparams.isadmin=isadmin;
                                        ceparams.traineeenrolled=0;
                                        var promise = Ajax.call([{
                                            methodname: 'local_trainingprogram_cancelprogramuser',
                                            args: ceparams
                                        }]);
                                        promise[0].done(function(resp) {
                                            window.location = M.cfg.wwwroot + '/local/trainingprogram/programenrolleduserslist.php?programid='+programid;
                                        }).fail(function() {
                                            console.log('exception');
                                        });
                                    }.bind(this));
                                    modal.show();
                                }.bind(this));
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
    });
};
