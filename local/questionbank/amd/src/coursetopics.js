define(['jquery', 'core/ajax', 'core/notification'], function($, Ajax, Notification) {

    return /** @alias module:tool_lpmigrate/frameworks_datasource */ {

        /**
         * Process the results for auto complete elements.
         *
         * @param {String}s selector The selector of the auto complete element.
         * @param {Array} results An array or results.
         * @return {Array} New array of results.
         */
        processResults: function(selector, results) {
            var options = [];
            $.each(results.data, function(index, response) {
                options.push({
                    value: response.id,
                    label: response.fullname
                });
            });
            return options;
        },

        /**
         * Source of data for Ajax element.
         *
         * @param {String} selector The selector of the auto complete element.
         * @param {String} query The query string.
         * @param {Function} callback A callback function receiving an array of results.
         */
        /* eslint-disable promise/no-callback-in-promise */
        transport: function(selector, query, callback) {
            var el = $(selector),
            type = el.data('type');
            questionbankid = el.data('questionbankid');
            switch(type){
                case 'topicslistforquestions':
                    
                    var courseid = $("#id_customfield_courses").val();
                    alert($(".qcategory").val());
                    // Ajax.call([{
                    // methodname: 'local_questionbank_topic_selector',
                    // args: {type: type,courseid:JSON.stringify(courseid),questionbankid: questionbankid}
                    // }])[0].then(callback).catch(Notification.exception);

                break;
                case 'topicslist':
                    if($("#id_customfield_courses").val()){
                        var courseid = $("#id_customfield_courses").val();
                    }else{
                       var courseid = $(".el_courselist option:selected").val();
                    }
         
                    Ajax.call([{
                    methodname: 'local_questionbank_topic_selector',
                    args: {type: type,courseid:JSON.stringify(courseid),questionbankid: questionbankid}
                    }])[0].then(callback).catch(Notification.exception);
           
                break;
                 case 'competencylist':
                       var ctype = $(".el_competencytype option:selected").val();
                       var type = 'qbcompetencylist';
                    
                    Ajax.call([{
                    methodname: 'local_questionbank_qbcompetencieslist',
                    args: {query:query,ctype: ctype}
                    }])[0].then(callback).catch(Notification.exception);
           
                break;
            }
        },
        selectedcourses: function() {

                $("select[name='topicsid[]'] option:selected").prop('selected', false);
                $("select[name='topicsid[]']").parent().find('.badge-info').html('');

            },

            

            
       
    };

});
