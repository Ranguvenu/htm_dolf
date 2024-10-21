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
import DynamicForm from 'core_form/dynamicform';
import {get_string as getString} from 'core/str';
import ModalFactory from 'core/modal_factory';
import ModalForm from 'core_form/modalform';
import ModalEvents from 'core/modal_events';
import Ajax from 'core/ajax';
import Templates from 'core/templates';
import Notification from 'core/notification';
// import homepage from 'theme_academy/homepage';

const Selectors = {
    actions: {
        editexamenrol: '[data-action="editexamenrol"]',
    },
};

// let HomePage = new homepage();
export const init = () => {
    document.addEventListener('click', function(e) {
        e.stopImmediatePropagation();

        let element = e.target.closest(Selectors.actions.editexamenrol);
        if (element) {
            e.preventDefault();
            const title = element.getAttribute('data-id') ?
                getString('editexamenrol', 'local_exams', element.getAttribute('data-name')) :
                getString('editexamenrol', 'local_exams');
            const form = new TPDynamicForm({
                formClass: 'local_exams\\form\\examenrolform',
                args: {id: element.getAttribute('data-id')},
                modalConfig: {title},
                returnFocus: element,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }
    });
    /**
     * If Assesment operator will enroll users to an exam profile.
     * 
     */
    let selectElement =  document.querySelector('.unenrolled_users');
    const examid = selectElement.getAttribute('data-examid');
    const productid = selectElement.getAttribute('data-productid');
    const entitytype = selectElement.getAttribute('data-entitytype');
    const refundtype = selectElement.getAttribute('data-refundtype');
    const productprice = selectElement.getAttribute('data-productprice');
    const ownedby = selectElement.getAttribute('data-ownedby');
    const examname = selectElement.getAttribute('data-examname');
    const profileid = selectElement.getAttribute('data-profileid');

    let getcurrentuserrole =  document.querySelector('#currentuserrole');
    var is_assessmentop = getcurrentuserrole.getAttribute('data-value');
    
    if(ownedby == 'CISI' && is_assessmentop == 'assessmentop') {
        var mform = document.querySelector('.mform');
        mform.style = 'opacity:0.5; pointer-events: none;cursor: not-allowed;';
    }
    $('.enrollusers').click(function (e) {
        if (is_assessmentop == 'assessmentop') {
        e.preventDefault();
            /**
             * If it is a paid exam get exam price
             * 
             */
            
            // Required Parameters
            const userarray = Array.from(selectElement.selectedOptions).map(option => option.value);
            const userids = userarray.join()
            const num_users = userarray.length;
            const form = document.getElementById('user_un_assign');
            const organization = document.getElementById('id_organizationusers');
            const selectedOrganization = organization.value;
            const selectedselectedOrganizationText = organization.options[organization.selectedIndex].text;

            if(ownedby == 'CISI') {
                var mform = document.querySelector('.mform');
                mform.style = 'opacity:0.5; pointer-events: none;cursor: not-allowed;';
            }else{
                if (!selectedOrganization) {
                    var error = getString('error');
                    var message = getString('missingorg', 'local_exams');
                    var ok = getString('ok');
                    Notification.alert(error, message, ok);
                }else{
                    var params = {};
                    params.productid = productid;
                    params.userid = userids;
                    params.entitytype = entitytype;
                    params.refundtype = refundtype;
                    params.productprice = productprice;
                    // Strings parameter
                    let strs = {};
                    strs.productprice = productprice;
                    strs.num_users = num_users;
                    strs.examname = examname;
                    if (params.productprice == 0) {
                        
                        var confrmstring = getString('examenrolment_confirm', 'local_exams', strs);
                        ModalFactory.create({
                            title: getString('examenrolment', 'local_exams'),
                            type: ModalFactory.types.SAVE_CANCEL,
                            body: confrmstring
                        }).done(function(modal) {
                            this.modal = modal;
                            modal.setSaveButtonText(getString('erollusers', 'local_exams'));
                            modal.getRoot().on(ModalEvents.save, function(e) {
                                form.submit();
                            }.bind(this));
                            modal.show();
                        }.bind(this));
                    }else{
                        var confrmstring = getString('paid_examenrolment_confirm', 'local_exams', strs);
                        // User enrolment confirmation
                        ModalFactory.create({
                            title: getString('examenrolment', 'local_exams'),
                            type: ModalFactory.types.SAVE_CANCEL,
                            body: confrmstring
                        }).done(function(modal) {
                            this.modal = modal;
                            modal.setSaveButtonText(getString('click_continue', 'local_exams'));
                            modal.getRoot().on(ModalEvents.save, function(e) {
                                e.preventDefault();
                                // Fetch Exam Officials.
                                var promise = Ajax.call([{
                                    methodname: 'local_exams_get_org_officials',
                                    args: {
                                        orgid: selectedOrganization
                                    },
                                }]);
                                promise[0].done(function (resp) {
                                    console.log(resp);
                                    ModalFactory.create({
                                        title: getString('orgInvoceSummay', 'local_exams'),
                                        type: ModalFactory.types.SAVE_CANCEL,
                                        body: resp.officials,
                                    }).done(function (modal) {
                                        this.modal = modal;
                                        modal.setSaveButtonText(getString('showinvoice', 'local_exams'));

                                        modal.getRoot().on(ModalEvents.save, function(e) {
                                            e.preventDefault();
                                            const orgOfficial = document.querySelector('#id_org_official');
                                            const selectedorgOfficial = orgOfficial.value;
                                            console.log(selectedorgOfficial);
                                            const selectedselectedorgOfficialText = orgOfficial.options[orgOfficial.selectedIndex].text;
                                            if (selectedorgOfficial) {
                                                var promise = Ajax.call([{
                                                    methodname: 'local_exams_calculate_invoice',
                                                    args: {
                                                        productprice: productprice,
                                                        num_users: num_users, 
                                                        orgid: selectedOrganization,
                                                        org_officialid : selectedorgOfficial,
                                                        examid : examid
                                                    },
                                                }]);
                                                promise[0].done(function (resp) {
                                                    console.log(resp);
                                                    ModalFactory.create({
                                                        title: getString('invoice', 'local_exams'),
                                                        type: ModalFactory.types.SAVE_CANCEL,
                                                        // body: getString('invoiceconfirm', 'local_exams', selectedselectedorgOfficialText),
                                                        body: resp.invoice,
                                                    }).done(function (modal) {
                                                        modal.setSaveButtonText(getString('confirmsendinvoice', 'local_exams'));
                                                        this.modal = modal;
                                                        modal.getRoot().on(ModalEvents.save, function(e) {
                                                            e.preventDefault();
                                                            var promise = Ajax.call([{
                                                                methodname: 'local_exams_send_invoice',
                                                                args: {
                                                                    productprice: productprice,
                                                                    // num_users: num_users,
                                                                    users : userids,
                                                                    profileid : profileid,
                                                                    examid: examid,
                                                                    productid : productid,
                                                                    orgid: selectedOrganization,
                                                                    orgofficial : selectedorgOfficial
                                                                },
                                                            }]);
                                                            promise[0].done(function (resp) {
                                                                console.log(resp);
                                                                if (resp.status == 'success') {
                                                                    $('#invoice-container').empty();
                                                                    $('#invoice-container').append(resp.msg);
                                                                    form.submit();
                                                                    this.modal.destroy();
                                                                }
                                                            }).fail(function(exception) {
                                                                var error = getString('exception', 'local_exams');
                                                                var ok = getString('ok');
                                                                console.log(exception);
                                                                Notification.alert(error, exception.message, ok);
                                                            });
                                                        }.bind(this));
                                                        modal.show();
                                                    }.bind(this));
                                                });
                                            }
                                        }.bind(this));
                                        modal.show();
                                    }.bind(this));
                                })
                            }.bind(this));
                            modal.show();
                        }.bind(this));
                    }
                }
            }
        }
    });
    // END
};
