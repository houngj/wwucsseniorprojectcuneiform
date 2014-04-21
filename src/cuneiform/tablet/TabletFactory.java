package cuneiform.tablet;

import java.io.BufferedReader;
import java.io.IOException;
import java.util.ArrayList;
import java.util.List;

import cuneiform.tablet.TabletGroup;

public class TabletFactory {
    private final BufferedReader reader;

    public TabletFactory(BufferedReader reader) {
        this.reader = reader;
    }

    private String readLine() throws IOException {
        reader.mark(4096);
        return reader.readLine();
    }

    private void unReadLine() throws IOException {
        reader.reset(); // returns the point of 'reader.mark(4096)'
    }

    public synchronized TabletGroup build()
            throws IOException {
        String name = null;
        String lang = null;
        String line = null;

        // Check if we're at the end of the file
        line = readLine();
        if (line == null) {
            return null;
        } else {
            unReadLine();
        }

        while ((line = readLine()) != null) {
            if (line.isEmpty()) {
                continue;
            } else if (line.charAt(0) == '&') {
                assert (name == null);
                name = line.trim();
            } else if (line.startsWith("#atf: lang")) {
                assert (lang == null);
                lang = line.replace("#atf: lang", "").trim();
            } else if (line.charAt(0) == '@') {
                unReadLine(); // We want this line to be read again by buildContainer
                break;
            } else {
            }
        }

        List<Container> containers = new ArrayList<>();

        Container container = null;
        while ((container = buildContainer(null)) != null) {
            containers.add(container);
        }

        return new TabletGroup(name, lang, containers);
    }

    private Container buildContainer(final String parentName )
            throws IOException {
        String name = readLine();

        if (name == null || name.charAt(0) == '&') {
            unReadLine();
            return null;  // End of file or end of TabletGroup.
        }

        // Containers that only belong at the root level.
        if (parentName != null &&
            (name.contains("envelope") || name.contains("tablet"))) {
            unReadLine();
            return null;
        }

        if (parentName != null &&
                ((parentName.startsWith("@obverse") && name.startsWith("@reverse")) ||
                 (parentName.startsWith("@reverse") && name.startsWith("@seal")))) {
            unReadLine();
            return null;
        }

        // Containers can either contain a text section or a collection of Containers.
        // If the next line starts with @, it't will be a container, if it's a digit,
        // It will be a text section


        String line = readLine();
        unReadLine();

        if (line.charAt(0) == '@') {
            List<Container> containers = new ArrayList<Container>();
            Container cont = null;
            while((cont = buildContainer(name.trim())) != null) {
                containers.add(cont);
            }
            return new Container(name, containers);
        } else {
            TextSection sect = buildSection();
            return new Container(name, sect);
        }
    }

    private TextSection buildSection()
            throws IOException {
        String line = null;
        List<String> lines = new ArrayList<>();
        while((line = readLine()) != null) {
            if (line.charAt(0) == '@' || line.charAt(0) == '&') {
                unReadLine(); // line is title of other Container or TabletGroup, must unread.
                break;
            } else if (line.startsWith("#tr.en")) {
                // TODO: add translation to previous line
            } else if (ignoreLine(line) == false) {
                lines.add(stripLineNumber(line));
            }
        }
        return new TextSection(lines);
    }

    private static boolean ignoreLine(String line) {
        char c = line.charAt(0);
        return (c == '$' || c == '#');
    }

    public static String stripLineNumber(String line) {
        for (int i = 0; i < line.length(); ++i) {
            char c = line.charAt(i);
            if ((c < '0' || '9' < c) && c != '.' && c != '\'') {
                return line.substring(i).trim();
            }
        }
        return "";
    }
}
