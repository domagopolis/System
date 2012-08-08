<?php if( $this->errors ){ ?>
<p class=error><?php echo $this->errors; ?></p>
<?php } ?>
<?php
foreach( $this->fieldsets as $legend=>$fieldset ){
   include( 'start_fieldset.php' );
   foreach( $fieldset->inputs as $name=>$input ){
      include( 'list_inputs.php' );
      }
   include( 'end_fieldset.php' );
   }
foreach( $this->inputs as $name=>$input ){
   include( 'list_inputs.php' );
   }
?>
