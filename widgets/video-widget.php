<?php

class Sydney_Video_Widget extends WP_Widget {

	public function __construct() {
		$widget_ops = array( 'classname' => 'sydney_video_widget_widget', 'description' => __( 'Display a video from Youtube, Vimeo etc.', 'sydney') );
        parent::__construct(false, $name = __('Sydney: Video', 'sydney'), $widget_ops);
		$this->alt_option_name = 'sydney_video_widget';
    }
	
	function form($instance) {
		$title      = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$url        = isset( $instance['url'] ) ? esc_url( $instance['url'] ) : '';
		$video_mode = isset( $instance['video_mode'] ) ? esc_attr( $instance['video_mode'] ) : '';
		$text       = isset( $instance['text'] ) ? wp_kses_post( $instance['text'] ) : '';
		
	?>

	<p>
	<label for="<?php echo esc_attr( $this->get_field_id('title') ); ?>"><?php esc_html_e('Title', 'sydney'); ?></label>
	<input class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
	</p>

	<p><label for="<?php echo esc_attr( $this->get_field_id( 'url' ) ); ?>"><?php esc_html_e( 'Paste the URL of the video (only from a network that supports oEmbed, like Youtube, Vimeo etc.):', 'sydney' ); ?></label>
	<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'url' ) ); ?>" type="text" value="<?php echo esc_attr( $url ); ?>" size="3" /></p>
	<p><label for="<?php echo esc_attr( $this->get_field_id('video_mode') ); ?>"><?php esc_html_e('Video mode:', 'sydney'); ?></label>
        <select name="<?php echo esc_attr( $this->get_field_name('video_mode') ); ?>" id="<?php echo esc_attr( $this->get_field_id('video_mode') ); ?>">		
			<option value="vid-normal" <?php if ( 'vid-normal' === $video_mode ) echo 'selected="selected"'; ?>><?php echo esc_html( __('Normal', 'sydney') ); ?></option>
			<option value="vid-lightbox" <?php if ( 'vid-lightbox' === $video_mode ) echo 'selected="selected"'; ?>><?php echo esc_html( __('Lightbox', 'sydney') ); ?></option>
       	</select>
    </p>  
	<p><label for="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>"><?php esc_html_e( 'Text before the play button (only for lightbox mode):', 'sydney' ); ?></label>
	<textarea class="widefat" rows="6" cols="20" id="<?php echo esc_attr( $this->get_field_id('text') ); ?>" name="<?php echo esc_attr( $this->get_field_name('text') ); ?>"><?php echo esc_textarea( $text ); ?></textarea></p>

	<?php
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title']      = wp_strip_all_tags($new_instance['title']);
		$instance['url']        = esc_url_raw($new_instance['url']);
		$instance['video_mode'] = sanitize_text_field($new_instance['video_mode']);     
		if ( current_user_can( 'unfiltered_html' ) ) {
			$instance['text'] = $new_instance['text'];
		} else {
			$instance['text'] = wp_kses_post( $new_instance['text'] );
		}

		return $instance;
	}
	
	function widget($args, $instance) {
		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		extract($args);

		$title  = ( ! empty( $instance['title'] ) ) ? $instance['title'] : '';
		$title  = apply_filters( 'widget_title', $title, $instance, $this->id_base );
		$url    = isset( $instance['url'] ) ? esc_url( $instance['url'] ) : '';
		$video_mode = isset( $instance['video_mode'] ) ? esc_html($instance['video_mode']) : 'vid-normal';
		$text   = isset( $instance['text'] ) ? $instance['text'] : '';
		echo $before_widget; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		
		if ( $title ) echo $before_title . $title . $after_title; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		
		if( ($url) ) {
			echo '<div class="sydney-video ' . esc_attr( $video_mode ) . '">';
				echo '<div class="video-overlay">';
				echo '<div class="sydney-video-inner"><span class="close-popup"><i class="fa fa-times"></i></span>' . wp_oembed_get($url) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '</div>';
				echo '<div class="video-text">' . wp_kses_post( $text ) . '</div>';
				echo '<a href="#" class="toggle-popup"><i class="fa fa-play"></i></a>';
			echo '</div>';
		}
		echo $after_widget; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}   