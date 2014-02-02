package cuneiform;

import java.io.BufferedReader;
import java.io.FileReader;
import java.io.IOException;
import java.io.PrintStream;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.Collections;
import java.util.Comparator;
import java.util.List;

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

    public void process(Connection conn, Tablet tablet) {
        for (Name name : names) {
            if (tabletContains(tablet, name)) {
                tablet.addName(name);
                name.addTablet(tablet);
            }
        }
        tablet.insertNames(conn);
    }

    private boolean tabletContains(Tablet tablet, Name name) {
        for (TabletObject obj : tablet.objects) {
            for (TabletSection sect : obj.sections) {
                for (String line : sect.lines) {
                    String[] parts = line.split(" ");
                    for (String part : parts) {
                        if (part.equalsIgnoreCase(name.name)) {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }
}

class Name implements Comparable<Name> {
    private      int           id      = -1;
    public final String        name;
    private final List<Tablet> tablets = new ArrayList<>();

    public Name(String name) {
        this.name = name;
    }

    public void addTablet(Tablet t) {
        tablets.add(t);
    }

    public int id() {
        return id;
    }

    public void print(PrintStream output) {
        Collections.sort(tablets, new TabletComparator());
        output.format("name: %-20s appearing in %d tablets%n", name, tablets.size());
        for (Tablet t : tablets) {
            String month = (t.foundMonth == null) ? ("") : (t.foundMonth.date.getText());
            String year  = (t.foundYear  == null) ? ("") : (t.foundYear.date.getText());
            output.format("  %-40s\t%-20s\t%s%n", t.name, year, month);
        }
    }

    public void insert(Connection conn, int tabletId) {
        synchronized (this) {  // Make sure the name is inserted first, only once please.
            if(this.id == -1) {
                insertName(conn);
            }
        }

        String sql = "INSERT INTO `name_reference` (`name_id`, `tablet_id`) VALUES (?, ?);";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setInt(1, this.id); // Parameters indices are 1-based
            stmt.setInt(2, tabletId);
            stmt.executeUpdate();
        } catch (SQLException e) {
            // TODO Auto-generated catch block
            e.printStackTrace();
            throw new IllegalStateException(e);
        }
    }

    private void insertName(Connection conn) {
        String sql = "INSERT INTO `name` (`name_text`) VALUES (?);";
        try (PreparedStatement stmt = conn.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS)) {
            stmt.setString(1, name); // Parameters indices are 1-based
            stmt.executeUpdate();

            try (ResultSet rs = stmt.getGeneratedKeys()) {
                if ((rs != null) && (rs.next())) {
                    this.id = rs.getInt(1);
                }
            }
        } catch (SQLException e) {
            // TODO Auto-generated catch block
            e.printStackTrace();
            throw new IllegalStateException(e);
        }

    }

    static class TabletComparator
            implements Comparator<Tablet> {
        public int compare(Tablet o1, Tablet o2) {
            String year1 = (o1.foundYear != null) ? (o1.foundYear.date.getText()) : (null);
            String year2 = (o2.foundYear != null) ? (o2.foundYear.date.getText()) : (null);
            if (year1 == null && year2 == null) return 0;
            if (year1 == null) return 1;
            if (year2 == null) return -1;
            return year1.compareTo(year2);
        }
    }

    @Override
    public int compareTo(Name o) {
        return -Integer.compare(tablets.size(), o.tablets.size());
    }
}
