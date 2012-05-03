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
package au.edu.apsr.harvester.util;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Date;
import java.util.Map;
import java.util.TimeZone;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServletResponse;

/**
 * utility methods for servlets
 * 
 * @author Scott Yeadon, ANU 
 */
public class ServletSupport
{
    /**
     * prepare an XML response for successful service call
     * 
     * @param response
     *              an HTTP response
     * @param message
     *              a message string to form part of the XML response
     * @param propertyMap
     *              a map containing name value pairs which will form
     *              part of the XML response
     *              
     * @throws ServletException
     */
    public static void doSuccessResponse(HttpServletResponse response,
            String message,
            Map<String,String> propertyMap) throws ServletException
    {
        try
        {
            response.setContentType("application/xml");
            response.getOutputStream().print(XMLSupport.getXMLResponse(XMLSupport.RESPONSE_TYPE_SUCCESS, message, propertyMap));
        }
        catch (Exception e)
        {
            throw new ServletException(e);
        }
    }
    
    
    /**
     * prepare an XML response for failed service call
     * 
     * @param response
     *              an HTTP response
     * @param message
     *              a message string to form part of the XML response
     * @param propertyMap
     *              a map containing name value pairs which will form
     *              part of the XML response
     *              
     * @throws ServletException
     */
    public static void doErrorResponse(HttpServletResponse response,
            String message,
            Map<String,String> propertyMap) throws ServletException
    {
        try
        {
            response.setContentType("application/xml");
            response.getOutputStream().print(XMLSupport.getXMLResponse(XMLSupport.RESPONSE_TYPE_FAILURE, message, propertyMap));
        }
        catch (Exception e)
        {
            throw new ServletException(e);
        }
    }
    
    
    /**
     * prepare an XML response for successful service call
     * 
     * @param response
     *              an HTTP response
     * @param message
     *              a message string to form part of the XML response
     *              
     * @throws ServletException
     */
    public static void doSuccessResponse(HttpServletResponse response,
            String message) throws ServletException
    {
        doSuccessResponse(response, message, null);
    }
    
    
    /**
     * prepare an XML response for failed service call
     * 
     * @param response
     *              an HTTP response
     * @param message
     *              a message string to form part of the XML response
     *              
     * @throws ServletException
     */
    public static void doErrorResponse(HttpServletResponse response,
            String message) throws ServletException
    {
        doErrorResponse(response, message, null);
    }
    
    
    /**
     * Get a Date in string format of the form YYYY-MM-DDThh:mm:ssZ
     * 
     * @param date
     *              The date object to convert to a string
     *
     * @return String
     *              The date in YYYY-DD-MMThh:mm:ss format
     */
    public static String getUTCString(Date date)
    {
        String dateTime = "";        
        if (date != null)
        {
            SimpleDateFormat df = new SimpleDateFormat(XMLSupport.TIMESTAMP_UTC_FORMAT);
            Calendar cal = Calendar.getInstance(TimeZone.getTimeZone("UTC"));
            cal.setTime(date);
            df.setCalendar(cal);
            dateTime = df.format(cal.getTime());
        }

        return dateTime;
    }
    
    
    /**
     * Given a date format string, return a date object
     * 
     * @param dateFormat
     *              the format of the date string according to
     *              java DateFormat rules
     * @param dateString
     *              the string representing a date/time
     *
     * @return Date
     *              The date object representative of the string
     */
    public static Date getDate(String dateFormat,
                               String dateString) throws ParseException
    {
        SimpleDateFormat df = new SimpleDateFormat(dateFormat);
        Calendar cal = Calendar.getInstance(TimeZone.getTimeZone("UTC"));
        df.setCalendar(cal);
        return df.parse(dateString);
    }
}