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

import java.util.ArrayList;
import java.util.HashMap;

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
import au.edu.apsr.pids.util.ProcessingException;
import au.edu.apsr.pids.util.ServletSupport;

/**
 * <p>Servlet for deleting a value from an existing handle.</p>
 * 
 * <p>Service parameters are described below. These are key/value
 * pairings in a URL, NOT Java args.</p>
 * 
 * <p><strong>Service: deleteValueByIndex</strong>
 * <p><strong>Mandatory Parameters</strong>
 * <ul>
 * <li><strong>handle</strong> - 
 *          The handle to which the value will be added</li>
 * <li><strong>index</strong> - 
 *          The index of the value to delete. Types able to be deleted are
 *          limited to DESC or URL.</li>
 * </ul></p>
 * </p>
 * 
 * @author Scott Yeadon, ANU 
 */
public class DeleteValueByIndexServlet extends HttpServlet
{
    private final Logger log = Logger.getLogger(DeleteValueByIndexServlet.class);

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

            String handleString = request.getParameter("handle");
            String indexString = request.getParameter("index");
            
            if (indexString == null || handleString == null)
            {
                ServletSupport.doErrorResponse(response, "Handle and index must be specified", Constants.MESSAGE_TYPE_USER);
                return;
            }

            if (handleString.trim().equals("") || indexString.trim().equals("")) 
            {
                ServletSupport.doErrorResponse(response, "All parameters must have values", Constants.MESSAGE_TYPE_USER);
                return;
            }

            int index;
            try
            {
                index = Integer.valueOf(indexString);
            }
            catch (NumberFormatException nfe)
            {
                ServletSupport.doErrorResponse(response, "Invalid value provided for index", Constants.MESSAGE_TYPE_USER);
                return;
            }

            log.info("deleteValue request received from " + request.getRemoteHost());            

            // check handle actually exists
            Handle handle = Handle.find(handleString);
            if (handle == null)
            {
                ServletSupport.doErrorResponse(response, "The handle: " + handleString + " was not found", Constants.MESSAGE_TYPE_USER);
                return;
            }
            
            // check if the authentication details allow that user to add values
            Identifier identifier = Identifier.retrieve((String)auth.getProperty("identifier"),
                    (String)auth.getProperty("authDomain"));
            
            if (!handle.isAdmin(identifier))
            {
                ServletSupport.doErrorResponse(response, "Request Denied. Only the admin of this handle can add values", Constants.MESSAGE_TYPE_USER);
                return;
            }
            
            // delete value
            if (!handle.deleteAllowed(index))
            {
                ServletSupport.doErrorResponse(response, "Either this type of value is not allowed to be deleted, or a value does not exist at the provided index", Constants.MESSAGE_TYPE_USER);
                return;
            }
            
            if (!handle.deleteValue(index))
            {
                ServletSupport.doErrorResponse(response, "Handle: " + handle.getHandle() + ". Failed to delete value due to system error", Constants.MESSAGE_TYPE_USER);
                return;
            }

            ArrayList<Handle> al = new ArrayList<Handle>();
            al.add(Handle.find(handleString));

            ServletSupport.doGetHandleResponse(response, "Successfully deleted handle value", Constants.MESSAGE_TYPE_USER, al);
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