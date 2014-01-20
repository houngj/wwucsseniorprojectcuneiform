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

        List<TabletSection> sections = new ArrayList<>();

        TabletSection sect = null;
        while ((sect = buildSection()) != null) {
            sections.add(sect);
        }

        return new Tablet(name, lang, null, sections);
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
