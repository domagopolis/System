<div id="social_nav">
<ul>
<li><p><?php echo $this->social_links_heading; ?>:</p></li>
<?php foreach( $links_arr as $social_link=>$link ){ ?>
<li><a href="<?php echo $link; ?>" class="<?php echo $social_link; ?>" target="_blank"><?php echo ucwords( $social_link ); ?></a></li>
<?php } ?>
</ul>
</div>
