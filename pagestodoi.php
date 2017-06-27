<?php

// extract DOIs from pages

error_reporting(E_ALL);

require_once (dirname(__FILE__) . '/adodb5/adodb.inc.php');
require_once (dirname(__FILE__) . '/lib.php');



//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	'root', '', 'ipni');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;


$journals = array('Fungal Diversity');



foreach ($journals as $journal)
{
	$sql = 'SELECT * FROM names_indexfungorum WHERE  title = "' . $journal .'" AND pages LIKE "10.%" AND doi IS NULL';


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
		
		if ($doi != '')
		{
			echo 'UPDATE names_indexfungorum SET doi="' . $doi . '" WHERE id=' . $id . ';' . "\n";
		}
	
		$result->MoveNext();
	}
}
?>