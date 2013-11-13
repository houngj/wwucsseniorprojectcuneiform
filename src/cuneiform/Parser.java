package cuneiform;

import java.io.BufferedReader;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.IOException;
import java.io.PrintStream;
import java.util.ArrayList;
import java.util.Collections;
import java.util.List;

class ParallelDateExtractor
        implements Runnable {
    private final BufferedReader reader;
    private final DateExtractor  dateExtractor;
    private final TabletFactory  factory;
    public final List<Tablet>    tablets = new ArrayList<>();

    public ParallelDateExtractor(BufferedReader reader)
            throws FileNotFoundException {
        this.reader = reader;
        this.dateExtractor = new DateExtractor();
        this.factory = new TabletFactory(reader);
    }

    private synchronized Tablet getTablet() {
        try {
            return factory.build();
        } catch (IOException e) {
            return null;
        }
    }

    public void run() {
        Tablet t = null;
        while ((t = getTablet()) != null) {
            dateExtractor.process(t);
            synchronized (tablets) {
                tablets.add(t);
            }
        }
    }
}

public class Parser {

    public static void main(String[] args)
            throws IOException {
        try (FileReader is = new FileReader("ur3_20110805_public.atf");
                BufferedReader bis = new BufferedReader(is)) {
            ParallelDateExtractor pd = new ParallelDateExtractor(bis);
            Thread[] threads = new Thread[12];
            for (int i = 0; i < threads.length; ++i) {
                threads[i] = new Thread(pd);
                threads[i].start();
            }
            for (int i = 0; i < threads.length; ++i) {
                threads[i].join();
            }

            Collections.sort(pd.tablets);

            PrintStream ps = new PrintStream("sorted.txt");

            int years = 0;
            int months = 0;
            double yearConf = 0;
            double monthConf = 0;
            for (Tablet t : pd.tablets) {
                t.printStats(ps);
                if (t.monthConf != null) {
                    months++;
                    monthConf += t.monthConf.confidence;
                }
                if (t.yearConf != null) {
                    years++;
                    yearConf += t.yearConf.confidence;
                }
            }

            System.out.format("months %d / %d   %f%n", months, pd.tablets.size(), 100.0 * months / pd.tablets.size());
            System.out.format("years  %d / %d   %f%n", years, pd.tablets.size(), 100.0 * years / pd.tablets.size());
            System.out.format("month conf %f %n", monthConf / pd.tablets.size());
            System.out.format("year conf %f %n", yearConf / pd.tablets.size());

            ps.close();

        } catch (FileNotFoundException e) {
            e.printStackTrace();
        } catch (IOException e) {
            e.printStackTrace();
        } catch (InterruptedException e) {
            e.printStackTrace();
        }
    }
}
