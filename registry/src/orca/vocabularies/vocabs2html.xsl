<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:voc="http://ands.org.au/schema/vocabulary" version="1.0" exclude-result-prefixes="voc">
	<xsl:output method="xml" 
		media-type="text/html" 
		doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
		doctype-system="DTD/xhtml1-strict.dtd"
		indent="yes"
		encoding="UTF-8"/>
	
	<xsl:template match="voc:vocabularies">
		<html xmlns="http://www.w3.org/1999/xhtml">
			<body xmlns="http://www.w3.org/1999/xhtml">
				<h2 xmlns="http://www.w3.org/1999/xhtml">Vocabularies for Registry Schema</h2>
				<xsl:apply-templates/>
			</body>
		</html>
	</xsl:template>

	<xsl:template match="voc:vocabulary">
		<xsl:if
			test="starts-with(voc:source, 'http://services.ands.org.au/documentation/rifcs/schema/vocabularies.html')">
			<h3 id="{voc:identifier}" xmlns="http://www.w3.org/1999/xhtml">
				<xsl:apply-templates select="voc:name[@type='primary']"/>
			</h3>
			<ul xmlns="http://www.w3.org/1999/xhtml">
				<xsl:apply-templates select="voc:term"/>
			</ul>
		</xsl:if>
	</xsl:template>

	<xsl:template match="voc:term">
		<li xmlns="http://www.w3.org/1999/xhtml">
			<strong xmlns="http://www.w3.org/1999/xhtml">
				<xsl:apply-templates select="voc:name"/>
			</strong>
			<xsl:text> : </xsl:text>
			<xsl:apply-templates select="voc:description"/>
		</li>
	</xsl:template>

</xsl:stylesheet>
