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

import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.ServletRequest;
import javax.servlet.ServletResponse;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.apache.log4j.Logger;

import au.edu.apsr.pids.security.AuthenticationException;

/**
 * Authenticator Factory to instantiate authenticator classes based
 * on the type of authentication being requested
 * 
 * @author Scott Yeadon, ANU 
 */
public class AuthenticatorFactory
{
    private static Logger log = Logger.getLogger(AuthenticatorFactory.class);

    /**
     * obtain the appropriate authenticator class based on the provided 
     * authType. The class instantiated is expected to be of the name
     * au.edu.apsr.pids.security.&lt;authType&gt;Authenticator
     * 
     * @return Authenticator
     *            An Authenticator implementation
     *            
     * @param authType
     *          the type of authentication being requested
     *          
     * @throws AuthenticationException
     * 
     */
    public static Authenticator getAuthenticator(String authType) throws AuthenticationException
    {
        return getAuthenticatorInstance(authType);
    }


    /**
     * obtain the appropriate authenticator class based on the provided 
     * name. The class instantiated is expected to be of the name
     * au.edu.apsr.pids.security.&lt;name&gt;Authenticator
     * 
     * @return Authenticator
     *            An Authenticator implementation
     *            
     * @param name
     *          the type of authentication being requested
     *          
     * @throws AuthenticationException
     * 
     */
    private static Authenticator getAuthenticatorInstance(String name) throws AuthenticationException
    {
        Authenticator auth = null;
        
        try
        {
            if (!name.contains("."))
            {
                name = "au.edu.apsr.pids.security." + name + "Authenticator";
            }
            
            Class<?> c = Class.forName(name);
            auth =  (Authenticator)c.newInstance();            
        }
        catch (ClassNotFoundException cnfe)
        {
            log.error(cnfe);
            throw new AuthenticationException(cnfe);
        }
        catch (InstantiationException ie)
        {
            log.error(ie);
            throw new AuthenticationException(ie);
        }
        catch (IllegalAccessException iae)
        {
            log.error(iae);
            throw new AuthenticationException(iae);
        }        

        return auth;
    }
}