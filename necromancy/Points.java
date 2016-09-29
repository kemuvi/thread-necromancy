/*
 * Decompiled with CFR 0_115.
 */
package necromancy;

public class Points {
    private String name;
    private int points;

    public Points(String name, long pts) {
        this.name = name;
        this.points = (int)pts;
    }

    public void addPoints(long pts) {
        this.points = (int)((long)this.points + pts);
    }

    public int getPoints() {
        return this.points;
    }

    public String getName() {
        return this.name;
    }
}

