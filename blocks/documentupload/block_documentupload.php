<?php  

defined('MOODLE_INTERNAL') || die();


/**
 * 
 */
class block_documentupload extends block_base 
{
     public function init() {
        global $CFG;
        $this->title = get_string('pluginname', 'block_documentupload');

        
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
       // $DB->set_debug(true);
        $doccountsql = "SELECT  * FROM {documentupload} WHERE mediatype = '1'";
        $doctotalrecords=$DB->get_records_sql($doccountsql);
        $doctotalnoofuploads = count($doctotalrecords);
        //$DB->set_debug(false);
      

        //Videos count
        $videocountsql = "SELECT  * FROM {documentupload} WHERE mediatype = '2'";
        $videototalrecords=$DB->get_records_sql($videocountsql);
        $videototalnoofuploads = count($videototalrecords);
        $this->content->text = get_string('totalnoofuploads', 'block_documentupload') . $doctotalnoofuploads.'<br>' . get_string('totalnoofvideos', 'block_documentupload') . $videototalnoofuploads . '<a class=""
                href="' . $CFG->wwwroot . '/blocks/documentupload/index.php" title=>'  .'<br>' .(get_string('viewfiles', 'block_documentupload')) .
                '</a>';
         return $this->content;
    }
   
}
