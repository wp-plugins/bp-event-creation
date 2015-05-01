<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class PP_Events_Component extends BP_Component {

	function __construct() {
		global $bp;
		parent::start('events',	__('Events', 'bp-simple-events'), PP_EVENTS_DIR);
		$this->includes();
		$bp->active_components[$this->id] = '1';
	}

	function includes( $includes = array() ) {

		if( ! is_admin() ) {

			$includes = array(
				'inc/viva-bp-functions.php',
				'inc/viva-bp-templates.php',
				'inc/viva-bp-screens.php',
				'inc/pp-events-widget.php'
			);

		}
		else {

			$includes = array(
		        'inc/admin/pp-events-admin.php',
				'inc/admin/pp-events-admin-settings.php',
				'inc/viva-bp-functions.php',
				'inc/viva-bp-widget.php'
			);

		}

		parent::includes( $includes );

	}

	function setup_globals( $args = array() ) {

		$bp = buddypress();

		if ( !defined( 'PP_EVENTS_SLUG' ) )
			define( 'PP_EVENTS_SLUG', $this->id );

		$globals = array(
			'slug'                  => PP_EVENTS_SLUG,
			'root_slug'             => isset( $bp->pages->{$this->id}->slug ) ? $bp->pages->{$this->id}->slug : PP_EVENTS_SLUG,
			'has_directory'         => true,
			'directory_title'       => __( 'Events', 'bp-simple-events' ),
			'search_string'         => sprintf(__( 'Search %s...', 'bp_simple_events' ),__('Events','bp_simple_events')),
		);

		parent::setup_globals( $globals );

	}

	function setup_nav( $main_nav = array(), $sub_nav = array() ) {

		if ( ! current_user_can('publish_events') )
			return;	
	
		$user_has_access = false;
		if( bp_is_my_profile() || is_super_admin() )
			$user_has_access = true;

		$tab_position = get_option( 'pp_events_tab_position' );
		$count        = pp_events_count_profile();
		$class        = ( 0 === $count ) ? 'no-count' : 'count';


		bp_core_new_nav_item( array(
			'name'                => sprintf( __( 'Events <span class="%s">%s</span>', 'bp-simple-events' ), esc_attr( $class ), number_format_i18n( $count ) ),
			'slug'                => 'events',
			'position'            => $tab_position,
			'screen_function'     => 'pp_events_profile',
			'default_subnav_slug' => 'upcoming',
			'item_css_id'         => 'member-events'
		) );

		bp_core_new_subnav_item( array(
			'name'              => 'Upcoming',
			'slug'              => 'upcoming',
			'parent_url'        => trailingslashit( bp_displayed_user_domain() . 'events' ),
			'parent_slug'       => 'events',
			'screen_function'   => 'pp_events_profile',
			'position'          => 20,
			'item_css_id'       => 'member-events-upcoming'
			//'user_has_access'   => $user_has_access
			)
		);


		bp_core_new_subnav_item( array(
			'name'              => 'Archive',
			'slug'              => 'archive',
			'parent_url'        => trailingslashit( bp_displayed_user_domain() . 'events' ),
			'parent_slug'       => 'events',
			'screen_function'   => 'pp_events_profile_archive',
			'position'          => 25,
			'item_css_id'       => 'member-events-archive'
			//'user_has_access'   => $user_has_access
			)
		);


		bp_core_new_subnav_item( array(
			'name'              => 'Create Event',
			'slug'              => 'create',
			'parent_url'        => trailingslashit( bp_displayed_user_domain() . 'events' ),
			'parent_slug'       => 'events',
			'screen_function'   => 'pp_events_profile_create',
			'position'          => 30,
			'item_css_id'       => 'member-events-create',
			'user_has_access'   => $user_has_access
			)
		);

		parent::setup_nav( $main_nav, $sub_nav );

	}

	function setup_admin_bar( $wp_admin_nav = array() ) {
		$bp = buddypress();

		if ( ! current_user_can('publish_events') )
			return;

		if ( is_user_logged_in() ) {
			$user_domain = bp_loggedin_user_domain();
			$item_link = trailingslashit( $user_domain . 'events' );

			$wp_admin_nav[] = array(
				'parent' => $bp->my_account_menu_id,
				'id'     => 'my-account-events',
				'title'  => __( 'Events',  'bp-simple-events' ),
				'href'   => trailingslashit( $item_link ),
				'meta'   => array( 'class' => 'menupop' )
			);

			// submenu
			$wp_admin_nav[] = array(
				'parent' => 'my-account-events',
				'id'     => 'my-account-events-upcoming',
				'title'  => __( 'Upcoming', 'bp-simple-events' ),
				'href'   => trailingslashit( $item_link ) . 'upcoming'
			);

			// submenu
			$wp_admin_nav[] = array(
				'parent' => 'my-account-events',
				'id'     => 'my-account-events-archive',
				'title'  => __( 'Archive', 'bp-simple-events' ),
				'href'   => trailingslashit( $item_link ) . 'archive'
			);

			// submenu
			$wp_admin_nav[] = array(
				'parent' => 'my-account-events',
				'id'     => 'my-account-events-create',
				'title'  => __( 'Create', 'bp-simple-events' ),
				'href'   => trailingslashit( $item_link ) . 'create'
			);

		}

		parent::setup_admin_bar( $wp_admin_nav );
	}

}
function pp_events_load_core_component() {
	global $bp;
	$bp->events = new PP_Events_Component();
}
add_action( 'bp_loaded', 'pp_events_load_core_component' );