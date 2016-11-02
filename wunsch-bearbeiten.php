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

#Daten aus Datenbank abrufen
$user_id = $user->data['user_id'];
$user_posts = $user->data['user_posts'];

#Pr�fe auf Blacklist
mysql_connect("localhost",$dbuser,$dbpasswd);
mysql_select_db($dbname);
$query = mysql_query("SELECT blacklist_id FROM wi_blacklist WHERE user_id = '$user_id'");
while ($erg =@ mysql_fetch_array($query)) { $blacklist = $erg["blacklist_id"]; }
mysql_close();


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
//$user_posts=100;
#�berpr�fe Rechte
include('static.php');
if ( !$user->data['is_registered'] ) { header("Location: was-ist-denn-hier-los.php?Grund=nicht_eingeloggt"); }
elseif ( $user_posts < $user_min_posts ) { header("Location: was-ist-denn-hier-los.php?Grund=zu_wenig_posts"); }
//elseif ( (($today < $eintragen_start)) || (($today > $eintragen_ende)) ) { header("Location: was-ist-denn-hier-los.php?Grund=zeit_eintragen"); }
elseif ( ($blacklist != NULL) ) { header("Location: was-ist-denn-hier-los.php?Grund=blacklist"); }


?>

<html>
<head>
<meta name="author" content="systemhexe">
<meta name="debug" content="toxic_garden">
<meta name="organization" content="n&auml;hkromanten">
<meta charset="UTF-8">
<title>Das n&auml;hkromanten Weihnachtswichteln</title>
<base target=_self>
<link href="wicht.css" rel="stylesheet" type="text/css">


</head>

<body>

<div style="width:100%; background: #fff; border-radius: 18px;"><br><br>
<div align="center">
<br>
<img src="nostern.gif" width="261" height="261" border="0" alt="*">
<h2>Wunsch-Bearbeitung</h2>
<br><br>
<table width="60%" ><tr><td>

<?php
#Ziehe Variablen aus HTTP_VARS
if(isset($post['datenaen'])) $_SESSION["datenaen"] = $post['datenaen'];

if(isset($post['eintrag'])) eintrag();
elseif(isset($post['check'])) check();
elseif(isset($post['senden'])) senden();
else info();

#Funktionen ausf�hren
function info ()
{
        global $user;

        include('lanq.php');

        #Infoseite anzeigen
        echo "<p><h3>Hallo ".$user->data['username']."!</h3></p>";
        echo $aendern_info;

        #Best�tigungsformular anzeigen
        echo <<<FORMULAR
        <form action="$PHP_SELF" method="post">
                <p><input type="checkbox" name="eintrag" value="select">&nbsp;&nbsp;Ich habe alles gelesen und bin einverstanden&nbsp;&nbsp;<input type="submit" name="absenden" value="OK"></p>
        </form>
FORMULAR;

} //function info()


function eintrag()
{
        global $user;
        include('lanq.php');

        #Infotext anzeigen
        echo "<p><b>Hallo ".$user->data['username']."!</b></p>";
        echo $aendern_info;

        $user_id=$user->data['user_id'];

        include("cfg.php");
        //Nach bem�ngelten W�nschen suchen
		mysql_connect("localhost",$dbuser,$dbpasswd);
		mysql_select_db($dbname);
        $query = mysql_query("SELECT wichtel_id, notizen FROM wi_wichtel WHERE forum_id = '$user_id'");
        while ($erg =@ mysql_fetch_array($query)) {
              $wichtel_id = $erg["wichtel_id"];
              $notizen = $erg["notizen"];
        }

        $query = mysql_query("SELECT * FROM wi_geschenk WHERE status = '5' AND wichtel_id = '$wichtel_id'");
        $wunschanzahl=mysql_num_rows($query); //Anzahl der zu �ndernden W�nsche

        if ($wunschanzahl==0) echo "<br><br><b>Es sind keine W&uuml;nsche von dir zur &Auml;nderung markiert, wenn du trotzdem eine Aufforderung von der Weihnachtswichtel bekommen hast, dann kontaktiere sie bitte noch mal, denn es scheint ein Fehler vorzuliegen!</b>";


        #Variablen vorbereiten
        $datenaen = $_SESSION["datenaen"];
        $user_id = $user->data['user_id'];

#        if (!$datenaen[0]) { //wenn sessiondaten noch nicht gef�llt wurden, trage datenbank-werte ein
           $datenaen[0]=$wunschanzahl;
           $query = mysql_query("SELECT * FROM wi_geschenk WHERE status = '5' AND wichtel_id = '$wichtel_id'");
           while ($erg =@ mysql_fetch_array($query)) {
                 $datenaen[]=$erg["geschenk_id"];
                 $datenaen[]=$erg["beschreibung"];
                 $datenaen[]=$erg["level"];
                 $datenaen[]=$erg["art"];
           }
           $datenaen[13]=$notizen;
#        }



        //�nderungsfelder anzeigen
        if ($datenaen[0]>=1) {
                #datenaenformular anzeigen
                echo <<<EINTRAG
                <form action="$PHP_SELF" method="post" name="Eintrag">
                <table border="0">

                </tr>
                <tr>
                        <td>
                                Wunsch mit der ID $datenaen[1]
                                <input type="hidden" value=$datenaen[0] name="datenaen[0]">
                                <input type="hidden" value=$datenaen[1] name="datenaen[1]">
                        </td>
                        <td>
                                <textarea name="datenaen[2]" rows="10" cols="35">$datenaen[2]</textarea>
                        </td>
                </tr>
                <tr>
                        <td>
                                Und zwei optionale Zusatzinfos:
                        </td>
                        <td>
                                * Schwierigkeitsgrad:&nbsp;
                                <select name="datenaen[3]" size="1">
                                         <option>$datenaen[3]</option>
                                         <option>Egal</option>
                                         <option>Kleinigkeit</option>
                                         <option>Mittel</option>
                                         <option>Anspruchsvoll</option>
                                </select><br>
                                * Kategorie:&nbsp;
                                <select name="datenaen[4]" size="1">
                                        <option>$datenaen[4]</option>
                                        <option>Egal</option>
                                        <option>Kleidung</option>
                                        <option>Tasche/Maeppchen</option>
                                        <option>Strick/Haekelsachen</option>
                                        <option>Haarschmuck/Muetze</option>
                                        <option>Schmuck</option>
                                        <option>Accessoires</option>
                                        <option>Schachtel/Box/Aufbewahrung</option>
                                        <option>Wohnungsdeko/Plueschtier/Kissen</option>
                                        <option>Kladde/Papierwaren/Kalender</option>
                                        <option>Kuechenaccessoires</option>
                                        <option>Kosmetik/Badezimmer</option>
                                </select>
                        </td>
                </tr>
EINTRAG;
        }
        if ($datenaen[0]>=2) {
         echo <<<EINTRAG
         <tr>
                        <td>
                                Wunsch mit der ID $datenaen[5]
                                <input type="hidden" value=$datenaen[5] name="datenaen[5]">
                        </td>
                        <td>
                                <textarea name="datenaen[6]" rows="10" cols="35">$datenaen[6]</textarea>
                        </td>
                </tr>
                <tr>
                        <td>
                                Und zwei optionale Zusatzinfos:
                        </td>
                        <td>
                                * Schwierigkeitsgrad:&nbsp;
                                <select name="datenaen[7]" size="1">
                                         <option>$datenaen[7]</option>
                                         <option>Egal</option>
                                         <option>Kleinigkeit</option>
                                         <option>Mittel</option>
                                         <option>Anspruchsvoll</option>
                                </select><br>
                                * Kategorie:&nbsp;
                                <select name="datenaen[8]" size="1">
                                        <option>$datenaen[8]</option>
                                        <option>Egal</option>
                                        <option>Kleidung</option>
                                        <option>Tasche/Maeppchen</option>
                                        <option>Strick/Haekelsachen</option>
                                        <option>Haarschmuck/Maetze</option>
                                        <option>Schmuck</option>
                                        <option>Accessoires</option>
                                        <option>Schachtel/Box/Aufbewahrung</option>
                                        <option>Wohnungsdeko/Plueschtier/Kissen</option>
                                        <option>Kladde/Papierwaren/Kalender</option>
                                        <option>Kuechenaccessoires</option>
                                        <option>Kosmetik/Badezimmer</option>
                                </select>
                        </td>
                </tr>
EINTRAG;
        }
        if ($datenaen[0]==3) {
                 echo <<<EINTRAG
         <tr>
                        <td>
                                Wunsch mit der ID $datenaen[9]
                                <input type="hidden" value=$datenaen[9] name="datenaen[9]">
                        </td>
                        <td>
                                <textarea name="datenaen[10]" rows="10" cols="35">$datenaen[10]</textarea>
                        </td>
                </tr>
                <tr>
                        <td>
                                Und zwei optionale Zusatzinfos:
                        </td>
                        <td>
                                * Schwierigkeitsgrad:&nbsp;
                                <select name="datenaen[11]" size="1">
                                         <option>$datenaen[11]</option>
                                         <option>Egal</option>
                                         <option>Kleinigkeit</option>
                                         <option>Mittel</option>
                                         <option>Anspruchsvoll</option>
                                </select><br>
                                * Kategorie:&nbsp;
                                <select name="datenaen[12]" size="1">
                                        <option>$datenaen[12]</option>
                                        <option>Egal</option>
                                        <option>Kleidung</option>
                                        <option>Tasche/Maeppchen</option>
                                        <option>Strick/Haekelsachen</option>
                                        <option>Haarschmuck/Maetze</option>
                                        <option>Schmuck</option>
                                        <option>Accessoires</option>
                                        <option>Schachtel/Box/Aufbewahrung</option>
                                        <option>Wohnungsdeko/Plueschtier/Kissen</option>
                                        <option>Kladde/Papierwaren/Kalender</option>
                                        <option>Kuechenaccessoires</option>
                                        <option>Kosmetik/Badezimmer</option>
                                </select>
                        </td>
                </tr>
EINTRAG;
        }

        if ($wunschanzahl!=0) {//nicht anzeigen bei "Es sind keine W�nsche von dir vorhanden"
        echo <<<EINTRAG
                <tr>
                <td>
                Die Notizen musst du nicht &uuml;berarbeiten wenn dich die Weihnachtswichtel nicht explizit dazu aufgefordert hat. Aber wir zeigen sie dir hier mit an, falls du sie deinen ge&auml;nderten Wunschbeschreibungen anpassen m&ouml;chtest.
                </td>
                <td>
                        <textarea name="datenaen[13]" rows="10" cols="35">$datenaen[13]</textarea>
                </td>
        </tr>
        <tr>
                <td colspan="2">
                        <div align="center"><br><b>Und wenn Du mit allem fertig bist: Ab daf&uuml;r!</b><br><input type="submit" name="check" value="Eintragen">&nbsp;&nbsp;&nbsp;<input type="reset" value=" L&ouml;schen "></div>
                </td>
        </tr>
        </table>
        </form>
EINTRAG;
        }



        mysql_close();

} //function eintrag()


function check()
{
        $datenaen = $_SESSION["datenaen"];
        global $user;
        include('lanq.php');

        #Infoseite anzeigen
        echo "<p><b>Hallo ".$user->data['username']."!</b></p>";
        echo $aendern_check;

        if ($datenaen[2]) $datenaen[2] = str_replace("\r\n","<br>",$datenaen[2]);
        if ($datenaen[6]) $datenaen[6] = str_replace("\r\n","<br>",$datenaen[6]);
        if ($datenaen[10]) $datenaen[10] = str_replace("\r\n","<br>",$datenaen[10]);
        if ($datenaen[13]) $datenaen[13] = str_replace("\r\n","<br>",$datenaen[13]);


        #datenaen zum �berpr�fen ausgeben
        echo <<<EINTRAG
        <form action="$PHP_SELF" method="post" name="Eintrag">
        <table border="0">
EINTRAG;
        if ($datenaen[0]>=1) {
                echo <<<EINTRAG1
                <tr><td colspan="2" valign="top"><b>Wunsch $datenaen[1]:</b></td></tr>
                <tr><td colspan="2">$datenaen[2]</td></tr>
                <tr><td><b>Anspruch:</b></td><td>$datenaen[3]</td></tr>
                <tr><td><b>Bereich:</b></td><td>$datenaen[4]</td></tr>
EINTRAG1;
        }
        if ($datenaen[0]>=2) {
                echo <<<EINTRAG2
                <tr><td colspan="2"><hr></td></tr>
                <tr><td colspan="2" valign="top"><b>Wunsch $datenaen[5]:</b></td></tr>
                <tr><td colspan="2">$datenaen[6]</td></tr>
                <tr><td><b>Anspruch:</b></td><td>$datenaen[7]</td></tr>
                <tr><td><b>Bereich:</b></td><td>$datenaen[8]</td></tr>
EINTRAG2;
        }
        if ($datenaen[0]==3) {
                echo <<<EINTRAG3
                <tr><td colspan="2"><hr></td></tr>
                <tr><td colspan="2" valign="top"><b>Wunsch $datenaen[9]:</b></td></tr>
                <tr><td colspan="2">$datenaen[10]</td></tr>
                <tr><td><b>Anspruch:</b></td><td>$datenaen[11]</td></tr>
                <tr><td><b>Bereich:</b></td><td>$datenaen[12]</td></tr>
EINTRAG3;
        }
        echo <<<EINTRAGN
        <tr><td colspan="2"><hr></td></tr>
        <tr><td colspan="2" valign="top"><b>Notizen:</b></td></tr>
        <tr><td colspan="2">$datenaen[13]</td></tr>
        <tr>
        <td colspan="2">
            <div align="center"><br><input type="submit" name="senden" value="Best&auml;tigen">&nbsp;&nbsp;&nbsp;<input type="submit" name="eintrag" value=" &auml;ndern "></div>
        </td>
        </tr>
        </table>
        </form>
EINTRAGN;

} //check()

function senden()
{
        $datenaen = $_SESSION["datenaen"];
        global $user;
        $user_id=$user->data['user_id'];
        $block=0;
        include('lanq.php');

        #Eingabedaten aus Array ziehen
        $notizen = $datenaen[13];

        #Wunschdaten aus Array ziehen
        $id1 = $datenaen[1];
        $wunsch1 = $datenaen[2];
        $level1 = $datenaen[3]; if ($level1 == "Bitte ausw&auml;hlen") $level1 = "Egal";
        $art1 = $datenaen[4]; if ($art1 == "Bitte ausw&auml;hlen") $art1 = "Egal";
        $id2 = $datenaen[5];
        $wunsch2 = $datenaen[6];
        $level2 = $datenaen[7]; if ($level2 == "Bitte ausw&auml;hlen") $level2 = "Egal";
        $art2 = $datenaen[8]; if ($art2 == "Bitte ausw&auml;hlen") $art2 = "Egal";
        $id3 = $datenaen[9];
        $wunsch3 = $datenaen[10];
        $level3 = $datenaen[11]; if ($level3 == "Bitte ausw&auml;hlen") $level3 = "Egal";
        $art3 = $datenaen[12]; if ($art3 == "Bitte ausw&auml;hlen") $art3 = "Egal";

        #Daten in DB-Schreiben

        include("cfg.php");
		mysql_connect("localhost",$dbuser,$dbpasswd);
		mysql_select_db($dbname);

        $query = mysql_query("SELECT wichtel_id FROM wi_wichtel WHERE forum_id = '$user_id'");
        while ($erg =@ mysql_fetch_array($query)) {$wichtel_id = $erg["wichtel_id"];}

        $query = mysql_query("SELECT status FROM wi_geschenk WHERE wichtel_id = '$wichtel_id'");
        while ($erg =@ mysql_fetch_array($query)) { if (($erg["status"] == 1) || ($erg["status"] == 2)) {$block=1;} }

#        if ($datenaen[0]>=1) {
        if (($datenaen[0]>=1) && ($block==0)) {
           $sql="UPDATE wi_geschenk SET beschreibung='$wunsch1', level='$level1', art='$art1', status='4' WHERE wichtel_id='$wichtel_id' AND geschenk_id='$id1'";
           $query = mysql_query($sql);
        }
        elseif (($datenaen[0]>=1) && ($block==1)) {
           $sql="UPDATE wi_geschenk SET beschreibung='$wunsch1', level='$level1', art='$art1', status='2' WHERE wichtel_id='$wichtel_id' AND geschenk_id='$id1'";
           $query = mysql_query($sql);
        }
        if (($datenaen[0]>=2) && ($block==0)) {
#        if ($datenaen[0]>=2) {
           $sql="UPDATE wi_geschenk SET beschreibung='$wunsch2', level='$level2', art='$art2', status='4' WHERE wichtel_id='$wichtel_id' AND geschenk_id='$id2'";
           $query = mysql_query($sql);
        }
        elseif (($datenaen[0]>=2) && ($block==1)) {
           $sql="UPDATE wi_geschenk SET beschreibung='$wunsch2', level='$level2', art='$art2', status='2' WHERE wichtel_id='$wichtel_id' AND geschenk_id='$id2'";
           $query = mysql_query($sql);
        }
        if (($datenaen[0]==3) && ($block==0)) {
#        if ($datenaen[0]==3) {
           $sql="UPDATE wi_geschenk SET beschreibung='$wunsch3', level='$level3', art='$art3', status='4' WHERE wichtel_id='$wichtel_id' AND geschenk_id='$id3'";
           $query = mysql_query($sql);
        }
        elseif (($datenaen[0]>=3) && ($block==1)) {
           $sql="UPDATE wi_geschenk SET beschreibung='$wunsch3', level='$level3', art='$art3', status='2' WHERE wichtel_id='$wichtel_id' AND geschenk_id='$id3'";
           $query = mysql_query($sql);
        }

        $query = mysql_query("UPDATE wi_wichtel SET notizen = '$notizen' WHERE wichtel_id = '$wichtel_id'");

        mysql_close();


        #Infotext anzeigen
        echo "<p><b>Hallo ".$user->data['username']."!</b></p>";
        echo $aendern_ende;


} //function senden()

?>

<p><a href="index.php">Zur&uuml;ck zur Startseite</a></p>
</td></tr></table><br><br></div>
</div>
</body>
</html>
