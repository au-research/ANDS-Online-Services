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

import javax.naming.InitialContext;
import javax.naming.NamingException;
import javax.sql.DataSource;

import java.sql.Connection;
import java.sql.SQLException;

import au.edu.apsr.harvester.dao.DAOException;

import org.apache.log4j.Logger;

/**
 * Class not used - earmarked for future use
 * 
 * @author Scott Yeadon, ANU 
 */
public class DAOManager
{
    private static DataSource datasource;
    private static final Logger log = Logger.getLogger(DAOManager.class);

    static
    {
        try
        {
            InitialContext ic = new InitialContext();
            if (ic == null)
            {
                log.error("Unable to instantiate Initial Context Object");
                throw new RuntimeException("Unable to instantiate Initial Context Object");
            }
    
            datasource = (DataSource)ic.lookup(DAOConstants.DAO_DATASOURCE);
            if (datasource == null)
            {
                log.error("Unable to locate datasource: " + DAOConstants.DAO_DATASOURCE);
                throw new RuntimeException("Unable to locate datasource: " + DAOConstants.DAO_DATASOURCE);
            }
        }
        catch (NamingException ne)
        {
            log.error("Naming Exception: " + ne.getMessage());
            throw new RuntimeException("Naming Exception: " + ne.getMessage());
        }
    }

    
    public static Connection getConnection() throws DAOException
    {
        try
        {
            Connection c = datasource.getConnection();
            c.setAutoCommit(false);
            return c;
        }
        catch (SQLException e)
        {
            log.error(e);
            throw new DAOException(e.getCause());
        }
    }
    
    
    public static void commit(Connection c) throws DAOException
    {
        try
        {
            c.commit();
        }
        catch (SQLException e)
        {
            log.error(e);
            throw new DAOException(e.getCause());
        }
    }
}