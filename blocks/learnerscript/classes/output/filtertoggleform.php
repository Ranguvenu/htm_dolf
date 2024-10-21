<?php
// Standard GPL and phpdocs
namespace block_learnerscript\output;

use renderable;
use renderer_base;
use templatable;
use stdClass;
class filtertoggleform implements renderable, templatable {
    public $filterform;
    public $plottabscontent;
    public function __construct($filterform = false, $plottabscontent = false, $reporttype = false) {
        $this->filterform = $filterform;
        $this->plottabscontent = $plottabscontent;
        $this->reporttype = $reporttype;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $OUTPUT;
        $data = new stdClass();
        $data->filterform = $this->filterform;
        $data->plottabscontent = $this->plottabscontent;


        if ($this->reporttype == 'examenrol' || $this->reporttype == 'programenrol' || $this->reporttype == 'eventenrol' || $this->reporttype == 'offerings' || $this->reporttype == 'transaction' || $this->reporttype == 'revenue' || $this->reporttype == 'traineeactivities') {

            $data->examenrolreport = 0;
        } else {
            $data->examenrolreport = 1;
        }
        return $data;
    }
}
