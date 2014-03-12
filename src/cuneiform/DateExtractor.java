package cuneiform;

import java.sql.Connection;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.List;

import cuneiform.stringComparator.Confidence;
import cuneiform.stringComparator.StringComparator;
import cuneiform.stringComparator.SumerianComparator;
import cuneiform.stringComparator.SumerianLevenstheinSubstringComparator;

public class DateExtractor {
    public final List<KnownDate>  knownMonths;
    public final List<KnownDate>  knownYears;
    public final StringComparator comparator = new SumerianComparator();

    public DateExtractor(Connection conn) {
        this.knownMonths = readKnownMonths(conn);
        this.knownYears  = readKnownYears(conn);
    }

/*
    private List<KnownDate> readKnownDates(String path) {
        List<KnownDate> output = new ArrayList<>();
        try (BufferedReader reader = new BufferedReader(new FileReader(path))) {
            String line;
            while ((line = reader.readLine()) != null) {
                if (line.startsWith("//") == false) {
                    output.add(new KnownDate(line));
                }
            }
        } catch (IOException e) {
            throw new IllegalStateException(e);
        }
        return output;
    }
*/

	private List<KnownDate> readKnownMonths(Connection conn) {
		
		List<KnownDate> months = new ArrayList<KnownDate>();
		
		try (Statement stmt = conn.createStatement()) {
		    stmt.execute("SELECT `canonical_month_id`, `text` FROM `canonical_month`;");
	    	
	    	ResultSet rs = stmt.getResultSet();
	    	
	    	if (null != rs) {
	    		while(rs.next()) {
                    int id = rs.getInt("canonical_month_id");
	    			String text = rs.getString("text");
	    			
	    			months.add(new KnownDate(id, text));
	    		}
	    	}
		}
		catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		
		return months;
	}

	private List<KnownDate> readKnownYears(Connection conn) {
		
		List<KnownDate> years = new ArrayList<KnownDate>();
    	
		try (Statement stmt = conn.createStatement()) {
			stmt.execute("SELECT `canonical_year_id`, `text` FROM `canonical_year`;");
	    	
	    	ResultSet rs = stmt.getResultSet();
	    	
	    	if (null != rs) {
	    		while(rs.next()) {
                    int id = rs.getInt("canonical_year_id");
	    			String text = rs.getString("text");
	    			
	    			years.add(new KnownDate(id, text));
	    		}
	    	}
	    	stmt.close();
		}
		catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		
		return years;
	}
	
    public void process(Connection conn, Tablet t) {
        final String yearStart = "mu";
        final String monthStart = "iti";

        t.insert(conn);

        for (TabletObject o : t.objects) {
            for (TabletSection s : o.sections) {
                List<String> graphemeList = new ArrayList<>();
                for (String line : s.lines) {
                    String[] graphs = line.split("-| ");
                    for(String g :graphs) {
                        graphemeList.add(g);
                    }
                }
                String[] graphemeArray = graphemeList.toArray(new String[graphemeList.size()]);
                for (int i = 0; i < graphemeArray.length - 1; ++i) {
                    String currentGrapheme = graphemeArray[i].replaceAll("[^A-Za-z0-9]", "");
                    if (yearStart.equalsIgnoreCase(currentGrapheme)) {
                        FoundDate c = getConfidence(graphemeArray, i + 1, knownYears);
                        t.setYear(c);
                        s.insertYear(conn, c.foundDate, c);
                    } else if (monthStart.equalsIgnoreCase(currentGrapheme)) {
                        FoundDate c = getConfidence(graphemeArray, i + 1, knownMonths);
                        t.setMonth(c);
                        s.insertMonth(conn, c.foundDate, c);
                    }
                }
            }
        }
    }

    private FoundDate getConfidence(String[] graphemes, int i, List<KnownDate> dates) {
        KnownDate guess = null;
        Confidence confd = new Confidence(Integer.MAX_VALUE, -1);
        int bestIndex = 0;
        double[] conf = new double[1];
        int[]    indx = new int[1];
        int[]    dist = new int[1];

        for (KnownDate d : dates) {
            SumerianLevenstheinSubstringComparator.compare(d.text, graphemes, i, conf, indx, dist);
            if (conf[0] > confd.confidence) {
                bestIndex = indx[0];
                confd = new Confidence(dist[0], conf[0]);
                guess = d;
            }
        }

        String output = "";
        for (int j = 0; j < bestIndex; ++j) {
            if (output.isEmpty() == false)
                output += " ";
            output += graphemes[j + i];
        }

        return new FoundDate(guess, output, confd);
    }
}
