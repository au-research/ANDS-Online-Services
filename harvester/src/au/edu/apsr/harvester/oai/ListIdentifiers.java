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
import java.io.UnsupportedEncodingException;
import java.net.URLEncoder;
import javax.xml.parsers.ParserConfigurationException;
import javax.xml.transform.TransformerException;

import org.xml.sax.SAXException;

/**
 * This class represents an ListIdentifiers response on either the server or
 * on the client
 *
 * @author Jeffrey A. Young, OCLC Online Computer Library Center
 */
public class ListIdentifiers extends HarvesterVerb {
    /**
     * Mock object constructor (for unit testing purposes)
     */
    public ListIdentifiers() throws Exception
    {
        super();
    }
    
    /**
     * Client-side ListIdentifiers verb constructor
     *
     * @param baseURL the baseURL of the server to be queried
     * @exception MalformedURLException the baseURL is bad
     * @exception SAXException the xml response is bad
     * @exception IOException an I/O error occurred
     */
    public ListIdentifiers(String baseURL, String from, String until,
            String set, String metadataPrefix)
    throws Exception, IOException, ParserConfigurationException, SAXException,
    TransformerException {
        super(getRequestURL(baseURL, from, until, set, metadataPrefix));
    }
    
    /**
     * Client-side ListIdentifiers verb constructor (resumptionToken version)
     * @param baseURL
     * @param resumptionToken
     * @throws IOException
     * @throws ParserConfigurationException
     * @throws SAXException
     * @throws TransformerException
     */
    public ListIdentifiers(String baseURL, String resumptionToken)
    throws Exception, IOException, ParserConfigurationException, SAXException,
    TransformerException {
        super(getRequestURL(baseURL, resumptionToken));
    }
    
    /**
     * Get the oai:resumptionToken from the response
     * 
     * @return the oai:resumptionToken value
     * @throws TransformerException
     * @throws NoSuchFieldException
     */
    public String getResumptionToken()
    throws TransformerException, NoSuchFieldException
    {
        if (SCHEMA_LOCATION_V2_0.equals(getSchemaLocation()))
        {
            return getElementContent("http://www.openarchives.org/OAI/2.0/", "resumptionToken");
        }
        else if (SCHEMA_LOCATION_V1_1_LIST_IDENTIFIERS.equals(getSchemaLocation()))
        {
            return getElementContent("http://www.openarchives.org/OAI/1.1/OAI_ListIdentifiers", "resumptionToken");
        }
        else
        {
            throw new NoSuchFieldException(getSchemaLocation());
        }
    }
    
    /**
     * Construct the query portion of the http request
     *
     * @return a String containing the query portion of the http request
     */
    private static String getRequestURL(String baseURL, String from,
            String until, String set,
            String metadataPrefix) {
        StringBuffer requestURL =  new StringBuffer(baseURL);
        requestURL.append("?verb=ListIdentifiers");
        if (from != null) requestURL.append("&from=").append(from);
        if (until != null) requestURL.append("&until=").append(until);
        if (set != null) requestURL.append("&set=").append(set);
        requestURL.append("&metadataPrefix=").append(metadataPrefix);
        return requestURL.toString();
    }
    
    /**
     * Construct the query portion of the http request (resumptionToken version)
     * @param baseURL
     * @param resumptionToken
     * @return
     */
    private static String getRequestURL(String baseURL,
            String resumptionToken) throws UnsupportedEncodingException 
    {
        StringBuffer requestURL =  new StringBuffer(baseURL);
        requestURL.append("?verb=ListIdentifiers");
        requestURL.append("&resumptionToken=").append(URLEncoder.encode(resumptionToken,"UTF-8"));
        return requestURL.toString();
    }
}
