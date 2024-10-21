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
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import Ajax from 'core/ajax';
import ModalForm from 'core_form/modalform';
import {get_string as getString} from 'core/str';
import homepage from 'theme_academy/homepage';

const Selectors = {
    actions: {
        
        schedule: '[data-action="schedule"]',
        deleteshedule: '[data-action="deleteshedule"]',
    },
};
let HomePage = new homepage();
export const init = () => {

    document.addEventListener('click', function(e) {
        let schedule = e.target.closest(Selectors.actions.schedule);
        if (schedule) {
            e.preventDefault();
             const name = schedule.getAttribute('data-programname'); 
             const title = schedule.getAttribute('data-id') ?
                getString('editschedule', 'local_trainingprogram',name) :
                getString('schedulenew', 'local_trainingprogram',name);
            const form = new TPDynamicForm({
                formClass: 'local_trainingprogram\\form\\schedule',
                args: {id: schedule.getAttribute('data-id'),trainingid: schedule.getAttribute('data-traineeid'), entitycode:$("input[name='sesskey']").val()},
                modalConfig: {title},
                returnFocus: schedule,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.addEventListener(form.events.FORM_CANCELLED, (e) => {
                var params = {};
                params.sessionkey = $("input[name='sesskey']").val();
                params.type = 'tprogram';
                var promise = Ajax.call([{
                    methodname: 'remove_reservations',
                    args: params
                }]);
                promise[0].done(function(resp) {
                    $(".entityhalldetails").html('');
                    console.log('Successfully reservations removed');
                }).fail(function() {
                     console.log('exception');
                });
            });            
            form.show();
        }
        let deleteshedule = e.target.closest(Selectors.actions.deleteshedule);
        if (deleteshedule) {
            e.preventDefault();
            const sheduleid = deleteshedule.getAttribute('data-id');
            const userroleshortname = deleteshedule.getAttribute('data-userroleshortname');
            const code = deleteshedule.getAttribute('data-code');
            
            ModalFactory.create({
                title: getString('deleteconfirm', 'local_trainingprogram'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('sheduledeleteconfirm', 'local_trainingprogram')
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('deletetext', 'local_trainingprogram'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    if(userroleshortname == 'to') {
                        var params = {};
                        params.rootid = sheduleid;
                        params.etype = 'offering';
                        var promise = Ajax.call([{
                            methodname: 'local_trainingprogram_tofficialdeleteaction',
                            args: params
                        }]);
                        promise[0].done(function(resp) {
                            if(resp.response == 'success') {
                                modal.hide();
                                HomePage.confirmbox(getString('offeringdeletionwaitingfortsuperviser', 'local_trainingprogram',code));
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
                        params.sheduleid = sheduleid;
                        var promise = Ajax.call([{
                            methodname: 'local_trainingprogram_deleteshedule',
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
        $(document).on('change','select[name="type"]', function(e){
            var type = $(this).val();
            if (type == 0) {
                $('input[name="halllocation1"]').removeAttr('checked');
                $(".entityhall").removeAttr("hidden");
                $(".entityhall").css("display", "flex");
                $('select[name="halladdress"]').removeAttr("disabled");
                // $(".badge.badge-info").html('Select Hall');
                var trainingmethod = $("select[name='trainingmethod']").val();

                if (trainingmethod == 'offline') {
                    $('*[data-groupname="hallalertgroup"]').removeAttr("hidden");
                    $('*[data-groupname="hallalertgroup"]').css("display", "flex");
                    $('*[data-groupname="reservationgroup"]').removeAttr("hidden");
                    $('*[data-groupname="reservationgroup"]').css("display", "flex");
                }
            }
        });
        $(document).on('change','.time_selector select', function(e){

            var timeSelector = $(this).parents().find('form').serializeArray();

            var starttimehour = 0;
            var starttimeminute = 0;
            var endtimehour = 0;
            var endtimeminute = 0;

            $.each(timeSelector,function(index,value) {
                if(value.name == 'starttime[hours]'){
                    starttimehour = value.value;
                }else if(value.name == 'starttime[minutes]'){
                    starttimeminute = value.value;
                }else if(value.name == 'endtime[hours]'){
                    endtimehour = value.value;
                }else if(value.name == 'endtime[minutes]'){
                    endtimeminute = value.value;
                }
           });
           var starttime = (starttimehour * 3600) + (starttimeminute * 60);
           var endtime = (endtimehour * 3600) + (endtimeminute * 60);
           var duration = Math.abs(starttime-endtime);
           var dur_min = duration/60;
           var hours = Math.floor(dur_min / 60);
           var minutes = (dur_min % 60);
            if(hours > 0) {
              $('#offering_hours').parent().removeClass('hidden');
            } 
            if(minutes > 0) {
              $('#offering_minutes').parent().removeClass('hidden');
            } 
            if(endtime <= starttime) {
                $('#offering_hours').parent().addClass('hidden');
                $('#offering_minutes').parent().addClass('hidden');
            }
            if(hours  == 1) {
                var hourtext = getString('hour', 'local_trainingprogram');
            } else {
                var hourtext = getString('hours', 'local_trainingprogram');
            }
            var minutetext = getString('minutes', 'local_trainingprogram');
            hourtext.done(function(html,js){
                $('#offering_hours').html(hours +" "+ html);
            });
            minutetext.done(function(html,js){
                $('#offering_minutes').html(minutes +" "+ html);
            });
        });

        $("select[name='trainingmethod']").on('change', function(e){
                    
            var trainingmethod = $(this).val();
            if(trainingmethod == 'elearning')  {
                $('#ofeering_duration').addClass('hidden');
                $('#offering_hours').addClass('hidden');
                $('#offering_minutes').addClass('hidden');
            }

        });
        var halllocation = document.querySelectorAll('[name="halllocation"]');
        const addHallButton = document.querySelector('#create_hall');
        for (let index = 0; index < halllocation.length; index++) {
            if (halllocation[index].checked) {
                if (halllocation[index].value == 'outside') {
                    if(halllocation[index].value == 'outside'){
                        addHallButton.style.display="block";
                    }
                }else{
                    addHallButton.style.display="none";
                }
            }
        }

    });
};
