<?php
	require_once(dirname(__FILE__) . '/config.php');   # Configuration
	//	$wgHooks['ParserFirstCallInit'][] = 'efOpasnetBaseImportInit';
	//	$wgExtensionMessagesFiles['OpasnetBaseImport'] = __DIR__ . '/OpasnetBaseImport.i18n.php';
	
	class OpasnetBaseImportParser
	{	
		public static function efOpasnetBaseImportInit(&$parser)
		{
			$parser->setFunctionHook( 'opasnet_base_import_link', 'OpasnetBaseImportParser::efOpasnetBaseImportLink_Render' );
			return true;
		}
		
		public static function efOpasnetBaseImportLink_Render( &$parser, $param1 )
		{
			//global $obImportPageUrl;

			// Nothing exciting here, just escape the user-provided
			// input and throw it back out again
			//return array('Show results from the [[Special:Opasnet_Base?id='.$param1.'|Opasnet base]]','isHTML');
			return array('<a href="'.obImportPageUrl.'?id='.$param1.'">' . wfMessage('text_upload_data')->text() . '</a>', 'isHTML' => true);
		}
	}

?>
