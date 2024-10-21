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


import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import Ajax from 'core/ajax';
import cfg from 'core/config';
import {get_string as getString} from 'core/str';
import Notification from 'core/notification';
import $ from 'jquery';
const Selectors = {
	actions: {
		courseSettings: '[data-key="editsettings"]',
		coursereuse: '[data-key="coursereuse"]',
		SECTIONACTIONMENU: '[class="section_action_menu ml-auto"]',
		ModuleActionOptions: '[class="cm_action_menu actions"]',
		addSection: '[data-action="addSection"]',
		hide: '[data-action="hide"]',
		deleteSection: '[data-action="deleteSection"]',
		showmodules: '[data-action="show"]',
		assignrolesonmoduleoption: '[data-action="assignroles"]',
		approve: '[data-action="approve"]',
		reject: '[data-action="reject"]',
	}
}

export const init = () => {
	const body = document.getElementsByTagName('body');
	const settings = document.querySelector(Selectors.actions.courseSettings);
	const coursereuse = document.querySelector(Selectors.actions.coursereuse);
	const sectionActionMenue = document.querySelectorAll(Selectors.actions.SECTIONACTIONMENU);
	const moduleActionOptions = document.querySelectorAll(Selectors.actions.ModuleActionOptions);
	const addSection = document.querySelector(Selectors.actions.addSection);
	const hide = document.querySelectorAll(Selectors.actions.hide);
	const deleteSection = document.querySelectorAll(Selectors.actions.deleteSection);
	const showmodules = document.querySelectorAll(Selectors.actions.showmodules);
	const assignrolesonmoduleoption = document.querySelectorAll(Selectors.actions.assignrolesonmoduleoption);
	

	function hide_edit_option() {
		$('.fa-pencil').css('display', 'none');
		var editoptions = document.querySelectorAll('.fa-pencil');
		for (var i = 0; i < editoptions.length; i++) {
			// editoptions[i].style.display = 'none';
			editoptions[i].remove();
		}
		if (settings !== undefined) {
			settings.remove();
		}
		if (coursereuse !== undefined) {
			coursereuse.remove();
		}
		for (var i = 0; i < hide.length; i++) {
			hide[i].remove();
		}
		for (var i = 0; i < deleteSection.length; i++) {
			deleteSection[i].remove();
		}
		for (var i = 0; i < showmodules.length; i++) {
			showmodules[i].remove();
		}
		for (var i = 0; i < assignrolesonmoduleoption.length; i++) {
			assignrolesonmoduleoption[i].remove();
		}
	}
	

	var id = body[0].id;
	var idarr = id.split("-");
	let iscoursePage = idarr[0] + '-' + idarr[1] + '-' + idarr[2];
	if (iscoursePage == 'page-course-view') {
		const currentUser = document.querySelector('.user_role');
		const role = currentUser.getAttribute('data-role');
		// console.log('Current user role: ' + role);
		if (role == 'editingtrainer') {
			hide_edit_option();
		}
	}
	function approve_or_reject_activity(params){
		var confrmstring;
		if (params.status == 'approve') {
			confrmstring = getString('confirmapprove', 'local_trainingprogram');
		}else if(params.status == 'reject'){
			confrmstring = getString('confirmreject', 'local_trainingprogram');
		}
		if (confrmstring) {
			ModalFactory.create({
                title: getString('confirm', 'local_trainingprogram'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: confrmstring
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('yes'));
                modal.getRoot().on(ModalEvents.save, function(e) {
					promise = Ajax.call([{
						methodname: 'local_trainingprogram_activity_approoved',
		                args: params
					}]);
					promise[0].done(function(resp) {
						if (resp.status == true) {
					        window.location.reload();
						}else{
							var error = getString('error');
		                    var message = getString('approvefailed', 'local_trainingprogram');
		                    var ok = getString('ok');
							Notification.alert(error, message, ok);
						}
				    }).fail(function(exception) {
				        console.log(exception);
				    });
                }.bind(this));
                modal.show();
            }.bind(this));
		}
	}
	document.addEventListener('click', function(e){
		if (addSection) {
			const currentUser = document.querySelector('.user_role');
			const role = currentUser.getAttribute('data-role');
			if (role == 'editingtrainer') {
				setInterval(hide_edit_option(), 1000);
			}
		}
		var promise =[];
		var params = {};
		// If activity is approved
		const approve = e.target.closest(Selectors.actions.approve);
		// If activity is Rejected
		const reject = e.target.closest(Selectors.actions.reject);
		if (approve) {
			let cmid = approve.getAttribute('data-id');
			let status = approve.getAttribute('data-action');
			params.cmid  = cmid;
			params.status  = status;
            approve_or_reject_activity(params);
		}
		if (reject) {
			let cmid = reject.getAttribute('data-id');
			let status = reject.getAttribute('data-action');
			params.cmid  = cmid;
			params.status  = status;
            approve_or_reject_activity(params);
		}
	});
}