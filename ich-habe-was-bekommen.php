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


$td=getdate();
if ($td["mday"]<10)
	$td["mday"]="0".$td["mday"];
if ($td["mon"]<10)
	$td["mon"]="0".$td["mon"];
if ($td["hours"]<10)
	$td["hours"]="0".$td["hours"];
if ($td["minutes"]<10)
	$td["minutes"]="0".$td["minutes"];

$today=$td["year"].$td["mon"].$td["mday"].$td["hours"].$td["minutes"];
#�berpr�fe Rechte
include('static.php');
  if ( !$user->data['is_registered'] ) {
    header("Location: was-ist-denn-hier-los.php?Grund=nicht_eingeloggt");
  }
  elseif ( (($today < $empfangen_start)) || (($today > $empfangen_ende)) ) {
    header("Location: was-ist-denn-hier-los.php?Grund=zeit_empfangen");
  }
?>

<html>
<head>
<meta name="author" content="Systemhexe">
<meta name="organization" content="N&auml;hkromanten">
<title>Das N&auml;hkromanten Weihnachtswichteln</title>
<base target=_self>
<meta charset="UTF-8">
<link href="wicht.css" rel="stylesheet" type="text/css">
</head>

<body>

<div style="width:100%; background: #fff; border-radius: 18px;"><br><br>
<div align="center">
<br>
<img src="nostern.gif" width="261" height="261" border="0" alt="*">
<h2>Ich habe Post bekommen!</h2>
<br><br>
<table width="60%" ><tr><td>

<?php
#Ziehe Variablen aus HTTP_VARS
if(isset($post['daten'])) $_SESSION["daten"] = $post['daten'];

if(isset($post['senden'])) senden();
else eintrag();

#Funktionen ausf�hren
function eintrag()
{
global $user;
include('lanq.php');

#Infotext anzeigen
echo "<p><b>Hallo ".$user->data['username']."!</b></p>";
echo $empfangen_hinweis;

#Datenformular anzeigen
echo <<<EINTRAG
<form action="$PHP_SELF" method="post">
<table border="0">
<tr>
<td>
        Geschenk-ID:
</td>
<td>
        <input type="text" name="daten[]" size="45" maxlength="100" VALUE="$geschenk_id">
</td>
</tr>
<tr>
<td>
        &nbsp;
</td>
<td>
        <div align="center"><br><input type="submit" name="senden" value="abschicken">&nbsp;&nbsp;&nbsp;<input type="reset" value=" l&ouml;schen "></div>
</td>
</tr>
</table>
</form>
EINTRAG;

} //function eintrag()

function senden()
{
  $daten = $_SESSION["daten"];
  global $user;
  include('lanq.php');

  $td=getdate();
  if ($td["mday"]<10)
  $td["mday"]="0".$td["mday"];
  if ($td["mon"]<10)
  $td["mon"]="0".$td["mon"];
  if ($td["hours"]<10)
  $td["hours"]="0".$td["hours"];
  if ($td["minutes"]<10)
  $td["minutes"]="0".$td["minutes"];

  $today=$td["year"].$td["mon"].$td["mday"].$td["hours"].$td["minutes"];
  #Eingabedaten aus Array ziehen
  $user_id = $user->data['user_id'];
  $geschenk_id = $daten[0];
  $empfangen = $today;

  #Daten aus Datenbank abrufen

  include("cfg.php");
  mysql_connect("localhost",$dbuser,$dbpasswd);
  mysql_select_db($dbname);
  $query = mysql_query("SELECT wichtel_id FROM wi_wichtel WHERE forum_id = '$user_id'");
  while ($erg =@ mysql_fetch_array($query))
  {
  $db_wichtel_id = $erg["wichtel_id"];
  }
  $query = mysql_query("SELECT * FROM wi_geschenk WHERE geschenk_id = '$geschenk_id'");
  while ($erg =@ mysql_fetch_array($query))
  {
  $db_geschenk_id = $erg["geschenk_id"];
  $db_wichtel_id2 = $erg["wichtel_id"];
  $db_status = $erg["status"];
  $db_empfangen = $erg["empfangen"];
  }
  mysql_close();

  #ueberpruefe Daten auf Richtigkeit
  if ( ($db_empfangen) && ($db_empfangen!= '0') ) {
    echo "Der Empfang dieses Geschenkes wurde bereits best&auml;tigt.!<br><br>";
    echo "Klicke <a href=\"javascript:history.back()\">hier</a>, um zum Formular zur&uuml;ckzukehren und die Fehler zu beheben.";
  } //if ( ($db_empfangten != '0') )
  elseif ( ($db_geschenk_id == NULL) || ($db_wichtel_id != $db_wichtel_id2) || ($db_status != 1) ) {
    echo "Deine Daten konnten nicht in der Datenbank gefunden werden!<br><br>";
    echo "Klicke <a href=\"javascript:history.back()\">hier</a>, um zum Formular zur&uuml;ckzukehren und die Fehler zu beheben.";
  } //if ( ($db_geschenk_id == NULL) || ($db_wichtel_id != $db_wichtel_id2) || ($db_status != 1) )
  #Daten speichern
  else {
    #Daten in DB-Schreiben

    include("cfg.php");
    mysql_connect("localhost",$dbuser,$dbpasswd);
    mysql_select_db($dbname);
    $query = mysql_query("UPDATE wi_geschenk SET empfangen = '$empfangen' WHERE geschenk_id = '$geschenk_id'");
    mysql_close();

      #Infotext anzeigen
      echo "<p><b>Hallo ".$user->data['username']."!</b></p>";
      echo $empfangen_ende;

    #Mail an Wichtel schicken
    mysql_connect("localhost",$dbuser,$dbpasswd);
    mysql_select_db($dbname);
    $query = mysql_query("SELECT email, nick FROM wi_wichtel LEFT JOIN wi_geschenk ON (wi_geschenk.partner_id = wi_wichtel.wichtel_id) WHERE wi_geschenk.geschenk_id = '$geschenk_id'");
    while ($erg =@ mysql_fetch_array($query)) {
      $mailto = $erg["email"]; $partner = $erg["nick"];
    }
    mysql_close();
    $subject = "Hallo Wichtel";
    $header = "From: Weihnachtshexe <dieverschleierte@web.de>";
    $bekommen_mail = str_replace ("_PARTNER_", $user->data['username'], $bekommen_mail);
    $bekommen_mail = str_replace ("_USERNAME_", $partner, $bekommen_mail);
    mail($mailto,$subject,$bekommen_mail,$header);
  } //else
} //function senden()

?>
<br>
<p><a href="index.php">Zur&uuml;ck zur Startseite</a></p>

</td></tr></table><br><br>
</div>
</div>
</body>
</html>
