/**
 * Date Modified: $Date: 2009-08-18 13:22:16 +1000 (Tue, 18 Aug 2009) $
 * Version: $Revision: 89 $
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
package au.edu.apsr.pids.security;

import java.io.BufferedReader;
import java.io.IOException;
import java.util.HashMap;
import java.util.Iterator;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServletRequest;
import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;

import org.apache.log4j.Logger;
import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;

import au.edu.apsr.pids.util.Constants;

/**
 * <p>Class for managing PI service authentication</p>
 * <p>This class parses the message body for authentication properties
 * which are stored to a property map prior to instantiating the appropriate
 * Authenticator and setting its properties to those parsed in the request
 * body</p>
 * 
 * @author Scott Yeadon, ANU 
 */
public class AuthenticationManager
{
    private static final Logger log = Logger.getLogger(AuthenticationManager.class);
    
    /**
     * obtain the appropriate authenticator class based on the authType
     * property in the request body
     * 
     * @return Authenticator
     *            An Authenticator implementation
     *            
     * @param request
     *          an HTTP Servlet request
     *          
     * @throws AuthenticationException
     * @throws BadRequestException
     * @throws IOException          
     * 
     */
    public static Authenticator getAuthenticator(HttpServletRequest request) throws AuthenticationException, BadRequestException, IOException
    {
        HashMap<String,Object> properties = new HashMap<String,Object>();
        
        try
        {
            // create a DocumentBuilderFactory
            DocumentBuilderFactory factory = DocumentBuilderFactory.newInstance();
            
            // create a DocumentBuilder (DOM Parser)
            DocumentBuilder builder = factory.newDocumentBuilder();
            
            // create an EMPTY XML document for the output
            Document doc = builder.parse(request.getInputStream());
            
            if (doc != null)
            {
                NodeList nl = doc.getDocumentElement().getElementsByTagName("properties");
                if (nl.getLength() > 0)
                {
                    NodeList propNodes = ((Element)nl.item(0)).getElementsByTagName("property");
                    int numNodes = propNodes.getLength(); 

                    for (int i = 0; i < numNodes; i++)
                    {
                        if (propNodes.item(i) instanceof Element)
                        {
                            String name = ((Element)propNodes.item(i)).getAttribute("name");
                            String value = ((Element)propNodes.item(i)).getAttribute("value");
                            if (name.length() !=0 && value.length() != 0)
                            {
                                properties.put(name, value);
                            }
                        }
                    }
                }
            }
        }
        catch(Exception e)
        {
            log.error("Exception:", e);
            throw new IOException(e.getMessage());
        }

       if (!properties.containsKey(Constants.ARG_AUTH_TYPE))
        {
            properties.put(Constants.ARG_AUTH_TYPE, new String("SSLHost"));
        }
        
        Authenticator auth = AuthenticatorFactory.getAuthenticator((String)properties.get(Constants.ARG_AUTH_TYPE));
        auth.addProperties(properties);
        
        return auth;
    }
}