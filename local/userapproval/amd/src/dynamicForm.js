import TPDynamicForm from 'local_trainingprogram/dynamicform';
import ModalForm from 'core_form/modalform';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import Ajax from 'core/ajax';
import Notification from 'core/notification';
import Templates from 'core/templates';
import {get_string as getString} from 'core/str';
const Selectors = {
    actions: {
        createuser: '[data-action="createuser"]',
        deleteuser: '[data-action="deleteuser"]',
        approveuser: '[data-action="approveuser"]',
        rejectuser: '[data-action="rejectuser"]',
        viewregistration: '[data-action="viewuserregistration"]',
        rejectorgrequest: '[data-action="rejectorgrequest"]',
        approveorgrequest: '[data-action="approveorgrequest"]',
        deletebannerimage:'[data-action="bannerimage"]',
        rejectrequest:'[data-action="rejectrequest"]',
        approverequest:'[data-action="approverequest"]',
        itemenrolledlist: '[data-action="itemenrolledlist"]',
        cancelrequest: '[data-action="cancelrequest"]', 
        editrequest: '[data-action="edit"]',
        deleterequest: '[data-action="deleterequest"]',
        sendemailtouser: '[data-action="sendemailtouser"]',
      

    },
};
export const init = () => {
    document.addEventListener('click', function(e) {

        //renu--request trainee edit

        let updaterequest = e.target.closest(Selectors.actions.editrequest);
        if (updaterequest) {
            e.preventDefault();
                var userid=updaterequest.getAttribute('data-userid');
                var id= updaterequest.getAttribute('data-id');
                var requesttype= updaterequest.getAttribute('data-requesttype');
                var name=updaterequest.getAttribute('data-name');
               
            const title = getString('updaterequest', 'local_userapproval');
            const form = new ModalForm({
                formClass: 'local_userapproval\\form\\requestedit_form',
                args: {id: id,
                    requesttype: requesttype,
                    name:name,
                    userid:userid,  

                },
                 modalConfig: {title},
                returnFocus: updaterequest,
            });
         form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();   
        } 

        //renu.. delete request
        
        let requestdelete = e.target.closest(Selectors.actions.deleterequest);
        if (requestdelete) {
            e.preventDefault();
             
            const userid=requestdelete.getAttribute('data-userid');
            const id = requestdelete.getAttribute('data-id');
            const requesttype = requestdelete.getAttribute('data-requesttype');

            ModalFactory.create({
                title: getString('confirm', 'local_userapproval'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('deleteconfirmation', 'local_userapproval')
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('deletetext', 'local_userapproval'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.id = id;
                    params.requesttype =requesttype;
                    var promise = Ajax.call([{
                        methodname: 'local_userapproval_deleterequest',
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





        // e.stopImmediatePropagation()
        let element = e.target.closest(Selectors.actions.createuser);
        if (element) {
            e.preventDefault();
            e.stopImmediatePropagation();
            const title = element.getAttribute('data-id') ?
                getString('edituser', 'local_userapproval', element.getAttribute('data-name')) :
                getString('registernewuser', 'local_userapproval');
            const form = new TPDynamicForm({
                formClass: 'local_userapproval\\form\\individual_registration_form',
                args: {id: element.getAttribute('data-id'),segment:element.getAttribute('data-segment'),jobfamily:element.getAttribute('data-jobfamily'),jobrole:element.getAttribute('data-jobrole')},
                modalConfig: {title},
                returnFocus: element,
            });
            form.addEventListener(form.events.ERROR, event => {
                form.enableButtons();
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }
        let deleteuserelement = e.target.closest(Selectors.actions.deleteuser);
        if (deleteuserelement) {
             e.preventDefault();
             e.stopImmediatePropagation()
            const userid = deleteuserelement.getAttribute('data-id');
            const username = deleteuserelement.getAttribute('data-name');
            ModalFactory.create({
                title: getString('confirm', 'local_userapproval'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('deleteconfirm', 'local_userapproval',username)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('deletetext', 'local_userapproval'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.userid = userid;
                    params.username = username;
                    var promise = Ajax.call([{
                        methodname: 'local_userapproval_deleteteuser',
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
        let approveuserelement = e.target.closest(Selectors.actions.approveuser);
        if (approveuserelement) {
             e.preventDefault();
             e.stopImmediatePropagation()
            const userid = approveuserelement.getAttribute('data-id');
            const username = approveuserelement.getAttribute('data-name');
            ModalFactory.create({
                title: getString('approvedconfirm', 'local_userapproval'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('userapproveconfirm', 'local_userapproval',username)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('approvetext', 'local_userapproval'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.userid = userid;
                    params.username = username;
                    var promise = Ajax.call([{
                        methodname: 'local_userapproval_approveuser',
                        args: params
                    }]);
                    promise[0].done(function(resp) {
                        window.location.reload(true);
                    }).fail(function(ex) {
                        Notification.exception(ex);
                    });
                }.bind(this));
                modal.show();
            }.bind(this));
        }
        let rejectuserelement = e.target.closest(Selectors.actions.rejectuser);
        if (rejectuserelement) {
             e.preventDefault();
             e.stopImmediatePropagation()
            const userid = rejectuserelement.getAttribute('data-id');
            const username = rejectuserelement.getAttribute('data-name');
            ModalFactory.create({
                title: getString('rejectedconfirm', 'local_userapproval'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('userrejectconfirm', 'local_userapproval',username)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('rejecttext', 'local_userapproval'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.userid = userid;
                    params.username = username;
                    var promise = Ajax.call([{
                        methodname: 'local_userapproval_rejectuser',
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
        let viewregistrationelement = e.target.closest(Selectors.actions.viewregistration);
        if (viewregistrationelement) {
             e.preventDefault();
             e.stopImmediatePropagation();
            const requestid = viewregistrationelement.getAttribute('data-requestid');
            const userid = viewregistrationelement.getAttribute('data-id');
            const username = viewregistrationelement.getAttribute('data-name');
            const requesttype = viewregistrationelement.getAttribute('data-requesttype');
            var  regtitle;

            if(requesttype){
                if(requesttype == 'Trainer'){
                    regtitle =  getString('trainerdetails','local_userapproval');

                }else {
                    regtitle =  getString('expertdetails','local_userapproval');

                }
            }else{
                regtitle =  getString('viewregistration','local_userapproval');


            }
            var params = {};
            params.requestid = requestid;
            params.userid = userid;
            params.username = username;
            params.requesttype = requesttype ;
            var promise = Ajax.call([{
                methodname: 'local_userapproval_viewregistration',
                args: params
            }]);
            promise[0].done(function(resp) {
                ModalFactory.create({
                    title: regtitle ,
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

        let rejectorgrequest = e.target.closest(Selectors.actions.rejectorgrequest);
        if (rejectorgrequest) {
             e.preventDefault();
             e.stopImmediatePropagation()
            const requestid = rejectorgrequest.getAttribute('data-id');
            const orgid = rejectorgrequest.getAttribute('data-orgid');
            const userid = rejectorgrequest.getAttribute('data-userid');
            const username = rejectorgrequest.getAttribute('data-username');
            const orgname = rejectorgrequest.getAttribute('data-orgname');
            var displayparams = {};
                displayparams.username = username;
                displayparams.orgname = orgname;
            ModalFactory.create({
                title: getString('orgrequestconfirm', 'local_userapproval'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('orgrequestrejectconfirm', 'local_userapproval',displayparams)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('orgrequestrejecttext', 'local_userapproval'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.requestid = requestid;
                    params.orgid = orgid;
                    params.userid = userid;
                    var promise = Ajax.call([{
                        methodname: 'local_userapproval_rejectorgrequest',
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

        let approveorgrequest = e.target.closest(Selectors.actions.approveorgrequest);
        if (approveorgrequest) {
             e.preventDefault();
             e.stopImmediatePropagation()
            const requestid = approveorgrequest.getAttribute('data-id');
            const orgid = approveorgrequest.getAttribute('data-orgid');
            const userid = approveorgrequest.getAttribute('data-userid');
            const username = approveorgrequest.getAttribute('data-username');
            const orgname = approveorgrequest.getAttribute('data-orgname');
            var displayparams = {};
                displayparams.username = username;
                displayparams.orgname = orgname;
            ModalFactory.create({
                title: getString('orgrequestconfirm', 'local_userapproval'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('orgrequestapproveconfirm', 'local_userapproval',displayparams)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('orgrequestapprovetext', 'local_userapproval'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.requestid = requestid;
                    params.orgid = orgid;
                    params.userid = userid;
                    var promise = Ajax.call([{
                        methodname: 'local_userapproval_approveorgrequest',
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
         let deletebannerimageelement = e.target.closest(Selectors.actions.deletebannerimage);
        if (deletebannerimageelement) {
             e.preventDefault();
             e.stopImmediatePropagation()
            const userid = deletebannerimageelement.getAttribute('data-id');
            
            ModalFactory.create({
                title: getString('confirm', 'local_userapproval'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('deletebannerimageconfirm', 'local_userapproval')
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('deletetext', 'local_userapproval'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.userid = userid;
                    
                    var promise = Ajax.call([{
                        methodname: 'local_userapproval_deletebannerimage',
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


        let rejectrequestelement = e.target.closest(Selectors.actions.rejectrequest);
        if (rejectrequestelement) {
             e.preventDefault();
             e.stopImmediatePropagation()
            const requestid = rejectrequestelement.getAttribute('data-id');
            const requesttype = rejectrequestelement.getAttribute('data-requesttype');
            const username = rejectrequestelement.getAttribute('data-name');
            var displayparams = {};
                displayparams.username = username;
                displayparams.requesttype = requesttype;
            
            ModalFactory.create({
                title: getString('reject', 'local_userapproval'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('rejectconfirm', 'local_userapproval',displayparams)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('reject', 'local_userapproval'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.requestid = requestid;
                    params.requesttype = requesttype;
                    
                    var promise = Ajax.call([{
                        methodname: 'local_userapproval_rejectrequest',
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
        let approverequestelement = e.target.closest(Selectors.actions.approverequest);
        if (approverequestelement) {
             e.preventDefault();
             e.stopImmediatePropagation()
            const approverequestid = approverequestelement.getAttribute('data-id');
            const requesttype = approverequestelement.getAttribute('data-requesttype');
            const username = approverequestelement.getAttribute('data-name');
            var displayparams = {};
                displayparams.username = username;
                displayparams.requesttype = requesttype;
            
            ModalFactory.create({
                title: getString('approve', 'local_userapproval'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('approveconfirm', 'local_userapproval',displayparams)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('approve', 'local_userapproval'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.approverequestid = approverequestid;
                    params.requesttype = requesttype;
                    
                    var promise = Ajax.call([{
                        methodname: 'local_userapproval_approverequest',
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
        //
             let cancelrequestrequestelement = e.target.closest(Selectors.actions.cancelrequest);
        if (cancelrequestrequestelement) {
             e.preventDefault();
             e.stopImmediatePropagation()
            const cancelrequestidrequestid = cancelrequestrequestelement.getAttribute('data-id');
            const requesttype = cancelrequestrequestelement.getAttribute('data-requesttype');
            const username = cancelrequestrequestelement.getAttribute('data-name');
            const trainerorexpertid = cancelrequestrequestelement.getAttribute('data-trainerorexpertid');
            var displayparams = {};
                displayparams.username = username;
                displayparams.requesttype = requesttype;
                displayparams.trainerorexpertid = trainerorexpertid;
            ModalFactory.create({
                title: getString('canceled', 'local_userapproval'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('cancelconfirm', 'local_userapproval',displayparams)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('confirm', 'local_userapproval'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.cancelid = cancelrequestidrequestid;
                    params.requesttype = requesttype;
                    params.trainerorexpertid = trainerorexpertid;
                    
                    var promise = Ajax.call([{
                        methodname: 'local_userapproval_cancelrequest',
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
        //

        let itemenrolledlist = e.target.closest(Selectors.actions.itemenrolledlist);
        if (itemenrolledlist) {
            e.preventDefault();
            e.stopImmediatePropagation();
            const userid = itemenrolledlist.getAttribute('data-id');
            const username = itemenrolledlist.getAttribute('data-name');
            const type = itemenrolledlist.getAttribute('data-type');
            if(type == 'exams') {
                var title = getString('examenrolledusers', 'local_userapproval');
            } else if(type == 'programs') {
                var title = getString('tpenrolledusers', 'local_userapproval');
            } else if(type == 'events') {
                var title = getString('eventenrolledusers', 'local_userapproval');
            } 
            var options = {};
            options.userid = userid;
            options.username = username;
            options.type = type;
            var trigger = $(Selectors.actions.itemenrolledlist);
            ModalFactory.create({
                title: title,
                body: Templates.render('local_userapproval/itemenrolled_display',options)
            }, trigger)
            .done(function(modal) {
                modal.setLarge();
                modal.show();
                modal.getRoot().on(ModalEvents.hidden, function() {
                modal.destroy();
                }.bind(this));
            });
        }
        let sendemailtouser = e.target.closest(Selectors.actions.sendemailtouser);
        if (sendemailtouser) {
            e.preventDefault();
            const title = getString('sendemail', 'local_userapproval',sendemailtouser.getAttribute('data-name'));
            const form = new ModalForm({
                formClass: 'local_userapproval\\form\\sendemailform',
                args: {id:sendemailtouser.getAttribute('data-id'),name:sendemailtouser.getAttribute('data-name'),email:sendemailtouser.getAttribute('data-email')},
                modalConfig: {title},
                returnFocus: sendemailtouser,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();

        }


    });
};
