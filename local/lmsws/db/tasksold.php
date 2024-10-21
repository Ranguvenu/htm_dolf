<?php
$tasks = array(
		array(
				'classname' => 'local_lmsws\task\camu_payload_cron',
				'blocking' => 0,
				'minute' => '00',
				'hour' => '8',
				'day' => '*',
				'dayofweek' => '5',
				'month' => '*'
		),
		array(
				'classname' => 'local_lmsws\task\camu_payload_notification_cron',
				'blocking' => 0,
				'minute' => '00',
				'hour' => '8',
				'day' => '*',
				'dayofweek' => '5',
				'month' => '*'
		)
);