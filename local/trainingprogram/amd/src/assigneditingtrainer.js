
/**
 * @module     local_trainingprogram
 * @copyright  Moodle India
 * @author 	   Ikram Ahmad (ikram.ahmad@moodle.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Ajax from 'core/ajax';
import cfg from 'core/config';
import Notification from 'core/notification';
import Str from 'core/str';
import $ from 'jquery';
const Selectors = {
    actions: {
        addOrRemoveTrainer: '[role="option"]',
        save: '[data-action="save"]',
        checkbox: '[class="editing_trainer"]',
    },
};
export const users = (e) => {
	// var  userid = $("#program_users option:selected").last().val();
	var dropdown = document.getElementById("program_users");
    var selectedValues = [];
	var promise;
  	var selectedTrainer;
  	var is_already_added;
    for (var i = 0; i < dropdown.options.length; i++) {
	    if (dropdown.options[i].selected) {
	    	selectedTrainer = dropdown.options[i].value;
	    	
	    	is_already_added = document.querySelector('.assignedditingtrainer_'+selectedTrainer);
	    	if (!is_already_added) {
	    		promise = Ajax.call([{
					methodname: 'local_trainingprogram_edditingtrainer_confirmation',
					args: {trainer_id: selectedTrainer}
				}]);
				promise[0].done(function(resp) {
			        $('.mform').append(resp.str);
			    }).fail(function(exception) {
			        console.log(exception);
			    });
	    	}
	    }
    }
    document.addEventListener('click', function(e){
	  	var trainer_ids = [];
    	let tagName = e.target.tagName;
    	let is_removed = e.target.closest(Selectors.actions.addOrRemoveTrainer);
    	if (tagName == 'SPAN') {
			if (is_removed != null) {
				let removed_trainer = is_removed.getAttribute('data-value');
				$('.assignedditingtrainer_'+removed_trainer).remove();
			}
		}
		// var edditing_trainer;
    	let checkbox = document.querySelectorAll('.editing_trainer');
    	var editingtrainer = document.querySelector('.editingtrainer');
    	if (checkbox) {
    		// $('.editingtrainer').val('');
	    	for (var i = 0; i < checkbox.length; i++) {
    			trainer_ids.push(parseInt(checkbox[i].value));
	    		if(!checkbox[i].checked){
	    			trainer_ids.pop(checkbox[i].value);
	                var ids = trainer_ids.join(',');
	    			editingtrainer.value = ids;
	    		}else{
	                var ids = trainer_ids.join(',');
                    editingtrainer.value = ids;
	    		}
	    	}
    	}
    });
}
export const orgusers = (e) => {
	var dropdown = document.getElementsByName("oguser");
	var selectedValues = [];
	var promise;
	var selectedTrainer;
	var is_already_added;
	dropdown = dropdown[0];
	for (var i = 0; i < dropdown.options.length; i++) {
	    if (dropdown.options[i].selected) {
	    	selectedTrainer = dropdown.options[i].value;
	    	
	    	is_already_added = document.querySelector('.assignedditingtrainer_'+selectedTrainer);
	    	if (!is_already_added) {
	    		promise = Ajax.call([{
					methodname: 'local_trainingprogram_edditingtrainer_confirmation',
					args: {trainer_id: selectedTrainer}
				}]);
				promise[0].done(function(resp) {
			        $('.mform').append(resp.str);
			    }).fail(function(exception) {
			        console.log(exception);
			    });
	    	}
	    }
    }
    document.addEventListener('click', function(e){
	  	var trainer_ids = [];
    	let tagName = e.target.tagName;
    	let is_removed = e.target.closest(Selectors.actions.addOrRemoveTrainer);
    	if (tagName == 'SPAN') {
			if (is_removed != null) {
				let removed_trainer = is_removed.getAttribute('data-value');
				$('.assignedditingtrainer_'+removed_trainer).remove();
			}
		}
		// var edditing_trainer;
    	let checkbox = document.querySelectorAll('.editing_trainer');
    	var editingtrainer = document.querySelector('.editingtrainer');
    	if (checkbox) {
    		// $('.editingtrainer').val('');
	    	for (var i = 0; i < checkbox.length; i++) {
    			trainer_ids.push(parseInt(checkbox[i].value));
	    		if(!checkbox[i].checked){
	    			trainer_ids.pop(checkbox[i].value);
	                var ids = trainer_ids.join(',');
	    			editingtrainer.value = ids;
	    		}else{
	                var ids = trainer_ids.join(',');
                    editingtrainer.value = ids;
	    		}
	    	}
    	}
    });
}