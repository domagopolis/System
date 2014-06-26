<?php

/*
Name: abn_lookup.php
Description: Classes to retrieve ABN details.
*/

class abn_lookup{

   private $authentication_guid;
   private $client;
   private $result;
   
   public function __construct( $authentication_guid=NULL, $abn=FALSE ){
      $this->url = 'http://abr.business.gov.au/abrxmlsearch/ABRXMLSearch.asmx?WSDL';
      $this->authentication_guid = $authentication_guid;
      $this->result = FALSE;

      $this->client = new soapclient( $this->url );
      
/*      $err = $this->client->getError();
      if ($err){
         echo '<p class=error>Constructor error: '.$err.'</p>';
         }*/
      if( $abn ){
         $param = array('searchString' => $abn, 'includeHistoricalDetails' => 'N', 'authenticationGuid' => $this->authentication_guid);
         if( !$this->call_search_by_abn( $param ) ){
            return FALSE;
            }
         }
         
      return TRUE;
      }

   public function __get( $property ){
      if( property_exists( $this, $property ) ){
         return $this->$property;
      }else{
         return FALSE;
         }
      }
      
   public function search( $abn=FALSE ){
      if( !$abn AND !$this->result ){
         return FALSE;
         }

      $abn = str_replace( " ", "", $abn );

      if( !$this->result ){
         $param = array('searchString' => $abn, 'includeHistoricalDetails' => 'N', 'authenticationGuid' => $this->authentication_guid);
         if( !$this->call_search_by_abn( $param ) ){
            return FALSE;
            }
         }

      return $this->result;
      }
      
   public function search_by_name( $name=FALSE ){
      if(!$name and !$this->result){
         return FALSE;
      }
      
      if( !$this->result ){
         $param = array( 'name' => $name, 'authenticationGuid' => $this->authentication_guid, 'filters' => array() );
         if( !$this->call_search_by_name( $param ) ){
            return FALSE;
         }
      }
      
      return $this->result;
   }

   public function validate( $abn=FALSE ){
      if( !$abn AND !$this->result ){
         return FALSE;
         }
      
      if( !$this->result ){
         $param = array('searchString' => $abn, 'includeHistoricalDetails' => 'N', 'authenticationGuid' => $this->authentication_guid);
         if( !$this->call_search_by_abn( $param ) ){
            return FALSE;
            }
         }

      return ( property_exists( $this->result->ABRPayloadSearchResults->response, 'businessEntity' ) AND $this->result->ABRPayloadSearchResults->response->businessEntity->entityStatus->entityStatusCode === 'Active' )?TRUE:FALSE;
      }

   public function get_given_name( $abn=FALSE ){
      if( !$abn AND !$this->result ){
         return FALSE;
         }

      if( !$this->result ){
         $param = array('searchString' => $abn, 'includeHistoricalDetails' => 'N', 'authenticationGuid' => $this->authentication_guid);
         if( !$this->call_search_by_abn( $param ) ){
            return FALSE;
            }
         }

      if( !property_exists( $this->result->ABRPayloadSearchResults->response->businessEntity, 'legalName' ) ){
         return FALSE;
         }

      $name = array(
         'first_name' => $this->result->ABRPayloadSearchResults->response->businessEntity->legalName->givenName,
         'middle_name' => $this->result->ABRPayloadSearchResults->response->businessEntity->legalName->otherGivenName,
         'last_name' => $this->result->ABRPayloadSearchResults->response->businessEntity->legalName->familyName,
         );
         
      return $name;
      }

   public function get_organisation( $abn=FALSE ){
      if( !$abn AND !$this->result ){
         return FALSE;
         }

      if( !$this->result ){
         $param = array('searchString' => $abn, 'includeHistoricalDetails' => 'N', 'authenticationGuid' => $this->authentication_guid);
         if( !$this->call_search_by_abn( $param ) ){
            return FALSE;
            }
         }

      if( !property_exists( $this->result->ABRPayloadSearchResults->response, 'businessEntity' ) ){
         return FALSE;
         }
      
      if( !property_exists( $this->result->ABRPayloadSearchResults->response->businessEntity, 'mainName' ) AND !property_exists( $this->result->ABRPayloadSearchResults->response->businessEntity, 'mainTradingName' ) ){
         return FALSE;
         }
      
      $organisation = array();
      if( property_exists( $this->result->ABRPayloadSearchResults->response->businessEntity, 'mainName' ) ){
         $organisation['organisation_name'] = $this->result->ABRPayloadSearchResults->response->businessEntity->mainName->organisationName;
         }

      if( property_exists( $this->result->ABRPayloadSearchResults->response->businessEntity, 'mainTradingName' ) ){
         $organisation['trading_name'] = $this->result->ABRPayloadSearchResults->response->businessEntity->mainTradingName->organisationName;
         }

      return $organisation;
      }

   public function get_address( $abn=FALSE ){
      if( !$abn AND !$this->result ){
         return FALSE;
         }

      if( !$this->result ){
         $param = array('searchString' => $abn, 'includeHistoricalDetails' => 'N', 'authenticationGuid' => $this->authentication_guid);
         if( !$this->call_search_by_abn( $param ) ){
            return FALSE;
            }
         }

      if( !property_exists( $this->result->ABRPayloadSearchResults->response, 'businessEntity' ) ){
         return FALSE;
         }

      $address = array(
         'state' => $this->result->ABRPayloadSearchResults->response->businessEntity->mainBusinessPhysicalAddress->stateCode,
         'postcode' => $this->result->ABRPayloadSearchResults->response->businessEntity->mainBusinessPhysicalAddress->postcode,
         );

      return $address;
      }

   private function call_search_by_abn( $param=array() ){
      $this->result = $this->client->__call('ABRSearchByABN', array('parameters' => $param));

      return $this->result;
      }
      
   private function call_search_by_name( $param=array() ){
      $this->result = $this->client->__call('ABRSearchByName', array('parameters' => $param));
      
      return $this->result;
      }
   }
   
   }
?>
