/**
 * Date Modified: $Date: 2009-09-04 08:44:09 +1000 (Fri, 04 Sep 2009) $
 * Version: $Revision: 131 $
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

import java.net.MalformedURLException;
import java.net.URL;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

import net.handle.hdllib.HandleValue;
import net.handle.hdllib.Util;

import au.edu.apsr.pids.to.Identifier;

/**
 * Utility methods for Handle-related operations
 * 
 * @author Scott Yeadon, ANU
 */
public class HandleSupport
{
    /**
     * create a Handle Value with index 1 for type and value. This
     * should only be used when creating a handle for the first time
     * and that handle has a single value.
     *
     * @return HandleValue[]
     *    An array containing a single HandleValue
     * 
     * @param type
     *          the handle type
     * @param value
     *          the handle value
     */
    public static HandleValue[] createHandleValue(String type,
                                                  String value)
    {
        HandleValue valueArray[] = new HandleValue[1];

        valueArray[0] = new HandleValue();
        valueArray[0].setIndex(1);
        valueArray[0].setType(Util.encodeString(type));
        valueArray[0].setData(Util.encodeString(value));
        valueArray[0].setTTL(Constants.DEFAULT_TTL);
           
        return valueArray;
    }
    

    /**
     * create a Handle Value at the given for type and value. This
     * should only be used when creating a handle for the first time
     * and that handle has a single value.
     * 
     * @return HandleValue[]
     *    An array containing a single HandleValue
     *
     * @param type
     *          the handle type
     * @param value
     *          the handle value
     * @param index
     *          the index the value will occupy
     */
    public static HandleValue[] createHandleValue(String type,
                                                  String value,
                                                  int index)
    {
        HandleValue valueArray[] = new HandleValue[1];

        valueArray[0] = new HandleValue();
        valueArray[0].setIndex(index);
        valueArray[0].setType(Util.encodeString(type));
        valueArray[0].setData(Util.encodeString(value));
        valueArray[0].setTTL(Constants.DEFAULT_TTL);

        return valueArray;
    }

    
    /**
     * determine whether the provided handle type is in the set of types
     * allowed to be created by the mint service.
     *
     * @return boolean
     *  <code>true</code> if type is allowed to be minted else <code>false</code>
     * 
     * @param type
     *          the handle type
     */
    public static boolean isAllowedType(String type)
    {
        boolean found = false;
        
        for(Constants.HandleType ht : Constants.HandleType.values())
        {
            if(type.equals(ht.toString()))
            {
                found = true;
                break;
            }
        }
        
        return found;
    }
    
    
    /**
     * determine whether the provided value is a valid URL
     *
     * @return boolean
     *  <code>true</code> if a valid URL, else <code>false</code>
     * 
     * @param value
     *          the value to check
     */
    public static boolean isValidURL(String value)
    {
        try
        {
            URL url = new URL(value);
        }
        catch (MalformedURLException mue)
        {
            return false;
        }
        
        return true;
    }
    
    
    /**
     * determine whether the provided index is a system reserved value
     *
     * @return boolean
     *  <code>true</code> if reserved, else <code>false</code>
     * 
     * @param index
     *          the index to check
     */
    public static boolean isIndexReserved(int index)
    {
        if ((index >= Constants.IDX_RESERVED_START && index <= Constants.IDX_RESERVED_END)
                || index == Constants.ADMIN_GROUP_IDX || index == Constants.SEC_KEY_IDX)
        {
            return true;
        }
        
        return false;
    }
}