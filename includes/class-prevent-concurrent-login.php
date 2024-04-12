<?php
/**
 * Handles prevent concurrent login functionality
 */

namespace LearnDash\Integrity;

/**
 * Class to prevent concurrent login.
 */
class Prevent_Concurrent_Login {
	/**
	 * Whether this feature is enabled or not
	 *
	 * @var bool
	 */
	private static $enabled;

	/**
	 * Init the hooks
	 *
	 * @return void
	 */
	public static function init() {

        // Learndash login multi session process (Keep these codes at the top)
		add_filter( 'wp_login_errors', array( __CLASS__, 'login_errors' ) );
		add_filter( 'learndash-login-modal-form-before', array( __CLASS__, 'login_errors_multi_session' ) );

        //Other Proccess

    }

    //get the ral ip address
	public static function get_ip_address(){
	    if ( isset($_SERVER['HTTP_CLIENT_IP']) ) {
	        $ip = $_SERVER['HTTP_CLIENT_IP'];
	    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	    } else {
	        $ip = $_SERVER['REMOTE_ADDR'];
	    }
	    return $ip;
	}

    //display to error on the login form 
	public static function login_errors_multi_session(){
		if ( isset( $_GET['exceed_max_concurrent_login'] ) ) {
			learndash_get_template_part(
				'modules/alert.php',
				array(
					'type'    => 'warning',
					'icon'    => 'alert',
					'message' => esc_html__('Multiple sessions detected. All your sessions have been terminated. Please try logging in again.', 'learndash-integrity'),
				),
				true
			);
		}
	}

    /**
	 * Save login transient data on user login
	 *
	 * @param  string $user_login User's username.
	 * @param  object $user       WP_User object.
	 * @return void
	 */
	public static function save_login_transient_on_user_login( $user_login, $user ) {

        /* 
            Other LearnDash Method Codes...
        */

        //Add this at the bottom of the other codes
        set_transient( 'learndash_user_login_ip_' . $user->ID, self::get_ip_address());
    }

    /**
	 * Check if login quota is available
	 *
	 * @param  int $user_id WP_User ID.
	 * @return boolean True if available|false otherwise
	 */
	public static function is_login_quota_available( $user_id ) {
		
        /*
            Other LearnDash Method Codes...
        */

		$transient_user_ip = get_transient( 'learndash_user_login_ip_' . $user_id );
		$current_user_ip = self::get_ip_address();

        // Will replace the position of the other condition within the method.
		if ($transient_user_ip != false ) {
			if ( $transient_user_ip == $current_user_ip ) {
	        	return true;
	    	}
		} else {
			if ($login_bypass || $transient || (!$transient && $cookie_timestamp === $transient_timestamp)) {
	        	return true;
	    	} else {
	    		return false;
	    	}
		}
	    
	}

    //Change the destroy_user_session method
    public static function destroy_user_sessions( $meta_ids, $user_id, $meta_key, $meta_value ) {
		if ( $meta_key === 'session_tokens' ) {
			delete_transient( 'learndash_user_login_' . $user_id );
			delete_transient( 'learndash_user_login_ip_' . $user_id ); //this is my code.
		}
	}

}