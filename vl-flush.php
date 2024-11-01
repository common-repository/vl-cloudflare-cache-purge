<?php
/**
 * This will flush CF Cache
 * andb then cf kv value
 */

 /** 
  * WP HTTP API functions
  *	to ge the headers -> wp_remote_retrieve_headers($result)
  *	to ge the body -> wp_remote_retrieve_body($result),
  */
class VLFlush
{
	function __construct() {
		add_action( 'post_updated', [ $this, 'flush_cf_kv' ] );
		register_activation_hook( dirname(__FILE__) . "/vl-cloudflare-cache-purge.php", array( $this , 'activate' ) );
	}

	/**
	 * This function will check the requirement and activate the plugin
	 */
	public function activate(){
		if(!defined( 'CF_AUTH_TOKEN')){
			deactivate_plugins(plugin_basename(dirname(__FILE__) . "/vl-cloudflare-cache-purge.php"));
			wp_die(__( 'Please define CF_AUTH_TOKEN in wp-config.php and try again.', 'vl-cloudflare-cache-purge' ), 'Plugin dependency check', array( 'back_link' => true ) );
		}
		if(!defined( 'CF_ZONE_ID')){
			deactivate_plugins(plugin_basename(dirname(__FILE__) . "/vl-cloudflare-cache-purge.php"));
			wp_die(__( 'Please define CF_ZONE_ID in wp-config.php and try again.', 'vl-cloudflare-cache-purge' ), 'Plugin dependency check', array( 'back_link' => true ) );
		} 

		if(!defined( 'CF_KV_AUTH')){
			deactivate_plugins(plugin_basename(dirname(__FILE__) . "/vl-cloudflare-cache-purge.php"));
			wp_die(__( 'Please define CF_KV_AUTH in wp-config.php and try again.', 'vl-cloudflare-cache-purge' ), 'Plugin dependency check', array( 'back_link' => true ) );
		}
	}


	/**
	 * This function will check the post_id and if this post is eligible for
	 * cache purge then it will purge the cache
	 */
	public function flush_cf_kv($post_id) {
		if(!empty($post_id)){
			// Checks save status
			$is_autosave = wp_is_post_autosave( $post_id );
			$is_revision = wp_is_post_revision( $post_id );
			$post_type = get_post_type($post_id);
			// Exits script depending on save status
			if ( $is_autosave || $is_revision || ($post_type == 'nav_menu_item')) {
				return;
			} 
			$url = get_permalink($post_id);
			# flush cf cache
			$cf_flush_result = $this->_flush_cache($url);
			if(!$cf_flush_result){
				return new WP_Error( 'broke', "Failed to clear cloudflare cache");
			}
			# flush cf kv
			$kv_flush_result = $this->_flush_kv($url);
			if(!$kv_flush_result){
				return new WP_Error( 'broke', "Failed to clear KV static cache");
			}
		}
	}
	
	/**
	 * This function will delete the Cloudflare cache
	 */
	private function _flush_cache( $url ) {
		$cf_api_url = 'https://api.cloudflare.com/client/v4/zones/' . CF_ZONE_ID . '/purge_cache';
		$post_data = [
			'method' => 'POST',
			'body' => json_encode([
				'files' => [$url]
			]),
			'headers' => [
				'Authorization' => 'Bearer ' . CF_AUTH_TOKEN,
				'Content-Type' => 'application/json'
			]
		];
		
		$result = wp_remote_request( $cf_api_url, $post_data );
		if(is_wp_error($result)) {
			$error_obj = [
				'api_url' => $cf_api_url,
				'headers'  => $post_data['headers'],
				'post_data' => json_decode($post_data['body']),
				'api_result' => $result->get_error_messages(),
				'code' => 0,
			];
			error_log(json_encode($error_obj));
			return false;
		}
		if(!empty($result['body']) && is_string($result['body'])){
			$json_result = json_decode($result['body'], true);
		}else{
			$json_result = ['success' => false, 'errors' => [['code' => 0, 'message' => 'no result returned by the api']]];
		}
		$code = $result['response']['code'];
		if($code != '200' || !$json_result['success']){
			$error_obj = [
				'api_url' => $cf_api_url,
				'headers'  => $post_data['headers'],
				'post_data' => json_decode($post_data['body']),
				'api_result' => $json_result,
				'code' => $code,
			];
			error_log(json_encode($error_obj));
			return false;
		}else{
			return true;
		}
	}

	/**
	 * This function will delete the KV static cache
	 */

	private function _flush_kv( $cf_api_url ) {
		$auth = base64_encode( CF_KV_AUTH );
		$post_data = [
			'method' => 'POST',
			'timeout' => 10,
			'headers' => [
				'X-KV-Clear' => '1',
				'Authorization' => "Basic $auth",
			]
		];
		$result = wp_remote_request( $cf_api_url, $post_data );
		if(is_wp_error($result)) {
			$error_obj = [
				'target_url' => $cf_api_url,
				'code' => 0,
				'curl_error' => $result->get_error_messages()
			];
			error_log(json_encode($error_obj));
			return false;
		}
		$code = $result['response']['code'];
		if($code != '200'){
			$error_obj = [
				'target_url' => $cf_api_url,
				'code' => $code,
				'curl_error' => $result['body']
			];
			error_log(json_encode($error_obj));
			return false;
		}else{
			return true;
		}
	}
}