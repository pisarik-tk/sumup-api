<?php
/**
 * Copyright (c) 2023, PISARIK.TK (Jan Písařík)
 * All rights reserved.
 * 
 * DOCUMENTATION
 *
 * Call this class
 * $api = new sumupApi($token) @string (sup_sk_xxx)
 *
 * Checkout informations
 * $api->getCheckout($id) @string
 * 
 * Create checkout
 * $api->createCheckout($reference, $amount, $redirect_url, $gateway_url) @int (must be unique), @double, @string, @string
 * (in '$gateway_url' you can use %s for $id)
 * 
 * Delete checkout
 * $api->deleteCheckout($id) @string
 * 
 * Print the current error
 * $api->_error
 *
 * API Documentation: https://developer.sumup.com/api
 */
class sumupApi {
	
	const ACCOUNT_URL 	= 'https://api.sumup.com/v0.1/me';
	const CHECKOUTS_URL 	= 'https://api.sumup.com/v0.1/checkouts/%s';
	
	const CURRENCY 		= 'EUR';
	const DESCRIPTION 	= 'Online card payment (Ref: %s)';
	
	protected $_token;
	protected $_status;
	public $_error;
	
	public function __construct($token) {
        $this->_token = $token;
		$this->getInfo();
		
		if($this->_status == 401){
			$this->_error = 'The API token is invalid';
		}
    }
	
	private function base($method, $url, $data) {
		$opts = [
			'method'  => $method,
			'header'  => "Accept: application/json\r\n".
				     "Content-Type: application/json\r\n".
				     "Authorization: Bearer ".$this->_token
		];
		
		if($method == 'POST' && !empty($data)) {
			$opts['content'] = json_encode($data);
		}
		
		$context = stream_context_create(['http' => $opts]);
		$result  = @file_get_contents($url, false, $context);
		preg_match('/([0-9])\d+/', $http_response_header[0], $status);
		$this->_status = intval($status[0]);
		return json_decode($result);
	}
	
	private function getInfo(){
		return $this->base('GET', self::ACCOUNT_URL, null);
	}
	
	public function getCheckout($id) {
		$url = sprintf(self::CHECKOUTS_URL, $id);
		$result = $this->base('GET', $url, null);
		if($this->_status == 200){
			return $result;
		}else{
			$this->_error = 'Checkout not found';
		}
	}
	
	public function createCheckout($reference, $amount, $redirect_url, $gateway_url) {
		$url = substr(sprintf(self::CHECKOUTS_URL, null), 0, -1);
		$currency = self::CURRENCY;
		$description = sprintf(self::DESCRIPTION, $vs);
		$merchant = $this->getInfo()->merchant_profile->merchant_code;
		
		$data = [
			'amount' => (double)$amount,
			'checkout_reference' => (int)$reference,
			'currency' => $currency,
			'description' => $description,
			'merchant_code' => $merchant,
			'redirect_url' => $redirect_url
		];
		
		$result = $this->base('POST', $url, $data);
		
		if($this->_status == 201){
			$location = sprintf($gateway_url, $result->id);
			header("Location: $location");
		}else if($this->_status == 400){
			$this->_error = 'Invalid parameters (amount or reference)';
		}else if($this->_status == 403){
			$this->_error = 'Unauthorized action';
		}else if($this->_status == 409){
			$this->_error = 'A checkout with this reference already exists';
		}
	}
	
	public function deleteCheckout($id) {
		$url = sprintf(self::CHECKOUTS_URL, $id);
		$result = $this->base('DELETE', $url, null);
		if($this->_status == 200){
			return $result;
		}else if($this->_status == 404){
			$this->_error = 'Checkout not found';
		}else if($this->_status == 409){
			$this->_error = 'The checkout has already been removed';
		}
	}

}
