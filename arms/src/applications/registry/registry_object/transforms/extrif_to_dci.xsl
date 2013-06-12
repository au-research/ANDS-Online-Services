<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet xmlns:ro="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:extRif="http://ands.org.au/standards/rif-cs/extendedRegistryObjects" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" exclude-result-prefixes="ro extRif">

    <xsl:output method="xml" indent="yes" omit-xml-declaration="yes" encoding="UTF-8"/>
    <xsl:strip-space elements="*"/>
    <xsl:param name="dateProvided"/>
    <xsl:template match="/">
        <xsl:apply-templates/>
    </xsl:template>

    <xsl:template match="ro:registryObject"/>

    <xsl:template match="ro:registryObject[ro:collection]">
        <DataRecord ProviderID="999999999">
            <Header>
                <DateProvided>
                    <xsl:value-of select="$dateProvided"/>
                </DateProvided>
                <RepositoryName>
                    <xsl:value-of select="ro:originatingSource"/>
                </RepositoryName>
                <Owner>
                    <xsl:value-of select="@group"/>
                </Owner>
                <RecordIdentifier>
                    <xsl:value-of select="ro:key"/>
                </RecordIdentifier>
            </Header>
            <BibliographicData>
                <xsl:if test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:contributor">
                    <AuthorList>
                        <xsl:apply-templates select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:contributor"/>
                    </AuthorList>
                </xsl:if>
                <TitleList>
                    <ItemTitle TitleType="English title">
                        <xsl:apply-templates select="extRif:extendedMetadata/extRif:displayTitle"/>
                    </ItemTitle>
                </TitleList>
                <Source>
                    <xsl:variable name="sourceUrl">
                        <xsl:call-template name="getSourceURL"/>
                    </xsl:variable>
                    <xsl:if test="$sourceUrl != ''">
                        <SourceURL>
                            <xsl:value-of select="$sourceUrl"/>
                        </SourceURL>
                    </xsl:if>
                    <xsl:if test="extRif:extendedMetadata/extRif:dataSourceTitle">
                        <SourceRepository AbbreviatedRepository="{@group}">
                            <xsl:value-of select="extRif:extendedMetadata/extRif:dataSourceTitle"/>
                        </SourceRepository>
                    </xsl:if>
                    <xsl:variable name="createdDate">
                        <xsl:call-template name="getCreatedDate"/>
                    </xsl:variable>
                    <xsl:if test="$createdDate != ''">
                        <CreatedDate>
                            <xsl:value-of select="$createdDate"/>
                        </CreatedDate>
                    </xsl:if>
                    <xsl:if test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:version">
                        <Version>
                            <xsl:value-of select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:version"/>
                        </Version>
                    </xsl:if>
                </Source>
                <LanguageList>
                    <Language>English</Language>
                </LanguageList>
            </BibliographicData>
            <xsl:if test="extRif:extendedMetadata/extRif:the_description">
                <Abstract>
                    <xsl:apply-templates select="extRif:extendedMetadata/extRif:the_description"/>
                </Abstract>
            </xsl:if>
            <Rights_Licensing>
                <xsl:choose>
                    <xsl:when test="extRif:extendedMetadata/extRif:right">
                        <RightsStatement>
                            <xsl:apply-templates select="extRif:extendedMetadata/extRif:right[@type='rightsStatement'] | extRif:extendedMetadata/extRif:right[@type='accessRights'] | extRif:extendedMetadata/extRif:right[@type='rights']"/>
                        </RightsStatement>
                        <LicenseStatement>
                            <xsl:apply-templates select="extRif:extendedMetadata/extRif:right[@type='licence']"/>
                        </LicenseStatement>
                    </xsl:when>
                    <xsl:otherwise>
                        <RightsStatement>NO RIGHTS STATEMENT WAS PROVIDED</RightsStatement>
                        <LicenseStatement>NO LICENSE STATEMENT WAS PROVIDED</LicenseStatement>
                    </xsl:otherwise>
                </xsl:choose>
            </Rights_Licensing>
            <!-- <ParentDataRef/> <relatedObject>
                <key>EMBL-NC-1</key>
                <relation type="isPartOf"/>
                </relatedObject>-->
            <!-- PROBABLY THEY WANT THEIR INTERNAL IDs -->
            <!--xsl:if test="ro:collection/ro:relatedObject/ro:relation/@type = 'isPartOf'">          
                <ParentDataRef><xsl:apply-templates select="ro:collection/ro:relatedObject[ro:relation/@type = 'isPartOf']/ro:key"/></ParentDataRef>
            </xsl:if-->

            <DescriptorsData>
                <xsl:if test="extRif:extendedMetadata/extRif:subjects/extRif:subject/extRif:subject_resolved">
                    <KeywordsList>
                        <xsl:apply-templates select="extRif:extendedMetadata/extRif:subjects/extRif:subject/extRif:subject_resolved"/>
                    </KeywordsList>
                </xsl:if>
                <!--
                <xs:element name="DataType" minOccurs="0">
                    <xs:annotation>
                        <xs:documentation>Type of data represented. E.g. survey data, protein sequence data etc.</xs:documentation>
                    </xs:annotation>
                </xs:element>
                -->
                <xsl:if test="ro:collection/ro:coverage/ro:spatial | ro:collection/ro:location/ro:spatial">
                    <GeographicalData>
                        <xsl:apply-templates select="ro:collection/ro:coverage/ro:spatial |  ro:collection/ro:location/ro:spatial"/>
                    </GeographicalData>
                </xsl:if>
                <!--
                <xs:element name="OrganismList" minOccurs="0">
                    <xs:annotation>
                        <xs:documentation>Orgainsm names used in the data resource. Latin names preferred</xs:documentation>
                    </xs:annotation>
                    <xs:complexType>
                        <xs:sequence>
                            <xs:element name="OrganismName" type="xs:string" maxOccurs="unbounded"/>
                        </xs:sequence>
                    </xs:complexType>
                </xs:element>
                <xs:element name="GeneNameList" minOccurs="0">
                    <xs:annotation>
                        <xs:documentation>Gene names used in the resource. One gene name per element</xs:documentation>
                    </xs:annotation>
                    <xs:complexType>
                        <xs:sequence>
                            <xs:element name="GeneName" type="xs:string" maxOccurs="unbounded"/>
                        </xs:sequence>
                    </xs:complexType>
                </xs:element>
                -->
                <xsl:if test="ro:collection/ro:coverage/ro:temporal/ro:date | ro:collection/ro:dates/ro:date">
                    <TimeperiodList>
                        <xsl:apply-templates select="ro:collection/ro:coverage/ro:temporal/ro:date | ro:collection/ro:dates/ro:date"/>
                    </TimeperiodList>
                </xsl:if>
                <!--
                <xs:element name="MethodologyList" minOccurs="0">
                    <xs:complexType>
                        <xs:sequence>
                            <xs:element name="Methodology" type="xs:string" maxOccurs="unbounded"/>
                        </xs:sequence>
                    </xs:complexType>
                </xs:element>
                <xs:element name="DemographicList" minOccurs="0">
                    <xs:complexType>
                        <xs:sequence>
                            <xs:element name="Demographic" type="xs:string" maxOccurs="unbounded"/>
                        </xs:sequence>
                    </xs:complexType>
                </xs:element>
                -->
                <xsl:if test="extRif:extendedMetadata/extRif:related_object/extRif:related_object_display_title">
                    <NamedPersonList>
                        <xsl:apply-templates select="extRif:extendedMetadata/extRif:related_object/extRif:related_object_display_title"/>
                    </NamedPersonList>
                </xsl:if>

            </DescriptorsData>
            <!--
            <FundingInfo/>
            <MicrocitationData/>
            <CitationList/>    
            -->
        </DataRecord>
    </xsl:template>


    <xsl:template match="extRif:displayTitle">
        <xsl:value-of select="."/>
    </xsl:template>


    <xsl:template match="extRif:right[@type='rightsStatement' and text()] | extRif:right[@type='accessRights' and text()] | extRif:right[@type='rights' and text()]">
        <xsl:value-of select="."/>
        <xsl:if test="following-sibling::extRif:right[@type='rightsStatement' and text()] | following-sibling::extRif:right[@type='accessRights' and text()] | following-sibling::extRif:right[@type='rights' and text()]">
            <xsl:text>           
        </xsl:text>
        </xsl:if>
    </xsl:template>

    <xsl:template match="extRif:right[@type='licence']">
        <xsl:value-of select="."/>
        <xsl:if test="following-sibling::extRif:right[@type='licence']">
            <xsl:text>           
        </xsl:text>
        </xsl:if>
    </xsl:template>

    <xsl:template name="getSourceURL">
        <xsl:choose>
            <xsl:when test="ro:collection/ro:location/ro:address/ro:electronic[@type='url']">
                <xsl:value-of select="ro:collection/ro:location/ro:address/ro:electronic[@type='url']"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:identifier[@type='doi']">
                <xsl:value-of select="ro:collection/ro:identifier[@type='doi']"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:identifier[@type='handle']">
                <xsl:value-of select="ro:collection/ro:identifier[@type='handle']"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:identifier[@type='uri']">
                <xsl:value-of select="ro:collection/ro:identifier[@type='uri']"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:identifier[@type='purl']">
                <xsl:value-of select="ro:collection/ro:identifier[@type='purl']"/>
            </xsl:when>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="extRif:subject_resolved">
        <Keyword>
            <xsl:value-of select="."/>
        </Keyword>
    </xsl:template>

    <xsl:template match="ro:spatial">
        <GeographicalLocation>
            <xsl:value-of select="."/>
        </GeographicalLocation>
    </xsl:template>

    <xsl:template match="ro:date">
        <xsl:choose>
            <xsl:when test="@type='dateFrom'">
                <TimePeriod TimeSpan="Start">
                    <xsl:value-of select="."/>
                </TimePeriod>
            </xsl:when>
            <xsl:when test="@type='dateTo'">
                <TimePeriod TimeSpan="End">
                    <xsl:value-of select="."/>
                </TimePeriod>
            </xsl:when>
            <xsl:otherwise>
                <TimePeriod>
                    <xsl:value-of select="."/>
                </TimePeriod>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="extRif:related_object_display_title">
        <NamedPerson>
            <xsl:value-of select="."/>
        </NamedPerson>
    </xsl:template>

    <xsl:template match="ro:contributor">
        <Author seq="{@seq}">
            <AuthorName>
                <xsl:apply-templates select="ro:namePart"/>
            </AuthorName>
        </Author>
    </xsl:template>

    <xsl:template match="ro:namePart">
        <xsl:value-of select="."/>
        <xsl:if test="following-sibling::ro:namePart">
            <xsl:text> </xsl:text>
        </xsl:if>
    </xsl:template>

    <xsl:template name="getCreatedDate">
        <xsl:choose>
            <xsl:when test="ro:collection/@dateModified">
                <xsl:value-of select="ro:collection/@dateModified"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:dates[@type='created']">
                <xsl:value-of select="ro:collection/ro:dates[@type='created']/ro:date/text()"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:dates[@type='dc.created']">
                <xsl:value-of select="ro:collection/ro:dates[@type='dc.created']/ro:date/text()"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:date[@type='created']">
                <xsl:value-of select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:date[@type='created']/text()"/>
            </xsl:when>
        </xsl:choose>
    </xsl:template>

</xsl:stylesheet>
