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
package au.edu.apsr.harvester.thread;

import java.io.IOException;

import org.apache.log4j.Logger;

import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.NodeList;

import au.edu.apsr.harvester.dao.DAOException;
import au.edu.apsr.harvester.to.Fragment;
import au.edu.apsr.harvester.to.Harvest;
import au.edu.apsr.harvester.util.Constants;
import au.edu.apsr.harvester.oai.Identify;
import au.edu.apsr.harvester.oai.ListRecords;

/**
 * PMH Harvest is not worried about any other request other
 * than ListRecords. Identify is used to obtain granularity and
 * earliestDatestamp, other requests are currently unused.
 * 
 * This is the "normal" OAI-PMH harvest class.
 * 
 * <p>This harvest:
 * <ul><li>harvests from an OAI-PMH data provider</li>
 * <li>only makes use of ListRecord and Identify requests</li>
 * <li>stores the result of the requests as a fragment</li>
 * <li>posts each fragment to a target service</li>
 * <li>on successful completion deletes all fragments stored during the harvest</li>
 * </ul></p>
 * 
 * @author Scott Yeadon, ANU
 */
public class PMHHarvestThread extends HarvestThread
{
    private final Logger log = Logger.getLogger(PMHHarvestThread.class);
    
    private ThreadManager threadManager = null;

    /**
     * Constructor obtains a reference to the Thread Manager
     */
    public PMHHarvestThread()
    {
        threadManager = ThreadManager.getThreadManager();
    }
    

    /**
     * execute the harvest
     */
    public void run()
    {
        try
        {
            if (threadManager.isStopped(harvest) || threadManager.isRunning(harvest))
            {
                log.info("harvest stopped by user or is running, harvest will not be executed: " + harvest.getHarvestID());
                return;
            }

            threadManager.setThreadRunning(this.harvest);
            
            log.info("harvest running: " + harvest.getHarvestID());

            setResumptionToken(harvest.getResumptionToken());
            
            if (getResumptionToken() == null)
            {
                Identify identify = new Identify(harvest.getSourceURL()); 
                Document doc = identify.getDocument();
                
                if (doc == null)
                {
                    throw new IOException("Response from Identify is null");
                }

                NodeList n = doc.getElementsByTagName("granularity");
                if (n.getLength() == 1)
                {
                    setGranularity(n.item(0).getTextContent());
                }
                
                if (harvest.getFrom() == null)
                {
                    NodeList nl2 = doc.getElementsByTagName("earliestDatestamp");
                    if (nl2.getLength() == 1)
                    {
                        setFrom(nl2.item(0).getTextContent());
                    }
                }
                else
                {
                    setFrom(harvest.getFrom());
                }
                
                getFragment(identify.toString(), "Identify");
            }
            
            setPMHArguments();            
            
            ListRecords listRecords;
            
            if (getResumptionToken() == null || getResumptionToken().length() == 0)
            {
                if ((harvest.getSet() == null) || (harvest.getSet().length() == 0))
                {
                    setSet(null);
                }
                else
                {
                    setSet(harvest.getSet());
                }
                
                listRecords = 
                    new ListRecords(harvest.getSourceURL(), getFrom(), getUntil(), null, getMetadataPrefix());
            }
            else
            {
                listRecords = 
                    new ListRecords(harvest.getSourceURL(), getResumptionToken());
            }
 
            boolean error = false;
            boolean last = false;

            while (listRecords != null)
            {
                NodeList errors = listRecords.getErrors();
                if (errors != null && errors.getLength() > 0)
                {
                    log.error("Errors in ListRecords response");
                    int length = errors.getLength();
                    for (int i=0; i<length; ++i)
                    {
                        Element item = (Element)errors.item(i);
                        log.error(item.getTagName() + ":" + 
                                  item.getAttribute("code") + ":" + 
                                  item.getTextContent());
                        
                        if (item.getAttribute("code").equals(NO_RECORDS))
                        {
                            error = false;
                        }
                        else
                        {
                            error = true;
                        }                        
                    }

                    if (error)
                    {
                        log.error(listRecords.toString());
                        break;
                    }
                }
                
                Fragment frag = getFragment(listRecords.toString(), "ListRecords");
                
                if (threadManager.isStopped(harvest))
                {
                    log.info("harvest id stopped: " + harvest.getHarvestID());
                    return;
                }
                
                if ((listRecords.getResumptionToken().length()==0) || (harvest.getMode().equals(Constants.MODE_TEST)))
                {
                    last = true;
                }
                
                postFragment(frag, harvest, "application/x-www-form-urlencoded", last);

                if (harvest.getMode().equals("test"))
                {
                    threadManager.setThreadComplete(harvest);
                    return;
                }
                
                if (listRecords.getResumptionToken().length() > 0)
                {
                    setResumptionToken(listRecords.getResumptionToken());
                }
                else
                {
                    setResumptionToken(null);
                }
                
                if (threadManager.isStopped(harvest))
                {
                    log.info("harvest id stopped: " + harvest.getHarvestID());
                    return;
                }
                
                harvest.setResumptionToken(getResumptionToken());
                harvest.update();
                
                if (getResumptionToken() == null || getResumptionToken().length() == 0)
                {
                    log.info("harvest " + harvest.getHarvestID() + " has no ListRecords resumption token, no more records will be retrieved.");
                    listRecords = null;
                }
                else
                {
                    log.info("harvest " + harvest.getHarvestID() + " ListRecords resumption token: " + getResumptionToken());
                    listRecords = new ListRecords(harvest.getSourceURL(), getResumptionToken());
                }                
            }
            
            if (error)
            {
                log.info("harvest id errored: " + harvest.getHarvestID());
                threadManager.setThreadError(harvest);
            }
            else
            {
                // end of harvest - set next from/until dates for next
                // incremental harvest
                harvest.setFrom(getNextFromDate());
                harvest.setUntil(null);
                threadManager.setThreadComplete(harvest);
                log.info("harvest id completed: " + harvest.getHarvestID());
            }
        }
        catch (IOException ioe)
        {
            log.error("IOExcpetion", ioe);
            try
            {
                threadManager.setThreadError(harvest);
            }
            catch (DAOException daoe)
            {
                log.error("DAOException", daoe);
                try
                {
                    if (Harvest.find(harvest.getHarvestID()) == null)
                    {
                        // in this case, this is OK since on cancel the harvest
                        // needs to be deleted on cancel
                        log.info("It appears the harvest was deleted while running");
                    }
                }
                catch (DAOException daoe2)
                {
                    log.error("DAOException encountered when attempting to find harvest", daoe2);                
                }
            }
        }
        catch (DAOException daoe)
        {
            log.error("DAOException", daoe);
        }
        catch (Exception e)
        {
            log.error("An Exception was encountered", e);
            try
            {
                threadManager.setThreadError(harvest);
            }
            catch (DAOException daoe)
            {
                log.error("DAOException", daoe);
            }
        }
    }
}