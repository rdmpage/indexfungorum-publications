<?xml version="1.0"?>
<xsl:stylesheet xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:output encoding="utf-8" indent="yes" method="html" version="1.0"/>
	<xsl:template match="/">
	<!--
		<html>
			<head>
				<meta charset="utf-8"/>
                <style type="text/css">	
body {
	margin: 10;
	padding: 10;	
	font-family: Verdana;
	font-size: 11px!important;
	
	line-height:1.5em;
}
h1 {
	line-height:1.5em;
	font-weight:normal;
}
h2 {
	line-height:1.5em;
	font-weight:normal;
	font-family: Helvetica,sans-serif;
}
p{
	margin:0px;
	padding:0px 0px;
	margin-top:10px;
}
tbody{
	font-family: Verdana;
	font-size: 11px!important;
}
                </style>
				
			</head>
			<body> -->
				<div>
					<p style="font-size:80%">
						<xsl:value-of select="//journal-meta/journal-title-group/journal-title"/>
						<xsl:text> </xsl:text>
						<xsl:if test="//article-meta/pub-date/day">
							<xsl:value-of select="//article-meta/pub-date/day"/>
							<xsl:text> </xsl:text>
						</xsl:if>
						<xsl:if test="//article-meta/pub-date/month">
							<xsl:choose>
								<xsl:when test="//article-meta/pub-date/month = 1">
									<xsl:text>January</xsl:text>
								</xsl:when>
								<xsl:when test="//article-meta/pub-date/month = 2">
									<xsl:text>February</xsl:text>
								</xsl:when>
								<xsl:when test="//article-meta/pub-date/month = 3">
									<xsl:text>March</xsl:text>
								</xsl:when>
								<xsl:when test="//article-meta/pub-date/month = 4">
									<xsl:text>April</xsl:text>
								</xsl:when>
								<xsl:when test="//article-meta/pub-date/month = 5">
									<xsl:text>May</xsl:text>
								</xsl:when>
								<xsl:when test="//article-meta/pub-date/month = 6">
									<xsl:text>June</xsl:text>
								</xsl:when>
								<xsl:when test="//article-meta/pub-date/month = 7">
									<xsl:text>July</xsl:text>
								</xsl:when>
								<xsl:when test="//article-meta/pub-date/month = 8">
									<xsl:text>August</xsl:text>
								</xsl:when>
								<xsl:when test="//article-meta/pub-date/month = 9">
									<xsl:text>September</xsl:text>
								</xsl:when>
								<xsl:when test="//article-meta/pub-date/month = 10">
									<xsl:text>October</xsl:text>
								</xsl:when>
								<xsl:when test="//article-meta/pub-date/month = 11">
									<xsl:text>November</xsl:text>
								</xsl:when>
								<xsl:when test="//article-meta/pub-date/month = 12">
									<xsl:text>December</xsl:text>
								</xsl:when>
							</xsl:choose>
							<xsl:text> </xsl:text>
						</xsl:if>
						<xsl:value-of select="//article-meta/pub-date/year"/>
						<xsl:text> </xsl:text>
						<xsl:value-of select="//article-meta/volume"/>
						<xsl:if test="//article-meta/issue">
							<xsl:text>(</xsl:text>
							<xsl:value-of select="//article-meta/issue"/>
							<xsl:text>)</xsl:text>
						</xsl:if>
						<xsl:text>: </xsl:text>
						<xsl:value-of select="//article-meta/fpage"/>
						<xsl:text>-</xsl:text>
						<xsl:value-of select="//article-meta/lpage"/>
					</p>
					<h1>
						<xsl:value-of select="//article-title"/>
					</h1>
						<xsl:apply-templates select="//contrib-group"/>
					<!--
					<div>
						<xsl:apply-templates select="//contrib-group/contrib[@contrib-type='author']/name"/>
					</div>
					-->
					
					<ul>
						<xsl:apply-templates select="//article-id"/>
						<xsl:apply-templates select="//self-uri[@content-type='lsid']"/>
					</ul>
					
					
					<xsl:if test="//abstract">
						<h2>Abstract</h2>
						<xsl:value-of select="//abstract"/>
					</xsl:if>
					
					<!-- this is BioStor specific -->
					
					<xsl:if test="//supplementary-material/graphic">
					<!--
					<h2>Full text</h2>
					<p>
						<xsl:text>
							Full text is available as a scanned copy of the original print version. 
							Get a printable copy (PDF file) of the 
						</xsl:text>
						<a>
							<xsl:attribute name="href">
								<xsl:text>https://archive.org/download/biostor-</xsl:text>
								<xsl:value-of select="//article-id[@pub-id-type=&quot;biostor&quot;]"/>
								<xsl:text>/biostor-</xsl:text>
								<xsl:value-of select="//article-id[@pub-id-type=&quot;biostor&quot;]"/>
								<xsl:text>.pdf</xsl:text>
							</xsl:attribute>
							<xsl:text>complete article</xsl:text>							
						</a>
						<xsl:text>.</xsl:text>						
						
						<xsl:if test="//back">
							<xsl:text>Links are also available for </xsl:text>
							<a href="#reference-sec">Selected References</a>
							<xsl:text>.</xsl:text>
						</xsl:if>
					</p>
					-->
					<div>
						<xsl:apply-templates select="//supplementary-material/graphic"/>
					</div>
					<!--
					<div style="clear:both;"/>
					<xsl:if test="//floats-group">
						<h2>Images in this article</h2>
						<p>Figures and tables extracted from ABBYY OCR XML.</p>
						<div>
							<xsl:apply-templates select="//fig"/>
						</div>
						<div style="clear:both;"/>
					</xsl:if>
					
					-->
					</xsl:if>
					
					<xsl:apply-templates select="//back"/>
					
			</div>
			<!-- </body>
		</html> -->
	</xsl:template>
	
<xsl:template match="article-id">
	<xsl:choose>
		<xsl:when test="@pub-id-type='doi'">
			<li>
				<xsl:text>DOI:</xsl:text>
				<xsl:value-of select="." />
			</li>
		</xsl:when>
		<xsl:when test="@pub-id-type='pmid'">
			<li>
				<xsl:text>PMID:</xsl:text>
				<xsl:value-of select="." />
			</li>
		</xsl:when>
		<xsl:when test="@pub-id-type='pmc'">
			<li>
				<xsl:text>PMC</xsl:text>
				<xsl:value-of select="." />
			</li>
		</xsl:when>
		
		<xsl:otherwise />
	</xsl:choose>
</xsl:template>


<!-- ZooBank LSID for article -->
<xsl:template match="//self-uri[@content-type='lsid']">
<li><xsl:value-of select="." /></li>
</xsl:template>

<!-- authors -->
<xsl:template match="//contrib-group">
	<h2>
		<xsl:apply-templates select="contrib"/>
	</h2>
</xsl:template>

    <xsl:template match="contrib">
        <xsl:if test="@contrib-type='author'">
            <xsl:if test="position() != 1"><xsl:text>, </xsl:text></xsl:if>
				<xsl:apply-templates select="name"/>
        </xsl:if>
        
    </xsl:template>
	
	<!-- person's name -->
	<xsl:template match="name">
		<xsl:if test="string-name">
			<xsl:value-of select="string-name" />
		</xsl:if>
		<xsl:if test="given-names">
			<xsl:value-of select="given-names" />
			<xsl:text> </xsl:text>
		</xsl:if>
		<xsl:if test="surname">
			<xsl:value-of select="surname" />
		</xsl:if>
	</xsl:template>
	
	<!-- scanned page -->
	<xsl:template match="//supplementary-material/graphic">
		<div style="float:left;padding:20px;">
			<!-- link to HTML -->
			<a>
				<xsl:attribute name="href">
					<xsl:text>html/</xsl:text>
					<xsl:value-of select="@xlink:role"/>
					<xsl:text>.html</xsl:text>
				</xsl:attribute>
				<img style="border:1px solid rgb(192,192,192);" height="140">
					<xsl:attribute name="src">
						<!--
					<xsl:text>Med_Hist_1985_Jan_29(1)_1-32/</xsl:text>
					<xsl:value-of select="substring-before(@xlink:href, '.tif')" /> 
					<xsl:text>.gif</xsl:text>
				-->
						<xsl:value-of select="@xlink:href"/>
						<xsl:text>,80,100</xsl:text>
					</xsl:attribute>
				</img>
			</a>
			<!-- Page name -->
			<div style="text-align:center;width:100px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
				<xsl:value-of select="@xlink:title"/>
			</div>
		</div>
	</xsl:template>
	<xsl:template match="fig">
		<xsl:apply-templates select="graphic"/>
	</xsl:template>
	<!-- figure or table -->
	<xsl:template match="graphic">
		<div style="float:left;padding:20px;">
			<a>
				<xsl:attribute name="href">
					<xsl:value-of select="@xlink:href"/>
				</xsl:attribute>
				<img height="100">
					<xsl:attribute name="src">
						<xsl:value-of select="@xlink:href"/>
					</xsl:attribute>
				</img>
			</a>
		</div>
	</xsl:template>
	<xsl:template match="back">
		<xsl:apply-templates select="ref-list"/>
	</xsl:template>
	
	<xsl:template match="ref-list">
		<h2 id="reference-sec">Selected references</h2>	
		<ol>
			<xsl:apply-templates select="ref"/>
		</ol>
	</xsl:template>
	
	<xsl:template match="ref">
		<li>
			<xsl:apply-templates select="mixed-citation"/> 
		</li>
	</xsl:template>
	
	<xsl:template match="mixed-citation">
		<!-- may be just unstructured text or full of tags -->
		
		<xsl:choose>
			<xsl:when test="article-title and volume">
				<xsl:value-of select="person-group/string-name" />
				<xsl:text> (</xsl:text>
				<xsl:value-of select="year" />
				<xsl:text>) </xsl:text>
				<b><xsl:value-of select="article-title" /></b>
				<xsl:text> </xsl:text>							
				<xsl:value-of select="source" />
				<xsl:text> </xsl:text>
				<xsl:value-of select="volume" />
				<xsl:text>:</xsl:text>
				<xsl:value-of select="fpage" />
				<!-- <xsl:text>-</xsl:text>
				<xsl:value-of select="lpage" /> -->
			</xsl:when>
			
			<xsl:when test="source and publisher-name and publisher-loc">
				<b><xsl:value-of select="source" /></b>
				<xsl:text>, </xsl:text>
				<xsl:value-of select="publisher-name" />
				<xsl:text>, </xsl:text>
				<xsl:value-of select="publisher-loc" />
			</xsl:when>

			<xsl:when test="chapter-title and source and size">
				<b><xsl:value-of select="chapter-title" /></b>
				<xsl:value-of select="source" />
				<xsl:text>, </xsl:text>
				<xsl:value-of select="size" />
			</xsl:when>
			
			<xsl:otherwise>
				<xsl:value-of select="."/>
			</xsl:otherwise>

			
		</xsl:choose>
		
		

		




		<xsl:apply-templates select="ext-link"/>
	</xsl:template>
	
	
	
	
	<xsl:template match="ext-link">
		<xsl:variable name="uri" select="@xlink:href"/>
		<xsl:if test="contains($uri, 'doi.org/')">
			<xsl:text> DOI: </xsl:text>
			<a>
				<xsl:attribute name="href">
					<xsl:value-of select="$uri"/>
				</xsl:attribute>
				<xsl:value-of select="substring-after($uri, 'doi.org/')"/>
			</a>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>
