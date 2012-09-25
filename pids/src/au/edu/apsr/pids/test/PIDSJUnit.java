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
import java.util.Properties;
import java.util.Arrays;

import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.NodeList;
import javax.xml.parsers.DocumentBuilderFactory;
import javax.xml.parsers.DocumentBuilder;
import javax.xml.xpath.XPathFactory;
import javax.xml.xpath.XPathConstants;
import javax.xml.xpath.XPath;

import org.junit.*;
import static org.junit.Assert.*;
import org.junit.Rule;
import org.junit.rules.TestName;
import static org.junit.Assume.*;


public class PIDSJUnit
{
	private static String params;

	private static Properties properties;

	@Rule public TestName name = new TestName();

	private XPath xPath;

	@BeforeClass public static void setUpClass() throws Exception
	{
		properties = new Properties();
		properties.load(PIDSJUnit.class.getResourceAsStream("PIDSJUnit.properties"));

		params = "<request name=\"addValue\">\n" + 
			"    <properties>\n" +
			"        <property name=\"authType\" value=\"SSLHost\"/>\n" + 
			"        <property name=\"identifier\" value=\"scott\"/>\n" +
			"        <property name=\"authDomain\" value=\"" +
			properties.getProperty("authDomain") +
			"\"/>\n" +
			"        <property name=\"appId\" value=\"" +
			properties.getProperty("appId") + 
			"\"/>\n" +		
			"    </properties>\n" +
			"</request>";

		if (properties.getProperty("verbose").equals("true")) {
			System.out.println("Post data:\n" + params + "\n");
		}
	}

	@Before public void setUp() throws Exception
	{
		// Skip tests if there is a list of tests to run in the properties file
		if (properties.getProperty("tests") != null) {
			String tests[] = properties.getProperty("tests").split(",");
			if (!Arrays.asList(tests).contains(name.getMethodName())) {
				// Skip this test
				assumeTrue(false);
			}
		}

		//System.out.println(System.getProperty("basedir"));
		HostnameVerifier hv = new HostnameVerifier();
		trustAllHttpsCertificates();
		HttpsURLConnection.setDefaultHostnameVerifier(hv);

		if (properties.getProperty("verbose").equals("true")) {
			// Prints the name of the test that's about to run
			System.out.print(name.getMethodName());
			System.out.println("------------------------------------------------".substring(name.getMethodName().length()));
		}

		xPath = XPathFactory.newInstance().newXPath();
	}

	@Test public void testMintUrlType() throws Exception
	{
		String testUrl = "http://example.org/PIDS-URL-Test";
		URL url = new URL(properties.getProperty("PIDSService") + "/mint?type=URL&value=" + testUrl);
		Document response = doConn(url, params);

		assertTrue(check(response).equals("success"));

		assertTrue(urlFromResponse(response).equals(testUrl));
	}

	@Test public void testMintDescType() throws Exception
	{
		URL url = new URL(properties.getProperty("PIDSService") + "/mint?type=DESC&value=ABC");
		Document response = doConn(url, params);
		assertTrue(check(response).equals("success"));
	}

	@Test public void testMintDescTypeIndex() throws Exception
	{
		String testDesc = "ABC";
		String testIndex = "99";

		URL url = new URL(properties.getProperty("PIDSService") + "/mint?type=DESC&value=" + testDesc + "&index=" + testIndex);
		Document response = doConn(url, params);
		assertTrue(check(response).equals("success"));

		assertTrue(descFromResponse(response, testIndex).equals(testDesc));

		String returnedIndex = xPath.evaluate("/response/identifier/property[@type='DESC']/@index",
			response.getDocumentElement());
		assertTrue(returnedIndex.equals(testIndex));
	}

	@Test public void testMintNoParams() throws Exception
	{
		URL url = new URL(properties.getProperty("PIDSService") + "/mint");
		Document response = doConn(url, params);
		assertTrue(check(response).equals("failure"));
	}

	@Test public void testMintNoValue() throws Exception
	{
		URL url = new URL(properties.getProperty("PIDSService") + "/mint?type=DESC&index=3");
		Document response = doConn(url, params);
		assertTrue(check(response).equals("failure"));
	}

	@Test public void testAddValue() throws Exception
	{
		Integer index = 11;
		String handle = handleFromResponse(mint(index));

		// Add new URL value to handle
		String newUrl = "http://example.org/new";
		URL url = new URL(properties.getProperty("PIDSService") + "/addValue?type=URL&value=" + newUrl + "&handle=" + handle);
		Document response = doConn(url, params);
		assertTrue(check(response).equals("success"));

		assertTrue(handleFromResponse(response).equals(handle));

		String returnedUrl = xPath.evaluate("/response/identifier/property[@type='URL' and not(@index='" + index + "')]/@value",
			response.getDocumentElement());

		assertTrue(newUrl.equals(returnedUrl));
	}

	@Test public void testAddValueByIndex() throws Exception
	{
		String handle = handleFromResponse(mint());

		// Add new DESC value to handle
		Integer index = 7;
		String desc = "Example description.";
		String encodedDesc = URLEncoder.encode(desc, "UTF-8");
		URL url = new URL(properties.getProperty("PIDSService") +
			"/addValueByIndex?type=DESC&value=" + encodedDesc +
			"&handle=" + handle +
			"&index=" + index);
		Document response = doConn(url, params);
		assertTrue(check(response).equals("success"));

		assertTrue(handleFromResponse(response).equals(handle));
		assertTrue(descFromResponse(response, index).equals(desc));
	}

	@Test public void testModifyValueByIndex() throws Exception
	{
		Integer index = 53;
		String handle = handleFromResponse(mint(index));
		String newUrl = "https://20.1.4.99:778/test:test-\u5443.0";

		URL url = new URL(properties.getProperty("PIDSService") +
			"/modifyValueByIndex?type=URL&value=" + newUrl +
			"&handle=" + handle +
			"&index=" + index);
		Document response = doConn(url, params);
		assertTrue(check(response).equals("success"));

		assertTrue(handleFromResponse(response).equals(handle));
		assertTrue(urlFromResponse(response, index).equals(newUrl));
	}

	@Test public void testDeleteValueByIndex() throws Exception
	{
		Document mintResponse = mint();
		String handle = handleFromResponse(mintResponse);
		String firstPropertyIndex = xPath.evaluate("/response/identifier/property/@index", mintResponse.getDocumentElement());

		URL url = new URL(properties.getProperty("PIDSService") +
			"/deleteValueByIndex?" +
			"handle=" + handle +
			"&index=" + firstPropertyIndex);
		Document deleteResponse = doConn(url, params);
		assertTrue(check(deleteResponse).equals("success"));

		String count = xPath.evaluate("count(/response/identifier/property[@index='" + firstPropertyIndex + "'])",
			deleteResponse.getDocumentElement());
		assertTrue("0".equals(count));
	}

	@Test public void testListHandles() throws Exception
	{
		String handle = handleFromResponse(mint());
		String decrementedHandle = handle.replaceFirst("[0-9]*$", "") + (Integer.parseInt(handle.replaceFirst("^.*[^0-9]", "")) - 1);

		URL url = new URL(properties.getProperty("PIDSService") +
			"/listHandles?" +
			"startHandle=" + decrementedHandle);
		Document response = doConn(url, params);
		assertTrue(check(response).equals("success"));

		String count = xPath.evaluate("count(/response/identifiers/identifier[@handle='" + handle + "'])",
			response.getDocumentElement());
		assertTrue(!"0".equals(count));
	}

	@Test public void testGetHandle() throws Exception
	{
		String handle = handleFromResponse(mint());

		URL url = new URL(properties.getProperty("PIDSService") +
			"/getHandle?" +
			"handle=" + handle);
		Document response = doConn(url, params);
		assertTrue(check(response).equals("success"));

		assertTrue(handle.equals(handleFromResponse(response)));
	}

	@Test public void testGetNonexistentHandle() throws Exception
	{
		String handle = "THIS+IS+NOT+A+HANDLE";

		URL url = new URL(properties.getProperty("PIDSService") +
			"/getHandle?" +
			"handle=" + handle);
		Document response = doConn(url, params);
		assertTrue(check(response).equals("failure"));
	}

	@Test public void testMintUndefinedType() throws Exception
	{
		URL url = new URL(properties.getProperty("PIDSService") +
			"/mint?" +
			"type=THISISNOTATYPE");
		Document response = doConn(url, params);
		assertTrue(check(response).equals("failure"));
	}

	@Test public void testAddValueByIndexRepeatedIndex() throws Exception
	{
		String index = "10238";

		URL url = new URL(properties.getProperty("PIDSService") + "/mint?type=DESC&value=testvalue&index=" + index);
		Document response = doConn(url, params);
		assertTrue(check(response).equals("success"));

		String handle = handleFromResponse(response);

		url = new URL(properties.getProperty("PIDSService") + "/addValueByIndex?type=DESC&value=newtestvalue" +
			"&index=" + index + 
			"&handle=" + handle);
		response = doConn(url, params);
		assertTrue(check(response).equals("failure"));
	}

	@Test public void testModifyValueByIndexNonexistentIndex() throws Exception
	{
		URL url = new URL(properties.getProperty("PIDSService") + "/mint?type=DESC&value=testvalue" +
			"&index=55");
		Document response = doConn(url, params);
		assertTrue(check(response).equals("success"));

		String handle = handleFromResponse(response);

		url = new URL(properties.getProperty("PIDSService") + "/modifyValueByIndex?type=DESC&value=newtestvalue" +
			"&index=60" + 
			"&handle=" + handle);
		response = doConn(url, params);
		assertTrue(check(response).equals("failure"));
	}


	private String descFromResponse(Document response) throws Exception
	{
		return xPath.evaluate("/response/identifier/property[@type='DESC']/@value", response.getDocumentElement());
	}

	private String descFromResponse(Document response, Integer index) throws Exception
	{
		return descFromResponse(response, index.toString());
	}

	private String descFromResponse(Document response, String index) throws Exception
	{
		return xPath.evaluate("/response/identifier/property[@type='DESC' and @index='" + index + "']/@value",
			response.getDocumentElement());
	}

	private String urlFromResponse(Document response) throws Exception
	{
		return xPath.evaluate("/response/identifier/property[@type='URL']/@value", response.getDocumentElement());
	}

	private String urlFromResponse(Document response, Integer index) throws Exception
	{
		return xPath.evaluate("/response/identifier/property[@type='URL' and @index='" + index + "']/@value",
			response.getDocumentElement());
	}

	private String handleFromResponse(Document response) throws Exception
	{
		return xPath.evaluate("/response/identifier/@handle", response.getDocumentElement());
	}

	private Document mint() throws Exception
	{
		return mint(null);
	}

	private Document mint(Integer index) throws Exception
	{
		URL url = new URL(properties.getProperty("PIDSService") + "/mint?type=URL&value=http://example.org");

		if (index != null)
			url = new URL(url + "&index=" + index);

		Document response = doConn(url, params);
		assertTrue(check(response).equals("success"));
		return response;
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
		conn.setRequestProperty("Content-Type", "multipart/form-data");
		conn.setRequestProperty("Content-Encoding", "UTF-8");
		conn.setRequestProperty("Content-Length", Integer.toString(params.length()));
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

		if (properties.getProperty("verbose").equals("true")) {
			System.out.println("URL: " + url);
			org.w3c.dom.ls.DOMImplementationLS domImplLS = (org.w3c.dom.ls.DOMImplementationLS) d.getImplementation();
			org.w3c.dom.ls.LSSerializer serializer = domImplLS.createLSSerializer();
			System.out.println("Response:\n" + serializer.writeToString(d) + "\n");
		}

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
