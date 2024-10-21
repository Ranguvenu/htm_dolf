<?php
namespace local_exams\form;
use stdClass;
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/csvlib.class.php');
use moodleform;

class fastsettingsform extends moodleform { 
    public function definition() {
        global $DB, $CFG;
        $mform = $this->_form;

        $mform->addElement('html', '<div class="qheader">'. get_string('fastsettings', 'local_exams') .'</div>');

        $mform->addElement('static', 'fastnotificationslink', '<div>'. get_string('fastnotificationslink', 'local_exams') .'</div>');
        $mform->addHelpButton('fastnotificationslink', 'fastlink', 'local_exams');


        $mform->addElement('static', 'userlogs', '<div>'. get_string('userlogslink', 'local_exams') .'</div>');
        $mform->addHelpButton('userlogs', 'userlogs', 'local_exams');

        // Replace Status
        $mform->addElement('advcheckbox', 'replacefast', get_string('replacefast', 'local_exams'), get_string('replacefast', 'local_exams'), null, [0,1]);
        $mform->setType('replacefast', PARAM_BOOL);

        $mform->addElement('html', '<div class="qheader">'. get_string('fastapidetails', 'local_exams') .'</div><br>');

        // FAST API's Settings
        $mform->addElement('advcheckbox', 'userregistration', get_string('userregistration', 'local_exams'), get_string('userregistration', 'local_exams'), null, [0,1]);
        $mform->setType('userregistration', PARAM_BOOL);
        $mform->addHelpButton('userregistration', 'userregistration', 'local_exams');
        $mform->disabledIf('userregistration', 'replacefast', 'eq', 1);

        $mform->addElement('advcheckbox', 'getfaschedules', get_string('getfaschedules', 'local_exams'), get_string('getfaschedules', 'local_exams'), null, [0,1]);
        $mform->setType('getfaschedules', PARAM_BOOL);
        $mform->addHelpButton('getfaschedules', 'getfaschedules', 'local_exams');
        $mform->disabledIf('getfaschedules', 'replacefast', 'eq', 1);

        $mform->addElement('advcheckbox', 'examreservation', get_string('examreservation', 'local_exams'), get_string('examreservation', 'local_exams'), null, [0,1]);
        $mform->setType('examreservation', PARAM_BOOL);
        $mform->disabledIf('examreservation', 'replacefast', 'eq', 1);

        $mform->addElement('advcheckbox', 'hallavailability', get_string('hallavailability', 'local_exams'), get_string('hallavailability', 'local_exams'), null, [0,1]);
        $mform->setType('hallavailability', PARAM_BOOL);
        $mform->disabledIf('hallavailability', 'replacefast', 'eq', 1);

        $mform->addElement('advcheckbox', 'rescheduleservice', get_string('rescheduleservice', 'local_exams'), get_string('rescheduleservice', 'local_exams'), null, [0,1]);
        $mform->setType('rescheduleservice', PARAM_BOOL);
        $mform->disabledIf('rescheduleservice', 'replacefast', 'eq', 1);

        $mform->addElement('advcheckbox', 'cancelservice', get_string('cancelservice', 'local_exams'), get_string('cancelservice', 'local_exams'), null, [0,1]);
        $mform->setType('cancelservice', PARAM_BOOL);
        $mform->disabledIf('cancelservice', 'replacefast', 'eq', 1);

        $mform->addElement('advcheckbox', 'replaceservice', get_string('replaceservice', 'local_exams'), get_string('replaceservice', 'local_exams'), null, [0,1]);
        $mform->setType('replaceservice', PARAM_BOOL);
        $mform->disabledIf('replaceservice', 'replacefast', 'eq', 1);

        $mform->addElement('advcheckbox', 'userattemptstatus', get_string('userattemptstatus', 'local_exams'), get_string('userattemptstatus', 'local_exams'), null, [0,1]);
        $mform->setType('userattemptstatus', PARAM_BOOL);
        $mform->disabledIf('userattemptstatus', 'replacefast', 'eq', 1);

        // Replace Settings
        $mform->addElement('html', '<div class="qheader">'. get_string('replacesettings', 'local_exams') .'</div><br>');

        $mform->addElement('text', 'replacegeneratetoken', get_string('rgeneratetoken', 'local_exams', 'generatetoken'));
        $mform->disabledIf('replacegeneratetoken', 'replacefast', 'eq', 0);

        $mform->addElement('text', 'replaceuserregistration', get_string('ruserregistration', 'local_exams','userregistration'));
        $mform->disabledIf('replaceuserregistration', 'replacefast', 'eq', 0);

        $mform->addElement('text', 'replacegetfaschedules', get_string('rgetfaschedules', 'local_exams','getfaschedules'));
        $mform->disabledIf('replacegetfaschedules', 'replacefast', 'eq', 0);

        $mform->addElement('text', 'replaceexamreservation', get_string('rexamreservation', 'local_exams','examreservation'));
        $mform->disabledIf('replaceexamreservation', 'replacefast', 'eq', 0);

        $mform->addElement('text', 'replacehallavailability', get_string('rhallavailability', 'local_exams','hallavailability'));
        $mform->disabledIf('replacehallavailability', 'replacefast', 'eq', 0);

        $mform->addElement('text', 'replacerescheduleservice', get_string('rrescheduleservice', 'local_exams','rescheduleservice'));
        $mform->disabledIf('replacerescheduleservice', 'replacefast', 'eq', 0);

        $mform->addElement('text', 'replacecancelservice', get_string('rcancelservice', 'local_exams','cancelservice'));
        $mform->disabledIf('replacecancelservice', 'replacefast', 'eq', 0);

        $mform->addElement('text', 'replacereplaceservice', get_string('rreplaceservice', 'local_exams','replaceservice'));
        $mform->disabledIf('replacereplaceservice', 'replacefast', 'eq', 0);

        $this->add_action_buttons(false);
    }

    public function validation($data, $files) {
        $errors = [];

        return $errors;
    }
}
