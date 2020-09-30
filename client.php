<?php

// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');

$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$results = false;

if ($query)
{
  // The Apache Solr Client library should be on the include path
  // which is usually most easily accomplished by placing in the
  // same directory as this script ( . or current directory is a default
  // php include path entry in the php.ini)
  require_once('Apache/Solr/Service.php');

  // create a new solr service instance - host, port, and webapp
  // path (all defaults in this example)
  $solr = new Apache_Solr_Service('localhost', 8983, '/solr/myexample');

  // if magic quotes is enabled then stripslashes will be needed
  if (get_magic_quotes_gpc() == 1)
  {
    $query = stripslashes($query);
  }

  // in production code you'll always want to use a try /catch for any
  // possible exceptions emitted  by searching (i.e. connection
  // problems or a query parsing error)
  try
  {
    $additionalParameters = array(
      'sort' => $_GET["rank"]
    );
    $results = $solr->search($query, 0, $limit, $additionalParameters);
  }
  catch (Exception $e)
  {
    // in production you'd probably log or email this error to an admin
    // and then show a special message to the user but for this example
    // we're going to show the full exception
    die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
  }
}

?>
<html>
  <head>
    <title>PHP Solr Client Example</title>
  </head>
  <body>
    <form  accept-charset="utf-8" method="get">
      <label for="q">Search:</label>
      <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
      <input type="radio" name="rank" <?php if (!isset($rank) || (isset($rank) && $rank=="")) echo "checked";?> value="">Lucene
      <input type="radio" name="rank" <?php if ($_GET['rank'] == "pageRankFile desc" || (isset($rank) && $rank=="pageRankFile desc")) echo "checked";?> value="pageRankFile desc">pageRankFile
      <input type="submit"/>
    </form>
<?php

// display results
$idURL = array();
try {
      $idURLCSV = fopen("URLtoHTML_nytimes_news.csv", "r");

      while (!feof($idURLCSV)) {
        $line = fgetcsv($idURLCSV, 0);
        $idURL[$line[0]] = $line[1];
      }

      fclose($idURLCSV);
} catch (Exception $error) {

}
if ($results)
{
  $total = (int) $results->response->numFound;
  $start = min(1, $total);
  $end = min($limit, $total);
?>
    <div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
    <ol>
<?php
  // iterate result documents
  foreach ($results->response->docs as $doc)
  {
?>
      <li>
        <table style="border: 1px solid black; text-align: left">
<?php
    // iterate document fields / values
    $id = $doc->id;
    $title = $doc->title;
    $url = $doc->og_url;
    if ($url == "") {
      $csv_id = str_replace("/Users/lemonade/Downloads/solr-7.7.2-3/crawl_data/", "", $id);
      $url = $idURL[$csv_id];

    }

    $desc = $doc->og_description;
    if ($desc == "") {
      $desc = "N/A";
    }

?>
    <tr>
      <th>id</th>
      <td><?php echo htmlspecialchars($id, ENT_NOQUOTES, 'utf-8'); ?></td>
    </tr>

    <tr>
      <th>og_description</th>
      <td><?php echo htmlspecialchars($desc, ENT_NOQUOTES, 'utf-8'); ?></td>
    </tr>

    <tr>
      <th>title</th>
      <td><a href="<?php echo htmlspecialchars($url, ENT_NOQUOTES, 'utf-8'); ?>"><?php echo htmlspecialchars($title, ENT_NOQUOTES, 'utf-8'); ?> </a></td>
    </tr>

    <tr>
      <th>og_url</th>
      <td><a href="<?php echo htmlspecialchars($url, ENT_NOQUOTES, 'utf-8'); ?>"><?php echo htmlspecialchars($url, ENT_NOQUOTES, 'utf-8'); ?> </a></td>
    </tr>

        </table>
      </li>
<?php
  }
?>
    </ol>
<?php
}
?>
  </body>
</html>
