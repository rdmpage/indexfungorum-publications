<?php

error_reporting(E_ALL);

require_once (dirname(__FILE__) . '/adodb5/adodb.inc.php');
require_once (dirname(__FILE__) . '/lib.php');

require_once (dirname(__FILE__) . '/crossref.php');
//require_once (dirname(__FILE__) . '/jstage.php');

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


$journals = array(
'Mycologia'
);

$journals = array(
'Acta bot. neerl.',
'Am. Midl. Nat.'
);

/*
11363	Mycotaxon	0093-4666
10713	Annls mycol.	NULL
7431	Revis. gen. pl. (Leipzig)
6458	Hedwigia	NULL
5908	Sydowia	NULL
5672	Cat. Lich. Univers.	NULL
5359	Grevillea	NULL
3692	Trans. Br. mycol. Soc.	NU
3348	Bull. trimest. Soc. mycol.
3243	Stud. Mycol.	0166-0616
3228	Flora, Jena	NULL
3144	Can. J. Bot.	NULL
2959	Bull. Soc. mycol. Fr.	NU
2806	Bull. Torrey bot. Club	NU
2770	Beih. Nova Hedwigia	NULL
2748	Persoonia	0031-5850
2706	Nova Hedwigia	NULL
2675	Lichenologist	0024-2829
2549	Fungal Diversity	NULL
2420	J. Linn. Soc., Bot.	NULL
2363	Michelia	NULL
2246	Syst. mycol. (Lundae)	NU
2236	Docums Mycol.	NULL
2236	Mycol. Res.	0953-7562
*/

$journals = array(
//'Mycotaxon',
//'Stud. Mycol.',
'Can. J. Bot.',
'Persoonia',
'Lichenologist',
'J. Linn. Soc., Bot.',
'Mycol. Res.',
'N.Z. Jl Bot.',
'Mycol. Progr.',
'Phyotaxa',
'Int. J. Syst. Evol. Microbiol.'
);

$journals=array(
//'J. gen. appl. Microbiol., Tokyo'
//'Trans. Br. mycol. Soc.'
//'FEMS Yeast Res.'
//'PLoS ONE'
//'N.Z. Jl Bot.'
//'Mycol. Res.'
//'Botany'
//'Aust. J. Bot.'
//'Bryologist'
//'Polish Bot. J.'
//'Norw. Jl Bot.'
//'Feddes Repert. Spec. Nov., Beih.'
//'Stud. Mycol.',
//'Phyotaxa',
//'Lichenologist'
//'Mycokeys'
//'Stud. Mycol.'
//'J. Linn. Soc., Bot.'
//'Fungal Diversity'
//'S. Afr. J. Bot.'
//'Feddes Repert.'
//'IMA Fungus',
//'Trans. Roy. Soc. South Africa'
//'Nova Hedwigia'
//'Phytopathology'
//'Castanea'
//'Edinb. J. Bot.'
//'Aust. Syst. Bot.'
//'Am. Midl. Nat.'
//'Archives of Phytopathology and Plant Protection'
//'Bull. Jard. bot. Etat Brux.'
//'Stud. Mycol.'
//'Int. J. Syst. Bacteriol.'
//'Syst. Appl. Microbiol.'
//'Archives of Phytopathology and Plant Protection'
//'Brittonia'
//'Ann. Mo. bot. Gdn'
//'Can. J. Microbiol.'
//'Fungal Biology'
//'Cryptog. Mycol.'
//'Organ. Divers. Evol.'
//'Persoonia, Mol. Phyl. Evol. Fungi',
//'Telopea',
//'Stud. Mycol.'
//'Phytotaxa'
//'Acta bot. hung.'
//'Bothalia',
//'Candollea'
//'J. Torrey bot. Soc.'
//'Mol. Phylogen. Evol.'
//'New Phytol.'
//'Mycopathologia'
//'Mycotaxon'
//'Mycosphere'
//'Telopea'
//'Taxon'
//'Phytopath. Mediterr.'
//'Bot. Mag., Tokyo'
//'Nordic Jl Bot.'
//'Systematics and Geography of Plants'
//'Plant Ecology and Evolution'
//'FEMS Microbiol. Lett.'
//'Reproduction, Nutrition, Développement'
//'International Journal of Medicinal Mushrooms (Redding)'
//'Turkish Journal of Botany'
//'J. Eukary. Microbiol.'
//'J. Invert. Path.'
//'Diagn. Microbiol. Infect. Dis.'
//'Eur. J. Protist.'
//'Revta Iberoamer. Micol.'
//'Evansia'
//'Mycobiology'
//'MycoKeys'
//'Field Mycology'
//'Syst. Bot.'
//'Tropical Plant Pathology'
//'Ukr. Bot. J.'
//'Scottish Journal of Geology'
//'Agriculture & Forestry'
//'Anais Acad. Brasil. Ciênc.'
//'Antarctic Science'
//'Australas. Pl. Path.'
//'Current Research in Environmental & Applied Mycology'
//'Eur. J. Pl. Path.'
//'J. Threatened Taxa'
//'Syst. Biodiv.'
//'Bull. mens. Soc. linn. Lyon'

// to do
'Ann. bot. fenn.'
);

$journals = array(
/*'J. gen. appl. Microbiol., Tokyo',
'Trans. Br. mycol. Soc.',
'FEMS Yeast Res.',
'PLoS ONE',
'N.Z. Jl Bot.',
'Mycol. Res.',
'Botany',
'Aust. J. Bot.',
'Bryologist',
'Polish Bot. J.',
'Norw. Jl Bot.',
'Feddes Repert. Spec. Nov., Beih.',
'Stud. Mycol.',
'Phyotaxa',
'Lichenologist',
'Mycokeys',
'Stud. Mycol.',
'J. Linn. Soc., Bot.',
'Fungal Diversity',
'S. Afr. J. Bot.',
'Feddes Repert.',
'IMA Fungus',
'Trans. Roy. Soc. South Africa',
'Nova Hedwigia',
'Phytopathology',
'Castanea',
'Edinb. J. Bot.',
'Aust. Syst. Bot.',
'Am. Midl. Nat.',
'Archives of Phytopathology and Plant Protection',
'Bull. Jard. bot. Etat Brux.',
'Stud. Mycol.',
'Int. J. Syst. Bacteriol.',
'Syst. Appl. Microbiol.',
'Archives of Phytopathology and Plant Protection',
'Brittonia',
'Ann. Mo. bot. Gdn',
'Can. J. Microbiol.',
'Fungal Biology',
'Cryptog. Mycol.',
'Organ. Divers. Evol.',
'Persoonia, Mol. Phyl. Evol. Fungi',
'Telopea',
'Phytotaxa',
'Acta bot. hung.',
'Bothalia',
'Candollea',
'J. Torrey bot. Soc.',
'Mol. Phylogen. Evol.',
'New Phytol.',
'Mycopathologia',
'Mycotaxon',*/
'Mycosphere',
'Telopea',
'Taxon',
'Phytopath. Mediterr.',
'Bot. Mag., Tokyo',
'Nordic Jl Bot.',
'Systematics and Geography of Plants',
'Plant Ecology and Evolution',
'FEMS Microbiol. Lett.',
'Reproduction, Nutrition, Développement',
'International Journal of Medicinal Mushrooms (Redding)',
'Turkish Journal of Botany',
'J. Eukary. Microbiol.',
'J. Invert. Path.',
'Diagn. Microbiol. Infect. Dis.',
'Eur. J. Protist.',
'Revta Iberoamer. Micol.',
'Evansia',
'Mycobiology',
'MycoKeys',
'Field Mycology',
'Syst. Bot.',
'Tropical Plant Pathology',
'Ukr. Bot. J.',
'Scottish Journal of Geology',
'Agriculture & Forestry',
'Anais Acad. Brasil. Ciênc.',
'Antarctic Science',
'Australas. Pl. Path.',
'Current Research in Environmental & Applied Mycology',
'Eur. J. Pl. Path.',
'J. Threatened Taxa',
'Syst. Biodiv.',
'Bull. mens. Soc. linn. Lyon'
);


$journals = array('Review of Palaeobotany and Palynology (Amsterdam)');

$journals = array('Mycoscience');
$journals = array('J. Clin. Microbiol.');
$journals = array('Persoonia, Mol. Phyl. Evol. Fungi');
$journals = array('Acta Mycologica, Warszawa');
$journals = array('Aust. Syst. Bot.');
$journals = array('J. Clin. Microbiol.');
$journals = array('Fungal Diversity');
$journals = array('Nordic Jl Bot.');

$journals = array('Aust. J. Bot.');
$journals = array('Bot. Mar.');

$journals=array('N.Z. Jl Bot.', 'Phytotaxa','Mycotaxon','Persoonia');


$journals=array('N.Z. Jl Bot.', 'Phytotaxa','Mycotaxon','Persoonia');

$journals = array('Bot. Gaz.');



foreach ($journals as $journal)
{
	$sql = 'select * from names_indexfungorum where title = "' . $journal .'" and doi is NULL';	
	 //$sql .= ' AND year > 2013';
	//$sql .= ' AND id < 100000';
	//$sql .= ' AND volume IN (34,35,36,37)';
	//$sql .= ' AND volume = 64';

	//$sql = 'select * from names_indexfungorum where id > 813995 and doi is NULL';	
	
	
	//$sql = 'select * from names_indexfungorum where id=131467';
	
	//echo $sql . "\n";


	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	while (!$result->EOF) 
	{	
		
		$reference = new stdclass;
	
		$reference->id = $result->fields['id'];	
		$reference->journal = new stdclass;
		$reference->journal->name = utf8_encode($result->fields['title']);
		$reference->journal->volume = $result->fields['volume'];
		$reference->journal->pages = $result->fields['pages'];
		
		$reference->journal->pages = preg_replace('/^(\d+)(.*)$/', '$1', $reference->journal->pages);
		
		if ($journal == 'Nordic Jl Bot.')
		{
			if ($reference->journal->pages < 100)
			{
				$reference->journal->pages = str_pad($reference->journal->pages, 3, '0', STR_PAD_LEFT);
			}
		}
	
		if ($result->fields['issn'] != '')
		{
			$identifier = new stdclass;
			$identifier->type = 'issn';
			$identifier->id = $result->fields['issn'];
			$reference->journal->identifier[] = $identifier;
		}
	
		$reference->year = $result->fields['year'];
		
		//print_r($reference);
		
		echo "-- " . $reference->journal->name . ' ' . $reference->journal->volume . ' ' . $reference->journal->pages . "\n";
	
		echo "-- ";

		$steps = -50;
		$found = false;
		while (!$found and $steps < 0 && $reference->journal->pages > 0)
		{
			$found = find_doi($reference);
			//$found = find_jstage($reference);
	
			if (!$found)
			{
				$reference->journal->pages--;
				$steps++;
		
				echo " " . $reference->journal->pages;
			}
		}
					
		if ($found)
		{
			// print_r($reference);
		
			$updates = array();
				
			if (isset($reference->identifier))
			{
				foreach ($reference->identifier as $identifier)
				{
					switch ($identifier->type)
					{
						case 'doi':
							$updates[] = 'doi="' . $identifier->id . '"';
							break;
						
						default:
							break;
					}
				}
			}
		
			if (isset($reference->journal->identifier))
			{
				foreach ($reference->journal->identifier as $identifier)
				{
					switch ($identifier->type)
					{
						case 'issn':
							$updates[] = 'issn="' . $identifier->id . '"';
							break;
						
						default:
							break;
					}
				}
			}
		
			if (isset($reference->link))
			{
				foreach ($reference->link as $link)
				{
					switch ($link->anchor)
					{
						case 'LINK':
							$updates[] = 'url="' . $link->url . '"';
							break;

						case 'PDF':
							$updates[] = 'pdf="' . $link->url . '"';
							break;
						
						default:
							break;
					}
				}
			}
		
		
			echo "\n" . 'UPDATE names_indexfungorum SET ' . join(",", $updates) . ' WHERE Id="' . $reference->id . '";' . "\n";
		}

	
	
		$result->MoveNext();
	}
}
?>