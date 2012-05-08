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
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:cfd="http://apsr.edu.au/namespaces/cfd">
<xsl:output method="xml" version="1.0" indent="yes" omit-xml-declaration="yes" encoding="utf-8" />
<xsl:preserve-space elements="" />

<xsl:template match="table">
   <xsl:variable name="aTitle"><xsl:value-of select="@title"/></xsl:variable>
   <xsl:variable name="aCols"><xsl:value-of select="@cols"/></xsl:variable>
   <table summary="{$aTitle}">
      <thead>
         <tr>
            <td colspan="{$aCols}"><xsl:value-of select="@title"/></td>
         </tr>
      </thead>
      <tbody>
         <xsl:apply-templates/>
      </tbody>
   </table>
</xsl:template>

<xsl:template match="row">
   <tr>
      <xsl:apply-templates/>
   </tr>
</xsl:template>

<xsl:template match="head">
   <xsl:variable name="aAlign"><xsl:value-of select="@align"/></xsl:variable>
   <xsl:choose>
      <xsl:when test="@align"><th align="{$aAlign}"><xsl:value-of select="text()"/></th></xsl:when>
      <xsl:otherwise><th><xsl:value-of select="text()"/></th></xsl:otherwise>
   </xsl:choose>
</xsl:template>

<xsl:template match="col">
   <xsl:variable name="aAlign"><xsl:value-of select="@align"/></xsl:variable>
   <xsl:choose>
      <xsl:when test="@align"><td align="{$aAlign}"><xsl:value-of select="text()"/></td></xsl:when>
      <xsl:otherwise><td><xsl:value-of select="text()"/></td></xsl:otherwise>
   </xsl:choose>
</xsl:template>

<xsl:template match="node()" priority="-999"/>
</xsl:stylesheet>