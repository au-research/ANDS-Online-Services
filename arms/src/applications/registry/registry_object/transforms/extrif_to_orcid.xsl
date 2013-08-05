<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet xmlns:ro="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:extRif="http://ands.org.au/standards/rif-cs/extendedRegistryObjects" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" exclude-result-prefixes="ro extRif">
<xsl:param name="base_url"/>
<!-- http://support.orcid.org/knowledgebase/articles/118795-->
<xsl:param name="work-type" select="'Advertisement'"/> 
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

    <xsl:template name="getCreatedDate">
        <xsl:choose>
            <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:date[@type='created']">
                <xsl:value-of select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:date[@type='created']"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:date[@type='issued']">
                <xsl:value-of select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:date[@type='issued']"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:dates[@type='created']">
                <xsl:value-of select="substring(ro:collection/ro:dates[@type='created']/ro:date,1,4)"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:dates[@type='dc.created']">
                <xsl:value-of select="substring(ro:collection/ro:dates[@type='dc.created']/ro:date,1,4)"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:location/@dateFrom">
                <xsl:value-of select="substring(ro:collection/ro:location/@dateFrom,1,4)"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:coverage/ro:temporal/ro:date[@type= 'dateFrom']">
                <xsl:value-of select="substring(ro:collection/ro:coverage/ro:temporal/ro:date[@type= 'dateFrom']/text() ,1,4)"/>
            </xsl:when>        
            <xsl:when test="ro:collection/@dateModified">
                <xsl:value-of select="substring(ro:collection/@dateModified,1,4)"/>
            </xsl:when>
        </xsl:choose>
    </xsl:template>

    <xsl:template name="getSourceURL">
        <xsl:choose>
            <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='doi']">
                <xsl:value-of select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='doi']"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='handle']">
                <xsl:value-of select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='handle']"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='uri']">
                <xsl:value-of select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='uri']"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='purl']">
                <xsl:value-of select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='purl']"/>
            </xsl:when>         
            <xsl:when test="ro:collection/ro:identifier[@type='doi']">
                <xsl:value-of select="ro:collection/ro:identifier[@type='doi']"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:identifier[@type='handle']">
                <xsl:value-of select="ro:collection/ro:identifier[@type='handle']"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:identifier[@type='uri']">
                <xsl:value-of select="ro:collection/ro:identifier[@type='uri']"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:identifier[@type='purl']">
                <xsl:value-of select="ro:collection/ro:identifier[@type='purl']"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:url">
                <xsl:value-of select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:url"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:location/ro:address/ro:electronic[@type='url']">
                <xsl:value-of select="ro:collection/ro:location/ro:address/ro:electronic[@type='url']"/>
            </xsl:when>
        </xsl:choose>
    </xsl:template>
   
     <xsl:template match="ro:contributor">
        <Author seq="{@seq}">
            <AuthorName>
                <xsl:variable name="title">
                    <xsl:apply-templates select="ro:namePart[@type = 'family']"/>
                    <xsl:apply-templates select="ro:namePart[@type = 'given']"/>
                    <xsl:apply-templates select="ro:namePart[@type = 'title']"/>
                    <xsl:apply-templates select="ro:namePart[@type != 'title' and @type != 'family' and @type = 'title' and @type = '' and not(@type)]"/>
                </xsl:variable>
                <xsl:value-of select="substring($title,1,string-length($title)-2)"/>
            </AuthorName>
        </Author>
    </xsl:template>

</xsl:stylesheet>

