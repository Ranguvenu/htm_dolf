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
import {get_string as getString} from 'core/str';
import ModalFactory from 'core/modal_factory';
import Ajax from 'core/ajax';
import ModalForm from 'core_form/modalform';
import ModalEvents from 'core/modal_events';
import homepage from 'theme_academy/homepage';
import Templates from 'core/templates';

const Selectors = {
    actions: {
        createOrganization: '[data-action="createorganization"]',
        deleteorganization: '[data-action="deleteorganization"]',
        unassignuser: '[data-action="unassignuser"]',
        approveorganization: '[data-action="approveorganization"]',
        rejectorganization: '[data-action="rejectorganization"]',
        vieworganization: '[data-action="vieworganization"]',
        orgofficialassign: '[data-action="orgofficialassign"]',
        sendemailtohr: '[data-action="sendemailtohr"]',
        createpartnertypes: '[data-action="createpartnertypes"]',
        viewpartners: '[data-action="viewpartners"]',
        deletepartners: '[data-action="deletepartners"]',
        loginasuser: '[data-action="loginasuser"]',
    },
};
let HomePage = new homepage();
export const init = () => {
    document.addEventListener('click', function(e) {
        //e.stopImmediatePropagation();
        let sendemailtohr = e.target.closest(Selectors.actions.sendemailtohr);
        if (sendemailtohr) {
            e.preventDefault();
            const title = getString('sendemail', 'local_organization');
            const form = new TPDynamicForm({
                formClass: 'local_organization\\form\\hremail_form',
                args: {organizationid: sendemailtohr.getAttribute('data-id'), hremail: sendemailtohr.getAttribute('data-hremail')},
                modalConfig: {title},
                returnFocus: sendemailtohr,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }
        let element = e.target.closest(Selectors.actions.createOrganization);
        if (element) {
            e.preventDefault();
            const title = element.getAttribute('data-id') ?
                getString('editorganization', 'local_organization', element.getAttribute('data-name')) :
                getString('createorganization', 'local_organization');
            const form = new TPDynamicForm({
                formClass: 'local_organization\\form\\organization_form',
                args: {id: element.getAttribute('data-id')},
                modalConfig: {title},
                returnFocus: element,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }
        let orgofficialassign = e.target.closest(Selectors.actions.orgofficialassign);
        if (orgofficialassign) {
            e.preventDefault();
            e.stopImmediatePropagation();
            const title = getString('enroltoorgof', 'local_organization');
            const form = new ModalForm({
                formClass: 'local_organization\\form\\assignform',
                args: {orgid: orgofficialassign.getAttribute('data-orgid'), roleid: orgofficialassign.getAttribute('data-role')},
                modalConfig: {title},
                returnFocus: orgofficialassign,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }
        let deleteorganization = e.target.closest(Selectors.actions.deleteorganization);
        if (deleteorganization) {
            const organizationid = deleteorganization.getAttribute('data-id');
            const orgname = deleteorganization.getAttribute('data-orgname');

            ModalFactory.create({
                title: getString('deleteorganization', 'local_organization'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('deleteorgconfirm', 'local_organization',orgname)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('delete', 'local_organization'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.elementid = organizationid;
                    params.elementtype = 'organization';
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
                            params.organizationid = organizationid;
                            var promise = Ajax.call([{
                                methodname: 'local_deleteorganization',
                                args: params
                            }]);
                            promise[0].done(function(resp) {
                                window.location.reload(true);
                            }).fail(function() {
                                // do something with the exception
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
        let unassignuser = e.target.closest(Selectors.actions.unassignuser);
        if (unassignuser) {
            const id = unassignuser.getAttribute('data-id');
            const rolename = unassignuser.getAttribute('data-rolename');
            const roleid = unassignuser.getAttribute('data-roleid');
            const orgid = unassignuser.getAttribute('data-orgid');
            const username = unassignuser.getAttribute('data-username');
             const orgname = unassignuser.getAttribute('data-orgname');
              var displayparams = {};
                displayparams.rolename = rolename;
                displayparams.username = username;
                displayparams.orgname = orgname;
            ModalFactory.create({
                title: getString('unassignconfirm', 'local_organization'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('orgunassignconfirm', 'local_organization',displayparams)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('unassigntext', 'local_organization'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.id = id;
                    params.roleid = roleid;
                    params.orgid = orgid;
                    var promise = Ajax.call([{
                        methodname: 'local_deleteorguser',
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
        let approveorganization = e.target.closest(Selectors.actions.approveorganization);
        if (approveorganization) {
            const orgid = approveorganization.getAttribute('data-id');
            ModalFactory.create({
                title: getString('approveorganization', 'local_organization'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('approveallconfirm', 'local_organization')
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('approve', 'local_organization'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.orgid = orgid;
                    var promise = Ajax.call([{
                        methodname: 'local_approve_organization',
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
        let rejectorganization = e.target.closest(Selectors.actions.rejectorganization);
        if (rejectorganization) {
            const orgid = rejectorganization.getAttribute('data-id');
            ModalFactory.create({
                title: getString('rejectorganization', 'local_organization'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('rejectallconfirm', 'local_organization')
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('reject', 'local_organization'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.orgid = orgid;
                    var promise = Ajax.call([{
                        methodname: 'local_reject_organization',
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
        let vieworganization = e.target.closest(Selectors.actions.vieworganization);
        if (vieworganization) {
            const orgid = vieworganization.getAttribute('data-id');
            var params = {};
            params.orgid = orgid;
            var promise = Ajax.call([{
                methodname: 'local_organization_info',
                args: params
            }]);
            promise[0].done(function(resp) {
                var organization = JSON.stringify(resp);
                ModalFactory.create({
                    title: getString('vieworganization', 'local_organization'),
                    body: resp.options
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
        let createpartnertypes = e.target.closest(Selectors.actions.createpartnertypes);
        if (createpartnertypes) {
            e.preventDefault();
            const title = createpartnertypes.getAttribute('data-id') ?
                getString('edittypes', 'local_organization') :
                getString('createpartner', 'local_organization');
            const form = new TPDynamicForm({
                formClass: 'local_organization\\form\\partnertypes_form',
                args: {id: createpartnertypes.getAttribute('data-id')},
                modalConfig: {title},
                returnFocus: createpartnertypes,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }

        let viewpartners = e.target.closest(Selectors.actions.viewpartners);
        if (viewpartners) {
            const partnerid = viewpartners.getAttribute('data-id');
            var params = {};
            params.partnerid = partnerid;
            var promise = Ajax.call([{
                methodname: 'viewpartnertypes',
                args: params
            }]);
            promise[0].done(function(resp) {
                ModalFactory.create({
                    title: getString('viewtypes', 'local_organization'),
                    body: resp.partnerinfo
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

        let deletepartners = e.target.closest(Selectors.actions.deletepartners);
        if (deletepartners) {
            const partnerid = deletepartners.getAttribute('data-id');
            const partnername = deletepartners.getAttribute('data-partnername');

            ModalFactory.create({
                title: getString('deletetype', 'local_organization'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('deletepartnerconfirm', 'local_organization',partnername)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('delete', 'local_organization'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.partnerid = partnerid;
                    var promise = Ajax.call([{
                        methodname: 'deletepartnertypes',
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


        let loginasuser = e.target.closest(Selectors.actions.loginasuser);
        if (loginasuser) {
            e.preventDefault();
            e.stopImmediatePropagation();
            const title = getString('loginasuser', 'local_userapproval');
            const form = new TPDynamicForm({
                formClass: 'local_userapproval\\form\\userloginas_form',
                args: {userid: loginasuser.getAttribute('data-userid'), localuserid: loginasuser.getAttribute('data-localuserid')},
                modalConfig: {title},
                returnFocus: loginasuser,
            });
             form.addEventListener(form.events.FORM_SUBMITTED, (event) => {
                const data = event.detail;
                if(data.response == 'success') {
                    window.location.href = data.returnurl;
                } else {
                    HomePage.confirmbox(data.response);
                }
            });
            form.show(); ;
        }

    });
};
