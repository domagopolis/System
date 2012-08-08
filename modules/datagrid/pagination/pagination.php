<?php
class pagination{

   public $orm_objects;
   public $rows_per_page = 10;
   public $options;

   public function __construct( $orm_objects=FALSE, $rows_per_page=FALSE, $options=array() ){

      if( $orm_objects ){
         $this->orm_objects = $orm_objects;
         $this->record_count = count( $this->orm_objects->{$this->orm_objects->object_data_arr} );

         if( $rows_per_page ){
            $this->rows_per_page = $rows_per_page;
            }
            
         $this->options = $options;

         return $this;
      }else{
         return FALSE;
         }
         
      }

   public function display_pagination( $style="numbered" ){
      include('view/'.$style.'.php');
      }

   }
?>
