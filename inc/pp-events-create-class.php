<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * create & edit events from member profile
 * file only required when loading the template:
 * templates\members\single\profile-events-create.php
 * in inc\pp-events-screen.php
 */

class PP_Simple_Events_Create {

	public $title = '';
	public $description = '';
	public $date = '';
	public $time = '';
	public $url = '';
	public $address = '';
	public $lnglat = '';
	public $cats = '';
	public $cats_checked = array();
	public $post_id = 0;
	public $editor = false;

	private $edit_permission = false;
	private $user_id = 0;
	private $errors = '';

    public function __construct() {

		if( ! bp_is_my_profile() && ! is_super_admin() )
			return;

		if( ! user_can( bp_displayed_user_id(), 'publish_events' ) )
			return;

		add_filter( 'bp_core_render_message_content', array( $this, 'message_format' ), 11, 2 );

		if( isset( $_GET['eid'] ) )
			$this->edit();

		$this->get_title();
		$this->get_description();
		$this->get_date();
		$this->get_time();
		$this->get_address();
		$this->get_url();
		$this->get_latlng();
		$this->get_cats_checked();

		$this->save();

	}

	private function edit() {

		if( ! isset( $_GET['edn'] ) || ! wp_verify_nonce( $_GET['edn'], 'editing' ) )
			echo 'Security Fail';
		else {

			$this->edit_permission_check( $_GET['eid'] );

			if( ! $this->edit_permission )
				echo 'You cannot edit this Event.';
			else {
				$post_object = get_post( $this->post_id );
				$this->title = $post_object->post_title;
				$this->description = $post_object->post_content;
				$this->cats_checked = wp_get_post_categories( $this->post_id );
				$this->editor = true;
			}
		}
	}

	private function edit_permission_check( $post_id) {

		$post_author_id = get_post_field( 'post_author', $post_id );

		if( $post_author_id != bp_displayed_user_id() )
			$this->edit_permission = false;
		else {
			$this->edit_permission = true;
			$this->post_id = $post_id;
		}

	}

	function get_title() {

		if( isset( $_POST['event-title'] ) && ! empty( $_POST['event-title'] ) )
			$this->title = stripslashes( $_POST['event-title'] );

	}

	function get_description() {

		if( isset( $_POST['event-description'] ) && ! empty( $_POST['event-description'] ) )
			$this->description = stripslashes( $_POST['event-description'] );

	}

	function get_date() {

		if( isset( $_POST['event-date'] ) && ! empty( $_POST['event-date'] ) )
			$date = $_POST['event-date'];
		else
			$date = get_post_meta( $this->post_id, 'event-date', true );

		$this->date = ! empty( $date ) ? $date : '';  //current_time( 'l, F j, Y' );

	}

	function get_time() {

		if( isset( $_POST['event-time'] ) && ! empty( $_POST['event-time'] ) )
			$time = $_POST['event-time'];
		else
			$time = get_post_meta( $this->post_id, 'event-time', true );

		$this->time = ! empty( $time ) ? $time : '';  //current_time( 'g:i a' );

	}

	function get_address() {

		if( isset( $_POST['event-address'] ) && ! empty( $_POST['event-address'] ) )
			$address = $_POST['event-address'];
		else
			$address = get_post_meta( $this->post_id, 'event-address', true );

		$this->address = ! empty( $address ) ? $address : '';

	}

	function get_latlng() {

		if( isset( $_POST['event-latlng'] ) && ! empty( $_POST['event-latlng'] ) )
			$latlng = $_POST['event-latlng'];
		else
			$latlng = get_post_meta( $this->post_id, 'event-latlng', true );

		$this->latlng = ! empty( $latlng ) ? $latlng : '';

	}

	function get_url() {

		if( isset( $_POST['event-url'] ) && ! empty( $_POST['event-url'] ) )
			$url = $_POST['event-url'];
		else
			$url = get_post_meta( $this->post_id, 'event-url', true );

		$this->url = ! empty( $url ) ? $url : '';

	}


	function get_cats_checked() {

		if( isset( $_POST['event-cats'] ) && ! empty( $_POST['event-cats'] ) )
			$this->cats_checked = $_POST['event-cats'];

	}




	function save() {

		if( bp_is_my_profile() || is_super_admin() )
			$this->user_id = bp_displayed_user_id();
		else {
			bp_core_add_message( __( 'Please use your own profile to create or edit Events.', 'bp-simple-events' ), 'error' );
			return;
		}

		if( 'POST' == $_SERVER['REQUEST_METHOD'] && !empty( $_POST['action'] ) &&  $_POST['action'] == "event-action" )
		 {

     /*     Upload Function     */			
			
			 			
			if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );
	$uploadedfile = $_FILES['myfile'];
	$upload_overrides = array( 'test_form' => false );
	$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
	
	if ( $movefile && !isset( $movefile['error'] )) 

		echo "file move successfully";
		$wp_filetype = $movefile['type'];
    	$filename = $movefile['file'];
    	$wp_upload_dir = wp_upload_dir();
     	$wp_upload_dir['url'];
    	
 	
 
			
			
			/*   End  Upload Function     */			
			
						
			check_admin_referer( 'event-nonce' );

			$this->check_required_fields();

			if( ! empty( $this->errors ) ) {

				$this->errors = 'These fields are required: ' . $this->errors;

				bp_core_add_message( $this->errors, 'error' );

			}

			else {

				$event = array(
					'post_title'	=>	wp_strip_all_tags( $_POST['event-title'] ),
					'post_content'	=>	$_POST['event-description'],
					'post_status'	=>	'publish',
					'post_type'		=>	'event',
					'post_author'   =>  $this->user_id,
					'guid' => $wp_upload_dir['url'] .'/'. basename( $filename ),
        			'post_mime_type' => $wp_filetype
				);

				if( ! empty( $_POST['eid'] ) ) {

					$this->edit_permission_check( $_POST['eid'] );

					if( $this->edit_permission ) {

						$event['ID'] = $this->post_id;

						$this->post_id = wp_update_post( $event );

					}
				}
				else
					$this->post_id = wp_insert_post( $event );


				if( $this->post_id != 0 ) {

					$this->save_event_meta();

					bp_core_add_message( __( 'Event was saved.', 'bp-simple-events' ) );

					bp_core_redirect( bp_core_get_user_domain( $this->user_id ) . '/events/' );

				}
			}
		}
	}


	private function check_required_fields() {

		if( $_POST['event-title'] == '' )
			$this->errors .= '# ' . __( 'Title', 'bp-simple-events' );

		if( $_POST['event-description'] == '' )
			$this->errors .= '# ' . __( 'Description', 'bp-simple-events' );

		$required_fields = get_option( 'pp_events_required' );

		if( empty( $_POST['event-date'] ) && in_array( 'date', $required_fields ) )
			$this->errors .= '# ' . __( 'Date', 'bp-simple-events' );

		if( empty( $_POST['event-time'] ) && in_array( 'time', $required_fields ) )
			$this->errors .= '# ' . __( 'Time', 'bp-simple-events' );

		if( empty( $_POST['event-location'] ) && in_array( 'location', $required_fields ) )
			$this->errors .= '# ' . __( 'Location', 'bp-simple-events' );

		if( empty( $_POST['event-url'] ) && in_array( 'url', $required_fields ) )
			$this->errors .= '# ' . __( 'Url', 'bp-simple-events' );

		if( empty( $_POST['event-cats'] ) && in_array( 'categories', $required_fields ) )
			$this->errors .= '# ' . __( 'Categories', 'bp-simple-events' );
		
	

	}


	function save_event_meta() {

		if( ! empty( $_POST['event-date'] ) ) {
			$this->date = sanitize_text_field( $_POST['event-date'] );
			update_post_meta( $this->post_id, 'event-date', $this->date );
		}

		if( ! empty( $_POST['event-time'] ) ) {
			$this->time = sanitize_text_field( $_POST['event-time'] );
			update_post_meta( $this->post_id, 'event-time', $this->time );
		}

		$this->save_timestamp();

		$this->save_location();

		$this->save_url();

		$this->save_cats();

	}


	/**
	 * A unix timestamp is needed for sorting based on Event date + time
	 * If the user entered non-valid text in the Date or Time field
	 * then use WP current_time to generate a timestamp based on timezone setting
	 * when the event is created.
	 */
	private function save_timestamp() {

		$date_flag = false;
		$date = date_parse( $this->date );

		if( $date["error_count"] == 0 && checkdate( $date["month"], $date["day"], $date["year"] ) )
			$date_flag = true;


		$time_flag = false;
		$time = date_parse( $this->time );

		if( $time["error_count"] == 0 )
			$time_flag = true;


		if( $date_flag && $time_flag ) {
			$date_time = $this->date . ' ' . $this->time;
			$timestamp = strtotime( $date_time );
		}
		elseif( $date_flag ) {
			$timestamp = strtotime( $this->date );
		}
		else {

			$event_unix = get_post_meta( $post_id, 'event-unix', true );

			if( ! empty( $event_unix ) )
				$timestamp = $event_unix;
			else
				$timestamp = current_time( 'timestamp' );
		}

		update_post_meta( $this->post_id, 'event-unix', $timestamp );

	}

	private function save_location() {

		if( ! empty( $_POST['event-location'] ) ) {

			if( ! empty( $_POST['event-address'] ) ) {

				$this->address = sanitize_text_field( $_POST['event-address'] );
				update_post_meta( $this->post_id, 'event-address', $this->address );

			}
			else
				delete_post_meta( $this->post_id, 'event-address' );

			if( ! empty( $_POST['event-latlng'] ) ) {

				$this->latlng = sanitize_text_field( $_POST['event-latlng'] );
				update_post_meta( $this->post_id, 'event-latlng', $this->latlng );

			}
			else
				delete_post_meta( $this->post_id, 'event-latlng' );

		}
		else {

			delete_post_meta( $this->post_id, 'event-address' );
			delete_post_meta( $this->post_id, 'event-latlng' );
		}

	}

	private function save_url() {

		if( ! empty( $_POST['event-url'] ) ) {

			$this->url = sanitize_text_field( $_POST['event-url'] );
			update_post_meta( $this->post_id, 'event-url', $this->url );

		}
		else
			delete_post_meta( $this->post_id, 'event-url' );
	}

	// save assigned categories
	private function save_cats() {

		if ( isset( $_POST['event-cats'] ) && ! empty( $_POST['event-cats'] ) ) {

			$cats = array();

			foreach ( $_POST['event-cats'] as $key => $value )
				$cats[] = $value;

			wp_set_post_terms($this->post_id, $cats, 'category');
		}

	}


	function message_format( $content, $type ) {

		$content = str_replace('#', '<br/>', $content);

		return $content;

	}


}  // end of PP_Simple_Events_Create

/**
 * this global is only used for this template
 * inc\templates\members\single\profile-events-create.php
 */

global $pp_ec;
$pp_ec = new PP_Simple_Events_Create();