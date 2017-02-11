<html>
  <head>
    <title>Nähkromanten-Wichteln</title>
    <meta charset="UTF-8">
    <meta name="author" content="Systemhexe">
    <link href="./wicht.css" rel="stylesheet" type="text/css">

  </head>
  <body>
    <div class="container">
      <img src="./img/wichteln.png" border="0" alt="">
      <?php
      include("static.php");
      include("nav.php");
      ?>
      <div class="main">
        <p>
        Wir begr&uuml;&szlig;en euch auch dieses Jahr zum Weihnachtswunschwichteln!<br>
        Um herauszufinden, was das Wichteln ist, wie es abl&auml;uft und wie ihr mitmachen k&ouml;nnt, schaut bitte in die <a href="./und-so-gehts.php" target="_blank">Regeln</a>.<br>
        Um auf dem neusten Stand zu sein, schaut bitte auch hinein, wenn ihr bereits mitgemacht habt, danke!<br>
      </p>
      <p>
        Wenn ihr noch Fragen habt, stellt diese im Forum (<a target="_blank" href="https://www.naehkromanten.net/forum/viewtopic.php?f=21&t=69757">in diesem Thread</a>) oder wendet euch direkt an den <a target="_blank" href="https://www.naehkromanten.net/forum/memberlist.php?mode=viewprofile&u=10714">Weihnachtswichtel</a>.<br>
      </p>

      <?php
        include ('cfg.php');
        $db = mysql_connect($dbsrv,$dbuser,$dbpasswd);
        if (!$db) {
          die("Datebankverbindung schlug fehl: ". mysql_error());
        } else{
        mysql_select_db($dbname);

        $query = mysql_query("SELECT COUNT(*) FROM wi_geschenk");
        while ($erg =@ mysql_fetch_array($query)) {
          $werta = $erg["COUNT(*)"];
        }

        $query = mysql_query("SELECT COUNT(*) FROM wi_geschenk WHERE status!=0 AND status!=4");

        while ($erg =@ mysql_fetch_array($query)) {
          $wertb = $erg["COUNT(*)"];
        }
        mysql_close();
        }
        if ($werta == 0) { $baumstatus = 100; } else {
          $baumstatus = round(100 / $werta * $wertb);
        }
        print("<div class='progress-bar'> Wunschstatus<br>");
        print("<progress max='100' value='".$baumstatus."'></progress></div>");
      ?>
            </div>
    </div>
  </div>

  </body>
</html>
