package util;

import org.jsoup.Jsoup;
import org.jsoup.nodes.Document;
import org.jsoup.nodes.Element;
import org.jsoup.select.Elements;

import java.io.*;
import java.util.HashMap;
import java.util.HashSet;
import java.util.Map;
import java.util.Set;


public class ExtractLinks {
    public static void main(String[] args) throws Exception {
        // Generate url -> file map
        BufferedReader br = new BufferedReader(new FileReader("/Users/lemonade/Documents/csci572/hw4/NYTIMES/URLtoHTML_nytimes_news.csv"));
        String line =  null;
        Map<String, String> urlFileMap = new HashMap<String, String>();
        Map<String, String> fileUrlMap = new HashMap<String, String>();

        while((line=br.readLine())!=null){
            String[] str = line.split(",");
            urlFileMap.put(str[1], str[0]);
            fileUrlMap.put(str[0], str[1]);
        }

        File dir = new File("/Users/lemonade/Documents/csci572/hw4/NYTIMES/nytimes");
        Set<String> edges = new HashSet<String>();

        for (File file : dir.listFiles()) {
            Document doc = Jsoup.parse(file, "UTF-8", fileUrlMap.get(file.getName()));
            Elements links = doc.select("a[href]");

            for (Element link : links) {
                String url = link.attr("abs:href").trim();
                if (urlFileMap.containsKey(url)) {
                    edges.add(file.getName() + " " + urlFileMap.get(url));
                }
            }
        }

        PrintWriter writer = new PrintWriter("/Users/lemonade/Documents/csci572/hw4/NYTIMES/edgeList.txt");
        System.out.println(edges.size());

        for (String s : edges) {
            writer.println(s);
        }

        writer.flush();
        writer.close();
    }

    private static void print(String msg, Object... args) {
        System.out.printf((msg) + "%n", args);
    }

    private static String trim(String s, int width) {
        if (s.length() > width) {
            return s.substring(0, width-1) + ".";
        } else {
            return s;
        }
    }
}
