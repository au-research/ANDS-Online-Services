<?xml version="1.0" encoding="UTF-8"?>

<!-- Date Modified: $Date: 2009-08-18 12:13:42 +1000 (Tue, 18 Aug 2009) $ -->
<!-- Version: $Revision: 81 $ -->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:iesr="http://iesr.ac.uk/terms/#" xmlns:iesrd="http://iesr.ac.uk/" xmlns:rslpcd="http://purl.org/rslp/terms#" xmlns:oai="http://www.openarchives.org/OAI/2.0/"  xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.openarchives.org/OAI/2.0/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:rif="http://apsr.edu.au/standards/iso2146/registryObjects" version="2.0">
    <xsl:output  indent="yes" method="xml"/>
    <xsl:strip-space elements="*"/>
	<!-- TODO: to be passed in by calling class -->
	<xsl:variable name="recordGroup">
		<xsl:value-of select="'iesr.ac.uk'"/>
	</xsl:variable>
	
	<xsl:template match="oai:metadata">
		<xsl:element name="{name()}">
			<xsl:apply-templates select="@*"/>
			<xsl:element name="rif:registryObjects">
				<xsl:attribute name="xsi:schemaLocation">
					<xsl:text>http://apsr.edu.au/standards/iso2146/registryObjects http://pilot.apsr.edu.au/public/rif/schema/registryObjects.xsd</xsl:text>
				</xsl:attribute>
				<xsl:apply-templates/>
			</xsl:element>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="oai:about">
		<xsl:copy-of select="."/>
	</xsl:template>

	<xsl:template match="iesrd:iesrDescription">
		<xsl:element name="rif:registryObject">
			<xsl:variable name="dateCreated">
				<xsl:choose>
					<xsl:when test="parent::oai:metadata/following-sibling::oai:about/oai_dc:dc/dc:date[1]">
						<xsl:value-of select="dateTime(parent::oai:metadata/following-sibling::oai:about/oai_dc:dc/dc:date[1],xsd:time('00:00:00Z'))"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="current-dateTime()"/>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:variable>
			<xsl:attribute name="dateCreated">
				<xsl:value-of select="$dateCreated"/>
			</xsl:attribute>
			<xsl:attribute name="dateModified">
				<xsl:value-of select="$dateCreated"/>
			</xsl:attribute>
			<xsl:attribute name="group">
				<xsl:value-of select="$recordGroup"/>
			</xsl:attribute>
			<xsl:apply-templates/>
		</xsl:element>
	</xsl:template>

	<xsl:template match="iesr:Collection">
		<xsl:variable name="collectionType">
			<xsl:value-of select="dc:type[@xsi:type='dcterms:DCMIType']"/>
			<!--xsl:value-of select="dc:type"/-->
		</xsl:variable>
		<xsl:variable name="tokens">
			<xsl:value-of select="tokenize(dc:identifier, '/')"/>
		</xsl:variable>
		
		<xsl:element name="rif:key">
			<!--xsl:value-of select="concat($recordGroup, '.', item-at($tokens[count($tokens)])"/-->
			<xsl:choose>
				<xsl:when test="dcterms:isReferencedBy">
					<xsl:value-of select="dcterms:isReferencedBy"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="dc:identifier[@xsi:type='dcterms:URI']"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:element>
		
		<xsl:element name="rif:collection">
			<xsl:attribute name="type">
				<xsl:choose>
					<xsl:when test="$collectionType='Collection'">
						<xsl:value-of select="'collection'"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="$collectionType"/>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
			<xsl:apply-templates select="dc:identifier"/>
			<xsl:apply-templates select="dc:title"/>
			<xsl:apply-templates select="dcterms:alternative"/>
			<xsl:apply-templates select="dcterms:isReferencedBy"/>
			<!--xsl:apply-templates select="dcterms:spatial"/-->
			<xsl:apply-templates select="iesr:usesControlledList"/>
			<xsl:apply-templates select="iesr:hasService"/>
			<xsl:apply-templates select="dcterms:isPartOf"/>
			<xsl:apply-templates select="iesr:madeAvailableBy"/>
			<xsl:apply-templates select="rslpcd:hasAssociation"/>
			<xsl:apply-templates select="rslpcd:owner"/>
			<xsl:apply-templates select="dc:subject"/>
			<xsl:apply-templates select="dcterms:abstract"/>
			<xsl:apply-templates select="dc:rights"/>
			<xsl:apply-templates select="dcterms:accessRights"/>
			<xsl:apply-templates select="iesr:logo"/>
		</xsl:element>
	</xsl:template>


	<xsl:template match="iesr:Service">
		<xsl:variable name="serviceType">
			<xsl:value-of select="dc:type[@xsi:type='iesr:AccMthdList']"/>
		</xsl:variable>
		<!--xsl:variable name="tokens">
			<xsl:value-of select="tokenize(dc:identifier, '/')"/>
		</xsl:variable-->
		
		<xsl:element name="rif:key">
			<xsl:value-of select="dc:identifier"/>
		</xsl:element>
		
		<xsl:element name="rif:service">
			<xsl:attribute name="type">
				<xsl:choose>
					<xsl:when test="$serviceType">
						<xsl:value-of select="$serviceType"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="'unspecified'"/>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
			<xsl:apply-templates select="dc:identifier"/>
			<xsl:apply-templates select="dc:title"/>
			<xsl:apply-templates select="dcterms:alternative"/>
			<xsl:apply-templates select="rslpcd:locator"/>
			<xsl:apply-templates select="iesr:mediator"/>
			<xsl:apply-templates select="rslpcd:administrator"/>
			<xsl:apply-templates select="iesr:supportsStandard"/>
			<xsl:apply-templates select="iesr:serves"/>
			<xsl:apply-templates select="iesr:interface"/>
			<xsl:apply-templates select="dcterms:abstract"/>
			<xsl:apply-templates select="dc:rights"/>
			<xsl:apply-templates select="dcterms:accessRights"/>
			<xsl:apply-templates select="iesr:useRights"/>
			<xsl:apply-templates select="iesr:logo"/>
			<xsl:apply-templates select="rslpcd:seeAlso"/>
		</xsl:element>
	</xsl:template>

	<xsl:template match="iesr:Agent">
		<xsl:element name="rif:key">
			<xsl:value-of select="dc:identifier[@xsi:type='dcterms:URI']"/>
		</xsl:element>
		
		<xsl:element name="rif:party">
			<xsl:attribute name="type">
				<xsl:text>group</xsl:text>
			</xsl:attribute>
			
			<xsl:apply-templates select="dc:identifier"/>
			<xsl:apply-templates select="dc:title" mode="agent"/>
			
			<xsl:if test="iesr:address or iesr:email or dc:relation">
				<xsl:element name="rif:location">
					<xsl:element name="rif:address">
						<xsl:apply-templates select="dc:relation"/>
						<!--xsl:apply-templates select="iesr:email"/-->
						<xsl:apply-templates select="iesr:address"/>
					</xsl:element>
				</xsl:element>
			</xsl:if>
			
			<xsl:apply-templates select="iesr:owns"/>
			<xsl:apply-templates select="iesr:administers"/>
			<xsl:apply-templates select="dc:description"/>
			<xsl:apply-templates select="iesr:logo"/>
		</xsl:element>
	</xsl:template>


	<xsl:template match="dc:identifier[not(@xsi:type='iesr:AthensInst')]">
		<xsl:element name="rif:identifier">
			<xsl:attribute name="type">
				<xsl:value-of select="'uri'"/>
			</xsl:attribute>
			<xsl:value-of select="."/>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="dc:title">
		<xsl:element name="rif:name">
			<xsl:copy-of select="@xml:lang"/>
			<xsl:value-of select="."/>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="dc:title" mode="agent">
		<xsl:element name="rif:name">
			<xsl:element name="rif:namePart">
				<xsl:copy-of select="@xml:lang"/>
				<xsl:value-of select="."/>
			</xsl:element>
		</xsl:element>
	</xsl:template>

	<xsl:template match="dcterms:alternative">
		<xsl:element name="rif:name">
			<xsl:attribute name="type">
				<xsl:value-of select="'alternative'"/>
			</xsl:attribute>
			<xsl:copy-of select="@xml:lang"/>
			<xsl:value-of select="."/>
		</xsl:element>
	</xsl:template>

	<xsl:template match="dcterms:isReferencedBy">
		<xsl:element name="rif:location">
			<xsl:element name="rif:address">
				<xsl:element name="rif:electronic">
					<xsl:attribute name="type">
						<xsl:value-of select="'url'"/>
					</xsl:attribute>
					<xsl:element name="rif:value">
						<xsl:value-of select="."/>
					</xsl:element>
				</xsl:element>
			</xsl:element>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="dcterms:spatial">
		<xsl:element name="rif:location">
			<xsl:element name="rif:spatial">
				<xsl:attribute name="type">
						<xsl:value-of select="'iso31661'"/>
				</xsl:attribute>
				<xsl:value-of select="."/>
			</xsl:element>
		</xsl:element>
	</xsl:template>

	<xsl:template match="iesr:usesControlledList">
		<xsl:element name="rif:relatedObject">
			<xsl:element name="rif:key">
				<xsl:value-of select="concat(@xsi:type, ':', .)"/>
			</xsl:element>
			<xsl:element name="rif:relation">
				<xsl:attribute name="type">
					<xsl:value-of select="'hasAssociationWith'"/>
				</xsl:attribute>
				<xsl:element name="rif:description">
					<xsl:value-of select="concat('Uses the ', ., ' controlled vocabulary')"/>
				</xsl:element>
			</xsl:element>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="iesr:supportsStandard">
		<xsl:element name="rif:relatedObject">
			<xsl:element name="rif:key">
				<xsl:value-of select="concat(@xsi:type, ':', .)"/>
			</xsl:element>
			<xsl:element name="rif:relation">
				<xsl:attribute name="type">
					<xsl:value-of select="'hasAssociationWith'"/>
				</xsl:attribute>
				<xsl:element name="rif:description">
					<xsl:value-of select="concat('Supports the ', ., ' standard')"/>
				</xsl:element>
			</xsl:element>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="iesr:hasService">
		<xsl:variable name="tokens">
			<xsl:value-of select="tokenize(., '/')"/>
		</xsl:variable>

		<xsl:element name="rif:relatedObject">
			<xsl:element name="rif:key">
				<!--xsl:value-of select="concat($recordGroup, '.', item-at($tokens[count($tokens)])"/-->
				<xsl:value-of select="."/>
			</xsl:element>
			<xsl:element name="rif:relation">
				<xsl:attribute name="type">
					<xsl:value-of select="'supports'"/>
				</xsl:attribute>
				<xsl:element name="rif:description">
					<xsl:value-of select="'Has Service'"/>
				</xsl:element>
				<!--xsl:element name="url">
					<xsl:value-of select="."/>
				</xsl:element-->
			</xsl:element>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="iesr:serves">
		<xsl:element name="rif:relatedObject">
			<xsl:element name="rif:key">
				<xsl:value-of select="."/>
			</xsl:element>
			<xsl:element name="rif:relation">
				<xsl:attribute name="type">
					<xsl:value-of select="'isSupportedBy'"/>
				</xsl:attribute>
				<xsl:element name="rif:description">
					<xsl:value-of select="'Serves Collection'"/>
				</xsl:element>
				<!--xsl:element name="url">
					<xsl:value-of select="."/>
				</xsl:element-->
			</xsl:element>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="dcterms:isPartOf">
		<xsl:variable name="tokens">
			<xsl:value-of select="tokenize(., '/')"/>
		</xsl:variable>

		<xsl:element name="rif:relatedObject">
			<xsl:element name="rif:key">
				<xsl:value-of select="."/>
			</xsl:element>
			<xsl:element name="rif:relation">
				<xsl:attribute name="type">
					<xsl:value-of select="'isPartOf'"/>
				</xsl:attribute>
				<xsl:element name="rif:description">
					<xsl:value-of select="'Is part of'"/>
				</xsl:element>
				<!--xsl:element name="url">
					<xsl:value-of select="."/>
				</xsl:element-->
			</xsl:element>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="iesr:madeAvailableBy">
		<xsl:element name="rif:relatedObject">
			<xsl:element name="rif:key">
				<xsl:value-of select="."/>
			</xsl:element>
			<xsl:element name="rif:relation">
				<xsl:attribute name="type">
					<xsl:value-of select="'hasAssociationWith'"/>
				</xsl:attribute>
				<xsl:element name="rif:description">
					<xsl:value-of select="'Made available by'"/>
				</xsl:element>
				<!--xsl:element name="url">
					<xsl:value-of select="."/>
				</xsl:element-->
			</xsl:element>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="rslpcd:owner">
		<xsl:variable name="tokens">
			<xsl:value-of select="tokenize(., '/')"/>
		</xsl:variable>

		<xsl:element name="rif:relatedObject">
			<xsl:element name="rif:key">
				<xsl:value-of select="."/>
			</xsl:element>
			<xsl:element name="rif:relation">
				<xsl:attribute name="type">
					<xsl:value-of select="'isOwnedBy'"/>
				</xsl:attribute>
				<xsl:element name="rif:description">
					<xsl:value-of select="'Is owned by'"/>
				</xsl:element>
				<!--xsl:element name="url">
					<xsl:value-of select="."/>
				</xsl:element-->
			</xsl:element>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="rslpcd:owns">
		<xsl:element name="rif:relatedObject">
			<xsl:element name="rif:key">
				<xsl:value-of select="."/>
			</xsl:element>
			<xsl:element name="rif:relation">
				<xsl:attribute name="type">
					<xsl:value-of select="'isOwnerOf'"/>
				</xsl:attribute>
				<xsl:element name="rif:description">
					<xsl:value-of select="'Owns'"/>
				</xsl:element>
				<!--xsl:element name="url">
					<xsl:value-of select="."/>
				</xsl:element-->
			</xsl:element>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="rslpcd:administrator">
		<xsl:element name="rif:relatedObject">
			<xsl:element name="rif:key">
				<xsl:value-of select="."/>
			</xsl:element>
			<xsl:element name="rif:relation">
				<xsl:attribute name="type">
					<xsl:value-of select="'isManagedBy'"/>
				</xsl:attribute>
				<xsl:element name="rif:description">
					<xsl:value-of select="'Service is administered by'"/>
				</xsl:element>
				<!--xsl:element name="url">
					<xsl:value-of select="."/>
				</xsl:element-->
			</xsl:element>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="rslpcd:administers">
		<xsl:element name="rif:relatedObject">
			<xsl:element name="rif:key">
				<xsl:value-of select="."/>
			</xsl:element>
			<xsl:element name="rif:relation">
				<xsl:attribute name="type">
					<xsl:value-of select="'isManagerOf'"/>
				</xsl:attribute>
				<xsl:element name="rif:description">
					<xsl:value-of select="'Administers'"/>
				</xsl:element>
				<!--xsl:element name="url">
					<xsl:value-of select="."/>
				</xsl:element-->
			</xsl:element>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="iesr:mediator">
		<xsl:element name="rif:relatedObject">
			<xsl:element name="rif:key">
				<xsl:value-of select="."/>
			</xsl:element>
			<xsl:element name="rif:relation">
				<xsl:attribute name="type">
					<xsl:value-of select="'hasAssociationWith'"/>
				</xsl:attribute>
				<xsl:element name="rif:description">
					<xsl:value-of select="'Mediator'"/>
				</xsl:element>
				<!--xsl:element name="url">
					<xsl:value-of select="."/>
				</xsl:element-->
			</xsl:element>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="rslpcd:hasAssociation">
		<xsl:variable name="tokens">
			<xsl:value-of select="tokenize(., '/')"/>
		</xsl:variable>

		<xsl:element name="rif:relatedObject">
			<xsl:element name="rif:key">
				<xsl:value-of select="."/>
			</xsl:element>
			<xsl:element name="rif:relation">
				<xsl:attribute name="type">
					<xsl:value-of select="'hasAssociationWith'"/>
				</xsl:attribute>
				<xsl:element name="rif:description">
					<xsl:value-of select="'Unspecified association'"/>
				</xsl:element>
				<!--xsl:element name="url">
					<xsl:value-of select="."/>
				</xsl:element-->
			</xsl:element>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="dc:subject[@xsi:type]">
		<xsl:element name="rif:subject">
			<xsl:attribute name="type">
				<xsl:value-of select="lower-case(tokenize(@xsi:type, ':')[2])"/>
			</xsl:attribute>
			<xsl:value-of select="."/>
		</xsl:element>
	</xsl:template>

	<xsl:template match="dcterms:abstract|dc:description">
		<xsl:element name="rif:description">
			<xsl:attribute name="type">
				<xsl:value-of select="'brief'"/>
			</xsl:attribute>
			<xsl:copy-of select="@xml:lang"/>
			<xsl:value-of select="."/>
		</xsl:element>
	</xsl:template>

	<xsl:template match="dc:rights">
		<xsl:element name="rif:description">
			<xsl:attribute name="type">
				<xsl:value-of select="'rights'"/>
			</xsl:attribute>
			<xsl:value-of select="."/>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="dcterms:accessRights">
		<xsl:element name="rif:description">
			<xsl:attribute name="type">
				<xsl:value-of select="'accessRights'"/>
			</xsl:attribute>
			<xsl:value-of select="."/>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="dcterms:useRights">
		<xsl:element name="rif:description">
			<xsl:attribute name="type">
				<xsl:value-of select="'useRights'"/>
			</xsl:attribute>
			<xsl:value-of select="."/>
		</xsl:element>
	</xsl:template>

	<xsl:template match="iesr:logo">
		<xsl:element name="rif:description">
			<xsl:attribute name="type">
				<xsl:value-of select="'logo'"/>
			</xsl:attribute>
			<xsl:value-of select="."/>
		</xsl:element>
	</xsl:template>

	<xsl:template match="iesr:interface">
		<xsl:element name="rif:description">
			<xsl:attribute name="type">
				<xsl:value-of select="'full'"/>
			</xsl:attribute>
			<xsl:value-of select="."/>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="rslpcd:locator">
		<xsl:element name="rif:location">
			<xsl:element name="rif:address">
				<xsl:element name="rif:electronic">
					<xsl:attribute name="type">
						<xsl:value-of select="'url'"/>
					</xsl:attribute>
					<xsl:element name="rif:value">
						<xsl:value-of select="."/>
					</xsl:element>
				</xsl:element>
			</xsl:element>
		</xsl:element>
	</xsl:template>

	<xsl:template match="iesr:address">
		<xsl:element name="rif:physical">
			<xsl:attribute name="type">
				<xsl:text>postalAddress</xsl:text>
			</xsl:attribute>
			<xsl:element name="rif:addressPart">
				<xsl:attribute name="type">
					<xsl:text>addressLine</xsl:text>
				</xsl:attribute>
				<xsl:value-of select="."/>
			</xsl:element>
			<xsl:if test="following-sibling::iesr:postcode">
				<xsl:element name="rif:addressPart">
					<xsl:attribute name="type">
						<xsl:text>postCode</xsl:text>
					</xsl:attribute>
					<xsl:value-of select="following-sibling::iesr:postcode"/>
				</xsl:element>			
			</xsl:if>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="iesr:email">
		<xsl:element name="rif:electronic">
			<xsl:attribute name="type">
				<xsl:value-of select="'email'"/>
			</xsl:attribute>
			<xsl:element name="rif:value">
				<xsl:value-of select="."/>
			</xsl:element>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="dc:relation">
		<xsl:element name="rif:electronic">
			<xsl:attribute name="type">
				<xsl:value-of select="'url'"/>
			</xsl:attribute>
			<xsl:element name="rif:value">
				<xsl:value-of select="."/>
			</xsl:element>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="rslpcd:seeAlso">
		<xsl:element name="rif:relatedInfo">
			<xsl:value-of select="."/>
		</xsl:element>
	</xsl:template>

    <xsl:template match="oai:*">
    	<xsl:element name="{name()}">
    		<xsl:apply-templates select="@*"/>
    		<xsl:apply-templates select="text()" mode="oai"/>
	    	<xsl:apply-templates/>
	   	</xsl:element>
    </xsl:template>
    
    <xsl:template match="@*">
    	<xsl:copy-of select="."/>
   	</xsl:template>

    <xsl:template match="text()" mode="oai">
   		<xsl:value-of select="."/>
   	</xsl:template>

    <!-- ignore everything not matching a template -->
	<xsl:template match="node()|text()"/>
	
</xsl:stylesheet>
