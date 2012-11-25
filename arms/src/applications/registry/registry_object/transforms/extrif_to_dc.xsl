<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet xmlns:ro="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:extRif="http://ands.org.au/standards/rif-cs/extendedRegistryObjects" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" exclude-result-prefixes="ro extRif">

    <xsl:output indent="yes" />
    <xsl:strip-space elements="*"/>

    <xsl:template match="/">
        <xsl:apply-templates/>
    </xsl:template>   
    


    <xsl:template match="ro:registryObject">
        <dc xmlns="http://purl.org/dc/elements/1.1/">
	        <xsl:apply-templates select="extRif:extendedMetadata/extRif:displayTitle"/>
	        <xsl:choose>
	        	<xsl:when test="extRif:description[@type='full']">
	        		<xsl:apply-templates select="extRif:description[@type='full']"/>
	        	</xsl:when>
	        	<xsl:when test="extRif:description[@type='brief']">
	        		<xsl:apply-templates select="extRif:description[@type='brief']"/>
	        	</xsl:when>
	        </xsl:choose>          	 
            <xsl:apply-templates select="ro:collection | ro:party | ro:activity | ro:service"/>
        </dc>
    </xsl:template> 

  
    <xsl:template match="extRif:displayTitle">
        <title xmlns="http://purl.org/dc/elements/1.1/">
            <xsl:value-of select="."/>
        </title>   
    </xsl:template>
   
    <xsl:template match="ro:collection | ro:party | ro:activity | ro:service">
        <xsl:apply-templates select="ro:identifier"/>     		    
    </xsl:template>

    <xsl:template match="extRif:description">
        <description xmlns="http://purl.org/dc/elements/1.1/">
            <xsl:value-of select="."/>
        </description>       
    </xsl:template>
    
    <xsl:template match="ro:identifier">
        <identifier xmlns="http://purl.org/dc/elements/1.1/">
            <xsl:value-of select="."/>
        </identifier>
    </xsl:template>
 
   
</xsl:stylesheet>

