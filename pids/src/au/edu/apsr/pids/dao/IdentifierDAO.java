/**
 * Date Modified: $Date: 2009-08-18 13:22:16 +1000 (Tue, 18 Aug 2009) $
 * Version: $Revision: 89 $
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
package au.edu.apsr.pids.dao;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Timestamp;

import javax.naming.InitialContext;
import javax.naming.NamingException;
import javax.sql.DataSource;

import net.handle.hdllib.Util;

import au.edu.apsr.pids.to.Handle;
import au.edu.apsr.pids.to.Identifier;
import au.edu.apsr.pids.util.Constants;
import au.edu.apsr.pids.util.HandleSupport;
import au.edu.apsr.pids.util.JDBCSupport;

import org.apache.log4j.Logger;

/**
 * Data Access Object for Handle Operations outside the standard handle
 * client API and server operations.
 * 
 * @author Scott Yeadon, ANU 
 */
public class IdentifierDAO
{
    private final Logger log = Logger.getLogger(IdentifierDAO.class);

    private DataSource datasource;
    
    private static final String SELECT_IDENTIFIER_SQL = 
        "SELECT handle, data " +
        "FROM handles WHERE data = ? " +
        "AND type = ?";
    
    /**
     * create an Identifier DAO
     * 
     * @exception DAOException
     */
    public IdentifierDAO() throws DAOException
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
     * obtain an identifier record
     * 
     * @return Identifier
     *           An identifier object if found, null if not found
     *           
     * @param identifier
     *          The identifier
     *          
     * @param authDomain
     *          The authentication domain
     *          
     * @exception DAOException
     */
    public Identifier retrieve(String identifier,
                               String authDomain) throws DAOException
    {
        Connection c = null; 
        
        PreparedStatement ps = null;
        
        ResultSet rs = null;
        
        try
        {
            c = datasource.getConnection();
            c.setAutoCommit(false);
            
            ps = c.prepareStatement(SELECT_IDENTIFIER_SQL);
            ps.setBytes(1, Util.encodeString(identifier + Identifier.separator + authDomain));
            ps.setBytes(2, Constants.XT_TYPE_DESC);
            rs = ps.executeQuery();
            
            Identifier id = null;
            if (rs.next())
            {
                id = new Identifier(identifier, authDomain);
                id.setHandle(Util.decodeString(rs.getBytes("handle")));
//                id.setID(rs.getInt("identifier_id"));
            }
            return id;
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