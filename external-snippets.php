<?php
/**
 * Plugin Name: External Snippets
 * Description: Easily pull in HTML from an external site to display in pages/posts.
 * Version: 1.0.0
 * Author: Virtual Inc
 * Author URI: https://virtualinc.com
 * License: GPL2
 */
 
/*  Copyright 2020  Virtual Inc (email webadmin@virtualinc.com) */
function vinc_fetch_external_page($atts) {
    global $pagenow;
    if ( ( $pagenow == 'post.php' ) || (get_post_type() == 'post') ) {
        return;
    } else {

    /* For reference on pulling html from external site.
    *
    *   https://codingreflections.com/php-parse-html/
    *   https://phpenthusiast.com/blog/parse-html-with-php-domdocument#php_domdocument_add_rel_nofollow_to_links
    *
    */
    // Use curl to pull in the HTML for the specified page
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $atts['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $res = curl_exec($ch);
    curl_close($ch);
    // Create a DomDocument instance to make easily manipulate the HTML
    $dom = new DomDocument();
    @ $dom->loadHTML($res);
    // Parse through the HTML and pull out are targeted DIV by id
    $chunk = $dom->getElementById($atts['elementid']); //DOMElement
    // Find any element which is a link
    $nodes = $dom->getElementsByTagName('a');  
    // Loop the elements
    foreach ($nodes as $node)               
    {             
        // Add the rel attribute
        $str = $node->getAttribute('href');
        if ($atts['replaceurl'] && strpos($str, '#') === false) {
            $node->setAttribute('href', 'https://www.lesusacanada.org' . $node->getAttribute('href'));
        }
    }
    $scpt = $dom->createElement('script');
    $scpt->setAttribute('src', 'https://cdn.ymaws.com/global/js/20200204/frontend/combined.js');
    $chunk->appendChild($scpt);
    $result = @ $dom->saveHTML($chunk);
    return $result;
    }
}
add_shortcode('fetch_external_content', 'vinc_fetch_external_page');
