<?php
/*
Name: eway.php
Description: Classes to enter and process eway payment information.
*/
class payment_eway_driver{
	//Properties
	public $test_mode;
	public $customer_id;
    public $cvn_enabled;
    public $amount;
	public $currency_code;
	public $authorisation_request;
	private $return_url;
	private $eway_url;
	private $xml_str;
	private $auth_trxn_number;
	private $post_data;

   function __construct( $config ){
      $this->return_url = $config['return_url'];
      $this->test_mode = $config['test_mode'];
      $this->cvn_enabled = $config['cvn_enabled'];
      $this->customer_id = ( array_key_exists( 'customer_id', $config ) )?$config['customer_id']:FALSE;
      $this->amount = ( array_key_exists( 'amount', $config ) )?$config['amount']:FALSE;
      $this->authorisation_request = ( array_key_exists( 'authorise', $config ) )?$config['authorise']:FALSE;

      $this->auth_trxn_number = FALSE;
      $this->post_data = array();
		
      if( $this->cvn_enabled AND !array_key_exists( 'transaction_number', $_SESSION ) ){
         $gateway = 'gateway_cvn';
      }else{
         $gateway = 'gateway';
         }

      if($this->test_mode){
         $this->eway_url = 'https://www.eway.com.au/'.$gateway.'/xmltest/';
      }else{
         $this->eway_url = 'https://www.eway.com.au/'.$gateway.'/';
         }
      }

   function create_order( basket $basket=NULL, payment_gateway_account $payment_gateway_account=NULL ){
   
      if( $basket instanceof basket ){
         $basket_items = $basket->basket_items;
         $this->amount = $basket->total;
         if( $basket->payment_gateway_account ){
            $payment_gateway_account = $basket->payment_gateway_account;
            }
         }

      if( $payment_gateway_account instanceof payment_gateway_account AND $payment_gateway_account->payment_gateway_account_id ){
         $this->currency_code = $payment_gateway_account->currency->currency_code;
         $this->customer_id = $payment_gateway_account->account_identity;
         $this->test_mode = $payment_gateway_account->test_account;
         }

      $expiry_months_arr = array( '01' => '01', '02' => '02', '03' => '03', '04' => '04', '05' => '05', '06' => '06', '07' => '07', '08' => '08', '09' => '09', '10' => '10', '11' => '11', '12' => '12' );
      $expiry_years_arr = array( date('Y') => date('Y'), date('Y')+1 => date('Y')+1, date('Y')+2 => date('Y')+2, date('Y')+3 => date('Y')+3, date('Y')+4 => date('Y')+4 );
      
      $form = new form_builder( 'eway_form' );
      $form->options( array( 'enctype' => 'multipart/form-data' ) );
      if( $this->amount > 0 ){
         $form->input( 'amount', 'hidden' )->default_value( $this->amount*100 );
      }else{
         $form->input( 'amount' )->required_input();
         }
      $form->input( 'card_holders_name' )->required_input();
      $form->input( 'card_number' )->required_input();
      $form->input( 'expiry_month', 'select' )->required_input()->select_values( $expiry_months_arr );
      $form->input( 'expiry_year', 'select' )->required_input()->select_values( $expiry_years_arr );
      if( $this->cvn_enabled ){
         $form->input( 'cvn' )->label( 'CVN' )->required_input();
         }
      $form->input( 'pay', 'button' );

      return $form;
      }

   function process(){

//unset($_SESSION['transaction_number']); //remove later
      if( $this->authorisation_request ){
         if( array_key_exists( 'transaction_number', $_SESSION ) AND !empty( $_SESSION['transaction_number'] ) ){
            $this->auth_trxn_number = $_SESSION['transaction_number'];
            $this->authorisation_complete();
         }else{
            $this->authorisation_request();
            }
      }else{
         $this->payment();
         }
      
      $ch = curl_init( $this->eway_url.$this->script.'.asp' );

      // Set custom curl options
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

      // Set the curl POST fields
      curl_setopt($ch, CURLOPT_POSTFIELDS, $this->xml_str);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      // Execute post and get results
      $response = curl_exec($ch);
      curl_close ($ch);
      $xml_response = simplexml_load_string( $response );

      if( !array_key_exists( 'transaction_number', $_SESSION ) OR empty( $_SESSION['transaction_number'] ) ){
         $_SESSION['transaction_number'] = (string) $xml_response->ewayTrxnNumber;
         header("Location: ".$this->return_url);
         }

      return ( $xml_response AND $xml_response->ewayTrxnStatus=='True') ? $xml_response : TRUE;
      }
      
   public function load_post_data( $post_data=array() ){
      $this->post_data = $post_data;
      
      return TRUE;
      }

   private function payment(){
      $writer = $this->xml_header();

      $writer->startElement( 'ewaygateway' );
      $writer->startElement( 'ewayCustomerID' );
      $writer->writeCData( $this->customer_id );
      $writer->endElement();
      $writer->startElement( 'ewayTotalAmount' );
      $writer->writeCData( $this->amount*100 );
      $writer->endElement();
      $writer->startElement( 'ewayCustomerFirstName' );
      $writer->endElement();
      $writer->startElement( 'ewayCustomerLastName' );
      $writer->endElement();
      $writer->startElement( 'ewayCustomerEmail' );
      $writer->endElement();
      $writer->startElement( 'ewayCustomerAddress' );
      $writer->endElement();
      $writer->startElement( 'ewayCustomerPostcode' );
      $writer->endElement();
      $writer->startElement( 'ewayCustomerInvoiceDescription' );
      $writer->endElement();
      $writer->startElement( 'ewayCustomerInvoiceRef' );
      $writer->endElement();
      $writer->startElement( 'ewayCardHoldersName' );
      if( array_key_exists( 'card_holders_name', $this->post_data ) ){
         $writer->writeCData( $this->post_data['card_holders_name'] );
         }
      $writer->endElement();
      $writer->startElement( 'ewayCardNumber' );
      if( array_key_exists( 'card_number', $this->post_data ) ){
         $writer->writeCData( $this->post_data['card_number'] );
         }
      $writer->endElement();
      $writer->startElement( 'ewayCardExpiryMonth' );
      if( array_key_exists( 'expiry_month', $this->post_data ) ){
         $writer->writeCData( $this->post_data['expiry_month'] );
         }
      $writer->endElement();
      $writer->startElement( 'ewayCardExpiryYear' );
      if( array_key_exists( 'expiry_year', $this->post_data ) ){
         $writer->writeCData( $this->post_data['expiry_year'] );
         }
      $writer->endElement();
      $writer->startElement( 'ewayTrxnNumber' );
      $writer->endElement();
      $writer->startElement( 'ewayOption1' );
      $writer->endElement();
      $writer->startElement( 'ewayOption2' );
      $writer->endElement();
      $writer->startElement( 'ewayOption3' );
      $writer->endElement();
      $writer->startElement( 'ewayCVN' );
      if( array_key_exists( 'cvn', $this->post_data ) ){
         $writer->writeCData( $this->post_data['cvn'] );
         }
      $writer->endElement();
      $writer->endElement();

      $this->xml_str = $writer->outputMemory(false);

      $this->script = ( $this->test_mode )?'testpage':'xmlpayment';
      }

   private function authorisation_request(){
      $writer = $this->xml_header();

      $writer->startElement( 'ewaygateway' );
      $writer->startElement( 'ewayCustomerID' );
      $writer->writeCData( $this->customer_id );
      $writer->endElement();
      $writer->startElement( 'ewayTotalAmount' );
      $writer->writeCData( $this->amount*100 );
      $writer->endElement();
      $writer->startElement( 'ewayCustomerFirstName' );
      $writer->endElement();
      $writer->startElement( 'ewayCustomerLastName' );
      $writer->endElement();
      $writer->startElement( 'ewayCustomerEmail' );
      $writer->endElement();
      $writer->startElement( 'ewayCustomerAddress' );
      $writer->endElement();
      $writer->startElement( 'ewayCustomerPostcode' );
      $writer->endElement();
      $writer->startElement( 'ewayCustomerInvoiceDescription' );
      $writer->endElement();
      $writer->startElement( 'ewayCustomerInvoiceRef' );
      $writer->endElement();
      $writer->startElement( 'ewayCardHoldersName' );
      if( array_key_exists( 'card_holders_name', $this->post_data ) ){
         $writer->writeCData( $this->post_data['card_holders_name'] );
         }
      $writer->endElement();
      $writer->startElement( 'ewayCardNumber' );
      if( array_key_exists( 'card_number', $this->post_data ) ){
         $writer->writeCData( $this->post_data['card_number'] );
         }
      $writer->endElement();
      $writer->startElement( 'ewayCardExpiryMonth' );
      if( array_key_exists( 'expiry_month', $this->post_data ) ){
         $writer->writeCData( $this->post_data['expiry_month'] );
         }
      $writer->endElement();
      $writer->startElement( 'ewayCardExpiryYear' );
      if( array_key_exists( 'expiry_year', $this->post_data ) ){
         $writer->writeCData( $this->post_data['expiry_year'] );
         }
      $writer->endElement();
      $writer->startElement( 'ewayTrxnNumber' );
      $writer->endElement();
      $writer->startElement( 'ewayOption1' );
      $writer->endElement();
      $writer->startElement( 'ewayOption2' );
      $writer->endElement();
      $writer->startElement( 'ewayOption3' );
      $writer->endElement();
      $writer->startElement( 'ewayCVN' );
      if( array_key_exists( 'cvn', $this->post_data ) ){
         $writer->writeCData( $this->post_data['cvn'] );
         }
      $writer->endElement();
      $writer->endElement();

      $this->xml_str = $writer->outputMemory(false);

      $this->script = 'xmlauth';
      if( $this->test_mode ){ $this->script = 'testpage'; } //remove later
      }
      
   private function authorisation_complete(){
      $writer = $this->xml_header();

      $writer->startElement( 'ewaygateway' );
      $writer->startElement( 'ewayCustomerID' );
      $writer->writeCData( $this->customer_id );
      $writer->endElement();
      $writer->startElement( 'ewayTotalAmount' );
      $writer->writeCData( $this->amount*100 );
      $writer->endElement();
      $writer->startElement( 'ewayAuthTrxnNumber' );
      $writer->writeCData( $this->auth_trxn_number );
      $writer->endElement();
      $writer->startElement( 'ewayCardExpiryMonth' );
      if( array_key_exists( 'expiry_month', $this->post_data ) ){
         $writer->writeCData( $this->post_data['expiry_month'] );
         }
      $writer->endElement();
      $writer->startElement( 'ewayCardExpiryYear' );
      if( array_key_exists( 'expiry_year', $this->post_data ) ){
         $writer->writeCData( $this->post_data['expiry_year'] );
         }
      $writer->endElement();
      $writer->startElement( 'ewayOption1' );
      $writer->endElement();
      $writer->startElement( 'ewayOption2' );
      $writer->endElement();
      $writer->startElement( 'ewayOption3' );
      $writer->endElement();
      $writer->endElement();

      $this->xml_str = $writer->outputMemory(false);

      $this->script = 'xmlauthcomplete';
      }
      
   private function authorisation_void(){
      $writer = $this->xml_header();

      $writer->startElement( 'ewaygateway' );
      $writer->startElement( 'ewayCustomerID' );
      $writer->writeCData( $this->customer_id );
      $writer->endElement();
      $writer->startElement( 'ewayTotalAmount' );
      $writer->writeCData( $this->amount*100 );
      $writer->endElement();
      $writer->startElement( 'ewayAuthTrxnNumber' );
      $writer->writeCData( $this->auth_trxn_number );
      $writer->endElement();
      $writer->endElement();

      $this->xml_str = $writer->outputMemory(false);

      $this->script = 'xmlauthvoid';
      }
      
   private function xml_header(){
      $writer = new XMLWriter();

      $writer->openMemory();
      $writer->setIndent( TRUE );
      $writer->setIndentString( "    " );
      $writer->startDocument( "1.0", "UTF-8" );
      
      return $writer;
      }
   }
?>
