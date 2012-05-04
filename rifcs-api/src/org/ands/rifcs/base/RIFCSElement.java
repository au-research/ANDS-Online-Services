/**
 * Date Modified: $Date: 2010-01-19 16:00:56 +1100 (Tue, 19 Jan 2010) $
 * Version: $Revision: 290 $
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
package org.ands.rifcs.base;

import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Date;
import java.util.List;
import java.util.TimeZone;

import org.w3c.dom.Element;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;

/**
 * super class of all RIF-CS elements
 * 
 * @author Scott Yeadon
 *
 */
public class RIFCSElement
{
    private Element e = null;
    
    /**
     * Construct a RIF-CS element
     * 
     * @param n 
     *        A w3c Node, typically an Element
     * @param name 
     *        The name of the RIF-CS Element
     *        
     * @exception RIFCSException
     *
     */ 
    protected RIFCSElement(Node n,
                           String name) throws RIFCSException
    {
        if (n == null)
        {
            throw new RIFCSException("Null Node passed to constructor");
        }

        if (n instanceof Element)
        {
            if (!n.getNodeName().endsWith(name))
            {
                throw new RIFCSException("Mismatch tag name. Node tag is: " + n.getNodeName() + ", expected: " + name);
            }
        }
        else
        {
            throw new RIFCSException("Node of type Element required in constructor");
        }

        e = (Element)n;
    }


    /**
     * Obtain an attribute value
     * 
     * @param name
     *      The name of the attribute
     *      
     * @return
     *      The attribute value or empty string if attribute
     *      is empty or not present
     */  
    protected String getAttributeValue(String name)
    {
        return e.getAttribute(name);
    }


    /**
     * Set an attribute value
     * 
     * @param name
     *      The name of the attribute
     *      
     * @param value 
     *      The attribute value
     */  
    protected void setAttributeValue(String name,
                                     String value)
    {
        e.setAttribute(name, value);
    }


    /**
     * Set an attribute value with namespace
     * 
     * @param ns
     *      The namespace URL of the attribute
     * @param name 
     *      The attribute name
     * @param value 
     *      The attribute value
     */  
    protected void setAttributeValueNS(String ns,
                                        String name,
                                     String value)
    {
        e.setAttributeNS(ns, name, value);
    }
    

    /**
     * Get an attribute value with namespace
     * 
     * @param ns
     *      The namespace URL of the attribute
     * @param name 
     *      The attribute name
     */  
    protected String getAttributeValueNS(String ns,
                                         String name)
    {
        return e.getAttributeNS(ns, name);
    }
    
    
    /**
     * Obtain an attribute value where the attribute has a
     * namespace
     * 
     * @param ns
     *      The attribute namespace URL
     * @param localName
     *      The unqualified attribute name
     *      
     * @return String 
     *      The attribute value or empty string if attribute
     *      is empty or not present
     */  
    protected String getAttributeValue(String ns,
                                       String localName)
    {
        return e.getAttributeNS(ns, localName);
    }
    
    
    /**
     * Obtain the text content of the RIFCS Element
     * 
     * @return 
     *      The text content of the element
     */  
    protected String getTextContent()
    {
        return e.getTextContent();
    }
    
    
    /**
     * Set the text content of the RIFCS Element
     * 
     * @param value 
     *      The text content of the element
     */  
    protected void setTextContent(String value)
    {
        e.setTextContent(value);
    }

    
    /**
     * Obtain a list of descendant RIFCS elements with the given name
     * 
     * @param localName
     *      Obtain a list of descendant METS elements
     *      
     * @return 
     *      The text content of the element
     */  
    protected NodeList getElements(String localName)
    {
        return e.getElementsByTagNameNS(Constants.NS_RIFCS, localName);
    }
    
    
    /**
     * Obtain a list of child RIFCS elements
     * 
     * @param localName
     *      An element name
     *      
     * @return 
     *      A list of RIFCS elements whose tag name matches
     *      the localName
     */  
    protected List<Node> getChildElements(String localName)
    {
        NodeList nl = e.getChildNodes();
        List<Node> l = new ArrayList<Node>();
        for (int i = 0; i < nl.getLength(); i++)
        {
            if (nl.item(i).getNodeType() == Node.ELEMENT_NODE &&
                nl.item(i).getLocalName().equals(localName))
            {
                l.add(nl.item(i));
            }
        }
        
        return l;
    }
    
    
    /**
     * Obtain all child elements
     *       
     * @return 
     *      A list of RIFCS elements
     */  
    protected List<Node> getChildElements()
    {
        NodeList nl = e.getChildNodes();
        List<Node> l = new ArrayList<Node>();
        for (int i = 0; i < nl.getLength(); i++)
        {
            if (nl.item(i).getNodeType() == Node.ELEMENT_NODE)
            {
                l.add(nl.item(i));
            }
        }
        
        return l;
    }

    
    /**
     * Obtain the w3c dom element this object represents
     * 
     * @return 
     *      A w3c dom element
     */  
    protected Element getElement()
    {
        return e;
    }
    
    
    /**
     * Return null, this class should be overridden by subclasses if sub-elements
     * are permitted
     * 
     * @return
     *      an element with the given name
     */  
    protected Element newElement(String elementName)
    {
        return this.getElement().getOwnerDocument().createElementNS(Constants.NS_RIFCS, elementName);
    }
    
    
    /**
     * Format a date to the required RIF-CS format (yyyy-MM-dd'T'HH:mm:ss'Z').
     *      
     * @return
     *      The date in UTC format
     */  
    protected static String formatDate(Date date)
    {
        SimpleDateFormat df = new SimpleDateFormat(Constants.TIMESTAMP_UTC_FORMAT);
        Calendar cal = Calendar.getInstance(TimeZone.getTimeZone("UTC"));
        cal.setTime(date);
        df.setCalendar(cal);
        return df.format(cal.getTime());
    }
}