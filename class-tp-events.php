<?php
/**
 * Plugin Name: Events
 * Description: Events for WordPress.
 *
 * Plugin URI: https://github.com/trendwerk/events
 * 
 * Author: Trendwerk
 * Author URI: https://github.com/trendwerk
 * 
 * Version: 1.0.1
 */

include_once( 'assets/inc/class-tp-event-settings.php' );

class TP_Events {
	var $post_type = 'events';

	function __construct() {
		add_action( 'plugins_loaded', array( $this, 'localization' ) );
		add_action( 'init', array( $this, 'setup' ) );
		add_filter( 'query_vars', array( $this, 'query_vars' ) );
		add_action( 'pre_get_posts', array( $this, 'query' ) );

		add_filter( 'views_edit-' . $this->post_type, array( $this, 'views' ) );
		add_filter( 'post_date_column_time', array( $this, 'event_time' ), 10, 3 );
	}

	/**
	 * Load localization
	 */
	function localization() {
		load_muplugin_textdomain( 'events', dirname( plugin_basename( __FILE__ ) ) . '/assets/lang/' );
	}

	/**
	 * Setup
	 */
	function setup() {
		/**
		 * Post type
		 */
		$args = apply_filters( 'events_post_type', array(
			'labels'            => array(
				'name'          => __( 'Events', 'events' ),
				'singular_name' => __( 'Event', 'events' ),
				'add_new'       => __( 'Add event', 'events' ),
				'add_new_item'  => __( 'Add event', 'events' ),
				'edit_item'     => __( 'Edit event', 'events' ),
			),
			'public'            => true,
			'has_archive'       => true,
			'menu_icon'         => 'dashicons-calendar',
			'menu_position'     => 20,
			'rewrite'           => array(
				'slug'          => __( 'events', 'events' ),
			),
			'supports'          => array( 'title', 'editor', 'thumbnail', 'revisions' ),
		) );

		register_post_type( $this->post_type, $args );

		/**
		 * Archive
		 */
		global $wp_rewrite;

		$archive_slug = apply_filters( 'events_archive_slug', __( 'archive', 'events' ) );

		add_rewrite_rule( '^' . $args['rewrite']['slug'] . '/' . $archive_slug . '/?$', 'index.php?post_type=' . $this->post_type . '&events_archive=true', 'top' );
		add_rewrite_rule( '^' . $args['rewrite']['slug'] . '/' . $archive_slug . '/' . $wp_rewrite->pagination_base . '/([0-9]+)/?$', 'index.php?post_type=' . $this->post_type . '&paged=$matches[1]&events_archive=true', 'top' );
	}

	/**
	 * Query vars
	 */
	function query_vars( $vars ) {
		$vars[] = 'events_archive';

		return $vars;
	}

	/**
	 * Main query
	 */
	function query( $query ) {
		if( ! $query->is_main_query() )
			return;

		if( $query->is_admin && $query->get( 'post_status' ) )
			return;

		if( ! $query->is_post_type_archive( $this->post_type ) )
			return;

		$query->set( 'meta_key', '_start' );
		$query->set( 'orderby', 'meta_value_num' );

		if( 'true' !== $query->get( 'events_archive' ) ) {
			/**
			 * Upcoming
			 */
			$query->set( 'order', 'ASC' );

			$query->set( 'meta_query', array(
				array(
					'key'     => '_start',
					'value'   => time(),
					'compare' => '>',
				)
			) );
		} else {
			/**
			 * Archive
			 */
			$query->set( 'order', 'DESC' );

			$query->set( 'meta_query', array(
				array(
					'key'     => '_start',
					'value'   => time(),
					'compare' => '<',
				)
			) );
		}
	}

	/**
	 * Admin views
	 */
	function views( $views ) {
		unset( $views['all'] );

		global $wp_query;

		/**
		 * Upcoming
		 */
		$class = empty( $_GET['events_archive'] ) && empty( $class ) && empty( $_REQUEST['post_status'] ) && empty( $_REQUEST['show_sticky'] ) ? ' class="current"' : '';
		$total_events = count( $this->get_events() );

		$upcoming = array( 'upcoming' => '<a href="edit.php?post_type=' . $this->post_type . '"' . $class . '>' . __( 'Upcoming', 'events' ) . ' <span class="count">(' . $total_events . ')</span></a>' );

		/**
		 * Archive
		 */
		$class = ! empty( $_GET['events_archive'] ) && empty( $class ) && empty( $_REQUEST['post_status'] ) && empty( $_REQUEST['show_sticky'] ) ? ' class="current"' : '';
		$total_events = count( $this->get_events( 'archive' ) );

		$archive = array( 'archive' => '<a href="edit.php?post_type=' . $this->post_type . '&events_archive=true"' . $class . '>' . __( 'Archive', 'events' ) . ' <span class="count">(' . $total_events . ')</span></a>' );

		return $upcoming + $archive + $views;
	}

	/**
	 * Get events
	 *
	 * @param string $status Upcoming or events from archive
	 */
	function get_events( $status = 'upcoming' ) {
		$args = array(
			'post_type'     => $this->post_type,
			'meta_key'      => '_start',
			'orderby'       => 'meta_value_num',
			'meta_query'    => array(
				array(
					'key'   => '_start',
					'value' => time(),
				),
			),
		);

		if( 'upcoming' === $status ) {
			$args['order'] = 'ASC';
			$args['meta_query'][0]['compare'] = '>';
		} else {
			$args['order'] = 'DESC';
			$args['meta_query'][0]['compare'] = '<';
		}

		return get_posts( $args );
	}

	/**
	 * Event time
	 */
	
	/**
	 * Show planned time
	 */
	function event_time( $time, $post, $column ) {
		if( $column != 'date' )
			return $time;

		if( $this->post_type != $_GET['post_type'] )
			return $time;

		return date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), get_post_meta( $post->ID, '_start', true ) );
	}

} new TP_Events;
