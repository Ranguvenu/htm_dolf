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
import Modalform from 'core_form/modalform';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import homepage from 'theme_academy/homepage';
import $ from 'jquery';
import {get_string as getString} from 'core/str';
import Ajax from 'core/ajax';
import Templates from 'core/templates';

const Selectors = {
    actions: {
        deducttraineeamount: '[data-action="deduct-traineeamount"]'
    },
};

let HomePage = new homepage();
export const init = () => {
    document.addEventListener('click', function(e) {
        // e.stopImmediatePropagation();
        let deducttraineeamount = e.target.closest(Selectors.actions.deducttraineeamount);
        if (deducttraineeamount) {
            e.stopImmediatePropagation();
            const walletid = deducttraineeamount.getAttribute('data-walletid');
            const walletamount = deducttraineeamount.getAttribute('data-walletamount');
            var options = {};
            options.walletid = walletid;
            options.walletamount = walletamount;
            var trigger = $(Selectors.actions.deducttraineeamount);
            ModalFactory.create({
            title: getString('paymentdeduction', 'tool_product'),
            type: ModalFactory.types.SAVE_CANCEL,
            body: Templates.render('tool_product/deductionsettings', options).done(function(html, js) {
                                        Templates.replaceNodeContents('deductionsettings', html, js);
                                    })
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('deduct', 'tool_product'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.walletid = walletid;
                    params.walletamount = walletamount;
                    params.deductedamount = $("#deductedamount").val();

                    if(params.deductedamount < 0){
                        HomePage.confirmbox(getString('deductamountisnegative', 'tool_product'));
                    } else if(+params.deductedamount > +params.walletamount){
                        HomePage.confirmbox(getString('deductamountismore', 'tool_product'));
                    } else if(isNaN(params.deductedamount)){
                        HomePage.confirmbox(getString('deductcannotbenull', 'tool_product'));
                    } else {
                        var promise = Ajax.call([{
                            methodname: 'tool_product_trainee_walletamount',
                            args: params
                        }]);
                        promise[0].done(function(resp) {
                            window.location = M.cfg.wwwroot + '/admin/tool/product/financialpayments.php?mode=3';
                        }).fail(function() {
                            // do something with the exception
                             console.log('exception');
                        });
                    }
                }.bind(this));
                modal.show();
            }.bind(this));
        }
    });  
};