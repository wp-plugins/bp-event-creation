<?php

/**
 * Template for creating or editing Events on a member profile page
 * You can copy this file to your-theme/buddypress/members/single
 * and then edit the layout. 
 */

global $pp_ec;  // access to BP_Simple_Events_Create singleton
$required_fields = get_option( 'pp_events_required' );
?>

<form id="profile-event-form" name="profile-event-form" method="post" action="" class="standard-form" enctype="multipart/form-data">

	<p>
		<label for="event-title"><?php echo __( 'Title', 'bp-simple-events' ); ?>: *</label>
		<input type="text" id="event-title" name="event-title" value="<?php echo $pp_ec->title; ?>" />
	</p>

	<p>
		<label for="event-description"><?php echo __( 'Description', 'bp-simple-events' ); ?>: *</label>
		<textarea id="event-description" name="event-description" ><?php echo $pp_ec->description; ?></textarea>
	</p>

	<p>
		<label for="event-date"><?php echo __( 'Date', 'bp-simple-events' ); ?>: <?php if( in_array('date', $required_fields) ) echo __( '*', 'bp-simple-events' ); ?></label>
		<input type="text" id="event-date" name="event-date" placeholder="<?php echo __( 'Click to add Date...', 'bp-simple-events' ); ?>" value="<?php echo $pp_ec->date; ?>" />
	</p>

	<p>
		<label for="event-time"><?php echo __( 'Time', 'bp-simple-events' ); ?>: <?php if( in_array('time', $required_fields) ) echo __( '*', 'bp-simple-events' ); ?></label>
		<input type="text" id="event-time" name="event-time" placeholder="<?php echo __( 'Click to add Time...', 'bp-simple-events' ); ?>" value="<?php echo $pp_ec->time; ?>" />
	</p>

	<p>
		<label for="event-location"><?php echo __( 'Location', 'bp-simple-events' ); ?>: <?php if( in_array('location', $required_fields) ) echo __( '*', 'bp-simple-events' ); ?></label>
		<input type="text" id="event-location" name="event-location" placeholder="<?php echo __( 'Start typing location name...', 'bp-simple-events' ); ?>" value="<?php echo $pp_ec->address; ?>" />
	</p>

	<p>
		<label for="file">Filename:</label>
    	<input type="file" name="myfile" id="myfile">
	</p>

	<?php
		$args = array(
			'type'                     => 'post',
			'child_of'                 => 0, //get_cat_ID( 'Events' ),
			'parent'                   => '',
			'orderby'                  => 'name',
			'order'                    => 'ASC',
			'hide_empty'               => 0,
			'hierarchical'             => 1,
			'exclude'                  => '',
			'include'                  => '',
			'number'                   => '',
			'taxonomy'                 => 'category',
			'pad_counts'               => false
		);

		$categories = get_categories( $args );
	?>

	<?php if( ! empty( $categories ) ) : ?>

		<p>
			<label for="event-cats"><?php echo __( 'Categories', 'bp-simple-events' ); ?>: <?php if( in_array('categories', $required_fields) ) echo __( '*', 'bp-simple-events' ); ?></label>
			<?php
				foreach( $categories as $category ) {

					$checked = '';
					if( in_array( $category->term_id, $pp_ec->cats_checked ) )
						$checked = ' checked';

					echo '&nbsp;&nbsp;<input type="checkbox" name="event-cats[]" value="' . $category->term_id . '"' . $checked . '/> ' . $category->name . '<br/>';
				}
			?>
		</p>

	<?php endif; ?>	
	

	<input type="hidden" id="event-address" name="event-address" value="<?php echo $pp_ec->address; ?>" />
	<input type="hidden" id="event-latlng" name="event-latlng"  value="<?php echo $pp_ec->latlng; ?>" />
	<input type="hidden" name="action" value="event-action" />
	<input type="hidden" name="eid" value="<?php echo $pp_ec->post_id; ?>" />
	<?php wp_nonce_field( 'event-nonce' ); ?>

	<input type="submit" name="submit" class="button button-primary" value="<?php echo __(' SAVE ', 'bp-simple-events'); ?>"/>

</form>
	