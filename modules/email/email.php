<?php

class email{
   //Properties
   var $data;

   var $name;
   var $from;
   var $from_name;
   var $to;
   var $to_name;
   var $subject;
   var $plain_text;
   var $html_text;
   var $message;
   var $headers;
  
   var $attachments_arr = array();

   //Methods
   
   public function __construct( $from_email_address=FALSE, $from_name=FALSE, $subject=FALSE ){
      $this->from = $from_email_address;
      $this->from_name = $from_name;
      $this->subject = $subject;
      
      return $this;
      }
   
   public function send_html_email(){
      $boundary1 = rand(0,9)."-"
      .rand(100000000,999999999)."-"
      .rand(100000000,999999999)."=:"
      .rand(10000,99999);
      $boundary2 = rand(0,9)."-"
      .rand(100000000,999999999)."-"
      .rand(100000000,999999999)."=:"
      .rand(10000,99999);
    
      if( sizeof( $this->attachments_arr ) > 0 ){

         $this->headers = 'From: '.$this->from_name.' <'.$this->from.">\n";
         $this->headers .= 'MIME-Version: 1.0'."\n";
         $this->headers .= 'Content-Type: multipart/mixed; boundary="'.$boundary1.'"'."\n\n";

         $this->message = 'This is a multi-part message in MIME format'."\n";
//	$this->message .= '--'.$boundary1."\n";
//	$this->message .= 'Content-Type: text/plain; charset="iso-8859-1"'."\n";
//	$this->message .= 'Content-Transfer-Encoding: quoted-printable'."\n\n";
//	$this->message .= str_replace("=", "=3D", $this->plain_text)."\n\n";
         $this->message .= '--'.$boundary1."\n";
         $this->message .= 'Content-Type: text/html; charset="iso-8859-1"'."\n";
         $this->message .= 'Content-Transfer-Encoding: quoted-printable'."\n\n";
         $this->message .= str_replace("=", "=3D", $this->html_text)."\n\n";

         foreach( $this->attachments_arr as $attachment ){
            $this->message .= '--'.$boundary1."\n";
            $this->message .= 'Content-Type: '.$attachment['type'].'; name="'.$attachment['name'].'"'."\n";
            $this->message .= 'Content-Transfer-Encoding: base64'."\n";
            $this->message .= 'Content-Disposition: attachment'."\n\n";
	        $this->message .= $attachment['attachment']."\n\n";
            }
        
        $this->message .= '--'.$boundary1.'--';
  
      }else{
    
         $this->headers .= 'MIME-Version: 1.0'."\n";
         $this->headers .= 'Content-Type: multipart/alternative;	boundary="'.$boundary1.'"'."\n\n";

         $this->message = 'This is a multi-part message in MIME format.'."\n";
         $this->message .= '--'.$boundary1."\n";
         $this->message .= 'Content-Type: text/plain; charset="iso-8859-1"'."\n";
         $this->message .= 'Content-Transfer-Encoding: quoted-printable'."\n\n";
         $this->message .= str_replace("=", "=3D", $this->plain_text)."\n\n";
         $this->message .= '--'.$boundary1."\n";
         $this->message .= 'Content-Type: text/html; charset="iso-8859-1"'."\n";
         $this->message .= 'Content-Transfer-Encoding: quoted-printable'."\n\n";
         $this->message .= str_replace("=", "=3D", $this->html_text)."\n\n";
         $this->message .= '--'.$boundary1.'--';
         }

      $ok = mail($this->to, $this->subject, $this->message, $this->headers, "-f".$this->from);

      if( $ok ){
         //empty the attachments array for next email
         $this->attachments_arr = array();

         return TRUE;
      }else{
         return FALSE;
         }
      }

   public function get_email_template( $template="default" ){

      GLOBAL $domain;
      GLOBAL $subdomain;
      GLOBAL $document_root_path;

      include( $document_root_path.'view/email_templates/'.$template.'.php' );

      $this->plain_text = strip_tags( preg_replace('/\<br(\s*)?\/?\>/i', "\n", $body_html) );
      $this->html_text = $body_html;
      }
  
   public function attach_document( $file ){

      $handle = fopen($file['tmp_name'], 'rb');
      if( $handle ){
         $file_contents = fread($handle, $file['size']);
         $attachment = chunk_split(base64_encode($file_contents));

         $this->attachments_arr[$file['name']]['type'] = $file['type'];
         $this->attachments_arr[$file['name']]['name'] = $file['name'];
         $this->attachments_arr[$file['name']]['attachment'] = $attachment;

         fclose($handle);
         }
      }

   public function attach_documents( $file_index ){

      GLOBAL $document_root_path;

      for($i = 0; $i<sizeof( $_FILES[$file_index]['name'] ); $i++){
         $attachment = '';
         $handle = FALSE;

         if( is_file( $_FILES[$file_index]['tmp_name'][$i] ) ){
            $handle = fopen($_FILES[$file_index]['tmp_name'][$i], 'rb');
            }

         if( !$handle AND is_file( $document_root_path.'uploads/'.$_FILES[$file_index]['name'][$i] ) ){
            $handle = fopen( $document_root_path.'uploads/'.$_FILES[$file_index]['name'][$i], 'rb' );
            }

         if( $handle ){
            $file_contents = fread($handle, $_FILES[$file_index]['size'][$i]);
            $attachment = chunk_split(base64_encode($file_contents));

            $this->attachments_arr[$i]['type'] = $_FILES[$file_index]['type'][$i];
            $this->attachments_arr[$i]['name'] = $_FILES[$file_index]['name'][$i];
            $this->attachments_arr[$i]['attachment'] = $attachment;

            fclose($handle);

            if( is_file( $document_root_path.'uploads/'.$_FILES[$file_index]['name'][$i] ) ){
               unlink( $document_root_path.'uploads/'.$_FILES[$file_index]['name'][$i] );
               }
            }

         }
      }
      
   public static function extract_css_file( $css_path=FALSE ){
      GLOBAL $subdomain;

      $raw_html = '';
      $fp = fopen( $css_path, "r+" );
      if( $fp ){
         while( !feof( $fp ) ){
            $raw_html .= preg_replace( '/([A-Za-z0-9.#,:-_ ]+){(.*)/i', '#email $1{', trim( fgets( $fp, 255 ) ) );
            }
         }
      @fclose( $fp );
      
      $raw_html = strtolower( $raw_html );
      $raw_html = str_replace( "../images", $subdomain."images", $raw_html );
      $raw_html = str_replace( "html{", "#html{", $raw_html );
      $raw_html = str_replace( "head{", "#head{", $raw_html );
      $raw_html = str_replace( "body{", "#body{", $raw_html );

      return $raw_html;
      }
   }
   
class newsletter extends email{
   //Properties
   var $contacts = array();

   public function add_newsletter_contact( $email_address=FALSE, $contact_name=FALSE ){
      if( validation::validate_email( $email_address ) ){
         $this->contacts[$email_address] = $contact_name;
         }
      }
      
   public function send_newsletter(){
      $errors = FALSE;
      
      foreach( $this->contacts as $email_address => $contact_name ){
         $this->to = $email_address;
         $this->to_name = $contact_name;

         if( !$this->send_html_email() ){
            $errors = TRUE;
            }
         }
         
      return !$errors;
      }
   }
?>
