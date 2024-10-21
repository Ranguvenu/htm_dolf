<?php
namespace local_cpd\output;

/**
 * Defines the version of Training program
 *
 * @package    local_cpd
 * @copyright  2022 Naveen kumar <naveen@eabyas.com>
 */
defined('MOODLE_INTERNAL') || die;

use renderable;
use templatable;
use renderer_base;
use stdClass;
/**
 * CPD renderer
 */
class cpdview implements renderable, templatable
{
    var $tpdata = null;

    public function __construct($tpdata)
    {
        $this->tpdata = $tpdata;
    }

    public function export_for_template(renderer_base $output) {
        $data = new stdClass();                                                                                                     
        $data= $this->tpdata;                                                                                          
        return $data;  
    }
}