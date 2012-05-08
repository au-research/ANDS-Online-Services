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

import net.handle.hdllib.Util;

import java.util.Map;
import java.util.EnumMap;
/**
 * Application-wide constants
 * 
 * @author Scott Yeadon, ANU
 */
public class Constants
{   
    /** Start of Admin Handle index range, 100 by convention */
    public static final int ADMIN_IDX = 100;

    /** The non-server handle admin index */
    public static final int AGENT_IDX = 101;

    /** The non-server handle admin index */
    public static final int AGENT_DESC_IDX = 102;

    /** The admin group index */
    public static final int ADMIN_GROUP_IDX = 200;
    
    /** Secret Key Index */
    public static final int SEC_KEY_IDX = 300;

    /** Start of PID application reserved index range (inclusive) */
    public static final int IDX_RESERVED_START = 100;

    /** End of PID application reserved index range (inclusive) */
    public static final int IDX_RESERVED_END = 199;
    
    /** Naming Authority prefix */
    public static final String NA_HANDLE_PREFIX = "0.NA/";
    
    /** Separator for key value args in request body */
    public static final String PROPERTY_SEPARATOR = "=";

    /** DESC string in byte form */
    public static final byte XT_TYPE_DESC[] = Util.encodeString("DESC");

    /** AGENTID string in byte form */
    public static final byte XT_AGENTID[] = Util.encodeString("AGENTID");
    
    /** String representing the DESC handle type */
    public static final String XT_TYPE_DESC_STRING = "DESC";
    
    /** String representing the AGENTID handle type */
    public static final String XT_TYPE_AGENTID_STRING = "AGENTID";

    /** String representing the URL handle type */
    public static final String STD_TYPE_URL_STRING = "URL";
    
    /** String representing the HS_ADMIN handle type */
    public static final String STD_TYPE_HSADMIN_STRING = "HS_ADMIN";

    /** Enumeration of handle types able to be created by users */
    public enum HandleType {URL, DESC};
    
    /** name of the authentication domain property */
    public static final String ARG_AUTH_DOMAIN = "authDomain";

    /** name of the authentication domain property */
    public static final String ARG_AUTH_TYPE = "authType";
    
    /** Value of XML system message type attribute */
    public static final String MESSAGE_TYPE_SYSTEM = "system";

    /** Value of XML user message type attribute */
    public static final String MESSAGE_TYPE_USER = "user";
    
    /** Default TTL for handle values, currently 30 minutes */
    public static final int DEFAULT_TTL = 1800;
}