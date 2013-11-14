package cuneiform.stringComparator;

public class Confidence
        implements Comparable<Confidence> {
    public final int    distance;
    public final double confidence;

    public Confidence(int distance, double confidence) {
        this.distance   = distance;
        this.confidence = confidence;
    }

    public Confidence(int distance, int length1, int length2) {
        this(distance, 100.0 - 100.0 * distance / max(length1, length2));
    }

    @Override
    public int compareTo(Confidence o) {
        return Double.compare(this.confidence, o.confidence);
    }
    
    private static int max(int a, int b) {
        return (a > b) ? (a) : (b); 
    }
}
