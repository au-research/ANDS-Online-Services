<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet xmlns:ro="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:extRif="http://ands.org.au/standards/rif-cs/extendedRegistryObjects" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" exclude-result-prefixes="ro extRif">
<xsl:param name="base_url"/>
<!-- http://support.orcid.org/knowledgebase/articles/118795-->
<xsl:param name="work-type" select="'Book'"/> 
    <xsl:output indent="yes" omit-xml-declaration="yes"/>
    <xsl:strip-space elements="*"/>

    <xsl:template match="/">
        <xsl:apply-templates/>
    </xsl:template>   
    
    <xsl:template match="ro:registryObject">
        <orcid-work>
            <work-title>
                <xsl:apply-templates select="extRif:extendedMetadata/extRif:displayTitle"/>
                <xsl:if test="ro:collection/ro:name[@type='alternative']">
                    <xsl:apply-templates select="ro:collection/ro:name[@type='alternative']"/>
                </xsl:if>
            </work-title>
            <xsl:if test="extRif:extendedMetadata/extRif:dci_description">
                <short-description>
                    <xsl:apply-templates select="extRif:extendedMetadata/extRif:dci_description"/>
                </short-description>
            </xsl:if>
            <xsl:if test="ro:collection/ro:citationInfo/ro:fullCitation">
                <work-citation>
                    <work-citation-type>
                        <xsl:variable name="style" select="ro:collection/ro:citationInfo/ro:fullCitation/@style"/>
                        <xsl:choose>
                            <xsl:when test="$style = 'Harvard'">
                                <xsl:text>formatted-harvard</xsl:text>
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:text>formatted-unspecified</xsl:text>
                            </xsl:otherwise>
                        </xsl:choose>
                    </work-citation-type>
                    <citation><xsl:value-of select="ro:collection/ro:citationInfo/ro:fullCitation/text()"/></citation>
                </work-citation>
            </xsl:if>
            <xsl:if test="$work-type != ''">
                <work-type><xsl:value-of select="$work-type"/></work-type>
            </xsl:if>
            <xsl:variable name="createdDate">
                <xsl:call-template name="getCreatedDate"/>
            </xsl:variable>
            <xsl:if test="$createdDate != ''">
                <publication-date>
                    <year>
                        <xsl:value-of select="$createdDate"/>
                    </year>
                </publication-date>
            </xsl:if>
            <xsl:if test="//ro:identifier">
                <work-external-identifiers>
                    <xsl:apply-templates select="//ro:identifier"/>
                </work-external-identifiers>
            </xsl:if>
            <xsl:variable name="sourceUrl">
                <xsl:call-template name="getSourceURL"/>
            </xsl:variable>
            <xsl:if test="sourceUrl != ''">
                <url><xsl:value-of select="sourceUrl"/></url>
            </xsl:if>
            <xsl:if test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:contributor">
                <work-contributors>
                    <xsl:apply-templates select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:contributor"/>
                </work-contributors>
            </xsl:if>          
        </orcid-work>
    </xsl:template> 



    <xsl:template match="extRif:displayTitle">
        <title> <xsl:value-of select="."/></title>
    </xsl:template>

    <xsl:template match="ro:name[@type='alternative']">
        <subtitle> <xsl:value-of select="."/></subtitle>
    </xsl:template>

    <xsl:template match="ro:identifier">
        <work-external-identifier>
          <work-external-identifier-type><xsl:value-of select="@type"/></work-external-identifier-type>
          <work-external-identifier-id><xsl:value-of select="."/></work-external-identifier-id>
        </work-external-identifier>
    </xsl:template>

    <xsl:template match="ro:namePart">
        <xsl:value-of select="."/><xsl:text>, </xsl:text>
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
      <contributor>
        <credit-name>
            <xsl:variable name="title">
                <xsl:apply-templates select="ro:namePart[@type = 'family']"/>
                <xsl:apply-templates select="ro:namePart[@type = 'given']"/>
                <xsl:apply-templates select="ro:namePart[@type = 'title']"/>
                <xsl:apply-templates select="ro:namePart[@type = '' or not(@type)]"/>
            </xsl:variable>
            <xsl:value-of select="substring($title,1,string-length($title)-2)"/>
        </credit-name>
        <contributor-attributes>
          <contributor-sequence><xsl:value-of select="@seq"/></contributor-sequence>
        </contributor-attributes>
      </contributor>
    </xsl:template>

</xsl:stylesheet>

