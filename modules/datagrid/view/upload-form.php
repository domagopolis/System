<form enctype="multipart/form-data" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post">
<input type="hidden" name="MAX_FILE_SIZE" value="500000">
<ul>
<li><label for="importfile">Import File:</label>
<input id="importfile" class="file" type="file" name="importfile">
</li>
<li><button type="submit" name="preview" value="Preview">Preview</button><li>
<li><button type="submit" name="enter" value="Enter">Enter</button></li>
</ul>
</form>
