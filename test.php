<?php
require_once('config.php');

$smsapi = new local_notifications\local\smsapi();
$smsapi->sendsms('Hello from Moodle', '582175991');