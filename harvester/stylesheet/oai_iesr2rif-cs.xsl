<?xml version="1.0" encoding="UTF-8"?>

<!-- Date Modified: $Date: 2009-09-15 11:27:38 +1000 (Tue, 15 Sep 2009) $ -->
<!-- Version: $Revision: 145 $ -->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:iesr="http://iesr.ac.uk/terms/#" xmlns:iesrd="http://iesr.ac.uk/" xmlns:rslpcd="http://purl.org/rslp/terms#" xmlns:oai="http://www.openarchives.org/OAI/2.0/"  xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/"  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:xsd="http://www.w3.org/2001/XMLSchema" version="2.0">
    <xsl:output  indent="yes" method="xml"/>

	<!-- TODO: to be passed in by calling class -->
	<xsl:variable name="recordGroup">
		<xsl:value-of select="'IESR'"/>
	</xsl:variable>
	
	<xsl:variable name="origSource">
		<xsl:value-of select="'http://iesr.ac.uk/service/iesroai'"/>
	</xsl:variable>

    <xsl:template match="oai:metadata|oai:ListRecords|oai:record">
    	<xsl:apply-templates/>
    </xsl:template>

	<xsl:template match="oai:OAI-PMH">
		<xsl:element name="registryObjects">
			<xsl:attribute name="xsi:schemaLocation">
				<xsl:text>http://ands.org.au/standards/rif-cs/registryObjects http://services.ands.org.au/home/orca/schemata/registryObjects.xsd</xsl:text>
			</xsl:attribute>
			<xsl:apply-templates/>
		</xsl:element>
	</xsl:template>

	<xsl:template match="iesrd:iesrDescription">
		<xsl:element name="registryObject">
			<xsl:variable name="dateModified">
				<xsl:choose>
					<xsl:when test="parent::oai:metadata/following-sibling::oai:about/oai_dc:dc/dc:date[1]">
						<xsl:value-of select="dateTime(parent::oai:metadata/following-sibling::oai:about/oai_dc:dc/dc:date[1],xsd:time('00:00:00Z'))"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="current-dateTime()"/>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:variable>
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
		
		<xsl:variable name="dateModified">
			<xsl:if test="ancestor::oai:metadata/following-sibling::oai:about/oai_dc:dc/dc:date[1]">
				<xsl:value-of select="dateTime(ancestor::oai:metadata/following-sibling::oai:about/oai_dc:dc/dc:date[1],xsd:time('00:00:00Z'))"/>
			</xsl:if>
		</xsl:variable>

		<xsl:variable name="tokens">
			<xsl:value-of select="tokenize(dc:identifier, '/')"/>
		</xsl:variable>
		
		<xsl:element name="key">
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

		<xsl:element name="originatingSource">
			<xsl:value-of select="$origSource"/>
		</xsl:element>
		
		<xsl:element name="collection">
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
			<xsl:if test="$dateModified">
				<xsl:attribute name="dateModified">
					<xsl:value-of select="$dateModified"/>
				</xsl:attribute>
			</xsl:if>
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
		
		<xsl:variable name="dateModified">
			<xsl:if test="ancestor::oai:metadata/following-sibling::oai:about/oai_dc:dc/dc:date[1]">
				<xsl:value-of select="dateTime(ancestor::oai:metadata/following-sibling::oai:about/oai_dc:dc/dc:date[1],xsd:time('00:00:00Z'))"/>
			</xsl:if>
		</xsl:variable>
		
		<xsl:element name="key">
			<xsl:value-of select="dc:identifier"/>
		</xsl:element>
		
		<xsl:element name="originatingSource">
			<xsl:value-of select="$origSource"/>
		</xsl:element>

		<xsl:element name="service">
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
			<xsl:if test="$dateModified">
				<xsl:attribute name="dateModified">
					<xsl:value-of select="$dateModified"/>
				</xsl:attribute>
			</xsl:if>

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
		<xsl:variable name="dateModified">
			<xsl:if test="ancestor::oai:metadata/following-sibling::oai:about/oai_dc:dc/dc:date[1]">
				<xsl:value-of select="dateTime(ancestor::oai:metadata/following-sibling::oai:about/oai_dc:dc/dc:date[1],xsd:time('00:00:00Z'))"/>
			</xsl:if>
		</xsl:variable>
		
		<xsl:element name="key">
			<xsl:value-of select="dc:identifier[@xsi:type='dcterms:URI']"/>
		</xsl:element>
		
		<xsl:element name="originatingSource">
			<xsl:value-of select="$origSource"/>
		</xsl:element>

		<xsl:element name="party">
			<xsl:attribute name="type">
				<xsl:text>group</xsl:text>
			</xsl:attribute>
			<xsl:if test="$dateModified">
				<xsl:attribute name="dateModified">
					<xsl:value-of select="$dateModified"/>
				</xsl:attribute>
			</xsl:if>

			<xsl:apply-templates select="dc:identifier"/>
			<!--xsl:apply-templates select="dc:title" mode="agent"/-->
			<xsl:apply-templates select="dc:title"/>
			
			<xsl:if test="iesr:address or iesr:email or dc:relation">
				<xsl:element name="location">
					<xsl:element name="address">
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
		<xsl:element name="identifier">
			<xsl:attribute name="type">
				<xsl:value-of select="'uri'"/>
			</xsl:attribute>
			<xsl:value-of select="."/>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="dc:title">
		<xsl:element name="name">
			<xsl:attribute name="type">
				<xsl:value-of select="'primary'"/>
			</xsl:attribute>
			<xsl:element name="namePart">
				<xsl:value-of select="."/>
			</xsl:element>
		</xsl:element>
	</xsl:template>
	
	<!--xsl:template match="dc:title" mode="agent">
		<xsl:element name="name">
			<xsl:element name="namePart">
				<xsl:value-of select="."/>
			</xsl:element>
		</xsl:element>
	</xsl:template-->

	<xsl:template match="dcterms:alternative">
		<xsl:element name="name">
			<xsl:attribute name="type">
				<xsl:value-of select="'alternative'"/>
			</xsl:attribute>
			<xsl:element name="namePart">
				<xsl:value-of select="."/>
			</xsl:element>
		</xsl:element>
	</xsl:template>

	<xsl:template match="dcterms:isReferencedBy">
		<xsl:element name="location">
			<xsl:element name="address">
				<xsl:element name="electronic">
					<xsl:attribute name="type">
						<xsl:value-of select="'url'"/>
					</xsl:attribute>
					<xsl:element name="value">
						<xsl:value-of select="."/>
					</xsl:element>
				</xsl:element>
			</xsl:element>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="dcterms:spatial">
		<xsl:element name="location">
			<xsl:element name="spatial">
				<xsl:attribute name="type">
						<xsl:value-of select="'iso31661'"/>
				</xsl:attribute>
				<xsl:value-of select="."/>
			</xsl:element>
		</xsl:element>
	</xsl:template>

	<xsl:template match="iesr:usesControlledList">
		<xsl:element name="relatedObject">
			<xsl:element name="key">
				<xsl:value-of select="concat(@xsi:type, ':', .)"/>
			</xsl:element>
			<xsl:element name="relation">
				<xsl:attribute name="type">
					<xsl:value-of select="'hasAssociationWith'"/>
				</xsl:attribute>
				<xsl:element name="description">
					<xsl:value-of select="concat('Uses the ', ., ' controlled vocabulary')"/>
				</xsl:element>
			</xsl:element>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="iesr:supportsStandard">
		<xsl:element name="relatedObject">
			<xsl:element name="key">
				<xsl:value-of select="concat(@xsi:type, ':', .)"/>
			</xsl:element>
			<xsl:element name="relation">
				<xsl:attribute name="type">
					<xsl:value-of select="'hasAssociationWith'"/>
				</xsl:attribute>
				<xsl:element name="description">
					<xsl:value-of select="concat('Supports the ', ., ' standard')"/>
				</xsl:element>
			</xsl:element>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="iesr:hasService">
		<xsl:variable name="tokens">
			<xsl:value-of select="tokenize(., '/')"/>
		</xsl:variable>

		<xsl:element name="relatedObject">
			<xsl:element name="key">
				<!--xsl:value-of select="concat($recordGroup, '.', item-at($tokens[count($tokens)])"/-->
				<xsl:value-of select="."/>
			</xsl:element>
			<xsl:element name="relation">
				<xsl:attribute name="type">
					<xsl:value-of select="'supports'"/>
				</xsl:attribute>
				<xsl:element name="description">
					<xsl:value-of select="'Has Service'"/>
				</xsl:element>
				<!--xsl:element name="url">
					<xsl:value-of select="."/>
				</xsl:element-->
			</xsl:element>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="iesr:serves">
		<xsl:element name="relatedObject">
			<xsl:element name="key">
				<xsl:value-of select="."/>
			</xsl:element>
			<xsl:element name="relation">
				<xsl:attribute name="type">
					<xsl:value-of select="'isSupportedBy'"/>
				</xsl:attribute>
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

		<xsl:element name="relatedObject">
			<xsl:element name="key">
				<xsl:value-of select="."/>
			</xsl:element>
			<xsl:element name="relation">
				<xsl:attribute name="type">
					<xsl:value-of select="'isPartOf'"/>
				</xsl:attribute>
				<!--xsl:element name="url">
					<xsl:value-of select="."/>
				</xsl:element-->
			</xsl:element>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="iesr:madeAvailableBy">
		<xsl:element name="relatedObject">
			<xsl:element name="key">
				<xsl:value-of select="."/>
			</xsl:element>
			<xsl:element name="relation">
				<xsl:attribute name="type">
					<xsl:value-of select="'hasAssociationWith'"/>
				</xsl:attribute>
				<xsl:element name="description">
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

		<xsl:element name="relatedObject">
			<xsl:element name="key">
				<xsl:value-of select="."/>
			</xsl:element>
			<xsl:element name="relation">
				<xsl:attribute name="type">
					<xsl:value-of select="'isOwnedBy'"/>
				</xsl:attribute>
				<!--xsl:element name="url">
					<xsl:value-of select="."/>
				</xsl:element-->
			</xsl:element>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="rslpcd:owns">
		<xsl:element name="relatedObject">
			<xsl:element name="key">
				<xsl:value-of select="."/>
			</xsl:element>
			<xsl:element name="relation">
				<xsl:attribute name="type">
					<xsl:value-of select="'isOwnerOf'"/>
				</xsl:attribute>
				<!--xsl:element name="url">
					<xsl:value-of select="."/>
				</xsl:element-->
			</xsl:element>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="rslpcd:administrator">
		<xsl:element name="relatedObject">
			<xsl:element name="key">
				<xsl:value-of select="."/>
			</xsl:element>
			<xsl:element name="relation">
				<xsl:attribute name="type">
					<xsl:value-of select="'isManagedBy'"/>
				</xsl:attribute>
				<!--xsl:element name="url">
					<xsl:value-of select="."/>
				</xsl:element-->
			</xsl:element>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="rslpcd:administers">
		<xsl:element name="relatedObject">
			<xsl:element name="key">
				<xsl:value-of select="."/>
			</xsl:element>
			<xsl:element name="relation">
				<xsl:attribute name="type">
					<xsl:value-of select="'isManagerOf'"/>
				</xsl:attribute>
				<!--xsl:element name="url">
					<xsl:value-of select="."/>
				</xsl:element-->
			</xsl:element>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="iesr:mediator">
		<xsl:element name="relatedObject">
			<xsl:element name="key">
				<xsl:value-of select="."/>
			</xsl:element>
			<xsl:element name="relation">
				<xsl:attribute name="type">
					<xsl:value-of select="'hasAssociationWith'"/>
				</xsl:attribute>
				<xsl:element name="description">
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

		<xsl:element name="relatedObject">
			<xsl:element name="key">
				<xsl:value-of select="."/>
			</xsl:element>
			<xsl:element name="relation">
				<xsl:attribute name="type">
					<xsl:value-of select="'hasAssociationWith'"/>
				</xsl:attribute>
				<!--xsl:element name="url">
					<xsl:value-of select="."/>
				</xsl:element-->
			</xsl:element>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="dc:subject[@xsi:type]">
		<xsl:element name="subject">
			<xsl:attribute name="type">
				<xsl:value-of select="lower-case(tokenize(@xsi:type, ':')[2])"/>
			</xsl:attribute>
			<xsl:value-of select="."/>
		</xsl:element>
	</xsl:template>

	<xsl:template match="dcterms:abstract|dc:description">
		<xsl:element name="description">
			<xsl:attribute name="type">
				<xsl:value-of select="'brief'"/>
			</xsl:attribute>
			<xsl:copy-of select="@xml:lang"/>
			<xsl:value-of select="."/>
		</xsl:element>
	</xsl:template>

	<xsl:template match="dc:rights">
		<xsl:element name="description">
			<xsl:attribute name="type">
				<xsl:value-of select="'rights'"/>
			</xsl:attribute>
			<xsl:value-of select="."/>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="dcterms:accessRights">
		<xsl:element name="description">
			<xsl:attribute name="type">
				<xsl:value-of select="'accessRights'"/>
			</xsl:attribute>
			<xsl:value-of select="."/>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="dcterms:useRights">
		<xsl:element name="description">
			<xsl:attribute name="type">
				<xsl:value-of select="'useRights'"/>
			</xsl:attribute>
			<xsl:value-of select="."/>
		</xsl:element>
	</xsl:template>

	<xsl:template match="iesr:logo">
		<xsl:element name="description">
			<xsl:attribute name="type">
				<xsl:value-of select="'logo'"/>
			</xsl:attribute>
			<xsl:value-of select="."/>
		</xsl:element>
	</xsl:template>

	<xsl:template match="iesr:interface">
		<xsl:element name="description">
			<xsl:attribute name="type">
				<xsl:value-of select="'full'"/>
			</xsl:attribute>
			<xsl:value-of select="."/>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="rslpcd:locator">
		<xsl:element name="location">
			<xsl:element name="address">
				<xsl:element name="electronic">
					<xsl:attribute name="type">
						<xsl:value-of select="'url'"/>
					</xsl:attribute>
					<xsl:element name="value">
						<xsl:value-of select="."/>
					</xsl:element>
				</xsl:element>
			</xsl:element>
		</xsl:element>
	</xsl:template>

	<xsl:template match="iesr:address">
		<xsl:element name="physical">
			<xsl:attribute name="type">
				<xsl:text>postalAddress</xsl:text>
			</xsl:attribute>
			<xsl:element name="addressPart">
				<xsl:attribute name="type">
					<xsl:text>addressLine</xsl:text>
				</xsl:attribute>
				<xsl:value-of select="."/>
			</xsl:element>
			<xsl:if test="following-sibling::iesr:postcode">
				<xsl:element name="addressPart">
					<xsl:attribute name="type">
						<xsl:text>postCode</xsl:text>
					</xsl:attribute>
					<xsl:value-of select="following-sibling::iesr:postcode"/>
				</xsl:element>			
			</xsl:if>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="iesr:email">
		<xsl:element name="electronic">
			<xsl:attribute name="type">
				<xsl:value-of select="'email'"/>
			</xsl:attribute>
			<xsl:element name="value">
				<xsl:value-of select="."/>
			</xsl:element>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="dc:relation">
		<xsl:element name="electronic">
			<xsl:attribute name="type">
				<xsl:value-of select="'url'"/>
			</xsl:attribute>
			<xsl:element name="value">
				<xsl:value-of select="."/>
			</xsl:element>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="rslpcd:seeAlso[not(@xsi:type='iesr:SvcShib')]">
		<xsl:element name="relatedInfo">
			<xsl:value-of select="."/>
		</xsl:element>
	</xsl:template>

    <!-- ignore everything not matching a template -->
	<xsl:template match="node()|text()"/>
	
</xsl:stylesheet>
