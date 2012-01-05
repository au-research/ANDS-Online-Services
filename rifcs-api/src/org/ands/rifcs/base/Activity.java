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
import java.util.Date;
import java.util.List;

import org.w3c.dom.Node;
import org.w3c.dom.NodeList;

/**
 * Class representing a RIF-CS Activity registry object
 * 
 * @author Scott Yeadon
 *
 */
public class Activity extends RIFCSElement
{
    private List<Identifier> identifiers = new ArrayList<Identifier>();
    private List<Name> names =  new ArrayList<Name>();
    private List<Location> locations =  new ArrayList<Location>();
    private List<RelatedObject> relatedObjects =  new ArrayList<RelatedObject>();
    private List<Subject> subjects =  new ArrayList<Subject>();
    private List<Description> descriptions =  new ArrayList<Description>();
    private List<RelatedInfo> ris =  new ArrayList<RelatedInfo>();

    /**
     * Construct an Activity object
     * 
     * @param n 
     *        A w3c Node, typically an Element
     *        
     * @exception RIFCSException
     */     
    protected Activity(Node n) throws RIFCSException
    {
        super(n, Constants.ELEMENT_ACTIVITY);
        initStructures();
    }

    
    /**
     * Set the type
     * 
     * @param type 
     *      The type of activity being described
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
     * Set the date the activity metadata was modified
     * 
     * @param date
     *      A date object representing the date the activity metadata
     *      was last modified 
     */          
    public void setDateModified(Date date)
    {
        super.setAttributeValue(Constants.ATTRIBUTE_DATE_MODIFIED, RegistryObject.formatDate(date));        
    }

    
    /**
     * Set the date the activity metadata was last modified
     * 
     * @param date
     *      A string in UTC and of one of the forms described in section 3.2.7
     *      of the <a href="http://www.w3.org/TR/xmlschema-2/">W3C's Schema 
     *      Data Types document</a> 
     */          
    public void setDateModified(String date)
    {
        super.setAttributeValue(Constants.ATTRIBUTE_DATE_MODIFIED, date);
    }

    
    /**
     * return the date modified
     * 
     * @return 
     *      The dateModified attribute value or empty string if attribute
     *      is empty or not present
     */  
    public String getDateModified()
    {
        return super.getAttributeValue(Constants.ATTRIBUTE_DATE_MODIFIED);
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
     * Add an identifier to the activity object 
     * 
     * @param identifier
     *    an Identifier object      
     */
    public void addIdentifier(Identifier identifier)
    {
/*        if (identifiers == null)
        {
            identifiers = new ArrayList<Identifier>();
        }
  */      
        this.getElement().appendChild(identifier.getElement());
        this.identifiers.add(identifier);
    }
    
    
    /**
     * Obtain the identifiers for this activity
     * 
     * @return 
     *      A list of Identifier objects
     */          
  public List<Identifier> getIdentifiers()
    {
        return identifiers;
    }
    

    /**
     * Create and return an empty Name object.
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
    public Name newName() throws RIFCSException
    {
        return new Name(this.newElement(Constants.ELEMENT_NAME));
    }

    
    /**
     * Add a name to the activity object 
     * 
     * @param name
     *    a Name object      
     */
    public void addName(Name name)
    {
       /* if (names == null)
        {
            names = new ArrayList<Name>();
        }*/
        
        this.getElement().appendChild(name.getElement());
        this.names.add(name);
    }
    
    
    /**
     * Obtain the names for this activity
     * 
     * @return List<Name> 
     *      A list of Name objects
     */          
    public List<Name> getNames()
    {
        return names;
    }
    

    /**
     * Create and return an empty Location object.
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
    public Location newLocation() throws RIFCSException
    {
        return new Location(this.newElement(Constants.ELEMENT_LOCATION));
    }

    
    /**
     * Add a location to the activity object 
     * 
     * @param location
     *    a Location object      
     */
    public void addLocation(Location location)
    {
     /*   if (locations == null)
        {
            locations = new ArrayList<Location>();
        }
       */ 
        this.getElement().appendChild(location.getElement());
        this.locations.add(location);
    }
    
    
    /**
     * Obtain the locations for this activity
     * 
     * @return 
     *      A list of Location objects
     */          
    public List<Location> getLocations()
    {
        return locations;
    }
    

    /**
     * Create and return an empty RelatedObject object.
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
    public RelatedObject newRelatedObject() throws RIFCSException
    {
        return new RelatedObject(this.newElement(Constants.ELEMENT_RELATED_OBJECT));
    }

    
    /**
     * Add a related object to the activity object 
     * 
     * @param relatedObject
     *    an RelatedObject object      
     */
    public void addRelatedObject(RelatedObject relatedObject)
    {
 /*       if (relatedObjects == null)
        {
            relatedObjects = new ArrayList<RelatedObject>();
        }
   */     
        this.getElement().appendChild(relatedObject.getElement());
        this.relatedObjects.add(relatedObject);
    }
    
    
    /**
     * Obtain the related objects for this activity
     * 
     * @return 
     *      A list of RelatedObject objects
     */          
    public List<RelatedObject> getRelatedObjects()
    {
        return relatedObjects;
    }
    

    /**
     * Create and return an empty Subject object.
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
    public Subject newSubject() throws RIFCSException
    {
        return new Subject(this.newElement(Constants.ELEMENT_SUBJECT));
    }

    
    /**
     * Add a subject to the activity object 
     * 
     * @param subject
     *    a Subject object      
     */
    public void addSubject(Subject subject)
    {
     /*   if (subjects == null)
        {
            subjects = new ArrayList<Subject>();
        }
       */ 
        this.getElement().appendChild(subject.getElement());
        this.subjects.add(subject);
    }
    
    
    /**
     * Obtain the subjects for this activity
     * 
     * @return 
     *      A list of Subject objects
     */          
    public List<Subject> getSubjects()
    {
        return subjects;
    }
    

    /**
     * Create and return an empty Description object.
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
    public Description newDescription() throws RIFCSException
    {
        return new Description(this.newElement(Constants.ELEMENT_DESCRIPTION));
    }

    
    /**
     * Add a description to the activity object 
     * 
     * @param description
     *    a Description object      
     */
    public void addDescription(Description description)
    {
    /*    if (descriptions == null)
        {
            descriptions = new ArrayList<Description>();
        }
      */  
        this.getElement().appendChild(description.getElement());
        this.descriptions.add(description);
    }
    
    
    /**
     * Obtain the description for this activity
     * 
     * @return 
     *      A list of Description objects
     */          
    public List<Description> getDescriptions()
    {
        return descriptions;
    }
    

    /**
     * Create and return an empty RelatedInfo object.
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
    public RelatedInfo newRelatedInfo() throws RIFCSException
    {
        return new RelatedInfo(this.newElement(Constants.ELEMENT_RELATED_INFO));
    }

    
    /**
     * Add related info to the activity object 
     * 
     * @param relatedInfo
     *    a relatedInfo object      
     */
    public void addRelatedInfo(RelatedInfo relatedInfo)
    {
        this.getElement().appendChild(relatedInfo.getElement());
        this.ris.add(relatedInfo);
    }
    
    
    /**
     * Obtain the related info for this activity
     * 
     * @return List<RelatedInfo> 
     *      A list of RelatedInfo objects
     */          
    public List<RelatedInfo> getRelatedInfo()
    {
        return ris;
    }
    
    
    /* initialisation code for existing documents */
    private void initStructures() throws RIFCSException
    {
        initIdentifiers();
        initNames();
        initLocations();
        initRelatedObjects();
        initSubjects();
        initDescriptions();
        initRelatedInfo();
    }
    
    private void initIdentifiers() throws RIFCSException
    {
        NodeList nl = super.getElements(Constants.ELEMENT_IDENTIFIER);
        
        for (int i = 0; i < nl.getLength(); i++)
        {
            identifiers.add(new Identifier(nl.item(i)));
        }
    }
    
    private void initNames() throws RIFCSException
    {
        NodeList nl = super.getElements(Constants.ELEMENT_NAME);
        
        for (int i = 0; i < nl.getLength(); i++)
        {
            names.add(new Name(nl.item(i)));
        }
    }

    private void initLocations() throws RIFCSException
    {
        NodeList nl = super.getElements(Constants.ELEMENT_LOCATION);
        
        for (int i = 0; i < nl.getLength(); i++)
        {
            locations.add(new Location(nl.item(i)));
        }
    }

    private void initRelatedObjects() throws RIFCSException
    {
        NodeList nl = super.getElements(Constants.ELEMENT_RELATED_OBJECT);
        
        for (int i = 0; i < nl.getLength(); i++)
        {
            relatedObjects.add(new RelatedObject(nl.item(i)));
        }
    }
    
    private void initSubjects() throws RIFCSException
    {
        NodeList nl = super.getElements(Constants.ELEMENT_SUBJECT);
        
        for (int i = 0; i < nl.getLength(); i++)
        {
            subjects.add(new Subject(nl.item(i)));
        }
    }

    private void initDescriptions() throws RIFCSException
    {
        NodeList nl = super.getElements(Constants.ELEMENT_DESCRIPTION);
        
        for (int i = 0; i < nl.getLength(); i++)
        {
            descriptions.add(new Description(nl.item(i)));
        }
    }

    private void initRelatedInfo() throws RIFCSException
    {
        NodeList nl = super.getElements(Constants.ELEMENT_RELATED_INFO);
        
        for (int i = 0; i < nl.getLength(); i++)
        {
            ris.add(new RelatedInfo(nl.item(i)));
        }
    }
}