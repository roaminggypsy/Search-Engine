# Search-Engine (Document in the work)

# 1. Web Crawling:
Crawled more than 12,000 NY Times pages using Java, crawler4j

# 2. Indexing HTML files:
- Indexed all pages with Apache Solr
- Developed client side with PHP, so users can enter a query and get matching results
- Extracted links with Java and Jsoup, computed PageRank scores via Python and NetworkX, and applied PageRank in Solr.


# 3. Spell checking and AutoComplete
spell correction: PHP version of Norvig's spelling corrector
<br/> autocoomplete: FuzzyLookupFactoory from Solr

<!--
Web Crawling:
work with a simple web crawler, download web pages from the crawl and gather webpage metadata.
crawler4j (an open source Java web crawler built upon the open source crawler4j library)
maximum pages to fetch: 20,000
maximum depth: 16
number of crawlers: 7 (multi-threading for efficiency)
file type: HTML, doc, pdf and different image formats
-->
