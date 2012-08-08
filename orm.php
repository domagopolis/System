<?php

/*
Name: database.php
Description: Classes to enter, check, save and delete database information.
If your table is a name in plural form define a class of the same name in singular form and extend to this class.
Irregular plurals are not yet covered.
*/

class orm{

   public $table;
   public $prefix = "";
   public $id_field;
   public $object_data_arr;

   public $selected_fields = array();
   
   private $select_arr = array();
   private $where_clause_arr = array();
   private $having_clause_arr = array();
   private $order_by_arr = array();
   private $group_by_arr = array();
   private $join_arr = array();
   private $limit_arr = array( "limit" => FALSE, "offset" => FALSE );
   
   private $debug_sql = false;

   public function __construct( $id_value=FALSE ){
      $this->table = $this->prefix.text::plural( $this->called_class );
      $this->id_field = $this->called_class."_id";
      $this->object_data_arr = $this->called_class."_arr";

      $this->where_clause_arr['and'] = array();
      $this->where_clause_arr['or'] = array();
      
      $this->having_clause_arr['and'] = array();
      $this->having_clause_arr['or'] = array();

      //If id given find and load the record
      if( $id_value ){
         $this->where( array( $this->id_field => $id_value ) )->find();

         return $this;
      }else{
         return $this;
         }
      }

   public function __get( $key ){

      //Get field value
      if( property_exists( $this, $key ) ){
         return $this->$key;
      //Get one to one relation using id from this table
      }else if( class_exists( $key ) AND property_exists( $this, $key.'_id' ) AND !empty( $this->{$key.'_id'} )  ){
         $class_obj = new $key( $this->{$key.'_id'} );
         
         $this->$key = $class_obj;

         return $class_obj;
      //Get one to many or many to many relations for this table
      }else if( preg_match( '/([A-Za-z0-9_-]+)(s|sses|xes|ies)\Z/i', $key ) AND class_exists( text::singular( $key ) ) ){

         $singular_class = text::singular( $key );
         $class_obj = new $singular_class;
         $id_arr = array();
         
         //merge existing where clauses to object class_obj
         $class_obj->select_arr = $this->select_arr;
         $class_obj->join_arr = $this->join_arr;
         $class_obj->where_clause_arr = $this->where_clause_arr;
         $class_obj->having_clause_arr = $this->having_clause_arr;
         $class_obj->group_by_arr = $this->group_by_arr;
         $class_obj->order_by_arr = $this->order_by_arr;
         $class_obj->limit_arr = $this->limit_arr;
         
         //If this instance has object data
         if( $this->{$this->id_field} ){
            //If class id is found on this table use it else look for the id in class obj table
            if( $this->{$class_obj->id_field} ){
               $class_obj->where( array( $class_obj->id_field => $this->{$class_obj->id_field} ) )->order_by( array( $class_obj->id_field ) )->find_all();
            }else{
               $class_obj->where( array( $this->id_field => $this->{$this->id_field} ) )->order_by( array( $class_obj->id_field ) )->find_all();
               }
         //If this instance instead has an array of object data
         }else if( $this->{$this->object_data_arr} AND is_array( $this->{$this->object_data_arr} ) AND sizeof( $this->{$this->object_data_arr} ) > 0 ){
            $id_list = "";
            foreach( $this->{$this->object_data_arr} as $data_obj ){
               
               //If class id is found on this table use it else look for the id in class obj table
               if($data_obj->{$class_obj->id_field}){
                  $id_field = $class_obj->id_field;
               }else{
                  $id_field = $this->id_field;
                  }
               $id_list .= $data_obj->$id_field.",";
               }
            $id_list = rtrim( $id_list, "," );
            if( $id_list !== "" ){
               $class_obj->where( array( $id_field." IN" => $id_list ) )->order_by( array( $class_obj->id_field ) )->find_all();
            }else{
               $class_obj->where( array( $id_field." IN" => "0" ) )->order_by( array( $class_obj->id_field ) )->find_all();
               }
               
            if( sizeof( $class_obj->{$class_obj->object_data_arr} ) > 0 ){
               $id_arr = array();
               foreach( $this->{$this->object_data_arr} as $data_obj ){
                  foreach( $class_obj->{$class_obj->object_data_arr} as $class_data_obj ){
                     if( $data_obj->{$this->id_field} === $class_data_obj->{$this->id_field} ){
                        $id_arr[ $class_obj->{$this->id_field} ][] = $class_data_obj->{$class_obj->id_field};
                     }else if( $data_obj->{$class_obj->id_field} === $class_data_obj->{$class_obj->id_field} ){
                        $id_arr[ $data_obj->{$this->id_field} ][] = $class_data_obj->{$class_obj->id_field};
                        }
                     }
                  }
               }
            }

         //If failed to find records try looking for pivot table data
         if( sizeof( $class_obj->{$class_obj->object_data_arr} ) === 0 ){

            //erase the previous attempt for creating class obj array
            if( $this->{$this->object_data_arr} AND is_array( $this->{$this->object_data_arr} ) ){
               foreach( $this->{$this->object_data_arr} as $object_data_arr_value ){
                  $object_data_arr_value->{$class_obj->object_data_arr} = array();
                  }
               }
               
            $id_arr = $this->get_pivot_ids( $this->table, $key );
            
            //loop though the 2d array of pivot ids given and build an id list for the joining table
            if( is_array( $id_arr ) ){
               $id_list = "";
               foreach( $id_arr as $id1 => $id2_arr ){
                  if ( is_array( $id2_arr ) ){
                     foreach( $id2_arr as $id2 ){
                        $id_list .= $id2.",";
                        }
                     }
                  }
               $id_list = rtrim( $id_list, "," );
               $class_obj->where_clause_arr = $this->where_clause_arr;
               if( $id_list !== "" ){
                  $class_obj->where( array( $class_obj->id_field." IN" => $id_list ) )->find_all();
               }else{
                  $class_obj->where( array( $class_obj->id_field." IN" => "0" ) )->find_all();
                  }
               
               }
            }

         //copy objects from class_obj to this instance data record relations with ids as reference
         if( is_array( $id_arr ) AND is_array( $this->{$this->object_data_arr} ) AND is_array( $class_obj->{$class_obj->object_data_arr} ) ){
            foreach( $id_arr as $id1 => $id2_arr ){
               if( is_array( $id2_arr ) ){
                  foreach( $id2_arr as $id2 ){
                     if( array_key_exists( $id2, $class_obj->{$class_obj->object_data_arr} ) ){
                        $this->{$this->object_data_arr}[$id1]->{$class_obj->object_data_arr} = array();
                        $this->{$this->object_data_arr}[$id1]->{$class_obj->object_data_arr}[$id2] = $class_obj->{$class_obj->object_data_arr}[$id2];
                        }
                     }
                  }
               }
            }

//         $this->$key = $class_obj;
         $this->clear_clauses();
         
         return $class_obj;
      //property does not exist return false
      }else{
         return FALSE;
         }
      }
      
   public function query( $sql=FALSE ){
      if( $sql ){
      
         if( $this->debug_sql ){
            echo $sql."<br><br>";
            }

         $result = mysql_query( $sql );
      
         return $result;
      }else{
         return FALSE;
         }
      }
      
   private function fetch_data( $result=FALSE ){
      if( $result ){
         return mysql_fetch_array( $result );
      }else{
         return $this;
         }
      }

   //Prepare input data for database entry
   public function set_by_key($key, $value){
      if( is_string( $value ) ){
         $value = trim($value);
         $value = stripslashes($value);
         $value = htmlentities($value);
         }
      $this->$key = $value;
      }

   //Load all elements of row array into properties of this object
   private function load_data( $row=NULL ){
      if( !is_array( $row ) ){
         return $this;
         }

      $this->selected_fields = array();
      
      foreach( $row as $key => $value ){
         if( !is_int( $key ) ){
            $this->$key = $value;
            $this->selected_fields[$key] = $key;
            }
         }
         
      return $this;
      }
      
   //Find all fields belonging to a table
   private function get_fields(){
      $sql = "SHOW COLUMNS FROM ".$this->table;

      $result = $this->query( $sql );

      while ( $row = $this->fetch_data( $result ) ) {
         $fields[] = $row['Field'];
         }

      return $fields;
      }
      
   //Prepare data for saving
   private function prepare_data( $value ){
      //unhtml the entries
//      $trans_tbl = get_html_translation_table (HTML_ENTITIES);
//      $trans_tbl = array_flip ($trans_tbl);
//      return strtr($value, $trans_tbl);
        $value = str_replace( "'", "\'", $value );
        
        return $value;
      }

   //Validate to check empty fields that are compulsory
   public function validate( $compulsory_arr=array() ){
      $errors = false;

      foreach( $compulsory_arr as $compulsory_field ){
         if( empty( $this->$compulsory_field) ){
            $error_field = $compulsory_field."_error_field";
            $this->$error_field = "This field is missing";
            $errors = true;
            }
         }
         
      return $errors;
      }
      
   //Method to enter and merge selected fields
   public function select( $select_arr=array() ){
      $this->select_arr = array_merge( $this->select_arr, $select_arr );
      
      return $this;
      }

   //Method to enter and merge where clauses expecting to be joined by AND
   public function where( $where_clause_arr=array() ){
      $this->where_clause_arr['and'] = array_merge( $this->where_clause_arr['and'], $where_clause_arr );
      
      return $this;
      }

   //Method to enter and merge where clauses expecting to be joined by OR
   public function where_or( $where_clause_arr=array() ){
      if( sizeof( $this->where_clause_arr['and'] ) > 0 ){
         //$this->where_clause_arr['and'] = array_merge( $this->where_clause_arr['and'], array( 'or' => $where_clause_arr ) );
         $this->where_clause_arr['or'] = array_merge( $this->where_clause_arr['or'], $where_clause_arr );
      }else{
         $this->where_clause_arr['or'] = array_merge( $this->where_clause_arr['or'], $where_clause_arr );
         }
      
      return $this;
      }

   //Method to enter and merge having clauses expecting to be joined by AND
   public function having( $having_clause_arr=array() ){
      $this->having_clause_arr['and'] = array_merge( $this->having_clause_arr['and'], $having_clause_arr );

      return $this;
      }

   //Method to enter and merge having clauses expecting to be joined by OR
   public function having_or( $having_clause_arr=array() ){
      if( sizeof( $this->having_clause_arr['and'] ) > 0 ){
         $this->having_clause_arr['or'] = array_merge( $this->having_clause_arr['or'], $having_clause_arr );
      }else{
         $this->having_clause_arr['or'] = array_merge( $this->having_clause_arr['or'], $having_clause_arr );
         }

      return $this;
      }
      
   //Method to enter and merge order by clauses
   public function order_by( $order_by_arr=array() ){
      $output_arr = array();

      foreach( $order_by_arr as $key => $value ){
         //If $key is non string and not the name of the field
         if( is_int($key) ){
            $output_arr[$value] = NULL;
         //$value is DESC or ASC
         }else{
            $output_arr[$key] = $value;
            }
         }
         
      $this->order_by_arr = array_merge( $this->order_by_arr, $output_arr );
      
      return $this;
      }

   //Method to enter and merge group by clauses
   public function group_by( $group_by_arr=array() ){
      $this->group_by_arr = array_merge( $this->group_by_arr, $group_by_arr );

      return $this;
      }

   //Method to add a join from $table1 to $table2, $join_type can be left, right, etc
   public function join( $table1, $table2=FALSE, $join_type=0 ){
      if( !$table2 ){
         $table2 = $this->table;
         }

      $join_arr = array( $table1 => array( $table2 => $join_type ) );

      $this->join_arr = array_merge( $this->join_arr, $join_arr );
      
      return $this;
      }

   public function limit( $limit=FALSE, $offset=FALSE ){
      $this->limit_arr = array( "limit" => $limit, "offset" => $offset );

      return $this;
      }

   //Build the sql to fetch records from database
   private function build_select_sql(){
   
      $select_sql = $join_sql = $where_sql = $having_sql = $group_by_sql = $order_by_sql = $limit_sql = '';

      if( sizeof( $this->select_arr ) > 0 ){
         $select_sql = "SELECT ";
         foreach( $this->select_arr as $key => $value ){
            if( array_key_exists( $this->table, $this->join_arr ) AND $value === $this->id_field ){
               $key_table = $this->table.".";
            }else{
               $key_table = "";
               }

            if( is_int( $key) ){
               $select_sql .= $key_table.$value.", ";
            }else{
               $select_sql .= $key_table.$value." AS ".$key.", ";
               }
            }
         $select_sql = rtrim( $select_sql, ", " );
         $select_sql .= " FROM ".$this->table;
      }else{
         $select_sql = "SELECT * FROM ".$this->table;
         }

      if( sizeof( $this->join_arr ) > 0 ){
         $join_sql = '';
         foreach( $this->join_arr as $table1 => $joining ){
            foreach( $joining as $table2 => $join_type ){
               if( strstr( $select_sql, "FROM ".$table2 ) OR strstr( $join_sql, "JOIN ".$table2 ) ){
                  $join_sql .= ( ( is_string( $join_type ) )?" ".strtoupper( $join_type ):"" )." JOIN ".$table1." ON ".$table2.".".text::singular($table1)."_id = ".$table1.".".text::singular($table1)."_id ";
               }else{
                  $join_sql .= ( ( is_string( $join_type ) )?" ".strtoupper( $join_type ):"" )." JOIN ".$table2." ON ".$table2.".".text::singular($table1)."_id = ".$table1.".".text::singular($table1)."_id ";
                  }
               }
            }
         }
      
      if( sizeof( $this->where_clause_arr['and'] ) > 0 OR sizeof( $this->where_clause_arr['or'] ) > 0 ){
         $where_sql .= " WHERE ";
         
         if( sizeof( $this->where_clause_arr['or'] ) > 0 )
         {
            $this->where_clause_arr['and'] = array_merge( $this->where_clause_arr['and'], array( 'or' => $this->where_clause_arr['or'] ) );
         }
         unset( $this->where_clause_arr['or'] );

         $where_sql .= $this->append_clause( $this->where_clause_arr, $join_sql );
         }
         
      if( sizeof( $this->having_clause_arr['and'] ) > 0 OR sizeof( $this->having_clause_arr['or'] ) > 0 ){
         $having_sql .= " HAVING ";

         if( sizeof( $this->having_clause_arr['or'] ) > 0 )
         {
            $this->having_clause_arr['and'] = array_merge( $this->having_clause_arr['and'], array( 'or' => $this->having_clause_arr['or'] ) );
         }
         unset( $this->having_clause_arr['or'] );

         $having_sql .= $this->append_clause( $this->having_clause_arr, $join_sql );
         }

      if( sizeof( $this->group_by_arr ) > 0 ){
         $group_by_sql .= " GROUP BY ";
         foreach( $this->group_by_arr as $key => $value ){
            if( preg_match( '/(.*) JOIN ([A-Za-z0-9_]+) ON ([A-Za-z0-9_]+).'.preg_replace( '/([A-Za-z0-9]+)([<>=]+| IN)/i', '$1', $value ).' (.*)/i', $join_sql ) ){
               $group_table = preg_replace( '/(.*) JOIN ([A-Za-z0-9_]+) ON ([A-Za-z0-9_]+).'.preg_replace( '/([A-Za-z0-9]+)([<>=]+| IN)/i', '$1', $value ).' (.*)/i', '$2.', $join_sql );
            }else{
               $group_table = "";
               }

            $group_by_sql .= $group_table.$value.", ";
            }
         $group_by_sql = rtrim( $group_by_sql, ", " );
         }

      if( sizeof( $this->order_by_arr ) > 0 ){
         $order_by_sql .= " ORDER BY ";
         foreach( $this->order_by_arr as $key => $value ){
            if( preg_match( '/(.*) JOIN ([A-Za-z0-9_]+) ON ([A-Za-z0-9_]+).'.preg_replace( '/([A-Za-z0-9]+)([<>=]+| IN)/i', '$1', $key ).' (.*)/i', $join_sql ) ){
               $order_table = preg_replace( '/(.*) JOIN ([A-Za-z0-9_]+) ON ([A-Za-z0-9_]+).'.preg_replace( '/([A-Za-z0-9]+)([<>=]+| IN)/i', '$1', $key ).' (.*)/i', '$2.', $join_sql );
            }else{
               $order_table = "";
               }

            if( empty( $value ) ){
               $order_by_sql .= $order_table.$key.", ";
            }else{
               $order_by_sql .= $order_table.$key." ".$value.", ";
               }
            }
         $order_by_sql = rtrim( $order_by_sql, ", " );
         }

      if( array_key_exists( "limit", $this->limit_arr ) AND $this->limit_arr['limit'] !== FALSE ){
         $limit_sql .= " LIMIT ".$this->limit_arr['limit'];

         if( array_key_exists( "offset", $this->limit_arr ) AND $this->limit_arr['offset'] !== FALSE ){
            $limit_sql .= ", ".$this->limit_arr['offset'];
            }
         }

      return $select_sql.$join_sql.$where_sql.$having_sql.$group_by_sql.$order_by_sql.$limit_sql;
      }

   //parsing a clause structured with AND and OR
   private function append_clause( $clause_arr=array(), $join_sql=false ){
      $boolean_operator = key( $clause_arr );
      $clause_sql = '';

      if( !$boolean_operator ){
         return FALSE;
         }

      foreach( $clause_arr[$boolean_operator] as $key => $value ){
         if( is_array( $value ) ){
            $subclause_arr = array( $key => $value );
            $clause_sql .= " ( ".$this->append_clause( $subclause_arr, $join_sql )." ) ".strtoupper( $boolean_operator )." ";
            }
            
         $value = $this->prepare_data( $value );
            
         if( preg_match( '/(.*) JOIN ([A-Za-z0-9_]+) ON ([A-Za-z0-9_]+).'.preg_replace( '/([A-Za-z0-9]+)([<>=]+| IN)/i', '$1', $key ).' (.*)/i', $join_sql ) ){
            $key_table = preg_replace( '/(.*) JOIN ([A-Za-z0-9_]+) ON ([A-Za-z0-9_]+).'.preg_replace( '/([A-Za-z0-9]+)([<>=]+| IN)/i', '$1', $key ).' (.*)/i', '$2.', $join_sql );
         }else{
            $key_table = "";
            }

         if( $key === "password" ){
            $clause_sql .= $key_table.$key." = PASSWORD('".$value."') ".strtoupper( $boolean_operator )." ";
         }else if( $key != 'or' ){
            if( preg_match( '/([A-Za-z0-9]+)([<>=]+)/i', $key ) ){
               $clause_sql .= $key_table.preg_replace( '/([A-Za-z0-9]+)([<>=]+)/i', '$1 $2', $key )." '".$value."' ".strtoupper( $boolean_operator )." ";
            }else if( preg_match( '/%(.*)%/i', $value ) ){
               $clause_sql .= $key_table.$key." LIKE '".$value."' ".strtoupper( $boolean_operator )." ";
            }else if( preg_match( '/([A-Za-z0-9]+)( IN)/i', $key ) ){
               $clause_sql .= $key_table.preg_replace( '/([A-Za-z0-9]+)( IN)/i', '$1', $key )." IN (".$value.") ".strtoupper( $boolean_operator )." ";
            }else{
               $clause_sql .= $key_table.$key." = '".$value."' ".strtoupper( $boolean_operator )." ";
               }
            }
         }
         $clause_sql = rtrim( $clause_sql, ' '.strtoupper( $boolean_operator ).' ' );

         return $clause_sql;
      }
   
   //Reset sql clause arrays ready for another query
   private function clear_clauses(){
      $this->select_arr = array();
      $this->where_clause_arr['and'] = array();
      $this->where_clause_arr['or'] = array();
      $this->having_clause_arr['and'] = array();
      $this->having_clause_arr['or'] = array();
      $this->order_by_arr = array();
      $this->group_by_arr = array();
      $this->join_arr = array();
      $this->limit_arr = array( "limit" => FALSE, "offset" => FALSE );
      }

   //Return a pivot table joining $table1 and $table2 checking first if table exists
   private function get_pivot_table( $table1=FALSE, $table2=FALSE ){
      $table = $table1."_".$table2;

      //Find which table comes first in alphabetical order
      if( strcmp( $table1, $table2 ) === -1 ){
         $table = $table1."_".$table2;
      }else{
         $table = $table2."_".$table1;
         }

/*      if( !$this->fetch_data( $this->query( "SHOW TABLES LIKE '".$table."'" ) ) ){
         return FALSE;
         }*/

      return $table;
      }

   //Build a 2d array for 2 ids from one pivot table joining $table1 and $table2
   private function get_pivot_ids( $table1=FALSE, $table2=FALSE ){

      if( !$table = $this->get_pivot_table( $table1, $table2 ) ){
         return FALSE;
         }

      $object_data_arr = $this->object_data_arr;

      if( $this->{$this->id_field} ){
         $sql = "SELECT ".text::singular( $table1 )."_id AS id1, ".text::singular( $table2 )."_id AS id2 FROM ".$table." WHERE ".$this->id_field." = '".$this->{$this->id_field}."'";
      }else if( $this->$object_data_arr AND is_array( $this->$object_data_arr ) AND sizeof( $this->$object_data_arr ) > 0 ){
         $id_list = "";
         foreach( $this->$object_data_arr as $data_obj ){
            $id_list .= $data_obj->{$this->id_field}.",";
            }
         $id_list = rtrim( $id_list, "," );

         $sql = "SELECT ".text::singular( $table1 )."_id AS id1, ".text::singular( $table2 )."_id AS id2 FROM ".$table." WHERE ".$this->id_field." IN (".$id_list.")";
         }

      $id_arr = array();
      
      if( ( $result = $this->query( $sql ) ) === FALSE ){
         return FALSE;
         }
      
      while( $row = $this->fetch_data( $result ) ){
         $id_arr[ $row['id1'] ][] = $row['id2'];
         }
         
      return $id_arr;
      }

   //Find and load result into object for one record
   public function find(){
   
      $sql = $this->build_select_sql()." LIMIT 1";

      $this->clear_clauses();
      
      if( $row = $this->fetch_data( $this->query( $sql ) ) ){
         return $this->load_data( $row );
      }else{
         return FALSE;
         }

      }

   //Find and load results into an array of objects for multiple records
   public function find_all( $limit=FALSE, $offset=FALSE ){
      
      $called_class = $this->called_class;
      
      $data_arr = $called_class."_arr";
      
      $record_arr = array();

      if( $limit !== FALSE ){
         $this->limit( $limit, $offset );
         }

      $sql = $this->build_select_sql();

      $result = $this->query( $sql );

      if( !$result ){
         $this->$data_arr = $record_arr;

         return $this;
         }
            
      $this->clear_clauses();
      
      while( $row = $this->fetch_data( $result ) ){
         $class = new $called_class();
         $class->load_data( $row );
         $record_arr[$class->{$class->id_field}] = $class;
         }
         
      $this->$data_arr = $record_arr;
      
      return $this;
      }
      
   //Find if record exists with where clause not include the record belonging to this object
   public function record_exists(){

      $this->where( array( $this->id_field.'<>' => $this->{$this->id_field} ) );
      
      $sql = $this->build_select_sql()." LIMIT 1";

      if( $row = $this->fetch_data( $this->query( $sql ) ) ){
         $this->clear_clauses();
         return TRUE;
      }else{
         $this->clear_clauses();
         return FALSE;
         }
      }

   //Save this object as a record in table
   public function save(){
      
      $fields_arr = $this->get_fields();
      
      if( empty( $this->{$this->id_field} ) ){
         $sql = "INSERT INTO ".$this->table." (";
      
         foreach( $fields_arr as $field ){
            if( property_exists( $this, $field ) AND $field !== $this->id_field ){
                $sql .= $field.", ";
                }
            }

         $sql = rtrim( $sql, ", " );
         $sql .= ") VALUES (";

         foreach( $fields_arr as $field ){
            if( property_exists( $this, $field ) AND $field !== $this->id_field ){
               $this->$field = $this->prepare_data( $this->$field );
               if( $field === "password" ){
                  $sql .= "PASSWORD('".$this->$field."'), ";
               }else{
                  $sql .= "'".$this->$field."', ";
                  }
               }
            }
         
         $sql = rtrim( $sql, ", " );
         $sql .= ")";

         $result = $this->query( $sql );

         $this->{$this->id_field} = mysql_insert_id();
      
      }else{
         $sql = "UPDATE ".$this->table." SET ";

         foreach( $fields_arr as $field ){
            if( property_exists( $this, $field ) AND $field !== $this->id_field AND $this->$field !== FALSE ){
               $this->$field = $this->prepare_data( $this->$field );
               if( $field === "password" ){
                  $sql .= $field." = PASSWORD('".$this->$field."'), ";
               }else{
                  $sql .= $field." = '".$this->$field."', ";
                  }
               }
            }
            
         $sql = rtrim( $sql, ", " );
         $sql .= " WHERE ".$this->id_field." = '".$this->{$this->id_field}."'";

         $result = $this->query( $sql );
         }

      return $result;
      }

   //Delete a record from this object
   public function delete(){

      $sql = "DELETE FROM ".$this->table." WHERE ".$this->id_field." = '".$this->{$this->id_field}."'";

      return $this->query( $sql );
      }

   //add a many to many relation out of a pivot table
   public function add( $object ){

      if( !$table = $this->get_pivot_table( $this->table, $object->table ) ){
         return FALSE;
         }
         
      if( $this->has( $object ) ){
         return FALSE;
         }

      $sql = "INSERT INTO ".$table." (".$this->id_field.", ".$object->id_field.") VALUES ('".$this->{$this->id_field}."', '".$object->{$object->id_field}."')";

      return $this->query( $sql );
      }

   //remove a many to many relation out of a pivot table
   public function remove( $object ){

      if( !$table = $this->get_pivot_table( $this->table, $object->table ) ){
         return FALSE;
         }

      if( $object->{$object->id_field} ){
         $sql = "DELETE FROM ".$table." WHERE ".$this->id_field." = '".$this->{$this->id_field}."' AND ".$object->id_field." = '".$object->{$object->id_field}."'";
      }else{
         $sql = "DELETE FROM ".$table." WHERE ".$this->id_field." = '".$this->{$this->id_field}."'";
         }

      return $this->query( $sql );
      }

   //test if many to many relation exists with $this and $object
   public function has( $object ){

      if( !$table = $this->get_pivot_table( $this->table, $object->table ) ){
         return FALSE;
         }

      $sql = "SELECT ".$this->id_field.", ".$object->id_field." FROM ".$table." WHERE ".$this->id_field." = '".$this->{$this->id_field}."' AND ".$object->id_field." = '".$object->{$object->id_field}."'";

      if( $row = $this->fetch_data( $this->query( $sql ) ) ){
         return TRUE;
      }else{
         return FALSE;
         }
      }
      
   public function start_transaction( $autocommit=0 ){
      $sql = "SET AUTOCOMMIT=".$autocommit.";";
      $sql = "START TRANSACTION;";
      
      return $this->query( $sql );
      }
      
   public function commit_transaction( $commit=TRUE ){
      if( $commit ){
         $sql = "COMMIT";
      }else{
         $sql = "ROLLBACK";
         }

      return $this->query( $sql );
      }
   }
?>
