import networkx as nx
G = nx.read_edgelist("/Users/lemonade/Documents/csci572/hw4/NYTIMES/edgeList.txt", create_using=nx.DiGraph())
pr = nx.pagerank(G, alpha = 0.85, personalization=None, max_iter=30, tol=1e-06, nstart=None, weight='weight', dangling=None)
f = open("/Users/lemonade/Documents/csci572/hw4/NYTIMES/external_pageRankFile.txt", "w")
for key, value in pr.items():
    f.write("/Users/lemonade/Downloads/solr-7.7.2-3/crawl_data/" + key + '=' + str(value) + "\n")
print(pr)
