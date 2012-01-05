/**
 * Date Modified: $Date: 2009-08-18 13:15:13 +1000 (Tue, 18 Aug 2009) $
 * Version: $Revision: 85 $
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
package au.edu.apsr.pids.admin;


/**
 * The core class for initiating various checks.
 * 
 * @author Scott Yeadon, ANU 
 */
public class PIDChecker
{
    // In order to use a cursor to retrieve data you have to set 
    // the ResultSet type of ResultSet.TYPE_FORWARD_ONLY and 
    // autocommit to false in addition to setting a fetch size
    // i.e.
    // conn.setAutoCommit(false);
    //Statement st = conn.createStatement();
    // Turn use of the cursor on.
    // st.setFetchSize(50);

    protected void check()
    {
        // start the link checking thread. In future may need to
        // create multiple threads and perform concurrent link checking
        LinkCheckerThreadManager lctm = LinkCheckerThreadManager.getThreadManager();
        lctm.checkURLs();
    }
}