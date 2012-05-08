/**
 * Copyright 2006 OCLC, Online Computer Library Center
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * 
 * Note: This class is a modified version of the OCLC class.
 * Modified by The Australian National University 2008
 *
 * Date Modified: $Date: 2009-08-18 12:43:25 +1000 (Tue, 18 Aug 2009) $
 * Version: $Revision: 84 $
 */
package au.edu.apsr.harvester.oai;

import java.io.FileNotFoundException;
import java.io.IOException;
import java.io.InputStream;
import java.io.StringWriter;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.Date;
import java.util.HashMap;
import java.util.StringTokenizer;
import java.util.zip.GZIPInputStream;
import java.util.zip.InflaterInputStream;
import java.util.zip.ZipInputStream;

import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;
import javax.xml.parsers.ParserConfigurationException;
import javax.xml.transform.OutputKeys;
import javax.xml.transform.Result;
import javax.xml.transform.Source;
import javax.xml.transform.Transformer;
import javax.xml.transform.TransformerException;
import javax.xml.transform.TransformerFactory;
import javax.xml.transform.dom.DOMSource;
import javax.xml.transform.stream.StreamResult;

import org.apache.log4j.Logger;
import org.w3c.dom.DOMImplementation;
import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.NodeList;
import org.xml.sax.InputSource;
import org.xml.sax.SAXException;

/**
 * HarvesterVerb is the parent class for each of the OAI verbs.
 * 
 * @author Jeffrey A. Young, OCLC Online Computer Library Center
 */
public abstract class HarvesterVerb
{
    private static Logger log = Logger.getLogger(HarvesterVerb.class);
    
    /* Primary OAI namespaces */
    public static final String SCHEMA_LOCATION_V2_0 = "http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd";
    public static final String SCHEMA_LOCATION_V1_1_GET_RECORD = "http://www.openarchives.org/OAI/1.1/OAI_GetRecord http://www.openarchives.org/OAI/1.1/OAI_GetRecord.xsd";
    public static final String SCHEMA_LOCATION_V1_1_IDENTIFY = "http://www.openarchives.org/OAI/1.1/OAI_Identify http://www.openarchives.org/OAI/1.1/OAI_Identify.xsd";
    public static final String SCHEMA_LOCATION_V1_1_LIST_IDENTIFIERS = "http://www.openarchives.org/OAI/1.1/OAI_ListIdentifiers http://www.openarchives.org/OAI/1.1/OAI_ListIdentifiers.xsd";
    public static final String SCHEMA_LOCATION_V1_1_LIST_METADATA_FORMATS = "http://www.openarchives.org/OAI/1.1/OAI_ListMetadataFormats http://www.openarchives.org/OAI/1.1/OAI_ListMetadataFormats.xsd";
    public static final String SCHEMA_LOCATION_V1_1_LIST_RECORDS = "http://www.openarchives.org/OAI/1.1/OAI_ListRecords http://www.openarchives.org/OAI/1.1/OAI_ListRecords.xsd";
    public static final String SCHEMA_LOCATION_V1_1_LIST_SETS = "http://www.openarchives.org/OAI/1.1/OAI_ListSets http://www.openarchives.org/OAI/1.1/OAI_ListSets.xsd";
    private Document doc = null;
    private String schemaLocation = null;
    private String requestURL = null;
    private String jsessionid = null;
    private static HashMap<Thread,DocumentBuilder> builderMap = new HashMap<Thread,DocumentBuilder>();
    private static Element namespaceElement = null;
    private static DocumentBuilderFactory factory = null;
    private static Exception staticBlockException = null;
    
    private static Transformer idTransformer = null;
    private static Thread theThread;
    static
    {
        try
        {
            /* create transformer */
            TransformerFactory xformFactory = TransformerFactory.newInstance();
            idTransformer = xformFactory.newTransformer();
            idTransformer.setOutputProperty(OutputKeys.OMIT_XML_DECLARATION, "yes");
            
            /* Load DOM Document */
            factory = DocumentBuilderFactory.newInstance();
            factory.setNamespaceAware(true);
            factory.setValidating(true);
            Thread t = Thread.currentThread();
            theThread = t;
            DocumentBuilder builder = factory.newDocumentBuilder();
            builderMap.put(t, builder);

            DOMImplementation impl = builder.getDOMImplementation();
            Document namespaceHolder = impl.createDocument(
                    "http://www.oclc.org/research/software/oai/harvester",
                    "harvester:namespaceHolder", null);
            namespaceElement = namespaceHolder.getDocumentElement();
            namespaceElement.setAttributeNS("http://www.w3.org/2000/xmlns/",
                    "xmlns:harvester",
            "http://www.oclc.org/research/software/oai/harvester");
            namespaceElement.setAttributeNS("http://www.w3.org/2000/xmlns/",
                    "xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
            namespaceElement.setAttributeNS("http://www.w3.org/2000/xmlns/",
                    "xmlns:oai20", "http://www.openarchives.org/OAI/2.0/");
            namespaceElement.setAttributeNS("http://www.w3.org/2000/xmlns/",
                    "xmlns:oai11_GetRecord",
            "http://www.openarchives.org/OAI/1.1/OAI_GetRecord");
            namespaceElement.setAttributeNS("http://www.w3.org/2000/xmlns/",
                    "xmlns:oai11_Identify",
            "http://www.openarchives.org/OAI/1.1/OAI_Identify");
            namespaceElement.setAttributeNS("http://www.w3.org/2000/xmlns/",
                    "xmlns:oai11_ListIdentifiers",
            "http://www.openarchives.org/OAI/1.1/OAI_ListIdentifiers");
            namespaceElement
            .setAttributeNS("http://www.w3.org/2000/xmlns/",
                    "xmlns:oai11_ListMetadataFormats",
            "http://www.openarchives.org/OAI/1.1/OAI_ListMetadataFormats");
            namespaceElement.setAttributeNS("http://www.w3.org/2000/xmlns/",
                    "xmlns:oai11_ListRecords",
            "http://www.openarchives.org/OAI/1.1/OAI_ListRecords");
            namespaceElement.setAttributeNS("http://www.w3.org/2000/xmlns/",
                    "xmlns:oai11_ListSets",
            "http://www.openarchives.org/OAI/1.1/OAI_ListSets");
        }
        catch (ParserConfigurationException e)
        {
            staticBlockException = e;
        }
        catch (TransformerException te)
        {
            staticBlockException = te;
        }
    }
    
    /**
     * Get the OAI response as a DOM object
     * 
     * @return the DOM for the OAI response
     */
    public Document getDocument()
    {
        return doc;
    }
    
    /**
     * Get the xsi:schemaLocation for the OAI response
     * 
     * @return the xsi:schemaLocation value
     */
    public String getSchemaLocation()
    {
        return schemaLocation;
    }
    
    /**
     * Get the OAI errors
     * @return a NodeList of /oai:OAI-PMH/oai:error elements
     * @throws TransformerException
     */
    public NodeList getErrors() throws TransformerException
    {
        if (SCHEMA_LOCATION_V2_0.equals(getSchemaLocation()))
        {
            NodeList nl =  doc.getElementsByTagNameNS("http://www.openarchives.org/OAI/2.0/", "error");
            if (nl.getLength() == 0)
            {
                return null;
            }
            return nl;
        }
        else
        {
            return null;
        }
    }
    
    
    /**
     * Get a list of nodes
     * @return a NodeList of /oai:OAI-PMH/oai:error elements
     * @throws TransformerException
     */
    public NodeList getNodes(String ns,
                             String localName) throws TransformerException
    {
        return doc.getElementsByTagNameNS(ns, localName);
    }
    
    
    /**
     * Get the OAI request URL for this response
     * @return the OAI request URL as a String
     */
    public String getRequestURL()
    {
        return requestURL;
    }
    
    /**
     * Mock object creator (for unit testing purposes)
     */
    public HarvesterVerb() throws Exception
    {
        if (staticBlockException == null)
        {
            throw staticBlockException;
        }
    }
    
    /**
     * Performs the OAI request
     * 
     * @param requestURL
     * @throws IOException
     * @throws ParserConfigurationException
     * @throws SAXException
     * @throws TransformerException
     */
    public HarvesterVerb(String requestURL) throws Exception, IOException,
    ParserConfigurationException, SAXException, TransformerException
    {
        if (staticBlockException != null)
        {
            throw staticBlockException;
        }
        
        harvest(requestURL);
    }
    
    /**
     * Performs the OAI request
     * 
     * @param requestURL
     * @throws IOException
     * @throws ParserConfigurationException
     * @throws SAXException
     * @throws TransformerException
     */
    public void harvest(String requestURL) throws IOException,
    ParserConfigurationException, SAXException, TransformerException
    {
        this.requestURL = requestURL;
        InputStream in = null;
        URL url = new URL(requestURL);
        HttpURLConnection con = null;
        int responseCode = 0;
        do
        {
            con = (HttpURLConnection) url.openConnection();
            con.setConnectTimeout(10000);
            con.setRequestProperty("User-Agent", "OAIHarvester/2.0");
            con.setRequestProperty("Accept-Encoding",
            "compress, gzip, identify");
            try
            {
                responseCode = con.getResponseCode();
                log.debug("responseCode=" + responseCode);
            }
            catch (FileNotFoundException e)
            {
                // assume it's a 503 response
                log.info(requestURL, e);
                responseCode = HttpURLConnection.HTTP_UNAVAILABLE;
            }
            
            if (responseCode == HttpURLConnection.HTTP_UNAVAILABLE)
            {
                long retrySeconds = con.getHeaderFieldInt("Retry-After", -1);
                if (retrySeconds == -1)
                {
                    long now = (new Date()).getTime();
                    long retryDate = con.getHeaderFieldDate("Retry-After", now);
                    retrySeconds = retryDate - now;
                }
                
                if (retrySeconds == 0)
                { // Apparently, it's a bad URL
                    throw new FileNotFoundException("Bad URL:" + requestURL);
                }
                
                log.error("Server response: Retry-After=" + retrySeconds);
                
                if (retrySeconds > 0)
                {
                    try
                    {
                        Thread.sleep(retrySeconds * 1000);
                    }
                    catch (InterruptedException ie)
                    {
                        log.error("InterruptedException", ie);
                    }
                }
            }
        } while (responseCode == HttpURLConnection.HTTP_UNAVAILABLE);
        
        // in case the data provider uses session ids with cookie
        String key = "";
        for (int i = 1;(key = con.getHeaderFieldKey(i)) != null; i++)
        {
            if (key.equalsIgnoreCase("set-cookie"))
            {
                String id = con.getHeaderField(key);
                jsessionid = id.substring(id.indexOf("=") + 1, id.indexOf(";"));
            }
        }
        log.info("session id = " + jsessionid);
        
        String contentEncoding = con.getHeaderField("Content-Encoding");
        log.debug("contentEncoding=" + contentEncoding);
        
        if ("compress".equals(contentEncoding))
        {
            ZipInputStream zis = new ZipInputStream(con.getInputStream());
            zis.getNextEntry();
            in = zis;
        }
        else if ("gzip".equals(contentEncoding))
        {
            in = new GZIPInputStream(con.getInputStream());
        }
        else if ("deflate".equals(contentEncoding))
        {
            in = new InflaterInputStream(con.getInputStream());
        }
        else
        {
            in = con.getInputStream();
        }
        
        InputSource data = new InputSource(in);
        Thread t = Thread.currentThread();

        DocumentBuilder builder = (DocumentBuilder) builderMap.get(t);
        if (builder == null)
        {
            builder = factory.newDocumentBuilder();
        }
        doc = builder.parse(data);
                
        String attString = doc.getDocumentElement().getAttributeNS("http://www.w3.org/2001/XMLSchema-instance", "schemaLocation");
        StringTokenizer tokenizer = null;
        
        if (attString != null && attString.length() > 0)
        {
            tokenizer = new StringTokenizer(attString, " ");
        }
        else
        {
            log.info("WARNING: xsi:schemaLocation not found");
        }
        
        StringBuffer sb = new StringBuffer();
        while (tokenizer.hasMoreTokens())
        {
            if (sb.length() > 0)
                sb.append(" ");
            sb.append(tokenizer.nextToken());
        }
        this.schemaLocation = sb.toString();
    }

    
    public String toString()
    {
        Source input = new DOMSource(getDocument());
        
        StringWriter sw = new StringWriter();
        Result output = new StreamResult(sw);
        try
        {
            idTransformer.transform(input, output);
            return sw.toString();
        }
        catch (TransformerException e)
        {
            log.error("Transformer", e);
            return null;
        }
    }
    
    
    protected String getElementContent(String ns,
                                       String tagName) throws NoSuchFieldException
    {
        NodeList nl = getDocument().getElementsByTagNameNS(ns, tagName);
        if (nl.getLength() > 0)
        {
            return nl.item(0).getTextContent();
        }
        else
        {
            return "";
        }
    }
    
    
    protected String getJSessionID()
    {
        return jsessionid;
    }
}