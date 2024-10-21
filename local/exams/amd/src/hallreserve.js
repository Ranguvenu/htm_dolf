/**
 * Add a create new group modal to the page.
 *
 * @module     local_hall/reserve
 * @class      NewCourse
 * @package    local_hall
 * @copyright  2022 Revanth kumar Grandhi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['local_trainingprogram/dynamicform', 'jquery', 'core/str', 'core/modal_factory', 'core/modal_events', 'core/fragment', 'core/ajax', 'core/yui', 'core/templates'],
        function(TPDynamicForm, $, Str, ModalFactory, ModalEvents, Fragment, Ajax, Y, Templates ) {

    /**
     * Constructor
     *
     * @param {String} selector used to find triggers for the new group modal.
     * @param {int} contextid
     *
     * Each call to init gets it's own instance of this class.
     */
    var NewCategory = function(selector, contextid, categoryid, hallid, typeid) {

        this.contextid = contextid;
        this.categoryid = categoryid;
        this.hallid = hallid;
        this.typeid = typeid;
        var self = this;
        self.init(selector);
    };

    /**
     * @var {Modal} modal
     * @private
     */
    NewCategory.prototype.modal = null;

    /**
     * @var {int} contextid
     * @private
     */
    NewCategory.prototype.contextid = -1;

    /**
     * Initialise the class.
     *
     * @param {String} selector used to find triggers for the new group modal.
     * @private
     * @return {Promise}
     */
    NewCategory.prototype.init = function(selector) {
        var self = this;
        // Fetch the title string.
            if (self.categoryid) {
                var head =  Str.get_string('reservation', 'local_exams');
            }else{
               var head =  Str.get_string('reservation', 'local_exams');
            }
            return head.then(function(title) {
                // Create the modal.
                return ModalFactory.create({
                    // type: ModalFactory.types.SAVE_CANCEL,
                    title: title,
                    body: self.getBody(),
                });
            }.bind(self)).then(function(modal) {
                // Keep a reference to the modal.
                self.modal = modal;
                self.modal.getRoot().addClass('tphallreserve');
                self.modal.show();
                // Forms are big, we want a big modal.
                self.modal.setLarge();

                // We want to reset the form every time it is opened.
                self.modal.getRoot().on(ModalEvents.hidden, function() {
                    self.modal.setBody('');
                    self.modal.getRoot().animate({"right":"-85%"}, 500);
                    setTimeout(function(){
                        modal.destroy();
                    }, 1000);
                }.bind(this));

                // We want to hide the submit buttons every time it is opened.
                self.modal.getRoot().on(ModalEvents.shown, function() {
                    self.modal.getRoot().append('<style>[data-fieldtype=submit] { display: none ! important; }</style>');
                }.bind(this));


                // We catch the modal save event, and use it to submit the form inside the modal.
                // Triggering a form submission will give JS validation scripts a chance to check for errors.
                self.modal.getRoot().on(ModalEvents.save, self.submitForm.bind(self));
                // We also catch the form submit event and use it to submit the form with ajax.
                self.modal.getRoot().on('submit', 'form', self.submitFormAjax.bind(self));
                self.modal.getRoot().animate({"right":"0%"}, 500);
                // self.modal.getRoot().on(ModalEvents.bodyRendered, (e) => {
                //     $("select[name='halls']").on('change', function(e){
                //         var hallid = $("select[name='halls']").val();
                //         alert(hallid);
                //         var params = {};
                //         params.hallid = hallid;
                //         params.examid = this.examid;
                //         var promise = Ajax.call([{
                //             methodname: 'local_hall_data',
                //             args: params
                //         }]);
                //         promise[0].done(function(resp) {
                //             $(".selecthall").html(resp.options);
                //         }).fail(function() {
                //             // do something with the exception
                //              console.log('exception');
                //         });                        
                //     });  
                // });
                return this.modal;
            }.bind(this));
        // });
    };

    /**
     * @method getBody
     * @private
     * @return {Promise}
     */
    NewCategory.prototype.getBody = function(formdata) {
        // var options = {};
        // options.examid = 1;      
        // return Templates.render('local_exams/testing',options).done(function(html, js) {
        //     Templates.replaceNodeContents('targetcompetencypc', html, js);
        // });
        if (typeof formdata === "undefined") {
            formdata = {};
        }

        params = {};
        params.jsonformdata = JSON.stringify(formdata);
        params.categoryid = this.categoryid;
        params.hallid = this.hallid;
        params.typeid = this.typeid;
        // this.contextid = 1;
        return Fragment.loadFragment('local_exams', 'listofhallsform', this.contextid, params);
        // return Templates.render('local_exams/testing',options).done(function(html, js) {
        //     Fragment.loadFragment('local_exams', 'listofhallsform', this.contextid, params);
        //     Templates.replaceNodeContents('targetcompetencypc', html, js);
        // });
    };

    /**
     * @method handleFormSubmissionResponse
     * @private
     * @return {Promise}
     */
    NewCategory.prototype.handleFormSubmissionResponse = function(formData) {
            var params = {};
            params.jsonformdata = formData;
            params.typeid = this.typeid;
            var promise = Ajax.call([{
                methodname: 'local_hall_data',
                args: params
            }]);
            promise[0].done(function(resp) {
                $(".selecthall").html(resp.options);
            }).fail(function() {
                // do something with the exception
                 console.log('exception');
            });
        // We could trigger an event instead.
        // Yuk.
        // Y.use('moodle-core-formchangechecker', function() {
        //     M.core_formchangechecker.reset_form_dirty_state();
        // });
        // document.location.reload();
    };

    /**
     * @method handleFormSubmissionFailure
     * @private
     * @return {Promise}
     */
    NewCategory.prototype.handleFormSubmissionFailure = function(data) {
        // Oh noes! Epic fail :(
        // Ah wait - this is normal. We need to re-display the form with errors!
        this.modal.setBody(this.getBody(data));
    };

    /**
     * Private method
     *
     * @method submitFormAjax
     * @private
     * @param {Event} e Form submission event.
     */
    NewCategory.prototype.submitFormAjax = function(e) {
        // We don't want to do a real form submission.
        e.preventDefault();

        // Convert all the form elements values to a serialised string.
        var formData = this.modal.getRoot().find('form').serialize();
        //console.log(this.contextid);
        //console.log(JSON.stringify(formData));
        // Now we can continue...
        Ajax.call([{
            methodname: 'local_hall_reservation',
            //args: {evalid:this.evalid, contextid: this.contextid, jsonformdata: JSON.stringify(formData)},
            args: {contextid: this.contextid, jsonformdata: JSON.stringify(formData)},
            done: this.handleFormSubmissionResponse.bind(this, formData),
            fail: this.handleFormSubmissionFailure.bind(this, formData)
        }]);
    };

    /**
     * This triggers a form submission, so that any mform elements can do final tricks before the form submission is processed.
     *
     * @method submitForm
     * @param {Event} e Form submission event.
     * @private
     */
    NewCategory.prototype.submitForm = function(e) {
        e.preventDefault();
        var self = this;
        self.modal.getRoot().find('form').submit();
    };

    return /** @alias module:local_course/newcourse */ {
        // Public variables and functions.
        /**
         * Attach event listeners to initialise this module.
         *
         * @method init
         * @param {string} selector The CSS selector used to find nodes that will trigger this module.
         * @param {int} contextid The contextid for the course.
         * @return {Promise}
         */
        init: function(args) {
            return new NewCategory(args.selector, args.contextid, args.categoryid, args.hallid, args.typeid);
        },
        load: function() {

        },


    };
});
