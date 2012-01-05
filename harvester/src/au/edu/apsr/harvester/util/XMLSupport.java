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

import java.io.StringWriter;
import java.math.BigInteger;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Date;
import java.util.Iterator;
import java.util.Map;
import java.util.TimeZone;

import org.w3c.dom.Document;
import org.w3c.dom.Element;

import javax.xml.parsers.DocumentBuilderFactory;
import javax.xml.parsers.DocumentBuilder;
import javax.xml.transform.dom.DOMSource;
import javax.xml.transform.stream.StreamResult;
import javax.xml.transform.TransformerFactory;
import javax.xml.transform.Transformer;

/**
 * Utility methods for XML processing
 * 
 * @author Scott Yeadon, ANU 
 */
public class XMLSupport
{
    /** Value of XML RESPONSE_TYPE_ATTRIBUTE to indicate a failed request */
    public static final String RESPONSE_TYPE_FAILURE = "failure";
    /** Value of XML RESPONSE_TYPE_ATTRIBUTE to indicate a successful request */
    public static final String RESPONSE_TYPE_SUCCESS = "success";
    
    /** Name of XML root element of service response */
    public static final String RESPONSE_ELEMENT = "response";
    /** Name of XML timestamp element */
    public static final String RESPONSE_TIMESTAMP_ELEMENT = "timestamp";
    /** Name of XML response type attribute */
    public static final String RESPONSE_TYPE_ATTRIBUTE = "type";
    /** Name of XML message element */
    public static final String RESPONSE_MESSAGE_ELEMENT = "message";
    /** Name of XML properties element */
    public static final String RESPONSE_PROPERTIES_ELEMENT = "properties";
    /** Name of XML property element */
    public static final String RESPONSE_PROPERTY_ELEMENT = "property";
    /** Name of XML property property name attribute */
    public static final String RESPONSE_NAME_ATTRIBUTE = "name";
    /** Name of XML response property value attribute */
    public static final String RESPONSE_VALUE_ATTRIBUTE = "value";
    
    /** Java Date Format for UTC dates used within the harvester application */
    public static final String TIMESTAMP_UTC_FORMAT = "yyyy-MM-dd'T'HH:mm:ss'Z'";
    
    /**
     * create an XML document representing a failed or successful
     * service call.
     * 
     * @param type
     *              'success' or 'failure'
     * @param messageString
     *              a message string to form part of the XML response
     * @param propertyMap
     *              a map containing name value pairs which will form
     *              part of the XML response (can be null)
     *              
     * @return String
     *              an XML string
     */
    public static String getXMLResponse(String type,
                                        String messageString,
                                        Map<String,String> propertyMap) throws Exception
    {
        try
        {
            // create a DocumentBuilderFactory
            DocumentBuilderFactory factory = DocumentBuilderFactory.newInstance();
            
            // create a DocumentBuilder (DOM Parser)
            DocumentBuilder builder = factory.newDocumentBuilder();
            
            // create an EMPTY XML document for the output
            Document doc = builder.newDocument();
            
            Element root = doc.createElement(RESPONSE_ELEMENT);
            root.setAttribute(RESPONSE_TYPE_ATTRIBUTE, type);
            
            SimpleDateFormat sdf = new SimpleDateFormat(TIMESTAMP_UTC_FORMAT);
            Element timestamp = doc.createElement(RESPONSE_TIMESTAMP_ELEMENT);
            timestamp.setTextContent(sdf.format(new Date()));
                        
            Element message = doc.createElement(RESPONSE_MESSAGE_ELEMENT);
            message.setTextContent(messageString);
            
            if (propertyMap != null)
            {
                Element properties = doc.createElement(RESPONSE_PROPERTIES_ELEMENT);

                for (Iterator<String> i = propertyMap.keySet().iterator(); i.hasNext();)
                {
                    String key = i.next();
                    Element property = doc.createElement(RESPONSE_PROPERTY_ELEMENT);
                    property.setAttribute("name", key);
                    property.setAttribute("value", propertyMap.get(key));
                    properties.appendChild(property);
                }
                
                root.appendChild(properties);
            }
            
            root.appendChild(timestamp);
            root.appendChild(message);
            doc.appendChild(root);
            
            // Create dom source for the document
            DOMSource domSource = new DOMSource(doc);
            
            // Create a string writer
            StringWriter stringWriter = new StringWriter();
            
            // Create the result stream for the transform
            StreamResult result = new StreamResult(stringWriter);
            
            // Create a Transformer to serialize the document
            TransformerFactory tf = TransformerFactory.newInstance();
            Transformer transformer = tf.newTransformer();
            transformer.setOutputProperty("indent","yes");
            
            // Transform the document to the result stream
            transformer.transform(domSource, result);
            
            return stringWriter.toString();
        }
        catch (Exception e)
        {
            throw new Exception(e);
        }
    }
    
    
    /** 
     * Calculate the SHA1 for the ip
     * 
     * @return String
     *          the SHA1 hash as a string
     */    
    public static String generateHash(String string) throws NoSuchAlgorithmException
    {
        MessageDigest md = MessageDigest.getInstance("SHA1");
        md.update(string.getBytes(), 0, string.length());
        return new BigInteger(1, md.digest()).toString(16);
    }
    
 
    /** 
     * Format a date time to meet xsd:dateTime requirements. Timezone
     * is unknown so not 100% correct.
     * 
     * @return String
     *          the dateTime string
     */
    public static String dateTime(String date,
                                  String fromTo)
{
        Calendar cal = Calendar.getInstance(TimeZone.getTimeZone("UTC"));

        if (date.length() < 4)
        {
            return "";
        }

        if (date.matches("[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}[Z]{0,1}"))
        {
            return date;
        }

        if (date.matches("^[0-9]{4}$")) // year only
        {
            cal.set(Calendar.YEAR, Integer.valueOf(date));
            if (fromTo.equals("from"))
            {
                cal.set(Calendar.MONTH,0);
                cal.set(Calendar.DAY_OF_MONTH,1);
                cal.set(Calendar.HOUR_OF_DAY,0);
                cal.set(Calendar.MINUTE,0);
                cal.set(Calendar.SECOND,0);
            }
            else
            {
                cal.set(Calendar.MONTH,11);
                cal.set(Calendar.DAY_OF_MONTH,31);
                cal.set(Calendar.HOUR_OF_DAY,23);
                cal.set(Calendar.MINUTE,59);
                cal.set(Calendar.SECOND,59);                
            }
        }
        else if (date.matches("^[0-9]{4}-[0-9]{2}$")) // yyyy-mm
        {
            cal.set(Calendar.YEAR, Integer.valueOf(date.substring(0,4)));
            cal.set(Calendar.MONTH,Integer.valueOf(date.substring(5)) - 1);
            if (fromTo.equals("from"))
            {
                cal.set(Calendar.DAY_OF_MONTH,1);
                cal.set(Calendar.HOUR_OF_DAY,0);
                cal.set(Calendar.MINUTE,0);
                cal.set(Calendar.SECOND,0);
            }
            else
            {
                cal.set(Calendar.DAY_OF_MONTH,cal.getActualMaximum(Calendar.DAY_OF_MONTH));
                cal.set(Calendar.HOUR_OF_DAY,23);
                cal.set(Calendar.MINUTE,59);
                cal.set(Calendar.SECOND,59);                
            }
        }
        else if (date.matches("^[0-9]{4}-[0-9]{2}-[0-9]{2}$")) // yyyy-mm-dd
        {
            cal.set(Calendar.YEAR, Integer.valueOf(date.substring(0,4)));
            cal.set(Calendar.MONTH,Integer.valueOf(date.substring(5,7)) - 1);
            cal.set(Calendar.DAY_OF_MONTH,Integer.valueOf(date.substring(8)));
            if (fromTo.equals("from"))
            {
                cal.set(Calendar.HOUR_OF_DAY,0);
                cal.set(Calendar.MINUTE,0);
                cal.set(Calendar.SECOND,0);
            }
            else
            {
                cal.set(Calendar.HOUR_OF_DAY,23);
                cal.set(Calendar.MINUTE,59);
                cal.set(Calendar.SECOND,59);                                
            }
        }
        else
        {
            return "";
        }      

        SimpleDateFormat df = new SimpleDateFormat("yyyy-MM-dd'T'HH:mm:ss'Z'");
        df.setCalendar(cal);
        return df.format(cal.getTime());
    }
}