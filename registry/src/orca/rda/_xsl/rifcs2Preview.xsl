<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:ro="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:extRif="http://ands.org.au/standards/rif-cs/extendedRegistryObjects" exclude-result-prefixes="ro extRif">
    <xsl:output method="html" encoding="UTF-8" indent="yes" omit-xml-declaration="yes"/>
    <xsl:strip-space elements="*"/>
    <xsl:param name="dataSource" select="//ro:originatingSource"/>
    <xsl:param name="dateCreated"/>
    <xsl:param name="base_url" select="'http://devl.ands.org.au/workareas/lizrda/view/'"/>  
    <xsl:param name="orca_home"/> 
	<xsl:variable name="roKey" select="ro:registryObjects/ro:registryObject/ro:key"/>
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
    	<div id="top">
			<ul id="breadcrumb">
				<li><a href="#" class="crumb">Home</a></li>
				<li><a href="#" class="crumb"><xsl:value-of select="./@group"/></a></li>
				<li><a href="#" class="crumb"><xsl:value-of select="$objectClass"/></a></li>
				<li><xsl:value-of select="//ro:displayTitle"/></li>
				<img id="print_icon">
				<xsl:attribute name="src">
				<xsl:value-of select="$base_url"/>
				<xsl:text>img/</xsl:text>
				<xsl:text>1313027722_print.png</xsl:text></xsl:attribute>
				</img>
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
      	<div id="item-view-inner" class="clearfix">
	
		<div id="left">           
 		<xsl:choose>
	        <xsl:when test="ro:displayTitle!=''">
	        	<xsl:apply-templates select="ro:displayTitle"/> 
	        </xsl:when>
	        <xsl:when test="ro:name[@type = 'primary']">
	        	<xsl:apply-templates select="ro:name[@type = 'primary']"/> 
	        	<xsl:apply-templates select="ro:name[@type = 'alternative']"/>
	        </xsl:when>
	        <xsl:when test="ro:name">
	        	<xsl:apply-templates select="ro:name[@type != 'alternative']"/> 
	        	<xsl:apply-templates select="ro:name[@type = 'alternative']"/>
	        </xsl:when> 
        </xsl:choose> 
                <xsl:if test="ro:existenceDates">
            <xsl:apply-templates select="ro:existenceDates"/> 
         </xsl:if>      
        <xsl:apply-templates select="ro:description[@type = 'logo']"/>

        <xsl:if test="ro:description">
            <div class="descriptions" style="position:relative;clear:both;">    				
				<xsl:apply-templates select="ro:description[@type= 'brief']" mode="content"/>
				<xsl:apply-templates select="ro:description[@type= 'full']" mode="content"/>
				<xsl:apply-templates select="ro:description[@type= 'significanceStatement']" mode="content"/>		
				<xsl:apply-templates select="ro:description[@type= 'notes']" mode="content"/>	
				<xsl:apply-templates select="ro:description[not(@type =  'notes' or @type =  'significanceStatement' or @type =  'full' or @type =  'brief' or @type =  'logo' or @type =  'rights' or @type =  'accessRights')]" mode="content"/>											
				
            </div>
        </xsl:if>
        <a href="javascript:void(0);" class="showall_descriptions hide">More...</a>
    
        <xsl:if test="ro:relatedInfo">
        <p><b>More Information:</b> </p>
            <xsl:apply-templates select="ro:relatedInfo"/> 
         </xsl:if>
                        

        
       <xsl:if test="ro:coverage or ro:location/ro:spatial">
            <xsl:variable name="coverageLabel">
            <xsl:choose>
            <xsl:when test="ro:coverage/ro:spatial and ro:location/ro:spatial">
            <xsl:text>Coverage And Location:</xsl:text>
            </xsl:when>
            <xsl:when test="ro:location/ro:spatial">
            <xsl:text>Location:</xsl:text>
            </xsl:when>
             <xsl:when test="ro:coverage/ro:spatial">
            <xsl:text>Coverage:</xsl:text>
            </xsl:when>
            
            </xsl:choose>
            </xsl:variable>
            <p><b><xsl:value-of select="$coverageLabel"/></b></p>
            <xsl:variable name="needMap">   
                <xsl:for-each select="ro:coverage/ro:spatial"> 
             	<xsl:if test="not(./@type) or (./@type!='text' and ./@type!='dcmiPoint')">        	
                      <xsl:text>yes</xsl:text>
               </xsl:if>

               </xsl:for-each>  
                    
             	<xsl:for-each select="ro:location/ro:spatial"> 
             	<xsl:if test="not(./@type) or (./@type!='text' and ./@type!='dcmiPoint')">        	
                      <xsl:text>yes</xsl:text>
               </xsl:if>            
               </xsl:for-each>                 

        	</xsl:variable>
        	
             <xsl:if test="ro:coverage/ro:spatial | ro:location/ro:spatial">
               	 	<xsl:apply-templates select="ro:coverage/ro:spatial | ro:location/ro:spatial"/>
               	 	<xsl:if test="$needMap!=''">
                  		<div id="spatial_coverage_map"></div>
                  	</xsl:if>
            </xsl:if>  
                      
            <xsl:if test="ro:coverage/ro:center">
                <xsl:apply-templates select="ro:coverage/ro:center"/>
            </xsl:if>   
         
            <xsl:if test="ro:coverage/ro:temporal/ro:date">
                <p>Time Period:<br />
                <xsl:apply-templates select="ro:coverage/ro:temporal/ro:date"/> 
                </p>    
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
    	<p> 	
    	<xsl:apply-templates select="ro:identifier[@type='doi']" mode = "doi"/>
    	<xsl:apply-templates select="ro:identifier[@type='ark']" mode = "ark"/>    	
     	<xsl:apply-templates select="ro:identifier[@type='AU-ANL:PEAU']" mode = "nla"/>  
     	<xsl:apply-templates select="ro:identifier[@type='handle']" mode = "handle"/>   
     	<xsl:apply-templates select="ro:identifier[@type='purl']" mode = "purl"/>
    	<xsl:apply-templates select="ro:identifier[@type='uri']" mode = "uri"/> 
 		<xsl:apply-templates select="ro:identifier[not(@type =  'doi' or @type =  'ark' or @type =  'AU-ANL:PEAU' or @type =  'handle' or @type =  'purl' or @type =  'uri')]" mode="other"/>											   	
   		</p>
	
            	</div>
            </div>
        </xsl:if>   
  
  
        </div>
     
        <!--  we will now transform the rights handside stuff -->
  		<div id="right">
	      
        <!-- AddToAny BEGIN   
        <p>
        <div class="a2a_kit a2a_default_style no_print" style="position:relative;clear:both;">
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
        a2a_config.linkname = "Research Data Australia";
        </script>
        <script type="text/javascript" src="http://static.addtoany.com/menu/page.js"></script>
        </p>
        < AddToAny END -->  

                         	
		<xsl:if test="ro:location/ro:address/ro:electronic/@type='url' 
		or ro:rights or ro:location/ro:address/ro:electronic/@type='email'  or ro:location/ro:address/ro:physical">		
		<div class="right-box">
			<h2>Access</h2>
			<div class="limitHeight300">
				 	 <xsl:if test="ro:rights/ro:accessrights">
				<h3>Access Rights</h3>	
			</xsl:if>	
			<xsl:apply-templates select="ro:rights/ro:accessrights"/>
		 	<xsl:if test="ro:location/ro:address/ro:electronic/@type='url'">
				<p><xsl:apply-templates select="ro:location/ro:address/ro:electronic"/></p>	
	 		</xsl:if>
	 		 <xsl:if test="ro:description/@type='accessRights' or ro:rights or ro:description/@type='rights'">
					<h3>Rights</h3>	
			</xsl:if>
				
		
				
	 		 <xsl:if test="ro:rights/ro:licence">
				<h3>Licence</h3>	
			</xsl:if>		
			<xsl:apply-templates select="ro:rights/ro:licence"/>	
			
	 		<xsl:if test="ro:rights/ro:rightsStatement">
				<h3>Rights statement</h3>	
			</xsl:if>
			<xsl:apply-templates select="ro:rights/ro:rightsStatement"/>
					
			<xsl:if test="ro:rights/ro:accessRights">
				<h3>Access Rights</h3>	
			</xsl:if>
			<xsl:apply-templates select="ro:rights/ro:accessRights"/>
			
        	<xsl:if test="ro:description">	
				<xsl:apply-templates select="ro:description[@type= 'accessRights']" mode="right"/>
				<xsl:apply-templates select="ro:description[@type= 'rights']" mode="right"/>
			</xsl:if>
			
							
		 	<xsl:if test="ro:location/ro:address/ro:electronic/@type='email' or ro:location/ro:address/ro:physical">
		 		<h3>Contacts</h3>
		 		<xsl:if test="ro:location/ro:address/ro:electronic/@type='email'">
					<p><xsl:apply-templates select="ro:location/ro:address/ro:electronic/@type"/></p>	
				</xsl:if>
			 	<xsl:if test="ro:location/ro:address/ro:physical/@type='telephoneNumber'">
					<p><xsl:apply-templates select="ro:location/ro:address/ro:physical"/></p>	
				</xsl:if>				
		 		<xsl:if test="ro:location/ro:address/ro:physical">
					<p><xsl:apply-templates select="ro:location/ro:address/ro:physical"/></p>	
				</xsl:if>				
	 		</xsl:if>			
			                        
			</div>
		</div>					
		</xsl:if>
		
		<xsl:if test="ro:relatedObject">
		<div class="right-box">
			<h2>Connections</h2>
			<div>
			<xsl:if test="ro:relatedObject[ro:key/@roclass = 'Collection'] or ro:relatedObject[ro:key/@roclass = 'collection']">
				<h3>Collections</h3>
				<ul>
					<xsl:apply-templates select="ro:relatedObject[ro:key/@roclass = 'Collection'] | ro:relatedObject[ro:key/@roclass = 'collection']"/>		
					</ul>
			</xsl:if>	
			<xsl:if test="ro:relatedObject[ro:key/@roclass = 'Party'] or ro:relatedObject[ro:key/@roclass = 'party']">
				<h3>Researchers / Research Groups</h3>
				<ul>
					<xsl:apply-templates select="ro:relatedObject[ro:key/@roclass = 'Party'] | ro:relatedObject[ro:key/@roclass = 'party']"/>			
				</ul>
			</xsl:if>
			<xsl:if test="ro:relatedObject[ro:key/@roclass = 'Activity'] or ro:relatedObject[ro:key/@roclass = 'activity']">
				<h3>Activities</h3>
				<ul>
					<xsl:apply-templates select="ro:relatedObject[ro:key/@roclass = 'Activity'] | ro:relatedObject[ro:key/@roclass = 'activity']"/>			
				</ul>
			</xsl:if>
			<xsl:if test="ro:relatedObject[ro:key/@roclass = 'Service'] or ro:relatedObject[ro:key/@roclass = 'service']">
				<h3>Services</h3>
				<ul>
					<xsl:apply-templates select="ro:relatedObject[ro:key/@roclass = 'Service'] | ro:relatedObject[ro:key/@roclass = 'service']"/>			
				</ul>
			</xsl:if>
			<xsl:if test="ro:relatedObject[ro:key/@roclass = ''] or ro:relatedObject[not(ro:key/@roclass)]">
				<h3>Does not have a class</h3>
				<ul>
					<xsl:apply-templates select="ro:relatedObject[ro:key/@roclass = ''] | ro:relatedObject[not(ro:key/@roclass)]"/>			
				</ul>
			</xsl:if>
			</div>
		</div>
 		</xsl:if>
			
			
			

					   
		</div>
       </div>              				
        
    </xsl:template>

<!--  the following templates will format the view page content -->
    <xsl:template match="ro:displayTitle">   
        <div id="displaytitle">
        	<h1><xsl:value-of select="."/></h1>
				<div class="right_icon">
				<img class="icon-heading">
				<xsl:attribute name="src"><xsl:value-of select="$base_url"/>
				<xsl:text>/img/icon/</xsl:text>
				<xsl:value-of select="$objectClassType"/>
				<xsl:text>_32.png</xsl:text></xsl:attribute>
				</img>
				</div>
	            <div class="clearfix"/>
		</div>   
    </xsl:template>
    
    <xsl:template match="ro:relatedObject">  
<xsl:if test="ro:key != ''">
    <li>       
        		<xsl:variable name="url" select="concat($orca_home, 'services/getRegistryObjectsSOLR.php?task=getTitle&amp;relatedKey=',ro:key)"/>
        		<xsl:variable name="draftTitle" select="document($url)/draft/title"/>
        		<xsl:variable name="recordTitle" select="document($url)/record"/>
        		<xsl:variable name="relation">
        		<xsl:for-each select="ro:relation/@type">
					 <xsl:choose>	 		
					 	<xsl:when test="position()=1">		
					 	<xsl:value-of select="."/>
					 	</xsl:when>
					 	<xsl:otherwise>
					 	, <xsl:value-of select="."/>
					 	</xsl:otherwise>
					 </xsl:choose>
        		</xsl:for-each>
        		</xsl:variable>
         		<xsl:variable name="relDescription" >
         		<xsl:if test="ro:relation/ro:description!=''">
         		<xsl:value-of select= "ro:relation/ro:description"/>
         		</xsl:if>
         		</xsl:variable>    		
				<xsl:choose>
					<xsl:when test="$draftTitle != ''">
					<xsl:variable name="ds" select="document($url)/draft/ds"/>	
					<a href="{$base_url}preview/?key={ro:key}&amp;ds={$ds}" title="{$relation}" class="tipme">
					   <xsl:value-of select="$draftTitle"/>
					</a>
					</xsl:when>
				
					<xsl:when test="$recordTitle != ''">
					<a href="{$base_url}view/?key={ro:key}" title="{$relation}" class="tipme">
					   <xsl:value-of select="$recordTitle"/>
					</a>
					</xsl:when>
				
					<xsl:otherwise>
						<xsl:value-of select="ro:key"/>
					</xsl:otherwise>
				</xsl:choose>	
				<xsl:if test="$relDescription != '' ">
					<xsl:if test="string-length($relDescription)&lt;64">
						<br /><span  class='faded'><xsl:value-of select="$relDescription"/></span>
					</xsl:if>
				</xsl:if>				
	</li> 
</xsl:if>  
    </xsl:template>
      
    
    <xsl:template match="ro:name[@type != 'alternative']">
            <div id="displaytitle">
        	<h1>
				<xsl:choose>
				<xsl:when test="$objectClass = 'Party'">
						<xsl:apply-templates select="ro:namePart[@type = 'title']"/>					
						<xsl:apply-templates select="ro:namePart[@type = 'given']"/>					
						<xsl:apply-templates select="ro:namePart[@type = 'initial']"/>					
						<xsl:apply-templates select="ro:namePart[@type = 'family']"/>					
						<xsl:apply-templates select="ro:namePart[@type = 'suffix']"/>
						<xsl:apply-templates select="ro:namePart[@type != 'title' and @type != 'given' and @type != 'initial' and @type != 'family' and @type != 'suffix']"/>			
				</xsl:when>
				<xsl:otherwise>
					<xsl:apply-templates select="ro:namePart"/>
				</xsl:otherwise>
				</xsl:choose>
			</h1>
			
			</div>
				<div class="right_icon">
				<img class="icon-heading">
				<xsl:attribute name="src"><xsl:value-of select="$base_url"/>
				<xsl:text>/img/icon/</xsl:text>
				<xsl:value-of select="$objectClassType"/>
				<xsl:text>_32.png</xsl:text></xsl:attribute>
				</img>
				</div>
	        <div class="clearfix"/>

    </xsl:template>
    
    
    
    <xsl:template match="ro:name[@type = 'alternative']">
     <p class="alt_displayTitle">
     	<xsl:choose>
			<xsl:when test="$objectClass = 'Party'">
					<xsl:apply-templates select="ro:namePart[@type = 'title']"/>					
					<xsl:apply-templates select="ro:namePart[@type = 'given']"/>					
					<xsl:apply-templates select="ro:namePart[@type = 'initial']"/>					
					<xsl:apply-templates select="ro:namePart[@type = 'family']"/>					
					<xsl:apply-templates select="ro:namePart[@type = 'suffix']"/>
					<xsl:apply-templates select="ro:namePart[@type != 'title' and @type != 'given' and @type != 'initial' and @type != 'family' and @type != 'suffix']"/>			
			</xsl:when>
			<xsl:otherwise>
				<xsl:apply-templates select="ro:namePart"/>
			</xsl:otherwise>
		</xsl:choose>
     </p>
    </xsl:template>
    
    <xsl:template match="ro:namePart">
      <xsl:text> </xsl:text><xsl:value-of select="."/>
    </xsl:template>
    
    <xsl:template match="ro:description[@type = 'logo']">   
        <div id="displaylogo"><img id="party_logo" style="max-width:130px;">
        <xsl:attribute name="src"><xsl:value-of select="."/></xsl:attribute>
        </img>
		</div>    
    </xsl:template> 
  
  <xsl:template match="ro:identifier" mode="ark">
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
    				<xsl:attribute name="href"><xsl:text>http://</xsl:text> <xsl:value-of select="$theidentifier"/></xsl:attribute>
    				<xsl:attribute name="title"><xsl:text>Resolve this ARK identifier</xsl:text></xsl:attribute>    				
    				<xsl:value-of select="."/>
    				</a>
    				</xsl:if>
    				<xsl:if test="string-length(substring-after(.,'/ark:/'))&lt;1">
    					<xsl:value-of select="."/>
    				</xsl:if>
    				 <br />		 
    </xsl:template>
 <xsl:template match="ro:identifier" mode="nla">
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
    				<xsl:attribute name="href"><xsl:text>http://nla.gov.au/</xsl:text> <xsl:value-of select="$theidentifier"/></xsl:attribute>
    				<xsl:attribute name="title"><xsl:text>View the record for this party in Trove</xsl:text></xsl:attribute>    				
    				<xsl:value-of select="."/>
    				</a> 	<br />
  				</xsl:if> 
  					<xsl:if test="string-length(substring-after(.,'nla.party'))&lt;1">		

    				<xsl:value-of select="."/>
    			<br />
  				</xsl:if> 
 </xsl:template>
 <xsl:template match="ro:identifier" mode="doi">   					
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
    				<xsl:attribute name="href"><xsl:text>http://dx.doi.org/</xsl:text> <xsl:value-of select="$theidentifier"/></xsl:attribute>
    				<xsl:attribute name="title"><xsl:text>Resolve this DOI</xsl:text></xsl:attribute>    				
    				<xsl:value-of select="."/>
    				</a> 		 <br />
  				</xsl:if> 
  					<xsl:if test="string-length(substring-after(.,'10.'))&lt;1">		
   				
    				<xsl:value-of select="."/>
    			<br />
  				</xsl:if> 					 			

    			
 </xsl:template>
 <xsl:template match="ro:identifier" mode="handle">      			
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
    				<xsl:attribute name="href"> <xsl:value-of select="$theidentifier"/></xsl:attribute>
    				<xsl:attribute name="title"><xsl:text>Resolve this handle</xsl:text></xsl:attribute>    				
    				<xsl:value-of select="."/>
    				</a> 	 
    			<br />
 </xsl:template>
 <xsl:template match="ro:identifier" mode="purl">     			
 	PURL: 
    <xsl:variable name="theidentifier">    			
    <xsl:choose>				
    	<xsl:when test="string-length(substring-after(.,'purl.org/'))>0">
    		<xsl:value-of select="substring-after(.,'purl.org/')"/>
    	</xsl:when>		     	
    	<xsl:otherwise>
    		<xsl:value-of select="."/>
    	</xsl:otherwise>		
    </xsl:choose>
 	</xsl:variable>   	   			
    <a>
    <xsl:attribute name="href"><xsl:text>http://purl.org/</xsl:text> <xsl:value-of select="$theidentifier"/></xsl:attribute>
    <xsl:attribute name="title"><xsl:text>Resolve this purl identifier</xsl:text></xsl:attribute>    				
    <xsl:value-of select="."/>
    </a>  
    	<br /> 
  </xsl:template>
  <xsl:template match="ro:identifier" mode="uri">     			
 	URI: 
   <xsl:variable name="theidentifier">    			
    <xsl:choose>				
    	<xsl:when test="string-length(substring-after(.,'http'))>0">
    		<xsl:value-of select="."/>
    	</xsl:when>		     	
    	<xsl:otherwise>
    		http://<xsl:value-of select="."/>
    	</xsl:otherwise>		
    </xsl:choose>
 	</xsl:variable>   	        			
    <a>
    <xsl:attribute name="href"><xsl:value-of select="$theidentifier"/></xsl:attribute>
    <xsl:attribute name="title"><xsl:text>Resolve this uri</xsl:text></xsl:attribute>    				
    <xsl:value-of select="."/>  
    </a>   		 
   	<br />
  </xsl:template> 
 <xsl:template match="ro:identifier" mode="other">     			 			 	    			 			
   <!--  <xsl:attribute name="name"><xsl:value-of select="./@type"/></xsl:attribute>  -->
   <xsl:choose>
   <xsl:when test="./@type='arc' or ./@type='abn' or ./@type='isil'">
 		<xsl:value-of select="translate(./@type,'abcdefghijklmnopqrstuvwxyz','ABCDEFGHIJKLMNOPQRSTUVWXYZ')"/>: <xsl:value-of select="."/>  
   </xsl:when>
    <xsl:when test="./@type='local'">
 		Local: <xsl:value-of select="."/>    
   </xsl:when>  
   <xsl:otherwise>
		<xsl:value-of select="./@type"/>: <xsl:value-of select="."/>
	</xsl:otherwise>
	</xsl:choose>
	<br />
  </xsl:template>  

    <xsl:template match="ro:title">
        <xsl:value-of select="."/><br />    
    </xsl:template>

    <xsl:template match="ro:relatedInfo/ro:notes">
        <xsl:value-of select="."/><br />    
    </xsl:template> 
    
    <xsl:template match="ro:spatial">
      <xsl:if test="not(./@type) or (./@type!= 'text' and ./@type!= 'dcmiPoint')">
      	<p class="coverage" name="{@type}"><xsl:value-of select="."/></p>
      </xsl:if>
      <xsl:if test="./@type= 'text' or ./@type= 'dcmiPoint'">
     	 <p class="coverage_text"><xsl:value-of select="./@type"/>: <xsl:value-of select="."/></p>
      </xsl:if>     
    </xsl:template>
    
    <xsl:template match="ro:center">
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
    <xsl:template match="ro:text">  
     
        <xsl:value-of select="."/>          
    </xsl:template>     
    <xsl:template match="ro:subject">   
        <xsl:if test="./@type='anzsrc-for' or ./@type='anzsrc-seo' or ./@type='anzsrc-toa'">
        <!--  xsl:variable name="subject" select="."/>
        <xsl:variable name="url" select="concat($orca_home, '/services/getRegistryObjectsSOLR.php?subject=',$subject, '&amp;vocab=',@type)"/>
        <xsl:variable name="resolvedName" select="document($url)/subject"/-->
		<li><a href="javascript:void(0);" class="{@type}" id="{.}"><xsl:value-of select="."/></a></li>
        </xsl:if>
        <xsl:if test="./@type != 'anzsrc-for' and ./@type != 'anzsrc-seo' and ./@type!='anzsrc-toa'">
            <li><a href="javascript:void(0);" id="{.}"><xsl:value-of select="."/></a></li>
        </xsl:if>           
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
                        <xsl:apply-templates select="./ro:addressPart[@type='fullName']"/>  
                        <xsl:apply-templates select="./ro:addressPart[@type='organizationname']"/>  
                        <xsl:apply-templates select="./ro:addressPart[@type='organizationName']"/>  
                        <xsl:apply-templates select="./ro:addressPart[@type='buildingorpropertyname']"/>        
                        <xsl:apply-templates select="./ro:addressPart[@type='buildingOrPropertyName']"/>    
                        <xsl:apply-templates select="./ro:addressPart[@type='flatorunitnumber']"/>      
                        <xsl:apply-templates select="./ro:addressPart[@type='flatOrUnitNumber']"/>  
                        <xsl:apply-templates select="./ro:addressPart[@type='floororlevelnumber']"/>    
                        <xsl:apply-templates select="./ro:addressPart[@type='floorOrLevelNumber']"/>
                        <xsl:apply-templates select="./ro:addressPart[@type='lotnumber']"/> 
                        <xsl:apply-templates select="./ro:addressPart[@type='lotNumber']"/> 
                        <xsl:apply-templates select="./ro:addressPart[@type='housenumber']"/>   
                        <xsl:apply-templates select="./ro:addressPart[@type='houseNumber']"/>       
                        <xsl:apply-templates select="./ro:addressPart[@type='streetname']"/>    
                        <xsl:apply-templates select="./ro:addressPart[@type='streetName']"/>        
                        <xsl:apply-templates select="./ro:addressPart[@type='postaldeliverynumberprefix']"/>    
                        <xsl:apply-templates select="./ro:addressPart[@type='postalDeliveryNumberPrefix']"/>        
                        <xsl:apply-templates select="./ro:addressPart[@type='postaldeliverynumbervalue']"/>   
                        <xsl:apply-templates select="./ro:addressPart[@type='postalDeliveryNumberValue']"/>     
                        <xsl:apply-templates select="./ro:addressPart[@type='postaldeliverynumbersuffix']"/>  
                        <xsl:apply-templates select="./ro:addressPart[@type='postalDeliveryNumberSuffix']"/>    
                        <xsl:apply-templates select="./ro:addressPart[@type='addressline']"/>   
                        <xsl:apply-templates select="./ro:addressPart[@type='addressLine']"/>       
                        <xsl:apply-templates select="./ro:addressPart[@type='suburborplaceorlocality']"/>   
                        <xsl:apply-templates select="./ro:addressPart[@type='suburbOrPlaceOrLocality']"/>       
                        <xsl:apply-templates select="./ro:addressPart[@type='stateorterritory']"/>  
                        <xsl:apply-templates select="./ro:addressPart[@type='stateOrTerritory']"/>  
                        <xsl:apply-templates select="./ro:addressPart[@type='postcode']"/>  
                        <xsl:apply-templates select="./ro:addressPart[@type='postCode']"/>  
                        <xsl:apply-templates select="./ro:addressPart[@type='country']"/>   
                        <xsl:apply-templates select="./ro:addressPart[@type='Country']"/>       
                        <xsl:apply-templates select="./ro:addressPart[@type='locationdescriptor']"/>
                        <xsl:apply-templates select="./ro:addressPart[@type='locationDescriptor']"/>
                        <xsl:apply-templates select="./ro:addressPart[@type='deliverypointidentifier']"/> 
                        <xsl:apply-templates select="./ro:addressPart[@type='deliveryPointIdentifier']"/>   
                                                
                        <xsl:apply-templates select="./ro:addressPart[not(@type='organizationname' or @type='fullname' or @type='buildingorpropertyname' or @type='flatorunitnumber' or @type='floororlevelnumber' or @type='lotnumber' or @type='housenumber' or @type='streetname' or @type='postaldeliverynumberprefix' or @type='postaldeliverynumbervalue' or @type='postaldeliverynumbersuffix' or @type='addressline' or @type='suburborplaceorlocality' or @type='stateorterritory' or @type='country' or @type='locationdescriptor' or @type='deliverypointidentifier' or @type='postcode' or @type='telephoneNumber' or @type='faxNumber' or @type='organizationName' or @type='fullName' or @type='buildingOrPropertyName' or @type='flatOrUnitNumber' or @type='floorOrLevelNumber' or @type='lotNumber' or @type='houseNumber' or @type='streetName' or @type='postalDeliveryNumberPrefix' or @type='postalDeliveryNumberValue' or @type='postalDeliveryNumberSuffix' or @type='addressLine' or @type='suburbOrPlaceOrLocality' or @type='stateOrTerritory' or @type='Country' or @type='locationDescriptor' or @type='deliveryPointIdentifier' or @type='postCode' or @type='telephoneNumber' or @type='faxNumber' )]"/>  
						<!--xsl:apply-templates select="./ro:addressPart[not(@type='addressLine') or @type!='deliveryPointIdentifier' or @type='locationDescriptor' or @type='country' or @type='stateOrTerritory' or @type='suburbOrPlaceOrLocality' or @type='suburbOrPlaceOrLocality' or @type='addressLine' or @type='postalDeliveryNumberSuffix])"/-->
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="."  disable-output-escaping="yes"/><br />			
				</xsl:otherwise>
			</xsl:choose>	
		</p>
	</xsl:template>	
	
	<xsl:template match="ro:addressPart">			
			<xsl:value-of select="." disable-output-escaping="yes"/><br />
	</xsl:template>
		
	<xsl:template match="ro:description" mode="right">			
			<p class="rights">
						<xsl:if test="./@type='rightsStatement'"><strong>Rights statement</strong><br /></xsl:if>
						<xsl:if test="./@type='rights'"><strong>Rights</strong><br /></xsl:if>
			<xsl:if test="./@type='accessRights'"><strong>Access rights</strong><br /></xsl:if>
	<xsl:value-of select="." disable-output-escaping="yes"/></p>		
	</xsl:template>
	
	<xsl:template match="ro:description" mode="content">     
        <p> 
             <div><xsl:attribute name="class"><xsl:value-of select="@type"/></xsl:attribute>
                <p><xsl:value-of select="." disable-output-escaping="yes"/></p>
            </div>
        </p>                     
	</xsl:template> 
	
	
	
	<xsl:template match="ro:location/ro:address/ro:electronic/@type">		
		<xsl:if test=".='email'">	
	  		<xsl:value-of select=".."/><br />
		</xsl:if>				
	</xsl:template>  
	<xsl:template match="ro:existenceDates">
		<p><xsl:if test="./ro:startDate"><xsl:value-of select="./ro:startDate"/></xsl:if> - <xsl:if test="./ro:endDate"><xsl:value-of select="./ro:endDate"/></xsl:if></p>		
	</xsl:template>	 

	<xsl:template match="ro:rights/ro:accessRights">
			<p class="rights"><xsl:value-of select="." disable-output-escaping="yes"/>
						<xsl:if test="./@rightsUri"><p>
				<a target="_blank">
				<xsl:attribute name="href"><xsl:value-of select="./@rightsUri"/></xsl:attribute><xsl:value-of select="./@rightsUri"/></a></p>
			</xsl:if>	
			</p>	
	</xsl:template>	
	<xsl:template match="ro:rights/ro:rightsStatement">
			<p class="rights"><xsl:value-of select="." disable-output-escaping="yes"/>
			<xsl:if test="./@rightsUri">
				<p>
				<a target="_blank">
				<xsl:attribute name="href"><xsl:value-of select="./@rightsUri"/></xsl:attribute><xsl:value-of select="./@rightsUri"/></a>
				</p>
			</xsl:if>	
			</p>	
	</xsl:template>		
	<xsl:template match="ro:rights/ro:licence">
		<p class="rights">
			<xsl:if test="string-length(substring-after(./@type,'CC-'))>0">
    		 	<img id="licence_logo" style="max-width:130px;">
				<xsl:attribute name="src"><xsl:value-of select="$base_url"/>
				<xsl:text>/img/</xsl:text>
				<xsl:value-of select="./@type"/>
				<xsl:text>.png</xsl:text></xsl:attribute>
				<xsl:attribute name="alt"><xsl:value-of select="./@type"/></xsl:attribute>
		  		</img>
    		</xsl:if>
    		<xsl:if test="string-length(substring-after(./@type,'CC-'))=0">	   
    			<xsl:if test="./@type='Unknown/Other' and .=''"><p>Unknown</p></xsl:if>
    			<xsl:if test="./@type!='Unknown/Other'"><p><xsl:value-of select="./@type"/></p></xsl:if>
				<!--  <xsl:value-of select="./@licence_type"/> -->
			</xsl:if>
			<xsl:if test="."><p><xsl:value-of select="."/></p></xsl:if>
			<xsl:if test="./@rightsUri"><p>
				<a target="_blank">
				<xsl:attribute name="href"><xsl:value-of select="./@rightsUri"/></xsl:attribute><xsl:value-of select="./@rightsUri"/></a></p>
			</xsl:if>			
		</p>		
	</xsl:template>				
    
</xsl:stylesheet>
