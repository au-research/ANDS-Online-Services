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

import java.net.MalformedURLException;
import java.net.URL;
import java.text.ParseException;
import java.util.Date;
import java.util.Iterator;
import java.util.Map;
import java.util.HashMap;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.apache.log4j.Logger;

import au.edu.apsr.harvester.dao.DAOException;
import au.edu.apsr.harvester.thread.ThreadManager;
import au.edu.apsr.harvester.to.Harvest;
import au.edu.apsr.harvester.util.ServletSupport;
import au.edu.apsr.harvester.util.Constants;
import au.edu.apsr.harvester.util.XMLSupport;

/**
 * Servlet handling the initial registering of a harvest. As soon
 * as the harvest is registered it is started.
 * 
 * Service parameters are described below. These are key/value
 * pairings in a URL, NOT Java args.
 * 
 * Note that any of these may be ignored/overridden in custom
 * harvesting classes if required.
 * 
 * <p><strong>Service: requestHarvest</strong>
 * <p><strong>Mandatory Parameters</strong>
 * <ul>
 * <li><strong>harvestid</strong> - 
 *          An externally provided harvestid. Must be unique
 *          across the Harvester application or an error will
 *          be returned</li>
 * <li><strong>responsetargeturl</strong> -
 *          A URL the harvester will POST fragments to</li>
 * <li><strong>sourceurl</strong> -
 *          The location from which to harvest</li>
 * </ul>
 * </p>
 * <p><strong>Optional Parameters</strong>
 * <ul>
 * <li><strong>mode</strong> -
 *          "test" or "harvest". Test returns only the first 
 *          ListRecords fragment and typically would be a one-off
 *          harvest and deleted once complete. Default is "harvest"</li>
 * <li><strong>method</strong> -
 *          The harvest method to use. This method must correlate
 *          to a class in the thread package with name of form 
 *          {$method}HarvestThread e.g. OAIHarvestThread. Default
 *          is OAI</li>
 * <li><strong>metadataPrefix</strong> -
 *          PMH metadataPrefix to use. Default is oai_dc</li>
 * <li><strong>from</strong> -
 *          date range harvesting. Harvest records "from" this
 *          date. Granularities supported are YYYY-MM-DD and
 *          YYYY-MM-DDTHH:mm:ssZ. Default is 0001-01-01 or
 *          0001-01-01T00:00:00Z depending on the granularity
 *          from the Identify request.</li>
 * <li><strong>until</strong> -  
 *          date range harvesting. Harvest records "until" this
 *          date. Granularities supported are YYYY-MM-DD and
 *          YYYY-MM-DDTHH:mm:ssZ. Default is the time the harvest
 *          started.</li>
 * <li><strong>set</strong> - 
 *          set spec defining a subset of records</li>
 * <li><strong>date</strong> - 
 *          a UTC date in the form YY-MM-DDThh:mm:ssZ indicating
 *          the date/time which the harvest should run</li>
 * <li><strong>frequency</strong> - 
 *          the recurrence period for a harvest. Currently supported
 *          are hourly, daily, weekly, fortnightly, monthly. If 
 *          frequency is not provided but date is, the harvest is
 *          scheduled for one-off execution at the specified date.
 *          If frequency is specified by date is not, the time the
 *          request was processed will be used as the basis for
 *          periodic scheduling. If both date and frequency are not
 *          provided the harvest will be executed immediately and treated
 *          as a one-off harvest.</li>
 * </ul></p>
 * </p>
 * <p>Arbitrary parameters are also permitted for custom harvests. These will
 * be stored against a particular harvest instance.</p>
 * 
 * @author Scott Yeadon, ANU
 */
public class RegisterHarvestServlet extends HttpServlet
{
	private final Logger log = Logger.getLogger(RegisterHarvestServlet.class);
	
	private ThreadManager threadManager = null;
	
    /**
     * obtain the ThreadManager
     * 
     * @exception ServletException
     */
    public void init() throws ServletException
    {
        threadManager = ThreadManager.getThreadManager();
    }
    
    
	/**
	 *  Process a GET request
	 * 
	 * @param request
	 *             a HTTP request
	 * 
	 * @param response
	 *             a HTTP response
	 * 
	 * @throws ServletException
	 * 
	 */
    protected void doGet(final HttpServletRequest request,
			      final HttpServletResponse response) throws ServletException
    {
        doPost(request, response);
    }


	/**
	 * Process a POST request
	 * 
	 * @param request
	 *             a HTTP request
	 * 
	 * @param response
	 *             a HTTP response
	 * 
	 * @throws ServletException
	 * 
	 */
    protected void doPost(final HttpServletRequest request,
			      final HttpServletResponse response) throws ServletException
    {
        String responseTargetURL = (String)request.getParameter("responsetargeturl");
        if (responseTargetURL == null)
        {
            ServletSupport.doErrorResponse(response, "Missing parameter: responsetargeturl");
            return;
        }
                
        String msg = checkURL(responseTargetURL);        
        if (!msg.equals("''"))
        {
            ServletSupport.doErrorResponse(response, msg);
            return;
        }
        
        String harvestID = (String)request.getParameter("harvestid");
        if (harvestID == null)
        {
            ServletSupport.doErrorResponse(response, "Missing parameter: harvestid");
            return;
        }
        
        if (harvestID.length() == 0)
        {
            ServletSupport.doErrorResponse(response, "Empty parameter: harvestid");
            return;
        }
        
        String sourceURL = (String)request.getParameter("sourceurl");
        if (sourceURL == null)
        {
            ServletSupport.doErrorResponse(response, "Missing parameter: sourceurl");
            return;
        }
        
        msg = checkURL(sourceURL);        
        if (!msg.equals("''"))
        {
            ServletSupport.doErrorResponse(response, msg);
            return;
        }

        Harvest harvest = new Harvest(responseTargetURL, harvestID, sourceURL);
        
        String method = (String)request.getParameter("method");
        if (method != null)
        {
            String s = "au.edu.apsr.harvester.thread." + method + "HarvestThread";
            try
            {
                Class<?> c = Class.forName(s);
            }
            catch (ClassNotFoundException cnfe)
            {
                ServletSupport.doErrorResponse(response, "Bad value in method param: " + method + ". Cannot find class with name " + s);
                return;
            }
            
            harvest.setMethod(method);
        }

        String mode = (String)request.getParameter("mode");
        if (mode != null)
        {
            if (!mode.equals("test") && !mode.equals("harvest"))
            {
                ServletSupport.doErrorResponse(response, "Bad value in mode param:" + mode);
                return;
            }
            
            if (mode.equals(Constants.MODE_TEST))
            {
                harvest.setMode(Constants.MODE_TEST);
            }
        }
        
        String metadataPrefix = (String)request.getParameter("metadataPrefix");
        if (metadataPrefix == null || metadataPrefix.length()==0)
        {
            // bodgy for ORCA
            if (method != null && method.equals("ORCA"))
            {
                harvest.setMetadataPrefix("rif");
            }
            else
            {
                harvest.setMetadataPrefix("oai_dc");
            }
        }
        else
        {
            harvest.setMetadataPrefix(metadataPrefix);
        }
        
        
        String from = (String)request.getParameter("from");
        if (from != null)
        {
            if (from.matches("^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}Z$") || from.matches("^[0-9]{4}-[0-9]{2}-[0-9]{2}$"))
            {
                harvest.setFrom(from);
            }
        }


        String until = (String)request.getParameter("until");
        if (until != null)
        {
            if (until.matches("^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}Z$") || from.matches("^[0-9]{4}-[0-9]{2}-[0-9]{2}$"))
            {
                harvest.setUntil(until);
            }
        }
        
        String set = (String)request.getParameter("set");        
        if (set != null && set.length() > 0)
        {
            harvest.setSet(set);
        }

        String ahm = (String)request.getParameter("ahm");        
        if (ahm != null && ahm.length() > 0)
        {
            harvest.setAHM(ahm);
        }
        
        String runDate = (String)request.getParameter("date");        
        if (runDate != null && runDate.length() > 0)
        {
            if (runDate.matches("^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}Z$"))
            {
                Date d = null;
                try
                {
                    d = ServletSupport.getDate(XMLSupport.TIMESTAMP_UTC_FORMAT, runDate);
                }
                catch (ParseException pe)
                {
                    log.error("ParseException occurred", pe);
                    throw new ServletException(pe);
                }
                harvest.setNextRun(d);
            }
            else
            {
                ServletSupport.doErrorResponse(response, "Bad date format:" + runDate + ". Must be of form YYYY-MM-DDThh:mm:ssZ");
                return;                
            }
        }
        else
        {
            harvest.setNextRun(new Date());
        }

        String frequency = (String)request.getParameter("frequency");        
        if (frequency != null && frequency.length() > 0)
        {
            harvest.setFrequency(frequency);
        }

        // Add any custom harvest parameters
        Map<String,String[]> parms = request.getParameterMap();
        Iterator<Map.Entry<String, String[]>> it = parms.entrySet().iterator();
        // avoid a bunch of string comparisons
        HashMap<String,String> hm = new HashMap<String,String>();
        hm.put("harvestid", "harvestid");
        hm.put("responsetargeturl", "responsetargeturl");
        hm.put("sourceurl", "sourceurl");
        hm.put("mode", "mode");
        hm.put("method", "method");
        hm.put("metadataPrefix", "metadataPrefix");
        hm.put("from", "from");
        hm.put("until", "until");
        hm.put("set", "set");
        hm.put("date", "date");        
        hm.put("frequency", "frequency");

        while (it.hasNext())
        {
            Map.Entry<String, String[]> pair = (Map.Entry<String, String[]>)it.next();
            String key = pair.getKey();
            if (!hm.containsKey(key))
            {
                harvest.addParameter(key, pair.getValue()[0]);
            }
        }
        
        try
        {
            if (harvest.getMode().equals("test"))
            {
                harvest.setNextRun(null);
                harvest.setFrequency(null);
            }
            harvest.register();
            
            Map<String,String> map = new HashMap<String,String>();
            
            map.put("harvestid", harvest.getHarvestID());
            map.put("sourceurl", harvest.getSourceURL());
            map.put("responsetargeturl", harvest.getResponseTargetURL());
            map.put("method", harvest.getMethod());
            map.put("mode", harvest.getMode());

            if (threadManager.schedule(harvest))
            {
                ServletSupport.doSuccessResponse(response, "Harvest has been scheduled", map);
            }
            else
            {
                ServletSupport.doErrorResponse(response, "Harvest already running", map);
            }
        }
        catch (DAOException daoe)
        {
            log.error("DAOException occurred", daoe);
            ServletSupport.doErrorResponse(response, daoe.getMessage());
            return;
        }
        catch (Exception e)
        {
            log.error("Exception occurred", e);
            throw new ServletException(e);
        }
    }
    
    
    /**
     * Check a URL is valid
     * 
     * @param url
     *          the url
     * 
     * @return String
     *          an error message, else empty string
     */
    private String checkURL(String url)
    {
        String msg = "''";
        try
        {
            URL urlObject = new URL(url);
        }
        catch (MalformedURLException mue)
        {
            msg = "Bad URL '" + url + "':" + mue.getMessage();
        }
        
        return msg;
    }
}