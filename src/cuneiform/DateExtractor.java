package cuneiform;

import java.io.BufferedReader;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.IOException;
import java.io.PrintStream;
import java.util.ArrayList;
import java.util.List;

public class DateExtractor {
    public List<KnownDate> knownMonths = new ArrayList<>();
    public List<KnownDate> knownYears  = new ArrayList<>();
    public PrintStream     ps;

    public DateExtractor()
            throws FileNotFoundException {
        ps = new PrintStream(new File("output.txt"));
        build();
    }

    public void build() {
        try (BufferedReader reader = new BufferedReader(new FileReader("months.txt"))) {
            String line;
            while ((line = reader.readLine()) != null) {
                if (line.startsWith("//") == false) {
                    knownMonths.add(new KnownDate(line));
                }
            }
        }  catch (IOException e) {
            // TODO Auto-generated catch block
            e.printStackTrace();
        }
        try (BufferedReader reader = new BufferedReader(new FileReader("years.txt"))) {
            String line;
            while ((line = reader.readLine()) != null) {
                if (line.startsWith("//") == false) {
                    knownYears.add(new KnownDate(line));
                }
            }
        }  catch (IOException e) {
            // TODO Auto-generated catch block
            e.printStackTrace();
        }
    }

    public void process(Tablet t) {
        final String yearStart = "mu ";
        final String monthStart = "iti ";
        for (TabletSection s : t.sections) {
            for (String line : s.lines) {
                int yearIndex = line.indexOf(yearStart);
                int monthIndex = line.indexOf(monthStart);
                if (monthIndex != -1) {
                    String substring = line.substring(monthIndex + monthStart.length()).trim();
                    Confidence c = getConfidence(substring, knownMonths);
                    t.setMonth(c);
                }
                if (yearIndex != -1) {
                    String substring = line.substring(yearIndex + yearStart.length()).trim();
                    Confidence c = getConfidence(substring, knownYears);
                    t.setYear(c);
                }
            }
        }
        t.printStats(ps);
    }

    private Confidence getConfidence(String substring, List<KnownDate> dates) {
        KnownDate guess = null;
        int distance = Integer.MAX_VALUE;
        for (KnownDate d : dates) {
            int newDist = d.levenshteinDistance(substring);
            if (newDist < distance) {
                distance = newDist;
                guess = d;
            }
        }
        return new Confidence(distance, guess, substring);
    }
}

class Confidence {
    final int       distance;
    final KnownDate date;
    final String    string;
    final double    confidence;

    public Confidence(int distance, KnownDate a, String b) {
        this.distance = distance;
        this.confidence = 100.0 - (100.0 * distance / (a.transliteration.length() + b.length()));
        this.string = b;
        this.date = a;
    }
}

class KnownDate {
    public final String transliteration;
    public final String canonical;

    public KnownDate(String line) {
        String[] parts = line.split("\t");
        transliteration = parts[1].trim();
        canonical = parts[0].trim();
    }

    public KnownDate(String trans, String canon) {
        this.transliteration = trans;
        this.canonical = canon;
    }

    public int levenshteinDistance(String s) {
        return levenshteinDistance(transliteration, s);
    }

    public static int levenshteinDistance(CharSequence str1, CharSequence str2) {
        int[][] distance = new int[str1.length() + 1][str2.length() + 1];

        for (int i = 0; i <= str1.length(); i++)
            distance[i][0] = i;
        for (int j = 1; j <= str2.length(); j++)
            distance[0][j] = j;

        for (int i = 1; i <= str1.length(); i++) {
            for (int j = 1; j <= str2.length(); j++) {
                int cost = (str1.charAt(i - 1) == str2.charAt(j - 1)) ? 0 : 1;
                distance[i][j] = minimum(distance[i - 1][j] + 1, distance[i][j - 1] + 1, distance[i - 1][j - 1] + cost);
            }
        }

        return distance[str1.length()][str2.length()];
    }

    private static int minimum(int a, int b, int c) {
        int t1 = (a < b) ? (a) : (b);
        return (c < t1) ? (c) : (t1);
    }
}
