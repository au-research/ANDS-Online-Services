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

import org.w3c.dom.Element;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;

/**
 * Class representing a RIF-CS electronic address object
 * 
 * @author Scott Yeadon
 *
 */
public class Electronic extends RIFCSElement
{
    List<Arg> args = new ArrayList<Arg>();
    
    /**
     * Construct an Electronic address object
     * 
     * @param n
     *        A w3c Node, typically an Element
     *        
     * @exception RIFCSException
     */     
    protected Electronic(Node n) throws RIFCSException
    {
        super(n, Constants.ELEMENT_ELECTRONIC);
        initStructures();
    }


    /**
     * Set the type
     * 
     * @param type 
     *      The electronic address type
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
     * Create and return an empty Arg object.
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
    public Arg newArg() throws RIFCSException
    {
        return new Arg(this.newElement(Constants.ELEMENT_ARG));
    }

    
    /**
     * Add an argument to the electroinc address object 
     * 
     * @param name
     *    the name of the argument      
     * @param required
     *    <code>true</true> if the argument is required else <code>false</code>      
     * @param type
     *    the argument type      
     * @param use
     *    the argument use
     */
    public void addArg(String name,
                       String required,
                       String type,
                       String use) throws RIFCSException
    {
        Arg arg = newArg();
        arg.setName(name);
        arg.setRequired(required);
        arg.setType(type);
        arg.setUse(use);
        addArg(arg);
    }
    
    
    /**
     * Obtain the arguments for this electronic address
     * 
     * @return 
     *      A list of Arg objects
     */          
    public List<Arg> getArgs()
    {
        return args;
    }
    
    
    /**
     * Add an argument to the electronic address object 
     * 
     * @param arg
     *    a completed Arg object      
     */
    public void addArg(Arg arg)
    {
       this.getElement().appendChild(arg.getElement());
       this.args.add(arg);
    }
    
    
    /**
     * Set the electronic address URI 
     * 
     * @param valueUri
     *    a resolvable URI representing the electronic address
     *    of the containing registry object      
     */
    public void setValue(String valueUri)
    {
        Element value = this.newElement(Constants.ELEMENT_VALUE);
        value.setTextContent(valueUri);
        this.getElement().appendChild(value);
    }


    /**
     * Return the electronic address URI 
     * 
     * @return
     *    a resolvable URI representing the electronic address
     *    of the containing registry object      
     */
    public String getValue()
    {
        NodeList nl = super.getElements(Constants.ELEMENT_VALUE);
        if (nl.getLength() == 1)
        {
            return nl.item(0).getTextContent();
        }
        
        return null;
    }
    
    
    /* initialisation code for existing documents */
    private void initStructures() throws RIFCSException
    {
        NodeList nl = super.getElements(Constants.ELEMENT_ARG);

        for (int i = 0; i < nl.getLength(); i++)
        {
            args.add(new Arg(nl.item(i)));
        }
    }
}