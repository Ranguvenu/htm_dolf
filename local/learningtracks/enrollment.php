<?php
ini_set('memory_limit', '-1');
define('NO_OUTPUT_BUFFERING', true);
require('../../config.php');
require_once($CFG->dirroot . '/local/learningtracks/lib.php');
require_once($CFG->dirroot . '/local/learningtracks/filters_form.php');
require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/course/externallib.php');
global $CFG,$DB,$USER,$PAGE,$OUTPUT,$SESSION;
 

$track_id     = required_param('trackid', PARAM_INT);

$context = context_system::instance();
$backurl = new moodle_url('/local/learningtracks/enrollment.php',['trackid'=> $track_id]);
$PAGE->set_context($context);
$PAGE->set_url($backurl);
$renderer = $PAGE->get_renderer('local_learningtracks');
$track = $renderer->track_check($track_id);
$learningtrack = $DB->get_record('local_learningtracks', ['id' => $track_id], 'id,name,namearabic', MUST_EXIST);

$lang= current_language();
if( $lang == 'ar'){
    $name = $learningtrack->namearabic;                
}else{                
    $name = $learningtrack->name;               
} 

$trackname = get_string('trackname', 'local_learningtracks', $name);
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->set_title(get_string('enrolled','local_learningtracks'));
$PAGE->set_heading($trackname);

$PAGE->navbar->add(get_string('manage_learningtracks','local_learningtracks'),new moodle_url('/local/learningtracks/index.php'));
$PAGE->navbar->add($name, new moodle_url('/local/learningtracks/view.php?id='.$track_id));
$PAGE->navbar->add(get_string('enrolled','local_learningtracks'));

echo $OUTPUT->header();


if ($learningtrack) {

    $entity_data = (new local_learningtracks\learningtracks)->get_learningtrack_entities($track_id);

    if(!is_siteadmin()){

        $manage_organizationofficial=false;

        if(has_capability('local/organization:manage_organizationofficial', $context)){


            $product_attributes=(new \tool_product\product)->get_button_order_seats($label=get_string('booknow','local_learningtracks'),'local_learningtracks','id',$track_id,$entity_data['lowestseats'],0, $action='booknow',$grouped=$track_id);

            $manage_organizationofficial=true;

         }else{

            $product_attributes=(new \tool_product\product)->get_product_attributes($track_id, 5, 'addtocart', false, 0, 1, false, $track_id);

         }

        

        $data = [
            'data' => $entity_data,
            'examlist' => json_encode($examlist),
            'programlist' => json_encode($programlist),
            'trackid' => $track_id,
            'product_attributes' => $product_attributes,
            'manage_organizationofficial'=>$manage_organizationofficial
        ];


        echo $OUTPUT->render_from_template('local_learningtracks/enrolment_entities', $data);

    }
}

echo $OUTPUT->footer();
