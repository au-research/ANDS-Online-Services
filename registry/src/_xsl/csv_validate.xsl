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

<xsl:template match="form">
   <xsl:apply-templates/>
</xsl:template>

<xsl:template match="text | list">
   <xsl:if test="validation/mandatory='true'"><xsl:value-of select="@id"/>||mandatory||<xsl:value-of select="presentation/label"/>@@</xsl:if>
   <xsl:if test="validation/type='integer'"><xsl:value-of select="@id"/>||integer||<xsl:value-of select="presentation/label"/>||<xsl:value-of select="value"/><xsl:if test="validation/min">||min=<xsl:value-of select="validation/min"/></xsl:if><xsl:if test="validation/max">||max=<xsl:value-of select="validation/max"/></xsl:if>@@</xsl:if>
   <xsl:if test="validation/type='number'"><xsl:value-of select="@id"/>||number||<xsl:value-of select="presentation/label"/>||<xsl:value-of select="value"/><xsl:if test="validation/min">||min=<xsl:value-of select="validation/min"/></xsl:if><xsl:if test="validation/max">||max=<xsl:value-of select="validation/max"/></xsl:if>@@</xsl:if>
   <xsl:if test="validation/type='date'"><xsl:value-of select="@id"/>||date||<xsl:value-of select="presentation/label"/>||<xsl:value-of select="value"/><xsl:if test="validation/min">||min=<xsl:value-of select="validation/min"/></xsl:if><xsl:if test="validation/max">||max=<xsl:value-of select="validation/max"/></xsl:if>@@</xsl:if>
   <xsl:if test="validation/type='time'"><xsl:value-of select="@id"/>||time||<xsl:value-of select="presentation/label"/>||<xsl:value-of select="value"/><xsl:if test="validation/min">||min=<xsl:value-of select="validation/min"/></xsl:if><xsl:if test="validation/max">||max=<xsl:value-of select="validation/max"/></xsl:if>@@</xsl:if>
   <xsl:if test="validation/type='datetime'"><xsl:value-of select="@id"/>||datetime||<xsl:value-of select="presentation/label"/>||<xsl:value-of select="value"/><xsl:if test="validation/min">||min=<xsl:value-of select="validation/min"/></xsl:if><xsl:if test="validation/max">||max=<xsl:value-of select="validation/max"/></xsl:if>@@</xsl:if>
</xsl:template>

<xsl:template match="node()" priority="-999"/>
</xsl:stylesheet>