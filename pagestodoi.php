<?php

// extract DOIs from pages

error_reporting(E_ALL);

require_once (dirname(__FILE__) . '/adodb5/adodb.inc.php');
require_once (dirname(__FILE__) . '/lib.php');



//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	'root', '', 'ipni');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;


$journals = array('Fungal Diversity');

//$journals = array('Plos ONE');


foreach ($journals as $journal)
{
	$sql = 'SELECT * FROM names_indexfungorum WHERE  title = "' . $journal .'" AND pages LIKE "10.%" AND doi IS NULL';
	//$sql = 'SELECT * FROM names_indexfungorum WHERE  title = "' . $journal .'"  AND doi IS NULL';
	
	$sql .= ' AND DATE_SUB(CURDATE(),INTERVAL 1 DAY) <= updated;';


	$sql = 'SELECT * FROM names_indexfungorum WHERE year="2018" and doi is null';


	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	while (!$result->EOF) 
	{	
		$id = $result->fields['id'];
		
		$pages = $result->fields['pages'];
		
		$doi = '';
		
		if (preg_match('/^(?<doi>10\..*),\s*\[/', $pages, $m))
		{
			$doi = $m['doi'];
			
			echo "-- $doi\n";
		}

		if (preg_match('/^(?<doi>10\..*),\s*\d/', $pages, $m))
		{
			$doi = $m['doi'];
			
			echo "-- $doi\n";
		}
		
		
		if (preg_match('/e(?<id>\d+),/', $pages, $m))
		{
			$doi = '10.1371/journal.pone.' . str_pad($m['id'], 7, '0', STR_PAD_LEFT);
		}
		
		if ($doi != '')
		{
			echo 'UPDATE names_indexfungorum SET doi="' . $doi . '" WHERE id=' . $id . ';' . "\n";
		}
	
		$result->MoveNext();
	}
}
?>