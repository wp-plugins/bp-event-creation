<?php

/**
 * Template for looping through expired Events on a member profile page
 * You can copy this file to your-theme/buddypress/members/single
 * and then edit the layout. 
 */

$paged = ( isset( $_GET['ep'] ) ) ? $_GET['ep'] : 1;

$args = array(
	'post_type'      => 'event',
	'author'         => bp_displayed_user_id(),
	'order'          => 'ASC',
	'orderby'		 => 'meta_value_num',
	'meta_key'		 => 'event-unix',
	'paged'          => $paged,
	'posts_per_page' => 10,

	'meta_query' => array(
		array(
			'key'		=> 'event-unix',
			'value'		=> current_time( 'timestamp' ),
			'compare'	=> '<=',
			'type' 		=> 'NUMERIC',
		),
	),

);

$wp_query = new WP_Query( $args );

$user_link = bp_core_get_user_domain( bp_displayed_user_id() );

?>

<?php if ( $wp_query->have_posts() ) : ?>

	<div class="entry-content"><br/>
		<?php echo pp_events_profile_pagination( $wp_query ); ?>
	</div>

	<?php while ( $wp_query->have_posts() ) : $wp_query->the_post(); 	?>

		<div class="entry-content">
			<br/>
			<h2 class="entry-title">
				<a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>">
				<?php the_title(); ?></a>
			</h2>

			<?php
			if( bp_is_my_profile() || is_super_admin() ) {

				$edit_link = wp_nonce_url( $user_link . 'events/create?eid=' . $post->ID, 'editing', 'edn');

				$delLink = get_delete_post_link( $post->ID );

			?>

				<span class="edit"><a href="<?php echo $edit_link; ?>" title="Edit  Event">Edit</a></span>
				&nbsp; &nbsp;
				<span class="trash"><a onclick="return confirm('Are you sure you want to delete this Event?')" href="<?php echo $delLink; ?>" title="Delete Event" class="submit">Delete</a></span>

			<?php } ?>

			<?php the_excerpt(); ?>

			<?php
			if ( has_post_thumbnail() ) {
				the_post_thumbnail( 'thumbnail' );
				echo '<br/>';
			}
			?>

			<?php
			$meta = get_post_meta($post->ID );

			if( ! empty( $meta['event-date'][0] ) )
				echo __( 'Date', 'bp-simple-events' ) . ':&nbsp;' . $meta['event-date'][0];

			if( ! empty( $meta['event-time'][0] ) )
				echo '<br/>' . __( 'Time', 'bp-simple-events' ) . ':&nbsp;' . $meta['event-time'][0];

			if( ! empty( $meta['event-address'][0] ) )
				echo '<br/>' . __( 'Location', 'bp-simple-events' ) . ':&nbsp;' . $meta['event-address'][0];

			if( ! empty( $meta['event-url'][0] ) )
				echo '<br/>' . __( 'Url', 'bp-simple-events' ) . ':&nbsp;' . pp_event_convert_url( $meta['event-url'][0] );

			?>

			<br/>
			Category: <?php the_category(', ') ?>


		</div><!-- .entry-content -->

	<?php endwhile; ?>

	<div class="entry-content"><br/>
		<?php echo pp_events_profile_pagination( $wp_query ); ?>
	</div>


	<?php wp_reset_query(); ?>

<?php else : ?>

	<div class="entry-content"><br/>There are no expired Events for this member.</div>


<?php endif; ?>
