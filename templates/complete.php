<strong>
<?php
	if ($status)
		echo wfMessage('text_upload_ok')->text();	
	else
		echo wfMessage('error_upload_not_ok')->text();
?>
</strong>

<p>
<?php

	if ($status)
	{
		echo '<a href="'.$browse_url.'">'.wfMessage('text_browse_results')->text().'</a><br/>';	
		echo '<a href="'.$extension_url.'">'.wfMessage('text_import_more')->text().'</a>';			
	}
	
?>
</p>