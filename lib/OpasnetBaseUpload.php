<?php
/*
 * Created on 15.8.2012
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
require_once(dirname(__FILE__).'/../config.php');   # Configuration
//require_once(dirname(__FILE__).'/config.php');   # Configuration
 
class OpasnetBaseUpload
{
	static function upload_to_base($ident, $unit, $indices, $raw_data, $comment, $subset_name = '', $rescol = true)
	{
		//global $obImportWikiID,$obImportPagePrefix, $obImportComment;
		global $wgUser;
		//global $obImportDatabaseUsername,$obImportDatabasePassword, $obImportChunkSize;
		
		$ident = strtolower($ident);
		
		$articleIdent = str_replace(strtolower(obImportPagePrefix),"",$ident);
		//if prefix couldn't be removed
		if(! is_numeric($articleIdent) || (int)$articleIdent < 1) throw new Exception($articleIdent.' ident number is not valid! Ident is '.$ident.' and prefix is '.obImportPagePrefix);
		//checking if articlename exists...
		$dbw = wfGetDB( DB_SLAVE );
		
		$res = $dbw->select('page',array('page_title','page_id'),"page_id={$articleIdent}",__METHOD__);

		foreach( $res as $row ) 
	       	$articleName = $row->page_title;
		//exception, if articleIdent is not found from the Wiki database
		if (count($res)<1) throw new Exception('Page (name) not found!'); 
		//setting up some variables
		$user_name=$wgUser->getName();
		
		if(! isset($articleName) || empty($articleName)) throw new Exception('Page name is empty!');
		
		$header = array(
			'object'=>array(
				'name' => $articleName,
				'ident' => $ident,
				'type' => "variable",
				'page' => $articleIdent,
				'wiki_id' => obImportWikiID
				),
			'act'=>array(
				'unit' => $unit,
				'who' => $user_name,
				'samples' => 1,
				'comments' => $comment
				),
			'indices'=> $indices
		);
		
				
		if ($subset_name != '')
		{
			$subset_ident = self::sanitize_subset_name($subset_name);		
			$header['object']['ident'] = $ident.'.'.$subset_ident;
			$header['object']['subset_name'] = trim($subset_name);
		}
		
		$header['username'] = obImportDatabaseUsername;
		$header['password'] = md5($header['object']['ident'] . obImportDatabasePassword);		
		
		$ret = json_decode(self::do_post_request(OB_INTERFACE_URL, http_build_query(array('json' => json_encode($header)))));	
			
		if ($ret && isset($ret->error))
			throw new Exception('Error fetching upload key: '.$ret->error);			
		elseif (! $ret)
			throw new Exception('Unable to fetch upload key! No server response!');

		$data['key'] = $ret->key;
		$data['indices'] = $indices;
					
		$passwd = md5($data['key'] . obImportDatabasePassword);
		$data['username'] = obImportDatabaseUsername;
		$data['password'] = $passwd;
		
		reset($raw_data);
		$row = current($raw_data);
		$row_count = 0;
		
		do
		{
			$r = 1;
			$dat = array();
			do
			{
				$i = 1;
				$tmp = array();
				foreach ($row as $k => $v)
					if ($k < count($row) - 1 || ! $rescol)
						$tmp[$i++] = $v;
					else
						$tmp['res'] = $v;
				$dat[$r++] = $tmp;
				unset($tmp);
			} while (($row = next($raw_data)) !== FALSE && $r <= obImportChunkSize);
			
			$data['data'] = $dat;

			#echo count($data['data']).'<br/>';

			unset($dat); // Free mem!
			
			$json = json_encode($data);
			unset($data['data']);			
			
			$ret = json_decode(self::do_post_request(OB_INTERFACE_URL, http_build_query(array('json' => $json))));	
			
			unset($json);
			
			if ($ret && isset($ret->error))
				throw new Exception($ret->error);
			elseif (! $ret)
				throw new Exception('Data upload failed!!! Server did not respond!');
			$row_count += (int)$ret->rows;						
			#echo $row_count.'<br/>';
						
			#echo (memory_get_usage()/1024/1024).'<br/>';

		} while ($row !== FALSE);

		if ($row_count != count($raw_data))
			throw new Exception('Invalid upload row count: ' . $row_count . ' vs ' . count($raw_data) );

		return true;
	}

	
 	//converts multiple results per row array into standard one result per row array
	static function create_multiresult_table($indices,$locations,$inputs)
	{
		$num_of_rows = count($inputs);
		$indices =$indices;
		$indices_num = count($indices);
		$extra_locs = $locations;
		$extra_locs_num = count($extra_locs);
		
		if ($extra_locs_num == 0) return $inputs;
		
		//we need to convert table into single result per row form
		$new_row=0; 		
		$k=0;
		$next_col=0;
		
		#echo (memory_get_usage()/1024/1024).'<br/>';		
		
		//READ all results into array cells
		for($i=0;$i<$num_of_rows;$i++)
		{
			$next_col=$indices_num-1; //column to start reading results from
			for($j=0;$j<$extra_locs_num;$j++)
			{
				$tmp[$new_row]=$inputs[$i][$next_col];
				$new_row++;
				$next_col++;
			}
		}

		//foreach($tmp as $value) echo "<br/>".$value;
		
		//adds extra locations into tmp2 result array		
		$something=0;
		
		$row_cnt = count($tmp);
		
		for($i=0;$i<$row_cnt;$i++)
		{
			//echo $i."<br/>"; 
			$tmp2[$i][0]=$extra_locs[$something]; //add location into result array
			$tmp2[$i][1]=$tmp[$i]; //add result into result array
			// Free memory!!!
			unset($tmp[$i]);
			$something++;
			if($something==$extra_locs_num) $something=0; //back to start
		}
		
		//foreach($res_rows as $value) echo "<br/>".$value;
		//foreach($tmp2 as $value) echo "<br/>".$value[0].$value[1];
		
		//creating array with given indices from input
		/*
		for($i=0;$i<$num_of_rows;$i++)
		{
			for($j=0;$j<=$indices_num-2;$j++)
			{
				$real_indices[$i][$j]= $inputs[$i][$j];
			}
		}
		*/
		//foreach($real_indices as $value) echo "<br/>".$value[0].$value[1];
				
		//merging results and indices
		$row_cnt = count($tmp2);
		$next = $row_cnt/$num_of_rows;
		$cnt=0;
		$cnt2=0;
		//echo $next;
		for($i=0;$i<$row_cnt;$i++)
		{
			$result[$i]=array_merge(array_slice($inputs[$cnt], 0, $indices_num-1),(array)$tmp2[$i]);
			
			// Free the mem!
			unset($tmp2[$i]);
			
			//print_r($result[$i]);
			
			$cnt2++;
			if($cnt2==$next)
			{
				$cnt2=0;
				 $cnt++; //move to next index row
			}

		}
		//foreach($result as $value) echo "<br/>".$value[0].$value[1].$value[2];
	
		return $result;
	}
	
	static function do_post_request($url, $data, $optional_headers = null)
	{
		  $params = array('http' => array('method' => 'POST', 'content' => $data));
		  
		  if ($optional_headers !== null)
		    $params['http']['header'] = $optional_headers;
		  
		  $ctx = stream_context_create($params);
		  $fp = @fopen($url, 'rb', false, $ctx);
		  
		  isset($php_errormsg) ? $msg = $php_errormsg : $msg = "";
		  
		  if (!$fp)
		    throw new Exception("Problem with $url, $msg");
		  
		  $response = @stream_get_contents($fp);
		  
		  if ($response === false)
		    throw new Exception("Problem reading data from $url, $rmsg");
		
		return $response;
	}
	
	static function ident2id($ident)
	{
		//global $obImportPagePrefix;
		//we have to look for wikiprefix and remove it if it's found, this is used for csv-uploader purposes
		$articleIdent = str_replace(strtolower(obImportPagePrefix),"",strtolower($ident));
		return $articleIdent;
	}


	static function sanitize_subset_name($name)
	{
		setlocale(LC_ALL, 'en_US.UTF8'); // This is needed for iconv to work
		$n = mb_strtolower(trim($name)); // Trim and lowercase
		$n = preg_replace('/[[:punct:] ]/','_',$n); // Remove punctuation chars
		$n = iconv("UTF-8", "ASCII//TRANSLIT", $n);  // Convert to ASCII
		$n = preg_replace('/_+/','_',$n); // Truncate multiple underscores to one
		$n = preg_replace('/^_|_$/','',$n); // Remove trailing and leading underscores
		return $n;
	}
	
	static function guess_index_types($data, $result_col = TRUE)
	{
		$ret = array();
		
		$size = count($data[0]);
		if ($result_col) $size --;
		
		for($i = 0; $i < $size; $i ++)
		{			
			$prev_type = '';
			$type = 'entity';
			foreach ($data as $row)
			{
				$c = trim($row[$i]);
						
		#		if (self::is_year(str_replace(',','',$c)))
		#			$type = 'year';
		#		elseif (is_numeric(str_replace(',','',$c)))
		#			$type = 'number';
				if (is_numeric($c) && ! self::is_float2($c))
					$type = 'integer';
				elseif (self::is_float2($c))
					$type = 'float';
				elseif (strtotime($c) !== false && strlen($c) > 8)
				{
					$type = 'time';
				}
				#else
				#{
				#	$ret[$i] = 'entity'; 
				#	break;
				#}
				
				
				#echo $c.":".$type;
/*
				if (($prev_type == 'number' && $type == 'year') || ($prev_type == 'year' && $type == 'number'))
				{
					$type = 'number';
				}*/
				if (($prev_type == 'float' && $type == 'integer') || ($prev_type == 'integer' && $type == 'float'))
				{
					$type = 'float';
				}
				elseif ($prev_type != '' && $prev_type != $type)
				{
					$ret[$i] = 'entity';
					break;
				}

				switch($type){
					#case 'year': $ret[$i] = 'entity'; break;
					case 'integer': $ret[$i] = 'entity'; break;
					case 'float': $ret[$i] = 'number'; break;
					case 'time': $ret[$i] = 'time'; break;
					default: $ret[$i] = 'entity';
				}
				$prev_type = $type;
			}
		}

		return $ret;
	}
	
	static function is_year($n)
	{
		return (( !is_int($n) ? (ctype_digit($n)) : true ) && (int)$n > 1000 && (int)$n < 3000);
	}

	static function is_float2($n)
	{
		if ((string)(int)$n === (string)$n)
			return false;
		$n1 = str_replace(',','.',$n);
		$n2 = str_replace(',','.',str_replace('.','',$n));
		if ((string)(float)$n === (string)$n)
			return true;
		elseif ((string)(float)$n1 === (string)$n1)
			return true;
		elseif ((string)(float)$n2 === (string)$n2)
			return true;
		return false;
	}
	

}
?>