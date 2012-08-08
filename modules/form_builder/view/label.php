<?php if( $input->label ){ ?>
<label for="<?php echo $input->name; ?>"<?php if( array_key_exists( 'title', $input->options ) ){ echo ' title="'.$input->options['title'].'"';} ?>>
<?php echo $input->label; ?><?php if( $input->required ){ ?><span class="red">*</span><?php } ?>:
</label>
<?php } ?>