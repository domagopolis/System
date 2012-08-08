<?php
class fieldset{

   public $legend;
   public $id;

   public $inputs = array();
   public $orm_objects = array();
   public $options = array();

   public function __construct( $legend, $orm_object=array(), $id=FALSE, $options = array() ){

      require_once('include/format.php');
      require_once('form_input.php');
      
      $this->legend = $legend;
      $this->orm_objects = $orm_object;
      if( $id ){
         $this->id = $id;
      }else{
         $this->id = $this->legend;
         }
   
      $this->options = array_merge( $this->options, $options );
      
      return $this;
      }

   public function input( $name, $type='text' ){
      $input_orm_object = FALSE;
      foreach( $this->orm_objects as $orm_object ){
         if( $orm_object AND $orm_object->$name !== FALSE ){
            $input_orm_object = $orm_object;
            }
         }

      if( !array_key_exists( $name, $this->inputs ) ){
         $this->inputs[$name] = new form_input( $name, $input_orm_object, $type );
         }
      
      return $this->inputs[$name];
      }

   public function options( $options ){
      $this->options = array_merge( $this->options, $options );

      return $this;
      }
   }
?>
