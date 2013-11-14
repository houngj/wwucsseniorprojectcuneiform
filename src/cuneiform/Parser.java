package cuneiform;

import java.io.BufferedReader;
import java.io.FileReader;
import java.io.IOException;
import java.io.PrintStream;

public class Parser {
    public static final String sourcePath  = "ur3_20110805_public.atf";
    public static final int    threadCount = Runtime.getRuntime().availableProcessors();

    public static void main(String[] args)
            throws IOException,
            InterruptedException {

        BufferedReader reader = new BufferedReader(new FileReader(sourcePath));
        ParallelExtractor dateExtractor = new ParallelExtractor(reader);
        dateExtractor.spawnThreads(threadCount);
        dateExtractor.join();

        try (PrintStream ps = new PrintStream("sorted.txt")) {
            dateExtractor.printStats(ps);
            dateExtractor.printSorted(ps);
        }
        dateExtractor.printStats(System.out);
    }
}
