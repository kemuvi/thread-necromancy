/*
 * Decompiled with CFR 0_115.
 */
package necromancy;

public class Post {
    private String name;
    private long time;

    public Post(String name, long time) {
        this.name = name;
        this.time = time;
    }

    public String getName() {
        return this.name;
    }

    public long getTime() {
        return this.time;
    }

    public String toString() {
        return String.valueOf(this.name) + ": " + this.time;
    }

    public boolean equals(Post other) {
        if (other.getName().equals(this.name) && other.getTime() == this.time) {
            return true;
        }
        return false;
    }
}

