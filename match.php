<?php

// martch using "remote" microcitation resolver

error_reporting(E_ALL);

require_once (dirname(__FILE__) . '/adodb5/adodb.inc.php');
require_once (dirname(__FILE__) . '/lib.php');

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
$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	'root', '', 'ipni');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 

// Webbia
// Myco. Progr.
// Mycologia
// Phytotaxa
// Stud. Mycol.
// Mycol. Res.
// Lichenologist

$title = 'Lichenologist';
$title = 'Cryptog. Mycol.';
$title = 'Mycotaxon';
$title = 'Kew Bull.';
$title = 'Harvard Pap. Bot.';
$title = 'Taiwania';
$title = 'Polish Bot. J.';
$title = 'Lichenologist';
$title = 'IMA Fungus';
$title = 'Bull. Soc. bot. Fr.';
$title = 'Bull. Jard. bot. État Brux.';
$title = 'Acta bot. Yunn.';
$title = 'Telopea';

$title = 'Schlechtendalia';
$title = 'Sydowia';

$sql = 'SELECT * FROM ipni.names_indexfungorum WHERE title="' . $title . '" and doi is NULL';

//$sql = 'SELECT * FROM ipni.names_indexfungorum WHERE issn="0031-5850" and doi is NULL';

//$sql .= ' and year = 2014';
//$sql .= ' and volume = 109';

//echo $sql . "\n";

$include_issue_in_search = false;
$include_authors_in_search = false;

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{	
	$reference = new stdclass;
	
	$reference->id = $result->fields['id'];
	
	/*
	// authors
	$reference->authors = $result->fields['Publishing_author'];
	$reference->authors = preg_replace('/([A-Z]\.)/', '', $reference->authors);
	$reference->authors = preg_replace('/\.$/', '', $reference->authors);
	$reference->authors = preg_replace('/(.*) ex /', '', $reference->authors);
	*/
	
	$reference->journal = new stdclass;
	$reference->journal->name = $result->fields['title'];
	
	/*
	// Clean
	if ($reference->journal->name == 'Kew Bull.')
	{
		$reference->journal->name = 'Kew Bulletin';
	}
	if ($reference->journal->name == 'Syst. Bot.')
	{
		$reference->journal->name = 'Systematic Botany';
	}
	if ($reference->journal->name == 'Bull. Misc. Inform. Kew')
	{
		$reference->journal->name = 'Bulletin of Miscellaneous Information (Royal Gardens, Kew)';
	}	
	*/	
	$identifier = new stdclass;
	$identifier->type = 'issn';
	$identifier->id = $result->fields['issn'];
	$reference->journal->identifier[] = $identifier;
	
	if ($result->fields['volume'])
	{
		$reference->journal->volume = $result->fields['volume'];
	}
	if ($result->fields['number'])
	{
		$reference->journal->issue = $result->fields['number'];
	}
	if ($result->fields['pages'])
	{
		$reference->journal->pages = $result->fields['pages'];
		
		$reference->journal->pages = preg_replace('/-\d+/', '', $reference->journal->pages);
	}
		
	$reference->year = $result->fields['year'];


	if (isset($reference->journal->pages))
	{
		// Find it...
		
		//print_r($reference);
		
		$parameters = array();
		
		if (isset($reference->journal->identifier))
		{
			foreach ($reference->journal->identifier as $identifier)
			{
				switch ($identifier->type)
				{
					case 'issn':
						$parameters['issn'] = $identifier->id;
						break;
					default:
						break;
				}
			}
		}
				
		if (isset($reference->journal->volume))
		{
		
			// temp
			//$reference->journal->volume = 16;
		
			$parameters['volume'] = $reference->journal->volume;
		}
		
		if ($include_issue_in_search)
		{
			if (isset($reference->journal->issue))
			{			
				$parameters['issue'] = $reference->journal->issue;
			}
		}		

		if (isset($reference->journal->pages))
		{
			$parameters['page'] = $reference->journal->pages;
		}
		
		/*
		if (isset($reference->journal->article_number))
		{
			$parameters['article_number'] = $reference->journal->article_number;
		}

		if (isset($reference->journal->series))
		{
			$parameters['series'] = $reference->journal->series;
		}
		*/
		
		if ($include_authors_in_search)
		{
			if (isset($reference->authors))
			{
				$parameters['authors'] = $reference->authors;
			}
		}
		
		
		//print_r($parameters);
		
		/*
		if (isset($reference->year))
		{
			$parameters['year'] = $reference->year;
		}
		*/
		
		

		$url = 'http://localhost/~rpage/microcitation/www/index.php?' . http_build_query($parameters);
			
		$json = get($url);
		
		//echo $url ."\n";
		
		//exit();
		//echo $json;
	
		$obj = json_decode($json);
	
		//print_r($obj);
	
		if (isset($obj->sql))
		{
			echo '-- ' . $obj->sql . ";\n";
		}
	
		if (isset($obj->found) && $obj->found)
		{
			//echo $obj->doi . "\n";
			
			if (count($obj->results) == 1)
			{
				// DOI
				if (isset($obj->results[0]->doi))
				{
					echo 'UPDATE names_indexfungorum SET doi="' . $obj->results[0]->doi . '" WHERE id="' . $reference->id . '";' . "\n";
				}
				
				// Handle
				if (isset($obj->results[0]->handle))
				{
					echo 'UPDATE names_indexfungorum SET handle="' . $obj->results[0]->handle . '" WHERE id="' . $reference->id . '";' . "\n";
				}

				// JSTOR
				if (isset($obj->results[0]->jstor))
				{
					echo 'UPDATE names_indexfungorum SET jstor="' . $obj->results[0]->jstor . '" WHERE id="' . $reference->id . '";' . "\n";
				}				
				
				// PDF
				if (isset($obj->results[0]->pdf))
				{
					echo 'UPDATE names_indexfungorum SET pdf="' . $obj->results[0]->pdf . '" WHERE id="' . $reference->id . '";' . "\n";
				}				
				
				// URL
				if (isset($obj->results[0]->url))
				{
					$use_url = true;
					
					if (isset($obj->results[0]->jstor)) { $use_url = false; }
					
					if (preg_match('/http:\/\/ci.nii.ac.jp\/naid\//', $obj->results[0]->url))
					{
						echo  'UPDATE names_indexfungorum SET cinii="' . str_replace('http://ci.nii.ac.jp/naid/', '', $obj->results[0]->url) . '" WHERE id="' . $reference->id . '";' . "\n";
					}
					
					if ($use_url)
					{
						echo 'UPDATE names_indexfungorum SET url="' . $obj->results[0]->url . '" WHERE id="' . $reference->id . '";' . "\n";
					}
				}
				
			}
		}
	}
	else
	{
		echo "-- no match\n";
	}
	
	
	$result->MoveNext();
}

?>