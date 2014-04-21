package cuneiform;

import cuneiform.tablet.DatabaseObject;

public class KnownDate extends DatabaseObject {
    public final String text;

    public KnownDate(int id, String text) {
        setID(id);
        this.text = text;
    }

    public String getText() {
        return this.text;
    }
}
