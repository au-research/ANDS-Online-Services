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

import au.edu.apsr.harvester.dao.DAOException;
import au.edu.apsr.harvester.dao.RequestDAO;

/**
 * Class representing a harvest request (i.e. an OAI-PMH verb)
 * 
 * @author Scott Yeadon, ANU 
 */
public class Request
{
    private String request = null;
    private int requestID = -1;
    
    /**
     * create a request
     * 
     * @param request
     *          an OAI-PMH verb
     */
    public Request(String request)
    {
        this.request = request;
    }

    
    /**
     * get the request id
     * 
     * @return int
     *          the id for this request
     */
    public int getID()
    {
        return this.requestID;
    }
    

    /**
     * set the requestID
     * 
     * @param requestID
     *          the requestID
     */
    public void setID(int requestID)
    {
       this.requestID = requestID;
    }
    
    
    /**
     * create a request object from its database record
     * 
     * @param request
     *          an OAI-PMH verb
     *          
     * @return Request
     *          the request object or null
     *          
     * @throws DAOException
     */
    public static Request find(String request) throws DAOException
    {
        return new RequestDAO().retrieve(request);
    }
}