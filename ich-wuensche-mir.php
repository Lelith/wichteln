<?php
#Beginne Session

session_start();
$post=$_POST;
date_default_timezone_set('Europe/Berlin');

$today=date(YmdHi); //$today="201611081200";

//$today="201511081200";


include("cfg.php");
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

#Pruefe auf Blacklist
$db = mysql_connect($dbsrv,$dbuser,$dbpasswd);
if (!$db) {
  die("Datebank verbindung schlug fehl: ". mysql_error());
} else{
    mysql_select_db($dbname);
    $query = mysql_query("SELECT blacklist_id FROM wi_blacklist WHERE user_id = '$user_id'");
    while ($erg =@ mysql_fetch_array($query)) {
      $blacklist = $erg["blacklist_id"];
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
 elseif ( ($today < $eintragen_start) || ($today > $eintragen_ende) ) {
   header("Location: was-ist-denn-hier-los.php?Grund=zeit_eintragen");
 }
 elseif ( ($blacklist != NULL) ) {
   header("Location: was-ist-denn-hier-los.php?Grund=blacklist");
 }
 elseif ( ($wunsch != NULL) ) {
    header("Location: was-ist-denn-hier-los.php?Grund=schon_wunsche");
  }

?>

<html>
<head>
<meta name="author" content="Systemhexe">
<meta name="debug" content="toxic_garden">
<meta name="organization" content="N&auml;hkromanten">
<meta charset="UTF-8">
<title>Das N&auml;hkromanten Weihnachtswichteln</title>
<base target=_self>
<link href="./wicht.css" rel="stylesheet" type="text/css">

<script type="text/javascript">
function chkFormular () {
  if (document.Eintrag.elements[1].value == "") {
    alert("Du hast leider keine eMail-Adresse eingegeben!");
    document.Eintrag.elements[1].focus();
    return false;
  }
  if (document.Eintrag.elements[1].value.indexOf("@") == -1) {
    alert("Du hast eine falsche eMail-Adresse eingegeben!");
    document.Eintrag.elements[1].focus();
    return false;
  }
  if (document.Eintrag.elements[2].value == "") {
    alert("Du hast leider keinen Namen eingegeben!");
    document.Eintrag.elements[2].focus();
    return false;
  }
  if (document.Eintrag.elements[3].value == "") {
    alert("Du hast leider keine Strasse eingegeben!");
    document.Eintrag.elements[3].focus();
    return false;
  }
  if (document.Eintrag.elements[5].value == "") {
    alert("Du hast leider keine Postleitzahl eingegeben!");
    document.Eintrag.elements[4].focus();
    return false;
  }
  if (document.Eintrag.elements[6].value == "") {
    alert("Du hast leider keinen Ort eingegeben!");
    document.Eintrag.elements[6].focus();
    return false;
  }
  if (document.Eintrag.elements[7].value == "") {
    alert("Du hast leider kein Land eingegeben!");
    document.Eintrag.elements[7].focus();
    return false;
  }
  if (document.Eintrag.elements[8].value == "") {
    alert("Du hast leider keinen Wunsch 1 eingegeben!");
    document.Eintrag.elements[8].focus();
    return false;
  }
  if (document.Eintrag.elements[11].value == "") {
    alert("Du hast leider keinen Wunsch 2 eingegeben!");
    document.Eintrag.elements[11].focus();
    return false;
  }
  if (document.Eintrag.elements[14].value == "") {
    alert("Du hast leider keinen Wunsch 3 eingegeben");
    document.Eintrag.elements[14].focus();
    return false;
  }
}
</script>

</head>

<body>
  <div class="container">
    <a href="./index.php"><img src="./img/nostern.gif" border="0" alt=""></a>
    <?php
    include("static.php");
    include("nav.php");
    include("lanq.php");
    ?>
    <div class="main">

<?php

#Ziehe Variablen aus HTTP_VARS
if(isset($post['datenein'])) $_SESSION["datenein"] = $post['datenein'];

if(isset($post['eintrag'])) eintrag();
elseif(isset($post['check'])) check();
elseif(isset($post['senden'])) senden();
else info();

#Funktionen ausfuehren
function info ()
{
  global $user;

  include('lanq.php');

  #Infoseite anzeigen
  echo "<div><p><h3>Hallo ".$user->data['username']."!</h3></p>";
  echo $eintragen_info;

  #Bestaetigungsformular anzeigen
  echo <<<FORMULAR
    <form action="$PHP_SELF" method="post">
      <p><input type="checkbox" name="eintrag" value="select">&nbsp;&nbsp;Ich habe alles gelesen und bin einverstanden&nbsp;&nbsp;<input type="submit" name="absenden" value="OK"></p>
    </form>
FORMULAR;

} //function info()


function eintrag()
{
  global $user;
  #Variablen vorbereiten
  $datenein = $_SESSION["datenein"];
  $datenein[0] = $user->data['username'];
  if (!$datenein[1]) { $datenein[1] = $user->data['user_email']; }
  if (!$datenein[9]) { $datenein[9] = "Bitte ausw&auml;hlen"; }
  if (!$datenein[10]) { $datenein[10] = "Bitte ausw&auml;hlen"; }
  if (!$datenein[12]) { $datenein[12] = "Bitte ausw&auml;hlen"; }
  if (!$datenein[13]) { $datenein[13] = "Bitte ausw&auml;hlen"; }
  if (!$datenein[15]) { $datenein[15] = "Bitte ausw&auml;hlen"; }
  if (!$datenein[16]) { $datenein[16] = "Bitte ausw&auml;hlen"; }

  #Infotext anzeigen
  echo "<p><b>Hallo ".$user->data['username']."!</b></p>";
  echo $eintragen_hinweis;

    #dateneinformular anzeigen
    echo <<<EINTRAG

      <form action="$PHP_SELF" method="post" onsubmit="return chkFormular()" name="Eintrag">
      <fieldset>
      <legend> Teil 1, deine persönlichen Daten </legend>
        <ul class="flex-outer">
        <li>
          <label>
            Dein Nickname im Forum:
          </label>
          <input type="text" name="datenein[]" size="45" maxlength="100" VALUE="$datenein[0]" readonly>
        </li>
        <li>
          <label>
            Deine eMail-Adresse:
          </label>

          <input type="text" name="datenein[]" size="45" maxlength="100" VALUE="$datenein[1]">
        </li>
        <li>
          <label>
            Dein echter Vor- und Nachname:
          </label>

          <input type="text" name="datenein[]" size="45" maxlength="100" VALUE="$datenein[2]">
        </li>
        <li>
          <label>
            Deine Stra&szlig;e und Hausnummer:
          </label>

          <input type="text" name="datenein[]" size="45" maxlength="100" VALUE="$datenein[3]">
        </li>
        <li>
          <label>
            * Zus&auml;tzliches Adressfeld ("Bei Schulze" o&Auml;) :
          </label>
          <input type="text" name="datenein[]" size="45" maxlength="100" VALUE="$datenein[4]">
        </li>
        <li>
          <label>
            Deine Postleitzahl:
          </label>

          <input type="text" name="datenein[]" size="45" maxlength="100" VALUE="$datenein[5]">
        </li>
        <li>
          <label>
            Dein Wohnort:
          </label>

          <input type="text" name="datenein[]" size="45" maxlength="100" VALUE="$datenein[6]">
        </li>
        <li>
          <label>
            Das Land in dem Du wohnst (=Staat, nicht Bundesland):
          </label>
          <input type="text" name="datenein[]" size="45" maxlength="100" VALUE="$datenein[7]">
        </li>
        </ul>
      </fieldset>
      <div>
        Wenn du eine alternative Adresse angeben willst die dein Wichtel zB nach einem bestimmten Zeitpunkt verwenden soll, dann schreib die bitte <b>nicht</b> noch zus&auml;tzlich in die Adressfelder sondern in das Notizfeld (s.u.). Wenn du unsicher bist ob deine Adressangaben verst&auml;ndlich/machbar sind, kontaktiere bitte vor dem Eintragen die Weihnachtshexe.<br>
      </div>

                <table>
                        <div align="center">
                        <br><b>Teil 2: Deine 3 W&uuml;nsche</b></div>


        <tr>
                <td>
                        Dein Wichtelwunsch Nummer 1
                </td>
                <td>
                        <textarea name="datenein[]" rows="10" cols="35">$datenein[8]</textarea>
                </td>
        </tr>
        <tr>
                <td>
                        Und zwei optionale Zusatzinfos:
                </td>
                <td>
                        * Schwierigkeitsgrad:&nbsp;
                        <select name="datenein[]" size="1">
                                 <option>$datenein[9]</option>
                                 <option>Egal</option>
                                 <option>Kleinigkeit</option>
                                 <option>Mittel</option>
                                 <option>Anspruchsvoll</option>
                        </select><br>
                        * Kategorie:&nbsp;
                        <select name="datenein[]" size="1">
                                <option>$datenein[10]</option>
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
        <tr>
                <td>
                        Dein Wichtelwunsch Nummer 2
                </td>
                <td>
                        <textarea name="datenein[]" rows="10" cols="35">$datenein[11]</textarea>
                </td>
        </tr>
        <tr>
                <td>
                        Und zwei optionale Zusatzinfos:
                </td>
                <td>
                        * Schwierigkeitsgrad:&nbsp;
                        <select name="datenein[]" size="1">
                                 <option>$datenein[12]</option>
                                 <option>Egal</option>
                                 <option>Kleinigkeit</option>
                                 <option>Mittel</option>
                                 <option>Anspruchsvoll</option>
                        </select>&nbsp;&nbsp;<br>
                        * Kategorie:&nbsp;
                        <select name="datenein[]" size="1">
                                <option>$datenein[13]</option>
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
        <tr>
                <td>
                        Dein Wichtelwunsch Nummer 3
                </td>
                <td>
                        <textarea name="datenein[]" rows="10" cols="35">$datenein[14]</textarea>
                </td>
        </tr>
        <tr>
                <td>
                        Und zwei optionale Zusatzinfos:
                </td>
                <td>
                        * Schwierigkeitsgrad:&nbsp;
                        <select name="datenein[]" size="1">
                                 <option>$datenein[15]</option>
                                 <option>Egal</option>
                                 <option>Kleinigkeit</option>
                                 <option>Mittel</option>
                                 <option>Anspruchsvoll</option>
                        </select>&nbsp;&nbsp;<br>
                        * Kategorie:&nbsp;
                        <select name="datenein[]" size="1">
                                <option>$datenein[16]</option>
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
        <tr>
                <td colspan="2">
                        <div align="center"><b><br>Teil 3: Ein paar letzte Infos</b></div>
                </td>
        </tr>
        <tr>
                <td>
Hier ist noch ein Feld in dem du allgemeine Notizen f&uml;r Deinen Wichtel hinterlassen kannst. Schreib bitte deinen Nick oder Namen nicht in dieses Feld, da er sonst dem potentiellen Wichtel zu fr&uuml;h verraten wird.<br>
Die Infos aus diesem Feld werden zu allen deinen W&uml;nschen angezeigt, schreib hier also nur Allgemeines &uuml;ber dich und deine Vorlieben rein. Spezielle Infos zu den einzelnen W&uuml;nschen geh&ouml;ren in die oberen Felder. Hier kannst du erw&auml;hnen was deine generellen Stilvorlieben sind, wie du eingerichtet bist, was du f&uuml;r Hobbies hast, welche Musik du magst, also alles was deinem Wichtel helfen k&ouml;nnte dich und deine W&uuml;nsche besser einzusch&auml;tzen. Au&szlig;erdem deine Ma&szlig;e, Kleidergr&ouml;&szlig;e, Schuhgr&ouml;&szlig;e, Kopfumfang (sofern nicht bereits oben angegeben). <br>
Wenn du Allergien hast gegen Materialien die dein Wichtel eventuell verwenden k&ouml;nnte oder gegen Haustiere oder &auml;hnliches, dann erw&auml;hne das hier bitte auf jeden Fall! Ebenso wenn es besondere Hinweise zu Adressierung oder Versand des Geschenkes gibt oder andere Dinge die dein Wichtel beachten sollte.<br>
Wir k&ouml;nnen im Nachhinein keine Infos weiterleiten, also schreib hier bitte alles rein was wichtig ist.
                </td>
                <td>
                        <textarea name="datenein[]" rows="10" cols="35">$datenein[17]</textarea>
                </td>
        </tr>
        <tr>
                <td colspan="2">
                        <div align="center"><br><b>Und wenn Du mit allem fertig bist: Ab daf&uuml;r!</b><br><input type="submit" name="check" value="Eintragen">&nbsp;&nbsp;&nbsp;<input type="reset" value=" L&ouml;schen "></div>
                </td>
        </tr>
        </table>
        </form><br><br></div>
EINTRAG;

} //function eintrag()

function check()
{
        $datenein = $_SESSION["datenein"];
        global $user;
        include('lanq.php');

        #Infoseite anzeigen
        echo "<p><b>Hallo ".$user->data['username']."!</b></p>";
        echo $eintragen_check;

        $datenein[8] = str_replace("\r\n","<br>",$datenein[8]);
        $datenein[11] = str_replace("\r\n","<br>",$datenein[11]);
        $datenein[14] = str_replace("\r\n","<br>",$datenein[14]);
        $datenein[17] = str_replace("\r\n","<br>",$datenein[17]);
        if ($datenein[9] == "Bitte ausw&auml;hlen") $view9="Egal"; else $view9=$datenein[9];
        if ($datenein[10] == "Bitte ausw&auml;hlen") $view10="Egal"; else $view10=$datenein[10];
        if ($datenein[12] == "Bitte ausw&auml;hlen") $view12="Egal"; else $view12=$datenein[12];
        if ($datenein[13] == "Bitte ausw&auml;hlen") $view13="Egal"; else $view13=$datenein[13];
        if ($datenein[15] == "Bitte ausw&auml;hlen") $view15="Egal"; else $view15=$datenein[15];
        if ($datenein[16] == "Bitte ausw&auml;hlen") $view16="Egal"; else $view16=$datenein[16];

        #datenein zum �berpr�fen ausgeben
        echo <<<EINTRAG
        <form action="$PHP_SELF" method="post" name="Eintrag">
        <table border="0">
        <tr><td width="100">Nick:</td><td>$datenein[0]</td></tr>
        <tr><td>Mail:</td><td>$datenein[1]</td></tr>
        <tr><td>Name:</td><td>$datenein[2]</td></tr>
        <tr><td>Stra&Szlig;e:</td><td>$datenein[3]</td></tr>
        <tr><td>Adresszusatz:</td><td>$datenein[4]</td></tr>
        <tr><td>PLZ:</td><td>$datenein[5]</td></tr>
        <tr><td>Ort:</td><td>$datenein[6]</td></tr>
        <tr><td>Land:</td><td>$datenein[7]</td></tr>
        <tr><td colspan="2"><hr></td></tr>
        <tr><td valign="top">Wunsch 1:</td><td>$datenein[8]</td></tr>
        <tr><td>Anspruch 1:</td><td>$view9</td></tr>
        <tr><td>Bereich 1:</td><td>$view10</td></tr>
        <tr><td colspan="2"><hr></td></tr>
        <tr><td valign="top">Wunsch 2:</td><td>$datenein[11]</td></tr>
        <tr><td>Anspruch 2:</td><td>$view12</td></tr>
        <tr><td>Bereich 2:</td><td>$view13</td></tr>
        <tr><td colspan="2"><hr></td></tr>
        <tr><td valign="top">Wunsch 3:</td><td>$datenein[14]</td></tr>
        <tr><td>Anspruch 3:</td><td>$view15</td></tr>
        <tr><td>Bereich 3:</td><td>$view16</td></tr>
        <tr><td colspan="2"><hr></td></tr>
        <tr><td valign="top">Notizen:</td><td>$datenein[17]</td></tr>
        <tr>
                <td colspan="2">
                        <div align="center"><br><input type="submit" name="senden" value="Best&auml;tigen">&nbsp;&nbsp;&nbsp;<input type="submit" name="eintrag" value=" &auml;ndern "></div>
                </td>
        </tr>
        </table>
        </form>
EINTRAG;

} //check()

function senden() {
  $datenein = $_SESSION["datenein"];
  global $user;
  include('lanq.php');

  #Eingabedaten aus Array ziehen
  $nick = $datenein[0];
  $mail = $datenein[1];
  $name = $datenein[2];
  $adresse = $datenein[3];
  $adrzusatz = $datenein[4];
  $plz = $datenein[5];
  $ort = $datenein[6];
  $land = $datenein[7];
  $notizen = $datenein[17];

  #Wunschdaten aus Array ziehen
  $wunsch1 = $datenein[8];
  $level1 = $datenein[9]; if ($level1 == "Bitte ausw&auml;hlen") $level1 = "Egal";
  $art1 = $datenein[10]; if ($art1 == "Bitte ausw&auml;hlen") $art1 = "Egal";
  $wunsch2 = $datenein[11];
  $level2 = $datenein[12]; if ($level2 == "Bitte ausw&auml;hlen") $level2 = "Egal";
  $art2 = $datenein[13]; if ($art2 == "Bitte ausw&auml;hlen") $art2 = "Egal";
  $wunsch3 = $datenein[14];
  $level3 = $datenein[15]; if ($level3 == "Bitte ausw&auml;hlen") $level3 = "Egal";
  $art3 = $datenein[16]; if ($art3 == "Bitte ausw&auml;hlen") $art3 = "Egal";


  #Daten speichern
  #ueberpruefe, ob Wichtel schon angemeldet
  $wichtel_id = 0;
  $forum_id     = $user->data['user_id'];
  include("cfg.php");
  mysql_connect("localhost",$dbuser,$dbpasswd);
  mysql_select_db($dbname);
  $query = mysql_query("SELECT wichtel_id,plz FROM wi_wichtel WHERE forum_id = '$forum_id'");
  while ($erg =@ mysql_fetch_array($query)) {
    $wichtel_id = $erg["wichtel_id"];
    $test = $erg["plz"];
  } //while ($erg =@ mysql_fetch_array($query))

  #Daten in DB-Schreiben
  if ($wichtel_id == 0) {

          #Schreibe User-Daten fuer neuen Wichtel
          $query = mysql_query("INSERT INTO wi_wichtel (wichtel_id, forum_id, nick, email, name, adresse, adrzusatz, plz, ort, land, notizen) VALUES ('$forum_id', '$forum_id', '$nick', '$mail', '$name', '$adresse', '$adrzusatz', '$plz', '$ort', '$land', '$notizen')");
          #Hole neue User-ID
          $query = mysql_query("SELECT wichtel_id FROM wi_wichtel WHERE forum_id = '$forum_id'");
          while ($erg =@ mysql_fetch_array($query)) {
            $wichtel_id = $erg["wichtel_id"];
          }
  } //if (!$wichtel_id)
  else {
          #Schreibe User-Daten fuer bekannten Wichtel
          $query = mysql_query("UPDATE wi_wichtel SET name = '$name', adresse = '$adresse', adrzusatz = '$adrzusatz', plz = '$plz', ort = '$ort', land = '$land', notizen = '$notizen' WHERE wichtel_id = '$wichtel_id'");
  } //else
  // Bloddy Workaround gegen anonyme Wichtel
  if ($wichtel_id != 0) {
      #Schreibe alle 3 Geschenke
      $query = mysql_query("INSERT INTO wi_geschenk (wichtel_id, beschreibung, level, art) VALUES ('$wichtel_id', '$wunsch1', '$level1', '$art1')");
      $query = mysql_query("INSERT INTO wi_geschenk (wichtel_id, beschreibung, level, art) VALUES ('$wichtel_id', '$wunsch2', '$level2', '$art2')");
      $query = mysql_query("INSERT INTO wi_geschenk (wichtel_id, beschreibung, level, art) VALUES ('$wichtel_id', '$wunsch3', '$level3', '$art3')");
      mysql_close();
      #User-Mail senden
      $mailto = $mail;
      $subject = "Hallo Wichtel".$mail;
      $mail2="ischtar@computerleidenschaft.de";
      $header = "From: Weihnachtshexe <dieverschleierte@web.de>";
      $eintragen_mail = str_replace ("_USERNAME_", $user->data['username'], $eintragen_mail);
      mail($mailto,$subject,$eintragen_mail,$header);
      mail($mail2,$subject,$eintragen_mail,$header);
      #Infotext anzeigen
      echo "<p><b>Hallo ".$user->data['username']."!</b></p>";
      echo $eintragen_ende;
    }
    else {
		    echo $fehler_eintragen;
		}

} //function senden()

?>

<p><a href="index.php" class="main_link">Zur&uuml;ck zur Startseite</a></p>
</div>
</body>
</html>
