<?
/*
Name: google_checkout.php
Description: Classes to enter and process google checkout payments information.
*/
class google_checkout{
  //Properties
  var $google_API_key;
  var $google_merchant_id;
  var $google_sandbox_API_key;
  var $google_sandbox_merchant_id;
  var $currency_code;

  var $xml_header;
  var $xml_str;
  var $curl_config;
  var $test_mode;

   //Methods
   function __construct($test_mode=FALSE){
      $this->google_API_key = 'B5WC8qQ56eAlb9JAtgYFdw';
      $this->google_merchant_id = '413022890617280';
      $this->google_sandbox_API_key = 'B5WC8qQ56eAlb9JAtgYFdw';
      $this->google_sandbox_merchant_id = '413022890617280';
      $this->currency_code = 'GBP';
      $this->test_mode = $test_mode;
      
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
         $this->base64encoding = base64_encode($this->google_sandbox_merchant_id.":".$this->google_sandbox_API_key);
      }else{
         $this->base64encoding = base64_encode($this->google_merchant_id.":".$this->google_API_key);
         }
			
      $this->xml_header = array("Authorization: Basic ".$this->base64encoding, "Content-Type: application/xml;charset=UTF-8", "Accept: application/xml;charset=UTF-8");
      }

   function create_xml_request(){
      $this->xml_str = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
	
      $this->xml_str .= '<checkout-shopping-cart xmlns="http://checkout.google.com/schema/2">'."\n";
      $this->xml_str .= '<shopping-cart>'."\n";
      $this->xml_str .= '<items>'."\n";
      $this->xml_str .= '<item>'."\n";
      $this->xml_str .= '<item-name>Product Fuel</item-name>'."\n";
      $this->xml_str .= '<item-description>Description</item-description>'."\n";
      $this->xml_str .= '<unit-price currency="'.$this->currency_code.'">0.01</unit-price>'."\n";
      $this->xml_str .= '<quantity>1</quantity>'."\n";
      $this->xml_str .= '</item>'."\n";
      $this->xml_str .= '</items>'."\n";	
      $this->xml_str .= '</shopping-cart>'."\n";
      $this->xml_str .= '<checkout-flow-support>'."\n";
      $this->xml_str .= '<merchant-checkout-flow-support>'."\n";
      $this->xml_str .= '<continue-shopping-url>http://kotest.mukurujosh.ath.cx/checkout</continue-shopping-url>'."\n";
      $this->xml_str .= '</merchant-checkout-flow-support>'."\n";
      $this->xml_str .= '</checkout-flow-support>'."\n";
      $this->xml_str .= '</checkout-shopping-cart>'."\n";
	
	  return $this->xml_str;
      }

   function create_charge_order_xml_request($google_order_total, $google_order_number){
      $this->xml_str = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
	
      $this->xml_str .= '<charge-order xmlns="http://checkout.google.com/schema/2" google-order-number="'.$google_order_number.'">'."\n";
      $this->xml_str .= '<amount currency="'.$this->currency_code.'">'.$google_order_total.'</amount>'."\n";
      $this->xml_str .= '</charge-order>'."\n";
		
      return $this->xml_str;
      }
      
   function process(){
      $post_url = ($this->test_mode)
         ? 'https://sandbox.google.com/checkout/api/checkout/v2/merchantCheckout/Merchant/'.$this->google_merchant_id // Test mode URL
         : 'https://checkout.google.com/api/checkout/v2/merchantCheckout/Merchant/'.$this->google_merchant_id; // Live URL

         $ch = curl_init($post_url);

         // Set custom curl options
         curl_setopt($ch, CURLOPT_POST, true);

         // Set the curl POST fields
         curl_setopt($ch, CURLOPT_HTTPHEADER, $this->xml_header);
         curl_setopt($ch, CURLOPT_POSTFIELDS, $this->xml_str);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

         // Execute post and get results
         $response = curl_exec($ch);
         curl_close ($ch);

         return (!empty($response)) ? $response : false;
      }
      
   function get_xml_response($post_data){
      return isset($post_data)?$post_data:file_get_contents("php://input");;
      }
   }
?>