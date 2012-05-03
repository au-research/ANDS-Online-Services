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

import javax.naming.InitialContext;
import javax.naming.NamingException;
import javax.sql.DataSource;

import org.apache.log4j.Logger;

import au.edu.apsr.harvester.dao.DAOException;
import au.edu.apsr.harvester.to.Request;
import au.edu.apsr.harvester.util.JDBCSupport;

/**
 * Data Access Object for requests
 * 
 * @author Scott Yeadon, ANU 
 */
public class RequestDAO
{
    private final Logger log = Logger.getLogger(RequestDAO.class);

    private DataSource datasource;
    
    private static final String SELECT_REQUEST_SQL = 
        "SELECT request_id, request FROM request WHERE request = ?";
        
    /**
     * create a Request DAO
     * 
     * @exception DAOException
     */
    public RequestDAO() throws DAOException
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
            log.error("NamingException occurred", ne);
            throw new DAOException(ne);
        }
    }
    
    
    /**
     * obtain the request object associated with an OAI-PMH verb 
     * 
     * @param request
     *          An OAI-PMH verb
     *          
     * @return Request
     *          the request object associated with the verb, else null
     *          
     * @exception DAOException
     */
    public Request retrieve(String request) throws DAOException
    {
        Connection c = null;        
        PreparedStatement ps = null;        
        ResultSet rs = null;
        
        try
        {
            c = datasource.getConnection();
            c.setAutoCommit(false);
            ps = c.prepareStatement(SELECT_REQUEST_SQL);
            ps.setString(1, request);
            rs = ps.executeQuery();
            Request verb = null;
            if (rs.next())
            {
                verb = new Request(request);
                verb.setID(rs.getInt("request_id"));
            }

            return verb;
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