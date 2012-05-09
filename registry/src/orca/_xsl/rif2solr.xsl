<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet [
<!ENTITY nbsp "&#xa0;">
]>
<xsl:stylesheet xmlns:ro="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" exclude-result-prefixes="ro">
    <xsl:output indent="yes" />
    <xsl:strip-space elements="*"/>
<xsl:template match="/">
    <xsl:apply-templates/>
</xsl:template>   
    
 <xsl:template match="ro:registryObjects">
     <add>
    <xsl:apply-templates/>
     </add>
 </xsl:template> 
    
    <xsl:template match="ro:registryObject">
        <doc>
            <xsl:apply-templates select="ro:key"/>
            <xsl:apply-templates select="ro:status"/>
            <xsl:apply-templates select="ro:reverseLinks"/>           
            <xsl:apply-templates select="ro:originatingSource"/>
            <xsl:apply-templates select="ro:dataSourceKey"/>            
            <xsl:element name="field">
                <xsl:attribute name="name">group</xsl:attribute>
                <xsl:value-of select="@group"/>
            </xsl:element>  
            <xsl:apply-templates select="ro:collection | ro:party | ro:activity | ro:service"/>

        </doc>
    </xsl:template> 
   
    <xsl:template match="ro:key">
        <xsl:element name="field">
            <xsl:attribute name="name">key</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="ro:status">
        <xsl:element name="field">
            <xsl:attribute name="name">status</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="ro:reverseLinks">
        <xsl:element name="field">
            <xsl:attribute name="name">reverseLinks</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="ro:originatingSource">
        <xsl:element name="field">
            <xsl:attribute name="name">originating_source</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
     <xsl:template match="ro:dataSourceKey">
        <xsl:element name="field">
            <xsl:attribute name="name">data_source_key</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>   
    
    
    <xsl:template match="ro:displayTitle">
        <xsl:element name="field">
            <xsl:attribute name="name">displayTitle</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="ro:listTitle">
        <xsl:element name="field">
            <xsl:attribute name="name">listTitle</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
   
    <xsl:template match="ro:collection | ro:party | ro:activity | ro:service">
        <xsl:element name="field">
            <xsl:attribute name="name">class</xsl:attribute>
            <xsl:value-of select="local-name()"/>
        </xsl:element>  
        <xsl:element name="field">
            <xsl:attribute name="name">type</xsl:attribute>
            <xsl:value-of select="@type"/>
        </xsl:element>  
        <xsl:element name="field">
            <xsl:attribute name="name">date_modified</xsl:attribute>
            <xsl:value-of select="@dateModified"/>
        </xsl:element>  
        <xsl:apply-templates select="ro:identifier" mode="value"/>
        <xsl:apply-templates select="ro:identifier" mode="type"/>
        <xsl:apply-templates select="ro:name"/>
        
        <xsl:apply-templates select="ro:subject" mode="value"/>
        <xsl:apply-templates select="ro:subject" mode="type"/>
        
        <xsl:apply-templates select="ro:description" mode="value"/>
        <xsl:apply-templates select="ro:description" mode="type"/>
        
        <xsl:apply-templates select="ro:displayTitle"/>
        <xsl:apply-templates select="ro:listTitle"/>
        
        <xsl:apply-templates select="ro:location"/>
        <xsl:apply-templates select="ro:coverage"/>
        
        <xsl:apply-templates select="ro:relatedObject"/>
        <xsl:apply-templates select="ro:relatedInfo"/>
    </xsl:template>
    
    <xsl:template match="ro:location">
        <xsl:element name="field">
            <xsl:attribute name="name">location</xsl:attribute>
            <xsl:apply-templates/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="ro:relatedInfo">
    <xsl:element name="field">
        <xsl:attribute name="name">relatedInfo</xsl:attribute>
        <xsl:apply-templates/>
    </xsl:element>       
    </xsl:template>
    
    <xsl:template match="ro:relatedInfo/*">
            <xsl:value-of select="."/><xsl:text> </xsl:text>
    </xsl:template>
    
    
    <xsl:template match="ro:relatedObject">

            <xsl:apply-templates/>
       
    </xsl:template>
    
    <xsl:template match="ro:relatedObject/*[not(local-name() = 'relation')]">
        <xsl:element name="field">
            <xsl:attribute name="name">relatedObject_<xsl:value-of select="local-name()"/></xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="ro:relatedObject/ro:relation">
    <xsl:element name="field">
        <xsl:attribute name="name">relatedObject_<xsl:value-of select="local-name()"/></xsl:attribute>
        <xsl:value-of select="@type"/>
    </xsl:element>  
    <xsl:apply-templates/>
    </xsl:template>

    <xsl:template match="ro:relatedObject/ro:relation/ro:description">
    <xsl:element name="field">
        <xsl:attribute name="name">relatedObject_relation_<xsl:value-of select="local-name()"/></xsl:attribute>
        <xsl:value-of select="."/>
    </xsl:element>  
    </xsl:template>
    
    <!--temporal>
        <date type="dateFrom" dateFormat="W3CDTF">1986-09-01</date>
        <date type="dateTo" dateFormat="W3CDTF">1991-01-01</date>
    </temporal-->
    
    <xsl:template match="ro:coverage/ro:temporal/ro:date[@type = 'dateFrom'] | ro:coverage/ro:temporal/ro:date[@type = 'dateTo']">
        <xsl:variable name="dateString"><xsl:value-of select="."/></xsl:variable>
        <xsl:variable name="dateValue">
            <xsl:choose>
                <xsl:when test="contains($dateString ,'-')">
                    <xsl:value-of select="substring-before($dateString ,'-')"/>
                </xsl:when>
                <xsl:when test="contains($dateString ,'/')">
                    <xsl:value-of select="substring-before($dateString ,'/')"/>
                </xsl:when>
                <xsl:when test="contains($dateString ,'T')">
                    <xsl:value-of select="substring-before($dateString ,'T')"/>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="."/>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <xsl:if test="number($dateValue) != 'NaN' and $dateValue != ''">
	        <xsl:element name="field">
	            <xsl:attribute name="name"><xsl:value-of select="@type"/></xsl:attribute>
	            <xsl:value-of select="$dateValue"/>           
	        </xsl:element>     
        </xsl:if>   
    </xsl:template>
    
    <xsl:template match="ro:address | ro:electronic | ro:physical | ro:coverage | ro:temporal">
            <xsl:apply-templates/>
    </xsl:template>
    
    <xsl:template match="ro:electronic/ro:value | ro:addressPart | ro:location/ro:spatial[@type = 'text']">
            <xsl:value-of select="."/><xsl:text> </xsl:text>
    </xsl:template>
    
    <xsl:template match="ro:identifier" mode="value">
        <xsl:element name="field">
            <xsl:attribute name="name">identifier_value</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="ro:identifier" mode="type">
        <xsl:element name="field">
            <xsl:attribute name="name">identifier_type</xsl:attribute>
            <xsl:value-of select="@type"/>
        </xsl:element>       
    </xsl:template>
    
    
    <xsl:template match="ro:name">
		<xsl:apply-templates/>     
    </xsl:template>
    
    <xsl:template match="ro:name/ro:listTitle">
        <xsl:element name="field">
            <xsl:attribute name="name">alt_listTitle</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="ro:name/ro:displayTitle">
        <xsl:element name="field">
            <xsl:attribute name="name">alt_displayTitle</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="ro:subject" mode="value">
        <xsl:element name="field">
            <xsl:attribute name="name">subject_value</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="ro:subject" mode="type">
        <xsl:element name="field">
            <xsl:attribute name="name">subject_type</xsl:attribute>
            <xsl:value-of select="@type"/>
        </xsl:element>
    </xsl:template>
    
    <xsl:template match="ro:description" mode="value">
        <xsl:element name="field">
            <xsl:attribute name="name">description_value</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="ro:description" mode="type">
        <xsl:element name="field">
            <xsl:attribute name="name">description_type</xsl:attribute>
            <xsl:value-of select="@type"/>
        </xsl:element>
    </xsl:template>
    <!-- ignore list -->
    <xsl:template match="ro:location/ro:spatial | ro:coverage/ro:spatial">
        <xsl:element name="field">
            <xsl:attribute name="name">spatial_coverage</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>
    </xsl:template>
    
    <xsl:template match="ro:location/ro:center | ro:coverage/ro:center">
        <xsl:element name="field">
            <xsl:attribute name="name">spatial_coverage_center</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>
    </xsl:template>
   
    <xsl:template match="ro:date"/>
   		
   
</xsl:stylesheet>

