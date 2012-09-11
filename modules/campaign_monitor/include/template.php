<?php

/*
Name: template.php
Description: API to send and receive template data.
*/

class cm_template extends campaign_monitor{

   public $TemplateID;
   public $Name;
   public $PreviewURL;
   public $ScreenshotURL;
   public $HtmlPageURL;
   public $ZipFileURL;
   
   public function __construct( $api_key=FALSE, $type='json' ){
      return parent::__construct( $api_key, $type );
      }

   public function __get( $property ){
      if( property_exists( $this, $property ) ){
         return $this->$property;
      }else{
         return FALSE;
         }
      }
      
   public function get( $TemplateID=FALSE ){
      $this->path = 'templates/'.$TemplateID;

      return $this->process( 'GET' );
      }
      
   public function create( $Name=FALSE, $HtmlPageURL=FALSE, $ZipFileURL=FALSE ){
      $this->path = 'templates/'.$client_id;
      $a_request = array( 'Name' => $Name, 'HtmlPageURL' => $HtmlPageURL, 'ZipFileURL' => $ZipFileURL );
      
      $request = $this->parse_request( $a_request );

      return $this->process( 'POST', $request );
      }
      
   public function update(){
      $this->path = 'templates/'.$TemplateID;
      $a_request = array( 'Name' => $Name, 'HtmlPageURL' => $HtmlPageURL, 'ZipFileURL' => $ZipFileURL );
      
      $request = $this->parse_request( $a_request );
      
      return $this->process( 'PUT', $request );
      }
      
   public function delete( $TemplateID=FALSE ){
      $this->path = 'templates/'.$TemplateID;
      $a_request = array( 'Name' => $Name, 'HtmlPageURL' => $HtmlPageURL, 'ZipFileURL' => $ZipFileURL );

      $request = $this->parse_request( $a_request );

      return $this->process( 'DELETE', $request );
      }
   }
?>
