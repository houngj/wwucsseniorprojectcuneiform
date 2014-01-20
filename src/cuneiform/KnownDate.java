package cuneiform;

import java.io.PrintStream;

import cuneiform.stringComparator.Confidence;

class KnownDate {
	public final int    id;
    public final String text;
    
    public KnownDate(int id, String text) {
    	this.id = id;
    	this.text = text;
    }
    
    public int getID() {
    	return this.id;
    }
    
    public String getText() {
    	return this.text;
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
        output.format("  %-25s %s%n",      "matched transliteration", date.getText());
//      output.format("  %-25s %s%n",      "matched canonical name",  date.canonical);
        output.format("  %-25s %d%n",      "levenshtein distance",    confidence.distance);
        output.format("  %-25s %3.2f%%%n", "confidence",              confidence.confidence);
    }

    public KnownDate getKnownDate() {
    	return this.date;
    }
    
    @Override
    public int compareTo(FoundDate o) {
        return confidence.compareTo(o.confidence);
    }
}
