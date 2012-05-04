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

import java.util.Iterator;
import java.util.List;

import org.w3c.dom.Element;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;

/**
 * Class representing a registry object
 * 
 * @author Scott Yeadon
 *
 */
public class RegistryObject extends RIFCSElement
{
//    private List<Identifier> identifiers = new ArrayList<Identifier>();
//    private List<Name> names = new ArrayList<Name>();
    private String objectClass = null;

    /**
     * Construct a registry object
     * 
     * @param n
     *        A w3c Node, typically an Element
     *        
     * @exception RIFCSException
     */     
    protected RegistryObject(Node n) throws RIFCSException
    {
        super(n, Constants.ELEMENT_REGISTRY_OBJECT);
        initStructures();
    }

    
    /**
     * Set the key value
     * 
     * @param keyValue
     *      The key uniquely identifying the registry object
     */          
    public void setKey(String keyValue)
    {
        Element key = this.newElement(Constants.ELEMENT_KEY);
        key.setTextContent(keyValue);
        this.getElement().appendChild(key);
    }
    
    
    /**
     * Get the key value
     * 
     * @return
     *     The key uniquely identifying the registry object
     */          
    public String getKey()
    {
        List<Node> nl = super.getChildElements(Constants.ELEMENT_KEY);
        if (nl.size() == 1)
        {
            return nl.get(0).getTextContent();
        }
        
        return null;
    }
    
    
    /**
     * Set the originating source
     * 
     * @param sourceValue
     *      A string identifying the source of this RIF-CS data
     */          
    public void setOriginatingSource(String sourceValue)
    {
        Element source = this.newElement(Constants.ELEMENT_ORIG_SOURCE);
        source.setTextContent(sourceValue);
        this.getElement().appendChild(source);
    }

    
    /**
     * Set the originating source
     * 
     * @param sourceValue
     *      A string identifying the source of this RIF-CS data
     * @param type
     *      A string clarifying the type of source e.g. to flag
     *      whether the source is authoritative
     */
    public void setOriginatingSource(String sourceValue,
                                     String type)
    {
        Element source = this.newElement(Constants.ELEMENT_ORIG_SOURCE);
        source.setTextContent(sourceValue);
        source.setAttribute(Constants.ATTRIBUTE_TYPE, type);
        this.getElement().appendChild(source);                
    }

    
    /**
     * Set the originating source type
     * 
     * @param type
     *      A string clarifying the type of source e.g. to flag
     *      whether the source is authoritative
     */
    public void setOriginatingSourceType(String type)
    {
        NodeList nl = super.getElements(Constants.ELEMENT_ORIG_SOURCE);
        if (nl.getLength() == 1)
        {
           ((Element)nl.item(0)).setAttribute(Constants.ATTRIBUTE_TYPE, type);
        }
    }

    
    /**
     * Return the originating source string
     * 
     * @return
     *     A string identifying the source of this RIF-CS data
     */
    public String getOriginatingSource()
    {
        NodeList nl = super.getElements(Constants.ELEMENT_ORIG_SOURCE);
        if (nl.getLength() == 1)
        {
            return nl.item(0).getTextContent();
        }
        
        return null;
    }

    
    /**
     * Return the originating source type
     * 
     * @return
     *      A string clarifying the type of source
     */
    public String getOriginatingSourceType()
    {
        NodeList nl = super.getElements(Constants.ELEMENT_ORIG_SOURCE);
        if (nl.getLength() == 1)
        {
           if (((Element)nl.item(0)).hasAttribute(Constants.ATTRIBUTE_TYPE))
           {
               return ((Element)nl.item(0)).getAttribute(Constants.ATTRIBUTE_TYPE);               
           }
           else
           {
               return null;
           }
        }
        
        return null;
    }


    /**
     * Set the group identifier
     * 
     * @param group
     *      A string identifying the group this registry object
     *      is associated with
     */
    public void setGroup(String group)
    {
        super.setAttributeValue(Constants.ATTRIBUTE_GROUP, group);        
    }


    /**
     * Get the group identifier
     * 
     * @return
     *      A string identifying the group this registry object
     *      is associated with
     */
    public String getGroup()
    {
        return super.getAttributeValue(Constants.ATTRIBUTE_GROUP);        
    }

    
    /**
     * Create and return an empty Collection object.
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
    public Collection newCollection() throws RIFCSException
    {
        Element coll = this.newElement(Constants.ELEMENT_COLLECTION);
        return new Collection(coll);
    }

    
    /**
     * Create and return an empty Activity object.
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
    public Activity newActivity() throws RIFCSException
    {
        Element activity = this.newElement(Constants.ELEMENT_ACTIVITY);
        return new Activity(activity);
    }

    
    /**
     * Create and return an empty Party object.
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
    public Party newParty() throws RIFCSException
    {
        Element party = this.newElement(Constants.ELEMENT_PARTY);
        return new Party(party);
    }

    
    /**
     * Create and return an empty Service object.
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
    public Service newService() throws RIFCSException
    {
        Element service = this.newElement(Constants.ELEMENT_SERVICE);
        return new Service(service);
    }
    
    
    /**
     * Add a collection to the registry object
     * 
     *  @param collection
     *      A Collection object
     */
    public void addCollection(Collection collection)
    {
        this.getElement().appendChild(collection.getElement());
        this.objectClass = Constants.ELEMENT_COLLECTION;
    }
    
    
    /**
     * Add an activity to the registry object
     * 
     *  @param activity
     *      An Activity object
     */
    public void addActivity(Activity activity)
    {
        this.getElement().appendChild(activity.getElement());
        this.objectClass = Constants.ELEMENT_PARTY;
    }
    
    
    /**
     * Add a party to the registry object
     * 
     *  @param party
     *      A Party object
     */
    public void addParty(Party party)
    {
        this.getElement().appendChild(party.getElement());
        this.objectClass = Constants.ELEMENT_PARTY;
    }
    
    
    /**
     * Add a service to the registry object
     * 
     *  @param service
     *      A Service object
     */
    public void addService(Service service)
    {
        this.getElement().appendChild(service.getElement());
        this.objectClass = Constants.ELEMENT_SERVICE;
    }
    

    /**
     * Return the object class name.
     * 
     *  @return
     *      The element name of the object class (i.e. collection, service,
     *      activity, party)
     */
    public String getObjectClassName()
    {
        return this.objectClass;
    }
    
    
    /**
     * Obtain the RIFCSElement object representing the object class
     * 
     *  @return
     *      An object class object (i.e. collection, service,
     *      activity, party) or null if a matching element was not found.
     *      If null is returned it is likely there is some problem with the
     *      document or its state.
     *      
     * @exception RIFCSException
     */    
    public RIFCSElement getClassObject() throws RIFCSException
    {
        NodeList nl = super.getElements(objectClass);
        
        if (nl.getLength() != 1)
        {
            return null;
        }
        
        if (objectClass.equals(Constants.ELEMENT_COLLECTION))
        {
            return new Collection(nl.item(0));
        }
        else if (objectClass.equals(Constants.ELEMENT_PARTY))
        {
            return new Party(nl.item(0));
        }
        else if (objectClass.equals(Constants.ELEMENT_ACTIVITY))
        {
            return new Activity(nl.item(0));
        }
        else if (objectClass.equals(Constants.ELEMENT_SERVICE))
        {
            return new Service(nl.item(0));
        }
        
        return null;
    }
    
    
    private void initStructures()
    {
        List<Node> nl = super.getChildElements();
        for (Iterator<Node> i=nl.iterator(); i.hasNext();)
        {
            Node n = i.next();
            if (n.getNodeName().equals(Constants.ELEMENT_COLLECTION))
            {
                objectClass = Constants.ELEMENT_COLLECTION;
                break;
            }
            else if (n.getNodeName().equals(Constants.ELEMENT_PARTY))
            {
                objectClass = Constants.ELEMENT_PARTY;
                break;
            }
            else if (n.getNodeName().equals(Constants.ELEMENT_ACTIVITY))
            {
                objectClass = Constants.ELEMENT_ACTIVITY;
                break;
            }
            else if (n.getNodeName().equals(Constants.ELEMENT_SERVICE))
            {
                objectClass = Constants.ELEMENT_SERVICE;
                break;
            }
        }
    }
}