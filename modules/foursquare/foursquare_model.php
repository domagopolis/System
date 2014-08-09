<?php

/*
Name: foursquare_model.php
Description: Classes to constuct foursquare models.
*/

class foursquare_venue{

   public $id;
   public $name;
   public $latitude;
   public $longitude;
   public $address;
   public $city;
   public $state;
   public $postcode;
   public $country;
   public $twitter;
   public $phone;
   public $url;
   public $categories;
   public $description;
   
   public function __construct( $venue=FALSE ){
      $this->id = $venue->id;
      $this->name = $venue->name;
      $this->latitude = $venue->location->lat;
      $this->longitude = $venue->location->lng;
      $this->address = $venue->location->address;
      $this->city = $venue->location->city;
      $this->state = $venue->location->state;
      $this->postcode = $venue->location->postcode;
      $this->country = $venue->location->country;

      $this->twitter = $venue->contact->twitter;
      $this->phone = $venue->contact->phone;
      $this->phone = $venue->contact->formattedPhone;

      $this->url = $venue->url;

      $this->categories = array();
      foreach( $venue->categories as $category ){
        $this->categories[$category->id] = $category->name;
      }

      $this->description = $venue->description;

      return $this;
      }

   public function __get( $property ){
      if( property_exists( $this, $property ) ){
         return $this->$property;
      }else{
         return FALSE;
         }
      }
   }
?>