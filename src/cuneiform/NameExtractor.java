package cuneiform;

import java.io.BufferedReader;
import java.io.FileReader;
import java.io.IOException;
import java.io.PrintStream;
import java.sql.Connection;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Collections;
import java.util.List;

import cuneiform.tablet.TabletGroup;

public class NameExtractor {
    private final List<Name> names;

    public NameExtractor() {
        names = readNames("./data/names.txt");
    }

    private List<Name> readNames(String path) {
        List<Name> output = new ArrayList<>();
        try (BufferedReader reader = new BufferedReader(new FileReader(path))) {
            String line;
            while ((line = reader.readLine()) != null) {
                String trimmed = line.trim();
                if (trimmed.isEmpty() == false && trimmed.startsWith("//") == false) {
                    output.add(new Name(trimmed));
                }
            }
        } catch (IOException e) {
            throw new IllegalStateException(e);
        }
        return output;
    }

    public void print(PrintStream output) {
        Collections.sort(names);
        for (Name name : names) {
            name.print(output);
            output.println();
        }
    }

    public void process(Connection conn, TabletGroup tablet) throws SQLException {
        for (Name name : names) {
            tablet.process(name);
        }
    }
}
