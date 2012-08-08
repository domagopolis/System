<?php if( $this->record_count > $this->rows_per_page ){ ?>
<p class="pagination">
<?php
$page = 0;
foreach( $this->orm_objects->{$this->orm_objects->object_data_arr} as $object_item_pagination ){
$page++;
if( $page%$this->rows_per_page === 0 ){
   $url = preg_replace( '/(page=)([0-9]+)/i', 'page='.($page/$this->rows_per_page), $_SERVER['REQUEST_URI'] );
   if( !strstr( $url, "page=" ) ){
      $url .= "?page=".($page/$this->rows_per_page);
      }
?>
<a href="<?php echo $url; ?>"><?php echo $page/$this->rows_per_page; ?></a>
<?php } ?>
<?php } ?>
<?php
if( $page%$this->rows_per_page !== 0 ){
   $url = preg_replace( '/(page=)([0-9]+)/i', 'page='.ceil($page/$this->rows_per_page), $_SERVER['REQUEST_URI'] );
   if( !strstr( $url, "page=" ) ){
      $url .= "?page=".ceil($page/$this->rows_per_page);
      }
?>
<a href="<?php echo $url; ?>"><?php echo ceil( $page/$this->rows_per_page ); ?></a>
<?php } ?>
</p>
<?php } ?>
