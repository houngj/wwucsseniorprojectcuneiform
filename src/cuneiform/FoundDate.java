package cuneiform;

import java.io.PrintStream;

import cuneiform.stringComparator.Confidence;

public class FoundDate
        implements Comparable<FoundDate> {
    public final KnownDate  date;
    public final String     foundDate;
    public final Confidence confidence;

    public FoundDate(KnownDate date, String found, Confidence conf) {
        this.date = date;
        this.foundDate = found;
        this.confidence = conf;
        if (found.isEmpty()) {
            throw new IllegalStateException();
        }
    }

    public void printStats(PrintStream output, int depth) {
        String indent = "";
        for(int i = 0; i < depth; ++i) {
            indent += " ";
        }
        output.format(indent + "%-25s %s%n",      "found value",             foundDate);
        output.format(indent + "%-25s %s%n",      "matched transliteration", date.getText());
//      output.format(indent + "%-25s %s%n",      "matched canonical name",  date.canonical);
        output.format(indent + "%-25s %d%n",      "levenshtein distance",    confidence.distance);
        output.format(indent + "%-25s %3.2f%%%n", "confidence",              confidence.confidence);
    }

    public KnownDate getKnownDate() {
    	return this.date;
    }

    @Override
    public int compareTo(FoundDate o) {
        return confidence.compareTo(o.confidence);
    }
}