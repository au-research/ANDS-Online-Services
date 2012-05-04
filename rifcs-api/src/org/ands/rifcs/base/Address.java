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
 * Class representing a RIF-CS address
 * 
 * @author Scott Yeadon
 *
 */
public class Address extends RIFCSElement
{
    private List<Electronic> electronics = new ArrayList<Electronic>();
    private List<Physical> physicals = new ArrayList<Physical>();

    
    /**
     * Construct an Address object
     * 
     * @param n 
     *        A w3c Node, typically an Element
     *        
     * @exception RIFCSException
     */ 
    protected Address(Node n) throws RIFCSException
    {
        super(n, Constants.ELEMENT_ADDRESS);
        initStructures();
    }
    
    
    /**
     * Create and return an empty electronic address.
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
    public Electronic newElectronic() throws RIFCSException
    {
        return new Electronic(this.newElement(Constants.ELEMENT_ELECTRONIC));
    }
    
    
    /**
     * Add an electronic address to the address object 
     * 
     * @param electronic
     *    an ElectronicAddress object      
     */
    public void addElectronic(Electronic electronic)
    {
        this.getElement().appendChild(electronic.getElement());
        this.electronics.add(electronic);
    }

    
    /**
     * Obtain the electronic addresses for this address
     * 
     * @return 
     *      A list of all electronic addresses within this address.
     */          
    public List<Electronic> getElectronics()
    {
        return this.electronics;
    }

    
    /**
     * Create and return an empty physical address.
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
    public Physical newPhysical() throws RIFCSException
    {
        return new Physical(this.newElement(Constants.ELEMENT_PHYSICAL));
    }


    /**
     * Add a physical address to the address object 
     * 
     * @param physical
     *    a PhysicalAddress object      
     */
    public void addPhysical(Physical physical)
    {
        if (this.physicals == null)
        {
            this.physicals = new ArrayList<Physical>();
        }
        
        this.getElement().appendChild(physical.getElement());
        this.physicals.add(physical);
    }
    
    
    /**
     * Obtain the physical addresses for this address
     * 
     * @return 
     *      A list of all physical addresses within this address.
     */              
    public List<Physical> getPhysicalAddresses()
    {
        return this.physicals;
    }
    
    
    /* initialisation code for existing documents */
    private void initStructures() throws RIFCSException
    {
        initPhysicals();
        initElectronics();
    }
    
    private void initPhysicals() throws RIFCSException
    {
        NodeList nl = super.getElements(Constants.ELEMENT_PHYSICAL);
        
        for (int i = 0; i < nl.getLength(); i++)
        {
            physicals.add(new Physical(nl.item(i)));
        }
    }

    private void initElectronics() throws RIFCSException
    {
        NodeList nl = super.getElements(Constants.ELEMENT_ELECTRONIC);
        
        for (int i = 0; i < nl.getLength(); i++)
        {
            electronics.add(new Electronic(nl.item(i)));
        }
    }
}