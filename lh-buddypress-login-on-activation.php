<?php
/**
 * Plugin Name: LH Buddypress login on Activation
 * Plugin URI: http://lhero.org/portfolio/lh-buddypress-login-on-activation/
 * Author: Peter Shaw
 * Author URI: https://shawfactor.com
 * Version: 1.03
 * Description: This plugin automatically logs in the user and redirects them to their profile when they activate their account
 * Text Domain: lh_bploa
 * Domain Path: /languages
 */
 
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if (!class_exists('LH_Buddypress_login_on_activation_plugin')) {

class LH_Buddypress_login_on_activation_plugin {
    
    private static $instance;
    
    static function return_plugin_namespace(){

        return 'lh_bploa';

    }

    public function autologin_on_activation( $user_id, $key = null, $user = null ) {
    
    	if ( defined( 'DOING_AJAX' ) or is_admin() ) {
    		return;
    	} elseif (!empty($key)) {
    
    	    $buddypress = buddypress();
    
    
        	//simulate Bp activation
        	/* Check for an uploaded avatar and move that to the correct user folder, just do what bp does */
        	if ( is_multisite() ) {
        		$hashed_key = wp_hash( $key );
        	} else {
        		$hashed_key = wp_hash( $user_id );
        	}
        	
        	/* Check if the avatar folder exists. If it does, move rename it, move it and delete the signup avatar dir */
        	if ( file_exists( BP_AVATAR_UPLOAD_PATH . '/avatars/signups/' . $hashed_key ) ) {
        		@rename( BP_AVATAR_UPLOAD_PATH . '/avatars/signups/' . $hashed_key, BP_AVATAR_UPLOAD_PATH . '/avatars/' . $user_id );
        	}
    
        	bp_core_add_message( __( 'Your account is active and you are logged in!', 'buddypress' ) );
    
        	$buddypress->activation_complete = true;
        	//now authorise and redirect
    
        	wp_set_auth_cookie( $user_id, true, false );
        	bp_core_redirect( apply_filters( 'lh_autologin_on_activation_redirect_url', bp_core_get_user_domain( $user_id ), $user_id ) );
    	
    	}
    	
    }

    public function plugin_init(){
        
        //potentially load translations
        load_plugin_textdomain( self::return_plugin_namespace(), false, basename( dirname( __FILE__ ) ) . '/languages' );
        
        //log the user in on activation
        add_action( 'bp_core_activated_user', array($this, 'autologin_on_activation'), 40, 3 );    
        
    }

    /**
     * Gets an instance of our plugin.
     *
     * using the singleton pattern
     */
    public static function get_instance(){
        
        if (null === self::$instance) {
            
            self::$instance = new self();
            
        }
 
        return self::$instance;

    }


    public function __construct() {
    
        //run whatever on plugins loaded
        add_action( 'plugins_loaded', array($this,'plugin_init'));
    
    }



}

$lh_buddypress_login_on_activation_instance = LH_Buddypress_login_on_activation_plugin::get_instance();

}

?>