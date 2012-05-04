/**
 * Date Modified: $Date: 2010-07-08 14:54:07 +1000 (Thu, 08 Jul 2010) $
 * Version: $Revision: 463 $
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
 * Class representing registry object related information
 * 
 * @author Scott Yeadon
 *
 */
public class RelatedInfo extends RIFCSElement
{
    private Identifier identifier = null;
    
    /**
     * Construct a RelatedInfo object
     * 
     * @param n
     *        A w3c Node, typically an Element
     *        
     * @exception RIFCSException
     */     
    protected RelatedInfo(Node n) throws RIFCSException
    {
        super(n, Constants.ELEMENT_RELATED_INFO);
        initStructures();
    }


    /**
     * Set the type
     * 
     * @param type 
     *          The type of related information
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
     * Create and return an empty Identifier object.
     * 
     * The returned object has no properties or content and is not part
     * of the RIF-CS document, it is essentially a constructor of an object
     * owned by the RIF-CS document. The returned object needs to be
     * "filled out" (e.g. with properties, additional sub-elements, etc) 
     * before being added to the RIF-CS document.
     * 
     * @exception RIFCSException
     *
     */
    public Identifier newIdentifier() throws RIFCSException
    {
        return new Identifier(this.newElement(Constants.ELEMENT_IDENTIFIER));
    }

    
    /**
     * Set the identifier
     * 
     * @param identifier 
     *      The identifier of the related information resource
     * @param type
     *      The type of the identifier
     * 
     */
    public void setIdentifier(String identifier,
                              String type) throws RIFCSException
    {
        this.identifier = this.newIdentifier(); 
        this.identifier.setValue(identifier);
        this.identifier.setType(type);
        this.getElement().appendChild(this.identifier.getElement());
    }


    /**
     * Obtain the identifier
     * 
     * @return Identifier
     *      The identifier of the related information resource
     */  
    public Identifier getIdentifier()
    {
        return identifier;
    }
    
    
    /**
     * Set the title 
     * 
     * @param title
     *    The title of the related information resource
     */
    public void setTitle(String title)
    {
        Element e = this.newElement(Constants.ELEMENT_TITLE);
        e.setTextContent(title);
        this.getElement().appendChild(e);
    }
    
    
    /**
     * Get the title 
     * 
     * @return String
     *    The title of the related information resource
     */
    public String getTitle()
    {
        NodeList nl = super.getElements(Constants.ELEMENT_TITLE);
        if (nl.getLength() == 1)
        {
            return nl.item(0).getTextContent();
        }
        
        return null;
    }
    
    
    /**
     * Set the notes 
     * 
     * @param notes
     *    Notes relevant to the related information resource
     */
    public void setNotes(String notes)
    {
        Element e = this.newElement(Constants.ELEMENT_NOTES);
        e.setTextContent(notes);
        this.getElement().appendChild(e);
    }
    
    
    /**
     * Get the notes
     * 
     * @return String
     *    The title of the related information resource
     */
    public String getNotes()
    {
        NodeList nl = super.getElements(Constants.ELEMENT_NOTES);
        if (nl.getLength() == 1)
        {
            return nl.item(0).getTextContent();
        }
        
        return null;
    }
    
    
    /* initialisation code for existing documents */
    private void initStructures() throws RIFCSException
    {
        NodeList nl = super.getElements(Constants.ELEMENT_IDENTIFIER);
        
        if (nl.getLength() > 0)
        {
            this.identifier = new Identifier(nl.item(0));
        }
    }
}