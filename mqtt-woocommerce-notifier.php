<?php
/**
 * Plugin Name: Alerts via MQQT for WooCommerce
 * Plugin URI:  https://github.com/yknivag/WooShiftrMQTT
 * Description: Plugin to WooCommerce which sends messages to a shiftr.io MQTT instance on certain events.
 * Version:     0.2.1
 * Author:      yknivag
 * License:     LGPL3
 * License URI: https://www.gnu.org/licenses/lgpl-3.0.en.html
 * WC requires at least: 4.0
 * WC tested up to: 4.2
 * Text Domain: wooshiftrmqtt
 */

// Initialization of the plugin function
if ( ! function_exists ( 'wooshiftrmqtt_plugin_init' ) ) {
	function wooshiftrmqtt_plugin_init() {
		global $wooshiftrmqtt_options;
		// Internationalization, first(!)
		load_plugin_textdomain( 'wooshiftrmqtt', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
		if ( ! is_admin() || ( is_admin() && isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'wooshiftrmqtt_plugin' ) ) {
			wooshiftrmqtt_register_settings();
		}
	}
}

// Adding admin plugin settings page function
if ( ! function_exists( 'add_wooshiftrmqtt_admin_menu' ) ) {
	function add_wooshiftrmqtt_admin_menu() {
		add_menu_page( __( 'MQTTWoo', 'wooshiftrmqtt' ), __( 'MQTTWoo', 'wooshiftrmqtt' ), 'manage_options', 'wooshiftrmqtt_plugin', 'wooshiftrmqtt_settings_page', 'dashicons-networking');
		//call register settings function
	}
}

// Plugin Deactivation Function
if ( ! function_exists( 'add_wooshiftrmqtt_admin_menu' ) ) {
	function add_wooshiftrmqtt_admin_menu() {
		// Nothing to do here.
	}
}
register_deactivation_hook( __FILE__, 'iaml_deactivate' );

// Plugin Delete Function
if ( ! function_exists( 'add_wooshiftrmqtt_admin_menu' ) ) {
	function add_wooshiftrmqtt_admin_menu() {
		delete_option( 'wooshiftrmqtt_shiftr_url' );
		delete_option( 'wooshiftrmqtt_shiftr_username' );
		delete_option( 'wooshiftrmqtt_shiftr_password' );
		delete_option( 'wooshiftrmqtt_shiftr_topic_prefix' );
		delete_option( 'wooshiftrmqtt_shiftr_retain' );
		delete_option( 'wooshiftrmqtt_shiftr_qos' );
	}
}
register_uninstall_hook( __FILE__, 'iaml_delete' );

// Initialization plugin settings function
if ( ! function_exists( 'wooshiftrmqtt_register_settings' ) ) {
	function wooshiftrmqtt_register_settings() {
		global $wpdb, $wooshiftrmqtt_options;
		$wooshiftrmqtt_option_defaults = array(
			'wooshiftrmqtt_shiftr_url'          => 'broker.shiftr.io',
			'wooshiftrmqtt_shiftr_username'     => '',
			'wooshiftrmqtt_shiftr_password'     => '',
			'wooshiftrmqtt_shiftr_topic_prefix' => '',
			'wooshiftrmqtt_shiftr_retain'       => 'false',
			'wooshiftrmqtt_shiftr_qos'          => '0'
		);
		// install the option defaults
		if ( is_multisite() ) {
			if ( ! get_site_option( 'wooshiftrmqtt_options' ) ) {
				add_site_option( 'wooshiftrmqtt_options', $wooshiftrmqtt_option_defaults, '', 'yes' );
			}
		} else {
			if ( ! get_option( 'wooshiftrmqtt_options' ) )
				add_option( 'wooshiftrmqtt_options', $wooshiftrmqtt_option_defaults, '', 'yes' );
		}
		// get options from the database
		if ( is_multisite() )
			$wooshiftrmqtt_options = get_site_option( 'wooshiftrmqtt_options' ); // get options from the database
		else
			$wooshiftrmqtt_options = get_option( 'wooshiftrmqtt_options' );// get options from the database
		// array merge incase this version has added new options
		$wooshiftrmqtt_options = array_merge( $wooshiftrmqtt_option_defaults, $wooshiftrmqtt_options );
		update_option( 'wooshiftrmqtt_options', $wooshiftrmqtt_options );
	}
}
// Admin plugin settings page content function
if ( ! function_exists( 'wooshiftrmqtt_settings_page' ) ) {
	function wooshiftrmqtt_settings_page() {
	    	    
		global $wooshiftrmqtt_options;
		$message = '';
		if( isset( $_POST['wooshiftrmqtt_submit'] ) && check_admin_referer( plugin_basename(__FILE__), 'wooshiftrmqtt_nonce_name' ) ) {
                    
            //Save options
            $wooshiftrmqtt_options[ 'wooshiftrmqtt_shiftr_username' ]     = sanitize_text_field( trim( $_POST[ 'wooshiftrmqtt_shiftr_username' ] ) );
            $wooshiftrmqtt_options[ 'wooshiftrmqtt_shiftr_password' ]     = sanitize_text_field( trim( $_POST[ 'wooshiftrmqtt_shiftr_password' ] ) );
            $wooshiftrmqtt_options[ 'wooshiftrmqtt_shiftr_topic_prefix' ] = sanitize_text_field( trim( $_POST[ 'wooshiftrmqtt_shiftr_topic_prefix' ] ) );
            $wooshiftrmqtt_options[ 'wooshiftrmqtt_shiftr_url' ]          = sanitize_text_field( trim( $_POST[ 'wooshiftrmqtt_shiftr_url' ] ) );
            $wooshiftrmqtt_options[ 'wooshiftrmqtt_shiftr_retain' ]       = sanitize_text_field( $_POST[ 'wooshiftrmqtt_cancel_return' ] );
            $wooshiftrmqtt_options[ 'wooshiftrmqtt_shiftr_qos' ]          = sanitize_text_field( $_POST[ 'wooshiftrmqtt_shiftr_qos' ] );

            $message = __( 'Settings saved' , 'wooshiftrmqtt' );
            update_option( 'wooshiftrmqtt_options', $wooshiftrmqtt_options );
		}
                
                
        //$wooshiftrmqtt_options = get_option('wooshiftrmqtt_options');
        $wooshiftrmqtt_shiftr_username     = isset( $wooshiftrmqtt_options[ 'wooshiftrmqtt_shiftr_username' ] )     ? $wooshiftrmqtt_options[ 'wooshiftrmqtt_shiftr_username' ]     : 'Key (Username)';
        $wooshiftrmqtt_shiftr_password     = isset( $wooshiftrmqtt_options[ 'wooshiftrmqtt_shiftr_password' ] )     ? $wooshiftrmqtt_options[ 'wooshiftrmqtt_shiftr_password' ]     : 'Secret (Password)';
        $wooshiftrmqtt_shiftr_topic_prefix = isset( $wooshiftrmqtt_options[ 'wooshiftrmqtt_shiftr_topic_prefix' ] ) ? $wooshiftrmqtt_options[ 'wooshiftrmqtt_shiftr_topic_prefix' ] : '';
        $wooshiftrmqtt_shiftr_url          = isset( $wooshiftrmqtt_options[ 'wooshiftrmqtt_shiftr_url' ] )          ? $wooshiftrmqtt_options[ 'wooshiftrmqtt_shiftr_url' ]          : 'broker.shiftr.io';
        $wooshiftrmqtt_shiftr_retain       = isset( $wooshiftrmqtt_options[ 'wooshiftrmqtt_shiftr_retain' ] )       ? $wooshiftrmqtt_options[ 'wooshiftrmqtt_shiftr_retain' ]       : 'false';
        $wooshiftrmqtt_shiftr_qos          = isset( $wooshiftrmqtt_options[ 'wooshiftrmqtt_shiftr_qos' ] )          ? $wooshiftrmqtt_options[ 'wooshiftrmqtt_shiftr_qos' ]          : '0';
               
		?>
		<div class="wrap">
		    <h2><?php esc_html_e( 'MQTTWoo', 'wooshiftrmqtt' ); ?></h2>
		    
		    <div id="poststuff"><div id="post-body">			
			<?php if ( $message != '' && isset( $_POST[ 'wooshiftrmqtt_submit' ] ) ) { ?>
				<div class="updated fade">
					<p><strong><?php echo esc_html( $message ); ?></strong></p>
				</div>
			<?php } ?>
                        
			<div class="postbox">
    			<h3 class="hndle"><label for="title"><?php esc_html_e( 'Quick Usage Guide', 'wooshiftrmqtt' ); ?></label></h3>
    			<div class="inside">
    			    <div class="wooshiftrmqtt_info_block">
						<h4><?php esc_html_e( 'Purpose', 'wooshiftrmqtt' ); ?></h4>
    				    	<p><?php esc_html_e( 'This plugin generates MQTT events to shiftr.io on when an order changes state, when a product hits the low stock threshold, or when it goes out of stock.', 'wooshiftrmqtt' ); ?></p>
							<p><?php esc_html_e( 'For order events the topic is orders/<state> where <state> is the state an order has just moved to.  The payload is the order number.', 'wooshiftrmqtt' ); ?></p>
							<p><?php esc_html_e( 'For stock events the topic is either stock/low or stock/out and the payload is the product id.', 'wooshiftrmqtt' ); ?></p>
						<h4><?php esc_html_e( 'Setting up shiftr.io', 'wooshiftrmqtt' ); ?></h4>
    				    	<p><?php esc_html_e( 'If you don\'t already have one, create an account at https://shiftr.io/ and create a Namespace for this project.', 'wooshiftrmqtt' ); ?></p>
    				    	<p><?php esc_html_e( 'In the namespace settings, create a full-access token and make a note of the credentials.', 'wooshiftrmqtt' ); ?></p>
    				    <h4><?php esc_html_e( 'Setting up plugin', 'wooshiftrmqtt' ); ?></h4>
    				    	<p><?php esc_html_e( 'Once you have completed the setup at shiftr.io then simply insert credentials you created below.', 'wooshiftrmqtt' ); ?></p>
							<p><?php esc_html_e( 'For more information on the retained and qos settings, see the shiftr.io documentation.', 'wooshiftrmqtt' ); ?></p>
    			    </div>
    			</div>
			</div>
			
			<div class="postbox">
    			<h3 class="hndle"><label for="title"><?php esc_html_e( 'Plugin Settings', 'wooshiftrmqtt' ); ?></label></h3>
    			<div class="inside">			
        			<form id="wooshiftrmqtt_settings_form" method='post' action=''>
        			    <!--<input type='hidden' id='sprdnt_tab_paypal' name='sprdnt_tab_paypal' value='1' />-->
        			    <fieldset>
        			        <legend><?php esc_html_e( 'MQTT Settings', 'wooshiftrmqtt' ); ?></legend>
							<label>
        				        <?php esc_html_e( 'shiftr.io broker URL', 'wooshiftrmqtt' ); ?> (<?php esc_html_e( 'You shouldn\'t need to change this.', 'wooshiftrmqtt' ); ?>)
        				        <input type='text' name='wooshiftrmqtt_shiftr_url' size='70' id='wooshiftrmqtt_shiftr_url' value="<?php if ( '' != $wooshiftrmqtt_options['wooshiftrmqtt_shiftr_url'] ) echo esc_html( $wooshiftrmqtt_options['wooshiftrmqtt_shiftr_url'] ); ?>" placeholder ="broker.shiftr.io" />
        				    </label><br />
        				    <label>
        				        <?php esc_html_e( 'shiftr.io namespace token key (username)', 'wooshiftrmqtt' ); ?>
        				        <input type='text' name='wooshiftrmqtt_shiftr_username' size='70' id='wooshiftrmqtt_shiftr_username' value="<?php if ( '' != $wooshiftrmqtt_options['wooshiftrmqtt_shiftr_username'] ) echo esc_html( $wooshiftrmqtt_options['wooshiftrmqtt_shiftr_username'] ); ?>" placeholder ="<?php esc_html_e( 'Key (Username).', 'wooshiftrmqtt' ); ?>" />
        				    </label><br />
        				    <label>
        				        <?php esc_html_e( 'shiftr.io namespace token secret (password)', 'wooshiftrmqtt' ); ?>
        				        <input type='text' name='wooshiftrmqtt_shiftr_password' size='70' id='wooshiftrmqtt_shiftr_password' value="<?php if ( '' != $wooshiftrmqtt_options['wooshiftrmqtt_shiftr_password'] ) echo esc_html( $wooshiftrmqtt_options['wooshiftrmqtt_shiftr_password'] ); ?>" placeholder ="<?php esc_html_e( 'Secret (Password).', 'wooshiftrmqtt' ); ?>" />
        				    </label><br />
							<label>
        				        <?php esc_html_e( 'MQTT Topic Prefix', 'wooshiftrmqtt' ); ?> (<?php esc_html_e( 'Eg mystore/alerts/ - must end in a slash!', 'wooshiftrmqtt' ); ?>)
        				        <input type='text' name='wooshiftrmqtt_shiftr_topic_prefix' size='70' id='wooshiftrmqtt_shiftr_topic_prefix' value="<?php if ( '' != $wooshiftrmqtt_options['wooshiftrmqtt_shiftr_topic_prefix'] ) echo esc_html( $wooshiftrmqtt_options['wooshiftrmqtt_shiftr_topic_prefix'] ); ?>" placeholder ="<?php esc_html_e( 'Topic Prefix (If Required).', 'wooshiftrmqtt' ); ?>" />
        				    </label><br />
        				    <label>
        				        <?php esc_html_e( 'Retain Message', 'wooshiftrmqtt' ); ?>
                                <select id="wooshiftrmqtt_shiftr_retain" name="wooshiftrmqtt_shiftr_retain">
                                    <option value="false" <?php echo ($wooshiftrmqtt_shiftr_retain == 'false') ? 'selected="selected"' : ''; ?>>False</option>
                                    <option value="true" <?php echo ($wooshiftrmqtt_shiftr_retain == 'true') ? 'selected="selected"' : ''; ?>>True</option>
                                </select>
                            </label><br />
							<label>
        				        <?php esc_html_e( 'Message QOS', 'wooshiftrmqtt' ); ?>
                                <select id="wooshiftrmqtt_shiftr_qos" name="wooshiftrmqtt_shiftr_qos">
                                    <option value="0" <?php echo ($wooshiftrmqtt_shiftr_qos == '0') ? 'selected="selected"' : ''; ?>>QOS - 0</option>
                                    <option value="1" <?php echo ($wooshiftrmqtt_shiftr_qos == '1') ? 'selected="selected"' : ''; ?>>QOS - 1</option>
									<option value="2" <?php echo ($wooshiftrmqtt_shiftr_qos == '2') ? 'selected="selected"' : ''; ?>>QOS - 2</option>
                                </select>
                            </label><br />
                        </fieldset>
                        <p><input type="submit" name="wooshiftrmqtt_submit" value="<?php esc_html_e( 'Save changes', 'wooshiftrmqtt' ); ?>" class="button-primary" /></p>
                        <?php wp_nonce_field( plugin_basename( __FILE__ ), 'wooshiftrmqtt_nonce_name' ); ?>
                    </form>
    			</div>
			</div>
			
		    </div></div><!-- End of poststuff and postbody -->
		
		</div><!-- end of wrap -->
	<?php 
	}
}

function shiftrwoo_send_message( $topic, $payload ) {
	$wooshiftrmqtt_options = get_option('wooshiftrmqtt_options');

	$shiftr_url = $wooshiftrmqtt_options[ 'wooshiftrmqtt_shiftr_url' ];
	$topic      = $wooshiftrmqtt_options[ 'wooshiftrmqtt_shiftr_topic_prefix' ] . $topic;
	$username   = $wooshiftrmqtt_options[ 'wooshiftrmqtt_shiftr_username' ];
	$password   = $wooshiftrmqtt_options[ 'wooshiftrmqtt_shiftr_password' ];
	$retained   = $wooshiftrmqtt_options[ 'wooshiftrmqtt_shiftr_retain' ];
	$qos        = $wooshiftrmqtt_options[ 'wooshiftrmqtt_shiftr_qos' ];

	//From shiftr.io documentation - request format
	//// curl -X POST 'http://username:password@broker.shiftr.io/foo/bar?retained=true&qos=0' -d 'Hello World!'

	$url = "http://";
	$url.= $username;
	$url.= ":";
	$url.= $password;
	$url.= "@";
	$url.= $shiftr_url;
	$url.= "/";
	$url.= $topic;
	$url.= "?retained=";
	$url.= $retained;
	$url.= "&qos=";
	$url.= $qos;

	$args = array(
		'body'        => $payload,
		'timeout'     => '5',
		'redirection' => '5',
		'httpversion' => '1.0',
		'blocking'    => true,
		'headers'     => array(),
		'cookies'     => array(),
	);

	wp_remote_post( $url, $args );
}

function shiftrwoo_orders( $order_id, $old_status, $new_status ) {
	$topic = "orders/" . $new_status;
	shiftrwoo_send_message( $topic, $order_id );
	shiftrwoo_stats_orders();
	shiftrwoo_stats_stock();
}
add_action( 'woocommerce_order_status_changed', 'shiftrwoo_orders', 10, 4 );

function shiftrwoo_stock_low( $product_id ) {
	$topic = "stock/low";
	shiftrwoo_send_message( $topic, $product_id );
	shiftrwoo_stats_orders();
	shiftrwoo_stats_stock();
}
add_action( 'woocommerce_low_stock', 'shiftrwoo_stock_low', 10, 4 );

function shiftrwoo_stock_out( $product_id ) {
	$topic = "stock/out";
	shiftrwoo_send_message( $topic, $product_id );
	shiftrwoo_stats_orders();
	shiftrwoo_stats_stock();
}
add_action( 'woocommerce_no_stock', 'shiftrwoo_stock_out', 10, 4 );

function shiftrwoo_stats_orders() {
	$topic = "stats/orders";
	$order_stats = array (
		'payment-pending' => wc_orders_count( 'pending' ),
		'on-hold' => wc_orders_count( 'on-hold' ),
		'processing' => wc_orders_count( 'processing' ),
		'completed' => wc_orders_count( 'completed' ),
		'cancelled' => wc_orders_count( 'cancelled' ),
		'refunded' => wc_orders_count( 'refunded' ),
		'failed' => wc_orders_count( 'failed' )
	);
	$payload = json_encode( $order_stats );
	shiftrwoo_send_message( $topic, $payload );
}

function shiftrwoo_stats_stock() {
	//TO-DO - Try to determine a way to retrieve these values without heave DB queries.
	//Not implemented yet, not mentioned yet in readme.
	$topic = "stats/stock";
	$stock_stats = array (
		'low-stock' => 0,
		'out-of-stock' => 0
	);
	$payload = json_encode( $stock_stats );
	//shiftrwoo_send_message( $topic, $payload );
}

register_activation_hook( __FILE__, 'wooshiftrmqtt_register_settings' );

add_action( 'init', 'wooshiftrmqtt_plugin_init' );
add_action( 'admin_init', 'wooshiftrmqtt_plugin_init' );
add_action( 'admin_menu', 'add_wooshiftrmqtt_admin_menu' );