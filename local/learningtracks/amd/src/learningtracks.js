/**
 * Add a create new group modal to the page.
 *
 * @module     core_group/AjaxForms
 * @class      AjaxForms
 * @package
 * @copyright  2017 Damyon Wiese <damyon@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 define([
    'core/str',
    'core/modal_factory',
    'core/modal_events',
    'core/ajax',
    'core/templates',
    'jquery',
    'theme_academy/cardPaginate',
    'theme_academy/homepage',
    'jqueryui',
], function(Str, ModalFactory, ModalEvents, Ajax, Templates, $,Cardpaginate, HomePage) {
    //var learningtrack;
    return {
        init: function(args) {
            
        },
        completionInfo: function(params) {
            var name = params.name;
            var target = "."+name;
            var promise = Ajax.call([{
                methodname: 'local_learningtracks_trackview_'+name,
                args: params
            }]);
            $("#courses_tabdataid").empty();
            $("#usersid").empty();
            $("#audiencesid").empty();
            $("#requestedusersid").empty();
            $(".tab-pane").removeClass('active');
            $("#"+name).addClass('active');

            promise[0].done(function(resp) {
                var data = Templates.render('local_learningtracks/trackview'+name, {response: resp});
                data.then(function(response){
                    $(target).html(response);
                });
            }).fail(function() {
                // do something with the exception
                //console.log(ex);
            });
        },
        coursesData: function(params) {
            var targetid = 'courses_tabdata';
            var options = {targetID: targetid,
                        templateName: 'local_learningtracks/trackviewcourses',
                        methodName: 'local_learningtracks_trackview_courses',
                        perPage: 5,
                        cardClass: 'col-md-6 col-12',
                        viewType: 'card'};

            var dataoptions = {tabname: 'courses',trackid: params};
            var filterdata = {};

            Cardpaginate.reload(options, dataoptions,filterdata);
        },
        load: function () {

        }
        // LearningItems: function() {
        //     let homepage = new HomePage();
        //     $('#enroll').on('click', function(e) {
        //         e.preventDefault();
        //        let learningitems = $('.learningitem');
        //        let message = '';
        //         console.log(learningitems);
        //        $(learningitems).each(function(index){
        //             let names[] = $(this).prop('name');
        //        });

        //        if(message !== ''){
        //             homepage.confirmbox('Please select variation for all the learningitems');
        //        }else{
        //         e.trigger('click');
        //        }
               
        //        // console.log(learningitems); debugger;
        //        //  if(($("input[name^='exam-*[]']").prop('checked') == false) || ($("input[name^='tp-*[]']").prop('checked') == false)){
        //        //      alert('Please select options');
        //        //      return false;
        //        //      //do something
        //        //  } else {
        //        //      return true;
        //        //  }
        //     });
       
        // }
    };
});
