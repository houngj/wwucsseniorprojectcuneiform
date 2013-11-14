package cuneiform;

import java.io.BufferedReader;
import java.io.IOException;
import java.util.ArrayList;
import java.util.List;

class TabletFactory {
    private final BufferedReader reader;
    private String               prevLine = null;

    public TabletFactory(BufferedReader reader) {
        this.reader = reader;
    }

    private String getLine()
            throws IOException {
        return prevLine = reader.readLine();
    }

    public synchronized Tablet build()
            throws IOException {
        // First line is name
        String name = (prevLine == null) ? getLine() : prevLine;
        if (name == null) return null;
        String lang = getLine(); // Second line is language;

        if (lang.charAt(0) != '#') {
            String line;
            do {
                line = getLine();
            } while (line.charAt(0) != '&');
            return build();
        }

        String object = getLine(); // Third line is the object type;
        while (object.charAt(0) == '#') {
            object = getLine();
        }
        List<TabletSection> sections = new ArrayList<>();

        {
            TabletSection sect = null;
            while ((sect = buildSection()) != null) {
                sections.add(sect);
            }
        }
        return new Tablet(name, lang, object, sections);
    }

    private TabletSection buildSection()
            throws IOException {
        String title = (prevLine == null) ? getLine() : prevLine;
        List<String> lines = new ArrayList<>();
        if (title == null || title.charAt(0) != '@') {
            return null;
        }

        String line;
        while ((line = getLine()) != null) {
            if (line.charAt(0) == '@' || line.charAt(0) == '&') {
                break;
            } else if (ignoreLine(line) == false) {
                lines.add(stripLineNumber(line));
            }
        }
        return new TabletSection(title, lines);
    }

    private static boolean ignoreLine(String line) {
        char c = line.charAt(0);
        return (c == '$' || c == '#');
    }

    private static String stripLineNumber(String line) {
        for (int i = 0; i < line.length(); ++i) {
            char c = line.charAt(i);
            if ((c < '0' || '9' < c) && c != '.' && c != '\'' && c != ' ') {
                return line.substring(i);
            }
        }
        return "";
    }
}
