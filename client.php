<?php
// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');
header("Access-Control-Allow-Origin: *");

include 'SpellCorrector.php';

$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$corrected = false;
$results = false;
$useSpellCorrect = true;

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
    $corrected = SpellCorrector::correct($query);
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
<?php ini_set ('memory_limit', -1)?>

<html>
  <head>
    <title>PHP Solr Client Example</title>

    <script src="https://code.jquery.com/jquery-3.5.1.js" integrity="sha256-QWo7LDvxbWT2tbbQ97B53yJnYU3WhH/C8ycbRAkjPDc=" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js" integrity="sha256-T0Vest3yCU7pafRw9r+settMBX6JkKN06dqBnpQ8d30=" crossorigin="anonymous"></script>

    <script>
    var options = [];

  //   $(function() {
  //     function log( message ) {
  //       $( "<div>" ).text( message ).prependTo( "#log" );
  //       $( "#log" ).scrollTop( 0 );
  //     }
  //
  //   $( "#q" ).autocomplete({
  //     source: function( request, response ) {
  //       $.ajax({
  //         url: "http://localhost:8983/solr/myexample/suggest",
  //         dataType: "jsonp",
  //         data: {
  //           q: $("#q").val()
  //         },
  //         success: function( data ) {
  //           console.log(data);
  //           response( data );
  //         }
  //       });
  //     },
  //     minLength: 3,
  //     select: function( event, ui ) {
  //       log( ui.item ?
  //         "Selected: " + ui.item.label :
  //         "Nothing selected, input was " + this.value);
  //     },
  //     open: function() {
  //       $( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
  //     },
  //     close: function() {
  //       $( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
  //     }
  //   });
  // });

      function correctSearch() {
        document.getElementById("q").value = document.getElementById("corrected").text.trim();
        document.getElementById("search").submit();
      }

      function suggest() {
        document.getElementById('suggestions').style.visibility = 'visible';
        query = document.getElementById("q").value;
        if (window.XMLHttpRequest) {
          xmlhttp=new XMLHttpRequest();
        } else {
          xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
        }

        xmlhttp.onreadystatechange=function() {
          if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            // var list = document.getElementById("suggestions");
            // var i, L = list.options.length - 1;
            // for(i = L; i >= 0; i--) {
            //   list.remove(i);
            // }
            document.getElementById("suggestions").innerHTML = "";

            //
            // var mycars = new Array();
            // mycars[0]='Herr';
            // mycars[1]='Frau';
            //
            // JSON.parse(JSON.stringify(data.suggest.suggest))[after].suggestions;
            //
            //


            // document.getElementById('anrede').innerHTML = options;
            var len = JSON.parse(xmlhttp.responseText).suggest.suggest[query].numFound;
            options = [];
            for(var i = 0; i < len; i++) {
              var li = document.createElement('LI');
              li.setAttribute("id", "li" + i);
              li.setAttribute("overflow", "hidden");
              options.push(JSON.parse(xmlhttp.responseText).suggest.suggest[query].suggestions[i].term);
              li.innerHTML = options[i];
              li.onclick = function() {
                document.getElementById('q').value = this.innerText;
                document.getElementById('suggestions').style.visibility = 'hidden';
               };
              console.log(li);
              document.getElementById('suggestions').appendChild(li);
            }

              // options += '<option value="'+ JSON.parse(xmlhttp.responseText).suggest.suggest[query].suggestions[i].term +'" />';
            //console.log(JSON.parse(xmlhttp.responseText).suggest.suggest[query]);
            // console.log(options);
            // document.getElementById('suggestions').innerHTML = options;
            // }
            //
            //
            //
            //
            // console.log(xmlhttp.responseText);
            // var suggestions = xmlhttp.responseText;
            // var res = suggestions.split(",");
            // var len =res.length;
            // var text = "";
            // document.getElementById("demo").innerHTML = xmlhttp.responseText;
          }
        }
        xmlhttp.open("GET",
        "http://localhost:8983/solr/myexample/suggest?q=" + query,
        true);
        xmlhttp.send();
        }
    </script>
  </head>
  <body>
    <form id="search" accept-charset="utf-8" method="get">
      <label for="q">Search:</label>
      <input id="q" name="q" type="text" onkeyup="suggest()" style="width:200px;" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
      <ol id="suggestions" style="list-style:none;margin-top:0;margin-left:61px;background:lightgray;width:200px;padding-left:0;overflow:hidden">
      </ol>

      <input type="radio" name="rank" <?php if (!isset($rank) || (isset($rank) && $rank=="")) echo "checked";?> value="">Lucene
      <input type="radio" name="rank" <?php if ($_GET['rank'] == "pageRankFile desc" || (isset($rank) && $rank=="pageRankFile desc")) echo "checked";?> value="pageRankFile desc">pageRankFile

      <input type="submit"/>
    </form>

    <?php
if ($useSpellCorrect && $corrected != $_GET['q']) {
    ?>
    <div>Did you mean:
        <a id="corrected" href="javascript: void(0)" onclick="correctSearch()">
            <?php echo $corrected; ?>
        </a>
    </div>
    <?php
}
?>

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
