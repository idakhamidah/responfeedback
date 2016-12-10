<?php
/*
	@wira iseng aje
*/
class Akses_tweet { 
	private $config = array(
		'use_whitelist' => true,
		'base_url' => 'https://api.twitter.com/1.1/'
	);
	private $whitelist = array();

	public function __construct(
			$oauth_access_token, 
			$oauth_access_token_secret, 
			$consumer_key, 
			$consumer_secret, $user_id, 
			$screen_name, 
			$count = 10) {

		$this->config = array_merge($this->config, compact('oauth_access_token', 'oauth_access_token_secret', 'consumer_key', 'consumer_secret', 'user_id', 'screen_name', 'count'));

		$this->whitelist['statuses/user_timeline.json?user_id=' . $this->config['user_id'] . '&screen_name=' . $this->config['screen_name'] . '&count=' . $this->config['count']] = true;
	}

	private function setURLString($baseURI, $method, $params) {
		$r = array();
		ksort($params);
		foreach($params as $key=>$value){
			$r[] = "$key=" . rawurlencode($value);
		} 
		return $method . "&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $r));
	}

	private function setOtentikasi($oauth) {
		$r = 'Authorization: OAuth ';
		$values =array();
		foreach($oauth as $key => $value) {
			$values[] = "$key=\"" . rawurlencode($value) . "\"";
		}
		$r .= implode(', ', $values);

		return $r;
	}
	
	public function get($url) {
		if (! isset($url)){
			die('URL belum dimasukan');
		}		
		 
		if ($this->config['use_whitelist'] && ! isset($this->whitelist[$url])){
			die('URL tidak bisa diakses');
		}
		 
		$url_parts = parse_url($url);
		parse_str($url_parts['query'], $url_arguments);
		 
		$full_url = $this->config['base_url'] . $url; 
		$base_url = $this->config['base_url'] . $url_parts['path'];

		//otentikasi
		$oauth = array(
			'oauth_consumer_key' => $this->config['consumer_key'],
			'oauth_nonce' => time(),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_token' => $this->config['oauth_access_token'],
			'oauth_timestamp' => time(),
			'oauth_version' => '1.0'
		);

		$base_info = $this->setURLString($base_url, 'GET', array_merge($oauth, $url_arguments));
		
		$composite_key = rawurlencode($this->config['consumer_secret']) . '&' . rawurlencode($this->config['oauth_access_token_secret']);

		$oauth['oauth_signature'] = base64_encode(hash_hmac('sha1', $base_info, $composite_key, true));
		 
		// Make Requests
		$header = array(
			$this->setOtentikasi($oauth), 
			'Expect:'
		);
		$options = array(
			CURLOPT_HTTPHEADER => $header, 
			CURLOPT_HEADER => false,
			CURLOPT_URL => $full_url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false
		);
		 
		$feed = curl_init();
		curl_setopt_array($feed, $options);
		$result = curl_exec($feed);
		$info = curl_getinfo($feed);
		curl_close($feed);
		  
		if (isset($info['content_type']) && isset($info['size_download'])){
			header('Content-Type: ' . $info['content_type']);
			header('Content-Length: ' . $info['size_download']);
		}

		return $result;
	}
}