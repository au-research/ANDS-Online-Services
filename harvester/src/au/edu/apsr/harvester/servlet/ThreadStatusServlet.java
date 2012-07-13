/**
 * Date Modified: $Date: 2009-08-18 12:43:25 +1000 (Tue, 18 Aug 2009) $
 * Version: $Revision: 84 $
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
package au.edu.apsr.harvester.servlet;

import java.util.HashMap;
import java.util.Map;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import au.edu.apsr.harvester.dao.DAOException;
import au.edu.apsr.harvester.to.Harvest;
import au.edu.apsr.harvester.util.Constants;
import au.edu.apsr.harvester.util.ServletSupport;

import org.apache.log4j.Logger;

/**
 * Servlet for reporting the current status of a harvest.
 * 
 * Service parameters are described below. These are key/value
 * pairings in a URL, NOT Java args.
 * 
 * <p><strong>Service: getHarvestStatus</strong>
 * <p><strong>Mandatory Parameters</strong>
 * <ul>
 * <li><strong>harvestid</strong> - 
 *          The harvestid of the harvest whose status is to be reported</li>
 * </ul>
 * </p></p>
 * 
 * @author Scott Yeadon, ANU 
 */
public class ThreadStatusServlet extends HttpServlet
{
    private final Logger log = Logger.getLogger(ThreadStatusServlet.class);
    
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
        String harvestID = (String)request.getParameter("harvestid");
        if (harvestID == null)
        {
            ServletSupport.doErrorResponse(response, "Missing parameter: harvestid");
            return;
        }
        doStatus(response, harvestID);
    }    
    
    
    /**
     * Report the current status of a harvest. The status is reported
     * as an XML response.
     * 
     * @param response
     *          HTTP response
     * 
     * @param harvestID
     *          The harvest ID of the harvest of interest
     * 
     * @throws ServletException
     */
    private void doStatus(HttpServletResponse response,
                          String harvestID) throws ServletException
    {
        Harvest harvest;
        
        try
        {
            harvest = Harvest.find(harvestID);
        }
        catch (DAOException daoe)
        {
            log.error("DAOException occurred", daoe);
            throw new ServletException(daoe);
        }
        
        if (harvest == null)
        {
            log.error("No harvest record found for harvest id: " + harvestID);
            ServletSupport.doErrorResponse(response, "No harvest record found for harvest id: " + harvestID);
        }
        else
        {
            Map<String,String> map = new HashMap<String,String>();
            
            map.put("harvestid", harvest.getHarvestID());
            map.put("sourceurl", harvest.getSourceURL());
            map.put("responsetargeturl", harvest.getResponseTargetURL());
            map.put("method", harvest.getMethod());
            map.put("mode", harvest.getMode());
            map.put("advancedharvestingmode",harvest.getAHM());
            
            if (harvest.getStatusAsString().equals(Constants.STATUS_SCHEDULED_STRING))
            {
                String dateTime = ServletSupport.getUTCString(harvest.getNextRun());
                 
                if (dateTime.length()==0)
                {
                    dateTime = "Unknown";                    
                }
                ServletSupport.doSuccessResponse(response, harvest.getStatusAsString() + " for " + dateTime, map);
            }
            else
            {
                ServletSupport.doSuccessResponse(response, harvest.getStatusAsString(), map);
            }
        }
    }
}