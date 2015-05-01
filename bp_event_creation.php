<?php
/*
Plugin Name: BP Event Creation
Description: An Events plugin for BuddyPress
Version: 1.0
Author: http://vivacityinfotech.com/
Author Email: support@vivacityinfotech.net

Copyright 2014  Vivacity InfoTech Pvt. Ltd.  (email : support@vivacityinfotech.net)
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

	 You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( !defined( 'ABSPATH' ) ) exit;


function pp_events_bp_check() {
	if ( !class_exists('BuddyPress') ) {
		add_action( 'admin_notices', 'pp_events_install_buddypress_notice' );
	}
}
add_action('plugins_loaded', 'pp_events_bp_check', 999);

function pp_events_install_buddypress_notice() {
	echo '<div id="message" class="error fade"><p style="line-height: 150%">';
	_e('<strong>Bp Events</strong></a> requires the BuddyPress plugin. Please <a href="http://buddypress.org/download">install BuddyPress</a> first, or <a href="plugins.php">deactivate BuddyPress Simple Events</a>.');
	echo '</p></div>';
}

/////////////////////////////////////////// ajax files/////////////////

 
/**************my ajax file calling functions***************/

function viva_bp_event_ajax()
{
	wp_enqueue_script('attend', plugins_url('js/attend.js', __FILE__));
	wp_localize_script('attend', 'my_ajax_script', array( 'ajaxurl' => admin_url('admin-ajax.php' ) ) );
}
add_action('wp_enqueue_scripts', 'viva_bp_event_ajax');

add_action("wp_ajax_nopriv_post_attend_method", "post_attend_method");
add_action("wp_ajax_post_attend_method", "post_attend_method");
function post_attend_method()
 			{
				$post_id = $_POST['post_id'];
    			$author_name  = $_POST['author_name'];
    			// Add the meta field in the database.
					add_post_meta($post_id ,'attend_key' , $author_name);	
					echo " Thanks to show affection to Attend this event. ";
					wp_die();
			} 
		

function pp_events_init() {

	$vcheck = pp_events_version_check();

	if( $vcheck ) {

		define( 'PP_EVENTS_DIR', dirname( __FILE__ ) );

		load_plugin_textdomain( 'bp-simple-events', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		require( dirname( __FILE__ ) . '/inc/viva-bp-core.php' );

	}

}
add_action( 'bp_include', 'pp_events_init' );



function pp_events_activation() {

	$vcheck = pp_events_version_check();

	if( $vcheck ) {

		pp_add_event_caps();

		pp_create_post_type_event();

		pp_create_events_page();

		pp_create_events_options();

		flush_rewrite_rules();
	}
}
register_activation_hook(__FILE__, 'pp_events_activation');


function pp_events_deactivation () {
	pp_remove_event_caps();

}
register_deactivation_hook(__FILE__, 'pp_events_deactivation');


function pp_events_uninstall () {
	delete_option( 'pp_events_tab_position' );
	delete_option( 'pp_events_required' );
}

register_uninstall_hook( __FILE__, 'pp_events_uninstall');


function pp_events_version_check() {

	if ( ! defined( 'BP_VERSION' ) )
		return false;

	if( version_compare( BP_VERSION, '2.2', '>=' ) )
		return true;
	else {
		echo '<div id="message" class="error">BP Events requires at least version 2.2 of BuddyPress.</div>';
		return false;
	}
}


function pp_create_events_options() {

	// tab position on profile pages
	add_option( 'pp_events_tab_position', '201', '', 'no' );

	//default required fields
	add_option( 'pp_events_required', array(), '', 'no' );
}


function pp_create_events_page() {

    $page = get_page_by_path('events');

    if( ! $page ){
		$events_page = array(
		  'post_title'    => 'Events',
		  'post_name'     => 'events',
		  'post_status'   => 'publish',
		  'post_author'   => get_current_user_id(),
		  'post_type'     => 'page'
		);

		$post_id = wp_insert_post( $events_page, true );
    }

}


function pp_create_post_type_event() {

	if ( ! defined( 'BP_VERSION' ) )
		return;

	register_post_type( 'event',
		array(
		  'labels' => array(
			'name' => __( 'Events' ),
			'singular_name' => __( 'Event' ),
			'add_new' => __( 'Add New' ),
			'add_new_item' => __( 'Add New Event' ),
			'edit' => __( 'Edit' ),
			'edit_item' => __( 'Edit Event' ),
			'new_item' => __( 'New Event' ),
			'view' => __( 'View Events' ),
			'view_item' => __( 'View Event' ),
			'search_items' => __( 'Search Events' ),
			'not_found' => __( 'No Events found' ),
			'not_found_in_trash' => __( 'No Events found in Trash' ),
            'bp_activity_admin_filter' => __( 'Events', 'bp-simple-events' ),
            'bp_activity_front_filter' => __( 'Events', 'bp-simple-events' ),
            'bp_activity_new_post'     => __( '%1$s created a new <a href="%2$s">Event</a>', 'bp-simple-events' ),
            'bp_activity_new_post_ms'  => __( '%1$s created a new <a href="%2$s">Event</a>, on the site %3$s', 'bp-simple-events' ),

		),
		'public' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'event' ),
		'capability_type' => array('event', 'events'),
		'exclude_from_search' => false,
		'has_archive' => true,
		'map_meta_cap' => true,
		'hierarchical' => false,
		"supports"	=> array("title", "editor", "thumbnail", "author", "comments", "trackbacks", "buddypress-activity"),
        'bp_activity' => array(
            'component_id'          => buddypress()->activity->id,
            'action_id'             => 'new_event',
            'contexts'              => array( 'activity', 'member', 'groups', 'member-groups' ),
            'position'              => 70,
        ),
		'taxonomies' => array('category'),
		)
	);
	register_taxonomy_for_object_type('category', 'event');

}
add_action( 'init', 'pp_create_post_type_event' );


function pp_add_event_caps() {

	$role = get_role( 'administrator' );
	$role->add_cap( 'delete_published_events' );
	$role->add_cap( 'delete_others_events' );
	$role->add_cap( 'delete_events' );
	$role->add_cap( 'edit_others_events' );
	$role->add_cap( 'edit_published_events' );
	$role->add_cap( 'edit_events' );
	$role->add_cap( 'publish_events' );

}

function pp_remove_event_caps() {
	global $wp_roles;

	$all_roles = $wp_roles->roles;

	foreach( $all_roles as $key => $value ){

		$role = get_role( $key );

		$role->remove_cap( 'delete_published_events' );
		$role->remove_cap( 'delete_others_events' );
		$role->remove_cap( 'delete_events' );
		$role->remove_cap( 'edit_others_events' );
		$role->remove_cap( 'edit_published_events' );
		$role->remove_cap( 'edit_events' );
		$role->remove_cap( 'publish_events' );

	}
}
?>