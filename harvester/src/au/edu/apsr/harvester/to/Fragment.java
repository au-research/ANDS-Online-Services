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

import java.sql.Timestamp;

import au.edu.apsr.harvester.dao.DAOException;
import au.edu.apsr.harvester.dao.FragmentDAO;

/**
 * Class representing a harvested fragment
 * 
 * @author Scott Yeadon, ANU 
 */
public class Fragment
{
    private String harvestID = null;
    private String text = null;
    private int requestID = -1;
    private Timestamp dateStored = null;
    
    public Fragment()
    {
        
    }
    
    /**
     * set the harvest id for this fragment
     * 
     * @param harvestID
     *          the id of the harvest this fragment belongs to
     */
    public void setHarvestID(String harvestID)
    {
        this.harvestID = harvestID;
    }
    
    
    /**
     * get the harvest id for this fragment
     * 
     * @return String
     *          the id of the harvest this fragment belongs to
     */
    public String getHarvestID()
    {
        return this.harvestID;
    }
    
    
    /**
     * set the request id for this fragment
     * 
     * @param requestID
     *          the id of the request (PMH verb) this fragment
     *          was harvested from
     */
    public void setRequestID(int requestID)
    {
        this.requestID = requestID;
    }
    
    
    /**
     * get the request id for this fragment
     * 
     * @return int
     *          the id of the request (PMH verb) this fragment
     *          was harvested from
     */
    public int getRequestID()
    {
        return this.requestID;
    }
    
    
    /**
     * set the fragment content
     * 
     * @param text
     *          the fragment content
     */
    public void setText(String text)
    {
        this.text = text;
    }
    
    
    /**
     * get the fragment content
     * 
     * @return String
     *          the fragment content
     */
    public String getText()
    {
        return this.text;
    }
    
    
    /**
     * create a new fragment record
     * 
     * @throws DAOException
     */
    public void create() throws DAOException
    {
        FragmentDAO fdao = new FragmentDAO();
        fdao.create(this);
    }
}