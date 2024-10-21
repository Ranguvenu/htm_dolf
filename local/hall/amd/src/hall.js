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

import ModalForm from 'local_trainingprogram/dynamicform';
import Modalform from 'core_form/modalform';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import homepage from 'theme_academy/homepage';
import Fragment  from 'core/fragment';
import Ajax from 'core/ajax';
import Templates from 'core/templates';
import {get_string as getString} from 'core/str';
import $ from 'jquery';

const Selectors = {
    actions: {
        editCategory: '[data-action="edithall"]',
        deletehall: '[data-action="deletehall"]',
        viewhall: '[data-action="viewhall"]',
        hallreserve: '[data-action="hallreserve"]',
        hallbtn: '[data-action="hallbtn"]',
        createhallchedule: '[data-action="createhallchedule"]',
        deleteschedulehall: '[data-action="deleteschedulehall"]',
        hallcodes: '[data-action="hallcodes"]',

    },
};

let HomePage = new homepage();
export const init = () => {
    document.addEventListener('click', function(e) {
        let element = e.target.closest(Selectors.actions.editCategory);
        if (element) {
            e.preventDefault();
            const title = element.getAttribute('data-id') ?
                getString('edithall', 'local_hall', element.getAttribute('data-name')) :
                getString('createhall', 'local_hall');
            const form = new ModalForm({
                formClass: 'local_hall\\form\\hallform',
                args: {
                    id: element.getAttribute('data-id'),
                    halllocation: element.getAttribute('data-halllocation'),
                    type: element.getAttribute('data-type')
                },
                modalConfig: {title},
                returnFocus: element,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, (e) => {
                var is_offeringform = document.getElementsByName('_qf__local_trainingprogram_form_schedule');
                if (is_offeringform) {
                    this.modal.destroy();
                }else{
                    window.location.reload(true);
                }
            });
            form.show();
        }


        let scheduleelement = e.target.closest(Selectors.actions.createhallchedule);
        if (scheduleelement) {
            e.preventDefault();
            const title = scheduleelement.getAttribute('data-id') ?
                getString('edithallschedule', 'local_hall', scheduleelement.getAttribute('data-name')) :
                getString('createhallschedule', 'local_hall');
            const form = new ModalForm({
                formClass: 'local_hall\\form\\schedulehallform',
                args: {id: scheduleelement.getAttribute('data-id'),hallid: scheduleelement.getAttribute('data-hallid')},
                modalConfig: {title},
                returnFocus: scheduleelement,
            });
            // form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.addEventListener(form.events.FORM_SUBMITTED, (event) => {
                if (event.detail) {
                    var type = event.detail.type;
                    var typeid = event.detail.typeid;
                    var errors = event.detail.errors;
                    window.location = M.cfg.wwwroot + '/local/hall/schedulenotices.php?type='+type+'&typeid='+typeid+'&errors='+errors;
                } else {
                    window.location.reload();
                }
            });
            form.show();
        }

        

        let hallelement = e.target.closest(Selectors.actions.deletehall);
        if (hallelement) {
            const hallid = hallelement.getAttribute('data-id');
            ModalFactory.create({
                title: getString('deletehall', 'local_hall'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('deleteallconfirm', 'local_hall')
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('delete', 'local_hall'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.hallid = hallid;
                    var promise = Ajax.call([{
                        methodname: 'local_deletehall',
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

        let deleteschedulehall = e.target.closest(Selectors.actions.deleteschedulehall);
        if (deleteschedulehall) {
            const id = deleteschedulehall.getAttribute('data-id');
            ModalFactory.create({
                title: getString('deleteschedule', 'local_hall'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('deletescheduleconfirm', 'local_hall')
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('deleteschedule', 'local_hall'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.id = id;
                    var promise = Ajax.call([{
                        methodname: 'local_deleteschedulehall',
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
        let viewhall = e.target.closest(Selectors.actions.viewhall);
        if (viewhall) {
            const hallid = viewhall.getAttribute('data-id');
            var params = {};
            params.hallid = hallid;
            var promise = Ajax.call([{
                methodname: 'local_hall_info',
                args: params
            }]);
            promise[0].done(function(resp) {
                ModalFactory.create({
                    title: getString('viewhall', 'local_hall'),
                    body: resp.options
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
        let hallreserve = e.target.closest(Selectors.actions.hallreserve);
        if (hallreserve) {
            e.preventDefault();
            var params = {};
            params.hallid = hallreserve.getAttribute('data-hallid');
            slid = hallreserve.getAttribute('data-slid');
            params.typeid = hallreserve.getAttribute('data-typeid');
            params.examdate = hallreserve.getAttribute('data-examdate');
            params.start = hallreserve.getAttribute('data-start');
            // params.start = $(".starttime").val();
            params.end = hallreserve.getAttribute('data-end');
            params.type = hallreserve.getAttribute('data-type');
            params.seats = $(".selectedseats"+slid).val();

            available = hallreserve.getAttribute('data-available');
            entitiesseats = hallreserve.getAttribute('data-entitiesseats');
            params.entityid = hallreserve.getAttribute('data-entityid');
            params.reservationid = hallreserve.getAttribute('data-reservationid');  
            params.submit_type = hallreserve.getAttribute('data-submit_type');          
            if(params.entityid == '') {
                params.entityid = params.typeid;
            }
            params.referencecode = $("input[name='sesskey']").val();

            var data = {};
            data.available = available;
            data.entitiesseats = entitiesseats;

            if(isNaN(params.seats)){
                HomePage.confirmbox(getString('seatsshouldbenumaric', 'local_hall'));
            } else if(+params.seats < 0) {
                HomePage.confirmbox(getString('seatscannotbenegative', 'local_hall'));
            } else if(+params.seats == 0) {
                HomePage.confirmbox(getString('seatscannotbezero', 'local_hall'));
            } else if(+available < +params.seats) {
                HomePage.confirmbox(getString('hallavailableseats', 'local_hall', data));
            } else if(params.seats == "") {
                HomePage.confirmbox(getString('selectseats', 'local_hall'));
            } else if(+entitiesseats < +params.seats && params.type != 'event') {
                HomePage.confirmbox(getString('entityseats', 'local_hall', data));
            } else {
                ModalFactory.create({
                    title: getString('confirm', 'local_hall'),
                    type: ModalFactory.types.SAVE_CANCEL,
                    body: getString('selectedseats', 'local_hall', params.seats)
                }).done(function(modal) {
                    this.modal = modal;
                    modal.setSaveButtonText(getString('reserve', 'local_hall'));
                    modal.getRoot().on(ModalEvents.save, function(e) {
                        modal.hide();
                        var options = {};
                        options.hallid = params.hallid;
                        options.typeid = params.typeid;
                        options.type = params.type;
                        options.examdate = params.examdate;
                        options.starttime = params.start;
                        options.duration = $(".duration").val();
                        if(options.duration == '') {
                            options.duration = 0;
                        }
                        var city = $("#city option:selected").val();
                        if(city == "") {
                            city = 0;
                        }
                        var buildingname = $("#buildingname option:selected").val();
                        if(buildingname == '') {
                            buildingname = 0;
                        }
                        entitiesseats = $(".entitiesseats").val();
                        if(entitiesseats == '') {
                            entitiesseats = 0;
                        }
                        options.city = city;
                        options.buildingname = buildingname;
                        options.entitiesseats = entitiesseats;
                        options.entityid = params.entityid;
                        options.referencecode = params.referencecode;
                        options.reservationid = params.reservationid;
                        options.submit_type = params.submit_type;
                        options.temp = 'hallreservation';
                        options.methodName = 'local_hall_data';
                        e.preventDefault();
                        var promise = Ajax.call([{
                            methodname: 'hall_slotbooking',
                            args: params
                        }]);
                        promise[0].done(function(resp) {

                            HomePage.confirmbox(getString('reservationsuccess', 'local_hall'));
                            Templates.render('local_hall/testing',options).done(function(html, js) {
                                $(".selecthall").html('');
                                // $(".selecthall").html(html);
                            });

                            modal.hide();
                            $("#hallinformation").css('display', 'block');
                            $(".entityhalldetails").html('<tr><td>'+ resp.name +'</td><td>'+ resp.examdate +'</td><td>'+resp.seats+'</td></tr>');
                        }).fail(function() {
                             console.log('hallexception');
                        });
                    }.bind(this));
                    modal.show();
                }.bind(this));
            }
        }
        let hallbtn = e.target.closest(Selectors.actions.hallbtn);
        if (hallbtn) {
            var typeid = $(".typeid").val();
            var type = $(".type").val();
            var duration = $(".duration").val();
            var entityid = $(".entityid").val();
            var starttime = $(".starttime").val();
            var reservationid = $(".reservationid").val();
            var submit_type = $("input[name='submit_type']").val();
            var contextid = $(".contextid").val();
            if(duration == '') {
                duration = 0;
            }
            var city = $("#city option:selected").val();
            if(city == "") {
                city = 0;
            }
            var buildingname = $("#buildingname option:selected").val();
            if(buildingname == '') {
                buildingname = 0;
            }
            var entitiesseats = $(".entitiesseats").val();
            if(entitiesseats == '') {
                entitiesseats = 0;
            }
            var hallid = $("#id_halls option:selected").val();
            var moduledates = $("select[name='moduledates']").val();
            var options = {};
            options.hallid = hallid;
            options.examdate = moduledates;
            options.typeid = typeid;
            options.type = type;
            options.duration = duration;
            options.city = city;
            options.buildingname = buildingname;
            options.entitiesseats = entitiesseats;
            options.entityid = entityid;
            options.starttime = starttime;
            options.reservationid = reservationid;
            options.submit_type = submit_type;
            options.referencecode = $("input[name='sesskey']").val();
            options.temp = 'hallreservation';
            options.methodName = 'local_hall_data';

            var hallfields = Fragment.loadFragment('local_hall', 'hallvalidations', contextid, options);

            hallfields.done(function(html, js) {
                response = JSON.parse(html);
                hallseatscount = response.hallseats;
                hallstarttime = response.hallstarttime;
                hallendtime = response.hallendtime;
                entityduration = response.entityduration;
                entityendtime = (+options.starttime + +entityduration);                

                if(hallseatscount.halls && hallseatscount.moduledates) {
                    $(".selecthall").html('');
                    HomePage.confirmbox(getString('reservationrequirements', 'local_hall'));
                } else if(hallseatscount.halls) {
                    $(".selecthall").html('');
                    HomePage.confirmbox(getString('selecthallforreservation', 'local_hall'));
                } else if(hallseatscount.moduledates) {
                    $(".selecthall").html('');
                    HomePage.confirmbox(getString('selectdateforreservation', 'local_hall'));
                } else if(+options.starttime < +hallstarttime || +entityendtime > +hallendtime) {
                    var hallstart = new Date(hallstarttime * 1000).toISOString().substring(11, 16);
                    var hallend = new Date(hallendtime * 1000).toISOString().substring(11, 16);
                    var data = {};
                    data.hallstart = hallstart;
                    data.hallend = hallend;
                    HomePage.confirmbox(getString('hallentitytimenotmatch', 'local_hall', data));
                } else {
                    $(".halldatemandatory").html("");
                    Templates.render('local_hall/testing',options).done(function(html, js) {
                        $(".selecthall").html(html);        
                    })

                    const myTimeout = setTimeout(myGreeting, 2000);

                    function myGreeting() {
                        $(".selectedseats0").attr("size", 4);
                        $(".selectedseats0").prop("readonly", false);
                    }
                }
            });
        }

        let hallcodes = e.target.closest(Selectors.actions.hallcodes);
        if (hallcodes) {
            e.preventDefault();
            const title = getString('hallcodes', 'local_hall');
            const form = new ModalForm({
                formClass: 'local_hall\\form\\hallcodes',
                args: {id: hallcodes.getAttribute('data-id')},
                modalConfig: {title},
                returnFocus: hallcodes,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }
    });
};
