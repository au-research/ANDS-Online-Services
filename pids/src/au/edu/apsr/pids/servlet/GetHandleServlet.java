/**
 * Date Modified: $Date: 2009-11-10 09:45:56 +1100 (Tue, 10 Nov 2009) $
 * Version: $Revision: 249 $
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
import au.edu.apsr.pids.util.ProcessingException;
import au.edu.apsr.pids.util.ServletSupport;

/**
 * <p>Servlet for obtaining values for an existing handle.</p>
 * 
 * <p>Service parameters are described below. These are key/value
 * pairings in a URL, NOT Java args.</p>
 * 
 * <p><strong>Service: getHandle</strong>
 * <p><strong>Mandatory Parameters</strong>
 * <ul>
 * <li><strong>handle</strong> - 
 *          The handle for which details are to be retrieved</li>
 * </ul></p>
 * </p>
 * 
 * @author Scott Yeadon, ANU 
 */
public class GetHandleServlet extends HttpServlet
{
    private final Logger log = Logger.getLogger(GetHandleServlet.class);

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
        try
        {
            String handleString = request.getParameter("handle");
            String value = request.getParameter("value");
            
            if (handleString == null && value==null)
            {
                ServletSupport.doErrorResponse(response, "A handle or value must be supplied", Constants.MESSAGE_TYPE_USER);
                return;
            }

            if (handleString != null)
            {
                if (handleString.trim().equals(""))
                {
                    ServletSupport.doErrorResponse(response, "Handle parameter must have a value", Constants.MESSAGE_TYPE_USER);
                    return;
                }

                Handle handle = Handle.find(handleString);
                if (handle == null)
                {
                    ServletSupport.doErrorResponse(response, "The handle: " + handleString + " was not found", Constants.MESSAGE_TYPE_USER);
                    return;
                }

                ArrayList<Handle> al = new ArrayList<Handle>();
                al.add(handle);
    
                ServletSupport.doGetHandleResponse(response, "Request successful", Constants.MESSAGE_TYPE_USER, al);
                return;
            }
            
            if (value != null)
            {
                if (value.trim().equals(""))
                {
                    ServletSupport.doErrorResponse(response, "Value parameter must have a value", Constants.MESSAGE_TYPE_USER);
                    return;
                }

                // search publicly readable values for the value passed in
                List<Handle> l = Handle.getHandlesByData(value, null, true);
                if (l.size()==1)
                {
                    ServletSupport.doGetHandleResponse(response, "Request successful", Constants.MESSAGE_TYPE_USER, l);
                }
                else if (l.size()==0)
                {
                    ServletSupport.doErrorResponse(response, "A handle with value: " + value + " was not found", Constants.MESSAGE_TYPE_USER);
                    return;
                }
                else
                {
                    ServletSupport.doListHandlesResponse(response, "Handle listing successful", Constants.MESSAGE_TYPE_USER, l);
                }
                return;                
            }
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