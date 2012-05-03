/**
 * Date Modified: $Date: 2010-06-24 08:52:53 +1000 (Thu, 24 Jun 2010) $
 * Version: $Revision: 443 $
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

import java.util.HashMap;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.apache.log4j.Logger;

import au.edu.apsr.pids.to.Handle;
import au.edu.apsr.pids.to.TrustedClient;
import au.edu.apsr.pids.util.Constants;
import au.edu.apsr.pids.util.ServletSupport;
import au.edu.apsr.pids.util.ProcessingException;

/**
 * Servlet for registering a new application.
 *  
 * Service parameters are described below. These are key/value
 * pairings in a URL, NOT Java args.
 * 
 * <p><strong>Service: register</strong>
 * <p><strong>Mandatory Parameters</strong>
 * <ul>
 * <li><strong>ip</strong> - 
 *          The ip of the client being registered</li>
 * <li><strong>desc</strong> - 
 *          A description client being registered</li>
 * </ul></p>
 * <p><strong>Optional Parameters</strong>
 * <ul>
 * <li><strong>appId</strong> - 
 *          The appId of the client being registered</li>
 * </ul></p>
 * </p>
 * 
 * @author Scott Yeadon, ANU 
 */
public class AddTrustedClientServlet extends HttpServlet
{
    private final Logger log = Logger.getLogger(AddTrustedClientServlet.class);

    public void init()
    {
    }
    
    /**
     *  Process a GET request
     * 
     * @param request
     *          a HTTP request
     * 
     * @param response
     *          HTTP response
     * 
     * @throws ServletException
     */
    protected void doGet(final HttpServletRequest request,
                  final HttpServletResponse response) throws ServletException
    {
        doPost(request, response);
    }


    /**
     *  Process a POST request
     * 
     * @param request
     *          a HTTP request
     * 
     * @param response
     *          HTTP response
     * 
     * @throws ServletException
     */
    protected void doPost(final HttpServletRequest request,
                  final HttpServletResponse response) throws ServletException
    {
        String adminIP = getInitParameter("admin-ip");
        if (adminIP == null)
        {
            throw new ServletException("admin-ip not set, unable to run service");
        }
        
        if (!request.getRemoteAddr().equals(adminIP))
        {
            throw new ServletException("Service only available to service administrator");
        }

        try
        {
            String ip = request.getParameter("ip");
            
            if (ip == null)
            {
                log.error("Parameter 'ip' must be provided");
                ServletSupport.doErrorResponse(response, "Parameter 'ip' must be provided", Constants.MESSAGE_TYPE_USER);
                return;
            }

            if (ip.trim().equals(""))
            {
                log.error("IP address must not contain empty values");
                ServletSupport.doErrorResponse(response, "IP address must not contain empty values", Constants.MESSAGE_TYPE_USER);
                return;
            }
            
            if (!ip.matches("[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}"))
            {
                log.error("Invalid IP Address: " + ip);
                ServletSupport.doErrorResponse(response, "IP address must not contain empty values", Constants.MESSAGE_TYPE_USER);
                return;                
            }
            
            String desc = request.getParameter("desc");
            
            if (desc == null)
            {
                log.error("Parameter 'desc' must be provided");
                ServletSupport.doErrorResponse(response, "Parameter 'desc' must be provided", Constants.MESSAGE_TYPE_USER);
                return;
            }

            if (desc.trim().equals(""))
            {
                log.error("Parameter 'desc' must not contain empty values");
                ServletSupport.doErrorResponse(response, "Parameter 'desc' must not contain empty values", Constants.MESSAGE_TYPE_USER);
                return;
            }

            if (TrustedClient.isRegistered(ip))
            {
                log.error("The ip address provided is already registered");
                ServletSupport.doErrorResponse(response, "The ip address provided is already registered", Constants.MESSAGE_TYPE_USER);                
                return;
            }

            String appId = request.getParameter("appId");
            
            if (appId != null)
            {
                if (appId.trim().equals(""))
                {
                    log.error("Parameter 'appId' must not contain empty values");
                    ServletSupport.doErrorResponse(response, "Parameter 'appId' must not contain empty values", Constants.MESSAGE_TYPE_USER);
                    return;
                }
            }

            // create an admin handle
            TrustedClient tc;
            if (appId == null)
            {
                tc = TrustedClient.create(ip, desc);
            }
            else
            {
                tc = TrustedClient.create(ip, desc, appId);
            }

            HashMap<String,String> hm = new HashMap<String,String>();
            hm.put("ip", ip);
            hm.put("appId", tc.getAppId());
            ServletSupport.doSuccessResponse(response, "Successfully created trusted client", Constants.MESSAGE_TYPE_USER, hm);
            return;
        }
        catch (ProcessingException pe)
        {
            response.setStatus(500);
            log.error("Processing Exception: ", pe);
            ServletSupport.doErrorResponse(response, pe.getMessage(), Constants.MESSAGE_TYPE_SYSTEM);
            return;
        }
        catch (Exception e)
        {
            log.error("Exception: ", e);
            ServletSupport.doErrorResponse(response, e.getMessage(), Constants.MESSAGE_TYPE_SYSTEM);
            return;
        }
    }
}