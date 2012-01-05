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
package au.edu.apsr.pids.util;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

/**
 * Utility methods for JDBC-related operations
 * 
 * @author Scott Yeadon, ANU 
 */
public class JDBCSupport
{
    /**
     * close some jdbc objects
     * 
     * @param rs
     *          a ResultSet
     * @param ps
     *          a PreparedStatement
     * @param c
     *          a Connection
     */
    public static void closeObjects(ResultSet rs,
                                    PreparedStatement ps,
                                    Connection c)
    {
        if (rs != null)
        {
            try
            {
                rs.close();
            }
            catch (SQLException sqle){ ; }
            rs = null;
        }
        
        if (ps != null)
        {
            try
            {
                ps.close();
            }
            catch (SQLException sqle) { ; }
            ps = null;
        }
        
        if (c != null)
        {
            try
            {
                c.close();
            }
            catch (SQLException sqle) { ; }
            c = null;
        }
    }
    
    
    /**
     * close some jdbc objects
     * 
     * @param ps
     *          a PreparedStatement
     * @param c
     *          a Connection
     */
    public static void closeObjects(PreparedStatement ps,
                                    Connection c)
    {
        if (ps != null)
        {
            try
            {
                ps.close();
            }
            catch (SQLException sqle) { ; }
            ps = null;
        }
        
        if (c != null)
        {
            try
            {
                c.close();
            }
            catch (SQLException sqle) { ; }
            c = null;
        }
    }

    
    /**
     * close some jdbc objects
     * 
     * @param ps
     *          a PreparedStatement
     */
    public static void closeObjects(PreparedStatement ps)
    {
        if (ps != null)
        {
            try
            {
                ps.close();
            }
            catch (SQLException sqle) { ; }
            ps = null;
        }
    }
}