<?php

require_once (dirname(__FILE__) . '/lib.php');
require_once (dirname(__FILE__) . '/couchsimple.php');
require_once (dirname(__FILE__) . '/crossref.php');
require_once (dirname(__FILE__) . '/fingerprint.php');
require_once (dirname(__FILE__) . '/lcs.php');

// Grab articles from a journal and look up

//----------------------------------------------------------------------------------------
function search($citation)
{
	$result = crossref_search($citation);
	
	//print_r($result);
	
	$double_check = true;
	$theshhold = 0.8;
	
	if ($double_check)
	{
		// get metadata 
		$query = explode('&', html_entity_decode($result->coins));
		$params = array();
		foreach( $query as $param )
		{
		  list($key, $value) = explode('=', $param);
		  
		  $key = preg_replace('/^\?/', '', urldecode($key));
		  $params[$key][] = trim(urldecode($value));
		}
		
		//print_r($params);
		
		$hit = '';
		if (isset($params['rft.au']))
		{
			$hit = join(",", $params['rft.au']);
		}
		  
		$hit .= ' ' . $params['rft.atitle'][0] 
			. '. ' . $params['rft.jtitle'][0]
			. ' ' . $params['rft.volume'][0]
			. ': ' .  $params['rft.spage'][0];

		$v1 = $citation;
		$v2 = $hit;
		
		//echo "-- $hit\n";
		
		//echo "v1: $v1\n";
		//echo "v2: $v2\n";
		

		$v1 = finger_print($v1);
		$v2 = finger_print($v2);					

		if (($v1 != '') && ($v2 != ''))
		{
			//echo "v1: $v1\n";
			//echo "v2: $v2\n";

			$lcs = new LongestCommonSequence($v1, $v2);
			$d = $lcs->score();

			// echo $d;

			$score = min($d / strlen($v1), $d / strlen($v2));

			//echo "score=$score\n";
			
			if ($score > $theshhold)
			{
			
			}
			else
			{
				unset ($result);
			}
		}
	}
	
	return $result;
}

$journal = 'Mycological Research';
$journal = 'New Zealand Journal of Botany';
//$journal = 'Mycological Progress';
$journal = 'New Phytologist';

$url = 'http://127.0.0.1:5984/mycobank/_design/csl/_view/container-title' . '?key=' . urlencode('"' . $journal . '"');

$json = get($url);

if ($json != '')
{
	$obj = json_decode($json);
	
	//print_r($obj);
	
	foreach ($obj->rows as $row)
	{
		//print_r($row);
		
		if ($row->value->DOI)
		{
			echo $row->value->DOI . "\n";
		}
		else
		{		
			$result = search($row->value->unstructured);
			print_r($result);
		}
	}


}

?>