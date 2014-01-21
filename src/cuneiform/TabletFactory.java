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
        prevLine = reader.readLine();
        if(prevLine != null) {
            prevLine = prevLine.trim();
        }
        return prevLine;
    }

    public synchronized Tablet build()
            throws IOException {
        String name = null;
        String lang = null;
        String line = (prevLine == null) ? getLine() : prevLine;

        if (line == null) {
            return null;
        }

        do {
            if (line.isEmpty()) {
                continue;
            } else if (line.charAt(0) == '&') {
                assert (name == null);
                name = line;
            } else if (line.startsWith("#atf: lang")) {
                assert (lang == null);
                lang = line.replace("#atf: lang", "").trim();
            } else if (line.charAt(0) == '@') {
                break;
            }
        } while ((line = getLine()) != null);

        List<TabletObject> objects = new ArrayList<>();

        TabletObject obj = null;
        while ((obj = buildObject()) != null) {
            objects.add(obj);
        }

        return new Tablet(name, lang, null, objects);
    }

    private TabletObject buildObject()
            throws IOException {
        String name = (prevLine == null) ? getLine() : prevLine;
        if (name == null || name.charAt(0) == '&') {
            return null;  // End of file or end of object.
        }

        prevLine = null; // makes buildSection() read new name
        List<TabletSection> sections = new ArrayList<>();

        TabletSection sect = null;
        while ((sect = buildSection()) != null) {
            sections.add(sect);
        }
        return new TabletObject(name, sections);
    }

    private TabletSection buildSection()
            throws IOException {
        String       line;
        String       title = (prevLine == null) ? (getLine()) : prevLine;
        List<String> lines = new ArrayList<>();

        if (title == null) return null; // End of file

        // Skip comments and blank lines
        while (title.isEmpty() || title.charAt(0) == '$' || title.charAt(0) == '#') {
            title = getLine();
        }

        if (title.charAt(0) == '&') {
            return null; // End of object
        } else if (Character.isDigit(title.charAt(0))) {
            lines.add(stripLineNumber(title)); // section has no title, use ""
            title = "";
        } else if (title.charAt(0) != '@') {
            throw new IllegalStateException("Invalid title " + title);
        }

        // Check for start of new object
        if (title.startsWith("@tablet")     || title.startsWith("@envelop")  ||
                title.startsWith("@bulla")  || title.startsWith("@seal")     ||
                title.startsWith("@object") || title.startsWith("@fragment") ||
                title.startsWith("@obejct")) {
            return null;
        }

        while ((line = getLine()) != null) {
            if (line.isEmpty()) {
                continue;
            } else if (line.charAt(0) == '@' || line.charAt(0) == '&') {
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
