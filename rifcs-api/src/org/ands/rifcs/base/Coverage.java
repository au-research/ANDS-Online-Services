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
import java.util.List;
import java.util.Date;

import org.w3c.dom.Node;
import org.w3c.dom.NodeList;

/**
 * Class representing a RIF-CS address
 * 
 * @author Scott Yeadon
 *
 */
public class Coverage extends RIFCSElement
{
    private List<Spatial> spatials = new ArrayList<Spatial>();
    private List<Temporal> temporals = new ArrayList<Temporal>();

    
    /**
     * Construct a Coverage object
     * 
     * @param n 
     *        A w3c Node, typically an Element
     *        
     * @exception RIFCSException
     */ 
    protected Coverage(Node n) throws RIFCSException
    {
        super(n, Constants.ELEMENT_COVERAGE);
        initStructures();
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

    
    /**
     * Create and return an empty Temporal object.
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
    public Temporal newTemporal() throws RIFCSException
    {
        return new Temporal(this.newElement(Constants.ELEMENT_TEMPORAL));
    }
  

    /**
     * Add temporal information to the coverage object 
     * 
     * @param temporal
     *    a completed Temporal object
     */
    public void addTemporal(Temporal temporal)
    {
        this.getElement().appendChild(temporal.getElement());
        this.temporals.add(temporal);
    }
    
    
    /**
     * Add temporal text to the coverage object. A convenience method
     * creating a single temporal element with a text element 
     * 
     * @param text
     *      The value to add to the text element
     *    
     */
    public void addTemporal(String text) throws RIFCSException
    {
        Temporal t = newTemporal();
        t.addText(text);
        this.getElement().appendChild(t.getElement());
        this.temporals.add(t);
    }
    

    /**
     * Add temporal date to the coverage object. A convenience method
     * creating a single temporal element with a date element. 
     * 
     * @param date
     *      The date to add to the date element.
     * @param type
     *      The type of date
     *    
     */    
    public void addTemporalDate(Date date,
                                String type) throws RIFCSException
    {
        Temporal t = newTemporal();
        t.addDate(date, type);
        this.getElement().appendChild(t.getElement());
        this.temporals.add(t);
    }
    

    /**
     * Add temporal date to the coverage object. A convenience method
     * creating a single temporal element with a date element. 
     * 
     * @param date
     *      The date to add to the date element.
     * @param type
     *      The type of date
     *    
     */    
    public void addTemporalDate(String date,
                                String type) throws RIFCSException
    {
        Temporal t = newTemporal();
        t.addDate(date, type);
        this.getElement().appendChild(t.getElement());
        this.temporals.add(t);
    }


    /**
     * Obtain the temporal information for this coverage
     * 
     * @return 
     *      A list of Temporal objects
     */
    public List<Temporal> getTemporals()
    {
        return this.temporals;
    }
    
    
    /* initialisation code for existing documents */
    private void initStructures() throws RIFCSException
    {
        initSpatials();
        initTemporals();
    }
    
    private void initSpatials() throws RIFCSException
    {
        NodeList nl = super.getElements(Constants.ELEMENT_SPATIAL);
        
        for (int i = 0; i < nl.getLength(); i++)
        {
            spatials.add(new Spatial(nl.item(i)));
        }
    }

    private void initTemporals() throws RIFCSException
    {
        NodeList nl = super.getElements(Constants.ELEMENT_TEMPORAL);
        
        for (int i = 0; i < nl.getLength(); i++)
        {
            temporals.add(new Temporal(nl.item(i)));
        }
    }
}