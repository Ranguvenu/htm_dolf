//import TPDynamicForm from 'local_exams/dynamicform';
import {get_string as getString} from 'core/str';
import ModalFactory from 'core/modal_factory';
import Ajax from 'core/ajax';
import ModalForm from 'core_form/modalform';
import ModalEvents from 'core/modal_events';
import Templates from 'core/templates';
import homepage from 'theme_academy/homepage';

const Selectors = {
    actions: {
        removefavourites: '[data-action="removefavourites"]',
        addtofavourites: '[data-action="addtofavourites"]',
    },
};
let HomePage = new homepage();
export const init = () => {
    document.addEventListener('click', function(e) {
       let removefavourites = e.target.closest(Selectors.actions.removefavourites);
        if (removefavourites) {
             e.preventDefault();
             const action = removefavourites.getAttribute('data-action');
             const userid = removefavourites.getAttribute('data-userid');
             const itemtype = removefavourites.getAttribute('data-itemtype');
             const itemid = removefavourites.getAttribute('data-itemid');
             const component = removefavourites.getAttribute('data-component');
             const courseid = removefavourites.getAttribute('data-courseid');

            ModalFactory.create({
                title: getString('remfavconfirm', 'local_exams'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('remfavconfirmheader', 'local_exams',name)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('yesremove', 'local_exams'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.userid = userid;
                    params.itemtype = itemtype;
                    params.itemid = itemid;
                    params.component = component;
                    params.courseid = courseid;
                    var html='<i class="fa fa-star addfav" data-component="'+params.component+'" data-action="addtofavourites" data-itemtype="'+itemtype+'" data-itemid="'+itemid+'" data-userid="'+userid+'"  data-courseid="'+courseid+'" title="JS Add To Favourites" id="add'+itemid+'" style="color: #2b2b2b;"></i>';
                    var promise = Ajax.call([{
                        methodname: 'local_exams_removefavourites',
                        args: params
                    }]);
                    promise[0].done(function(resp) {
                         $(".removeshow"+itemid).hide();
                         $(".removehide"+itemid).hide();
                         $(".addhide"+itemid).show();
                         $(".addshow"+itemid).show();
                         modal.destroy();
                         HomePage.confirmbox(getString('removefavsuccess', 'local_exams'));
                    }).fail(function() {
                        console.log('exception');
                    });       
                }.bind(this));
                modal.show();
            }.bind(this));
        }
        let addtofavourites = e.target.closest(Selectors.actions.addtofavourites);
        if (addtofavourites) {
             e.preventDefault();
             const action = addtofavourites.getAttribute('data-action');
             const userid = addtofavourites.getAttribute('data-userid');
             const itemtype = addtofavourites.getAttribute('data-itemtype');
             const itemid = addtofavourites.getAttribute('data-itemid');
             const component = addtofavourites.getAttribute('data-component');
             const courseid = addtofavourites.getAttribute('data-courseid');
            ModalFactory.create({
                title: getString('favconfirm', 'local_exams'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('favconfirmheader', 'local_exams',name)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('addfav', 'local_exams'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.userid = userid;
                    params.itemtype = itemtype;
                    params.itemid = itemid;
                    params.component = component;
                    params.courseid = courseid;
                    var html='<i class="fa fa-star removefav" data-component="'+params.component+'" data-action="removefavourites" data-itemtype="'+itemtype+'" data-itemid="'+itemid+'" data-userid="'+userid+'"  data-courseid="'+courseid+'" title="JS Remove Favourites" id="remove'+itemid+'" style="color: #004c98;"></i>';
                    var promise = Ajax.call([{
                        methodname: 'local_exams_addtofavourites',
                        args: params
                    }]);
                    promise[0].done(function(resp) {
                     $(".addshow"+itemid).hide();   
                     $(".addhide"+itemid).hide();   
                     $(".removehide"+itemid).show();
                     $(".removeshow"+itemid).show();
                      HomePage.confirmbox(getString('addfavsuccess', 'local_exams'));
                     modal.destroy();
                    }).fail(function() {
                        console.log('exception');
                    });       
                }.bind(this));
                modal.show();
            }.bind(this));
        }
       
    });
};
