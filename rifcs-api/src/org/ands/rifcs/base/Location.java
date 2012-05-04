/**
 * Date Modified: $Date: 2010-07-07 16:14:13 +1000 (Wed, 07 Jul 2010) $
 * Version: $Revision: 458 $
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
 * Class representing a RIF-CS location object
 * 
 * @author Scott Yeadon
 *
 */
public class Location extends RIFCSElement
{
    private List<Address> addresses = new ArrayList<Address>();
    private List<Spatial> spatials = new ArrayList<Spatial>();

    
    /**
     * Construct an Location object
     * 
     * @param n
     *        A w3c Node, typically an Element
     *        
     * @exception RIFCSException
     */     
    protected Location(Node n) throws RIFCSException
    {
        super(n, Constants.ELEMENT_LOCATION);
        initStructures();
    }
    
    
    /**
     * Set the date the location was relevant from
     * 
     * @param dateFrom
     *      A date object representing the date the contained location
     *      information was valid from
     */          
    public void setDateFrom(Date dateFrom)
    {
        super.setAttributeValue(Constants.ATTRIBUTE_DATE_FROM, RegistryObject.formatDate(dateFrom));
    }


    /**
     * Set the date the location was relevant from
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
     * Set the date the location was relevant from
     */
    public String getDateFrom()
    {
        return super.getAttributeValue(Constants.ATTRIBUTE_DATE_FROM);
    }


    /**
     * Set the date the location was relevant to
     * 
     * @param dateTo
     *      A date object representing the date the contained location
     *      information was valid to
     */          
    public void setDateTo(Date dateTo)
    { 
        super.setAttributeValue(Constants.ATTRIBUTE_DATE_TO, RegistryObject.formatDate(dateTo));
    }

    
    /**
     * Set the date the location was relevant to
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
     * Set the date the location was relevant to
     */
    public String getDateTo()
    {
        return super.getAttributeValue(Constants.ATTRIBUTE_DATE_TO);
    }
    
   
    /**
     * Create and return an empty Address object.
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
    public Address newAddress() throws RIFCSException
    {
        return new Address(this.newElement(Constants.ELEMENT_ADDRESS));
    }

    
    /**
     * Add an address to the location object 
     * 
     * @param address
     *    a completed Address object
     */
    public void addAddress(Address address)
    {
        this.getElement().appendChild(address.getElement());
        this.addresses.add(address);
    }
    
    
    /**
     * Obtain the addresses for this location
     * 
     * @return List<Address> 
     *      A list of Address objects
     */          
    public List<Address> getAddresses()
    {
        return this.addresses;
    }

    
    /**
     * Create and return an empty Spatial object.
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
    public Spatial newSpatial() throws RIFCSException
    {
        return new Spatial(this.newElement(Constants.ELEMENT_SPATIAL));
    }
  

    /**
     * Add spatial information to the location object 
     * 
     * @param spatial
     *    a completed Spatial object
     */
    public void addSpatial(Spatial spatial)
    {
        this.getElement().appendChild(spatial.getElement());
        this.spatials.add(spatial);
    }


    /**
     * Obtain the spatial information for this location
     * 
     * @return 
     *      A list of Spatial objects
     */          
    public List<Spatial> getSpatials()
    {
        return this.spatials;
    }
    
    
    /* initialisation code for existing documents */
    private void initStructures() throws RIFCSException
    {
        initSpatials();
        initAddresses();
    }
    
    private void initSpatials() throws RIFCSException
    {
        NodeList nl = super.getElements(Constants.ELEMENT_SPATIAL);
        
        for (int i = 0; i < nl.getLength(); i++)
        {
            spatials.add(new Spatial(nl.item(i)));
        }
    }

    private void initAddresses() throws RIFCSException
    {
        NodeList nl = super.getElements(Constants.ELEMENT_ADDRESS);
        
        for (int i = 0; i < nl.getLength(); i++)
        {
            addresses.add(new Address(nl.item(i)));
        }
    }
}