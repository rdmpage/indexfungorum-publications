<?php

// IPNI data browser

require_once(dirname(__FILE__) . '/config.inc.php');
require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');


//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names_indexfungorum 'utf8'"); 


//--------------------------------------------------------------------------------------------------
function default_display()
{
	global $config;
	global $db;	
	
	// some stats
	$num_names = 0;
	$num_dois = 0;
	
	$sql = 'SELECT COUNT(id) AS c FROM names_indexfungorum';

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
	if ($result->NumRows() == 1)
	{
		$num_names = $result->fields['c'];
	}

	$sql = 'SELECT COUNT(id) AS c FROM names_indexfungorum WHERE doi IS NOT NULL';

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
	if ($result->NumRows() == 1)
	{
		$num_dois = $result->fields['c'];
	}
	
	
	
	
	
	echo '<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<base href="' . $config['web_root'] . '" />
		<link type="text/css" href="' . $config['web_root'] . 'css/main.css" rel="stylesheet" />
		<title>' . $config['site_name'] . '</title>
	</head>
	<body>
		<div style="float:right;">
			<form method="get" action="index.php">
			<input type="search"  name="q" id="q" value="" placeholder="Genus"></input>
			<input type="submit" value="Search" ></input>
			</form>
		</div>
		<h1>IndexFungorum Browser</h1>';
		
		
	$with = 100 * $num_dois/$num_names;
	$without = 100 - $with;
	
	echo '<img src="https://chart.googleapis.com/chart?cht=p3&chs=250x100&chd=t:' . $with . ',' . $without . '&chl=DOI|none" />';
		
		
	echo '
	</body>
</html>';
}


//--------------------------------------------------------------------------------------------------
function display_search($query, $type = 'genus')
{
	global $config;
	global $db;
	
	$found = false;
	
	$query = trim(mysql_escape_string($query));
	
	if (preg_match('/^\w+/', $query))
	{
		switch($type)
		{
			case 'genus':
				$sql = 'SELECT * FROM names_indexfungorum WHERE genusPart = ' . $db->qstr($query) . ' LIMIT 1';
			
				$result = $db->Execute($sql);
				if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
				
				if ($result->NumRows() == 1)
				{
					$genus = $query;
					display_genus($query);
					$found = true;
				}
				break;

			case 'publication':
				$sql = 'SELECT * FROM names_indexfungorum WHERE title = ' . $db->qstr($query) . ' LIMIT 1';
			
				$result = $db->Execute($sql);
				if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
				
				if ($result->NumRows() == 1)
				{
					$genus = $query;
					display_publication($query);
					$found = true;
				}
				break;
		
			default:
				break;
		}
				
				
	}
	
	if (!$found)
	{
		echo '<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<base href="' . $config['web_root'] . '" />
		<link type="text/css" href="' . $config['web_root'] . 'css/main.css" rel="stylesheet" />
		<title>' . $config['site_name'] . '</title>
	</head>
	<body>
		<div style="float:right;">
			<form method="get" action="index.php">
			<input type="search"  name="q" id="q" value="" placeholder="Genus"></input>
			<input type="submit" value="Search" ></input>
			</form>
		</div>
		<p>Sorry, your search for "' . $query . '" didn\'t match any data (note that you can only search for genus names).</p>
	</body>
</html>';

	
	
	
	}

}

//--------------------------------------------------------------------------------------------------
function display_publication($publication)
{
	$sql = 'SELECT * FROM names_indexfungorum WHERE title = "' . $publication . '" ORDER BY year, volume, pages';
	display_query($sql);
}

//--------------------------------------------------------------------------------------------------
function display_genus($genus)
{
	$sql = 'SELECT * FROM names_indexfungorum WHERE genusPart = "' . $genus . '" OR nameComplete="' . $genus . '" ORDER BY specificEpithet';
	display_query($sql);
}


//--------------------------------------------------------------------------------------------------
function display_query($sql)
{
	global $config;
	global $db;
	
	$species = array();
	$major_group ='';
	$family = '';
	
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	while (!$result->EOF) 
	{
		$record = new stdclass;
		$record->id = $result->fields['id'];
		
		$record->name = $result->fields['nameComplete'];
		$record->html = '<i>' . $record->name . '</i>';
		
		$record->html .= ' ' . utf8_encode($result->fields['authorship']);
		
		$record->publication = '<a href="?p=' . $result->fields['title'] . '">' . utf8_encode($result->fields['title']) . '</a> ' 
			. trim(utf8_encode($result->fields['volume']));
			
		if ($result->fields['number'] != '')
		{
			$record->publication .= '(' . $result->fields['number'] . ')';
		}
			
		$record->publication .= ': ' . trim(utf8_encode($result->fields['pages']));
		$record->publication .= ' ' . $result->fields['year'];
		
		$record->pages = $result->fields['pages'];
		
		// identifiers
		
		$identifiers = array('issn', 'doi', 'jstor', 'biostor', 'bhl', 'cinii', 'url', 'pdf', 'handle', 'isbn', 'oclc');
		foreach ($identifiers as $i)
		{
			if ($result->fields[$i] != '')
			{
				$record->{$i} = $result->fields[$i];
			}
		}
		
		// comments
		if ($result->fields['rdmp_comment'] != '')
		{
			$record->comment .= '(' . $result->fields['rdmp_comment'] . ')';
		}
		
		
		$species[] = $record;
		$result->MoveNext();	
	
	}
	
	$title = $genus;
	
	// Display...
	echo 
'<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<base href="' . $config['web_root'] . '" />
		<link type="text/css" href="' . $config['web_root'] . 'css/main.css" rel="stylesheet" />
		<script type="text/javascript" src="' . $config['web_root'] . 'js/jquery-1.4.4.min.js"></script>
		<script type="text/javascript" src="' . $config['web_root'] . 'js/jquery.tablesorter.js"></script>

		
		<title>' . $title . ': ' . $config['site_name'] . '</title>
		
		<script>
			
		
			function show_altmetric(doi)
			{
				$.getJSON("http://api.altmetric.com/v1/doi/" + doi + "?callback?",
					function(data){
					   if (data.images) {
					      var html = \'<br/><a href="\' + data.details_url + \'" target="_new"><img src="\' + data.images["small"] + \'"></a>\';
						  $("#details").html($("#details").html() + html);
						}
					}
					
				);	
			}			
			
		
			function show_orcid(doi)
			{
				
				$.getJSON("orcid.php?doi=" + encodeURIComponent(doi),
					function(data){
						var html = "<div style=\"padding:4px;font-size:10px;\">";
					    for (var i in data.results) {
					    	html += \'<img src="images/orcid.png" align="middle" width="20"/>\' + " " + "<a href=\"http://orcid.org/" + data.results[i].orcid + "\" target=\"_new\">" + data.results[i].orcid + "</a> " + data.results[i].name + "<br/>";
					    }
					    html += "</div>";
						$("#details").html($("#details").html() + html);
					}
					
				);	
				//$("#details").html("xxx");
			}
		
		
			function show_doi(doi)
			{
				$("#details").html("");
				$.getJSON("pub.php?doi=" + encodeURIComponent(doi),
					function(data){
						var html = data.html;
						$("#details").html(html);
						show_orcid(doi);
						show_altmetric(doi);
						//show_types();
					}
					
				);	
				//$("#details").html("xxx");
			}
			
			function show_cinii(cinii)
			{
				$("#details").html("");
				$.getJSON("pub.php?cinii=" + cinii,
					function(data){
						var html = data.html;
						$("#details").html(html);
					}
					
				);	
				//$("#details").html("xxx");
			}
			
			function show_biostor(biostor)
			{
				$("#details").html("");
				$.getJSON("pub.php?biostor=" + biostor,
					function(data){
						var html = data.html;
						$("#details").html(html);
					}
					
				);	
				//$("#details").html("xxx");
			}

			function show_url(url)
			{
				//alert(url);
				$("#details").html("");
				$.getJSON("pub.php?url=" + url,
					function(data){
						var html = data.html;
						$("#details").html(html);
					}
					
				);	
				//$("#details").html("xxx");
			}
			
			function show_jstor(jstor)
			{
				$("#details").html("");
				$.getJSON("pub.php?jstor=" + jstor,
					function(data){
						var html = data.html;
						$("#details").html(html);
					}
					
				);	
				//$("#details").html("xxx");
			}
			
			
			function show_bhl(PageID, term)
			{
				$("#details").html("");
				$.getJSON("bhl.php?PageID=" + PageID + "&term=" + term,
					function(data){
						var html = data.html;
						$("#details").html(html);
					}
					
				);	
			}
			
			function show_pdf(pdf, page)
			{
				$("#details").html("");
				$.getJSON("/~rpage/microcitation/www/pdfpage.php?pdf=" + pdf + "&page=" + page,
					function(data){
						var html = "";
						
						if (data.image) {
							html = "<img src=\"" + data.image + "\" width=\"500\" />";
						} else {
							html = "PDF \"" + pdf + "\" and page \"" + page + "\" not found";
						}
						$("#details").html(html);
					}
					
				);	
			}
						
			
			
		</script>
	</head>
	<body>
		<div style="float:right;">
			<form method="get" action="index.php">
			<input type="search"  name="q" id="q" value="" placeholder="Genus"></input>
			<input type="submit" value="Search" ></input>
			</form>
		</div>
		<h1><i>' . $title . '</i></h1>
		<h2>Species in genus <i>' . $title . '</i></h2>';

	echo '<div style="position:relative;">';
	echo '<div style="width:800px;height:400px;overflow:auto;border:1px solid rgb(128,128,128);">';
//	echo '<div style="width:900px;overflow:auto;border:1px solid rgb(128,128,128);">';

	echo '<table id="specieslist" cellspacing="0">';
	
	echo '<thead style="font-size:12px;">';
	echo '<tr>';
	
	echo '<th>id</th>';
	echo '<th>Species</th>';
	echo '<th>Publication</th>';
	echo '<th>ISSN</th>';
	echo '<th>DOI</th>';
	echo '<th>Handle</th>';
	echo '<th>BioStor</th>';
	echo '<th>BHL</th>';
	echo '<th>JSTOR</th>';
	echo '<th>CiNii</th>';
	echo '<th>URL</th>';
	echo '<th>PDF</th>';
	echo '<th>ISBN</th>';
	echo '<th>OCLC</th>';
	echo '<th>Comment</th>';
	
	echo '</tr>';
	echo '</thead>';
	
	
	echo '<tbody style="font-size:12px;">';
	
	$odd = true;
	
	foreach ($species as $sp)
	{
		echo '<tr';
		
		$haslink = false;
		if (isset($sp->doi)) $haslink = true;
		if (isset($sp->biostor)) $haslink = true;
		if (isset($sp->bhl)) $haslink = true;
		if (isset($sp->jstor)) $haslink = true;
		if (isset($sp->doi)) $haslink = true;
		if (isset($sp->cinii)) $haslink = true;
		if (isset($sp->url)) $haslink = true;
		if (isset($sp->pdf)) $haslink = true;
		if (isset($sp->isbn)) $haslink = true;
		if (isset($sp->oclc)) $haslink = true;
		
	
		
		/*
		if ($odd)
		{
			echo ' style="background-color:#eef;"';
			$odd = false;
		}
		else
		{
			echo ' style="background-color:#fff;"';
			$odd = true;
		}
		*/
		
		if (isset($sp->comment))
		{
			echo ' style="background-color:red;color:white;"';
		}
		else
		{
			if ($haslink)
			{
				if (isset($sp->doi))
				{
					echo ' style="background-color:#00FF80;"';
				}
				else
				{
					echo ' style="background-color:#FFFF66;"';
				}
			
			}
			else
			{
				echo ' style="background-color:#fff;"';
			}
		}		
		
		
		echo '>';
		echo '<td>' . '<a href="http://www.indexfungorum.org/Names/NamesRecord.asp?RecordID=' . $sp->id . '" target="_new">' . $sp->id . '</td>';
		
		
		echo '<td>' . $sp->html . '</td>';
		//echo '<td>' . str_replace(' ', '&nbsp;', $sp->publication) . '</td>';
		echo '<td style="word-break: break-all;width:300px;">' . $sp->publication . '</td>';
		
		echo '<td>';
		if (isset($sp->issn))
		{
			echo str_replace('-', '', $sp->issn);
		}		
		echo '</td>';
		
		
		echo '<td>';
		if (isset($sp->doi))
		{
			echo '<span onclick="show_doi(\'' . $sp->doi . '\');">';
			echo $sp->doi;
			echo '</span>';
		}		
		echo '</td>';

		echo '<td>';
		if (isset($sp->handle))
		{
			echo $sp->handle;
		}		
		echo '</td>';
		

		echo '<td>';
		if (isset($sp->biostor))
		{
			echo '<span onclick="show_biostor(\'' . $sp->biostor . '\');">';
			echo $sp->biostor;
			echo '</span>';
		}		
		echo '</td>';

		echo '<td>';
		if (isset($sp->bhl))
		{
			echo '<span onclick="show_bhl(\'' . $sp->bhl . '\',\'' . $sp->name . '\');">';		
			echo $sp->bhl;
			echo '</span>';
		}		
		echo '</td>';
		
		echo '<td>';
		if (isset($sp->jstor))
		{
			echo '<span onclick="show_jstor(\'' . $sp->jstor . '\');">';		
			echo $sp->jstor;
			echo '</span>';
		}		
		echo '</td>';
		

		echo '<td>';
		if (isset($sp->cinii))
		{
			echo '<span onclick="show_cinii(\'' . $sp->cinii . '\');">';
			echo $sp->cinii;
			echo '</span>';
		}		
		echo '</td>';

		echo '<td>';
		if (isset($sp->url))
		{
			echo '<span onclick="show_url(\'' . urlencode($sp->url) . '\');">';
			echo substr($sp->url, 7, 20) . '...';
			echo '</span>';

			//echo '<a href="' . $sp->url . '" title="' . $sp->url . '">';
			//echo substr($sp->url, 7, 20) . '...';
			//echo '</a>';
		}		
		echo '</td>';

		echo '<td>';
		if (isset($sp->pdf))
		{
			//echo '<span onclick="show_url(\'' . urlencode($sp->pdf) . '\');">';
			
			echo '<span onclick="show_pdf(\'' . $sp->pdf . '\',\'' . $sp->pages . '\');">';
			
			//echo '<a href="' . $sp->pdf . '" title="' . $sp->pdf . '">';
			echo substr($sp->pdf, 7, 20) . '...';
			//echo '</a>';
			echo '</span>';
		}		
		echo '</td>';

		echo '<td>';
		if (isset($sp->isbn))
		{
			//echo '<a href="' . $sp->pdf . '" title="' . $sp->pdf . '">';
			echo $sp->isbn;
			//echo '</a>';
		}		
		echo '</td>';

		echo '<td>';
		if (isset($sp->oclc))
		{
			//echo '<a href="' . $sp->pdf . '" title="' . $sp->pdf . '">';
			echo $sp->oclc;
			//echo '</a>';
		}		
		echo '</td>';

		echo '<td>';
		if (isset($sp->comment))
		{
			echo $sp->comment;
		}		
		echo '</td>';
		
		
		echo '</tr>';
		echo "\n";
	}
	echo '</tbody>';
	echo '</table>';
	echo '</div>';
	
	echo '<div style="font-size:12px;position:absolute;top:0px;left:800px;width:auto;padding-left:10px;">';
	echo '<p style="padding:0px;margin:0px;" id="details"></p>';
	echo '</div>';
	
	echo '</div>';
	
	echo "<script>$(function(){
  $('#specieslist').tablesorter(); 
});</script>";
	
	echo
'	</body>
</html>';

}




//--------------------------------------------------------------------------------------------------
function main()
{
	global $config;
	global $debug;
	
	$query = '';
		
	// If no query parameters 
	if (count($_GET) == 0)
	{
		default_display();
		exit(0);
	}

	$major_group = '';
	$family = '';
	$genus = '';
	
	if (isset($_GET['q']))
	{
		$query = $_GET['q'];
		display_search($query);
	}
	

	if (isset($_GET['p']))
	{	
		$publication = $_GET['p'];
		display_search($publication, 'publication');
	}

}


main();
		
?>