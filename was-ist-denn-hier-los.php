<?php
$Grund = $_GET['Grund'];
#Beginne Session
session_start();
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

?>

<html>
<head>
<meta name="author" content="Systemhexe">
<meta name="organization" content="N&auml;hkromanten">
<title>Das N&auml;hkromanten Weihnachtswichteln</title>
<meta charset="UTF-8">
<base target=_self>
<link href="wicht.css" rel="stylesheet" type="text/css">
</head>

<body>

<header class="head">
<img src="nostern.gif" width="261" height="261" border="0" alt="*">
<h2 class="page-heading">Hier hat was nicht funktioniert!</h2>
</header>
<section class="main">

<?php
// Besorge Fehlermeldung und Daten
include('static.php');

switch($Grund) {

        case 'nicht_eingeloggt':
                echo "<p style=\"color: #fff;\"><b>Hallo Gast!</b><br><br>F&uuml;r diesen Dienst musst Du im Forum angemeldet sein.<br>Klicke <a href=\"https://www.naehkromanten.net/forum/ucp.php?mode=login\" target=\"_blank\"><font color=\"#FFFFFF\">hier</font></a> um das nachzuholen und versuche es anschlie&szlig;end <a href=\"index.php\"><font color=\"#FFFFFF\">erneut</font></a>.</p>";
                break;

        case 'zu_wenig_posts':
                echo "<p style=\"color: #fff;\"><b>Hallo ".$user->data['username']."!</b><br><br>F&uuml;r diesen Dienst musst Du mindestens ".$user_min_posts." Posts auf Deinem Userkonto haben. Leider hast Du aktuell nur ".$user->data['user_posts'].".<br>Viel Gl&uuml;ck beim n&auml;chsten Mal.</p>";
                break;

        case 'zu_wenig_buerge_posts':
                echo "<p style=\"color: #fff;\"><b>Hallo ".$user->data['username']."!</b><br><br>F&uuml;r diesen Dienst musst Du mindestens ".$buerge_min_posts." Posts auf Deinem Userkonto haben. Leider hast Du aktuell nur ".$user->data['user_posts'].".<br>Viel Gl&uuml;ck beim n&auml;chsten Mal.</p>";
                break;

        case 'zeit_eintragen':
                echo "<p style=\"color: #fff;\"><b>Hallo ".$user->data['username']."!</b><br><br>Dieser Dienst steht leider nur vom 08.11.2015 bis zum 22.11.2015 zur Verf&uuml;gung. Und da heute der ".date("d.m.Y")." ist, ist dieser Dienst leider nicht verf&uuml;gbar.</p>";
                break;

        case 'zeit_anfragen':
                echo "<p style=\"color: #fff;\"><b>Hallo ".$user->data['username']."!</b><br><br>Dieser Dienst steht leider nur vom 15.11.2015 bis zum 29.11.2015 zur Verf&uuml;gung. Und da heute der ".date("d.m.Y")." ist, ist dieser Dienst leider nicht verf&uuml;gbar.</p>";
                break;

        case 'zeit_senden':
                echo "<p style=\"color: #fff;\"><b>Hallo ".$user->data['username']."!</b><br><br>Dieser Dienst steht leider nur vom 15.11.2015 bis zum 13.12.2015 zur Verf&uuml;gung. Und da heute der ".date("d.m.Y")." ist, ist dieser Dienst leider nicht verf&uuml;gbar.</p>";
                break;

        case 'zeit_empfangen':
                echo "<p style=\"color: #fff;\"><b>Hallo ".$user->data['username']."!</b><br><br>Dieser Dienst steht leider nur vom 15.11.2015 bis zum 20.12.2015 zur Verf&uuml;gung. Und da heute der ".date("d.m.Y")." ist, ist dieser Dienst leider nicht verf&uuml;gbar.</p>";
                break;

        case 'zeit_buergen':
                echo "<p style=\"color: #fff;\"><b>Hallo ".$user->data['username']."!</b><br><br>Dieser Dienst steht leider nur vom 15.11.2015 bis zum 29.11.2015 zur Verf&uuml;gung. Und da heute der ".date("d.m.Y")." ist, ist dieser Dienst leider nicht verf&uuml;gbar.</p>";
                break;

        case 'blacklist':
				mysql_connect("localhost",$dbuser,$dbpasswd);
				mysql_select_db($dbname);
                #Hole Grund aus DB
                $user_id=$user->data['user_id'];
                $query = mysql_query("SELECT grund FROM wi_blacklist WHERE user_id = '$user_id'");
                while ($erg =@ mysql_fetch_array($query)) { $grund = $erg["grund"]; }
                mysql_close();
                if ($grund == "AKTIVSPERRE") {echo "<p><b>Hallo ".$user->data['username']."!</b><br><br>Da Du im Forum vom Suche&Biete- und Aktivit&auml;tenbereich dauerhaft gebannt bist, kannst Du auch nicht am Wichteln teilnehmen. Wenn Du der Meinung bist, zu Unrecht gebannt worden zu sein, wende dich bitte an die Weihnachtshexe.</p>";}
                elseif ($grund == "WICHTELN") {echo "<p><b>Hallo ".$user->data['username']."!</b><br><br>Du hast beim letztj&auml;hrigen Wichteln die Deadline zum Verschicken nicht eingehalten und bist daher f&uuml;r das diesj&auml;hrige Wichteln gesperrt. Danach erlischt die Sperre automatisch. Wenn Du der Meinung bist, zu Unrecht gesperrt worden zu sein, wende dich bitte an die Weihnachtshexe.</p>";}
                elseif ($grund == "TEMP") {echo "<p><b>Hallo ".$user->data['username']."!</b><br><br>Du bist im Forum im Aktivit&auml;tenbereich vorr&uuml;bergehend gesperrt und solange diese Sperre gilt bist du auch vom Wichteln ausgeschlossen. Wenn Du der Meinung bist, zu Unrecht gesperrt worden zu sein, wende dich bitte an die Weihnachtshexe.</p>";}
                else echo "<p><b>Hallo ".$user->data['username']."!</b><br><br>Du bist f&uuml;r das Wichteln gesperrt, aber wir k&ouml;nnen dir leider momentan nicht sagen warum. Um den Grund zu erfahren oder wenn du denkst die Sperre ist nicht berechtigt wende dich bitte an die Weihnachtshexe.</p>";
                break;

        case 'schon_wunsche':
               echo "<p style=\"color: #fff;\"><b>Hallo ".$user->data['username']."!</b><br><br>Es sind bereits W&uuml;nsche von Dir in der Datenbank vorhanden. Wenn Du meinst, dass es sich hier um einen Fehler handelt, dann kontaktiere bitte die Weihnachtshexe.</p>";
               break;

        case 'nur_ein_geschenk':
                echo "<p style=\"color: #fff;\"><b>Hallo ".$user->data['username']."!</b><br><br>Du hast bereits ein Geschenk ausgew&uuml;hlt. Bevor du ein weiteres aussuchen kannst musst du das andere erst verschickt haben.</p>";
                break;

        case 'kein_admin':
                echo "<p style=\"color: #fff;\"><b>Hallo ".$user->data['username']."!</b><br><br>Du bist leider weder der Admin, noch der Weihnachtshexe. Daher hast Du hier leider keinen Zutritt.</p>";
                break;

        default:
                //Konnte keinen Fehler finden
                echo "<p style=\"color: #fff;\">Wir konnten leider keinen bestimmten Fehler finden. Am besten probierst Du es einfach noch einmal. <a href=\"index.php\"><font class=\"main_link\">Hier gehts zum Start zur&uuml;ck.</font></a></p>";
                break;
} //switch($Grund)

?>
<p><a href="index.php" class="main_link">Zur&uumlck zur Startseite</a></p>

</section>
</body>
</html>
