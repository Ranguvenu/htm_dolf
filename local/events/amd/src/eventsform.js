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
import {get_string as getString} from 'core/str';

const Selectors = {
    actions: {
        deleteevent: '[data-action="deleteevent"]',
        createagenda: '[data-action="createagenda"]',
        deletagenda: '[data-action="deletagenda"]',
        createattendee: '[data-action="createattendee"]',
        deletattendee:  '[data-action="deletattendee"]',
        viewagenda:  '[data-action="viewagenda"]',
        viewattendee: '[data-action="viewattendee"]',
        createspeaker: '[data-action="createspeaker"]',
        viewspeaker: '[data-action="viewspeaker"]',
        deletspeaker: '[data-action="deletspeaker"]',
        createsponsor: '[data-action="createsponsor"]',
        viewsponsor: '[data-action="viewsponsor"]',
        deletesponsor: '[data-action="deletesponsor"]',
        createpartner: '[data-action="createpartner"]',
        viewpartner: '[data-action="viewpartner"]',
        deletepartner: '[data-action="deletepartner"]',
        editfinance: '[data-action="editfinance"]',
    },
};
export const init = () => {
    document.addEventListener('click', function(e) {
            e.stopImmediatePropagation();
            let element = e.target.closest(Selectors.actions.createagenda);
            if (element) {
                e.preventDefault();
                const title = element.getAttribute('data-id') ?
                getString('editagenda', 'local_events', element.getAttribute('data-name')) :
                getString('addnewtopic', 'local_events');
            const form = new ModalForm({
                formClass: 'local_events\\form\\agendaform',
                args: {id: element.getAttribute('data-id'), eventid: element.getAttribute('data-eventid')},
                modalConfig: {title},
                returnFocus: element,
            });
                form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
                form.show();
            }
        let deleteelement = e.target.closest(Selectors.actions.deleteevent);
        if (deleteelement) {
            const eventid = deleteelement.getAttribute('data-id');
            const eventname = deleteelement.getAttribute('data-name');
            ModalFactory.create({
                title: getString('delete', 'local_events'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('deleteconfirm', 'local_events', eventname)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('delete', 'local_events'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.confirm = true;
                    params.eventid = eventid;
                    var promise = Ajax.call([{
                        methodname: 'local_events_deleteevent',
                        args: params
                    }]);
                    promise[0].done(function() {
                       window.location.href = M.cfg.wwwroot + '/local/events/index.php';
                    }).fail(function() {
                        // do something with the exception
                    });
                }.bind(this));
                modal.show();
            }.bind(this));
        }

        let deletagenda = e.target.closest(Selectors.actions.deletagenda);
        if (deletagenda) {
            const agendaid = deletagenda.getAttribute('data-id');
            const eventid = deletagenda.getAttribute('data-eventid');
            const agendaname = deletagenda.getAttribute('data-name');
            ModalFactory.create({
                title: getString('delete', 'local_events'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('deleteconfirm', 'local_events', agendaname)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('delete', 'local_events'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.confirm = true;
                    params.agendaid = agendaid;
                    params.eventid = eventid;
                    var promise = Ajax.call([{
                        methodname: 'local_events_delete_agenda',
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

        let attendees = e.target.closest(Selectors.actions.createattendee);
            if (attendees) {
                e.preventDefault();
                const title = attendees.getAttribute('data-id') ?
                getString('editattendee', 'local_events', attendees.getAttribute('data-name')) :
                getString('addattendee', 'local_events');
            const form = new ModalForm({
                formClass: 'local_events\\form\\attendeeform',
                args: {id: attendees.getAttribute('data-id'), eventid: attendees.getAttribute('data-eventid')},
                modalConfig: {title},
                returnFocus: attendees,
            });
            // alert("test");
             // form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
                form.addEventListener(form.events.FORM_SUBMITTED, (event) => {
                    if (event.detail.returnparams) {
                        // console.log(event.detail.returnparams);
                        window.location.href = event.detail.returnparams;                        
                    }else{
                        window.location.reload();
                    }
                });
                form.show();
            }

            let deletattendee = e.target.closest(Selectors.actions.deletattendee);
            if (deletattendee) {
                const attid = deletattendee.getAttribute('data-id');
                const eventid = deletattendee.getAttribute('data-eventid');
                const attname = deletattendee.getAttribute('data-name');
                ModalFactory.create({
                    title: getString('delete', 'local_events'),
                    type: ModalFactory.types.SAVE_CANCEL,
                    body: getString('deleteconfirm', 'local_events', attname)
                }).done(function(modal) {
                    this.modal = modal;
                    modal.setSaveButtonText(getString('delete', 'local_events'));
                    modal.getRoot().on(ModalEvents.save, function(e) {
                        e.preventDefault();
                        var params = {};
                        params.confirm = true;
                        params.attid = attid;
                        params.eventid = eventid;
                        var promise = Ajax.call([{
                            methodname: 'local_events_delete_attendees',
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

            let viewattendee = e.target.closest(Selectors.actions.viewattendee);
            if (viewattendee) {
                const attid = viewattendee.getAttribute('data-id');
                const eventid = viewattendee.getAttribute('data-eventid');
                const attname = viewattendee.getAttribute('data-name');
                var params = {};
                params.attid = attid;
                params.eventid = eventid;
                var promise = Ajax.call([{
                    methodname: 'local_events_view_attendee',
                    args: params
                }]);
                promise[0].done(function(resp) {
                    ModalFactory.create({
                        title: getString('view', 'local_events', attname),
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
            let viewagenda = e.target.closest(Selectors.actions.viewagenda);
            if (viewagenda) {
                const agdid = viewagenda.getAttribute('data-id');
                const eventid = viewagenda.getAttribute('data-eventid');
                const agdname = viewagenda.getAttribute('data-name');
                var params = {};
                params.agdid = agdid;
                params.eventid = eventid;
                var promise = Ajax.call([{
                    methodname: 'local_events_view_agenda',
                    args: params
                }]);
                promise[0].done(function(resp) {
                    ModalFactory.create({
                        title: getString('view', 'local_events', agdname),
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
            let speaker = e.target.closest(Selectors.actions.createspeaker);
            if (speaker) {
                e.preventDefault();
                const title = speaker.getAttribute('data-id') ?
                getString('editspeaker', 'local_events', speaker.getAttribute('data-name')) :
                getString('addspeaker', 'local_events');
                const form = new ModalForm({
                formClass: 'local_events\\form\\speakerform',
                args: {id: speaker.getAttribute('data-id'), eventid: speaker.getAttribute('data-eventid')},
                modalConfig: {title},
                returnFocus: speaker,
            });
                form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
                form.show();
            }

         let deletspeaker = e.target.closest(Selectors.actions.deletspeaker);
        if (deletspeaker) {
            const speakerid = deletspeaker.getAttribute('data-id');
            const eventid = deletspeaker.getAttribute('data-eventid');
            const speakername = deletspeaker.getAttribute('data-name');
            ModalFactory.create({
                title: getString('delete', 'local_events'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('deleteconfirm', 'local_events', speakername)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('delete', 'local_events'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.confirm = true;
                    params.speakerid = speakerid;
                    params.eventid = eventid;
                    var promise = Ajax.call([{
                        methodname: 'local_events_delete_speaker',
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

        let viewspeaker = e.target.closest(Selectors.actions.viewspeaker);
            if (viewspeaker) {
                const speakerid = viewspeaker.getAttribute('data-id');
                const eventid = viewspeaker.getAttribute('data-eventid');
                var params = {};
                params.speakerid = speakerid;
                params.eventid = eventid;
                var promise = Ajax.call([{
                    methodname: 'local_events_view_speaker',
                    args: params
                }]);
                promise[0].done(function(resp) {
                    ModalFactory.create({
                        title: getString('viewspeaker', 'local_events'),
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

            let createsponsor = e.target.closest(Selectors.actions.createsponsor);
            if (createsponsor) {
                e.preventDefault();
                const title = createsponsor.getAttribute('data-id') ?
                getString('editsponsor', 'local_events', createsponsor.getAttribute('data-name')) :
                getString('addnewsponsor', 'local_events');
                const form = new ModalForm({
                formClass: 'local_events\\form\\sponsorform',
                args: {id: createsponsor.getAttribute('data-id'), eventid: createsponsor.getAttribute('data-eventid')},
                modalConfig: {title},
                returnFocus: speaker,
            });
                form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
                form.show();
            }

            let deletesponsor = e.target.closest(Selectors.actions.deletesponsor);
            if (deletesponsor) {
                const sponsorid = deletesponsor.getAttribute('data-id');
                const eventid = deletesponsor.getAttribute('data-eventid');
                const sponsorname = deletesponsor.getAttribute('data-name');
                ModalFactory.create({
                    title: getString('delete', 'local_events'),
                    type: ModalFactory.types.SAVE_CANCEL,
                    body: getString('deleteconfirm', 'local_events', sponsorname)
                }).done(function(modal) {
                    this.modal = modal;
                    modal.setSaveButtonText(getString('delete', 'local_events'));
                    modal.getRoot().on(ModalEvents.save, function(e) {
                        e.preventDefault();
                        var params = {};
                        params.confirm = true;
                        params.sponsorid = sponsorid;
                        params.eventid = eventid;
                        var promise = Ajax.call([{
                            methodname: 'local_events_delete_sponsor',
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

            let viewsponsor = e.target.closest(Selectors.actions.viewsponsor);
            if (viewsponsor) {
                const sponsorid = viewsponsor.getAttribute('data-id');
                const eventid = viewsponsor.getAttribute('data-eventid');
                var params = {};
                params.sponsorid = sponsorid;
                params.eventid = eventid;
                var promise = Ajax.call([{
                    methodname: 'local_events_view_sponsor',
                    args: params
                }]);
                promise[0].done(function(resp) {
                    ModalFactory.create({
                        title: getString('viewsponsor', 'local_events'),
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

            let partner = e.target.closest(Selectors.actions.createpartner);
            if (partner) {
                e.preventDefault();
                const title = partner.getAttribute('data-id') ?
                getString('editpartner', 'local_events', partner.getAttribute('data-name')) :
                getString('newpartner', 'local_events');
                const form = new ModalForm({
                formClass: 'local_events\\form\\partnerform',
                args: {id: partner.getAttribute('data-id'), eventid: partner.getAttribute('data-eventid')},
                modalConfig: {title},
                returnFocus: partner,
            });
                form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
                form.show();
            }

            let viewpartner = e.target.closest(Selectors.actions.viewpartner);
            if (viewpartner) {
                const partnerid = viewpartner.getAttribute('data-id');
                const eventid = viewpartner.getAttribute('data-eventid');
                const partnername = viewpartner.getAttribute('data-name');
                var params = {};
                params.partnerid = partnerid;
                params.eventid = eventid;
                var promise = Ajax.call([{
                    methodname: 'local_events_view_partner',
                    args: params
                }]);
                promise[0].done(function(resp) {
                    ModalFactory.create({
                        title: getString('view', 'local_events', partnername),
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

            let deletepartner = e.target.closest(Selectors.actions.deletepartner);
            if (deletepartner) {
                const partnerid = deletepartner.getAttribute('data-id');
                const eventid = deletepartner.getAttribute('data-eventid');
                const partnername = deletepartner.getAttribute('data-name');
                ModalFactory.create({
                    title: getString('delete', 'local_events'),
                    type: ModalFactory.types.SAVE_CANCEL,
                    body: getString('deleteconfirm', 'local_events', partnername)
                }).done(function(modal) {
                    this.modal = modal;
                    modal.setSaveButtonText(getString('delete', 'local_events'));
                    modal.getRoot().on(ModalEvents.save, function(e) {
                        e.preventDefault();
                        var params = {};
                        params.confirm = true;
                        params.partnerid = partnerid;
                        params.eventid = eventid;
                        var promise = Ajax.call([{
                            methodname: 'local_events_delete_partner',
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
            let editfinance = e.target.closest(Selectors.actions.editfinance);
            if (editfinance) {
                e.preventDefault();
                const title = editfinance.getAttribute('data-id') ?
                getString('editattendee', 'local_events', editfinance.getAttribute('data-name')) :
                getString('additem', 'local_events');
            const form = new ModalForm({
                formClass: 'local_events\\form\\financeform',
                args: {id: editfinance.getAttribute('data-id'), eventid: editfinance.getAttribute('data-eventid')},
                modalConfig: {title},
                returnFocus: editfinance,
            });
                form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
                form.show();
            }
    });
};
