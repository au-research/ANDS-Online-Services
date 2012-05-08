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
package au.edu.apsr.harvester.dao;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.SQLException;
import java.sql.Timestamp;

import javax.naming.InitialContext;
import javax.naming.NamingException;
import javax.sql.DataSource;

import org.apache.log4j.Logger;

import au.edu.apsr.harvester.dao.DAOException;
import au.edu.apsr.harvester.to.Harvest;
import au.edu.apsr.harvester.to.Fragment;
import au.edu.apsr.harvester.util.JDBCSupport;

/**
 * Data Access Object for harvested fragments.
 * 
 * @author Scott Yeadon, ANU 
 */
public class FragmentDAO
{
    private final Logger log = Logger.getLogger(FragmentDAO.class);

    private DataSource datasource;
    
    private static final String CREATE_FRAGMENT_SQL = 
        "INSERT INTO fragment (harvest_id, request_id, date_stored, text) VALUES (?, ?, ?, ?)";
    
    private static final String DELETE_FRAGMENTS_SQL = 
        "DELETE FROM fragment WHERE harvest_id = ?";

    /**
     * create a Fragment DAO
     * 
     * @exception DAOException
     */
    public FragmentDAO() throws DAOException
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
     * add a fragment record to the database
     * 
     * @param frag
     *          The fragment to create
     *          
     * @exception DAOException
     */
    public void create(Fragment frag) throws DAOException
    {
        Connection c = null; 
    
        PreparedStatement ps = null;
    
        try
        {
            c = datasource.getConnection();
            c.setAutoCommit(false);
              
            ps = c.prepareStatement(CREATE_FRAGMENT_SQL);
            ps.setString(1, frag.getHarvestID());
            ps.setInt(2, frag.getRequestID());
            ps.setTimestamp(3, new Timestamp(System.currentTimeMillis()));
            ps.setString(4, frag.getText());
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
    
    
    /**
     * delete fragment records for a harvest
     * 
     * @param harvest
     *          The harvest for which all fragments are to be deleted
     *          
     * @exception DAOException
     */
    public void delete(Harvest harvest) throws DAOException
    {
        Connection c = null; 
        
        PreparedStatement ps = null;
        
        try
        {
            c = datasource.getConnection();
            c.setAutoCommit(false);
             
            ps = c.prepareStatement(DELETE_FRAGMENTS_SQL);
            ps.setString(1, harvest.getHarvestID());
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