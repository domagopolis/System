<?php
class form_builder{

   public $name;
   public $id;
   public $action;
   public $method;
   public $errors = FALSE;
   
   public $fieldsets = array();
   public $inputs = array();
   public $orm_objects = array();
   public $options = array();

   public function __construct( $name, $orm_object=FALSE, $method='post', $action = '', $id=FALSE, $options=array() ){

      require_once('include/format.php');
      require_once('fieldset.php');
      require_once('form_input.php');
      
      $this->name = $name;
      $this->orm_objects[] = $orm_object;
      if( $id ){
         $this->id = $id;
      }else{
         $this->id = $this->name;
         }
         
      $this->action = $action;
      $this->method = $method;
   
      $this->options = $options;

      return $this;
      }

   public function fieldset( $legend, $id=FALSE, $options=array() ){

      if( !array_key_exists( $legend, $this->fieldsets ) ){
         $this->fieldsets[$legend] = new fieldset( $legend, $this->orm_objects, $id, $options );
         }
      
      return $this->fieldsets[$legend];
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

   public function display_form(){
      ob_start();
      include('view/form.php');
      $form = ob_get_contents();
      ob_end_clean();
      
      echo $form;
      }

   public function form_to_string(){
      ob_start();
      include('view/form.php');
      $form = ob_get_contents();
      ob_end_clean();

      return $form;
      }

   }
?>
