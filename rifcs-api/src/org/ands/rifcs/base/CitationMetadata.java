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

import java.util.ArrayList;
import java.util.List;

import org.w3c.dom.Element;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;

/**
 * Class representing registry object related information
 * 
 * @author Scott Yeadon
 *
 */
public class CitationMetadata extends RIFCSElement
{
    private Identifier identifier = null;
    private List<Contributor> names = new ArrayList<Contributor>();
    private List<CitationDate> dates = new ArrayList<CitationDate>();
    
    /**
     * Construct a CitationMetadata object
     * 
     * @param n
     *        A w3c Node, typically an Element
     *        
     * @exception RIFCSException
     */     
    protected CitationMetadata(Node n) throws RIFCSException
    {
        super(n, Constants.ELEMENT_CITATION_METADATA);
        initStructures();
    }


    /**
     * Create and return an empty Contributor object.
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
    public Contributor newContributor() throws RIFCSException
    {
        return new Contributor(this.newElement(Constants.ELEMENT_CONTRIBUTOR));
    }


    /**
     * Create and return an empty CitationDate object.
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
    public CitationDate newCitationDate() throws RIFCSException
    {
        return new CitationDate(this.newElement(Constants.ELEMENT_DATE));
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
     *      The identifier of the resource or
     *     <code>null</code> if no identifier is present
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
     *    The title of the related information resource or
     *    <code>null</code> if no title is present
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
     * Set the actionable URI 
     * 
     * @param uri
     *    an actionable uri where the resource can be located
     */
    public void setURL(String url)
    {
        Element value = this.newElement(Constants.ELEMENT_URL);
        value.setTextContent(url);
        this.getElement().appendChild(value);
    }


    /**
     * Return the citation uri 
     * 
     * @return String
     *    an actionable URI where the resource can be located      
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

    
    /**
     * Set the resource edition 
     * 
     * @param edition
     *    the resource edition
     */
    public void setEdition(String edition)
    {
        Element e = this.newElement(Constants.ELEMENT_EDITION);
        e.setTextContent(edition);
        this.getElement().appendChild(e);
    }


    /**
     * Return the resource edition
     * 
     * @return String
     *    the resource edition      
     */
    public String getEdition()
    {
        NodeList nl = super.getElements(Constants.ELEMENT_EDITION);
        if (nl.getLength() == 1)
        {
            return nl.item(0).getTextContent();
        }
        
        return null;
    }

    /**
     * Set the resource publisher 
     * 
     * @param publisher
     *    the resource publisher
     */
    public void setPublisher(String publisher)
    {
        Element e = this.newElement(Constants.ELEMENT_PUBLISHER);
        e.setTextContent(publisher);
        this.getElement().appendChild(e);
    }


    /**
     * Return the resource publisher
     * 
     * @return String
     *    the resource publisher      
     */
    public String getPublisher()
    {
        NodeList nl = super.getElements(Constants.ELEMENT_PUBLISHER);
        if (nl.getLength() == 1)
        {
            return nl.item(0).getTextContent();
        }
        
        return null;
    }

    
    /**
     * Set the placePublished 
     * 
     * @param placePublished
     *    the place the resource was published
     */
    public void setPlacePublished(String placePublished)
    {
        Element e = this.newElement(Constants.ELEMENT_PLACE_PUBLISHED);
        e.setTextContent(placePublished);
        this.getElement().appendChild(e);
    }


    /**
     * Return the placePublished
     * 
     * @return String
     *    the place the resource was published      
     */
    public String getPlacePublished()
    {
        NodeList nl = super.getElements(Constants.ELEMENT_PLACE_PUBLISHED);
        if (nl.getLength() == 1)
        {
            return nl.item(0).getTextContent();
        }
        
        return null;
    }

    
    /**
     * Set the resource context 
     * 
     * @param context
     *    the context of the resource (for example if the resource is
     *    a smaller part of a larger context)
     */
    public void setContext(String context)
    {
        Element e = this.newElement(Constants.ELEMENT_CONTEXT);
        e.setTextContent(context);
        this.getElement().appendChild(e);
    }


    /**
     * Return the context
     * 
     * @return String
     *    the context of the resource (for example if the resource is
     *    a smaller part of a larger context)
     */
    public String getContext()
    {
        NodeList nl = super.getElements(Constants.ELEMENT_CONTEXT);
        if (nl.getLength() == 1)
        {
            return nl.item(0).getTextContent();
        }
        
        return null;
    }

    
    /**
     * Add a name to the citation information 
     * 
     * @param contributor
     *    a Contributor object
     */
    public void addContributor(Contributor contributor)
    {
        this.getElement().appendChild(contributor.getElement());
        this.names.add(contributor);
    }
    
    
    /**
     * Obtain the contributors to this resource who are required for
     * forming a citation of this resource
     * 
     * @return 
     *      A list of Contributor objects
     */          
    public List<Contributor> getContributors()
    {
        return names;
    }

    
    /**
     * Add a date to the citation information 
     * 
     * @param date
     *    a CitationDate object
     */
    public void addDate(CitationDate date)
    {
        this.getElement().appendChild(date.getElement());
        this.dates.add(date);
    }
    
    
    /**
     * Obtain the contributors to this resource who are required for
     * forming a citation of this resource
     * 
     * @return 
     *      A list of Contributor objects
     */          
    public List<CitationDate> getDates()
    {
        return dates;
    }
    
    
    /* initialisation code for existing documents */
    private void initStructures() throws RIFCSException
    {
        initIdentifier();
        initContributors();
        initCitationDates();
    }

    
    /* initialisation code for existing documents */
    private void initIdentifier() throws RIFCSException
    {
        NodeList nl = super.getElements(Constants.ELEMENT_IDENTIFIER);
        
        if (nl.getLength() > 0)
        {
            this.identifier = new Identifier(nl.item(0));
        }
    }
    
    
    private void initContributors() throws RIFCSException
    {
        NodeList nl = super.getElements(Constants.ELEMENT_CONTRIBUTOR);
        
        for (int i = 0; i < nl.getLength(); i++)
        {
            names.add(new Contributor(nl.item(i)));
        }
    }

        
    private void initCitationDates() throws RIFCSException
    {
        NodeList nl = super.getElements(Constants.ELEMENT_DATE);
        
        for (int i = 0; i < nl.getLength(); i++)
        {
            dates.add(new CitationDate(nl.item(i)));
        }
    }
}