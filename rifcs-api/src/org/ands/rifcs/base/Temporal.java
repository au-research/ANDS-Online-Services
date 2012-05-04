/**
 * Date Modified: $Date: 2012-04-04 12:13:39 +1000 (Wed, 04 Apr 2012) $
 * Version: $Revision: 1695 $
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

import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.List;
import java.util.Date;
import java.util.Iterator;

import org.w3c.dom.Element;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;

/**
 * Class representing a RIF-CS address
 * 
 * @author Scott Yeadon
 *
 */
public class Temporal extends RIFCSElement
{
    private List<TemporalCoverageDate> dates = new ArrayList<TemporalCoverageDate>();
    private List<Element> texts = new ArrayList<Element>();

    /**
     * Construct a Temporal object
     * 
     * @param n 
     *        A w3c Node, typically an Element
     *        
     * @exception RIFCSException
     */ 
    protected Temporal(Node n) throws RIFCSException
    {
        super(n, Constants.ELEMENT_TEMPORAL);
    }

    
    /**
     * Create and return an empty DateElement object.
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
   // public DateElement newDate() throws RIFCSException
   // {
   //     return new DateElement(this.newElement(Constants.ELEMENT_DATE));
   // }
    
    public TemporalCoverageDate newDate() throws RIFCSException
    {
    return new TemporalCoverageDate(this.newElement(Constants.ELEMENT_DATE));
    }

    
    /**
     * Obtain the date information for this temporal coverage
     * 
     * @return 
     *      A list of DateElement objects
     */          
    public List<TemporalCoverageDate> getDates()
    {
        return this.dates;
    }

    
    /**
     * Obtain the text information for this temporal coverage
     * 
     * @return 
     *      A list of string values
     */          
    public List<String> getText()
    {
        ArrayList al = new ArrayList<String>();
        for (Iterator<Element> i = texts.iterator(); i.hasNext();)
        {
            al.add(i.next().getTextContent());
        }
        return al;
    }

    
    /**
     * Add text information to the temporal object 
     * 
     * @param text
     *    a text description of the temporal coverage
     */
    public void addText(String text)
    {
        Element e = this.newElement(Constants.ELEMENT_TEXT);
        e.setTextContent(text);
        this.getElement().appendChild(e);
        this.texts.add((Element)e);
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
    

    public void addDate(String date, String type) throws RIFCSException
	{
    	this.addDate(date, type,"W3C");
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
    

    public void addDate(String date, String type, String dateFormat) throws RIFCSException
	{
	    TemporalCoverageDate de = this.newDate();
	    de.setType(type);
	    de.setDateFormat(dateFormat);
	    de.setValue(date);
	    this.getElement().appendChild(de.getElement());
	    this.dates.add((TemporalCoverageDate)de);
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
    

    public void addDate(Date date, String type) throws RIFCSException
	{
    	DateFormat df = new SimpleDateFormat("yyyy-MM-dd'T'HH:mm:ssZ");  
    	String text = df.format(date);  
    	String result = text.substring(0, 22) + ":" + text.substring(22);
    	this.addDate(result, type);
	}


    /* initialisation code for existing documents */
    private void initStructures() throws RIFCSException
    {
        initTexts();
        initDates();
    }
    
    private void initTexts() throws RIFCSException
    {
        NodeList nl = super.getElements(Constants.ELEMENT_TEXT);
        
        for (int i = 0; i < nl.getLength(); i++)
        {
            texts.add((Element)nl.item(i));
        }
    }

    private void initDates() throws RIFCSException
    {
        NodeList nl = super.getElements(Constants.ELEMENT_DATE);
        
        for (int i = 0; i < nl.getLength(); i++)
        {
            dates.add(new TemporalCoverageDate(nl.item(i)));
        }
    }
}