/**
 * Date Modified: $Date: 2009-08-18 13:15:13 +1000 (Tue, 18 Aug 2009) $
 * Version: $Revision: 85 $
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
package au.edu.apsr.pids.admin;

import java.io.IOException;
import java.io.StringWriter;
import java.net.ConnectException;
import java.net.HttpURLConnection;
import java.net.MalformedURLException;
import java.net.SocketTimeoutException;
import java.net.URL;
import java.net.URLConnection;
import java.net.UnknownHostException;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Collections;
import java.util.Date;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;
import java.util.TimeZone;
import java.util.Timer;

import javax.net.ssl.HttpsURLConnection;
import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;
import javax.xml.transform.Transformer;
import javax.xml.transform.TransformerFactory;
import javax.xml.transform.dom.DOMSource;
import javax.xml.transform.stream.StreamResult;

import org.apache.log4j.Logger;
import org.w3c.dom.Document;
import org.w3c.dom.Element;

import au.edu.apsr.pids.admin.LinkChecker;
import au.edu.apsr.pids.admin.CheckedHandle;
import au.edu.apsr.pids.util.JDBCSupport;
import au.edu.apsr.pids.util.XMLSupport;

/** 
 * A singleton class for managing all harvests within the web application
 * 
 * @author Scott Yeadon, APSR 
 */
public class LinkCheckerThreadManager
{
    private static final Logger log = Logger.getLogger(LinkCheckerThreadManager.class);

    private static LinkCheckerThreadManager lctm = null;
    
    private static int numThreads = 5;
    
    private static String dbUrl = "jdbc:postgresql://127.0.0.1:5432/pids?user=pidmaster";
    private static final String SELECT_URL_COUNT_SQL = 
        "SELECT count(*) " + 
        "FROM handles " +
        "WHERE type = 'URL'";
    
    private static int numErrorThreads = 0;
    private static int numFinishedThreads = 0;
    private static int numRecords = 0;
    
    List<CheckedHandle> list = Collections.synchronizedList(new ArrayList<CheckedHandle>());

    private  LinkCheckerThreadManager()
    {
    }

    
    /** 
     * obtain a reference to the LinkChevckerThreadManager
     * 
     * @return LinkCheckerThreadManager
     *             the link checker thread manager
     */
    public static synchronized LinkCheckerThreadManager getThreadManager()
    {
        if (lctm == null)
        {
            lctm = new LinkCheckerThreadManager();
        }
        return lctm;
    }

    
    /** 
     * As this is a singleton class, invoking the clone method will
     * result in an exception being thrown
     * 
     * @return Object
     *             the cloned object will not be returned if attempted
     *
     * @throws CloneNotSupportedException
     */
    public Object clone() throws CloneNotSupportedException
    {
        throw new CloneNotSupportedException("Clone operation not supported"); 
    }
    
    
    public void checkURLs()
    {
        Connection conn = null;
        PreparedStatement ps = null;
        ResultSet rs = null;
        
        try
        {
            Class.forName("org.postgresql.Driver");

            conn = getDBConnection();
            if (conn == null)
            {
                return;
            }
            
            ps = conn.prepareStatement(SELECT_URL_COUNT_SQL);
            rs = ps.executeQuery();
            rs.next();
            numRecords = rs.getInt(1);
            JDBCSupport.closeObjects(rs, ps, conn);
            
            startThreads();
        }
        catch (SQLException sqle)
        {
            log.error(sqle);
        }
        catch (ClassNotFoundException cnfe)
        {
            log.error(cnfe);
        }
        catch (Exception e)
        {
            log.error(e);
        }
        finally
        {
        }
    }
    
    
    private void startThreads() throws Exception
    {
        int recordsPerThread = numRecords/numThreads;
        int leftovers = numRecords%numThreads;
        if (leftovers < 0)
        {
            leftovers += recordsPerThread;
        }
        int startRecord = 0;
        
        for (int i=0; i<numThreads; i++)
        {
            if (i > 0 && i < numThreads)
            {
                startRecord += recordsPerThread;
            }
            
            if (i == numThreads - 1)
            {
                recordsPerThread += leftovers;
            }
            
            new Thread(new LinkChecker(recordsPerThread, startRecord)).start();
        }

        while (numFinishedThreads + numErrorThreads < numThreads)
        {
            Thread.sleep(3000);
        }
        doReport();
    }
    
    
    protected void threadFinished()
    {
        numFinishedThreads++;
    }

    
    protected void threadErrored()
    {
        numFinishedThreads++;
    }

    
    private Connection getDBConnection()
    {
        Connection c = null;
        try
        {
            c = DriverManager.getConnection(dbUrl);
        }
        catch (SQLException sqle)
        {
            log.error(sqle);
        }
        
        return c;
    }

    
    protected void addCheckedHandle(CheckedHandle ch)
    {
        this.list.add(ch);
    }
    
    
    private void doReport() throws Exception
    {
        DocumentBuilderFactory factory = DocumentBuilderFactory.newInstance();
        DocumentBuilder builder = factory.newDocumentBuilder();
        Document doc = builder.newDocument();
        
        Element root = doc.createElement(XMLSupport.RESPONSE_ELEMENT);
        root.setAttribute(XMLSupport.RESPONSE_TYPE_ATTRIBUTE, XMLSupport.RESPONSE_TYPE_SUCCESS);
        doc.appendChild(root);
        
        Element timestamp = doc.createElement(XMLSupport.RESPONSE_TIMESTAMP_ELEMENT);
        SimpleDateFormat sdf = new SimpleDateFormat(XMLSupport.TIMESTAMP_UTC_FORMAT);
        Calendar cal = Calendar.getInstance(TimeZone.getTimeZone("UTC"));
        sdf.setCalendar(cal);
        timestamp.setTextContent(sdf.format(cal.getTime()));
        root.appendChild(timestamp);

        Element identifiers = doc.createElement(XMLSupport.RESPONSE_IDENTIFIERS_ELEMENT);
        root.appendChild(identifiers);
        
        synchronized(list)
        {
            for (Iterator<CheckedHandle> i = list.iterator(); i.hasNext();)
            {
                CheckedHandle hr = i.next();
                Element identifier = doc.createElement(XMLSupport.RESPONSE_IDENTIFIER_ELEMENT);
                identifier.setAttribute("handle", hr.getHandle());
                identifier.setAttribute("value", hr.getValue());
                identifier.setAttribute("error", hr.getErrorType());
                identifiers.appendChild(identifier);
            }
        }

        Element propertyPassed = doc.createElement(XMLSupport.RESPONSE_PROPERTY_ELEMENT);
        propertyPassed.setAttribute("name", "totalPassed");
        propertyPassed.setAttribute("value", String.valueOf(numRecords - list.size()));
        root.appendChild(propertyPassed);

        Element propertyFailed = doc.createElement(XMLSupport.RESPONSE_PROPERTY_ELEMENT);
        propertyFailed.setAttribute("name","totalFailed");
        propertyFailed.setAttribute("value", String.valueOf(list.size()));
        root.appendChild(propertyFailed);
        
        DOMSource domSource = new DOMSource(doc);
        
        // Create a string writer
        StringWriter stringWriter = new StringWriter();
        
        // Create the result stream for the transform
        StreamResult result = new StreamResult(stringWriter);
        // Create a Transformer to serialize the document
        TransformerFactory tf = TransformerFactory.newInstance();
        Transformer transformer = tf.newTransformer();
        transformer.setOutputProperty("indent","yes");
        
        // Transform the document to the result stream
        transformer.transform(domSource, result);
        System.out.println(stringWriter.toString());
    }
}