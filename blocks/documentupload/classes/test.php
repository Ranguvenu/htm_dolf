<?php  

namespace block_faq;
/**
 * 
 */
class test
{
    public function get_faqs() {
        global $DB;
        $faqs = $DB->get_records('faq');
        foreach($faqs as $faq) {
            $postings[] = ['id' => $faq->id, 'title' => $faq->title,
                            'description' => $faq->description,'categoryid'=>$faq->categoryid];
        };
        return $postings;
    }
}