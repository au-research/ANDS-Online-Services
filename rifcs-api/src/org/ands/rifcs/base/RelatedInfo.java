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
 * Class representing registry object related information
 * 
 * @author Scott Yeadon
 *
 */
public class RelatedInfo extends RIFCSElement
{
    /**
     * Construct a RelatedInfo object
     * 
     * @param n
     *        A w3c Node, typically an Element
     *        
     * @exception RIFCSException
     */     
    protected RelatedInfo(Node n) throws RIFCSException
    {
        super(n, Constants.ELEMENT_RELATED_INFO);
    }


    /**
     * Set the related info URI
     * 
     * @param value 
     *      The related info URI
     */
    public void setValue(String value)
    {
        super.setTextContent(value);
    }


    /**
     * Obtain the related info URI
     * 
     * @return
     *      The related info URI
     */  
    public String getValue()
    {
        return super.getTextContent();
    }
}