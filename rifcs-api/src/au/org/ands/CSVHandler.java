package au.org.ands;

import java.io.FileReader;
import java.io.FileWriter;
import java.util.List;

import au.com.bytecode.opencsv.CSVReader;
import au.com.bytecode.opencsv.CSVWriter;

public class CSVHandler {
	private String inputFile;

	public CSVHandler(String inputFile) {
		this.inputFile = inputFile;
	}

	public List<String[]> readAll() throws Exception {
		CSVReader reader;
		reader = new CSVReader(new FileReader(inputFile));
		return reader.readAll();
	}

	public void writeAll(List<String[]> allLines) throws Exception {
		CSVWriter writer = new CSVWriter(new FileWriter("out"+inputFile));
		writer.writeAll(allLines);
		writer.close();
	}

}
