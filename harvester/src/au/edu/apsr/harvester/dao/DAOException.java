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

/**
 * An exception class for exceptions in DAO code
 * 
 * @author Scott Yeadon, ANU 
 */
public class DAOException extends Exception
{
    /**
     * create a DAOException
     * 
     * @param reason
     *            string describing reason for the exception
     */
    public DAOException(String reason)
    {
        super(reason);
    }
    
    
    /**
     * create a DAOException
     * 
     * @param reason
     *            string describing reason for the exception
     * @param cause
     *            A Throwable describing the cause of the exception
     */
    public DAOException(String reason,
                        Throwable cause)
    {
        super(reason, cause);
    }
    
    
    /**
     * create a DAOException
     * 
     * @param cause
     *            A Throwable describing the cause of the exception
     */
    public DAOException(Throwable cause)
    {
        super(cause);
    }
    
    
    /**
     * Obtain the reason for the exception
     *
     * @return String
     *          The message text of the exception
     */
    public String getMessage()
    {
        return super.getMessage();
    }
}