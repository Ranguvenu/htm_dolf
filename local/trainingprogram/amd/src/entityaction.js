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
 * TODO describe module entityaction
 *
 * @module     local_trainingprogram/entityaction
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
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
        cancelentity: '[data-action="cancelentity"]',
        publishorunpublishentity: '[data-action="publishorunpublishentity"]',
        update_financially_closed_status: '[data-action="update_financially_closed_status"]',
    },
};
let HomePage = new homepage();
export const init = () => {
    document.addEventListener('click', function(e) {
        let cancelentity = e.target.closest(Selectors.actions.cancelentity);
        if (cancelentity) {
            e.preventDefault();
            e.stopImmediatePropagation()
            const rootid = cancelentity.getAttribute('data-rootid');
            const fieldid = cancelentity.getAttribute('data-fieldid');
            const productid = cancelentity.getAttribute('data-productid');
            const fieldcode = cancelentity.getAttribute('data-fieldcode');
            const entitytype = cancelentity.getAttribute('data-entitytype');
            const currentuser = cancelentity.getAttribute('data-currentuser');
            const costtype = cancelentity.getAttribute('data-costtype');
            const requestby = cancelentity.getAttribute('data-requestby')
            const requesttype = cancelentity.getAttribute('data-requesttype')
            const hasenrollments = cancelentity.getAttribute('data-hasenrollments')
            var displayparams = {};
            displayparams.rootid = rootid;
            displayparams.fieldid = fieldid;
            displayparams.fieldcode = fieldcode;
            displayparams.type = entitytype;
            displayparams.requestby = requestby;
            displayparams.requesttype = requesttype;
            if(requesttype =='cancelentity') {
                var headermessage = getString('cancelconfirm', 'local_trainingprogram');
                var bodymessage = getString('entitycancelconfirmmessage', 'local_trainingprogram', displayparams);
            } else if(requesttype =='approvecancelrequest') {
                var headermessage = getString('approveconfirm', 'local_trainingprogram');
                var bodymessage = getString('approveentitycancelconfirmmessage', 'local_trainingprogram', displayparams);
            } else {
                var headermessage = getString('rejectconfirm', 'local_trainingprogram');
                var bodymessage = getString('rejectentitycancelconfirmmessage', 'local_trainingprogram', displayparams);
            }
            ModalFactory.create({
                title: headermessage,
                type: ModalFactory.types.SAVE_CANCEL,
                body: bodymessage
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('yes', 'local_trainingprogram'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    Templates.renderForPromise('tool_product/loader', {}).then(({html, js}) => {
                        Templates.appendNodeContents('.modal-content', html, js);
                    });
                    if(requesttype =='cancelentity') {
                        const title = getString('reasoncancel', 'local_exams');
                        const form = new Modalform({
                            formClass: 'local_exams\\form\\reasonform',
                            args: {entitytype: entitytype, userid: 0, productid: productid},
                            modalConfig: {title},
                            returnFocus: cancelentity,
                        });
                        form.addEventListener(form.events.FORM_SUBMITTED, (event) => {
                            const data = event.detail;
                            var params = {};
                            params.rootid = rootid;
                            params.fieldid = fieldid;
                            params.productid = productid;
                            params.fieldcode = fieldcode;
                            params.entitytype = entitytype;
                            params.costtype = costtype;
                            params.currentuser = currentuser;
                            params.requesttype =requesttype;
                            params.hasenrollments =hasenrollments;
                            var promise = Ajax.call([{
                                methodname: 'local_trainingprogram_cancelentity',
                                args: params
                            }]);
                            promise[0].done(function(resp) {
                                if(resp.response == 'success') {
                                    if(currentuser =='admin' || ((currentuser =='ts' || currentuser == 'eventmanager') && (requesttype == 'approvecancelrequest' || requesttype == 'rejectcancelrequest'))) { 
                                        window.location.href = resp.returnurl;
                                    } else {
                                        if(currentuser =='financial_manager') {
                                          var bodayMessage  = getString('tofficialcancelresponse', 'local_trainingprogram',displayparams);
                                        } else {
                                            if(entitytype == 'event') {

                                                var bodayMessage  =  getString('emcancelresponse', 'local_trainingprogram',displayparams);

                                            } else {
                                                if(hasenrollments == 1) {
                                                    var bodayMessage  =  getString('faofficialcancelresponse', 'local_trainingprogram',displayparams);
                                                } else {
                                                    var bodayMessage  =  getString('tofficialcancelresponse', 'local_trainingprogram',displayparams);
                                                }
                                            }
                                            
                                        }
                                        HomePage.confirmbox(bodayMessage);
                                        setTimeout(function() {
                                            location.reload();
                                        },4000);
                                    }   
                                } else {
                                    modal.hide();
                                    HomePage.confirmbox(resp.response);
                                }
                            }).fail(function(err) {
                                HomePage.confirmbox(err.message);
                            });
                                
                        });
                        form.show();
                    } else {
                       
                        var params = {};
                        params.rootid = rootid;
                        params.fieldid = fieldid;
                        params.productid = productid;
                        params.fieldcode = fieldcode;
                        params.entitytype = entitytype;
                        params.costtype = costtype;
                        params.currentuser = currentuser;
                        params.requesttype =requesttype;
                        params.hasenrollments =hasenrollments;
                        var promise = Ajax.call([{
                            methodname: 'local_trainingprogram_cancelentity',
                            args: params
                        }]);
                        promise[0].done(function(resp) {
                            if(resp.response == 'success') {
                                if(currentuser =='admin' || ((currentuser =='ts' || currentuser == 'eventmanager') && (requesttype == 'approvecancelrequest' || requesttype == 'rejectcancelrequest'))) { 
                                    window.location.href = resp.returnurl;
                                } else {
                                    if(currentuser =='financial_manager') {
                                        var bodayMessage  = getString('tofficialcancelresponse', 'local_trainingprogram',displayparams);
                                      } else {
                                        if(entitytype == 'event') {

                                        var bodayMessage  =  getString('emcancelresponse', 'local_trainingprogram',displayparams);

                                        } else {
                                            if(hasenrollments == 1) {
                                                var bodayMessage  =  getString('faofficialcancelresponse', 'local_trainingprogram',displayparams);
                                            } else {
                                                var bodayMessage  =  getString('tofficialcancelresponse', 'local_trainingprogram',displayparams);
                                            }
                                        }
                                          
                                      }
                                    HomePage.confirmbox(bodayMessage);
                                    setTimeout(function() {
                                        location.reload();
                                    },4000);
                                }   
                            } else {
                                modal.hide();
                                HomePage.confirmbox(resp.response);
                            }
                        }).fail(function(err) {
                            HomePage.confirmbox(err.message);
                        });

                    }
                   
                }.bind(this));
                modal.show();
            }.bind(this));
        }

        let publishorunpublishentity = e.target.closest(Selectors.actions.publishorunpublishentity);
        if (publishorunpublishentity) {
             e.preventDefault();
            const id = publishorunpublishentity.getAttribute('data-id');
            const code = publishorunpublishentity.getAttribute('data-code');
            const entitytype = publishorunpublishentity.getAttribute('data-entitytype');
            const actiontype = publishorunpublishentity.getAttribute('data-actiontype');
            var displayparams = {};
            displayparams.code = code;
            displayparams.entitytype = entitytype;
            if(actiontype =='publish') {
                var headermessage = getString('publishconfirm', 'local_trainingprogram');
                var bodymessage = getString('entitypublishconfirm', 'local_trainingprogram',displayparams);
                var footermessage = getString('publishtext', 'local_trainingprogram');
            } else {
                var headermessage = getString('unpublishconfirm', 'local_trainingprogram');
                var bodymessage = getString('entityunpublishconfirm', 'local_trainingprogram',displayparams);
                var footermessage = getString('unpublishtext', 'local_trainingprogram');
            }
            ModalFactory.create({
                title: headermessage,
                type: ModalFactory.types.SAVE_CANCEL,
                body: bodymessage
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(footermessage);
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.id = id;
                    params.code = code;
                    params.entitytype = entitytype;
                    params.actiontype = actiontype;
                    var promise = Ajax.call([{
                        methodname: 'local_trainingprogram_publishorunpublishoffering',
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
        let update_financially_closed_status = e.target.closest(Selectors.actions.update_financially_closed_status);
        if (update_financially_closed_status) {
             e.preventDefault();
            const id = update_financially_closed_status.getAttribute('data-id');
            const code = update_financially_closed_status.getAttribute('data-code');
            const actiontype = update_financially_closed_status.getAttribute('data-actiontype');

            if(actiontype =='open_fc_offering') {
                var headermessage = getString('open_fc_confirm', 'local_trainingprogram');
                var bodymessage = getString('openfcofferingconfirm', 'local_trainingprogram',code);
                var footermessage = getString('yes', 'local_trainingprogram');
            } else {
                var headermessage = getString('close_fc_confirm', 'local_trainingprogram');
                var bodymessage = getString('closefcofferingconfirm', 'local_trainingprogram',code);
                var footermessage = getString('yes', 'local_trainingprogram');
            }
            ModalFactory.create({
                title: headermessage,
                type: ModalFactory.types.SAVE_CANCEL,
                body: bodymessage
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(footermessage);
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.id = id;
                    params.code = code;
                    params.actiontype = actiontype;
                    var promise = Ajax.call([{
                        methodname: 'local_trainingprogram_update_offering_financially_closed_status',
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
