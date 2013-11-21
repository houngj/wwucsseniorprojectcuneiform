package cuneiform.stringComparator;

public class SumerianComparator
        extends StringComparator {

    @Override
    public Confidence compare(String arg0, String arg1) {
        String[] str1 = arg0.split("-| ");
        String[] str2 = arg1.split("-| ");
        int[][] distance = new int[str1.length + 1][str2.length + 1];

        for (int i = 0; i <= str1.length; i++)
            distance[i][0] = i;
        for (int j = 1; j <= str2.length; j++)
            distance[0][j] = j;

        for (int i = 1; i <= str1.length; i++) {
            for (int j = 1; j <= str2.length; j++) {
                int cost = getCost(str1[i - 1], str2[j - 1]);
                distance[i][j] = minimum(distance[i - 1][j] + 1, distance[i][j - 1] + 1, distance[i - 1][j - 1] + cost);
            }
        }

        return new Confidence(distance[str1.length][str2.length], str1.length, str2.length);
    }

    public int getCost(String c1, String c2) {
        c1 = c1.replace("<>[]", "");
        c2 = c2.replace("<>[]", "");
        if (c1.isEmpty() ^ c2.isEmpty()) {
            return 1;
        }
        if (c1.equalsIgnoreCase(c2)) {
            return 0;
        } else if (c1.equalsIgnoreCase("{d}" + c2) || c2.equalsIgnoreCase("{d}" + c1)) {
            return 0;
        } else if (c1.equalsIgnoreCase(c2 + "{ki}") || c2.equalsIgnoreCase(c1 + "{ki}")) {
            return 0;
        } else if (c1.equalsIgnoreCase(c2 + "(disz)") || c2.equalsIgnoreCase(c1 + "(disz)")) {
            return 0;
        } else if (c1.equalsIgnoreCase(c2 + "#") || c2.equalsIgnoreCase(c1 + "#")) {
            return 0;
        } else {
            return 1;
        }
    }
}
