/**********************************************************************
Copyright 2011 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
***********************************************************************/ 
import java.net.*;
import java.io.*;
import java.util.Map;
import java.util.HashMap;
import java.util.Scanner;
import org.w3c.dom.Document;
import javax.xml.parsers.*;
import org.xml.sax.*;



class MockDOIMintClient {

	// Location of the DOI Service
	private static final String DOIServicePoint = "http://services.ands.org.au/home/dois/";
	
	// DOI Application ID (supplied by ANDS)
	private static final String DOIApplicationID = ""; 
	
	// Suffices for certain services
	private static final String DOIMintServiceSuffix = "doi_mint.php?app_id=<app_id>&url=<url>";
	private static final String DOIUpdateServiceSuffix = "doi_update.php?app_id=<app_id>&DOI=<doi_id>";
	private static final String DOIMetadataRequestServiceSuffix = "doi_xml.php?app_id=<app_id>&DOI=<doi_id>";
	

	public static void main(String[] args) throws Exception {
		
		// Build up the URL for us to mint at
		String url_of_data = "http://test.ands.org.au/robots.txt";
		String datacite_xml_fragment_file = "test_datacite_metadata.xml";
		String mint_service_url = DOIServicePoint + DOIMintServiceSuffix;
		
		
		// Replace the required fields
		HashMap<String, String> url_replacement_values = new HashMap<String, String>();
	    url_replacement_values.put("app_id", DOIApplicationID);
		url_replacement_values.put("url", url_of_data);
		
		mint_service_url = replaceURLFields(mint_service_url, url_replacement_values);
		

		
		System.out.println("Read XML from " + datacite_xml_fragment_file);
		System.out.println("Sending query data to" + mint_service_url);
		System.out.println("Output:");
		
		String response_string = postDOIRequest(mint_service_url, loadXMLFromFile(datacite_xml_fragment_file));
		
		System.out.println(response_string);
		
		
}





	private static String postDOIRequest(String service_url, String contents) throws Exception
	{
		
		URL url = new URL(service_url);
		HttpURLConnection conn = (HttpURLConnection) url.openConnection();		
		try {
		    // Construct data
		    String data = "xml=" + URLEncoder.encode(contents, "UTF-8");

		    // Send data

			conn.setRequestMethod("POST");
		    conn.setDoOutput(true); conn.setDoInput(true);
		    OutputStreamWriter wr = new OutputStreamWriter(conn.getOutputStream());
		    wr.write(data);
		    wr.flush();

			System.out.println(conn.getResponseCode() + " - " + conn.getResponseMessage());

		    // Get the response
			StringBuffer output_buffer = new StringBuffer();
		    BufferedReader rd = new BufferedReader(new InputStreamReader(conn.getInputStream()));
		    String line;
		    while ((line = rd.readLine()) != null) {
		        output_buffer.append(line);
		    }
		    wr.close();
		    rd.close();
			
			return output_buffer.toString(); 
		} catch (Exception e) {
			// error occured
			BufferedReader rde = new BufferedReader(new InputStreamReader(conn.getErrorStream()));
		    String line;
		    while ((line = rde.readLine()) != null) {
		       System.out.println(line);
		    }
			rde.close();
		}
		finally
		{
		}
		
		return "";
			
	}





	private static String loadXMLFromFile(String filename) throws Exception
	{
	
		FileInputStream fis = new FileInputStream(filename); 
		InputStreamReader in = new InputStreamReader(fis, "UTF-8");
		Scanner s = null;
		StringBuffer xml_buffer = new StringBuffer();
        try {
            s = new Scanner(new BufferedReader(new FileReader(filename)));
			s.useDelimiter(System.getProperty("line.separator"));
            while (s.hasNext()) {
                xml_buffer.append(s.next() + System.getProperty("line.separator"));
            }
        } finally {
            if (s != null) {
                s.close();
            }
        }
		
		return xml_buffer.toString();
			
		
	}



	private static String replaceURLFields(String input_string, HashMap<String,String> replacements_map)
	{
		String return_url = input_string;
		for (String key : replacements_map.keySet()) {
		  return_url = return_url.replaceAll("\\<" + key + "\\>", replacements_map.get(key));
		}
		
		/*
		// sanity check
		if (return_url.matches(".*\\<.*\\>.*"))
		{
			// should throw an exception
			// Service Point URL contains fields which are still incomplete? 
		}
		*/
			
		return return_url;
		
	}

}

class SimpleErrorHandler implements ErrorHandler {
    public void warning(SAXParseException e) throws SAXException {
        System.out.println(e.getMessage());
    }

    public void error(SAXParseException e) throws SAXException {
        System.out.println(e.getMessage());
    }

    public void fatalError(SAXParseException e) throws SAXException {
        System.out.println(e.getMessage());
    }
}

