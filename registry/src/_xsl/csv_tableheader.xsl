<?xml version="1.0" encoding="UTF-8" ?>
<!-- 
Copyright 2009 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
**************************************************************************** -->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="text" indent="yes" omit-xml-declaration="yes" encoding="utf-8" />
<xsl:preserve-space elements="" />

<xsl:template match="table">
   <xsl:value-of select="@title"/>||<xsl:value-of select="@cols"/>||<xsl:value-of select="@numbered"/>||<xsl:value-of select="@show"/>||<xsl:value-of select="@max"/>@@
   <xsl:apply-templates/>
</xsl:template>

<xsl:template match="row">
   <xsl:apply-templates/>
</xsl:template>

<xsl:template match="head">
   <xsl:choose>
      <xsl:when test="@align"><xsl:value-of select="@id"/>||<xsl:value-of select="text()"/>||<xsl:value-of select="@align"/>||<xsl:value-of select="@sort"/>||<xsl:value-of select="@type"/>||<xsl:value-of select="@ref"/>||<xsl:value-of select="@refname"/>||<xsl:value-of select="@reftype"/>||<xsl:value-of select="@refkey"/>@@</xsl:when>
      <xsl:otherwise><xsl:value-of select="@id"/>||<xsl:value-of select="text()"/>||left||<xsl:value-of select="@sort"/>||<xsl:value-of select="@type"/>||<xsl:value-of select="@ref"/>||<xsl:value-of select="@refname"/>||<xsl:value-of select="@reftype"/>||<xsl:value-of select="@refkey"/>@@</xsl:otherwise>
   </xsl:choose>
</xsl:template>

<xsl:template match="node()" priority="-999"/>
</xsl:stylesheet>