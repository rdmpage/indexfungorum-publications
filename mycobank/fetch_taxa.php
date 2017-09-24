<?php

require_once (dirname(dirname(__FILE__)) . '/lib.php');

$ids = array(503003,287548,201027,503275);

foreach ($ids as $id)
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
					$obj->id = $value;
					break;
					
				case 'name':
					$obj->name = $value;
					break;
					
				case 'mycobanknr_':
					$obj->MYCOBANK = $value;
					break;
		
				case 'literature_pt_':
					if (preg_match('/<TargetRecord><Id>(\d+)<\/Id>/Uu', $value, $m))
					{
						$obj->publishedInCitation = $m[1];
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



	print_r($obj);
	
}

?>