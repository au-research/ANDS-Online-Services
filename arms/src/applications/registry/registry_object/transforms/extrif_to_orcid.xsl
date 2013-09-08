<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet xmlns:ro="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:extRif="http://ands.org.au/standards/rif-cs/extendedRegistryObjects" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" exclude-result-prefixes="ro extRif">
<xsl:param name="base_url"/>
<xsl:param name="rda_url"/>
<!-- http://support.orcid.org/knowledgebase/articles/118795-->
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
                            <xsl:when test="$style = 'Harvard'"><xsl:text>formatted-harvard</xsl:text></xsl:when>
                            <xsl:when test="$style = 'APA'"><xsl:text>formatted-apa</xsl:text></xsl:when>
                            <xsl:when test="$style = 'IEEE'"><xsl:text>formatted-ieee</xsl:text></xsl:when>
                            <xsl:when test="$style = 'MLA'"><xsl:text>formatted-mla</xsl:text></xsl:when>
                            <xsl:when test="$style = 'Vancouver'"><xsl:text>formatted-vancouver</xsl:text></xsl:when>
                            <xsl:when test="$style = 'Chicago'"><xsl:text>formatted-chicago</xsl:text></xsl:when>
                            <xsl:when test="($style = 'Bibtex') or $style = 'bibtex'"><xsl:text>bibtex</xsl:text></xsl:when>
                            <xsl:otherwise>
                                <xsl:text>formatted-unspecified</xsl:text>
                            </xsl:otherwise>
                        </xsl:choose>
                    </work-citation-type>
                    <citation><xsl:value-of select="ro:collection/ro:citationInfo/ro:fullCitation/text()"/></citation>
                </work-citation>
            </xsl:if>
            <work-type>
                <xsl:choose>
                    <xsl:when test="ro:collection/@type='Book'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Advertisement'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Audio/Visual'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Book'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Brochure'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Cartoon/comic'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Chapter Anthology'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Components'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Conference Proceedings'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Congressional Publication'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Court Case'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Database'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Dictionary Entry'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Digital Image'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Dissertation Abstract'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Dissertation'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Email'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Editorial'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Electronic Only'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Encyclopedia Article'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Executive Order'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Federal Bill'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Federl Report'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Federal Rule'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Federal Statute'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Federal Testimony'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Film / Movie'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Government Publication'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Interview'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Journal Article'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Lecture / Speech'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Legal'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Letter'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Live Performance'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Magazine Article'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Mailing List'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Manuscript'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Map / Chart'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Musical Recording'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Newsgroup'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Newsletter'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Newspaper Article'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Non-periodicals'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Other'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Painting'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Pamphlet'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Patent'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Periodicals'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Photograph'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Press Release'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Raw Data'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Religious Text'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Report'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Reports / Working Papers'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Review'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Scholarly Project'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Software'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Standards'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Television / Radio'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Theological Text'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Thesis'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='Web Site'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:otherwise>Other</xsl:otherwise>
                </xsl:choose>
            </work-type>
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
            <!-- <xsl:variable name="sourceUrl">
                <xsl:call-template name="getSourceURL"/>
            </xsl:variable>
            <xsl:if test="$sourceUrl != ''">
                <url><xsl:value-of select="$sourceUrl"/></url>
            </xsl:if> -->
            <url><xsl:value-of select="$rda_url"/></url>
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
            <work-external-identifier-type>
                <xsl:choose>
                    <xsl:when test="(@type='arxiv') 
                        or (@type='asin') 
                        or (@type='asin-tld') 
                        or (@type='bibcode') 
                        or (@type='doi')
                        or (@type='eid')
                        or (@type='isbn')
                        or (@type='issn')
                        or (@type='jfm')
                        or (@type='jstor')
                        or (@type='lccn')
                        or (@type='mr')
                        or (@type='oclc')
                        or (@type='ol')
                        or (@type='osti')
                        or (@type='pmc')
                        or (@type='pmid')
                        or (@type='rfc')
                        or (@type='ssrn')
                        or (@type='zbl')
                        ">
                        <xsl:value-of select="@type"/>
                    </xsl:when>
                    <xsl:otherwise>other-id</xsl:otherwise>
                </xsl:choose>
                <!-- http://support.orcid.org/knowledgebase/articles/118807 -->
            </work-external-identifier-type>
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
            <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='doi']">
                <xsl:value-of select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='doi']"/>
            </xsl:when>
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
          <contributor-sequence>
            <xsl:choose>
                <xsl:when test="@seq=1">first</xsl:when>
                <xsl:otherwise>additional</xsl:otherwise>
            </xsl:choose>
        </contributor-sequence>
        <contributor-role>
            author
            <!-- author,  assignee,  editor,  chair-or-translator,  co-investigator,  co-inventor,  graduate-student,  other-inventor,  principal-investigator,  postdoctoral-researcher,  support-staff-->
        </contributor-role>
        </contributor-attributes>
      </contributor>
    </xsl:template>

</xsl:stylesheet>

