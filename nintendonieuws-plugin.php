<?php
/*
Plugin Name: Custom Nintendonieuws.be
Description: Site specific code aanpassingen voor Nintendonieuws.be
*/
/* Start Adding Functions Below this Line */

//Add custom css

function add_my_stylesheet() 
{
    wp_enqueue_style( 'nintendonieuws-custom', plugins_url( '/css/nintendonieuws-custom.css', __FILE__ ) );

}

add_action('wp_enqueue_scripts', 'add_my_stylesheet', 25);

//Reposition Primary menu (under header to header)
add_action( 'after_setup_theme', 'relocate_menu', 0 );
function relocate_menu() {
    remove_action( 'genesis_after_header', 'genesis_do_nav' );
}
add_action( 'genesis_header', 'genesis_do_nav' );

//Forum menu
function register_additional_genesis_menus() {

register_nav_menu( 'third-menu' ,
__( 'Third Navigation Menu' ));
}
add_action( 'init', 'register_additional_genesis_menus' );

//add_action( 'genesis_after_header', 'add_third_nav' ); 

function add_third_nav() {

wp_nav_menu( array( 
'theme_location' => 'third-menu', 
'container_class' => 'genesis-nav-menu' ) );
}

//Alter footer
add_filter('genesis_footer_creds_text', 'sp_footer_creds_filter');
function sp_footer_creds_filter( $creds ) {
	$creds = '<a href="http://creativecircle.be" rel="nofollow" title="Fotografie, Webdesign, Multimedia & IT">CreativeCircle.be</a> | info[at]nintendonieuws.com | Onafhankelijke website';
	return $creds;
}

// Function to change email address
function wpb_sender_email( $original_email_address ) {
    return 'info@nintendonieuws.com';
}

// Function to change sender name
function wpb_sender_name( $original_email_from ) {
	return 'Nintendo Nieuws';
}

// Hooking up our functions to WordPress filters 
add_filter( 'wp_mail_from', 'wpb_sender_email' );
add_filter( 'wp_mail_from_name', 'wpb_sender_name' );

// Hide Meta 
function restyle_posts () {

    if (!is_singular()){
    remove_action( 'genesis_entry_header', 'genesis_post_info', 12 );
    remove_action( 'genesis_entry_footer', 'genesis_post_meta' );
	remove_action( 'genesis_entry_content', 'genesis_do_post_content' );
	//remove_action( 'genesis_entry_content', 'genesis_do_post_image', 8 );
	//add_action( 'genesis_entry_header', 'genesis_do_post_image', 8 );
    add_action ('genesis_entry_header', 'genesis_post_meta');
    add_filter ('genesis_post_meta', 'cc_post_meta_filter');
    function cc_post_meta_filter($post_meta){
        $post_meta = '[post_categories before=""]';
        return $post_meta;
    }
    }
}
add_action( 'genesis_before_loop', 'restyle_posts' );

// Add Yoast Breadcrumbs
//add_action( 'genesis_before_entry', 'wordpress_seo_plugins_breadcrumbs' );
function wordpress_seo_plugins_breadcrumbs() {
if ( function_exists('yoast_breadcrumb') && is_single() ) {
	yoast_breadcrumb('<p id="breadcrumbs">','</p>');
	}
}

// Add featured image background - Single post
add_action ('genesis_before', 'cc_featured_background');
function cc_featured_background(){
    if (is_single() && has_post_thumbnail()){
        printf( '<div class=body-background style=background-image:url(%s)></div>', get_the_post_thumbnail_url( $post = null ,$size = 'featured-image'));
    }
}

//Display related posts (based on tags & cat)
//add_action( 'genesis_before_comments', 'sk_related_posts', 12 );
/**
 * Outputs related posts with thumbnail
 *
 * @author Nick the Geek
 * @url http://designsbynickthegeek.com/tutorials/related-posts-genesis
 * @global object $post
 */
function sk_related_posts() {

	global $do_not_duplicate;

	if ( ! is_singular ( 'post' ) ) {
		return;
	}

	$count = 0;

	$related = '';

	$do_not_duplicate = array();

	$tags = wp_get_post_tags( get_the_ID() );

	$cats = wp_get_post_categories( get_the_ID() );

	// If we have some tags, run the tag query.
	if ( $tags ) {
		$query    = sk_related_tag_query( $tags, $count );
		$related .= $query['related'];
		$count    = $query['count'];
	}

	// If we have some categories and less than 5 posts, run the cat query.
	if ( $cats && $count <= 4 ) {
		$query    = sk_related_cat_query( $cats, $count );
		$related .= $query['related'];
		$count    = $query['count'];
	}

	// End here if we don't have any related posts.
	if ( ! $related ) {
		return;
	}

	// Display the related posts section.
	echo '<section class="entry-related">';
		echo '<h3 class="entry-related-title">You might also enjoy...</h3>';
		echo '<div class="related-posts-list" data-columns>' . $related . '</div>';
	echo '</section>';

}

function sk_related_tag_query( $tags, $count ) {

	global $do_not_duplicate;

	if ( ! $tags ) {
		return;
	}

	$postIDs = array( get_the_ID() );

	foreach ( $tags as $tag ) {
		$tagID[] = $tag->term_id;
	}

	$tax_query = array(
		array(
			'taxonomy'  => 'post_format',
			'field'     => 'slug',
			'terms'     => array(
				'post-format-link',
				'post-format-status',
				'post-format-aside',
				'post-format-quote'
				),
			'operator' => 'NOT IN'
		)
	);
	$args = array(
		'tag__in'               => $tagID,
		'post__not_in'          => $postIDs,
		'showposts'             => 5,
		'ignore_sticky_posts'   => 1,
		'tax_query'             => $tax_query,
	);

	$related  = '';

	$tag_query = new WP_Query( $args );

	if ( $tag_query->have_posts() ) {
		while ( $tag_query->have_posts() ) {
			$tag_query->the_post();

			$do_not_duplicate[] = get_the_ID();

			$count++;

			// $title = genesis_truncate_phrase( get_the_title(), 35 );
			$title = get_the_title();

			$related .= '<div class="related-post">';
			$related .= '<a class="related-post-title" href="' . get_permalink() . '" rel="bookmark" title="Permanent Link to ' . $title . '">' . $title . '</a>';
			$related .= '<a class="related-image" href="' . get_permalink() . '" rel="bookmark" title="Permanent Link to ' . $title . '">' . genesis_get_image( array( 'size' => 'related' ) ) . '</a>';
			$related .= '</div>';
		}
	}

	wp_reset_postdata();

	$output = array(
		'related' => $related,
		'count'   => $count
	);

	return $output;
}

function sk_related_cat_query( $cats, $count ) {

	global $do_not_duplicate;

	if ( ! $cats ) {
		return;
	}

	$postIDs = array_merge( array( get_the_ID() ), $do_not_duplicate );

	$catIDs = array();

	foreach ( $cats as $cat ) {
		if ( 3 == $cat ) {
			continue;
		}
		$catIDs[] = $cat;
	}

	$showposts = 5 - $count;

	$tax_query = array(
		array(
			'taxonomy'  => 'post_format',
			'field'     => 'slug',
			'terms'     => array(
				'post-format-link',
				'post-format-status',
				'post-format-aside',
				'post-format-quote'
				),
			'operator' => 'NOT IN'
		)
	);
	$args = array(
		'category__in'          => $catIDs,
		'post__not_in'          => $postIDs,
		'showposts'             => $showposts,
		'ignore_sticky_posts'   => 1,
		'orderby'               => 'rand',
		'tax_query'             => $tax_query,
	);

	$related  = '';

	$cat_query = new WP_Query( $args );

	if ( $cat_query->have_posts() ) {
		while ( $cat_query->have_posts() ) {
			$cat_query->the_post();

			$count++;

			// $title = genesis_truncate_phrase( get_the_title(), 35 );
			$title = get_the_title();

			$related .= '<div class="related-post">';
			$related .= '<a class="related-post-title" href="' . get_permalink() . '" rel="bookmark" title="Permanent Link to ' . $title . '">' . $title . '</a>';
			$related .= '<a class="related-image" href="' . get_permalink() . '" rel="bookmark" title="Permanent Link to ' . $title . '">' . genesis_get_image( array( 'size' => 'related' ) ) . '</a>';
			$related .= '</div>';

		}
	}

	wp_reset_postdata();

	$output = array(
		'related' => $related,
		'count'   => $count
	);

	return $output;

}

//Moves titles above content and sidebar
add_action( 'loop_start', 'remove_titles_all_single_posts' );
function remove_titles_all_single_posts() {
    if ( is_singular('post') ) {
        remove_action( 'genesis_entry_header', 'genesis_do_post_title' );
		add_action( 'genesis_before_entry', 'genesis_do_post_title', 7 );
    }
}

//* Add support for post formats
add_theme_support( 'post-formats', array(
	'video'
) );

//* Add support for post format images
//add_theme_support( 'genesis-post-format-images' );

//Display reaction count
//add_filter( 'genesis_entry_header', 'comments_numbers', 12);
function comments_numbers() {
    return __(comments_number( '<h3>{ Geen reacties }</h3>', '<h3>{ 1 Reactie }</h3>', '<h3>{ % Reacties } </h3>' ), 'genesis' );
}

function check_mediatype($media, $tag) {
if ( strpos($tag, “bbpress.css”) !== false ) {
$media = array(“all”);
}
if ( strpos($tag, “buddypress.min.css”) !== false ) {
$media = array(“all”);
}
return $media;
}

// Deregister Contact Form 7 styles
add_action( 'wp_print_styles', 'aa_deregister_styles', 100 );
function aa_deregister_styles() {
    if ( ! is_page( array('contact', 'vacatures') ) ) {
        wp_deregister_style( 'contact-form-7' );
    }
}

// Deregister Contact Form 7 JavaScript files on all pages without a form
add_action( 'wp_print_scripts', 'aa_deregister_javascript', 100 );
function aa_deregister_javascript() {
    if ( ! is_page( array('contact', 'vacatures') ) ) {
        wp_deregister_script( 'contact-form-7' );
    }
}

//oembed comments
add_filter( 'comment_text', 'wpse_105942_oembed_comments', 0 );
function wpse_105942_oembed_comments( $comment )
{
    add_filter( 'embed_oembed_discover', '__return_false', 999 );

    $comment = $GLOBALS['wp_embed']->autoembed( $comment );

    remove_filter( 'embed_oembed_discover', '__return_false', 999 );

    return $comment;
}

/** Add post navigation (requires HTML5 support) */
// Previous/next post navigation.
//add_action( 'genesis_after_entry_content', 'auto_load_next_post_compatible_post_nav', 5 );
function auto_load_next_post_compatible_post_nav() {
	if ( is_single() ) {
		the_post_navigation( array(
			'next_text' => '<span class="meta-nav" aria-hidden="true">' . __( 'Next', 'twentyfifteen' ) . '</span> ' .
				'<span class="screen-reader-text">' . __( 'Next post:', 'twentyfifteen' ) . '</span> ' .
				'<span class="post-title">%title</span>',
			'prev_text' => '<span class="meta-nav" aria-hidden="true">' . __( 'Previous', 'twentyfifteen' ) . '</span> ' .
				'<span class="screen-reader-text">' . __( 'Previous post:', 'twentyfifteen' ) . '</span> ' .
			'<span class="post-title">%title</span>',
		) );
	}
}

/* On scroll progress bar */

function cc_progress_bar_script() {

	if(is_single()){

	wp_register_script('cc_progress_bar', plugins_url('js/cc_progress_bar.js', __FILE__), '', '1.0', true);
	wp_enqueue_script('cc_progress_bar');

	}

}

add_action( 'wp_enqueue_scripts', 'cc_progress_bar_script' );  

function cc_progress_bar(){

	echo '
		<div class="KW_progressContainer">
		<div class="KW_progressBar">

		</div>
	</div>';

}

add_action( 'genesis_after_header', 'cc_progress_bar');

//Upprev alter image used
add_filter( 'iworks_upprev_get_the_post_thumbnail' , 'change_thumbnail' );
function change_thumbnail( $image )
{
	if (is_single() && has_post_thumbnail()){
         $thumb = get_the_post_thumbnail_url( $post = null ,$size = 'medium');
		 $image = "<img src='" . $thumb . "' />";
    }
	return $image;
}

function ymc_add_meta_settings($comment_id) {
  add_comment_meta(
    $comment_id, 
    'mailchimp_subscribe', 
    $_POST['mailchimp_subscribe'], 
    true
  );
}
add_action ('comment_post', 'ymc_add_meta_settings', 1);

function ymc_add_subscribe_box (){
	echo '<label for="mailchimp_subscribe"></em><input type="checkbox" name="mailchimp_subscribe" id="mailchimp_subscribe" value="1" checked="checked"> Hou mij op de hoogte <em>100% spamvrij</label>';
}
add_action ('comment_form', 'ymc_add_subscribe_box');

function ymc_subscription_add( $comment_ID, $comment_approved, $commentdata ) {
	ini_set('display_errors', 'On');
	error_reporting(E_ALL);

  $comment_ID = (int) $comment_ID;
	
  if ( !is_object($commentdata) )
    $commentdata = get_comment($comment_ID);
		
  //if ( 1 === $comment_approved ) {
    $subscribe = get_comment_meta($comment_ID, 'mailchimp_subscribe', true);
	//if ( $subscribe == 'on' ) {
		$apikey   = '2c0977866849c25c409196eae164427f-us15';
		//$listid   = 'dad05ef456';
		$endpoint   = 'https://us15.api.mailchimp.com/3.0/lists/dad05ef456/members/';

		$request   = array(
		'apikey' => $apikey,
		//'id' => $listid,
		'email_address' => strtolower( $commentdata->comment_author_email ),
		'double_optin' => true,
		'status' => 'subscribed',
		'merge_fields' => array(
			'FNAME' => $commentdata->comment_author,
			'EMAIL' => strtolower( $commentdata->comment_author_email ),
			'SIGNUP' => "comments",
			'URL' => get_permalink($commentdata->comment_post_ID, trues)
		)
		);

		$opts = array(
		'headers' => array(
			'Content-Type' => 'application/json',
			'Authorization' => 'apikey ' . $apikey
		),
		'body' => json_encode($request)
		);
		wp_remote_post( $endpoint, $opts );
	//}
  //}
}

add_action('comment_post', 'ymc_subscription_add', 10 ,3);

/* Stop Adding Functions Below this Line */
?>