<?php
class datagrid{

public $orm_objects_pagination;
public $orm_objects;
public $rows_per_page;
public $options = array();
public $filename;

public function __construct( $orm_objects, $options = array() ){

   require_once('include/format.php');
   require_once('pagination/pagination.php');

   $this->orm_objects = $orm_objects;
   
   $this->options = array(
                     'add-record' => FALSE, //False or string post variable for add record button
                     'update-record' => array(), //Edit/Delete or other record update button with record id Button Title => Get or Post
                     'editable-fields' => array(), //Editable column of data Field name => Button label
                     'select' => FALSE, //False or string table heading for select boxes
                     'table_form_submit' => array(), //Table submit buttons. Input Name => button label
                     'totals' => array(), //Array of field names to calculate totals on last row
                     'format' => array(), // date formats and decimal points Field Name => format type
                     'record-link' => array(), // record link to view record details Field Name => URL without $_GET params
                     'show_table_heading' => TRUE, //Boolean to show table heading or not
                     );

   $this->options = array_merge( $this->options, $options );
   $this->filename = $this->orm_objects->table.'_database.csv';

   $this->rows_per_page = 15;

   if( array_key_exists( 'add_record', $_GET ) ){
      header("location: edit_".text::singular( $this->orm_objects->table ).".php");
      }
   
   return $this;
   }

public function filter( $params=array() ){
   $where_clause_arr = array();
   
   if( array_key_exists( "show_all", $params ) ){
      return $where_clause_arr;
      }

   if( array_key_exists( "search", $params ) ){
      foreach( $params as $field => $item ){
         if( is_array( $item ) ){
         foreach( $item as $key => $value ){

            $date = date_create( str_replace( "/", "-", $value ) );
            if( $date instanceof DateTime ){
               $date = strtotime( date_format( $date, 'm/d/Y' ) );
            }else{
               $date = FALSE;
            }
         
            if( $key === "max_field" AND !empty( $value ) ){
               $where_clause_arr = array_merge( $where_clause_arr, array( $field.'<=' => $value ) );
            }else if( $key === "min_field" AND !empty( $value ) ){
               $where_clause_arr = array_merge( $where_clause_arr, array( $field.'>=' => $value ) );
            }else if( $key === "field_range_low" AND !empty( $value ) ){
               if( $date ){
                  $where_clause_arr = array_merge( $where_clause_arr, array( $field.'>=' => $date ) );
               }else{
                  $where_clause_arr = array_merge( $where_clause_arr, array( $field.'>=' => $value ) );
                  }
            }else if( $key === "field_range_high" AND !empty( $value ) ){
               if( $date ){
                  $where_clause_arr = array_merge( $where_clause_arr, array( $field.'<' => $date+24*60*60 ) );
               }else{
                  $where_clause_arr = array_merge( $where_clause_arr, array( $field.'<=' => $value ) );
                  }
            }else if( $key === "field_in" AND !empty( $value ) ){
               $id_list = "";
               foreach( $value as $key => $id ){
                  $id_list .= $id.",";
                  }
               $id_list = rtrim( $id_list, "," );
               $where_clause_arr = array_merge( $where_clause_arr, array( $field.' IN' => $id_list ) );
            }else if( $key === "field" AND is_numeric( $value ) ){
               $where_clause_arr = array_merge( $where_clause_arr, array( $field => $value ) );
            }else if( $key === "field" AND !empty( $value ) AND $date ){
               $where_clause_arr = array_merge( $where_clause_arr, array( $field.'>=' => $date, $field.'<' => $date+24*60*60 ) );
            }else if( $key === "field" AND !empty( $value ) ){
               $where_clause_arr = array_merge( $where_clause_arr, array( $field => '%'.$value.'%' ) );
               }
            }
         }
      }
   }
      
   return $where_clause_arr;
   }
   
public function load_data( $orm_objects, $params=array(), $limit=FALSE, $offset=FALSE ){
   ( array_key_exists( "page", $params ) )?$page = $params['page']:$page = 1;
   $orm_objects_pagination = clone $orm_objects;

   //Use find_all if there are no 2nd table relation
   if( $this->orm_objects->table === $orm_objects->table ){
      $orm_objects_pagination_arr = $orm_objects_pagination->find_all( $limit, $offset )->{$this->orm_objects->object_data_arr};
      $orm_objects_arr = $orm_objects->find_all( ($page-1)*$this->rows_per_page, $this->rows_per_page )->{$this->orm_objects->object_data_arr};
   //Use the relation of the table given in datagrid construct
   }else if( $this->orm_objects->table ){
      $orm_objects_pagination_arr = $orm_objects_pagination->limit( $limit, $offset )->{$this->orm_objects->table}->{$this->orm_objects->object_data_arr};
      $orm_objects_arr = $orm_objects->limit( ($page-1)*$this->rows_per_page, $this->rows_per_page )->{$this->orm_objects->table}->{$this->orm_objects->object_data_arr};
      }
   
   $class = text::singular( $this->orm_objects->table );

   $this->orm_objects_pagination = new $class;
   $this->orm_objects_pagination->{$this->orm_objects->object_data_arr} = $orm_objects_pagination_arr;

   $this->orm_objects = new $class;
   $this->orm_objects->{$this->orm_objects->object_data_arr} = $orm_objects_arr;
   }

public function get_total_records(){
   return sizeof( $this->orm_objects->{$this->orm_objects->object_data_arr} );
   }

public function upload(){
   if( array_key_exists( "preview",  $_POST ) OR array_key_exists( "enter",  $_POST ) ){
      if( fnmatch( "*.csv", $_FILES['importfile']['name'] ) ){
         $fd = fopen( $_FILES['importfile']['tmp_name'], "r" );

         $field_arr = fgetcsv($fd, 1024);

         $class = text::singular( $this->orm_objects->table );
         
         $this->orm_objects->{$this->orm_objects->object_data_arr} = array();
         
         $i = 0;
         
         while( !feof( $fd ) ){
            $data_record_arr = fgetcsv($fd, 1024);

            if( is_array( $data_record_arr ) AND !in_array( NULL, $data_record_arr ) ){
               $object = new $class;
               foreach( $field_arr as $key => $value ){
                  $object->{str_replace(" ", "_",  strtolower( $value ) )} = $data_record_arr[$key];
                  }

               if( array_key_exists( "enter",  $_POST ) ){
                  $object->save();
            
                  $this->orm_objects->{$this->orm_objects->object_data_arr}[$object->{$object->id_field}] = $object;
                  $this->orm_objects->{$this->orm_objects->object_data_arr}[$object->{$object->id_field}]->selected_fields = array();
                  foreach( $field_arr as $key => $value ){
                     $value = str_replace(" ", "_", strtolower( $value ) );
                     $this->orm_objects->{$this->orm_objects->object_data_arr}[$object->{$object->id_field}]->selected_fields[$value] = $value;
                     }
               }else{
                  $this->orm_objects->{$this->orm_objects->object_data_arr}[$i] = $object;
                  $this->orm_objects->{$this->orm_objects->object_data_arr}[$i]->selected_fields = array();
                  foreach( $field_arr as $key => $value ){
                     $value = str_replace(" ", "_", strtolower( $value ) );
                     $this->orm_objects->{$this->orm_objects->object_data_arr}[$i]->selected_fields[$value] = $value;
                     }
                  $i++;
                  }
               }
            }
            
         fclose($fd);
      }else{
         $object->errors = "Import file must be a CSV file in form";
         }
      }
   }

public function display_upload_form(){
   include('view/upload-form.php');
   }

public function display_filter_form(){
   include('view/filter-form.php');
   }

public function display_download(){
   include('view/download-link.php');
   }

public function display_table( $rows_per_page=FALSE ){

   $pagination = new pagination( $this->orm_objects_pagination, ($rows_per_page)?$rows_per_page:$this->rows_per_page );
   
   include('view/table.php');
   }
   }
?>
