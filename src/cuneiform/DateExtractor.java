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
import cuneiform.tablet.Container;
import cuneiform.tablet.TabletGroup;
import cuneiform.tablet.TextSection;

public class DateExtractor {
    public final List<KnownDate>  knownMonths;
    public final List<KnownDate>  knownYears;
    public final StringComparator comparator = new SumerianComparator();
    final String                  yearStart  = "mu";
    final String                  monthStart = "iti";

    public DateExtractor(Connection conn) throws SQLException {
        this.knownMonths = readKnownMonths(conn);
        this.knownYears  = readKnownYears(conn);
    }

    private List<KnownDate> readKnownMonths(Connection conn)
            throws SQLException
    {
        List<KnownDate> months = new ArrayList<KnownDate>();

        try (Statement stmt = conn.createStatement()) {
            stmt.execute("SELECT `canonical_month_id`, `text` FROM `canonical_month`;");
            ResultSet rs = stmt.getResultSet();
            if (rs == null) {
                // No point of continuing if we can't retrieve the known months
                throw new IllegalStateException("Cannot get ResultsSet");
            }
            while (rs.next()) {
                int id = rs.getInt("canonical_month_id");
                String text = rs.getString("text");
                months.add(new KnownDate(id, text));
            }
        }
        return months;
    }

    private List<KnownDate> readKnownYears(Connection conn)
            throws SQLException
    {
        List<KnownDate> years = new ArrayList<KnownDate>();

        try (Statement stmt = conn.createStatement()) {
            stmt.execute("SELECT `canonical_year_id`, `text` FROM `canonical_year`;");
            ResultSet rs = stmt.getResultSet();
            if (rs == null) {
                // No point of continuing if we can't retrieve the known years
                throw new IllegalStateException("Cannot get ResultsSet");
            }
            while (rs.next()) {
                int id = rs.getInt("canonical_year_id");
                String text = rs.getString("text");
                years.add(new KnownDate(id, text));
            }
            stmt.close();
        }
        return years;
    }
	
    public void process(Connection conn, TabletGroup t) throws SQLException {
       for (Container container : t.containers) {
            process(conn, container);
        }
    }

    private void process(Connection conn, Container input) throws SQLException {
        if (input.section != null) {
            process(conn, input.section);
        } else {
            for(Container c : input.containers) {
                process(conn, c);
            }
        }
    }

    private void process(Connection conn, TextSection s) throws SQLException {
        String[] graphemeArray = s.getGraphemes();
        for (int i = 0; i < graphemeArray.length - 1; ++i) {
            String currentGrapheme = graphemeArray[i].replaceAll("[^A-Za-z0-9]", "");
            if (yearStart.equalsIgnoreCase(currentGrapheme)) {
                FoundDate c = getConfidence(graphemeArray, i + 1, knownYears);
                s.addYear(c);
            } else if (monthStart.equalsIgnoreCase(currentGrapheme)) {
                FoundDate c = getConfidence(graphemeArray, i + 1, knownMonths);
                s.addMonth(c);
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
