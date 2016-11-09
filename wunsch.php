<?php
#Beginne Session
session_start();
$post=$_POST;
$get = $_GET;
date_default_timezone_set('Europe/Berlin');
$today=date(YmdHi); //$today="201611081200";

//$today="201511081200";
require_once("cfg.php");
require_once("static.php");
// Benoetigte Dateien und Variablen von phpBB3
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../forum/';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require($phpbb_root_path . 'common.' . $phpEx);

// Session auslesen und Benutzer-Informationen laden
$user->session_begin();  // Session auslesen
$auth->acl($user->data); // Benutzer-Informationen laden
$user->setup();


#Daten aus Datenbank abrufen
$user_id = $user->data['user_id'];
$user_posts = $user->data['user_posts'];

$un=$user->data['username'];
if ($un=="Anonymous") $user_id=0;
$geschenk_id = 0;
$geschenk_id = $get['geschenk_id'];


$db = mysql_connect($dbsrv,$dbuser,$dbpasswd);
if (!$db) {
  die("Datebank verbindung schlug fehl: ". mysql_error());
} else {
  mysql_select_db($dbname);

  $query = mysql_query("SELECT id_blacklist FROM wi_blacklist WHERE id_forum = '$user_id'");
  while ($erg =@ mysql_fetch_array($query)) {
     $blacklist = $erg["id_blacklist"];
   }

  if ( !$user->data['is_registered'] ) { header("Location: was-ist-denn-hier-los.php?Grund=nicht_eingeloggt"); }
  elseif ( ($user_posts < $user_min_posts) && ($buerge == NULL) ) { header("Location: was-ist-denn-hier-los.php?Grund=zu_wenig_posts"); }
  elseif ( (($today < $anfragen_start)) || (($today > $anfragen_ende)) ) { header("Location: was-ist-denn-hier-los.php?Grund=zeit_anfragen"); }
  elseif ( ($blacklist != NULL) ) { header("Location: was-ist-denn-hier-los.php?Grund=blacklist"); }
  elseif ($geschenk_id > 0) {
    //fetch all the data
    $query = "SELECT wichtel_id, beschreibung, level, art, status FROM wi_geschenk WHERE geschenk_id = '$geschenk_id'";

    $result = mysql_query($query);
    if (!$result) {
        $message  = 'Ungültige Abfrage: ' . mysql_error() . "\n";
        $message .= 'Gesamte Abfrage: ' . $query;
        die($message);
    }

    while ($erg =@ mysql_fetch_array($result)) {
      $wichtel_id = $erg["wichtel_id"];
      $beschreibung = $erg["beschreibung"];
      $level = $erg["level"];
      $art = $erg["art"];
      $status = $erg["status"];
    } //while ($erg =@ mysql_fetch_array($query))

    $query = "SELECT notizen FROM wi_wichtel WHERE wichtel_id = '$wichtel_id'";

    while ($erg =@ mysql_fetch_array($query)) {
      $notizen = $erg["notizen"];
    } //while ($erg =@ mysql_fetch_array($query))
    $beschreibung = str_replace("\r\n","<br>",$beschreibung);
    $notizen = str_replace("\r\n","<br>",$notizen);
  }
  mysql_close();
}
?>

<html>
<head>
<meta name="author" content="Cpt.Kaylee">
<meta name="debug" content="toxic_garden">
<meta name="organization" content="N&auml;hkromanten">
<meta charset="UTF-8">
<title>Das Nähkromanten Weihnachtswichteln</title>
<base target=_self>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<link href="./wicht.css" rel="stylesheet" type="text/css">
</head>

<body>
  <article class="container">
    <header class="head">
    <a href="./index.php"><img src="./img/nostern.gif" border="0" alt=""></a>

    <section class="main">
      <p><a href="index.php">Zurück zur Startseite</a></p>
      <h2>Wunschdetails</h2>
      <div class="infobox">
        <?php
        #Geschenkdaten anzeigen
        echo <<<EINTRAG
        <div>
          <p>
            <h3>Beschreibung:</h3>
            $beschreibung
            </p>
          <p>
          <b>Schwierigkeit: </b>
            $level
          </p>
          <p>
            <b>Kategorie:</b>
            $art
          </p>
          <p>
            <h3>Notizen:</h3>
            $notizen
          </p>
          </div>
EINTRAG;

      if($status == 0) {

        echo <<<SUBMIT
          <form action="./wunsch_aussuchen.php" method="post" name="Eintrag">
          <div>
          <input type="hidden" name="wunsch_id" value="$geschenk_id" />
          <input type="submit" name="verifize" value="Diesen Wunsch jetzt erfüllen">
          </div>
          </form>
SUBMIT;
    }
       ?>

      </div>

    </section>
    </article>
</body>
</html>
