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
if ( !$user->data['is_registered'] ) { header("Location: was-ist-denn-hier-los.php?Grund=nicht_eingeloggt"); }
elseif ( (($today < $senden_start)) || (($today > $senden_ende)) ) { header("Location: was-ist-denn-hier-los.php?Grund=zeit_senden"); }
?>

<html>
<head>
<meta name="author" content="Systemhexe">
<meta name="organization" content="N&auml;hkromanten">
<title>Das N&auml;hkromanten Weihnachtswichteln</title>
<base target=_self>
<meta charset="UTF-8">
<link href="wicht.css" rel="stylesheet" type="text/css">

<script type="text/javascript">
function chkFormular () {
  if (document.Eintrag.elements[0].value == "") {
    alert("Du hast leider keine Geschenk-ID eingegeben!");
    document.Eintrag.elements[0].focus();
    return false;
  }
  if (document.Eintrag.elements[1].value == "") {
    alert("Du hast leider keinen Anbieter eingegeben!");
    document.Eintrag.elements[1].focus();
    return false;
  }
  if (document.Eintrag.elements[2].value == "") {
    alert("Du hast leider keine Trackingnummer eingegeben!");
    document.Eintrag.elements[2].focus();
    return false;
  }
}
</script>

</head>

<body>

<div style="width:100%; background: #fff; border-radius: 18px;"><br><br>
<div align="center">
<br>
<img src="nostern.gif" width="261" height="261" border="0" alt="*">
<h2>Mein Geschenk ist verschickt!</h2>
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
        echo $gesendet_hinweis;

        #Datenformular anzeigen
        echo <<<EINTRAG
        <form action="$PHP_SELF" method="post" onsubmit="return chkFormular()" name="Eintrag">
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
                        Anbieter:
                </td>
                <td>
                        <input type="text" name="daten[]" size="45" maxlength="100" VALUE="$post_art">
                </td>
        </tr>
        <tr>
                <td>
                        Trackingnummer:
                </td>
                <td>
                        <input type="text" name="daten[]" size="45" maxlength="100" VALUE="$post_id">
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

        #Eingabedaten aus Array ziehen
        $user_id = $user->data['user_id'];
        $geschenk_id = $daten[0];
        $post_art = $daten[1];
        $post_id = $daten[2];
        $gesendet = $today;


		include("cfg.php");
		#Daten aus Datenbank abrufen
		mysql_connect("localhost",$dbuser,$dbpasswd);
		mysql_select_db($dbname);

        $db_wichtel_id = $user_id;
        $query = mysql_query("SELECT * FROM wi_geschenk WHERE geschenk_id = '$geschenk_id'");
        while ($erg =@ mysql_fetch_array($query))
        {
                $db_geschenk_id = $erg["geschenk_id"];
                $db_status = $erg["status"];
                $db_partner_id = $erg["partner_id"];
                $db_gesendet = $erg["gesendet"];
        }
        mysql_close();
        #�berpr�fe Daten auf Richtigkeit


       if ( ($db_geschenk_id == NULL) || ($db_status != 1) || ($db_partner_id != $db_wichtel_id) || ($db_gesendet != '0') ) {
                echo "Deine Daten konnten nicht in der Datenbank gefunden werden!<br><br>";
                echo "Klicke <a href=\"javascript:history.back()\">hier</a>, um zum Formular zur&uuml;ckzukehren und die Fehler zu beheben.";
        } //if ( ($db_geschenk_id == NULL) || ($db_partner_id != $user->data['user_id']) || ($db_status != '1') || ($db_gesendet != NULL) )

        #Daten speichern
        else {
                #Daten in DB-Schreiben
				mysql_connect("localhost",$dbuser,$dbpasswd);
				mysql_select_db($dbname);
                $query = mysql_query("UPDATE wi_geschenk SET gesendet = NOW(), post_art = '$post_art', post_id = '$post_id' WHERE geschenk_id = '$geschenk_id'");
                mysql_close();

                #Infotext anzeigen
                echo "<p><b>Hallo ".$user->data['username']."!</b></p>";
                echo $gesendet_ende;
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
