/**
 * Date Modified: $Date: 2010-11-15 13:38:09 +1100 (Mon, 15 Nov 2010) $
 * Version: $Revision: 559 $
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

import java.io.StringWriter;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.Calendar;
import java.util.TimeZone;
import java.util.Iterator;
import java.util.List;
import java.util.Map;

import org.w3c.dom.Document;
import org.w3c.dom.Element;

import javax.xml.parsers.DocumentBuilderFactory;
import javax.xml.parsers.DocumentBuilder;
import javax.xml.transform.dom.DOMSource;
import javax.xml.transform.stream.StreamResult;
import javax.xml.transform.TransformerFactory;
import javax.xml.transform.Transformer;

import net.handle.hdllib.HandleValue;

import au.edu.apsr.pids.servlet.MintServlet;
import au.edu.apsr.pids.to.Handle;
import au.edu.apsr.pids.util.Constants;
import au.edu.apsr.pids.to.TrustedClient;

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
    
    /** Name of XML identifiers element */
    public static final String RESPONSE_IDENTIFIERS_ELEMENT = "identifiers";

    /** Name of XML identifier element */
    public static final String RESPONSE_IDENTIFIER_ELEMENT = "identifier";
	
	/** Name of XML trustedclients element */
    public static final String RESPONSE_TRUSTEDCLIENTS_ELEMENT = "trustedclients";
	
	/** Name of XML client element */
    public static final String RESPONSE_CLIENT_ELEMENT = "client";
	
	/** Name of XML appId attribute */
    public static final String RESPONSE_APPID_ATTRIBUTE = "appId";
	
	/** Name of XML IP attribute */
    public static final String RESPONSE_IP_ATTRIBUTE = "ip";
	
	/** Name of XML DESCRIPTION attribute */
    public static final String RESPONSE_DESCRIPTION_ATTRIBUTE = "desc";
    
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
     * @param messageCategory
     *              the class of message
     * @param clientMap
     *              a map containing appId, IP and description of clients
     *              part of the XML response (can be null)
     *              
     * @return String
     *              an XML string
     */
    public static String getXMLResponse(String type,
                                        String messageString,
                                        String messageCategory,
                                        ArrayList<TrustedClient> clientMap) throws Exception
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
            
            Element timestamp = doc.createElement(RESPONSE_TIMESTAMP_ELEMENT);
            SimpleDateFormat sdf = new SimpleDateFormat(TIMESTAMP_UTC_FORMAT);
            Calendar cal = Calendar.getInstance(TimeZone.getTimeZone("UTC"));
            sdf.setCalendar(cal);
            timestamp.setTextContent(sdf.format(cal.getTime()));
                        
            Element message = doc.createElement(RESPONSE_MESSAGE_ELEMENT);
            message.setAttribute(RESPONSE_TYPE_ATTRIBUTE, messageCategory);
            message.setTextContent(messageString);
            
            if (clientMap != null)
            {
                Element clients = doc.createElement(RESPONSE_TRUSTEDCLIENTS_ELEMENT);

                for (TrustedClient tc : clientMap)
                {
                    Element client = doc.createElement(RESPONSE_CLIENT_ELEMENT);
                    client.setAttribute("appId", tc.getAppId());
                    client.setAttribute("ip", tc.getIP());
					client.setAttribute("desc", tc.getDescription());
                    clients.appendChild(client);
                }
                
                root.appendChild(clients);
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
     * create an XML document representing a failed or successful
     * service call.
     * 
     * @param type
     *              'success' or 'failure'
     * @param messageString
     *              a message string to form part of the XML response
     * @param messageCategory
     *              the class of message
     * @param propertyMap
     *              a map containing name value pairs which will form
     *              part of the XML response (can be null)
     *              
     * @return String
     *              an XML string
     */
    public static String getXMLResponse(String type,
                                        String messageString,
                                        String messageCategory,
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
            
            Element timestamp = doc.createElement(RESPONSE_TIMESTAMP_ELEMENT);
            SimpleDateFormat sdf = new SimpleDateFormat(TIMESTAMP_UTC_FORMAT);
            Calendar cal = Calendar.getInstance(TimeZone.getTimeZone("UTC"));
            sdf.setCalendar(cal);
            timestamp.setTextContent(sdf.format(cal.getTime()));
                        
            Element message = doc.createElement(RESPONSE_MESSAGE_ELEMENT);
            message.setAttribute(RESPONSE_TYPE_ATTRIBUTE, messageCategory);
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
     * create an XML document representing a failed or successful
     * listHandles service call
     * 
     * @param type
     *              'success' or 'failure'
     * @param messageString
     *              a message string to form part of the XML response
     * @param messageCategory
     *              the class of message
     * @param handleList
     *              a list of handle objects
     *              
     * @return String
     *              an XML string
     */
    public static String getXMLListStringsResponse(String type,
            String messageString,
            String messageCategory,
            List<String> handleList) throws Exception
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
            
            Element timestamp = doc.createElement(RESPONSE_TIMESTAMP_ELEMENT);
            SimpleDateFormat sdf = new SimpleDateFormat(TIMESTAMP_UTC_FORMAT);
            Calendar cal = Calendar.getInstance(TimeZone.getTimeZone("UTC"));
            sdf.setCalendar(cal);
            timestamp.setTextContent(sdf.format(cal.getTime()));
            
            Element message = doc.createElement(RESPONSE_MESSAGE_ELEMENT);
            message.setAttribute(RESPONSE_TYPE_ATTRIBUTE, messageCategory);
            message.setTextContent(messageString);
            
            if (handleList.size() > 0)
            {
                Element identifiers = doc.createElement(RESPONSE_IDENTIFIERS_ELEMENT);

                for (Iterator<String> i = handleList.iterator(); i.hasNext();)
                {
                    Element identifier = doc.createElement(RESPONSE_IDENTIFIER_ELEMENT);                    
                    identifier.setAttribute("handle", i.next());
                    identifiers.appendChild(identifier);
                }
                root.appendChild(identifiers);
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
     * create an XML document representing a failed or successful
     * listHandles service call
     * 
     * @param type
     *              'success' or 'failure'
     * @param messageString
     *              a message string to form part of the XML response
     * @param messageCategory
     *              the class of message
     * @param handleList
     *              a list of handle objects
     *              
     * @return String
     *              an XML string
     */
    public static String getXMLListHandlesResponse(String type,
            String messageString,
            String messageCategory,
            List<Handle> handleList) throws Exception
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
            
            Element timestamp = doc.createElement(RESPONSE_TIMESTAMP_ELEMENT);
            SimpleDateFormat sdf = new SimpleDateFormat(TIMESTAMP_UTC_FORMAT);
            Calendar cal = Calendar.getInstance(TimeZone.getTimeZone("UTC"));
            sdf.setCalendar(cal);
            timestamp.setTextContent(sdf.format(cal.getTime()));
            
            Element message = doc.createElement(RESPONSE_MESSAGE_ELEMENT);
            message.setAttribute(RESPONSE_TYPE_ATTRIBUTE, messageCategory);
            message.setTextContent(messageString);
            
            if (handleList.size() > 0)
            {
                Element identifiers = doc.createElement(RESPONSE_IDENTIFIERS_ELEMENT);

                for (Iterator<Handle> i = handleList.iterator(); i.hasNext();)
                {
                    Element identifier = doc.createElement(RESPONSE_IDENTIFIER_ELEMENT);
                    identifier.setAttribute("handle", i.next().getHandle());
                    identifiers.appendChild(identifier);
                }
                root.appendChild(identifiers);
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
     * create an XML document representing a failed or successful
     * getHandle service call
     * 
     * @param type
     *              'success' or 'failure'
     * @param messageString
     *              a message string to form part of the XML response
     * @param messageCategory
     *              the class of message
     * @param handleList
     *              a list of handle objects
     *              
     * @return String
     *              an XML string
     */
    public static String getXMLGetHandleResponse(String type,
            String messageString,
            String messageCategory,
            List<Handle> handleList) throws Exception
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
            
            Element timestamp = doc.createElement(RESPONSE_TIMESTAMP_ELEMENT);
            SimpleDateFormat sdf = new SimpleDateFormat(TIMESTAMP_UTC_FORMAT);
            Calendar cal = Calendar.getInstance(TimeZone.getTimeZone("UTC"));
            sdf.setCalendar(cal);
            timestamp.setTextContent(sdf.format(cal.getTime()));
            
            Element message = doc.createElement(RESPONSE_MESSAGE_ELEMENT);
            message.setAttribute(RESPONSE_TYPE_ATTRIBUTE, messageCategory);
            message.setTextContent(messageString);
            if (handleList.size() > 0)
            {
                Element identifier = doc.createElement(RESPONSE_IDENTIFIER_ELEMENT);                    
                Handle handle = handleList.iterator().next();
                identifier.setAttribute("handle", handle.getHandle());
                
                String[] types = {Constants.STD_TYPE_URL_STRING, Constants.XT_TYPE_DESC_STRING};
                HandleValue[] hv = handle.getValues(types);                
                
                if (hv.length > 0)
                {
                    for (int j = 0; j < hv.length; j++)
                    {
//                        if (hv[j].getTypeAsString().equals(Constants.STD_TYPE_URL_STRING) || hv[j].getTypeAsString().equals(Constants.XT_TYPE_DESC_STRING))
//                        {
                            Element property = doc.createElement(RESPONSE_PROPERTY_ELEMENT);                    
                            property.setAttribute("index", String.valueOf(hv[j].getIndex()));
                            property.setAttribute("type", String.valueOf(hv[j].getTypeAsString()));
                            property.setAttribute("value", String.valueOf(hv[j].getDataAsString()));
                            identifier.appendChild(property);
//                        }
                    }
                }
                root.appendChild(identifier);
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
}