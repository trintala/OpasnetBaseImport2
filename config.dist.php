<?php
	global $wgArticlePath, $wgScript;
	
	// Url to special page
	if (isset($wgArticlePath) && ! empty($wgArticlePath))
	{
		define('obImportPageUrl', str_replace('$1','Special:Opasnet_Base_Import',$wgArticlePath));
		define('baseuiPageUrl', str_replace('$1','Special:Opasnet_Base',$wgArticlePath));
	}
	else
	{
		define('obImportPageUrl', $wgScript.'/Special:Opasnet_Base_Import');
		define('baseuiPageUrl', $wgScript.'/Special:Opasnet_Base');
	}
	
	define('obImportPagePrefix', "Op_en");
	define('obImportComment', "Uploaded using Opasnet Base Import");
	
	// $wikis = array('Opasnet' => 1, 'FI Opasnet' => 2, 'Heande' => 3, 'TEST_ERAC' => 4);
	
	define('obImportWikiID', 1);
	
	define('obImportDebug', true);
	define('obImportUploadsPath', dirname(__FILE__)."/uploads");
	
	// Maximum file size in import
	define('obImportFileMaxSize', "100000000");
	
	if (! defined('OB_INTERFACE_URL'))
		define('OB_INTERFACE_URL', 'http://'.''.'/opasnet_base_2/index.php'); // modify to the appropriate OpasnetBase2 server url
	
 	define('obImportDatabaseUsername', ''); // user name matching this wiki in OpasnetBase2 user database
 	define('obImportDatabasePassword', ''); // corresponding password/secret 

	define('obImportChunkSize', 3000); // Data chunk size in rows for uploading

?>
