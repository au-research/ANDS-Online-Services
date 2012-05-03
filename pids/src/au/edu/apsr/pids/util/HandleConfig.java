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
package au.edu.apsr.pids.util;

import java.security.PrivateKey;
import java.util.Map;

import javax.servlet.ServletContext;

import net.handle.hdllib.Util;

import org.apache.log4j.Logger;

/** 
 * A singleton class for holding config information used by the web application
 * 
 * @author Scott Yeadon, ANU 
 */
public class HandleConfig
{
    private static final Logger log = Logger.getLogger(HandleConfig.class);

    private static HandleConfig hc = null;
    
    private Map<String,Object> properties = null;
        
    private  HandleConfig()
    {
    }
 
    
    /** 
     * obtain a reference to the config
     * 
     * @return HandleConfig
     *             the config
     */
    public static synchronized HandleConfig getHandleConfig()
    {
        if (hc == null)
        {
            hc = new HandleConfig();
        }
        return hc;
    }
    
    
    /** 
     * As this is a singleton class, invoking the clone method will
     * result in an exception being thrown
     * 
     * @return Object
     *             the cloned object will not be returned if attempted
     *
     * @throws CloneNotSupportedException
     */
    public Object clone() throws CloneNotSupportedException
    {
        throw new CloneNotSupportedException("Clone operation not supported"); 
    }
    
    
    /** 
     * Load the config into this object.
     * 
     * TODO: Change to config file parser and pass config file
     * path to parse.
     * 
     * @throws Exception
     *             If the initialisation information is not found
     */
    public void init(Map<String,Object> properties) throws Exception
    {
        this.properties = properties;
    }
    
    
    /** 
     * Return the namespace prefix
     * 
     * @return String
     *          The namespace prefix
     * 
     */    
    public String getPrefix()
    {
        return (String)properties.get("naming-authority");
    }
    
    
    /** 
     * Return the PrivateKey object.
     * 
     * @return String
     *          The admin private key
     * 
     */    
    public PrivateKey getPrivateKey()
    {
        return (PrivateKey)properties.get("key");
    }
}