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
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Timestamp;
import java.util.ArrayList;
import java.util.Date;
import java.util.List;
import java.util.Map;
import java.util.Iterator;

import javax.naming.InitialContext;
import javax.naming.NamingException;
import javax.sql.DataSource;

import au.edu.apsr.harvester.dao.DAOException;
import au.edu.apsr.harvester.to.Harvest;
import au.edu.apsr.harvester.util.Constants;
import au.edu.apsr.harvester.util.JDBCSupport;

import org.apache.log4j.Logger;

/**
 * Data Access Object for harvests
 * 
 * @author Scott Yeadon, APSR 
 */
public class HarvestDAO
{
    private final Logger log = Logger.getLogger(HarvestDAO.class);
    
    protected DataSource datasource;
    
    protected static final String CREATE_PROVIDER_SQL =
        "INSERT INTO provider VALUES (DEFAULT, ?)";
    
    protected static final String CREATE_HARVEST_SQL =
        "INSERT INTO harvest(harvest_id, provider_id, response_url, " +
        "method, mode, date_started, date_completed, resumption_token, " +
        "status, date_from, date_until, metadata_prefix, set, advanced_harvesting_mode)" +
        " VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    protected static final String SELECT_PROVIDER_SQL =
        "SELECT * FROM provider WHERE source_url = ?";
    
    protected static final String SELECT_PROVIDER_ID_SQL = 
        "SELECT CURRVAL('provider_provider_id_seq')";
     
    protected static final String SELECT_HARVESTS_SQL =
        "SELECT provider.source_url, " +
        "harvest.harvest_id, harvest.status, harvest.response_url, " +
        "harvest.method, harvest.mode, harvest.date_started, " +
        "harvest.date_completed, harvest.resumption_token, harvest.date_from, " + 
        "harvest.date_until, harvest.advanced_harvesting_mode, harvest.metadata_prefix, provider.provider_id, " +
        "harvest.set, schedule.last_run, schedule.next_run, schedule.frequency " + 
        "FROM provider, harvest, schedule " +
        "WHERE provider.provider_id=harvest.provider_id " +
        "AND schedule.harvest_id=harvest.harvest_id";
    
    protected static final String SELECT_HARVEST_SQL =
        "SELECT provider.source_url, " +
        "harvest.harvest_id, harvest.status, harvest.response_url, " +
        "harvest.method, harvest.mode, harvest.date_started, " +
        "harvest.date_completed, harvest.resumption_token, harvest.date_from, " +
        "harvest.date_until, harvest.advanced_harvesting_mode, harvest.metadata_prefix, provider.provider_id, " +
        "harvest.set, schedule.last_run, schedule.next_run, schedule.frequency " + 
        "FROM provider, harvest, schedule " +
        "WHERE provider.provider_id=harvest.provider_id " +
        "AND schedule.harvest_id=harvest.harvest_id " +
        "AND harvest.harvest_id = ?";
    
    protected static final String SELECT_PROVIDER_HARVEST_COUNT_SQL = 
        "SELECT count(*) FROM harvest where provider_id = ?";    
    
    protected static final String UPDATE_HARVEST_SQL = 
        "UPDATE harvest SET status = ?, date_started = ?, date_completed = ?, " +
        "resumption_token = ?, date_from = ?, date_until = ?, mode = ?,  " +
        "method = ?, metadata_prefix = ?, set = ? " + 
        "WHERE harvest_id = ?";    
    
    protected static final String DELETE_HARVEST_SQL = 
        "DELETE FROM harvest WHERE harvest_id = ?";

    protected static final String DELETE_HARVEST_PARAMETERS_SQL = 
        "DELETE FROM harvest_parameter WHERE harvest_id = ?";
    
    protected static final String CREATE_PARAMETER_SQL = 
        "INSERT INTO harvest_parameter (harvest_id, name, value)" + 
        " VALUES (?, ?, ?)";
    
    protected static final String SELECT_HARVEST_PARAMETERS_SQL = 
        "SELECT name, value FROM harvest_parameter " + 
        " WHERE harvest_id = ?";    
    
    protected static final String DELETE_PROVIDER_SQL =
        "DELETE FROM provider where provider_id = ?";

    protected static final String CREATE_SCHEDULE_SQL =
        "INSERT INTO schedule(harvest_id, last_run, next_run, " +
        "frequency)" +
        " VALUES (?, ?, ?, ?)";
    
    protected static final String UPDATE_SCHEDULE_SQL =
        "UPDATE schedule SET last_run = ?, next_run = ?, " +
        "frequency = ? " + 
        "WHERE harvest_id = ?";    

    protected static final String DELETE_SCHEDULE_SQL = 
        "DELETE FROM schedule WHERE harvest_id = ?";

    /**
     * create a Harvest DAO
     * 
     * @exception DAOException
     */
    public HarvestDAO() throws DAOException
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
     * add a harvest record to the database
     * 
     * @param harvest
     *          The harvest to create
     *          
     * @exception DAOException
     */
    public void create(Harvest harvest) throws DAOException
    {
        Connection c = null; 

        PreparedStatement ps = null;
        
        ResultSet rs = null;
        
        try
        {
            c = datasource.getConnection();
            c.setAutoCommit(false);
            
            if (getProviderID(harvest.getSourceURL()) == -1)
            {
                ps = c.prepareStatement(CREATE_PROVIDER_SQL);
                ps.setString(1, harvest.getSourceURL());
                ps.executeUpdate();
                ps.close();
                ps = null;
            
                ps = c.prepareStatement(SELECT_PROVIDER_ID_SQL);
                rs = ps.executeQuery();
                rs.next();
                harvest.setProviderID(rs.getInt(1));
                
                ps.close();
                rs.close();
                ps = null;
                rs = null;
            }
            else
            {
                harvest.setProviderID();
            }
            
            ps = c.prepareStatement(CREATE_HARVEST_SQL);
            ps.setString(1, harvest.getHarvestID());
            ps.setInt(2, harvest.getProviderID());
            ps.setString(3, harvest.getResponseTargetURL());
            ps.setString(4, harvest.getMethod());
            ps.setString(5, harvest.getMode());
            if (harvest.getDateStarted() != null)
            {    
                ps.setTimestamp(6, new Timestamp(harvest.getDateStarted().getTime()));
            }
            else
            {
                ps.setTimestamp(6, null);
            }
            
            if (harvest.getDateCompleted() != null)
            {    
                ps.setTimestamp(7, new Timestamp(harvest.getDateCompleted().getTime()));
            }
            else
            {
                ps.setTimestamp(7, null);
            }
            ps.setString(8, harvest.getResumptionToken());
            ps.setInt(9, Constants.STATUS_REGISTERED);
            ps.setString(10, harvest.getFrom());
            ps.setString(11, harvest.getUntil());
            ps.setString(12, harvest.getMetadataPrefix());
            ps.setString(13, harvest.getSet());
            ps.setString(14, harvest.getAHM());
            ps.executeUpdate();
            ps.close();
            ps = null;
         
            // Add any custom harvest parameters
            Map<String,String> parms = harvest.getParameters();
            if (parms != null)
            {
                Iterator<Map.Entry<String, String>> it = parms.entrySet().iterator();
                while (it.hasNext())
                {
                    Map.Entry<String, String> pair = it.next();
                    ps = c.prepareStatement(CREATE_PARAMETER_SQL);
                    ps.setString(1, harvest.getHarvestID());
                    ps.setString(2, pair.getKey());
                    ps.setString(3, pair.getValue());
                    ps.executeUpdate();
                    ps.close();
                    ps = null;
                }
            }
            
            // create the schedule record
            ps = c.prepareStatement(CREATE_SCHEDULE_SQL);
            ps.setString(1, harvest.getHarvestID());
            if (harvest.getLastRun() != null)
            {
                ps.setTimestamp(2, new Timestamp(harvest.getLastRun().getTime()));
            }
            else
            {
                ps.setTimestamp(2, null);
            }
            if (harvest.getNextRun() != null)
            {
                ps.setTimestamp(3, new Timestamp(harvest.getNextRun().getTime()));
            }
            else
            {
                ps.setTimestamp(3, null);
            }
            ps.setString(4, harvest.getFrequency());
            ps.executeUpdate();
            ps.close();
            ps = null;
            
            c.commit();
            c.close();
            c = null;
        }
        catch (SQLException sqle)
        {
            log.error(sqle.getMessage());
            throw new DAOException(sqle.getMessage(), sqle.getCause());
        }   
        finally
        {
            JDBCSupport.closeObjects(rs, ps, c);
        }
    }
    
    
    /**
     * check if a data provider with the given URL is registered
     * in the database
     * 
     * @param sourceURL
     *          The URL of the OAI-PMH data provider
     *          
     * @return boolean
     *          true if exists, else false
     *          
     * @exception DAOException
     */
    public boolean providerExists(String sourceURL) throws DAOException
    {
        Connection c = null; 
        
        PreparedStatement ps = null;
        
        ResultSet rs = null;
        boolean found = false; 
        
        try
        {
            c = datasource.getConnection();
            c.setAutoCommit(false);
            
            ps = c.prepareStatement(SELECT_PROVIDER_SQL);
            ps.setString(1, sourceURL);
            rs = ps.executeQuery();
            int i=0;
            while (rs.next())
            {
                i++;
            }
            
            if (i == 0) // no records found for this provider
            {
                found = false;
            }
            else
            {
                found = true;
            }
            
            return found;
        }
        catch (SQLException sqle)
        {
            log.error(sqle.getMessage());
            throw new DAOException(sqle.getMessage(), sqle.getCause());
        }   
        finally
        {
            JDBCSupport.closeObjects(rs, ps, c);
        }
    }
    
    
    /**
     * obtains the id of a registered OAI-PMH data provider
     * 
     * @param sourceURL
     *          The URL of the OAI-PMH data provider
     *          
     * @return int
     *          the database ID of the data provider
     *          
     * @exception DAOException
     */
    public int getProviderID(String sourceURL) throws DAOException
    {
        Connection c = null; 
        
        PreparedStatement ps = null;
        
        ResultSet rs = null;
        
        try
        {
            c = datasource.getConnection();
            c.setAutoCommit(false);
            
            ps = c.prepareStatement(SELECT_PROVIDER_SQL);
            ps.setString(1, sourceURL);
            rs = ps.executeQuery();
            int i = -1;
            if (rs.next())
            {
                i = rs.getInt(1);
            }
            return i;
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
     * obtains a List of harvest objects with one or more provided
     * status values
     * 
     * @param statusList
     *          An array of status values. If zero length all harvests
     *          will be returned. 
     *          
     * @return List
     *          A list of harvest objects whose current status matches
     *          one of those in statusList. Empty list if no matches.
     *          
     * @exception DAOException
     */
    public List<Harvest> getHarvests(int[] statusList) throws DAOException
    {
        ArrayList<Harvest> al = new ArrayList<Harvest>();

        Connection c = null; 
        
        PreparedStatement ps = null;
        
        ResultSet rs = null;
        
        try
        {
            c = datasource.getConnection();
            c.setAutoCommit(false);
            
            String statusQuery = SELECT_HARVESTS_SQL; 
            
            if (statusList.length > 0)
            {
                statusQuery += " AND";
            }
            
            for (int i = 0; i < statusList.length; i++)
            {
                if (i != 0)
                {
                    statusQuery += " OR harvest.status = " + statusList[i];
                }
                else
                {
                    statusQuery += " harvest.status = " + statusList[i];
                }
            }
            
            ps = c.prepareStatement(statusQuery);
            rs = ps.executeQuery();
            while (rs.next())
            {
                Harvest h = new Harvest(rs.getString("response_url"),
                        rs.getString("harvest_id"),
                        rs.getString("source_url"));
                h.setMethod(rs.getString("method"));
                h.setMode(rs.getString("mode"));
                h.setStatus(rs.getInt("status"));
                if (rs.getTimestamp("date_started") != null)
                {
                    h.setDateStarted(new Date(rs.getTimestamp("date_started").getTime()));                    
                }
                if (rs.getTimestamp("date_completed") != null)
                {
                    h.setDateCompleted(new Date(rs.getTimestamp("date_completed").getTime()));                    
                }
                h.setResumptionToken(rs.getString("resumption_token"));
                h.setFrom(rs.getString("date_from"));
                h.setUntil(rs.getString("date_until"));
                h.setMetadataPrefix(rs.getString("metadata_prefix"));
                h.setSet(rs.getString("set"));
                h.setAHM(rs.getString("advanced_harvesting_mode"));
                h.setProviderID();
                if (rs.getTimestamp("last_run") != null)
                {
                    h.setLastRun(new Date(rs.getTimestamp("last_run").getTime()));
                }
                if (rs.getTimestamp("next_run") != null)
                {
                    h.setNextRun(new Date(rs.getTimestamp("next_run").getTime()));
                }
                h.setFrequency(rs.getString("frequency"));
                
                // Add any custom harvest parameters
                PreparedStatement ps2 = c.prepareStatement(SELECT_HARVEST_PARAMETERS_SQL);
                ps2.setString(1, h.getHarvestID());
                ResultSet rs2 = ps2.executeQuery();
                while (rs2.next())
                {
                    h.addParameter(rs2.getString("name"), rs2.getString("value"));
                }
                ps2.close();
                rs2 = null;
                
                al.add(h);
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
        
        return al;
    }
    
    
    /**
     * commit changes to a harvest record
     * 
     * @param harvest
     *          The harvest object from which to update
     *          
     * @exception DAOException
     */
    public void update(Harvest harvest) throws DAOException
    {
        int status = harvest.getStatus();
        if (status == -1)
        {
            return;
        }

        Connection c = null;
        
        PreparedStatement ps = null;
        
        try
        {
            c = datasource.getConnection();
            c.setAutoCommit(false);
            
            ps = c.prepareStatement(UPDATE_HARVEST_SQL);
            ps.setInt(1, status);
            if (harvest.getDateStarted() != null)
            {    
                ps.setTimestamp(2, new Timestamp(harvest.getDateStarted().getTime()));
            }
            else
            {
                ps.setTimestamp(2, null);
            }
            
            if (harvest.getDateCompleted() != null)
            {    
                ps.setTimestamp(3, new Timestamp(harvest.getDateCompleted().getTime()));
            }
            else
            {
                ps.setTimestamp(3, null);
            }
            ps.setString(4, harvest.getResumptionToken());
            ps.setString(5, harvest.getFrom());
            ps.setString(6, harvest.getUntil());
            ps.setString(7, harvest.getMode());
            ps.setString(8, harvest.getMethod());
            ps.setString(9, harvest.getMetadataPrefix());
            ps.setString(10, harvest.getSet());
            ps.setString(11, harvest.getHarvestID());
            ps.executeUpdate();
            ps.close();
            ps = null;
            
            //update schedule
            ps = c.prepareStatement(UPDATE_SCHEDULE_SQL);
            if (harvest.getLastRun() != null)
            {
                ps.setTimestamp(1, new Timestamp(harvest.getLastRun().getTime()));
            }
            else
            {
                ps.setTimestamp(1, null);
            }
            if (harvest.getNextRun() != null)
            {
                ps.setTimestamp(2, new Timestamp(harvest.getNextRun().getTime()));
            }
            else
            {
                ps.setTimestamp(2, null);
            }
            ps.setString(3, harvest.getFrequency());
            ps.setString(4, harvest.getHarvestID());
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
     * obtain the harvest object associated with a harvest id 
     * 
     * @param harvestID
     *          The harvest id of the harvest to retrieve
     *          
     * @return Harvest
     *          the harvest object associated with the id, else null
     *          
     * @exception DAOException
     */
    public Harvest retrieve(String harvestID) throws DAOException
    {
        Connection c = null;        
        PreparedStatement ps = null;        
        ResultSet rs = null;
        
        try
        {
            c = datasource.getConnection();
            c.setAutoCommit(false);
            ps = c.prepareStatement(SELECT_HARVEST_SQL);
            ps.setString(1, harvestID);
            rs = ps.executeQuery();
            Harvest h = null;
            if (rs.next())
            {
                h = new Harvest(rs.getString("response_url"),
                                        rs.getString("harvest_id"),
                                        rs.getString("source_url"));
                h.setMethod(rs.getString("method"));
                h.setMode(rs.getString("mode"));
                h.setStatus(rs.getInt("status"));
                if (rs.getTimestamp("date_started") != null)
                {
                    h.setDateStarted(new Date(rs.getTimestamp("date_started").getTime()));                    
                }
                if (rs.getTimestamp("date_completed") != null)
                {
                    h.setDateCompleted(new Date(rs.getTimestamp("date_completed").getTime()));                    
                }
                h.setResumptionToken(rs.getString("resumption_token"));
                h.setFrom(rs.getString("date_from"));
                h.setUntil(rs.getString("date_until"));
                h.setMetadataPrefix(rs.getString("metadata_prefix"));
                h.setSet(rs.getString("set"));
                h.setAHM(rs.getString("advanced_harvesting_mode"));
                h.setProviderID();
                if (rs.getTimestamp("last_run") != null)
                {
                    h.setLastRun(new Date(rs.getTimestamp("last_run").getTime()));
                }
                if (rs.getTimestamp("next_run") != null)
                {
                    h.setNextRun(new Date(rs.getTimestamp("next_run").getTime()));
                }
                h.setFrequency(rs.getString("frequency"));
                
                // Add any custom harvest parameters
                ps = null;
                rs = null;
                
                ps = c.prepareStatement(SELECT_HARVEST_PARAMETERS_SQL);
                ps.setString(1, h.getHarvestID());
                rs = ps.executeQuery();
                while (rs.next())
                {
                    h.addParameter(rs.getString("name"), rs.getString("value"));
                }
            }

            return h;
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
     * deletes all fragment records associated with a harvest
     * 
     * @param harvest
     *          The harvest whose fragments are to be deleted
     *                    
     * @exception DAOException
     */
    public void deleteFragments(Harvest harvest) throws DAOException
    {
        Connection c = null;        
        PreparedStatement ps = null;        
        ResultSet rs = null;
        
        try
        {
            c = datasource.getConnection();
            c.setAutoCommit(false);

            new FragmentDAO().delete(harvest);

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
            JDBCSupport.closeObjects(rs, ps, c);
        }
    }
 
    
    /**
     * delete a harvest including all fragments and the provider if
     * no more harvests remain for that provider
     * 
     * @param harvest
     *          The harvest to be deleted
     *          
     * @exception DAOException
     */
    public void delete(Harvest harvest) throws DAOException
    {
        Connection c = null;        
        PreparedStatement ps = null;        
        ResultSet rs = null;
        
        try
        {
            c = datasource.getConnection();
            c.setAutoCommit(false);

            new FragmentDAO().delete(harvest);
            
            log.info("deleting harvest and schedule with id: " + harvest.getHarvestID());

            ps = c.prepareStatement(DELETE_SCHEDULE_SQL);
            ps.setString(1, harvest.getHarvestID());
            ps.executeUpdate();
            ps = null;

            ps = c.prepareStatement(DELETE_HARVEST_PARAMETERS_SQL);
            ps.setString(1, harvest.getHarvestID());
            ps.executeUpdate();
            ps = null;            

            ps = c.prepareStatement(DELETE_HARVEST_SQL);
            ps.setString(1, harvest.getHarvestID());
            ps.executeUpdate();
            ps = null;
            
            // check if no more harvests for this provider, then
            // junk the provider so no orphan provider records
            ps = c.prepareStatement(SELECT_PROVIDER_HARVEST_COUNT_SQL);
            ps.setInt(1, harvest.getProviderID());  
            rs = ps.executeQuery();
            rs.next();
            int numHarvests = rs.getInt(1);
            ps = null;
            rs = null;
            
            if (numHarvests == 0)
            {
                log.info("Deleting provider source: " + harvest.getSourceURL() + " as no harvest records remain");                
                ps = c.prepareStatement(DELETE_PROVIDER_SQL);
                ps.setInt(1, harvest.getProviderID());
                ps.executeUpdate();
                ps = null;
            }
          
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
            JDBCSupport.closeObjects(rs, ps, c);
        }
    }
}