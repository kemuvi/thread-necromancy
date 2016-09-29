/*
 * Decompiled with CFR 0_115.
 */
package necromancy;

import java.io.IOException;
import java.io.InputStream;
import java.io.Reader;
import java.io.StringReader;
import java.net.URL;
import java.util.ArrayList;
import java.util.Scanner;
import java.util.regex.Matcher;
import java.util.regex.Pattern;
import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;
import javax.xml.parsers.ParserConfigurationException;
import necromancy.Necromancy;
import necromancy.Post;
import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;
import org.xml.sax.InputSource;
import org.xml.sax.SAXException;

public class ThreadPuller {
    private String url;
    private long threadId;
    private DocumentBuilder docBuilder;
    private Document page1 = null;
    private int totalPages;

    public ThreadPuller(String[] subForums, long threadId) throws ParserConfigurationException, IOException, SAXException {
        this.threadId = threadId;
        this.url = "http://forums.redbana.com/forum/";
        String[] arrstring = subForums;
        int n = arrstring.length;
        int n2 = 0;
        while (n2 < n) {
            String s = arrstring[n2];
            this.url = String.valueOf(this.url) + s + "/";
            ++n2;
        }
        this.url = String.valueOf(this.url) + Long.toString(threadId);
        DocumentBuilderFactory dbf = DocumentBuilderFactory.newInstance();
        dbf.setValidating(false);
        this.docBuilder = dbf.newDocumentBuilder();
        this.page1 = this.getPage(1);
        Element root = (Element)this.page1.getElementsByTagName("html").item(0);
        Element threadViewTab = this.getElementById(root, "thread-view-tab");
        Element curr = (Element)threadViewTab.getElementsByTagName("div").item(0);
        curr = (Element)curr.getElementsByTagName("div").item(0);
        curr = (Element)curr.getElementsByTagName("ul").item(0);
        curr = (Element)curr.getElementsByTagName("li").item(1);
        curr = (Element)curr.getElementsByTagName("div").item(0);
        curr = (Element)curr.getElementsByTagName("form").item(0);
        curr = (Element)curr.getElementsByTagName("div").item(0);
        curr = (Element)curr.getElementsByTagName("span").item(0);
        this.totalPages = Integer.parseInt(curr.getChildNodes().item(0).getTextContent().replace(",", ""));
    }

    public long getThreadId() {
        return this.threadId;
    }

    public int getTotalPages() {
        return this.totalPages;
    }

    private Element getElementById(Element element, String id) {
        Element found = null;
        if (element.getAttribute("id").equals(id)) {
            found = element;
        } else {
            NodeList children = element.getChildNodes();
            if (children.getLength() > 0) {
                int i = 0;
                while (i < children.getLength()) {
                    Element result;
                    if (children.item(i).getNodeType() == 1 && (result = this.getElementById((Element)children.item(i), id)) != null) {
                        found = result;
                        break;
                    }
                    ++i;
                }
            }
        }
        return found;
    }

    public ArrayList<Post> getPosts(Document doc) {
        Element root = (Element)doc.getElementsByTagName("html").item(0);
        Element threadContainerDiv = this.getElementById(root, "thread-view-tab");
        NodeList threadContainerChildren = threadContainerDiv.getChildNodes();
        Element conversationContainer = null;
        int i = 0;
        while (i < threadContainerChildren.getLength()) {
            if (threadContainerChildren.item(i).getNodeType() == 1 && ((Element)threadContainerChildren.item(i)).getAttribute("class").equals("conversation-content")) {
                conversationContainer = (Element)threadContainerChildren.item(i);
                break;
            }
            ++i;
        }
        NodeList pagePosts = ((Element)conversationContainer.getElementsByTagName("ul").item(0)).getChildNodes();
        ArrayList<Post> posts = new ArrayList<Post>();
        int i2 = 0;
        while (i2 < pagePosts.getLength()) {
            if (pagePosts.item(i2).getNodeType() == 1) {
                Element start = (Element)pagePosts.item(i2);
                Element curr = (Element)start.getElementsByTagName("div").item(0);
                curr = (Element)curr.getElementsByTagName("div").item(0);
                curr = (Element)curr.getElementsByTagName("div").item(0);
                curr = (Element)curr.getElementsByTagName("div").item(0);
                curr = (Element)curr.getElementsByTagName("div").item(0);
                String name = curr.getChildNodes().item(0).getChildNodes().item(0).getChildNodes().item(0).getTextContent();
                start = (Element)pagePosts.item(i2);
                curr = (Element)start.getElementsByTagName("div").item(18);
                curr = (Element)curr.getElementsByTagName("div").item(3);
                curr = (Element)curr.getElementsByTagName("div").item(1);
                curr = (Element)curr.getElementsByTagName("div").item(0);
                curr = (Element)curr.getElementsByTagName("time").item(0);
                String time = curr.getAttribute("datetime");
                long currPostTime = Necromancy.calToTime(Necromancy.parseForumTime(time));
                posts.add(new Post(name, currPostTime));
            }
            ++i2;
        }
        return posts;
    }

    public Document getPage(int pageId) throws IOException, SAXException {
        String[] removes;
        if (pageId == 1 && this.page1 != null) {
            return this.page1;
        }
        String pageUrl = new String(this.url);
        if (pageId >= 2) {
            pageUrl = String.valueOf(pageUrl) + "/page" + pageId;
        }
        String xml = "";
        Scanner in = new Scanner(new URL(pageUrl).openStream());
        while (in.hasNextLine()) {
            xml = String.valueOf(xml) + in.nextLine() + "\n";
        }
        in.close();
        String[] arrstring = removes = new String[]{"<style type=\"text/css\">", "<\\\\\\/style>", "itemscope ", "&reg;", "&nbsp;", "&copy;", " \\|\\| document\\.write\\('<script type=\"text/javascript\" src=\"http\\:\\/\\/forums.redbana.com\\/js\\/jquery\\/jquery-1.7.2.min.js\"><\\\\\\/script>'\\);", "<\\!DOCTYPE html>", "<input type=\"button\" value=\"Spoiler\"onclick=\"if \\(this\\.parentNode\\.parentNode\\.getElementsByTagName\\('div'\\)\\[1\\]\\.getElementsByTagName\\('div'\\)\\[0\\]\\.style\\.display \\!= ''\\) \\{ this\\.parentNode\\.parentNode\\.getElementsByTagName\\('div'\\)\\[1\\]\\.getElementsByTagName\\('div'\\)\\[0\\]\\.style\\.display = '';this\\.innerText = ''; this\\.value = 'Hide'; \\} else \\{ this\\.parentNode\\.parentNode\\.getElementsByTagName\\('div'\\)\\[1\\]\\.getElementsByTagName\\('div'\\)\\[0\\]\\.style\\.display = 'none'; this\\.value = 'Spoiler';\\}\">", "(data\\-orig\\-)?src\\=\\\"[^\\\"]+?\\\""};
        int n = arrstring.length;
        int n2 = 0;
        while (n2 < n) {
            String r = arrstring[n2];
            xml = Pattern.compile(r, 32).matcher(xml).replaceAll("");
            ++n2;
        }
        xml = Pattern.compile("<font \\*\\*\\*\\*\\*\\*\\*\\*\\*\\*\\*\\*\\*\\*\\*\\*\\*>", 32).matcher(xml).replaceAll("<font>");
        return this.docBuilder.parse(new InputSource(new StringReader(xml)));
    }
}

