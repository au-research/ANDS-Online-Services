/**
 * Date Modified: $Date: 2012-04-04 12:13:39 +1000 (Wed, 04 Apr 2012) $
 * Version: $Revision: 1695 $
 * 
 * Copyright 2008 The Australian National University (ANU)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
package org.ands.rifcs.base;

/**
 * Class containing constants
 * 
 * @author Scott Yeadon
 *
 */
public class Constants
{
    /** Name of the date accessioned attribute */
    public static final String ATTRIBUTE_DATE_ACCESSIONED = "dateAccessioned";

    /** Name of the dateFrom attribute */
    public static final String ATTRIBUTE_DATE_FROM = "dateFrom";

    /** Name of the dateGenerated attribute */
    public static final String ATTRIBUTE_DATE_GENERATED = "dateGenerated";
    
    /** Name of the date modified attribute */
    public static final String ATTRIBUTE_DATE_MODIFIED = "dateModified";

    /** Name of the dateTo attribute */
    public static final String ATTRIBUTE_DATE_TO = "dateTo";

    /** Name of the group attribute */
    public static final String ATTRIBUTE_GROUP = "group";

    /** Name of the termIdentifier attribute */
    public static final String ATTRIBUTE_TERM_IDENTIFIER = "termIdentifier";

    /** Name of the language attribute */
    public static final String ATTRIBUTE_LANG = "xml:lang";

    /** Name of the required attribute */
    public static final String ATTRIBUTE_REQUIRED = "required";
    
    /** The XML schemaLocation attribute name */
    public static final String ATTRIBUTE_SCHEMA_LOCATION = "xsi:schemaLocation";
    
    /** The seq attribute name */
    public static final String ATTRIBUTE_SEQ = "seq";
    
    /** The style attribute name */
    public static final String ATTRIBUTE_STYLE = "style";

    /** Name of the type attribute */
    public static final String ATTRIBUTE_TYPE = "type";
    
    /** Name of the dateFormat attribute */
    public static final String ATTRIBUTE_DATE_FORMAT = "dateFormat";

    /** Name of the use attribute */
    public static final String ATTRIBUTE_USE = "use";
    
    /** Name of the access policy element */
    public static final String ELEMENT_ACCESS_POLICY = "accessPolicy";
    
    /** Name of the activity element */
    public static final String ELEMENT_ACTIVITY = "activity";
    
    /** Name of the address element */
    public static final String ELEMENT_ADDRESS = "address";
    
    /** Name of the addressPart element */
    public static final String ELEMENT_ADDRESSPART = "addressPart";
    
    /** Name of the arg element */
    public static final String ELEMENT_ARG = "arg";
    
    /** Name of the desc element */
    public static final String ELEMENT_DESCRIPTION = "description";

    /** Name of the citationInfo element */
    public static final String ELEMENT_CITATIONINFO = "citationInfo";

    /** Name of the citationMetadata element */
    public static final String ELEMENT_CITATION_METADATA = "citationMetadata";

    /** Name of the collection element */
    public static final String ELEMENT_COLLECTION = "collection";

    /** Name of the context element */
    public static final String ELEMENT_CONTEXT = "context";

    /** Name of the contributor element */
    public static final String ELEMENT_CONTRIBUTOR = "contributor";

    /** Name of the coverage element */
    public static final String ELEMENT_COVERAGE = "coverage";

    /** Name of the date element */
    public static final String ELEMENT_DATE = "date";

    /** Name of the startDate element */
    public static final String ELEMENT_START_DATE = "startDate";

    /** Name of the endDate element */
    public static final String ELEMENT_END_DATE = "endDate";

    /** Name of the edition element */
    public static final String ELEMENT_EDITION = "edition";

    /** Name of the publisher element */
    public static final String ELEMENT_PUBLISHER = "publisher";

    /** Name of the electronic address element */
    public static final String ELEMENT_ELECTRONIC = "electronic";

    /** Name of the fullCitation element */
    public static final String ELEMENT_FULL_CITATION = "fullCitation";

    /** Name of the identifier element */
    public static final String ELEMENT_IDENTIFIER = "identifier";

    /** Name of the key element */
    public static final String ELEMENT_KEY = "key";

    /** Name of the location element */
    public static final String ELEMENT_LOCATION = "location";

    /** Name of the name element */
    public static final String ELEMENT_NAME = "name";

    /** Name of the namePart element */
    public static final String ELEMENT_NAMEPART = "namePart";

    /** Name of the notes element */
    public static final String ELEMENT_NOTES = "notes";

    /** Name of the originating source element */
    public static final String ELEMENT_ORIG_SOURCE = "originatingSource";

    /** Name of the party element */
    public static final String ELEMENT_PARTY = "party";

    /** Name of the physical address element */
    public static final String ELEMENT_PHYSICAL = "physical";

    /** Name of the place published element */
    public static final String ELEMENT_PLACE_PUBLISHED = "placePublished";

    /** Name of the registryObject element */
    public static final String ELEMENT_REGISTRY_OBJECT = "registryObject";

    /** Name of the relatedInfo element */
    public static final String ELEMENT_RELATED_INFO = "relatedInfo";

    /** Name of the Rights element */
    public static final String ELEMENT_RIGHTS = "rights";

    /** Name of the RightsInfo element */
    public static final String ELEMENT_RIGHTS_INFO = "rightsInfo";

    /** Name of the RightsTypedInfo element */
    public static final String ELEMENT_RIGHTS_TYPED_INFO = "rightsTypedInfo";

    /** Name of the ExistenceDates element */
    public static final String ELEMENT_EXISTENSE_DATES = "existenceDates";

    /** Name of the rightsUri element */
    public static final String ATTRIBUTE_RIGHTS_URI = "rightsUri";

    /** Name of the relatedObject element */
    public static final String ELEMENT_RELATED_OBJECT = "relatedObject";

    /** Name of the relation element */
    public static final String ELEMENT_RELATION = "relation";

    /** Name of the RIF-CS root element */
    public static final String ELEMENT_REGISTRY_OBJECTS = "registryObjects";

    /** Name of the service element */
    public static final String ELEMENT_SERVICE = "service";

    /** Name of the spatial element */
    public static final String ELEMENT_SPATIAL = "spatial";

    /** Name of the subject element */
    public static final String ELEMENT_SUBJECT = "subject";

    /** Name of the title element */
    public static final String ELEMENT_TITLE = "title";

    /** Name of the temporal element */
    public static final String ELEMENT_TEMPORAL = "temporal";

    /** Name of the text element */
    public static final String ELEMENT_TEXT = "text";

    /** Name of the uri element */
    public static final String ELEMENT_URI = "uri";

    /** Name of the url element */
    public static final String ELEMENT_URL = "url";

    /** Name of the value element */
    public static final String ELEMENT_VALUE = "value";

    /** rightsStatement of the value element */
    public static final String ELEMENT_RIGHTS_STATEMENT = "rightsStatement";

    /** licence of the value element */
    public static final String ELEMENT_LICENCE = "licence";

    /** accessRights of the value element */
    public static final String ELEMENT_ACCESS_RIGHTS = "accessRights";

    /** The rif namespace prefix for internal use */
    public static final String NS_PREFIX = "xmlns:rif";

    /** RIF-CS schema namespace location */
    public static final String NS_RIFCS = "http://ands.org.au/standards/rif-cs/registryObjects";

    /** XML schema instance namespace location */
    public static final String NS_SCHEMA = "http://www.w3.org/2001/XMLSchema-instance";
    
    /** XML namespace location */
    public static final String NS_XML = "http://www.w3.org/XML/1998/namespace";

    /** RIF-CS schema location base path */
    public static final String SCHEMA_REGISTRY_OBJECTS = "http://services.ands.org.au/home/orca/schemata/registryObjects.xsd";
    public static final String SCHEMA_REGISTRY_TYPES = "http://services.ands.org.au/home/orca/schemata/registryTypes.xsd";
    public static final String SCHEMA_ACTIVITY = "http://services.ands.org.au/home/orca/schemata/activity.xsd";
    public static final String SCHEMA_COLLECTION = "http://services.ands.org.au/home/orca/schemata/collection.xsd";
    public static final String SCHEMA_PARTY = "http://services.ands.org.au/home/orca/schemata/party.xsd";
    public static final String SCHEMA_SERVICE = "http://services.ands.org.au/home/orca/schemata/service.xsd";
    
    /** Java Date Format for UTC dates */
    public static final String TIMESTAMP_UTC_FORMAT = "yyyy-MM-dd'T'HH:mm:ss'Z'";
}