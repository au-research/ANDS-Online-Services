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

import java.util.ArrayList;
import java.util.List;

import org.w3c.dom.Node;
import org.w3c.dom.NodeList;

/**
 * Class representing a RIF-CS physical address object
 * 
 * @author Scott Yeadon
 *
 */
public class Physical extends RIFCSElement
{
    private List<AddressPart> addressParts = new ArrayList<AddressPart>();

    /**
     * Construct a physical address object
     * 
     * @param n
     *        A w3c Node, typically an Element
     *        
     * @exception RIFCSException
     */     
    protected Physical(Node n) throws RIFCSException
    {
        super(n, Constants.ELEMENT_PHYSICAL);
        initStructures();
    }
    
    
    /**
     * Set the type
     * 
     * @param type 
     *          The type of physical address
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
     * @return 
     *      The type attribute value or empty string if attribute
     *      is empty or not present
     */  
    public String getLanguage()
    {
        return super.getAttributeValueNS(Constants.NS_XML, Constants.ATTRIBUTE_LANG);
    }

    
    /**
     * Create and return an empty AddressPart object.
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
    public AddressPart newAddressPart() throws RIFCSException
    {
        return new AddressPart(this.newElement(Constants.ELEMENT_ADDRESSPART));
    }
    
    
    /**
     * Add an address part to the physical address object 
     * 
     * @param addressPart
     *    a completed AddressPart object      
     */
    public void addAddressPart(AddressPart addressPart)
    {
        /*if (addressParts == null)
        {
            addressParts = new ArrayList<AddressPart>();
        }*/
        
       this.getElement().appendChild(addressPart.getElement());
       this.addressParts.add(addressPart);
    }
    
    
    /**
     * Obtain the address parts for this physical address
     * 
     * @return 
     *      A list of AddressPart objects
     */          
    public List<AddressPart> getAddressParts()
    {
        return addressParts;
    }
    
    
    /* initialisation code for existing documents */
    private void initStructures() throws RIFCSException
    {
        NodeList nl = super.getElements(Constants.ELEMENT_ADDRESSPART);
        
        for (int i = 0; i < nl.getLength(); i++)
        {
            addressParts.add(new AddressPart(nl.item(i)));
        }        
    }

}