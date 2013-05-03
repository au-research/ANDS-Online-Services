<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet xmlns:ro="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:extRif="http://ands.org.au/standards/rif-cs/extendedRegistryObjects" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" exclude-result-prefixes="ro extRif">

    <xsl:output indent="yes" omit-xml-declaration="yes"/>
    <xsl:strip-space elements="*"/>

    <xsl:template match="/ | @* | node()">
        <xsl:copy>
            <xsl:apply-templates select="@* | node()" />
        </xsl:copy>
    </xsl:template>

    <xsl:template match="@field_id | @tab_id"/>

    
    <!-- Note: No checks for @lang/@seq attributes -->
    <xsl:template match="location[not(@dateFrom) and not(@dateTo) and not(@type) and not(spatial/@type) and not(spatial/text()) and not(address/electronic/@type = '') and not(address/electronic/value/text()) and not(address/electronic/arg/value/text()) and not(address/electronic/arg/required/text()) and not(address/electronic/arg/@type) and not(address/electronic/arg/use/text()) and not(address/physical/@type) and not(address/physical/addressPart/@type) and not(address/physical/addressPart/text())]"/>
   
    
    <xsl:template match="spatial[not(@type) and not(text())]"/>
    <xsl:template match="coverage[not(temporal/date/text()) and not(temporal/date/@dateFormat) and not(temporal/date/@type) and not(temporal/text()) and not(spatial/text()) and not(spatial/@type)]"/>

 
   
</xsl:stylesheet>

