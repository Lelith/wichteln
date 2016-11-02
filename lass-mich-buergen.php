<?php
#Beginne Session
session_start();

$post=$_POST;
// Ben�tigte Dateien und Variablen von phpBB3
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../forum/';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require($phpbb_root_path . 'common.' . $phpEx);
include("cfg.php");
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
//$today="201511270000";
#Daten aus Datenbank abrufen

$user_id = $user->data['user_id'];
$user_posts = $user->data['user_posts'];
mysql_connect("localhost",$dbuser,$dbpasswd);
mysql_select_db($dbname);
$query = mysql_query("SELECT blacklist_id FROM wi_blacklist WHERE user_id = '$user_id'");
while ($erg =@ mysql_fetch_array($query)) { $blacklist = $erg["blacklist_id"]; }
mysql_close();

#�berpr�fe Rechte
include('static.php');
if ( !$user->data['is_registered'] ) { header("Location: was-ist-denn-hier-los.php?Grund=nicht_eingeloggt"); }
elseif ( $user_posts < $buerge_min_posts ) { header("Location: was-ist-denn-hier-los.php?Grund=zu_wenig_buerge_posts"); }
elseif ( (($today < $buergen_start)) || (($today > $buergen_ende)) ) { header("Location: was-ist-denn-hier-los.php?Grund=zeit_buergen"); }
elseif ( ($blacklist != NULL) ) { header("Location: was-ist-denn-hier-los.php?Grund=blacklist"); }
?>


<html>
<head>
<meta name="author" content="systemhexe">
<meta name="organization" content="N&auml;hkromanten">
<meta charset="UTF-8">
<title>Das N&auml;hkromanten Weihnachtswichteln</title>
<base target=_self>
<link href="wicht.css" rel="stylesheet" type="text/css">
</head>

<body>

<div style="width:100%; background: #fff; border-radius: 18px;"><br><br>
<div align="center">
<br>
<img src="nostern.gif" width="261" height="261" border="0" alt="*">
<h2>Lass mich Dein B&uuml;rge sein!</h2>
<br><br>
<table width="60%" ><tr><td>

<?php
#Ziehe Variablen aus HTTP_VARS
if(isset($post['daten'])) $_SESSION["daten"] = $post['daten'];

if(isset($post['select'])) eintrag();
elseif(isset($post['senden'])) senden();
else info();

#Funktionen ausf�hren
function info ()
{
        global $user;
        include('lanq.php');
        include('static.php');

        #Infoseite anzeigen
        echo "<p><b>Hallo ".$user->data['username']."!</b></p>";
        $buergen_info = str_replace ("_MINPOST_", $user_min_posts, $buergen_info);
        echo $buergen_info;

        #Best�tigungsformular anzeigen
        echo <<<FORMULAR
        <form action="$PHP_SELF" method="post">
                <p><input type="checkbox" name="select" value="select">&nbsp;&nbsp;Ich habe alles gelesen und bin einverstanden&nbsp;&nbsp;<input type="submit" name="absenden" value="OK"></p>
        </form>
FORMULAR;

} //function info()


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
                <p>Nick: <input type="text" name="daten[]" size="50" maxlength="50" VALUE="$wichtel_nick"><br><br>
                <input type="submit" name="senden" value="eintragen">&nbsp;<input type="reset" value=" l&ouml;schen "></p>
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
		mysql_connect("localhost",$dbuser,$dbpasswd);
		mysql_select_db($dbname);
        $query = mysql_query("SELECT user_id, user_email FROM phpbb_users WHERE username = '$wichtel_nick'");
        while ($erg =@ mysql_fetch_array($query))
        {
                $wichtel_id = $erg["user_id"];
                $wichtel_mail = $erg["user_email"];
        }
        mysql_close();

        #�berpr�fe User-ID ob User angemeldet
        if ($wichtel_id == NULL) {
                echo "Der Nick <b>".$wichtel_nick."</b> konnte im Forum nicht gefunden werden!<br><br>";
                echo "Klicke <a href=\"javascript:history.back()\">hier</a>, um zum Formular zur&uuml;ckzukehren und den Fehler zu beheben.";
        } //if ($wichtel_id == NULL)

        #Daten speichern
        else {
                #Daten in DB-Schreiben
                $buerge_id = $user->data['user_id'];
                $buerge_nick = $user->data['username'];
                include("cfg.php");
				mysql_connect("localhost",$dbuser,$dbpasswd);
				mysql_select_db($dbname);
	            $query = mysql_query("INSERT INTO wi_buerge (buerge_forum_id, buerge_forum_nick, wichtel_id, wichtel_nick) VALUES ('$buerge_id', '$buerge_nick', '$wichtel_id', '$wichtel_nick')");
                mysql_close();

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
</td></tr></table><br><br></div>
</div>
</body>
</html>
