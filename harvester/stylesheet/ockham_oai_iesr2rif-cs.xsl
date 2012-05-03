<?xml version="1.0" encoding="UTF-8"?>

<!-- Date Modified: $Date: 2009-09-15 11:27:38 +1000 (Tue, 15 Sep 2009) $ -->
<!-- Version: $Revision: 145 $ -->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:iesr="http://iesr.ac.uk/terms/#" xmlns:iesrd="http://iesr.ac.uk/" xmlns:rslpcd="http://purl.org/rslp/terms#" xmlns:oai="http://www.openarchives.org/OAI/2.0/"  xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/"  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:xsd="http://www.w3.org/2001/XMLSchema" version="2.0">
    <xsl:output  indent="yes" method="xml"/>

	<!-- TODO: to be passed in by calling class -->
	<xsl:variable name="recordGroup">
		<xsl:value-of select="'OCKHAM'"/>
	</xsl:variable>

	<xsl:variable name="origSource">
		<xsl:value-of select="'http://registry.library.oregonstate.edu/oai'"/>
	</xsl:variable>
	
	<!--xsl:variable name="dateCreated">
		<xsl:value-of select="current-dateTime()"/>
	</xsl:variable-->
	
    <xsl:template match="oai:metadata|oai:ListRecords|oai:record|iesrd:iesrDescription">
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

	<xsl:template match="iesr:Collection">
		<xsl:element name="registryObject">
			<!--xsl:attribute name="dateCreated">
				<xsl:value-of select="$dateCreated"/>
			</xsl:attribute>
			<xsl:attribute name="dateModified">
				<xsl:value-of select="$dateCreated"/>
			</xsl:attribute-->
			<xsl:attribute name="group">
				<xsl:value-of select="$recordGroup"/>
			</xsl:attribute>
		
			<xsl:element name="key">
				<!--xsl:value-of select="concat($rcordGroup,'.',dc:identifier)"/-->
				<!--xsl:value-of select="concat('coll-', dc:identifier)"/-->
				<xsl:value-of select="dc:identifier"/>
			</xsl:element>
			
			<xsl:element name="originatingSource">
				<xsl:value-of select="$origSource"/>
			</xsl:element>
		
			<xsl:element name="collection">
				<xsl:attribute name="type">
					<xsl:value-of select="'collection'"/>
				</xsl:attribute>
				<xsl:apply-templates select="dc:identifier"/>
				<xsl:apply-templates select="dc:title"/>
				<xsl:apply-templates select="dcterms:alternative"/>
				<xsl:apply-templates select="dcterms:isReferencedBy"/>
				<xsl:apply-templates select="dcterms:spatial"/>
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
		</xsl:element>
	</xsl:template>


	<xsl:template match="iesr:Service">
		<xsl:element name="registryObject">
			<xsl:attribute name="group">
				<xsl:value-of select="$recordGroup"/>
			</xsl:attribute>
		
			<xsl:element name="key">
				<!--xsl:value-of select="concat('svc-', dc:identifier)"/-->
				<xsl:value-of select="dc:identifier"/>
			</xsl:element>
		
			<xsl:element name="originatingSource">
				<xsl:value-of select="$origSource"/>
			</xsl:element>

			<xsl:element name="service">
				<xsl:attribute name="type">
					<xsl:value-of select="dc:type"/>
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
		</xsl:element>
	</xsl:template>

	<xsl:template match="iesr:Agent">
		<xsl:element name="registryObject">
			<xsl:attribute name="group">
				<xsl:value-of select="$recordGroup"/>
			</xsl:attribute>
		
			<xsl:element name="key">
				<!--xsl:value-of select="concat('party-', dc:identifier)"/-->
				<xsl:value-of select="dc:identifier"/>
			</xsl:element>

			<xsl:element name="originatingSource">
				<xsl:value-of select="$origSource"/>
			</xsl:element>
		
			<xsl:element name="party">
				<xsl:attribute name="type">
					<xsl:text>group</xsl:text>
				</xsl:attribute>
			
				<xsl:apply-templates select="dc:identifier"/>
				<xsl:apply-templates select="dc:title" mode="agent"/>
			
				<xsl:if test="iesr:address or iesr:email or dc:relation">
					<xsl:element name="location">
						<xsl:element name="address">
							<xsl:apply-templates select="dc:relation"/>
							<xsl:apply-templates select="iesr:email"/>
							<!--xsl:apply-templates select="iesr:phone"/-->
							<xsl:apply-templates select="iesr:address"/>
						</xsl:element>
					</xsl:element>
				</xsl:if>
			
				<xsl:apply-templates select="iesr:owns"/>
				<xsl:apply-templates select="iesr:administers"/>
				<xsl:apply-templates select="dc:description"/>
				<xsl:apply-templates select="iesr:logo"/>
			</xsl:element>
		</xsl:element>
	</xsl:template>


	<xsl:template match="dc:identifier">
		<xsl:element name="identifier">
			<xsl:attribute name="type">
				<xsl:choose>
					<xsl:when test="parent::iesr:Agent">
						<xsl:value-of select="'local'"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="'uri'"/>
					</xsl:otherwise>
				</xsl:choose>
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
	
	<xsl:template match="dc:title" mode="agent">
		<xsl:element name="name">
			<xsl:attribute name="type">
				<xsl:value-of select="'primary'"/>
			</xsl:attribute>
			<xsl:element name="namePart">
				<xsl:value-of select="."/>
			</xsl:element>
		</xsl:element>
	</xsl:template>

	<xsl:template match="dcterms:alternative">
		<xsl:element name="name">
			<xsl:attribute name="type">
				<xsl:value-of select="'alternative'"/>
			</xsl:attribute>
			<xsl:copy-of select="@xml:lang"/>
			<xsl:value-of select="."/>
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
					<xsl:value-of select="'text'"/>
				</xsl:attribute>
				<xsl:value-of select="."/>
			</xsl:element>
		</xsl:element>
	</xsl:template>

	<!--xsl:template match="iesr:usesControlledList">
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
	</xsl:template-->
	
	<xsl:template match="iesr:supportsStandard">
		<xsl:element name="relatedObject">
			<xsl:element name="key">
				<xsl:value-of select="."/>
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
		<xsl:element name="relatedObject">
			<xsl:element name="key">
				<!--xsl:value-of select="concat('svc-', .)"/-->
				<xsl:value-of select="."/>
			</xsl:element>
			<xsl:element name="relation">
				<xsl:attribute name="type">
					<xsl:value-of select="'supports'"/>
				</xsl:attribute>
				<xsl:element name="url">
					<xsl:value-of select="."/>
				</xsl:element>
			</xsl:element>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="iesr:serves">
		<xsl:element name="relatedObject">
			<xsl:element name="key">
				<!--xsl:value-of select="concat('coll-', .)"/-->
				<xsl:value-of select="."/>
			</xsl:element>
			<xsl:element name="relation">
				<xsl:attribute name="type">
					<xsl:value-of select="'isSupportedBy'"/>
				</xsl:attribute>
				<xsl:element name="url">
					<xsl:value-of select="."/>
				</xsl:element>
			</xsl:element>
		</xsl:element>
	</xsl:template>
	
	<!--xsl:template match="dcterms:isPartOf">
		<xsl:element name="relatedObject">
			<xsl:element name="key">
				<xsl:value-of select="."/>
			</xsl:element>
			<xsl:element name="relation">
				<xsl:attribute name="type">
					<xsl:value-of select="'isPartOf'"/>
				</xsl:attribute>
				<xsl:element name="description">
					<xsl:value-of select="'Is part of'"/>
				</xsl:element>
				<xsl:element name="url">
					<xsl:value-of select="."/>
				</xsl:element>
			</xsl:element>
		</xsl:element>
	</xsl:template-->
	
	<!--xsl:template match="iesr:madeAvailableBy">
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
				<xsl:element name="url">
					<xsl:value-of select="."/>
				</xsl:element>
			</xsl:element>
		</xsl:element>
	</xsl:template-->
	
	<xsl:template match="rslpcd:owner">
		<xsl:element name="relatedObject">
			<xsl:element name="key">
				<!--xsl:value-of select="concat('party-', .)"/-->
				<xsl:value-of select="."/>
			</xsl:element>
			<xsl:element name="relation">
				<xsl:attribute name="type">
					<xsl:value-of select="'isOwnedBy'"/>
				</xsl:attribute>
				<xsl:element name="url">
					<xsl:value-of select="."/>
				</xsl:element>
			</xsl:element>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="iesr:owns">
		<xsl:element name="relatedObject">
			<xsl:element name="key">
				<!--xsl:value-of select="concat('coll-', .)"/-->
				<xsl:value-of select="."/>
			</xsl:element>
			<xsl:element name="relation">
				<xsl:attribute name="type">
					<xsl:value-of select="'isOwnerOf'"/>
				</xsl:attribute>
				<xsl:element name="url">
					<xsl:value-of select="."/>
				</xsl:element>
			</xsl:element>
		</xsl:element>
		<!--xsl:element name="relatedObject">
			<xsl:element name="key">
				<xsl:value-of select="concat('svc-', .)"/>
				<xsl:value-of select="."/>
			</xsl:element>
			<xsl:element name="relation">
				<xsl:attribute name="type">
					<xsl:value-of select="'isOwnerOf'"/>
				</xsl:attribute>
				<xsl:element name="description">
					<xsl:value-of select="'Owns'"/>
				</xsl:element>
				<xsl:element name="url">
					<xsl:value-of select="."/>
				</xsl:element>
			</xsl:element>
		</xsl:element-->
	</xsl:template>
	
	<xsl:template match="rslpcd:administrator">
		<xsl:element name="relatedObject">
			<xsl:element name="key">
				<!--xsl:value-of select="concat('party-', .)"/-->
				<xsl:value-of select="."/>
			</xsl:element>
			<xsl:element name="relation">
				<xsl:attribute name="type">
					<xsl:value-of select="'isManagedBy'"/>
				</xsl:attribute>
				<xsl:element name="url">
					<xsl:value-of select="."/>
				</xsl:element>
			</xsl:element>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="iesr:administers">
		<xsl:element name="relatedObject">
			<xsl:element name="key">
				<!--xsl:value-of select="concat('party-', .)"/-->
				<xsl:value-of select="."/>
			</xsl:element>
			<xsl:element name="relation">
				<xsl:attribute name="type">
					<xsl:value-of select="'isManagerOf'"/>
				</xsl:attribute>
				<xsl:element name="url">
					<xsl:value-of select="."/>
				</xsl:element>
			</xsl:element>
		</xsl:element>
	</xsl:template>
	
	<!--xsl:template match="iesr:mediator">
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
				<xsl:element name="url">
					<xsl:value-of select="."/>
				</xsl:element>
			</xsl:element>
		</xsl:element>
	</xsl:template-->
	
	<!--xsl:template match="rslpcd:hasAssociation">
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
				<xsl:element name="description">
					<xsl:value-of select="'Unspecified association'"/>
				</xsl:element>
				<xsl:element name="url">
					<xsl:value-of select="."/>
				</xsl:element>
			</xsl:element>
		</xsl:element>
	</xsl:template-->
	
	<xsl:template match="dc:subject">
		<xsl:element name="subject">
			<xsl:attribute name="type">
				<xsl:value-of select="'local'"/>
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

	<!--xsl:template match="iesr:interface">
		<xsl:element name="description">
			<xsl:attribute name="type">
				<xsl:value-of select="'full'"/>
			</xsl:attribute>
			<xsl:value-of select="."/>
		</xsl:element>
	</xsl:template-->
	
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

	<xsl:template match="iesr:phone">
		<xsl:element name="electronic">
			<xsl:attribute name="type">
				<xsl:value-of select="'voice'"/>
			</xsl:attribute>
			<xsl:element name="value">
				<xsl:value-of select="."/>
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
			<xsl:if test="preceding-sibling::iesr:postcode">
				<xsl:element name="addressPart">
					<xsl:attribute name="type">
						<xsl:text>postCode</xsl:text>
					</xsl:attribute>
					<xsl:value-of select="preceding-sibling::iesr:postcode"/>
				</xsl:element>
			</xsl:if>
			<xsl:if test="preceding-sibling::iesr:country">
				<xsl:element name="addressPart">
					<xsl:attribute name="type">
						<xsl:text>country</xsl:text>
					</xsl:attribute>
					<xsl:value-of select="preceding-sibling::iesr:country"/>
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
	
	<xsl:template match="rslpcd:seeAlso">
		<xsl:element name="relatedInfo">
			<xsl:value-of select="."/>
		</xsl:element>
	</xsl:template>

    <!-- ignore everything not matching a template -->
	<xsl:template match="node()|text()"/>
	
</xsl:stylesheet>
