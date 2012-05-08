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
package au.edu.apsr.harvester.test;

import java.io.IOException;
import java.io.OutputStreamWriter;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLEncoder;
import java.util.Calendar;
import java.util.Date;
import java.util.TimeZone;

import au.edu.apsr.harvester.util.ServletSupport;

/**
 * <p>Class to test concurrent harvests.</p>
 * 
 * <p>VERY rough script to test a number of concurrently scheduled
 * harvests. Harvest ids will be harvestX where X is a number from
 * 0 to &lt;num harvests&gt;.</p>

 * <p>Usage: java au.edu.apsr.harvester.test.ConcurrentHarvest &lt;num harvests&gt; &lt;webapp url&gt; &lt;source url&gt; &lt;target url&gt; &lt;method&gt;
 * <p>Where:</p>
 * <p>&lt;num harvests&gt; is the number of concurrent harvests to run</p>  
 * <p>&lt;webapp url&gt; is the url to the harvester webapp</p>  
 * <p>&lt;source url&gt; is the baseurl of an OAI-PMH Data Provider (or just an XML target if using the GET method)</p>
 * <p>&lt;target url&gt; is the url the OAI responses will be POST-ed to</p>  
 * <p>&lt;method&gt; is the harvest method to use (e.g. PMH, GET, etc)</p>
 * </p>
 * <p>For example: java au.edu.apsr.harvester.test.ConcurrentHarvest 30 http://mytomcat.edu:8080/harvester http://an-oai-server.org/oai-provider http://my-server.edu/target-app PMH</p>
 * <p>Harvests are scheduled hourly in order to keep the harvest records in the database for examination. Manual cleanup of the database tables will be required after running this script.</p>  
 */

public class ConcurrentHarvest
{
    public static void main(String[] argv) throws Exception
    {
        if (argv.length != 5)
        {
            System.out.println("Usage: java au.edu.apsr.harvester.test.ConcurrentHarvest <num concurrent harvests> <webapp url> <source url> <target url> <method>");
            System.out.println("Where:");
            System.out.println("<num harvests> is the number of concurrent harvests to run");
            System.out.println("<webapp url> is the url to the harvester webapp");
            System.out.println("<source url> is the baseurl of an OAI-PMH Data Provider (or just an XML target if using the GET method)");
            System.out.println("<target url> is the url the OAI responses will be POST-ed to");
            System.out.println("<method> is the harvest method to use (e.g. PMH, GET, etc)");
            System.out.println();
            System.out.println("For example: java au.edu.apsr.harvester.test.ConcurrentHarvest 30 http://mytomcat.edu:8080/harvester http://an-oai-server.org/oai-provider http://my-server.edu/target-app PMH");
            System.out.println("Harvests are scheduled hourly in order to keep the harvest records in the database for examination. Manual cleanup of the database tables will be required after running this script.");
            System.exit(0);
        }
        
        int numHarvests = Integer.valueOf(argv[0]);
        
        Calendar nextRunCal = Calendar.getInstance(TimeZone.getTimeZone("UTC"));
        nextRunCal.setTime(new Date());
        nextRunCal.add(Calendar.SECOND, 5);
        
        for (int i=0; i<numHarvests; i++)
        {
            doPost(i, argv[1], argv[2], argv[3], argv[4], nextRunCal);
        }
    }

    
    private static void doPost(int i,
                               String harvesterUrl,
                               String sourceUrl,
                               String targetUrl,
                               String method,
                               Calendar cal) throws Exception
    {
        String params = "harvestid=" + URLEncoder.encode("harvest" + i, "UTF-8");
        params += "&sourceurl=" + URLEncoder.encode(sourceUrl, "UTF-8");
        params += "&responsetargeturl=" + URLEncoder.encode(targetUrl, "UTF-8");
        params += "&mode=" + URLEncoder.encode("harvest", "UTF-8");
        params += "&method=" + URLEncoder.encode(method, "UTF-8");
        params += "&frequency=" + URLEncoder.encode("hourly", "UTF-8");        
        params += "&date=" + URLEncoder.encode(ServletSupport.getUTCString(cal.getTime()), "UTF-8");            
        int responseCode = 0;
        HttpURLConnection conn = null;
        URL url = new URL(harvesterUrl + "/requestHarvest");
        conn = (HttpURLConnection)url.openConnection();
        conn.setConnectTimeout(10000);
        conn.setRequestMethod("POST");
        conn.setAllowUserInteraction(false);
        conn.setDoOutput(true);
        conn.setRequestProperty("User-Agent", "APSRHarvester/1.0");
        conn.setRequestProperty( "Content-type", "application/x-www-form-urlencoded");
        conn.setRequestProperty( "Content-length", Integer.toString(params.length()));
        OutputStreamWriter out = new OutputStreamWriter(conn.getOutputStream());
        out.write(params);
        out.flush();
        out.close();
        responseCode = conn.getResponseCode();
        if (responseCode < 200 || responseCode > 299)
        {
            conn.disconnect();
            throw new IOException("A problem was encountered. Response code: " + responseCode);
        }
        
        conn.disconnect();
    }
}