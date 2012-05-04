/**
 * Date Modified: $Date: 2010-05-18 11:07:57 +1000 (Tue, 18 May 2010) $
 * Version: $Revision: 372 $
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
 * Class representing a RIF-CS namePart object
 * 
 * @author Scott Yeadon
 *
 */
public class NamePart extends RIFCSElement
{
    /**
     * Construct a NamePart object
     * 
     * @param n
     *        A w3c Node, typically an Element
     *        
     * @exception RIFCSException
     */     
    protected NamePart(Node n) throws RIFCSException
    {
        super(n, Constants.ELEMENT_NAMEPART);
    }


    /**
     * Set the type
     * 
     * @param type 
     *          The type of namePart being described
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
     * Set the content
     * 
     * @param value 
     *      The content of the namePart
     */
    public void setValue(String value)
    {
        super.setTextContent(value);
    }


    /**
     * Obtain the content
     * 
     * @return 
     *      The namePart string
     */  
    public String getValue()
    {
        return super.getTextContent();
    }
}