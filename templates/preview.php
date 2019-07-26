<h3><?php echo wfMessage('text_object_props')->text(); ?></h3>
<p>
<?php echo wfMessage('text_ident')->text();?>:<br/><strong><?php echo $ident; ?></strong>
</p>
<p>
<?php echo wfMessage('text_subset')->text();?>:<br/><strong><?php echo $subset; ?></strong>
</p>
<p>
<?php echo wfMessage('text_unit')->text();?>:<br/><strong><?php echo $unit; ?></strong>
</p>


<?php
function type_selector($i, $sel)
{
	$ret = '';
	$options = array('entity','number','time');	
	$ret .= "<select name='index_types[".$i."]'>";
	foreach ($options as $opt)
	{
		$sel == $opt ? $s = 'selected="selected"' : $s = '';
		$ret .= "<option ".$s.">".$opt."</option>";
	}
	$ret .= "</select>";
	return $ret;
}
?>


<h3>
<?php

$num = 10;
if ($num > (count($data)-1)) $num = (count($data)-1);

echo wfMessage('text_previewing_data_rows')->text().' '.$num.'&nbsp;/&nbsp;'.(count($data)-1);
?>
</h3>

<form action="" name="confirm_form" method="POST">

<div id='preview'>
<table class='preview'>
<?php
$i = 0;

echo '<tr>';
foreach ($index_types as $it)
	echo '<th>'.type_selector($i++,$it).'</th>';
if ($last_column_result)
	echo '<th>&nbsp;</th>';
echo '</tr>';

$j = 0;
$i = 0;
foreach($data as $row)
{
	echo '<tr>';
	foreach ($row as $v)
	{
		if ($i == 0)
		{
			if ($j < count($row) - 1 || ! $last_column_result)
				echo '<th>'.$v.'</th>';
			else
				echo '<th>'.wfMessage('text_result')->text().'</th>';				
		}
		else
		{
			if (isset($index_types[$j]) && $index_types[$j] == 'time')
				echo '<td>'. date('c', strtotime($v)).'</td>';
			else
				echo '<td>'.(strlen($v) > 20 ? substr($v,0,20).'...' : $v).'</td>';
		}
		$j ++;
	}
	echo '</tr>';
	$i ++;
	$j = 0;
	if ($i > $num)
		break;
}
?>

</table>

</div>
<p>



<input type='hidden' name='confirmed_data' value='1'/>
<input type='hidden' name='decimal_sep' value='<?php echo $decimal_sep;?>'/>
<input type='hidden' name='file_encoding' value='<?php echo $file_encoding;?>'/>
<input type='hidden' name='indices' value='<?php echo $indices;?>'/>
<input type='hidden' name='locations' value='<?php echo $locations;?>'/>
<input type='hidden' name='delimiter' value='<?php echo $delimiter;?>'/>
<input type='hidden' name='enclosure' value='<?php echo $enclosure;?>'/>
<input type='hidden' name='ident' value='<?php echo $ident;?>'/>
<input type='hidden' name='subset' value='<?php echo $subset;?>'/>
<input type='hidden' name='unit' value='<?php echo $unit;?>'/>
<input type='hidden' name='first_row_indices' value='<?php echo $first_row_indices;?>'/>
<input type='hidden' name='last_column_result' value='<?php echo $last_column_result;?>'/>
<input type='hidden' name='filename' value='<?php echo $filename;?>'/>
<input type='hidden' name='ext' value='<?php echo $file_ext;?>'/>

<input type='submit' name='' value='<?php echo wfMessage('text_confirm')->text();?>'/>
<input type='button' onclick='history.go(-1)' name='' value='<?php echo wfMessage('text_cancel')->text();?>'/>
</p>

</form>


