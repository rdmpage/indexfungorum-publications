<?php

require_once (dirname(__FILE__) . '/lib.php');
require_once (dirname(__FILE__) . '/couchsimple.php');

$ids = array(503003,287548,201027,503275);

$start = 503000;
$stop  = 503999;





$ids = array(503003,287548,201027,503275);

for ($id = $start; $id <= $stop; $id++)
//foreach ($ids as $id)
{
	$url = 'http://www.mycobank.org/Services/Generic/SearchService.svc/rest/xml';
	
	$parameters = array(
		'layout' => '14682616000000161',
		'filter' => 'mycobanknr_="' . $id . '"'
	);
		
	$url .= '?' . http_build_query($parameters);
	
	$xml = get($url);
	
	//echo $xml;
	
	$dom= new DOMDocument;
	$dom->loadXML($xml);
	$xpath = new DOMXPath($dom);


	$obj = new stdclass;

	$nodeCollection = $xpath->query ("//*");
	foreach($nodeCollection as $node)
	{
		$key = $node->nodeName;
		//echo "\n$key= ";
		$value = $node->firstChild->nodeValue;
		//echo $value;
		
		if ($value != '')
		{
		
			switch ($key)
			{
				case '_id':
					$obj->url = 'http://www.mycobank.org/BioloMICS.aspx?Table=Mycobank&Rec=' . $value . '&Fields=All';
					break;
					
				case 'name':
					$obj->name = $value;
					break;
					
				case 'mycobanknr_':
					$obj->MYCOBANK = $value;
					$obj->_id = 'MB' . $value;
					break;
		
				case 'literature_pt_':
					if (preg_match('/<TargetRecord><Id>(\d+)<\/Id>/Uu', $value, $m))
					{
						$obj->publishedInCitation = $m[1];
						
						$pub_ids[] = $obj->publishedInCitation;
					}
					if (preg_match('/<Name>(.*)<\/Name>/Uu', $value, $m))
					{
						$obj->publishedIn = $m[1];
					}
					break;
				
				case 'literaturejournalbook_':
					$obj->publishedIn = $value;
					break;
				
				case 'u3733':
					if (preg_match('/<Link><Name>Wikispecies<\/Name><Url>(.*)<\/Url><\/Link>/Uu', $value, $m))
					{
						// This is simply a guess that it exists, they don't actually know, hence page may not exist
						//$obj->WIKISPECIES = str_replace(' ', '_', $m[1]);
					}
					break;
				
				default:
					break;
			}
		}			
	}

	if (isset($obj->_id))
	{
		print_r($obj);
		
		// clean up older data
		if (1)
		{
			$couch->add_update_or_delete_document(null, $id, 'delete');
		}
		
	
		$go = true;

		// Check whether this record already exists (i.e., have we done this object already?)
		$exists = $couch->exists($obj->_id);

		if ($exists)
		{
			echo $obj->_id . " exists\n";
			$go = false;

			if ($force)
			{
				echo "[forcing]\n";
				$couch->add_update_or_delete_document(null, $obj->_id, 'delete');
				$go = true;		
			}
		}

		if ($go)
		{
			// Do we want to attempt to add any identifiers here, such as DOIs?
			$resp = $couch->send("PUT", "/" . $config['couchdb_options']['database'] . "/" . urlencode($obj->_id), json_encode($obj));
			var_dump($resp);					
		}						
		
		
	}
	
}

$pub_ids = array_unique($pub_ids);

echo '$ids=array(' . join(",", $pub_ids) . ');' . "\n";

?>