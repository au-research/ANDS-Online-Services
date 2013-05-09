<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet xmlns:ro="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:extRif="http://ands.org.au/standards/rif-cs/extendedRegistryObjects" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" exclude-result-prefixes="ro extRif">

    <xsl:output indent="yes" omit-xml-declaration="yes"/>
    <xsl:param name="removeFormAttributes" select="'true'"/>
    <xsl:strip-space elements="*"/>

    <xsl:template match="/|comment()|processing-instruction()">
        <xsl:copy>
          <xsl:apply-templates/>
        </xsl:copy>
    </xsl:template>

    <xsl:template match="*">
        <xsl:element name="{local-name()}">
          <xsl:apply-templates select="@*|node()"/>
        </xsl:element>
    </xsl:template>

    <xsl:template match="@*">
        <xsl:attribute name="{local-name()}">
          <xsl:value-of select="."/>
        </xsl:attribute>
    </xsl:template>

    <xsl:template match="@field_id | @tab_id | @roclass">
        <xsl:if test="$removeFormAttributes != 'true'">
                <xsl:copy-of select="."/>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="dates">
        <xsl:choose>
            <xsl:when test="date[text() != '' or @type != '']">
                <xsl:copy>
                    <xsl:apply-templates select="@* | date[text() != '' or @type != '']" />
                </xsl:copy>   
            </xsl:when>
        </xsl:choose>
    </xsl:template>


    <xsl:template match="citationInfo">
        <xsl:choose>
            <xsl:when test="fullCitation[@style != '' or text() != ''] or citationMetadata[identifier/@type !='' or identifier/text() != '' or title/text() != '' or publisher/text() != '']">
                <xsl:copy>
                    <xsl:apply-templates select="@* | node()" />
                </xsl:copy>   
            </xsl:when>
        </xsl:choose>
    </xsl:template>


    <xsl:template match="physical">
        <xsl:choose>
            <xsl:when test="addressPart[@type != '' or text() != '']">
                <xsl:copy>
                    <xsl:apply-templates select="@* | addressPart[@type != '' or text() != '']" />
                </xsl:copy>   
            </xsl:when>
        </xsl:choose>
    </xsl:template>



    <xsl:template match="electronic">
        <xsl:choose>
            <xsl:when test="@type != '' or value/text() != ''">
                <xsl:copy>
                    <xsl:apply-templates select="@* | node()" />
                </xsl:copy>   
            </xsl:when>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="format">
        <xsl:choose>
            <xsl:when test="identifier[@type != '' or text() != '']">
                <xsl:copy>
                    <xsl:apply-templates select="identifier[@type != '' or text() != '']" />
                </xsl:copy>   
            </xsl:when>
        </xsl:choose>
    </xsl:template>
    
    <!-- Note: No checks for @lang/@seq attributes -->
    <xsl:template match="location[not(@dateFrom) and not(@dateTo) and not(@type) and not(spatial/@type) and not(spatial/text()) and not(address/electronic/@type = '') and not(address/electronic/value/text()) and not(address/electronic/arg/text()) and not(address/electronic/arg/@required) and not(address/electronic/arg/@type) and not(address/electronic/arg/@use) and not(address/physical/@type) and not(address/physical/addressPart/@type) and not(address/physical/addressPart/text())]"/>
    <xsl:template match="relatedObject[not(key/text()) and relation/@type = '' and not(relation/description/text()) and not(relation/url/text())]"/> 
    <xsl:template match="description[(not(@type) or @type='') and not(text())]"/>
    <xsl:template match="spatial[(not(@type) or @type='') and not(text())]"/>
    <xsl:template match="text[not(text())]"/>
    <xsl:template match="addressPart[not(text()) or (not(@type) or @type='')]"/>
    <xsl:template match="subject[(not(@type) or @type='') and not(text())]"/>
    <xsl:template match="namePart[(not(@type) or @type='') and not(text()) and (following-sibling::namePart[text() != ''] or preceding-sibling::namePart[text() != ''])]"/>
    <xsl:template match="date[not(parent::citationMetadata) and not(text())]"/>
    <xsl:template match="fullCitation[(not(@style) or @style='') and not(text()) ]"/>
    <xsl:template match="identifier[not(parent::citationMetadata) and not(parent::relatedInfo) and not(text()) and (not(@type) or @type='')]"/>
    <xsl:template match="coverage[not(temporal/date/text()) and not(temporal/date/@dateFormat) and not(temporal/date/@type) and not(temporal/text/text()) and not(spatial/text()) and not(spatial/@type)]"/>

    <xsl:template match="citationMetadata[(not(identifier/@type) or identifier/@type='') and not(identifier/text()) and not(title/text()) and not(publisher/text())]"/>


<!--citationInfo><citationMetadata><identifier type=""></identifier><title field_id="668"></title><edition field_id="672"></edition><placePublished field_id="676"></placePublished><publisher field_id="680"></publisher><url field_id="684"></url><context field_id="688"></context><contributor field_id="695" seq="1"><namePart field_id="697" type=""></namePart></contributor><date field_id="706" type=""></date></citationMetadata></citationInfo-->

 
   
</xsl:stylesheet>

