<?php

/**
 * Template for displaying the Events Loop
 * You can copy this file to your-theme
 * and then edit the layout. 
 */

get_header();

$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

$args = array(
	'post_type'      => 'event',
	'order'          => 'ASC',
	'orderby'		 => 'meta_value_num',
	'meta_key'		 => 'event-unix',
	'paged'          => $paged,
	'posts_per_page' => 10,

	'meta_query' => array(
		array(
			'key'		=> 'event-unix',
			'value'		=> current_time( 'timestamp' ),
			'compare'	=> '>=',
			'type' 		=> 'NUMERIC',
		),
	),

);

$wp_query = new WP_Query( $args );
?>

<div id="primary" class="content-area">
	<div id="content" class="site-content" role="main">

		<?php if ( $wp_query->have_posts() ) : ?>

			<div class="entry-content"><br/>
				<?php echo pp_events_pagination( $wp_query ); ?>
			</div>

			<?php while ( $wp_query->have_posts() ) : $wp_query->the_post(); 	?>

				<div class="entry-content">
					<br/>

					<h2 class="entry-title">
						<a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>">
						<?php the_title(); ?></a>
					</h2>

					<?php
					$author_id = get_the_author_meta('ID');
					$author_name = get_the_author_meta('display_name');
					?>

					<a href="<?php echo bp_core_get_user_domain( $author_id ); ?>">
					<?php echo bp_core_fetch_avatar( array( 'item_id' => $author_id ) ); ?>
					&nbsp;<?php echo $author_name; ?></a>


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
			<?php echo pp_events_pagination( $wp_query ); ?>
		</div>

		<?php else : ?>

			<div class="entry-content"><br/>There are no upcoming Events.</div>

		<?php endif; ?>


		<?php wp_reset_postdata(); ?>

	</div><!-- #content -->
</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>