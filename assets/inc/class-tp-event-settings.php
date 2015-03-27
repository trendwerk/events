<?php
/** 
 * Settings
 */

class TP_Event_Settings {
	var $post_type;

	function __construct( $post_type ) {
		$this->post_type = $post_type;

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );

		add_action( 'add_meta_boxes', array( $this, 'register' ), 9 );
		add_action( 'save_post', array( $this, 'save' ) );
	}

	function enqueue() {
		wp_enqueue_script( 'bootstrap-datepicker', plugins_url( '../coffee/lib/bootstrap-datepicker/bootstrap-datepicker-built.js', __FILE__ ), array( 'jquery' ) );
		wp_enqueue_style( 'bootstrap-datepicker', plugins_url( '../coffee/lib/require.css', __FILE__ ) );

		wp_enqueue_script( 'events', plugins_url( '../coffee/admin.js', __FILE__ ), array( 'jquery', 'bootstrap-datepicker' ) );
	}
	
	function register() {
		add_meta_box( 'event-settings', __( 'Settings', 'events' ), array( $this, 'display' ), $this->post_type, 'normal', 'high' );
	}
	
	/**
	 * Display
	 */
	function display( $post ) {
		$_start = get_post_meta( $post->ID, '_start', true );
		$_end = get_post_meta( $post->ID, '_end', true );
		?>

		<table class="form-table">

			<tbody>
				
				<tr valign="top">
					<th scope="row">
						<label for="_start"><?php _e( 'Start', 'events' ); ?></label>
					</th>

					<td>
						<input type="text" name="_start" id="_start" value="<?php echo date( 'd-m-Y', $_start ); ?>" class="medium-text" />

						<?php _e( 'at', 'events' ); ?>

						<select name="_start-time[hours]">
							<?php $this->hours_options( date( 'H', $_start ) ); ?>
						</select>

						<select name="_start-time[minutes]">
							<?php $this->minutes_options( date( 'i', $_start ) ); ?>
						</select>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row">
						<label for="_end"><?php _e( 'End', 'events' ); ?></label>
					</th>

					<td>
						<input type="text" name="_end" id="_end" value="<?php echo date( 'd-m-Y', $_end ); ?>" class="medium-text" />

						<?php _e( 'at', 'events' ); ?>

						<select name="_end-time[hours]">
							<?php $this->hours_options( date( 'H', $_end ) ); ?>
						</select>

						<select name="_end-time[minutes]">
							<?php $this->minutes_options( date( 'i', $_end ) ); ?>
						</select>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row">
						<label for="_location"><?php _e( 'Location', 'events' ); ?></label>
					</th>

					<td>
						<input type="text" name="_location" id="_location" value="<?php echo get_post_meta( get_the_ID(), '_location', true ); ?>" class="regular-text" />
					</td>
				</tr>

				<tr valign="top">
					<th scope="row">
						<label for="_address"><?php _e( 'Address', 'events' ); ?></label>
					</th>

					<td>
						<input type="text" name="_address" id="_address" value="<?php echo get_post_meta( get_the_ID(), '_address', true ); ?>" class="regular-text" />
					</td>
				</tr>

				<tr valign="top">
					<th scope="row">
						<label for="_zipcode"><?php _e( 'Zipcode', 'events' ); ?></label>
					</th>

					<td>
						<input type="text" name="_zipcode" id="_zipcode" value="<?php echo get_post_meta( get_the_ID(), '_zipcode', true ); ?>" class="regular-text" />
					</td>
				</tr>

				<tr valign="top">
					<th scope="row">
						<label for="_city"><?php _e( 'City', 'events' ); ?></label>
					</th>

					<td>
						<input type="text" name="_city" id="_city" value="<?php echo get_post_meta( get_the_ID(), '_city', true ); ?>" class="regular-text" />
					</td>
				</tr>

				<tr valign="top">
					<th scope="row">
						<label for="_cost"><?php _e( 'Cost', 'events' ); ?></label>
					</th>

					<td>
						<input type="text" name="_cost" id="_cost" value="<?php echo get_post_meta( get_the_ID(), '_cost', true ); ?>" class="regular-text" />
					</td>
				</tr>
				
			</tbody>

		</table>

		<?php
	}

	/**
	 * Ouput options for hours
	 * 
	 * @param string $current 
	 */
	function hours_options( $current ) {
		for( $i = 0; $i < 24; $i++ ) {
			$val = sprintf( '%02d' , $i );
			?>

			<option <?php selected( $val, $current ); ?>>
				<?php echo $val; ?>
			</option>

			<?php
		}
	}

	/**
	 * Ouput options for minutes
	 * 
	 * @param string $current 
	 */
	function minutes_options( $current ) {
		$options = array( '00', '15', '30', '45' );

		foreach( $options as $option ) {
			?>

			<option <?php selected( $option, $current ); ?>>
				<?php echo $option; ?>
			</option>

			<?php
		}
	}
	
	/**
	 * Save as post meta
	 */
	function save( $post_id ) {
		/**
		 * Perform checks
		 */
		if( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) )
			return;

		if( isset( $_REQUEST['doing_wp_cron'] ) )
			return;
			
		if( isset( $_REQUEST['post_view'] ) )
		    return;

		if( ! isset( $_POST['post_type'] ) || $this->post_type != $_POST['post_type'] )
			return;

		/**
		 * Save data
		 */
		$_start = $_POST['_start'] . ' ' . $_POST['_start-time']['hours'] . ':' . $_POST['_start-time']['minutes'];
		$_end = $_POST['_end'] . ' ' . $_POST['_end-time']['hours'] . ':' . $_POST['_end-time']['minutes'];

		update_post_meta( $post_id, '_start', strtotime( $_start ) );
		update_post_meta( $post_id, '_end', strtotime( $_end ) );

		update_post_meta( $post_id, '_location', $_POST['_location'] );
		update_post_meta( $post_id, '_address', $_POST['_address'] );
		update_post_meta( $post_id, '_zipcode', $_POST['_zipcode'] );
		update_post_meta( $post_id, '_city', $_POST['_city'] );
		update_post_meta( $post_id, '_cost', floatval( str_replace( ',', '.', $_POST['_cost'] ) ) );
	}
}
new TP_Event_Settings( 'events' );
