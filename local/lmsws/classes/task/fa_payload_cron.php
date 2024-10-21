<?php 

namespace local_lmsws\task;

//Sending Daily report in the form of excel to configured People
class fa_payload_cron extends \core\task\scheduled_task {

	/**
	 * Get a descriptive name for this task (shown to admins).
	 *
	 * @return string
	 */	
	public function get_name() {
		return get_string('fa_payload_cron', 'local_lmsws');
	}

	public function execute() {
		global $CFG;
		mtrace('Cron to handle failed data in FA');
		require_once 'fa_payload.php';
	}
}