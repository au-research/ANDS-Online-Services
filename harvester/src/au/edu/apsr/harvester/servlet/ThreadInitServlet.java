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
package au.edu.apsr.harvester.servlet;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;

import au.edu.apsr.harvester.dao.DAOException;
import au.edu.apsr.harvester.thread.ThreadManager;

import org.apache.log4j.Logger;

/**
 * Servlet which is configured in web.xml to reschedule all harvests when
 * the servlet container is restarted. This is for recovery of schedules
 * in the event the servlet container is shut down for maintenance, error, etc.
 * 
 * @author Scott Yeadon, ANU 
 */
public class ThreadInitServlet extends HttpServlet
{
    private final Logger log = Logger.getLogger(ThreadInitServlet.class);
    
    private ThreadManager threadManager = null;
    
    /**
     * Destruction of the servlet
     */
    public void destroy()
    {
        super.destroy();
    }
    
    /**
     * obtain the ThreadManager
     * 
     * @exception ServletException
     */    
    public void init() throws ServletException
    {
        threadManager = ThreadManager.getThreadManager();
        try
        {
            threadManager.init();
        }
        catch (DAOException daoe)
        {
            log.error("DAOException occurred", daoe);
            throw new ServletException(daoe);
        }
    }
}