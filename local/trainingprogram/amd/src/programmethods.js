import TPDynamicForm from 'local_trainingprogram/dynamicform';
import ModalForm from 'core_form/modalform';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import Ajax from 'core/ajax';
import {get_string as getString} from 'core/str';
const Selectors = {
    actions: {
        createprogrammethod: '[data-action="createprogrammethod"]',
        deleteprogrammethod: '[data-action="deleteprogrammethod"]',
        
    },
};
export const init = () => {
    document.addEventListener('click', function(e) {
       // e.stopImmediatePropagation()
        let element = e.target.closest(Selectors.actions.createprogrammethod);
        if (element) {
            e.preventDefault();
            e.stopImmediatePropagation();
            const title = element.getAttribute('data-id') ?
                getString('editprogrammethod', 'local_trainingprogram') :
                getString('createprogrammethod', 'local_trainingprogram');
            const form = new TPDynamicForm({
                formClass: 'local_trainingprogram\\form\\programmethod_form',
                args: {id: element.getAttribute('data-id')},
                modalConfig: {title},
                returnFocus: element,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }

        let deleteprogram = e.target.closest(Selectors.actions.deleteprogrammethod);
        if (deleteprogram) {
             e.preventDefault();
             e.stopImmediatePropagation();
             const id = deleteprogram.getAttribute('data-id');
             const pname= deleteprogram.getAttribute('data-name');
             
             var displayparams = {};
             displayparams.id = id;
             displayparams.pname = pname;
            ModalFactory.create({
                title: getString('deleteconfirm', 'local_trainingprogram'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('deleteprogrammessage', 'local_trainingprogram',displayparams)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('deletetext', 'local_trainingprogram'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.id = id;
                    var promise = Ajax.call([{
                        methodname: 'local_trainingprogram_deleteprogrammethod',
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
