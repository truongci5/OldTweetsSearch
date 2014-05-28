<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Twitter Search</title>
        <link href="bootstrap/css/bootstrap.css" rel="stylesheet">
        <link href="bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
        <script src="bootstrap/js/jquery.js"></script>
        <script src="bootstrap/js/bootstrap.js"></script>
    </head>
    <body>
        <div class="container">
            <div class="page-header">
                <h2>Twitter Search</h2>
            </div>
            <form method="GET" action="./">
                <fieldset>
                    <input type="text" name="query1" placeholder="Keyword" /> 
                    <input type="text" name="query2" placeholder="Keyword" /> <br>
                    <input type="text" name="query3" placeholder="Keyword" /> 
                    <input type="text" name="query4" placeholder="Keyword" /> <br>
                    Language :
                    <select name="lang">
                        <option value='en'>en</option>
                        <option value='fr'>fr</option>
                        <option value='all'>all</option>
                    </select> 
                    Since : 
                    <select name="since">
                        <?php 
                        for ($i = 0 ; $i < 7 ; $i++ ) {
                            echo "<option value='".date('Y-m-d',time()-$i*86400)."'>".date('Y-m-d',time()-$i*86400)."</option>";
                        }
                        ?>
                    </select>
                    <br>
                    <button type="submit" class="btn btn-small btn-primary">Submit</button>
                </fieldset>
            </form>
            <hr>
            <div>
                <?php
                    $q = array();
                    if (!empty($_GET['query1'])) $q[] = '"' . $_GET['query1'] . '"';
                    if (!empty($_GET['query2'])) $q[] = '"' . $_GET['query2'] . '"';
                    if (!empty($_GET['query3'])) $q[] = '"' . $_GET['query3'] . '"';
                    if (!empty($_GET['query4'])) $q[] = '"' . $_GET['query4'] . '"';

                    if (count($q)) {
                        $q = implode(" OR ", $q);

                        $lang = $_GET['lang'];
                        if ($lang == 'all') $lang = false;
                        $since = strtotime($_GET['since']);

                        echo "Query : <em>" . $q . "</em> Since : <em>" . $_GET['since'] . "</em> ";

                        include_once("../TwitterSearch.class.php");

                        $searcher = new TwitterSearch(
                            "YOUR APP KEY", 
                            "YOUR APP SECRET", 
                            "A VALID TOKEN ACCESS", 
                            "A VALID TOKEN SECRET ACCESS"
                        );

                        $tweets = $searcher->search($q, $lang, $since);

                        $path = "csv/" . str_replace("\"","",str_replace(" ", "_", $q)) . ".csv";

                        echo " <a class='btn btn-small btn-success' href='".$path."'> Export CSV </a> <br><br>";

                        $file = fopen($path,"w");

                        echo "<table class='table table-condensed table-bordered'>";
                        foreach ($tweets as $t) {
                            fputcsv($file, $t);
                            echo "<tr>"."<td>".$t['id']."</td>"."<td>@".$t['user_screen_name']."</td>"."<td>".$t['text']."</td>"."<td>".date('Y-m-d H:i:s',$t['created_at'])."</td>"."<td>".$t['retweet_count']." RT</td>"."</tr>";   
                        }
                        echo "</table>";
                        fclose($file);
                    }
                ?>
            </div>  
        </div>
    </body>
</html>
