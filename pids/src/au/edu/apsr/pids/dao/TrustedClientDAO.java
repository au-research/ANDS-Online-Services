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
package au.edu.apsr.pids.dao;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Timestamp;
import java.util.ArrayList;

import javax.naming.InitialContext;
import javax.naming.NamingException;
import javax.sql.DataSource;

import net.handle.hdllib.Util;

import au.edu.apsr.pids.to.Handle;
import au.edu.apsr.pids.to.TrustedClient;
import au.edu.apsr.pids.util.Constants;
import au.edu.apsr.pids.util.HandleSupport;
import au.edu.apsr.pids.util.JDBCSupport;

import org.apache.log4j.Logger;

/**
 * Data Access Object for Handle Operations outside the standard handle
 * client API and server operations.
 * 
 * @author Scott Yeadon, ANU 
 */
public class TrustedClientDAO
{
    private final Logger log = Logger.getLogger(TrustedClientDAO.class);

    private DataSource datasource;
    
    private static final String SELECT_CLIENT_SQL = 
        "SELECT ip_address, app_id, description " +
        "FROM trusted_client WHERE ip_address = ?";
		
	private static final String SELECT_ALLCLIENTS_SQL = 
        "SELECT ip_address, app_id, description " +
        "FROM trusted_client ORDER BY app_id DESC";

    private static final String INSERT_CLIENT_SQL = 
        "INSERT INTO trusted_client (ip_address, app_id, description) " +
        "VALUES (?, ?, ?)";
    
    /**
     * create an Identifier DAO
     * 
     * @exception DAOException
     */
    public TrustedClientDAO() throws DAOException
    {
        try
        {
            InitialContext ic = new InitialContext();
            if (ic == null)
            {
                log.error("Unable to instantiate Initial Context Object");
                throw new DAOException("Unable to instantiate Initial Context Object");
            }

            datasource = (DataSource)ic.lookup(DAOConstants.DAO_DATASOURCE);
            if (datasource == null)
            {
                log.error("Unable to locate datasource: " + DAOConstants.DAO_DATASOURCE);
                throw new DAOException("Unable to locate datasource: " + DAOConstants.DAO_DATASOURCE);
            }
        }
        catch (NamingException ne)
        {
            throw new DAOException("Naming Exception: " + ne.getMessage());
        }
    }
    
    
    /**
     * obtain a trusted client record
     * 
     * @return TrustedClient
     *           A TrustedClient object if found, null if not found
     *           
     * @param ip
     *          The identifier
     *          
     * @exception DAOException
     */
    public TrustedClient retrieve(String ip) throws DAOException
    {
        Connection c = null; 
        
        PreparedStatement ps = null;
        
        ResultSet rs = null;
        
        try
        {
            c = datasource.getConnection();
            c.setAutoCommit(false);
            
            ps = c.prepareStatement(SELECT_CLIENT_SQL);
            ps.setString(1, ip);
            rs = ps.executeQuery();
            
            TrustedClient id = null;
            if (rs.next())
            {
                id = new TrustedClient(ip, rs.getString("app_id"), rs.getString("description"));
//                id.setID(rs.getInt("identifier_id"));
            }
            return id;
        }
        catch (SQLException sqle)
        {
            log.error("SQLException occurred", sqle);
            throw new DAOException(sqle);
        }
        finally
        {
            JDBCSupport.closeObjects(rs, ps, c);
        }
    }
	
	
	/**
     * obtain a list of all trusted client records
     * 
     * @return ArrayList<TrustedClient>
     *           A list containing all TrustedClient objects found
     *                    
     * @exception DAOException
     */
    public ArrayList<TrustedClient> retrieveAll() throws DAOException
    {
        Connection c = null; 
        
        PreparedStatement ps = null;
        
        ResultSet rs = null;
        
        try
        {
            c = datasource.getConnection();
            c.setAutoCommit(false);
            
            ps = c.prepareStatement(SELECT_ALLCLIENTS_SQL);
            rs = ps.executeQuery();
            
            ArrayList<TrustedClient> clients = new ArrayList<TrustedClient>();
            while (rs.next())
            {
                clients.add(new TrustedClient(rs.getString("ip_address"), rs.getString("app_id"), rs.getString("description")));
            }
            return clients;
        }
        catch (SQLException sqle)
        {
            log.error("SQLException occurred", sqle);
            throw new DAOException(sqle);
        }
        finally
        {
            JDBCSupport.closeObjects(rs, ps, c);
        }
    }
     
    
    /**
     * add a trusted client record to the database
     * 
     * @param tc
     *          A Trusted Client object to store in the database
     *          
     * @exception DAOException
     */
    public void create(TrustedClient tc) throws DAOException
    {
        Connection c = null;
    
        PreparedStatement ps = null;
    
        try
        {
            c = datasource.getConnection();
            c.setAutoCommit(false);
              
            ps = c.prepareStatement(INSERT_CLIENT_SQL);
            ps.setString(1, tc.getIP());
            ps.setString(2, tc.getAppId());            
            ps.setString(3, tc.getDescription());            
            ps.executeUpdate();
            ps.close();
            ps = null;
            c.commit();
            c.close();
            c = null;
        }
        catch (SQLException sqle)
        {
            log.error("SQLException occurred", sqle);
            throw new DAOException(sqle);
         }
        finally
        {
            JDBCSupport.closeObjects(ps, c);
        }
    }
}