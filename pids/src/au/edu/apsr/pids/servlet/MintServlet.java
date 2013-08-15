/**
 * Date Modified: $Date: 2009-08-25 15:13:24 +1000 (Tue, 25 Aug 2009) $
 * Version: $Revision: 119 $
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
 * <p>Servlet for minting an identifier.</p>
 * 
 * <p>Service parameters are described below. These are key/value
 * pairings in a URL, NOT Java args. Both parameters must be provided and left
 * empty if requesting a handle with no values attached.</p>
 * 
 * <p>The mint service is responsible for the creation of a handle. If type
 *  and value are both empty a handle with no values is created. The 
 *  response will be similar to that in Example 2 above. When the mint 
 *  request is actioned the handle is assigned an HS_ADMIN value at index 
 *  100 representing the handle server administrator. It is also assigned an 
 *  AGENTID value at index 101 containing the handle of the owner of the 
 *  newly created handle. If the owner is not known to the handle system, 
 *  an owner is created from the identifier and authDomain values from the 
 *  request and assigned a handle. This owner handle is also treated the 
 *  same as a newly minted handle with the addition of a non-publicly 
 *  readable DESC value at index 102 describing the owner</p>
 * 
 * <p><strong>Service: mint</strong>
 * <p><strong>Mandatory Parameters</strong>
 * <ul>
 * <li><strong>type</strong> - 
 *          The type of the initial value. Limited to DESC or URL</li>
 * <li><strong>value</strong> - 
 *          The value of the handle</li>
 * </ul></p>
 * <p><strong>Optional Parameters</strong>
 * <ul>
 * <li><strong>index</strong> - 
 *          The index at which to place the handle value</li>
 * </ul></p>
 * </p>
 * 
 * @author Scott Yeadon, ANU 
 */
public class MintServlet extends HttpServlet
{
    private final Logger log = Logger.getLogger(MintServlet.class);

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

            String type = request.getParameter("type");
            String value = request.getParameter("value");
            String index = request.getParameter("index");
            
            if (value == null || type == null)
            {
                ServletSupport.doErrorResponse(response, "Type and value must be specified", Constants.MESSAGE_TYPE_USER);
                return;
            }
            
            if ((index != null) && !index.trim().equals("")) 
            {
                if (!index.matches("^[0-9]+$"))
                {
                    ServletSupport.doErrorResponse(response, "Index must be numeric", Constants.MESSAGE_TYPE_USER);
                    return;
                }
                if (HandleSupport.isIndexReserved(Integer.valueOf(index)))
                {
                    ServletSupport.doErrorResponse(response, "Index is reserved and cannot be used", Constants.MESSAGE_TYPE_USER);
                    return;
                }
            }            

            if ((value.trim().equals("") && type.length() > 0) || (type.trim().equals("") && value.length() > 0)) 
            {
                ServletSupport.doErrorResponse(response, "The type and value either must both have values or must both be empty", Constants.MESSAGE_TYPE_USER);
                return;
            }
            
            // normalise to null if both empty - both empty means user is requesting
            // minting of a handle without an associated value
            if (type.equals("") && value.equals(""))
            {
                type = null;
                value = null;
            }
            
            if (type != null)
            {
                if (!HandleSupport.isAllowedType(type))
                {
                    ServletSupport.doErrorResponse(response, "The handle type: " + type + " is not supported by this service", Constants.MESSAGE_TYPE_USER);
                    return;
                }
                
                if (type.equals("URL"))
                {
                    if (!HandleSupport.isValidURL(value))
                    {
                        ServletSupport.doErrorResponse(response, "The URL provided is invalid or not supported by this service", Constants.MESSAGE_TYPE_USER);
                        return;
                    }
                }
            }
            
            log.info("Mint request received from " + request.getRemoteHost());

            Identifier identifier = Identifier.retrieve((String)auth.getProperty("identifier"),
                    (String)auth.getProperty("authDomain"));
            
            Handle handle = null;
            
            if (identifier != null)
            {
                if (value != null)
                {
                    if (index == null)
                    {
                        handle = Handle.create(identifier, HandleSupport.createHandleValue(type, value, 1));
                    }
                    else
                    {
                        handle = Handle.create(identifier, HandleSupport.createHandleValue(type, value, Integer.valueOf(index)));
                    }
                }
                else
                {
                    handle = Handle.create(identifier, new HandleValue[0]);
                }
            }
            else
            {
                log.info("Unable to get identifier properties: " + auth.getProperty("identifier") + " " + auth.getProperty("authDomain"));
                ServletSupport.doErrorResponse(response, "Identifier not found", Constants.MESSAGE_TYPE_USER);  
                return;          
            }
            
            ArrayList<Handle> al = new ArrayList<Handle>();
            al.add(handle);

            ServletSupport.doGetHandleResponse(response, "Successfully authenticated and created handle", Constants.MESSAGE_TYPE_USER, al);
            
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