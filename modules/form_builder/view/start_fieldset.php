<fieldset<?php foreach( $fieldset->options as $key=>$value ){ echo ( is_numeric( $key ) )?' '.$value:' '.$key.'="'.$value.'"'; } ?>>
<legend><?php echo $legend; ?></legend>
