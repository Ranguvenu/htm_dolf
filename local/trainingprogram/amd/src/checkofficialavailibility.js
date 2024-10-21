
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


export const CheckForOfficialAvailableSlot = (element) => {
	var err = getString('error');
	var msg = getString('dateunchecked', 'local_trainingprogram');
	var ok = getString('ok');
	var startdate = document.querySelector('[name="startdate[enabled]"]');
	var enddate = document.querySelector('[name="enddate[enabled]"]');
	var savebtn = document.querySelector('div.modal-footer>button[data-action="save"]');
	
	// Selected User
	var ele = document.getElementById(element);
	var officialId = ele.value;
	if (!isNaN(officialId)) {
		savebtn.disabled = true;
		var officialName = ele.options[ele.selectedIndex].text;;
		var trainingid = document.querySelector('[name="trainingid"]').value;
		/**
		 * Start date
		 * 
		 */
		// Day
		var startday = document.querySelector('[name="startdate[day]"]').value;
		var startmonth = document.querySelector('[name="startdate[month]"]').value;
		var startyear = document.querySelector('[name="startdate[year]"]').value;
		/**
		 * Start time
		 * 
		 */
		var starttimehour = document.querySelector('[name="starttime[hours]"]').value;
		var starttimeminute = document.querySelector('[name="starttime[minutes]"]').value;
		/**
		 * END date
		 * 
		 */
		var endday = document.querySelector('[name="enddate[day]"]').value;
		var endmonth = document.querySelector('[name="enddate[month]"]').value;
		var endyear = document.querySelector('[name="enddate[year]"]').value;
		/**
		 * Start time
		 * 
		 */
		var endtimehour = document.querySelector('[name="endtime[hours]"]').value;
		var endtimeminute = document.querySelector('[name="endtime[minutes]"]').value;
		/**
		 * Meeting type
		 * 
		 */
		var meetingtype = document.querySelector('[name="meetingtype"]');
		var selectedmeeting = meetingtype.options[meetingtype.selectedIndex].text;

		var params = {};
		params.startday = startday;
		params.startmonth = startmonth;
		params.startyear = startyear;
		params.starttimehrs = starttimehour;
		params.starttimemin = starttimeminute;
		params.endday = endday;
		params.endmonth = endmonth;
		params.endyear = endyear;
		params.endtimehurs = endtimehour;
		params.endtimemin = endtimeminute;
		params.officialid = officialId;
		params.meetingtype = selectedmeeting;
		params.trainingid = trainingid;
		params.officialname = officialName;
		console.log("Checking if "+ params.officialname + "can be assigned to this schedule");
	    var promise = Ajax.call([{
	        methodname: 'local_trainingprogram_checkofficial_availibility',
	        args: params
	    }]);
	    promise[0].done(function(resp) {
	        // console.log(resp);
	        if (resp.status == false) {
				console.log("This Official cannot be assigned to this schedule..!");
	        	var title = getString('cannotassign', 'local_trainingprogram');
	        	var msg = getString('cannotassignofficial', 'local_trainingprogram', resp.official_name);
	        	Notification.alert(title, msg, ok);
	        	savebtn.disabled = true;
	        	var ele = document.querySelector('#training-officials');
	        	ele.selectedIndex = -1;
	        }else{
				console.log("Yes this official can be assgned to this schedule.");
	        	savebtn.disabled = false;
	        }
	    }).fail(function() {
	        // do something with the exception
	        var ele = document.querySelector('#training-officials');
	    	ele.selectedIndex = -1;
	         console.log('Something went wrong..!');
	    });
	}
}