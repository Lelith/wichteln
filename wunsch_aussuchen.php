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
      <h2>Wunsch auswählen</h2>
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
$geschenk_id = $post['wunsch_id'];

senden($geschenk_id);

function senden($geschenk_id) {
  global $user;
  include("lanq.php");
  include("cfg.php");

  #Infoseite anzeigen
  echo "<p><b>Hallo ".$user->data['username']."!</b></p>";
  echo $anfragen_ende;

  #Geschenk- und Wichteldaten abrufen
  $geschenk_id;
  $forum_id = $user->data['user_id'];

  $db = mysql_connect($dbsrv,$dbuser,$dbpasswd);
  if (!$db) {
    die("Datebank verbindung schlug fehl: ". mysql_error());
  } else {
    mysql_select_db($dbname);

    $query ="SELECT wichtel_id, beschreibung, level, art, status FROM wi_geschenk WHERE geschenk_id = '$geschenk_id'";
    $result = mysql_query($query);

    while ($erg =@ mysql_fetch_array($result)) {
      $wichtel_id = $erg["wichtel_id"];
      $beschreibung = $erg["beschreibung"];
      $level = $erg["level"];
      $art = $erg["art"];
      $status = $erg["status"];
    } //while ($erg =@ mysql_fetch_array($query))

      $query = mysql_query("SELECT nick, name, adresse, plz, ort, land, notizen, adrzusatz FROM wi_wichtel WHERE wichtel_id = '$wichtel_id'");

      while ($erg =@ mysql_fetch_array($query)) {
        $nick = $erg["nick"];
        $name = $erg["name"];
        $adresse = $erg["adresse"];
        $adrzusatz = $erg["adrzusatz"];
        $plz = $erg["plz"];
        $ort = $erg["ort"];
        $land = $erg["land"];
        $notizen = $erg["notizen"];
      } //while ($erg =@ mysql_fetch_array($query))

      #ueberpruefe ob Wichtel vorhanden, sonst nachtragen
      $query = mysql_query("SELECT wichtel_id, email FROM wi_wichtel WHERE forum_id = '$forum_id'");

      while ($erg =@ mysql_fetch_array($query)) {
        $user_wichtel_id = $erg["wichtel_id"];
        $usermail = $erg["email"];
      } //while ($erg =@ mysql_fetch_array($query))

      if (!$user_wichtel_id) {
        $usermail = $user->data['user_email'];
        $usernick = $user->data['username'];
        $query = mysql_query("INSERT INTO wi_wichtel ( forum_id, nick, email) VALUES ('$forum_id', '$usernick', '$usermail')");

        $query = mysql_query("SELECT wichtel_id FROM wi_wichtel WHERE forum_id = '$forum_id'");
        while ($erg =@ mysql_fetch_array($query)) {
          $user_wichtel_id = $erg["wichtel_id"];
        }
      }// if (!$user_wichtel_id)

      #ueberprruefe ob Geschenk noch frei und kein eigenes ist
      if ( ($status==0 || $status==4) && ($wichtel_id != $user_wichtel_id) ) {
        #Geschenk-Status anpassen
        $query = mysql_query("UPDATE wi_geschenk SET status = 2 WHERE wichtel_id = '$wichtel_id' AND status != 5;");

        $query = mysql_query("UPDATE wi_geschenk SET status = 1 WHERE geschenk_id = '$geschenk_id'");

        $query = mysql_query("UPDATE wi_geschenk SET partner_id = '$user_wichtel_id' WHERE geschenk_id = '$geschenk_id'");

        mysql_close();

        #Geschenk- und Wichteldaten anzeigen
        echo <<<EINTRAG
          <div>
            <h2>Geschenk-ID: $geschenk_id </h2>
            &nbsp;(Bitte bewahre die Geschenk-ID gut auf, du musst sie sp&auml;ter auf ds Paket schreiben!)
            <p><b>Nick:</b> $nick</p>
            <p><b>Name:</b> $name</p>
            <p><b>Adresse:</b> $adrzusatz $adresse, $plz $ort, $land</p>
            <p><b>Beschreibung:</b><br>$beschreibung</p>
            <p><b>Schwierigkeit:</b> $level</p>
            <p><b>Kategorie:</b> $art</p>
            <p><b>Notizen:</b><br>$notizen</p>
          </div>
EINTRAG;

          #User-Mail senden
          $wunschinfo="\n\nGeschenk-ID: $geschenk_id\nNick: $nick\nName: $name\nAdresse: $adrzusatz $adresse, $plz $ort, $land\n\nBeschreibung:\n$beschreibung\nSchwierigkeit: $level\nKategorie: $art\n\nNotizen:\n$notizen\n\n Du kannst dir die Wunschinformationen auch <a href='https://naehkromanten.net/wichteln/wunsch.php?geschenk_id=$geschenk_id'>hier  noch einmal ansehen und die Daten über deinen <a href='https://naehkromanten.net/wichteln/wichtel.php?wichtel_id=$wichtel_id'>Wichtel hier.</a>";
          $mailto = $usermail;
          $subject = "Hallo Wichtel";
          $header = "From: Weihnachtshexe <dieverschleierte@web.de>";
          $anfragen_mail = str_replace ("_USERNAME_", $user->data['username'], $anfragen_mail);
          $anfragen_mail = str_replace ("_WUNSCHINFO_", $wunschinfo, $anfragen_mail);
          mail($mailto,$subject,$anfragen_mail,$header);
          echo "<p>Diese Informationen wurden gerade auch per Mail an die Adresse <i>".$usermail."</i> verschickt.</p>";
        } //( (!$status) && ($wichtel_id != $user_wichtel_id) )
        else {
          #Infotext anzeigen
          echo $anfragen_geschenk_weg;
        } //else
      }
} //function senden()
?>
</div>
<p><a href="index.php">Zurück zur Startseite</a></p>
</section>
</article>
</body>
</html>
