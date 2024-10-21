<?php 
namespace tool_product;
defined('MOODLE_INTERNAL') || die();
class max_discount_percentage_settings extends \admin_setting_configtext {
    public function __construct($name, $visiblename, $description, $defaultsetting, $paramtype=PARAM_INT, $size=null) {
        $this->paramtype = $paramtype;
        if (!is_null($size)) {
            $this->size  = $size;
        } else {
            $this->size  = ($paramtype === PARAM_INT) ? 5 : 30;
        }
        parent::__construct($name, $visiblename, $description, $defaultsetting);
    }
    public function validate($data) {
        $temp = $data;
        $temp = (int)$temp;
        if("$temp" === "$data"){
            if($temp > 100 ){
                return get_string('max_discount_percentage_error', 'tool_product');
            }else{
                return true;
            }
        }else{
            return get_string('validateerror', 'admin');
        }
    }
}