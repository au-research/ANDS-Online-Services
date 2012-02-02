<?xml version="1.0"?>

<!-- Date Modified: $Date: 2009-08-18 12:43:25 +1000 (Tue, 18 Aug 2009) $ -->
<!-- Version: $Revision: 84 $ -->

<xsl:stylesheet version="2.0" exclude-result-prefixes="xsi"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:xsi="http://www.w3.org/2000/10/XMLSchema-instance">

<xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>

<xsl:strip-space elements="*"/>

<xsl:template match="/">
    <xsl:apply-templates/>
</xsl:template>

  <xsl:template match="text()">
    <xsl:value-of select="."/>
  </xsl:template>

<xsl:template match="node()[local-name() != '']">
    <xsl:element name="{local-name()}">
      <xsl:choose>
        <xsl:when test="not(parent::*)">
            <xsl:apply-templates select="@*"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:apply-templates select="@*[not(contains(name(),'xsi'))]"/>
        </xsl:otherwise>
        </xsl:choose>
        <xsl:apply-templates/>
    </xsl:element>
</xsl:template>


  <xsl:template match="@*">
    <xsl:attribute name="{name()}">
      <xsl:value-of select="."/>
    </xsl:attribute>
  </xsl:template>

</xsl:stylesheet>
