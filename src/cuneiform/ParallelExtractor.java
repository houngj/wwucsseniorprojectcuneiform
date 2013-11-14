package cuneiform;

import java.io.BufferedReader;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.io.PrintStream;
import java.util.ArrayList;
import java.util.Collections;
import java.util.List;

class ParallelExtractor
        implements Runnable {
    private final DateExtractor dateExtractor;
    private final NameExtractor nameExtractor;
    private final TabletFactory factory;
    private final List<Tablet>  tablets   = new ArrayList<>();
    private Thread[]            threads;
    private int                 years     = 0;
    private int                 months    = 0;
    private double              yearConf  = 0;
    private double              monthConf = 0;

    public ParallelExtractor(BufferedReader reader)
            throws FileNotFoundException {
        dateExtractor = new DateExtractor();
        nameExtractor = new NameExtractor();
        factory = new TabletFactory(reader);
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
        while ((t = getTablet()) != null) {
            dateExtractor.process(t);
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
}
