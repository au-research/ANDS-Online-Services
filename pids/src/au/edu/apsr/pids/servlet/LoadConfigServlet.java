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
package au.edu.apsr.pids.servlet;

import java.io.File;
import java.io.FileInputStream;
import java.util.HashMap;

import javax.servlet.ServletContext;
import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;

import net.handle.hdllib.Util;

import au.edu.apsr.pids.util.HandleConfig;

import org.apache.log4j.Logger;

/**
 * Servlet which is configured in web.xml to load the handle configuration
 * whenever the servlet container is restarted
 * 
 * @author Scott Yeadon, ANU 
 */
public class LoadConfigServlet extends HttpServlet
{
    private final Logger log = Logger.getLogger(LoadConfigServlet.class);
        
    /**
     * Destruction of the servlet
     */
    public void destroy()
    {
        super.destroy();
    }
    
    /**
     * 
     * 
     * @exception ServletException
     */    
    public void init() throws ServletException
    {
        HandleConfig handleConfig = HandleConfig.getHandleConfig();
        try
        {
            HashMap<String,Object> hm = new HashMap<String,Object>();
            
            String na = getInitParameter("naming-authority");
            if (na == null)
            {
                throw new ServletException("naming-authority not set, unable to initialise");
            }
            
            hm.put("naming-authority", na);

            String configDir = getInitParameter("config-dir");
            if (configDir == null)
            {
                throw new ServletException("config-dir not set, unable to initialise");
            }
            
            hm.put("config-dir", configDir);
            
            File privateKeyFile = new File(configDir + File.separator + "admpriv.bin");
            FileInputStream in = new FileInputStream(privateKeyFile);
            byte encKeyBytes[] = new byte[(int)privateKeyFile.length()];
            int n = 0; int r= 0;
            while(n < encKeyBytes.length && (r = in.read(encKeyBytes, n, encKeyBytes.length-n)) > 0)
            {
                n +=r;
            }
            in.close();
            
            // only support unencrypted keys for now
            byte[] keyBytes = Util.decrypt(encKeyBytes, null);

            hm.put("key", Util.getPrivateKeyFromBytes(keyBytes, 0));            
            
            handleConfig.init(hm);
        }
        catch (Exception e)
        {
            log.error("Exception occurred", e);
            throw new ServletException(e);
        }
    }
}