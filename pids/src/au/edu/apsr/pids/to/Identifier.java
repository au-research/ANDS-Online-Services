/**
 * Date Modified: $Date: 2009-08-18 13:22:16 +1000 (Tue, 18 Aug 2009) $
 * Version: $Revision: 89 $
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

import au.edu.apsr.pids.dao.DAOException;
import au.edu.apsr.pids.dao.IdentifierDAO;
import au.edu.apsr.pids.util.ProcessingException;

/**
 * Class representing an agent identifier (typically a person or automated
 * process)
 * 
 * @author Scott Yeadon, ANU 
 */
public class Identifier
{
    /** separator used for storing admin id info within a handle value */    
    public final static String separator = "####";
    
    private String identifier = null;
    private String authDomain = null;
    private String handle = null;
    private String hash = null;
    private String adminKey = null;
    

    /** 
     * Construct an identifier object using the provided identifier and
     * authentication domain strings. An identifier will typically be 
     * constructed through use of the <code>retrieve</code> method rather
     * than explicitly constructed.
     * 
     * @param identifier
     *              the agent identifier
     * @param authDomain
     *              a string indicating the agent's authentication domain
     */
    public Identifier(String identifier,
                      String authDomain)
    {
        this.identifier = identifier;
        this.authDomain = authDomain;
        try
        {
            this.hash = calculateHashCode();
        }
        catch (NoSuchAlgorithmException nsae)
        {
            this.hash = "Unknown";
        }
        
        this.adminKey = this.identifier + this.separator + this.authDomain; 
    }
    
    
   /**
     * determine if an identifier is registered with the PID service
     * 
     * @return boolean
     *             <code>true</code> if the identifier and authentication
     *             domain combination is registered, otherwise <code>false</code>
     * @param identifier
     *              the agent identifier
     * @param authDomain
     *              a string indicating the agent's authentication domain
     * @throws DAOException
     */
    public static boolean isRegistered(String identifier,
                                       String authDomain) throws ProcessingException
    {
        try
        {
            IdentifierDAO idao = new IdentifierDAO();
            if (idao.retrieve(identifier, authDomain) == null)
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
     * retrieve an identifier
     * 
     * @return Identifier
     *             the Identifier object
     * @param identifier
     *              the agent identifier
     * @param authDomain
     *              a string indicating the agent's authentication domain
     * @throws ProcessingException
     */
    public static Identifier retrieve(String identifier,
                                      String authDomain) throws ProcessingException
    {
        try
        {
            IdentifierDAO idao = new IdentifierDAO();
             return idao.retrieve(identifier, authDomain);
        }
        catch (DAOException daoe)
        {
            throw new ProcessingException(daoe);
        }
    }
    

    /** 
     * Return the handle of this identifier
     * 
     * @return String
     *          the handle of the identifier or <code>null</code> if this
     *          Identifier has not been assigned a handle
     */    
    public String getHandle()
    {
        return this.handle;
    }
    
    /**
     * Set the handle of this identifier
     * 
     * @param handle
     *         the handle to assign the Identifier
     */    
    public void setHandle(String handle)
    {
        this.handle = handle;
    }
    

    /** 
     * Return the agent identifier string
     * 
     * @return String
     *          the agent identifier string
     */    
    public String getIdentifier()
    {
        return this.identifier;
    }

    
    /** 
     * Return the agent authentication domain string
     * 
     * @return String
     *          the agent authentication domain string
     */    
    public String getAuthDomain()
    {
        return this.authDomain;
    }
    

    /** 
     * Return a hash of this Identifier
     * 
     * @return String
     *          the agent identifier string
     */    
    public String getHash()
    {
        return this.hash;
    }
    
    
    /** 
     * Return the string value occupying the DESC value of the agent's handle
     * record
     * 
     * @return String
     *          the agent DESC handle value as a string
     */    
    public String getAdminKey()
    {
        return this.adminKey;
    }
    
    
    /** 
     * Create an MD5 hash for this Identifier
     * 
     * @return String
     *          the MD5 hash as a string
     */    
    private String calculateHashCode() throws NoSuchAlgorithmException
    {
        String s = this.identifier + this.authDomain;
        MessageDigest md = MessageDigest.getInstance("MD5");
        md.update(s.getBytes(), 0, s.length());
        return new BigInteger(1, md.digest()).toString(16);        
    }
}