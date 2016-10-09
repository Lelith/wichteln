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
#ueberpruefe Rechte

if ( !$user->data['is_registered'] ) { header("Location: was-ist-denn-hier-los.php?Grund=nicht_eingeloggt"); }
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

<script type="text/javascript">
function chkFormular () {
  var geschenk_id = $('#geschenk_id').val();
  var post_art = $('#post_art').val();
  var post_id = $('#post_id').val();

  if (geschenk_id == "") {
    alert("Du hast leider keine Geschenk-ID eingegeben!");
    $('#geschenk_id').focus();
    return false;
  }
  if (post_art == "") {
    alert("Du hast leider keinen Anbieter eingegeben!");
    $('#post_art').focus();
    return false;
  }
  if (post_id == "") {
    alert("Du hast leider keine Trackingnummer eingegeben!");
    $('#poist_id').focus();
    return false;
  }
}
</script>

</head>

<body>

  <article class="container">
    <header class="head">
    <a href="./index.php"><img src="./img/nostern.gif" border="0" alt=""></a>

    <section class="main">
      <h2>Mein Geschenk ist verschickt!</h2>

<?php
#Ziehe Variablen aus HTTP_VARS
if(isset($post['daten'])) $_SESSION["daten"] = $post['daten'];

if(isset($post['senden'])) senden();
else eintrag();

#Funktionen ausfuehren
function eintrag() {
  global $user;
  include("lanq.php");

  #Infotext anzeigen
  echo "<p><b>Hallo ".$user->data['username']."!</b></p>";
  echo $gesendet_hinweis;

  #Datenformular anzeigen
  echo <<<EINTRAG
    <form action="$PHP_SELF" method="post" onsubmit="return chkFormular()" name="Eintrag">
      <fieldset>
        <ul class="flex-outer">
          <li>
            <label for="geschenk_id">Geschenk-ID:</label>
            <input  id="geschenk_id" type="text" name="daten[]" size="45" maxlength="100" VALUE="$geschenk_id">
          </li>
          <li>
            <label for="post_art">Anbieter:</label>
            <input id="post_art" type="text" name="daten[]" size="45" maxlength="100" VALUE="$post_art">
          </li>
          <li>
            <label for="post_id">Trackingnummer:</label>
            <input type="text" name="daten[]" size="45" maxlength="100" VALUE="$post_id">
          </li>
        </ul>
        <div>
          <input type="submit" name="senden" value="abschicken">
          <input type="reset" value=" l&ouml;schen ">
        </div>
      </fieldset>
    </form>
EINTRAG;

} //function eintrag()

function senden()
{
  $daten = $_SESSION["daten"];
  global $user;
  include('lanq.php');
  include('cfg.php');

  #Eingabedaten aus Array ziehen
  $user_id = $user->data['user_id'];
  $geschenk_id = $daten[0];
  $post_art = $daten[1];
  $post_id = $daten[2];
  $gesendet = $today;


  #Daten aus Datenbank abrufen
  $db = mysql_connect($dbsrv,$dbuser,$dbpasswd);
  if (!$db) {
    die("Datebank verbindung schlug fehl: ". mysql_error());
  } else {
    $cu_forum_id = $user_id;

    $result = mysql_query("SELECT * FROM wi_geschenk WHERE geschenk_id = '$geschenk_id'");

    if (!$result) {
        $message  = 'Ungültige Abfrage: ' . mysql_error() . "\n";
        $message .= 'Gesamte Abfrage: ' . $query;
        die($message);
    }

    while ($erg =@ mysql_fetch_array($result)){
      $db_geschenk_id = $erg["geschenk_id"];
      $db_status = $erg["status"];
      $db_partner_id = $erg["partner_id"];
      $db_gesendet = $erg["gesendet"];
    }
    mysql_close();
  }

  #ueberpruefe Daten auf Richtigkeit
  if ( ($db_geschenk_id == NULL) || ($db_status != 1) || ($db_partner_id != $cu_forum_id) || ($db_gesendet != '0') ) {
    echo "Deine Daten konnten nicht in der Datenbank gefunden werden!<br><br>";

    echo "Klicke <a href=\"javascript:history.back()\">hier</a>, um zum Formular zur&uuml;ckzukehren und die Fehler zu beheben.";
  } //if ( ($db_geschenk_id == NULL) || ($db_partner_id != $user->data['user_id']) || ($db_status != '1') || ($db_gesendet != NULL) )
  #Daten speichern
  else {
    $db = mysql_connect($dbsrv,$dbuser,$dbpasswd);
    if (!$db) {
      die("Datebank verbindung schlug fehl: ". mysql_error());
    } else {
      $cu_forum_id = $user_id;
      $query = mysql_query("UPDATE wi_geschenk SET gesendet = NOW(), post_art = '$post_art', post_id = '$post_id' WHERE geschenk_id = '$geschenk_id'");

      if($today > $senden_ende){
        $query = "INSER INTO wi_blacklist (forum_id, nick, grund ) VALUES ('$cu_forum_id', )"
      }
      mysql_close();
    }
    #Infotext anzeigen
    echo "<p><b>Hallo ".$user->data['username']."!</b></p>";
    echo $gesendet_ende;
  } //else
} //function senden()

?>
<p><a href="index.php">Zur&uuml;ck zur Startseite</a></p>

</section>
</article>
</body>
</html>
