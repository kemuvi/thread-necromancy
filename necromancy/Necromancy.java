/*
 * Decompiled with CFR 0_115.
 */
package necromancy;

import java.io.File;
import java.io.FileOutputStream;
import java.io.InputStream;
import java.io.OutputStream;
import java.io.PrintStream;
import java.io.PrintWriter;
import java.net.URL;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Scanner;
import javax.swing.JFrame;
import necromancy.Database;
import necromancy.Post;
import necromancy.ThreadPuller;
import org.w3c.dom.Document;

public class Necromancy
extends JFrame {
    public static Calendar parseForumTime(String time) {
        int year = Integer.parseInt(time.substring(0, 4));
        int month = Integer.parseInt(time.substring(5, 7));
        int day = Integer.parseInt(time.substring(8, 10));
        int hour = Integer.parseInt(time.substring(11, 13));
        int minute = Integer.parseInt(time.substring(14, 16));
        int second = Integer.parseInt(time.substring(17, 19));
        Calendar c = Calendar.getInstance();
        c.set(1, year);
        c.set(2, month - 1);
        c.set(5, day);
        c.set(11, hour);
        c.set(12, minute);
        c.set(13, second);
        c.set(14, 0);
        return c;
    }

    public static long calToTime(Calendar cal) {
        return cal.getTimeInMillis() / 1000;
    }

    public static void log(String msg) {
        Necromancy.log(msg, true);
    }

    public static void log(String msg, boolean newLine) {
        try {
            if (newLine) {
                msg = String.valueOf(msg) + "\r\n";
            }
            PrintWriter out = new PrintWriter(new FileOutputStream(new File("log.txt"), true));
            out.print(msg);
            out.close();
            System.out.print(msg);
        }
        catch (Exception out) {
            // empty catch block
        }
    }

    public static boolean contains(ArrayList<Post> all, Post p) {
        boolean c = false;
        for (Post a : all) {
            if (!a.getName().equals(p.getName()) || a.getTime() != p.getTime()) continue;
            c = true;
            break;
        }
        return c;
    }

    public static void main(String[] args) {
        Database db;
        String[] subForum = new String[]{"audition", "off-topic-corner", "off-beat-cafe", "off-beat-forum-games"};
        long THREADID = 2988411;
        Necromancy.log("Attempting to connect to the database...", false);
        try {
            db = new Database();
            Necromancy.log("OK!");
        }
        catch (SQLException e) {
            Necromancy.log("Failed!");
            Necromancy.log(e.getMessage());
            return;
        }
        try {
            Necromancy.log("Determining number of pages...", false);
            ThreadPuller tp = new ThreadPuller(subForum, 2988411);
            int numPages = tp.getTotalPages();
            int startingPage = numPages - 10;
            ArrayList<Post> allPosts = new ArrayList<Post>();
            ResultSet allPostsRS = db.prepareAndExecuteQuery("SELECT * FROM ThreadNecromancyPosts ORDER BY post_time ASC", new String[0]);
            while (allPostsRS.next()) {
                allPosts.add(new Post(allPostsRS.getString("post_name"), allPostsRS.getLong("post_time")));
            }
            Necromancy.log(Integer.toString(allPosts.size()));
            Necromancy.log(String.format("Thread ID %s has %d pages.", tp.getThreadId(), numPages));
            Necromancy.log(String.format("Starting at page %d.", startingPage));
            int pageNum = startingPage;
            while (pageNum <= numPages) {
                Necromancy.log(String.format("Downloading page %d...", pageNum), false);
                ArrayList<Post> pagePosts = tp.getPosts(tp.getPage(pageNum));
                Necromancy.log("Done!");
                Necromancy.log(String.format("\nAdding posts on page %d to database...", pageNum));
                int i = 0;
                while (i < pagePosts.size()) {
                    Post currentPost = pagePosts.get(i);
                    if (!Necromancy.contains(allPosts, currentPost)) {
                        Necromancy.log("Adding post: " + currentPost);
                        db.prepareAndExecuteUpdate("INSERT INTO ThreadNecromancyPosts(post_name, post_time, post_page) VALUES(?,?,?)", currentPost.getName(), Long.toString(currentPost.getTime()), Integer.toString(pageNum));
                    } else {
                        Necromancy.log("Skipping post (exists already): " + currentPost);
                    }
                    ++i;
                }
                Necromancy.log("Done!");
                ++pageNum;
            }
            Scanner update = new Scanner(new URL("http://www.audifan.net/necro/update.php").openStream());
            update.close();
        }
        catch (Exception e) {
            e.printStackTrace();
        }
    }
}

