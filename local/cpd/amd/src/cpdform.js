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

import ModalForm from 'core_form/modalform';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import Ajax from 'core/ajax';
import Notification from 'core/notification';
import HomePageJs from "theme_academy/homepage";
import {get_string as getString} from 'core/str';
require(['jquery'], function($) {
    $(document).ready(function(){
        $('body').on('change','input[name="evidencetype"]',function(){
            var selected_type = $(this).val();
            $('.tagscontainer').addClass('invisible1');
            if(selected_type==1){
              $('.tagscontainer[data-tagtype="1"]').removeClass('invisible1');
            }
            if(selected_type==2){
                $('.tagscontainer[data-tagtype="2"]').removeClass('invisible1');
              }
          });
    });
  });
const Selectors = {
    actions: {
        editCategory: '[data-action="createcpd"]',
        deletecpd: '[data-action="deletecpd"]',
        createformalevid: '[data-action="createformalevid"]',
        createinformalevid: '[data-action = "createinformalevid"]',
        createevidence: '[data-action = "createevidence"]',
        evidencestatus: '[data-action = "evidencestatus"]',
        evidenceuserview: '[data-action = "evidenceuserview"]',
        viewevidence : '[data-action = "viewevidence"]',
        deleteevidence :  '[data-action = "deleteevidence"]',
        edittrainigprogram: '[data-action = "edittrainigprogram"]',
        deletetrainigprogram: '[data-action = "deletetrainigprogram"]',
        evidence_rejectstatus:  '[data-action = "evidence_rejectstatus"]',
    },
};

export const init = () => {
    document.addEventListener('click', function(e) {
        e.stopImmediatePropagation();
        let rejectstatus = e.target.closest(Selectors.actions.evidence_rejectstatus);
        if (rejectstatus) {
            e.preventDefault();
            const title = getString('rejectionreason', 'local_cpd');
            const form = new ModalForm({
                formClass: 'local_cpd\\form\\evidence_rejection_form',
                args: {evedid: rejectstatus.getAttribute('data-id')},
                modalConfig: {title},
                returnFocus: rejectstatus,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }
        let element = e.target.closest(Selectors.actions.editCategory);
        if (element) {
            e.preventDefault();
            const title = element.getAttribute('data-id') ?
                getString('editcpd', 'local_cpd', element.getAttribute('data-name')) :
                getString('addnewcpd', 'local_cpd');
            const form = new ModalForm({
                formClass: 'local_cpd\\form\\form',
                args: {id: element.getAttribute('data-id')},
                modalConfig: {title},
                returnFocus: element,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }
        let deleteelement = e.target.closest(Selectors.actions.deletecpd);
        if (deleteelement) {
            const cpdid = deleteelement.getAttribute('data-id');
            const cpname = deleteelement.getAttribute('data-name');
            ModalFactory.create({
                title: getString('delete', 'local_cpd'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('deleteconfirm', 'local_cpd', cpname)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('delete_cpd', 'local_cpd'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.confirm = true;
                    params.cpdid = cpdid;
                    var promise = Ajax.call([{
                        methodname: 'local_cpd_deletecpd',
                        args: params
                    }]);
                    promise[0].done(function() {
                        window.location.reload(true);
                    }).fail(function() {
                        // do something with the exception
                    });
                }.bind(this));
                modal.show();
            }.bind(this));
        }
        let formalelement = e.target.closest(Selectors.actions.createformalevid);
        if (formalelement) {
            e.preventDefault();
            const title = getString('formaltraining', 'local_cpd',formalelement.getAttribute('data-typename'));
            const form = new ModalForm({
                formClass: 'local_cpd\\form\\formaltrainingform',
                args: {id: formalelement.getAttribute('data-id'), cpdid: formalelement.getAttribute('data-cpdid'), evidid: formalelement.getAttribute('data-evidid'),
                type: formalelement.getAttribute('data-type')},
                modalConfig: {title},
                returnFocus: formalelement,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, (e) => {
               // e.preventDefault();
                const args = formalelement.getAttribute('data-typename');
                let homepage = new HomePageJs();
                homepage.confirmbox(getString('learninginfosuccess', 'local_cpd', args));
            }
            );
            form.show();
            //form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            //form.show();
        }
        
        let informalelement = e.target.closest(Selectors.actions.createinformalevid);
        if (informalelement) {
            e.preventDefault();
            const title = getString('informaltraining', 'local_cpd',informalelement.getAttribute('data-typename'));
            const form = new ModalForm({
                formClass: 'local_cpd\\form\\informaltrainingform',
                args: {id: informalelement.getAttribute('data-id'), cpdid: informalelement.getAttribute('data-cpdid'), evidid: informalelement.getAttribute('data-evidid'),
                type: informalelement.getAttribute('data-type')},
                modalConfig: {title},
                returnFocus: informalelement,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, (e) => {
               // e.preventDefault();
              const args = informalelement.getAttribute('data-typename');
              let homepage = new HomePageJs();
              homepage.confirmbox(getString('learninginfosuccess', 'local_cpd', args));
            }
            );
            form.show();
            //form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            //form.show();
        }

        let evidence = e.target.closest(Selectors.actions.createevidence);
        if (evidence) {
            e.preventDefault();
            const title = evidence.getAttribute('data-id') ?
                getString('editevidence', 'local_cpd', evidence.getAttribute('data-name')) :
                getString('addevidence', 'local_cpd');
            const form = new ModalForm({
                formClass: 'local_cpd\\form\\evidenceform',
                args: {id: evidence.getAttribute('data-id')},
                modalConfig: {title},
                returnFocus: evidence,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }
        let evidencestatus = e.target.closest(Selectors.actions.evidencestatus);
        if (evidencestatus) {
            const evalid = evidencestatus.getAttribute('data-id');
            const header = evidencestatus.getAttribute('data-title');
            const status = evidencestatus.getAttribute('data-status');
            const creditedhours = evidencestatus.getAttribute('data-creditedhours');
            if(status =="1") {
                var confirm_msg = getString('approveconfirm', 'local_cpd');
                var buttonstring = getString(header, 'local_cpd');
            } else {
                var confirm_msg = getString('rejectconfirm', 'local_cpd');
                var buttonstring = getString(header, 'local_cpd');
            }
            ModalFactory.create({
                title: getString(header, 'local_cpd'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: confirm_msg
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(buttonstring);
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.confirm = true;
                    params.evalid = evalid;
                    params.status = status;
                    params.creditedhours = creditedhours;
                    var promise = Ajax.call([{
                        methodname: 'local_cpd_evidencestatus',
                        args: params
                    }]);
                    promise[0].done(function() {
                        window.location.reload(true);
                    }).fail(function() {
                        // do something with the exception
                    });
                }.bind(this));
                modal.show();
            }.bind(this));
        }

        let evidenceuserview = e.target.closest(Selectors.actions.evidenceuserview);
        if (evidenceuserview) {
            const ceid = evidenceuserview.getAttribute('data-id');
            const userid = evidenceuserview.getAttribute('data-userid');
            const evdtype = evidenceuserview.getAttribute('data-type');
            var params = {};
            params.ceid = ceid;
            params.userid = userid;
            params.evdtype = evdtype;
            var promise = Ajax.call([{
                methodname: 'local_cpd_evidenceuserinfo',
                args: params
            }]);
            promise[0].done(function(resp) {
                ModalFactory.create({
                    title: getString('evidenceuserview', 'local_cpd'),
                    body: resp.options
                }).done(function(modal) {
                    this.modal = modal;
                    modal.show();
                }.bind(this));
            }).fail(function() {
                // do something with the exception
                 //console.log('exception');
            });
        }

        let viewevidence = e.target.closest(Selectors.actions.viewevidence);
        if (viewevidence) {
            const ceid = viewevidence.getAttribute('data-id');
            const evdtype = viewevidence.getAttribute('data-type');
            var params = {};
            params.ceid = ceid;
            params.evdtype = evdtype;
            var promise = Ajax.call([{
                methodname: 'local_cpd_viewevidence',
                args: params
            }]);
            promise[0].done(function(resp) {
                ModalFactory.create({
                    title: getString('view', 'local_cpd'),
                    body: resp.options
                }).done(function(modal) {
                    this.modal = modal;
                    modal.show();
                }.bind(this));
            }).fail(function() {
                // do something with the exception
                 //console.log('exception');
            });
        }

        let deleteevidence = e.target.closest(Selectors.actions.deleteevidence);
        if (deleteevidence) {
            const evidid = deleteevidence.getAttribute('data-id');
            const evdtitle = deleteevidence.getAttribute('data-name');
            const evidtype = deleteevidence.getAttribute('data-type');
            const totalcount = deleteevidence.getAttribute('data-totalcount');
            ModalFactory.create({
                title: getString('delete', 'local_cpd'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('deleteconfirm', 'local_cpd', evdtitle)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('delete_cpd', 'local_cpd'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.confirm = true;
                    params.evidid = evidid;
                    params.evidtype = evidtype;
                    var promise = Ajax.call([{
                        methodname: 'local_cpd_deleteevidence',
                        args: params
                    }]);
                    promise[0].done(function() {
                        if (totalcount > 1) {
                            window.location.reload(true);
                        } else {
                            window.location.href = M.cfg.wwwroot + '/local/cpd/index.php';
                        }
                    }).fail(function() {
                        // do something with the exception
                    });
                }.bind(this));
                modal.show();
            }.bind(this));
        }

        let addtrainigprogram = e.target.closest(Selectors.actions.edittrainigprogram);
        if (addtrainigprogram) {
            e.preventDefault();
            const title = getString('addtrainingprograms', 'local_cpd');
            const form = new ModalForm({
                formClass: 'local_cpd\\form\\trainingprogram_form',
                args: {id: addtrainigprogram.getAttribute('data-id'), cpdid: addtrainigprogram.getAttribute('data-cpdid')},
                modalConfig: {title},
                returnFocus: addtrainigprogram,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }

        let deletetrainigprogram = e.target.closest(Selectors.actions.deletetrainigprogram);
        if (deletetrainigprogram) {
            const programid = deletetrainigprogram.getAttribute('data-id');
            const programname = deletetrainigprogram.getAttribute('data-name');
            ModalFactory.create({
                title: getString('delete', 'local_cpd'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('deleteconfirm', 'local_cpd', programname)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('delete_cpd', 'local_cpd'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.confirm = true;
                    params.programid = programid;
                    var promise = Ajax.call([{
                        methodname: 'local_cpd_delete_training_program',
                        args: params
                    }]);
                    promise[0].done(function() {
                        window.location.reload(true);
                    }).fail(function() {
                        // do something with the exception
                    });
                }.bind(this));
                modal.show();
            }.bind(this));
        }
    });
};
