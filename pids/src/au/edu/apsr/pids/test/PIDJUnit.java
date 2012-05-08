package au.edu.apsr.pids.test;

import java.io.BufferedReader;
import java.io.InputStream;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.OutputStreamWriter;
import java.io.UnsupportedEncodingException;
import java.net.HttpURLConnection;
import javax.net.ssl.HttpsURLConnection;
import javax.net.ssl.SSLSession;
import java.net.MalformedURLException;
import java.net.URL;
import java.net.URLEncoder;
import java.text.ParseException;

import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.NodeList;
import javax.xml.parsers.DocumentBuilderFactory;
import javax.xml.parsers.DocumentBuilder;

import org.junit.*;
import static org.junit.Assert.*;


public class PIDJUnit
{
	private String params;
	
	@Before
	public void setUp() throws Exception
	{
	    System.out.println(System.getProperty("basedir"));
		HostnameVerifier hv = new HostnameVerifier();
		trustAllHttpsCertificates();
		HttpsURLConnection.setDefaultHostnameVerifier(hv);

		params = "<request name=\"addValue\">" + 
		"    <properties>" + "        <property name=\"authType\" value=\"SSLHost\"/>" + 
		"        <property name=\"identifier\" value=\"scott\"/>" +
		"        <property name=\"authDomain\" value=\"150.203.59.132\"/>" +
		"        <property name=\"appId\" value=\"528ba9dab93f680dd6b5d8d8e69a4da3f088580c\"/>" +		
		"    </properties>" +
		"</request>";
	}

/*	
	@org.junit.Test public void testMintDescType() throws Exception
	{
		URL url = new URL("https://localhost:8443/pids/mint?type=DESC&value=ABC");
		Document response = doConn(url, params);
	    assertTrue(check(response).equals("success"));
	}

	@org.junit.Test public void testMintDescTypeIndex() throws Exception
	{
		URL url = new URL("https://localhost:8443/pids/mint?type=DESC&value=ABC&index=99");
		Document response = doConn(url, params);
	    assertTrue(check(response).equals("success"));
	}
*/
	@org.junit.Test public void testMintNoParms() throws Exception
	{
		URL url = new URL("https://localhost:8443/pids/mint");
		Document response = doConn(url, params);
	    assertTrue(check(response).equals("failure"));
	}

	@org.junit.Test public void testMintNoValue() throws Exception
	{
		URL url = new URL("https://localhost:8443/pids/mint?type=DESC&index=3");
		Document response = doConn(url, params);
	    assertTrue(check(response).equals("failure"));
	}

	private String check(Document response)
	{
		NodeList nl = response.getElementsByTagName("response");
		if (nl.getLength() > 0)
		{
			return ((Element)nl.item(0)).getAttribute("type");
		}

    	return "bad response";
	}
    
    private Document doConn(URL url, String params) throws Exception
    {
        int responseCode = 0;
        HttpsURLConnection conn = null;
        
        conn = (HttpsURLConnection)url.openConnection();
        conn.setConnectTimeout(10000);
        conn.setRequestMethod("POST");
        conn.setAllowUserInteraction(false);
        conn.setDoOutput(true);
        conn.setRequestProperty("User-Agent", "PIDTest/1.0");
        conn.setRequestProperty( "Content-Type", "multipart/form-data");
        conn.setRequestProperty( "Content-Encoding", "UTF-8");
        conn.setRequestProperty( "Content-Length", Integer.toString(params.length()));
        OutputStreamWriter out = new OutputStreamWriter(conn.getOutputStream());
        out.write(params);
        out.flush();
        out.close();
        responseCode = conn.getResponseCode();
/*        
        BufferedReader in = new BufferedReader (new InputStreamReader(conn.getInputStream()));
		String temp;
		String response = "";
		while ((temp = in.readLine()) != null)
		{
			response += temp + "\n";
		}
		temp = null;
		in.close(); */
		DocumentBuilderFactory factory = DocumentBuilderFactory.newInstance();
        DocumentBuilder builder = factory.newDocumentBuilder();
        Document d = builder.parse(conn.getInputStream());
        conn.disconnect();
        return d;
    }
    
    // Just add these two functions in your program 

    public static class miTM implements javax.net.ssl.TrustManager,
            javax.net.ssl.X509TrustManager
    {
        public java.security.cert.X509Certificate[] getAcceptedIssuers()
        {
            return null;
        }
 
        public boolean isServerTrusted(
                java.security.cert.X509Certificate[] certs)
        {
            return true;
        }
 
        public boolean isClientTrusted(
                java.security.cert.X509Certificate[] certs)
        {
            return true;
        }
 
        public void checkServerTrusted(
                java.security.cert.X509Certificate[] certs, String authType)
                throws java.security.cert.CertificateException
        {
            return;
        }
 
        public void checkClientTrusted(
                java.security.cert.X509Certificate[] certs, String authType)
                throws java.security.cert.CertificateException
        {
            return;
        }
    }

    
        private static void trustAllHttpsCertificates() throws Exception
    {
 
        //  Create a trust manager that does not validate certificate chains:
 
        javax.net.ssl.TrustManager[] trustAllCerts =
 
        new javax.net.ssl.TrustManager[1];
 
        javax.net.ssl.TrustManager tm = new miTM();
 
        trustAllCerts[0] = tm;
 
        javax.net.ssl.SSLContext sc =
 
        javax.net.ssl.SSLContext.getInstance("SSL");
 
        sc.init(null, trustAllCerts, null);
 
        javax.net.ssl.HttpsURLConnection.setDefaultSSLSocketFactory(
 
        sc.getSocketFactory());
 
    }
    
    private static class HostnameVerifier implements javax.net.ssl.HostnameVerifier
    {
        public boolean verify(String urlHostName, SSLSession session)
        {
            return true;
        }
    }
}
