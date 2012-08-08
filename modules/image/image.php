<?php

/*
Name: image.php
Description: Classes to save image information.
*/

class image{

   private $height;
   private $width;
   private $type;
   private $path_arr = array();
   private $image_file_arr = array();
   private $image_title;
   private $extension;
   
   private $file_data_arr = array();
   
   /**
   * params $file_data_arr $_FILES single server file data
   */
   public function __construct( $file_data_arr=array(), $height=FALSE, $width=FALSE ){
      $this->height = $height;
      $this->width = $width;

      $this->parse_upload_files_array( $file_data_arr );
      }

   public function __get( $property ){
      if( property_exists( $this, $property ) ){
         return $this->$property;
      }else{
         return FALSE;
         }
      }
      
   public function get_image_file_names(){
      return $this->image_file_arr;
      }

   public function get_image_paths(){
      return $this->path_arr;
      }
      
   public function get_path_and_images(){
      $image_file_name_arr = $this->get_image_file_names();
      $image_path_arr = $this->get_image_paths();
      $path_and_image_arr = array();

      foreach( $image_file_name_arr as $key => $image_file_name ){
         $path_and_image_arr[$key] = $image_path_arr[$key].$image_file_name;
         }

      return $path_and_image_arr;
      }
      
   private function parse_upload_files_array( $files_data_arr=array() ){
      foreach( $files_data_arr as $file_key => $file ){
         if( is_array( $file ) ){
            foreach( $file as $file_attr => $attr_value ){
               if( is_array( $attr_value ) ){
                  foreach( $attr_value as $key => $value ){
                     $this->file_data_arr[$file_key][$key][$file_attr] = $value;
                     }
               }else{
                  $this->file_data_arr[$file_key][$file_attr] = $attr_value;
                  }
               }
         }else{
            $this->file_data_arr[$file_key] = $file;
            }
         }
      }

   public function copy( $path=FALSE, $image_title=FALSE ){
      if( array_key_exists( "tmp_name", $this->file_data_arr ) ){
         $this->copy_to_server( $path, $image_title, $this->file_data_arr );
      }else{
         foreach( $this->file_data_arr as $file_key => $file_obj ){
            if( array_key_exists( "tmp_name", $file_obj ) ){
               $this->copy_to_server( $path, $image_title, $file_obj );
            }else{
               foreach( $file_obj as $key => $file ){
                  $this->copy_to_server( $path, $image_title, $file );
                  }
               }
            }
         }
      }
   
   private function copy_to_server( $path=FALSE, $image_title=FALSE, $file=array() ){
      $this->path_arr[$file['tmp_name']] = $path;
      
      if( !empty( $file['tmp_name'] ) ){
	     switch( $file['type'] ){
            case "image/png":
               $this->extension = ".png";
               break;
            case "image/gif":
               $this->extension = ".gif";
               break;
            case "image/jpeg":
               $this->extension = ".jpeg";
               break;
            case "image/pjpeg":
               $this->extension = ".jpeg";
               break;
            default:
               $this->extension = ".jpeg";
               break;
            }

         if( $image_title ){
            $this->image_file_arr[$file['tmp_name']] = $image_title.$this->extension;
         }else{
            $randNum = "";
            for( $j=0; $j<20; $j++ ){
               $randNum .= rand(0,9);
               }
            $this->image_file_arr[$file['tmp_name']] = $randNum.$this->extension;
            }

         $uploadfile = str_replace( " ", "", $_SERVER['DOCUMENT_ROOT'].$this->path_arr[$file['tmp_name']].$this->image_file_arr[$file['tmp_name']] );

         if( copy( $file['tmp_name'], $uploadfile ) ){
            $upload_error =  "The file ".basename($file['name'])." has been uploaded";

            if( $this->extension == ".gif" ){
               $src_img = imagecreatefromgif($uploadfile);
            }else if( $this->extension == ".png" ){
               $src_img = imagecreatefrompng($uploadfile);
            }else{
               $src_img = imagecreatefromjpeg($uploadfile);
               }

            if( $this->height OR $this->width ){
               $this->height = imagesy( $src_img )*( $this->width/imagesx( $src_img ) );
               $dst_img = imagecreatetruecolor($this->width,$this->height);
               imagecopyresampled($dst_img,$src_img,0,0,0,0,$this->width,$this->height,imagesx($src_img),imagesy($src_img));

               if( $this->extension == ".gif" ){
                  imagegif($dst_img, $uploadfile);
               }else if( $this->extension == ".png" ){
                  imagepng($dst_img, $uploadfile);
               }else{
                  imagejpeg($dst_img, $uploadfile, 50);
                  }
               }
            }
         }
      }
      
   public static function delete_image( $path=FALSE, $file=FALSE ){
      if( file_exists( $_SERVER['DOCUMENT_ROOT'].$path.$file ) ){
         return unlink( $_SERVER['DOCUMENT_ROOT'].$path.$file );
      }else{
         return FALSE;
         }
      }

   public function get_image_extension(){
      return $this->extension;
      }
   }
?>
