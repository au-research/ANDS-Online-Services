<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet xmlns:ro="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:extRif="http://ands.org.au/standards/rif-cs/extendedRegistryObjects" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" exclude-result-prefixes="ro extRif">
<xsl:param name="base_url"/>
    <xsl:output indent="yes" omit-xml-declaration="yes"/>
    <xsl:strip-space elements="*"/>

    <xsl:template match="/">
        <xsl:apply-templates/>
    </xsl:template>   
    
    <xsl:template match="ro:registryObject">
        <orcid-work>
            <work-title>
                <xsl:apply-templates select="extRif:extendedMetadata/extRif:displayTitle"/>
            </work-title>
            <work-citation>
                <work-citation-type>bibtext</work-citation-type>
                <citation>Citation Info</citation>
            </work-citation>
            <work-type>Book</work-type>
            <publication-date></publication-date>
        </orcid-work>
    </xsl:template> 

    <xsl:template match="extRif:displayTitle">
        <title> <xsl:value-of select="."/></title>
    </xsl:template>
   
</xsl:stylesheet>

