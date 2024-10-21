import TPDynamicForm from 'local_trainingprogram/dynamicform';
import ModalForm from 'core_form/modalform';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import Templates from 'core/templates';
import Ajax from 'core/ajax';
import homepage from 'theme_academy/homepage';
import {get_string as getString} from 'core/str';
import $ from 'jquery';

const Selectors = {
    actions: {
    	offeringlogs: '[data-action="offeringlogs"]'
    },
};

let HomePage = new homepage();
export const init = () => {
    document.addEventListener('click', function(e) {
        e.stopImmediatePropagation();

        let offeringlogs = e.target.closest(Selectors.actions.offeringlogs);
        if (offeringlogs) {
            e.stopImmediatePropagation();
            const programid = offeringlogs.getAttribute('data-id');
            var options = {};
            options.programid = programid;
            var trigger = $(Selectors.actions.offeringlogs);
            ModalFactory.create({
                title: getString('offeringlogs', 'local_trainingprogram'),
                body: Templates.render('local_trainingprogram/recent_offerings_display', options)
            }, trigger)
            .done(function(modal) {
               this.modal = modal;
               this.modal.setLarge();
                this.modal.getRoot().addClass('currentofferingsmodel');
                modal.show();
                modal.getRoot().on(ModalEvents.hidden, function() {
                modal.destroy();
                }.bind(this));
            });
        }
    });
};
