/**
 * Date Modified: $Date: 2010-05-19 12:28:12 +1000 (Wed, 19 May 2010) $
 * Version: $Revision: 375 $
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

import java.io.File;
import java.io.IOException;

import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;
import javax.xml.transform.stream.StreamSource;
import javax.xml.transform.TransformerFactory;

import org.apache.log4j.Level;
import org.apache.log4j.Logger;

import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;

import org.w3c.dom.ls.LSSerializer;
import org.w3c.dom.ls.DOMImplementationLS;
import org.w3c.dom.DOMImplementation;

import javax.xml.transform.Source;
import javax.xml.transform.Transformer;
import javax.xml.transform.TransformerException;
import javax.xml.transform.dom.DOMResult;
import javax.xml.transform.dom.DOMSource;

import au.edu.apsr.harvester.dao.DAOException;
import au.edu.apsr.harvester.thread.HarvestThread;
import au.edu.apsr.harvester.thread.RIFHarvestThread;
import au.edu.apsr.harvester.thread.ThreadManager;
import au.edu.apsr.harvester.to.Fragment;
import au.edu.apsr.harvester.to.Harvest;
import au.edu.apsr.harvester.oai.Identify;
import au.edu.apsr.harvester.oai.ListRecords;
import au.edu.apsr.harvester.util.Constants;

/**
 * A RIF Harvest is not worried about any other request other
 * than ListRecords. Identify is used to obtain granularity and
 * earliestDatestamp, other requests are currently unused.
 * 
 * As this harvest is attuned to ORCA requirements it strips out
 * all OAI-PMH markup and runs an XSL transform over the metadata
 * payload before posting the resultant XML to a target service.
 * 
 * <p>This harvest:
 * <ul><li>harvests from an OAI-PMH data provider</li>
 * <li>only makes use of ListRecord requests</li>
 * <li>strips PMH markup</li>
 * <li>strips namespaces from the metadata payload</li>
 * <li>stores the result of the requests as a fragment</li>
 * <li>posts each fragment to a target service</li>
 * <li>on successful completion deletes all fragments stored during the harvest</li>
 * </ul></p>
 * 
 * @author Scott Yeadon, ANU 
 */
public class RIFHarvestThread extends HarvestThread
{
    private final Logger log = Logger.getLogger(RIFHarvestThread.class);
    
    private ThreadManager threadManager = null;
    
    // following fields only used when debugging
    private int numRecordsRcvd = 0;
    private int numRecordsSent = 0;
    private String errorMsg = "";
    
    /**
     * Constructor obtains a reference to the Thread Manager
     */
    public RIFHarvestThread()
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

            // Commented out due to short expiry on resumption token
            // on ORCA application. If a previous resumption token is used
            // chances are high it has expired so currently this harvest
            // does not support resuming a harvest, it will start from 
            // scratch.
            // Uncomment the following code to use stored resumption
            // token. More targetted error handling may also be 
            // necessary if this is done (e.g. what to do if a badResumptionToken
            // error is received).
            //setResumptionToken(harvest.getResumptionToken());
            
            
            if (getResumptionToken() == null)
            {
                Identify identify = new Identify(harvest.getSourceURL()); 

                Document identifyDoc = identify.getDocument();
                
                if (identifyDoc == null)
                {
                	errorMsg += "Response from Identify is null\n";
                	postError(errorMsg, harvest, "application/x-www-form-urlencoded", harvest.getHarvestID());
                	throw new IOException("Response from Identify is null");                    
                }

                NodeList nl = identifyDoc.getElementsByTagName("granularity");
                if (nl.getLength() == 1)
                {
                    setGranularity(nl.item(0).getTextContent());
                }
                
                if (harvest.getFrom() == null)
                {
                    NodeList nl2 = identifyDoc.getElementsByTagName("earliestDatestamp");
                    if (nl2.getLength() == 1)
                    {
                        setFrom(nl2.item(0).getTextContent());
                    }
                }
                else
                {
                    setFrom(harvest.getFrom());
                }
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
                log.info("first ListRecords call for " + harvest.getHarvestID());
                log.debug("source="+harvest.getSourceURL() + " from=" + getFrom() + " until=" + getUntil() + " set=" + getSet() + " mp=" + getMetadataPrefix());       

                //if the harvest is not incremental, remove datefrom and dateuntil from list records
                //log.info("HARVEST AHM: "+harvest.getAHM());
                if(!harvest.getAHM().equals("INCREMENTAL")){
                    setFrom(null);
                    setUntil(null);
                }

                log.info("source="+harvest.getSourceURL() + " from=" + getFrom() + " until=" + getUntil() + " set=" + getSet() + " mp=" + getMetadataPrefix());
                listRecords = 
                    new ListRecords(harvest.getSourceURL(), getFrom(), getUntil(), getSet(), getMetadataPrefix());
            }
            else
            {
                log.debug("source="+harvest.getSourceURL() + " from=" + getFrom() + " until=" + getUntil() + " set=" + getSet() + " mp=" + getMetadataPrefix());                
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
                    errorMsg += "Errors in ListRecords response\n";
                    int length = errors.getLength();
                    for (int i=0; i<length; ++i)
                    {
                        Element item = (Element)errors.item(i);
                        log.error(item.getTagName() + ":" + 
                                  item.getAttribute("code") + ":" + 
                                  item.getTextContent());
                        errorMsg += item.getTagName() + ":" + 
                        item.getAttribute("code") + ":" + 
                        item.getTextContent();
                        
                        if (item.getAttribute("code").equals(NO_RECORDS))
                        {
                            if (item.getTextContent().contains("server error"))
                            {
                                error = true;
                                break;
                            }
                            else
                            {
                                error = false;
                            }
                        }
                        else
                        {
                            error = true;
                            break;
                        }
                    }
                    
                    if (error)
                    {
                    	postError(errorMsg, harvest, "application/x-www-form-urlencoded", harvest.getHarvestID());
                    	break;
                    }
                }
                
                Document doc = listRecords.getDocument();
                Fragment frag = getFragment(docToString(doc), "ListRecords");

                if ((listRecords.getResumptionToken().length()==0)  || (harvest.getMode().equals(Constants.MODE_TEST))) 
                {
                    last = true;
                }

                postFragment(frag, harvest, "application/x-www-form-urlencoded", last);
                
                if (harvest.getMode().equals(Constants.MODE_TEST))
                {
                    threadManager.setThreadComplete(harvest);
                    return;
                }
                log.info(harvest.getHarvestID() + " OLD resumption token = " + getResumptionToken());
                log.info(harvest.getHarvestID() + " resumption token = " + listRecords.getResumptionToken());
                if (listRecords.getResumptionToken().length() > 0)
                {
                	log.info("setting resumption token");
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
                log.info("harvester is updated");
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
                //end of harvest - set next from/until dates
                //uncomment the following lines for incremental harvesting
                harvest.setFrom(getNextFromDate());
                harvest.setUntil(null);
                threadManager.setThreadComplete(harvest);
                log.info("harvest id completed: " + harvest.getHarvestID());
                log.debug(harvest.getHarvestID() + " total records received:" + numRecordsRcvd);
                log.debug(harvest.getHarvestID() + " total records sent:" + numRecordsSent);
            }
        }
        catch (IOException ioe)
        {
            log.error("IOException", ioe);
            try
            {
            	log.info("IOException: so trying no notify ORCA");
            	postError(ioe.toString(), harvest, "application/x-www-form-urlencoded", harvest.getHarvestID());
            	threadManager.setThreadError(harvest);
            }
            catch (DAOException daoe)
            {
                log.error("DAOException", daoe);
            }
            catch (IOException ioe2)
            {
                log.error("IOException", ioe2);
            }
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
                log.error("DAOException while attempting to find harvest", daoe2);                
            }
        }
        catch (Exception e)
        {
            log.error("An Exception occurred", e);
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
    
    
    /**
     * set default metadataPrefix 
     */
    protected void setPMHArguments()
    {
        harvest.setMetadataPrefix("rif");
        super.setPMHArguments();
    }
    
    
    /**
     * obtain an XML Document in String form
     * 
     * @param n
     *     the starting node to be string-ified
     *      
     * @return String
     *             the XML Document in String form
     */
    private String docToString(Document doc) throws Exception
    {
        DOMImplementation impl = doc.getImplementation();
        DOMImplementationLS implLS = (DOMImplementationLS) impl.getFeature("LS","3.0"); 

        LSSerializer writer = implLS.createLSSerializer();
        // This is to suppress the xml header, with version and the encoding being automatically generated
        writer.getDomConfig().setParameter("xml-declaration", false);
        return writer.writeToString(doc);
    }
}