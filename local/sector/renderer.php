<?php
class local_sector_renderer extends plugin_renderer_base {
    public function get_sectors_view($filter = false) {
        $systemcontext = context_system::instance();
      $options = array('targetID' => 'manage_sectors','perPage' => 2, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'list');
      $options['methodName']='local_sectors_view';
      $options['templateName']='local_sector/sectors';
      $options = json_encode($options);
      $filterdata = json_encode(array());
      $dataoptions = json_encode(array('contextid' => $systemcontext->id));
      $context = [
        'targetID' => 'manage_sectors',
        'options' => $options,
        'dataoptions' => $dataoptions,
        'filterdata' => $filterdata,
        'widthclass' => 'col-md-12',
        ];
        if($filter){
            return  $context;
        }else{
            return  $this->render_from_template('theme_academy/cardPaginate', $context);
        }
    }
  public function get_jobrole_level_view( $filter = false,$jobid) {
    
      $systemcontext = context_system::instance();
      $options = array('targetID' => 'manage_sectors','perPage' => 5, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'list');
      $options['methodName']='local_jobrole_level_view';
      $options['templateName']='local_sector/jobrole';
      $options = json_encode($options);
      $filterdata = json_encode(array());
      $dataoptions = json_encode(array('contextid' => $systemcontext->id,'jobid'=>$jobid));
      $context = [
        'targetID' => 'manage_sectors',
        'options' => $options,
        'dataoptions' => $dataoptions,
        'filterdata' => $filterdata,
        
    ];
    if($filter){
        return  $context;
    }else{
        return  $this->render_from_template('theme_academy/cardPaginate', $context);
    }
}




}
