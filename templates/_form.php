<?php
/*
 * Created on 10.5.2011
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
?>



<form name='obj_form' id='obj_form' enctype="multipart/form-data" action="" method="POST">
<p>
<?php echo wfMessage('text_ident')->text();?>*:<br/><input readonly='readonly' name='_ident' type='text' size='10' value='<?php if (isset($_GET['id'])) echo $_GET['id']; ?>'/>
<input name='ident' type='hidden' value='<?php if (isset($_GET['id'])) echo $_GET['id']; ?>'/>
</p>
<p>
<?php echo wfMessage('text_subset')->text();?>:<br/><input name='subset' type='text' size='64' value=''/>
</p>
<p>
<?php echo wfMessage('text_unit')->text();?>*:<br/><input name='unit' type='text' size='10' />
</p>
<p>
<?php echo wfMessage('text_last_column_is_result')->text();?>:<br/><input checked='checked' name='last_column_result' type='checkbox' value='1'/>
</p>
<p>
<?php echo wfMessage('text_get_indices_from_the_first_row')->text();?>:<br/><input onchange='mw.OpasnetBaseImport.check_special(this)' checked='checked' name='first_row_indices' type='checkbox' value='1'/>
</p>
<div id='special'>
<p>
<?php echo wfMessage('text_indices')->text();?>:<br/><input disabled='disabled' name='indices' type='text' size='100' />
</p><p>
<?php echo wfMessage('text_locations')->text();?>:<br/><input disabled='disabled'  name='locations' type='text' size='100'/>
</p>
</div>
</p>
<?php echo wfMessage('text_file')->text();?>:<br/>
<div class='csv_file_upload'>
<strong><?php echo wfMessage('text_general_options')->text();?></strong>
<p>
<?php echo wfMessage('text_file_encoding')->text();?>:<select name='file_encoding'><option value='ISO-8859-1'>ISO-8859-1</option><option value='UTF-8'>UTF-8</option></select>
</p>
<strong><?php echo wfMessage('text_csv_options')->text();?></strong>
<p>
<?php echo wfMessage('text_delimiter')->text();?>:<input name='delimiter' type='text' size='3' value=';'/>
	<?php echo wfMessage('text_enclosure')->text();?>:<input name='enclosure' type='text' size='3' value='"' />
</p>
<p>
	<?php echo wfMessage('text_decimal_separator')->text();?>:
	<input checked='checked' type='radio' name='decimal_sep' value='comma'/><?php echo wfMessage('text_comma')->text();?>
	<input type='radio' name='decimal_sep' value='point'/><?php echo wfMessage('text_point')->text();?>
	</p>
	<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $file_max_size; ?>" />
</p>
<strong><?php echo wfMessage('text_csv_or_xls_file')->text();?></strong>
<p>
	<span style='display:block; margin-top:10px;'><input name="csvfile" type="file" /></span>
</p>
</div>
<p>
<input type='submit' name='submit' value="<?php echo wfMessage('text_upload')->text();?>"/>
</p>
</form>