<?xml version="1.0"?>
<xsl:stylesheet xmlns:ro="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:extRif="http://ands.org.au/standards/rif-cs/extendedRegistryObjects" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" exclude-result-prefixes="ro extRif">
    <xsl:output method="html" encoding="UTF-8" indent="no" omit-xml-declaration="yes"/>
    <xsl:param name="dataSource"/>
    <xsl:param name="reverseLinks" select ="'true'"/>
    <xsl:param name="output" select="'script'"/>
    <xsl:param name="relatedObjectClassesStr" select="'true'"/>
    <xsl:template match="/">
        <xsl:apply-templates/>
    </xsl:template>
    
    <xsl:template match="ro:registryObjects">
	    <xsl:choose>
		    <xsl:when test="$output = 'script'">
		        <script>
					<xsl:apply-templates select="ro:registryObject"/>
					<xsl:text>$("#errors_preview").delay(1100).show();</xsl:text>
				</script>
		    </xsl:when>
		    <xsl:otherwise>
		    	<div class="quality-test-results">
		    		<xsl:apply-templates select="ro:registryObject"/>
		    	</div>
		    </xsl:otherwise>
	    </xsl:choose>
    </xsl:template>
     
    <!-- REGISTRY OBJECT CHECKS -->
    <xsl:template match="ro:registryObject">
        <xsl:if test="string-length(ro:collection/@type) = 0 and string-length(ro:activity/@type) = 0 and string-length(ro:party/@type) = 0 and string-length(ro:service/@type) = 0">
           <xsl:choose>
			    <xsl:when test="$output = 'script'">
					<xsl:text>SetErrors("errors_mandatoryInformation_type","Type must be specified");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Registry Object Type must be specified</span>
			    </xsl:otherwise>
	    	</xsl:choose>           
        </xsl:if>
        <xsl:if test="string-length(ro:collection/@type) &gt; 32 or string-length(ro:activity/@type) &gt; 32 or string-length(ro:party/@type) &gt; 32 or string-length(ro:service/@type) &gt; 32">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_mandatoryInformation_type","Type must be less than 32 characters");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Registry Object Type must be less than 32 characters</span>
			    </xsl:otherwise>
	    	</xsl:choose>          
        </xsl:if>
        <xsl:if test="string-length($dataSource) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_mandatoryInformation_dataSource","A Data Source must be selected for this record");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">A Data Source must be selected for this record</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(ro:key) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_mandatoryInformation_key","A valid key must be specified for this record");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">A valid key must be specified for this record</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(ro:key) &gt; 512">
        	<xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_mandatoryInformation_key","Key must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Key must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@group) = 0">
        	<xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_mandatoryInformation_group","A group must be specified for this record");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">A group must be specified for this record</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@group) &gt; 512">
        	<xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_mandatoryInformation_group","A group must be less then 512 character");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">A group must be less then 512 character</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        
        <xsl:apply-templates select="ro:collection | ro:activity | ro:party | ro:service" />
    </xsl:template>
    
    <!--  COLLECTION/PARTY/ACTIVITY LEVEL CHECKS -->
    <xsl:template match="ro:collection">
    <xsl:variable name="CP_roError_cont">
	<xsl:if test="$reverseLinks = 'true'">
	<xsl:text> &lt;i&gt;If you have created the relationship from the Party to the Collection, please ignore this message.&lt;/i&gt;</xsl:text>
	</xsl:if>
    </xsl:variable>
    <xsl:variable name="CA_roError_cont">
	<xsl:if test="$reverseLinks = 'true'">
	<xsl:text> &lt;i&gt;If you have created the relationship from the Activity to the Collection, please ignore this message.&lt;/i&gt;</xsl:text>
	</xsl:if>
    </xsl:variable>
	<xsl:if test="not(ro:name[@type='primary'])">
        	<xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetWarnings("errors_name","At least one primary name is required for the Collection record.","REQ_PRIMARY_NAME");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">At least one primary name is required for the Collection record.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        
        <xsl:if test="not(ro:description[@type='brief']) and not(ro:description[@type='full'])">
        	<xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetWarnings("errors_description","At least one description (brief and/or full) is required for the Collection.","REQ_DESCRIPTION_FULL");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">At least one description (brief and/or full) is required for the Collection.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
       <xsl:if test="not(ro:description[@type='rights']) and not(ro:description[@type='accessRights']) and not(ro:rights)">
        	<xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetWarnings("errors_description","At least one description of the rights, licences or access rights relating to the Collection is required.","REQ_RIGHT");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">At least one description of the rights, licences or access rights relating to the Collection is required.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="not(ro:location/ro:address)">
        	<xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetWarnings("errors_location","At least one location address is required for the Collection.","REQ_LOCATION_ADDRESS");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">At least one location address is required for the Collection.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>  
        
        <xsl:if test="not(ro:relatedObject/ro:key[@roclass = 'Activity']) and $output = 'script'">
            <xsl:text>SetInfos("errors_relatedObject","The Collection must be related to at least one Activity record where available.</xsl:text><xsl:value-of select="$CA_roError_cont"/><xsl:text>","REC_RELATED_OBJECT_ACTIVITY");</xsl:text>
		</xsl:if>
		
        <xsl:if test="not(contains($relatedObjectClassesStr, 'Activity') or ro:relatedObject/ro:key[@roclass = 'Activity'] or ro:relatedObject/ro:key[@roclass = 'activity']) and $output = 'html'">
			<span class="info">The Collection must be related to at least one Activity record where available.<xsl:value-of select="$CA_roError_cont"/></span>
        </xsl:if>
        
        <xsl:if test="not(ro:relatedObject/ro:key[@roclass = 'Party']) and $output = 'script'">
            <xsl:text>SetWarnings("errors_relatedObject","The Collection must be related to at least one Party record</xsl:text><xsl:value-of select="$CP_roError_cont"/><xsl:text>","REQ_RELATED_OBJECT_PARTY");</xsl:text>
        </xsl:if>
        
        <xsl:if test="not(contains($relatedObjectClassesStr, 'Party') or ro:relatedObject/ro:key[@roclass = 'Party'] or ro:relatedObject/ro:key[@roclass = 'party']) and $output = 'html'">
			<span class="warning">The Collection must be related to at least one Party record.<xsl:value-of select="$CP_roError_cont"/></span>
        </xsl:if>
  	
        <xsl:if test="not(ro:identifier)">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetInfos("errors_identifier","At least one identifier is recommended for the Collection.","REC_IDENTIFIER");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="info">At least one identifier is recommended for the Collection.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        
        <xsl:if test="not(ro:subject) or not(ro:subject[string-length(.) &gt; 0] and ro:subject[string-length(@type) &gt; 0])">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetInfos("errors_subject","At least one subject (e.g. anzsrc-for code) is recommended for the Collection.","REC_SUBJECT");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="info">At least one subject (e.g. anzsrc-for code) is recommended for the Collection.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        
        <xsl:if test="not(ro:coverage/ro:spatial)">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetInfos("errors_coverage","At least one spatial coverage for the Collection is recommended.","REC_SPATIAL_COVERAGE");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="info">At least one spatial coverage for the Collection is recommended.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        
        <xsl:if test="not(ro:coverage/ro:temporal/ro:date[@type='dateFrom']) and not(ro:coverage/ro:temporal/ro:date[@type = 'dateTo'])">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetInfos("errors_coverage","At least one temporal coverage entry for the collection is recommended.","REC_TEMPORAL_COVERAGE");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="info">At least one temporal coverage entry for the collection is recommended.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
         <xsl:if test="not(ro:citationInfo)">
        	<xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetInfos("errors_citationInfo","Citation data for the collection is recommended.","REC_CITATION");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="info">Citation data for the collection is recommended.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>         
        <xsl:apply-templates select="ro:description | ro:coverage | ro:location | ro:name | ro:identifier | ro:subject | ro:relatedObject | ro:relatedInfo | ro:citationInfo | ro:rights"/>
   </xsl:template>
    
    <xsl:template match="ro:party">
    <xsl:variable name="PC_roError_cont">
	<xsl:if test="$reverseLinks = 'true'">
	<xsl:text> &lt;i&gt;If you have created the relationship from the Collection to the Party, please ignore this message.&lt;/i&gt;</xsl:text>
	</xsl:if>
    </xsl:variable>
    <xsl:variable name="PA_roError_cont">
	<xsl:if test="$reverseLinks = 'true'">
	<xsl:text> &lt;i&gt;If you have created the relationship from the Activity to the Party, please ignore this message.&lt;/i&gt;</xsl:text>
	</xsl:if>
    </xsl:variable>

	<xsl:if test="not(ro:name[@type='primary'])">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetWarnings("errors_name","At least one primary name is required for the Party record.","REQ_PRIMARY_NAME");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">At least one primary name is required for the Party record.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        
        <xsl:if test="not(ro:identifier)">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetInfos("errors_identifier","At least one identifier is recommended for the Party.","REC_IDENTIFIER");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="info">At least one identifier is recommended for the Party.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        
        <xsl:if test="not(ro:location/ro:address)">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetInfos("errors_location","At least one location address is recommended for the Party.","REC_LOCATION_ADDRESS");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="info">At least one location address is recommended for the Party.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>    
               
        <xsl:if test="not(ro:relatedObject/ro:key[@roclass = 'Activity']) and $output = 'script'">
            <xsl:text>SetInfos("errors_relatedObject","It is recommended that the Party be related to at least one Activity record.</xsl:text><xsl:value-of select="$PA_roError_cont"/><xsl:text>","REC_RELATED_OBJECT_ACTIVITY");</xsl:text>
		</xsl:if>
		
        <xsl:if test="not(contains($relatedObjectClassesStr, 'Activity') or ro:relatedObject/ro:key[@roclass = 'Activity'] or ro:relatedObject/ro:key[@roclass = 'activity']) and $output = 'html'">
			<span class="info">It is recommended that the Party be related to at least one Activity record.<xsl:value-of select="$PA_roError_cont"/></span>
        </xsl:if>
        
        <xsl:if test="not(ro:relatedObject/ro:key[@roclass = 'Collection']) and $output = 'script'">
            <xsl:text>SetWarnings("errors_relatedObject","The Party must be related to at least one Collection record.</xsl:text><xsl:value-of select="$PC_roError_cont"/><xsl:text>","REQ_RELATED_OBJECT_COLLECTION");</xsl:text>
        </xsl:if>
        
        <xsl:if test="not(contains($relatedObjectClassesStr, 'Collection') or ro:relatedObject/ro:key[@roclass = 'Collection'] or ro:relatedObject/ro:key[@roclass = 'collection']) and $output = 'html'">
			<span class="warning">The Party must be related to at least one Collection record.<xsl:value-of select="$PC_roError_cont"/></span>
        </xsl:if>
                      
        <xsl:if test="not(ro:description[@type='brief']) and not(ro:description[@type='full'])">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetInfos("errors_description","At least one description (brief and/or full) is recommended for the Party.","REC_DESCRIPTION_FULL");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="info">At least one description (brief and/or full) is recommended for the Party.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        
        <xsl:if test="not(ro:subject) or not(ro:subject[string-length(.) &gt; 0] and ro:subject[string-length(@type) &gt; 0])">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetInfos("errors_subject","At least one subject (e.g. anzsrc-for code) is recommended for the Party.","REC_SUBJECT");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="info">At least one subject (e.g. anzsrc-for code) is recommended for the Party.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="not(ro:existenceDates)">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetInfos("errors_existenceDates","Existence dates are recommended for the Party.","REC_EXISTENCEDATE");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="info">Existence dates are recommended for the Party.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>        
        <xsl:apply-templates select="ro:description | ro:coverage | ro:location | ro:name | ro:identifier | ro:subject | ro:relatedObject | ro:relatedInfo | ro:relatedInfo | ro:rights | ro:existenceDates"/>
    </xsl:template>
    
    
    <xsl:template match="ro:activity">
    <xsl:variable name="AC_roError_cont">
	<xsl:if test="$reverseLinks = 'true'">
	<xsl:text> &lt;i&gt;If you have created the relationship from the Collection to the Activity, please ignore this message.&lt;/i&gt;</xsl:text>
	</xsl:if>
    </xsl:variable>
    <xsl:variable name="AP_roError_cont">
	<xsl:if test="$reverseLinks = 'true'">
	<xsl:text> &lt;i&gt;If you have created the relationship from the Party to the Activity, please ignore this message.&lt;/i&gt;</xsl:text>
	</xsl:if>
    </xsl:variable>
	<xsl:if test="not(ro:name[@type='primary'])">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetWarnings("errors_name","At least one primary name is required for the Activity record.","REQ_PRIMARY_NAME");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">At least one primary name is required for the Activity record.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        
        <xsl:if test="not(ro:description[@type='brief']) and not(ro:description[@type='full'])">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetWarnings("errors_description","At least one description (brief and/or full) is required for the Activity.","REQ_DESCRIPTION_FULL");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">At least one description (brief and/or full) is required for the Activity.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        
        <xsl:if test="not(ro:location/ro:address)">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetInfos("errors_location","At least one location address is recommended for the Activity.","REC_LOCATION_ADDRESS");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="info">At least one location address is recommended for the Activity.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>    
        
        <xsl:if test="not(ro:relatedObject/ro:key[@roclass = 'Party']) and $output = 'script'">
            <xsl:text>SetWarnings("errors_relatedObject","The Activity must be related to at least one Party record.</xsl:text><xsl:value-of select="$AP_roError_cont"/><xsl:text>","REQ_RELATED_OBJECT_PARTY");</xsl:text>
		</xsl:if>
		
        <xsl:if test="not(contains($relatedObjectClassesStr, 'Party') or ro:relatedObject/ro:key[@roclass = 'Party'] or ro:relatedObject/ro:key[@roclass = 'party']) and $output = 'html'">
			<span class="warning">The Activity must be related to at least one Party record.<xsl:value-of select="$AP_roError_cont"/></span>
        </xsl:if>
              
       <xsl:if test="not(ro:relatedObject/ro:key[@roclass = 'Collection']) and $output = 'script'">
            <xsl:text>SetInfos("errors_relatedObject","The Activity must be related to at least one Collection record if available.</xsl:text><xsl:value-of select="$AC_roError_cont"/><xsl:text>","REC_RELATED_OBJECT_COLLECTION");</xsl:text>
        </xsl:if>
        
        <xsl:if test="not(contains($relatedObjectClassesStr, 'Collection') or ro:relatedObject/ro:key[@roclass = 'Collection'] or ro:relatedObject/ro:key[@roclass = 'collection']) and $output = 'html'">
			<span class="info">The Activity must be related to at least one Collection record if available.<xsl:value-of select="$AC_roError_cont"/></span>
        </xsl:if>             
        <xsl:if test="not(ro:subject) or not(ro:subject[string-length(.) &gt; 0] and ro:subject[string-length(@type) &gt; 0])">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetInfos("errors_subject","At least one subject (e.g. anzsrc-for code) is recommended for the Activity.","REC_SUBJECT");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="info">At least one subject (e.g. anzsrc-for code) is recommended for the Activity.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="not(ro:existenceDates)">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetInfos("errors_existenceDates","Existence dates are recommended for the Activity.","REC_EXISTENCEDATE");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="info">Existence dates are recommended for the Activity.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>              
         <xsl:apply-templates select="ro:description | ro:coverage | ro:location | ro:name | ro:identifier | ro:subject | ro:relatedObject | ro:relatedInfo | ro:relatedInfo | ro:rights | ro:existenceDates"/>
    </xsl:template>
    
    
    <xsl:template match="ro:service">
    <xsl:variable name="SC_roError_cont">
	<xsl:if test="$reverseLinks = 'true'">
	<xsl:text> &lt;i&gt;If you have created the relationship from the Collection to the Service, please ignore this message.&lt;/i&gt;</xsl:text>
	</xsl:if>
    </xsl:variable>
    <xsl:variable name="SP_roError_cont">
	<xsl:if test="$reverseLinks = 'true'">
	<xsl:text> &lt;i&gt;If you have created the relationship from the Party to the Service, please ignore this message.&lt;/i&gt;</xsl:text>
	</xsl:if>
    </xsl:variable>
	<xsl:if test="not(ro:name[@type='primary'])">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetWarnings("errors_name","At least one primary name is required for the Service record.","REQ_PRIMARY_NAME");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">At least one primary name is required for the Service record.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        
        <xsl:if test="not(ro:relatedObject/ro:key[@roclass = 'Party']) and $output = 'script'">
            <xsl:text>SetInfos("errors_relatedObject","It is recommended that the Service be related to at least one Party record.</xsl:text><xsl:value-of select="$SP_roError_cont"/><xsl:text>", "REC_RELATED_OBJECT_PARTY");</xsl:text>
		</xsl:if>
		
        <xsl:if test="not(contains($relatedObjectClassesStr, 'Party') or ro:relatedObject/ro:key[@roclass = 'Party'] or ro:relatedObject/ro:key[@roclass = 'party']) and $output = 'html'">
			<span class="info">It is recommended that the Service be related to at least one Party record.<xsl:value-of select="$SP_roError_cont"/></span>
        </xsl:if>
        
        <xsl:if test="not(ro:relatedObject/ro:key[@roclass = 'Collection']) and $output = 'script'">
            <xsl:text>SetWarnings("errors_relatedObject","The Service must be related to at least one Collection record.</xsl:text><xsl:value-of select="$SC_roError_cont"/><xsl:text>","REQ_RELATED_OBJECT_COLLECTION");</xsl:text>
        </xsl:if>
        
        <xsl:if test="not(contains($relatedObjectClassesStr, 'Collection') or ro:relatedObject/ro:key[@roclass = 'Collection'] or ro:relatedObject/ro:key[@roclass = 'collection']) and $output = 'html'">
			<span class="warning">The Service must be related to at least one Collection record.<xsl:value-of select="$SC_roError_cont"/></span>
        </xsl:if> 
               
        <xsl:if test="not(ro:description[@type='brief']) and not(ro:description[@type='full'])">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetInfos("errors_description","At least one description (brief and/or full) is recommended for the Service.","REC_DESCRIPTION_FULL");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="info">At least one description (brief and/or full) is recommended for the Service.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        
        <xsl:if test="not(ro:location/ro:address/ro:electronic)">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetInfos("errors_location","At least one electronic address is required for the Service if available.","REC_LOCATION_ADDRESS_ELECTRONIC");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="info">At least one electronic address is required for the Service if available.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>    
     <!--    
        <xsl:if test="not(ro:accessPolicy)">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetWarnings("errors_accessPolicy","At least one Access Policy URL is recommended for the Service record.","REQ_ACCESS_POLICY");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">At least one Access Policy URL is recommended for the Service record.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if> --> 
        
         <xsl:apply-templates select="ro:description | ro:coverage | ro:location | ro:name | ro:identifier | ro:subject | ro:relatedObject | ro:relatedInfo | ro:accessPolicy | ro:rights | ro:existenceDates"/>
    </xsl:template>
    
    <!-- SERVICE LEVEL CHECKS -->
    
    <!--  SUBJECT CHECKS -->
    <xsl:template match="ro:subject">
        <xsl:choose>
            <xsl:when test="string-length(@type) &gt; 512">
	            <xsl:choose>
				    <xsl:when test="$output = 'script'">
	                	<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_type","Type must be less than 512 characters.");</xsl:text>
				    </xsl:when>
				    <xsl:otherwise>
						<span class="error">Subject Type must be less than 512 characters.</span>
				    </xsl:otherwise>
		    	</xsl:choose>
            </xsl:when>
            <xsl:when test="string-length(@type) = 0">
	            <xsl:choose>
				    <xsl:when test="$output = 'script'">
                		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_type","A Subject Type must be specified. &lt;br/&gt;&lt;span&gt;E.g. 'anzsrc-for'&lt;/span&gt;");</xsl:text>
				    </xsl:when>
				    <xsl:otherwise>
						<span class="error">Subject Type must be specified.</span>
				    </xsl:otherwise>
		    	</xsl:choose>
            </xsl:when>
        </xsl:choose>
        <xsl:choose>
        	<xsl:when test="string-length(@termIdentifier) &gt; 512">
	            <xsl:choose>
				    <xsl:when test="$output = 'script'">
	                	<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_termIdentifier","Term Identifier must be less than 512 characters.");</xsl:text>
				    </xsl:when>
				    <xsl:otherwise>
						<span class="error">Subject Term Identifier must be less than 512 characters.</span>
				    </xsl:otherwise>
		    	</xsl:choose>
            </xsl:when>
        </xsl:choose>
        <xsl:choose>
            <xsl:when test="string-length(.) &gt; 512">
	            <xsl:choose>
				    <xsl:when test="$output = 'script'">
                		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Subject must be less than 512 characters.");</xsl:text>
				    </xsl:when>
				    <xsl:otherwise>
						<span class="error">Subject must be less than 512 characters.</span>
				    </xsl:otherwise>
		    	</xsl:choose>
            </xsl:when>
            <xsl:when test="string-length(.) = 0">
	            <xsl:choose>
				    <xsl:when test="$output = 'script'">
               			 <xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","A Subject Value must be entered. &lt;br/&gt;&lt;span&gt;E.g. '0302' (A 4 digit ANZSRC Field of Research code)&lt;/span&gt;");</xsl:text>
				    </xsl:when>
				    <xsl:otherwise>
						<span class="error">Subject must have a value.</span>
				    </xsl:otherwise>
		    	</xsl:choose>
            </xsl:when>
        </xsl:choose>
    </xsl:template>
    
    
    <!-- DESCRIPTION CHECKS -->
    <xsl:template match="ro:collection/ro:description | ro:party/ro:description | ro:activity/ro:description | ro:service/ro:description">
        <xsl:choose>
            <xsl:when test="string-length(@type) &gt; 512">
	            <xsl:choose>
				    <xsl:when test="$output = 'script'">
                		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_type","Type must be less than 512 characters.");</xsl:text>
				    </xsl:when>
				    <xsl:otherwise>
						<span class="error">Description Type must be less than 512 characters.</span>
				    </xsl:otherwise>
		    	</xsl:choose>
            </xsl:when>
            <xsl:when test="string-length(@type) = 0">
	            <xsl:choose>
				    <xsl:when test="$output = 'script'">
                		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_type","A Description Type must be specified. &lt;br/&gt;&lt;span&gt;E.g. 'full'&lt;/span&gt;");</xsl:text>
				    </xsl:when>
				    <xsl:otherwise>
						<span class="error">Description Type must be specified.</span>
				    </xsl:otherwise>
		    	</xsl:choose>
            </xsl:when>
        </xsl:choose>
        <xsl:choose>
            <xsl:when test="string-length(.) &gt; 12000">
                <xsl:choose>
				    <xsl:when test="$output = 'script'">
               			 <xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Description Value must be less than 12000 characters.");</xsl:text>
				    </xsl:when>
				    <xsl:otherwise>
						<span class="error">Description must be less than 12000 characters.</span>
				    </xsl:otherwise>
		    	</xsl:choose>
            </xsl:when>
            <xsl:when test="string-length(.) = 0">
                <xsl:choose>
				    <xsl:when test="$output = 'script'">
                		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","A Description Value must be entered. ");</xsl:text>
				    </xsl:when>
				    <xsl:otherwise>
						<span class="error">Description must have a value.</span>
				    </xsl:otherwise>
		    	</xsl:choose>
            </xsl:when>
            <!-- xsl:when test="string-length(.) &lt; 9">
                <xsl:choose>
				    <xsl:when test="$output = 'script'">
                		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Description Value must be 9 characters or more.");</xsl:text>
				    </xsl:when>
				    <xsl:otherwise>
						<span class="error">Description must be 9 characters or more.</span>
				    </xsl:otherwise>
		    	</xsl:choose>
            </xsl:when-->
        </xsl:choose>
    </xsl:template>
    
    
    <!-- NAME CHECKS -->
    <xsl:template match="ro:name">
        <xsl:if test="not(ro:namePart)">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_namePart","Each Name must have at least one Name Part.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Each Name must have at least one Name Part.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <!-- 
        <xsl:if test="string-length(@type) = 0">
            <xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_type","Name must have a type");</xsl:text>
        </xsl:if>
        -->
        <xsl:if test="string-length(@type) &gt; 512">
        	<xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_type","Name must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Name must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:apply-templates select="ro:namePart" />
    </xsl:template>
    
    <xsl:template match="ro:namePart">
        <!--xsl:if test="string-length(@type) = 0 and ancestor::ro:party[@type = 'person']">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetWarnings("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_type","A Name Part Type must be specified.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">Name Part must have a type.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if-->
        <xsl:if test="string-length(@type) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
					<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_type","Name Part type must be less than 512 characters");</xsl:text>          							
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Name Part type must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>	    	
        </xsl:if>
        <xsl:if test="string-length(.) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script' and ancestor::ro:activity">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","A Name Part Value must be entered. &lt;br/&gt;&lt;span&gt;E.g. 'Study of bacteria growth in Lake Macquarie 2010-2011'&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:when test="$output = 'script' and ancestor::ro:collection">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","A Name Part Value must be entered. &lt;br/&gt;&lt;span&gt;E.g. 'Effects of Nicotine on the Human Body'&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:when test="$output = 'script' and ancestor::ro:service">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","A Name Part Value must be entered. &lt;br/&gt;&lt;span&gt;E.g. 'Australian Mammal Identification Portal'&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:when test="$output = 'script' and ancestor::ro:party">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","A Name Part Value must be entered. &lt;br/&gt;&lt;span&gt; E.g. 'John'&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Name Part must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
	    			<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Name Part must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Name Part must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    
    <!--  IDENTIFIER CHECKS -->
    <xsl:template match="ro:identifier">
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Identifier must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Identifier must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(.) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
	    			<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","An Identifier Value must be entered. &lt;br/&gt;&lt;span&gt;E.g. '10.1234/5678' (a DOI)&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Identifier must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
	    			<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_type","Type must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Identifier Type must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
	    			<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_type","An Identifier Type must be specified. &lt;br/&gt;&lt;span&gt;E.g. 'doi'&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Identifier must have a type.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    <!-- LOCATION CHECKS -->
    <xsl:template match="ro:location">   
    	<xsl:apply-templates select="ro:address | ro:spatial"/>
    </xsl:template>
    
    <xsl:template match="ro:address">
    	<xsl:apply-templates select="ro:electronic | ro:physical"/>   	
    </xsl:template>
    
    <xsl:template match="ro:electronic">
        <xsl:if test="string-length(ro:value) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
	    			<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value_1_value","Electronic Address must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Electronic Address must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(ro:value) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
	    			<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value_1_value","An Electronic Address Value must be entered. &lt;br/&gt;&lt;span&gt;E.g. 'john.doe@example.com' (An email address) &lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Electronic Address must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
	    			<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_type","Electronic Address Type must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Electronic Address Type  must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    	<xsl:apply-templates select="ro:arg"/>
    </xsl:template>
   
   
       <xsl:template match="ro:physical">
        <xsl:if test="string-length(@lang) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
	    			<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_lang","Physical Address Lang Attribute must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Physical Address Lang Attribute must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="not(ro:addressPart)">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
	    			<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Physical Address must have at least one Address Part.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Physical Address must have at least one Address Part.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
	    			<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_type","Electronic Address Type must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Electronic Address Type must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    	<xsl:apply-templates select="ro:addressPart"/>
    </xsl:template>
   
   
    <xsl:template match="ro:addressPart">
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Address Part must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Address Part must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(.) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","An Address Part Value must be entered.&lt;br/&gt;&lt;span&gt;E.g. '123 Example Street' (An address line)&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Address Part must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_type","An Address Part Type must be specified.&lt;br/&gt;&lt;span&gt;E.g. 'addressLine'&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Address Part Type must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_type","Address Part Type must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Address Part Type must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
   <xsl:template match="ro:coverage">
    	<xsl:apply-templates select="ro:temporal| ro:spatial"/>
    </xsl:template>
    
    <xsl:template match="ro:arg">
    	<xsl:if test="not(@required = 'true' or @required = 'false')">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_required","Required must be either true or false.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Electronic Address Arg. Required must be either true or false.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    	<xsl:if test="string-length(ro:name) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_name","Name must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Electronic Address Arg. Name must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_type","An Argument Type must be specified. &lt;br/&gt;&lt;span&gt;E.g. 'string'&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Electronic Address Arg. Type must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    	<xsl:if test="string-length(@type) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_type","Type must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Electronic Address Arg. Type must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(.) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","An Argument Value must be entered. &lt;br/&gt;&lt;span&gt;E.g. 'http://www.example.com/createRecord'&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Electronic Address Argument must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    	<xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Argument value must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Argument value must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
  
    
    <!-- RELATED OBJECT CHECKS -->
    <xsl:template match="ro:relatedObject">
        <xsl:if test="not(ro:key)">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_key","Key of the Related Object must be specified.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Key of the Related Object must be specified.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:apply-templates select="ro:key | ro:relation"/>
    </xsl:template>
  
    <xsl:template match="ro:relatedObject/ro:key">
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Key must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Related Object Key must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(.) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","A Related Object Key must be entered. &lt;br/&gt;&lt;span&gt;E.g. 'exampleKey.1'&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Related Object Key must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:relation">
    
        <xsl:if test="string-length(@type) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_type","A Relation Type must be specified. &lt;br/&gt;&lt;span&gt;E.g. 'isOwnedBy'&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Each Relation type must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_type","Relation type must be less than 512 characters");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Relation type must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:apply-templates select="ro:description | ro:url"/>
    </xsl:template>
    
    <xsl:template match="ro:relation/ro:description">
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Relation Description must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Relation Description must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:relation/ro:url">
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
           			<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Relation URL must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Relation URL must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    
    <!-- RELATED INFO CHECKS -->
    <xsl:template match="ro:relatedInfo">
        <!--xsl:if test="string-length(@type) = 0">
            <xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_type","Each Related Information must have a Type specified");</xsl:text>
        </xsl:if-->
        <xsl:if test="string-length(@type) &gt; 64">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
           			<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_type","Related Information Type must be less than 64 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Related Information Type must be less than 64 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:apply-templates select="ro:identifier | ro:title | ro:notes"/>
    </xsl:template>
    
    <xsl:template match="ro:relatedInfo/ro:identifier">
        <xsl:if test="string-length(.) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","A Related Info Identifier Value must be entered. &lt;br/&gt;&lt;span&gt;E.g. '9780471418450' (An ISBN)&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Related Info Identifier must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Related Info Identifier value must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Related Info Identifier value must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_type","A Related Info Identifier Type must be specified. &lt;br/&gt;&lt;span&gt;E.g. 'isbn'&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Related Info Identifier must have a type.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_type","Related Info Identifier Type must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Related Info Identifier Type must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:relatedInfo/ro:title">
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Related Info Title must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Related Info Title must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    <xsl:template match="ro:relatedInfo/ro:notes">
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Related Info Notes must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Related Info Notes must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    
    <!--  SERVICE ACCESS POLICY CHECKS -->
    <xsl:template match="ro:accessPolicy">
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Access Policy value must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Access Policy value must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(.) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Access Policy must have a value.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Access Policy must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
     <xsl:template match="ro:coverage/ro:temporal">
        <xsl:apply-templates select="ro:date|ro:text" /> 
     </xsl:template>
    
    <xsl:template match="ro:coverage/ro:temporal/ro:date">
    	<xsl:if test="string-length(.) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Temporal Coverage Date must have a value.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Temporal Coverage Date must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Temporal Coverage Date value must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Temporal Coverage Date value must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_type","A Temporal Coverage Date Type must be specified. &lt;br/&gt;&lt;span&gt;E.g. 'dateFrom'&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Temporal Coverage Date must have a type.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_type","Temporal Coverage Date type must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Temporal Coverage Date type must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@dateFormat) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_dateFormat","A Temporal Coverage Date Format must be specified. &lt;br/&gt;&lt;span&gt;E.g. 'W3CDTF'&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Temporal Coverage Date must have a dateFormat.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@dateFormat) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_dateFormat","Temporal Coverage Date dateFormat must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Temporal Coverage Date dateFormat must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:coverage/ro:temporal/ro:text">
    	<xsl:if test="string-length(.) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Temporal Coverage Text must have a value.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Temporal Coverage Text must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Temporal Coverage Text value must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Temporal Coverage Text value must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:coverage/ro:spatial">
        <xsl:if test="string-length(.) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","A Spatial Coverage Value must be entered.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Spatial Coverage must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Spatial Coverage value must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Spatial Coverage value must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_type","A Spatial Coverage Type must be specified. &lt;br/&gt;&lt;span&gt;E.g. 'gml'&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Spatial Coverage Type must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
           			<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_type","Spatial Coverage Type value must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Spatial Coverage Type value must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:location/ro:spatial">
        <xsl:if test="string-length(.) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","A Spatial Location Value must be entered.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Spatial Location must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Spatial Location value must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Spatial Location value must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_type","A Spatial Location Type must be specified. &lt;br/&gt;&lt;span&gt;E.g. 'gml'&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Spatial Location Type must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_type","Spatial Location Type value must be less than 512 characters");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Spatial Location Type value must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:citationInfo">
    	<xsl:apply-templates select="ro:fullCitation | ro:citationMetadata"/>
    </xsl:template>
    
    <xsl:template match="ro:fullCitation">
    	<xsl:if test="string-length(.) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","A Full Citation Value must be entered. &lt;br/&gt;&lt;span&gt;E.g. 'Australian Bureau of Agricultural and Resource Economics 2001, &lt;br/&gt;Aquaculture development in Australia: a review of key economic issues, ABARE, Canberra.'&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Full Citation must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Full Citation must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Full Citation must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@style) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_style","Full Citation Style must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Full Citation Style must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:existenceDates">
		<xsl:apply-templates select="ro:startDate | ro:endDate"/>
    </xsl:template>
    
    <xsl:template match="ro:existenceDates/ro:startDate">
    	<xsl:if test="string-length(.) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Existence Date must have a value.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Existence Start Date must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Existence Date must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Existence Start Date must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@dateFormat) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
           			<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_dateFormat","A Date Format must be specified. &lt;br/&gt;&lt;span&gt;E.g. 'W3CDTF'&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Existence Start Date Format must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@dateFormat) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
           			<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_dateFormat","Date Format must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Existence Start Date Format must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:existenceDates/ro:endDate">
    	<xsl:if test="string-length(.) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Existence Date must have a value.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Existence End Date must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Existence Date must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Existence End Date must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@dateFormat) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
           			<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_dateFormat","A Date Format must be specified. &lt;br/&gt;&lt;span&gt;E.g. 'W3CDTF'&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Existence End Date Format must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@dateFormat) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
           			<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_dateFormat","Date Format must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Existence End Date Format must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    
    <xsl:template match="ro:rights">
		<xsl:apply-templates select="ro:rightsStatement | ro:licence | ro:accessRights"/>
    </xsl:template>
        
    <xsl:template match="ro:rights/ro:rightsStatement">
        <xsl:if test="string-length(.) &gt; 12000">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Rights Statement must be less than 12000 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Rights Statement must be less than 12000 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@rightsUri) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
           			<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_rightsUri","Rights URI must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Rights Statement Rights URI must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:rights/ro:licence">
        <xsl:if test="string-length(.) &gt; 12000">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Licence must be less than 12000 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Licence must be less than 12000 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@rightsUri) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
           			<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_rightsUri","Rights URI must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Licence Rights URI must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:rights/ro:accessRights">
        <xsl:if test="string-length(.) &gt; 12000">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Access Rights must be less than 12000 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Access Rights must be less than 12000 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@rightsUri) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
           			<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_rightsUri","Rights URI must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Access Rights URI must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:citationMetadata">
        <xsl:if test="not(ro:contributor)">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Citation Metadata must have at least one Contributor.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata must have at least one Contributor.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
		<xsl:apply-templates select="ro:identifier | ro:contributor | ro:title | ro:edition | ro:publisher | ro:placePublished | ro:date | ro:url | ro:context"/>
    </xsl:template>
    
    
    <xsl:template match="ro:citationMetadata/ro:identifier">
        <xsl:if test="string-length(.) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","An Identifier Value must be entered. &lt;br/&gt;&lt;span&gt;E.g. 'exampleHandle/1234' (A handle)&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Identifier must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Identifier must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Identifier must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_type","An Identifier Type must be specified. &lt;br/&gt;&lt;span&gt;E.g. 'handle'&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Identifier Type must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
           			<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_type","Identifier Type must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Identifier Type must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
 
    <xsl:template match="ro:citationMetadata/ro:contributor">
    	<xsl:if test="not(number(@seq)) and @seq != ''">
	        <xsl:choose>
			    <xsl:when test="$output = 'script'">
	        		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_seq","Sequence must be a number.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Contributor Sequence must be a number.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="not(ro:namePart)">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
           			<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Contributor must have at least one namepart.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Contributor must have at least one namepart.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:apply-templates select="ro:namePart"/>
    </xsl:template>
    
    <xsl:template match="ro:citationMetadata/ro:contributor/ro:namePart">
         <xsl:if test="string-length(.) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","A Name Part Value must be entered. &lt;br/&gt;&lt;span&gt;E.g. 'John Doe'&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Contributor Name Part must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Name Part must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Contributor Name Part must be less than 512 character.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_type","Name Part Type must be less than 512 characters");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Contributor Name Part Type must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:citationMetadata/ro:date">
        <xsl:if test="string-length(.) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Date must have a value.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Date must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Date must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Date must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_type","A Date Type must be specified. &lt;br/&gt;&lt;span&gt;E.g. 'publicationDate'&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Date Type must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_type","Date Type must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Date Type must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:citationMetadata/ro:title">
	    <xsl:if test="string-length(.) = 0">
	        <xsl:choose>
			    <xsl:when test="$output = 'script'">
	        		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","A Title must be entered. &lt;br/&gt;&lt;span&gt;E.g. 'Aquaculture development in Australia'&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Title must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
	    </xsl:if>
	    <xsl:if test="string-length(.) &gt; 512">
	        <xsl:choose>
			    <xsl:when test="$output = 'script'">
	        		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Title must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Title must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
	    </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:citationMetadata/ro:url">
	    <xsl:if test="string-length(.) = 0">
	        <xsl:choose>
			    <xsl:when test="$output = 'script'">
	        		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","A URL must be entered. &lt;br/&gt;&lt;span&gt;E.g. 'http://www.example.com'&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata URL must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
	    </xsl:if>
	    <xsl:if test="string-length(.) &gt; 512">
	        <xsl:choose>
			    <xsl:when test="$output = 'script'">
	        		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","URL must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata URL must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
	    </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:citationMetadata/ro:context">
	    <xsl:if test="string-length(.) = 0">
	        <xsl:choose>
			    <xsl:when test="$output = 'script'">
	        		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","A Context must be entered. &lt;br/&gt;&lt;span&gt;E.g. 'Aquaculture development database'&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Context must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
	    </xsl:if>
	    <xsl:if test="string-length(.) &gt; 512">
	        <xsl:choose>
			    <xsl:when test="$output = 'script'">
	        		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Context must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Context must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
	    </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:citationMetadata/ro:edition">
	    <xsl:if test="string-length(.) = 0">
	        <xsl:choose>
			    <xsl:when test="$output = 'script'">
	        		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","An Edition must be entered. &lt;br/&gt;&lt;span&gt;E.g. '2nd edition'&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Edition must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
	    </xsl:if>
	    <xsl:if test="string-length(.) &gt; 512">
	        <xsl:choose>
			    <xsl:when test="$output = 'script'">
	        		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Edition must be less than 512 characters");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Edition must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
	    </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:citationMetadata/ro:publisher">
	    <xsl:if test="string-length(.) &gt; 512">
	        <xsl:choose>
			    <xsl:when test="$output = 'script'">
	        		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Publisher must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Publisher must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
	    </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:citationMetadata/ro:placePublished">
	    <xsl:if test="string-length(.) = 0">
	        <xsl:choose>
			    <xsl:when test="$output = 'script'">
	        		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","A Place Published must be entered. &lt;br/&gt;&lt;span&gt;E.g. 'Sydney, Australia'&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Place Published must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
	    </xsl:if>
	    <xsl:if test="string-length(.) &gt; 512">
	        <xsl:choose>
			    <xsl:when test="$output = 'script'">
	        		<xsl:text>SetErrors("errors_</xsl:text><xsl:value-of select="@field_id"/><xsl:text>_value","Place Published must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Place Published must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
	    </xsl:if>
    </xsl:template>
    
    <xsl:template match="@* | node()" />
    
    
</xsl:stylesheet>
