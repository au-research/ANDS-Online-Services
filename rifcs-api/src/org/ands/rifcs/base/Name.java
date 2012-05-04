/**
 * Date Modified: $Date: 2010-05-18 11:08:56 +1000 (Tue, 18 May 2010) $
 * Version: $Revision: 373 $
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

import java.util.ArrayList;
import java.util.Date;
import java.util.List;

import org.w3c.dom.Node;
import org.w3c.dom.NodeList;

/**
 * Class representing a RIF-CS name object
 * 
 * @author Scott Yeadon
 *
 */
public class Name extends RIFCSElement
{
    private List<NamePart> nameParts = new ArrayList<NamePart>();

    /**
     * Construct a Name object
     * 
     * @param n
     *        A w3c Node, typically an Element
     *        
     * @exception RIFCSException
     */     
    protected Name(Node n) throws RIFCSException
    {
        super(n, Constants.ELEMENT_NAME);
        initStructures();
    }
    
    
    /**
     * Set the type
     * 
     * @param type 
     *          The type of name
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
    * Set the language
    * 
    * @param lang 
    *      The xml:lang attribute value
    */  
    public void setLanguage(String lang)
    {
        super.setAttributeValueNS(Constants.NS_XML, Constants.ATTRIBUTE_LANG, lang);
    }


    /**
     * Obtain the language
     * 
     * @return String 
     *      The type attribute value or empty string if attribute
     *      is empty or not present
     */  
    public String getLanguage()
    {
        return super.getAttributeValueNS(Constants.NS_XML, Constants.ATTRIBUTE_LANG);
    }


    /**
     * Set the date the name was relevant from
     * 
     * @param dateFrom
     *      A date object representing the date the name
     *      information was valid from
     */          
    public void setDateFrom(Date dateFrom)
    {
        super.setAttributeValue(Constants.ATTRIBUTE_DATE_FROM, RegistryObject.formatDate(dateFrom));
    }


    /**
     * Set the date the name was relevant from
     * 
     * @param dateFrom
     *      A string in UTC and of one of the forms described in section 3.2.7
     *      of the <a href="http://www.w3.org/TR/xmlschema-2/">W3C's Schema 
     *      Data Types document</a> 
     */          
    public void setDateFrom(String dateFrom)
    {
        super.setAttributeValue(Constants.ATTRIBUTE_DATE_FROM, dateFrom);
    }


    /**
     * Set the date the name was relevant from
     */
    public String getDateFrom()
    {
        return super.getAttributeValue(Constants.ATTRIBUTE_DATE_FROM);
    }


    /**
     * Set the date the name was relevant to
     * 
     * @param dateTo
     *      A date object representing the date the name was valid to
     */          
    public void setDateTo(Date dateTo)
    { 
        super.setAttributeValue(Constants.ATTRIBUTE_DATE_TO, RegistryObject.formatDate(dateTo));
    }

    
    /**
     * Set the date the name was relevant to
     * 
     * @param dateTo
     *      A string in UTC and of one of the forms described in section 3.2.7
     *      of the <a href="http://www.w3.org/TR/xmlschema-2/">W3C's Schema 
     *      Data Types document</a> 
     */          
    public void setDateTo(String dateTo)
    {
        super.setAttributeValue(Constants.ATTRIBUTE_DATE_TO, dateTo);
    }
    
    
    /**
     * Set the date the name was relevant to
     */
    public String getDateTo()
    {
        return super.getAttributeValue(Constants.ATTRIBUTE_DATE_TO);
    }
    
    
    /**
     * Create and return an empty NamePart object.
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
    public NamePart newNamePart() throws RIFCSException
    {
        return new NamePart(this.newElement(Constants.ELEMENT_NAMEPART));
    }
    
    
    /**
     * Add a name part to a name object 
     * 
     * @param namePart
     *    a completed NamePart object
     */
    public void addNamePart(NamePart namePart)
    {
       this.getElement().appendChild(namePart.getElement());
       this.nameParts.add(namePart);
    }
    
    
    /**
     * Convenience method to add a name part to a name object 
     * 
     * @param namePart
     *    String with the name value
     * @param type
     *    namePart type (e.g. surname, middle name) or null
     */
    public void addNamePart(String namePart,
                            String type) throws RIFCSException
    {
        NamePart np = newNamePart();
        np.setValue(namePart);
        np.setType(type);
        this.getElement().appendChild(np.getElement());
        this.nameParts.add(np);
    }
    
    
    /**
     * Obtain the name parts for this name
     * 
     * @return 
     *      A list of NamePart objects
     */
    public List<NamePart> getNameParts()
    {
        return nameParts;
    }
    
    
    /* initialisation code for existing documents */
    private void initStructures() throws RIFCSException
    {
        NodeList nl = super.getElements(Constants.ELEMENT_NAMEPART);
        
        for (int i = 0; i < nl.getLength(); i++)
        {
            nameParts.add(new NamePart(nl.item(i)));
        }        
    }

}