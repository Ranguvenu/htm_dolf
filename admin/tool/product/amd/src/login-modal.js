define(['jquery', 'core/notification', 'core/ajax', 'core/custom_interaction_events', 'core/modal', 'core/modal_registry', "theme_academy/homepage"],
        function($, Notification, Ajax, CustomEvents, Modal, ModalRegistry, HomePageJs) {

    var registered = false;
    var SELECTORS = {
        LOGIN_BUTTON: '[data-action="login"]',
    };

    /**
     * Constructor for the Modal.
     *
     * @param {object} root The root jQuery element for the modal
     */
    var ModalLogin = function(root) {
        Modal.call(this, root);

        if (!this.getFooter().find(SELECTORS.LOGIN_BUTTON).length) {
            Notification.exception({message: 'No login button found'});
        }

        // if (!this.getFooter().find(SELECTORS.CANCEL_BUTTON).length) {
        //     Notification.exception({message: 'No cancel button found'});
        // }
    };

    ModalLogin.TYPE = 'tool_product-login';
    ModalLogin.prototype = Object.create(Modal.prototype);
    ModalLogin.prototype.constructor = ModalLogin;

    /**
     * Set up all of the event handling for the modal.
     *
     * @method registerEventListeners
     */
    ModalLogin.prototype.registerEventListeners = function() {
        // Apply parent event listeners.
        Modal.prototype.registerEventListeners.call(this);

        this.getModal().on(CustomEvents.events.activate, SELECTORS.LOGIN_BUTTON, function(e, data) {
          //   // Add your logic for when the login button is clicked. This could include the form validation,
          //   // loading animations, error handling etc.
         	// console.log("data", $("#inputPassword").val());
             $('[data-loader=login]').removeClass('d-none');  
            let promise = Ajax.call([{
            	methodname: 'tool_product_user_login',
	            args: {
	            	username: $("#username").val(),
                    password: $("#inputPassword").val()
	            }
	        }]);
	        promise[0].done(function(response) {
                let homepage = new HomePageJs();
	            if(response.success){
                    console.log(window.location.pathname);
                   // alert(window.location.pathnam);
                    if(window.location.pathname == '/local/exams/exams_qualification_details.php'){
                        location.replace(window.location.href);
                    }else if(window.location.pathname == '/local/trainingprogram/programcourseoverview.php'){
                        location.replace(window.location.href);
                    }else{
                        location.replace($("#redirecturi").val());
                    }
                    
                }else{
                    $('[data-loader=login]').addClass('d-none');  
                    homepage.confirmbox(response.error);
                }
	        }).fail(function( error ) {
                let homepage = new HomePageJs();
                    homepage.confirmbox(error.message);
	        });
        }.bind(this));

        // this.getModal().on(CustomEvents.events.activate, SELECTORS.CANCEL_BUTTON, function(e, data) {
        //     // Add your logic for when the cancel button is clicked
        //         //this.Modal.setBody('');
        //         this.hide();
        // }.bind(this));
    };

    // Automatically register with the modal registry the first time this module is imported so that you can create modals
    // of this type using the modal factory.
    if (!registered) {
        ModalRegistry.register(ModalLogin.TYPE, ModalLogin, 'tool_product/login-modal');
        registered = true;
    }

    return ModalLogin;
});
