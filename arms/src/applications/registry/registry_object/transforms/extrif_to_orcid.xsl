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
                <subtitle>Subtitle</subtitle>
            </work-title>
            <short-description>My Abstract</short-description>
            <work-citation>
              <work-citation-type>formatted-apa</work-citation-type>
              <citation>My correctly formatted citation</citation>
            </work-citation>
            <work-type>journal-article</work-type>
            <publication-date>
              <year>2010</year>
              <month>11</month>
            </publication-date>
            <work-external-identifiers>
              <work-external-identifier>
                <work-external-identifier-type>other-id</work-external-identifier-type>
                <work-external-identifier-id>1234</work-external-identifier-id>
              </work-external-identifier>
            </work-external-identifiers>
            <url>www.orcid.org</url>
            <work-contributors>
              <contributor>
                <credit-name>LastName, FirstName</credit-name>
                <contributor-attributes>
                  <contributor-sequence>first</contributor-sequence>
                  <contributor-role>author</contributor-role>
                </contributor-attributes>
              </contributor>
            </work-contributors>
        </orcid-work>
    </xsl:template> 

    <xsl:template match="extRif:displayTitle">
        <title> <xsl:value-of select="."/></title>
    </xsl:template>
   
</xsl:stylesheet>

