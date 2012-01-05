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

public class CheckedHandle
{
    private String handle;
    private String value;
    private String errorType;
    
    
    protected CheckedHandle(String hdl, String val, String t)
    {
        handle = hdl;
        value = val;
        errorType = t;
    }
    
    protected String getValue()
    {
        return value;
    }

    protected void setValue(String val)
    {
        value = val;
    }

    protected String getHandle()
    {
        return handle;
    }

    protected void setHandle(String hdl)
    {
        handle = hdl;
    }
    
    protected String getErrorType()
    {
        return errorType;
    }

    protected void setErrorType(String t)
    {
        errorType = t;
    }
}