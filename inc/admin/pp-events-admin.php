<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * CPT admin metaboxes
 * Groups admin metabox
 */


class PP_Simple_Events_Admin {

    public function __construct() {

		add_action( 'admin_enqueue_scripts',            array( $this, 'events_scripts' ), 1000 );
		add_action( 'add_meta_boxes',                   array( $this, 'custom_meta_boxes' ) );
		add_action( 'save_post_event',                  array( $this, 'save_meta_boxes' ) );
		add_filter( 'manage_edit-event_columns',        array( $this, 'custom_columns_head' ), 10 );
		add_action( 'manage_event_posts_custom_column', array( $this, 'custom_columns_content' ), 10, 2 );

	}

	// add scripts & styles
	function events_scripts() {
		global $post_type;

		if( 'event' != $post_type )
			return;

		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_script('jquery-ui-timepicker', plugins_url( 'js/jquery.ui.timepicker.min.js' , dirname(__FILE__) ) );
		wp_enqueue_style( 'jquery-ui-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/themes/smoothness/jquery-ui.css', true);

		wp_enqueue_script('script', plugins_url( 'js/events.js' , dirname(__FILE__) ) );

		wp_register_script( 'google-places-api', 'http://maps.google.com/maps/api/js?libraries=places' );
		wp_print_scripts( 'google-places-api' );
	}

	
	// add event meta boxes
	function custom_meta_boxes() {
		global $post_type;

		if( 'event' != $post_type )
			return;

		add_meta_box('event_date_time',  __('Date / Time', 'bp-simple-events'), array( $this, 'date_time_show' ), 'event', 'normal', 'default');

		add_meta_box('event_location',  __('Location', 'bp-simple-events'), array( $this, 'location_show' ), 'event', 'normal', 'default');

		add_meta_box('event_url',  __('URL', 'bp-simple-events'), array( $this, 'url_show' ), 'event', 'normal', 'default');

	}


	// date & time metaboxes
	function date_time_show( $post ) {

		wp_nonce_field( 'date_time_box', 'date_time_box_nonce' );

		$date = get_post_meta( $post->ID, 'event-date', true );
		$time = get_post_meta( $post->ID, 'event-time', true );

		$date = ! empty( $date ) ? $date : current_time( 'l, F j, Y' );
		$time = ! empty( $time ) ? $time : current_time( 'g:i a' );
		?>

		<p>
			<label for="event-date"><?php echo __( 'Event Date:', 'bp-simple-events' ); ?></label>
			<input type="text" size="25" id="event-date" name="event-date" value="<?php echo $date; ?>" />
		</p>
		<p>
			<label for="event-time"><?php echo __( 'Event Time:', 'bp-simple-events' ); ?></label>
			<input type="text"  size="25" id="event-time" name="event-time"  value="<?php echo $time; ?>" />
		</p>
		<?php
	}


	// url metabox
	function url_show( $post ) {

		wp_nonce_field( 'url_box', 'url_box_nonce' );

		$date = get_post_meta( $post->ID, 'event-url', true );

		$url = ! empty( $date ) ? $date : '';
		?>

		<p>
			<label for="event-url"><?php echo __( 'Event Url:', 'bp-simple-events' ); ?></label>
			<input type="text" size="70" id="event-url" name="event-url" value="<?php echo $url; ?>" />
		</p>

		<?php
	}


	// location metabox
	function location_show( $post ) {

		wp_nonce_field( 'location_box', 'location_box_nonce' );

		$location = get_post_meta( $post->ID, 'event-address', true );
		$location = ! empty( $location ) ? $location : '';

		$latlng = get_post_meta( $post->ID, 'event-latlng', true );
		$latlng = ! empty( $latlng ) ? $latlng : '';

		if( ! empty( $location ) ) :
		?>
			<p>
				<label for="event-location"><?php echo __( 'Event Location:', 'bp-simple-events' ); ?></label>
				<input type="text" size="80" id="event-location" name="event-location" placeholder="<?php echo $location; ?>" />

		<?php else : ?>
			<p>
				<label for="event-location"><?php echo __( 'Event Location:', 'bp-simple-events' ); ?></label><br/>
				<input type="text" size="80" id="event-location" name="event-location" placeholder="Start typing location name..." />

		<?php endif; ?>

			<input type="hidden" id="event-address" name="event-address" value="<?php echo $location; ?>" />
			<input type="hidden" id="event-latlng" name="event-latlng"  value="<?php echo $latlng; ?>" />
		</p>

		<?php
	}





	function save_meta_boxes( $post_id ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;

		if ( ! current_user_can( 'manage_options', $post_id ) )
			return $post_id;

		$this->save_date_time( $post_id );
		$this->save_url( $post_id );
		$this->save_location( $post_id );

	}


	private function save_date_time( $post_id ) {

		if ( ! isset( $_POST['date_time_box_nonce'] ) )
			return $post_id;

		$nonce = $_POST['date_time_box_nonce'];

		if ( ! wp_verify_nonce( $nonce, 'date_time_box' ) )
			return $post_id;

		$date = sanitize_text_field( $_POST['event-date'] );
		update_post_meta( $post_id, 'event-date', $date );

		$time = sanitize_text_field( $_POST['event-time'] );
		update_post_meta( $post_id, 'event-time', $time );

		//$check_stamp = $date . ' ' . $time;
		$this->save_timestamp( $post_id, $date, $time );

	}


	/**
	 * A unix timestamp is needed for sorting based on Event date + time
	 * If the user entered non-valid text in the Date or Time field
	 * then use WP current_time to generate a timestamp based on timezone setting
	 * when the event is created.
	 */
	private function save_timestamp( $post_id, $event_date, $event_time ) {

		$date_flag = false;
		$date = date_parse($event_date);

		if ($date["error_count"] == 0 && checkdate($date["month"], $date["day"], $date["year"]))
			$date_flag = true;


		$time_flag = false;
		$time = date_parse($event_time);

		if ($time["error_count"] == 0 )
			$time_flag = true;


		if( $date_flag && $time_flag ) {
			$date_time = $event_date . ' ' . $event_time;
			$timestamp = strtotime( $date_time );
		}
		elseif( $date_flag ) {
			$timestamp = strtotime( $event_date );
		}
		else {

			$event_unix = get_post_meta( $post_id, 'event-unix', true );

			if( ! empty( $event_unix ) )
				$timestamp = $event_unix;
			else
				$timestamp = current_time( 'timestamp' );
		}

		update_post_meta( $post_id, 'event-unix', $timestamp );

	}

	private function save_url( $post_id ) {

		if ( ! isset( $_POST['url_box_nonce'] ) )
			return $post_id;

		$nonce = $_POST['url_box_nonce'];

		if ( ! wp_verify_nonce( $nonce, 'url_box' ) )
			return $post_id;

		$url = sanitize_text_field( $_POST['event-url'] );
		update_post_meta( $post_id, 'event-url', $url );

	}

	private function save_location( $post_id ) {

		if ( ! isset( $_POST['location_box_nonce'] ) )
			return $post_id;

		$nonce = $_POST['location_box_nonce'];

		if ( ! wp_verify_nonce( $nonce, 'location_box' ) )
			return $post_id;

		$address = sanitize_text_field( $_POST['event-address'] );
		update_post_meta( $post_id, 'event-address', $address );

		$latlng = sanitize_text_field( $_POST['event-latlng'] );
		update_post_meta( $post_id, 'event-latlng', $latlng );

	}





	// add custom columns
	function custom_columns_head( $defaults ) {

		unset( $defaults['date'] );

		$defaults['event_date'] = __( 'Date', 'bp-simple-events' );
		$defaults['event_location'] = __( 'Location', 'bp-simple-events' );

		return $defaults;
	}

	// add content to custom columns
	function custom_columns_content( $column_name, $post_id ) {

		if ( 'event_date' == $column_name ) {
			$date = get_post_meta( $post_id, 'event-date', true );
			$time = get_post_meta( $post_id, 'event-time', true );
			echo $date . '<br/>' . $time;

		}

		if ( 'event_location' == $column_name ) {
			$location = get_post_meta( $post_id, 'event-address', true );
			echo $location;
		}
	}



	/**
	 * Create & save metabox on single group screen
	 */

	function add_group_metabox() {

		add_meta_box( 'bp_group_events', _x( 'Group Events', 'group admin edit screen', 'bp-simple-events' ),  array( $this, 'show_group_metabox'), get_current_screen()->id, 'side' );

	}

	function show_group_metabox() {
		$group_id = isset( $_REQUEST['gid'] ) ? (int) $_REQUEST['gid'] : '';
	?>

		<div id="bp_groups_events" class="postbox">
			<div class="inside">
				<input type="checkbox" name="pp-events-assignable" id="pp-events-assignable" value="1"<?php $this->group_assignable_setting( $group_id ); ?> /> <?php _e( 'Allow group members to assign Events to this group.', 'bp-simple-events' ); ?>
			</div>
		</div>

	<?php
	}

	function save_group_metabox( $group_id ) {

		if ( ! empty( $_POST['pp-events-assignable'] ) )
			groups_update_groupmeta( $group_id, 'pp-events-assignable', '1' );
		else
			groups_delete_groupmeta( $group_id, 'pp-events-assignable' );

	}

	private function group_assignable_setting( $group_id ) {

		if ( groups_get_groupmeta( $group_id, 'pp-events-assignable' ) )
			echo ' checked="checked"';

	}

} // end of PP_Simple_Events_Admin class

$pp_se_admin_instance = new PP_Simple_Events_Admin();

