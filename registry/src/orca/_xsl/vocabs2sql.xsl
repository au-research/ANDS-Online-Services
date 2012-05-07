<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:voc="http://ands.org.au/schema/vocabulary" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:ro="http://ands.org.au/standards/rif-cs/registryObjects" exclude-result-prefixes="ro">
	<xsl:output method="text" encoding="UTF-8" indent="yes" omit-xml-declaration="yes"/>

	<xsl:template match="voc:vocabularies">
		<xsl:apply-templates select="voc:vocabulary"/>
	</xsl:template>
	
	
	<xsl:variable name="snglQt" select='"&#39;"'/>
	<xsl:variable name="dblQt" select="'&#34;'"/>
	<xsl:template match="voc:vocabulary">
		<xsl:variable name="vocabulary_identifier" select="voc:identifier"/>
		<xsl:variable name="vocabulary_name" select="voc:name"/>
<xsl:text>INSERT INTO tbl_vocabularies (identifier, identifier_type, "version", "name", name_type, description , source, authority_identifier) VALUES ('</xsl:text>
		<xsl:value-of select="voc:identifier"/><xsl:text>','</xsl:text>
		<xsl:value-of select="voc:identifier/@type"/><xsl:text>','</xsl:text>
		<xsl:value-of select="voc:version"/><xsl:text>','</xsl:text>
		<xsl:value-of select="voc:name"/><xsl:text>','</xsl:text>
		<xsl:value-of select="voc:name/@type"/><xsl:text>','</xsl:text>
		<xsl:value-of select="translate(voc:description, $dblQt, $snglQt)"/><xsl:text>','</xsl:text>
		<xsl:value-of select="voc:source"/><xsl:text>',NULL);
</xsl:text>
		<xsl:apply-templates select="voc:term">
			<xsl:with-param name="vocabulary_identifier" select="$vocabulary_identifier"/>
			<xsl:with-param name="vocabulary_name" select="$vocabulary_name"/>
			<xsl:with-param name="parent_term_identifier" select="'NULL'"/>
		</xsl:apply-templates>
	</xsl:template>
	
	
	<xsl:template match="voc:term">
		<xsl:param name="vocabulary_identifier"/>
		<xsl:param name="vocabulary_name"/>
		<xsl:param name="parent_term_identifier"/>
		<xsl:variable name="term_identifier" select="voc:identifier"/>	
		<xsl:variable name="term_name" select="translate(voc:name, $snglQt, '!')"/>
<xsl:text>INSERT INTO tbl_terms (identifier, identifier_type, name, qualifier, description, description_type, vocabulary_identifier, parent_term_identifier, "type", relationType, vocabPath, lang) VALUES ('</xsl:text>
		<xsl:value-of select="voc:identifier"/><xsl:text>','</xsl:text>
		<xsl:value-of select="voc:identifier/@type"/><xsl:text>','</xsl:text>
		<xsl:value-of select="$term_name"/><xsl:text>','</xsl:text>
		<xsl:value-of select="voc:qualifier"/><xsl:text>','</xsl:text>
		<xsl:value-of select="translate(voc:description, $snglQt, $dblQt)"/><xsl:text>','</xsl:text>
		<xsl:value-of select="voc:description/@type"/><xsl:text>','</xsl:text>
		<xsl:value-of select="$vocabulary_identifier"/><xsl:text>',</xsl:text>
		<xsl:if test="$parent_term_identifier != 'NULL'">
			<xsl:text>'</xsl:text>
		</xsl:if>		
		<xsl:value-of select="$parent_term_identifier"/>
		<xsl:if test="$parent_term_identifier != 'NULL'">
			<xsl:text>'</xsl:text>
		</xsl:if>
		<xsl:text>,'</xsl:text>
		<xsl:value-of select="@type"/><xsl:text>','','</xsl:text>
		<xsl:value-of select="$vocabulary_name"/><xsl:text>','</xsl:text>
		<xsl:value-of select="@lang"/><xsl:text>');
</xsl:text>
		<xsl:apply-templates select="voc:term">
			<xsl:with-param name="vocabulary_identifier" select="$vocabulary_identifier"/>
			<xsl:with-param name="vocabulary_name" select="concat($vocabulary_name, '&gt;&gt;' , $term_name)"/>
			<xsl:with-param name="parent_term_identifier" select="$term_identifier"/>
		</xsl:apply-templates>
	</xsl:template>
		
		
	
	</xsl:stylesheet> 
