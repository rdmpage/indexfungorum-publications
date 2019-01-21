<?php

$xml = '<?xml version="1.0"?>

<rdf:RDF xmlns:tn="http://rs.tdwg.org/ontology/voc/TaxonName#" xmlns:tpub="http://rs.tdwg.org/ontology/voc/PublicationCitation#" xmlns:tto="http://rs.tdwg.org/ontology/voc/Specimen#" xmlns:tcom="http://rs.tdwg.org/ontology/voc/Common#" xmlns:tc="http://rs.tdwg.org/ontology/voc/TaxonConcept#" xmlns:trank="http://rs.tdwg.org/ontology/voc/TaxonRank#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:owl="http://www.w3.org/2002/07/owl#" xmlns:dcterms="http://purl.org/dc/terms/">

  <tn:TaxonName rdf:about="urn:lsid:indexfungorum.org:names:364004">

    <dc:title>Alectoriomyces sarmentosae Cif. &amp; Tomas. 1953</dc:title>

    <owl:versionInfo>1.1.2.1</owl:versionInfo>

    <tn:nameComplete>Alectoriomyces sarmentosae</tn:nameComplete>

    <tn:genusPart>Alectoriomyces</tn:genusPart>

    <tn:specificEpithet>sarmentosae</tn:specificEpithet>

    <tn:authorship>Cif. &amp; Tomas.</tn:authorship>

    <tn:basionymAuthorship>Cif. &amp; Tomas.</tn:basionymAuthorship>

    <tn:year>1953</tn:year>

    <tcom:publishedIn>Atti Ist. bot. Univ. Lab. crittog. Pavia, Ser. 5 10(1): 44, 70 (1953)</tcom:publishedIn>

    <tn:publication>

      <tpub:PublicationCitation>

        <dc:identifier>1235</dc:identifier>

        <tpub:year>1953</tpub:year>

        <tpub:title>Atti Ist. bot. Univ. Lab. crittog. Pavia, Ser. 5</tpub:title>

        <tpub:volume>10</tpub:volume>

        <tpub:number>1</tpub:number>

        <tpub:pages>44, 70</tpub:pages>

      </tpub:PublicationCitation>

    </tn:publication>

    <tn:rankString>sp.</tn:rankString>

    <tn:nomenclaturalCode rdf:resource="http://rs.tdwg.org/ontology/voc/TaxonName#ICBN" />

  </tn:TaxonName>

  <tn:NomenclaturalCodeTerm rdf:about="http://rs.tdwg.org/ontology/voc/TaxonName#ICBN" />

</rdf:RDF>
';



// Extract stuff
$basedir = '/Volumes/G-DRIVE slim/indexfungorum-rdf';
$basedir = '/Users/rpage/Development/indexfungorum-rdf-harvest-o/rdf';
$basedir = dirname(__FILE__) . '/rdf'; // local directory

$files1 = scandir($basedir);

foreach ($files1 as $directory)
{
	if (preg_match('/^\d+$/', $directory))
	{	
		
		$files2 = scandir($basedir . '/' . $directory);

		foreach ($files2 as $filename)
		{
			if (preg_match('/\.xml$/', $filename))
			{	

				$xml = file_get_contents($basedir . '/' . $directory . '/' . $filename);


	$dom= new DOMDocument;
	$dom->loadXML($xml);
	$xpath = new DOMXPath($dom);
	
	$xpath->registerNamespace('dc',      'http://purl.org/dc/elements/1.1/');
	$xpath->registerNamespace('dcterms', 'http://purl.org/dc/terms/');
	$xpath->registerNamespace('tdwg_pc', 'http://rs.tdwg.org/ontology/voc/PublicationCitation#');
	$xpath->registerNamespace('tdwg_co', 'http://rs.tdwg.org/ontology/voc/Common#');
	$xpath->registerNamespace('tdwg_tn', 'http://rs.tdwg.org/ontology/voc/TaxonName#');
	$xpath->registerNamespace('rdfs',    'http://www.w3.org/2000/01/rdf-schema#');
	$xpath->registerNamespace('rdf',     'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
	
	
	$obj = new stdclass;
	
	// Identifier (must be position 1 because if basionymn present IF adds it as well
	$nodeCollection = $xpath->query ('//tdwg_tn:TaxonName[1]/@rdf:about');
	foreach ($nodeCollection as $node)
	{
		$obj->id = str_replace('urn:lsid:indexfungorum.org:names:', '', $node->firstChild->nodeValue);
	}

	// basionym
	$nodeCollection = $xpath->query ('//tdwg_tn:hasBasionym/@rdf:resource');
	foreach ($nodeCollection as $node)
	{
		$obj->basionym_id = str_replace('urn:lsid:indexfungorum.org:names:', '', $node->firstChild->nodeValue);
	}

	// Name
	$nodeCollection = $xpath->query ('//tdwg_tn:nameComplete');
	foreach ($nodeCollection as $node)
	{
		$obj->nameComplete = $node->firstChild->nodeValue;
	}

	$nodeCollection = $xpath->query ('//tdwg_tn:genusPart');
	foreach ($nodeCollection as $node)
	{
		$obj->genusPart = $node->firstChild->nodeValue;
	}
	$nodeCollection = $xpath->query ('//tdwg_tn:specificEpithet');
	foreach ($nodeCollection as $node)
	{
		$obj->specificEpithet = $node->firstChild->nodeValue;
	}
	$nodeCollection = $xpath->query ('//tdwg_tn:infraspecificEpithet');
	foreach ($nodeCollection as $node)
	{
		$obj->infraspecificEpithet = $node->firstChild->nodeValue;
	}
	$nodeCollection = $xpath->query ('//tdwg_tn:authorship');
	foreach ($nodeCollection as $node)
	{
		$obj->authorship = $node->firstChild->nodeValue;
	}
	$nodeCollection = $xpath->query ('//tdwg_tn:basionymAuthorship');
	foreach ($nodeCollection as $node)
	{
		$obj->basionymAuthorship = $node->firstChild->nodeValue;
	}
	$nodeCollection = $xpath->query ('//tdwg_tn:combinationAuthorship');
	foreach ($nodeCollection as $node)
	{
		$obj->combinationAuthorship = $node->firstChild->nodeValue;
	}

	$nodeCollection = $xpath->query ('//tdwg_tn:rankString');
	foreach ($nodeCollection as $node)
	{
		$obj->rankString = $node->firstChild->nodeValue;
	}
	$nodeCollection = $xpath->query ('//tdwg_tn:nomenclaturalCode/@rdf:resource');
	foreach ($nodeCollection as $node)
	{
		$obj->nomenclaturalCode = str_replace('http://rs.tdwg.org/ontology/voc/TaxonName#', '', $node->firstChild->nodeValue);
	}
	
	
	// publication
	$nodeCollection = $xpath->query ('//tdwg_co:publishedIn');
	foreach ($nodeCollection as $node)
	{
		$obj->publishedIn = $node->firstChild->nodeValue;
	}
	
	$nodeCollection = $xpath->query ('//tdwg_pc:PublicationCitation/dc:identifier');
	foreach ($nodeCollection as $node)
	{
		$obj->identifier = $node->firstChild->nodeValue;
	}
	$nodeCollection = $xpath->query ('//tdwg_pc:PublicationCitation/tdwg_pc:title');
	foreach ($nodeCollection as $node)
	{
		$obj->title = $node->firstChild->nodeValue;
	}
	$nodeCollection = $xpath->query ('//tdwg_pc:PublicationCitation/tdwg_pc:volume');
	foreach ($nodeCollection as $node)
	{
		$obj->volume = $node->firstChild->nodeValue;
	}
	$nodeCollection = $xpath->query ('//tdwg_pc:PublicationCitation/tdwg_pc:number');
	foreach ($nodeCollection as $node)
	{
		$obj->number = $node->firstChild->nodeValue;
	}	
	$nodeCollection = $xpath->query ('//tdwg_pc:PublicationCitation/tdwg_pc:pages');
	foreach ($nodeCollection as $node)
	{
		$obj->pages = $node->firstChild->nodeValue;
	}	
	$nodeCollection = $xpath->query ('//tdwg_pc:PublicationCitation/tdwg_pc:year');
	foreach ($nodeCollection as $node)
	{
		$obj->year = $node->firstChild->nodeValue;
	}
	

	//print_r($obj);
	
	//if ($obj->id >= 5015136)
	{
		
		$keys = array();
		$values = array();
		
		foreach ($obj as $k => $v)
		{
			$keys[] = $k;
			$values[] = "'" . addslashes($v) . "'";
		}
		
		$sql = 'REPLACE INTO names_indexfungorum ('
			. join(",", $keys) . ') VALUES ('
			. join(",", $values) . ');';
			
		echo $sql . "\n";
	}	
		
		
			}
		}
		
		
	}
}	

?>
