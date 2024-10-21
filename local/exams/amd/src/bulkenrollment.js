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
 * TODO describe module bulkenrollment
 *
 * @module     local_exams/bulkenrollment
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
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
        removeuserconfirmation: '[data-action="removeuserconfirmation"]',
        confirmbulkenrolluser: '[data-action="confirmbulkenrolluser"]',
        
    },
};
let HomePage = new homepage();
export const init = () => {
    document.addEventListener('click', function(e) {
        let removeuserconfirmation = e.target.closest(Selectors.actions.removeuserconfirmation);
        if (removeuserconfirmation) {
            e.preventDefault();
            e.stopImmediatePropagation()
            const rootid = removeuserconfirmation.getAttribute('data-rootid');
            const fieldid = removeuserconfirmation.getAttribute('data-fieldid');
            const userid = removeuserconfirmation.getAttribute('data-id');
            const type = removeuserconfirmation.getAttribute('data-type');
            const userfullname = removeuserconfirmation.getAttribute('data-name');
            const idnumber = removeuserconfirmation.getAttribute('data-idnumber');
            var displayparams = {};
            displayparams.userid = userid;
            displayparams.userfullname = userfullname;
            displayparams.idnumber = idnumber;
            ModalFactory.create({
                title: getString('removeconfirm', 'local_exams'),
                type: ModalFactory.types.SAVE_CANCEL,
                body:getString('removeenrollmentconfirmmessage', 'local_exams', displayparams)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('yes', 'local_exams'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    modal.hide();
                    var params = {};
                    params.rootid = rootid;
                    params.fieldid = fieldid;
                    params.userid = userid;
                    params.type = type;
                    var promise = Ajax.call([{
                        methodname: 'local_exams_removeenrollmentconfirmation',
                        args: params
                    }]);
                    promise[0].done(function(resp) {
                        promise[0].done(function(resp) {
                            window.location.reload(true);
                        }).fail(function() {
                            console.log('exception');
                        }); 
                    }).fail(function(err) {
                        HomePage.confirmbox(err.message);
                    });
                }.bind(this));
                modal.show();
            }.bind(this));
        }

        let confirmbulkenrolluser = e.target.closest(Selectors.actions.confirmbulkenrolluser);
        if (confirmbulkenrolluser) {
            e.preventDefault();
            e.stopImmediatePropagation()
            const rootid = confirmbulkenrolluser.getAttribute('data-rootid');
            const fieldid = confirmbulkenrolluser.getAttribute('data-fieldid');
            const roleid = confirmbulkenrolluser.getAttribute('data-roleid');
            const cusers = confirmbulkenrolluser.getAttribute('data-cusers');
            const organization = confirmbulkenrolluser.getAttribute('data-organization');
            const scheduleid = confirmbulkenrolluser.getAttribute('data-scheduleid');
            const orgofficial = confirmbulkenrolluser.getAttribute('data-orgofficial');
            const actionfor = confirmbulkenrolluser.getAttribute('data-actionfor');
            const discount = confirmbulkenrolluser.getAttribute('data-discount');
            const discounttableid = confirmbulkenrolluser.getAttribute('data-discounttableid');
            const discounttype = confirmbulkenrolluser.getAttribute('data-discounttype');
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
                    e.preventDefault();
                        var params = {};
                        params.rootid = rootid;
                        params.fieldid = fieldid;
                        params.roleid = roleid;
                        params.cusers = cusers;
                        params.organization = organization;
                        params.scheduleid = scheduleid;
                        params.orgofficial = orgofficial;
                        params.actionfor = actionfor;
                        var promise = Ajax.call([{
                            methodname: 'local_exams_registerbulkenrollusers',
                            args: params
                        }]);
                        promise[0].done(function(resp) {
                            if(resp.response == 'success') {
                                var params = {};
                                params.rootid = rootid;
                                params.fieldid = fieldid;
                                params.roleid = roleid;
                                params.scheduleid = scheduleid;
                                params.type = 'bulkenrollment';
                                params.tuserid = cusers;
                                params.organization = organization;
                                params.orgofficial = orgofficial;
                                params.actionfor = actionfor;
                                params.discount = discount;
                                params.discounttableid = discounttableid;
                                params.discounttype = discounttype;
                                var promise = Ajax.call([{
                                    methodname: 'local_exams_generate_sadad_for_bulkenrollusers',
                                    args: params
                                }]);
                                promise[0].done(function(resp) {
                                    if(resp.response == 'success') {
                                        window.location.href = resp.returnurl;
                                    } else {
                                        modal.hide();
                                        HomePage.confirmbox(resp.response);
                                    }
        
                                }).fail( (error) => {
                                    HomePage.confirmbox(error.message);
                                });

                            } else {
                                modal.hide();
                                HomePage.confirmbox(resp.response);
                            }
                        
                        }).fail(function(err) {
                            HomePage.confirmbox(err.message);
                        });
                    
                }.bind(this));
                modal.show();
            }.bind(this));
        }
    });
};
