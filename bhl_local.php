<?php

// look in local BHL

// assumes we know item for a given reference

error_reporting(E_ALL);

require_once (dirname(__FILE__) . '/adodb5/adodb.inc.php');


//--------------------------------------------------------------------------------------------------
// From http://snipplr.com/view/6314/roman-numerals/
// Expand subtractive notation in Roman numerals.
function roman_expand($roman)
{
	$roman = str_replace("CM", "DCCCC", $roman);
	$roman = str_replace("CD", "CCCC", $roman);
	$roman = str_replace("XC", "LXXXX", $roman);
	$roman = str_replace("XL", "XXXX", $roman);
	$roman = str_replace("IX", "VIIII", $roman);
	$roman = str_replace("IV", "IIII", $roman);
	return $roman;
}
    
//--------------------------------------------------------------------------------------------------
// From http://snipplr.com/view/6314/roman-numerals/
// Convert Roman number into Arabic
function arabic($roman)
{
	$result = 0;
	
	$roman = strtoupper($roman);

	// Remove subtractive notation.
	$roman = roman_expand($roman);

	// Calculate for each numeral.
	$result += substr_count($roman, 'M') * 1000;
	$result += substr_count($roman, 'D') * 500;
	$result += substr_count($roman, 'C') * 100;
	$result += substr_count($roman, 'L') * 50;
	$result += substr_count($roman, 'X') * 10;
	$result += substr_count($roman, 'V') * 5;
	$result += substr_count($roman, 'I');
	return $result;
} 

//--------------------------------------------------------------------------------------------------
// Convert Arabic numerals into Roman numerals.
function roman($arabic)
{
	$ones = Array("", "I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX");
	$tens = Array("", "X", "XX", "XXX", "XL", "L", "LX", "LXX", "LXXX", "XC");
	$hundreds = Array("", "C", "CC", "CCC", "CD", "D", "DC", "DCC", "DCCC", "CM");
	$thousands = Array("", "M", "MM", "MMM", "MMMM");

	if ($arabic > 4999)
	{
		// For large numbers (five thousand and above), a bar is placed above a base numeral to indicate multiplication by 1000.
		// Since it is not possible to illustrate this in plain ASCII, this function will refuse to convert numbers above 4999.
		die("Cannot represent numbers larger than 4999 in plain ASCII.");
	}
	elseif ($arabic == 0)
	{
		// About 725, Bede or one of his colleagues used the letter N, the initial of nullae,
		// in a table of epacts, all written in Roman numerals, to indicate zero.
		return "N";
	}
	else
	{
		// Handle fractions that will round up to 1.
		if (round(fmod($arabic, 1) * 12) == 12)
		{
			$arabic = round($arabic);
		}

		// With special cases out of the way, we can proceed.
		// NOTE: modulous operator (%) only supports integers, so fmod() had to be used instead to support floating point.
		$roman = $thousands[($arabic - fmod($arabic, 1000)) / 1000];
		$arabic = fmod($arabic, 1000);
		$roman .= $hundreds[($arabic - fmod($arabic, 100)) / 100];
		$arabic = fmod($arabic, 100);
		$roman .= $tens[($arabic - fmod($arabic, 10)) / 10];
		$arabic = fmod($arabic, 10);
		$roman .= $ones[($arabic - fmod($arabic, 1)) / 1];
		$arabic = fmod($arabic, 1);


		return $roman;
	}
}


//--------------------------------------------------------------------------------------------------
function find(&$reference)
{
	global $db;
	
	if ($reference->double)
	{
		$page = $reference->journal->pages;
		if ($reference->journal->pages % 2 == 0)
		{
			$page--;
		}
		$sql = "SELECT * FROM bhl_page WHERE ItemID=" . $reference->ItemID . "  AND PageNumber=\"" . $page . "\"";
	}
	else
	{
		$sql = "SELECT * FROM bhl_page WHERE ItemID=" . $reference->ItemID . "  AND ((PageNumber=\"" . $reference->journal->pages . "\") OR (PageNumber=\"[" . $reference->journal->pages . ']"))';
	}
	
	echo "-- $sql\n";
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	if ($result->NumRows() == 1)
	{
		$reference->PageID = $result->fields['PageID'];
	}
	
	//print_r($reference);
}
	
	
//--------------------------------------------------------------------------------------------------
function find_item($TitleID, $volume, $issue='', $year = '')
{
	global $db;
	
	$ItemID = 0;
	
	$pattern = '';
	
	switch ($TitleID)
	{
		case 5371:
			$pattern = "v.$volume";
			break;		
			
		case 6898:
			$pattern = "v.$volume (%";
			break;

		default:
			$pattern = "$volume";
			break;
	}
	
	$sql = 'SELECT * FROM bhl_item WHERE TitleID=' . $TitleID . '  AND VolumeInfo LIKE "' . $pattern . '"';
	echo "-- $sql\n";
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	//print_r($result);
	
	if ($result->NumRows() == 1)
	{
		$ItemID = $result->fields['ItemID'];
	}
	
	return $ItemID;
}


//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	'root', '', 'ipni');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$TitleID = 5371;
$sql = 'SELECT * FROM `names_indexfungorum` WHERE title="Syll. fung. (Abellini)" AND volume=1';
$sql = 'SELECT * FROM `names_indexfungorum` WHERE title="Syll. fung. (Abellini)" AND volume=2';
$sql = 'SELECT * FROM `names_indexfungorum` WHERE title="Syll. fung. (Abellini)" AND volume=3';
$sql = 'SELECT * FROM `names_indexfungorum` WHERE title="Syll. fung. (Abellini)" AND volume=4';
$sql = 'SELECT * FROM `names_indexfungorum` WHERE title="Syll. fung. (Abellini)" AND volume=5';

$sql = 'SELECT * FROM `names_indexfungorum` WHERE title="Syll. fung. (Abellini)" AND volume=17';


$TitleID = 6898;
$sql = 'SELECT * FROM `names_indexfungorum` WHERE title="Grevillea" AND volume=10';


$sql .= ' AND bhl IS NULL';

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

//print_r($result);

while (!$result->EOF) 
{	
	$reference = new stdclass;
	
	$reference->double = false; // true if two pages on same physcial page
	
	$reference->id = $result->fields['id'];	
	$reference->name = $result->fields['nameComplete'];	
	$reference->journal = new stdclass;
	$reference->journal->name = utf8_encode($result->fields['title']);
	$reference->journal->volume = $result->fields['volume'];
	$reference->journal->pages = $result->fields['pages'];
	$reference->year = $result->fields['year'];
	
	//print_r($reference);

	
	//print_r($reference);
	
	
	
		// Map publication to ItemID here... 
		
		if (isset($reference->journal->volume))
		{
			if (!is_numeric($reference->journal->volume))
			{
				$reference->journal->volume = arabic($reference->journal->volume);
			}
				
			//print_r($reference);
		
			//$ItemID = find_item($TitleID, $reference->journal->volume);
			
			if (isset($reference->journal->issue))
			{
				if (isset($reference->year))
				{
					$ItemID = find_item($TitleID, $reference->journal->volume, $reference->journal->issue, $reference->year);
				}
				else
				{
					$ItemID = find_item($TitleID, $reference->journal->volume, $reference->journal->issue);
				}				
			}
			else
			{
				if (isset($reference->year))
				{
					$ItemID = find_item($TitleID, $reference->journal->volume, '', $reference->year);
				}
				else
				{
					$ItemID = find_item($TitleID, $reference->journal->volume);
				}
			}
		
		

		
		
		echo "-- ItemID=$ItemID \n";
		
		if ($ItemID != 0)
		{
			$reference->ItemID = $ItemID;		
			//print_r($reference);
			find($reference);
			//print_r($reference);
			
			if (isset($reference->PageID))
			{
				echo 'UPDATE names_indexfungorum SET bhl=' . $reference->PageID . ' WHERE Id="' . $reference->id . '";' . "\n";
			}
			
			//exit();
		}		

	}
	else
	{
		echo "-- not matched\n";
		//exit();
	}
	
	
	$result->MoveNext();
}

?>