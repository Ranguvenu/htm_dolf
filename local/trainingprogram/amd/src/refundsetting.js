import TPDynamicForm from 'local_trainingprogram/dynamicform';
import ModalForm from 'core_form/modalform';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import Ajax from 'core/ajax';
import {get_string as getString} from 'core/str';
const Selectors = {
    actions: {
        createrefundsetting: '[data-action="createrefundsetting"]',
        deleterefundsetting: '[data-action="deleterefundsetting"]',
    },
};
export const init = () => {
    document.addEventListener('click', function(e) {
       // e.stopImmediatePropagation()
        let element = e.target.closest(Selectors.actions.createrefundsetting);
        if (element) {
            e.preventDefault();
            e.stopImmediatePropagation();
            const title = element.getAttribute('data-id') ?
                getString('editrefundsetting', 'local_trainingprogram') :
                getString('createrefundsetting', 'local_trainingprogram');
            const form = new TPDynamicForm({
                formClass: 'local_trainingprogram\\form\\refundsettingsform',
                args: {id: element.getAttribute('data-id')},
                modalConfig: {title},
                returnFocus: element,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }
        let deleterefundsetting = e.target.closest(Selectors.actions.deleterefundsetting);
        if (deleterefundsetting) {
             e.preventDefault();
             const id = deleterefundsetting.getAttribute('data-id');
            ModalFactory.create({
                title: getString('deleteconfirm', 'local_trainingprogram'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('deleterefundsettingmessage', 'local_trainingprogram')
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('deletetext', 'local_trainingprogram'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.id = id;
                    var promise = Ajax.call([{
                        methodname: 'local_trainingprogram_deleterefundsetting',
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
    });
};
