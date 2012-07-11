<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:rif="http://ands.org.au/standards/rif-cs/registryObjects" version="1.0">
	
	<xsl:output method="xml" encoding="UTF-8" indent="yes" omit-xml-declaration="no"/>
	<xsl:strip-space elements="*"/>



	

	<xsl:template match="json">
		<xsl:element name="registryObjects" xmlns="http://ands.org.au/standards/rif-cs/registryObjects">
			<xsl:element name="registryObject" xmlns="http://ands.org.au/standards/rif-cs/registryObjects">
				<xsl:attribute name="group">
					<xsl:value-of select="mandatoryInformation/group"/>
				</xsl:attribute>
				<xsl:element name="key">
					<xsl:value-of select="mandatoryInformation/key"/>
				</xsl:element>
				<xsl:element name="originatingSource">
					<xsl:value-of select="mandatoryInformation/originatingSource"/>
				</xsl:element>
				<xsl:apply-templates select="objectClass"/>
			</xsl:element>
		</xsl:element>
	</xsl:template>

	<xsl:template match="objectClass">
		<xsl:variable name="oClass">
			<xsl:choose>
				<xsl:when test=". = 'Collection'">
					<xsl:text>collection</xsl:text>
				</xsl:when>
				<xsl:when test=". = 'Party'">
					<xsl:text>party</xsl:text>	
				</xsl:when>
				<xsl:when test=". = 'Activity'">
					<xsl:text>activity</xsl:text>
				</xsl:when>
				<xsl:when test=". = 'Service'">
					<xsl:text>service</xsl:text>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="."/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>	
		<xsl:element name="{$oClass}" xmlns="http://ands.org.au/standards/rif-cs/registryObjects">
			<xsl:if test="../mandatoryInformation/dateAccessioned != ''">
				<xsl:attribute name="dateAccessioned">
					<xsl:value-of select="../mandatoryInformation/dateAccessioned"/>
				</xsl:attribute>
			</xsl:if>
			<xsl:if test="../mandatoryInformation/dateModified != ''">
				<xsl:attribute name="dateModified">
					<xsl:value-of select="../mandatoryInformation/dateModified"/>
				</xsl:attribute>
			</xsl:if>
			<xsl:attribute name="type">
				<xsl:value-of select="../mandatoryInformation/type"/>
			</xsl:attribute>
			<xsl:apply-templates select="following-sibling::node()"/>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="roclass" mode="attribute">
		<xsl:attribute name="roclass">
			<xsl:value-of select="."/>
		</xsl:attribute>
	</xsl:template>
	

	<xsl:template match="type" mode="attribute">
		<xsl:attribute name="type">
			<xsl:value-of select="."/>
		</xsl:attribute>
	</xsl:template>
	
	<xsl:template match="lang" mode="attribute">
		<xsl:if test=". != ''">
			<xsl:attribute name="xml:lang">
				<xsl:value-of select="."/>
			</xsl:attribute>
		</xsl:if>
	</xsl:template>

	<xsl:template match="date/dateFormat" mode="attribute">
		<xsl:attribute name="dateFormat">
			<xsl:value-of select="."/>
		</xsl:attribute>
	</xsl:template>
	
	<xsl:template match="startDate/dateFormat | endDate/dateFormat" mode="attribute">
		<xsl:attribute name="dateFormat">
			<xsl:value-of select="."/>
		</xsl:attribute>
	</xsl:template>


	<xsl:template match="use" mode="attribute">
		<xsl:attribute name="use">
			<xsl:value-of select="."/>
		</xsl:attribute>
	</xsl:template>

	<xsl:template match="required" mode="attribute">
		<xsl:attribute name="required">
			<xsl:value-of select="."/>
		</xsl:attribute>
	</xsl:template>

	<xsl:template match="seq" mode="attribute">
		<xsl:attribute name="seq">
			<xsl:value-of select="."/>
		</xsl:attribute>
	</xsl:template>

	<xsl:template match="dateFrom" mode="attribute">
		<xsl:if test=". != ''">
			<xsl:attribute name="dateFrom">
				<xsl:value-of select="."/>
			</xsl:attribute>
		</xsl:if>
	</xsl:template>
	
	<xsl:template match="dateTo" mode="attribute">
		<xsl:if test=". != ''">
			<xsl:attribute name="dateTo">
				<xsl:value-of select="."/>
			</xsl:attribute>
		</xsl:if>
	</xsl:template>
	
	<xsl:template match="rightsUri" mode="attribute">
		<xsl:if test=". != ''">
			<xsl:attribute name="rightsUri">
				<xsl:value-of select="."/>
			</xsl:attribute>
		</xsl:if>
	</xsl:template>
	
	<xsl:template match="termIdentifier" mode="attribute">
		<xsl:if test=". != ''">
			<xsl:attribute name="termIdentifier">
				<xsl:value-of select="."/>
			</xsl:attribute>
		</xsl:if>
	</xsl:template>
	
	<xsl:template match="style" mode="attribute">
		<xsl:if test=". != ''">
			<xsl:attribute name="style">
				<xsl:value-of select="."/>
			</xsl:attribute>
		</xsl:if>
	</xsl:template>

	<xsl:template match="licence" mode="attribute">
		<xsl:if test=". != ''">
			<xsl:attribute name="type">
				<xsl:value-of select="."/>
			</xsl:attribute>
		</xsl:if>
	</xsl:template>

	<xsl:template match="node() | value[parent::electronic]">
		<xsl:param name="id"/>
		<xsl:param name="tab_id"/>
		<xsl:element name="{name()}" xmlns="http://ands.org.au/standards/rif-cs/registryObjects">
			<xsl:apply-templates select="type" mode="attribute"/>
			<xsl:apply-templates select="termIdentifier" mode="attribute"/>
			<xsl:apply-templates select="lang" mode="attribute"/>
			<xsl:apply-templates select="dateFormat" mode="attribute"/>
			<xsl:apply-templates select="use" mode="attribute"/>
			<xsl:apply-templates select="required" mode="attribute"/>
			<xsl:apply-templates select="dateFrom" mode="attribute"/>
			<xsl:apply-templates select="dateTo" mode="attribute"/>
			<xsl:apply-templates select="style" mode="attribute"/>
			<xsl:apply-templates select="roclass" mode="attribute"/>
			<xsl:apply-templates select="seq" mode="attribute"/>
			<xsl:apply-templates select="rightsUri" mode="attribute"/>
			<xsl:apply-templates select="rights/licence" mode="attribute"/>			
			<xsl:variable name="tabId">
				<xsl:choose>
					<xsl:when test="$tab_id = '' and id != '' ">
						<xsl:value-of select="name()"/>
					</xsl:when>
					<xsl:when test="$tab_id != '' ">
						<xsl:value-of select="$tab_id"/>
					</xsl:when>
					<xsl:otherwise></xsl:otherwise>
				</xsl:choose>
			</xsl:variable>
			<xsl:variable name="fieldId">
				<xsl:choose>
					<xsl:when test="$id != '' and id != '' ">
						<xsl:value-of select="concat($id,'_',name(), '_', id)"/>
					</xsl:when>
					<xsl:when test="id != '' ">
						<xsl:value-of select="concat(name(), '_', id)"/>
					</xsl:when>
					<xsl:otherwise></xsl:otherwise>
				</xsl:choose>
			</xsl:variable>
			<xsl:attribute name="field_id">
				<xsl:value-of select="$fieldId"/>
			</xsl:attribute>
			<xsl:if test="$tabId != '' ">
				<xsl:attribute name="tab_id">
					<xsl:value-of select="$tabId"/>
				</xsl:attribute>
			</xsl:if>
			<xsl:choose>
			<xsl:when test="name() = 'location'">
			<xsl:apply-templates select="node()[name() != 'spatial']">
				<xsl:with-param name="id" select="$fieldId"/>
				<xsl:with-param name="tab_id" select="$tabId"/>
			</xsl:apply-templates>
			<xsl:apply-templates select="spatial">
				<xsl:with-param name="id" select="$fieldId"/>
				<xsl:with-param name="tab_id" select="$tabId"/>
			</xsl:apply-templates>
			</xsl:when>
			<xsl:otherwise>
			<xsl:apply-templates>
				<xsl:with-param name="id" select="$fieldId"/>
				<xsl:with-param name="tab_id" select="$tabId"/>
			</xsl:apply-templates>
			</xsl:otherwise>
			</xsl:choose>
		</xsl:element>
	</xsl:template>
	
	<!-- Note: No checks for @lang/@seq attributes -->
	<xsl:template match="identifier[not(value/text()) and not(type/text()) and not(../title)]"/>
	<xsl:template match="name[not(type/text()) and not(dateFrom/text()) and not(dateTo/text()) and not(namePart/value/text()) and not(namePart/type/text())]"/>
	<xsl:template match="spatial[not(type/text()) and not(value/text())]"/>
	<xsl:template match="electronic[not(type/text()) and not(value/text()) and not(arg/value/text()) and not(arg/required/text()) and not(arg/type/text()) and not(arg/use/text())]"/>
	<xsl:template match="physical[not(type/text()) and not(addressPart/type/text()) and not(addressPart/value/text())]"/>
	<xsl:template match="arg[not(value/text()) and not(type/text()) and not(required/text()) and not(use/text())]"/>	
	<xsl:template match="address[not(electronic/type/text()) and not(electronic/value/value/text()) and not(electronic/arg/value/text()) and not(electronic/arg/required/text()) and not(electronic/arg/type/text()) and not(electronic/arg/use/text()) and not(physical/type/text()) and not(physical/addressPart/type/text()) and not(physical/addressPart/value/text())]"/>
	<xsl:template match="location[not(dateFrom/text()) and not(dateTo/text()) and not(type/text()) and not(spatial/type/text()) and not(spatial/value/text()) and not(address/electronic/type/text()) and not(address/electronic/value/value/text()) and not(address/electronic/arg/value/text()) and not(address/electronic/arg/required/text()) and not(address/electronic/arg/type/text()) and not(address/electronic/arg/use/text()) and not(address/physical/type/text()) and not(address/physical/addressPart/type/text()) and not(address/physical/addressPart/value/text())]"/>
	<xsl:template match="relatedObject[not(key/value/text()) and not(relation/type/text()) and not(relation/url/value/text()) and not(relation/description/value/text())]"/>
	<xsl:template match="subject[not(value/text()) and not(type/text()) and not(termIdentifier/text())]"/>
	<xsl:template match="description[not(value/text()) and not(type/text())]"/>
	<xsl:template match="coverage[not(temporal/date/value/text()) and not(temporal/date/dateFormat/text()) and not(temporal/date/type/text()) and not(temporal/text/value/text()) and not(spatial/value/text()) and not(spatial/type/text())]"/>
	<xsl:template match="temporal[not(date/value/text()) and not(date/dateFormat/text()) and not(date/type/text()) and not(text/value/text())]"/>	
	<xsl:template match="date[not(dateFormat/text()) and not(type/text()) and not(value/text())]"/>	
	<xsl:template match="text[not(value/text())]"/>
	<xsl:template match="citationInfo[not(fullCitation/style/text()) and not(fullCitation/value/text()) and not(citationMetadata/identifier/value/text()) and not(citationMetadata/identifier/type/text()) and not(citationMetadata/contributor/namePart/value/text()) and not(citationMetadata/contributor/namePart/type/text()) and not(citationMetadata/title/value/text()) and not(citationMetadata/edition/value/text()) and not(citationMetadata/placePublished/value/text()) and not(citationMetadata/publisher/value/text()) and not(citationMetadata/date/value/text()) and not(citationMetadata/date/type/text()) and not(citationMetadata/url/value/text()) and not(citationMetadata/context/value/text())]"/>
	<xsl:template match="fullCitation[not(style/text()) and not(value/text())]"/>
	<xsl:template match="citationMetadata[not(identifier/value/text()) and not(identifier/type/text()) and not(contributor/namePart/value/text()) and not(contributor/namePart/type/text()) and not(title/value/text()) and not(edition/value/text()) and not(publisher/value/text()) and not(placePublished/value/text()) and not(date/value/text()) and not(date/type/text()) and not(url/value/text()) and not(context/value/text())]"/>
	<xsl:template match="relatedInfo[not(type/text()) and not(identifier/value/text()) and not(identifier/type/text()) and not(title/value/text()) and not(notes/value/text())]"/>
	<xsl:template match="existenceDates[not(startDate/value/text()) and not(startDate/dateFormat/text()) and not(endDate/value/text()) and not(endDate/dateFormat/text())]"/>
	<xsl:template match="startDate[not(value/text()) and not(dateFormat/text())]"/>
	<xsl:template match="endDate[not(value/text()) and not(dateFormat/text())]"/>
	<xsl:template match="accessRights[not(value/text()) and not(rightsUri/text())]"/>
	<xsl:template match="licence[not(value/text()) and not(rightsUri/text()) and not(type/text())]"/>
	<xsl:template match="rightsStatement[not(value/text()) and not(rightsUri/text())]"/>
	<xsl:template match="rights[not(rightsStatement/value/text()) and not(rightsStatement/rightsUri/text()) and not(licence/value/text()) and not(licence/rightsUri/text()) and not(accessRights/value/text()) and not(accessRights/rightsUri/text()) and not(licence/type/text())]"/>
	<xsl:template match="accessPolicy[not(value/text())]"/>

	<xsl:template
		match="type | name[parent::key] | seq | roclass | mandatoryInformation | id | form | dateFormat | lang | use | required | dateFrom | dateTo | style | dateFormat | rightsUri | termIdentifier"/>

	<xsl:template match="text() | value">
		<xsl:value-of select="."/>
	</xsl:template>
	
	<xsl:template match="value[ancestor::electronic and parent::value]">
		<xsl:value-of select="translate(. ,' ', '+')"/>
	</xsl:template>
	

	

</xsl:stylesheet>
