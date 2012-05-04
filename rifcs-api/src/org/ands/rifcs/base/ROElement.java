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

// THIS CLASS NOT CURRENTLY USED
import java.util.ArrayList;
import java.util.List;

import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;
import javax.xml.parsers.ParserConfigurationException;

import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;

/**
 * superclass of all RIF-CS object classes (class not currently used)
 * 
 * @author Scott Yeadon
 *
 */
public class ROElement
{
    private Element e = null;
    
    /**
     * Construct a RIF-CS element
     * 
     * @param n 
     *        A w3c Node, typically an Element
     *        
     * @exception RIFCSException
     *
     */ 
    protected ROElement(Node n) throws RIFCSException
    {
        if (n == null)
        {
            throw new RIFCSException("Null Node passed to constructor");
        }

        if (!(n instanceof Element))
        {
            throw new RIFCSException("Node of type Element required in constructor");
        }
        
        String name = n.getNodeName(); 
        
        if (!name.equals(Constants.ELEMENT_ACTIVITY) &&
            !name.equals(Constants.ELEMENT_COLLECTION) &&
            !name.equals(Constants.ELEMENT_PARTY) &&
            !name.equals(Constants.ELEMENT_SERVICE))
        {
            throw new RIFCSException("Invalid regsitry object type: " + name);
        }

//        this.getElement().getOwnerDocument().createElementNS(Constants.NS_RIFCS, name);
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
     *      The attribute value
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
     * Obtain an attribute value where the attribute has a
     * namespace
     * 
     * @param ns
     *      The attribute namespace URL
     * @param localName
     *      The unqualified attribute name
     *      
     * @return 
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
    protected String getText()
    {
        return e.getTextContent();
    }
    
    
    /**
     * Set the text content of the RIFCS Element
     * 
     * @param value 
     *      The text content of the element
     */  
    protected void setText(String value)
    {
        e.setTextContent(value);
    }

    
    /**
     * Obtain a list of descendant RIFCS elements with the given name
     * 
     * @param localName
     *      Obtain a list of descendant METS elements
     *      
     * @return org.w3c.dom.NodeList 
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
}