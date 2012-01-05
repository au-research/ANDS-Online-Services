<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:ro="http://ands.org.au/standards/rif-cs/registryObjects" exclude-result-prefixes="ro">
	<xsl:output method="xml" encoding="UTF-8" indent="yes" omit-xml-declaration="yes"/>
	<xsl:param name="dataSource"/>
	<xsl:param name="dateCreated"/>
	<xsl:template match="ro:registryObjects">
			<xsl:apply-templates select="ro:registryObject"/>
	</xsl:template>

	<xsl:template match="ro:registryObject">
		<table class="recordTable" summary="Preview of Draft Registry Object" style="width:100%;">
			<tbody class="recordFields">
				<tr>
					<td>Type: </td>
					<td style="">
						<xsl:apply-templates select="ro:collection/@type | ro:activity/@type | ro:party/@type  | ro:service/@type"/>
					</td>
				</tr>

				<tr>
					<td>Key: </td>
					<td>
						<xsl:apply-templates select="ro:key"/>
					</td>
				</tr>

				<tr>
					<td>Source: </td>
					<td>
						<xsl:value-of select="$dataSource"/>
					</td>
				</tr>

				<tr>
					<td>Originating Source: </td>
					<td>
						<xsl:apply-templates select="ro:originatingSource"/>
					</td>
				</tr>
			
				<tr>
					<td>Group: </td>
					<td>
						<xsl:apply-templates select="@group"/>
					</td>
				</tr>	
			
				<xsl:apply-templates select="ro:collection | ro:activity | ro:party | ro:service"/>
				
				
				<tr>
					<td>Created When:</td>
					<td>
						<xsl:value-of select="$dateCreated"/>
					</td>
				</tr>
				
		
			</tbody>
		</table>
	</xsl:template>

	<xsl:template match="@group">
		<xsl:value-of select="."/>
	</xsl:template>
	
	<xsl:template match="ro:collection/@type | ro:activity/@type | ro:party/@type  | ro:service/@type">
		<xsl:value-of select="."/>
	</xsl:template>
	
	<xsl:template match="ro:key">
		<xsl:value-of select="."/>
	</xsl:template>
	
	<xsl:template match="ro:relatedObject/ro:key">
		<tr>
			<td class="attribute">
				<xsl:value-of select="local-name()"/><xsl:text>: </xsl:text>
			</td>
			<td class="value">
				<xsl:value-of select="."/>
			</td>
		</tr>
	</xsl:template>
	
	<xsl:template match="ro:originatingSource">
		<xsl:value-of select="."/>
	</xsl:template>


	<xsl:template match="ro:collection | ro:activity | ro:party | ro:service">

		<xsl:if test="ro:name">
			<tr>
				<td>Names:</td>
				<td>
					<table class="subtable">
					<xsl:apply-templates select="ro:name"/>
					</table>
				</td>
			</tr>
		</xsl:if>

		<xsl:if test="ro:identifier">
			<tr>
				<td>Identifiers:</td>
				<td>
					<table class="subtable">
						<xsl:apply-templates select="ro:identifier"/>
					</table>
				</td>
			</tr>
		</xsl:if>
		
		<xsl:if test="ro:location">
			<tr>
				<td>Location:</td>
				<td>
					<table class="subtable">
						<xsl:apply-templates select="ro:location"/>
					</table>
				</td>
			</tr>
		</xsl:if>
		
		<xsl:if test="ro:coverage">
			<tr>
				<td>Coverage:</td>
				<td>
					<table class="subtable">
						<xsl:apply-templates select="ro:coverage"/>
					</table>
				</td>
			</tr>
		</xsl:if>
		
		
		<xsl:if test="ro:relatedObject">
			<tr>
				<td>Related Objects:</td>
				<td>
					<table class="subtable">
						<xsl:apply-templates select="ro:relatedObject"/>
					</table>
				</td>
			</tr>
		</xsl:if>
		
		
		<xsl:if test="ro:subject">
			<tr>
				<td>Subjects:</td>
				<td>
					<table class="subtable">
						<xsl:apply-templates select="ro:subject"/>
					</table>
				</td>
			</tr>
		</xsl:if>
			
		<xsl:choose>
			<xsl:when test="ro:description">
				<tr>
					<td>Description:</td>
					<td><!--  div name="errors_description" class="fieldError"/-->
						<table class="subtable">
							<xsl:apply-templates select="ro:description"/>
						</table>
					</td>
				</tr>
			</xsl:when>
	 	</xsl:choose>
	 	
	 	<xsl:choose>
			<xsl:when test="ro:existenceDates">
				<tr>
					<td>Existence Dates:</td>
					<td>
						<table class="subtable">
							<xsl:apply-templates select="ro:existenceDates"/>
						</table>
					</td>
				</tr>
			</xsl:when>
	 	</xsl:choose>
	 	
	 	<xsl:if test="ro:citationInfo">
			<tr>
				<td>Citation:</td>
				<td>
					<table class="subtable">
						<xsl:apply-templates select="ro:citationInfo"/>
					</table>
				</td>
			</tr>
		</xsl:if>
	 	
	 	<xsl:if test="ro:relatedInfo">
			<tr>
				<td>Related Info:</td>
				<td>
					<table class="subtable">
						<xsl:apply-templates select="ro:relatedInfo"/>
					</table>
				</td>
			</tr>
		</xsl:if>
		
		 <xsl:if test="ro:rights">
			<tr>
				<td>Rights:</td>
				<td>
					<table class="subtable">
						<xsl:apply-templates select="ro:rights"/>
					</table>
				</td>
			</tr>
		</xsl:if>
		
	 	<xsl:if test="ro:accessPolicy">
			<tr>
				<td>Access Policy:</td>
				<td>
					<table class="subtable">
						<xsl:apply-templates select="ro:accessPolicy"/>
					</table>
				</td>
			</tr>
		</xsl:if>
	 	
	</xsl:template>

	<xsl:template match="ro:relation/ro:description">
		<tr>	
			<td class="attribute">
				<xsl:value-of select="local-name()"/><xsl:text>: </xsl:text>
			</td>
			<td>
				<table class="subtable1">
					<xsl:apply-templates select="@* | node()"/>
				</table>
			</td>
		</tr>
	</xsl:template>


	<xsl:template match="ro:name/ro:namePart">
		<tr>	
			<td class="attribute">
			<xsl:choose>
			<xsl:when test="following-sibling::ro:namePart">
			<xsl:text>Name Parts:</xsl:text>
			</xsl:when>
			<xsl:when test="preceding-sibling::ro:namePart"/>
			<xsl:otherwise>
			<xsl:text>Name Part:</xsl:text>
			</xsl:otherwise>
			</xsl:choose>
			</td>
			<td>
				<table class="subtable1">
					<xsl:apply-templates select="@* | node()"/>
				</table>
			</td>
		</tr>
	</xsl:template>

	<xsl:template match="text()">
		<xsl:if test="not(following-sibling::node()) and not(preceding-sibling::node())">
		<tr>
			<td class="attribute">Value: </td>
			<td class="value">
				<xsl:value-of select="."/>
			</td>
		</tr>
		</xsl:if>
	</xsl:template>


	<xsl:template match="ro:value/text()">
		<tr>
			<td class="value">
				<xsl:value-of select="."/>
			</td>
		</tr>
	</xsl:template>


	<xsl:template match="node()">
		<tr>
			<xsl:if test="(not(contains('-name-relatedObject-description-subject-rights-', concat('-',local-name(),'-'))))">	
				<td class="attribute">
					<xsl:value-of select="local-name()"/><xsl:text>: </xsl:text>
				</td>
			</xsl:if>
			<td>
				<table class="subtable1">
					<xsl:apply-templates select="@* | node()"/>
				</table>
			</td>
		</tr>
	</xsl:template>

	<xsl:template match="@*">
		<tr>
			<td class="attribute">
				<xsl:value-of select="name()"/><xsl:text>: </xsl:text></td>
			<td class="valueAttribute">
				<xsl:value-of select="."/>
			</td>
		</tr>
	</xsl:template>
	
		
	<xsl:template match="@field_id | @tab_id | @lang"/>


</xsl:stylesheet>
