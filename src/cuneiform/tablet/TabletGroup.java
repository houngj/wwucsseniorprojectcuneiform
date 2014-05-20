package cuneiform.tablet;

import java.io.IOException;
import java.io.PrintStream;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.List;

import cuneiform.Name;

public class TabletGroup
        extends DatabaseObject
{
    public final String          name;
    public final String          lang;
    public final List<Container> containers;

    TabletGroup(String name, String lang, List<Container> containers)
            throws IOException {
        // Efficient null check
        name.getClass();
        this.name       = name;
        this.lang       = lang;
        this.containers = containers;
        if (this.name.charAt(0) != '&') {
            throw new IllegalStateException("name is not of correct form");
        }
    }

    public boolean hasMonth() {
        for(Container c : containers) {
            if (c.hasMonth()) {
                return true;
            }
        }
        return false;
    }

    public boolean hasYear() {
        for(Container c : containers) {
            if (c.hasYear()) {
                return true;
            }
        }
        return false;
    }

    public boolean process(Name name) {
        boolean output = false;
        for (Container cont : containers) {
            boolean rv = cont.process(name);
            output = output || rv;
        }

        if(output) {
            name.addTablet(this);
        }
        return output;
    }

    public void print(PrintStream output) {
        output.println(name);
        output.println("#atf: lang " + lang);
        for (Container t : containers) {
            t.print(output, 0);
        }
    }

    public void printStats(PrintStream output) {
        output.format("%-27s %s%n", "name:", name);
        for(Container c : containers) {
            c.printStats(output, 1);
        }
        output.format("%n");
    }

    public synchronized void insert(Connection conn)
            throws SQLException
    {
        this.insertTabletGroup(conn);

        for (Container continaer : this.containers) {
            continaer.insert(conn, this, null);
        }
    }

    private synchronized void insertTabletGroup(Connection conn)
            throws SQLException
    {
        int id = Integer.parseInt(name.substring(2, 8));
        String query = "INSERT INTO `tablet_group` (`tablet_group_id`, `tablet_group_name`, `tablet_group_lang`) VALUES (?, ?, ?)";
        try (PreparedStatement stmt = conn.prepareStatement(query, Statement.RETURN_GENERATED_KEYS)) {
            stmt.setInt(1,  id);
            stmt.setString(2, name);
            // TODO: Find way to insert default value.
            stmt.setString(3, (lang == null) ? "sux" : lang);
            stmt.executeUpdate();

            try (ResultSet rs = stmt.getGeneratedKeys()) {
                if ((rs != null) && (rs.next())) {
                    setID(rs.getInt(1));
                } else {
                    throw new IllegalStateException("Couldn't obtain id");
                }
            }
        } catch (SQLException e) {
            e.printStackTrace();
            throw e;
        }
    }
}
