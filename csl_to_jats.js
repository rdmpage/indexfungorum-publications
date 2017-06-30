function csl_to_jats(doc) {
	var xml = '';
	
	var xw = new XMLWriter('UTF-8');
	xw.formatting = 'indented'; //add indentation and newlines
	xw.indentChar = ' '; //indent with spaces
	xw.indentation = 2; //add 2 spaces per level

	xw.writeStartDocument();
	xw.writeDocType('PUBLIC "SYSTEM" "jats-archiving-dtd-1.0/JATS-archivearticle1.dtd"');

	xw.writeStartElement('article');
	xw.writeAttributeString('xmlns:xlink', 'http://www.w3.org/1999/xlink');
	xw.writeStartElement('front');
	if (doc.message['container-title']) {
	  xw.writeStartElement('journal-meta');
	  xw.writeStartElement('journal-title-group');

	  if (Array.isArray(doc.message['container-title'])) {
		xw.writeElementString('journal-title', doc.message['container-title'][0]);
	  } else {
		xw.writeElementString('journal-title', doc.message['container-title']);
	  }
	  xw.writeEndElement();

	  if (doc.message.ISSN) {
		for (var i in doc.message.ISSN) {
		  xw.writeElementString('issn', doc.message.ISSN[i]);
		}
	  }

	  xw.writeEndElement();
	}

	xw.writeStartElement('article-meta');

	if (doc.message.DOI) {
	  xw.writeStartElement('article-id');
	  xw.writeAttributeString('pub-id-type', 'doi');
	  xw.writeString(doc.message.DOI);
	  xw.writeEndElement();
	}

	if (doc.message.PMID) {
	  xw.writeStartElement('article-id');
	  xw.writeAttributeString('pub-id-type', 'pmid');
	  xw.writeString(String(doc.message.PMID));
	  xw.writeEndElement();
	}

	if (doc.message.PMC) {
	  xw.writeStartElement('article-id');
	  xw.writeAttributeString('pub-id-type', 'pmc');
	  xw.writeString(doc.message.PMC);
	  xw.writeEndElement();
	}

	// title		
	xw.writeStartElement('title-group');
	if (doc.message.title) {

	  if (Array.isArray(doc.message.title)) {
		xw.writeElementString('article-title', doc.message.title[0]);
	  } else {
		xw.writeElementString('article-title', doc.message.title);
	  }

	}
	xw.writeEndElement();

	// authors
	if (doc.message.author) {
	  xw.writeStartElement('contrib-group');
	  for (var i in doc.message.author) {
		xw.writeStartElement('contrib');
		xw.writeAttributeString('contrib-type', 'author');

		xw.writeStartElement('name');
		if (doc.message.author[i].literal) {
		  xw.writeElementString('string-name', doc.message.author[i].literal);
		}
		if (doc.message.author[i].family) {
		  xw.writeElementString('surname', doc.message.author[i].family);
		}
		if (doc.message.author[i].given) {
		  xw.writeElementString('given-names', doc.message.author[i].given);
		}
		xw.writeEndElement();

		xw.writeEndElement();
	  }
	  xw.writeEndElement();
	}

	// date
	if (doc.message.issued) {
	  var dateparts = doc.message.issued['date-parts'][0];
	  xw.writeStartElement('pub-date');

	  if (dateparts.length >= 1) {
		xw.writeElementString('year', String(dateparts[0]));
	  }
	  if (dateparts.length >= 2) {
		xw.writeElementString('month', String(dateparts[1]));
	  }
	  if (dateparts.length == 3) {
		xw.writeElementString('day', String(dateparts[2]));
	  }
	  xw.writeEndElement();
	}

	if (doc.message.volume) {
	  xw.writeElementString('volume', doc.message.volume);
	}
	if (doc.message.issue) {
	  xw.writeElementString('issue', doc.message.issue);
	}

	if (doc.message.page) {
	  var delimiter = doc.message.page.indexOf('-');
	  if (delimiter != -1) {
		spage = doc.message.page.substring(0, delimiter);
		xw.writeElementString('fpage', spage);
		epage = doc.message.page.substring(delimiter + 1)
		xw.writeElementString('lpage', epage);
	  } else {
		xw.writeElementString('fpage', doc.message.page);
	  }
	}


	// abstract
	if (doc.message.abstract) {
	  xw.writeStartElement('abstract');
	  xw.writeElementString('p', doc.message.abstract);
	  xw.writeEndElement();
	}

	xw.writeEndElement(); // article-meta

	xw.writeEndElement(); // front

	xw.writeStartElement('body');

	// OCR text
	/*
	if (count($reference->text) > 0)
	{
		foreach ($reference->text as $text)
		{
			$preformat = $body->appendChild($doc->createElement('preformat'));
			$preformat->appendChild($doc->createTextNode($text));
		}
	}
	*/

	
	// OCR scans 
	if (doc.message.bhl_pages) {
		xw.writeStartElement('body');
			xw.writeStartElement('supplementary-material');
			xw.writeAttributeString('content-type', 'scanned-pages');
	
			for (var i in doc.message.bhl_pages) {
				xw.writeStartElement('graphic');
				xw.writeAttributeString('xlink:href', 'http://biostor.org/page/image/' + doc.message.bhl_pages[i] + '-small.jpg');
				xw.writeAttributeString('xlink:role', doc.message.bhl_pages[i]);
				xw.writeAttributeString('xlink:title', i);
				xw.writeEndElement();
			}
			xw.writeEndElement();
		xw.writeEndElement();
	}

	xw.writeEndElement(); // body

	xw.writeStartElement('back');

	// References in CrossRef CSL
	if (doc.message.reference) {
	  xw.writeStartElement('ref-list');

	  for (var i in doc.message.reference) {
		xw.writeStartElement('ref');
		xw.writeAttributeString('id', doc.message.reference[i].key);

		xw.writeStartElement('mixed-citation');

		if (doc.message.reference[i].unstructured) {
		  // Note use of CDATA to avoid issues with ampersands
		  xw.writeString('<![CDATA[' + doc.message.reference[i].unstructured + ']]>');
		} else {
		  // some structure
		  
		  // {"key":"7295_B26","first-page":"63","article-title":"On a new species of Rafflesia from Manila.","volume":"4","author":"Teschemacher","year":"1842","journal-title":"Boston Journal of Natural History"}
		  
		  var keys = ['author', 'year', 'article-title', 'journal-title', 'volume', 'issue', 'first-page'];
		  
		  for (var j in keys) {
		    if (doc.message.reference[i][keys[j]]) {
				switch (keys[j]) {
					// keys that map directly to JATS
					case 'article-title':
					case 'volume':
					case 'issue':
					case 'year':
						xw.writeElementString(keys[j], doc.message.reference[i][keys[j]]);
						break;
					
					// keys that we need to map 
					case 'author':
						xw.writeStartElement('person-group');
							xw.writeElementString('string-name', doc.message.reference[i][keys[j]]);
						xw.writeEndElement();
						break;
					
					case 'journal-title':
						xw.writeElementString('source', doc.message.reference[i][keys[j]]);
						break;

					case 'first-page':
						xw.writeElementString('fpage', doc.message.reference[i][keys[j]]);
						break;
					
					default:
						break;
				}
			}
		  }
		  
		  
		
		}

		if (doc.message.reference[i].DOI) {
		  xw.writeStartElement('ext-link');
		  xw.writeAttributeString('ext-link-type', "uri");
		  xw.writeAttributeString('xlink:href', "https://doi.org/" + doc.message.reference[i].DOI);
		  xw.writeAttributeString('xlink:type', "simple");
		  xw.writeString("doi:" + doc.message.reference[i].DOI);
		  xw.writeEndElement();
		}

		xw.writeEndElement(); // miexed-citation

		xw.writeEndElement(); // ref
	  }

	  xw.writeEndElement(); // ref-list
	}

	xw.writeEndElement(); // back

	xw.writeEndElement(); // article
	xw.writeEndDocument();

	var xml = xw.flush(); //generate the xml string
	xw.close(); //clean the writer
	xw = undefined; //don't let visitors use it, it's closed
	
	return xml;
}
