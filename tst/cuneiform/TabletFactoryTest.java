package cuneiform;

import static org.junit.Assert.assertEquals;

import java.io.BufferedReader;
import java.io.ByteArrayInputStream;
import java.io.ByteArrayOutputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.PrintStream;
import java.io.Reader;

import org.junit.Test;

import cuneiform.tablet.TabletFactory;
import cuneiform.tablet.TabletGroup;

public class TabletFactoryTest {
    private static String[][] testData = new String[][] { {"data/test/test_1_input.atf", "data/test/test_1_expected.atf"} };
    @Test
    public void testStripLineNumbers() {
        String input    = "1. 1 (disz)";
        String expected = "1 (disz)";
        String actual = TabletFactory.stripLineNumber(input);
        assertEquals(expected, actual);
    }

    @Test
    public void TestParse() throws IOException {
        for (String[] data : testData) {
            String source = data[0];
            String expected = data[1];
            BufferedReader reader = new BufferedReader(new FileReader(source));
            TabletFactory factory = new TabletFactory(reader);
            ByteArrayOutputStream os = new ByteArrayOutputStream();
            PrintStream ps = new  PrintStream(os);
            TabletGroup tg = null;
            while((tg = factory.build()) != null) {
                tg.print(ps);
            }

            assertEqual(os.toByteArray(), new File(expected));
        }
    }

    private void assertEqual(byte[] computed, File expected)
            throws FileNotFoundException, IOException {
        InputStream computedIS = new ByteArrayInputStream(computed);
        InputStream expectedIs = new FileInputStream(expected);
        assertEqual(computedIS, expectedIs);
    }

    private void assertEqual(InputStream computed, InputStream expected)
            throws IOException {
        assertEqual(new InputStreamReader(computed), new InputStreamReader(expected));
    }

    private void assertEqual(Reader computed, Reader expected) throws IOException {
        BufferedReader c = new BufferedReader(computed);
        BufferedReader e = new BufferedReader(expected);
        assertEqual(c, e);
    }

    private void assertEqual(BufferedReader computed, BufferedReader expected)
            throws IOException {
        String e = expected.readLine();
        String c = computed.readLine();

        while((e != null) && e.equals(c)) {
            e = expected.readLine();
            c = computed.readLine();
        }
        if (c != null || e != null) {
            System.err.format("computed: %s%n", c);
            for(int i = 0; i < 5; ++i) {
                System.err.format("computed: %s%n", computed.readLine());
            }

            System.err.format("expected: %s%n", e);
            for(int i = 0; i < 5; ++i) {
                System.err.format("expected: %s%n", expected.readLine());
            }


            throw new IllegalStateException("Not equal");
        }
    }
}
