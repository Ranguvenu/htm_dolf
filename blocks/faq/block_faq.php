<?php  

defined('MOODLE_INTERNAL') || die();


/**
 * 
 */
class block_faq extends block_base 
{
     public function init() {
        global $CFG;
        $this->title = get_string('pluginname', 'block_faq');

        
    }
    function has_config() {
        return true;
    }
     public function get_content() {
        global $USER,$DB,$CFG;
        if ($this->content !== null) {
            return $this->content;
        }
        $this->content = new stdClass();
        $countsql = "SELECT  * FROM {faq}";
        $totalnoofquestions=$DB->get_records_sql($countsql);
        $count = count($totalnoofquestions);
        $this->content->text = get_string('totalnoofquestions', 'block_faq').$count. ' <a class=""
                href="'.$CFG->wwwroot.'/blocks/faq/index.php">' . (get_string('viewfiles', 'block_faq')) .
                '</a>';
        return $this->content;
       
    }
   
}
