package cuneiform.stringComparator;

public class SumerianLevenstheinSubstringComparator {

    protected final static int minimum(int a, int b, int c) {
        int t1 = (a < b) ? (a) : (b);
        return (c < t1) ? (c) : (t1);
    }

    static int getCost(String c1, String c2) {
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
        } else if (c1.equalsIgnoreCase("{d}en.zu") && c2.equalsIgnoreCase("{d}suen")) {
            return 0;
        } else if (c1.equalsIgnoreCase("{d}suen") && c2.equalsIgnoreCase("{d}en.zu")) {
            return 0;
        } else {
            return 1;
        }
    }

    /**
     * Calculates the minimum number of edits such that foundGraphemes will
     * start with known, starting at position foundStart. Uses a hacky hacky way
     * to return multiple values. Move on. Nothing to see here.
     *
     * @param known
     * @param foundGraphemes
     * @param foundStart
     * @param conf
     *            An array of at least size 1, in which the confidence will be
     *            placed
     * @param indx
     *            An array of at least size 1, in which the number of graphemes
     *            that were in the best substring will be placed
     * @param dist
     *            An array of at least size 1, in which the number of edits will
     *            be placed
     */
    public static void compare(String known, String[] foundGraphemes, final int foundStart, double[] conf, int[] indx, int[] dist) {
        String[] knownGraphemes = known.split("-| ");
        int[][] distance = new int[knownGraphemes.length + 1][foundGraphemes.length - foundStart + 1];

        for (int i = 0; i <= knownGraphemes.length; i++)
            distance[i][0] = i;
        for (int j = 1; j <= foundGraphemes.length - foundStart; j++)
            distance[0][j] = j;

        for (int i = 1; i <= knownGraphemes.length; i++) {
            for (int j = 1; j <= foundGraphemes.length - foundStart; j++) {
                int cost = getCost(knownGraphemes[i - 1], foundGraphemes[foundStart + j - 1]);
                distance[i][j] = minimum(distance[i - 1][j] + 1, distance[i][j - 1] + 1, distance[i - 1][j - 1] + cost);
            }
        }

        int bestIndex = 0;
        int bestValue = Integer.MAX_VALUE;
        for (int i = 0; i <= foundGraphemes.length - foundStart; ++i) {
            if (distance[knownGraphemes.length][i] <= bestValue) {
                bestIndex = i;
                bestValue = distance[knownGraphemes.length][i];
            }
        }

        conf[0] = 100.0 * (knownGraphemes.length - bestValue) / knownGraphemes.length;
        indx[0] = bestIndex;
        dist[0] = bestValue;
    }

}
