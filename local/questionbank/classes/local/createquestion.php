<?php
namespace local_questionbank\local;

use core_tag_tag;
use question_bank;
use context_system;
use \core\event\question_created;
use \core_question\local\bank\question_version_status;
use context_course;
use context;
use block_contents;

class createquestion {

    /**
     * Process the file
     * question method should not normally be overidden
     * @return bool success
     */
    public function clone($question, $qc) {
       global $USER, $DB, $OUTPUT;
        // get list of valid answer grades
        $gradeoptionsfull = question_bank::fraction_options_full();

        $importcontext = context::instance_by_id($qc->contextid);

            if (!empty($question->fraction) and (is_array($question->fraction))) {
                $fractions = $question->fraction;
                $invalidfractions = array();
                foreach ($fractions as $key => $fraction) {
                    $newfraction = match_grade_options($gradeoptionsfull, $fraction);
                    if ($newfraction === false) {
                        $invalidfractions[] = $fraction;
                    } else {
                        $fractions[$key] = $newfraction;
                    }
                }

                $question->fraction = $fractions;
                
            }
        
            $question->context = $importcontext;


            $question->category = $question->category;
            $question->stamp = make_unique_id_code();  // Set the unique code (not to be changed)

            $question->createdby = $USER->id;
            $question->timecreated = time();
            $question->modifiedby = $USER->id;
            $question->timemodified = time();
            if (isset($question->idnumber)) {
                if ((string) $question->idnumber === '') {
                    // Id number not really set. Get rid of it.
                    unset($question->idnumber);
                } else {
                    if ($DB->record_exists('question_bank_entries',
                            ['idnumber' => $question->idnumber, 'questioncategoryid' => $question->category])) {
                        // We cannot have duplicate idnumbers in a category. Just remove it.
                        unset($question->idnumber);
                    }
                }
            }

            $fileoptions = array(
                    'subdirs' => true,
                    'maxfiles' => -1,
                    'maxbytes' => 0,
                );

            $question->id = $DB->insert_record('question', $question);
            // Create a bank entry for each question imported.
            $questionbankentry = new \stdClass();
            $questionbankentry->questioncategoryid = $question->category;
            $questionbankentry->idnumber = $question->idnumber ?? null;
            $questionbankentry->ownerid = $question->createdby;
            $questionbankentry->id = $DB->insert_record('question_bank_entries', $questionbankentry);
            // Create a version for each question imported.
            $questionversion = new \stdClass();
            $questionversion->questionbankentryid = $questionbankentry->id;
            $questionversion->questionid = $question->id;
            $questionversion->version = 1;
            $questionversion->status = \core_question\local\bank\question_version_status::QUESTION_STATUS_READY;
            $questionversion->id = $DB->insert_record('question_versions', $questionversion);

            $event = \core\event\question_created::create_from_question_instance($question, $importcontext);
            $event->trigger();

            if (isset($question->questiontextitemid)) {
                $question->questiontext = file_save_draft_area_files($question->questiontextitemid,
                        $importcontext->id, 'question', 'questiontext', $question->id,
                        $fileoptions, $question->questiontext);
            } else if (isset($question->questiontextfiles)) {
                foreach ($question->questiontextfiles as $file) {
                    question_bank::get_qtype($question->qtype)->import_file(
                            $importcontext, 'question', 'questiontext', $question->id, $file);
                }
            }
            if (isset($question->generalfeedbackitemid)) {
                $question->generalfeedback = file_save_draft_area_files($question->generalfeedbackitemid,
                        $importcontext->id, 'question', 'generalfeedback', $question->id,
                        $fileoptions, $question->generalfeedback);
            } else if (isset($question->generalfeedbackfiles)) {
                foreach ($question->generalfeedbackfiles as $file) {
                    question_bank::get_qtype($question->qtype)->import_file(
                            $importcontext, 'question', 'generalfeedback', $question->id, $file);
                }
            }
            $DB->update_record('question', $question);


            // Now to save all the answers and type-specific options

            $result = question_bank::get_qtype($question->qtype)->save_question_options($question);

            if (core_tag_tag::is_enabled('core_question', 'question')) {
                // Is the current context we're importing in a course context?
                $importingcontext = $importcontext;
                $importingcoursecontext = $importingcontext->get_course_context(false);
                $isimportingcontextcourseoractivity = !empty($importingcoursecontext);

                // if (!empty($question->coursetags)) {
                //     if ($isimportingcontextcourseoractivity) {
                //         $mergedtags = array_merge($question->coursetags, $question->tags);

                //         core_tag_tag::set_item_tags('core_question', 'question', $question->id,
                //             $question->context, $mergedtags);
                //     } else {
                //         core_tag_tag::set_item_tags('core_question', 'question', $question->id,
                //             context_course::instance($this->course->id), $question->coursetags);

                //         if (!empty($question->tags)) {
                //             core_tag_tag::set_item_tags('core_question', 'question', $question->id,
                //                 $importingcontext, $question->tags);
                //         }
                //     }
                // } else if (!empty($question->tags)) {
                    core_tag_tag::set_item_tags('core_question', 'question', $question->id,
                        $question->context, $question->tags);
                // }
            }

            if (!empty($result->error)) {
                echo $OUTPUT->notification($result->error);
                // Can't use $transaction->rollback(); since it requires an exception,
                // and I don't want to rewrite this code to change the error handling now.
                $DB->force_transaction_rollback();
                return false;
            }

            $transaction->allow_commit();

            if (!empty($result->notice)) {
                echo $OUTPUT->notification($result->notice);
                return true;
            }

        
        return true;
    }
  // Vinod - Questionbank block for exam official and expert - Starts//
    public function questionbankfakeblock () {
        global $PAGE,$USER,$DB;
        $systemcontext = context_system::instance();
        if(!is_siteadmin() && (has_capability('local/organization:manage_examofficial', $systemcontext) || has_capability('local/organization:manage_expert', $systemcontext))) {
            $bc = new block_contents();
            $bc->title = get_string('questionbank','local_questionbank');
            $bc->attributes['class'] = 'questionbank_fakeblock';
            $bc->content = $this->questionbank_block();
            $PAGE->blocks->add_fake_block($bc, 'content');
        }
    }
    public function questionbank_block()
    { 
        global $DB, $PAGE, $OUTPUT, $CFG, $USER;
        $renderer = $PAGE->get_renderer('local_questionbank');
        $renderable = new \local_questionbank\output\questionbank();
        return $renderer->render($renderable);
    }
    //Vinod - Questionbank block for exam official and expert - Ends//
}
