<?php 



defined('MOODLE_INTERNAL') || die();

$observers = array(

    array(
        'eventname' => '\core\event\question_deleted',
        'callback' => '\local_questionbank\observer::question_deleted',
    ),
    array(
        'eventname'   => '\core\event\question_created',
        'callback'    =>  '\local_questionbank\observer::question_created',
    )  
);