<?php
	require_once(dirname(__FILE__) . '/config.php');   # Configuration
	// Excel reader (from sourceforge)
	require_once dirname(__FILE__).'/excel/excel_reader2.php';
	require_once dirname(__FILE__).'/lib/OpasnetBaseUpload.php';

	//global $obImportDebug, $obImportT2BPath;

	// Include Juha's magick functions
	//include $obImportT2BPath;

	/*if (isset($obImportDebug) && $obImportDebug)
	{
		//error_reporting(E_ALL);
		//ini_set("display_errors", "on");
	}*/
	
	#require_once 'OpasnetConnection.class.php';

	class OpasnetBaseImport extends SpecialPage
	{
		private $connection;
		private $start_time;
		
		// Set this to true to echo clean output, no wgOut
		private $clean_output;
		
		private $index_types = array();
				
		function __construct()
		{
			parent::__construct( 'OpasnetBaseImport' );
			#wfLoadExtensionMessages('OpasnetBaseImport');
			$this->clean_output = false;
		}

		function execute( $par ) {
			global $wgRequest, $wgOut, $wgServer, $wgScriptPath;
			//,$obImportUp,loadsPath,$obImportPageUrl,$baseuiPageUrl,
			global $wgUser;
			//,$obImportFileMaxSize;

			$wgOut->addModules( 'ext.OpasnetBaseImport' );

			$this->setHeaders();
			$wgOut->setPagetitle("OpasnetBaseImport");
			
			$this->start_time = microtime(true);
		#	$this->connection = new OpasnetConnection();
	 
			try {		

				// USER MUST BE LOGGED IN!!!
				if ($wgUser->getId() == 0)
				{
					$this->render_error(wfMessage('error_user_not_logged_in_wiki')->text());
					return;
				}

				$params = array(			
					'path' => $wgServer.$wgScriptPath.'/extensions/OpasnetBaseImport/',
					'file_max_size' => obImportFileMaxSize
				);
				
				if (isset($_POST['confirmed_data']))
				{
					$params['status'] = $this->save_object();
					$ident = $_POST['ident'];
					$id = OpasnetBaseUpload::ident2id($ident);
					#echo $id.":".$ident;
					$this->touch_page($id);
					#echo "PAge touched: ".$id;
					$params['extension_url'] = obImportPageUrl;
					$params['browse_url'] = baseuiPageUrl.'?id='.$_POST['ident'];
					$this->render('complete',$params);		
				}
				elseif (isset($_POST['ident']))
				{
					$params['data'] = $this->handle_upload();
					$params['ident'] = htmlspecialchars($_POST['ident']);
					$params['subset'] = htmlspecialchars($_POST['subset']);
					$params['unit'] = htmlspecialchars($_POST['unit']);
					$params['indices'] = (isset($_POST['indices']) ? htmlspecialchars($_POST['indices']) : '');
					$params['locations'] = (isset($_POST['locations']) ? htmlspecialchars($_POST['locations']) : '');
					$params['index_types'] = $this->index_types;
					$params['first_row_indices'] = (isset($_POST['first_row_indices']) && ! empty($_POST['first_row_indices']));
					$params['last_column_result'] = (isset($_POST['last_column_result']) && ! empty($_POST['last_column_result']));
					$params['filename'] = obImportUploadsPath."/".basename($_FILES['csvfile']['tmp_name']);
					$params['delimiter'] = htmlspecialchars(isset($_POST['delimiter']) ? $delimiter = $_POST['delimiter'] : $delimiter = ',');
					$params['enclosure'] = htmlspecialchars(isset($_POST['enclosure']) ? $enclosure = $_POST['enclosure'] : $enclosure = '"');
					$params['file_encoding'] = $_POST['file_encoding'];
					#$params['decimal_sep'] = $_POST['decimal_sep'];
					$path_info = pathinfo($_FILES['csvfile']['name']);
					$params['file_ext'] = $path_info['extension'];
					$this->render('preview',$params);
				}
				else				
					$this->render('index',$params);
				
				# Disconnect the database
				#$this->connection->disconnect();
			}
			catch (Exception $e)
			{
				$this->render_error($e->getMessage());
			}
			
		}

				
		function render($target, $vars=null, $skip_debug = false)
		{
			//global $obImportDebug;
			
		    if (is_array($vars) && !empty($vars)) {
		        extract($vars);
		    }
		    
		    ob_start();
		    //include 'templates/helpers.php';
		    include 'templates/'.$target.'.php';
	    	$this->output(ob_get_clean());
		    
		    // DEBUG??
/*
		    if (!$skip_debug && $qs = $this->connection->get_queries())
		    	foreach ($qs as $q)
		    	{
					$this->output("<div class='query_debug'>".$q[0]);
					$this->output("<div class='gentime'>".$q[1].'s</div></div>');
		    	}
	*/	    
		    if (!$skip_debug and obImportDebug == true)
				$this->output("<div class='gentime'>".round(microtime(true) - $this->start_time, 5) . 's</div>');

			if ($this->clean_output)
				die;
		}
		
		function render_error($msg)
		{
			global $wgOut;
			if ($this->clean_output)
				echo '<strong>'.$msg.'</strong>';
			else
				$wgOut->addHTML('<strong>'.$msg.'</strong><p><a href="javascript: history.go(-1);">'.wfMessage('text_go_back')->text().'</a></p>');
			if ($this->clean_output)
				die;
		}
		
		function output($content)
		{
			global $wgOut;
			if ($this->clean_output)
				echo $content;
			else
				$wgOut->addHTML($content);
		}
		
	
		function handle_upload()
		{
			//global $obImportUploadsPath;
			//global $obImportWikiID, $obImportDebug;
			//$test = true;
			//echo "test_prints:".obImportUploadsPath.obImportWikiID.obImportDebug.$test."\n";
			isset($_POST['delimiter']) ? $delimiter = $_POST['delimiter'] : $delimiter = ',';
			isset($_POST['enclosure']) ? $enclosure = $_POST['enclosure'] : $enclosure = '"';
			
			// Empty?
			if ($enclosure == '')
				$enclosure = "\0";
			
			if (! isset($_POST['ident']) or empty($_POST['ident']) or ! isset($_POST['unit']) or empty($_POST['unit']))
				throw new Exception(wfMessage('error_missing_inputs')->text());
			
			if (isset($_FILES['csvfile']) && is_uploaded_file($_FILES['csvfile']['tmp_name']))
			{
				$f = obImportUploadsPath."/".basename($_FILES['csvfile']['tmp_name']);
				
				$path_info = pathinfo($_FILES['csvfile']['name']);
							
				// For csv do encoding change
				if ($path_info['extension'] == 'csv' && $_POST['file_encoding'] != 'UTF-8')
				{
					$fc = iconv($_POST['file_encoding'], "UTF-8//TRANSLIT//IGNORE", file_get_contents($_FILES['csvfile']['tmp_name']));
					file_put_contents($f, $fc);
				}
				else
				{
					move_uploaded_file($_FILES['csvfile']['tmp_name'], $f);
				}	
				
				//move_uploaded_file($_FILES['csvfile']['tmp_name'], $f);
    			
    			
    			if ($path_info['extension'] == 'csv')
					$data = $this->parse_csv($f, $delimiter, $enclosure);
				elseif ($path_info['extension'] == 'xls')
					$data = $this->parse_xls($f, $_POST['file_encoding']);
				else
					throw new Exception(wfMessage('error_unknown_filetype')->text());
														
				//$this->change_encoding($data, $_POST['file_encoding'], 'UTF-8');
														
				if (! (isset($_POST['first_row_indices']) && $_POST['first_row_indices']))
				{
					$indices = explode(',',$_POST['indices']);
					$locations = explode(',',$_POST['locations']);

					# Try TAB separation as well
					if (count($indices) < 2)
						$indices = explode("\t",$_POST['indices']);
					if (count($locations) < 2)
						$locations = explode("\t",$_POST['locations']);
					
					array_shift($data);
					$data = OpasnetBaseUpload::create_multiresult_table($indices, $locations, $data);					
					$indices[] = 'result';
					array_unshift($data, $indices);				
				}
	
				# Try to guess index types
				$this->index_types = OpasnetBaseUpload::guess_index_types(array_slice($data,1,10), (isset($_POST['last_column_result']) && $_POST['last_column_result']) );

				if ($path_info['extension'] == 'csv' && $_POST['decimal_sep'] == 'comma')
					$this->convert_commas($data);
					
				#$data = $this->remove_empty_results($data);
				
				// Remove old files from uploads
				$this->remove_old_uploads();
				
				return $data;
			}
			else
				throw new Exception(wfMessage('error_invalid_file')->text());
		}
		
		function save_object()
		{	
			//global $obImportWikiID, $obImportComment;
			
			$delimiter = $_POST['delimiter'];
			$enclosure = $_POST['enclosure'];
			$unit = $_POST['unit'];
			$ident = $_POST['ident'];
			$subset = $_POST['subset'];
			$ext = $_POST['ext'];
			$fname = $_POST['filename'];
			$rescol =  (isset($_POST['last_column_result']) && $_POST['last_column_result']);
			$index_types = $_POST['index_types'];
			
			if ($ext == 'csv')
				$data = $this->parse_csv( $fname, $delimiter, $enclosure);
			elseif ($ext == 'xls')
				$data = $this->parse_xls( $fname );
			else
				throw new Exception(wfMessage('error_unknown_filetype')->text());
									
			if (isset($_POST['first_row_indices']) && $_POST['first_row_indices'])
			{
				$indices = array_shift($data);
				if ($rescol)
					array_pop($indices);
			}
			else
			{
				$indices = explode(',',$_POST['indices']);
				$locations = explode(',',$_POST['locations']);
				
				# Try TAB separation as well
				if (count($indices) < 2)
					$indices = explode("\t",$_POST['indices']);
				if (count($locations) < 2)
					$locations = explode("\t",$_POST['locations']);
				
				array_shift($data);
				$data = OpasnetBaseUpload::create_multiresult_table($indices, $locations, $data);
			}
			//print_r($indices)
			
			#if ($ext == 'csv' && $_POST['decimal_sep'] == 'comma')
			#	$this->convert_commas($data);
			
			#if ($rescol)
			#	$data = $this->remove_empty_results($data);
			
			$inds = array();
			
			# Determine index types using ten first rows
			#$this->index_types = OpasnetBaseUpload::guess_index_types(array_slice($data,1,10));
			
			// Complete indices
			$i = 1;
			foreach ($indices as $ind)
			{
				$inds[$i] = array('type' => $index_types[$i-1], 'name' => $ind, 'page' => 0, 'wiki_id' => obImportWikiID, 'order_index' => $i, 'hidden' => 0, 'unit' => '');
				$i ++;
			}
			
			return OpasnetBaseUpload::upload_to_base($ident, $unit, $inds, $data, obImportComment, $subset, $rescol);
		}
		
		// Remove rows with empty result, returns new table
		private function remove_empty_results($data)
		{
			$tmp = array();
			
			$c = count($data[0])-1;
			foreach ($data as $row)
				if (isset($row[$c]) && (! empty($row[$c]) || is_numeric($row[$c])))
					$tmp[]= $row;
		
			return $tmp;
		}		
		
		// Convert number column commas (and points) of data
		private function convert_commas(&$data)
		{
			for ($c = 0; $c < count($data[0]); $c ++)
				if (isset($this->index_types[$c]) && $this->index_types[$c] == 'number')
					for ($j = 0; $j < count($data); $j ++)
						$data[$j][$c] = str_replace(array(".",","),array("","."),$data[$j][$c]);
		}
		
		private function parse_xls($file, $encoding)
		{
			$data = new Spreadsheet_Excel_Reader($file, false, $encoding);
			//$data->read($file);

			$ret = array();
			for ($i = 1; $i <= $data->rowcount(); $i++)
				for ($j = 1; $j <= $data->colcount(); $j++)
					$ret[$i-1][$j-1] = $data->val($i, $j);
					//$ret[$i-1][$j-1] = $data->sheets[0]['cells'][$i][$j];
			return $ret;
		}


		private function parse_csv($file, $delimiter, $enclosure)
		{
			$row = 1;
			$oldnum = -1;
			$ret = array();
			if (($handle = fopen($file, "r")) !== FALSE) {
				
				while (($data =  fgetcsv ($handle, $length = 0, $delimiter,  $enclosure)) !== FALSE) {
			        $num = count($data);
			        if ($oldnum > -1 && $num != $oldnum)
			        	throw new Exception(wfMessage('error_invalid_column_count_at_row')->text().' '.$row);
			        $oldnum = $num;
			        $row++;
			        $ret[] = $data;
				}
			    return $ret;			
			}
			else throw new Exception(wfMessage('error_invalid_file')->text());
		}


		// Removes all uploads more than 7 days old
		private function remove_old_uploads()
		{
			//global $obImportUploadsPath;
			
			// remove files more than x days old
			$d_limit = 7;
			$s_limit = $d_limit * 24 * 60 * 60; // as seconds
						
			if ($handle = opendir(obImportUploadsPath)) {
			    /* This is the correct way to loop over the directory. */
			    while (false !== ($fname = readdir($handle)))
			    if (! is_dir(obImportUploadsPath . "/" . $fname))
			    {
			    	$mtime = filemtime(obImportUploadsPath . "/" . $fname);
			    	$age = time() - $mtime;
			    	if ($age >= $s_limit)
			    		unlink(obImportUploadsPath . "/" . $fname);
			    }			
			    closedir($handle);
			}

		}
		
		private function touch_page($id)
		{
			global $wgTitle;
			global $wgArticle;

			$ts =  time();

			//$shit = $wgTitle->nameOf($id);
			$a = Article::newFromId($id);
			
			if ($a)	
				$content = $a->getContent();
			else
				throw new Exception('Article not found with id '.$id);
			
			$str = "<!-- __OBI_TS:";
			
			$pattern = "/".$str."[0-9]* -->/";
			$replacement = $str.$ts." -->";
			$ret = preg_replace($pattern, $replacement, $content);
			
			if ($ret == $content)
				$content .= $str.$ts." -->";
			else
				$content = $ret;
			
			$a->doEdit(
                    $content,
                    "Data uploaded using Opasnet Base Import",
                    EDIT_UPDATE
                );

			//$str = file_get_contents('http://www.opasnet.org/'.$wgScriptPath."/api.php?action=query&prop=info|revisions&intoken=edit&titles=Main%20Page");
			//$xml = new SimpleXMLElement($str);
			
			//print_r($xml);
		}

	
	}



