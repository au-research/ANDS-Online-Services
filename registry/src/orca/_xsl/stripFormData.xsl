<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
xmlns:extRif="http://ands.org.au/standards/rif-cs/extendedRegistryObjects"
	xmlns:rif="http://ands.org.au/standards/rif-cs/registryObjects"
version="1.0">
	<xsl:output indent="yes"/>
	<xsl:strip-space elements="*"/>
	<!-- copy all other nodes and attributes -->
	
	<xsl:template match="/">
		<xsl:apply-templates/>
	</xsl:template>
	
	<xsl:template match="@* | node()">
		<xsl:copy>
			<xsl:apply-templates select="@* | node()"/>
		</xsl:copy>
	</xsl:template>
	
<xsl:template match="@tab_id | @field_id | @roclass | @lang[. = '']"/>

<xsl:template match="extRif:* | @extRif:*"/>

		
</xsl:stylesheet>
