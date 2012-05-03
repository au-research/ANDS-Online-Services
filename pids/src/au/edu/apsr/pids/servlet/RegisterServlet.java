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

import java.util.HashMap;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.apache.log4j.Logger;

import au.edu.apsr.pids.security.AuthenticationException;
import au.edu.apsr.pids.security.AuthenticationManager;
import au.edu.apsr.pids.security.Authenticator;
import au.edu.apsr.pids.security.AuthenticatorFactory;
import au.edu.apsr.pids.security.BadRequestException;
import au.edu.apsr.pids.to.Handle;
import au.edu.apsr.pids.to.Identifier;
import au.edu.apsr.pids.util.Constants;
import au.edu.apsr.pids.util.HandleSupport;
import au.edu.apsr.pids.util.ServletSupport;
import au.edu.apsr.pids.util.ProcessingException;

/**
 * Servlet for registering an administrative handle.
 * 
 * Service parameters are described below. These are key/value
 * pairings in a URL, NOT Java args.
 * 
 * NOT USED!
 * 
 * <p><strong>Service: register</strong>
 * <p><strong>Mandatory Parameters</strong>
 * <ul>
 * <li><strong>identifier</strong> - 
 *          The identifier of the agent being registered</li>
 * <li><strong>authDomain</strong> - 
 *          The authentication domain of the agent being registered</li></ul></p>
 * </p>
 * 
 * @author Scott Yeadon, ANU 
 */
public class RegisterServlet extends HttpServlet
{
    private final Logger log = Logger.getLogger(RegisterServlet.class);

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
        // do we only allow registration via specific URL
        try
        {
            Authenticator auth = AuthenticationManager.getAuthenticator(request);
            if (!auth.authenticate(request))
            {
                log.error("Authentication failed from host " + request.getRemoteHost());
                ServletSupport.doErrorResponse(response, "Autentication Failed", Constants.MESSAGE_TYPE_USER);
                return;                
            }
            
            String identifier = request.getParameter("identifier");
            String authDomain = request.getParameter("authDomain");
            
            if (identifier == null || authDomain == null)
            {
                log.error("Identifier and authDomain must be provided");
                ServletSupport.doErrorResponse(response, "Identifier and authDomain must be provided", Constants.MESSAGE_TYPE_USER);
                return;
            }
            
            if (identifier.trim().equals("") || authDomain.trim().equals(""))
            {
                log.error("Identifier and authDomain must not contain empty values");
                ServletSupport.doErrorResponse(response, "Identifier and authDomain must not contain empty values", Constants.MESSAGE_TYPE_USER);
                return;
            }

            if (Identifier.isRegistered(identifier, authDomain))
            {
                log.error("The identifier and authDomain provided is already registered");
                ServletSupport.doErrorResponse(response, "The identifier and authDomain provided is already registered", Constants.MESSAGE_TYPE_USER);                
                return;
            }
            
            // Create an admin handle
            Handle handle = Handle.createAdmin(identifier, authDomain);

            HashMap<String,String> hm = new HashMap<String,String>();
            hm.put("handle", handle.getHandle());
            ServletSupport.doSuccessResponse(response, "Successfully authenticated and created admin handle", Constants.MESSAGE_TYPE_USER, hm);
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
        catch (Exception e)
        {
            log.error("Bad Request Exception: ", e);
            ServletSupport.doErrorResponse(response, e.getMessage(), Constants.MESSAGE_TYPE_SYSTEM);
            return;
        }        
    }
}