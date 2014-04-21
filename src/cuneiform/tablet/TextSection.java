package cuneiform.tablet;

import java.io.PrintStream;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.List;

import cuneiform.FoundDate;
import cuneiform.Name;

public class TextSection
        extends DatabaseObject {
    public final List<String>    lines;
    public final List<Name>      names  = new ArrayList<>();
    public final List<FoundDate> years  = new ArrayList<>();
    public final List<FoundDate> months = new ArrayList<>();

    TextSection(List<String> lines) {
        this.lines = lines;
    }

    public boolean hasYear() {
        return (!years.isEmpty());
    }

    public boolean hasMonth() {
        return (!months.isEmpty());
    }

    public String[] getGraphemes() {
        List<String> graphemeList = new ArrayList<>();
        for (String line : lines) {
            String[] graphs = line.split("-| ");
            for(String g :graphs) {
                graphemeList.add(g);
            }
        }
        return graphemeList.toArray(new String[graphemeList.size()]);
    }

    void addName(Name name) {
        names.add(name);
    }

    public void addYear(FoundDate date) {
        years.add(date);
    }

    public void addMonth(FoundDate date) {
        months.add(date);
    }

    boolean process(Name name) {
        for (String line : this.lines) {
            String[] parts = line.split(" ");
            for (String part : parts) {
                if (part.equalsIgnoreCase(name.name)) {
                    this.addName(name);
                    return true;
                }
            }
        }
        return false;
    }

    public void print(PrintStream output) {
        print(output, 0);
    }

    void print(PrintStream output, int depth) {
        String indent = "";
        for (int i = 0; i < depth; ++i) {
            indent += "    ";
        }
        for (int i = 0; i < lines.size(); ++i) {
            output.format(indent + "%d. %s%n", i + 1, lines.get(i));
        }
    }

    public void printStats(PrintStream output, int depth) {
        String indent = "";
        for(int i = 0; i < depth; ++i) {
            indent += " ";
        }
        output.println(indent + "names:");
        for (Name n : names) {
            output.format(indent + " %s%n", n.name);
        }
        output.println(indent + "years:");
        for (FoundDate year : years) {
            year.printStats(output, depth + 1);
        }
        output.println(indent + "months:");
        for (FoundDate month : months) {
            month.printStats(output, depth + 1);
        }
    }

    synchronized void insert(Connection conn, TabletGroup root, Container parent)
            throws SQLException {
        if (isInserted()) {
            throw new IllegalStateException("Text section is already inserted");
        }

        insertTextSection(conn, root, parent);

        for (String line : this.lines) {
            insertLine(conn, line);
        }

        for (FoundDate year : years) {
            insertYear(conn, year);
        }

        for (FoundDate month : months) {
            insertMonth(conn, month);
        }

        for (Name n : names) {
            n.insertNameReference(conn, this);
        }
    }

    private synchronized void insertTextSection(Connection conn, TabletGroup root, Container parent)
            throws SQLException {
        String query = "INSERT INTO `text_section` "
                     + "(`text_section_id`, `container_id`, `tablet_group_id`, `text_section_text`) "
                     + "VALUES (NULL, ?, ?, ?)";
        String text = "";
        for (String line : this.lines) {
            text += line + " ";
        }
        try (PreparedStatement stmt = conn.prepareStatement(query, Statement.RETURN_GENERATED_KEYS)) {
            // Indices are 1-based
            stmt.setInt(1, parent.getID());
            stmt.setInt(2, root.getID());
            stmt.setString(3, text);

            stmt.executeUpdate();

            try (ResultSet rs = stmt.getGeneratedKeys()) {
                if ((rs != null) && (rs.next())) {
                    setID(rs.getInt(1));
                } else {
                    throw new IllegalStateException("Cannot obtain id");
                }
            }
        }
    }

    private synchronized void insertLine(Connection conn, String line)
            throws SQLException
    {
        String query = "INSERT INTO `line` "
                     + "(`line_id`, `text_section_id`, `line_text`) "
                     + "VALUES (NULL, ?, ?)";
        try (PreparedStatement stmt = conn.prepareStatement(query)) {
            stmt.setInt(1, getID());
            stmt.setString(2, line);
            stmt.executeUpdate();
        }
    }

    private synchronized void insertMonth(Connection conn, FoundDate date)
            throws SQLException {
        String query = "INSERT INTO `month_reference` "
                     + "(`text_section_id`, `canonical_month_id`, `found_text`, `confidence`) VALUES "
                     + "(?, ?, ?, ?)";
        try (PreparedStatement stmt = conn.prepareStatement(query)) {
            stmt.setInt(1, getID());
            stmt.setInt(2, date.getKnownDate().getID());
            stmt.setString(3, date.foundDate);
            stmt.setDouble(4, date.confidence.confidence / 100);
            stmt.executeUpdate();
        }
    }

    private synchronized void insertYear(Connection conn, FoundDate date)
            throws SQLException {
        String sql = "INSERT INTO `year_reference` "
                   + "(`year_reference_id`, `text_section_id`, `canonical_year_id`, `found_text`, `confidence`) VALUES "
                   + "(NULL, ?, ?, ?, ?)";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setInt(1, getID());
            stmt.setInt(2, date.getKnownDate().getID());
            stmt.setString(3, date.foundDate);
            stmt.setDouble(4, date.confidence.confidence / 100);
            stmt.executeUpdate();
        }
    }
}
