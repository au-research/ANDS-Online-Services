<?xml version="1.0" encoding="UTF-8"?>

<!-- Date Modified: $Date: 2009-08-18 12:13:42 +1000 (Tue, 18 Aug 2009) $ -->
<!-- Version: $Revision: 81 $ -->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:oai="http://www.openarchives.org/OAI/2.0/"  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:rif="http://apsr.edu.au/standards/iso2146/registryObjects" xmlns="http://apsr.edu.au/standards/iso2146/registryObjects" version="2.0">
    <xsl:output indent="yes" method="xml"/>
    <xsl:strip-space elements="*"/>

    <xsl:template match="oai:metadata|oai:ListRecords|oai:record">
    	<xsl:apply-templates/>
    </xsl:template>

	<xsl:template match="oai:OAI-PMH">
		<xsl:element name="registryObjects">
			<xsl:attribute name="xsi:schemaLocation">
				<xsl:text>http://apsr.edu.au/standards/iso2146/registryObjects http://pilot.apsr.edu.au/public/rif/schema/registryObjects.xsd</xsl:text>
			</xsl:attribute>
			<xsl:apply-templates/>
		</xsl:element>
	</xsl:template>

	<xsl:template match="rif:registryObjects">
		<xsl:apply-templates/>
	</xsl:template>
	
	<xsl:template match="oai:*">
		<xsl:apply-templates/>
	</xsl:template>
	
	<xsl:template match="rif:*">
		<xsl:element name="{local-name()}">
			<xsl:apply-templates select="@*[not(contains(name(),'xsi'))]"/>
			<xsl:apply-templates/>
		</xsl:element>
	</xsl:template>

	<xsl:template match="@*">
		<xsl:copy-of select="."/>
	</xsl:template>
	
	<xsl:template match="text()[parent::rif:*]">
		<xsl:value-of select="."/>
	</xsl:template>
	
	<xsl:template match="node()|text()"/>
	
</xsl:stylesheet>