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

import org.w3c.dom.Node;

/**
 * Class representing a RIF-CS Subject object
 * 
 * @author Scott Yeadon
 *
 */
public class Subject extends RIFCSElement
{
    /**
     * Construct a Subject object
     * 
     * @param n
     *        A w3c Node, typically an Element
     *        
     * @exception RIFCSException
     */     
    protected Subject(Node n) throws RIFCSException
    {
        super(n, Constants.ELEMENT_SUBJECT);
    }


    /**
     * Set the type
     * 
     * @param type 
     *          The type of subject
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
    * Set the termIdentifier
    * 
    * @param termIdentifier 
    *          The termIdentifier of subject
    */      
   public void setTermIdentifier(String termIdentifier)
   {
       super.setAttributeValue(Constants.ATTRIBUTE_TERM_IDENTIFIER, termIdentifier);
   }

   
   /**
    * return the termIdentifier
    * 
    * @return 
    *      The termIdentifier attribute value or empty string if attribute
    *      is empty or not present
    */  
  public String getTermIdentifier()
   {
       return super.getAttributeValue(Constants.ATTRIBUTE_TERM_IDENTIFIER);
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
     *      The language or empty string if attribute
     *      is empty or not present
     */  
    public String getLanguage()
    {
        return super.getAttributeValueNS(Constants.NS_XML, Constants.ATTRIBUTE_LANG);
    }


    /**
     * Set the content
     * 
     * @param value 
     *      The content of the subject
     */
    public void setValue(String value)
    {
        super.setTextContent(value);
    }


    /**
     * Obtain the content
     * 
     * @return
     *      The subject string
     */  
    public String getValue()
    {
        return super.getTextContent();
    }
}