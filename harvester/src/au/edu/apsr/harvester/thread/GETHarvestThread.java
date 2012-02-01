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
import java.io.InputStream;
import java.net.URL;

import org.apache.log4j.Logger;

import au.edu.apsr.harvester.dao.DAOException;
import au.edu.apsr.harvester.thread.GETHarvestThread;
import au.edu.apsr.harvester.thread.HarvestThread;
import au.edu.apsr.harvester.thread.ThreadManager;
import au.edu.apsr.harvester.to.Fragment;

/**
 * Harvests content from a URL by simply reading the URL stream. It's a
 * one-pass harvest and has no relation to OAI-PMH, it simply allows a
 * consumer to use the harvest services to manage the reading of URL
 * content within the harvesting framework. Note the contents of the URL
 * are (attempted to be) read into a DOM document so is only useful for small
 * XML documents.
 * 
 * @author Scott Yeadon, ANU 
 */
public class GETHarvestThread extends HarvestThread
{
    private final Logger log = Logger.getLogger(GETHarvestThread.class);
    
    private ThreadManager threadManager = null;

    /**
     * Constructor obtains a reference to the Thread Manager
     */
    public GETHarvestThread()
    {
        super();
        threadManager = ThreadManager.getThreadManager();
    }
    
    
    /**
     * execute the harvest
     * 
     * This harvest reads from a URL, stores the content as a single
     * fragment and posts to a target service. Once the harvest is
     * complete the fragment and harvest record is deleted.
     */
    public void run()
    {
        try
        {
            if (threadManager.isStopped(harvest) || threadManager.isRunning(harvest))
            {
                return;
            }
            
            threadManager.setThreadRunning(harvest);
            
            Fragment frag = getFragment(new URL(harvest.getSourceURL()));

            if (threadManager.isStopped(harvest))
            {
                log.info("Harvest stopped after getting fragment");
                return;
            }
            
            postFragment(frag, harvest, "application/x-www-form-urlencoded", true);
            threadManager.setThreadComplete(harvest);
            harvest = null;
        }
        catch (IOException ioe)
        {
            log.error("IOException", ioe);            
            try
            {
                threadManager.setThreadError(harvest);
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
        }
    }
    
    
    /**
     * reads URL content and creates a fragment from the stream
     * 
     * @param fragmentURL
     *          the URL to read from
     *          
     * @return Fragment
     *          the newly created fragment object
     */
    protected Fragment getFragment(URL fragmentURL) throws IOException, DAOException
    {
        InputStream is = getInputStream(fragmentURL);
        String data = getFragment(is);
        Fragment frag = new Fragment();
        frag.setHarvestID(harvest.getHarvestID());
        frag.setText(data);
        // set as a ListRecords request
        frag.setRequestID(4);
        frag.create();
        return frag;
    }
}