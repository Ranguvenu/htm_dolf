<?php
defined('MOODLE_INTERNAL') || die();

/* List of handlers */

$tasks = array(
	array(
		'classname' => 'tool_product\task\sadadpayment',
		'blocking' => 0,
		'minute' => '*',
		'hour' => '*/1',
		'day' => '*',
		'dayofweek' => '*',
		'month' => '*',
	)
);
