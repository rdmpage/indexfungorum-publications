<?php

require_once('lib.php');

//--------------------------------------------------------------------------------------------------
function augment_jstor(&$reference, $id)
{
	//echo "Augment\n";
	
	$url = 'http://www.jstor.org/stable/' . $id;
	
	echo $url . "\n";
	
	$html = get($url);
	
	
	$html = str_replace("\n", "", $html);
	$html = str_replace("\r", "", $html);
	
	//echo "html=$html\n";
	
	//exit();
	
	// extract stuff
	
	$reference->url = 'http://www.jstor.org/stable/' . $id;
	
	/*
	if (preg_match('/<cite>(?<journal>.*)<\/cite>/Uu', $html, $m))
	{
		$reference->secondary_title = $m['journal'];
	}
	*/
	
	if (0)
	{
		if (preg_match('/<div id="articlePageNav" class="container">\s+Page\s+\[?\d+\]? of (?<spage>\d+)-(?<epage>\d+)/', $html, $m))
		{
			//print_r($m);
			$reference->journal->pages = $m['spage'] . '--' . $m['epage'];

		}
	}
	
	
	if (0)
	{
		if (preg_match('/<div\s+class="author">(?<author>.*)<\/div>/Uu', $html, $m))
		{
			$authorstring = $m['author'];
			$authors = authors_from_string($authorstring);
			foreach ($authors as $a)
			{
				$a = str_replace('.', '', $a);
				$author = new stdclass;
				
				// clean off III etc.
				
				echo "|$a|\n";
				$a = preg_replace('/,\s+[I]+$/i', '', $a);
				
				
				if (preg_match('/^(?<firstname>.*)\s+(?<lastname>\w+(-\w+)?)$/Uu', $a, $m))
				{
					$author->firstname = $m['firstname'];
					$author->lastname = $m['lastname'];
					$author->name = $author->firstname . ' ' . $author->lastname;
				}
				else
				{
					$author->name = $a;
				}
				$reference->author[] = $author;
			}
	
			
		}
	}
	
	if (1)
	{
		if (preg_match('/<\/cite>\s*<br\/>(?<citation>.*)<br\/>/Uu', $html, $m))
		{
			//print_r($m);
			
			$citation = $m['citation'];
			if (preg_match('/Vol.\s+(?<volume>\d+),(\s+No.\s+(?<issue>\d+))?\s+\((\w+.,\s+)?(?<year>[0-9]{4})\),\s+pp.\s+(?<spage>\d+)-(?<epage>\d+)/', $citation, $mm))
			{
				//print_r($mm);
				//$reference->volume = $mm['volume'];
				//$reference->issue = $mm['issue'];
				//$reference->spage = $mm['spage'];
				//$reference->epage = $mm['epage'];
				//$reference->year = $mm['year'];
				
				$reference->journal->pages = $mm['spage'] . '--' . $mm['epage'];
				
			}
		}
		else
		{
			if (preg_match('/Vol.\s+(?<volume>\d+),(\s+No.\s+(?<issue>\d+))?\s+\((\w+.,\s+)?(?<year>[0-9]{4})\),\s+pp.\s+(?<spage>\d+)-(?<epage>\d+)/', $html, $mm))
			{
				//print_r($mm);
				//$reference->volume = $mm['volume'];
				//$reference->issue = $mm['issue'];
				//$reference->spage = $mm['spage'];
				//$reference->epage = $mm['epage'];
				//$reference->year = $mm['year'];
				
				$reference->journal->pages = $mm['spage'] . '--' . $mm['epage'];			
			}	
		}
		
		if (preg_match('/Abstract:\s*<p>(?<abstract>.*)<\/p>/Uu', $html, $m))
		{
			$reference->abstract = trim($m['abstract']);
		}
	}
	
	//print_r($reference);
}

//--------------------------------------------------------------------------------------------------
function get_doi_metadata_unixref($doi, &$reference)
{
	global $config;
	
	$url = 'http://www.crossref.org/openurl?pid=r.page@bio.gla.ac.uk&rft_id=info:doi/' . $doi . '&noredirect=true&format=unixref';
	
	echo "-- $url\n";
	
	$xml = get($url);
	
	//echo $xml;
	
			
	if (preg_match('/<doi_record/', $xml))
	{	
		$have_issn = false;
	
	
		$dom= new DOMDocument;
		$dom->loadXML($xml);
		$xpath = new DOMXPath($dom);
		
		$xpath_query = '//journal/journal_metadata/full_title';
		$nodeCollection = $xpath->query ($xpath_query);
		
		foreach($nodeCollection as $node)
		{
			$reference->journal = new stdclass;
			$reference->journal->name = $node->firstChild->nodeValue;
		}

		$xpath_query = '//journal/journal_issue/journal_volume/volume';
		$nodeCollection = $xpath->query ($xpath_query);
		
		foreach($nodeCollection as $node)
		{
			$reference->journal->volume = $node->firstChild->nodeValue;
		}
		
		$xpath_query = '//journal/journal_article/pages/first_page';
		$nodeCollection = $xpath->query ($xpath_query);
		
		foreach($nodeCollection as $node)
		{
			$reference->journal->pages = $node->firstChild->nodeValue;
		}
		$xpath_query = '//journal/journal_article/pages/last_page';
		$nodeCollection = $xpath->query ($xpath_query);
		
		foreach($nodeCollection as $node)
		{
			$reference->journal->pages .= '--' . $node->firstChild->nodeValue;
		}
		
		$reference->title = '';
		
		$xpath_query = '//journal_article[@publication_type="full_text"]/titles/title';
		$nodeCollection = $xpath->query ($xpath_query);
		foreach($nodeCollection as $node)
		{
   			$children  = $node->childNodes;

    		foreach ($children as $child) 
    		{ 
        		$reference->title  .= $node->ownerDocument->saveHTML($child);
    		}
		}
			
		$reference->title = strip_tags($reference->title);
			
		$reference->title = preg_replace('/\s\s+/u', ' ', $reference->title);
		$reference->title = preg_replace('/^\s+/u', '', $reference->title);
		$reference->title = preg_replace('/\s+$/u', '', $reference->title);
		$reference->title = preg_replace('/\n/', '', $reference->title);
		
		$xpath_query = '//journal/journal_metadata/issn[@media_type="print"]';
		$nodeCollection = $xpath->query ($xpath_query);
		
		foreach($nodeCollection as $node)
		{
			if (isset($reference->journal))
			{
				$identifier = new stdclass;
				$identifier->type = 'issn';
				$identifier->id = $node->firstChild->nodeValue;
				
				if (strlen($identifier->id) == 8)
				{
					$identifier->id = substr($identifier->id, 0, 4) . '-' . substr($identifier->id, 4);
				}				
				$reference->journal->identifier[] = $identifier;
				
				$have_issn = true;
			}
		}

		if (!$have_issn)
		{
			$xpath_query = '//journal/journal_metadata/issn[@media_type="electronic"]';
			$nodeCollection = $xpath->query ($xpath_query);
			
			foreach($nodeCollection as $node)
			{
				if (isset($reference->journal))
				{
					$identifier = new stdclass;
					$identifier->type = 'issn';
					$identifier->id = $node->firstChild->nodeValue;
					
					if (strlen($identifier->id) == 8)
					{
						$identifier->id = substr($identifier->id, 0, 4) . '-' . substr($identifier->id, 4);
					}				
					$reference->journal->identifier[] = $identifier;
					
					$have_issn = true;
				}
			}
		}	
		
		$xpath_query = '//journal_article/publication_date[@media_type="print"]/year';
		$nodeCollection = $xpath->query ($xpath_query);
		foreach($nodeCollection as $node)
		{
			$reference->year = $node->firstChild->nodeValue;
		}
		
		
		if (!isset($reference->author))
		{
			$xpath_query = '//journal_article/contributors/person_name[@contributor_role="author"]';
			$nodeCollection = $xpath->query ($xpath_query);
			foreach($nodeCollection as $node)
			{
				$author = new stdclass;
				$nc = $xpath->query ('given_name', $node);
				foreach($nc as $n)
				{
					$author->firstname = $n->firstChild->nodeValue;
				}
				$nc = $xpath->query ('surname', $node);
				foreach($nc as $n)
				{
					$author->lastname = $n->firstChild->nodeValue;
				}
				$author->name = $author->firstname . ' ' . $author->lastname;
			
				$reference->author[] = $author;
			}
		
		}
		
	
	
	
	}
}
//--------------------------------------------------------------------------------------------------
function find_doi(&$reference)
{
	global $config;
	
	$found = false;
	$have_issn = false;
	
	//print_r($reference);
	
	$url = 'http://www.crossref.org/openurl?pid=r.page@bio.gla.ac.uk'
	 . '&title=' . urlencode($reference->journal->name)
	 . '&volume=' . $reference->journal->volume
	 . '&spage=' . $reference->journal->pages;
	 
	 
	 if (isset($reference->journal->identifier))
	 {
	 	$url .= '&issn=' . $reference->journal->identifier[0]->id;
	 	$have_issn = true;
	 }
	 
	 $url .=  '&noredirect=true&format=unixref';
	
	//echo "-- $url\n";
	
	$xml = get($url);
	
	//echo $xml;
			
	if (preg_match('/<doi_record/', $xml))
	{	
		
		$dom= new DOMDocument;
		$dom->loadXML($xml);
		$xpath = new DOMXPath($dom);
		
		$xpath_query = '//journal_article/doi_data/doi';
		$nodeCollection = $xpath->query ($xpath_query);
		
		foreach($nodeCollection as $node)
		{
			$identifier = new stdclass;
			$identifier->type = 'doi';
			$identifier->id = $node->firstChild->nodeValue;

			$reference->identifier[] = $identifier;
			
			$found = true;
			
			/*
			if (preg_match('/10.2307\/(?<id>\d+)$/', $identifier->id, $m))
			{
				augment_jstor($reference, $m['id']);
			}
			*/
			
			// fix Canadian Journal of Botany fuck up
			if (preg_match('/10.1139\/cjb/', $identifier->id))
			{
				//echo "xxx\n";
				$nc = $xpath->query ('//journal_article/doi_data/resource');
				foreach($nc as $n)
				{
					$url = $n->firstChild->nodeValue;
					$url = preg_replace('/^(.*)\?cjb_/', '', $url);
					$url = preg_replace('/_\d+(.*)$/', '', $url);
					
					$reference->identifier = array();
					$identifier = new stdclass;
					$identifier->type = 'doi';
					$identifier->id = '10.1139/' . $url;
					$reference->identifier[] = $identifier;
					
					
				}
			}
			
		}
		
		
		
		if (!$have_issn)
		{
			$xpath_query = '//journal/journal_metadata/issn[@media_type="print"]';
			$nodeCollection = $xpath->query ($xpath_query);
			
			foreach($nodeCollection as $node)
			{
				if (isset($reference->journal))
				{
					$identifier = new stdclass;
					$identifier->type = 'issn';
					$identifier->id = $node->firstChild->nodeValue;
					
					if (strlen($identifier->id) == 8)
					{
						$identifier->id = substr($identifier->id, 0, 4) . '-' . substr($identifier->id, 4);
					}				
					$reference->journal->identifier[] = $identifier;
					
					$have_issn = true;
				}
			}
		}
		
		if (!$have_issn)
		{
			$xpath_query = '//journal/journal_metadata/issn[@media_type="electronic"]';
			$nodeCollection = $xpath->query ($xpath_query);
			
			foreach($nodeCollection as $node)
			{
				if (isset($reference->journal))
				{
					$identifier = new stdclass;
					$identifier->type = 'issn';
					$identifier->id = $node->firstChild->nodeValue;
					
					if (strlen($identifier->id) == 8)
					{
						$identifier->id = substr($identifier->id, 0, 4) . '-' . substr($identifier->id, 4);
					}				
					$reference->journal->identifier[] = $identifier;
					
					$have_issn = true;
				}
			}
		}	
	
		
	
	}
	
	return $found;
}


if (0)
{

$reference = new stdclass;

$reference->journal = new stdclass;
$reference->journal->name = 'Novon';
$reference->journal->volume = 8;
$reference->journal->issue = 1;
$reference->journal->pages = 23;

//$reference->year = 1997;


find_doi($reference);


print_r($reference);
}

?>