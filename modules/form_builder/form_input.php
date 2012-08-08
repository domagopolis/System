<?php
class form_input{

   public $name;
   public $orm_object;
   public $type;
   public $custom_type;
   public $id;
   public $label;
   public $required;
   public $default_value;
   public $format;
   public $select_values = array();
   public $options = array();

   public function __construct( $name, $orm_object=FALSE, $type='text', $options = array() ){

      require_once('include/format.php');
      
      $this->name = $name;
      $this->orm_object = $orm_object;
      
      $this->type = $type;
      $this->id = $this->name;
      $this->label = ucwords( str_replace( "_", " ", $this->name ) );
      $this->required = FALSE;
      $this->default_value = FALSE;
      $this->format = FALSE;
      $this->select_values = array();
      $this->radio_group = FALSE;
      
      $this->options = $options;

      return $this;
      }

   public function id( $id ){
      $this->id = $id;
      
      return $this;
      }
   
   public function label( $label ){
      $this->label = $label;

      return $this;
      }
      
   public function required_input( $required=TRUE ){
      $this->required = TRUE;

      return $this;
      }
   
   public function select_values( $array=array() ){
      $this->select_values = $array;

      return $this;
      }

   public function default_value( $value=FALSE ){
      $this->default_value = $value;

      return $this;
      }
      
   public function format( $format=FALSE ){
      $this->format = $format;

      return $this;
      }
      
   public function options( $options=array() ){
      $this->options = array_merge( $this->options, $options );

      return $this;
      }

   }
?>
