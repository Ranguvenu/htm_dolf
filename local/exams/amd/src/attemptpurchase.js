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

// import ModalForm from 'local_trainingprogram/dynamicform';
import Modalform from 'core_form/modalform';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import Ajax from 'core/ajax';
import homepage from 'theme_academy/homepage';
import Templates from 'core/templates';
import {get_string as getString} from 'core/str';
import $ from 'jquery';

const Selectors = {
    actions: {
        purcahsenextattempt: '[data-action="purcahsenextattempt"]',
    },
};

let HomePage = new homepage();
export const init = () => {
    document.addEventListener('click', function(e) {
        e.stopImmediatePropagation();
        let attemptpurchase = e.target.closest(Selectors.actions.purcahsenextattempt);
        if (attemptpurchase) {
            
            const examid               = attemptpurchase.getAttribute('data-examid'); 
            const userid               = attemptpurchase.getAttribute('data-tuserid'); 
            const profileid            = attemptpurchase.getAttribute('data-profileid'); 
            const lastattemptprofileid = attemptpurchase.getAttribute('data-lastattemptprofileid'); 
            const hallscheduleid       = attemptpurchase.getAttribute('data-scheduleid');
            const purchasinguser       = attemptpurchase.getAttribute('data-purchasinguser');

            ModalFactory.create({
                title: getString('confirm', 'local_exams'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('purchaseconfirm', 'local_exams')
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('yes', 'local_exams'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    let promise = Ajax.call([{
                        methodname: 'local_exams_attempt_request',
                        args: {
                            // examinfo: event.detail.returnparams
                            examid               : examid,
                            userid               : userid,
                            profileid            : profileid,
                            lastattemptprofileid : lastattemptprofileid,
                            hallscheduleid       : hallscheduleid,
                        }
                    }]);
                    promise[0].done((response) => {
                        if(!response.status) {
                            HomePage.confirmbox(response.response);
                        } else {
                            if (purchasinguser=="orgoff") {
                                window.location = M.cfg.wwwroot + '/local/exams/examdetails.php?id='+examid+'&tuserid='+userid;
                            } else {
                                window.location = M.cfg.wwwroot + '/local/exams/hallschedule.php?examid='+examid+'&profileid='+profileid+'&tuserid='+userid;
                            }

                        }
                    }).fail( (error) => {				
                        HomePage.confirmbox(error.error);
                    });
                }.bind(this));
                modal.show();
            }.bind(this));
        }
    });
};
