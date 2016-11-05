<?php
#Beginne Session
session_start();
$post=$_POST;
date_default_timezone_set('Europe/Berlin');

$today=date(YmdHi); //$today="201611081200";

//$today="201511081200";

include('cfg.php');
include('static.php');
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

$db = mysql_connect($dbsrv,$dbuser,$dbpasswd);
if (!$db) {
  die("Datebank verbindung schlug fehl: ". mysql_error());
} else {
  mysql_select_db($dbname);
  $query = mysql_query("SELECT buerge_id FROM wi_buerge WHERE wichtel_id = '$user_id'");

  while ($erg =@ mysql_fetch_array($query)) {
    $buerge = $erg["buerge_id"];
  }
  $query = mysql_query("SELECT id_blacklist FROM wi_blacklist WHERE id_forum = '$user_id'");
  while ($erg =@ mysql_fetch_array($query)) {
     $blacklist = $erg["id_blacklist"];
   }

  $query = "SELECT wi_geschenk.geschenk_id FROM wi_geschenk LEFT JOIN wi_wichtel ON (wi_geschenk.partner_id = wi_wichtel.wichtel_id) WHERE wi_wichtel.forum_id = '$user_id' AND wi_geschenk.gesendet IS NULL";

  $result = mysql_query($query);
  if (!$result) {
    $message  = 'Ungültige Abfrage: ' . mysql_error() . "\n";
    $message .= 'Gesamte Abfrage: ' . $query;
    die($message);
  } else {
    while ($row =@ mysql_fetch_array($result)) {
      $geschenk = $row["geschenk_id"];
    }
  }
  mysql_close();
}

#ueberpruefe Rechte
if ( !$user->data['is_registered'] ) { header("Location: was-ist-denn-hier-los.php?Grund=nicht_eingeloggt"); }
elseif ( ($user_posts < $user_min_posts) && ($buerge == NULL) ) { header("Location: was-ist-denn-hier-los.php?Grund=zu_wenig_posts"); }
elseif ( (($today < $anfragen_start)) || (($today > $anfragen_ende)) ) { header("Location: was-ist-denn-hier-los.php?Grund=zeit_anfragen"); }
elseif ( ($blacklist != NULL) ) { header("Location: was-ist-denn-hier-los.php?Grund=blacklist"); }
elseif ( ($geschenk != NULL) ) { header("Location: was-ist-denn-hier-los.php?Grund=geschenksperre"); }
?>

<html>
<head>
  <meta name="author" content="Cpt.Kaylee">
  <meta name="debug" content="toxic_garden">
  <meta name="organization" content="N&auml;hkromanten">
  <meta charset="UTF-8">
  <title>Das N&auml;hkromanten Weihnachtswichteln</title>
  <base target=_self>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
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
      <h2>Ich m&ouml;chte jemanden beschenken!</h2>

<?php
#Ziehe Variablen aus HTTP_VARS
if(isset($post['datenanf'])) $_SESSION["datenanf"] = $post['datenanf'];

if ($geschenksperre==1) {infosperre();}
elseif(isset($post['suche'])) { $suchstat = 0; suche($suchstat); }
elseif(isset($post['suchedb'])) { $suchstat = 1; suche($suchstat); }
elseif(isset($post['sucherand'])) { $suchstat = 2; suche($suchstat); }
elseif(isset($post['detail'])) detail();
elseif(isset($post['senden'])) senden();
elseif(isset($post['verifize'])) verifize();
else info();

#Funktionen ausfuehren
function info ()
{
  global $user;
  include('lanq.php');

  #Infoseite anzeigen
  echo "<p><b>Hallo ".$user->data['username']."!</b></p>";
  echo $anfragen_info;

  #Bestaetigungsformular anzeigen
  echo <<<FORMULAR
    <form action="$PHP_SELF" method="post">
      <p>
      <input id="aussuchen" type="checkbox" name="suche" value="select"><label for="aussuchen">Ich habe alles gelesen und bin einverstanden</label>
      <input type="submit" name="suchen" value="OK">
      </p>
  </form>
FORMULAR;

} //function info()

function suche($suchstat) {
  $datenanf = $_SESSION["datenanf"];
  $search = $_SESSION["datenanf"];
  global $user;
  include('lanq.php');

  #Infotext anzeigen
  echo "<p><b>Hallo ".$user->data['username']."!</b></p>";
  echo $anfragen_hinweis;

  #Suchformular anzeigen
  echo <<<EINTRAG
    <form action="$PHP_SELF" method="post" name="Suche">
    <fieldset>
      <legend>Suche</legend>
      <ul class="flex-outer">
      <li>
        <label>Stichwort:</label>
        <input type="text" name="datenanf[0]" value="$datenanf[0]" size="35" maxlength="50"> </td>
      </li>
      <li>
        <label>Schwierigkeitsgrad: </label>
        <select name="datenanf[1]" size="1">
             <option id="1">Egal</option>
             <option id="2">Kleinigkeit</option>
             <option id="3">Mittel</option>
             <option id="4">Anspruchsvoll</option>
         </select>
        </li>
        <li>
          <label>Kategorie: </label>
          <select name="datenanf[2]" size="1">
              <option id="1">Egal</option>
              <option id="2">Kleidung</option>
              <option id="3">Tasche/Maeppchen</option>
              <option id="4">Strick/Haekelsachen</option>
              <option id="5">Haarschmuck/Muetze</option>
              <option id="6">Schmuck</option>
              <option id="7">Accessoires</option>
              <option id="8">Schachtel/Box/Aufbewahrung</option>
              <option id="9">Wohnungsdeko/Plueschtier/Kissen</option>
              <option id="10">Kladde/Papierwaren/Kalender</option>
              <option id="11">Kuechenaccessoires</option>
              <option id="12">Kosmetik/Badezimmer</option>
            </select>
          </li>
          <li>
          <input id="deutschland" type="checkbox" name="datenanf[3]" value="deutschland"><label for="deutschland">Nur Wünsche die nach Deutschland verschickt werden anzeigen</label>
          </li>
          </ul>

          <input type="submit" name="suchedb" value="Suchen">
          </form>
          </fieldset>
          <p> ODER </p>
          <form action="$PHP_SELF" method="post" name="Suche2"><input type="submit" name="sucherand" value="Alle Wünsche anzeigen"></form>
EINTRAG;

  #Suche durchfuehren und Ergebnisse und anzeigen
  if ($suchstat) {
    #Hole ID's aus Datenbank
    echo "<h2>Ergebnisse</h2>";
    $cu_forum_id = $user->data['user_id'];
    $cu_wichtel_id = 0;
    $text = $search[0];
    $level =  $search[1];
    $art =  $search[2];
    $deutschland = $search[3];

    include("cfg.php");
    $db = mysql_connect($dbsrv,$dbuser,$dbpasswd);
    if (!$db) {
      die("Datebank verbindung schlug fehl: ". mysql_error());
    } else {
      mysql_select_db($dbname);

      #hole wichtel id von current user, wenn user bereits wichtel
      $result = mysql_query("SELECT wichtel_id FROM wi_wichtel WHERE forum_id = '$cu_forum_id'");

      if(!$result){
        $message  = 'Ungültige Abfrage: ' . mysql_error() . "\n";
        $message .= 'Gesamte Abfrage: ' . $query;
        die($message);
      }

      while ($row =@ mysql_fetch_array($result)) {
        $cu_wichtel_id = $row["wichtel_id"];
      }

      #hole geschenk ids ausser current user geschenke
      if($deutschland == 'deutschland') {
        $sql = "SELECT geschenk.geschenk_id, wichtel.wichtel_id FROM wi_geschenk as geschenk, wi_wichtel as wichtel WHERE wichtel.wichtel_id=geschenk.wichtel_id AND status=0 AND wichtel.wichtel_id!='$cu_wichtel_id' AND (wichtel.land ='deutschland' OR wichtel.land = 'germany')";
      } else {
        $sql = "SELECT geschenk_id FROM wi_geschenk WHERE status=0 AND wichtel_id!='$cu_wichtel_id'";
      }

      #if ($text) { $sql = $sql." AND MATCH (beschreibung) AGAINST ('$text' IN BOOLEAN MODE)"; }
      if ($suchstat == 1) {
        if ($text) {
          $sql = $sql." AND beschreibung LIKE CONCAT('%' , '$text', '%')";
        }
        if ($level != "Egal") {
          $sql = $sql." AND level='$level'";
        }
        if ($art != "Egal") {
          $sql = $sql." AND art='$art'";
        }

      } //if ($suchstat == 1)

      $wunschquery = mysql_query($sql);

      if(!$wunschquery){
        $message  = 'Ungültige Abfrage: ' . mysql_error() . "\n";
        $message .= 'Gesamte Abfrage: ' . $query;
        die($message);
      }

      while ($row =@ mysql_fetch_array($wunschquery)) {
        $wunschliste[] = $row["geschenk_id"];
      }



      if (count($wunschliste) > 0){
        foreach($wunschliste as $i => $geschenk) {
          $query = "SELECT beschreibung, level, art FROM wi_geschenk WHERE geschenk_id = '$geschenk'";
          $result = mysql_query($query);

          if(!$result){
            $message  = 'Ungültige Abfrage: ' . mysql_error() . "\n";
            $message .= 'Gesamte Abfrage: ' . $query;
            die($message);
          }
          while ($erg =@ mysql_fetch_array($result)) {
            $beschreibung = $erg["beschreibung"];
            $level = $erg["level"];
            $art = $erg["art"];
            $beschreibung2 = substr($beschreibung, 0, 200);
            if (strlen($beschreibung)>200) $beschreibung2=$beschreibung2."...";
            echo <<<AUSGABE
              <div class="wunschbox">
                <p class="description">$beschreibung2</p>
                <p><i>Schwierigkeitsgrad: $level</i></p>
                <p><i>Kategorie: $art</i></p>
                <p><a href="./wunsch.php?geschenk_id=$geschenk" target="_blank">alle details anzeigen</a></p>
              </div>
AUSGABE;
          } //while ($erg =@ mysql_fetch_array($query))
        } //foreach
      } else {
        echo "<div>Keine passenden W&uuml;nsche gefunden. Versuch es bitte mit weniger Einschr&auml;nkungen erneut.</div>";
      } //else
      mysql_close();
    }
  }
    baumstatus();
} //function suche($suchstat)

function baumstatus(){
  include ('cfg.php');
  #Baum berechnen und anzeigen
  $db = mysql_connect($dbsrv,$dbuser,$dbpasswd);
  if (!$db) {
    die("Datebank verbindung schlug fehl: ". mysql_error());
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
        $baumstatus = floor($baumstatus/10);
        switch ($baumstatus) {
        case 10:
                echo "<img src=\"./statusschleife/schleifeff1fed.gif\" width=\"600\" height=\"115\" border=\"0\" alt=\"Status\">";
                break;
        case 9:
                echo "<img src=\"./statusschleife/schleifefa90ae.gif\" width=\"600\" height=\"115\" border=\"0\" alt=\"Status\">";
                break;
        case 8:
                echo "<img src=\"./statusschleife/schleifeee85de.gif\" width=\"600\" height=\"115\" border=\"0\" alt=\"Status\">";
                break;
        case 7:
                echo "<img src=\"./statusschleife/schleifede7dfa.gif\" width=\"600\" height=\"115\" border=\"0\" alt=\"Status\">";
                break;
        case 6:
                echo "<img src=\"./statusschleife/schleifedb66f3.gif\" width=\"600\" height=\"115\" border=\"0\" alt=\"Status\">";
                break;
        case 5:
                echo "<img src=\"./statusschleife/schleifecf5f6c.gif\" width=\"600\" height=\"115\" border=\"0\" alt=\"Status\">";
                break;
        case 4:
                echo "<img src=\"./statusschleife/schleifece44bd.gif\" width=\"600\" height=\"115\" border=\"0\" alt=\"Status\">";
                break;
        case 3:
                echo "<img src=\"./statusschleife/schleifeca3210.gif\" width=\"600\" height=\"115\" border=\"0\" alt=\"Status\">";
                break;
        case 2:
                echo "<img src=\"./statusschleife/schleifebf25fc.gif\" width=\"600\" height=\"115\" border=\"0\" alt=\"Status\">";
                break;
        case 1:
                echo "<img src=\"./statusschleife/schleifeae172d.gif\" width=\"600\" height=\"115\" border=\"0\" alt=\"Status\">";
                break;
        default:
                echo "<img src=\"./statusschleife/schleifeaa0f34.gif\" width=\"600\" height=\"115\" border=\"0\" alt=\"Status\">";
        } //switch ($baumstatus)
}

?>

<p><a href="index.php">Zur&uuml;ck zur Startseite</a></p>
</div>
</body>
</html>
