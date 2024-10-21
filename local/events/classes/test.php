<?php  

namespace local_test;
/**
 * 
 */
class test
{
    public function get_posts() {
        global $DB;
        $posts = $DB->get_records('postings');
        foreach($posts as $post) {
            $postings[] = ['id' => $post->id, 'title' => $post->title,
                            'description' => $post->description];
        };
        return $postings;
    }
}