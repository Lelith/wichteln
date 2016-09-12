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

#Daten aus Datenbank abrufen
$user_id = $user->data['user_id'];
$user_posts = $user->data['user_posts'];
mysql_connect("localhost",$dbuser,$dbpasswd);
mysql_select_db($dbname);

$query = mysql_query("SELECT buerge_id FROM wi_buerge WHERE wichtel_id = '$user_id'");
while ($erg =@ mysql_fetch_array($query)) { $buerge = $erg["buerge_id"]; }
$query = mysql_query("SELECT blacklist_id FROM wi_blacklist WHERE user_id = '$user_id'");
while ($erg =@ mysql_fetch_array($query)) { $blacklist = $erg["blacklist_id"]; }
$query = mysql_query("SELECT wi_geschenk.geschenk_id FROM wi_geschenk LEFT JOIN wi_wichtel ON (wi_geschenk.partner_id = wi_wichtel.wichtel_id) WHERE wi_wichtel.forum_id =  '$user_id' AND wi_geschenk.gesendet='0'");
while ($erg =@ mysql_fetch_array($query)) { $geschenk = $erg["geschenk_id"]; }
mysql_close();


#�berpr�fe Rechte
include('static.php');
if ( !$user->data['is_registered'] ) { header("Location: was-ist-denn-hier-los.php?Grund=nicht_eingeloggt"); }
elseif ( ($user_posts < $user_min_posts) && ($buerge == NULL) ) { header("Location: was-ist-denn-hier-los.php?Grund=zu_wenig_posts"); }
elseif ( (($today < $anfragen_start)) || (($today > $anfragen_ende)) ) { header("Location: was-ist-denn-hier-los.php?Grund=zeit_anfragen"); }
elseif ( ($blacklist != NULL) ) { header("Location: was-ist-denn-hier-los.php?Grund=blacklist"); }
elseif ( ($geschenk != NULL) ) { $geschenksperre=1; }
?>

<html>
<head>
<meta name="author" content="Systemhexe">
<meta name="organization" content="N&auml;hkromanten">
<meta charset="utf-8" />
<title>Das N&auml;hkromanten Weihnachtswichteln</title>
<base target=_self>
<link href="wicht.css" rel="stylesheet" type="text/css">
<style type="text/css">
<!--
body {
 background: url(nobortemini.gif) repeat-x top;
}
-->
</style>
</head>

<body>

<div align="center">
<br>
<br>
<img src="noherzkleindeko.gif" width="297" height="45" border="0" alt="*">
<h2>Ich m&ouml;chte jemanden beschenken!</h2>
<br><br>
<table width="800" ><tr><td>

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

#Funktionen ausf�hren
function info ()
{
        global $user;
        include('lanq.php');

        #Infoseite anzeigen
        echo "<p><b>Hallo ".$user->data['username']."!</b></p>";
        echo $anfragen_info;

        #Best�tigungsformular anzeigen
        echo <<<FORMULAR
        <form action="$PHP_SELF" method="post">
                <p><input type="checkbox" name="suche" value="select">&nbsp;&nbsp;Ich habe alles gelesen und bin einverstanden&nbsp;&nbsp;<input type="submit" name="natronisttoll" value="OK"></p>
        </form>
FORMULAR;

} //function info()

function infosperre ()
{
        global $user;
        include('lanq.php');

        #Infoseite anzeigen
        echo "<p><b>Hallo ".$user->data['username']."!</b><br><br>Du hast bereits ein Geschenk ausgew&auml;hlt. Bevor du ein weiteres aussuchen kannst musst du das andere erst verschickt und das auch best&auml;tigt haben. Hier siehst du so lange die aktuelle Quote der Geschenkvergabe:</p><br>";

        #Baum berechnen und anzeigen
		mysql_connect("localhost",$dbuser,$dbpasswd);
		mysql_select_db($dbname);
        $query = mysql_query("SELECT COUNT(*) FROM wi_geschenk");
        while ($erg =@ mysql_fetch_array($query)) { $werta = $erg["COUNT(*)"]; }
        $query = mysql_query("SELECT COUNT(*) FROM wi_geschenk WHERE status!=0 AND status!=4");
        while ($erg =@ mysql_fetch_array($query)) { $wertb = $erg["COUNT(*)"]; }
        mysql_close();
        if ($werta == 0) { $baumstatus = 100; } else { $baumstatus = round(100 / $werta * $wertb); }
        $baumstatus = floor($baumstatus/10);
        switch ($baumstatus) {
        case 10:
                echo "<img src=\"statusschleife/schleifeff1fed.gif\" width=\"600\" height=\"115\" border=\"0\" alt=\"Status\">";
                break;
        case 9:
                echo "<img src=\"statusschleife/schleifefa90ae.gif\" width=\"600\" height=\"115\" border=\"0\" alt=\"Status\">";
                break;
        case 8:
                echo "<img src=\"statusschleife/schleifeee85de.gif\" width=\"600\" height=\"115\" border=\"0\" alt=\"Status\">";
                break;
        case 7:
                echo "<img src=\"statusschleife/schleifede7dfa.gif\" width=\"600\" height=\"115\" border=\"0\" alt=\"Status\">";
                break;
        case 6:
                echo "<img src=\"statusschleife/schleifedb66f3.gif\" width=\"600\" height=\"115\" border=\"0\" alt=\"Status\">";
                break;
        case 5:
                echo "<img src=\"statusschleife/schleifecf5f6c.gif\" width=\"600\" height=\"115\" border=\"0\" alt=\"Status\">";
                break;
        case 4:
                echo "<img src=\"statusschleife/schleifece44bd.gif\" width=\"600\" height=\"115\" border=\"0\" alt=\"Status\">";
                break;
        case 3:
                echo "<img src=\"statusschleife/schleifeca3210.gif\" width=\"600\" height=\"115\" border=\"0\" alt=\"Status\">";
                break;
        case 2:
                echo "<img src=\"statusschleife/schleifebf25fc.gif\" width=\"600\" height=\"115\" border=\"0\" alt=\"Status\">";
                break;
        case 1:
                echo "<img src=\"statusschleife/schleifeae172d.gif\" width=\"600\" height=\"115\" border=\"0\" alt=\"Status\">";
                break;
        default:
                echo "<img src=\"statusschleife/schleifeaa0f34.gif\" width=\"600\" height=\"115\" border=\"0\" alt=\"Status\">";
        } //switch ($baumstatus)

        echo "<br><br>";


} //function infosperre()


function suche($suchstat)
{
        $datenanf = $_SESSION["datenanf"];
        $search = $_SESSION["datenanf"];
        global $user;
        include('lanq.php');

        #Infotext anzeigen
        echo "<p><b>Hallo ".$user->data['username']."!</b></p>";
        echo $anfragen_hinweis;

        #Suchformular anzeigen
        echo <<<EINTRAG
        <table width="100%" cellpadding="10" border="0">
        <tr>
                <td valign="top" align="left">
                        <form action="$PHP_SELF" method="post" name="Suche">

                                <table>
                                <tr>
                                 <td>Stichwort: </td>
                                 <td><input type="text" name="datenanf[0]" value="$datenanf[0]" size="35" maxlength="50"> </td>
                                </tr>
                                <tr>
                                 <td>Schwierigkeitsgrad: </td>
                                 <td><select name="datenanf[1]" size="1">
                                         <option id="1">Egal</option>
                                         <option id="2">Kleinigkeit</option>
                                         <option id="3">Mittel</option>
                                         <option id="4">Anspruchsvoll</option>
                                     </select>
                                 </td>
                                </tr>
                                <tr>
                                 <td>Kategorie: </td>
                                 <td><select name="datenanf[2]" size="1">
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
                                 </td>
                                </tr>

                                </table>


                                <br>
                                <input type="submit" name="suchedb" value="Suchen"></form>
                                <form action="$PHP_SELF" method="post" name="Suche2"><input type="submit" name="sucherand" value="Auf gut Gl&uuml;ck!"></form>
                </td>
                <td valign="top">
EINTRAG;

                #Suche durchf�hren und Ergebnisse und anzeigen
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
								mysql_connect("localhost",$dbuser,$dbpasswd);
								mysql_select_db($dbname);
                                $query = mysql_query("SELECT wichtel_id FROM wi_wichtel WHERE forum_id = '$forum_id'");
                                while ($erg =@ mysql_fetch_array($query)) { $user_wichtel_id = $erg["wichtel_id"]; }
                                $sql = "SELECT geschenk_id FROM wi_geschenk WHERE status=0 AND wichtel_id!='$user_wichtel_id'";
                               if ($suchstat == 1) {
#                                        if ($text) { $sql = $sql." AND MATCH (beschreibung) AGAINST ('$text' IN BOOLEAN MODE)"; }
                                        if ($text) { $sql = $sql." AND beschreibung LIKE CONCAT('%' , '$text', '%')"; }
                                        if ($level != "Egal") { $sql = $sql." AND level='$level'"; }
                                        if ($art != "Egal") { $sql = $sql." AND art='$art'"; }
                                } //if ($suchstat == 1)
                                $query = mysql_query($sql);
                                while ($erg =@ mysql_fetch_array($query)) { $liste[] = $erg["geschenk_id"]; }
                                if (count($liste) == 0) { $liste2 = array('x','x','x'); }
                                elseif (count($liste) == 1) { $liste2 = array(0,'x','x'); }
                                elseif (count($liste) == 2) { $liste2 = array(0,1,'x'); }
                                elseif (count($liste) > 2) { $liste2 = array_rand($liste, 3); }

                                #Zeige Ergebnisse an
                                for ($i = 0; $i < 3; $i++) {
                                        $geschenk_id = $liste[$liste2[$i]];
                                        $query = mysql_query("SELECT beschreibung, level, art FROM wi_geschenk WHERE geschenk_id = '$geschenk_id'");
                                        while ($erg =@ mysql_fetch_array($query)) {
                                                $beschreibung = $erg["beschreibung"];
                                                $level = $erg["level"];
                                                $art = $erg["art"];
                                                $beschreibung2 = substr($beschreibung, 0, 200);
                                                if (strlen($beschreibung)>200) $beschreibung2=$beschreibung2."...";

                                                echo <<<AUSGABE
                                                        <tr><td>
                                                        <table width="100%" cellpadding="0" border="0">
                                                        <tr><td>$beschreibung2</td></tr>
                                                        <tr><td><i>Schwierigkeitsgrad: $level</i></td></tr>
                                                        <tr><td><i>Kategorie: $art</i></td></tr>
                                                        <tr><td>
                                                        <form action="$PHP_SELF" method="post" name="Detail$i">
                                                        <input type="hidden" name="datenanf[3]" value="$geschenk_id">
                                                        <input type="submit" name="detail" value="mehr Infos"></form>
                                                        </td></tr>
                                                        </table>
                                                        </td></tr>
AUSGABE;
                                        } //while ($erg =@ mysql_fetch_array($query))
                                } //for ($i=0; $i<3; $i++)
                                if (count($liste) == 0) echo "<tr><td>Keine passenden W&uuml;nsche gefunden. Versuch es bitte mit weniger Einschr&auml;nkungen erneut.</td></tr>";
                                mysql_close();
                                echo "</table>";
                     } //if ($suchstat)
                     else echo "&nbsp;";


        echo "</td></tr><tr><td colspan=\"2\" align=\"center\" valign=\"top\">";

        include("cfg.php");
        #Baum berechnen und anzeigen
		mysql_connect("localhost",$dbuser,$dbpasswd);
		mysql_select_db($dbname);
        $query = mysql_query("SELECT COUNT(*) FROM wi_geschenk");
        while ($erg =@ mysql_fetch_array($query)) { $werta = $erg["COUNT(*)"]; }
        $query = mysql_query("SELECT COUNT(*) FROM wi_geschenk WHERE status!=0 AND status!=4");
        while ($erg =@ mysql_fetch_array($query)) { $wertb = $erg["COUNT(*)"]; }
        mysql_close();
        if ($werta == 0) { $baumstatus = 100; } else { $baumstatus = round(100 / $werta * $wertb); }
        $baumstatus = floor($baumstatus/10);
        switch ($baumstatus) {
        case 10:
                echo "<img src=\"statusschleife/schleifeff1fed.gif\" width=\"600\" height=\"115\" border=\"0\" alt=\"Status\">";
                break;
        case 9:
                echo "<img src=\"statusschleife/schleifefa90ae.gif\" width=\"600\" height=\"115\" border=\"0\" alt=\"Status\">";
                break;
        case 8:
                echo "<img src=\"statusschleife/schleifeee85de.gif\" width=\"600\" height=\"115\" border=\"0\" alt=\"Status\">";
                break;
        case 7:
                echo "<img src=\"statusschleife/schleifede7dfa.gif\" width=\"600\" height=\"115\" border=\"0\" alt=\"Status\">";
                break;
        case 6:
                echo "<img src=\"statusschleife/schleifedb66f3.gif\" width=\"600\" height=\"115\" border=\"0\" alt=\"Status\">";
                break;
        case 5:
                echo "<img src=\"statusschleife/schleifecf5f6c.gif\" width=\"600\" height=\"115\" border=\"0\" alt=\"Status\">";
                break;
        case 4:
                echo "<img src=\"statusschleife/schleifece44bd.gif\" width=\"600\" height=\"115\" border=\"0\" alt=\"Status\">";
                break;
        case 3:
                echo "<img src=\"statusschleife/schleifeca3210.gif\" width=\"600\" height=\"115\" border=\"0\" alt=\"Status\">";
                break;
        case 2:
                echo "<img src=\"statusschleife/schleifebf25fc.gif\" width=\"600\" height=\"115\" border=\"0\" alt=\"Status\">";
                break;
        case 1:
                echo "<img src=\"statusschleife/schleifeae172d.gif\" width=\"600\" height=\"115\" border=\"0\" alt=\"Status\">";
                break;
        default:
                echo "<img src=\"statusschleife/schleifeaa0f34.gif\" width=\"600\" height=\"115\" border=\"0\" alt=\"Status\">";
        } //switch ($baumstatus)

        echo "</td></tr></table>";
} //function suche($suchstat)

function detail()
{
        $datenanf = $_SESSION["datenanf"];
        global $user;
        include('lanq.php');

        #Infoseite anzeigen
        echo "<p><b>Hallo ".$user->data['username']."!</b></p>";
        echo $anfragen_detail;

        #Geschenkdaten abrufen
        $geschenk_id = $datenanf[3];
        include("cfg.php");
		mysql_connect("localhost",$dbuser,$dbpasswd);
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

        $beschreibung = str_replace("\r\n","<br>",$beschreibung);
        $notizen = str_replace("\r\n","<br>",$notizen);

        #Geschenkdaten anzeigen
        echo <<<EINTRAG
                <form action="$PHP_SELF" method="post" name="Eintrag">
                <table border="0">
                <tr><td><b>Beschreibung:</b><br>$beschreibung</td></tr>
                <tr><td><b><br>Schwierigkeit:</b> $level</td></tr>
                <tr><td><b>Kategorie:</b> $art</td></tr>
                <tr><td><b><br>Notizen:</b><br>$notizen</td></tr>
                <tr>
                        <td>
                                <div align="center"><br><input type="submit" name="verifize" value="Best&auml;tigen">&nbsp;&nbsp;&nbsp;<input type="submit" name="suche" value=" Zur&uuml;ck "></div>
                        </td>
                </tr>
                </table>
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
        <input type="checkbox" name="senden" value="select">&nbsp;&nbsp;Ich habe alles gelesen und bin einverstanden&nbsp;&nbsp;<input type="submit" name="verifize" value="OK">
        </form>
EINTRAG;
} //verifize()

function senden()
{
        $datenanf = $_SESSION["datenanf"];
        global $user;
        include('lanq.php');

        #Infoseite anzeigen
        echo "<p><b>Hallo ".$user->data['username']."!</b></p>";
        echo $anfragen_ende;

        #Geschenk- und Wichteldaten abrufen
        $geschenk_id = $datenanf[3];
        $forum_id = $user->data['user_id'];

        include("cfg.php");
		mysql_connect("localhost",$dbuser,$dbpasswd);
		mysql_select_db($dbname);
        $query = mysql_query("SELECT wichtel_id, email FROM wi_wichtel WHERE forum_id = '$forum_id'");
        while ($erg =@ mysql_fetch_array($query)) {
                $user_wichtel_id = $erg["wichtel_id"];
                $usermail = $erg["email"];
        } //while ($erg =@ mysql_fetch_array($query))
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
        mysql_close();

        #ueberpruefe ob Wichtel vorhanden, sonst nachtragen
        if (!$user_wichtel_id) {
                $usermail = $user->data['user_email'];
                $usernick = $user->data['username'];
				mysql_connect("localhost",$dbuser,$dbpasswd);
				mysql_select_db($dbname);
                $query = mysql_query("INSERT INTO wi_wichtel (wichtel_id, forum_id, nick, email) VALUES ('$forum_id', '$forum_id', '$usernick', '$usermail')");
                $query = mysql_query("SELECT wichtel_id FROM wi_wichtel WHERE forum_id = '$forum_id'");
                while ($erg =@ mysql_fetch_array($query)) { $user_wichtel_id = $erg["wichtel_id"]; }
                mysql_close();
        }// if (!$user_wichtel_id)

        #�berpr�fe ob Geschenk noch frei und kein eigenes ist
        if ( ($status==0 || $status==4) && ($wichtel_id != $user_wichtel_id) ) {
                #Geschenk-Status anpassen
				mysql_connect("localhost",$dbuser,$dbpasswd);
				mysql_select_db($dbname);
                $query = mysql_query("UPDATE wi_geschenk SET status = 2 WHERE wichtel_id = '$wichtel_id' AND status != 5;");
                $query = mysql_query("UPDATE wi_geschenk SET status = 1 WHERE geschenk_id = '$geschenk_id'");
                $query = mysql_query("UPDATE wi_geschenk SET partner_id = '$user_wichtel_id' WHERE geschenk_id = '$geschenk_id'");
                mysql_close();

                #Geschenk- und Wichteldaten anzeigen
                echo <<<EINTRAG
                <table border="0">
                        <tr><td><b>Geschenk-ID:</b> $geschenk_id &nbsp;(Bitte bewahre die Geschenk-ID gut auf, du musst sie sp&auml;ter auf ds Paket schreiben!)</td></tr>
                        <tr><td><b>Nick:</b> $nick</td></tr>
                        <tr><td><b>Name:</b> $name</td></tr>
                        <tr><td><b>Adresse:</b> $adrzusatz $adresse, $plz $ort, $land</td></tr>
                        <tr><td><b>Beschreibung:</b><br>$beschreibung</td></tr>
                        <tr><td><b>Schwierigkeit:</b> $level</td></tr>
                        <tr><td><b>Kategorie:</b> $art</td></tr>
                        <tr><td><b>Notizen:</b><br>$notizen</td></tr>
                </table>
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
} //function senden()

?>

<p><a href="index.php">Zur&uuml;ck zur Startseite</a></p>
</td></tr></table>
</div>
</body>
</html>
