<?php

require_once (dirname(dirname(__FILE__)) . '/lib.php');

$ids = array(34531,15474,7025);

foreach ($ids as $id)
{
	$url = 'http://www.mycobank.org/Services/Generic/SearchService.svc/rest/xml';
	
	$parameters = array(
		'layout' => '14682616000000163',
		'filter' => '_id="' . $id . '"'
	);
		
	$url .= '?' . http_build_query($parameters);
	
	$xml = get($url);
	
	//echo $xml;
	
	$dom= new DOMDocument;
	$dom->loadXML($xml);
	$xpath = new DOMXPath($dom);

	
	$obj = new stdclass;
	$obj->author = array();
	
	$tags = array('title_', 'journal_pt_', 'volume_', 'issue_', 'year_', 'pagefrom_', 'pageto_', 'doi_',
	 'pubmedid_', 'hyperlinkto_', 'doctype_', 'authors_', 'editors_', 'publisher_', 'isbn_', 'id_',
	 'e3811');
	
	$tag_map = array(
		'title_' 		=> 'title',
		'volume_' 		=> 'volume',
		'issue_' 		=> 'issue',
		'doi_' 			=> 'DOI',
		'pubmedid_' 	=> 'PMID',
		'hyperlinkto_' 	=> 'URL',
		'isbn_' 		=> 'ISBN',
		'e3811'			=> 'unstructured',
		'id_'			=> 'MYCOBANK'
	);
		
		
	foreach ($tags as $tag)
	{
		$nodeCollection = $xpath->query ("//" . $tag);
		foreach($nodeCollection as $node)
		{
			$value = $node->firstChild->nodeValue;
			if ($value != '') 
			{
			
				//echo $tag . '=' . $value . "\n";
			
				if (isset($tag_map[$tag]))
				{
					$obj->{$tag_map[$tag]} = $node->firstChild->nodeValue;
				}
				else
				{
					switch ($tag)
					{
						case 'authors_':
							$parts = explode(";", $value);
							foreach ($parts as $part)
							{
								$author = new stdclass;
								$author->literal = $part;
								$obj->author[] = $author;
							}
							break;
					
						case 'doctype_':
							switch ($value)
							{
								case 'Article':
									$obj->type = 'article-journal';
									break;
									
								default:
									$obj->type = $value;
									break;
							}
							break;
					
						case 'journal_pt_':
							if (preg_match('/<Name>(.*)<\/Name>/u', $value, $m))
							{
								$obj->{'container-title'} = $m[1];
							}
							break;
							
						case 'pagefrom_':
							$obj->page = $value;
							$obj->{'page-first'} = $value;
							break;

						case 'pageto_':
							$obj->page .= '-' . $value;
							break;
							
						case 'year_':
							$obj->issued = new stdclass;
							$obj->issued->{'date-parts'} = array();
							$obj->issued->{'date-parts'}[0] = array();
							$obj->issued->{'date-parts'}[0][] = (Integer)$value;
							break;
							
					
						default:
							break;
					}
				}
			}
		}
	}
	
	// clean up
	if (count($obj->author) == 0)
	{
		unset($obj->author);
	}
	
	print_r($obj);	
	

	
}

?>