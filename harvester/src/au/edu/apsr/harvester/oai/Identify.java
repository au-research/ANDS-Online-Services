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

import org.xml.sax.SAXException;

/**
 * This class represents an Identify response on either the server or
 * on the client
 *
 * @author Jeffrey A. Young, OCLC Online Computer Library Center
 */
public class Identify extends HarvesterVerb
{
    /**
     * Client-side Identify verb constructor
     *
     * @param baseURL the baseURL of the server to be queried
     * @exception MalformedURLException the baseURL is bad
     * @exception IOException an I/O error occurred
     */
    public Identify(String baseURL)
        throws Exception, IOException, ParserConfigurationException, SAXException,
    TransformerException
    {
        super(getRequestURL(baseURL));
    }
    
    /**
     * Get the oai:protocolVersion value from the Identify response
     * 
     * @return the oai:protocolVersion value
     * @throws TransformerException
     * @throws NoSuchFieldException
     */
    public String getProtocolVersion()
        throws TransformerException, NoSuchFieldException
    {
        if (SCHEMA_LOCATION_V2_0.equals(getSchemaLocation()))
        {
            return getElementContent("http://www.openarchives.org/OAI/2.0/", "protocolVersion");
        }
        else if (SCHEMA_LOCATION_V1_1_IDENTIFY.equals(getSchemaLocation()))
        {
            return getElementContent("http://www.openarchives.org/OAI/1.1/OAI_Identify", "protocolVersion");
        }
        else
        {
            throw new NoSuchFieldException(getSchemaLocation());
        }
    }
    
    /**
     * generate the Identify request URL for the specified baseURL
     * @param baseURL
     * @return the requestURL
     */
    private static String getRequestURL(String baseURL)
    {
        StringBuffer requestURL =  new StringBuffer(baseURL);
        requestURL.append("?verb=Identify");
        return requestURL.toString();
    }
}