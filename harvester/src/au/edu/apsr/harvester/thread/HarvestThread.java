/**
 * Date Modified: $Date: 2010-03-11 14:20:24 +1100 (Thu, 11 Mar 2010) $
 * Version: $Revision: 335 $
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

import org.apache.log4j.Logger;

import java.io.BufferedReader;
import java.io.InputStream;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.OutputStreamWriter;
import java.io.UnsupportedEncodingException;
import java.net.HttpURLConnection;
import java.net.MalformedURLException;
import java.net.URL;
import java.net.URLEncoder;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Date;
import java.util.TimeZone;
import java.util.TimerTask;
import java.util.zip.GZIPInputStream;
import java.util.zip.InflaterInputStream;
import java.util.zip.ZipInputStream;

import au.edu.apsr.harvester.dao.DAOException;
import au.edu.apsr.harvester.thread.HarvestThread;
import au.edu.apsr.harvester.to.Fragment;
import au.edu.apsr.harvester.to.Harvest;
import au.edu.apsr.harvester.to.Request;
import au.edu.apsr.harvester.util.ServletSupport;

/**
 * An abstract class all harvest classes must extend.
 * 
 * @author Scott Yeadon, ANU
 */
public abstract class HarvestThread extends TimerTask
{
    private final Logger log = Logger.getLogger(HarvestThread.class);
    protected static final String NO_RECORDS = "noRecordsMatch";
    
    protected Harvest harvest;

    private String metadataPrefix = null;
    private String from = null;
    private String until = null;
    private String resumptionToken = null;
    private String granularity = null;
    private String set = null;
    
    /**
     * set the harvest instance to run
     * 
     * @param harvest
     *          the harvest the thread will be responsible for
     */
    public void setHarvest(Harvest harvest)
    {
        this.harvest = harvest;
    }
    
    
    /**
     * send a harvested fragment to some target service. 
     * 
     * @param fragment
     *          the fragment to post
     * @param harvest
     *          the harvest being executed
     * @param mimetype
     *          the mimetype of the content being posted
     * @param lastFragment
     *          a boolean indicating whether this is the last fragment
     *          of the harvest
     *          
     * @exception UnsupportedEncodingException
     * @exception MalformedURLException
     * @exception IOException
     * @exception DAOException
     */
    protected void postFragment(Fragment fragment,
                             Harvest harvest,
                             String mimetype,
                             boolean lastFragment) throws UnsupportedEncodingException, MalformedURLException, IOException, DAOException
    {
        String params = "harvestid=" + URLEncoder.encode(fragment.getHarvestID(), "UTF-8");
        params += "&done=" + lastFragment;
        params += "&sourceurl=" + URLEncoder.encode(harvest.getSourceURL(), "UTF-8");
        params += "&responsetargeturl=" + URLEncoder.encode(harvest.getResponseTargetURL(), "UTF-8");
        params += "&mode=" + URLEncoder.encode(harvest.getMode(), "UTF-8");
        params += "&method=" + URLEncoder.encode(harvest.getMethod(), "UTF-8");
        if (harvest.getFrequency() != null)
        {
            params += "&date=" + URLEncoder.encode(ServletSupport.getUTCString(harvest.getNextRun()), "UTF-8");            
        }
        params += "&content="+ URLEncoder.encode(fragment.getText(), "UTF-8");
        int responseCode = 0;
        HttpURLConnection conn = null;
        URL url = new URL(harvest.getResponseTargetURL());
        conn = (HttpURLConnection)url.openConnection();
        conn.setConnectTimeout(10000);
        conn.setRequestMethod("POST");
        conn.setAllowUserInteraction(false);
        conn.setDoOutput(true);
        conn.setRequestProperty("User-Agent", "APSRHarvester/1.0");
        conn.setRequestProperty( "Content-type", mimetype+";charset=UTF-8");
        conn.setRequestProperty( "Content-length", Integer.toString(params.length()));
        OutputStreamWriter out = new OutputStreamWriter(conn.getOutputStream());
        out.write(params);
        out.flush();
        out.close();
        responseCode = conn.getResponseCode();
        log.debug(harvest.getHarvestID() + " post fragment code: " + responseCode);
        if (responseCode < 200 || responseCode > 299)
        {
            conn.disconnect();
            log.error("Unexpected response code from" + harvest.getResponseTargetURL() + ": " + responseCode);
            throw new IOException("Unexpected response code from" + harvest.getResponseTargetURL() + ": " + responseCode);
        }
        
        conn.disconnect();
    }

    protected void postError(String errMsg,
            Harvest harvest,
            String mimetype,
            String harvestId) throws UnsupportedEncodingException, MalformedURLException, IOException, DAOException
		{
		String params = "harvestid=" + URLEncoder.encode(harvestId, "UTF-8");
		params += "&sourceurl=" + URLEncoder.encode(harvest.getSourceURL(), "UTF-8");
		params += "&responsetargeturl=" + URLEncoder.encode(harvest.getResponseTargetURL(), "UTF-8");
		params += "&mode=" + URLEncoder.encode(harvest.getMode(), "UTF-8");
		params += "&method=" + URLEncoder.encode(harvest.getMethod(), "UTF-8");
		params += "&errmsg=" + URLEncoder.encode(errMsg, "UTF-8");
		
		if (harvest.getFrequency() != null)
		{
		params += "&date=" + URLEncoder.encode(ServletSupport.getUTCString(harvest.getNextRun()), "UTF-8");            
		}
		int responseCode = 0;
		HttpURLConnection conn = null;
		URL url = new URL(harvest.getResponseTargetURL());
		conn = (HttpURLConnection)url.openConnection();
		conn.setConnectTimeout(10000);
		conn.setRequestMethod("POST");
		conn.setAllowUserInteraction(false);
		conn.setDoOutput(true);
		conn.setRequestProperty("User-Agent", "APSRHarvester/1.0");
		conn.setRequestProperty( "Content-type", mimetype+";charset=UTF-8");
		conn.setRequestProperty( "Content-length", Integer.toString(params.length()));
		OutputStreamWriter out = new OutputStreamWriter(conn.getOutputStream());
		out.write(params);
		out.flush();
		out.close();
		responseCode = conn.getResponseCode();
		log.debug(harvest.getHarvestID() + " post fragment code: " + responseCode);
		if (responseCode < 200 || responseCode > 299)
		{
		conn.disconnect();
		log.error("Unexpected response code from" + harvest.getResponseTargetURL() + ": " + responseCode);
		throw new IOException("Unexpected response code from" + harvest.getResponseTargetURL() + ": " + responseCode);
		}

		conn.disconnect();
}

    /**
     * obtain an input stream from a URL. This code is pretty much a 
     * copy of OCLC's harvester2 HarvesterVerb harvest method.
     * 
     * @param url
     *          the URL to open
     * 
     * @return InputStream
     *          a URL input stream
     *          
     * @exception IOException
     */
    protected InputStream getInputStream(URL url) throws IOException
    {
        HttpURLConnection conn;
        InputStream in = null;
        int responseCode = 0;

        do
        {
            conn = (HttpURLConnection)url.openConnection();
            conn.setConnectTimeout(10000);
            conn.setRequestProperty("User-Agent", "APSRHarvester/1.0");
            conn.setRequestProperty("Accept-Encoding", "compress, gzip, identify");
            try
            {
                responseCode = conn.getResponseCode();
                if (responseCode < 200 || responseCode > 299)
                {
                    conn.disconnect();
                    log.error("HTTP code " + responseCode + " connecting to url: " + url);
                    throw new IOException("HTTP code " + responseCode + " connecting to url: " + url);
                }
            }
            catch (IOException ioe)
            {
                if (responseCode != HttpURLConnection.HTTP_UNAVAILABLE)
                {
                    conn.disconnect();
                    throw ioe;
                }
            }
            
            if (responseCode == HttpURLConnection.HTTP_UNAVAILABLE)
            {
                long retrySeconds = conn.getHeaderFieldInt("Retry-After", -1);
                log.error("retry seconds: " + retrySeconds);
                if (retrySeconds == -1)
                {
                    log.error("could not connect to: " + url.toExternalForm() + " , retrying");
                    long now = (new Date()).getTime();
                    long retryDate = conn.getHeaderFieldDate("Retry-After", now);
                    retrySeconds = retryDate - now;
                }
                
                if (retrySeconds == 0)
                {
                    conn.disconnect();
                    log.error("Too many retries connecting to: " + url.toExternalForm());
                    throw new IOException("Too many retries connecting to: " + url);
                }

                if (retrySeconds > 0)
                {
                    try
                    {
                        Thread.sleep(retrySeconds * 1000);
                    }
                    catch (InterruptedException ex)
                    {
                        conn.disconnect();
                        log.error(ex);
                    }
                }
            }
        } while (responseCode == HttpURLConnection.HTTP_UNAVAILABLE);
        
        String contentEncoding = conn.getHeaderField("Content-Encoding");

        if ("compress".equals(contentEncoding))
        {
            ZipInputStream zis = new ZipInputStream(conn.getInputStream());
            zis.getNextEntry();
            in = zis;
        }
        else if ("gzip".equals(contentEncoding))
        {
            in = new GZIPInputStream(conn.getInputStream());
        }
        else if ("deflate".equals(contentEncoding))
        {
            in = new InflaterInputStream(conn.getInputStream());
        }
        else
        {
            in = conn.getInputStream();
        }
        
        return in;
    }
    
    /**
     * obtain a fragment from an input stream
     * 
     * @param is
     *          the input stream to read from
     * 
     * @return String
     *          a string representation of the input stream content
     *          
     * @exception IOException
     */
    public String getFragment(InputStream is) throws IOException
    {
        BufferedReader data = new BufferedReader(new InputStreamReader(is));        
        StringBuffer buf = new StringBuffer();
        
        int aChar = -1;
        
        while ((aChar = data.read()) != -1)
        {
            buf.append((char)aChar);
        }

        data.close();

        return buf.toString();
    }
    
    
    /**
     * get the next from date
     * 
     * @return String
     *             the new from date
     *             
     * @throws ParseException
     */
    public String getNextFromDate() throws ParseException
    {
        String newFrom = until;
        
        if (until != null)
        {
            SimpleDateFormat df;
            if (until.length() == 20)
            {
                df = new SimpleDateFormat("yyyy-MM-dd'T'HH:mm:ss'Z'");
                Calendar cal = Calendar.getInstance(TimeZone.getTimeZone("UTC"));
                df.setCalendar(cal);
                Date d = df.parse(until);
                cal.setTime(d);
                // Maybe best not to do this in case records are missed if added
                // during the second after the data provider executed its query?
                cal.add(Calendar.SECOND, 1);
                newFrom = df.format(cal.getTime());
            }
            else if (until.length() == 10)
            {
                df = new SimpleDateFormat("yyyy-MM-dd");
                Calendar cal = Calendar.getInstance(TimeZone.getTimeZone("UTC"));
                df.setCalendar(cal);
                Date d = df.parse(until);
                cal.setTime(d);
                newFrom = df.format(cal.getTime());
            }
        }
        return newFrom;
    }
    
    
    /**
     * create a fragment record from harvested data
     * 
     * @param data
     *     the xml data in string form
     * @param verb
     *      an OAI-PMH verb
     *      
     * @return Fragment
     *             the newly created fragment
     */
    protected Fragment getFragment(String data,
                                   String verb) throws DAOException
    {
        Fragment frag = new Fragment();
        frag.setHarvestID(harvest.getHarvestID());
        Request request = Request.find(verb);
        frag.setRequestID(request.getID());
        frag.setText(data);
        frag.create();
        return frag;
    }

    
    /**
     * set from, until, set and metadataPrefix and resumption token
     */
    protected void setPMHArguments()
    {
        setDefaultArgs();
    }
    
    
    /**
     * set default metadataPrefix, from and until PMH values 
     */
    private void setDefaultArgs()
    {
        if (resumptionToken == null)
        {
            resumptionToken = harvest.getResumptionToken();
        }
        
        if (metadataPrefix == null)
        {
            metadataPrefix = harvest.getMetadataPrefix();
            if (metadataPrefix==null)
            {
                metadataPrefix = "oai_dc";
                harvest.setMetadataPrefix("oai_dc");
            }
        }
        
        if (from == null)
        {
            from = harvest.getFrom();
            if (from == null)
            {
                if (granularity == null || granularity.length() < 20)
                {
                 // all repositories must support at least YYYY-MM-DD
                    from = "0001-01-01";
                }
                else
                {
                    from = "0001-01-01T00:00:00Z";
                }
            }
            else
            {
                from = harvest.getFrom();
            }
        }
        
        if (until == null)
        {
            String untilString = harvest.getUntil();
            if (untilString == null)
            {
                SimpleDateFormat df;
                if (granularity == null || granularity.length() < 20)
                {
                    df = new SimpleDateFormat("yyyy-MM-dd");
                }
                else
                {
                    df = new SimpleDateFormat("yyyy-MM-dd'T'HH:mm:ss'Z'");
                }
                df.setCalendar(Calendar.getInstance(TimeZone.getTimeZone("UTC")));
                until = df.format(new Date());
            }
        }
    }
    
    
    /**
     * set the metadata prefix
     * 
     * @param metadataPrefix
     *          the metadataPrefix
     */
    protected void setMetadataPrefix(String metadataPrefix)
    {
        this.metadataPrefix = metadataPrefix;
    }
    
    
    /**
     * get the metadata prefix
     * 
     * @return String
     *          the metadataPrefix
     */
    protected String getMetadataPrefix()
    {
        return this.metadataPrefix;
    }
    
    
    /**
     * set the from date
     * 
     * @param from
     *          the from date
     */
    protected void setFrom(String from)
    {
        this.from = from;
    }
    
    
    /**
     * get the from date
     * 
     * @return String
     *          the from date
     */
    protected String getFrom()
    {
        return this.from;
    }
    
    
    /**
     * set the until date
     * 
     * @param until
     *          the until date
     */
    protected void setUntil(String until)
    {
        this.until = until;
    }
    
    
    /**
     * get the until date
     * 
     * @return String
     *          the until date
     */
    protected String getUntil()
    {
        return this.until;
    }
    
    
    /**
     * set the resumption token
     * 
     * @param token
     *              the resumption token
     */
    protected void setResumptionToken(String resumptionToken)
    {
        this.resumptionToken = resumptionToken;
    }
    
    
    /**
     * get the resumption token
     * 
     * @return String
     *              the resumption token
     */
    protected String getResumptionToken()
    {
        return this.resumptionToken;
    }
    
    
    /**
     * set the granularity
     * 
     * @param granularity
     *          the granularity
     */    
    protected void setGranularity(String granularity)
    {
        this.granularity = granularity;
    }
    
    
    /**
     * get the granularity
     * 
     * @return String
     *          the granularity
     */    
    protected String getGranularity()
    {
        return this.granularity;
    }
    
    
    /**
     * set the set spec
     * 
     * @param setSpec
     *          the set spec
     */
    protected void setSet(String set)
    {
        this.set = set;
    }
    
    
    /**
     * get the set spec
     * 
     * @return String
     *          the set spec
     */
    protected String getSet()
    {
        return this.set;
    }
}