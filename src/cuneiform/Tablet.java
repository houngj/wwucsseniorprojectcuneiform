package cuneiform;

import java.io.IOException;
import java.io.PrintStream;
import java.util.ArrayList;
import java.util.List;

public class Tablet
        implements Comparable<Tablet> {
    public final String              name;
    public final String              lang;
    public final String              object;
    public final List<TabletSection> sections;
    public FoundDate                 foundMonth;
    public FoundDate                 foundYear;
    public final List<String>        names = new ArrayList<>();

    Tablet(String name, String lang, String object, List<TabletSection> sections)
            throws IOException {
        this.name = name;
        this.lang = lang;
        this.object = object;
        this.sections = sections;
        if (name.charAt(0) != '&')
            throw new IllegalStateException();
    }

    public void print(PrintStream output) {
        output.println(name);
        output.println(lang);
        output.println(object);
        for (TabletSection t : sections) {
            t.print(output);
        }
    }

    public void setMonth(FoundDate newMonth) {
        if (foundMonth == null || newMonth.compareTo(foundMonth) > 0) {
            foundMonth = newMonth;
        }
    }

    public void setYear(FoundDate newYear) {
        if (foundYear == null || newYear.compareTo(foundYear) > 0) {
            foundYear = newYear;
        }
    }

    public void addName(String name) {
        names.add(name);
    }

    public void printStats(PrintStream output) {
        output.format("%-27s %s%n", "name:", name);
        output.println(" month data:");
        if (foundMonth != null) {
            foundMonth.printStats(output);

        }
        output.println(" year data:");
        if (foundYear != null) {
            foundYear.printStats(output);
        }
        output.println(" names:");
        for (String n : names) {
            output.format("  %s%n", n);
        }
        output.format("%n");
    }

    @Override
    public int compareTo(Tablet othe) {
        int rv = compare(this.foundYear, othe.foundYear);
        if(rv != 0) return rv;
        return compare(this.foundMonth, othe.foundMonth);
        /*
        double thisC = ((this.foundMonth == null) ? (0) : (this.foundMonth.confidence.confidence)) + ((this.foundYear == null) ? (0) : (this.foundYear.confidence.confidence));
        double otheC = ((othe.foundMonth == null) ? (0) : (othe.foundMonth.confidence.confidence)) + ((othe.foundYear == null) ? (0) : (othe.foundYear.confidence.confidence));
        int rv1 = Double.compare(otheC, thisC);
        if (rv1 != 0) return rv1;
        int rv2 = Integer.compare(othe.names.size(), this.names.size());
        if (rv2 != 0) return rv2;
        return othe.name.compareTo(this.name);
        */
    }

    private static int compare(FoundDate fd1, FoundDate fd2) {
        if(fd1 == null && fd2 == null) return 0;
        if(fd1 == null) return 1;
        if(fd2 == null) return -1;
        return -fd1.confidence.compareTo(fd2.confidence);
    }
}

class TabletSection {
    public final String       title;
    public final List<String> lines;

    TabletSection(String title, List<String> lines) {
        this.title = title;
        this.lines = lines;
    }

    public void print(PrintStream output) {
        output.println(title);
        for (int i = 0; i < lines.size(); ++i) {
            output.format("%3d. %s%n", i + 1, lines.get(i));
        }
    }
}
