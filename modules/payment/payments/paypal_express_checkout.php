<?php
/*
Name: paypal_express_checkout.php
Description: Classes to enter and process paypal express checkout payment information.
*/
class payment_paypal_express_checkout_driver{
	//Properties
	var $api_username;
	var $api_password;
	var $api_signature;
	var $api_endpoint;
	var $paypal_url;
	var $version;
	var $amt;
	var $token;
	var $payerid;
	var $payment_action;
	var $return_url;
	var $cancel_url;
	var $error_url;
	var $currency_code;

   //Methods
   public function __construct( $config ){
      $this->api_username = '';
      $this->api_password = '';
      $this->api_signature = '';
      $this->version = '3.0';
      $this->payment_action = 'Sale';
      $this->payment_action = ( array_key_exists( 'payment_action', $config ) )?$config['payment_action']:'Sale';
      $this->test_mode = TRUE;
      $this->return_url = $config['return_url'];
      $this->cancel_url = $config['cancel_url'];
      $this->error_url = '';

		$this->curl_config = array
		(
			CURLOPT_HEADER         => FALSE,
			CURLOPT_SSL_VERIFYPEER => FALSE,
			CURLOPT_SSL_VERIFYHOST => FALSE,
			CURLOPT_VERBOSE        => TRUE,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_POST           => TRUE
		);

		if($this->test_mode){
			$this->api_endpoint = 'https://api.sandbox.paypal.com/nvp';
			$this->paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=';
		}else{
			$this->api_endpoint = 'https://api-3t.paypal.com/nvp';
			$this->paypal_url = 'https://www.paypal.com/webscr&cmd=_express-checkout&token=';
			}
		}

   public function create_order( $basket, $payment_gateway_account ){

      $this->amt = $basket->total;
      $this->currency_code = $basket->currency->currency_code;
      $this->api_username = $payment_gateway_account->api_username;
      $this->api_password = $payment_gateway_account->api_password;
      $this->api_signature = $payment_gateway_account->api_signiture;

      $this->payerid = $basket->payment_token;

      return TRUE;
      }
   
	public function process(){

		if ( !array_key_exists( 'paypal_token', $_SESSION ) ){
			$this->paypal_login();
			return FALSE;
			}

		// Post data for submitting to server
		$data = '&TOKEN='.$_SESSION['paypal_token'].
		        '&PAYERID='.$this->payerid.
		        '&IPADDRESS='.urlencode($_SERVER['SERVER_NAME']).
		        '&Amt='.$this->amt.
		        '&PAYMENTACTION='.$this->payment_action.
		        '&ReturnUrl='.$this->return_url.
		        '&CANCELURL='.$this->cancel_url.
		        '&CURRENCYCODE='.$this->currency_code.
                '&COUNTRYCODE=AU';

		$response    = $this->contact_paypal('DoExpressCheckoutPayment', $data);
		$nvpResArray = $this->deformatNVP($response);

		return ($nvpResArray['ACK'] == 'Success');
		}

   public function load_post_data( $post_data=array() ){
      $this->post_data = $post_data;

      return TRUE;
      }

	private function paypal_login(){
		$data = '&Amt='.$this->amt.
		'&PAYMENTACTION='.$this->payment_action.
		'&ReturnURL='.$this->return_url.
		'&CancelURL='.$this->cancel_url.
        '&CURRENCYCODE='.$this->currency_code;

		$reply = $this->contact_paypal('SetExpressCheckout', $data);
		//$this->session->set(array('reshash' => $reply));

		$reply = $this->deformatNVP($reply);

		$ack = strtoupper($reply['ACK']);

		if ($ack == 'SUCCESS'){
			$paypal_token = urldecode($reply['TOKEN']);

			// Redirect to paypal.com here
			$_SESSION['paypal_token'] = $paypal_token;

			// We are off to paypal to login!
			header("Location: ".$this->paypal_url.$paypal_token);
		}else{
			header("Location: ".$this->error_url);
			}
		}

	private function contact_paypal($method, $data){
		$final_data = 'METHOD='.urlencode($method).
			'&VERSION='.urlencode($this->version).
			'&PWD='.urlencode($this->api_password).
			'&USER='.urlencode($this->api_username).
			'&SIGNATURE='.urlencode($this->api_signature).$data;

		$ch = curl_init($this->api_endpoint);

		// Set custom curl options
		curl_setopt_array($ch, $this->curl_config);
		curl_setopt($ch, CURLOPT_POST, 1);

		// Setting the nvpreq as POST FIELD to curl
		curl_setopt($ch, CURLOPT_POSTFIELDS, $final_data);

		// Getting response from server
		$response = curl_exec($ch);

		if (curl_errno($ch)){
			// Moving to error page to display curl errors
			//$this->session->set_flash(array('curl_error_no' => curl_errno($ch), 'curl_error_msg' => curl_error($ch)));
			header("Location: ".$this->error_url);
		}else{
			curl_close($ch);
			}

		return $response;
		}

	private function deformatNVP($nvpstr){
		$intial   = 0;
		$nvpArray = array();

		while (strlen($nvpstr)){
			// Postion of Key
			$keypos = strpos($nvpstr, '=');

			// Position of value
			$valuepos = strpos($nvpstr, '&') ? strpos($nvpstr, '&') : strlen($nvpstr);

			// Getting the Key and Value values and storing in a Associative Array
			$keyval = substr($nvpstr, $intial, $keypos);
			$valval = substr($nvpstr, $keypos + 1, $valuepos - $keypos - 1);

			// Decoding the respose
			$nvpArray[urldecode($keyval)] = urldecode( $valval);

			$nvpstr = substr($nvpstr, $valuepos + 1, strlen($nvpstr));
			}

		return $nvpArray;
		}
	}
?>
