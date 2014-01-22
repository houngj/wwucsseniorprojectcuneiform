package cuneiform;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.PrintStream;
import java.util.ArrayList;
import java.util.Collections;
import java.util.List;
import java.sql.*;

class ParallelExtractor
        implements Runnable {
    private final NameExtractor nameExtractor;
    private final TabletFactory factory;
    private final List<Tablet>  tablets   = new ArrayList<>();
    private Thread[]            threads;
    private DateExtractor       dateExtractor;
    private int                 years     = 0;
    private int                 months    = 0;
    private double              yearConf  = 0;
    private double              monthConf = 0;

    private static final String DB_HOST = "jdbc:mysql://wwu-cuneiform.co5tt9crocw2.us-west-2.rds.amazonaws.com/cuneiform";
    private static final String DB_USER = "dingo";
    private static final String DB_PASS = "hungry!";

    public ParallelExtractor(BufferedReader reader) {
        this.factory = new TabletFactory(reader);
        this.nameExtractor = new NameExtractor();
        clearDatabase();
    }

    void clearDatabase() {
        final String[] tables = new String[] {
                "line", "month_reference", "year_reference",
                "text_section", "tablet_object", "tablet"
        };
        try (Connection conn = getConnection();
                Statement stmnt = conn.createStatement()) {
            for (String table : tables) {
                stmnt.executeUpdate(String.format("DELETE FROM `%s`;", table));
                stmnt.executeUpdate(String.format("ALTER TABLE `%s` AUTO_INCREMENT = 1;", table));
            }
        } catch (SQLException e) {
            // TODO Auto-generated catch block
            e.printStackTrace();
            throw new IllegalStateException(e);
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
            throws InterruptedException {
        for (int i = 0; i < threads.length; ++i) {
            threads[i].join();
        }
    }

    public void printStats(PrintStream output) {
        output.format("months with data:         %3.3f%%  %d / %d%n", 100.0 * months / tablets.size(), months, tablets.size());
        output.format("years with data:          %3.3f%%  %d / %d%n", 100.0 * years / tablets.size(), years, tablets.size());
        output.format("average month confidence: %3.3f%%%n", monthConf / tablets.size());
        output.format("average year confidence:  %3.3f%%%n", yearConf / tablets.size());
        output.println();
    }

    public void printSorted(PrintStream output) {
        Collections.sort(tablets);
        for (Tablet t : tablets) {
            t.printStats(output);
        }
    }

    public void printUnmatched(PrintStream output) {
        for(Tablet t : tablets) {
            if(t.foundMonth == null && t.foundYear == null) {
                t.print(output);
            }
        }
    }

    public void printNames(PrintStream output) {
        nameExtractor.print(output);
    }

    private Tablet getTablet() {
        try {
            return factory.build();
        } catch (IOException e) {
            return null;
        }
    }

    public void run() {
        Tablet t = null;

        // Establish the database connection.
        // Although Connection objects should be thread-safe, let's give
        // each thread its own connection object.

        try (Connection conn = getConnection()) {
            if (null != conn)
            {
                // We have a valid connection. Let's go !

                this.dateExtractor = new DateExtractor(conn);

                while ((t = getTablet()) != null) {
                    dateExtractor.process(conn, t);
                    nameExtractor.process(t);
                    System.err.println(tablets.size());

                    synchronized (tablets) {
                        tablets.add(t);

                        if (t.foundMonth != null) {
                            months++;
                            monthConf += t.foundMonth.confidence.confidence;
                        }
                        if (t.foundYear != null) {
                            years++;
                            yearConf += t.foundYear.confidence.confidence;
                        }
                    }
                }
            }
        } catch (SQLException e)
        {
            // Something dreadful happened SQL-wise.

            System.out.println("Database access problem encountered: " + e.getMessage());
        }
    }

    private static Connection getConnection() throws SQLException
    {
        return DriverManager.getConnection(DB_HOST, DB_USER, DB_PASS);
    }
}
