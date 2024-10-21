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
import ModalForm from 'core_form/modalform';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import Ajax from 'core/ajax';
import homepage from 'theme_academy/homepage';
import {get_string as getString} from 'core/str';

const Selectors = {
    actions: {
        createSectors: '[data-action="createsector"]',
        createSegment: '[data-action="createsegment"]',
        createjobfamily: '[data-action="createjobfamily"]',
        deletesector: '[data-action="deletesector"]',
        deletesegment: '[data-action="deletesegment"]',
        deletejobfamily: '[data-action="deletejobfamily"]',
    },
};
let HomePage = new homepage();
export const init = () => {
    document.addEventListener('click', function(e) {
        let element = e.target.closest(Selectors.actions.createSectors);
        e.stopImmediatePropagation();
        if (element) {
            e.preventDefault();
            const title = element.getAttribute('data-id') ?
                getString('edit_sector', 'local_sector', element.getAttribute('data-name')) :
                getString('add_sector', 'local_sector');
            const form = new ModalForm({
                formClass: 'local_sector\\form\\customsector',
                args: {id: element.getAttribute('data-id')},
                modalConfig: {title},
                returnFocus: element,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }
        element = e.target.closest(Selectors.actions.createSegment);
        if (element) {
            e.preventDefault();
            const title = element.getAttribute('data-id') ?
                getString('edit_segment', 'local_sector', element.getAttribute('data-name')) :
                getString('add_segmentname', 'local_sector', element.getAttribute('data-sectorname'));
            const form = new ModalForm({
                formClass: 'local_sector\\form\\customsegment',
                args: {id: element.getAttribute('data-id'),sectorid: element.getAttribute('data-sectorid')},
                modalConfig: {title},
                returnFocus: element,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }
        element = e.target.closest(Selectors.actions.createjobfamily);
        if (element) {
            e.preventDefault();
            var shared=0;
        
            if(element.getAttribute('data-shared')){
                shared = 1;
           
            } 
            const title = element.getAttribute('data-id') ?
            getString('edit_jobfamily', 'local_sector') :(element.getAttribute('data-shared') ? getString('add_jobfamilyname', 'local_sector'): 
            getString('add_jobname', 'local_sector',element.getAttribute('data-jobname')));
        
            const form = new TPDynamicForm({
                formClass: 'local_sector\\form\\customjobfamily',
                args: {id: element.getAttribute('data-id'),segmentid: element.getAttribute('data-segmentid'), shared: shared},
                modalConfig: {title},
                returnFocus: element,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }
        sectorelement = e.target.closest(Selectors.actions.deletesector);
        if (sectorelement) {
            const sectorid = sectorelement.getAttribute('data-id');
            const candeletesector = sectorelement.getAttribute('data-candeletesector');
            ModalFactory.create({
                title: getString('deletesector', 'local_sector'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('deleteallconfirm', 'local_sector')
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('delete', 'local_sector'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.elementid = sectorid;
                    params.elementtype = 'sector';
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
                            params.id = sectorid;
                            var promise = Ajax.call([{
                                methodname: 'local_sector_deletesector',
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
        segmentelement = e.target.closest(Selectors.actions.deletesegment);
        if (segmentelement) {
            const segmentid = segmentelement.getAttribute('data-id');
            const candeletesegment = segmentelement.getAttribute('data-candeletesegment');
            ModalFactory.create({
                title: getString('deletesegment', 'local_sector'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('deletesegmentconf', 'local_sector')
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('delete', 'local_sector'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.elementid = segmentid;
                    params.elementtype = 'segment';
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
                            params.id = segmentid;
                            var promise = Ajax.call([{
                                methodname: 'local_sector_deletesegment',
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
        jobfamilyelement = e.target.closest(Selectors.actions.deletejobfamily);
        if (jobfamilyelement) {
            const jobid = jobfamilyelement.getAttribute('data-id');
            const candeletejobfamily = jobfamilyelement.getAttribute('data-candeletejobfamily');
            ModalFactory.create({
                title: getString('deletejobfamily', 'local_sector'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('deletejobfamilyconf', 'local_sector')
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('delete', 'local_sector'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.elementid = jobid;
                    params.elementtype = 'jobfamily';
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
                            params.id = jobid;
                            var promise = Ajax.call([{
                                methodname: 'local_sector_deletejobfamily',
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
    });
};
