<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:ro="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:extRif="http://ands.org.au/standards/rif-cs/extendedRegistryObjects" exclude-result-prefixes="ro">
    <xsl:output method="html" encoding="UTF-8" indent="no" omit-xml-declaration="yes"/>
    <xsl:strip-space elements="*"/>
    <xsl:param name="dataSource" select="//ro:originatingSource"/>
    <xsl:param name="dateCreated"/>
    <xsl:param name="base_url" select="'https://test.ands.org.au/orca/'"/>  
    <xsl:param name="orca_view"/>  
    <xsl:param name="theGroup"/>     
    <xsl:param name="key"/>   
    <xsl:variable name="connectionLimit">
    	5
    </xsl:variable>   
    <xsl:variable name="objectClass" >
        <xsl:choose>
            <xsl:when test="//ro:collection">Collection</xsl:when>
            <xsl:when test="//ro:activity">Activity</xsl:when>
            <xsl:when test="//ro:party">Party</xsl:when>
            <xsl:when test="//ro:service">Service</xsl:when>            
        </xsl:choose>       
    </xsl:variable>
	<xsl:variable name="objectClassType" >
		<xsl:choose>
			<xsl:when test="//ro:collection">collections</xsl:when>
			<xsl:when test="//ro:activity">activities</xsl:when>
			<xsl:when test="//ro:party/@type='group'">party_multi</xsl:when>
			<xsl:when test="//ro:party/@type='person'">party_one</xsl:when>		
			<xsl:when test="//ro:party">party_multi</xsl:when>	
			<xsl:when test="//ro:service">services</xsl:when>	
		</xsl:choose>		
	</xsl:variable>		                    
    <xsl:template match="ro:registryObject">
        <!--  We will first set up the breadcrumb menu for the page -->   
        <span id="originating_source" class="hide"><xsl:value-of select="$dataSource"/></span>     			
		<xsl:apply-templates select="ro:collection | ro:activity | ro:party | ro:service"/>
      </xsl:template>       
       
    
 

    <xsl:template match="ro:collection | ro:activity | ro:party | ro:service">
		<xsl:choose>
			<!--  we want to check if there are any related objects or a contributor page to see if we need to set tp the connections box -->
	    	<xsl:when test="../extRif:extendedMetadata/extRif:contributorPage!='' or extRif:relatedObjectPersonCount!='0' or extRif:relatedObjectGroupCount!='0' or extRif:relatedObjectCollectionCount!='0' or extRif:relatedObjectServiceCount!='0' or extRif:relatedObjectActivityCount!='0'">
				<span id="connections-realnumfound" class="hide">1</span>	
	
				<!-- for each record class or type determine if it goes into the lists of connections -->	
					
				<xsl:if test="ro:relatedObject/extRif:relatedObjectType='person'">	
					<h3><img class="icon-heading-connections"><xsl:attribute name="src"><xsl:value-of select="$base_url"/><xsl:text>/img/icon/party_one_16.png</xsl:text></xsl:attribute><xsl:attribute name="alt">Researchers</xsl:attribute></img> Researchers</h3>				    		
					<ul  class="connection_list">
					<xsl:for-each select="ro:relatedObject/extRif:relatedObjectType[../extRif:relatedObjectType='person']">
						<xsl:if test="position()&lt;=$connectionLimit">	
							<li>
							<a> 
							<xsl:attribute name="href">
							<xsl:value-of select="$base_url"/>
							<xsl:choose>
								<xsl:when test="../extRif:relatedObjectSlug=''">
									<xsl:text>view/?key=</xsl:text><xsl:value-of select="../key"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="../extRif:relatedObjectSlug"/>
								</xsl:otherwise>					
							</xsl:choose>							
							</xsl:attribute>
					 		<xsl:attribute name="title">
					 		<xsl:for-each select="../ro:relation/@extRif:type">
					 			<xsl:choose>	 		
					 				<xsl:when test="position()=1">		
					 					<xsl:value-of select="."/>
					 				</xsl:when>
					 				<xsl:otherwise>
					 					,<xsl:value-of select="."/>
					 				</xsl:otherwise>
					 			</xsl:choose>
					 		</xsl:for-each>
							</xsl:attribute>											
							<xsl:value-of select="../extRif:relatedObjectDisplayTitle"/>
							</a>	
							<xsl:if test="../ro:relation!='' and string-length(../ro:relation)&lt;64">					 		
					 			<br /><span class="faded"><xsl:value-of select="../ro:relation"/></span>
					 		</xsl:if>						
							</li>					    	
						</xsl:if>
					</xsl:for-each>		
					</ul>
					<xsl:if test="..//extRif:relatedObjectPersonCount &gt; $connectionLimit">
					<a class="connections_NumFound" type_id="person">
					<xsl:attribute name="href">
					<xsl:text>javascript:void(0);</xsl:text>
					</xsl:attribute>						
					<p>View all <span id="collectionconnections-realnumfound"><xsl:value-of select="..//extRif:relatedObjectPersonCount"/></span> connected researchers</p>
					</a>
					</xsl:if>
				</xsl:if>
				
				<xsl:if test="ro:relatedObject/extRif:relatedObjectType='group'">		
					<h3><img class="icon-heading-connections"><xsl:attribute name="src"><xsl:value-of select="$base_url"/><xsl:text>/img/icon/party_multi_16.png</xsl:text></xsl:attribute><xsl:attribute name="alt">Research Groups</xsl:attribute></img>Research Groups</h3>							    		
					<ul  class="connection_list">			 
					 <xsl:for-each select="ro:relatedObject/extRif:relatedObjectType[../extRif:relatedObjectType='group']">
						<xsl:if test="position()&lt;=$connectionLimit">			
							<li>
							<a> 
							<xsl:attribute name="href">
							<xsl:value-of select="$base_url"/>
							<xsl:choose>
								<xsl:when test="../extRif:relatedObjectSlug=''">
									<xsl:text>view/?key=</xsl:text><xsl:value-of select="../key"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="../extRif:relatedObjectSlug"/>
								</xsl:otherwise>					
							</xsl:choose>							
							</xsl:attribute>
					 		<xsl:attribute name="title">
					 		<xsl:for-each select="../ro:relation/@extRif:type">
					 			<xsl:choose>	 		
					 				<xsl:when test="position()=1">		
					 					<xsl:value-of select="."/>
					 				</xsl:when>
					 				<xsl:otherwise>
					 					,<xsl:value-of select="."/>
					 				</xsl:otherwise>
					 			</xsl:choose>
					 		</xsl:for-each>
							</xsl:attribute>											
							<xsl:value-of select="../extRif:relatedObjectDisplayTitle"/>
							</a>	
							<xsl:if test="../ro:relation!='' and string-length(../ro:relation)&lt;64">					 		
					 			<br /><span class="faded"><xsl:value-of select="../ro:relation"/></span>
					 		</xsl:if>						
							</li>					    	
						</xsl:if>
					</xsl:for-each>		
					</ul>
					<xsl:if test="..//extRif:relatedObjectGroupCount &gt; $connectionLimit">
					<a class="connections_NumFound" type_id="group">
					<xsl:attribute name="href">
					<xsl:text>javascript:void(0);</xsl:text>
					</xsl:attribute>						
					<p>View all <span id="collectionconnections-realnumfound"><xsl:value-of select="..//extRif:relatedObjectGroupCount"/></span> connected research groups</p>
					</a>
					</xsl:if>				
				</xsl:if>				
					
				<xsl:if test="ro:relatedObject/extRif:relatedObjectClass='activity'">
					<h3><img class="icon-heading-connections"><xsl:attribute name="src"><xsl:value-of select="$base_url"/><xsl:text>/img/icon/activities_16.png</xsl:text></xsl:attribute><xsl:attribute name="alt">Activities</xsl:attribute></img>Activities</h3>	
					<ul  class="connection_list">										
					<xsl:for-each select="ro:relatedObject/extRif:relatedObjectType[../extRif:relatedObjectClass='activity']">
						<xsl:if test="position()&lt;=$connectionLimit">	
							<li>
							<a> 
							<xsl:attribute name="href">
							<xsl:value-of select="$base_url"/>
							<xsl:choose>
								<xsl:when test="../extRif:relatedObjectSlug=''">
									<xsl:text>view/?key=</xsl:text><xsl:value-of select="../key"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="../extRif:relatedObjectSlug"/>
								</xsl:otherwise>					
							</xsl:choose>							
							</xsl:attribute>
					 		<xsl:attribute name="title">
					 		<xsl:for-each select="../ro:relation/@extRif:type">
					 			<xsl:choose>	 		
					 				<xsl:when test="position()=1">		
					 					<xsl:value-of select="."/>
					 				</xsl:when>
					 				<xsl:otherwise>
					 					,<xsl:value-of select="."/>
					 				</xsl:otherwise>
					 			</xsl:choose>
					 		</xsl:for-each>
							</xsl:attribute>											
							<xsl:value-of select="../extRif:relatedObjectDisplayTitle"/>
							</a>	
							<xsl:if test="../ro:relation!='' and string-length(../ro:relation)&lt;64">					 		
					 			<br /><span class="faded"><xsl:value-of select="../ro:relation"/></span>
					 		</xsl:if>						
							</li>					    	
						</xsl:if>
					</xsl:for-each>		
					</ul>
					<xsl:if test="..//extRif:relatedObjectActivityCount &gt; $connectionLimit">
					<a class="connections_NumFound" type_id="group">
					<xsl:attribute name="href">
					<xsl:text>javascript:void(0);</xsl:text>
					</xsl:attribute>						
					<p>View all <span id="collectionconnections-realnumfound"><xsl:value-of select="..//extRif:relatedObjectActivityCount"/></span> connected activities</p>
					</a>
					</xsl:if>
				</xsl:if>

				<xsl:if test="ro:relatedObject/extRif:relatedObjectClass='service'">
					<h3><img class="icon-heading-connections"><xsl:attribute name="src"><xsl:value-of select="$base_url"/><xsl:text>/img/icon/services_16.png</xsl:text></xsl:attribute><xsl:attribute name="alt">Services</xsl:attribute></img><xsl:text>Services</xsl:text></h3>
				<ul  class="connection_list">										
					<xsl:for-each select="ro:relatedObject/extRif:relatedObjectType[../extRif:relatedObjectClass='service']">
						<xsl:if test="position()&lt;=$connectionLimit">	
							<li>
							<a> 
							<xsl:attribute name="href">
							<xsl:value-of select="$base_url"/>
							<xsl:choose>
								<xsl:when test="../extRif:relatedObjectSlug=''">
									<xsl:text>view/?key=</xsl:text><xsl:value-of select="../key"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="../extRif:relatedObjectSlug"/>
								</xsl:otherwise>					
							</xsl:choose>							
							</xsl:attribute>
					 		<xsl:attribute name="title">
					 		<xsl:for-each select="../ro:relation/@extRif:type">
					 			<xsl:choose>	 		
					 				<xsl:when test="position()=1">		
					 					<xsl:value-of select="."/>
					 				</xsl:when>
					 				<xsl:otherwise>
					 					,<xsl:value-of select="."/>
					 				</xsl:otherwise>
					 			</xsl:choose>
					 		</xsl:for-each>
							</xsl:attribute>											
							<xsl:value-of select="../extRif:relatedObjectDisplayTitle"/>
							</a>	
							<xsl:if test="../ro:relation!='' and string-length(../ro:relation)&lt;64">					 		
					 			<br /><span class="faded"><xsl:value-of select="../ro:relation"/></span>
					 		</xsl:if>						
							</li>					    	
						</xsl:if>
					</xsl:for-each>		
					</ul>
					<xsl:if test="..//extRif:relatedObjectServiceCount &gt; $connectionLimit">
					<a class="connections_NumFound" type_id="group">
					<xsl:attribute name="href">
					<xsl:text>javascript:void(0);</xsl:text>
					</xsl:attribute>						
					<p>View all <span id="collectionconnections-realnumfound"><xsl:value-of select="..//extRif:relatedObjectServiceCount"/></span> connected services</p>
					</a>
					</xsl:if>	
				</xsl:if>	
						
				<xsl:if test="ro:relatedObject/extRif:relatedObjectClass='collection'">
					<h3><img class="icon-heading-connections"><xsl:attribute name="src"><xsl:value-of select="$base_url"/><xsl:text>/img/icon/collections_16.png</xsl:text></xsl:attribute><xsl:attribute name="alt">Collections</xsl:attribute></img>Collections</h3>
				<ul class="connection_list">										
					<xsl:for-each select="ro:relatedObject/extRif:relatedObjectType[../extRif:relatedObjectClass='collection']">
						<xsl:if test="position()&lt;=$connectionLimit">	
							<li>
							<a> 
							<xsl:attribute name="href">
							<xsl:value-of select="$base_url"/>
							<xsl:choose>
								<xsl:when test="../extRif:relatedObjectSlug=''">
									<xsl:text>view/?key=</xsl:text><xsl:value-of select="../key"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="../extRif:relatedObjectSlug"/>
								</xsl:otherwise>					
							</xsl:choose>							
							</xsl:attribute>
					 		<xsl:attribute name="title">
					 		<xsl:for-each select="../ro:relation/@extRif:type">
					 			<xsl:choose>	 		
					 				<xsl:when test="position()=1">		
					 					<xsl:value-of select="."/>
					 				</xsl:when>
					 				<xsl:otherwise>
					 					,<xsl:value-of select="."/>
					 				</xsl:otherwise>
					 			</xsl:choose>
					 		</xsl:for-each>
							</xsl:attribute>											
							<xsl:value-of select="../extRif:relatedObjectDisplayTitle"/>
							</a>	
							<xsl:if test="../ro:relation!='' and string-length(../ro:relation)&lt;64">					 		
					 			<br /><span class="faded"><xsl:value-of select="../ro:relation"/></span>
					 		</xsl:if>						
							</li>					    	
						</xsl:if>
					</xsl:for-each>		
					</ul>
					<xsl:if test="..//extRif:relatedObjectCollectionCount &gt; $connectionLimit">
					<a class="connections_NumFound" class_id="collection">
					<xsl:attribute name="href">
					<xsl:text>javascript:void(0);</xsl:text>
					</xsl:attribute>						
					<p>View all <span id="collectionconnections-realnumfound"><xsl:value-of select="..//extRif:relatedObjectCollectionCount"/></span> connected collections</p>
					</a>
					</xsl:if>
				</xsl:if>		
								
				<!-- the following will check if a contributor page exists for this record and if so generate a link to it and display its logo if it has one -->
	    		<xsl:if test="../extRif:extendedMetadata/extRif:contributorPage!=''">
					<h3>
					<img class="icon-heading-connections">
						<xsl:attribute name="src"><xsl:value-of select="$base_url"/><xsl:text>/img/icon/party_multi_16.png</xsl:text></xsl:attribute>
						<xsl:attribute name="alt">Contributor group</xsl:attribute>		
					</img>
					Contributed by
					</h3>
					<ul class="connection_list" >
						<li><a title="Contributor group">
							<xsl:attribute name="href"><xsl:value-of select="$base_url"/>view/group/?group=<xsl:value-of select="../extRif:extendedMetadata/extRif:contributorPage"/><xsl:text>&amp;groupName=</xsl:text><xsl:value-of select="../@group"/></xsl:attribute>
							<xsl:value-of select="../@group"/>
							</a>
		
							<xsl:if test="../extRif:extendedMetadata/extRif:contributorDisplayLogo">	
       						<br />
       						<img id="party_logo" style="max-width:130px;max-height:63px">
	   							<xsl:attribute name="src"><xsl:value-of select="../extRif:extendedMetadata/extRif:contributorDisplayLogo"/></xsl:attribute>
								<xsl:attribute name="alt">Contributor logo</xsl:attribute>
							</img>
							</xsl:if>
		
						</li>
					</ul>    				
				</xsl:if>	
			
			</xsl:when>
			<xsl:otherwise>
				<span id="connections-realnumfound" class="hide">0</span>		
			</xsl:otherwise>
		</xsl:choose>
				
				
				
				
								
	
        
    </xsl:template>


	      
</xsl:stylesheet>
