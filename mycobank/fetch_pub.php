<?php

require_once (dirname(__FILE__) . '/lib.php');
require_once (dirname(__FILE__) . '/couchsimple.php');


$ids = array(34531,15474,7025);

$ids=array(18252,4187,15929);

$ids=array(5328,29826,34531,51123,53920,18252,4187,15929,3653,15074,28165,18593,24719,29869,6971,15668,7049,11663,14447,52691,27370,45196,8910,28034,18824,48679,1340,8236,33632,19741,40485,56440,28498,14443,28512,14449,28503,3032,15117,5933,29875,6976,11636,39672,14445,35994,17387,32519,28026,42887,3538,15543,3988,8882,51933,1454,1332,4198,7065,11627,27919,3728,3378,11098,37234,18097,3993,3623,28023,11621,9292,51056,7026,40534,26149,22200,29871,30938,253,7021,14965,3832,3772,28036,7067,30644,26081,30021,11664,2814,3666,32246,30643,28599,6963,34522,40438,40503,33839,3499,46248,28482,30693,11626,28595,23713,51779,34529,28585,16200,8883,15118,3834,15431,27848,40947,44240,30269,30702,13898,13810,28481,13602,53948,3651,18689,46076,28587,6964,50877,56182,37346,38519,40499,27925,14448,42597,4336,52103,30200,23662,40500,29828,52104,38688,39752,30709,14444,30740,44181,15667,3564,7012,22787,27849,38524,48600,46691,55200,29278,3980,51949,11623,55659,29886,8825,22837,22788,3569,22813,7917,56157,18237,33629,22822,22774,30264,22832,23661,22835,36872,36145,22845,22775,23166,38522,33644,5651,28121,34007,52680,4739,34394,26382,44185,48060,19242,19188,28586,51504,17905,7031,7006,34163,29987,30858,4337,16161,46803,41217,15084,14709,31130,29821,34458,45033,3033,23664,14001,46250,11665,40200,45688,46233,4329,23711,29890,26175,26144,49234,48651,7991,36615,31647,29874,51079,56535,28594,40159,28187,28132,26599,4368,47007,6610,52307,3646,29991,19748,3979,51762,55857,52512,5327,12006,21784,30132,34532,31651,11514,43687,3725,742,44700,32181,22856,40250,13809,6967,22829,9591,39895,30053,11383,6962,52350,30935,10632,7084,3831,3975,7087,28166,28172,6968,1334,3570,6325,28169,28499,41195,23714,19244,46020,1472,3662,3659,3660,45735,15648,28171,39922,46600,28480,19261,36622,5143,36150,18687,18089,30713,36624);

$ids=array(1340);

//$ids=array(36624);

//$ids = array(30643); // DOI field has BHL URL
//$ids = array(8883);
//$ids = array(3646);
//$ids = array(60927); PLoS One

$ids=array(7049,30269,21784,27919); // fix DOI

$ids=array(29890);

$ids=array(35943);
$ids=array(35942);

$ids=array(35000);

$start = 45000;
$stop  = 50000;

$force = false;
$count = 1;

for ($id = $start; $id <= $stop; $id++)
//foreach ($ids as $id)
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
	$obj->_id = 'ref' . $id;
	$obj->URL = array();
	$obj->URL[] = 'http://www.mycobank.org/BioloMICS.aspx?TableKey=14682616000000061&Rec=' . $id .'&Fields=All';
	
	$obj->ok = false;
	
	$obj->author = array();
	
	$tags = array('title_', 'journal_pt_', 'volume_', 'issue_', 'year_', 'pagefrom_', 'pageto_', 'doi_',
	 'pubmedid_', 'hyperlinkto_', 'doctype_', 'authors_', 'editors_', 'publisher_', 'isbn_', 'id_', 'series_', 'taxonname_pt_', 'e3811');
	
	$tag_map = array(
		'title_' 		=> 'title',
		'series_'		=> 'collection-title',
		'volume_' 		=> 'volume',
		'issue_' 		=> 'issue',
		//'doi_' 			=> 'DOI',
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
					
					$obj->ok = true;
				}
				else
				{
					switch ($tag)
					{
						case 'authors_':
							$parts = array();
							
							if (preg_match('/;/', $value))
							{
								$parts = preg_split('/;\s+/', $value);
							}
							else
							{
								if (preg_match_all('/\w+\$?,\s+[A-Z]\w+/', $value, $m))
								{
									$parts = preg_split('/\$?,\s+/', $value);
								}
								else
								{
									$parts[] = $value;
								}
							}
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

								case 'Book':
									$obj->type = 'book';
									break;
									
								default:
									$obj->type = $value;
									break;
							}
							break;
							
						// This doesn't seem to be used for DOIs but BHL links!
						case 'doi_':
							if (preg_match('/<Link><Name>(.*)<\/Name><Url>(?<url>http:\/\/www.biodiversitylibrary.org\/.*)<\/Url><\/Link>/', $value, $m))
							{
								$obj->URL[] = $m['url'];
								$obj->BHL = $m['url'];
							}
							
							// =<Link><Name>10.1016/j.mycres.2007.03.004</Name><Url>http://dx.doi.org/10.1016/j.mycres.2007.03.004</Url></Link>
							if (preg_match('/<Link><Name>(.*)<\/Name><Url>http:\/\/dx.doi.org\/(?<doi>.*)<\/Url><\/Link>/', $value, $m))
							{
								$obj->DOI = $m['doi'];
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
							
						case 'taxonname_pt_':
							if (preg_match_all('/<TargetRecord><Id>(?<id>\d+)<\/Id><Name>(?<name>.*)<\/Name><\/TargetRecord>/Uu', $value, $m))
							{
								$n = count($m[0]);
								
								$obj->names = array();
								for ($i = 0; $i < $n; $i++)
								{
									$obj->names[$m['id'][$i]] = $m['name'][$i];
								}
							
							}
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
	
	if ($obj->ok)
	{
		unset($obj->ok);
	
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
	
	// Give server a break every 10 items
	if (($count++ % 10) == 0)
	{
		$rand = rand(1000000, 3000000);
		echo "\n...sleeping for " . round(($rand / 1000000),2) . ' seconds' . "\n\n";
		usleep($rand);
	}	
	

	
}

?>