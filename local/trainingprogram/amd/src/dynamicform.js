import ModalForm from 'core_form/modalform';
import Pending from 'core/pending';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import Notification from 'core/notification';
import Ajax from 'core/ajax';
import {get_strings as getStrings} from 'core/str';

/**
 * @class local_trainingprogram/dynamicform
 */
export default class TPDynamicForm extends ModalForm {
/**
     * Various events that can be observed.
     *
     * @type {Object}
     */
    events = {
        // Form was successfully submitted - the response is passed to the event listener.
        // Cancellable (but it's hardly ever needed to cancel this event).
        FORM_SUBMITTED: 'core_form_modalform_formsubmitted',
        // Cancel button was pressed.
        // Cancellable (but it's hardly ever needed to cancel this event).
        FORM_CANCELLED: 'core_form_modalform_formcancelled',
        // User attempted to submit the form but there was client-side validation error.
        CLIENT_VALIDATION_ERROR: 'core_form_modalform_clientvalidationerror',
        // User attempted to submit the form but server returned validation error.
        SERVER_VALIDATION_ERROR: 'core_form_modalform_validationerror',
        // Error occurred while performing request to the server.
        // Cancellable (by default calls Notification.exception).
        ERROR: 'core_form_modalform_error',
        // Right after user pressed no-submit button,
        // listen to this event if you want to add JS validation or processing for no-submit button.
        // Cancellable.
        NOSUBMIT_BUTTON_PRESSED: 'core_form_modalform_nosubmitbutton',
        // Right after user pressed submit button,
        // listen to this event if you want to add additional JS validation or confirmation dialog.
        // Cancellable.
        SUBMIT_BUTTON_PRESSED: 'core_form_modalform_submitbutton',
        // Right after user pressed cancel button,
        // listen to this event if you want to add confirmation dialog.
        // Cancellable.
        CANCEL_BUTTON_PRESSED: 'core_form_modalform_cancelbutton',
        // Modal was loaded and this.modal is available (but the form content may not be loaded yet).
        LOADED: 'core_form_modalform_loaded',
        CONTENT_LOADED: 'local_trainingprogram_dynamicform_content_loaded'
    };
/**
     * Initialise the modal and shows it
     *
     * @return {Promise}
     */
    show() {
        const pendingPromise = new Pending('core_form/modalform:init');
        return ModalFactory.create(this.config.modalConfig)
        .then((modal) => {
            this.modal = modal;

            // Retrieve the form and set the modal body. We can not set the body in the modalConfig,
            // we need to make sure that the modal already exists when we render the form. Some form elements
            // such as date_selector inspect the existing elements on the page to find the highest z-index.
            const formParams = new URLSearchParams(Object.entries(this.config.args || {}));
            //Modal View - Starts//
            this.modal.setLarge();
            this.modal.getRoot().addClass('openLMStransition');
            this.modal.getRoot().on(ModalEvents.hidden, function() {
            this.modal.getRoot().animate({"right":"-85%"}, 500);
            setTimeout(function(){
                this.modal.destroy();
            }, 1000);
            this.modal.setBody('');
            }.bind(this));
            // this.modal.show();
            this.modal.getRoot().animate({"right":"0%"}, 500);
             this.modal.getFooter().find('[data-action="cancel"]').on('click', function() {
                this.modal.setBody('');
                this.modal.hide();
                setTimeout(function(){
                    this.modal.destroy();
                }, 1000);
            });
            this.modal.getRoot().find('[data-action="hide"]').on('click', function() {
                this.modal.hide();
                setTimeout(function(){
                    this.modal.destroy();
                }, 1000);
                this.modal.destroy();
            });
            //Modal View - Ends//
            const bodyContent = this.getBody(formParams.toString());
            this.modal.setBodyContent(bodyContent);
            this.modal.getRoot().on(ModalEvents.bodyRendered, (e) => {
            this.trigger(this.events.CONTENT_LOADED);
                if(typeof "select[name='sectors[]']" != 'undefined'){
                    var sectors = $("select[name='sectors[]']").val();
                    if(typeof $('#el_segmentlist') != 'undefined'){
                        $('select#el_segmentlist').data('sectorid',sectors);
                    }
                 
                    if(typeof $('#el_jobfamily') != 'undefined'){
                        $('#el_jobfamily').data('sectorid',sectors);
                    }
                }
                $("select[name='sectors[]']").on('change', function(e){
                    
                       var sectors = $(this).val();

                       var segments = $(this).closest("form").find("select[name='segments[]']");
                       segments.val('');
                       segments.attr('data-sectorid',sectors);

                       var targetgroup = $(this).closest("form").find("select[name='targetgroup[]']");

                       targetgroup.val('');
                       targetgroup.attr('data-sectorid',sectors);
                });

                if(typeof "select[name='sectors']" != 'undefined'){
                    var sectors = $("select[name='sectors']").val();
                    if(typeof $('#el_segmentlist') != 'undefined'){
                        $('select#el_segmentlist').attr('data-sectorid',sectors);
                    }
                 
                    if(typeof $('#el_jobfamily') != 'undefined'){
                        $('#el_jobfamily').attr('data-sectorid',sectors);
                    }
                }
                $("select[name='sectors']").on('change', function(e){
                    
                       var sectors = $(this).val();

                       var segments = $(this).closest("form").find("select[name='segments[]']");
                       var segment = $(this).closest("form").find("select[name='segment']");
                       segments.val('');
                       segments.attr('data-sectorid',sectors);

                       segment.val('');
                       segment.attr('data-sectorid',sectors);

                       var targetgroup = $(this).closest("form").find("select[name='targetgroup[]']");

                       targetgroup.val('');
                       targetgroup.attr('data-sectorid',sectors);
                });
                // competencies by type - Starts

                if(typeof "select[name='ctype[]']" != 'undefined'){
                    var ctype = $("select[name='ctype[]']").val();
                    if(typeof $('#el_competencieslist') != 'undefined'){
                        $('select#el_competencieslist').data('ctype',ctype);
                    }
                }
                // competencies by type - Ends

                //Job family
                if(typeof $('#el_segmentlist') != 'undefined'){
                    if(typeof $('#el_jobfamily') != 'undefined'){
                        var sectors = $('.el_sectorlist').val();
                        $('select#el_jobfamily').attr('data-sectorid',sectors);
                        var segments = $('#el_segmentlist').val();
                        $('select#el_jobfamily').attr('data-segmentid',segments);
                    }
                }
                $('#el_segmentlist').on('change', function(e){
                       var segments = $('#el_segmentlist').val();
                       $('select#el_jobfamily').val('');
                       $('select#el_jobfamily').attr('data-segmentid',segments);
                });

                //job role
                if(typeof $('#el_jobfamily') != 'undefined'){
                    if(typeof $('#el_jobroles') != 'undefined'){
                        var jobfamily = $('#el_jobfamily').val();
                        $('select#el_jobroles').attr('data-jobfamilyid',jobfamily);
                    }
                }
                $('#el_jobfamily').on('change', function(e){
                       var jobfamily = $('#el_jobfamily').val();
                       $('select#el_jobroles').val('');
                       $('select#el_jobroles').attr('data-jobfamilyid',jobfamily);
                });

                $("select[name='startdate[day]']").on('change', function(e){
                    $('.entityhall .badge-info').html('');

                    const requiredStrings = [
                        {key: 'selecthall', component: 'local_trainingprogram'},
                    ];

                    getStrings(requiredStrings).then(([title]) => {
                        $('.entityhall .badge-info').html(title);
                    }).catch();

                    $("select[name='halladdress']").val("0");
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
            });
            bodyContent.catch(Notification.exception);

            // After successfull submit, when we press "Cancel" or close the dialogue by clicking on X in the top right corner.
            this.modal.getRoot().on(ModalEvents.hidden, () => {
               this.notifyResetFormChanges();
                this.modal.destroy();
                // Focus on the element that actually launched the modal.
                if (this.config.returnFocus) {
                    this.config.returnFocus.focus();
                }
            });

            // Add the class to the modal dialogue.
            this.modal.getModal().addClass('modal-form-dialogue');

            // We catch the press on submit buttons in the forms.
            this.modal.getRoot().on('click', 'form input[type=submit][data-no-submit]',
                (e) => {
                    e.preventDefault();
                    const event = this.trigger(this.events.NOSUBMIT_BUTTON_PRESSED, e.target);
                    if (!event.defaultPrevented) {
                        this.processNoSubmitButton(e.target);
                    }
                });

            // We catch the form submit event and use it to submit the form with ajax.
            this.modal.getRoot().on('submit', 'form', (e) => {
                e.preventDefault();
                const event = this.trigger(this.events.SUBMIT_BUTTON_PRESSED);
                if (!event.defaultPrevented) {
                    this.submitFormAjax();
                }
            });

            // Change the text for the save button.
            if (typeof this.config.saveButtonText !== 'undefined' &&
                typeof this.modal.setSaveButtonText !== 'undefined') {
                this.modal.setSaveButtonText(this.config.saveButtonText);
            }
            // Set classes for the save button.
            if (typeof this.config.saveButtonClasses !== 'undefined') {
                this.setSaveButtonClasses(this.config.saveButtonClasses);
            }
            // When Save button is pressed - submit the form.
            this.modal.getRoot().on(ModalEvents.save, (e) => {
                e.preventDefault();
                this.modal.getRoot().find('form').submit();
            });

            // When Cancel button is pressed - allow to intercept.
            this.modal.getRoot().on(ModalEvents.cancel, (e) => {
                const event = this.trigger(this.events.CANCEL_BUTTON_PRESSED);
                if (event.defaultPrevented) {
                    e.preventDefault();
                }
            });
            this.futureListeners.forEach(args => this.modal.getRoot()[0].addEventListener(...args));
            this.futureListeners = [];
            this.trigger(this.events.LOADED, null, false);
            return this.modal.show();
        })
        .then(pendingPromise.resolve);
    }


}
