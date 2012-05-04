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

import org.w3c.dom.Element;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;

/**
 * Class representing registry object related information
 * 
 * @author Scott Yeadon
 *
 */
public class CitationInfo extends RIFCSElement
{
    private CitationMetadata cm = null;
    
    /**
     * Construct a RelatedInfo object
     * 
     * @param n
     *        A w3c Node, typically an Element
     *        
     * @exception RIFCSException
     */     
    protected CitationInfo(Node n) throws RIFCSException
    {
        super(n, Constants.ELEMENT_CITATIONINFO);
        initStructures();
    }


    /**
     * Create and return an empty CitationMetadata object.
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
    public CitationMetadata newCitationMetadata() throws RIFCSException
    {
        return new CitationMetadata(this.newElement(Constants.ELEMENT_CITATION_METADATA));
    }

    
    
    /**
     * Set the fullCitation and citation style
     * 
     * @param citation 
     *      The full citation in string form
     * @param style
     *      The full citation style, use <code>null</code> if style is unknown
     * 
     */
    public void setCitation(String citation,
                            String style) throws RIFCSException
    {
        Element e = this.newElement(Constants.ELEMENT_FULL_CITATION);
        e.setTextContent(citation);
        if (style != null)
        {
            e.setAttribute(Constants.ATTRIBUTE_STYLE, style);
        }
        this.getElement().appendChild(e);
    }
    
    
    /**
     * Get the fullCitation
     * 
     * @return String 
     *      The fullCitation element content in string form 
     *      or empty string if not present
     * 
     */
    public String getCitation()
    {
        NodeList nl = super.getElements(Constants.ELEMENT_FULL_CITATION);
        if (nl.getLength() > 0)
        {
            return nl.item(0).getTextContent();
        }
        
        return null;
    }

    
    /**
     * Get the fullCitation style
     * 
     * @return String 
     *      The fullCitation element style or empty string if 
     *      there is no style associated with the citation text
     * 
     */
    public String getCitationStyle()
    {
        NodeList nl = super.getElements(Constants.ELEMENT_FULL_CITATION);
        if (nl.getLength() > 0)
        {
            return ((Element)nl.item(0)).getAttribute(Constants.ATTRIBUTE_STYLE);
        }
        
        return null;
    }
    
    
    /**
     * Add citationMetadata to the citationInfo object 
     * 
     * @param citationMetadata
     *    a citationMetadata option      
     */
    public void addCitationMetadata(CitationMetadata citationMetadata)
    {
        this.getElement().appendChild(citationMetadata.getElement());
        cm = citationMetadata;
    }

    
    /**
     * Get the citationMetadata  
     * 
     * @return CitationMetadata
     *    a citationMetadata object      
     */
    public CitationMetadata getCitationMetadata()
    {
        return cm;
    }

    
    // intialise structures for existing documents
    private void initStructures() throws RIFCSException
    {
        NodeList nl = super.getElements(Constants.ELEMENT_CITATION_METADATA);
        
        if (nl.getLength() > 0)
        {
            this.cm = new CitationMetadata(nl.item(0));
        }
    }
}