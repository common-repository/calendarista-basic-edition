<?php
class Calendarista_NonceHelper{
    public static function valid($key = 'calendarista_nonce', $token = 'calendarista-ajax-nonce', $kill = false) {
		if(!function_exists('wp_create_nonce')) {
			include(ABSPATH . "wp-includes/pluggable.php"); 
		}
		$noncePost = isset($_REQUEST[$key]) ? $_REQUEST[$key] : null;
		if($noncePost && is_array($noncePost)){
			$noncePost = $noncePost[0];
		}
		if($noncePost && wp_verify_nonce($noncePost, $token)){
			return true;
		}
		if($kill){
			esc_html_e('You have been inactive for too long and your session has expired. Please refresh the page again to continue.', 'calendarista');
			wp_die();
		}
		return false;
    }
}
?>