import TPDynamicForm from 'local_trainingprogram/dynamicform';
import ModalForm from 'core_form/modalform';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import Ajax from 'core/ajax';
import {get_string as getString} from 'core/str';

const Selectors = {
    actions: {
        createuser_custom_form: '[data-action="user_custom_form"]',
        useraddwallet: '[data-action="user_wallet_form"]',
        removerequest: '[data-action="removerequest"]',

    },
};
export const init = () => {
    document.addEventListener('click', function(e) {
        //e.stopImmediatePropagation()
        let element = e.target.closest(Selectors.actions.createuser_custom_form);
        if (element) {
            e.preventDefault();
            const title = element.getAttribute('data-id') ?
                getString('editcustom', 'local_userapproval', element.getAttribute('data-name')) :
                getString('addcustom', 'local_userapproval');
            const form = new TPDynamicForm({
                formClass: 'local_userapproval\\form\\user_custom_form',
                args: {id: element.getAttribute('data-id'),userid: element.getAttribute('data-userid')},
                modalConfig: {title},
                returnFocus: element,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }

         let removerequest = e.target.closest(Selectors.actions.removerequest);
        if (removerequest) {
             e.preventDefault();
            const orgid = removerequest.getAttribute('data-orgid');
            const userid = removerequest.getAttribute('data-userid');
            const orgname = removerequest.getAttribute('data-orgname');
            ModalFactory.create({
                title: getString('removeconfirm', 'local_userapproval'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('orgrequestremoveconfirm', 'local_userapproval',orgname)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('orgrequestremoveconfirmtext', 'local_userapproval'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.orgid = orgid;
                    params.userid = userid;
                    var promise = Ajax.call([{
                        methodname: 'local_userapproval_removeorgrequest',
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
        let walletelement = e.target.closest(Selectors.actions.useraddwallet);
        if (walletelement) {
            e.preventDefault();
            const title = getString('addwallet', 'local_userapproval');
            const form = new ModalForm({
                formClass: 'local_userapproval\\form\\user_wallet_form',
                args: {userid: walletelement.getAttribute('data-userid')},
                modalConfig: {title},
                returnFocus: walletelement,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, (e) => {
                e.preventDefault();
                const response = JSON.parse(e.detail);
                window.location.href = response.returnurl;
            });
            form.show();
        }
    });

};
