/**
 * Copyright 2006 OCLC, Online Computer Library Center
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * 
 * Note: This class is a modified version of the OCLC class.
 * Modified by The Australian National University 2008
 *
 * Date Modified: $Date: 2009-08-18 12:43:25 +1000 (Tue, 18 Aug 2009) $
 * Version: $Revision: 84 $
 */
package au.edu.apsr.harvester.oai;

import java.io.IOException;
import javax.xml.parsers.ParserConfigurationException;
import javax.xml.transform.TransformerException;

import org.w3c.dom.NodeList;
import org.xml.sax.SAXException;

/**
 * This class represents an ListMetadataFormats response on either the server or
 * on the client
 *
 * @author Jeffrey A. Young, OCLC Online Computer Library Center
 */
public class ListMetadataFormats extends HarvesterVerb {
    /**
     * Mock object constructor (for unit testing purposes)
     */
    public ListMetadataFormats() throws Exception
    {
        super();
    }
    
    /**
     * Client-side ListMetadataFormats verb constructor
     *
     * @param baseURL the baseURL of the server to be queried
     * @exception MalformedURLException the baseURL is bad
     * @exception SAXException the xml response is bad
     * @exception IOException an I/O error occurred
     */
    public ListMetadataFormats(String baseURL)
    throws Exception, IOException, ParserConfigurationException, SAXException,
    TransformerException {
        this(baseURL, null);
    }
    
    /**
     * Client-side ListMetadataFormats verb constructor (identifier version)
     * @param baseURL
     * @param identifier
     * @throws IOException
     * @throws ParserConfigurationException
     * @throws SAXException
     * @throws TransformerException
     */
    public ListMetadataFormats(String baseURL, String identifier)
    throws Exception, IOException, ParserConfigurationException, SAXException,
    TransformerException {
        super(getRequestURL(baseURL, identifier));
    }
    
    /**
     * Construct the query portion of the http request
     *
     * @return a String containing the query portion of the http request
     */
    private static String getRequestURL(String baseURL, String identifier) {
        StringBuffer requestURL =  new StringBuffer(baseURL);
        requestURL.append("?verb=ListMetadataFormats");
        if (identifier != null)
            requestURL.append("&identifier=").append(identifier);
        return requestURL.toString();
    }
    
    /**
     * Get an array of metadataFormat names
     * 
     * @return an array of format names
     */
    public String[] getSupportedFormats() throws NoSuchFieldException
    {
        NodeList nl;
        
        String schemaLocation = getSchemaLocation();
        if (schemaLocation.indexOf(SCHEMA_LOCATION_V2_0) != -1)
        {
            nl = getDocument().getElementsByTagNameNS("http://www.openarchives.org/OAI/2.0/", "metadataPrefix");
        }
        else if (schemaLocation.indexOf(SCHEMA_LOCATION_V1_1_LIST_METADATA_FORMATS) != -1)
        {
            nl = getDocument().getElementsByTagNameNS("http://www.openarchives.org/OAI/1.1/OAI_ListMetadataFormats", "metadataPrefix");
        }
        else
        {
            throw new NoSuchFieldException(schemaLocation);
        }

        if (nl.getLength() > 0)
        {
            String[] formatNames = new String[nl.getLength()];
            for (int i=0; i < nl.getLength(); i++)
            {
                formatNames[i] = nl.item(i).getTextContent();
            }
            return formatNames;
        }
        else
        {
            return new String[0];
        }
    }
}
