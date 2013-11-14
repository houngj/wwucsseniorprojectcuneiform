package cuneiform;

import java.io.PrintStream;

import cuneiform.stringComparator.Confidence;

class KnownDate {
    public final String transliteration;
    public final String canonical;

    public KnownDate(String line) {
        String[] parts = line.split("\t");
        transliteration = parts[1].trim();
        canonical = parts[0].trim();
    }

    public KnownDate(String trans, String canon) {
        this.transliteration = trans;
        this.canonical = canon;
    }
}

class FoundDate
        implements Comparable<FoundDate> {
    public final KnownDate  date;
    public final String     foundDate;
    public final Confidence confidence;

    public FoundDate(KnownDate date, String found, Confidence conf) {
        this.date = date;
        this.foundDate = found;
        this.confidence = conf;
    }

    public void printStats(PrintStream output) {
        output.format("  %-25s %s%n",      "found value",             foundDate);
        output.format("  %-25s %s%n",      "matched transliteration", date.transliteration);
        output.format("  %-25s %s%n",      "matched canonical name",  date.canonical);
        output.format("  %-25s %d%n",      "levenshtein distance",    confidence.distance);
        output.format("  %-25s %3.2f%%%n", "confidence",              confidence.confidence);
    }

    @Override
    public int compareTo(FoundDate o) {
        return confidence.compareTo(o.confidence);
    }
}
