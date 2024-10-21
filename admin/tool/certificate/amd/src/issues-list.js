// This file is part of the tool_certificate plugin for Moodle - http://moodle.org/
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

/**
 * AMD module used when viewing the list of issued certificates
 *
 * @module     tool_certificate/issues-list
 * @copyright  2019 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery',
        'tool_certificate/modal_form',
        'core/notification',
        'core/str',
        'core/ajax',
        'core/toast'],
function($,
         ModalForm,
         Notification,
         Str,
         Ajax,
         Toast) {

    const SELECTORS = {
        ADDISSUE: "[data-element='addbutton']",
        REGENERATEFILE: "[data-action='regenerate']",
        REVOKEISSUE: "[data-action='revoke']",
        CHECKALL: '[data-action="checkall"]',
        ISSUEFORSELECTEDUSERS: '[data-action="issueforselectedusers"]',
        ISSUEFORALLUSERS: '[data-action="issueforallusers"]',
        UNCHECKALL: '[data-action="uncheckall"]',
    };
    // Define the function to be executed
    function makeModalSmall() {
        var a = document.querySelector('.modal-lg');
        if (a) {
            a.classList.remove('modal-lg');
            return true;
        }
    }
    /**
     * Add issue dialogue
     * @param {Event} e
     */
    var addIssue = function(e) {
        e.preventDefault();
        var page = '';
        page = $(e.currentTarget).attr('data-page');
        var examid = $(e.currentTarget).attr('data-examid');
        var userid = $(e.currentTarget).attr('data-userid');
        var element = $(e.currentTarget).attr('data-element');
        
        var modal = new ModalForm({
            formClass: 'tool_certificate\\form\\certificate_issues',
            args: {
                tid: $(e.currentTarget).attr('data-tid'),
                page : page,
                examid: examid,
                userid: userid,
                element: element
            },
            modalConfig: {
                title: Str.get_string('issuecertificates', 'tool_certificate'), 
                scrollable: false
            },
            saveButtonText: Str.get_string('save'),
            triggerElement: $(e.currentTarget),
        });
        

        if (page) {
            // Call setInterval to execute the function every 2 seconds
            var intervalId = setInterval(makeModalSmall, 200);

            // To stop the interval execution after a certain time (e.g., 10 seconds)
            setTimeout(function() {
              clearInterval(intervalId);
              console.log('Interval stopped!');
            }, 10000);
        }
        modal.onSubmitSuccess = function(data) {
            data = parseInt(data, 10);
            if (data) {
                Str.get_strings([
                    {key: 'oneissuewascreated', component: 'tool_certificate'},
                    {key: 'aissueswerecreated', component: 'tool_certificate', param: data}
                ]).done(function(s) {
                    var str = data > 1 ? s[1] : s[0];
                    Toast.add(str);
                });
                window.location.reload();
            } else {
                Str.get_string('noissueswerecreated', 'tool_certificate')
                    .done(function(s) {
                        Toast.add(s);
                    });
            }
        };

    };

    /**
     * Revoke issue
     * @param {Event} e
     */
    var revokeIssue = function(e) {
        e.preventDefault();
        e.stopPropagation();
        Str.get_strings([
            {key: 'confirm', component: 'moodle'},
            {key: 'revokecertificateconfirm', component: 'tool_certificate'},
            {key: 'revoke', component: 'tool_certificate'},
            {key: 'cancel', component: 'moodle'}
        ]).done(function(s) {
            Notification.confirm(s[0], s[1], s[2], s[3], function() {
                var promises = Ajax.call([
                    {methodname: 'tool_certificate_revoke_issue',
                        args: {id: $(e.currentTarget).attr('data-id')}}
                ]);
                promises[0].done(function() {
                    window.location.reload();
                }).fail(Notification.exception);
            });
        }).fail(Notification.exception);
    };

    /**
     * Revoke issue
     * @param {Event} e
     */
    var regenerateIssueFile = function(e) {
        e.preventDefault();
        e.stopPropagation();
        Str.get_strings([
            {key: 'confirm', component: 'moodle'},
            {key: 'regeneratefileconfirm', component: 'tool_certificate'},
            {key: 'regenerate', component: 'tool_certificate'},
            {key: 'cancel', component: 'moodle'}
        ]).done(function(s) {
            Notification.confirm(s[0], s[1], s[2], s[3], function() {
                var promises = Ajax.call([
                    {methodname: 'tool_certificate_regenerate_issue_file',
                        args: {id: $(e.currentTarget).attr('data-id')}}
                ]);
                promises[0].done(function() {
                    window.location.reload();
                }).fail(Notification.exception);
            });
        }).fail(Notification.exception);
    };
    /**
     * CHECK ALL USERS
     * 
     */
    var checkall = function (e) {
        var allcheckboxes = document.querySelectorAll('.user-checkbox');
        var arrexamids = [];
        allcheckboxes.forEach(function(usercheckbox){
            usercheckbox.checked = true;
        });
        $(e.currentTarget).attr('data-action', 'uncheckall')
    }
    /**
     * UN-CHECK ALL USERS
     * 
     */
    var uncheckall = function (e) {
        var allcheckboxes = document.querySelectorAll('.user-checkbox');
        var arrexamids = [];
        allcheckboxes.forEach(function(usercheckbox){
            usercheckbox.checked = false;
        });
        $(e.currentTarget).attr('data-action', 'checkall')
    }
    var issueforselectedusers = function(e) {
        e.preventDefault();
        var allcheckboxes = document.querySelectorAll('.user-checkbox');
        var arrexamids = [];
        var checkedids = [];
        allcheckboxes.forEach(function(usercheckbox){
            if(usercheckbox.checked){
                checkedids.push(usercheckbox.getAttribute('data-userid'));
                arrexamids.push(usercheckbox.getAttribute('data-examid'));
            }
        });
        if (checkedids.length < 1) {
            var title = Str.get_string('alert', 'tool_certificate');
            var message = Str.get_string('usernotselected', 'tool_certificate');
            var ok = Str.get_string('ok');
            Notification.alert(title, message, ok);
        }else{
            var page = $(e.currentTarget).attr('data-page');
            var userids = checkedids.join(',');
            var examids = arrexamids.join(',');
            var modal = new ModalForm({
                formClass: 'tool_certificate\\form\\certificate_issues',
                args: {
                    tid: $(e.currentTarget).attr('data-tid'),
                    page : $(e.currentTarget).attr('data-page'),
                    examid: examids,
                    userid: userids,
                    element: $(e.currentTarget).attr('data-action')
                },
                modalConfig: {
                    title: Str.get_string('issuecertificates', 'tool_certificate'), 
                    scrollable: false
                },
                saveButtonText: Str.get_string('sure','tool_certificate'),
                triggerElement: $(e.currentTarget),
            });
            modal.onSubmitSuccess = function(data) {
                data = parseInt(data, 10);
                if (data) {
                    Str.get_strings([
                        {key: 'oneissuewascreated', component: 'tool_certificate'},
                        {key: 'aissueswerecreated', component: 'tool_certificate', param: data}
                    ]).done(function(s) {
                        var str = data > 1 ? s[1] : s[0];
                        Toast.add(str);
                    });
                    window.location.reload();
                } else {
                    Str.get_string('noissueswerecreated', 'tool_certificate')
                        .done(function(s) {
                            Toast.add(s);
                        });
                }
            };
            if (page) {
                // Call setInterval to execute the function every 2 seconds
                var intervalId = setInterval(makeModalSmall, 200);

                // To stop the interval execution after a certain time (e.g., 10 seconds)
                setTimeout(function() {
                  clearInterval(intervalId);
                  console.log('Interval stopped!');
                }, 10000);
            }
        }
    }
    var issueforallusers = function(e) {
        const tid = $(e.currentTarget).attr('data-tid');
        const element = $(e.currentTarget).attr('data-action');
        var page = '';
        page = 'exam_certificate';
        var promises = Ajax.call([{
            methodname: 'tool_certificate_issue_certificate_for_all',
            args: {status:true, tid: tid, element: element}
        }]);
        promises[0].done(function(resp) {
            console.log(resp);
            if (resp.userids) {
                var modal = new ModalForm({
                    formClass: 'tool_certificate\\form\\certificate_issues',
                    args: {
                        tid: tid,
                        page : 'exam_certificate',
                        examid: resp.examids,
                        userid: resp.userids,
                        element: resp.element
                    },
                    modalConfig: {
                        title: Str.get_string('issuecertificates', 'tool_certificate'), 
                        scrollable: false
                    },
                    saveButtonText: Str.get_string('sure','tool_certificate'),
                    triggerElement: $(e.currentTarget),
                });
                modal.onSubmitSuccess = function(data) {
                    data = parseInt(data, 10);
                    if (data) {
                        Str.get_strings([
                            {key: 'oneissuewascreated', component: 'tool_certificate'},
                            {key: 'aissueswerecreated', component: 'tool_certificate', param: data}
                        ]).done(function(s) {
                            var str = data > 1 ? s[1] : s[0];
                            Toast.add(str);
                        });
                        window.location.reload();
                    } else {
                        Str.get_string('noissueswerecreated', 'tool_certificate')
                            .done(function(s) {
                                Toast.add(s);
                            });
                    }
                };
                if (page) {
                    // Call setInterval to execute the function every 2 seconds
                    var intervalId = setInterval(makeModalSmall, 200);

                    // To stop the interval execution after a certain time (e.g., 10 seconds)
                    setTimeout(function() {
                      clearInterval(intervalId);
                      console.log('Interval stopped!');
                    }, 10000);
                }
            }else{
                var title = Str.get_string('alert', 'tool_certificate');
                var message = Str.get_string('nousercompletionfound', 'tool_certificate');
                var ok = Str.get_string('ok');
                Notification.alert(title, message, ok);
            }
        }).fail(Notification.exception);
    }
    return {
        /**
         * Init page
         */
        init: function() {
            // Add button is not inside a tab, so we can't use Tab.addButtonOnClick .
            $('body')
                .on('click', SELECTORS.ADDISSUE, addIssue)
                .on('click', SELECTORS.REVOKEISSUE, revokeIssue)
                .on('click', SELECTORS.REGENERATEFILE, regenerateIssueFile)
                .on('click', SELECTORS.CHECKALL, checkall)
                .on('click', SELECTORS.ISSUEFORSELECTEDUSERS, issueforselectedusers)
                .on('click', SELECTORS.ISSUEFORALLUSERS, issueforallusers)
                .on('click', SELECTORS.UNCHECKALL, uncheckall);
        }
    };
});
