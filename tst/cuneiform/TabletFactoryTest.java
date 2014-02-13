package cuneiform;

import static org.junit.Assert.*;

import org.junit.Test;

public class TabletFactoryTest {

    @Test
    public void testStripLineNumbers() {
        String input    = "1. 1 (disz)";
        String expected = "1 (disz)";
        String actual = TabletFactory.stripLineNumber(input);
        assertEquals(expected, actual);
    }

}
