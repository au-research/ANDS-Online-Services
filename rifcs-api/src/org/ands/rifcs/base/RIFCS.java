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
 * 
 */
package org.ands.rifcs.base;

import java.io.IOException;
import java.io.OutputStream;
import java.net.URL;
import java.net.MalformedURLException;
import java.util.HashMap;
import java.util.ArrayList;
import java.util.List;
import java.util.Map;

import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;
import javax.xml.parsers.ParserConfigurationException;
import javax.xml.transform.Source;
import javax.xml.transform.dom.DOMSource;
import javax.xml.transform.stream.StreamSource;
import javax.xml.validation.SchemaFactory;
import javax.xml.validation.Schema;
import javax.xml.validation.Validator;
import javax.xml.XMLConstants;

import org.w3c.dom.DOMImplementation;
import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.NodeList;
import org.w3c.dom.ls.DOMImplementationLS;
import org.w3c.dom.ls.LSOutput;
import org.w3c.dom.ls.LSSerializer;

import org.xml.sax.SAXException;

/**
 * The root level RIF-CS class
 * 
 * @author Scott Yeadon
 *
 */
public class RIFCS
{
    private Document doc = null;
    private HashMap<String,RegistryObject> ros = new HashMap<String,RegistryObject>();
    private HashMap<String, ArrayList<RegistryObject>> rosByClass = new HashMap<String, ArrayList<RegistryObject>>();

    
    /**
     * Construct an empty RIF-CS document.
     * The RIF-CS document will consist only of a root element with
     * sub-elements. Used when creating a new RIF-CS document.
     * 
     * @exception RIFCSException
     */     
    public RIFCS() throws RIFCSException
    {
        try
        {
            DocumentBuilderFactory factory = DocumentBuilderFactory.newInstance();
            factory.setNamespaceAware(true);
            DocumentBuilder builder = factory.newDocumentBuilder();
            doc = builder.newDocument();
            Element root = doc.createElementNS(Constants.NS_RIFCS, Constants.ELEMENT_REGISTRY_OBJECTS);
            root.setAttributeNS(Constants.NS_SCHEMA, Constants.ATTRIBUTE_SCHEMA_LOCATION, Constants.NS_RIFCS + " " + Constants.SCHEMA_REGISTRY_OBJECTS);
            doc.appendChild(root);
            initObjectClassMap();
        }
        catch (ParserConfigurationException pce)
        {
            throw new RIFCSException(pce);
        }
    }

    
    /**
     * Create and return an empty RegistryObject object.
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
    public RegistryObject newRegistryObject() throws RIFCSException
    {
        Element ro = doc.createElementNS(Constants.NS_RIFCS, Constants.ELEMENT_REGISTRY_OBJECT);
        return new RegistryObject(ro);
    }
    
    
    /**
     * Construct a RIF-CS document from an existing RIF-CS document.
     * 
     * @param d 
     *        A w3c Document representing a RIF-CS DOM
     *        
     * @exception RIFCSException
     */
    public RIFCS(Document d) throws RIFCSException
    {
        this.doc = d;
        initObjectClassMap();
        initRegistryObjects();
    }
    
    
    /**
     * Obtain the DOM document
     * 
     * @return A w3c Document representing the RIF-CS DOM
     */
    public Document getDocument()
    {
       return this.doc;
    }

    
    /**
     * Obtain all registry objects representing collections
     * 
     * @return A List of RegistryObject objects, all of which are collections.
     *        Empty List if no collections exist.
     */
    public List<RegistryObject> getCollections()
    {
        return rosByClass.get(Constants.ELEMENT_COLLECTION);
    }
    

    /**
     * Obtain all registry objects representing activities
     * 
     * @return A List of RegistryObject objects, all of which are activities.
     *        Empty List if no activities exist.
     */
    public List<RegistryObject> getActivities()
    {
        return rosByClass.get(Constants.ELEMENT_ACTIVITY);
    }

    
    /**
     * Obtain all registry objects representing parties
     * 
     * @return A List of RegistryObject objects, all of which are parties.
     *        Empty List if no parties exist.
     */
    public List<RegistryObject> getParties()
    {
        return rosByClass.get(Constants.ELEMENT_PARTY);
    }
    
    
    /**
     * Obtain all registry objects representing services
     * 
     * @return
     *        A List of RegistryObject objects, all of which are services.
     *        Empty List if no services exist.
     */
    public List<RegistryObject> getServices()
    {
        return rosByClass.get(Constants.ELEMENT_SERVICE);
    }
    
    
    /**
     * Obtain all registry objects
     * 
     * @return A Map of RegistryObject objects or empty Map if none exist. The 
     *        Map key is the content of the RegistryObject "key" element.
     */
    public Map<String,RegistryObject> getRegistryObjects()
    {
        return ros;
    }

    
    /**
     * Add a registry object to the RIF-CS document
     * 
     * @param r
     *    A RegistryObject
     */
    public void addRegistryObject(RegistryObject r) throws RIFCSException
    {
        doc.getDocumentElement().appendChild(r.getElement());
        ros.put(r.getKey(), r);        
        rosByClass.get(r.getObjectClassName()).add(r);
    }
    
    
    /**
     * For existing DOM, initialise and create all the supporting structures
     */
    private void initRegistryObjects() throws RIFCSException
    {
        NodeList nl = doc.getElementsByTagNameNS(Constants.NS_RIFCS, Constants.ELEMENT_REGISTRY_OBJECT);

        for (int i = 0; i < nl.getLength(); i++)
        {
            RegistryObject ro = new RegistryObject(nl.item(i));
            ros.put(ro.getKey(), ro);
            rosByClass.get(ro.getObjectClassName()).add(ro);
        }
    }
    
    
    /**
     * Initialise the object class cache
     */
    private void initObjectClassMap()
    {
        rosByClass.put(Constants.ELEMENT_COLLECTION, new ArrayList());
        rosByClass.put(Constants.ELEMENT_ACTIVITY, new ArrayList());
        rosByClass.put(Constants.ELEMENT_PARTY, new ArrayList());
        rosByClass.put(Constants.ELEMENT_SERVICE, new ArrayList());        
    }
}