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
if ($un=="Anonymous")
  $user_id=0;

$db = mysql_connect($dbsrv,$dbuser,$dbpasswd);
if (!$db) {
  die("Datebank verbindung schlug fehl: ". mysql_error());
} else {
  $query = mysql_query("SELECT buerge_id FROM wi_buerge WHERE wichtel_id = '$user_id'");

  while ($erg =@ mysql_fetch_array($query)) {
    $buerge = $erg["buerge_id"];
  }
  $query = mysql_query("SELECT blacklist_id FROM wi_blacklist WHERE user_id = '$user_id'");
  while ($erg =@ mysql_fetch_array($query)) {
     $blacklist = $erg["blacklist_id"];
   }
  $query = mysql_query("SELECT wi_geschenk.geschenk_id FROM wi_geschenk LEFT JOIN wi_wichtel ON (wi_geschenk.partner_id = wi_wichtel.wichtel_id) WHERE wi_wichtel.forum_id =  '$user_id' AND wi_geschenk.gesendet='0'");

  while ($erg =@ mysql_fetch_array($query)) {
    $geschenk = $erg["geschenk_id"];
  }
  mysql_close();
}

#ueberpruefe Rechte
if ( !$user->data['is_registered'] ) { header("Location: was-ist-denn-hier-los.php?Grund=nicht_eingeloggt"); }
elseif ( ($user_posts < $user_min_posts) && ($buerge == NULL) ) { header("Location: was-ist-denn-hier-los.php?Grund=zu_wenig_posts"); }
elseif ( (($today < $anfragen_start)) || (($today > $anfragen_ende)) ) { header("Location: was-ist-denn-hier-los.php?Grund=zeit_anfragen"); }
elseif ( ($blacklist != NULL) ) { header("Location: was-ist-denn-hier-los.php?Grund=blacklist"); }
elseif ( ($geschenk != NULL) ) { $geschenksperre=1; }

baumstatus();
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
          <p><input type="checkbox" name="suche" value="select">&nbsp;&nbsp;Ich habe alles gelesen und bin einverstanden&nbsp;&nbsp;<input type="submit" name="natronisttoll" value="OK"></p>
  </form>
FORMULAR;

} //function info()

function infosperre () {
  global $user;
  include('lanq.php');

  #Infoseite anzeigen
  echo "<p><b>Hallo ".$user->data['username']."!</b><br><br>Du hast bereits ein Geschenk ausgew&auml;hlt. Bevor du ein weiteres aussuchen kannst musst du das andere erst verschickt und das auch best&auml;tigt haben. Hier siehst du so lange die aktuelle Quote der Geschenkvergabe:</p><br>";

} //function infosperre()


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
          </ul>
          <input type="submit" name="suchedb" value="Suchen"></form>

          <form action="$PHP_SELF" method="post" name="Suche2"><input type="submit" name="sucherand" value="Auf gut Gl&uuml;ck!"></form>
EINTRAG;

  #Suche durchfuehren und Ergebnisse und anzeigen
  if ($suchstat) {
    #Hole ID's aus Datenbank
    echo "<h2>Ergebnisse</h2>";
    echo "<table width=\"100%\" cellpadding=\"5\" border=\"0\">";
    $wichtel_id = $user->data['user_id'];
    $forum_id = $user->data['user_id'];
    $text = $search[0];
    $level =  $search[1];
    $art =  $search[2];

    include("cfg.php");
    $db = mysql_connect($dbsrv,$dbuser,$dbpasswd);
    if (!$db) {
      die("Datebank verbindung schlug fehl: ". mysql_error());
    } else {
      mysql_select_db($dbname);

      $query = mysql_query("SELECT wichtel_id FROM wi_wichtel WHERE forum_id = '$forum_id'");
      while ($erg =@ mysql_fetch_array($query)) { $user_wichtel_id = $erg["wichtel_id"]; }
      $sql = "SELECT geschenk_id FROM wi_geschenk WHERE status=0 AND wichtel_id!='$user_wichtel_id'";

      #if ($text) { $sql = $sql." AND MATCH (beschreibung) AGAINST ('$text' IN BOOLEAN MODE)"; }
      if ($suchstat == 1) {
        if ($text) { $sql = $sql." AND beschreibung LIKE CONCAT('%' , '$text', '%')"; }
        if ($level != "Egal") { $sql = $sql." AND level='$level'"; }
        if ($art != "Egal") { $sql = $sql." AND art='$art'"; }
      } //if ($suchstat == 1)

      $query = mysql_query($sql);

      while ($erg =@ mysql_fetch_array($query)) {
        $liste[] = $erg["geschenk_id"];
      }
      if (count($liste) > 0){
        foreach($liste as $geschenk) {
          $geschenk_id = $liste[$liste2[$i]];

          $query = mysql_query("SELECT beschreibung, level, art FROM wi_geschenk WHERE geschenk_id = '$geschenk_id'");

          while ($erg =@ mysql_fetch_array($query)) {
            $beschreibung = $erg["beschreibung"];
            $level = $erg["level"];
            $art = $erg["art"];
            $beschreibung2 = substr($beschreibung, 0, 200);
            if (strlen($beschreibung)>200) $beschreibung2=$beschreibung2."...";
            echo <<<AUSGABE
              <div>
                <p>$beschreibung2</p>
                <p><i>Schwierigkeitsgrad: $level</i></p>
                <p><i>Kategorie: $art</i></p>
                <form action="$PHP_SELF" method="post" name="Detail$i">
                <input type="hidden" name="datenanf[3]" value="$geschenk_id">
                <input type="submit" name="detail" value="mehr Infos"></form>
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

function detail()
{
  include('lanq.php');
  include("cfg.php");
  $datenanf = $_SESSION["datenanf"];
  global $user;

  #Infoseite anzeigen
  echo "<p><b>Hallo ".$user->data['username']."!</b></p>";
  echo $anfragen_detail;

  #Geschenkdaten abrufen
  $geschenk_id = $datenanf[3];
  $db = mysql_connect($dbsrv,$dbuser,$dbpasswd);
  if (!$db) {
    die("Datebank verbindung schlug fehl: ". mysql_error());
  } else {
    mysql_select_db($dbname);
    $query = mysql_query("SELECT wichtel_id, beschreibung, level, art FROM wi_geschenk WHERE geschenk_id = '$geschenk_id'");
    while ($erg =@ mysql_fetch_array($query)) {
      $wichtel_id = $erg["wichtel_id"];
      $beschreibung = $erg["beschreibung"];
      $level = $erg["level"];
      $art = $erg["art"];
    } //while ($erg =@ mysql_fetch_array($query))
    $query = mysql_query("SELECT notizen FROM wi_wichtel WHERE wichtel_id = '$wichtel_id'");
      while ($erg =@ mysql_fetch_array($query)) {
              $notizen = $erg["notizen"];
      } //while ($erg =@ mysql_fetch_array($query))
      mysql_close();
    }

    $beschreibung = str_replace("\r\n","<br>",$beschreibung);
    $notizen = str_replace("\r\n","<br>",$notizen);

    #Geschenkdaten anzeigen
    echo <<<EINTRAG
    <div>
      <p>
        <h3>Beschreibung:</h3>
        $beschreibung
        </p>
      <p>
      <h3>Schwierigkeit: </h3>
        $level
      </p>
      <p>
        <h3>Kategorie:</h3>
        $art
      </p>
      <p>
        <h3>Notizen:</h3>
        $notizen
      </p>
      </div>
      <form action="$PHP_SELF" method="post" name="Eintrag">
        <div>
        <input type="submit" name="verifize" value="Best&auml;tigen">
        <input type="submit" name="suche" value=" Zur&uuml;ck ">
        </div>
      </form>
EINTRAG;
} //detail()

function verifize()
{
  global $user;
  include('lanq.php');

  #Infoseite anzeigen
  echo "<p><b>Hallo ".$user->data['username']."!</b></p>";
  echo $anfragen_verifizieren;

  #Verifizierung einholen
  echo <<<EINTRAG
  <form action="$PHP_SELF" method="post">
  <input id="accept" type="checkbox" name="senden" value="select"><label for="accept">Ich habe alles gelesen und bin einverstanden</label>
  <input type="submit" name="verifize" value="OK">
  </form>
EINTRAG;
} //verifize()

function senden() {
  $datenanf = $_SESSION["datenanf"];
  global $user;
  include('lanq.php');
  include("cfg.php");

  #Infoseite anzeigen
  echo "<p><b>Hallo ".$user->data['username']."!</b></p>";
  echo $anfragen_ende;

  #Geschenk- und Wichteldaten abrufen
  $geschenk_id = $datenanf[3];
  $forum_id = $user->data['user_id'];

  $db = mysql_connect($dbsrv,$dbuser,$dbpasswd);
  if (!$db) {
    die("Datebank verbindung schlug fehl: ". mysql_error());
  } else {
    mysql_select_db($dbname);

    $query = mysql_query("SELECT wichtel_id, beschreibung, level, art, status FROM wi_geschenk WHERE geschenk_id = '$geschenk_id'");

    while ($erg =@ mysql_fetch_array($query)) {
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
        $query = mysql_query("INSERT INTO wi_wichtel (wichtel_id, forum_id, nick, email) VALUES ('$forum_id', '$forum_id', '$usernick', '$usermail')");
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
          $wunschinfo="\n\nGeschenk-ID: $geschenk_id\nNick: $nick\nName: $name\nAdresse: $adrzusatz $adresse, $plz $ort, $land\n\nBeschreibung:\n$beschreibung\nSchwierigkeit: $level\nKategorie: $art\n\nNotizen:\n$notizen\n\n";
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

<p><a href="index.php">Zur&uuml;ck zur Startseite</a></p>
</div>
</body>
</html>
