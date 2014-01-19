package cuneiform;

import java.io.BufferedReader;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.IOException;
import java.util.ArrayList;
import java.util.List;

import cuneiform.stringComparator.Confidence;
import cuneiform.stringComparator.StringComparator;
import cuneiform.stringComparator.SumerianComparator;
import java.sql.*;

public class DateExtractor {
    public final List<KnownDate>  knownMonths;
    public final List<KnownDate>  knownYears;
    public final StringComparator comparator = new SumerianComparator();

    public DateExtractor()
            throws FileNotFoundException {
        knownMonths = readKnownDates("months.txt");
        knownYears  = readKnownDates("years.txt");
    }

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

    public void process(Connection conn, Tablet t) {
        final String yearStart = "mu";
        final String monthStart = "iti";
        
        t.insert(conn);
        
        for (TabletSection s : t.sections) {
            for (String line : s.lines) {
                int yearIndex = line.indexOf(yearStart);
                int monthIndex = line.indexOf(monthStart);
                if (monthIndex != -1) {
                    String substring = line.substring(monthIndex + monthStart.length()).trim();
                    FoundDate c = getConfidence(substring, knownMonths);
                    t.setMonth(c);
                    
                    // TODO: load KnownDates into memory so that we can relate found date
                    // references to the best KnownDate match.
                    
                    s.insertMonth(conn, substring, c);
                }
                if (yearIndex != -1) {
                    String substring = line.substring(yearIndex + yearStart.length()).trim();
                    FoundDate c = getConfidence(substring, knownYears);
                    t.setYear(c);
                    
                    // TODO: load KnownDates into memory so that we can relate found date
                    // references to the best KnownDate match.
                    
                    s.insertYear(conn, substring, c);
                }
            }
        }
    }

    private FoundDate getConfidence(String substring, List<KnownDate> dates) {
        KnownDate guess = null;
        Confidence conf = new Confidence(Integer.MAX_VALUE, -1);
        for (KnownDate d : dates) {
            Confidence newConf = comparator.compare(d.transliteration, substring);
            if (newConf.compareTo(conf) > 0) {
                conf = newConf;
                guess = d;
            }
        }
        return new FoundDate(guess, substring, conf);
    }
}
