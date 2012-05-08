/**
 * Date Modified: $Date: 2010-02-11 11:05:33 +1100 (Thu, 11 Feb 2010) $
 * Version: $Revision: 303 $
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

import java.util.Map;

import javax.servlet.http.HttpServletRequest;

import org.apache.log4j.Logger;

import net.handle.hdllib.HandleException;

import au.edu.apsr.pids.dao.DAOException;
import au.edu.apsr.pids.to.Handle;
import au.edu.apsr.pids.to.Identifier;
import au.edu.apsr.pids.to.TrustedClient;
import au.edu.apsr.pids.util.ProcessingException;

/**
 * <p>Class for SSL host-based authentication</p>
 * <p>This class requires identifier and authentication domain properties
 * in order to provide access to PI services. If the identifier and
 * authentication domain are not registered within the PI webapp, or the 
 * identifier and authentication domain are not specified, access
 * to services is refused</p>
 * 
 * @author Scott Yeadon, ANU 
 */
public class SSLHostAuthenticator implements Authenticator
{
    private Logger log = Logger.getLogger(SSLHostAuthenticator.class);
    
    private Map<String,Object> properties = null;
    
    /**
     * run the authentication checking
     * 
     * @return boolean
     *     <code>true</code> if authenticates successfully otherwise <code>false</code>
     * 
     * @param request
     *          a HTTP Servlet request
     * 
     * @throws ProcessingException
     */
    public boolean authenticate(HttpServletRequest request) throws ProcessingException
    {
        TrustedClient tc = TrustedClient.retrieve(request.getRemoteAddr());
        
        if (tc == null)
        {
            log.error("Request Denied - unregistered client: " + request.getRemoteAddr() + ". Client must be registered in order to use service");
            return false;
        }

        String authDomain = (String)properties.get("authDomain");
        String identifier = (String)properties.get("identifier");
        String appId = (String)properties.get("appId");
        
        if (authDomain == null || identifier == null || appId == null)
        {
            log.error("authDomain and/or identifier and/or appId is null");
            return false;
        }
        
        if (!tc.getAppId().equals(appId))
        {
            log.error("The supplied application identifier does not match registered identifier");
            return false;
        }
        
        if (!isRegisteredIdentifier(identifier, authDomain))
        {
            try
            {
                Handle handle = Handle.createAdmin(identifier, authDomain);
                log.info("Identifier " + identifier + "," + authDomain + " is not a registered user of this service, added");
            }
            catch (DAOException daoe)
            {
                log.error("Caught DAO Exception:", daoe);
                throw new ProcessingException(daoe);
            }
            catch (HandleException daoe)
            {
                log.error("Caught Handle Exception:", daoe);
                throw new ProcessingException(daoe);
            }
        }

        return true;
    }


    /**
     * set any properties required by the authenticator
     * 
     * @param map {@code <String,Object>}
     *          a map of authentication properties
     * 
     */
    public void setProperties(Map<String,Object> map)
    {
        this.properties = map;
    }


    /**
     * add one or more properties to the existing property map
     * 
     * @param map  {@code <String,Object>}
     *          a map of authentication properties
     * 
     */
    public void addProperties(Map<String,Object> map)
    {
        if (properties == null)
        {
            this.properties = map;
        }
        else
        {
            this.properties.putAll(map);
        }
    }


    /**
     * obtain the authenticator property map
     * 
     * @return map {@code <String,Object>}
     *          a map of authentication properties
     * 
     */
    public Map<String,Object> getProperties()
    {
        return this.properties;
    }


    /**
     * obtain the object associated with the provided property name
     * 
     * @return Object
     *      the value corresponding to the property name
     * 
     * @param property
     *      the name of the property to retrieve
     */    
    public Object getProperty(String property)
    {
        return this.properties.get(property);
    }

    
    /**
     * obtain the object associated with the provided property name
     * 
     * @return boolean
     *      <code>true</code> if agent has been registered, else <code>false</code>
     * 
     * @param identifier
     *      the identifier of the agent requesting access to PI services
     *
     * @param authDomain
     *      the authentication domain of the agent requesting access to PI services
     */    
    private boolean isRegisteredIdentifier(String identifier,
                                           String authDomain) throws ProcessingException
    {
        if (Identifier.isRegistered(identifier, authDomain))
        {
            return true;
        }

        return false;
    }
}