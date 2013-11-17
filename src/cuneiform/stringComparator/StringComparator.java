package cuneiform.stringComparator;


public abstract class StringComparator {
    public abstract Confidence compare(String str1, String str2);
    protected final static int minimum(int a, int b, int c) {
        int t1 = (a < b) ? (a) : (b);
        return (c < t1) ? (c) : (t1);
    }
}
