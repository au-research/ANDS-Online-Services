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
import java.net.HttpURLConnection;
import javax.net.ssl.HttpsURLConnection;
import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;
import javax.xml.transform.Transformer;
import javax.xml.transform.TransformerFactory;
import javax.xml.transform.dom.DOMSource;
import javax.xml.transform.stream.StreamResult;

import org.w3c.dom.Document;
import org.w3c.dom.Element;

import java.net.ConnectException;
import java.net.MalformedURLException;
import java.net.SocketTimeoutException;
import java.net.UnknownHostException;
import java.net.URL;
import java.net.URLConnection;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Iterator;
import java.util.TimeZone;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

import net.handle.hdllib.Util;

import au.edu.apsr.pids.admin.CheckedHandle;
import au.edu.apsr.pids.util.XMLSupport;

/**
 * The core class for initiating various checks.
 * 
 * @author Scott Yeadon, ANU 
 */
public class LinkChecker implements Runnable
{
    // In order to use a cursor to retrieve data you have to set 
    // the ResultSet type of ResultSet.TYPE_FORWARD_ONLY and 
    // autocommit to false in addition to setting a fetch size
    // i.e.
    // conn.setAutoCommit(false);
    //Statement st = conn.createStatement();
    // Turn use of the cursor on.
    // st.setFetchSize(50);
    private org.apache.log4j.Logger log = org.apache.log4j.Logger.getLogger(LinkChecker.class);
    private static String dbUrl = "jdbc:postgresql://127.0.0.1:5432/pids?user=pidmaster";
    
    private static final String SELECT_URL_VALUES_SQL = 
    "SELECT handle, idx, data " + 
    "FROM handles " +
    "WHERE type = 'URL' " +
    "LIMIT ? OFFSET ?";
    
//    private ArrayList<CheckedHandle> recs = new ArrayList<CheckedHandle>();
    private long numOK = 0;
    private long numBroke = 0;
    private int numRecords = -1;
    private int startRecord = -1;
    private LinkCheckerThreadManager lctm = null;

    private Connection conn = null;
    private PreparedStatement ps = null;
    private ResultSet rs = null;
    
    public LinkChecker(int numRecords, int startRecord)
    {
        this.numRecords = numRecords;
        this.startRecord = startRecord;
        lctm = LinkCheckerThreadManager.getThreadManager();
    }
    

    public void run()
    {
        try
        {
            Class.forName("org.postgresql.Driver");

            conn = getDBConnection();
            if (conn == null)
            {
                lctm.threadErrored();
                return;
            }
            
            conn.setAutoCommit(false);
            ps = conn.prepareStatement(SELECT_URL_VALUES_SQL);
            ps.setInt(1, numRecords);
            ps.setInt(2, startRecord);
            ps.setFetchSize(10000);
            rs = ps.executeQuery();
            while (rs.next())
            {
                String handle = Util.decodeString(rs.getBytes("handle"));
                int index = rs.getInt("idx");
                String value = Util.decodeString(rs.getBytes("data"));
                URL url = null;
                try
                {
                    url = new URL(value);
                }
                catch (MalformedURLException mue)
                {
                    numBroke++;
                    lctm.addCheckedHandle(new CheckedHandle(handle, value, "MalformedURL"));
                    continue;
                }
                URLConnection uc = url.openConnection();
                if (url.getHost().length() == 0)
                {
                    numBroke++;
                    lctm.addCheckedHandle(new CheckedHandle(handle, value, "BadHostName"));
                    continue;
                }
                // five second timeout
                uc.setConnectTimeout(5000);
                try
                {
                    uc.connect();
                }
                catch (SocketTimeoutException ste)
                {
                    numBroke++;
                    lctm.addCheckedHandle(new CheckedHandle(handle, value, "Timeout"));
                    continue;
                }
                catch (UnknownHostException uhe)
                {
                    numBroke++;
                    lctm.addCheckedHandle(new CheckedHandle(handle, value, "UnknownHost"));
                    continue;
                }
                catch (ConnectException ce)
                {
                    numBroke++;
                    lctm.addCheckedHandle(new CheckedHandle(handle, value, "UnableToConnect"));
                    continue;
                }
                
                if (uc instanceof HttpURLConnection)
                {
                    numOK++;
                    ((HttpURLConnection)uc).disconnect();
                }
                else if (uc instanceof HttpsURLConnection)
                {
                    numOK++;
                    ((HttpsURLConnection)uc).disconnect();
                }
                else if (url.getProtocol().equals("ftp"))
                {
                    numOK++;
                }
                else
                {
                    lctm.addCheckedHandle(new CheckedHandle(handle, value, "UnknownProtocol"));
                    numBroke++;
                }
            }
            lctm.threadFinished();
        }
        catch (SQLException sqle)
        {
            lctm.threadErrored();
            log.error(sqle);
        }
        catch (IOException ioe)
        {
            lctm.threadErrored();
            log.error(ioe);
        }
        catch (ClassNotFoundException cnfe)
        {
            lctm.threadErrored();
            log.error(cnfe);
        }
        finally
        {
            closeObjects(rs, ps, conn);
        }
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
    
    
    /**
     * close some jdbc objects
     * 
     * @param rs
     *          a ResultSet
     * @param ps
     *          a PreparedStatement
     * @param c
     *          a Connection
     */
    public static void closeObjects(ResultSet rs,
                                    PreparedStatement ps,
                                    Connection c)
    {
        if (rs != null)
        {
            try
            {
                rs.close();
            }
            catch (SQLException sqle){ ; }
            rs = null;
        }

        if (ps != null)
        {
            try
            {
                ps.close();
            }
            catch (SQLException sqle) { ; }
            ps = null;
        }

        if (c != null)
        {
            try
            {
                c.close();
            }
            catch (SQLException sqle) { ; }
            c = null;
        }
    }
}