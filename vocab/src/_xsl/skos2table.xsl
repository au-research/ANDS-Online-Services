<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#">
<xsl:param name="about" select="'http://purl.org/au-research/vocabulary/anzsrc-for/2008/01'"/>
<xsl:output indent="yes"/>
<xsl:key name="descKey" use="@rdf:about" match="rdf:Description"/>
    
    <xsl:template match="rdf:RDF">
        <xsl:apply-templates select="rdf:Description[@rdf:about = $about]"/>
    </xsl:template>
    
    
    <xsl:template match="rdf:Description">
        <xsl:element name="table">
            <xsl:apply-templates select="skos:prefLabel"/>
            <xsl:apply-templates select="@rdf:about"/>
            <xsl:apply-templates select="skos:altLabel"/>
            <xsl:if test="skos:broader">
                <xsl:element name="tr">
                    <xsl:element name="td">Broader Concepts</xsl:element>
                    <xsl:element name="td"><xsl:apply-templates select="skos:broader"/></xsl:element>           
                </xsl:element>
            </xsl:if>
            <xsl:if test="skos:narrower">
                <xsl:element name="tr">
                    <xsl:element name="td">Narrower Concepts</xsl:element>
                    <xsl:element name="td"><xsl:apply-templates select="skos:narrower"/></xsl:element>           
                </xsl:element>
            </xsl:if>           
        </xsl:element>
    </xsl:template>
       
    <xsl:template match="skos:prefLabel">
        <xsl:element name="tr">
        <xsl:element name="td">Preferred Label</xsl:element>
        <xsl:element name="td"><xsl:value-of select="."/></xsl:element>           
        </xsl:element>
    </xsl:template>
    
    <xsl:template match="@rdf:about">
        <xsl:element name="tr">
            <xsl:element name="td">URI</xsl:element>
            <xsl:element name="td"><xsl:value-of select="."/></xsl:element>           
        </xsl:element>
    </xsl:template>
    
    
    <xsl:template match="skos:altLabel">
        <xsl:element name="tr">
            <xsl:element name="td">Alternative Label</xsl:element>
            <xsl:element name="td"><xsl:value-of select="."/></xsl:element>           
        </xsl:element>
    </xsl:template>
    
    
    <xsl:template match="skos:broader | skos:narrower">
        <xsl:element name="a"><xsl:attribute name="onclick">displayResult("<xsl:value-of select="@rdf:resource" />");</xsl:attribute><xsl:value-of select="key('descKey',@rdf:resource)/skos:prefLabel"/></xsl:element><xsl:element name="br"/>           
    </xsl:template>
       
    
</xsl:stylesheet>
