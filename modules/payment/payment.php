<?php
/*
Name: payment.php
Description: Classes to enter and process paypal payments information.
*/
class payment{
   protected $config = array
   (
      // The driver string
      'driver'      => NULL,
      // Test mode is set to true by default
      'test_mode'   => TRUE,
   );
	
   protected $driver = NULL;

   public function __construct($config = array()){
      GLOBAL $document_root_path;

      if (empty($config)){
         // Load the default group
      }elseif (is_string($config)){
         $this->config['driver'] = $config;
      }elseif (is_array($config)){
         $this->config = array_merge($this->config, $config);
         }

      if( !class_exists( 'payment_'.$this->config['driver'].'_driver' ) ){
         include('payments/'.$this->config['driver'].'.php');
         include($document_root_path.'include/payment_configurations.php');
         }
         
      $external_config = get_payment_configuration( $this->config['driver'] );
      
      $driver = 'payment_'.$this->config['driver'].'_driver';

      $this->config = array_merge( $this->config, $external_config );

      $this->driver = new $driver($this->config);
      }
      
   public function create_order( basket $basket=NULL, payment_gateway_account $payment_gateway_account=NULL ){
      return $this->driver->create_order( $basket, $payment_gateway_account );
      }
   
   public function process(){
      return $this->driver->process();
      }

   public function load_post_data( $post_data=array() ){
      return $this->driver->load_post_data( $post_data );
      }
   }
?>
