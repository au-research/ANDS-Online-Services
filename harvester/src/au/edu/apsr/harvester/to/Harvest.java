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
package au.edu.apsr.harvester.to;

import java.util.Calendar;
import java.util.Date;
import java.util.HashMap;
import java.util.Map;
import java.util.TimeZone;

import au.edu.apsr.harvester.dao.DAOException;
import au.edu.apsr.harvester.dao.HarvestDAO;
import au.edu.apsr.harvester.util.Constants;

import org.apache.log4j.Logger;

/**
 * Class representing a harvest
 * 
 * @author Scott Yeadon, ANU
 */
public class Harvest
{
    private static final Logger log = Logger.getLogger(Harvest.class);
    private String responseTargetURL = null;
    private String harvestID = null;
    private String sourceURL = null;
    private String mode = null;
    private String method = null;
    
    private String resumptionToken = null;
    private String metadataPrefix = null;
    private String from = null;
    private String until = null;
    private String set = null;
    private String ahm = null;
    
    private int status = -1;

    private Date dateStarted = null;
    private Date dateCompleted = null;
    
    private Date lastRun = null;
    private Date nextRun = null;
    private String frequency = null;
    
    private Map<String, String> parameters = null;
    
    private int providerID = -1;
    
    /**
     * Create a new harvest. Default method is OAI and mode is harvest.
     * Use the set methods and update method to override and reflect
     * in the database respectively.
     * 
     * @param responseTargetURL
     *          harvests will POST results of harvesting to this URL
     * @param harvestID
     *          the id of the harvest (must be unique within webapp)
     * @param sourceURL
     *          URL to data provider or other data source         
     */
    public Harvest(String responseTargetURL,
                   String harvestID,
                   String sourceURL)
    {
        this.responseTargetURL = responseTargetURL;
        this.harvestID = harvestID;
        this.sourceURL = sourceURL;
        this.method = Constants.METHOD_OAI_PMH;
        this.mode = Constants.MODE_HARVEST;
    }
    
    
    /**
     * Indicates whether a data provider already exists.
     *
     * @return boolean
     *          true if exists, else false
     */
    public boolean providerExists() throws Exception
    {
        return new HarvestDAO().providerExists(this.sourceURL);
    }

    
    /**
     * get the id of the provider
     *
     * @param sourceURL
     *          the URL of the data provider
     *          
     * @return int
     *          the provider id, -1 indicates an uncommitted harvest
     *          object
     */
    public int getProviderID(String sourceURL) throws DAOException
    {
        if (providerID == -1)
        {
            providerID = new HarvestDAO().getProviderID(this.sourceURL);
        }
        return providerID;
    }
    
    
    /**
     * get the id of the provider
     *
     * @return int
     *          the provider id, -1 indicates an uncommitted harvest
     *          object
     */
    public int getProviderID() throws DAOException
    {
        return this.providerID;
    }
    
    
    /**
     * set the id of the provider
     *
     * @throws DAOException
     */
    public void setProviderID() throws DAOException
    {
        if (this.providerID == -1)
        {
            this.providerID = new HarvestDAO().getProviderID(this.sourceURL);
        }
    }
     
    
    /**
     * set the id of the provider
     *
     * @param providerID
     *          the provider id
     *          
     * @throws DAOException
     */
    public void setProviderID(int providerID) throws DAOException
    {
        if (this.providerID == -1)
        {
            this.providerID = providerID;
        }
    }
    
    
    /**
     * register the harvest in the database
     *          
     * @throws DAOException
     */
    public void register() throws DAOException
    {
        new HarvestDAO().create(this);
        setProviderID();
    }
 
    
    /**
     * set the status of the harvest
     * 
     * @param status
     *      the status to set
     */
    public void setStatus(int status)
    {
        this.status = status;
    }
     
    
    /**
     * get the status of the harvest
     * 
     * @return int
     *         the status of the harvest
     */
    public int getStatus()
    {
        return this.status;
    }
    
    
    /**
     * get the status of the harvest in string form
     * 
     * @return String
     *         the status of the harvest
     */
    public String getStatusAsString()
    {
        String statusString = "Unknown";
        
        if (this.status == Constants.STATUS_COMPLETE)
        {
            statusString = Constants.STATUS_COMPLETE_STRING;
        }
        else if (this.status == Constants.STATUS_REGISTERED)
        {
            statusString = Constants.STATUS_REGISTERED_STRING;
        }
        else if (this.status == Constants.STATUS_RUNNING)
        {
            statusString = Constants.STATUS_RUNNING_STRING;
        }
        else if (this.status == Constants.STATUS_USER_TERMINATED)
        {
            statusString = Constants.STATUS_USER_TERMINATED_STRING;
        }
        else if (this.status == Constants.STATUS_ERROR)
        {
            statusString = Constants.STATUS_ERROR_STRING;
        }
        else if (this.status == Constants.STATUS_SCHEDULED)
        {
            statusString = Constants.STATUS_SCHEDULED_STRING;
        }
        
        return statusString;
    }

    
    /**
     * get the date/time the harvest started running
     * 
     * @return Date
     *         the date/time the harvest started running
     */
    public Date getDateStarted()
    {
        return this.dateStarted;
    }

    
    /**
     * set the date/time the harvest started running
     * 
     * @param dateStarted
     *              the date/time the harvest started running
     */
    public void setDateStarted(Date dateStarted)
    {
        this.dateStarted = dateStarted;
    }

    
    /**
     * get the date/time the harvest completed
     * 
     * @return Date
     *              the date/time the harvest completed
     */
    public Date getDateCompleted()
    {
        return this.dateCompleted;
    }

    
    /**
     * set the date/time the harvest completed
     * 
     * @param dateCompleted
     *              the date/time the harvest completed
     */
    public void setDateCompleted(Date dateCompleted)
    {
        this.dateCompleted = dateCompleted;
    }
    
    
    /**
     * set the resumption token
     * 
     * @param token
     *              the resumption token
     */
    public void setResumptionToken(String token)
    {
        this.resumptionToken = token;
    }
    
    
    /**
     * get the resumption token
     * 
     * @return String
     *              the resumption token
     */
    public String getResumptionToken()
    {
        return this.resumptionToken;
    }
    
    
    /**
     * get the data source (typically OAI-PMH data provider) URL
     * 
     * @return String
     *              the data source URL
     */
    public String getSourceURL()
    {
        return this.sourceURL;
    }
        
    
    /**
     * get the harvest id
     * 
     * @return String
     *            the harvest id
     */
    public String getHarvestID()
    {
        return this.harvestID;
    }
    
    
    /**
     * get the harvest method
     * 
     * @return String
     *            the harvest method
     */
    public String getMethod()
    {
        return this.method;
    }
    
    
    /**
     * get the harvest mode
     * 
     * @return String
     *            the harvest mode
     */
    public String getMode()
    {
        return this.mode;
    }
    
    
    /**
     * get the response target URL
     * 
     * @return String
     *            the response target URL
     */
    public String getResponseTargetURL()
    {
        return this.responseTargetURL;
    }
    
    
    /**
     * commit changes in the harvest object to the database
     *
     * @throws DAOException
     */
    public void update() throws DAOException
    {
        new HarvestDAO().update(this);
    }
    
    
    /**
     * retrieve a harvest record given a harvest id
     * 
     * @param harvestID
     *          the harvest id
     * 
     * @return Harvest
     *            a harvest object if found, else null
     *
     * @throws DAOException
     */
    public static Harvest find(String harvestID) throws DAOException
    {
        return new HarvestDAO().retrieve(harvestID);
    }
    
    
    /**
     * delete this harvest from the database
     * 
     * @throws DAOException
     */
    public void delete() throws DAOException
    {
        new HarvestDAO().delete(this);
    }
    
    
    /**
     * delete fragments belonging to this harvest from the database
     * 
     * @throws DAOException
     */
    public void deleteFragments() throws DAOException
    {
        new HarvestDAO().deleteFragments(this);
    }
    
    
    /**
     * set the metadata prefix
     * 
     * @param metadataPrefix
     *          the metadataPrefix
     */
    public void setMetadataPrefix(String metadataPrefix)
    {
        this.metadataPrefix = metadataPrefix;
    }
    
    
    /**
     * get the metadata prefix
     * 
     * @return String
     *          the metadataPrefix
     */
    public String getMetadataPrefix()
    {
        return this.metadataPrefix;
    }
    
    
    /**
     * get the until date
     * 
     * @return String
     *          the until date
     */
    public String getUntil()
    {
        return this.until;
    }

    
    /**
     * get the from date
     * 
     * @return String
     *          the from date
     */
    public String getFrom()
    {
        return this.from;
    }
    
    
    /**
     * set the until date
     * 
     * @param until
     *          the until date
     */
    public void setUntil(String until)
    {
        this.until = until;
    }
    
    
    /**
     * set the from date
     * 
     * @param from
     *          the from date
     */
    public void setFrom(String from)
    {
        this.from = from;
    }
   
    
    /**
     * get the set spec
     * 
     * @return String
     *          the set spec
     */
    public String getSet()
    {
        return this.set;
    }
    
    
    /**
     * set the set spec
     * 
     * @param setSpec
     *          the set spec
     */
    public void setSet(String setSpec)
    {
        this.set = setSpec;
    }

    /**
     * set the advanced harvesting mode
     * 
     * 
     */
    public void setAHM(String ahm)
    {
        this.ahm = ahm;
    }

    /**
     * Get the advanced harvesting mode
     * 
     * 
     */
    public String getAHM()
    {
        return this.ahm;
    }
    
    
    /**
     * set the harvest method
     * 
     * @param method
     *          the harvest method
     */
    public void setMethod(String method)
    {
        this.method = method;
    }
    
    
    /**
     * set the harvest mode
     * 
     * @param mode
     *          the harvest mode
     */
    public void setMode(String mode)
    {
        this.mode = mode;
    }
    
    
    /**
     * sets the date/time of the next scheduled run
     *          
     * @param nextRun
     *            the date/time of the next scheduled run
     */
    public void setNextRun(Date nextRun)
    {
        this.nextRun = nextRun;
    }
        
    
    /**
     * gets the date/time of the next scheduled run
     *          
     * @return Date
     *              the date/time of the next scheduled run
     */
    public Date getNextRun()
    {
        return this.nextRun;
    }
    

    /**
     * sets the date/time of the last scheduled run
     * The last scheduled run should be considered
     * separate from the last actual run. The last
     * actual run should be set by the setDateStarted()
     * and setDateCompleted() methods
     *          
     * @param lastRun
     *         the date/time of the last scheduled run
     */
    public void setLastRun(Date lastRun)
    {
        this.lastRun = lastRun;
    }
    
    
    /**
     * gets the date/time of the last scheduled run
     * The last scheduled run should be considered
     * separate from the last actual run. The last
     * actual run should be obtained by the getDateStarted()
     * and getDateCompleted() methods
     *          
     * @return Date
     *              the date/time of the last scheduled run
     */
    public Date getLastRun()
    {
        return this.lastRun;
    }
    
    
    /**
     * sets the frequency of the schedule
     *          
     * @param frequency
     *              the frequency of the harvester run
     */
    public void setFrequency(String frequency)
    {
        this.frequency = frequency;
    }
    
    
    /**
     * gets the frequency of the schedule
     *          
     * @return String
     *              the frequency of the harvester run
     */
    public String getFrequency()
    {
        return frequency;
    }
    
    
    /**
     * sets any parameters required for custom processing
     *          
     * @param parameters
     *           Map<String,String> of parameter names and values
     */
    public void setParameters(Map<String,String> parameters)
    {
        this.parameters = parameters;
    }
    
    
    /**
     * Add a parameter and value to the parameters Map
     *          
     * @param name
     *          The name of the parameter
     * @param value
     *        The String value of the parameter
     */
    public void addParameter(String name,
                             String value)
    {
        if (this.parameters == null)
        {
            this.parameters = new HashMap<String,String>();
        }
        this.parameters.put(name,value);
    } 

    
    /**
     * gets the Map of custom processing parameters
     *          
     * @return Map<String,String>
     *         A Map of parameter names and values. May be null.
     */
    public Map<String,String> getParameters()
    {
        return this.parameters;
    }

    
    /**
     * Get a parameter value from the parameters Map
     *          
     * @param name
     *          The name of the parameter
     * @return String
     *        The value of the parameter (may be null)
     */
    public String getParameter(String name)
    {
        return this.parameters.get(name);
    } 

    
    /**
     * sets the last run and next run dates. If the next run occurs in the
     * past it reschedules to the nearest period keeping to the same schedule.
     * For example, if the next run for a weekly job is Tuesday and it is
     * currently Wednesday the next run will be scheduled to the following
     * Tuesday. The update() method must be called to move these changes
     * to the database record.
     *          
     */
    public void setRunDates()
    {
        if (getFrequency() == null)
        {
            log.info("one-off job, no future run date");
            return;
        }

        // No date provided, but frequency provided. Set to immediate future run.
        // May not be a good idea to leave this in final version as presumably if
        // a job is being scheduled, it would be expected for someone to nominate
        // a date/time for periodic run.
/*        if (getNextRun() == null)
        {
            Calendar cal = Calendar.getInstance(TimeZone.getTimeZone("UTC"));
            cal.setTime(new Date());
            cal.add(Calendar.SECOND, 5);
            setNextRun(cal.getTime());
            log.info("no run date provided, next run date is set to " + getNextRun());
        }
*/
        Calendar nextRunCal = Calendar.getInstance(TimeZone.getTimeZone("UTC"));
        nextRunCal.setTime(getNextRun());
        
        Calendar currCal = Calendar.getInstance(TimeZone.getTimeZone("UTC"));
        currCal.setTime(new Date());

        // Check that it's not a one-off run prior to the next scheduled
        // run date.
        // If this is the case we don't want to change the scheduling 
        if (nextRunCal.compareTo(currCal) > 0)
        {
            return;
        }

        // Some of this may be going a bit overboard, but what the hey.
        // And it's sure easier to read and (hopefully) more reliable 
        // than millisecond calculations!
        
        Calendar cal = Calendar.getInstance(TimeZone.getTimeZone("UTC"));
        setLastRun(getNextRun());
        cal.setTime(getLastRun());
        // Ensure we don't hit any boundary condition which would result
        // in the next run date being in the past
        //cal.add(Calendar.MINUTE, 1);

        if (getFrequency().equals("hourly"))
        {
            cal.add(Calendar.HOUR_OF_DAY, 1);
            if (cal.compareTo(currCal) < 0)
            {
                log.info("next hourly run date is in the past, updating to latest schedule");

                if (currCal.get(Calendar.MINUTE) >= cal.get(Calendar.MINUTE))
                {
                    currCal.add(Calendar.HOUR_OF_DAY, 1);
                }
                
                currCal.set(Calendar.MINUTE, cal.get(Calendar.MINUTE));
                currCal.set(Calendar.SECOND, cal.get(Calendar.SECOND));
                setNextRun(currCal.getTime());
            }
            else
            {
                setNextRun(cal.getTime());
            }
        }
        
        if (getFrequency().equals("daily"))
        {
            cal.add(Calendar.DAY_OF_YEAR, 1);
            if (cal.compareTo(currCal) < 0)
            {
                log.info("next daily run date is in the past, updating to latest schedule");
                // if currently later in the day than usual schedule
                // move to following day
                if (currCal.get(Calendar.HOUR_OF_DAY) > cal.get(Calendar.HOUR_OF_DAY))
                {
                    currCal.add(Calendar.DAY_OF_YEAR, 1);
                }
                
                if (currCal.get(Calendar.HOUR_OF_DAY) == cal.get(Calendar.HOUR_OF_DAY))
                {
                    if (currCal.get(Calendar.MINUTE) >= cal.get(Calendar.MINUTE))
                    {
                        currCal.add(Calendar.DAY_OF_YEAR, 1);
                    }
                }
                
                currCal.set(Calendar.HOUR_OF_DAY, cal.get(Calendar.HOUR_OF_DAY));
                currCal.set(Calendar.MINUTE, cal.get(Calendar.MINUTE));
                currCal.set(Calendar.SECOND, cal.get(Calendar.SECOND));
                setNextRun(currCal.getTime());
            }
            else
            {
                setNextRun(cal.getTime());
            }
        }
        
        if (getFrequency().equals("weekly"))
        {
            cal.add(Calendar.WEEK_OF_YEAR, 1);
            if (cal.compareTo(currCal) < 0)
            {
                log.info("next weekly run date is in the past, updating to latest schedule");
                // if currently later in the day than usual schedule
                // move to following day
                if (currCal.get(Calendar.DAY_OF_WEEK) > cal.get(Calendar.DAY_OF_WEEK))
                {
                    currCal.add(Calendar.WEEK_OF_YEAR, 1);
                }
                
                if (currCal.get(Calendar.DAY_OF_WEEK) == cal.get(Calendar.DAY_OF_WEEK))
                {
                    if (currCal.get(Calendar.HOUR_OF_DAY) > cal.get(Calendar.HOUR_OF_DAY))
                    {
                        currCal.add(Calendar.WEEK_OF_YEAR, 1);
                    }
                    
                    if (currCal.get(Calendar.HOUR_OF_DAY) == cal.get(Calendar.HOUR_OF_DAY))
                    {
                        if (currCal.get(Calendar.MINUTE) >= cal.get(Calendar.MINUTE))
                        {
                            currCal.add(Calendar.WEEK_OF_YEAR, 1);
                        }
                    }
                }
                
                currCal.set(Calendar.DAY_OF_WEEK, cal.get(Calendar.DAY_OF_WEEK));
                currCal.set(Calendar.HOUR_OF_DAY, cal.get(Calendar.HOUR_OF_DAY));
                currCal.set(Calendar.MINUTE, cal.get(Calendar.MINUTE));
                currCal.set(Calendar.SECOND, cal.get(Calendar.SECOND));
                setNextRun(currCal.getTime());
            }
            else
            {
                setNextRun(cal.getTime());
            }
        }
        
        if (getFrequency().equals("fortnightly"))
        {
            cal.add(Calendar.WEEK_OF_YEAR, 2);
            if (cal.compareTo(currCal) < 0)
            {
                log.info("next fortnightly run date is in the past, updating to latest schedule");
                // if currently later in the day than usual schedule
                // move to following day.
                if (currCal.get(Calendar.WEEK_OF_YEAR) > cal.get(Calendar.WEEK_OF_YEAR))
                {
                    // is the fortnight based on an odd or even week?
                    if (cal.get(Calendar.WEEK_OF_YEAR) % 2 == 0)
                    {
                        if (currCal.get(Calendar.WEEK_OF_YEAR) % 2 == 0)
                        {
                            currCal.add(Calendar.WEEK_OF_YEAR, 2);
                        }
                        else
                        {
                            currCal.add(Calendar.WEEK_OF_YEAR, 1);                            
                        }
                    }
                    else // odd week
                    {
                        if (currCal.get(Calendar.WEEK_OF_YEAR) % 2 == 0)
                        {
                            currCal.add(Calendar.WEEK_OF_YEAR, 1);                            
                        }
                        else
                        {
                            currCal.add(Calendar.WEEK_OF_YEAR, 2);                            
                        }
                    }
                }
                
                if (currCal.get(Calendar.WEEK_OF_YEAR) == cal.get(Calendar.WEEK_OF_YEAR))
                {
                    if (currCal.get(Calendar.DAY_OF_WEEK) > cal.get(Calendar.DAY_OF_WEEK))
                    {
                        currCal.add(Calendar.WEEK_OF_YEAR, 2);
                    }
                    
                    if (currCal.get(Calendar.DAY_OF_WEEK) == cal.get(Calendar.DAY_OF_WEEK))
                    {
                        if (currCal.get(Calendar.HOUR_OF_DAY) > cal.get(Calendar.HOUR_OF_DAY))
                        {
                            currCal.add(Calendar.WEEK_OF_YEAR, 2);
                        }
                        
                        if (currCal.get(Calendar.HOUR_OF_DAY) == cal.get(Calendar.HOUR_OF_DAY))
                        {
                            if (currCal.get(Calendar.MINUTE) >= cal.get(Calendar.MINUTE))
                            {
                                currCal.add(Calendar.WEEK_OF_YEAR, 2);
                            }
                        }
                    }
                }

                currCal.set(Calendar.DAY_OF_WEEK, cal.get(Calendar.DAY_OF_WEEK));
                currCal.set(Calendar.HOUR_OF_DAY, cal.get(Calendar.HOUR_OF_DAY));
                currCal.set(Calendar.MINUTE, cal.get(Calendar.MINUTE));
                currCal.set(Calendar.SECOND, cal.get(Calendar.SECOND));
                setNextRun(currCal.getTime());
            }
            else
            {
                setNextRun(cal.getTime());
            }
        }
        
        if (getFrequency().equals("monthly"))
        {
            setLastRun(getNextRun());
            cal.setTime(getLastRun());
            cal.add(Calendar.MONTH, 1);
            if (cal.compareTo(currCal) < 0)
            {
                log.info("next monthly run date is in the past, updating to latest schedule");
                // if currently later in the day than usual schedule
                // move to following day
                if (currCal.get(Calendar.DAY_OF_MONTH) > cal.get(Calendar.DAY_OF_MONTH))
                {
                    currCal.add(Calendar.MONTH, 1);
                }
                
                if (currCal.get(Calendar.DAY_OF_MONTH) == cal.get(Calendar.DAY_OF_MONTH))
                {
                    if (currCal.get(Calendar.HOUR_OF_DAY) > cal.get(Calendar.HOUR_OF_DAY))
                    {
                        currCal.add(Calendar.MONTH, 1);
                    }
                    
                    if (currCal.get(Calendar.HOUR_OF_DAY) == cal.get(Calendar.HOUR_OF_DAY))
                    {
                        if (currCal.get(Calendar.MINUTE) >= cal.get(Calendar.MINUTE))
                        {
                            currCal.add(Calendar.MONTH, 1);
                        }
                    }
                }
                
                currCal.set(Calendar.DAY_OF_MONTH, cal.get(Calendar.DAY_OF_MONTH));
                currCal.set(Calendar.HOUR_OF_DAY, cal.get(Calendar.HOUR_OF_DAY));
                currCal.set(Calendar.MINUTE, cal.get(Calendar.MINUTE));
                currCal.set(Calendar.SECOND, cal.get(Calendar.SECOND));
                setNextRun(currCal.getTime());
            }
            else
            {
                setNextRun(cal.getTime());
            }
        }
    }

    
    /**
     * gets an actual run date. This may be different to the actual
     * scheduled run date which is returned by the getNextRun() method.
     * This would typically be called prior to scheduling a job for
     * running or just prior to running as it ensures if the scheduled
     * run date is in the past the job will get run straight away.
     *          
     * @return Date
     *              an actual rather than scheduled run date
     */
    public Date getRunDate()
    {
        Calendar nextRunCal = Calendar.getInstance(TimeZone.getTimeZone("UTC"));
        nextRunCal.setTime(new Date());
        
        if (getNextRun() != null)
        {
            log.info("rundate = " + getNextRun());
            Calendar cal = Calendar.getInstance(TimeZone.getTimeZone("UTC"));
            cal.setTime(getNextRun());
            if (cal.compareTo(nextRunCal) < 0)
            {
                log.info("Next run (" + getNextRun() + ") earlier than current time");
                nextRunCal.add(Calendar.SECOND, 2);
                log.info("Next run time is:" + nextRunCal.getTime());
            }
            else
            {
                nextRunCal = cal;
            }
        }
        
        return nextRunCal.getTime();
    }
}