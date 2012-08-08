<?php
/*
Name: paypal_standard.php
Description: Classes to enter and process paypal standard payment information.
*/
class payment_paypal_standard_driver{
	//Properties
	var $return_url;
    var $ipn_url;
	var $cancel_url;
    var $amount;
	var $currency_code;

	//Methods
	function __construct( $config ){
		$this->test_mode = $config['test_mode'];
		$this->return_url = $config['return_url'];
        $this->ipn_url = $config['ipn_url'];
		$this->cancel_url = $config['cancel_url'];
		
		if($this->test_mode){
			$this->paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
		}else{
			$this->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
			}
		}

   function create_order( $basket, $payment_gateway_account ){
   
      $basket_items = $basket->basket_items;
      $this->currency_code = $payment_gateway_account->currency->currency_code;
      
      $form = new form_builder( 'paypal_standard_form', FALSE, 'post', $this->paypal_url );
      $form->input( 'cmd', 'hidden' )->default_value( '_cart' );
      $form->input( 'upload', 'hidden' )->default_value( '1' );
      $form->input( 'item_name', 'hidden' )->default_value( 'Shopping Cart' );
      $i=1;
      $this->amount = 0;
      foreach( $basket_items->basket_item_arr as $basket_item ){
         $purchase_item = $basket_item->purchase_item;
         $form->input( 'item_name_'.$i, 'hidden' )->default_value( $purchase_item->purchase_item_title );
         $form->input( 'item_number_'.$i, 'hidden' )->default_value( $purchase_item->item_no );
         $form->input( 'amount_'.$i, 'hidden' )->default_value( $purchase_item->cost );
         $form->input( 'quantity_'.$i, 'hidden' )->default_value( $basket_item->qty );
         $this->amount += $basket_item->qty * $purchase_item->cost;
         $i++;
         }
      $form->input( 'amount', 'hidden' )->default_value( $this->amount );
      $form->input( 'business', 'hidden' )->default_value( $payment_gateway_account->email );
      $form->input( 'currency_code', 'hidden' )->default_value( $this->currency_code );
      $form->input( 'button_subtype', 'hidden' )->default_value( 'products' );
      $form->input( 'return', 'hidden' )->default_value( $this->return_url );
      $form->input( 'notify_url', 'hidden' )->default_value( $this->ipn_url );
      $form->input( 'cancel_return', 'hidden' )->default_value( $this->cancel_url );
      $form->input( 'go_to_paypal', 'button' );

      return $form;
      }

	function process(){
		}
	}
?>
