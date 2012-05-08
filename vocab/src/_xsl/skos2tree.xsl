<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#">
    <xsl:param name="selectedID"/>
    <xsl:output indent="yes" omit-xml-declaration="yes" method="html" />
    <xsl:key name="descKey" use="@rdf:about" match="rdf:Description"/>
    
    <xsl:template match="rdf:RDF">
        <xsl:element name="root">
            <xsl:apply-templates select="rdf:Description[skos:hasTopConcept]"/>
        </xsl:element>
    </xsl:template>
    
    
    <xsl:template match="rdf:Description">
        <xsl:param name="parentID" select="'0'"/>
        <xsl:variable name="thisID" select="@rdf:about"/>
        
        <xsl:element name="item">
            <xsl:if test="$parentID = '0'">
                <xsl:attribute name="rel">root</xsl:attribute>
            </xsl:if>
            <xsl:if test="not(skos:narrower)">
                <xsl:attribute name="rel">leaf</xsl:attribute>
            </xsl:if>
            <xsl:attribute name="id"><xsl:value-of select="$thisID"/></xsl:attribute>
            <xsl:attribute name="parent_id"><xsl:value-of select="$parentID"/></xsl:attribute>
            <xsl:attribute name="state">closed</xsl:attribute>
            <!-- <xsl:attribute name="onclick">displayResult(<xsl:value-of select="$thisID"/>);</xsl:attribute>
            -->
            
            <xsl:element name="content">
                <xsl:apply-templates select="skos:prefLabel"/>
            </xsl:element>
            
            <xsl:for-each select="skos:narrower">
                <xsl:apply-templates select="key('descKey',current()/@rdf:resource)">
                    <xsl:with-param name="parentID" select="$thisID"/>       
                </xsl:apply-templates>
            </xsl:for-each>
            
            <xsl:for-each select="skos:hasTopConcept">
                <xsl:apply-templates select="key('descKey',current()/@rdf:resource)"> 
                    <xsl:with-param name="parentID" select="$thisID"/>                     
                </xsl:apply-templates>
            </xsl:for-each>
            
        </xsl:element> 
    </xsl:template>
    
    
    <xsl:template match="skos:prefLabel">
        <xsl:element name="name">
            <xsl:value-of select="."/>
        </xsl:element>
    </xsl:template>
    
</xsl:stylesheet>
