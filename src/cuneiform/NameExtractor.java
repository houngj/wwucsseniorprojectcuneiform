package cuneiform;

import java.io.BufferedReader;
import java.io.FileReader;
import java.io.IOException;
import java.util.ArrayList;
import java.util.List;

public class NameExtractor {
	private final List<String> names;
	public NameExtractor() {
		names = readNames("names.txt");
	}
	private List<String> readNames(String path) {
		List<String> output = new ArrayList<>();
		try (BufferedReader reader = new BufferedReader(new FileReader(path))) {
            String line;
            while ((line = reader.readLine()) != null) {
            	String trimmed = line.trim();
                if (trimmed.isEmpty() == false && trimmed.startsWith("//") == false) {
                	output.add(trimmed);
                }
            }
        }  catch (IOException e) {
        	throw new IllegalStateException(e);
        }
		return output;
	}

	public void process(Tablet tablet) {
		for (String name : names) {
			if (tabletContains(tablet, name)) {
				tablet.addName(name);
			}
		}
	}

	private boolean tabletContains(Tablet tablet, String name) {
		for (TabletSection sect : tablet.sections) {
			for (String line : sect.lines) {
				String[] parts = line.split(" ");
				for (String part : parts) {
					if (part.equalsIgnoreCase(name)) {
						return true;
					}
				}
			}
		}
		return false;
	}
}
