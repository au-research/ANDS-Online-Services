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

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.apache.log4j.Logger;

import net.handle.hdllib.HandleException;
import net.handle.hdllib.HandleValue;

import au.edu.apsr.pids.security.AuthenticationException;
import au.edu.apsr.pids.security.AuthenticationManager;
import au.edu.apsr.pids.security.Authenticator;
import au.edu.apsr.pids.security.AuthenticatorFactory;
import au.edu.apsr.pids.security.BadRequestException;
import au.edu.apsr.pids.to.Handle;
import au.edu.apsr.pids.to.Identifier;
import au.edu.apsr.pids.util.Constants;
import au.edu.apsr.pids.util.HandleSupport;
import au.edu.apsr.pids.util.HandleConfig;
import au.edu.apsr.pids.util.ProcessingException;
import au.edu.apsr.pids.util.ServletSupport;

/**
 * <p>Servlet for listing the requestors handles</p>
 * <p>Note: this service was written in the context of a prototype
 * user interface. Clients are encouraged to contact ANDS service
 * provider where a requirement for listing a large volume of
 * handles exists to discuss development of alternative services
 * to better meet these requirements</p>
 * 
 * <p>Note: this service is prototype only and imposes a limit on the
 * number of handles returned (1000). If there is a need for obtaining
 * all handles owned by a particular user please contact the service
 * maintenance agency to discuss requirements as a more targetted service
 * may need to be implemented to meet requirements. Alternatively, to obtain
 * a full listing of handles use repeated calls to this service using
 * the final handle in each response as the startHandle value.</p>
 *
 * <p>Service parameters are described below. These are key/value
 * pairings in a URL, NOT Java args.</p>
 * 
 * <p><strong>Service: listHandles</strong>
 * <p><strong>Mandatory Parameters</strong>
 * <ul>
 * <li>None</li>
 * </ul></p>
 * <p><strong>Optional Parameters</strong>
 * <ul>
 * <li><strong>startHandle</strong> - 
 *          The handle from which to start the listing. The startHandle is exclusive so will
 *          not appear in the listing.</li>
 * </ul></p>
 * </p>
 * 
 * @author Scott Yeadon, ANU 
 */
public class ListHandlesServlet extends HttpServlet
{
    private final Logger log = Logger.getLogger(ListHandlesServlet.class);

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
        Authenticator auth = null;
        try
        {
            auth = AuthenticationManager.getAuthenticator(request);
            if (!auth.authenticate(request))
            {
                log.error("Authentication failed from host " + request.getRemoteHost());
                ServletSupport.doErrorResponse(response, "Authentication Failed", Constants.MESSAGE_TYPE_USER);
                return;
            }
            
            String startHandle = request.getParameter("startHandle");
            
            if (startHandle != null)
            {
                if (startHandle.trim().equals(""))
                {
                    ServletSupport.doErrorResponse(response, "startHandle parameter must have a value", Constants.MESSAGE_TYPE_USER);
                    return;
                }
                
                HandleConfig hc = HandleConfig.getHandleConfig();
                if (!startHandle.startsWith(hc.getPrefix()))
                {
                    ServletSupport.doErrorResponse(response, "startHandle has a different prefix to what is configured in the Local Handle Server", Constants.MESSAGE_TYPE_USER);
                    return;
                }
                
                if (startHandle.length() <= hc.getPrefix().length() + 1)
                {
                    ServletSupport.doErrorResponse(response, "startHandle does not appear to be a valid handle", Constants.MESSAGE_TYPE_USER);
                    return;
                }
            }

            Identifier identifier = Identifier.retrieve((String)auth.getProperty("identifier"),
                    (String)auth.getProperty("authDomain"));

            List<String> l = Handle.getHandleStrings(identifier, startHandle);
            ServletSupport.doListStringsResponse(response, "Handle listing successful", Constants.MESSAGE_TYPE_USER, l);
            return;
        }
        catch (AuthenticationException ae)
        {
            log.error("Authentication failed from host " + request.getRemoteHost());
            ServletSupport.doErrorResponse(response, ae.getMessage(), Constants.MESSAGE_TYPE_SYSTEM);
            return;
        }
        catch (BadRequestException bre)
        {
            log.error("Bad Request Exception: ", bre);
            ServletSupport.doErrorResponse(response, bre.getMessage(), Constants.MESSAGE_TYPE_SYSTEM);
            return;
        }
        catch (ProcessingException pe)
        {
            log.error("Processing Exception: ", pe);
            ServletSupport.doErrorResponse(response, pe.getMessage(), Constants.MESSAGE_TYPE_SYSTEM);
            return;
        }
        catch (HandleException he)
        {
            log.error("Handle Exception: ", he);
            ServletSupport.doErrorResponse(response, he.toString(), Constants.MESSAGE_TYPE_SYSTEM);
            return;
        }
        catch (Exception e)
        {
            log.error("Bad Request Exception: ", e);
            ServletSupport.doErrorResponse(response, e.getMessage(), Constants.MESSAGE_TYPE_SYSTEM);
            return;
        }
    }
}