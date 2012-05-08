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

import au.edu.apsr.pids.util.ProcessingException;

/**
 * <p>Interface for service authenticator classes</p>
 * <p>The purpose of an authenticator class is to authenticate an agent
 * prior to allowing access to the PI services</p>
 * 
 * @author Scott Yeadon, ANU 
 */
public interface Authenticator
{
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
    public boolean authenticate(HttpServletRequest request) throws ProcessingException;

    
    /**
     * set any properties required by the authenticator
     * 
     * @param map {@code <String,Object>}
     *          a map of authentication properties
     * 
     */
    public void setProperties(Map<String,Object> map);

    
    /**
     * add one or more properties to the existing property map
     * 
     * @param map {@code <String,Object>}
     *          a map of authentication properties
     * 
     */
    public void addProperties(Map<String,Object> map);

    
    /**
     * obtain the authenticator property map
     * 
     * @return map
     *          a map of authentication properties
     * 
     */
    public Map<String,Object> getProperties();

    
    /**
     * obtain the object associated with the provided property name
     * 
     * @return Object
     *      the value corresponding to the property name
     * 
     * @param property
     *      the name of the property to retrieve
     */    
    public Object getProperty(String property);
}