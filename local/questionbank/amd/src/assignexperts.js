
/**
 * @module     local_questionbank
 * @copyright  Moodle India
 * @author 	   Ikram Ahmad (ikram.ahmad@moodle.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Ajax from 'core/ajax';
import cfg from 'core/config';
import {get_string as getString} from 'core/str';
import $ from 'jquery';
import Notification from 'core/notification';


const Selectors = {
    actions: {
        addOrRemoveexpert: '[role="option"]',
        fdescription : '[class="fdescription"]',
        
    },
};
export const removeexpert = () => {
	document.addEventListener("click", function(e){
		var isExpertForm = document.getElementsByName('_qf__local_questionbank_form_expertsform');
	    if (isExpertForm.length > 0) {
	        // console.log(isExpertForm);
			let tagName = e.target.tagName;
			let is_removed = e.target.closest(Selectors.actions.addOrRemoveexpert);
			if (tagName == 'SPAN') {
				if (is_removed != null) {
					let removed_expert = is_removed.getAttribute('data-value');
					$('.noofquestionsfor_'+removed_expert).remove();
				}
			}else{
				if (tagName == 'LI') {
					var savebtn = document.querySelector('div.modal-footer>button[data-action="save"]');
					savebtn.disabled = true;
					const fdescription = document.querySelector('.fdescription');
					let selectExpert = e.target.closest(Selectors.actions.addOrRemoveexpert);
					if (selectExpert) {
						let seleceted_expertid = selectExpert.getAttribute('data-value');
						if (seleceted_expertid) {
							$.ajax({
								url: cfg.wwwroot+'/local/questionbank/process.php',
						    	data: {expert_id: seleceted_expertid},
						    	type:"POST",
						    	success: function (names) {
						    		let element = JSON.parse(names);
						    		$('.fdescription').remove();
						    		$('.mform').append(element.allowedquestionfields);
						    		$('.mform').append(fdescription);
						    	}
							});
						}
					}
				}
			}
	    }
	});
}
export const checkvalue = (element) => {
	var sum = parseInt(element.value);
	var err = getString('error');
	console.log(sum);
	var savebtn = document.querySelector('div.modal-footer>button[data-action="save"]');
	if (isNaN(sum)) {
		element.classList.add("is-invalid");
	    Notification.alert(err, getString('questionnum_cannotbenull', 'local_questionbank'), getString('ok'));
	    savebtn.disabled = true;
	}else if (sum < 0) {
	    Notification.alert(err, getString('questionnum_cannotbenegatve', 'local_questionbank'), getString('ok'));
	    savebtn.disabled = true;
	}
	else{
		element.classList.remove("is-invalid");
	    savebtn.disabled = false;
	}
}