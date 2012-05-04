package au.org.ands;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.util.ArrayList;
import java.util.List;
import java.util.TreeMap;

import org.apache.commons.configuration.XMLConfiguration;

public class Main {
	static XMLConfiguration config = null;

	public static void main(String[] args) {
		WebPageChecker pageChecker = new WebPageChecker();
		TreeMap<String, Boolean> urls = new TreeMap<>();
		try {
			config = new XMLConfiguration("conf/config.xml");
			pageChecker.setUp(config.getString("driver"),
					config.getString("driver-exe-path"));
			CSVHandler csvHandler = new CSVHandler(config.getString("csv-file"));
			List<String[]> lines = csvHandler.readAll();
			List<String[]> wlines = new ArrayList<String[]>();
			String[] header = lines.get(0);
			String[] wline = new String[header.length + 1];
			wline[header.length] = "Status!";
			System.arraycopy(header, 0, wline, 0, header.length);
			wlines.add(wline);
			for (int i = 1; i < lines.size(); i++) {
				String[] line = lines.get(i);
				wline = new String[line.length + 1];
				Boolean failed = urls.get(line[2].trim());
				System.arraycopy(line, 0, wline, 0, line.length);
				if (failed == null) {
					String url = (line[2].toLowerCase().startsWith("http://") || line[2]
							.toLowerCase().startsWith("ftp://")) ? line[2]
							: ("http://" + line[2]);
					pageChecker.check(url);
					System.out.print("\r\nThe failure reason was: " + line[4]
							+ "\r\n");
					System.out.print("Does the page seem OK? (y/n)[y]:");
					BufferedReader br = new BufferedReader(
							new InputStreamReader(System.in));
					String answer = br.readLine();
					failed = answer.trim().equalsIgnoreCase("n");
					urls.put(line[2].trim(), failed);
				}
				if (failed)
					wline[line.length] = "Failed!";
				else
					wline[line.length] = "OK";
				wlines.add(wline);
			}
			csvHandler.writeAll(wlines);

		} catch (Exception e) {
			e.printStackTrace();
		}
	}

}
