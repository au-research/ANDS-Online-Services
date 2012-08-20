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
        
		<xsl:variable name="group">	
		<xsl:choose>
		<xsl:when test="string-length(./@group)>30">
		<xsl:value-of select="substring(./@group,0,30)"/>...
		</xsl:when>
		<xsl:otherwise>
		<xsl:value-of select="./@group"/>
		</xsl:otherwise>
		</xsl:choose>
		</xsl:variable>	

		<xsl:variable name="theTitle">	
		<xsl:choose>
		<xsl:when test="string-length(/extRif:extendedMetadata/extRif:displayTitle)>30">
		<xsl:value-of select="substring(/extRif:extendedMetadata/extRif:displayTitle,0,30)"/>...
		</xsl:when>
		<xsl:otherwise>
		<xsl:value-of select="/extRif:extendedMetadata/extRif:displayTitle"/>
		</xsl:otherwise>
		</xsl:choose>
		</xsl:variable>			
    	<div id="top" class="top-corner">
			<ul id="breadcrumb" class="top-corner">
				<li><a href="{$base_url}" class="crumb">Home</a></li>
				<xsl:if test="$theGroup">
					<li><a class="crumb">
					<xsl:attribute name="href">
					<xsl:value-of select="$base_url"/>view/group/?group=<xsl:value-of select="$theGroup"/>&amp;groupName=<xsl:value-of select="./@group"/>
					</xsl:attribute><xsl:value-of select="$group"/></a></li>					
				</xsl:if>
				<xsl:if test="$theGroup = ''">
				<li><a href="{$base_url}search/browse/{./@group}" class="crumb"><xsl:value-of select="$group"/></a></li>				
				</xsl:if>

				<li><a href="{$base_url}search/browse/{./@group}/{$objectClass}" class="crumb"><xsl:value-of select="$objectClass"/></a></li>
				<li><xsl:value-of select="$theTitle"/></li>
				
				
				<div id="breadcrumb-corner">
				    
				
					 <!-- AddToAny BEGIN -->   
	      
	       			 <div class="a2a_kit a2a_default_style no_print" id="share">
	        		<a class="a2a_dd" href="http://www.addtoany.com/share_save">Share</a>
	        		<span class="a2a_divider"></span>
	       			 <a class="a2a_button_linkedin"></a>
	        		<a class="a2a_button_facebook"></a>
	        		<a class="a2a_button_twitter"></a>
	        		<a class="a2a_button_wordpress"></a>
	        		<a class="a2a_button_stumbleupon"></a>
	        		<a class="a2a_button_delicious"></a>
	        		<a class="a2a_button_digg"></a>
	        		<a class="a2a_button_reddit"></a>
	        		<a class="a2a_button_email"></a>
	        		</div>
	        		<script type="text/javascript">
	        		var a2a_config = a2a_config || {};
	        		</script>
	        		<script type="text/javascript" src="http://static.addtoany.com/menu/page.js"></script>
	      
	        		<!-- AddToAny END -->  
     		
					<a target="_blank">
                    <xsl:attribute name="href"><xsl:value-of select="$base_url"/>view/printview/?key=<xsl:value-of select="ro:key"/></xsl:attribute>                    
                    <img id="print_icon">
                    <xsl:attribute name="src">
                    <xsl:value-of select="$base_url"/>
                    <xsl:text>img/</xsl:text>
                    <xsl:text>1313027722_print.png</xsl:text></xsl:attribute>
                    <xsl:attribute name="alt">Print Icon</xsl:attribute>
                    </img>
                    </a>
				</div>
			</ul>
	
		</div>	
			
		<!--  the following hidden elements dfine content to be used in further ajax calls --> 
        <div id="group_value" class="hide"><xsl:value-of select="@group"/></div>
        <div id="datasource_key" class="hide"><xsl:value-of select="@originatingSource"/></div>
        <div id="key_value" class="hide"></div>
         <div id="class" class="hide"><xsl:value-of select="$objectClass"/></div>       
        <span id="key" class="hide"><xsl:value-of select="ro:key"/></span>
             
        <xsl:apply-templates select="ro:collection | ro:activity | ro:party | ro:service"/>
    
    </xsl:template>

    <xsl:template match="ro:collection | ro:activity | ro:party | ro:service">
      	<div id="item-view-inner" class="clearfix" itemscope="" itemType="http://schema.org/Thing">
	
		<div id="left">           
 		<xsl:choose>

	        <xsl:when test="../extRif:extendedMetadata/extRif:displayTitle!=''">
	        	<xsl:apply-templates select="../extRif:extendedMetadata/extRif:displayTitle"/>

	        	
	        </xsl:when>
	         <xsl:otherwise>
	                
	        <div id="displaytitle"><h1 itemprop="name"><xsl:value-of select="../ro:key"/></h1>
	                        	
	            	
	        <xsl:for-each select="//ro:existenceDates">
        		<xsl:if test="./ro:startDate"><xsl:value-of select="./ro:startDate"/></xsl:if>
        		-
				<xsl:if test="./ro:endDate"><xsl:value-of select="./ro:endDate"/></xsl:if><br/>
			</xsl:for-each>
			
			
			</div>
			
			<div class="right_icon">
				<img class="icon-heading">
					<xsl:attribute name="src"><xsl:value-of select="$base_url"/>
						<xsl:text>/img/icon/</xsl:text>
						<xsl:value-of select="$objectClassType"/>
						<xsl:text>_32.png</xsl:text>
					</xsl:attribute>
					<xsl:attribute name="alt"><xsl:value-of select="$objectClassType"/></xsl:attribute>
					
				</img>
			</div> 
	        </xsl:otherwise> 
	        
        </xsl:choose>    
        <div class="clearfix"></div>  

        <xsl:apply-templates select="../extRif:extendedMetadata/extRif:displayLogo"/>

        <div class="clearfix"></div>  

        <xsl:apply-templates select="ro:name[@type='alternative']/ro:displayTitle"/>
                
                
        <div class="clearfix"></div>
        <xsl:if test="ro:description">

            <div class="descriptions" style="position:relative;clear:both;">
				<xsl:apply-templates select="extRif:description[@type= 'brief']" mode="content"/>
				<xsl:apply-templates select="extRif:description[@type= 'full']" mode="content"/>
				<xsl:apply-templates select="extRif:description[@type= 'significanceStatement']" mode="content"/>		
				<xsl:apply-templates select="extRif:description[@type= 'notes']" mode="content"/>	
				<xsl:apply-templates select="extRif:description[not(@type =  'notes' or @type =  'significanceStatement' or @type =  'full' or @type =  'brief' or @type =  'logo' or @type =  'rights' or @type =  'accessRights')]" mode="content"/>											

				
            </div>
        </xsl:if>
        <a href="javascript:void(0);" class="showall_descriptions hide">More...</a>
    
        <xsl:if test="ro:relatedInfo">
        <p><b>More Information:</b> </p>
            <xsl:apply-templates select="ro:relatedInfo"/> 
         </xsl:if>
                        
        <xsl:if test="ro:coverage/extRif:spatial or ro:location/extRif:spatial or ro:coverage/ro:temporal">
            <xsl:variable name="coverageLabel">
            <xsl:choose>
            <xsl:when test="(ro:coverage/extRif:spatial or ro:location[@type='coverage']) and ro:location/extRif:spatial">
            <xsl:text>Coverage And Location:</xsl:text>
            </xsl:when>
            <xsl:when test="ro:location/extRif:spatial">
            <xsl:text>Location:</xsl:text>
            </xsl:when>
             <xsl:when test="ro:coverage/extRif:spatial or ro:coverage/ro:temporal">
            <xsl:text>Coverage:</xsl:text>
            </xsl:when>
            
            </xsl:choose>
            </xsl:variable>
            <p><b><xsl:value-of select="$coverageLabel"/></b></p>
            <xsl:variable name="needMap">   
                <xsl:for-each select="ro:coverage/extRif:spatial"> 
             	<xsl:if test="not(./@type) or (./@type!='text' and ./@type!='dcmiPoint')">        	
                      <xsl:text>yes</xsl:text>
               </xsl:if>
               </xsl:for-each>    

             	<xsl:for-each select="ro:location/extRif:spatial"> 
             	<xsl:if test="not(./@type) or (./@type!='text' and ./@type!='dcmiPoint')">        	
                      <xsl:text>yes</xsl:text>
               </xsl:if>            
               </xsl:for-each>               
        	</xsl:variable>
        
            <xsl:if test="ro:coverage/extRif:spatial/extRif:coords | ro:location/extRif:spatial/extRif:coords">
              <xsl:apply-templates select="ro:coverage/extRif:spatial/extRif:coords | ro:location/extRif:spatial/extRif:coords"/>
              <xsl:if test="$needMap!=''">
                    <div id="spatial_coverage_map"></div>
              </xsl:if>
            </xsl:if>   
                    
            <xsl:if test="ro:coverage/extRif:spatial/extRif:center | ro:location/extRif:spatial/extRif:center">
                <xsl:apply-templates select="ro:coverage/extRif:spatial/extRif:center | ro:location/extRif:spatial/extRif:center"/>
            </xsl:if> 
            
           	<xsl:for-each select="ro:coverage/extRif:spatial[@type!='iso19139dcmiBox' and @type!='gmlKmlPolyCoords' and @type!='kmlPolyCoords']">
     	 		<p class="coverage_text"><xsl:value-of select="./@type"/>: <xsl:value-of select="."/></p>
      		</xsl:for-each>
      		
            <xsl:if test="ro:coverage/ro:temporal/ro:date | ro:location[@dateFrom!=''] | ro:location[@dateTo!='']">
             	<br/><br/>   Time Period:
             </xsl:if>
             
    		<xsl:if test="ro:coverage/ro:temporal/ro:date">
               <br />  <xsl:apply-templates select="ro:coverage/ro:temporal/ro:date"/> 
             </xsl:if> 
             
             <xsl:if test="ro:location[@dateFrom!=''] | ro:location[@dateTo!='']">
             	<br /> <xsl:apply-templates select="ro:location[@dateFrom!=''] | ro:location[@dateTo!='']"/>   
             </xsl:if>           
             
    
            <xsl:if test="ro:coverage/ro:temporal/ro:text">
                <p>Time Period:<br />
                <xsl:apply-templates select="ro:coverage/ro:temporal/ro:text"/> 
                </p>    
            </xsl:if> 
        </xsl:if>
            
        <xsl:if test="ro:subject">
              <div style="position:relative;clear:both">
            <p><b>Subjects:</b>
            <xsl:if test="ro:subject/@type='anzsrc-for' or ro:subject/@type='anzsrc-seo' or ro:subject/@type='anzsrc-toa'">
                <p>ANZSRC</p>
                <ul class="subjects">
                <xsl:for-each select="ro:subject">      
                    <xsl:sort select="./@type"/>
                    <xsl:if test="@type='anzsrc-for'or @type='anzsrc-seo' or @type='anzsrc-toa'">
                        <xsl:apply-templates select="."/>
                    </xsl:if>
                </xsl:for-each>
                </ul>
            </xsl:if>
            
            <xsl:if test="ro:subject/@type!='anzsrc-for' and ro:subject/@type!='anzsrc-seo' and ro:subject/@type!='anzsrc-toa'">
                <p>Keywords</p> 
                <ul class="subjects">
                <xsl:for-each select="ro:subject">      
                    <xsl:sort select="./@type"/>
                    <xsl:if test="@type!='anzsrc-for'and @type!='anzsrc-seo' and @type!='anzsrc-toa'">
                        <xsl:apply-templates select="."/>
                    </xsl:if>
                </xsl:for-each>
                </ul>
            </xsl:if> 
             </p> 
             </div>  
        </xsl:if>
        <xsl:choose>
            <xsl:when test="ro:citationInfo">
                <div id="citation" style="position:relative;clear:both;">
                <xsl:choose>
                    <xsl:when test="ro:citationInfo/ro:citationMetadata">
                        <b>How to Cite this Collection:</b><br />
                       <!--   <a title="Add this article to your Mendeley library" target="_blank">
                       <xsl:attribute name="href">
                        http://www.mendeley.com/import/?url=<xsl:value-of select="ro:citationInfo/ro:citationMetadata/ro:url"/>
                        </xsl:attribute> 
                        <img src="http://www.mendeley.com/graphics/mendeley.png"/></a> -->
                        <xsl:apply-templates select="ro:citationInfo/ro:citationMetadata"/> 
                    </xsl:when>
                    <xsl:when test="ro:citationInfo/ro:fullCitation">
                        <b>How to Cite this Collection:</b><br />
                        <xsl:apply-templates select="ro:citationInfo/ro:fullCitation"/>
                    </xsl:when>
                    <xsl:otherwise >
                    <!-- If we have found an empty citation element build the openURL using the object display title -->
                        <span class="Z3988">    
                        <xsl:attribute name="title">
                        <xsl:text>ctx_ver=Z39.88-2004</xsl:text>
                        <xsl:text>&amp;rft_val_fmt=info%3Aofi%2Ffmt%3Akev%3Amtx%3Adc</xsl:text>
                        <xsl:text>&amp;rfr_id=info%3Asid%2FANDS</xsl:text>
                        <xsl:text>&amp;rft.title=</xsl:text><xsl:value-of select="//ro:displayTitle"/>
                        <xsl:text>&amp;rft.description=</xsl:text><xsl:value-of select="//ro:displayTitle"/>
                        </xsl:attribute>
                        </span><span class="Z3988"></span>      
                    </xsl:otherwise>                        
                </xsl:choose>   
                </div>          
            </xsl:when>

         </xsl:choose>
            
        <xsl:if test="ro:identifier">
            <div style="position:relative;clear:both;"><p><b>Identifiers:</b></p>
           	 	<div id="identifiers">
     
    		
    	<xsl:apply-templates select="ro:identifier[@type='doi']" mode = "doi"/>
    	<xsl:apply-templates select="ro:identifier[@type='ark']" mode = "ark"/>    	
     	<xsl:apply-templates select="ro:identifier[@type='AU-ANL:PEAU']" mode = "nla"/>  
     	<xsl:apply-templates select="ro:identifier[@type='handle']" mode = "handle"/>   
     	<xsl:apply-templates select="ro:identifier[@type='purl']" mode = "purl"/>
    	<xsl:apply-templates select="ro:identifier[@type='uri']" mode = "uri"/> 
 		<xsl:apply-templates select="ro:identifier[not(@type =  'doi' or @type =  'ark' or @type =  'AU-ANL:PEAU' or @type =  'handle' or @type =  'purl' or @type =  'uri')]" mode="other"/>											   	
   		
	
            	</div>
            </div>
        </xsl:if>   
        <!--div style="position:relative;clear:both;" class="no_print">
          	<p>	<a>
          		<xsl:attribute name="href"><xsl:value-of select="$orca_view"/>?key=<xsl:value-of select="$key"/></xsl:attribute>
          		View the complete record in the ANDS Collections Registry
          		</a>
          	</p>  
        </div-->  
  
        </div>
     
        <!--  we will now transform the rights handside stuff -->
  		<div id="right">
	      
 
                         	
		<xsl:if test="ro:location/ro:address/ro:electronic/@type='url' 
		or ro:rights or ro:location/ro:address/ro:electronic/@type='email'  or ro:location/ro:address/ro:physical">		
		<div class="right-box">
			<h2>Access</h2>
			<div class="limitHeight300">
		 	<xsl:if test="ro:location/ro:address/ro:electronic/@type='url'">
				<p><xsl:apply-templates select="ro:location/ro:address/ro:electronic"/></p>	
	 		</xsl:if>
	 		
	 		 <xsl:if test="extRif:rights or ro:rights">
					<h3>Rights</h3>	
			</xsl:if>
	 		 <xsl:if test="extRif:rights[@type='licence']">
					<h3>Licence</h3>	
			</xsl:if>				
			<!-- <xsl:apply-templates select="ro:description[@type = 'accessRights' or @type = 'rights']" mode="right"/>	 -->	
			<xsl:apply-templates select="extRif:rights[@type='licence']"/>	
	
			<xsl:apply-templates select="extRif:rights[@type!='licence']"/>			
		 	<xsl:if test="ro:location/ro:address/ro:electronic/@type='email' or ro:location/ro:address/ro:physical">
		 		<h3>Contacts</h3>
		 		<xsl:if test="ro:location/ro:address/ro:electronic/@type='email'">
					<p><xsl:apply-templates select="ro:location/ro:address/ro:electronic/@type"/></p>	
				</xsl:if>
		 		<xsl:if test="ro:location/ro:address/ro:physical">
					<p>
					<xsl:if test="ro:location/ro:address/ro:physical/ro:addressPart/@type='telephoneNumber'">
						<xsl:for-each select="ro:location/ro:address/ro:physical/ro:addressPart[@type='telephoneNumber']">
							<p>Ph:	<xsl:value-of select="."  disable-output-escaping="yes"></xsl:value-of></p>
						</xsl:for-each>
					</xsl:if>
						
					<xsl:if test="ro:location/ro:address/ro:physical/ro:addressPart/@type='faxNumber'">
						<xsl:for-each select="ro:location/ro:address/ro:physical/ro:addressPart[@type='faxNumber']">
							<p>Fax:<xsl:value-of select="."  disable-output-escaping="yes"></xsl:value-of></p>
						</xsl:for-each>
					</xsl:if>	
					
					<xsl:apply-templates select="ro:location/ro:address/ro:physical"/></p>	
				</xsl:if>		
	 		</xsl:if>			
			                        
			</div>
		</div>					
		</xsl:if>
				
			<!-- NEW CONNECTION -->
			<div class="right-box" id="connectionsRightBox">
			<div id="connectionsInfoBox" class="hide"></div>
			<h2>Connections</h2>
			<div id="connections">
				<img>
				<xsl:attribute name="src"><xsl:value-of select="$base_url"/><xsl:text>/img/ajax-loader.gif</xsl:text></xsl:attribute>
				<xsl:attribute name="class">loading-icon</xsl:attribute>
				<xsl:attribute name="alt">Loading…</xsl:attribute>
				</img>
			</div>
			</div>	
				
				
				
								
		 	<xsl:if test="$objectClass='Collection'">
				<div class="right-box" id="seeAlsoRightBox">
				<div id="infoBox" class="hide"></div>
				<h2>ANDS Suggested Links</h2>
				<div id="seeAlso">
					<img>
					<xsl:attribute name="src"><xsl:value-of select="$base_url"/><xsl:text>/img/ajax-loader.gif</xsl:text></xsl:attribute>
					<xsl:attribute name="class">loading-icon</xsl:attribute>
					<xsl:attribute name="alt">Loading…</xsl:attribute>
					</img>
					</div>
				<div id="seeAlsoDataCite">
					<img>
					<xsl:attribute name="src"><xsl:value-of select="$base_url"/><xsl:text>/img/ajax-loader.gif</xsl:text></xsl:attribute>
					<xsl:attribute name="class">loading-icon</xsl:attribute>
					<xsl:attribute name="alt">Loading…</xsl:attribute>
					</img>
				</div>				
				</div>
			</xsl:if>
			
		 	<xsl:if test="$objectClass='Party'">
				<div class="right-box" id="seeAlso-Identifier">
				<div id="infoBox" class="hide"></div>
				<h2>ANDS Suggested Links</h2>
				<div id="seeAlso-IdentifierBox">
					<img>
					<xsl:attribute name="src"><xsl:value-of select="$base_url"/><xsl:text>/img/ajax-loader.gif</xsl:text></xsl:attribute>
					<xsl:attribute name="class">loading-icon</xsl:attribute>
					<xsl:attribute name="alt">Loading…</xsl:attribute>
					</img>
				</div>
				</div>
			</xsl:if>	
					   
		</div>
       </div>              				
        
    </xsl:template>

<!--  the following templates will format the view page content -->
    <xsl:template match="extRif:displayTitle">   
        <div id="displaytitle">
        	<h1><xsl:value-of select="."/></h1>
        	<xsl:for-each select="//ro:existenceDates">
        		<xsl:if test="./ro:startDate"><xsl:value-of select="./ro:startDate"/></xsl:if> - <xsl:if test="./ro:endDate"><xsl:value-of select="./ro:endDate"/></xsl:if><br/>
			</xsl:for-each>     
		</div>			
		
		<div class="right_icon">
		  <img class="icon-heading">
			<xsl:attribute name="src"><xsl:value-of select="$base_url"/>
			<xsl:text>/img/icon/</xsl:text>
			<xsl:value-of select="$objectClassType"/>
			<xsl:text>_32.png</xsl:text></xsl:attribute>
			<xsl:attribute name="alt"><xsl:value-of select="$objectClassType"/></xsl:attribute>
		  </img>
		</div>   
    </xsl:template>
    
    <xsl:template match="extRif:displayLogo">   
        <div>
        <img id="party_logo" style="max-width:130px;">
        	<xsl:attribute name="src"><xsl:value-of select="."/></xsl:attribute>
        	<xsl:attribute name="alt">Party Logo</xsl:attribute>
        </img>
		</div>    
    </xsl:template> 
    
    <xsl:template match="ro:name[@type='alternative']/ro:displayTitle">   
        <p class="alt_displayTitle"><xsl:value-of select="."/></p>
    </xsl:template> 
        	    	 	 	
    <xsl:template match="ro:title">
        <xsl:value-of select="."/>    
    </xsl:template>

    <xsl:template match="ro:relatedInfo/ro:notes">
        <xsl:value-of select="."/>   
    </xsl:template> 
    

    <xsl:template match="ro:coverage/extRif:spatial/extRif:coords">
      <xsl:if test="not(./@type) or (./@type!= 'text' and ./@type!= 'dcmiPoint')">
        <p class="coverage" name="{@type}"><xsl:value-of select="."/></p>
      </xsl:if>
    </xsl:template>
     <xsl:template match="ro:location/extRif:spatial/extRif:coords">
      <xsl:if test="not(./@type) or (./@type!= 'text' and ./@type!= 'dcmiPoint')">
        <p class="coverage" name="{@type}"><xsl:value-of select="."/></p>
      </xsl:if>
    </xsl:template>   
    <xsl:template match="extRif:center">
        <p class="spatial_coverage_center"><xsl:value-of select="."/></p>
    </xsl:template>
    
    <xsl:template match="ro:date">  
        <xsl:if test="./@type = 'dateFrom'">
            From 
        </xsl:if>
        <xsl:if test="./@type = 'dateTo'">
            To  
        </xsl:if>       
        <xsl:value-of select="."/>          
    </xsl:template> 
      <xsl:template match="ro:location[@dateFrom!=''] | ro:location[@dateTo!='']">  
        <xsl:if test="./@dateFrom != ''">
            From         <xsl:value-of select="./@dateFrom"/>     
        </xsl:if>
        <xsl:if test="./@dateTo != ''">
            To         <xsl:value-of select="./@dateTo"/>   
        </xsl:if>       
     
    </xsl:template>   
    <xsl:template match="ro:subject">   
            <li><a href="javascript:void(0);" class="subjectFilter" id="{@extRif:resolvedValue}" title="{.}"><xsl:value-of select="@extRif:resolvedValue"/></a></li>       
    </xsl:template>
    
   <xsl:template match="ro:relatedInfo">
        <p>

   		 <xsl:if test="./ro:title">
         	<xsl:value-of select="./ro:title"/><br />
         </xsl:if>
   		<xsl:apply-templates select="./ro:identifier[@type='doi']" mode = "doi"/>
    	<xsl:apply-templates select="./ro:identifier[@type='ark']" mode = "ark"/>    	
     	<xsl:apply-templates select="./ro:identifier[@type='AU-ANL:PEAU']" mode = "nla"/>  
     	<xsl:apply-templates select="./ro:identifier[@type='handle']" mode = "handle"/>   
     	<xsl:apply-templates select="./ro:identifier[@type='purl']" mode = "purl"/>
    	<xsl:apply-templates select="./ro:identifier[@type='uri']" mode = "uri"/> 
 		<xsl:apply-templates select="./ro:identifier[not(@type =  'doi' or @type =  'ark' or @type =  'AU-ANL:PEAU' or @type =  'handle' or @type =  'purl' or @type =  'uri')]" mode="other"/>			            	
                         
        <xsl:if test="./ro:notes">
             <xsl:apply-templates select="./ro:notes"/>
        </xsl:if>
        </p>        
    </xsl:template>
  <xsl:template match="ro:identifier" mode="ark">
    <p>
ARK: 
	<xsl:variable name="theidentifier">    			
	<xsl:choose>	
    	<xsl:when test="string-length(substring-after(.,'http://'))>0">
     		<xsl:value-of select="(substring-after(.,'http://'))"/>
     	</xsl:when>	    							

     	<xsl:otherwise>
     		<xsl:value-of select="."/>
     	</xsl:otherwise>		
	</xsl:choose>
	</xsl:variable>  
	<xsl:if test="string-length(substring-after(.,'/ark:/'))>0">    			     
	<a>
        <xsl:attribute name="class">identifier</xsl:attribute>
    	<xsl:attribute name="href"><xsl:text>http://</xsl:text> <xsl:value-of select="$theidentifier"/></xsl:attribute>
    	<xsl:attribute name="title"><xsl:text>Resolve this ARK identifier</xsl:text></xsl:attribute>    				
    	<xsl:value-of select="."/>
	</a>
	</xsl:if>
	<xsl:if test="string-length(substring-after(.,'/ark:/'))&lt;1">
		<a class="identifier"><xsl:value-of select="."/></a>
	</xsl:if>
    </p>	 
</xsl:template>
 <xsl:template match="ro:identifier" mode="nla">
    <p>
 NLA: 
    <xsl:variable name="theidentifier">    			
	<xsl:choose>				
    	<xsl:when test="string-length(substring-after(.,'nla.gov.au/'))>0">
     		<xsl:value-of select="substring-after(.,'nla.gov.au/')"/>
     	</xsl:when>		     	
     	<xsl:otherwise>
     		<xsl:value-of select="."/>
     	</xsl:otherwise>		
	</xsl:choose>
	</xsl:variable>  
	<xsl:if test="string-length(substring-after(.,'nla.party'))>0">		
		<a>
            <xsl:attribute name="class">identifier</xsl:attribute>
    		<xsl:attribute name="href"><xsl:text>http://nla.gov.au/</xsl:text> <xsl:value-of select="$theidentifier"/></xsl:attribute>
    		<xsl:attribute name="title"><xsl:text>View the record for this party in Trove</xsl:text></xsl:attribute>    				
    		<xsl:value-of select="."/>
		</a>
	</xsl:if> 
	<xsl:if test="string-length(substring-after(.,'nla.party'))&lt;1">		
		<a class="identifier"><xsl:value-of select="."/></a>
    </xsl:if> 
   </p>
  				
 </xsl:template>
 <xsl:template match="ro:identifier" mode="doi">   		
    <p>			
DOI: 
    <xsl:variable name="theidentifier">    			
	<xsl:choose>				
    	<xsl:when test="string-length(substring-after(.,'doi.org/'))>0">
     		<xsl:value-of select="substring-after(.,'doi.org/')"/>
     	</xsl:when>		     	
     	<xsl:otherwise>
     		<xsl:value-of select="."/>
     	</xsl:otherwise>		
	</xsl:choose>
	</xsl:variable> 


	<xsl:if test="string-length(substring-after(.,'10.'))>0">		
    <a>
        <xsl:attribute name="class">identifier</xsl:attribute>
    	<xsl:attribute name="href"><xsl:text>http://dx.doi.org/</xsl:text> <xsl:value-of select="$theidentifier"/></xsl:attribute>
    	<xsl:attribute name="title"><xsl:text>Resolve this DOI</xsl:text></xsl:attribute>    				
    	<xsl:value-of select="."/>
	</a>
	</xsl:if>

  	<xsl:if test="string-length(substring-after(.,'10.'))&lt;1">		
   		<a class="identifier"><xsl:value-of select="."/></a>
    </xsl:if> 	
    </p>			 			

    			
 </xsl:template>
 <xsl:template match="ro:identifier" mode="handle">      
 <p>			
    Handle: 
	<xsl:variable name="theidentifier">    			
	<xsl:choose>
	    	<xsl:when test="string-length(substring-after(.,'hdl:'))>0">
     		<xsl:text>http://hdl.handle.net/</xsl:text><xsl:value-of select="substring-after(.,'hdl:')"/>
     	</xsl:when> 
	    	<xsl:when test="string-length(substring-after(.,'hdl.handle.net/'))>0">
     		<xsl:text>http://hdl.handle.net/</xsl:text><xsl:value-of select="substring-after(.,'hdl.handle.net/')"/>
     	</xsl:when>   			     	     				
    	<xsl:when test="string-length(substring-after(.,'http:'))>0">
     		<xsl:text></xsl:text><xsl:value-of select="."/>
     	</xsl:when>    										     	
     	<xsl:otherwise>
     		<xsl:text>http://hdl.handle.net/</xsl:text><xsl:value-of select="."/>
     	</xsl:otherwise>		
	</xsl:choose>
    </xsl:variable>
    			     
		<a>
            <xsl:attribute name="class">identifier</xsl:attribute>
			<xsl:attribute name="href"> <xsl:value-of select="$theidentifier"/></xsl:attribute>
			<xsl:attribute name="title"><xsl:text>Resolve this handle</xsl:text></xsl:attribute>    				
			<xsl:value-of select="."/>
		</a> 
   </p>
 </xsl:template>
 <xsl:template match="ro:identifier" mode="purl">     
    <p>			
 	PURL: 
    <xsl:variable name="theidentifier">    			
    <xsl:choose>				
    	<xsl:when test="string-length(substring-after(.,'purl.org/'))>0">
    		<a class="identifier"><xsl:value-of select="substring-after(.,'purl.org/')"/></a>
    	</xsl:when>		     	
    	<xsl:otherwise>
    		<a class="identifier"><xsl:value-of select="."/></a>
    	</xsl:otherwise>		
    </xsl:choose>
 	</xsl:variable>   	   			
    <a>
        <xsl:attribute name="class">identifier</xsl:attribute>
        <xsl:attribute name="href"><xsl:text>http://purl.org/</xsl:text> <xsl:value-of select="$theidentifier"/></xsl:attribute>
        <xsl:attribute name="title"><xsl:text>Resolve this purl identifier</xsl:text></xsl:attribute>    				
        <xsl:value-of select="."/>
    </a> 
    </p>
  </xsl:template>
  <xsl:template match="ro:identifier" mode="uri">    
  <p> 			
 	URI: 
   <xsl:variable name="theidentifier">    			
    <xsl:choose>				
    	<xsl:when test="string-length(substring-after(.,'http'))>0">
    		<a class="identifier"><xsl:value-of select="."/></a>
    	</xsl:when>		     	
    	<xsl:otherwise>
            <a>
                <xsl:attribute name="class">identifier</xsl:attribute>
                <xsl:attribute name="href"><xsl:value-of select="."/></xsl:attribute>
                <xsl:value-of select="."/>
            </a>
    	</xsl:otherwise>		
    </xsl:choose>
 	</xsl:variable>   	        			
    <a>
    <xsl:attribute name="href"><xsl:value-of select="$theidentifier"/></xsl:attribute>
    <xsl:attribute name="title"><xsl:text>Resolve this uri</xsl:text></xsl:attribute>    				
    <xsl:value-of select="."/>  
    </a>   		 
   </p>
  </xsl:template> 
 <xsl:template match="ro:identifier" mode="other">   
 <p>  			 			 	    			 			
   <!--  <xsl:attribute name="name"><xsl:value-of select="./@type"/></xsl:attribute>  -->
   <xsl:choose>
   <xsl:when test="./@type='arc' or ./@type='abn' or ./@type='isil'">
 		<xsl:value-of select="translate(./@type,'abcdefghijklmnopqrstuvwxyz','ABCDEFGHIJKLMNOPQRSTUVWXYZ')"/>: <xsl:value-of select="."/>  
   </xsl:when>
    <xsl:when test="./@type='local'">
 		Local: <a class="identifier"><xsl:value-of select="."/></a>
   </xsl:when>  
   <xsl:otherwise>
	<xsl:value-of select="./@type"/>: <a class="identifier"><xsl:value-of select="."/></a>
	</xsl:otherwise>
	</xsl:choose>
	</p>
  </xsl:template>  
  
    <xsl:template match="ro:citationInfo/ro:fullCitation">
        <p><xsl:value-of select="."/></p>
        <span class="Z3988">    
        <xsl:attribute name="title">
        <xsl:text>ctx_ver=Z39.88-2004</xsl:text>
        <xsl:text>&amp;rft_val_fmt=info%3Aofi%2Ffmt%3Akev%3Amtx%3Adc</xsl:text>
        <xsl:text>&amp;rfr_id=info%3Asid%2FANDS</xsl:text>
        <xsl:text>&amp;rft.title=</xsl:text><xsl:value-of select="//ro:displayTitle"/>
        <xsl:text>&amp;rft.description=</xsl:text><xsl:value-of select="."/>
        </xsl:attribute>
        </span>
                <span class="Z3988">
        </span>     
    </xsl:template>
                
    <xsl:template match="ro:citationInfo/ro:citationMetadata">
     <p>
        <xsl:if test="./ro:contributor">
            <xsl:apply-templates select="ro:contributor"/>
        </xsl:if>
        <xsl:if test="./ro:date">
        (
            <xsl:apply-templates select="//ro:citationMetadata/ro:date"/>               
        )           
        </xsl:if>   
        <xsl:if test="./ro:title != ''">
            <xsl:text> </xsl:text>
            <xsl:value-of select="./ro:title"/>.
        </xsl:if>
        <xsl:if test="./ro:edition != ''">
            <xsl:text> </xsl:text>
            <xsl:value-of select="./ro:edition"/>.
        </xsl:if>   
        <xsl:if test="./ro:placePublished != ''">
            <xsl:text> </xsl:text>      
            <xsl:value-of select="./ro:placePublished"/>.
        </xsl:if>
        <xsl:if test="./ro:publisher != ''">
            <xsl:text> </xsl:text>      
            <xsl:value-of select="./ro:publisher"/>.
        </xsl:if>        
        <xsl:if test="./ro:url != ''">
            <xsl:text> </xsl:text>      
            <xsl:value-of select="./ro:url"/>
        </xsl:if>
        <xsl:if test="./ro:context != ''">
            <xsl:text> </xsl:text>      
            , <xsl:value-of select="./ro:context"/>
        </xsl:if>
        <xsl:if test="./ro:identifier != ''">,         
        	<xsl:apply-templates select="./ro:identifier[@type = 'doi']"  mode="doi"/>	
         	<xsl:apply-templates select="./ro:identifier[@type = 'uri']"  mode="uri"/>	 
         	<xsl:apply-templates select="./ro:identifier[@type = 'URL']"  mode="uri"/>	
           	<xsl:apply-templates select="./ro:identifier[@type = 'url']"  mode="uri"/>	  
            <xsl:apply-templates select="./ro:identifier[@type = 'purl']"  mode="purl"/>	  
            <xsl:apply-templates select="./ro:identifier[@type = 'handle']"  mode="handle"/>	
            <xsl:apply-templates select="./ro:identifier[@type = 'AU-ANL:PEAU']"  mode="nla"/>
            <xsl:apply-templates select="./ro:identifier[@type = 'ark']"  mode="ark"/>  
            <xsl:apply-templates select="./ro:identifier[@type != 'doi' and @type != 'uri' and @type != 'URL' and @type != 'url' and @type != 'purl' and @type != 'handle' and @type != 'AU-ANL:PEAU' and @type != 'ark']"  mode="other"/>				
        </xsl:if>
     	</p>
     	<span class="Z3988">   
        	<xsl:attribute name="title">
        	<xsl:text>ctx_ver=Z39.88-2004</xsl:text>
        	<xsl:text>&amp;rft_val_fmt=info%3Aofi%2Ffmt%3Akev%3Amtx%3Adc</xsl:text>
        	<xsl:text>&amp;rfr_id=info%3Asid%2FANDS</xsl:text>
        	<xsl:text>&amp;rft.contributor=</xsl:text><xsl:apply-templates select="ro:contributor"/>
        	<xsl:text>&amp;rft.title=</xsl:text><xsl:value-of select="./ro:title"/> 
        	<xsl:text>&amp;rft.place=</xsl:text><xsl:value-of select="./ro:placePublished"/>
        	<xsl:text>&amp;rft_id=</xsl:text><xsl:value-of select="./ro:url"/>
        	<xsl:text>&amp;rft.edition=</xsl:text><xsl:value-of select="./ro:edition"/>.
        	<xsl:text>&amp;rft.description=</xsl:text><xsl:value-of select="./ro:context"/>
        	</xsl:attribute>
    	</span>
    	<span class="Z3988">
    	</span>                                                     
    </xsl:template> 
    
    <xsl:template match="ro:contributor">       
        <xsl:if test="./ro:namePart/@type='family'">
            <xsl:value-of select="./ro:namePart[@type='family']"/>,
        </xsl:if>
        <xsl:if test="./ro:namePart/@type='given'">
            <xsl:value-of select="./ro:namePart[@type='given']"/>.
        </xsl:if>
                <xsl:if test="./ro:namePart/@type='initial' and not(./ro:namePart/@type='given')">
            <xsl:value-of select="./ro:namePart[@type='initial']"/>.
        </xsl:if>   
    </xsl:template> 

    <xsl:template match="//ro:citationInfo/ro:citationMetadata/ro:date">
        <xsl:if test="position()>1">
            <xsl:text>,</xsl:text>
        </xsl:if>       
        <xsl:value-of select="."/> 
    </xsl:template> 
    
	<xsl:template match="ro:location/ro:address/ro:electronic">
		<xsl:if test="./@type='url'">
		<xsl:variable name="url">
		
		<xsl:choose>
		<xsl:when test="string-length(.)>30">
		<xsl:value-of select="substring(.,0,30)"/>...
		</xsl:when>
		<xsl:otherwise>
		<xsl:value-of select="."/>
		</xsl:otherwise>
		</xsl:choose>
		</xsl:variable>	
			<a><xsl:attribute name="href"><xsl:value-of select="."/></xsl:attribute><xsl:attribute name="target">_blank</xsl:attribute><xsl:value-of select="$url"/></a><br />
		</xsl:if>		
	</xsl:template>
	
	<xsl:template match="ro:location/ro:address/ro:physical">
		<p>
			<xsl:choose>
				<xsl:when test = "./ro:addressPart or ./ro:addressPart!=''">
				
						<xsl:apply-templates select="./ro:addressPart[@type='fullname']"/>	
						<xsl:apply-templates select="./ro:addressPart[@type='organizationname']"/>	
						<xsl:apply-templates select="./ro:addressPart[@type='buildingorpropertyname']"/>		
						<xsl:apply-templates select="./ro:addressPart[@type='flatorunitnumber']"/>		
						<xsl:apply-templates select="./ro:addressPart[@type='floororlevelnumber']"/>	
						<xsl:apply-templates select="./ro:addressPart[@type='lotnumber']"/>	
						<xsl:apply-templates select="./ro:addressPart[@type='housenumber']"/>		
						<xsl:apply-templates select="./ro:addressPart[@type='streetname']"/>		
						<xsl:apply-templates select="./ro:addressPart[@type='postaldeliverynumberprefix']"/>		
						<xsl:apply-templates select="./ro:addressPart[@type='postaldeliverynumbervalue']"/>		
						<xsl:apply-templates select="./ro:addressPart[@type='postaldeliverynumbersuffix']"/>	
						<xsl:apply-templates select="./ro:addressPart[@type='addressline']"/>		
						<xsl:apply-templates select="./ro:addressPart[@type='suburborplaceorlocality']"/>		
						<xsl:apply-templates select="./ro:addressPart[@type='stateorterritory']"/>	
						<xsl:apply-templates select="./ro:addressPart[@type='postcode']"/>	
						<xsl:apply-templates select="./ro:addressPart[@type='country']"/>		
						<xsl:apply-templates select="./ro:addressPart[@type='locationdescriptor']"/>
						<xsl:apply-templates select="./ro:addressPart[@type='deliverypointidentifier']"/>	
												
						<xsl:apply-templates select="./ro:addressPart[not(@type='organizationname' or @type='fullname' or @type='buildingorpropertyname' or @type='flatorunitnumber' or @type='floororlevelnumber' or @type='lotnumber' or @type='housenumber' or @type='streetname' or @type='postaldeliverynumberprefix' or @type='postaldeliverynumbervalue' or @type='postaldeliverynumbersuffix' or @type='addressline' or @type='suburborplaceorlocality' or @type='stateorterritory' or @type='country' or @type='locationdescriptor' or @type='deliverypointidentifier' or @type='postcode' or @type='telephoneNumber' or @type='faxNumber' )]"/>	
						<!--xsl:apply-templates select="./ro:addressPart[not(@type='addressLine') or @type!='deliveryPointIdentifier' or @type='locationDescriptor' or @type='country' or @type='stateOrTerritory' or @type='suburbOrPlaceOrLocality' or @type='suburbOrPlaceOrLocality' or @type='addressLine' or @type='postalDeliveryNumberSuffix])"/-->
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="." disable-output-escaping="yes"/><br />			
				</xsl:otherwise>
			</xsl:choose>	
		</p>
	</xsl:template>	

	<xsl:template match="ro:addressPart">			
			<xsl:value-of select="." disable-output-escaping="yes"/><br />
	</xsl:template> 
		

	<xsl:template match="extRif:rights[@type!='licence']">

			<xsl:if test="./@type='rights'"><h4>Rights statement</h4></xsl:if>
			<xsl:if test="./@type='accessRights'"><h4>Access rights</h4></xsl:if>
			<!-- ><xsl:if test="./@type='licence'"><h4>Licence</h4></xsl:if>	-->			
			<p class="rights"><xsl:value-of select="." disable-output-escaping="yes"/>
			<xsl:if test="./@rightsUri"><p>
				<a target="_blank">
				<xsl:attribute name="href"><xsl:value-of select="./@rightsUri"/></xsl:attribute><xsl:value-of select="./@rightsUri"/></a></p>
			</xsl:if>
			</p>	

	</xsl:template>	
	<xsl:template match="extRif:rights[@type='licence']">
		<p class="rights">
			<xsl:if test="string-length(substring-after(./@licence_type,'CC-'))>0">
    		 	<img id="licence_logo" style="width:130px;">
				<xsl:attribute name="src"><xsl:value-of select="$base_url"/>
				<xsl:text>/img/</xsl:text>
				<xsl:value-of select="./@licence_type"/>
				<xsl:text>.png</xsl:text></xsl:attribute>
				<xsl:attribute name="alt"><xsl:value-of select="./@licence_type"/></xsl:attribute>
		  		</img>
    		</xsl:if>
    		<xsl:if test="string-length(substring-after(./@licence_type,'CC-'))=0">	   
    			<xsl:if test="./@licence_type='Unknown/Other' and .=''"><p>Unknown</p></xsl:if>
    			<xsl:if test="./@licence_type!='Unknown/Other'"><p><xsl:value-of select="./@licence_type"/></p></xsl:if>
				<!--  <xsl:value-of select="./@licence_type"/> -->
			</xsl:if>
			<xsl:if test="."><p><xsl:value-of select="."/></p></xsl:if>
			<xsl:if test="./@rightsUri"><p>
				<a target="_blank">
				<xsl:attribute name="href"><xsl:value-of select="./@rightsUri"/></xsl:attribute><xsl:value-of select="./@rightsUri"/></a></p>
			</xsl:if>						
		</p>		
	</xsl:template>

	
	<xsl:template match="extRif:description" mode="content">     
        <div><xsl:attribute name="class"><xsl:value-of select="@type"/></xsl:attribute>
           <p><xsl:value-of select="." disable-output-escaping="yes"/></p>
        </div>
	</xsl:template> 
	
	
	
	<xsl:template match="ro:location/ro:address/ro:electronic/@type">		
		<xsl:if test=".='email'">	
	  		<xsl:value-of select=".."/><br />
		</xsl:if>				
	</xsl:template>  
	      
</xsl:stylesheet>
