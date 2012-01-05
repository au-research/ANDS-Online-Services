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
package au.edu.apsr.harvester.thread;

import java.util.Date;
import java.util.HashMap;
import java.util.List;
import java.util.Timer;

import org.apache.log4j.Logger;

import au.edu.apsr.harvester.dao.DAOException;
import au.edu.apsr.harvester.dao.HarvestDAO;
import au.edu.apsr.harvester.to.Harvest;
import au.edu.apsr.harvester.util.Constants;


/** 
 * A singleton class for managing all harvests within the web application
 * 
 * @author Scott Yeadon, APSR 
 */
public class ThreadManager
{
    private static final Logger log = Logger.getLogger(ThreadManager.class);

    private static ThreadManager tm = null;
    
    private List<Harvest> harvestList = null;
    
//    private int total = 0; 
 
    static private HashMap<String,Timer> timers = new HashMap<String,Timer>();
    
    private  ThreadManager()
    {
    }
 
    
/*    protected synchronized void addTotal(int total)
    {
        this.total += total;
    }
*/   
    /** 
     * obtain a reference to the ThreadManager
     * 
     * @return ThreadManager
     *             the thread manager
     */
    public static synchronized ThreadManager getThreadManager()
    {
        if (tm == null)
        {
            tm = new ThreadManager();            
        }
        return tm;
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
    
    
    /** 
     * Get all harvests and schedule them.
     * This is expected to only be invoked on application startup.
     * All harvests are included in case of an unstructured shutdown
     * of the harvester application where status will not be updated.
     * 
     * A registry of currently active harvests is kept in memory by
     * the Thread Manager.
     *
     * @throws ClassNotFoundException
     * @throws InstantiationException
     * @throws IllegalAccessException
     * @throws DAOException
     */
    public void init() throws DAOException
    {
        // get registered and running harvests (if running, means servlet
        // container shutdown improperly so we want to start them up again)
        int[] modeList = {}; 
        
        harvestList = new HarvestDAO().getHarvests(modeList);
 
        for (Harvest h : harvestList)
        {
            h.setRunDates();
            h.update();
            scheduleHarvest(h);
            setHarvestStatus(h, Constants.STATUS_SCHEDULED);
        }
    }
    
    
    /** 
     * Stop a currently running harvest. The status will be updated
     * to indicate the harvest was user terminated.
     *
     * @param harvest
     *          the harvest to stop
     *          
     * @throws DAOException
     */
    public boolean stop(Harvest harvest) throws DAOException
    {
        try
        {
            setHarvestStatus(harvest, Constants.STATUS_USER_TERMINATED);
            if (timers.containsKey(harvest.getHarvestID()))
            {
                timers.get(harvest.getHarvestID()).cancel();
                timers.remove(harvest.getHarvestID());
            }
            
            if (harvest.getFrequency() != null)
            {
                harvest.setRunDates();
                harvest.update();
                scheduleHarvest(harvest);
                setHarvestStatus(harvest, Constants.STATUS_SCHEDULED);
            }
        }
        catch (DAOException daoe)
        {
            log.error(daoe);
            return false;
        }
        
        return true;
    }
    
    
    /** 
     * Stop (if running) and delete a harvest from the database
     *
     * @param harvest
     *          the harvest to stop
     *          
     * @throws DAOException
     */
    public boolean delete(Harvest harvest) throws DAOException
    {
        try
        {
            if (timers.containsKey(harvest.getHarvestID()))
            {
                timers.get(harvest.getHarvestID()).cancel();
                timers.remove(harvest.getHarvestID());
            }
            harvest.delete();
        }
        catch (DAOException daoe)
        {
            log.error(daoe);
            return false;
        }
        
        return true;
    }
    
    
    /** 
     * Start a currently running harvest. If the harvest is currently
     * running the start action will fail and have no impact. If
     * successful the status will be updated to indicate the harvest is
     * running.
     *
     * @param harvest
     *          the harvest to start
     *          
     * @throws ClassNotFoundException
     * @throws InstantiationException
     * @throws IllegalAccessException
     * @throws DAOException
     */
    public boolean start(Harvest harvest)
    {
        try
        {
            if (harvest.getStatus() == Constants.STATUS_RUNNING)
            {
                return false;
            }
            else
            {
                runHarvest(harvest);
            }
        }
        catch (Exception daoe)
        {
            log.error(daoe);
            return false;
        }
        
        return true;
    }
    
    
    /** 
     * Schedule a harvest. If the harvest is currently
     * running the schedule action will fail. If
     * successful the status will be updated to indicate the harvest is
     * scheduled.
     *
     * @param harvest
     *          the harvest to start
     *          
     * @throws ClassNotFoundException
     * @throws InstantiationException
     * @throws IllegalAccessException
     * @throws DAOException
     */
    public boolean schedule(Harvest harvest)
    {
        try
        {
            if (harvest.getStatus() == Constants.STATUS_RUNNING)
            {
                return false;
            }
            else
            {
                harvest.setRunDates();
                harvest.update();
                scheduleHarvest(harvest);
                setHarvestStatus(harvest, Constants.STATUS_SCHEDULED);
            }
        }
        catch (Exception daoe)
        {
            log.error(daoe);
            return false;
        }
        
        return true;
    }
    
    
    /** 
     * Processing when a harvest has completed normally. This
     * will set the harvest status and optionally delete the 
     * harvest from the application.
     *
     * @param harvest
     *          the harvest to update
     * @param delete
     *          true to delete the harvest from the system
     *          
     * @throws DAOException
     */
    protected void setThreadComplete(Harvest harvest) throws DAOException
    {
        setHarvestComplete(harvest);
        if (timers.containsKey(harvest.getHarvestID()))
        {
            if (harvest.getFrequency() == null)
            {
                timers.remove(harvest.getHarvestID());
                harvest.delete();
            }
            else
            {
                harvest.setRunDates();
                harvest.update();
                scheduleHarvest(harvest);
                setHarvestStatus(harvest, Constants.STATUS_SCHEDULED);
            }
        }
//        log.info("total records=" + this.total);
    }
    
    
    /** 
     * Indicates whether a harvest is currently running or not
     *
     * @param harvest
     *          the harvest of interest
     */
    protected boolean isStopped(Harvest harvest)
    {
        if (harvest.getStatus() == Constants.STATUS_USER_TERMINATED)
        {
            return true;
        }
        
        // could also have been deleted or web server crashed so status
        // is incorrect
        if (!timers.containsKey(harvest.getHarvestID()))
        {
            return true;
        }
        
        return false;
    }
    

    /** 
     * Indicates whether a harvest is currently running or not
     *
     * @param harvest
     *          the harvest of interest
     */
    protected boolean isRunning(Harvest harvest)
    {
        if (harvest.getStatus() == Constants.STATUS_RUNNING)
        {
            return true;
        }
        
        return false;
    }

    
    
    /** 
     * Update the harvest to running status
     *
     * @param harvest
     *          the harvest of interest
     *          
     * @throws DAOException
     */
    protected void setThreadRunning(Harvest harvest) throws DAOException
    {
        setHarvestRunning(harvest);
    }
    
    
    /** 
     * Update the harvest to error status
     *
     * @param harvest
     *          the harvest of interest
     *          
     * @throws DAOException
     */
    protected void setThreadError(Harvest harvest) throws DAOException
    {
        setHarvestStatus(harvest, Constants.STATUS_ERROR);
        if (timers.containsKey(harvest.getHarvestID()))
        {
            timers.get(harvest.getHarvestID()).cancel();
            timers.remove(harvest.getHarvestID());
        }
        
        if (harvest.getFrequency() != null)
        {
            harvest.setRunDates();
            harvest.update();
        }
    }
    
    
    /** 
     * Set the harvest status and update the database
     *
     * @param harvest
     *          the harvest of interest
     * @param int
     *          the status of the harvest
     *          
     * @throws DAOException
     */
    private void setHarvestStatus(Harvest harvest,
                                  int status) throws DAOException
    {
        harvest.setStatus(status);
        harvest.update();
    }
    
    
    /** 
     * Update the harvest to running status
     *
     * @param harvest
     *          the harvest of interest
     *          
     * @throws DAOException
     */
    private void setHarvestRunning(Harvest harvest) throws DAOException
    {
        harvest.setStatus(Constants.STATUS_RUNNING);
        harvest.setDateStarted(new Date());
        harvest.update();
    }
    
    
    /** 
     * Update the harvest to completed status
     *
     * @param harvest
     *          the harvest of interest
     *          
     * @throws DAOException
     */
    private void setHarvestComplete(Harvest harvest) throws DAOException
    {
        harvest.setStatus(Constants.STATUS_COMPLETE);
        harvest.setDateCompleted(new Date());
        harvest.update();
        harvest.deleteFragments();
    }
    
    
    /** 
     * create and return a harvest thread for a harvest
     * 
     * @param harvest
     *          the harvest object to be encapsulated
     *          
     * @return HarvestThread
     *          the harvest thread
     *          
     * @throws ClassNotFoundException
     * @throws InstantiationException
     * @throws IllegalAccessException
     * 
     */
    private HarvestThread createHarvestThread(Harvest harvest) throws ClassNotFoundException, InstantiationException, IllegalAccessException 
    {
        String s = "au.edu.apsr.harvester.thread." + 
        harvest.getMethod() + "HarvestThread";
        
        Class<?> c = Class.forName(s);
        HarvestThread ht =  (HarvestThread)c.newInstance();            
        if (!(ht instanceof HarvestThread))
        {
            log.error("bad class:" + ht.getClass().getName() + ". Class not of type HarvestThread");
            ht = null;
        }
        else
        {
            ht.setHarvest(harvest);
        }
        return ht;
    }
    
    
    /**
     * schedule a harvest for execution
     * 
     * @param harvest
     *      the harvest object to schedule
     * 
     * @return boolean
     *      whether the schedule action was successful
     */
    protected boolean scheduleHarvest(Harvest harvest) throws DAOException
    {
        boolean scheduled = true;

        HarvestThread ht = null;
        try
        {
            ht = createHarvestThread(harvest);
        }
        catch (ClassNotFoundException cnfe)
        {
            log.error("Class not found:" + ht.getClass().getName() + ". Harvest with id " + harvest.getHarvestID() + " will not be scheduled");
            scheduled = false;
        }
        catch (InstantiationException ie)
        {
            log.error(ie);
            log.error("Harvest with id " + harvest.getHarvestID() + " will not be scheduled");
            scheduled = false;
        }
        catch (IllegalAccessException iae)
        {
            log.error(iae);
            log.error("Harvest with id " + harvest.getHarvestID() + " will not be scheduled");
            scheduled = false;
        }
        
        if (ht != null)
        {
            Timer t = timers.get(harvest.getHarvestID());

            log.info("Scheduling harvest with id: " + harvest.getHarvestID());

            if (t == null)
            {
                log.info("Timer not found for harvest " + harvest.getHarvestID() + ", new timer being created");
                t = new Timer(true);
            }
            else
            {
                log.info("Timer found for harvest " + harvest.getHarvestID() + ", updating existing timer");
            }
            
            t.schedule(ht, harvest.getRunDate());            
            timers.put(harvest.getHarvestID(), t);
            log.info("Harvest " + harvest.getHarvestID() + ", scheduled for " + harvest.getRunDate());
        }
        
        return scheduled;
    }
    
    
    /**
     * run a harvest now
     * 
     * @param harvest
     *      the harvest object to schedule
     * 
     * @return boolean
     *      whether the schedule action was successful
     */
    private boolean runHarvest(Harvest harvest)
    {
        boolean scheduled = true;

        HarvestThread ht = null;
        try
        {
            ht = createHarvestThread(harvest);
        }
        catch (ClassNotFoundException cnfe)
        {
            log.error("Class not found:" + ht.getClass().getName() + ". Harvest with id " + harvest.getHarvestID() + " will not be scheduled");
            scheduled = false;
        }
        catch (InstantiationException ie)
        {
            log.error(ie);
            log.error("Harvest with id " + harvest.getHarvestID() + " will not be scheduled");
            scheduled = false;
        }
        catch (IllegalAccessException iae)
        {
            log.error(iae);
            log.error("Harvest with id " + harvest.getHarvestID() + " will not be scheduled");
            scheduled = false;
        }
        
        if (ht != null)
        {
            Timer t = timers.get(harvest.getHarvestID());

            if (t == null)
            {
                log.info("Timer not found, new timer being created");
                t = new Timer(true);
            }
            else
            {
                log.info("timer found, updating existing timer");
            }
            
            t.schedule(ht, new Date());
            timers.put(harvest.getHarvestID(), t);
        }
        
        return scheduled;
    }
}