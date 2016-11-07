<?php
#Beginne Session
session_start();

$post=$_POST;
include("cfg.php");
// Ben�tigte Dateien und Variablen von phpBB3
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../forum/';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require($phpbb_root_path . 'common.' . $phpEx);

// Session auslesen und Benutzer-Informationen laden
$user->session_begin();  // Session auslesen
$auth->acl($user->data); // Benutzer-Informationen laden
$user->setup();

#�berpr�fe Rechte
include('static.php');
  if ( !$user->data['is_registered'] ) {
    header("Location: was-ist-denn-hier-los.php?Grund=nicht_eingeloggt");
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
    <a href="./index.php"><img src="./img/nostern.gif" border="0" alt=""></a>

    <section class="main">

<?php
#Ziehe Variablen aus HTTP_VARS
if(isset($post['daten'])) $_SESSION["daten"] = $post['daten'];

if(isset($post['senden'])) senden();
else eintrag();

#Funktionen ausfuehren
function eintrag() {
  include('lanq.php');
  global $user;

  #Infotext anzeigen
  echo "<p><b>Hallo ".$user->data['username']."!</b></p>";
  echo $empfangen_hinweis;

  #Datenformular anzeigen
  echo <<<EINTRAG
    <form action="$PHP_SELF" method="post">
      <fieldset>
        <ul class="flex-outer">
          <li>
            <label for="geschenk_id">Geschenk-ID:</li>
            <input id="geschenk_id" type="text" name="daten[]" size="45" maxlength="100" VALUE="$geschenk_id">
          </li>
          <li>
            <input type="submit" name="senden" value="abschicken">
            <input type="reset" value=" löschen ">
          </li>
        </ul>
      </fieldset>
    </form>
EINTRAG;

} //function eintrag()

function senden() {
  $daten = $_SESSION["daten"];
  global $user;
  include('lanq.php');
  include('cfg.php');

  #Eingabedaten aus Array ziehen
  $user_id = $user->data['user_id'];
  $geschenk_id = $daten[0];

  #Daten aus Datenbank abrufen

  $db = mysql_connect($dbsrv,$dbuser,$dbpasswd);
  if (!$db) {
    die("Datebankverbindung schlug fehl: ". mysql_error());
  } else {
    mysql_select_db($dbname);
    $query = mysql_query("SELECT wichtel_id FROM wi_wichtel WHERE forum_id = '$user_id'");

    while ($erg =@ mysql_fetch_array($query)) {
      $db_wichtel_id = $erg["wichtel_id"];
    }

    $query = mysql_query("SELECT * FROM wi_geschenk WHERE geschenk_id = '$geschenk_id'");

    while ($erg =@ mysql_fetch_array($query)) {
      $db_geschenk_id = $erg["geschenk_id"];
      $db_wichtel_id2 = $erg["wichtel_id"];
      $db_status = $erg["status"];
      $db_empfangen = $erg["empfangen"];
    }

    mysql_close();
  }
  #ueberpruefe Daten auf Richtigkeit
  if ( $db_status == 4 ) {
    echo "Der Empfang dieses Geschenkes wurde bereits best&auml;tigt.!<br><br>";
    echo "Klicke <a href=\"javascript:history.back()\">hier</a>, um zum Formular zur&uuml;ckzukehren und die Fehler zu beheben.";
  } //if ( ($db_empfangten != '0') )
  elseif ( ($db_geschenk_id == NULL) || ($db_wichtel_id != $db_wichtel_id2) || ($db_status == 2) ) {
    echo "Geschenk, dessen Empfang du best&auml;tigen willst: ".$geschenk_id."<br>";

    echo "Deine Daten konnten nicht in der Datenbank gefunden werden!<br><br>";
    echo "Klicke <a href=\"javascript:history.back()\">hier</a>, um zum Formular zur&uuml;ckzukehren und den Fehler zu beheben.";
  } //if ( ($db_geschenk_id == NULL) || ($db_wichtel_id != $db_wichtel_id2) || ($db_status != 1) )
  #Daten speichern

  else {
    #Daten in DB-Schreiben
    $db = mysql_connect($dbsrv,$dbuser,$dbpasswd);
    if (!$db) {
      die("Datebankverbindung schlug fehl: ". mysql_error());
    } else {
      mysql_select_db($dbname);
      $query = mysql_query("UPDATE wi_geschenk SET empfangen = NOW(), status = '4' WHERE geschenk_id = '$geschenk_id'");
      mysql_close();
    }
    #Infotext anzeigen
    echo "<p><b>Hallo ".$user->data['username']."!</b></p>";
    echo $empfangen_ende;

    #Mail an Wichtel schicken
    $db = mysql_connect($dbsrv,$dbuser,$dbpasswd);
    if (!$db) {
      die("Datebankverbindung schlug fehl: ". mysql_error());
    } else {
      mysql_select_db($dbname);
      $query = mysql_query("SELECT email, nick FROM wi_wichtel LEFT JOIN wi_geschenk ON (wi_geschenk.partner_id = wi_wichtel.wichtel_id) WHERE wi_geschenk.geschenk_id = '$geschenk_id'");

      while ($erg =@ mysql_fetch_array($query)) {
        $mailto = $erg["email"]; $partner = $erg["nick"];
      }
      mysql_close();
    }

    $mailto = $mail;
    $subject = "Hallo Wichtel".$mail;
    $mail2="kri_zilla@yahoo.de";
    $header = "From: Weihnachtswichtel <kri_zilla@yahoo.de>";
    $bekommen_mail = str_replace ("_PARTNER_", $user->data['username'], $bekommen_mail);
    $bekommen_mail = str_replace ("_USERNAME_", $partner, $bekommen_mail);
    mail($mailto,$subject,$bekommen_mail,$header);
  } //else
} //function senden()

?>
<p><a href="index.php">Zurück zur Startseite</a></p>

</section>
</article>
</body>
</html>
