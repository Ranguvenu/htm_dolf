//import TPDynamicForm from 'local_trainingprogram/dynamicform';
import ModalForm from 'core_form/modalform';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import homepage from 'theme_academy/homepage';
import Ajax from 'core/ajax';
import {get_string as getString} from 'core/str';
import $ from 'jquery';
import Templates from 'core/templates';
import cardPaginate from 'theme_academy/cardPaginate';
import cfg from 'core/config';

const HomePage = new homepage();
const Selectors = {
    actions: {
        deletequestionbank: '[data-action="deletequestionbank"]',
        viewquestiontopics: '[data-action="viewquestiontopics"]',
        viewexperts: '[data-action="viewexperts"]',
        viewcompetencies: '[data-action="viewcompetencies"]',
        deletecompetency: '[data-action="deletecompetency"]',
        deletecourse: '[data-action="deletecourse"]',
        assignexpert: '[data-action="assignexpert"]',
        unassignexpert: '[data-action="unassignexpert"]',
        mapcompetencies: '[data-action="mapcompetencies"]',
        assignopicstocourse: '[data-action="assignopicstocourse"]',
        unassigntopic: '[data-action="unassigntopic"]',
        changestatus: '[data-action="changestatus"]',
        assignreviewer: '[data-action="assignreviewer"]',
        programtabs: '[data-action="programtabs"]',
        movetoprodquestionbank: '[data-action="movetoprodquestionbank"]',
        viewtopics: '[data-action="viewtopics"]', 
        selectedcourses: '[data-action="selectedcourses"]',
        bulkchangestatus: '[data-action="bulkchangestatus"]',
        //reservequestions: '[data-action="reservequestions"]'
        submit : '[data-action="save"]',
        selectedexperts : '[role="option"]',
        cancelbtn: '[data-action="cancel"]',
    },
};

export const init = () => {
    document.addEventListener('click', function(e) {
        let deletequestionbank = e.target.closest(Selectors.actions.deletequestionbank);
        if (deletequestionbank) {
            const workshopid = deletequestionbank.getAttribute('data-id');
            const workshopname = deletequestionbank.getAttribute('data-name');
            ModalFactory.create({
                title: getString('confirm', 'local_questionbank'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('deleteconfirm', 'local_questionbank',workshopname)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('deletetext', 'local_questionbank'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.workshopid = workshopid;
                    params.workshopname = workshopname;
                    var promise = Ajax.call([{
                        methodname: 'local_questionbank_delete',
                        args: params
                    }]);
                    promise[0].done(function() {
                        window.location.reload(true);
                    }).fail(function() {
                        //console.log('exception');
                    });
                }.bind(this));
                modal.show();
            }.bind(this));
        }
        let viewquestiontopics = e.target.closest(Selectors.actions.viewquestiontopics);
        if (viewquestiontopics) {
            const workshopid = viewquestiontopics.getAttribute('data-id');
            const workshopname = viewquestiontopics.getAttribute('data-name');
            var params = {};
            params.workshopid = workshopid;
            params.workshopname = workshopname;
            var promise = Ajax.call([{
                methodname: 'local_questionbank_viewtopics',
                args: params
            }]);
            promise[0].done(function(resp) {
                ModalFactory.create({
                    title: getString('viewtopics', 'local_questionbank'),
                    type: ModalFactory.types.DEFAULT,
                    body: resp.options,
                }).done(function(modal) {
                    this.modal = modal;
                    modal.setLarge(true);
                    modal.show();
                }.bind(this));
            }).fail(function() {
                // do something with the exception
                 //console.log('exception');
            });
        }
        let unassigntopic  = e.target.closest(Selectors.actions.unassigntopic);
        if (unassigntopic) {
            const questionbankid = unassigntopic.getAttribute('data-id');
            const topicid = unassigntopic.getAttribute('data-topicid');
            ModalFactory.create({
                title: getString('unassignconfirmtopic', 'local_questionbank'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('unassigntopic', 'local_questionbank')
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('unassign', 'local_questionbank'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.questionbankid = questionbankid;
                    params.topicid = topicid;
                    var promise = Ajax.call([{
                        methodname: 'local_questionbank_unassigntopics',
                        args: params
                    }]);
                    promise[0].done(function() {
                       window.location.reload(true);
                         // modal.destroy();
                    }).fail(function() {
                        //console.log('exception');
                    });
                }.bind(this));
                modal.show();
            }.bind(this));
            // var params = {};
            // params.questionbankid = questionbankid;
            // params.topicid = topicid;
            // Ajax.call([{
            //     methodname: 'local_questionbank_unassigntopics',
            //     args: params
            // }])[0].done(function() {
            //         window.location.reload();
            // });
        }

        let unassignexpert  = e.target.closest(Selectors.actions.unassignexpert);
        if (unassignexpert) {
            const questionbankid = unassignexpert.getAttribute('data-id');
             const username = unassignexpert.getAttribute('data-user');
            ModalFactory.create({
                title: getString('unassignconfirm', 'local_questionbank'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('unassignexpert', 'local_questionbank',username)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('unassign', 'local_questionbank'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.questionbankid = questionbankid;
                    var promise = Ajax.call([{
                        methodname: 'local_questionbank_assignexperts',
                        args: params
                    }]);
                    promise[0].done(function() {
                       window.location.reload(true);
                         // modal.destroy();
                    }).fail(function() {
                        //console.log('exception');
                    });
                }.bind(this));
                modal.show();
            }.bind(this));
            //var params = {};
            // params.questionbankid = questionbankid;
            // Ajax.call([{
            //     methodname: 'local_questionbank_assignexperts',
            //     args: params
            // }])[0].done(function() {
            //         window.location.reload();
            // });
        }
        // let reservequestions = e.target.closest(Selectors.actions.reservequestions);
        // if (reservequestions) {
        //     e.preventDefault();
        //     var params = {};
        //     params.eid = reservequestions.getAttribute('data-id');
        //     // slid = hallreserve.getAttribute('data-slid');            
        //     params.userid = reservequestions.getAttribute('data-userid');
        //     params.wid = reservequestions.getAttribute('data-wid');
        //     // params.start = hallreserve.getAttribute('data-start');
        //     //params.referencecode = $("input[name='sesskey']").val();
        //     params.qcount = $(".questionscount"+params.eid).val();
        //     available = reservequestions.getAttribute('data-availablequestions');
        //     var data = {};
        //     data.available = available;

        //     if(isNaN(params.qcount)){
        //         HomePage.confirmbox(getString('durationerr', 'local_questionbank'));
        //     } else if(+params.qcount < 0) {
        //         HomePage.confirmbox(getString('validquestioncount', 'local_questionbank'));
        //     }else if(+available < +params.qcount) {
        //         HomePage.confirmbox(getString('availableqcount', 'local_questionbank',  available));
        //     } else if(params.qcount == "") {
        //         HomePage.confirmbox(getString('noofquestionserr', 'local_questionbank'));
        //     }else {
        //         ModalFactory.create({
        //             title: getString('confirm', 'local_hall'),
        //             type: ModalFactory.types.SAVE_CANCEL,
        //             body: getString('selectedquestions', 'local_questionbank', params.qcount)
        //         }).done(function(modal) {
        //             this.modal = modal;
        //             modal.setSaveButtonText(getString('reserve', 'local_hall'));
        //             modal.getRoot().on(ModalEvents.save, function(e) {
        //                 modal.hide();
        //                 var options = {};
        //                 options.eid = params.eid;
        //                 options.userid = params.userid;
        //                 options.wid = params.wid;
        //                 options.qcount = params.qcount;
        //                 options.temp = 'reservequestions';
        //                // options.methodName = 'local_hall_data';
        //                 e.preventDefault();
        //                 var promise = Ajax.call([{
        //                     methodname: 'reservequestionsforexpert',
        //                     args: params
        //                 }]);
        //                 promise[0].done(function(resp) {

        //                     HomePage.confirmbox(getString('reservationsuccess', 'local_hall'));
        //                      window.location.reload(true);
        //                 }).fail(function() {
        //                      console.log('exception');
        //                 });
        //             }.bind(this));
        //             modal.show();
        //             // window.location.reload();
        //         }.bind(this));
        //     }
        // }
        let assignexpert  = e.target.closest(Selectors.actions.assignexpert);
        if (assignexpert) {
            e.preventDefault();
            const title = getString('assignexperts', 'local_questionbank');
            const form = new ModalForm({
                formClass: 'local_questionbank\\form\\expertsform',
                args: {
                    questionbankid: assignexpert.getAttribute('data-id')
                },
                modalConfig: {title},
                returnFocus: assignexpert,
            });

            form.show();
            form.addEventListener(form.events.FORM_SUBMITTED, (e) => {
                e.preventDefault();
                const spanElements = document.querySelectorAll('.badge');
                let allowed_questions = $('#questionallowed').val();
                var sum = 0;
                var emptyinput = 0;
                Array.from(spanElements).forEach((span) => {
                    const dataValue = span.getAttribute('data-value');
                    var selectedexperts = parseInt(dataValue, 10);

                    if (!isNaN(selectedexperts)) {
                        var tag_name = $('#noofquestionsfor_'+selectedexperts)[0].tagName;
                        if (tag_name == 'INPUT') {
                            if (!isNaN(parseInt($('#noofquestionsfor_'+selectedexperts).val()))) {
                                sum = sum + parseInt($('#noofquestionsfor_'+selectedexperts).val());
                            }else{
                                emptyinput ++;
                            }
                        }
                    }
                });
                if (emptyinput == 0) {

                    var savebtn = document.querySelector('div.modal-footer>button[data-action="save"]');
                    console.log(sum);
                    var mform = document.querySelector('.mform');
                    const inputs = mform.querySelectorAll('input');
                    if (!isNaN(sum)) {
                        if (sum <= allowed_questions) {
                            let formdata = $('form').serializeArray();
                            var data = JSON.stringify(formdata);
                            $.ajax({
                                url: cfg.wwwroot+'/local/questionbank/process.php',
                                type: 'POST',
                                data: {jsonformdata: data},
                                success:function(response){
                                    var resp = JSON.parse(response);
                                    console.log(resp);
                                    if (resp.status == true) {
                                        window.location.reload();
                                    }else{
                                        HomePage.confirmbox(getString('somethingwentwrong', 'local_questionbank'));
                                    }
                                }
                            });
                        }else{
                            inputs.forEach(input => {
                                input.classList.add("is-invalid");
                            });
                            savebtn.disabled = true;
                            HomePage.confirmbox(getString('cannotaddmorequestions', 'local_questionbank', allowed_questions));
                        }
                    }else{
                        inputs.forEach(input => {
                            input.classList.add("is-invalid");
                        });
                        savebtn.disabled = true;
                        HomePage.confirmbox(getString('invalidvalue', 'local_questionbank'));
                    }
                }else{
                    HomePage.confirmbox(getString('questionnum_cannotbenull', 'local_questionbank'));
                }
            });
            var savebtn = document.querySelector('div.modal-footer>button[data-action="save"]');
            console.log(savebtn);
            savebtn.disabled = true;
        }
        // Bulk Question Status Update
        let bulkchangestatus  = e.target.closest(Selectors.actions.bulkchangestatus);
        let questionselected = [];
        if (bulkchangestatus) {
            bulkchangestatus.disabled = true;
            const selectedquestions = document.querySelectorAll('input[type="checkbox"]');
            selectedquestions.forEach(function(checkbox) {
                if (checkbox.checked) {
                    let questionid = parseInt(checkbox.value)
                    if (questionid) {
                        questionselected.push(questionid);
                    }
                }
            });
            let params = {};
            // console.log (questionselected);
            if (questionselected.length !== 0) {
                status = $('#bulkquestion_statusupdate').val();
                if (status == '') {
                    bulkchangestatus.disabled = false;
                    HomePage.confirmbox(getString('no_status_selected', 'local_questionbank'));
                }else{
                    bulkchangestatus.disabled = true;
                    params.questionids = questionselected.join(',');
                    params.wid = bulkchangestatus.getAttribute('data-wid');
                    params.status = status;
                    // var params
                    var promise = Ajax.call([{
                                            methodname: 'local_bulk_update_question_status',
                                            args: params,
                                        }]);
                    promise[0].done(function(resp) {
                        window.location.reload();
                    }).fail(function() {
                        bulkchangestatus.disabled = false;
                        // do something with the exception
                         //console.log('exception');
                    });
                }
            }else{
                bulkchangestatus.disabled = false;
                HomePage.confirmbox(getString('noquestion_selected', 'local_questionbank'));
            }
        }
        let assignopicstocourse  = e.target.closest(Selectors.actions.assignopicstocourse);
        if (assignopicstocourse) {
            e.preventDefault();
            const title = getString('assignopicstocourse', 'local_questionbank', assignopicstocourse.getAttribute('data-name'));
            const form = new ModalForm({
                formClass: 'local_questionbank\\form\\topicsform',
                args: {questionbankid: assignopicstocourse.getAttribute('data-id')},
                modalConfig: {title},
                returnFocus: assignopicstocourse,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }
        let viewtopics  = e.target.closest(Selectors.actions.viewtopics);
        if (viewtopics) {
            e.preventDefault();
            const questionid = viewtopics.getAttribute('data-id');
            const workshopid = viewtopics.getAttribute('data-workshop');
            const questionname = viewtopics.getAttribute('data-name');
            var params = {};
            params.workshopid = workshopid;
            params.questionid = questionid;
            params.questionname = questionname;
            
            var promise = Ajax.call([{
                methodname: 'local_questionbank_displaytopics',
                args: params
            }]);
            promise[0].done(function(resp) {
                ModalFactory.create({
                     title:  getString('targettopic', 'local_questionbank', viewtopics.getAttribute('data-name')),
                    type: ModalFactory.types.DEFAULT,
                    body: resp.options,
                }).done(function(modal) {
                    this.modal = modal;
                    modal.show();
                }.bind(this));
            }).fail(function() {
                // do something with the exception
                 //console.log('exception');
            });
            //const title = getString('targettopic', 'local_questionbank', viewtopics.getAttribute('data-name'));
          
        }

        let viewexperts = e.target.closest(Selectors.actions.viewexperts);
        if (viewexperts) {
            const workshopid = viewexperts.getAttribute('data-id');
            const availableseats = viewexperts.getAttribute('data-seats');
            const workshopname = viewexperts.getAttribute('data-name');
            const qbstatus = viewexperts.getAttribute('data-qbstatus');
            var options = {};
            options.workshopid = workshopid;
            options.availableseats = availableseats;
            options.qbstatus = qbstatus;
            var trigger = $(Selectors.actions.viewexperts);
            ModalFactory.create({
                title: getString('experts', 'local_questionbank'),
                body: Templates.render('local_questionbank/viewexperts',options)
            }, trigger)
            .done(function(modal) {
                modal.show();
                modal.setLarge(true);
                modal.getRoot().on(ModalEvents.hidden, function() {
                    modal.destroy();
                }.bind(this));
            });
        }
        let mapcompetencies = e.target.closest(Selectors.actions.mapcompetencies);
        if (mapcompetencies) {
            e.preventDefault();
            const title = getString('mapcompetencies', 'local_trainingprogram');
            const form = new ModalForm({
                formClass: 'local_questionbank\\form\\competencieslistform',
                args: {id: mapcompetencies.getAttribute('data-id')},
                modalConfig: {title},
                returnFocus: mapcompetencies,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }
        let viewcompetencies = e.target.closest(Selectors.actions.viewcompetencies);
        if (viewcompetencies) {
           const workshopid = viewcompetencies.getAttribute('data-id');
            var options = {};
            options.workshopid = workshopid;
            var trigger = $(Selectors.actions.viewcompetencies);
            ModalFactory.create({
                title: getString('viewcompetencies', 'local_questionbank'),
                body: Templates.render('local_questionbank/competencies_display',options)
            }, trigger)
            .done(function(modal) {
                modal.setLarge();
                modal.show();
                modal.getRoot().on(ModalEvents.hidden, function() {
                modal.destroy();
                }.bind(this));
            });
        }
        let deletecompetency = e.target.closest(Selectors.actions.deletecompetency);
        if (deletecompetency) {
            const competencyid = deletecompetency.getAttribute('data-id');
            const wid = deletecompetency.getAttribute('data-wid');
            const competencyname = deletecompetency.getAttribute('data-name');
            ModalFactory.create({
                title: getString('unassigncompetency', 'local_questionbank'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('deletecompetencyconfirm', 'local_questionbank',competencyname)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('unassign', 'local_questionbank'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.competencyid = competencyid;
                    params.wid = wid;
                    params.competencyname = competencyname;
                    var promise = Ajax.call([{
                        methodname: 'local_questionbank_deletecompetency',
                        args: params
                    }]);
                    promise[0].done(function() {
                        window.location.reload(true);
                    }).fail(function() {
                        //console.log('exception');
                    });
                }.bind(this));
                modal.show();
            }.bind(this));
        }
        let deletecourse = e.target.closest(Selectors.actions.deletecourse);
        if (deletecourse) {
            const courseid = deletecourse.getAttribute('data-id');
            const wid = deletecourse.getAttribute('data-wid');
            const coursename = deletecourse.getAttribute('data-name');

            ModalFactory.create({
                title: getString('unassigncourse', 'local_questionbank'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('deletecourseconfirm', 'local_questionbank',coursename)
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('unassign', 'local_questionbank'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    var params = {};
                    params.courseid = courseid;
                    params.wid = wid;
                    params.coursename = coursename;
                    var promise = Ajax.call([{
                        methodname: 'local_questionbank_deletecourse',
                        args: params
                    }]);
                    promise[0].done(function() {
                        window.location.reload(true);
                    }).fail(function() {
                        //console.log('exception');
                    });
                }.bind(this));
                modal.show();
            }.bind(this));
        }
         let changestatus = e.target.closest(Selectors.actions.changestatus);

        if (changestatus) {

            e.preventDefault();
            const questionid = changestatus.getAttribute('data-id');
            const status  = $('#status'+questionid).val(); 
            const hidestatus  = changestatus.getAttribute('data-hidestatus');    
            const statusText = $('#status'+questionid+' option:selected').text();  
            // const qstatus = changestatus.getAttribute('data-status');
            const workshopid = changestatus.getAttribute('data-wid');
            var params = {};
                    params.questionid = questionid;
                    params.workshopid = workshopid;
                    params.status = status;
                    var promise = Ajax.call([{
                        methodname: 'local_questionbank_changestatus',
                        args: params
                    }]);
                    promise[0].done(function(res) {
                        $('.questioncreation_page #status_span'+questionid).text(statusText);
                        if(statusText == 'Publish' && hidestatus !=1){
                           $('.questioncreation_page #status_change'+questionid).hide();
                        }
                        if(statusText == 'Publish'){
                            $('.questioncreation_page #assignreviewerto'+questionid).hide();
                        }
                        HomePage.confirmbox(getString('questionstatus', 'local_questionbank', statusText));

                    }).fail(function() {
                        //console.log('exception');
                    });
            // const title = getString('assignopicstocourse', 'local_questionbank');
            // const form = new ModalForm({
            //     formClass: 'local_questionbank\\form\\createquestion',
            //     args: {questionbankid: changestatus.getAttribute('data-wid'),questionid: changestatus.getAttribute('data-qid')},
            //     modalConfig: {title},
            //     returnFocus: changestatus,
            // });
            // form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            // form.show();
        }
        let assignreviewer = e.target.closest(Selectors.actions.assignreviewer);
        if (assignreviewer) {

            e.preventDefault();
            const questionid = assignreviewer.getAttribute('data-id');
  
            const reviewerid  = $('#reviewer'+questionid).val(); 
            const dropdown = document.getElementById('reviewer'+questionid);
            const reviewername = dropdown.options[dropdown.selectedIndex].text;
            const qstatus = assignreviewer.getAttribute('data-status');
            const workshopid = assignreviewer.getAttribute('data-wid');
            var params = {};
                params.reviewerid = reviewerid;
                params.questionid = questionid;
                params.workshopid = workshopid;
                var promise = Ajax.call([{
                    methodname: 'local_questionbank_assignreviewer',
                    args: params
                }]);
                promise[0].done(function(response) {
                    var dropdown = document.querySelector('#reviewer'+params.questionid);
                    // Remove all existing options
                    dropdown.options = [];
                    // Append the new option to the dropdown
                    dropdown.innerHTML = response.options;
                    $('.questioncreation_page #reviewer_span'+questionid).text(reviewername);
                    HomePage.confirmbox(getString('reviewerinfo', 'local_questionbank', reviewername));
                    // window.location.reload(true);
                }).fail(function() {
                    //console.log('exception');
                });
        }
        let movetoprodquestionbank  = e.target.closest(Selectors.actions.movetoprodquestionbank);
        if (movetoprodquestionbank) {
            e.preventDefault();
            const title = getString('moveqcategory', 'local_questionbank');
            const form = new ModalForm({
                formClass: 'local_questionbank\\form\\questioncategoryform',
                args: {workshopid: movetoprodquestionbank.getAttribute('data-id'),
                       qcategoryid: movetoprodquestionbank.getAttribute('data-qcat')},
                modalConfig: {title},
                returnFocus: movetoprodquestionbank,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }
        let qtypeform = document.getElementById('chooserform');
        if (qtypeform) {
            qtypeform.action = M.cfg.wwwroot+"/local/questionbank/editquestion.php";
        }
    });
    /**
     * This set of code is written to prevent experts to change the settings of any question.
     * 
     */
    // ************** List of elements to be hidden from an expert ********************
    
    let currentuserrole = document.querySelector('#currentuserrole');
    let id_updatecategory = document.querySelector('#id_updatecategory');
    let fitem_id_defaultmark = document.querySelector('#fitem_id_defaultmark');
    let fitem_id_generalfeedback = document.querySelector('#fitem_id_generalfeedback');
    let fitem_id_idnumber = document.querySelector('#fitem_id_idnumber');
    let categoryfield = document.querySelector('#fitem_id_category');
    let fitem_id_usecase = document.querySelector('#fitem_id_usecase');
    let fitem_id_single = document.querySelector('#fitem_id_single');
    let fitem_id_answernumbering = document.querySelector('#fitem_id_answernumbering');
    let fitem_id_showstandardinstruction = document.querySelector('#fitem_id_showstandardinstruction');
    var checkbox = document.querySelector("#id_generalheadercontainer > div:nth-child(9) > div.col-md-9.checkbox");
    let fgroup_id_currentgrp = document.querySelector('#fgroup_id_currentgrp');
    let fitem_id_categorymoveto = document.querySelector('#fitem_id_categorymoveto');
    
    if (currentuserrole.innerText == 'expert') {
        if (categoryfield) {
            categoryfield.style = 'display:none';
        }
        if (id_updatecategory) {
            id_updatecategory.style = 'display:none';
        }
        if (fitem_id_defaultmark) {
            fitem_id_defaultmark.style = 'display:none';
        }
        if (fitem_id_generalfeedback) {
            fitem_id_generalfeedback.style = 'display:none';
        }
        if (fitem_id_idnumber) {
            fitem_id_idnumber.style = 'display:none';
        }
        if (fitem_id_usecase) {
            fitem_id_usecase.style = 'display:none';
        }
        if (fitem_id_single) {
            fitem_id_single.style = 'display:none';
        }
        if (fitem_id_answernumbering) {
            fitem_id_answernumbering.style = 'display:none';
        }
        if (fitem_id_showstandardinstruction) {
            fitem_id_showstandardinstruction.style = 'display:none';
        }
        if (fitem_id_usecase) {
            fitem_id_usecase.style = 'display:none';
        }
        if (checkbox) {
           checkbox.style.display = "none";
        }
        if (fgroup_id_currentgrp) {
            fgroup_id_currentgrp.style = 'display:none';
        }
        if (fitem_id_categorymoveto) {
            fitem_id_categorymoveto.style = 'display:none';
        }
        let settings = document.querySelectorAll('fieldset');
        settings.forEach(function(fieldset){

            if(fieldset.id !== 'id_generalheader'){
                if ( fieldset.id!== 'id_answerhdr') {
                    var classes = fieldset.attributes['class'].nodeValue;
                    console.log(classes);
                    var classes_array = classes.split(" ");
                    if(classes_array[0] == 'clearfix'){
                        fieldset.style = 'display:none';
                    }
                }
            }
        }); 
    }
};
