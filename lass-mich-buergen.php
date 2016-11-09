<?php
#Beginne Session
session_start();
$post=$_POST;
date_default_timezone_set('Europe/Berlin');

$today=date(YmdHi); //$today="201611081200";

//$today="201511081200";


include("cfg.php");
include("static.php");
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

#Pruefe auf Blacklist
$db = mysql_connect($dbsrv,$dbuser,$dbpasswd);
if (!$db) {
  die("Datebank verbindung schlug fehl: ". mysql_error());
} else {
    mysql_select_db($dbname);
    $query = mysql_query("SELECT id_blacklist FROM wi_blacklist WHERE id_forum = '$user_id'");
    while ($erg =@ mysql_fetch_array($query)) {
      $blacklist = $erg["id_blacklist"];
    }

    $query = mysql_query("SELECT wi_wichtel.wichtel_id AS wichtel FROM wi_geschenk LEFT JOIN wi_wichtel ON (wi_geschenk.wichtel_id=wi_wichtel.wichtel_id) WHERE wi_wichtel.forum_id = '$user_id'");
    while ($erg =@ mysql_fetch_array($query)) {
      $wunsch = $erg["wichtel"];
    }
    mysql_close();
}


#ueberpruefe Rechte

  if ( !$user->data['is_registered'] ) {
   header("Location: was-ist-denn-hier-los.php?Grund=nicht_eingeloggt");
  }
  elseif ( $user_posts < $user_min_posts ) {
    header("Location: was-ist-denn-hier-los.php?Grund=zu_wenig_posts");
  }
  elseif ( $user_posts < $buerge_min_posts ) {
    header("Location: was-ist-denn-hier-los.php?Grund=zu_wenig_buerge_posts");
  }
 elseif ( ($today < $eintragen_start) || ($today > $eintragen_ende) ) {
   header("Location: was-ist-denn-hier-los.php?Grund=zeit_eintragen");
 }
 elseif ( ($blacklist != NULL) ) {
   header("Location: was-ist-denn-hier-los.php?Grund=blacklist");
 }

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
<body>
  <article class="container">
    <header class="head">
      <a href="./index.php"><img src="./img/nostern.gif" border="0" alt=""></a>
    </header>
    <?php  include("nav.php");?>
    <section class="main">

<?php
#Ziehe Variablen aus HTTP_VARS
if(isset($post['daten'])) $_SESSION["daten"] = $post['daten'];

if(isset($post['select'])) eintrag();
elseif(isset($post['senden'])) senden();
else { eintrag();}

function eintrag()
{
    global $user;
    include('lanq.php');

    #Infotext anzeigen
    echo "<p><b>Hallo ".$user->data['username']."!</b></p>";
    echo $buergen_hinweis;

    #Datenformular anzeigen
    echo <<<EINTRAG
    <form action="$PHP_SELF" method="post">
            <p>Name desjenigen f&uuml;r den Du b&uuml;rgen m&ouml;chtest: <input type="text" name="daten[]" size="50" maxlength="50" VALUE="$wichtel_nick"><br><br>
            <input type="submit" class="btn" name="senden" value="eintragen"> <input class="btn" type="reset" value=" l&ouml;schen "></p>
    </form>
EINTRAG;

} //function eintrag()


function senden()
{
  $daten = $_SESSION["daten"];
  global $user;
  include('lanq.php');

  #Eingabedaten aus Array ziehen
  $wichtel_nick = $daten[0];

  #Daten aus Datenbank abrufen
  $wichtel_id = $daten["user_id"];//0;
  include("cfg.php");
  $db = mysql_connect($dbsrv,$dbuser,$dbpasswd);
  if (!$db) {
    die("Datebank verbindung schlug fehl: ". mysql_error());
  } else {
    mysql_select_db($dbname);

    $query = sprintf("SELECT user_id, user_email FROM phpbb_users WHERE username ='%s'",
      mysql_real_escape_string($wichtel_nick));

    $result = mysql_query($query);

    while ($erg =@ mysql_fetch_array($result))
    {
      $wichtel_id = $erg["user_id"];
      $wichtel_mail = $erg["user_email"];
    }
    mysql_close();
  }
  #ueberpruefe User-ID ob User angemeldet
  if ($wichtel_id == NULL) {
    echo "Der Nick <b>".$wichtel_nick."</b> konnte im Forum nicht gefunden werden!<br><br>";
    echo "Klicke  <a href=\"javascript:history.back()\">hier</a>, um zum Formular zur&uuml;ckzukehren und die Fehler zu beheben.";
  } //if ($wichtel_id == NULL)

  #Daten speichern
  else {
    #Daten in DB-Schreiben
    $buerge_id = $user->data['user_id'];
    $buerge_nick = $user->data['username'];
    include("cfg.php");
    $db = mysql_connect($dbsrv,$dbuser,$dbpasswd);
    if (!$db) {
      die("Datebank verbindung schlug fehl: ". mysql_error());
    } else {
      mysql_select_db($dbname);
      $query = "INSERT INTO wi_buerge (buerge_forum_id, buerge_forum_nick, wichtel_id, wichtel_nick) VALUES ('$buerge_id', '$buerge_nick', '$wichtel_id', '$wichtel_nick')";
      mysql_query($query);
      mysql_close();
    }

    #User-Mail senden
    $mailto = $wichtel_mail.",".$user->data['user_email'];
    $subject = "Buerge bestaetigt";
    $header = "From: Weihnachtswichtel <kri_zilla@yahoo.de>";
    $buergen_mail = str_replace ("_BURGE_", $buerge_nick, $buergen_mail);
    $buergen_mail = str_replace ("_WICHT_", $wichtel_nick, $buergen_mail);
    mail($mailto,$subject,$buergen_mail,$header);

    #Infotext anzeigen
    echo "<p><b>Hallo ".$user->data['username']."!</b></p>";
    echo $buergen_ende;

  } //else
} //function senden()

?>

<p><a href="index.php">Zur&uuml;ck zur Startseite</a></p>
</section>
</article>
</body>
</html>
