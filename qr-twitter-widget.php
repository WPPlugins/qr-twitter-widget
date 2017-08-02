<?php
/*
 * Plugin Name: QR Twitter Widget
 * Version: 0.2.0
 * Plugin URI: https://qrokes.com/en/wordpress/plugins/qr-twitter-widget/
 * Description: Display an official Twitter Embedded Timeline widget.
 * Author: QROkes
 * Author URI: https://qrokes.com/
 * Text Domain: qr-twitter-widget
 * Domain Path: /languages/
 * License: GPL v3
 */

/**
 * QR Twitter Widget
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace QRtw;

if ( ! function_exists( 'add_action' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/*
 * Based on Easy Twitter Feed Widget (https://wordpress.org/plugins/easy-twitter-feed-widget/) & Jetpack (Twitter Timeline Widget)
 * See: https://twitter.com/settings/widgets and https://dev.twitter.com/docs/embedded-timelines for details on Twitter Timelines
 */
 
/* Translation Ready */
add_action( 'plugins_loaded', function() {
    load_plugin_textdomain( 'qr-twitter-widget', false, basename( dirname( __FILE__ ) ) . '/languages/' );
});
 
/* Widget Setup */
add_action( 'widgets_init', function() {
	register_widget( 'QRtw\Widget_twitter' );
});

class Widget_twitter extends \WP_Widget {

	private $defaults;

	/**
	 *  Set up the widget's unique name, ID, class, description, and other options.
	 */
	function __construct() {
		parent::__construct(
			'qrokes-twitterwidget',  // Base ID (Class)
			__( 'QR Twitter Timeline', 'qr-twitter-widget'),  // Name & Text Domain
			array( 
				'classname' => 'widget_twitter_timeline',      // Class
				'description' => __( 'Display your tweets from Twitter.', 'qr-twitter-widget' ) )   // Args
		);
		
		$this->defaults = array(
			'title' => esc_attr__( 'Follow me on Twitter', 'qr-twitter-widget'),
			'twitter_widget_id' => '',
			'twitter_widget_screen_name' => 'Username',
			'twitter_widget_tweet_limit' => 0,
			'twitter_widget_show_replies' => 'false',
			'twitter_widget_width' => '325',
			'twitter_widget_height' => '500',
			'twitter_widget_theme' => 'light',
			'twitter_widget_link_color' => '#f96e5b',
			'twitter_widget_border_color' => '#e8e8e8',
			'twitter_widget_layout_header' => 1,
			'twitter_widget_layout_footer' => 0,
			'twitter_widget_layout_border' => 1,
			'twitter_widget_layout_scrollbar' => 0,
			'twitter_widget_layout_background' => 1
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	function widget( $args, $instance ) {

		/** Extract Args */
		extract( $args );

		/** Merge the user-selected arguments with the defaults. */
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		/** Open the output of the widget. */
		echo $before_widget;

		if ( ! empty( $instance['title'] ) ) {
			echo $before_title . apply_filters( 'widget_title',  $instance['title'], $instance, $this->id_base ) . $after_title;
		}
		
		$twtlw = new \QRtw\Timeline($instance);
		$twtlw->printl();
		
		/** Close the output of the widget. */
		echo $after_widget;
		
	}

	/** Updates the widget control options for the particular instance of the widget.
	 *
	 * This function should check that $new_instance is set correctly.
	 * The newly calculated value of $instance should be returned.
	 * If "false" is returned, the instance won't be saved/updated.
	 *
	 * @param array $new_instance New settings for this instance as input by the user via form()
	 * @param array $old_instance Old settings for this instance
	 * @return array Settings to save or bool false to cancel saving
	 */
	function update( $new_instance, $old_instance ) {
		
		// If they entered something that might be a full URL, try to parse it out
		if ( is_string( $new_instance['twitter_widget_id'] ) ) {
			if ( preg_match( '#https?://twitter\.com/settings/widgets/(\d+)#s', $new_instance['twitter_widget_id'], $matches ) ) {
				$new_instance['twitter_widget_id'] = $matches[1];
			}
		}

		$instance['twitter_widget_id'] = sanitize_text_field( $new_instance['twitter_widget_id'] );
		$instance['twitter_widget_id'] = is_numeric( $instance['twitter_widget_id'] ) ? $instance['twitter_widget_id'] : '';
		
		/** Update */
		$instance = $old_instance;
		foreach( $this->defaults as $key => $val ) {
			$instance[$key] = strip_tags( $new_instance[$key] );
		}
		return $instance;

	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	function form( $instance ) {

		/** Merge the user-selected arguments with the defaults. */
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		$title = strip_tags( $instance['title'] );
		$twitter_widget_show_replies = array( 'true' => 'Yes', 'false' => 'No' );
		$twitter_widget_theme = array( 'light' => 'Light', 'dark' => 'Dark' );
		$boolean = array( 1 => 'Yes', 0 => 'No' );
?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'qr-twitter-widget' ); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>

		<p><strong><?php _e( 'Settings', 'qr-twitter-widget' ); ?></strong></p>
		<hr />

		<p>
			<label for="<?php echo $this->get_field_id( 'twitter_widget_screen_name' ); ?>"><?php _e( 'Twitter Username:', 'qr-twitter-widget' ); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'twitter_widget_screen_name' ); ?>" name="<?php echo $this->get_field_name( 'twitter_widget_screen_name' ); ?>" value="<?php echo esc_attr( $instance['twitter_widget_screen_name'] ); ?>" />
		</p>

        <p>
			<label for="<?php echo $this->get_field_id( 'twitter_widget_id' ); ?>"><?php _e( 'Twitter Widget ID: ', 'qr-twitter-widget' ); ?><small><?php _e( ' (Optional)', 'qr-twitter-widget' ); ?></small></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'twitter_widget_id' ); ?>" name="<?php echo $this->get_field_name( 'twitter_widget_id' ); ?>" value="<?php echo esc_attr( $instance['twitter_widget_id'] ); ?>" />
			<small>You need to <a href="https://twitter.com/settings/widgets/new/user" target="_blank">create a widget at Twitter.com</a>, and then enter your widget id (the long number found in the URL of your widget's config page) in the field below. <a href="https://qrokes.com/en/tutorials/my-wordpress/how-to-create-a-twitter-widget-timeline/" target="_blank">Read more</a>.</small>
		</p>

		<p><strong><?php _e( 'Layout Options', 'qr-twitter-widget' ); ?></strong></p>
		<hr />
		<p>
			<label for="<?php echo $this->get_field_id( 'twitter_widget_theme' ); ?>"><?php _e( 'Timeline Theme:', 'qr-twitter-widget' ); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id( 'twitter_widget_theme' ); ?>" name="<?php echo $this->get_field_name( 'twitter_widget_theme' ); ?>">
              <?php foreach ( $twitter_widget_theme as $key => $val ): ?>
			    <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $instance['twitter_widget_theme'], $key ); ?>><?php echo esc_html( $val ); ?></option>
			  <?php endforeach; ?>
            </select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'twitter_widget_layout_background' ); ?>"><?php _e( 'Transparent Background:', 'qr-twitter-widget' ); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id( 'twitter_widget_layout_background' ); ?>" name="<?php echo $this->get_field_name( 'twitter_widget_layout_background' ); ?>">
              <?php foreach ( $boolean as $key => $val ): ?>
			    <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $instance['twitter_widget_layout_background'], $key ); ?>><?php echo esc_html( $val ); ?></option>
			  <?php endforeach; ?>
            </select>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'twitter_widget_layout_header' ); ?>"><?php _e( 'Show Header:', 'qr-twitter-widget' ); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id( 'twitter_widget_layout_header' ); ?>" name="<?php echo $this->get_field_name( 'twitter_widget_layout_header' ); ?>">
              <?php foreach ( $boolean as $key => $val ): ?>
			    <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $instance['twitter_widget_layout_header'], $key ); ?>><?php echo esc_html( $val ); ?></option>
			  <?php endforeach; ?>
            </select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'twitter_widget_layout_footer' ); ?>"><?php _e( 'Show Footer:', 'qr-twitter-widget' ); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id( 'twitter_widget_layout_footer' ); ?>" name="<?php echo $this->get_field_name( 'twitter_widget_layout_footer' ); ?>">
              <?php foreach ( $boolean as $key => $val ): ?>
			    <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $instance['twitter_widget_layout_footer'], $key ); ?>><?php echo esc_html( $val ); ?></option>
			  <?php endforeach; ?>
            </select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'twitter_widget_layout_border' ); ?>"><?php _e( 'Show Border:', 'qr-twitter-widget' ); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id( 'twitter_widget_layout_border' ); ?>" name="<?php echo $this->get_field_name( 'twitter_widget_layout_border' ); ?>">
              <?php foreach ( $boolean as $key => $val ): ?>
			    <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $instance['twitter_widget_layout_border'], $key ); ?>><?php echo esc_html( $val ); ?></option>
			  <?php endforeach; ?>
            </select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'twitter_widget_layout_scrollbar' ); ?>"><?php _e( 'Show Scrollbar:', 'qr-twitter-widget' ); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id( 'twitter_widget_layout_scrollbar' ); ?>" name="<?php echo $this->get_field_name( 'twitter_widget_layout_scrollbar' ); ?>">
              <?php foreach ( $boolean as $key => $val ): ?>
			    <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $instance['twitter_widget_layout_scrollbar'], $key ); ?>><?php echo esc_html( $val ); ?></option>
			  <?php endforeach; ?>
            </select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'twitter_widget_link_color' ); ?>"><?php _e( 'Link Color (hex):', 'qr-twitter-widget' ); ?> <small><?php _e( 'e.g #333333', 'qr-twitter-widget' ); ?></small></label><br />
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'twitter_widget_link_color' ); ?>" name="<?php echo $this->get_field_name( 'twitter_widget_link_color' ); ?>" value="<?php echo esc_attr( $instance['twitter_widget_link_color'] ); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'twitter_widget_border_color' ); ?>"><?php _e( 'Border Color (hex):', 'qr-twitter-widget' ); ?> <small><?php _e( 'e.g #333333', 'qr-twitter-widget' ); ?></small></label><br />
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'twitter_widget_border_color' ); ?>" name="<?php echo $this->get_field_name( 'twitter_widget_border_color' ); ?>" value="<?php echo esc_attr( $instance['twitter_widget_border_color'] ); ?>" />
		</p>
		<hr />
		<p>
			<label for="<?php echo $this->get_field_id( 'twitter_widget_tweet_limit' ); ?>"><?php _e( '# of Tweets Shown:', 'qr-twitter-widget' ); ?></label>
            <input type="number" max="20" min="0" class="widefat" id="<?php echo $this->get_field_id( 'twitter_widget_tweet_limit' ); ?>" name="<?php echo $this->get_field_name( 'twitter_widget_tweet_limit' ); ?>" value="<?php echo esc_attr( $instance['twitter_widget_tweet_limit'] ); ?>">
            </input>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'twitter_widget_show_replies' ); ?>"><?php _e( 'Show Replies:', 'qr-twitter-widget' ); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id( 'twitter_widget_show_replies' ); ?>" name="<?php echo $this->get_field_name( 'twitter_widget_show_replies' ); ?>">
              <?php foreach ( $twitter_widget_show_replies as $key => $val ): ?>
			    <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $instance['twitter_widget_show_replies'], $key ); ?>><?php echo esc_html( $val ); ?></option>
			  <?php endforeach; ?>
            </select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'twitter_widget_width' ); ?>"><?php _e( 'Width (px):', 'qr-twitter-widget' ); ?></label>
            <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'twitter_widget_width' ); ?>" name="<?php echo $this->get_field_name( 'twitter_widget_width' ); ?>" value="<?php echo esc_attr( $instance['twitter_widget_width'] ); ?>">
            </input>
		</p>
			
		<p>
			<label for="<?php echo $this->get_field_id( 'twitter_widget_height' ); ?>"><?php _e( 'Height (px):', 'qr-twitter-widget' ); ?></label>
            <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'twitter_widget_height' ); ?>" name="<?php echo $this->get_field_name( 'twitter_widget_height' ); ?>" value="<?php echo esc_attr( $instance['twitter_widget_height'] ); ?>">
            </input>
		</p><?php
	}
	
}


/** *********************************
******  Shortcode [twittertl]  ******
********************************** **/
add_shortcode( 'twittertl', [ new \QRtw\Shortcode_twitter, 'shortcode' ] );

class Shortcode_twitter {
	
	function __construct () {
		// https://wordpress.stackexchange.com/questions/61437/php-error-with-shortcode-handler-from-a-class
		add_filter( 'qr_twittertl_instance', [ $this, 'get_instance' ] );
	}
	
	public static function get_instance() {
        return $this;
    }
	
	public static function shortcode( $atts, $content = null ) {
		$atr = shortcode_atts( array(
			'id' => '',
			'user' => 'Username',
			'limit' => 0,
			'replies' => 'false',
			'width' => '325',
			'height' => '500',
			'theme' => 'light',
			'linkcolor' => '#f96e5b',
			'bordercolor' => '#e8e8e8',
			'header' => 'true',
			'footer' => 'false',
			'border' => 'true',
			'scrollbar' => 'false',
			'background' => 'true'
		), $atts );
		
		/** Convert shortcode attributes into known variables names to be procesed in widget functions **/
		$datr = array(
				'twitter_widget_id' => $atr['id'],
				'twitter_widget_screen_name' => $atr['user'],
				'twitter_widget_tweet_limit' => $atr['limit'],
				'twitter_widget_show_replies' => $atr['replies'],
				'twitter_widget_width' => $atr['width'],
				'twitter_widget_height' => $atr['height'],
				'twitter_widget_theme' => $atr['theme'],
				'twitter_widget_link_color' => $atr['linkcolor'],
				'twitter_widget_border_color' => $atr['bordercolor'],
				'twitter_widget_layout_header' => $atr['header'],
				'twitter_widget_layout_footer' => $atr['footer'],
				'twitter_widget_layout_border' => $atr['border'],
				'twitter_widget_layout_scrollbar' => $atr['scrollbar'],
				'twitter_widget_layout_background' => $atr['background']
			);
		
		$datr['twitter_widget_layout_header'] = ( $datr['twitter_widget_layout_header'] == 'false' )? 0: 1;
		$datr['twitter_widget_layout_footer'] = ( $datr['twitter_widget_layout_footer'] == 'false' )? 0: 1;
		$datr['twitter_widget_layout_border'] = ( $datr['twitter_widget_layout_border'] == 'false' )? 0: 1;
		$datr['twitter_widget_layout_scrollbar'] = ( $datr['twitter_widget_layout_scrollbar'] == 'false' )? 0: 1;
		$datr['twitter_widget_layout_background'] = ( $datr['twitter_widget_layout_background'] == 'true' )? 1: 0;
		
		$twtls = new \QRtw\Timeline($datr);
		$twtls->printl();
		
		return;
	}

}


class Timeline {
	
	private $instanced;
	
	function __construct($data) {
		$this->instanced = $data;
	}
	
	/** Output HTML Twitter Timeline Code **/
	public function printl() {		
		
		/** Data Layout */
		$data_layout = array();
		$data_layout[] = ( $this->instanced['twitter_widget_layout_header'] == 0 )? 'noheader': '';
		$data_layout[] = ( $this->instanced['twitter_widget_layout_footer'] == 0 )? 'nofooter': '';
		$data_layout[] = ( $this->instanced['twitter_widget_layout_border'] == 0 )? 'noborders': '';
		$data_layout[] = ( $this->instanced['twitter_widget_layout_scrollbar'] == 0 )? 'noscrollbar': '';
		$data_layout[] = ( $this->instanced['twitter_widget_layout_background'] == 1 )? 'transparent': '';

		/** Data Attributes */
		$data_twitter_widget = array(
			'data-widget-id' => $this->instanced['twitter_widget_id'],
			'data-screen-name' => $this->instanced['twitter_widget_screen_name'],
			'data-show-replies' => $this->instanced['twitter_widget_show_replies'],
			'data-theme' => $this->instanced['twitter_widget_theme'],
			'data-link-color' => $this->instanced['twitter_widget_link_color'],
			'data-border-color' => $this->instanced['twitter_widget_border_color'],
			'data-chrome' => join( ' ', array_filter($data_layout) )
		);
		
		/** Twitter only manages scrollbar / height at default value. So this is for it :) */
		if( $this->instanced['twitter_widget_tweet_limit'] != 0 ) {
			$data_twitter_widget['data-tweet-limit'] = $this->instanced['twitter_widget_tweet_limit'];
		}

		/** Data Attributes as name=value */
		$data_twitter_widget_nv = '';
		foreach ( $data_twitter_widget as $key => $val ) {
			if( $key == 'data-widget-id' && !is_numeric($val) ) {
				$key = 'href';
				$val = 'https://twitter.com/' . $this->instanced['twitter_widget_screen_name'];
			}
			if( !empty($val) ) {
				$data_twitter_widget_nv .= $key . '=' . '"' . esc_attr( $val ) . '"' . ' ';
			}
		}
				
		/** Set language **/
		$lang = substr( strtoupper( get_locale() ), 0, 2 );
		
		/** HTML Output **/
		echo '<div class="twitter-widget-feed"><a class="twitter-timeline" width="' . $this->instanced['twitter_widget_width'] . '" height="' . $this->instanced['twitter_widget_height'] . '" ' . $data_twitter_widget_nv . 'lang="' . $lang . '">Tweets by @' . $this->instanced['twitter_widget_screen_name'] . '</a></div>';

		/** Insert Twitter JavaScript */
		require_once( 'lib/qr-twitter-widget-twapi.php' );
		
		return;
	}

}