<?php

/**
 * Widget for ad fields.
 */
class TafWidget extends WP_Widget {

	public function __construct( $id_base = '', $name = '', array $widget_options = array(), array $control_options = array() ) {
		parent::__construct( 'taf-widget', __( 'Ad Block Widget', 'taf' ), array(
			'class_name'  => 'taf',
			'description' => __( 'Widget to display ad field in specified position.', 'taf' ),
		) );
	}

	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_html_e( 'Title', 'taf' ); ?>
			</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
					name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text"
					value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'term_id' ) ); ?>">
				<?php esc_html_e( 'Position', 'taf' ); ?>:
			</label>
			<?php
			wp_dropdown_categories( array(
				'taxonomy'         => 'ad-position',
				'show_option_none' => __( 'Please select...', 'taf' ),
				'hide_empty'       => false,
				'name'             => $this->get_field_name( 'term_id' ),
				'id'               => $this->get_field_id( 'term_id' ),
				'selected'         => isset( $instance['term_id'] ) ? (int) $instance['term_id'] : 0,
			) )
			?>
		</p>
		<?php
	}

	/**
	 * Render widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		$term = get_term_by( 'id', $instance['term_id'], 'ad-position' );
		if ( ! $term || is_wp_error( $term ) ) {
			return;
		}
		$title  = isset( $instance['title'] ) && $instance['title']
			? "{$args['before_title']}{$instance['title']}{$args['after_title']}" : '';
		$before = "{$args['before_widget']}{$title}<div class=\"widget-taf\">";
		$after  = '</div>' . $args['after_widget'];
		echo taf_render( $term->slug, $before, $after );
	}

	/**
	 * Update widget
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		var_dump( $new_instance );
		return $new_instance;
	}
}
