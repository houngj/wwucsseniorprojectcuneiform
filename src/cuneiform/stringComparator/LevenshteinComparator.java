package cuneiform.stringComparator;


public class LevenshteinComparator
        implements StringComparator {

    @Override
    public Confidence compare(String str1, String str2) {
        int[][] distance = new int[str1.length() + 1][str2.length() + 1];

        for (int i = 0; i <= str1.length(); i++)
            distance[i][0] = i;
        for (int j = 1; j <= str2.length(); j++)
            distance[0][j] = j;

        for (int i = 1; i <= str1.length(); i++) {
            for (int j = 1; j <= str2.length(); j++) {
                int cost = (str1.charAt(i - 1) == str2.charAt(j - 1)) ? 0 : 1;
                distance[i][j] = minimum(distance[i - 1][j] + 1, distance[i][j - 1] + 1, distance[i - 1][j - 1] + cost);
            }
        }

        return new Confidence(distance[str1.length()][str2.length()], str1.length(), str2.length());
    }

    private static int minimum(int a, int b, int c) {
        int t1 = (a < b) ? (a) : (b);
        return (c < t1) ? (c) : (t1);
    }
}
