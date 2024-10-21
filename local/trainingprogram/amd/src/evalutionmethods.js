import TPDynamicForm from 'local_trainingprogram/dynamicform';
import ModalForm from 'core_form/modalform';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import Ajax from 'core/ajax';
import {get_string as getString} from 'core/str';
const Selectors = {
    actions: {
        createevalutionmethod: '[data-action="createevalutionmethod"]',
        deleteevalutionmethod: '[data-action="deleteevalution"]',
      
    },
};
export const init = () => {
    document.addEventListener('click', function(e) {
       // e.stopImmediatePropagation()
        let element = e.target.closest(Selectors.actions.createevalutionmethod);
        if (element) {
            e.preventDefault();
            e.stopImmediatePropagation();
            const title = element.getAttribute('data-id') ?
                getString('editevalutionmethod', 'local_trainingprogram') :
                getString('createevalutionmethod', 'local_trainingprogram');
            const form = new TPDynamicForm({
                formClass: 'local_trainingprogram\\form\\evalutionmethod_form',
                args: {id: element.getAttribute('data-id')},
                modalConfig: {title},
                returnFocus: element,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }

        let deleteevalution = e.target.closest(Selectors.actions.deleteevalutionmethod);
        if (deleteevalution) {
             e.preventDefault();
             e.stopImmediatePropagation();
             const id = deleteevalution.getAttribute('data-id');
             const pname= deleteevalution.getAttribute('data-name');
             
             var displayparams = {};
             displayparams.id = id;
             displayparams.pname = pname;
            ModalFactory.create({
                title: getString('deleteconfirm', 'local_trainingprogram'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('deleteevalutionmessage', 'local_trainingprogram',displayparams)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('deletetext', 'local_trainingprogram'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.id = id;
                    var promise = Ajax.call([{
                        methodname: 'local_trainingprogram_deleteevaluation',
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
