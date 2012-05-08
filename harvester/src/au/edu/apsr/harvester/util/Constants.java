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
package au.edu.apsr.harvester.util;

/**
 * Application-wide constants
 * 
 * @author Scott Yeadon, ANU
 */
public class Constants
{
    // TODO: add status constants to HashMap as convenience:
    //    public final static HashMap<Integer,String> STATUS = new HashMap<Integer,String>();
    //    static
    //    {
    //       STATUS.put(new Integer(STATUS_COMPLETE), STATUS_COMPLETE_STRING);
    //    }

    /** Harvest status indicating harvest successfully completed */
    public static final int STATUS_COMPLETE = 0;
    /** Harvest status indicating harvest successfully registered
     * (i.e. added to database) */
    public static final int STATUS_REGISTERED = 1;
    /** Harvest status indicating harvest currently running */
    public static final int STATUS_RUNNING = 2;
    /** Harvest status indicating harvest was terminated via user */
    public static final int STATUS_USER_TERMINATED = 3;
    /** Harvest status indicating harvest terminated due to error*/
    public static final int STATUS_ERROR = 4;
    /** Harvest status indicating harvest successfully scheduled */
    public static final int STATUS_SCHEDULED = 5;
    
    /** String form of STATUS_COMPLETE */
    public static final String STATUS_COMPLETE_STRING = "Completed";
    /** String form of STATUS_REGISTERED */
    public static final String STATUS_REGISTERED_STRING = "Queued";
    /** String form of STATUS_RUNNING */
    public static final String STATUS_RUNNING_STRING = "Running";
    /** String form of STATUS_USER_TERMINATED */
    public static final String STATUS_USER_TERMINATED_STRING = "Stopped by User";
    /** String form of STATUS_ERROR */
    public static final String STATUS_ERROR_STRING = "Stopped by Error";
    /** String form of STATUS_SCHEDULED */
    public static final String STATUS_SCHEDULED_STRING = "Scheduled";
    
    /** Mode value for full harvest */
    public static final String MODE_HARVEST = "harvest";
    /** Mode value for harvest test */
    public static final String MODE_TEST = "test";

    /** Default method */
    public static final String METHOD_OAI_PMH = "PMH";
    
    /** Frequency indicating hourly harvest */
    public static final String FREQ_HOURLY = "hourly";
    /** Frequency indicating daily harvest */
    public static final String FREQ_DAILY = "daily";
    /** Frequency indicating weekly harvest */
    public static final String FREQ_WEEKLY = "weekly";
    /** Frequency indicating monthly harvest */
    public static final String FREQ_MONTHLY = "monthly";
}