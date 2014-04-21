package cuneiform.tablet;

import java.io.PrintStream;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.sql.Types;
import java.util.List;

import cuneiform.Name;

public class Container extends DatabaseObject {
    public final String          name;
    public final List<Container> containers;
    public final TextSection     section;

    public Container(String name, List<Container> containers) {
        this(name, null, containers);
    }

    public Container(String name, TextSection sect) {
        this(name, sect, null);
    }

    private Container(String name, TextSection section, List<Container> containers) {
        this.name = name.trim();
        this.containers = containers;
        this.section = section;

        if ((containers == null) == (section == null)) {
            throw new IllegalStateException("Child Container and TextSection present");
        }
    }

    public boolean hasMonth() {
        if (section != null) {
            return section.hasMonth();
        } else {
            for (Container c : containers) {
                if (c.hasMonth()) {
                    return true;
                }
            }
            return false;
        }
    }

    public boolean hasYear() {
        if (section != null) {
            return section.hasYear();
        } else {
            for (Container c : containers) {
                if (c.hasYear()) {
                    return true;
                }
            }
            return false;
        }
    }



    public boolean process(Name name) {
        if(section != null) {
            return section.process(name);
        } else {
            boolean rv = false;
            for(Container c : containers) {
                boolean r = c.process(name);
                rv = rv || r;
            }
            return rv;
        }
    }

    public void print(PrintStream output) {
        print(output, 0);
    }

    void print(PrintStream output, int depth) {
        String indent = "";
        for(int i = 0; i < depth; ++i) {
            indent += "    ";
        }
        output.println(indent + name);
        if (section != null) {
            section.print(output, depth + 1);
        } else {
            for(Container c : containers) {
                c.print(output, depth + 1);
            }
        }
    }

    public void printStats(PrintStream output, int depth) {
        String indent = "";
        for(int i = 0; i < depth; ++i) {
            indent += " ";
        }
        output.println(indent + name);
        if (section != null) {
            section.printStats(output, depth + 1);
        } else {
            for(Container c : containers) {
                c.printStats(output, depth + 1);
            }
        }
    }

    public synchronized void insert(Connection conn, TabletGroup rootGroup, Container parentContainer)
            throws SQLException {
        // Insert own record into the database.
        insertContainer(conn, rootGroup, parentContainer);

        if (section != null) {
            section.insert(conn, rootGroup, this);
        } else {
            for(Container c : containers) {
                c.insert(conn, rootGroup, this);
            }
        }
    }

    private synchronized void insertContainer(Connection conn, TabletGroup rootGroup, Container parentContainer)
            throws SQLException {
        String query = "INSERT INTO `container` (`container_id`, `tablet_group_id`, `parent_container_id`, `container_name`) "
                     + "VALUES (NULL, ?, ?, ?)";
        try (PreparedStatement stmt = conn.prepareStatement(query, Statement.RETURN_GENERATED_KEYS)) {
            stmt.setInt(1, rootGroup.getID()); // Parameters indices are 1-based
            if (parentContainer == null) {
                stmt.setNull(2, Types.INTEGER);
            } else {
                stmt.setInt(2, parentContainer.getID());
            }
            stmt.setString(3, name);

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




}