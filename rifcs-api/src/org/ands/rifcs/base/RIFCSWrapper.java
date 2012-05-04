/**
 * Date Modified: $Date: 2012-04-04 12:13:39 +1000 (Wed, 04 Apr 2012) $
 * Version: $Revision: 1695 $
 * 
 * Copyright 2008 The Australian National University (ANU)
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
import java.io.StringWriter;
import java.net.URL;
import java.net.MalformedURLException;

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

import javax.xml.transform.Result;
import javax.xml.transform.stream.StreamResult;
import javax.xml.transform.Transformer;
import javax.xml.transform.TransformerException;
import javax.xml.transform.TransformerFactory;

import org.w3c.dom.*;
import org.w3c.dom.ls.*;

import org.ands.rifcs.base.Constants;

import org.xml.sax.SAXException;

/**
 * Class for wrapping RIFCS documents.
 * 
 * Because different profiles will have different requirements only a DOM
 * Document object get is currently supported.
 * 
 * @author Scott Yeadon
 *
 */
public class RIFCSWrapper
{
    private Document doc = null;
    private RIFCS rifcs = null;
    
    
    /**
     * Construct a RIFCS wrapper containing an empty RIFCS document.
     * The RIFCS document will consist only of a RIFCS element with
     * no attributes set and no sub-elements. Used when creating a 
     * new RIFCS document.
     * 
     * @exception RIFCSException
     */     
    public RIFCSWrapper() throws RIFCSException
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
            rifcs = new RIFCS(doc);
        }
        catch (ParserConfigurationException pce)
        {
            throw new RIFCSException(pce);
        }
    }
    
    
    /**
     * Construct a RIFCS wrapper for an existing RIFCS document.
     * 
     * @param d 
     *        A w3c Document representing a RIFCS DOM
     *        
     * @exception RIFCSException
     */
    public RIFCSWrapper(Document d) throws RIFCSException
    {
        this.doc = d;
        rifcs = new RIFCS(d);
    }
    
    
    /**
     * Obtain the DOM document
     * 
     * @return 
     *        A w3c Document representing a RIFCS DOM
     */
    public Document getRIFCSDocument()
    {
       return this.doc;
    }
    
    
    /**
     * Obtain a RIFCS object
     * 
     * @return 
     *        a RIFCS object representing a RIFCS root element
     */     
    public RIFCS getRIFCSObject()
    {
        return this.rifcs;
    }
    
    
    /**
     * Write a RIFCS document to an output stream
     * 
     * @param os 
     *        The OutputStream to write the data to
     */
    public void write(OutputStream os)
    {
        DOMImplementation impl = doc.getImplementation();
        DOMImplementationLS implLS = (DOMImplementationLS)impl.getFeature("LS","3.0");
        
        LSOutput lso = implLS.createLSOutput();
        lso.setByteStream(os);
        LSSerializer writer = implLS.createLSSerializer();
        DOMConfiguration domConfig = writer.getDomConfig();
        domConfig.setParameter("format-pretty-print", Boolean.valueOf(true));
        writer.write(doc, lso);
    }
    
    
    /**
     * Output a RIFCS document in string form.
     * 
     * @return 
     *        The RIFCS document in string form or <code>null</code> if an exception occurs
     */     
    public String toString()
    {
        try
        {
            TransformerFactory factory = TransformerFactory.newInstance();
            Transformer transformer = factory.newTransformer();
            StringWriter writer = new StringWriter();
            Result result = new StreamResult(writer);
            Source source = new DOMSource(doc);
            transformer.transform(source, result);
            writer.close();
            return writer.toString();
        }
        catch (TransformerException te)
        {
            return null;
        }
        catch (IOException ioe)
        {
            return null;
        }
        //DOMImplementation impl = doc.getImplementation();
        //DOMImplementationLS implLS = (DOMImplementationLS) impl.getFeature("LS","3.0");
        //LSSerializer writer = implLS.createLSSerializer();
        // This is to suppress the xml header, with version and the encoding being automatically generated
        //writer.getDomConfig().setParameter("xml-declaration", false);
        //return writer.writeToString(doc);
    }
    
    
    /**
     * Validate against the most recent rif-cs schema. The schema is
     * accessed remotely from the production site. If wanting to use a
     * local cached schema use the other validate method.
     * 
     * @exception SAXException
     *      if document is invalid
     * @exception MalformedURLException
     *      if schema URL is invalid
     * @exception IOException
     *      if URL stream cannot be accessed
     */     
    public void validate() throws SAXException, MalformedURLException, IOException, ParserConfigurationException
    {
        // create a SchemaFactory capable of understanding WXS schemas
        SchemaFactory factory = SchemaFactory.newInstance(XMLConstants.W3C_XML_SCHEMA_NS_URI);

        Schema schema = factory.newSchema(doXercesWorkaround());

        // create a Validator instance, which can be used to validate an instance document
        Validator validator = schema.newValidator();
        validator.validate(new DOMSource(doc));
    }

    // Xerces cannot handle multiple schema files with same namespace so
    // need to work around this
    private Source doXercesWorkaround() throws SAXException, MalformedURLException, IOException, ParserConfigurationException
    {
        DocumentBuilderFactory factory = DocumentBuilderFactory.newInstance();
        factory.setNamespaceAware(true);
        DocumentBuilder builder = factory.newDocumentBuilder();

        Document docRO = builder.parse(new URL(Constants.SCHEMA_REGISTRY_OBJECTS).openStream());
        Document docActivity = builder.parse(new URL(Constants.SCHEMA_ACTIVITY).openStream());
        Document docCollection = builder.parse(new URL(Constants.SCHEMA_COLLECTION).openStream());
        Document docParty = builder.parse(new URL(Constants.SCHEMA_PARTY).openStream());
        Document docService = builder.parse(new URL(Constants.SCHEMA_SERVICE).openStream());
        Document docTypes = builder.parse(new URL(Constants.SCHEMA_REGISTRY_TYPES).openStream());

        removeElements(docRO, "xsd:include");
        Element xmlImport = docRO.createElementNS("http://www.w3.org/2001/XMLSchema", "xsd:import");
        xmlImport.setAttribute("namespace", "http://www.w3.org/XML/1998/namespace");
        xmlImport.setAttribute("schemaLocation", "http://www.w3.org/2001/xml.xsd");
        Element root = docRO.getDocumentElement();
        root.insertBefore(xmlImport, root.getElementsByTagName("xsd:element").item(0));
        
        removeElements(docActivity, "xsd:include");
        removeElements(docCollection, "xsd:include");
        removeElements(docParty, "xsd:include");
        removeElements(docService, "xsd:include");
        removeElements(docTypes, "xsd:import");
        
        addToSchema(docRO, docActivity);
        addToSchema(docRO, docCollection);
        addToSchema(docRO, docParty);
        addToSchema(docRO, docService);
        addToSchema(docRO, docTypes);
                
        return new DOMSource(docRO);
    }
    

    // Only to be called from Xerces workaround
    private void removeElements(Document targetDoc,
                                String element)
    {
        NodeList nl = targetDoc.getDocumentElement().getElementsByTagName(element);

        Node[] n = new Node[nl.getLength()];

        // xerces updates nodelist dynamically so need to keep
        // references in a temp array in order to remove elements
        for (int i=0; i < nl.getLength(); i++)
        {
            n[i] = nl.item(i);
        }
        
        for (int i=0; i < n.length; i++)
        {
            targetDoc.getDocumentElement().removeChild(n[i]);
        }        
    }
    
    
    // Only to be called from Xerces workaround
    private void addToSchema(Document targetDoc,
                             Document sourceDoc)
    {
        NodeList nl = sourceDoc.getDocumentElement().getChildNodes();
        for (int i=0; i < nl.getLength(); i++)
        {
            Node n = targetDoc.importNode(nl.item(i), true);
            targetDoc.getDocumentElement().appendChild(n);
        }                
    }
    
    
    /**
     * Validate against the user-provided in schema. Note that Xerces
     * cannot handle schema include directives where the fragments are
     * in the same namespace so a single schema file will need to be
     * provided in these cases.
     * 
     * @param schemaUrl
     *          the URL of a schema to validate the METS document against
     *          
     * @exception SAXException
     *      if document is invalid
     * @exception MalformedURLException
     *      if schema URL is invalid
     * @exception IOException
     *      if URL stream cannot be accessed
     */     
    public void validate(String schemaUrl) throws SAXException, MalformedURLException, IOException
    {
        // create a SchemaFactory capable of understanding WXS schemas
        SchemaFactory factory = SchemaFactory.newInstance(XMLConstants.W3C_XML_SCHEMA_NS_URI);

        URL theSchema = new URL(schemaUrl);
        // load a WXS schema, represented by a Schema instance
        Source schemaFile = new StreamSource(theSchema.openStream());
        Schema schema = factory.newSchema(schemaFile);

        // create a Validator instance, which can be used to validate an instance document
        Validator validator = schema.newValidator();

        validator.validate(new DOMSource(doc));
    }
}