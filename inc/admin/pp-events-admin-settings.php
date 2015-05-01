<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Settings Page class
 */


class PP_Simple_Events_Admin_Settings {

	private $roles_message = '';
	private $settings_message = '';

    public function __construct() {

		if ( is_multisite() ) {
		
			if ( ! function_exists( 'is_plugin_active_for_network' ) )
			    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
    
        }
        				
        if ( is_multisite() && is_plugin_active_for_network( 'Bp-Events/bp_event.php' ) ) 
			add_action('network_admin_menu', array( $this, 'multisite_admin_menu' ) );
		else
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );

	}

	
	function admin_menu() {
		add_options_page(  __( 'Viva BP Events', 'bp-simple-events'), __( 'Viva BP Events', 'bp-simple-events' ), 'manage_options', 'bp-simple-events', array( $this, 'settings_admin_screen' ) );
	}

	function multisite_admin_menu() {
		add_submenu_page( 'settings.php', __( 'Viva BP Events', 'bp-simple-events'), __( 'Viva BP Events', 'bp-simple-events' ), 'manage_options', 'bp-simple-events', array( $this, 'settings_admin_screen' ) );
	}	

	
	function settings_admin_screen(){
		global $wp_roles;

		if( !is_super_admin() )
			return;

		$this->roles_update();
		$this->settings_update();

		$all_roles = $wp_roles->roles;
		?>

		<h3>Settings</h3>

		<table border="0" cellspacing="10" cellpadding="10">
		<tr>
		<td style="vertical-align:top;" >

			<h3><?php echo __('Assign User Roles', 'bp-simple-events'); ?></h3>
			<?php echo $this->roles_message; ?>
			<em><?php echo __('Which roles can create Events?', 'bp-simple-events'); ?></em><br/>
			<form action="" name="access-form" id="access-form"  method="post" class="standard-form">

			<?php wp_nonce_field('allowedroles-action', 'allowedroles-field'); ?>

			<ul id="pp-user_roles">

			<?php foreach(  $all_roles as $key => $value ){

				if( $key == 'administrator' ) :
				?>

					<li><label><input type="checkbox" id="admin-preset-role" name="admin-preset" checked="checked" disabled /> <?php echo ucfirst($key); ?></label></li>

				<?php else:

					if( array_key_exists('publish_events', $value["capabilities"]) )
						$checked = ' checked="checked"';
					else
						$checked = '';

				?>

					<li><label for="allow-roles-<?php echo $key ?>"><input id="allow-roles-<?php echo $key ?>" type="checkbox" name="allow-roles[]" value="<?php echo $key ?>" <?php echo  $checked ; ?> /> <?php echo ucfirst($key); ?></label></li>

				<?php endif;

			}?>

			</ul>

			<input type="hidden" name="role-access" value="1"/>
			<input type="submit" name="submit" class="button button-primary" value="<?php echo __('Save Roles', 'bp-simple-events'); ?>"/>
			</form>

		</td>

		</tr></table>
	<?php
	}


	//  save any changes to role access options
	private function roles_update() {
		global $wp_roles;

		if( isset( $_POST['role-access'] ) ) {

			if( !wp_verify_nonce($_POST['allowedroles-field'],'allowedroles-action') )
				die('Security check');

			if( !is_super_admin() )
				return;

			$updated = false;

			$all_roles = $wp_roles->roles;

			if( is_multisite() && is_network_admin() ) {
			    //apply_caps_to_blog
				global $current_site,$wpdb;
				$blog_ids = $wpdb->get_col('SELECT blog_id FROM '.$wpdb->blogs.' WHERE site_id='.$current_site->id);
				foreach($blog_ids as $blog_id){
					switch_to_blog($blog_id);
				    //normal blog role application
					foreach(  $all_roles as $key => $value ){
		
						if( 'administrator' != $key ) {
		
							$role = get_role( $key );
		
							$role->remove_cap( 'delete_published_events' );
							$role->remove_cap( 'delete_events' );
							$role->remove_cap( 'edit_published_events' );
							$role->remove_cap( 'edit_events' );
							$role->remove_cap( 'publish_events' );
		
							$updated = true;
						}
					}
					
					if( isset( $_POST['allow-roles'] ) ) {
		
						foreach( $_POST['allow-roles'] as $key => $value ){
		
							if( array_key_exists($value, $all_roles ) ) {
		
								if( 'administrator' != $value ) {
		
									$role = get_role( $value );
									$role->add_cap( 'delete_published_events' );
									$role->add_cap( 'delete_events' );
									$role->add_cap( 'edit_published_events' );
									$role->add_cap( 'edit_events' );
									$role->add_cap( 'publish_events' );
		
								}
							}
						}
		
					}					
					
					restore_current_blog();
				}
			}	
			
			// not multisite		
			else { 
				foreach(  $all_roles as $key => $value ){
	
					if( 'administrator' != $key ) {
	
						$role = get_role( $key );
	
						$role->remove_cap( 'delete_published_events' );
						$role->remove_cap( 'delete_events' );
						$role->remove_cap( 'edit_published_events' );
						$role->remove_cap( 'edit_events' );
						$role->remove_cap( 'publish_events' );
	
						$updated = true;
					}
				}
	
	
				if( isset( $_POST['allow-roles'] ) ) {
	
					foreach( $_POST['allow-roles'] as $key => $value ){
	
						if( array_key_exists($value, $all_roles ) ) {
	
							if( 'administrator' != $value ) {
	
								$role = get_role( $value );
								$role->add_cap( 'delete_published_events' );
								$role->add_cap( 'delete_events' );
								$role->add_cap( 'edit_published_events' );
								$role->add_cap( 'edit_events' );
								$role->add_cap( 'publish_events' );
	
							}
						}
					}
	
				}
			}

			if( $updated )
				$this->roles_message .=
					"<div class='updated below-h2'>" .
					__('User Roles have been updated.', 'bp-simple-events') .
					"</div>";
			else
				$this->roles_message .=
					"<div class='updated below-h2' style='color: red'>" .
					__('No changes were detected re User Roles.', 'bp-simple-events') .
					"</div>";
		}
	}

	//  save any changes to settings options
	private function settings_update() {

		if( isset( $_POST['settings-access'] ) ) {

			if( !wp_verify_nonce($_POST['settings-field'],'settings-action') )
				die('Security check');

			if( !is_super_admin() )
				return;

			if( ! empty( $_POST['pp-tab-position'] ) ) {

				 if( is_numeric( $_POST['pp-tab-position'] ) )
				    $tab_value = $_POST['pp-tab-position'];
				else
					$tab_value = 52;
			}
			else
				$tab_value = 52;

			update_option( 'pp_events_tab_position', $tab_value );


			delete_option( 'pp_events_required' );
			$required_fields = array();
			if( ! empty( $_POST['pp-required'] ) ) {
				foreach ( $_POST['pp-required'] as $value )
					$required_fields[] = $value;
			}
			update_option( 'pp_events_required', $required_fields );


			$this->settings_message .=
				"<div class='updated below-h2'>" .
				__('Settings have been updated.', 'bp-simple-events') .
				"</div>";
		}
	}

} // end of PP_Simple_Events_Admin_Settings class

$pp_se_admin_settings_instance = new PP_Simple_Events_Admin_Settings();