/**
 * Date Modified: $Date: 2009-08-18 12:13:42 +1000 (Tue, 18 Aug 2009) $
 * Version: $Revision: 81 $
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
import java.util.HashMap;
import java.util.Iterator;
import java.util.Map;

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
import au.edu.apsr.harvester.oai.Identify;
import au.edu.apsr.harvester.oai.ListMetadataFormats;
import au.edu.apsr.harvester.oai.ListRecords;
import au.edu.apsr.harvester.to.Fragment;
import au.edu.apsr.harvester.to.Harvest;
import au.edu.apsr.harvester.util.Constants;

/**
 * This harvest takes iso19139 MCP metadata from a MEST instance OAI-PMH feed, 
 * transforms it to RIF-CS and collates party information.
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
public class GeoPMHRIFHarvestThread extends HarvestThread
{
    private final Logger log = Logger.getLogger(GeoPMHRIFHarvestThread.class);
    
    //private final static String RIF_PREFIX = "rif";
    
    private ThreadManager threadManager = null;
    private TransformerFactory tf;
    private String stylesheetDir;
    private Document collationDoc;
    
    // following fields only used when debugging
    private int numRecordsRcvd = 0;
    private int numRecordsSent = 0;
    
    /**
     * Constructor obtains a reference to the Thread Manager
     */
    public GeoPMHRIFHarvestThread()
    {
        threadManager = ThreadManager.getThreadManager();
        
        stylesheetDir = System.getProperty("catalina.home") + 
        File.separator + "webapps" + File.separator + 
        "harvester" + File.separator + "WEB-INF" +
        File.separator + "stylesheet";
        
        tf = new net.sf.saxon.TransformerFactoryImpl();
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

            // set the transform. Check the metadataPrefix against those
            // returned by ListMetadataFormats and then locate the stylesheet
            // of form <format>2rifcs.xsl. This may need to change in future if
            // formats are not named the same by multiple data sources.
            ListMetadataFormats lmf = new ListMetadataFormats(harvest.getSourceURL());
            String[] supportedFormats = lmf.getSupportedFormats();
            if (supportedFormats.length == 0)
            {
                throw new Exception("No metadata formats in ListMetadataFormats response from source: " + harvest.getSourceURL());
            }

            boolean supported = checkSupported(supportedFormats);
            String stylesheet = null;
            
            // Somewhat unnecessary but for ORCA we always force a transform
            // in order to remove harvest markup and namespaces.
            if (supported) // || harvest.getMetadataPrefix().equals(RIF_PREFIX))
            {
                stylesheet = getStylesheet(harvest.getMetadataPrefix());

                if (stylesheet == null)
                {
                    throw new Exception("No stylesheet found for format " + harvest.getMetadataPrefix());
                }
            }
            else
            {
                throw new Exception("The format " + harvest.getMetadataPrefix() + " is not supported by the Data Provider at " + harvest.getSourceURL());
            }
            
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
                
                listRecords = 
                    new ListRecords(harvest.getSourceURL(), getFrom(), getUntil(), getSet(), getMetadataPrefix());
            }
            else
            {
                listRecords = 
                    new ListRecords(harvest.getSourceURL(), getResumptionToken());
            }
 
            boolean error = false;
            boolean last = false;
            
            // set up the transform
            Transformer transformer = tf.newTransformer(new StreamSource(stylesheet));
            
            Map<String,String> parms = harvest.getParameters();
            if (parms != null)
            {
                Iterator<Map.Entry<String, String>> it = parms.entrySet().iterator();
                while (it.hasNext())
                {
                    Map.Entry<String, String> pair = it.next();
                    transformer.setParameter(pair.getKey(), pair.getValue());
                }
            }
            
            // This is quite dodgy, but will do for now
            if (harvest.getParameter("origSource") == null)
            {
                transformer.setParameter("origSource", harvest.getSourceURL() + "?verb=ListRecords&metadataPrefix=" + harvest.getMetadataPrefix());
            }
            
            HashMap<String, Node> parties = new HashMap<String, Node>();          
            // temporary holding area for party nodes to be collated
            collationDoc = startDoc();

            String sessionID = listRecords.getSessionID();

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
                        break;
                    }
                }
                
                Document doc = listRecords.getDocument();
                Document records;

                DOMSource domSource = new DOMSource(doc);
                DOMResult domResult = new DOMResult();
                
                transformer.transform(domSource, domResult);

                // need to collate the parties
                Document rifcs = startDoc();

                Element root = rifcs.createElement("registryObjects");

                root.setAttributeNS("http://www.w3.org/2001/XMLSchema-instance","xsi:schemaLocation", "http://ands.org.au/standards/rif-cs/registryObjects http://services.ands.org.au/home/orca/schemata/registryObjects.xsd");
                root.setAttribute("xmlns", "http://ands.org.au/standards/rif-cs/registryObjects");
                
                rifcs.appendChild(root);

                NodeList ros = ((Document)domResult.getNode()).getElementsByTagName("registryObject");

                // This section currently specific to MEST MCP processing, may
                // need to be generalised/configurable in future.
                for (int i=0; i<ros.getLength(); i++)
                {
/*                    if (((Element)ros.item(i)).getLastChild().getNodeName().equals("collection"))
                    {
                        Node newNode = rifcs.importNode(ros.item(i), true);
                        root.appendChild(newNode);
                    }
                    else if (((Element)ros.item(i)).getLastChild().getNodeName().equals("party"))
                    {
                        Node existingParty = parties.get(((Element)ros.item(i)).getFirstChild().getTextContent());                       
                        if (existingParty != null)
                        {
                            // get the existing related objects (needed for inserting new ones)
                            NodeList epRelos = ((Element)existingParty).getElementsByTagName("relatedObject");
                            
                            // if existing party just want the related objects
                            NodeList relos = ((Element)ros.item(i)).getElementsByTagName("relatedObject");
                            for (int j=0; j<relos.getLength(); j++)
                            {
                                Node relo = collationDoc.importNode(relos.item(j), true);
                                // can insert before since there should always be an existing relatedObject element
                                epRelos.item(0).getParentNode().insertBefore(relo, epRelos.item(0));
                            }
                        }
                        else
                        {
                            // if new party add to hashmap
                            //parties.put(((Element)ros.item(i)).getFirstChild().getTextContent(), ros.item(i));
                            parties.put(((Element)ros.item(i)).getFirstChild().getTextContent(), collationDoc.importNode(ros.item(i), true));
                        }
                    } */
                    
                    if (((Element)ros.item(i)).getLastChild().getNodeName().equals("party"))
                    {
                        Node existingParty = parties.get(((Element)ros.item(i)).getFirstChild().getTextContent());                       
                        if (existingParty != null)
                        {
                            // get the existing related objects (needed for inserting new ones)
                            NodeList epRelos = ((Element)existingParty).getElementsByTagName("relatedObject");
                            
                            // if existing party just want the related objects
                            NodeList relos = ((Element)ros.item(i)).getElementsByTagName("relatedObject");
                            for (int j=0; j<relos.getLength(); j++)
                            {
                                Node relo = collationDoc.importNode(relos.item(j), true);
                                // can insert before since there should always be an existing relatedObject element
                                epRelos.item(0).getParentNode().insertBefore(relo, epRelos.item(0));
                            }
                        }
                        else
                        {
                            // if new party add to hashmap
                            //parties.put(((Element)ros.item(i)).getFirstChild().getTextContent(), ros.item(i));
                            parties.put(((Element)ros.item(i)).getFirstChild().getTextContent(), collationDoc.importNode(ros.item(i), true));
                        }
                    }
                    else
                    {
                        Node newNode = rifcs.importNode(ros.item(i), true);
                        root.appendChild(newNode);
                    }
                }
    
                records = rifcs;

                if ((listRecords.getResumptionToken().length()==0) || (harvest.getMode().equals(Constants.MODE_TEST))) 
                {
                    last = true;
                    for (Iterator<Node> pi = parties.values().iterator(); pi.hasNext();)
                    {
                        Node newNode = rifcs.importNode(pi.next(), true);
                        root.appendChild(newNode);
                    }
                }

                Fragment frag = getFragment(docToString(records), "ListRecords");
                postFragment(frag, harvest, "application/x-www-form-urlencoded", last);
                
                if (harvest.getMode().equals(Constants.MODE_TEST))
                {
                    threadManager.setThreadComplete(harvest);
                    return;
                }
                
                log.info(harvest.getHarvestID() + " resumption token = " + listRecords.getResumptionToken());
                if (listRecords.getResumptionToken().length() > 0)
                {
                    setResumptionToken(listRecords.getResumptionToken());
                }
                else
                {
                    setResumptionToken(null);
                }
                log.debug("resumption=" + getResumptionToken());
                                
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
                    if (sessionID != null)
                    {
                        listRecords = new ListRecords(harvest.getSourceURL(), sessionID, getResumptionToken());
                    }
                    else
                    {
                        log.info("no sessionID");
                        listRecords = new ListRecords(harvest.getSourceURL(), getResumptionToken());
                    }
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
                //harvest.setFrom(getNextFromDate());
                //harvest.setUntil(null);
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
                threadManager.setThreadError(harvest);
            }
            catch (DAOException daoe)
            {
                log.error("DAOException", daoe);
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

    
    /**
     * Get the stylesheet for the transform
     * 
     * @param metadataPrefix
     *     the metadataPrefix used as the source of the transform
     *      
     * @return String
     *             the path of the stylesheet
     */
    private String getStylesheet(String metadataPrefix)
    {
        String stylesheet = stylesheetDir + File.separator +
        metadataPrefix + "2rifcs.xsl";
    
        File f = new File(stylesheet);
        if (!f.exists())
        {
            stylesheet = null;
        }
        else
        {
            log.info("Using stylesheet: " + stylesheet);
        }
        
        return stylesheet;
    }


    /**
     * Check the metadataPrefix is supported by the listMetadataFormats
     * response
     * 
     * @param supportedFormats
     *     an array of metadataPrefix strings returned from the
     *     listMetadataFormats response
     *      
     * @return boolean
     *             whether the harvest's metadataPrefix is in the list
     *             of supported formats
     */
    private boolean checkSupported(String[] supportedFormats)
    {
        for (int i = 0; i < supportedFormats.length; i++)
        {
            if (supportedFormats[i].equals(harvest.getMetadataPrefix()))
            {
                return true;
            }
        }
        return false;
    }
    
    // need to do this since couple of the MESTS won't return records
    // when until has a value
    public String getUntil()
    {
        return null;
    }

    public String getFrom()
    {
        return null;
    }


    // start a new document
    private Document startDoc() throws Exception
    {
        // create a DocumentBuilderFactory
        DocumentBuilderFactory factory = DocumentBuilderFactory.newInstance();
            
        // create a DocumentBuilder (DOM Parser)
        DocumentBuilder builder = factory.newDocumentBuilder();
                    
        // create an EMPTY XML document for the output
        return builder.newDocument();
    }
}