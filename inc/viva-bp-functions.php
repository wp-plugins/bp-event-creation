<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// total events per member for Events profile tab
function pp_events_count_profile( $user_id = 0 ) {
	global $wpdb;

	if ( empty( $user_id ) )
		$user_id = bp_displayed_user_id();

	return $wpdb->get_var( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_author = $user_id AND post_type = 'event' AND post_status = 'publish'" );

}

// activity entries for new events
function pp_event_tracking_args_activity() {

    if ( ! bp_is_active( 'activity' ) )
        return;

    bp_activity_set_post_type_tracking_args( 'event', array(
        'component_id'             => 'events',
        'action_id'                => 'new_event',
        'bp_activity_admin_filter' => __( 'Events', 'bp-simple-events' ),
        'bp_activity_front_filter' => __( 'Events', 'bp-simple-events' ),
        'contexts'                 => array( 'activity', 'member', 'groups' ),
        'activity_comment'         => true,
        'bp_activity_new_post'     => __( '%1$s created a new <a href="%2$s">Event</a>', 'bp-simple-events' ),
        'bp_activity_new_post_ms'  => __( '%1$s created a new <a href="%2$s">Event</a>, on the site %3$s', 'bp-simple-events' ),
        'position'                 => 100,
    ) );
}
//add_action( 'bp_init', 'pp_event_tracking_args_activity' );


// pagination for Events loop page
function pp_events_pagination( $wp_query ) {

	$big = 999999999;

	$events_links = paginate_links( array(
		'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
		'format' => '?paged=%#%',
		'current' => max( 1, get_query_var('paged') ),
		'total' => $wp_query->max_num_pages
	) );

	return apply_filters( 'pp_events_pagination', $events_links );
}

// pagination for profile Events loop page
function pp_events_profile_pagination( $wp_query ) {

	$events_profile_page_links = paginate_links( array(
		'base' => add_query_arg( 'ep', '%#%' ),
		'format' => '',
		'total' => ceil( (int) $wp_query->found_posts / (int) get_query_var('posts_per_page') ),
		'current' => (int) get_query_var('paged'),
		//'prev_text' => '&larr;',
		//'next_text' => '&rarr;',
		//'mid_size' => 1
	) );

	return apply_filters( 'pp_events_profile_pagination', $events_profile_page_links );

}

// so event cpt is found on assigned cat archive page
function pp_event_query_post_type($query) {

	if( is_category() || is_tag() ) {
		$post_type = get_query_var('post_type');
		if($post_type)
			$post_type = $post_type;
		else
			$post_type = array( 'event', 'nav_menu_item');

		$query->set('post_type',$post_type);

		return $query;
	}

}
add_filter('pre_get_posts', 'pp_event_query_post_type');

// redirect when Event is trashed on front-end
function pp_event_trash_redirect(){
    if (is_404()){
        global $wp_query, $wpdb;
        $page_id = $wpdb->get_var( $wp_query->request );
        $post_status = get_post_status( $page_id );
        if($post_status == 'trash'){
            wp_redirect(site_url('/events/'), 301);
            die();
        }
    }
}
add_action('template_redirect', 'pp_event_trash_redirect');


/*
// cleanup when Event is permanently deleted - Note: not used, using 'trash' hook below
function pp_event_delete_cleanup( $postid ){
    global $post_type;

    if ( $post_type != 'event' )
        return;

	BP_Activity_Activity::delete( array( 'secondary_item_id' => $postid ) );

	//  delete notifications ? //	bp_notifications_delete_notifications_by_item_id
}
//add_action( 'before_delete_post', 'pp_event_delete_cleanup' );
*/

// cleanup when Event is trashed
function pp_event_trash_cleanup( $postid ){

	BP_Activity_Activity::delete( array( 'secondary_item_id' => $postid ) );

	/*
	// Notifications only supported in Pro version
	$user_id = get_post_field( 'post_author', $postid );
	$item_id = $postid;
	$component_name = 'events';
	$component_action = 'event_attender';
	
	bp_notifications_delete_notifications_by_item_id( $user_id, $item_id, $component_name, $component_action );
	*/

}
add_action( 'trash_event', 'pp_event_trash_cleanup' );


// turn Event > Url to a link
function pp_event_convert_url( $text, $scheme = 'http://' ) {

	$url = parse_url( $text, PHP_URL_SCHEME) === null ? $scheme . $text : $text;

	$disallowed = array('http://', 'https://');
	foreach( $disallowed as $d ) {
		if( strpos( $text, $d ) === 0 )
			$text = str_replace( $d, '', $text );
	}

	return apply_filters( 'pp_event_convert_url', '<a href="' . $url . '" rel="nofollow">' . $text . '</a>', $text );
}
