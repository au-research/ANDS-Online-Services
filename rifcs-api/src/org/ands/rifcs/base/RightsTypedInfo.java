/**
 * Date Modified: $Date: 2012-04-10 15:45:16 +1100 (TUE, 10 APR 2012) $
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
 * Class representing a RIF-CS rightsTypedInfo object
 * 
 * @author Mahmoud Sadeghi
 *
 */
public class RightsTypedInfo extends RIFCSElement
{
    /**
     * Construct a rightsTypedInfo object
     * 
     * @param n 
     *        A w3c Node, typically an Element
     *        
     * @exception RIFCSException
     */     
    protected RightsTypedInfo(Node n) throws RIFCSException
    {
        super(n, n.getNodeName());
    }


    /**
     * Set the type
     * 
     * @param type 
     *          The type of rightsTypedInfo
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
     * Set the rightsUri
     * 
     * @param rightsUri
     *          The rightsUri of RightsInfo
     */      
    public void setRightsUri(String rightsUri)
    {
        super.setAttributeValue(Constants.ATTRIBUTE_RIGHTS_URI, rightsUri);
    }


    /**
     * return the rightsUri
     * 
     * @return
     *      The type attribute value or empty string if attribute
     *      is empty or not present
     */
    public String getRightsUri()
    {
        return super.getAttributeValue(Constants.ATTRIBUTE_RIGHTS_URI);
    }
    

    /**
     * Set the content
     * 
     * @param value 
     *      The content of the rightsTypedInfo
     */
    public void setValue(String value)
    {
        super.setTextContent(value);
    }


    /**
     * Obtain the content
     * 
     * @return 
     *      The rightsTypedInfo string
     */  
    public String getValue()
    {
        return super.getTextContent();
    }
}