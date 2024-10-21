<?php

// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * External Web Service Template
 *
 * @package    localwstemplate
 * @copyright  2011 Moodle Pty Ltd (http://moodle.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once("{$CFG->dirroot}/mod/quiz/attemptlib.php");

require_once($CFG->libdir . "/externallib.php");

class lms_webservice extends external_api {

    /**
     * Makes sure user may execute functions in this context.
     * @param object $context
     * @return void
     */
    public static function validate_context($context) {
        global $CFG;
        if (empty($context)) {
            throw new invalid_parameter_exception('Context does not exist');
        }

        $rcontext = get_context_instance(CONTEXT_SYSTEM);

        if ($rcontext->contextlevel == $context->contextlevel) {
            if ($rcontext->id != $context->id) {
                throw new restricted_context_exception();
            }
        } else if ($rcontext->contextlevel > $context->contextlevel) {
            throw new restricted_context_exception();
        } else {
            $parents = get_parent_contexts($context);
            if (!in_array($rcontext->id, $parents)) {
                throw new restricted_context_exception();
            }
        }

        if ($context->contextlevel >= CONTEXT_COURSE) {
            list($context, $course, $cm) = get_context_info_array($context->id);
        }
    }

    /**
     * extracts the context given a token
     * @return session object or false if the session is not valid
     */
    public static function get_context_by_token($token) {
        global $DB;
        $token_entry = $DB->get_record('external_tokens', array('token' => $token));
        return get_context_instance_by_id($token_entry->contextid);
    }

    public static function validate_parameters(external_description $description, $params) {
        if ($description instanceof external_value) {
            if (is_array($params) or is_object($params)) {
                throw new invalid_parameter_exception('Scalar type expected, array or object received.');
            }

            if ($description->type == PARAM_BOOL) {
                // special case for PARAM_BOOL - we want true/false instead of the usual 1/0 - we can not be too strict here ;-)
                if (is_bool($params) or $params === 0 or $params === 1 or $params === '0' or $params === '1') {
                    return (bool)$params;
                }
            }
            $debuginfo = 'Invalid external api parameter: the value is "' . $params .
                    '", the server was expecting "' . $description->type . '" type';
            return validate_param($params, $description->type, $description->allownull, $debuginfo);

        } else if ($description instanceof external_single_structure) {
            if (!is_array($params)) {
                throw new invalid_parameter_exception('Only arrays accepted. The bad value is: \''
                        . print_r($params, true) . '\'');
            }
            $result = array();
            foreach ($description->keys as $key=>$subdesc) {
                if (!array_key_exists($key, $params)) {
                    if ($subdesc->required == VALUE_REQUIRED) {
                        throw new invalid_missing_parameter_exception('Missing required key in single structure: '. $key);
                    }
                    if ($subdesc->required == VALUE_DEFAULT) {
                        try {
                            $result[$key] = static::validate_parameters($subdesc, $subdesc->default);
                        } catch (invalid_parameter_exception $e) {
                            //we are only interested by exceptions returned by validate_param() and validate_parameters()
                            //(in order to build the path to the faulty attribut)
                            throw new invalid_parameter_exception($key." => ".$e->getMessage() . ': ' .$e->debuginfo);
                        }
                    }
                } else {
                    try {
                        $result[$key] = static::validate_parameters($subdesc, $params[$key]);
                    } catch (invalid_parameter_exception $e) {
                        //we are only interested by exceptions returned by validate_param() and validate_parameters()
                        //(in order to build the path to the faulty attribut)
                        throw new invalid_parameter_exception($key." => ".$e->getMessage() . ': ' .$e->debuginfo);
                    }
                }
                unset($params[$key]);
            }
            if (!empty($params)) {
                throw new invalid_parameter_exception('Unexpected keys (' . implode(', ', array_keys($params)) . ') detected in parameter array.');
            }
            return $result;

        } else if ($description instanceof external_multiple_structure) {
            if (!is_array($params)) {
                throw new invalid_parameter_exception('Only arrays accepted. The bad value is: \''
                        . print_r($params, true) . '\'');
            }
            $result = array();
            foreach ($params as $param) {
                $result[] = static::validate_parameters($description->content, $param);
            }
            return $result;

        } else {
            throw new invalid_parameter_exception('Invalid external api description');
        }
    }


}

class Response {
    const E_INSERT = 1000;
    const E_UPDATE = 1001;
    const E_DELETE = 1002;
    
    
    const INSERT_SUCCESFULL = 2000;
}



/**
 * External Web Service Template
 *
 * @package    locallmswebservices
 * @copyright  2019 fa
 * @license   fa global 
 * @specification handling all the user define error 
 */
class CustomErrorHandling {

    public function responseHandling($statusArr, $execptionArr, $paramArr) {
        global $DB, $USER;
        if (!empty($USER->id)) {
            $username = $USER->firstname . " " . $USER->lastname;
        } else {
            $username = "External user ";
        }
      //echo "<pre>statusArr";
   //  print_r($statusArr);
    //  echo "<pre>execptionArr";
    //  print_r($execptionArr);
    //  echo "<pre>paramArr";
    //  print_r($paramArr);
       
        for ($i = 0; $i < count($statusArr); $i++) {
           
            if (!empty($execptionArr)) {
                // fail case data              
                $errormessage = $execptionArr->getMessage();
                $statusmessage = $execptionArr->module;
                $errorcode = $execptionArr->errorcode;
                $debuginfo = $execptionArr->debuginfo;
                $query = $execptionArr->sql;
                $suggestion = "please check debuginfo!";
            } else {
                // success case data
                $errormessage = $statusArr[$i]['errormessage'];
                $statusmessage = $statusArr[$i]['statusmessage'];
                $errorcode = $statusArr[$i]['errorcode'];
            }
       
            if (!empty($statusArr[$i]['statuscode'])) {                
                if($statusArr[$i]['statuscode'] == 505){
                    $statusmessage = $execptionArr->module;
                }else if($statusArr[$i]['statuscode'] == 200){
                    $statusmessage = 'Ok';
                }else if($statusArr[$i]['statuscode'] == 400){
                    $statusmessage = 'Bad Request';
                }else if($statusArr[$i]['statuscode'] == 201){
                     $statusmessage ='Some id(s) does not exists or invalid';
                }
            } else {
                $statusmessage = $statusArr[$i]['statusmessage'];
            }            
            $insert = new stdClass();
            $insert->responsecode = $statusArr[$i]['statuscode'];
            $insert->exception = $errorcode;
            $insert->errormessage = $errormessage;
            $insert->statuscode = $statusArr[$i]['statuscode'];
            $insert->statusmessage = $statusmessage;
            $insert->debuginfo = $debuginfo;
            $insert->suggestion = $suggestion;
            $insert->action = $query;
            $insert->event = $statusArr[$i]['event'];
            $insert->body = $paramArr;
            $insert->additionmessage = $errormsg->additionalmessage;
            $insert->retrail =$errormsg->retrial;
            $insert->reprocessed =$errormsg->reprocessed;            
            $insert->username = $username;
            $insert->timecreated = time();
            $insert->status = $statusArr[$i]['sucess'];           
            $id=$DB->insert_record('local_wsapi_log', $insert);       
           // print_r($insert);
        }
    }
}
