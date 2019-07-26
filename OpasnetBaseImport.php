<?php
# OBSOLETE
# Alert the user that this is not a valid entry point to MediaWiki if they try to access the special pages file directly.

if (!defined('MEDIAWIKI')) {
        echo <<<EOT
To install my extension, put the following line in LocalSettings.php:
require_once( "\$IP/extensions/OpasnetBaseImport/OpasnetBaseImport.php" );
EOT;
        exit( 1 );
}
 
$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'OpasnetBaseImport',
	'author' => 'Einari Happonen, Juha Villman',
	'email' => 'einari.happonen@thl.fi',
	'url' => 'http://www.mediawiki.org/wiki/Extension:OpasnetBaseImport',
	'descriptionmsg' => 'opasnet_import_desc',
	'version' => '2.0.1'
);
 
$dir = dirname(__FILE__) . '/';
 
$wgAutoloadClasses['OpasnetBaseImport'] = $dir . 'OpasnetBaseImport_body.php'; # Tell MediaWiki to load the extension body.
//$wgAutoloadClasses['OpasnetBaseImportTags'] = $dir . 'OpasnetBaseImport.tags.php';   #implements tags
$wgExtensionMessagesFiles['OpasnetBaseImport'] = $dir . 'OpasnetBaseImport.i18n.php';
$wgExtensionMessagesFiles['OpasnetBaseImportAlias'] = $dir . 'OpasnetBaseImport.alias.php';
$wgSpecialPages['OpasnetBaseImport'] = 'OpasnetBaseImport'; # Let MediaWiki know about your new special page.
//$wgSpecialPageGroups['OpasnetBaseImport'] = 'opasnet';

$wgResourceModules['ext.OpasnetBaseImport'] = array(
        // JavaScript and CSS styles. To combine multiple file, just list them as an array.
        'scripts' => 'modules/scripts.js',
        'styles' => 'modules/screen.css',
 
        // When your module is loaded, these messages will be available through mw.msg()
      //  'messages' => array( 'myextension-hello-world', 'myextension-goodbye-world' ),
 
        // If your scripts need code from other modules, list their identifiers as dependencies
        // and ResourceLoader will make sure they're loaded before you.
        // You don't need to manually list 'mediawiki' or 'jquery', which are always loaded.
        //'dependencies' => array( 'jquery' ),
 
        // ResourceLoader needs to know where your files are; specify your
        // subdir relative to "/extensions" (or $wgExtensionAssetsPath)
        'localBasePath' => dirname( __FILE__ ),
        'remoteExtPath' => 'OpasnetBaseImport'
);

//require_once($dir . 'config.php');   # Configuration
//require_once($dir . 'OpasnetBaseImport.parser.php');   # Parsers

?>