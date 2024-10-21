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
        function(TPDynamicForm, $, Str, ModalFactory, ModalEvents, Fragment, Ajax, Y, Templates) {

    /**
     * Constructor
     *
     * @param {String} selector used to find triggers for the new group modal.
     * @param {int} contextid
     *
     * Each call to init gets it's own instance of this class.
     */
    var NewCategory = function(selector, contextid, categoryid, hallid, typeid, type, startdate=false, enddate=false, duration=false, entitiesseats=false, hallseats=false, entityname=false, entityid=false, starttime=false, reservationid=false, submit_type=false) {

        this.contextid = contextid;
        this.categoryid = categoryid;
        this.hallid = hallid;
        this.typeid = typeid;
        this.type = type;
        this.startdate = startdate;
        this.enddate = enddate;
        this.duration = duration;
        this.entitiesseats = entitiesseats;
        this.hallseats = hallseats;
        this.entityname = entityname;
        this.entityid = entityid;
        this.starttime = starttime;
        this.reservationid = reservationid;
        this.submit_type = submit_type;
        var self = this;
        self.init(selector, entitiesseats, hallseats, entityname);
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
    NewCategory.prototype.init = function(selector, entitiesseats, hallseats, entityname) {
        var self = this;
        // Fetch the title string.
            if (self.categoryid) {
                var head =  Str.get_string('reservation', 'local_hall');
            } else {
               var head =  Str.get_string('reservation', 'local_hall');
            }

            var data = {};
            data.entityname = entityname;
            data.entityseats = entitiesseats;
            data.hallseats = hallseats;

            if(entityname == 'Current Offering') {
                var showing =  Str.get_string('currentoffering', 'local_hall', data);            
            } else if(entityname == 'Current Event') {
                var showing =  Str.get_string('currentevent', 'local_hall', data);            
            } else if(entityname == 'Current Questionbank') {
                var showing =  Str.get_string('currentquestionbank', 'local_hall', data);
            } else {
                data.entityname = entityname;
                var showing =  Str.get_string('entityshowing', 'local_hall', data);                
            }

            showing.done(function(html, js) {
                return head.then(function(title) {
                    // Create the modal.
                    return ModalFactory.create({
                        // type: ModalFactory.types.SAVE_CANCEL,
                        title: html,
                        body: self.getBody()
                    });
                }.bind(self)).then(function(modal) {
                    // Keep a reference to the modal.
                    self.modal = modal;
                    // self.modal.getRoot().addClass('openLMStransition');
                    self.modal.show();

                    $("#city").on('change', function(e) {

                        $("#buildingname").prop('selected', false);
                        $("#buildingname").parent().find('.badge-info').html('');

                        $("#id_halls option:selected").prop('selected', false);
                        $('#id_halls').parent().find('.badge-info').html('');
                        var city = $(this).text();
                        city = JSON.stringify(city);
                        var hallcities = $(this).closest("form").find("select[name='halls']");
                        // hallcities.val('');
                        hallcities.data('city',city);
                    });
                    $("select[name='buildingname']").on('change', function(e) {
                        $("#id_halls option:selected").prop('selected', false);
                        $('#id_halls').parent().find('.badge-info').html('');
                        var building = $("#buildingname option:selected" ).text();
                        building = JSON.stringify(building);
                        var buildingname = $(this).closest("form").find("select[name='halls']");
                        // buildingname.val('');
                        buildingname.data('buildingname',building);
                    });
                   $('#id_halls').on('change', function(e) {
                        $(".selecthall").html('');
                        $('.entityhall .badge-info').html('');
                        $('.entityhall .badge-info').html($("#id_halls option:selected").text());
                        $("select[name='halladdress']").val($("#id_halls option:selected").val());

                        var params = {};
                        params.sessionkey = $("input[name='sesskey']").val();
                        params.type = $("select[name='halladdress']").data('type');
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

                    $('#id_moduledates').on('change',function(){
                        // alert("Works");
                        $(".selecthall").html('');
                       /* $('.entityhall .badge-info').html('');
                        $('.entityhall .badge-info').html($("#id_halls option:selected").text());
                        $("select[name='halladdress']").val($("#id_halls option:selected").val());
                        var params = {};
                        params.sessionkey = $("input[name='sesskey']").val();
                        params.type = $("select[name='halladdress']").data('type');
                        var promise = Ajax.call([{
                             methodname: 'remove_reservations',
                             args: params
                        }]);
                        promise[0].done(function(resp) {
                            $(".entityhalldetails").html('');
                            console.log('Successfully reservations removed');
                        }).fail(function() {
                            console.log('exception');
                        }); */   
                    });
                    // Forms are big, we want a big modal.
                    self.modal.setLarge();
                    self.modal.getRoot().addClass('reservationhallmodal');
                    self.modal.getRoot().find(".modal-dialog").addClass('modal-xl');
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
                    self.modal.getRoot().on(ModalEvents.bodyRendered, (e) => {
                        $("#city").on('change', function(e) {

                            $("#buildingname").prop('selected', false);
                            $("#buildingname").parent().find('.badge-info').html('');
                            
                            $("#id_halls option:selected").prop('selected', false);
                            $('#id_halls').parent().find('.badge-info').html('');
                            var city = $(this).text();
                            city = JSON.stringify(city);
                            var hallcities = $(this).closest("form").find("select[name='halls']");
                            hallcities.data('city',city);
                        });
                        $("select[name='buildingname']").on('change', function(e) {
                            $("#id_halls option:selected").prop('selected', false);
                            $('#id_halls').parent().find('.badge-info').html('');                        
                            var building = $("#buildingname option:selected" ).text();
                            building = JSON.stringify(building);
                            var buildingname = $(this).closest("form").find("select[name='halls']");
                            // buildingname.val('');
                            buildingname.data('buildingname',building);
                        });

                        $('#id_halls').on('change', function(e) {
                            $(".selecthall").html('');                            
                            $('.entityhall .badge-info').html('');
                            $('.entityhall .badge-info').html($("#id_halls option:selected").text());
                            $("select[name='halladdress']").val($("#id_halls option:selected").val());

                            var params = {};
                            params.sessionkey = $("input[name='sesskey']").val();
                            params.type = $("select[name='halladdress']").data('type');
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

                        $('#id_moduledates').on('change',function(){
                            // alert("Works");
                            $(".selecthall").html('');
                           /* $('.entityhall .badge-info').html('');
                            $('.entityhall .badge-info').html($("#id_halls option:selected").text());
                            $("select[name='halladdress']").val($("#id_halls option:selected").val());
                     
                            var params = {};
                            params.sessionkey = $("input[name='sesskey']").val();
                            params.type = $("select[name='halladdress']").data('type');
                            var promise = Ajax.call([{
                                 methodname: 'remove_reservations',
                                 args: params
                            }]);
                            promise[0].done(function(resp) {
                                $(".entityhalldetails").html('');
                                console.log('Successfully reservations removed');
                            }).fail(function() {
                                console.log('exception');
                            });*/    
                        });
                    });                
                    return this.modal;
                }.bind(this));
            });
    };

    /**
     * @method getBody
     * @private
     * @return {Promise}
     */
    NewCategory.prototype.getBody = function(formdata) {
        if (typeof formdata === "undefined") {
            formdata = {};
        }

        params = {};
        params.jsonformdata = JSON.stringify(formdata);
        params.categoryid = this.categoryid;
        params.hallid = this.hallid;
        params.typeid = this.typeid;
        params.type = this.type;
        params.startdate = this.startdate;
        params.enddate = this.enddate;
        params.duration = this.duration;
        params.entitiesseats = this.entitiesseats;
        params.entityid = this.entityid;
        params.starttime = this.starttime;
        params.reservationid = this.reservationid;
        params.submit_type = this.submit_type;
        // this.contextid = 1
        return Fragment.loadFragment('local_hall', 'listofhallsform', this.contextid, params);
    };

    /**
     * @method handleFormSubmissionResponse
     * @private
     * @return {Promise}
     */
    NewCategory.prototype.handleFormSubmissionResponse = function(formData) {
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
            return new NewCategory(args.selector, args.contextid, args.categoryid, args.hallid, args.typeid, args.type, 0,0,0,args.entitytotalseats, args.hallseats, args.entityname, args.typeid, args.starttime, args.hallreservationid);
        },
        load: function() {

        },
        reservation: function(type) {
            if(type == "tprogram") {
                var hallid = $("select[name='halladdress']").val();
                var starthours = $("select[name='starttime[hours]']").val();
                var startminutes = $("select[name='starttime[minutes]']").val();

                const hours = starthours*3600; // 24-hour format, 0 = midnight, 15 = 3PM
                const minutes = startminutes*60;
                var starttime = hours+minutes;
                var typeid = 0;

                var endhours = $("select[name='endtime[hours]']").val();
                var endminutes = $("select[name='endtime[minutes]']").val();

                const end_hours = endhours*3600; // 24-hour format, 0 = midnight, 15 = 3PM
                const end_minutes = endminutes*60;
                var endtime = end_hours+end_minutes;

                var duration = endtime - starttime;
                var entitiesseats = $("input[name='availableseats']").val();
                var entityname = 'Current Offering';
                var entityid = $("input[name='trainingid']").val();
                var contextid = $("input[name='contextid']").val();
                var submit_type = $("input[name='submit_type']").val();

            } else if(type == "questionbank") {
                var hallid = $("select[name='halladdress']").val();
                var id = $("select[name='halladdress']").data('id');
                var starthours = $("select[name='starttime[hours]']").val();
                var startminutes = $("select[name='starttime[minutes]']").val();
                var typeid = $("input[name='id']").val();
                var submit_type = $("input[name='submit_type']").val();
                if(typeid == '' || typeid < 0) {
                    typeid = 0;
                }
                var duration = $("input[name='duration[number]']").val();
                var entityname = 'Current Questionbank';
                var entityid = 0;
                var entitiesseats = 0;
                var contextid = $("input[name='contextid']").val();
            } else {
                var type = 'event';
                var hallid = $("select[name='halladdress']").val();
                var starthours = $("select[name='eventslothour']").val();
                var startminutes = $("select[name='eventslotmin']").val();
                var typeid = $("input[name='id']").val();
                var submit_type = $("input[name='submit_type']").val();
                if(typeid == '') {
                    typeid = 0;
                }
                var duration = $("input[name='eventduration[number]']").val();
                var entityname = 'Current Event';
                var entityid = 0;
                var entitiesseats = 0;
                var contextid = $("input[name='contextid']").val();                
            }

            var params = {};
            params.sessionkey = $("input[name='sesskey']").val();
            params.halls = hallid;
            params.type = type;                
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

            const hours = starthours*3600; // 24-hour format, 0 = midnight, 15 = 3PM
            const minutes = startminutes*60;
            var starttime = hours+minutes;

            var city = $("#city option:selected").val();
            if(city == "") {
                city = 0;
            }
            var buildingname = $("#buildingname option:selected").val();
            if(buildingname == '') {
                buildingname = 0;
            }

            if(hallid > 0) {
                params = {};
                params.hallid = hallid;
                params.entitiesseats = entitiesseats;
                params.type = type;
                var hallseats = Fragment.loadFragment('local_hall', 'hallseats', contextid, params);
                var hallseatscount = '';
                hallseats.done(function(html, js) {
                    response = JSON.parse(html);
                    hallseatscount = response.hallseats;
                    entitiesseats = response.entitiesseats;
                    var startdate = $("select[name='startdate[year]']").val()+'-'+$("select[name='startdate[month]']").val()+'-'+$("select[name='startdate[day]']").val();
                    if(type == "questionbank") {
                        var enddate = $("select[name='startdate[year]']").val()+'-'+$("select[name='startdate[month]']").val()+'-'+$("select[name='startdate[day]']").val();
                    } else {
                        var enddate = $("select[name='enddate[year]']").val()+'-'+$("select[name='enddate[month]']").val()+'-'+$("select[name='enddate[day]']").val();
                    }
                    var hallreservationid = 0;
                    return new NewCategory('reservationmodal', contextid, 0, hallid, typeid, type, startdate, enddate, duration, entitiesseats, hallseatscount, entityname, entityid, starttime, hallreservationid, submit_type);
                });
            } else {
                alert("Please select proper Hall");
            }
        }        
    };
});
