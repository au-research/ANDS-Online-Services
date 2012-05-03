<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:mcp="http://bluenet3.antcrc.utas.edu.au/mcp"
                 xmlns:gmd="http://www.isotc211.org/2005/gmd"
                 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                 xmlns:gml="http://www.opengis.net/gml"
                 xmlns:gco="http://www.isotc211.org/2005/gco"
                 xmlns:gts="http://www.isotc211.org/2005/gts"
                 xmlns:srv="http://www.isotc211.org/2005/srv"
                 xmlns:geonet="http://www.fao.org/geonetwork"
                 gco:isoType="gmd:MD_Metadata"
                 xmlns:gmx="http://www.isotc211.org/2005/gmx"
                 xmlns:util="java:au.edu.apsr.harvester.util.XMLSupport"
                 xmlns:oai="http://www.openarchives.org/OAI/2.0/"  
                 xmlns="http://ands.org.au/standards/rif-cs/registryObjects">

<!-- stylesheet to convert iso19139 mcp in OAI-PMH ListRecords response to RIF-CS -->

<xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>

<xsl:strip-space elements="*"/>

<!-- the originating source --> 
<xsl:param name="origSource"/>

<!-- the registry object group -->
<xsl:param name="group"/>


<xsl:template match="oai:metadata|oai:ListRecords|oai:record">
   	<xsl:apply-templates/>
</xsl:template>


<xsl:template match="oai:OAI-PMH">
	<xsl:element name="registryObjects">
		<xsl:attribute name="xsi:schemaLocation">
        	<xsl:text>http://ands.org.au/standards/iso2146/registryObjects http://services.ands.org.au/home/orca/schemata/registryObjects.xsd</xsl:text>
		</xsl:attribute>
		<xsl:apply-templates/>
	</xsl:element>
</xsl:template>


<xsl:template match="gmd:voice[not(@gco:nilReason)]">
	<xsl:element name="electronic">
		<xsl:attribute name="type">
			<xsl:text>voice</xsl:text>
		</xsl:attribute>
		<xsl:element name="value">
			<xsl:value-of select="concat('tel:',translate(translate(gco:CharacterString,'+',''),' ','-'))"/>
		</xsl:element>
	</xsl:element>
</xsl:template>


<xsl:template match="gmd:facsimile[not(@gco:nilReason)]">
	<xsl:element name="electronic">
		<xsl:attribute name="type">
			<xsl:text>fax</xsl:text>
		</xsl:attribute>
		<xsl:element name="value">
			<xsl:value-of select="concat('tel:',translate(translate(gco:CharacterString,'+',''),' ','-'))"/>
		</xsl:element>
	</xsl:element>
</xsl:template>


<xsl:template match="gmd:organisationName[not(@gco:nilReason)]">
	<xsl:element name="addressPart">
		<xsl:attribute name="type">
			<xsl:text>locationDescriptor</xsl:text>
		</xsl:attribute>
		<xsl:value-of select="gco:CharacterString"/>
	</xsl:element>
</xsl:template>


<xsl:template match="deliveryPoint[not(@gco:nilReason)]">
	<xsl:element name="addressPart">
		<xsl:attribute name="type">
			<xsl:text>addressLine</xsl:text>
		</xsl:attribute>
		<xsl:value-of select="gco:CharacterString"/>
	</xsl:element>
</xsl:template>


<xsl:template match="gmd:city[not(@gco:nilReason)]">
	<xsl:element name="addressPart">
		<xsl:attribute name="type">
			<xsl:text>suburbOrPlaceOrLocality</xsl:text>
		</xsl:attribute>
		<xsl:value-of select="gco:CharacterString"/>
	</xsl:element>
</xsl:template>


<xsl:template match="gmd:administrativeArea[not(@gco:nilReason)]">
	<xsl:element name="addressPart">
		<xsl:attribute name="type">
			<xsl:text>stateOrTerritory</xsl:text>
		</xsl:attribute>
		<xsl:value-of select="gco:CharacterString"/>
	</xsl:element>
</xsl:template>


<xsl:template match="gmd:postalCode[not(@gco:nilReason)]">
	<xsl:element name="addressPart">
		<xsl:attribute name="type">
			<xsl:text>postCode</xsl:text>
		</xsl:attribute>
		<xsl:value-of select="gco:CharacterString"/>
	</xsl:element>
</xsl:template>


<xsl:template match="gmd:country[not(@gco:nilReason)]">
	<xsl:element name="addressPart">
		<xsl:attribute name="type">
			<xsl:text>country</xsl:text>
		</xsl:attribute>
		<xsl:value-of select="gco:CharacterString"/>
	</xsl:element>
</xsl:template>


<xsl:template match="gmd:title">
	<xsl:value-of select="gco:CharacterString"/>
</xsl:template>


<xsl:template match="gmd:EX_GeographicBoundingBox">
	<xsl:element name="spatial">
		<xsl:attribute name="type">
			<xsl:text>iso19139dcmiBox</xsl:text>
		</xsl:attribute>
		<xsl:value-of select="concat('northlimit=',gmd:northBoundLatitude/gco:Decimal,'; southlimit=',gmd:southBoundLatitude/gco:Decimal,'; westlimit=',gmd:westBoundLongitude/gco:Decimal,'; eastlimit=',gmd:eastBoundLongitude/gco:Decimal)"/>
		<xsl:apply-templates select="../gmd:verticalElement"/>
		<xsl:text>; projection=WGS84</xsl:text>
	</xsl:element>
</xsl:template>


<xsl:template match="gmd:verticalElement">
	<xsl:value-of select="concat('; uplimit=',gmd:EX_VerticalExtent/gmd:maximumValue/gco:Real,'; downlimit=',gmd:EX_VerticalExtent/gmd:minimumValue/gco:Real)"/>
</xsl:template>


<xsl:template match="gmd:description[not(@gco:nilReason)]">
	<xsl:element name="spatial">
		<xsl:attribute name="type">
			<xsl:text>text</xsl:text>
		</xsl:attribute>
		<xsl:value-of select="gco:CharacterString"/>
	</xsl:element>
</xsl:template>


<xsl:template match="gml:coordinates">
	<xsl:element name="spatial">
		<xsl:attribute name="type">
			<xsl:text>gmlKmlPolyCoords</xsl:text>
		</xsl:attribute>
		<xsl:call-template name="gmlToKml">
			<xsl:with-param name="coords" select="."/>
		</xsl:call-template>
	</xsl:element>
</xsl:template>


<xsl:template match="gmd:keyword">
	<xsl:call-template name="splitSubject">
		<xsl:with-param name="string" select="gco:CharacterString"/>
	</xsl:call-template>	
</xsl:template>


<xsl:template match="gmd:MD_TopicCategoryCode">			
	<xsl:element name="subject">
		<xsl:attribute name="type">
			<xsl:text>local</xsl:text>
		</xsl:attribute>
		<xsl:value-of select="."/>
	</xsl:element>
</xsl:template>


<xsl:template match="gmd:abstract">
	<xsl:element name="description">
		<xsl:attribute name="type">
			<xsl:text>brief</xsl:text>
		</xsl:attribute>
	    <xsl:value-of select="gco:CharacterString"/>
	</xsl:element>
</xsl:template>


<xsl:template match="mcp:MD_Metadata">
	<xsl:choose>
		<xsl:when test="gmd:hierarchyLevel/gmd:MD_ScopeCode[@codeListValue='service']">
			<xsl:apply-templates select="." mode="service"/>
		</xsl:when>
		<xsl:otherwise>
			<xsl:apply-templates select="." mode="collection"/>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>


<!--
	CREATE COLLECTION OBJECT
-->
<xsl:template match="mcp:MD_Metadata" mode="collection">
	<xsl:variable name="ge" select="gmd:identificationInfo/mcp:MD_DataIdentification/gmd:extent/gmd:EX_Extent/gmd:geographicElement"/>
	<xsl:variable name="te" select="gmd:identificationInfo/mcp:MD_DataIdentification/gmd:extent/gmd:EX_Extent/gmd:temporalElement"/>
	<xsl:variable name="ve" select="gmd:identificationInfo/mcp:MD_DataIdentification/gmd:extent/gmd:EX_Extent/gmd:verticalElement"/>
	
	<xsl:variable name="formattedFrom">
		<xsl:choose>
			<xsl:when test="$te[1]/mcp:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:beginPosition">
				<xsl:value-of select="util:dateTime($te[1]/mcp:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:beginPosition, 'from')"/>
			</xsl:when>
			<xsl:when test="$te[1]/mcp:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:begin/gml:TimeInstant/gml:timePosition">
				<xsl:value-of select="util:dateTime($te[1]/mcp:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:begin/gml:TimeInstant/gml:timePosition, 'from')"/>
			</xsl:when>
		</xsl:choose>					
	</xsl:variable>
			
	<xsl:variable name="formattedTo">
		<xsl:choose>
			<xsl:when test="$te[position()=last()]/mcp:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:endPosition">
				<xsl:value-of	select="util:dateTime($te[position()=last()]/mcp:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:endPosition, 'to')"/>
			</xsl:when>
			<xsl:when test="$te[position()=last()]/mcp:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:end/gml:TimeInstant/gml:timePosition">
				<xsl:value-of select="util:dateTime($te[position()=last()]/mcp:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:end/gml:TimeInstant/gml:timePosition, 'to')"/>
			</xsl:when>
		</xsl:choose>
	</xsl:variable>

	<xsl:variable name="from">
		<xsl:choose>
			<xsl:when test="$te[1]/mcp:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:beginPosition">
				<xsl:value-of select="$te[1]/mcp:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:beginPosition"/>
			</xsl:when>
			<xsl:when test="$te[1]/mcp:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:begin/gml:TimeInstant/gml:timePosition">
				<xsl:value-of select="$te[1]/mcp:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:begin/gml:TimeInstant/gml:timePosition"/>
			</xsl:when>
		</xsl:choose>
	</xsl:variable>
			
	<xsl:variable name="to">
		<xsl:choose>
			<xsl:when test="$te[position()=last()]/mcp:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:endPosition">
				<xsl:value-of	select="$te[position()=last()]/mcp:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:endPosition"/>
			</xsl:when>
			<xsl:when test="$te[position()=last()]/mcp:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:end/gml:TimeInstant/gml:timePosition">
				<xsl:value-of select="$te[position()=last()]/mcp:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:end/gml:TimeInstant/gml:timePosition"/>
			</xsl:when>
		</xsl:choose>
	</xsl:variable>

	<xsl:element name="registryObject">
		<xsl:attribute name="group">
			<xsl:value-of select="$group"/>
		</xsl:attribute>
		<xsl:element name="key">
			<xsl:value-of select="gmd:fileIdentifier/gco:CharacterString"/>
		</xsl:element>
		<xsl:element name="originatingSource">
			<xsl:value-of select="$origSource"/>
		</xsl:element>
		<xsl:element name="collection">
			<xsl:attribute name="type">
				<xsl:value-of select="'dataset'"/>
			</xsl:attribute>
			<xsl:element name="identifier">
				<xsl:attribute name="type">
					<xsl:text>local</xsl:text>
				</xsl:attribute>
				<xsl:value-of select="gmd:fileIdentifier/gco:CharacterString"/>
			</xsl:element>
			
			<xsl:element name="name">
				<xsl:attribute name="type">
					<xsl:text>primary</xsl:text>
				</xsl:attribute>
				<xsl:element name="namePart">
					<xsl:attribute name="type">
						<xsl:text>full</xsl:text>
					</xsl:attribute>
					<xsl:apply-templates select="gmd:identificationInfo/mcp:MD_DataIdentification/gmd:citation/gmd:CI_Citation/gmd:title"/>
				</xsl:element>
			</xsl:element>

			<xsl:element name="location">
				<xsl:element name="address">
					<xsl:element name="electronic">
						<xsl:attribute name="type">
							<xsl:text>url</xsl:text>
						</xsl:attribute>
						<xsl:element name="value">
							<xsl:variable name="url">
								<xsl:value-of select="gmd:distributionInfo/gmd:MD_Distribution/gmd:transferOptions/gmd:MD_DigitalTransferOptions/gmd:onLine/gmd:CI_OnlineResource/gmd:linkage[following-sibling::gmd:description = 'Point of truth URL of this metadata record']/gmd:URL"/>	
							</xsl:variable>
							<xsl:choose>
								<xsl:when test="not($url='')">
								<!-- source has some duplicates, odd, possibly automated data (CSIRO bluenet) --> 
										<xsl:value-of select="$url"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="concat($origSource,'/geonetwork/srv/en/metadata.show?uuid=',gmd:fileIdentifier/gco:CharacterString)"/>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:element>
					</xsl:element>
				</xsl:element>
			</xsl:element>

			<xsl:if test="$ge/gmd:EX_GeographicBoundingBox">
				<xsl:element name="location">
					<xsl:attribute name="type">
						<xsl:text>coverage</xsl:text>
					</xsl:attribute>
					<!-- date time -->
					<xsl:if test="not($formattedFrom='')">
						<xsl:attribute name="dateFrom">
							<xsl:value-of select="$formattedFrom"/>
						</xsl:attribute>
					</xsl:if>
					<xsl:if test="not($formattedTo='')">
						<xsl:attribute name="dateTo">
							<xsl:value-of select="$formattedTo"/>
						</xsl:attribute>						
					</xsl:if>

					<xsl:apply-templates select="$ge/gmd:EX_GeographicBoundingBox"/>
					
					<xsl:apply-templates select="gmd:identificationInfo/mcp:MD_DataIdentification/gmd:extent/gmd:EX_Extent/gmd:description"/>

					<xsl:apply-templates select="$ge/gmd:EX_BoundingPolygon/gmd:polygon/gml:Polygon/gml:exterior/gml:LinearRing/gml:coordinates[text()!='']"/>

				</xsl:element>
			</xsl:if>

			<!-- related individuals generated here -->
			<xsl:for-each-group select="descendant::gmd:CI_ResponsibleParty[not(ancestor::gmd:thesaurusName) and gmd:individualName[not(@gco:nilReason)] and not(gmd:role/gmd:CI_RoleCode/@codeListValue='')]" group-by="gmd:individualName/gco:CharacterString">
				<xsl:element name="relatedObject">
					<xsl:element name="key">
						<xsl:value-of select="util:generateHash(translate(concat($group,current-grouping-key()), ' ', ''))"/>
					</xsl:element>	
					<xsl:for-each-group select="gmd:role" group-by="gmd:CI_RoleCode/@codeListValue">
						<xsl:variable name="code">
							<xsl:value-of select="current-grouping-key()"/>
						</xsl:variable>
		
						<xsl:variable name="codelist">
							<xsl:value-of select="substring-after(gmd:CI_RoleCode/@codeList, '#')"/>
						</xsl:variable>
			
						<xsl:variable name="url">
							<xsl:value-of select="substring-before(gmd:CI_RoleCode/@codeList, '#')"/>
						</xsl:variable>

						<xsl:element name="relation">
							<xsl:attribute name="type">
								<xsl:value-of select="$code"/>
							</xsl:attribute>
						</xsl:element>
					</xsl:for-each-group>
				</xsl:element>
			</xsl:for-each-group>

			<!-- related organisations generated here -->
			<xsl:for-each-group select="descendant::gmd:CI_ResponsibleParty[not(ancestor::gmd:thesaurusName) and gmd:organisationName[not(@gco:nilReason)] and not(gmd:role/gmd:CI_RoleCode/@codeListValue='') and (gmd:individualName='' or not(gmd:individualName))]" group-by="gmd:organisationName/gco:CharacterString">
				<xsl:element name="relatedObject">
					<xsl:element name="key">
						<xsl:value-of select="util:generateHash(translate(concat($group,current-grouping-key()), ' ', ''))"/>
					</xsl:element>
					<xsl:for-each-group select="gmd:role" group-by="gmd:CI_RoleCode/@codeListValue">
						<xsl:variable name="code">
							<xsl:value-of select="current-grouping-key()"/>
						</xsl:variable>

						<xsl:element name="relation">
							<xsl:attribute name="type">
								<xsl:value-of select="$code"/>
							</xsl:attribute>
						</xsl:element>		
					</xsl:for-each-group>
				</xsl:element>
			</xsl:for-each-group>
			
			<xsl:apply-templates select="gmd:identificationInfo/mcp:MD_DataIdentification/gmd:descriptiveKeywords/gmd:MD_Keywords/gmd:keyword"/>

			<xsl:apply-templates select="gmd:identificationInfo/mcp:MD_DataIdentification/gmd:topicCategory/gmd:MD_TopicCategoryCode"/>
			
			<xsl:apply-templates select="gmd:identificationInfo/mcp:MD_DataIdentification/gmd:abstract"/>
		
			<xsl:if test="$te/mcp:EX_TemporalExtent/gmd:extent/gml:TimePeriod">
				
				<xsl:if test="not($from='') and $formattedFrom=''">
					<xsl:element name="description">
						<xsl:attribute name="type">
							<xsl:text>temporal</xsl:text>
						</xsl:attribute>
						<xsl:choose>
							<xsl:when test="$from = $to or $to=''">
								<xsl:text>Time period: </xsl:text>
								<xsl:value-of select="$from"/>
							</xsl:when>
							<xsl:otherwise>
								<xsl:text>Time period: </xsl:text>
								<xsl:value-of select="concat($from, ' to ', $to)"/>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:element>
				</xsl:if>
			</xsl:if>		
		</xsl:element>
	</xsl:element>
	  
	  
	<!-- Create all the associated party objects for individuals -->
	<xsl:for-each-group select="descendant::gmd:CI_ResponsibleParty[not(ancestor::gmd:thesaurusName) and gmd:individualName[not(@gco:nilReason)] and not(gmd:role/gmd:CI_RoleCode/@codeListValue='')]" group-by="gmd:individualName/gco:CharacterString">
		<xsl:element name="registryObject">
			<xsl:attribute name="group">
				<xsl:value-of select="$group"/>
			</xsl:attribute>
			<xsl:element name="key">
				<xsl:value-of select="util:generateHash(translate(concat($group,current-grouping-key()), ' ', ''))"/>
			</xsl:element>
			<xsl:element name="originatingSource">
				<xsl:value-of select="$origSource"/>
			</xsl:element>
			<xsl:element name="party">
				<xsl:attribute name="type">
					<xsl:text>person</xsl:text>
				</xsl:attribute>
				<xsl:element name="name">
					<xsl:attribute name="type">
						<xsl:text>primary</xsl:text>
					</xsl:attribute>
					<xsl:element name="namePart">
						<xsl:attribute name="type">
							<xsl:text>full</xsl:text>
						</xsl:attribute>
						<xsl:value-of select="current-grouping-key()"/>
					</xsl:element>
				</xsl:element>
				
				<!-- to normalise parties within a single record we need to group them, obtain the fragment for each party with the most information, and at the same time cope with rubbish data. In the end the only way to cope is to ensure at least an organisation name, city, phone or fax exists (sigh) -->
				<xsl:for-each select="current-group()">
					<xsl:sort select="count(gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/child::*) + count(gmd:contactInfo/gmd:CI_Contact/gmd:phone/gmd:CI_Telephone/child::*)" data-type="number" order="descending"/>
					<xsl:choose>
						<xsl:when test="position()=1">
							<xsl:if test="gmd:organisationName[not(@gco:nilReason)] or gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:city or gmd:contactInfo/gmd:CI_Contact/gmd:phone/gmd:CI_Telephone[gmd:voice or gmd:fax]">
								<xsl:element name="location">
									<xsl:element name="address">
										<xsl:apply-templates select="gmd:contactInfo/gmd:CI_Contact/gmd:phone/gmd:CI_Telephone/gmd:voice"/>
										<xsl:apply-templates select="gmd:contactInfo/gmd:CI_Contact/gmd:phone/gmd:CI_Telephone/gmd:facsimile"/>
										<xsl:if test="not(gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:city/gco:CharacterString='')">
											<xsl:element name="physical">
												<xsl:attribute name="type">
													<xsl:text>streetAddress</xsl:text>
												</xsl:attribute>
												<xsl:apply-templates select="gmd:organisationName"/>
												<xsl:apply-templates select="gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:deliveryPoint"/>
												<xsl:apply-templates select="gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:city"/>
												<xsl:apply-templates select="gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:administrativeArea[not(@gco:nilReason)]"/>
												<xsl:apply-templates select="gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:postalCode[not(@gco:nilReason)]"/>
												<xsl:apply-templates select="gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:country"/>
											</xsl:element>
										</xsl:if>
									</xsl:element>
								</xsl:element>
							</xsl:if>
						</xsl:when>
					</xsl:choose>
				</xsl:for-each>
		
				<xsl:element name="relatedObject">
					<xsl:element name="key">
						<xsl:value-of select="ancestor::mcp:MD_Metadata/gmd:fileIdentifier/gco:CharacterString"/>
					</xsl:element>	

					<xsl:for-each-group select="gmd:role" group-by="gmd:CI_RoleCode/@codeListValue">
						<xsl:variable name="code">
							<xsl:value-of select="current-grouping-key()"/>
						</xsl:variable>
						
						<xsl:element name="relation">
							<xsl:attribute name="type">
								<xsl:value-of select="$code"/>
							</xsl:attribute>
						</xsl:element>		
					</xsl:for-each-group>
				</xsl:element>
			</xsl:element>
		</xsl:element>
	</xsl:for-each-group>


	<!-- Create all the associated party objects for organisations -->
	<xsl:for-each-group select="descendant::gmd:CI_ResponsibleParty[not(ancestor::gmd:thesaurusName) and (gmd:individualName[@gco:nilReason] or not(gmd:individualName)) and gmd:organisationName[not(@gco:nilReason)] and not(gmd:role/gmd:CI_RoleCode/@codeListValue='')]" group-by="gmd:organisationName/gco:CharacterString">
		<xsl:element name="registryObject">
			<xsl:attribute name="group">
				<xsl:value-of select="$group"/>
			</xsl:attribute>
			<xsl:element name="key">
				<xsl:value-of select="util:generateHash(translate(concat($group,current-grouping-key()), ' ', ''))"/>
			</xsl:element>
			<xsl:element name="originatingSource">
				<xsl:value-of select="$origSource"/>
			</xsl:element>
			<xsl:element name="party">
				<xsl:attribute name="type">
					<xsl:text>group</xsl:text>
				</xsl:attribute>
				<xsl:element name="name">
					<xsl:attribute name="type">
						<xsl:text>primary</xsl:text>
					</xsl:attribute>
					<xsl:element name="namePart">
						<xsl:attribute name="type">
							<xsl:text>full</xsl:text>
						</xsl:attribute>
						<xsl:value-of select="current-grouping-key()"/>
					</xsl:element>
				</xsl:element>
				
				<!-- to normalise parties within a single record we need to group them, obtain the fragment for each party with the most information, and at the same time cope with rubbish data. In the end the only way to cope is to ensure at least an organisation name, city, phone or fax exists (sigh) -->
				<xsl:for-each select="current-group()">
					<xsl:sort select="count(gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/child::*) + count(gmd:contactInfo/gmd:CI_Contact/gmd:phone/gmd:CI_Telephone/child::*)" data-type="number" order="descending"/>
					<xsl:choose>
						<xsl:when test="position()=1">
							<xsl:if test="gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:city or gmd:contactInfo/gmd:CI_Contact/gmd:phone/gmd:CI_Telephone[gmd:voice or gmd:fax]">
								<xsl:element name="location">
									<xsl:element name="address">
										<xsl:apply-templates select="gmd:contactInfo/gmd:CI_Contact/gmd:phone/gmd:CI_Telephone/gmd:voice"/>
										<xsl:apply-templates select="gmd:contactInfo/gmd:CI_Contact/gmd:phone/gmd:CI_Telephone/gmd:facsimile"/>
										<xsl:if test="not(gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:city/gco:CharacterString='')">
											<xsl:element name="physical">
												<xsl:attribute name="type">
													<xsl:text>streetAddress</xsl:text>
												</xsl:attribute>
												<xsl:apply-templates select="gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:deliveryPoint"/>
												<xsl:apply-templates select="gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:city"/>
												<xsl:apply-templates select="gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:administrativeArea[not(@gco:nilReason)]"/>
												<xsl:apply-templates select="gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:postalCode[not(@gco:nilReason)]"/>
												<xsl:apply-templates select="gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:country"/>
										</xsl:element>
										</xsl:if>
									</xsl:element>
								</xsl:element>
							</xsl:if>
						</xsl:when>
					</xsl:choose>
				</xsl:for-each>
		
				<xsl:element name="relatedObject">
					<xsl:element name="key">
						<xsl:value-of select="ancestor::mcp:MD_Metadata/gmd:fileIdentifier/gco:CharacterString"/>
					</xsl:element>	

					<xsl:for-each-group select="gmd:role" group-by="gmd:CI_RoleCode/@codeListValue">
						<xsl:variable name="code">
							<xsl:value-of select="current-grouping-key()"/>
						</xsl:variable>
						
						<xsl:element name="relation">
							<xsl:attribute name="type">
								<xsl:value-of select="$code"/>
							</xsl:attribute>
						</xsl:element>		
					</xsl:for-each-group>
				</xsl:element>
			</xsl:element>
		</xsl:element>
	</xsl:for-each-group>

</xsl:template>


<!--
	CREATE SERVICE OBJECT
-->
<xsl:template match="mcp:MD_Metadata" mode="service">
	<xsl:variable name="ge" select="gmd:identificationInfo/mcp:MD_DataIdentification/gmd:extent/gmd:EX_Extent/gmd:geographicElement"/>
	<xsl:variable name="te" select="gmd:identificationInfo/mcp:MD_DataIdentification/gmd:extent/gmd:EX_Extent/gmd:temporalElement"/>
	<xsl:variable name="ve" select="gmd:identificationInfo/mcp:MD_DataIdentification/gmd:extent/gmd:EX_Extent/gmd:verticalElement"/>
	
	<xsl:variable name="formattedFrom">
		<xsl:choose>
			<xsl:when test="$te[1]/mcp:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:beginPosition">
				<xsl:value-of select="util:dateTime($te[1]/mcp:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:beginPosition, 'from')"/>
			</xsl:when>
			<xsl:when test="$te[1]/mcp:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:begin/gml:TimeInstant/gml:timePosition">
				<xsl:value-of select="util:dateTime($te[1]/mcp:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:begin/gml:TimeInstant/gml:timePosition, 'from')"/>
			</xsl:when>
		</xsl:choose>					
	</xsl:variable>
			
	<xsl:variable name="formattedTo">
		<xsl:choose>
			<xsl:when test="$te[position()=last()]/mcp:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:endPosition">
				<xsl:value-of	select="util:dateTime($te[position()=last()]/mcp:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:endPosition, 'to')"/>
			</xsl:when>
			<xsl:when test="$te[position()=last()]/mcp:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:end/gml:TimeInstant/gml:timePosition">
				<xsl:value-of select="util:dateTime($te[position()=last()]/mcp:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:end/gml:TimeInstant/gml:timePosition, 'to')"/>
			</xsl:when>
		</xsl:choose>
	</xsl:variable>

	<xsl:variable name="from">
		<xsl:choose>
			<xsl:when test="$te[1]/mcp:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:beginPosition">
				<xsl:value-of select="$te[1]/mcp:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:beginPosition"/>
			</xsl:when>
			<xsl:when test="$te[1]/mcp:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:begin/gml:TimeInstant/gml:timePosition">
				<xsl:value-of select="$te[1]/mcp:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:begin/gml:TimeInstant/gml:timePosition"/>
			</xsl:when>
		</xsl:choose>
	</xsl:variable>
			
	<xsl:variable name="to">
		<xsl:choose>
			<xsl:when test="$te[position()=last()]/mcp:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:endPosition">
				<xsl:value-of	select="$te[position()=last()]/mcp:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:endPosition"/>
			</xsl:when>
			<xsl:when test="$te[position()=last()]/mcp:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:end/gml:TimeInstant/gml:timePosition">
				<xsl:value-of select="$te[position()=last()]/mcp:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:end/gml:TimeInstant/gml:timePosition"/>
			</xsl:when>
		</xsl:choose>
	</xsl:variable>

	<xsl:element name="registryObject">
		<xsl:attribute name="group">
			<xsl:value-of select="$group"/>
		</xsl:attribute>
		<xsl:element name="key">
			<xsl:value-of select="gmd:fileIdentifier/gco:CharacterString"/>
		</xsl:element>
		<xsl:element name="originatingSource">
			<xsl:value-of select="$origSource"/>
		</xsl:element>
		<xsl:element name="service">
			<xsl:attribute name="type">
				<xsl:value-of select="'search-http'"/>
			</xsl:attribute>
			<xsl:element name="identifier">
				<xsl:attribute name="type">
					<xsl:text>local</xsl:text>
				</xsl:attribute>
				<xsl:value-of select="gmd:fileIdentifier/gco:CharacterString"/>
			</xsl:element>
			
			<xsl:element name="name">
				<xsl:attribute name="type">
					<xsl:text>primary</xsl:text>
				</xsl:attribute>
				<xsl:element name="namePart">
					<xsl:attribute name="type">
						<xsl:text>full</xsl:text>
					</xsl:attribute>
					<xsl:apply-templates select="gmd:identificationInfo/srv:SV_ServiceIdentification/gmd:citation/gmd:CI_Citation/gmd:title"/>
				</xsl:element>
			</xsl:element>

			<xsl:element name="location">
				<xsl:element name="address">
					<xsl:element name="electronic">
						<xsl:attribute name="type">
							<xsl:text>url</xsl:text>
						</xsl:attribute>
						<xsl:element name="value">
							<xsl:variable name="url">
								<xsl:value-of select="gmd:distributionInfo/gmd:MD_Distribution/gmd:transferOptions/gmd:MD_DigitalTransferOptions/gmd:onLine/gmd:CI_OnlineResource/gmd:linkage[following-sibling::gmd:description = 'Point of truth URL of this metadata record']/gmd:URL"/>	
							</xsl:variable>
							<xsl:choose>
								<xsl:when test="not($url='')">
								<!-- source has some duplicates, odd, possibly automated data (CSIRO bluenet) --> 
										<xsl:value-of select="$url"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="concat($origSource,'/geonetwork/srv/en/metadata.show?uuid=',gmd:fileIdentifier/gco:CharacterString)"/>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:element>
					</xsl:element>
				</xsl:element>
			</xsl:element>

			<xsl:for-each select="gmd:distributionInfo/gmd:MD_Distribution/gmd:transferOptions/gmd:MD_DigitalTransferOptions/gmd:onLine/gmd:CI_OnlineResource/gmd:linkage[following-sibling::gmd:protocol/gco:CharacterString='WWW:LINK-1.0-http--samples']/gmd:URL">
				<xsl:element name="location">
					<xsl:element name="address">
						<xsl:element name="electronic">
							<xsl:attribute name="type">
								<xsl:text>url</xsl:text>
							</xsl:attribute>
							<xsl:element name="value">
								<xsl:value-of select="."/>
							</xsl:element>
						</xsl:element>
					</xsl:element>
				</xsl:element>
			</xsl:for-each>

			<xsl:if test="$ge/gmd:EX_GeographicBoundingBox">
				<xsl:element name="location">
					<xsl:attribute name="type">
						<xsl:text>coverage</xsl:text>
					</xsl:attribute>
					<!-- date time -->
					<xsl:if test="not($formattedFrom='')">
						<xsl:attribute name="dateFrom">
							<xsl:value-of select="$formattedFrom"/>
						</xsl:attribute>
					</xsl:if>
					<xsl:if test="not($formattedTo='')">
						<xsl:attribute name="dateTo">
							<xsl:value-of select="$formattedTo"/>
						</xsl:attribute>
					</xsl:if>

					<xsl:apply-templates select="$ge/gmd:EX_GeographicBoundingBox"/>
					
					<xsl:apply-templates select="gmd:identificationInfo/mcp:MD_DataIdentification/gmd:extent/gmd:EX_Extent/gmd:description"/>

					<xsl:apply-templates select="$ge/gmd:EX_BoundingPolygon/gmd:polygon/gml:Polygon/gml:exterior/gml:LinearRing/gml:coordinates[text()!='']"/>

				</xsl:element>
			</xsl:if>
			
			<!-- related collections here -->
			<xsl:for-each select="gmd:identificationInfo/srv:SV_ServiceIdentification/srv:operatesOn/@uuidref">
				<xsl:element name="relatedObject">
					<xsl:element name="key">
						<xsl:value-of select="."/>
					</xsl:element>
					<xsl:element name="relation">
						<xsl:attribute name="type">
							<xsl:value-of select="'operatesOn'"/>
						</xsl:attribute>
					</xsl:element>
				</xsl:element>
			</xsl:for-each>
					
			<!-- related individuals generated here -->
			<xsl:for-each-group select="descendant::gmd:CI_ResponsibleParty[not(ancestor::gmd:thesaurusName) and gmd:individualName[not(@gco:nilReason)] and not(gmd:role/gmd:CI_RoleCode/@codeListValue='')]" group-by="gmd:individualName/gco:CharacterString">
				<xsl:element name="relatedObject">
					<xsl:element name="key">
						<xsl:value-of select="util:generateHash(translate(concat($group,current-grouping-key()), ' ', ''))"/>
					</xsl:element>	
					<xsl:for-each-group select="gmd:role" group-by="gmd:CI_RoleCode/@codeListValue">
						<xsl:variable name="code">
							<xsl:value-of select="current-grouping-key()"/>
						</xsl:variable>
		
						<xsl:variable name="codelist">
							<xsl:value-of select="substring-after(gmd:CI_RoleCode/@codeList, '#')"/>
						</xsl:variable>
			
						<xsl:variable name="url">
							<xsl:value-of select="substring-before(gmd:CI_RoleCode/@codeList, '#')"/>
						</xsl:variable>

						<xsl:element name="relation">
							<xsl:attribute name="type">
								<xsl:value-of select="$code"/>
							</xsl:attribute>
						</xsl:element>
					</xsl:for-each-group>
				</xsl:element>
			</xsl:for-each-group>

			<!-- related organisations generated here -->
			<xsl:for-each-group select="descendant::gmd:CI_ResponsibleParty[not(ancestor::gmd:thesaurusName) and gmd:organisationName[not(@gco:nilReason)] and not(gmd:role/gmd:CI_RoleCode/@codeListValue='') and (gmd:individualName='' or not(gmd:individualName))]" group-by="gmd:organisationName/gco:CharacterString">
				<xsl:element name="relatedObject">
					<xsl:element name="key">
						<xsl:value-of select="util:generateHash(translate(concat($group,current-grouping-key()), ' ', ''))"/>
					</xsl:element>
					<xsl:for-each-group select="gmd:role" group-by="gmd:CI_RoleCode/@codeListValue">
						<xsl:variable name="code">
							<xsl:value-of select="current-grouping-key()"/>
						</xsl:variable>

						<xsl:element name="relation">
							<xsl:attribute name="type">
								<xsl:value-of select="$code"/>
							</xsl:attribute>
						</xsl:element>		
					</xsl:for-each-group>
				</xsl:element>
			</xsl:for-each-group>
			
			<xsl:apply-templates select="gmd:identificationInfo/mcp:MD_DataIdentification/gmd:descriptiveKeywords/gmd:MD_Keywords/gmd:keyword"/>

			<xsl:apply-templates select="gmd:identificationInfo/mcp:MD_DataIdentification/gmd:topicCategory/gmd:MD_TopicCategoryCode"/>
			
			<xsl:apply-templates select="gmd:identificationInfo/srv:SV_ServiceIdentification/gmd:abstract"/>
		
			<xsl:if test="$te/mcp:EX_TemporalExtent/gmd:extent/gml:TimePeriod">
				
				<xsl:if test="not($from='') and $formattedFrom=''">
					<xsl:element name="description">
						<xsl:attribute name="type">
							<xsl:text>temporal</xsl:text>
						</xsl:attribute>
						<xsl:choose>
							<xsl:when test="$from = $to or $to=''">
								<xsl:text>Time period: </xsl:text>
								<xsl:value-of select="$from"/>
							</xsl:when>
							<xsl:otherwise>
								<xsl:text>Time period: </xsl:text>
								<xsl:value-of select="concat($from, ' to ', $to)"/>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:element>
				</xsl:if>
			</xsl:if>		
		</xsl:element>
	</xsl:element>
	  
	  
	<!-- Create all the associated party objects for individuals -->
	<xsl:for-each-group select="descendant::gmd:CI_ResponsibleParty[not(ancestor::gmd:thesaurusName) and gmd:individualName[not(@gco:nilReason)] and not(gmd:role/gmd:CI_RoleCode/@codeListValue='')]" group-by="gmd:individualName/gco:CharacterString">
		<xsl:element name="registryObject">
			<xsl:attribute name="group">
				<xsl:value-of select="$group"/>
			</xsl:attribute>
			<xsl:element name="key">
				<xsl:value-of select="util:generateHash(translate(concat($group,current-grouping-key()), ' ', ''))"/>
			</xsl:element>
			<xsl:element name="originatingSource">
				<xsl:value-of select="$origSource"/>
			</xsl:element>
			<xsl:element name="party">
				<xsl:attribute name="type">
					<xsl:text>person</xsl:text>
				</xsl:attribute>
				<xsl:element name="name">
					<xsl:attribute name="type">
						<xsl:text>primary</xsl:text>
					</xsl:attribute>
					<xsl:element name="namePart">
						<xsl:attribute name="type">
							<xsl:text>full</xsl:text>
						</xsl:attribute>
						<xsl:value-of select="current-grouping-key()"/>
					</xsl:element>
				</xsl:element>
				
				<!-- to normalise parties within a single record we need to group them, obtain the fragment for each party with the most information, and at the same time cope with rubbish data. In the end the only way to cope is to ensure at least an organisation name, city, phone or fax exists (sigh) -->
				<xsl:for-each select="current-group()">
					<xsl:sort select="count(gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/child::*) + count(gmd:contactInfo/gmd:CI_Contact/gmd:phone/gmd:CI_Telephone/child::*)" data-type="number" order="descending"/>
					<xsl:choose>
						<xsl:when test="position()=1">
							<xsl:if test="gmd:organisationName[not(@gco:nilReason)] or gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:city or gmd:contactInfo/gmd:CI_Contact/gmd:phone/gmd:CI_Telephone[gmd:voice or gmd:fax]">
								<xsl:element name="location">
									<xsl:element name="address">
										<xsl:apply-templates select="gmd:contactInfo/gmd:CI_Contact/gmd:phone/gmd:CI_Telephone/gmd:voice"/>
										<xsl:apply-templates select="gmd:contactInfo/gmd:CI_Contact/gmd:phone/gmd:CI_Telephone/gmd:facsimile"/>
										<xsl:if test="not(gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:city/gco:CharacterString='')">
											<xsl:element name="physical">
												<xsl:attribute name="type">
													<xsl:text>streetAddress</xsl:text>
												</xsl:attribute>
												<xsl:apply-templates select="gmd:organisationName"/>
												<xsl:apply-templates select="gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:deliveryPoint"/>
												<xsl:apply-templates select="gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:city"/>
												<xsl:apply-templates select="gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:administrativeArea[not(@gco:nilReason)]"/>
												<xsl:apply-templates select="gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:postalCode[not(@gco:nilReason)]"/>
												<xsl:apply-templates select="gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:country"/>
											</xsl:element>
										</xsl:if>
									</xsl:element>
								</xsl:element>
							</xsl:if>
						</xsl:when>
					</xsl:choose>
				</xsl:for-each>
		
				<xsl:element name="relatedObject">
					<xsl:element name="key">
						<xsl:value-of select="ancestor::mcp:MD_Metadata/gmd:fileIdentifier/gco:CharacterString"/>
					</xsl:element>	

					<xsl:for-each-group select="gmd:role" group-by="gmd:CI_RoleCode/@codeListValue">
						<xsl:variable name="code">
							<xsl:value-of select="current-grouping-key()"/>
						</xsl:variable>
						
						<xsl:element name="relation">
							<xsl:attribute name="type">
								<xsl:value-of select="$code"/>
							</xsl:attribute>
						</xsl:element>		
					</xsl:for-each-group>
				</xsl:element>
			</xsl:element>
		</xsl:element>
	</xsl:for-each-group>


	<!-- Create all the associated party objects for organisations -->
	<xsl:for-each-group select="descendant::gmd:CI_ResponsibleParty[not(ancestor::gmd:thesaurusName) and (gmd:individualName[@gco:nilReason] or not(gmd:individualName)) and gmd:organisationName[not(@gco:nilReason)] and not(gmd:role/gmd:CI_RoleCode/@codeListValue='')]" group-by="gmd:organisationName/gco:CharacterString">
		<xsl:element name="registryObject">
			<xsl:attribute name="group">
				<xsl:value-of select="$group"/>
			</xsl:attribute>
			<xsl:element name="key">
				<xsl:value-of select="util:generateHash(translate(concat($group,current-grouping-key()), ' ', ''))"/>
			</xsl:element>
			<xsl:element name="originatingSource">
				<xsl:value-of select="$origSource"/>
			</xsl:element>
			<xsl:element name="party">
				<xsl:attribute name="type">
					<xsl:text>group</xsl:text>
				</xsl:attribute>
				<xsl:element name="name">
					<xsl:attribute name="type">
						<xsl:text>primary</xsl:text>
					</xsl:attribute>
					<xsl:element name="namePart">
						<xsl:attribute name="type">
							<xsl:text>full</xsl:text>
						</xsl:attribute>
						<xsl:value-of select="current-grouping-key()"/>
					</xsl:element>
				</xsl:element>
				
				<!-- to normalise parties within a single record we need to group them, obtain the fragment for each party with the most information, and at the same time cope with rubbish data. In the end the only way to cope is to ensure at least an organisation name, city, phone or fax exists (sigh) -->
				<xsl:for-each select="current-group()">
					<xsl:sort select="count(gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/child::*) + count(gmd:contactInfo/gmd:CI_Contact/gmd:phone/gmd:CI_Telephone/child::*)" data-type="number" order="descending"/>
					<xsl:choose>
						<xsl:when test="position()=1">
							<xsl:if test="gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:city or gmd:contactInfo/gmd:CI_Contact/gmd:phone/gmd:CI_Telephone[gmd:voice or gmd:fax]">
								<xsl:element name="location">
									<xsl:element name="address">
										<xsl:apply-templates select="gmd:contactInfo/gmd:CI_Contact/gmd:phone/gmd:CI_Telephone/gmd:voice"/>
										<xsl:apply-templates select="gmd:contactInfo/gmd:CI_Contact/gmd:phone/gmd:CI_Telephone/gmd:facsimile"/>
										<xsl:if test="not(gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:city/gco:CharacterString='')">
											<xsl:element name="physical">
												<xsl:attribute name="type">
													<xsl:text>streetAddress</xsl:text>
												</xsl:attribute>
												<xsl:apply-templates select="gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:deliveryPoint"/>
												<xsl:apply-templates select="gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:city"/>
												<xsl:apply-templates select="gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:administrativeArea[not(@gco:nilReason)]"/>
												<xsl:apply-templates select="gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:postalCode[not(@gco:nilReason)]"/>
												<xsl:apply-templates select="gmd:contactInfo/gmd:CI_Contact/gmd:address/gmd:CI_Address/gmd:country"/>
										</xsl:element>
										</xsl:if>
									</xsl:element>
								</xsl:element>
							</xsl:if>
						</xsl:when>
					</xsl:choose>
				</xsl:for-each>
		
				<xsl:element name="relatedObject">
					<xsl:element name="key">
						<xsl:value-of select="ancestor::mcp:MD_Metadata/gmd:fileIdentifier/gco:CharacterString"/>
					</xsl:element>	

					<xsl:for-each-group select="gmd:role" group-by="gmd:CI_RoleCode/@codeListValue">
						<xsl:variable name="code">
							<xsl:value-of select="current-grouping-key()"/>
						</xsl:variable>
						
						<xsl:element name="relation">
							<xsl:attribute name="type">
								<xsl:value-of select="$code"/>
							</xsl:attribute>
						</xsl:element>		
					</xsl:for-each-group>
				</xsl:element>
			</xsl:element>
		</xsl:element>
	</xsl:for-each-group>

</xsl:template>




<xsl:template match="node()"/>

<xsl:template name="splitSubject">
	<xsl:param name="string"/>
	<xsl:param name="separator" select="', '"/>

    <xsl:choose>
    	<xsl:when test="contains($string, $separator)">
      		<xsl:if test="not(starts-with($string, $separator))">

       			<xsl:element name="subject">
					<xsl:attribute name="type">
						<xsl:text>local</xsl:text>
					</xsl:attribute>
					<xsl:value-of select="substring-before($string, $separator)"/>
				</xsl:element>
	       </xsl:if>
      	   <xsl:call-template name="splitSubject">
			   <xsl:with-param name="string" select="substring-after($string,$separator)" />
	       </xsl:call-template>
   		 </xsl:when>
	     <xsl:otherwise>

  			<xsl:element name="subject">
				<xsl:attribute name="type">
					<xsl:text>local</xsl:text>
				</xsl:attribute>
				<xsl:value-of select="$string"/>
			</xsl:element>
	    </xsl:otherwise>
	</xsl:choose>

</xsl:template>

<xsl:template name="gmlToKml">
	<xsl:param name="coords"/>
	
	<xsl:for-each select="tokenize($coords, ', ')">
		<xsl:choose>
			<xsl:when test="position()=last()">
				<xsl:value-of select="."/>
			</xsl:when>
			<xsl:when test="position() mod 2 = 0">
				<xsl:value-of select="."/>
				<xsl:text> </xsl:text>
			</xsl:when>
			<xsl:when test="position() mod 2 = 1">
				<xsl:value-of select="."/>
				<xsl:text>,</xsl:text>
			</xsl:when>
		</xsl:choose>
	</xsl:for-each>
	
</xsl:template>

</xsl:stylesheet>
