<?php

// Dump data for darwin core style export to CouchDB
require_once(dirname(__FILE__) . '/config.inc.php');
require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');

require_once (dirname(__FILE__). '/couchsimple.php');



//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names_indexfungorum 'utf8'"); 


$page = 100;
$offset = 0;

$result = $db->Execute('SET max_heap_table_size = 1024 * 1024 * 1024');
$result = $db->Execute('SET tmp_table_size = 1024 * 1024 * 1024');


$done = false;

while (!$done)
{
	$sql = 'SELECT * FROM `names_indexfungorum` LIMIT ' . $page . ' OFFSET ' . $offset;
	
	$name = 'Ophioceras%';
	//$name = 'Rhytidhysteron%';
	//$name = 'Tubulicrinopsis%';
	//$name = 'Dendrominia%';
	$name = 'Minimelanolocus%';
	//$name = 'Belemnospora%';
	$name = 'Rhytidhysteron%';
	$name = 'Lasiodiplodia%';
	$name = 'Jaminaea%';
	$name = 'Thelonectria%';
	$name = 'Oudemansiella%';
	$name = 'Hohenbuehelia%';
	//$name = 'Irpex%';
	$sql = 'SELECT * FROM `names_indexfungorum` WHERE nameComplete LIKE "' . $name . '"';
	
	
	//$sql = 'SELECT * FROM `names_indexfungorum` WHERE title = "Schlechtendalia"';
	
	$sql .= ' LIMIT ' . $page . ' OFFSET ' . $offset;
	
	/*
	//$id = 522039;
	//$id = 550939;
	$id = 103809;
	$id = 570142;
	$sql = 'SELECT * FROM `names_indexfungorum` WHERE id=' . $id;
	*/
	
	echo $sql . "\n";

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	while (!$result->EOF && ($result->NumRows() > 0)) 
	{	
		$obj = new stdclass;
		
		$obj->id 					= 'urn:lsid:indexfungorum.org:names:' . $result->fields['id'];
		$obj->cluster_id = $obj->id;
		
		$obj->type = 'http://rs.tdwg.org/ontology/voc/TaxonName#TaxonName';
		
		// For DarwinCore
		//$obj->taxonID 				= 'urn:lsid:indexfungorum.org:names:' . $result->fields['id'];
		
		$obj->scientificNameID 		= 'urn:lsid:indexfungorum.org:names:' . $result->fields['id'];
		
		if ($result->fields['basionym_id'] != '')
		{
			$obj->originalNameUsageID   = 'urn:lsid:indexfungorum.org:names:' . $result->fields['basionym_id'];
		}		
		
		$obj->scientificName  		= $result->fields['nameComplete'];
		
		if ($result->fields['authorship'] != '')
		{
			$obj->scientificNameAuthorship  = utf8_encode($result->fields['authorship']);
		}
		
		// rank
		if ($result->fields['rankString'] != '')
		{
			$obj->verbatimTaxonRank  = $result->fields['rankString'];
		}				
		
		$obj->html = $obj->scientificName;
		
		if ($result->fields['genusPart'] != '')
		{
			$obj->genus = $result->fields['genusPart'];
			
			$obj->html = '<i>' . $obj->genus . '</i>';
		}	
		if ($result->fields['specificEpithet'] != '')
		{
			$obj->specificEpithet  = $result->fields['specificEpithet'];
			
			$obj->html .= ' <i>' . $obj->specificEpithet . '</i>';
		}		
		if ($result->fields['infraSpecificEpithet'] != '')
		{
			$obj->infraspecificEpithet  = $result->fields['infraSpecificEpithet'];
			
			if (isset($obj->verbatimTaxonRank))
			{
				$obj->html .= ' ' . $obj->verbatimTaxonRank;	
			}
			
			$obj->html .= ' <i>' . $obj->infraspecificEpithet . '</i>';			
		}	
		
		if (isset($obj->scientificNameAuthorship))
		{
			$obj->html .= ' ' . $obj->scientificNameAuthorship;	
		}
		
		$obj->kingdom 	=  'Fungi';

		$obj->nomenclaturalCode 	=  $result->fields['nomenclaturalCode'];
		
		
		// publication
		
		$publication_parts = array();
		
		if ($result->fields['title'] != '')
		{
			$publication_parts[] = utf8_encode($result->fields['title']);
			
			$obj->source = utf8_encode($result->fields['title']);
		}		
		if ($result->fields['volume'] != '')
		{
			$publication_parts[] = $result->fields['volume'];
		}		
		if ($result->fields['pages'] != '')
		{
			$publication_parts[] = $result->fields['pages'];
			
			if (is_numeric($result->fields['pages']))
			{
				$obj->microreference = $result->fields['pages'];
			}
		}		
		if ($result->fields['year'] != '')
		{
			$publication_parts[] = $result->fields['year'];
			
			if (isset($obj->scientificNameAuthorship))
			{
				$obj->html .= ',';
			}			
			$obj->html .= ' ' . $result->fields['year'];				
		}		
		
		if (count($publication_parts) > 0)
		{
			$obj->namePublishedIn = join(', ',  $publication_parts);
		}
		
		if ($result->fields['year'] != '')
		{
			$obj->namePublishedInYear  = $result->fields['year'];
		}		
		
		// identifiers and links
		if ($result->fields['issn'] != '')
		{
			$obj->issn  = $result->fields['issn'];
		}		
		
		if ($result->fields['doi'] != '')
		{
			$obj->doi  = $result->fields['doi'];
			$obj->doi = strtolower($obj->doi);
			
			$obj->namePublishedInID = 'doi:' . $obj->doi;
		}		
		
		if ($result->fields['jstor'] != '')
		{
			$obj->jstor  = $result->fields['jstor'];
			
			if (!isset($obj->namePublishedInID))
			{	
				$obj->namePublishedInID = 'http://www.jstor.org/stable/' . $obj->jstor;
			}
		}		
		
		if ($result->fields['biostor'] != '')
		{
			$obj->biostor  = $result->fields['biostor'];
			
			if (!isset($obj->namePublishedInID))
			{	
				$obj->namePublishedInID = 'http://biostor.org/reference/' . $obj->biostor;
			}
		}		
		
		if ($result->fields['url'] != '')
		{
			$obj->url  = $result->fields['url'];

			if (!isset($obj->namePublishedInID))
			{	
				$obj->namePublishedInID = $obj->url;
			}
		}	
			
		if ($result->fields['pdf'] != '')
		{
			$obj->pdf  = $result->fields['pdf'];
		}
				
		if ($result->fields['bhl'] != '')
		{
			$obj->bhl  = $result->fields['bhl'];
		}
				
		if ($result->fields['isbn'] != '')
		{
			$obj->isbn  = $result->fields['isbn'];
			
			if (!isset($obj->namePublishedInID))
			{	
				$obj->namePublishedInID = 'isbn:' . $obj->isbn;
			}
			
		}		
		
		// comments
		if ($result->fields['rdmp_comment'] != '')
		{
			$obj->taxonRemarks .= '(' . $result->fields['rdmp_comment'] . ')';
		}
		
		
		if ($result->fields['updated'] != '')
		{
			$obj->timestamp  = strtotime($result->fields['updated']);
		}		

		
		// message is a text/csv (we are simulating a row in a database)
		// https://tools.ietf.org/html/rfc4180
		$doc = new stdclass;
		
		$doc->_id = $obj->id;
		$doc->{'message-timestamp'} = date("c", time());
		$doc->{'message-modified'} 	= $doc->{'message-timestamp'};
		$doc->{'message-format'} 	= 'text/csv';
		
		$doc->message = $obj;
		
		print_r($doc);
		
		
		// Upload 
		echo "CouchDB...";
		$couch->add_update_or_delete_document($doc,  $doc->_id);

		
		$count++;

		$result->MoveNext();
	}
	
	//echo "-------\n";
	
	if ($result->NumRows() < $page)
	{
		$done = true;
	}
	else
	{
		$offset += $page;
		
		//if ($offset > 3000) { $done = true; }
	}
	
	
}
	



?>