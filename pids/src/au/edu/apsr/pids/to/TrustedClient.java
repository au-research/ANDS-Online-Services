/**
 * Date Modified: $Date: 2010-11-15 13:38:09 +1100 (Mon, 15 Nov 2010) $
 * Version: $Revision: 559 $
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
package au.edu.apsr.pids.to;

import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.math.BigInteger;

import org.apache.log4j.Logger;

import au.edu.apsr.pids.dao.TrustedClientDAO;
import au.edu.apsr.pids.dao.DAOException;
import au.edu.apsr.pids.servlet.AddTrustedClientServlet;
import au.edu.apsr.pids.util.ProcessingException;

/**
 * Class representing a trusted client (typically an application)
 * 
 * @author Scott Yeadon, ANU 
 */
public class TrustedClient
{
    private static final Logger log = Logger.getLogger(TrustedClient.class);
    
    private String ip = null;
    private String appId = null;
    private String description = null;
    
    /** 
     * Construct an identifier object using the provided ip and
     * hash. A trusted client will typically be 
     * constructed through use of the <code>retrieve</code> method rather
     * than explicitly constructed.
     * 
     * @param ip
     *              the ip address
     * @param appId
     *              the application id (a SHA1 hash)
     * @param description
     *        plain text description of the client
     */
    public TrustedClient(String ip,
                      String appId,
                      String description)
    {
        this.ip = ip;
        this.appId = appId;
        this.description = description;
    }
    
    
   /**
     * determine if an identifier is registered with the PID service
     * 
     * @return boolean
     *             <code>true</code> if the identifier and authentication
     *             domain combination is registered, otherwise <code>false</code>
     * @param ip
     *              the trusted client ip address
     * @throws DAOException
     */
    public static boolean isRegistered(String ip) throws ProcessingException
    {
        try
        {
            TrustedClientDAO tcdao = new TrustedClientDAO();
            if (tcdao.retrieve(ip) == null)
            {
                return false;
            }
            
            return true;
        }
        catch (DAOException daoe)
        {
            throw new ProcessingException(daoe);
        }
    }

    
    /**
     * retrieve a TrustedClient object
     * 
     * @return TrustedClient
     *             the TrustedClient object
     * @param ip
     *              the ip address
     * @throws ProcessingException
     */
    public static TrustedClient retrieve(String ip) throws ProcessingException
    {
        try
        {
            TrustedClientDAO tcdao = new TrustedClientDAO();
             return tcdao.retrieve(ip);
        }
        catch (DAOException daoe)
        {
            throw new ProcessingException(daoe);
        }
    }
    

    /**
     * create a TrustedClient
     * 
     * @param ip
     *              the ip address
     * @param description
     *        plain text description of the client
     * 
     * @throws DAOException
     */
    public static TrustedClient create(String ip,
                                       String description) throws ProcessingException
    {
        try
        {
            TrustedClient tc = new TrustedClient(ip, calculateHash(ip), description); 
            TrustedClientDAO tcdao = new TrustedClientDAO();
            tcdao.create(tc);
            return tc;
        }
        catch (DAOException daoe)
        {
            throw new ProcessingException(daoe);
        }
    }


    /**
     * create a TrustedClient
     * 
     * @param ip
     *              the ip address
     * @param description
     *        plain text description of the client
     * @param appId
     *        the appId of the client
     * 
     * @throws DAOException
     */
    public static TrustedClient create(String ip,
                                       String description,
                                       String appId) throws ProcessingException
    {
        try
        {
            TrustedClient tc = new TrustedClient(ip, appId, description); 
            TrustedClientDAO tcdao = new TrustedClientDAO();
            tcdao.create(tc);
            return tc;
        }
        catch (DAOException daoe)
        {
            throw new ProcessingException(daoe);
        }
    }

    
    /** 
     * Return the ip address of this client
     * 
     * @return String
     *          the ip address of the client or <code>null</code> if the ip
     *          has not been set on this object
     */    
    public String getIP()
    {
        return this.ip;
    }
    
    /** 
     * Return the client identifier hash
     * 
     * @return String
     *          the application identifier hash
     */    
    public String getAppId()
    {
        return this.appId;
    }
    

    /** 
     * Return the client description
     * 
     * @return String
     *          the client description
     */    
    public String getDescription()
    {
        return this.description;
    }

    
    /** 
     * Calculate the SHA1 for the ip
     * 
     * @return String
     *          the SHA1 hash as a string
     */    
    public static String calculateHash(String ip) throws ProcessingException
    {
        try
        {
            MessageDigest md = MessageDigest.getInstance("SHA1");
            md.update(ip.getBytes(), 0, ip.length());
            return new BigInteger(1, md.digest()).toString(16);
        }
        catch (NoSuchAlgorithmException nsae)
        {
            log.error("NoSuchAlgorithmException", nsae);
            throw new ProcessingException(nsae);
        }
    }
}