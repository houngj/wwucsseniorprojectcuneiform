package cuneiform;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.PrintStream;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.List;

import cuneiform.tablet.Container;
import cuneiform.tablet.TabletFactory;
import cuneiform.tablet.TabletGroup;
import cuneiform.tablet.TextSection;

class ParallelExtractor
        implements Runnable {
    private final TabletFactory     factory;
    private final DateExtractor     dateExtractor;
    private final NameExtractor     nameExtractor;
    private final List<TabletGroup> tablets           = new ArrayList<>();
    private Thread[]                threads;
    private int                     years             = 0;
    private int                     months            = 0;
    private int                     tabletsWithMonths = 0;
    private int                     tabletsWithYears  = 0;
    private double                  yearConf          = 0;
    private double                  monthConf         = 0;
    private final String            dbHost;
    private final String            dbUser;
    private final String            dbPass;


    public ParallelExtractor(BufferedReader reader, String dbHost, String dbUser, String dbPass) {
        this.factory       = new TabletFactory(reader);
        this.dbHost        = dbHost;
        this.dbUser        = dbUser;
        this.dbPass        = dbPass;
        // Initialize these here so we have one, not one per thread
        this.nameExtractor = new NameExtractor();
        this.dateExtractor = createDateExtractor();
    }

    private DateExtractor createDateExtractor() {
        // DateExtractor needs a database connection to retrieve known dates.
        try (Connection conn = getConnection()) {
            return new DateExtractor(conn);
        } catch (SQLException e) {
            throw new IllegalStateException("Cannot create DateExtractor", e);
        }
    }

    public void spawnThreads(int numThreads) {
        threads = new Thread[numThreads];
        for (int i = 0; i < threads.length; ++i) {
            threads[i] = new Thread(this);
            threads[i].start();
        }
    }

    public void join()
            throws InterruptedException
    {
        for (int i = 0; i < threads.length; ++i) {
            threads[i].join();
        }
    }

    public void printNames(PrintStream ps) {
        nameExtractor.print(ps);
    }

    public void printUnmatched(PrintStream ps) {
        for(TabletGroup tg : tablets) {
            if (tg.hasYear() == false && tg.hasMonth() == false) {
                tg.print(ps);
            }
        }
    }

    public void printStats(PrintStream output) {
        output.format("months with data:         %3.3f%%  %d / %d%n", 100.0 * tabletsWithMonths / tablets.size(), tabletsWithMonths, tablets.size());
        output.format("years with data:          %3.3f%%  %d / %d%n", 100.0 * tabletsWithYears / tablets.size(), tabletsWithYears, tablets.size());
        output.format("average month confidence: %3.3f%%%n", monthConf / months);
        output.format("average year confidence:  %3.3f%%%n", yearConf / years);
        output.println();
    }

    public void printSorted(PrintStream output) {
        for (TabletGroup t : tablets) {
            t.printStats(output);
        }
    }

    private TabletGroup getTablet() {
        try {
            return factory.build();
        } catch (IOException e) {
            return null;
        }
    }

    public void run() {
        TabletGroup t = null;

        // Establish the database connection.
        // Although Connection objects should be thread-safe, let's give
        // each thread its own connection object.

        try (Connection conn = getConnection()) {
            if (conn == null) {
                throw new IllegalStateException("Didn't receive database connection");
            }
            // We have a valid connection. Let's go !

            while ((t = getTablet()) != null) {
                dateExtractor.process(conn, t);
                nameExtractor.process(conn, t);
                t.insert(conn);
                System.err.println(tablets.size());
                addTabletGroupStats(t);
            }

        } catch (SQLException e) {
            // Something dreadful happened SQL-wise.
            System.out.println("Database access problem encountered: " + e.getMessage());
            System.exit(-1);
        }
    }

    void addTabletGroupStats(TabletGroup input) {
        synchronized (tablets) {
            tablets.add(input);
            if (input.hasMonth()) {
                tabletsWithMonths++;
            }
            if (input.hasYear()) {
                tabletsWithYears++;
            }
        }
        for (Container container : input.containers) {
            addTabletGroupStats(container);
        }
    }

    private void addTabletGroupStats(Container input) {
        if (input.section != null) {
            addTabletGroupStats(input.section);
        } else {
            for (Container container : input.containers) {
                addTabletGroupStats(container);
            }
        }
    }

    private void addTabletGroupStats(TextSection section) {
        synchronized (tablets) {
            for (FoundDate month : section.months) {
                months++;
                monthConf += month.confidence.confidence;
            }
            for (FoundDate year : section.years) {
                years++;
                yearConf += year.confidence.confidence;
            }
        }
    }

    private synchronized Connection getConnection()
            throws SQLException
    {
        return DriverManager.getConnection(dbHost, dbUser, dbPass);
    }

}
