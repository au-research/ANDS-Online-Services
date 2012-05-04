/**
 * Date Modified: $Date: 2010-01-18 10:22:16 +1100 (Mon, 18 Jan 2010) $
 * Version: $Revision: 288 $
 * 
 * Copyright 2009 The Australian National University (ANU)
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

import org.w3c.dom.Element;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;

/**
 * Class representing a related object relation
 * 
 * @author Scott Yeadon
 *
 */
public class Relation extends RIFCSElement
{
    /**
     * Construct a Relation object
     * 
     * @param n
     *        A w3c Node, typically an Element
     *        
     * @exception RIFCSException
     */     
    protected Relation(Node n) throws RIFCSException
    {
        super(n, Constants.ELEMENT_RELATION);
    }


    /**
     * Set the type
     * 
     * @param type 
     *          The type of relation being described
     */      
    public void setType(String type)
    {
        super.setAttributeValue(Constants.ATTRIBUTE_TYPE, type);
    }

    
    /**
     * return the type
     * 
     * @return
     *      The type attribute value or empty string if attribute
     *      is empty or not present
     */  
    public String getType()
    {
        return super.getAttributeValue(Constants.ATTRIBUTE_TYPE);
    }
    
    
    /**
     * Set the relation description value
     * 
     * @param descValue
     *      A plain text description of the relation
     */          
    public void setDescription(String descValue)
    {
        Element desc = this.newElement(Constants.ELEMENT_DESCRIPTION);
        desc.setTextContent(descValue);
        this.getElement().appendChild(desc);
    }


    /**
     * Get the relation description value
     * 
     * @return
     *      The relation description
     */          
    public String getDescription()
    {
        NodeList nl = super.getElements(Constants.ELEMENT_DESCRIPTION);
        if (nl.getLength() == 1)
        {
            return nl.item(0).getTextContent();
        }
        
        return null;
    }
    
    
    /**
     * Set the language of the description
     * 
     * @param lang 
     *      The xml:lang attribute value
     */  
    public void setDescriptionLanguage(String lang)
    {
        NodeList nl = super.getElements(Constants.ELEMENT_DESCRIPTION);
        if (nl.getLength() == 1)
        {
            ((Element)nl.item(0)).setAttributeNS(Constants.NS_XML, Constants.ATTRIBUTE_LANG, lang);
        }        
    }
    
    
    /**
     * Obtain the language of the description
     * 
     * @return 
     *      The language value or empty string if attribute
     *      is empty or null if not present
     */  
    public String getDescriptionLanguage()
    {
        NodeList nl = super.getElements(Constants.ELEMENT_DESCRIPTION);
        if (nl.getLength() == 1)
        {
            return ((Element)nl.item(0)).getAttributeNS(Constants.NS_XML, Constants.ATTRIBUTE_LANG);
        }
        
        return null;
    }
    
    
    /**
     * Set the relation URL
     * 
     * @param urlValue 
     *      A URL expressing or implementing the relationship 
     *      between registry objects
     */  
    public void setURL(String urlValue)
    {
        Element url = this.newElement(Constants.ELEMENT_URL);
        url.setTextContent(urlValue);
        this.getElement().appendChild(url);
    }


    /**
     * Get the relation URL
     * 
     * @return 
     *      The URL expressing or implementing the relationship 
     *      between registry objects or null if not present
     */  
    public String getURL()
    {
        NodeList nl = super.getElements(Constants.ELEMENT_URL);
        if (nl.getLength() == 1)
        {
            return nl.item(0).getTextContent();
        }
        
        return null;
    }
}