/**
 * Date Modified: $Date: 2010-09-24 15:11:16 +1000 (Fri, 24 Sep 2010) $
 * Version: $Revision: 507 $
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

import java.util.ArrayList;
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
import au.edu.apsr.pids.dao.TrustedClientDAO;
import au.edu.apsr.pids.dao.DAOException;

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
public class ListTrustedClientServlet extends HttpServlet
{
    private final Logger log = Logger.getLogger(ListTrustedClientServlet.class);

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
            // get a list of all trusted clients
			TrustedClientDAO tcdao = new TrustedClientDAO();
            ArrayList<TrustedClient> trusted_clients = tcdao.retrieveAll();
			
            ServletSupport.doSuccessResponse(response, "Successfully fetched all trusted clients", Constants.MESSAGE_TYPE_USER, trusted_clients);
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