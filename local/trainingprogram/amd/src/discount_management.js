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
 * TODO describe module discount_management
 *
 * @module     local_trainingprogram/discount_management
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import TPDynamicForm from 'local_trainingprogram/dynamicform';
import ModalForm from 'core_form/modalform';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import Ajax from 'core/ajax';
import {get_string as getString} from 'core/str';
const Selectors = {
    actions: {
        createcoupon: '[data-action="createcoupon"]',
        couponmail: '[data-action="couponmail"]',
        deletecoupon: '[data-action="deletecoupon"]',

        createearlyregistration: '[data-action="createearlyregistration"]',
        deleteearlyregistration: '[data-action="deleteearlyregistration"]',

        creatediscountgroups: '[data-action="creatediscountgroups"]',
        deletediscountgroups: '[data-action="deletediscountgroups"]',

        viewdiscountentity: '[data-action="viewdiscountentity"]',

        
    },
};
export const init = () => {
    document.addEventListener('click', function(e) {
        let couponElement = e.target.closest(Selectors.actions.createcoupon);
        if (couponElement) {
            e.preventDefault();
            e.stopImmediatePropagation();
            const title = couponElement.getAttribute('data-id') ?
                getString('editcoupon', 'local_trainingprogram') :
                getString('createcoupon', 'local_trainingprogram');
            const form = new TPDynamicForm({
                formClass: 'local_trainingprogram\\form\\couponform',
                args: {id: couponElement.getAttribute('data-id')},
                modalConfig: {title},
                returnFocus: couponElement,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }
        let couponmail = e.target.closest(Selectors.actions.couponmail);
        if (couponmail) {
            e.preventDefault();
            e.stopImmediatePropagation();
            const title = getString('couponmail', 'local_trainingprogram');
            const form = new TPDynamicForm({
                formClass: 'local_trainingprogram\\form\\coupon_mail_form',
                args: {couponid: couponmail.getAttribute('data-couponid'),couponcode: couponmail.getAttribute('data-couponcode')},
                modalConfig: {title},
                returnFocus: couponmail,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }
        let deletecoupon = e.target.closest(Selectors.actions.deletecoupon);
        if (deletecoupon) {
            e.preventDefault();
            e.stopImmediatePropagation()
            const couponid = deletecoupon.getAttribute('data-couponid');
            const couponcode = deletecoupon.getAttribute('data-couponcode');
            ModalFactory.create({
                title: getString('couponconfirm', 'local_trainingprogram'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('deletecouponconfirm', 'local_trainingprogram',couponcode)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('deletetext', 'local_trainingprogram'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.couponid = couponid;
                    params.couponcode = couponcode;
                    var promise = Ajax.call([{
                        methodname: 'local_trainingprogram_deletecoupon',
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

        let earlyregistrationelement = e.target.closest(Selectors.actions.createearlyregistration);
        if (earlyregistrationelement) {
            e.preventDefault();
            e.stopImmediatePropagation();
            const title = earlyregistrationelement.getAttribute('data-id') ?
                getString('editearlyregistration', 'local_trainingprogram') :
                getString('createearlyregistration', 'local_trainingprogram');
            const form = new TPDynamicForm({
                formClass: 'local_trainingprogram\\form\\earlyregistrationform',
                args: {id: earlyregistrationelement.getAttribute('data-id')},
                modalConfig: {title},
                returnFocus: earlyregistrationelement,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }
        let deleteearlyregistration = e.target.closest(Selectors.actions.deleteearlyregistration);
        if (deleteearlyregistration) {
            e.preventDefault();
            e.stopImmediatePropagation()
            const earlyregistrationid = deleteearlyregistration.getAttribute('data-earlyregistrationid');
            const days = deleteearlyregistration.getAttribute('data-days');
            const discount= deleteearlyregistration.getAttribute('data-discount');
            var displayparams = {};
            displayparams.days = days;
            displayparams.discount = discount;
            ModalFactory.create({
                title: getString('earlyregistrationconfirm', 'local_trainingprogram'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('deleteearlyregistrationconfirm', 'local_trainingprogram',displayparams)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('deletetext', 'local_trainingprogram'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.earlyregistrationid = earlyregistrationid;
                    params.days = days;
                    params.discount = discount;
                    var promise = Ajax.call([{
                        methodname: 'local_trainingprogram_deleteearlyregistration',
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
        let creatediscountgroups = e.target.closest(Selectors.actions.creatediscountgroups);
        if (creatediscountgroups) {
            e.preventDefault();
            e.stopImmediatePropagation();
            const title = creatediscountgroups.getAttribute('data-id') ?
                getString('editgroupdiscount', 'local_trainingprogram') :
                getString('creategroupdiscount', 'local_trainingprogram');
            const form = new TPDynamicForm({
                formClass: 'local_trainingprogram\\form\\groupdiscountsform',
                args: {id: creatediscountgroups.getAttribute('data-id')},
                modalConfig: {title},
                returnFocus: creatediscountgroups,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }
        let deletediscountgroups = e.target.closest(Selectors.actions.deletediscountgroups);
        if (deletediscountgroups) {
            e.preventDefault();
            e.stopImmediatePropagation()
            const id = deletediscountgroups.getAttribute('data-id');
            const count = deletediscountgroups.getAttribute('data-count');
            const discount= deletediscountgroups.getAttribute('data-discount');
            var displayparams = {};
            displayparams.count = count;
            displayparams.discount = discount;
            ModalFactory.create({
                title: getString('deletegroupdiscountconfirm', 'local_trainingprogram'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('deletedeletegroupdiscountconfirm', 'local_trainingprogram',displayparams)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('deletetext', 'local_trainingprogram'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.id = id;
                    var promise = Ajax.call([{
                        methodname: 'local_trainingprogram_deletegroupdiscount',
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
        let viewdiscountentity = e.target.closest(Selectors.actions.viewdiscountentity);
        if (viewdiscountentity) {
            const entityid = viewdiscountentity.getAttribute('data-id');
            const entitytype = viewdiscountentity.getAttribute('data-entitytype');
            var params = {};
            params.entityid = entityid;
            params.entitytype = entitytype;
            var promise = Ajax.call([{
                methodname: 'local_trainingprogram_viewdiscountentity',
                args: params
            }]);
            promise[0].done(function(resp) {
                if(entitytype == 'coupon') {
                    var titlestring = 'Coupon Discount'
                } else if(entitytype == 'group') {
                    var titlestring = 'Group Discount'
                } else {
                    var titlestring = 'Earlyregistration Discount'
                }
                ModalFactory.create({
                    title: getString('view_entity', 'local_trainingprogram',titlestring),
                    type: ModalFactory.types.DEFAULT,
                    body: resp.options,
                }).done(function(modal) {
                    this.modal = modal;
                    this.modal.setLarge();
                    modal.show();
                }.bind(this));
            }).fail(function() {
                console.log('exception');
            });
        }
    });
};

