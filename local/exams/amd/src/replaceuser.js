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
 * TODO describe module replaceuser
 *
 * @module     local_exams/replaceuser
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import TPDynamicForm from 'local_trainingprogram/dynamicform';
import Modalform from 'core_form/modalform';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import homepage from 'theme_academy/homepage';
import Ajax from 'core/ajax';
import Templates from 'core/templates';
import {get_string as getString} from 'core/str';
const Selectors = {
    actions: {
        replaceuser: '[data-action="replaceuser"]',
        editprofilecode: '[data-action="profilecode"]',
        edituser: '[data-action="users"]',

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

          let profilecode = e.target.closest(Selectors.actions.editprofilecode);
        if (profilecode) {
            e.preventDefault();
            const title = getString('updateprofilecode', 'local_exams');
            const form = new Modalform({
                formClass: 'local_exams\\form\\updateprofilecode',
                args: {id: profilecode.getAttribute('data-id'),
                    profileid: profilecode.getAttribute('data-profileid'),
                    examid:profilecode.getAttribute('data-examid'),
                    userid:profilecode.getAttribute('data-userid'),

                },
                 modalConfig: {title},
                returnFocus: profilecode,
            });
         form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();   
        } 


        let listorg = e.target.closest(Selectors.actions.edituser);
        if (listorg) {
            e.preventDefault();
            const title = getString('edituser', 'local_exams');
            const form = new Modalform({
                formClass: 'local_exams\\form\\updateuser',
                args: {id: listorg.getAttribute('data-id'),
                      examid:listorg.getAttribute('data-examid'),
                      userid:listorg.getAttribute('data-userid'),
                },
                 modalConfig: {title},
                returnFocus: listorg,
            });
         form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();   
        }


        let replaceuser = e.target.closest(Selectors.actions.replaceuser);
        if (replaceuser) {
            e.preventDefault();
            e.stopImmediatePropagation()
            const productid = replaceuser.getAttribute('data-productid');
            const userid = replaceuser.getAttribute('data-userid');
            const username = replaceuser.getAttribute('data-username');
            const entityname = replaceuser.getAttribute('data-entityname');
            const costtype = replaceuser.getAttribute('data-costtype');
            const useridnumber = replaceuser.getAttribute('data-useridnumber');
            const replacementfee = replaceuser.getAttribute('data-replacementfee');
            const isadmin = replaceuser.getAttribute('data-issiteadmin');
            const entitytype = replaceuser.getAttribute('data-entitytype');
            const orgofficialenrolled = replaceuser.getAttribute('data-orgofficialenrolled');
            const ownedby = replaceuser.getAttribute('data-ownedby');
            const cangenerateinvoice = replaceuser.getAttribute('data-cangenerateinvoice');
            var displayparams = {};
            displayparams.username = username;
            displayparams.entityname = entityname;
            displayparams.isadmin = isadmin;
            ModalFactory.create({
                title: getString('replaceconfirm', 'local_exams'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('replaceconfirmmessage', 'local_exams', displayparams)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('replace', 'local_exams'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    if(ownedby == 'CISI' || ownedby == 'CII') {
                        HomePage.confirmbox(getString('notallowedtoreplacemessage', 'local_exams'));
                    } else {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                          modal.hide();
                        if(costtype == 0 || (isadmin == 1 && orgofficialenrolled == 0) || cangenerateinvoice == 0) {
                           
                            const title = getString('replaceuser', 'local_exams');
                            const form = new TPDynamicForm({
                                formClass: 'local_exams\\form\\replaceuserform',
                                args: {productid: replaceuser.getAttribute('data-productid'),userid: replaceuser.getAttribute('data-userid'),username: replaceuser.getAttribute('data-username'),useridnumber: replaceuser.getAttribute('data-useridnumber'),entitytype: replaceuser.getAttribute('data-entitytype') ,costtype: replaceuser.getAttribute('data-costtype'),policyconfirm:0},
                                modalConfig: {title},
                                returnFocus: replaceuser,
                            });
                            form.addEventListener(form.events.FORM_SUBMITTED, (event) => {
                                event.preventDefault();
                                e.preventDefault();
                                Templates.renderForPromise('tool_product/loader', {}).then(({html, js}) => {
                                    Templates.appendNodeContents('.modal-content', html, js);
                                });
                                let productdata;
                                productdata = event.detail;
                                console.log(productdata);
                                var replaceparams = {};
                                replaceparams.productid=productdata.productid;
                                replaceparams.rootid = productdata.rootid;
                                replaceparams.fieldid = productdata.fieldid;
                                replaceparams.fromuserid = productdata.fromuserid;
                                replaceparams.touserid = productdata.touserid;
                                replaceparams.replacementfee = replacementfee;
                                replaceparams.entitytype = entitytype;
                                replaceparams.policyconfirm = 0;
                                replaceparams.costtype = costtype;
                                replaceparams.enrollinguserid = productdata.enrollinguserid;
                                replaceparams.cangenerateinvoice = cangenerateinvoice;
                                var promise = Ajax.call([{
                                    methodname: 'local_exams_replaceuser',
                                    args: replaceparams
                                }]);
                                promise[0].done(function(resp) {
                                    if(resp.response == 'success') {
                                        if(entitytype =='exam') {
                                            window.location = M.cfg.wwwroot + '/local/exams/examusers.php?id='+productdata.rootid;
                                        } else if(entitytype =='trainingprogram') {
                                            window.location = M.cfg.wwwroot + '/local/trainingprogram/programenrolleduserslist.php?programid='+productdata.rootid;
                                        } else {
                                            window.location = M.cfg.wwwroot + '/local/events/attendees.php?id='+productdata.rootid;
                                        }
                                    } else {
                                        modal.hide();
                                        HomePage.confirmbox(resp.response);
                                    }
                                }).fail(function(err) {
                                    HomePage.confirmbox(err.message);
                                    //console.log('exception');
                                });
                            });
                            form.show();
                        } else if(isadmin == 1 && orgofficialenrolled == 1) {
                            modal.hide();
                            const title = getString('reasonreplace', 'local_exams');
                            const form = new TPDynamicForm({
                                formClass: 'local_exams\\form\\reasonform',
                                args: {entitytype: entitytype,userid: userid, productid: productid},
                                modalConfig: {title},
                                returnFocus: replaceuser,
                            });
                            form.addEventListener(form.events.FORM_SUBMITTED, (event) => {
                                const data = event.detail;
                                Templates.renderForPromise('tool_product/loader', {}).then(({html, js}) => {
                                    Templates.appendNodeContents('.modal-content', html, js);
                                });
                                if(data.policy > 0) {
                                   var policyconfirm = 1;
                                    ModalFactory.create({                               
                                        type: ModalFactory.types.SAVE_CANCEL,
                                        body: getString('replacementproceedmessage', 'local_exams',replacementfee)
                                    }).done(function(modal) {
                                        this.modal = modal;
                                        modal.setSaveButtonText(getString('confirm', 'local_exams'));
                                        modal.getRoot().on(ModalEvents.save, function(e) {
                                            e.preventDefault();
                                            e.stopImmediatePropagation();
                                            modal.hide();
                                            const title = getString('replaceuser', 'local_exams');
                                            const form = new TPDynamicForm({
                                                formClass: 'local_exams\\form\\replaceuserform',
                                                args: {productid: replaceuser.getAttribute('data-productid'),userid: replaceuser.getAttribute('data-userid'),username: replaceuser.getAttribute('data-username'),useridnumber: replaceuser.getAttribute('data-useridnumber'),entitytype: replaceuser.getAttribute('data-entitytype'),costtype: replaceuser.getAttribute('data-costtype'),policyconfirm:1},
                                                modalConfig: {title},
                                                returnFocus: replaceuser,
                                            });
                                            form.addEventListener(form.events.FORM_SUBMITTED, (event) => {
                                                event.preventDefault();
                                                e.preventDefault();
                                                Templates.renderForPromise('tool_product/loader', {}).then(({html, js}) => {
                                                    Templates.appendNodeContents('.modal-content', html, js);
                                                });
                                                let productdata;
                                                productdata = event.detail;
                                                console.log(productdata);
                                                var replaceparams = {};
                                                replaceparams.productid=productdata.productid;
                                                replaceparams.rootid = productdata.rootid;
                                                replaceparams.fieldid = productdata.fieldid;
                                                replaceparams.fromuserid = productdata.fromuserid;
                                                replaceparams.touserid = productdata.touserid;
                                                replaceparams.replacementfee = replacementfee;
                                                replaceparams.entitytype = entitytype;
                                                replaceparams.policyconfirm = 1;
                                                replaceparams.costtype = costtype;
                                                replaceparams.enrollinguserid = productdata.enrollinguserid;
                                                replaceparams.cangenerateinvoice = cangenerateinvoice;
                                                var promise = Ajax.call([{
                                                    methodname: 'local_exams_replaceuser',
                                                    args: replaceparams
                                                }]);
                                                Templates.render('tool_product/loader');
                                                promise[0].done(function(resp) {
                                                    if(resp.response == 'success') {
                                                        if(entitytype =='exam') {
                                                            window.location = M.cfg.wwwroot + '/local/exams/examusers.php?id='+productdata.rootid;
                                                        } else if(entitytype =='trainingprogram') {
                                                            window.location = M.cfg.wwwroot + '/local/trainingprogram/programenrolleduserslist.php?programid='+productdata.rootid;
                                                        } else {
                                                            window.location = M.cfg.wwwroot + '/local/events/attendees.php?id='+productdata.rootid;
                                                        }
                                                    } else {
                                                        modal.hide();
                                                        HomePage.confirmbox(resp.response);
                                                    }
                                                }).fail(function(err) {
                                                    HomePage.confirmbox(err.message);
                                                    //console.log('exception');
                                                });
                                            });
                                            form.show();
                                        }.bind(this));
                                       modal.show();
                                    }.bind(this));
                                } else {
                                   var policyconfirm = 0;
                                   e.preventDefault();
                                    e.stopImmediatePropagation();
                                    modal.hide();
                                    const title = getString('replaceuser', 'local_exams');
                                    const form = new TPDynamicForm({
                                        formClass: 'local_exams\\form\\replaceuserform',
                                        args: {productid: replaceuser.getAttribute('data-productid'),userid: replaceuser.getAttribute('data-userid'),username: replaceuser.getAttribute('data-username'),useridnumber: replaceuser.getAttribute('data-useridnumber'),entitytype: replaceuser.getAttribute('data-entitytype'),costtype: replaceuser.getAttribute('data-costtype'),policyconfirm:0},
                                        modalConfig: {title},
                                        returnFocus: replaceuser,
                                    });
                                    form.addEventListener(form.events.FORM_SUBMITTED, (event) => {
                                        event.preventDefault();
                                        e.preventDefault();
                                        Templates.renderForPromise('tool_product/loader', {}).then(({html, js}) => {
                                            Templates.appendNodeContents('.modal-content', html, js);
                                        });
                                        let productdata;
                                        productdata = event.detail;
                                        console.log(productdata);
                                        var replaceparams = {};
                                        replaceparams.productid=productdata.productid;
                                        replaceparams.rootid = productdata.rootid;
                                        replaceparams.fieldid = productdata.fieldid;
                                        replaceparams.fromuserid = productdata.fromuserid;
                                        replaceparams.touserid = productdata.touserid;
                                        replaceparams.replacementfee = replacementfee;
                                        replaceparams.entitytype = entitytype;
                                        replaceparams.policyconfirm = 0;
                                        replaceparams.costtype = costtype;
                                        replaceparams.enrollinguserid = productdata.enrollinguserid;
                                        replaceparams.cangenerateinvoice = cangenerateinvoice;
                                        
                                        var promise = Ajax.call([{
                                            methodname: 'local_exams_replaceuser',
                                            args: replaceparams
                                        }]);
                                        Templates.render('tool_product/loader');
                                        promise[0].done(function(resp) {
                                            if(resp.response == 'success') {
                                                if(entitytype =='exam') {
                                                    window.location = M.cfg.wwwroot + '/local/exams/examusers.php?id='+productdata.rootid;
                                                } else if(entitytype =='trainingprogram') {
                                                    window.location = M.cfg.wwwroot + '/local/trainingprogram/programenrolleduserslist.php?programid='+productdata.rootid;
                                                } else {
                                                    window.location = M.cfg.wwwroot + '/local/events/attendees.php?id='+productdata.rootid;
                                                }
                                            } else {
                                                modal.hide();
                                                HomePage.confirmbox(resp.response);
                                            }
                                        }).fail(function(err) {
                                            HomePage.confirmbox(err.message);
                                            //console.log('exception');
                                        });
                                    });
                                    form.show();
                                }
                            });
                            form.show();
                        } else {
                            ModalFactory.create({                               
                                type: ModalFactory.types.SAVE_CANCEL,
                                body: getString('replacementproceedmessage', 'local_exams',replacementfee)
                            }).done(function(modal) {
                                this.modal = modal;
                                modal.setSaveButtonText(getString('confirm', 'local_exams'));
                                modal.getRoot().on(ModalEvents.save, function(e) {
                                    e.preventDefault();
                                    e.stopImmediatePropagation();
                                    modal.hide();
                                    const title = getString('replaceuser', 'local_exams');
                                    const form = new TPDynamicForm({
                                        formClass: 'local_exams\\form\\replaceuserform',
                                        args: {productid: replaceuser.getAttribute('data-productid'),userid: replaceuser.getAttribute('data-userid'),username: replaceuser.getAttribute('data-username'),useridnumber: replaceuser.getAttribute('data-useridnumber'),entitytype: replaceuser.getAttribute('data-entitytype'),costtype: replaceuser.getAttribute('data-costtype')},
                                        modalConfig: {title},
                                        returnFocus: replaceuser,
                                    });
                                    form.addEventListener(form.events.FORM_SUBMITTED, (event) => {
                                        event.preventDefault();
                                        e.preventDefault();
                                        Templates.renderForPromise('tool_product/loader', {}).then(({html, js}) => {
                                            Templates.appendNodeContents('.modal-content', html, js);
                                        });
                                        let productdata;
                                        productdata = event.detail;
                                        console.log(productdata);
                                        var replaceparams = {};
                                        replaceparams.productid=productdata.productid;
                                        replaceparams.rootid = productdata.rootid;
                                        replaceparams.fieldid = productdata.fieldid;
                                        replaceparams.fromuserid = productdata.fromuserid;
                                        replaceparams.touserid = productdata.touserid;
                                        replaceparams.replacementfee = replacementfee;
                                        replaceparams.entitytype = entitytype;
                                        replaceparams.policyconfirm = 1;
                                        replaceparams.costtype = costtype;
                                        replaceparams.enrollinguserid = productdata.enrollinguserid;
                                        replaceparams.cangenerateinvoice = cangenerateinvoice;
                                        
                                        var promise = Ajax.call([{
                                            methodname: 'local_exams_replaceuser',
                                            args: replaceparams
                                        }]);
                                        Templates.render('tool_product/loader');
                                        promise[0].done(function(resp) {
                                            if(resp.response == 'success') {
                                                if(entitytype =='exam') {
                                                    window.location = M.cfg.wwwroot + '/local/exams/examusers.php?id='+productdata.rootid;
                                                } else if(entitytype =='trainingprogram') {
                                                    window.location = M.cfg.wwwroot + '/local/trainingprogram/programenrolleduserslist.php?programid='+productdata.rootid;
                                                } else {
                                                    window.location = M.cfg.wwwroot + '/local/events/attendees.php?id='+productdata.rootid;
                                                }
                                            } else {
                                                modal.hide();
                                                HomePage.confirmbox(resp.response);
                                            }
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
                    }
                }.bind(this));
                modal.show();
            }.bind(this));
        }



    });

};
