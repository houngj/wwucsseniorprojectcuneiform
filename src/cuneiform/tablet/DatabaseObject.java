package cuneiform.tablet;

public abstract class DatabaseObject {
    private int id = -1;

    public final int getID() {
        if (this.id == -1) {
            throw new IllegalStateException("id == -1");
        }
        return this.id;
    }

    protected final void setID(int id) {
        this.id = id;
    }

    public final boolean isInserted() {
        return (this.id != -1);
    }
}
