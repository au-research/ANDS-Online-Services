<?xml version="1.0"?>
<xsl:stylesheet xmlns:ro="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:extRif="http://ands.org.au/standards/rif-cs/extendedRegistryObjects" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" exclude-result-prefixes="ro extRif">
	<xsl:output method="xml" encoding="UTF-8" indent="yes" omit-xml-declaration="yes"/>
	<xsl:key name="relatedObjectByKey" use="ro:key" match="ro:registryObject"/>
	<xsl:param name="item-url"/>
	<xsl:strip-space elements="*"/>

	<xsl:template match="/">
		<xsl:apply-templates/>
	</xsl:template>

	<xsl:template match="ro:registryObjects">
		<p class="error-info">(i) element or content is required</p>
		<p class="recommended-info">(ii) element or content is strongly recommended if at all possible</p>
		<table id="qtable">
			<tbody>
				<xsl:apply-templates select="ro:registryObject"/>
			</tbody>
		</table>
	</xsl:template>

	<xsl:template match="ro:registryObject">
		<tr>
			<td>
				<xsl:apply-templates select="ro:collection | ro:activity | ro:party | ro:service"/>
			</td>
		</tr>
	</xsl:template>

	<xsl:template match="ro:collection">
		<span class="info">Collection: key = <a href="{$item-url}{preceding-sibling::ro:key}">
				<xsl:value-of select="preceding-sibling::ro:key"/>
			</a></span>
		<br/>
		<xsl:if test="not(ro:name[@type='primary'])">
			<span class="error">There must be a name with type "primary".</span>
			<br/>
		</xsl:if>
		<xsl:if
			test="(string-length(ro:description[@type='brief']) &lt; 10) and (string-length(ro:description[@type='full']) &lt; 10)">
			<span class="error">No description (or a short one) found. A good description is critical for
				letting people decide whether a collection is worth investigating further. Any information
				helps.</span>
			<br/>
		</xsl:if>
		<xsl:if test="not(ro:location/ro:address)">
			<span class="error">No location information found. This is important for explaining how
				someone can get access to the collection, whether electronically or through another person.</span>
			<br/>
		</xsl:if>
		<!-- this was "2" but I can't remember why I specified that in the first place, and it's
			not in the metadata content requirements Steve Bennett. -->
		<xsl:choose>
			<xsl:when test="count(ro:relatedObject) &lt; 1">
				<span class="error">This collection is not linked to any other objects.</span>
				<br/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:variable name="relatedObjectClasses">
					<xsl:for-each select="ro:relatedObject">
						<xsl:for-each select="key('relatedObjectByKey',current()/ro:key)">
							<xsl:if test="ro:party">
								<xsl:text>party</xsl:text>
							</xsl:if>
							<xsl:if test="ro:activity">
								<xsl:text>activity</xsl:text>
							</xsl:if>
						</xsl:for-each>
					</xsl:for-each>
				</xsl:variable>
				<xsl:if test="not(contains($relatedObjectClasses, 'party'))">
					<span class="error">This collection is not linked to any party records.</span>
					<br/>
				</xsl:if>
				<xsl:if test="not(contains($relatedObjectClasses, 'activity'))">
					<span class="recommended">This collection is not linked to any activity records.</span>
					<br/>
				</xsl:if>
			</xsl:otherwise>
		</xsl:choose>
		<xsl:if test="not(ro:description[@type='rights'] or ro:description[@type='accessRights'] or ro:rights)">
			<span class="error">No rights description (description with type "rights") found. Please provide information about rights held in and over the collection such as copyright, licenses and other intellectual property rights.</span>
			<br/>
		</xsl:if>
		<xsl:if test="not(ro:identifier)">
			<span class="recommended">Adding any local or external identifiers is recommended to improve
				findability.</span>
			<br/>
		</xsl:if>
		<xsl:if test="not(ro:subject)">
			<span class="recommended">ANZSRC, FOR, RFCD or other subject codes are recommended to improve
				browsing of subject areas.</span>
			<br/>
		</xsl:if>
		<xsl:if test="not(ro:coverage/ro:spatial)">
			<span class="recommended">There is no spatial coverage defined. Adding geographic information
				allows collections to be located on a map.</span>
			<br/>
		</xsl:if>
		<xsl:if test="not(ro:coverage/ro:temporal/ro:date) and not(ro:coverage/ro:temporal/ro:text)">
			<span class="recommended">Temporal coverage is recommended.</span>
			<br/>
		</xsl:if>
	</xsl:template>

	<xsl:template match="ro:party">
		<span class="info">Party: key = <a href="{$item-url}{preceding-sibling::ro:key}">
				<xsl:value-of select="preceding-sibling::ro:key"/>
			</a></span>
		<br/>
		<xsl:if test="not(ro:name[@type='primary'])">
			<span class="error">Parties must have one name with type "primary".</span>
			<br/>
		</xsl:if>
		<xsl:if
			test="(string-length(ro:description[@type='brief']) &lt; 10) and (string-length(ro:description[@type='full']) &lt; 10)">
			<span class="recommended">No description (or a short one) found. This could be a researcher
				profile, a biography or a description of the organisation.</span>
			<br/>
		</xsl:if>
		<xsl:if test="not(ro:location/ro:address)">
			<span class="recommended">No address is provided. Consider including contact details, within
				the bounds of privacy considerations.</span>
			<br/>
		</xsl:if>
		<xsl:choose>
			<xsl:when test="count(ro:relatedObject) &lt; 1">
				<span class="recommended">This party is not related to any object. Party records need to be
					linked to collections, other parties, or to activities, to provide context to those
					objects.</span>
				<br/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:variable name="relatedObjectClasses">
					<xsl:for-each select="ro:relatedObject">
						<xsl:for-each select="key('relatedObjectByKey',current()/ro:key)">
							<xsl:if test="ro:collection">
								<xsl:text>collection</xsl:text>
							</xsl:if>
							<xsl:if test="ro:activity">
								<xsl:text>activity</xsl:text>
							</xsl:if>
						</xsl:for-each>
					</xsl:for-each>
				</xsl:variable>
				<xsl:if test="not(contains($relatedObjectClasses, 'collection'))">
					<span class="recommended">This party is not related to any collection objects. This is
						usually an error.</span>
					<br/>
				</xsl:if>
				<xsl:if test="not(contains($relatedObjectClasses, 'activity'))">
					<span class="recommended">This party is not related to any activity objects.</span>
					<br/>
				</xsl:if>
			</xsl:otherwise>
		</xsl:choose>
		<xsl:if test="not(ro:identifier)">
			<span class="recommended">This party has no identifiers. Any publicly visible and shareable
				identifiers should be included.</span>
			<br/>
		</xsl:if>
		<xsl:if test="not(ro:subject)">
			<span class="recommended">This party has no subject classifications. These could describe the
				expertise of the researcher or organisation.</span>
			<br/>
		</xsl:if>
	</xsl:template>

	<xsl:template match="ro:activity">
		<span class="info">Activity : key = <a href="{$item-url}{preceding-sibling::ro:key}">
				<xsl:value-of select="preceding-sibling::ro:key"/>
			</a></span>
		<br/>
		<xsl:if test="not(ro:name[@type='primary'])">
			<span class="error">There must be one name with type "primary".</span>
			<br/>
		</xsl:if>
		<xsl:if
			test="(string-length(ro:description[@type='brief']) &lt; 10) and (string-length(ro:description[@type='full']) &lt; 10)">
			<span class="error">No description (or a short one) found. This could be a description of the
				grant or a project profile.</span>
			<br/>
		</xsl:if>
		<xsl:if test="not(ro:location/ro:address)">
			<span class="recommended">No location or contact details for the activity were provided.</span>
			<br/>
		</xsl:if>
		<xsl:choose>
			<xsl:when test="count(ro:relatedObject) &lt; 1">
				<span class="error">This activity is not related to any other objects.</span>
				<br/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:variable name="relatedObjectClasses">
					<xsl:for-each select="ro:relatedObject">
						<xsl:for-each select="key('relatedObjectByKey',current()/ro:key)">
							<xsl:if test="ro:party">
								<xsl:text>party</xsl:text>
							</xsl:if>
						</xsl:for-each>
					</xsl:for-each>
				</xsl:variable>
				<xsl:if test="not(contains($relatedObjectClasses, 'party'))">
					<span class="error">This activity is not related to any party objects.</span>
					<br/>
				</xsl:if>
			</xsl:otherwise>
		</xsl:choose>
		<xsl:if test="not(ro:subject)">
			<span class="recommended">No subject classification (eg, ANZSRC, FOR, RFCD) found.</span>
			<br/>
		</xsl:if>
	</xsl:template>
	
	<xsl:template match="ro:service">
		<span class="info">Service : key = <a href="{$item-url}{preceding-sibling::ro:key}">
				<xsl:value-of select="preceding-sibling::ro:key"/>
			</a></span>
		<br/>
		<xsl:if test="not(ro:name[@type='primary'])">
			<span class="error">There must be a name with type "primary".</span>
			<br/>
		</xsl:if>
		<xsl:if
			test="(string-length(ro:description[@type='brief']) &lt; 10) and (string-length(ro:description[@type='full']) &lt; 10)">
			<span class="recommended">No description (or a short one) found. This describes what the
				service does and why it is useful.</span>
			<br/>
		</xsl:if>
		<xsl:if test="not(ro:location/ro:address/ro:electronic)">
			<span class="recommended">No electronic address found. This defines where the service is
				running, and is usually a URL.</span>
			<br/>
		</xsl:if>
		<xsl:choose>
			<xsl:when test="count(ro:relatedObject) &lt; 1">
				<span class="recommended">This service is not linked to any other objects.</span>
				<br/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:variable name="relatedObjectClasses">
					<xsl:for-each select="ro:relatedObject">
						<xsl:for-each select="key('relatedObjectByKey',current()/ro:key)">
							<xsl:if test="ro:party">
								<xsl:text>party</xsl:text>
							</xsl:if>
						</xsl:for-each>
					</xsl:for-each>
				</xsl:variable>
				<xsl:if test="not(contains($relatedObjectClasses, 'party'))">
					<span class="recommended">This service should be linked to one or more party objects.</span>
					<br/>
				</xsl:if>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

</xsl:stylesheet>
