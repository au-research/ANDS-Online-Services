/**
 * Date Modified: $Date: 2010-06-24 08:45:06 +1000 (Thu, 24 Jun 2010) $
 * Version: $Revision: 442 $
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

import java.util.ArrayList;
import java.util.List;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

import javax.naming.InitialContext;
import javax.naming.NamingException;
import javax.sql.DataSource;

import net.handle.hdllib.Util;

import au.edu.apsr.pids.to.Handle;
import au.edu.apsr.pids.to.Identifier;
import au.edu.apsr.pids.util.Constants;
import au.edu.apsr.pids.util.HandleConfig;
import au.edu.apsr.pids.util.JDBCSupport;

import org.apache.log4j.Logger;

/**
 * Data Access Object for Handle Operations outside the standard handle
 * client API and server operations.
 * 
 * @author Scott Yeadon, ANU 
 */
public class HandleDAO
{
    private final Logger log = Logger.getLogger(HandleDAO.class);

    private DataSource datasource;
    
    private static final String SELECT_NEXT_SUFFIX_SQL = 
        "SELECT nextval('handlesuffix_seq')";

    //arbitrary limit as the list service is not scalable and
    // initially added just for prototype GUI. If this kind of
    // thing turns out to be needed, maybe need to add a suffix
    // table or make a custom storage class for handle (latter
    // is probably the best idea)
    private static final String SELECT_HANDLE_FOR_USER_SQL = 
        "SELECT handle " + 
        "FROM handles " +
        "WHERE data = ? " +
        "AND CAST(CAST(SUBSTRING(handle FROM ?) AS TEXT) AS BIGINT) > ? " + 
        "ORDER BY CAST(CAST(SUBSTRING(handle FROM ?) AS TEXT) AS BIGINT)";

    private static final String SELECT_SINGLE_HANDLE_SQL = 
        "SELECT handle, idx, type, data " + 
        "FROM handles " +
        "WHERE data = ? " +
        "AND handle = ?";
    
    private static final String SELECT_HANDLES_SUFFIX_ORDER_SQL =
        "SELECT * FROM handles " +
        "WHERE type = 'URL' " + 
        "ORDER BY CAST(CAST(SUBSTRING(handle FROM 13) AS TEXT) AS BIGINT);";

    private static final String SELECT_HANDLES_BY_DATA_SQL = 
        "SELECT handle, idx, type, data " + 
        "FROM handles " +
        "WHERE data = ? ";

    private static final String SELECT_HANDLES_BY_DATA_TYPE_SQL = 
        "SELECT handle, idx, type, data " + 
        "FROM handles " +
        "WHERE data = ? " + 
        "AND type = ?";

    private static final String SELECT_ORDERED_INDEX_SQL = 
        "SELECT idx " +
        "FROM handles " +
        "WHERE handle = ? " +
        "ORDER BY idx";
    
    /**
     * create a Handle DAO
     * 
     * @exception DAOException
     */
    public HandleDAO() throws DAOException
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
     * obtain a handle suffix
     * 
     * @return long
     *          The handle suffix
     *          
     * @exception DAOException
     */
    public long getNextSuffix() throws DAOException
    {
        Connection c = null; 
        
        PreparedStatement ps = null;
        
        ResultSet rs = null;
        
        try
        {
            c = datasource.getConnection();
            c.setAutoCommit(false);
            
            ps = c.prepareStatement(SELECT_NEXT_SUFFIX_SQL);
            rs = ps.executeQuery();
            long l = -1;
            if (rs.next())
            {
                l = rs.getLong(1);
            }
            return l;
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
     * Obtain a List of Handle objects belonging to the provided Identifier
     * (not used)  
     * 
     * @return List&lt;Handle&gt;
     *           A list of Handle Objects
     * @param identifier
     *          The Identifier object representing the agent whose handles
     *          are to be returned
     * @param startHandle
     *          The handle from which the list is to be started (exclusive)
     * @throws HandleException
     * @throws DAOException
     */
    public List<String> getHandles(Identifier identifier,
                                   String startHandle) throws DAOException
    {
        Connection c = null; 
        
        PreparedStatement ps = null;
        
        ResultSet rs = null;
        
        ArrayList<String> handleList = new ArrayList<String>();
        
        try
        {
            c = datasource.getConnection();
            c.setAutoCommit(false);
            
            ps = c.prepareStatement(SELECT_HANDLE_FOR_USER_SQL);
            ps.setBytes(1, Util.encodeString(identifier.getHandle()));
            
            HandleConfig hc = HandleConfig.getHandleConfig();
            int index = hc.getPrefix().length();
            int suffix = 0;
            if (startHandle != null)
            {
                suffix = Integer.parseInt(startHandle.substring(index + 1));
            }
            ps.setBytes(1, Util.encodeString(identifier.getHandle()));
            ps.setInt(2, index + 2);
            ps.setInt(3, suffix);
            ps.setInt(4, index + 2);
            rs = ps.executeQuery();
            
            while (rs.next())
            {
                String s = Util.decodeString(rs.getBytes(1));
                handleList.add(s);
            }
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
        
        return handleList;
    }
    

    /**
     * Obtain a List of Handle objects matching a string  
     * 
     * @return List&lt;Handle&gt;
     *           A list of Handle Objects
     * @param data
     *          A string contained within hamdles handles are to be returned
     * @param type
     *          The handle value type (or null if all types)
     * @param pubReadOnly
     *          Only include publicly readable values (default is <code>false</code>)
     * @throws HandleException
     * @throws DAOException
     */
    public List<String> getHandlesByData(String data,
                                         String type,
                                         boolean pubReadOnly) throws DAOException
    {
        Connection c = null;
        
        PreparedStatement ps = null;
        
        ResultSet rs = null;
        
        ArrayList<String> handleList = new ArrayList<String>();
        
        try
        {
            c = datasource.getConnection();
            c.setAutoCommit(false);

            String statement = null;
            
            if (type != null)
            {
                statement = SELECT_HANDLES_BY_DATA_TYPE_SQL;
                if (pubReadOnly)
                {
                    statement += " AND pub_read=TRUE";
                }
                ps = c.prepareStatement(statement);
                ps.setBytes(1, Util.encodeString(data));
                ps.setBytes(2, Util.encodeString(type));
            }
            else
            {
                statement = SELECT_HANDLES_BY_DATA_SQL;
                if (pubReadOnly)
                {
                    statement += " AND pub_read=TRUE";
                }
                ps = c.prepareStatement(statement);
                ps.setBytes(1, Util.encodeString(data));
            }
            
            rs = ps.executeQuery();
            
            while (rs.next())
            {
                String s = Util.decodeString(rs.getBytes(1));
                handleList.add(s);
            }
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
        
        return handleList;
    }

    
    /**
     * Obtain a List of Handle objects belonging to the provided Identifier
     * (not used)  
     * 
     * @return List&lt;Handle&gt;
     *           A list of Handle Objects
     * @param identifier
     *          The Identifier object representing the agent whose handles
     *          are to be returned
     * @param token
     *          a resumption token value
     * @throws HandleException
     * @throws DAOException
     */
/*    public List<String> getHandles(Identifier identifier,
                                   String token) throws DAOException
    {
        Connection c = null; 
        
        PreparedStatement ps = null;
        
        ResultSet rs = null;
        
        ArrayList<String> handleList = new ArrayList<String>();
        
        try
        {
            c = datasource.getConnection();
            c.setAutoCommit(false);
            
            ps = c.prepareStatement(SELECT_HANDLE_FOR_USER_SQL);
            ps.setBytes(1, Util.encodeString(identifier.getHandle()));
            rs = ps.executeQuery();
            
            while (rs.next())
            {
                String s = Util.decodeString(rs.getBytes(1));
                handleList.add(s);
            }
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
        
        return handleList;
    }
  */  
    
    /**
     * Obtain an array of sorted indexes for a handle
     * 
     * @return Integer[]
     *           An array of sorted integers
     * @param handle
     *          The Handle object
     * @throws HandleException
     * @throws DAOException
     */
    public Integer[] getSortedIndexes(Handle handle) throws DAOException
    {
        Connection c = null; 
        
        PreparedStatement ps = null;
        
        ResultSet rs = null;
        
        ArrayList<Integer> indexList = new ArrayList<Integer>();
        
        try
        {
            c = datasource.getConnection();
            c.setAutoCommit(false);
            
            ps = c.prepareStatement(SELECT_ORDERED_INDEX_SQL);
            ps.setBytes(1, Util.encodeString(handle.getHandle()));
            rs = ps.executeQuery();
            
            while (rs.next())
            {
                int i = rs.getInt(1);
                indexList.add(new Integer(i));
            }
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
        
        return (Integer[])indexList.toArray(new Integer[indexList.size()]);
    }
}