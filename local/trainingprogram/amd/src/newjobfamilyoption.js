import Ajax from 'core/ajax';
import cfg from 'core/config';
import $ from 'jquery';

const Selectors = {
	actions: {
		addOrRemoveJobfamilyOption: '[role="option"]',
		save: '[data-action="save"]',
		checkbox: '[class="jobfamily_options"]',
	},
}; 

require(['jquery'], function($) {
    $(document).ready(function(){
		
    });
});




export const init = () => {
	document.addEventListener('click', function(e){
		/*var notappliedtargetgroup = $('input[id^="id_notappliedtargetgroup_"]:checked').map(function() {
			return this.value;
		}).get();
		console.log("notappliedtargetgroup " + notappliedtargetgroup)
		if(notappliedtargetgroup!='') {
			$('.newjobfamilyoption').hide();
			$("#alltargetgroup").hide();
			$('#fitem_jobfamily_dropdown').hide();
		} else {
			$('.newjobfamilyoption').show();
			$("#alltargetgroup").show();
			$('#fitem_jobfamily_dropdown').show();
		}*/
		var fitem_jobfamily_dropdown = document.getElementById("jobfamily_dropdown");
	
		var notappliedtargetgroup = $('input[name=notappliedtargetgroup]:checked').map(function() {
			return this.value;
		}).get();

		var alltargetgroup = $('input[name=alltargetgroup]:checked').map(function() {
			return this.value;
		}).get();
		if(alltargetgroup!='' || notappliedtargetgroup!='') {
			let checkbox = document.querySelectorAll('.newjobfamily_options');
			if(checkbox.length > 0){
				for (var i = 0; i < checkbox.length; i++) {
						checkbox[i].disabled = true;
				}
			}
			$('input[id^="newjobfamilyoptions_"]').not(this).prop('checked', false);
			
		}
		else if(alltargetgroup =='' || notappliedtargetgroup =='') {
			let checkbox = document.querySelectorAll('.newjobfamily_options');
			if(checkbox.length > 0){
				for (var i = 0; i < checkbox.length; i++) {
						checkbox[i].disabled = false;
				}
			}
			
		}
		else {
			
			$('.newjobfamilyoption').show();
		}

		var jobfamilycheckbx = $('input[id^="newjobfamilyoptions_"]:checked').map(function() {
			return this.value;
		}).get();
		
		/*var notappliedtargetgroup = $('input[name=notappliedtargetgroup]:checked').map(function() {
			document.getElementById("newjobfamilyoptions_").disabled = true;
		}).get();*/
		// if(jobfamilycheckbx!='') {
		// 	$('.notapplied').hide();
		// 	// $("#alltargetgroup").hide();
		// } else {
		// 	$('.notapplied').show();
		// 	// $("#alltargetgroup").show();
		// }

		
       

		var sector_ids = [];
		let tagName = e.target.tagName;
		let is_removed = e.target.closest(Selectors.actions.addOrRemoveJobfamilyOption);
		if (tagName == 'SPAN') {
			if (is_removed != null) {
				let removed_option = is_removed.getAttribute('data-value');
				$('.newjobfamilyoptions_'+removed_option).remove();
			}
		}
       

		// var JobfamilyOption;
		let checkbox = document.querySelectorAll('.newjobfamily_options');
		var jobfamilynew_option = document.querySelector('.new_jobfamily_option');
		if (checkbox) {
			for (var i = 0; i < checkbox.length; i++) {
				sector_ids.push(parseInt(checkbox[i].value));
				if(!checkbox[i].checked){
					sector_ids.pop(checkbox[i].value);
					var ids = sector_ids.join(',');
					jobfamilynew_option.value = ids;
				}else{
					var ids = sector_ids.join(',');
					jobfamilynew_option.value = ids;
				}
			}
		}
	});
}