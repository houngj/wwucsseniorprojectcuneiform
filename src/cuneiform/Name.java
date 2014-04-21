package cuneiform;

import java.io.PrintStream;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.List;

import cuneiform.tablet.DatabaseObject;
import cuneiform.tablet.TabletGroup;
import cuneiform.tablet.TextSection;

public class Name
        extends DatabaseObject
        implements Comparable<Name>
{
    public final String             name;
    private final List<TabletGroup> tablets = new ArrayList<>();

    public Name(String name) {
        // Efficient null check
        name.getClass();
        this.name = name;
    }

    public void addTablet(TabletGroup t) {
        tablets.add(t);
    }

    public void print(PrintStream output) {
        output.format("name: %-20s appearing in %d tablets%n", name, tablets.size());
        for (TabletGroup t : tablets) {
            output.format("  %-40s%n", t.name);
        }
    }

    private synchronized void insertName(Connection conn)
            throws SQLException
    {
        if (isInserted()) {
            return;
        }

        String sql = "INSERT INTO `name` (`name_text`) VALUES (?);";
        try (PreparedStatement stmt = conn.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS)) {
            stmt.setString(1, name); // Parameters indices are 1-based
            stmt.executeUpdate();

            try (ResultSet rs = stmt.getGeneratedKeys()) {
                if ((rs != null) && (rs.next())) {
                    setID(rs.getInt(1));
                } else {
                    throw new IllegalStateException("Cannot obtain id");
                }
            }
        } catch (SQLException e) {
            e.printStackTrace();
            throw e;
        }
    }

    public synchronized void insertNameReference(Connection conn, TextSection section)
            throws SQLException
    {
        // Make sure the name is inserted first, only once please.
        if (isInserted() == false) {
            insertName(conn);
        }

        String sql = "INSERT INTO `name_reference` (`name_id`, `text_section_id`) VALUES (?, ?);";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setInt(1, getID()); // Parameters indices are 1-based
            stmt.setInt(2, section.getID());
            stmt.executeUpdate();
        } catch (SQLException e) {
            // TODO Auto-generated catch block
            e.printStackTrace();
            throw e;
        }
    }

    @Override
    public int compareTo(Name o) {
        return -Integer.compare(tablets.size(), o.tablets.size());
    }
}
