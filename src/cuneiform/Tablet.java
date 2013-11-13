package cuneiform;

import java.util.List;
import java.io.IOException;
import java.io.PrintStream;

import javax.jws.Oneway;

public class Tablet
        implements Comparable<Tablet> {
    public final String              name;
    public final String              lang;
    public final String              object;
    public final List<TabletSection> sections;
    public Confidence                monthConf;
    public Confidence                yearConf;

    Tablet(String name, String lang, String object, List<TabletSection> sections)
            throws IOException {
        this.name = name;
        this.lang = lang;
        this.object = object;
        this.sections = sections;
        if (name.charAt(0) != '&')
            throw new IllegalStateException();
    }

    public void print() {
        System.out.println(name);
        System.out.println(lang);
        System.out.println(object);
        for (TabletSection t : sections) {
            t.print();
        }
    }

    public void setMonth(Confidence conf) {
        if (monthConf == null || monthConf.confidence < conf.confidence) {
            this.monthConf = conf;
        }
    }

    public void setYear(Confidence conf) {
        if (yearConf == null || yearConf.confidence < conf.confidence) {
            this.yearConf = conf;
        }
    }

    public void printStats(PrintStream output) {
        output.format("%-15s %s%n", "NAME", name);
        if (monthConf != null) {
            output.format("%-15s %s%n", "MONTH SRC", monthConf.string);
            output.format("%-15s %s%n", "MONTH TRANS", monthConf.date.transliteration);
            output.format("%-15s %s%n", "MONTH CANON", monthConf.date.canonical);
            output.format("%-15s %s%n", "MONTH DIST", monthConf.distance);
            output.format("%-15s %s%n", "MONTH CONF", monthConf.confidence);
        } else {
            output.format("NO MONTH DATA%n");
        }
        if (yearConf != null) {
            output.format("%-15s %s%n", "YEAR SRC", yearConf.string);
            output.format("%-15s %s%n", "YEAR TRANS", yearConf.date.transliteration);
            output.format("%-15s %s%n", "YEAR CANON", yearConf.date.canonical);
            output.format("%-15s %s%n", "YEAR DIST", yearConf.distance);
            output.format("%-15s %s%n", "YEAR CONF", yearConf.confidence);
        } else {
            output.format("NO YEAR DATA%n");
        }
        output.format("%n");
    }

    @Override
    public int compareTo(Tablet othe) {
        double thisC = ((this.monthConf == null) ? (0) : (this.monthConf.confidence)) + ((this.yearConf == null) ? (0) : (this.yearConf.confidence));
        double otheC = ((othe.monthConf == null) ? (0) : (othe.monthConf.confidence)) + ((othe.yearConf == null) ? (0) : (othe.yearConf.confidence));
        return -Double.compare(thisC, otheC);
    }

}

class TabletSection {
    public final String       title;
    public final List<String> lines;

    TabletSection(String title, List<String> lines) {
        this.title = title;
        this.lines = lines;
    }

    public void print() {
        System.out.println(title);
        for (int i = 0; i < lines.size(); ++i) {
            System.out.format("%3d. %s%n", i + 1, lines.get(i));
        }
    }
}
