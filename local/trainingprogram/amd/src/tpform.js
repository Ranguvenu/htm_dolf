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
 * TODO describe module tpform
 *
 * @module     local_trainingprogram/tpform
 * @copyright  2023 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import TPDynamicForm from 'local_trainingprogram/dynamicform';
import {get_string as getString} from 'core/str';
import ModalFactory from 'core/modal_factory';
import Ajax from 'core/ajax';
import ModalForm from 'core_form/modalform';
import ModalEvents from 'core/modal_events';
import Templates from 'core/templates';
import homepage from 'theme_academy/homepage';

const Selectors = {
    actions: {
        editCategory: '[data-action="createprogram"]',
        deleteprogram: '[data-action="deleteprogram"]',
        publichprogram: '[data-action="publichprogram"]',
        trainerassign: '[data-action="trainerassign"]',
        unassignuser: '[data-action="unassignuser"]',
        createtrainingtopics: '[data-action="createtrainingtopics"]',
        deletetrainingtopics: '[data-action="deletetrainingtopics"]',
        createprogramgoal: '[data-action="createprogramgoal"]',
        unpublichprogram: '[data-action="unpublichprogram"]',
       
    },
};

require(['jquery'], function($) {
    $(document).ready(function(){
        $('body').on('change','input[name="tporoff"]',function(){
            var selected1 =$("input[name='tporoff']:checked").val();
            if(selected1 == 2){
            $('input[type=radio][name="startdatesort"][value='+ 3 +']').prop('disabled', true);
            $('input[type=radio][name="startdatesort"][value='+ 4 +']').prop('disabled', true);
            }
            else{
            $('input[type=radio][name="startdatesort"][value='+ 3 +']').prop('disabled', false);
            $('input[type=radio][name="startdatesort"][value='+ 4 +']').prop('disabled', false);        
            }
          });
    });
  });
let HomePage = new homepage();
export const init = () => {

    document.addEventListener('click', function(e) {
        let element = e.target.closest(Selectors.actions.editCategory);
        if (element) {
            e.preventDefault();
            e.stopImmediatePropagation();
            const title = element.getAttribute('data-id') ?
                getString('updatetp', 'local_trainingprogram') :
                getString('createtp', 'local_trainingprogram');
            const form = new TPDynamicForm({
                formClass: 'local_trainingprogram\\form\\trainingprogram',
                args: {id: element.getAttribute('data-id'),courseid: element.getAttribute('data-courseid')},
                modalConfig: {title},
                returnFocus: element,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());

            form.show();    
        }
        let deleteprogram = e.target.closest(Selectors.actions.deleteprogram);
        if (deleteprogram) {
            e.preventDefault();
            const programid = deleteprogram.getAttribute('data-id');
            const programname = deleteprogram.getAttribute('data-name');
            const programcode = deleteprogram.getAttribute('data-code');
            const userroleshortname = deleteprogram.getAttribute('data-userroleshortname');
            var displayparams = {};
            displayparams.programname = programname;
            displayparams.programcode = programcode;
            ModalFactory.create({
                title: getString('deleteconfirm', 'local_trainingprogram'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('programdeleteconfirm', 'local_trainingprogram',programname)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('deletetext', 'local_trainingprogram'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    if(userroleshortname == 'to') {
                        var params = {};
                        params.rootid = programid;
                        params.etype = 'program';
                        var promise = Ajax.call([{
                            methodname: 'local_trainingprogram_tofficialdeleteaction',
                            args: params
                        }]);
                        promise[0].done(function(resp) {
                            if(resp.response == 'success') {
                                modal.hide();
                                HomePage.confirmbox(getString('programdeletionwaitingfortsuperviser', 'local_trainingprogram',displayparams));
                                setTimeout(function() {
                                    location.reload();
                                },4000);
                                  
                            } else {
                                modal.hide();
                                HomePage.confirmbox(resp.response);
                            }
                        }).fail(function() {
                            console.log('exception');
                        });
                    } else {
                        var params = {};
                        params.programid = programid;
                        var promise = Ajax.call([{
                            methodname: 'local_trainingprogram_deleteprogram',
                            args: params
                        }]);
                        promise[0].done(function(resp) {
                            window.location.reload(true);
                        }).fail(function() {
                            console.log('exception');
                        });
                    }
                }.bind(this));
                modal.show();
            }.bind(this));
        }
        let publichprogram = e.target.closest(Selectors.actions.publichprogram);
        if (publichprogram) {
             e.preventDefault();
             const programid = publichprogram.getAttribute('data-id');
             const programname = publichprogram.getAttribute('data-name');
            ModalFactory.create({
                title: getString('publishconfirm', 'local_trainingprogram'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('programpublishconfirm', 'local_trainingprogram',programname)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('publishtext', 'local_trainingprogram'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.programid = programid;
                    var promise = Ajax.call([{
                        methodname: 'local_trainingprogram_publishprogram',
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
        let unpublichprogram = e.target.closest(Selectors.actions.unpublichprogram);
        if (unpublichprogram) {
             e.preventDefault();
             const programid = unpublichprogram.getAttribute('data-id');
             const programname = unpublichprogram.getAttribute('data-name');
            ModalFactory.create({
                title: getString('unpublishconfirm', 'local_trainingprogram'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('programunpublishconfirm', 'local_trainingprogram',programname)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('unpublishtext', 'local_trainingprogram'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.programid = programid;
                    var promise = Ajax.call([{
                        methodname: 'local_trainingprogram_unpublishprogram',
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
        let trainerassign = e.target.closest(Selectors.actions.trainerassign);
        if (trainerassign) {
            e.preventDefault();
            const programname = trainerassign.getAttribute('data-programname');
            const title = getString('enroltoprogram', 'local_trainingprogram',programname);
            const form = new ModalForm({
                formClass: 'local_trainingprogram\\form\\trainerassignform',
                args: {
                    programid: trainerassign.getAttribute('data-programid'),
                    offeringid: trainerassign.getAttribute('data-offeringid'), 
                    roleid: trainerassign.getAttribute('data-role')
                },
                modalConfig: {title},
                returnFocus: trainerassign,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }

        let unassignuser = e.target.closest(Selectors.actions.unassignuser);
        if (unassignuser) {
             e.preventDefault();
             const userid = unassignuser.getAttribute('data-id');
             const programid = unassignuser.getAttribute('data-programid');
             const roleid = unassignuser.getAttribute('data-roleid');
             const offeringid = unassignuser.getAttribute('data-offeringid');
             const rolename = unassignuser.getAttribute('data-rolename');
             const username = unassignuser.getAttribute('data-username');
             const coursename = unassignuser.getAttribute('data-coursename');
               var displayparams = {};
                displayparams.rolename = rolename;
                displayparams.username = username;
                displayparams.coursename = coursename;
            ModalFactory.create({
                title: getString('unassignconfirm', 'local_trainingprogram'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('programunassignconfirm', 'local_trainingprogram',displayparams)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('unassigntext', 'local_trainingprogram'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.userid = userid;
                    params.programid = programid;
                    params.offeringid = offeringid;
                    params.roleid = roleid;
                    var promise = Ajax.call([{
                        methodname: 'local_trainingprogram_unassignuser',
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

        let createtrainingtopicselement = e.target.closest(Selectors.actions.createtrainingtopics);
        if (createtrainingtopicselement) {
            e.preventDefault();
            const title = createtrainingtopicselement.getAttribute('data-id') ?
                getString('updatetrainingtopic', 'local_trainingprogram') :
                getString('createtrainingtopic', 'local_trainingprogram');
            const form = new TPDynamicForm({
                formClass: 'local_trainingprogram\\form\\trainingtopicsform',
                args: {id: createtrainingtopicselement.getAttribute('data-id')},
                modalConfig: {title},
                returnFocus: createtrainingtopicselement,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());

            form.show();    
        }


        let deletetrainingtopics = e.target.closest(Selectors.actions.deletetrainingtopics);
        if (deletetrainingtopics) {
             e.preventDefault();
             const id = deletetrainingtopics.getAttribute('data-id');
             const name = deletetrainingtopics.getAttribute('data-name');
            ModalFactory.create({
                title: getString('deleteconfirm', 'local_trainingprogram'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('trainingtopicdeleteconfirm', 'local_trainingprogram',name)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('deletetext', 'local_trainingprogram'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.elementid = id;
                    params.elementtype = 'trainingtopic';
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
                            params.id = id;
                            var promise = Ajax.call([{
                                methodname: 'local_trainingprogram_deletetrainingtopic',
                                args: params
                            }]);
                            promise[0].done(function(resp) {
                                window.location.reload(true);
                            }).fail(function() {
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

        createprogramgoal = e.target.closest(Selectors.actions. createprogramgoal);
        if (createprogramgoal) {
            
            e.preventDefault();
            const title = createprogramgoal.getAttribute('data-id') ?
                getString('updategoals', 'local_trainingprogram', createprogramgoal.getAttribute('data-name')) :
                getString('addprogramgoal', 'local_trainingprogram');
            const form = new ModalForm({
                formClass: 'local_trainingprogram\\form\\programgoalsform',
                args: {id: createprogramgoal.getAttribute('data-id'),programid: createprogramgoal.getAttribute('data-programid')},
                modalConfig: {title},
                returnFocus: createprogramgoal,
            });
         
        
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }

       
    });
};
