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
  die("Datebankverbindung schlug fehl: ". mysql_error());
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
  var nameReg = /^[A-Za-z]+$/;
  var numberReg =  /^[0-9]+$/;
  var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;

  var email = $('#emailaddr').val();
  var name = $('#name').val();
  var addr1 = $('#addr1').val();
  var plz = $('#plz').val();
  var ort = $('#ort').val();
  var country = $('#country').val();
  var wish1 = $('#wish1').val();
  var wish2 = $('#wish2').val();
  var wish3 = $('#wish3').val();


  if (email == "") {
    alert("Du hast leider keine E-Mail-Adresse eingegeben!");
    $('#email').focus();
    return false;
  } else if (!emailReg.test(email)) {
    alert("Du hast eine falsche E-Mail-Adresse eingegeben!");
    $('#email').focus();
    return false;
  }


  if (name == "") {
    alert("Du hast leider keinen Namen eingegeben!");
    $('#name').focus();
    return false;
  }

  if (addr1 == "") {
    alert("Du hast leider keine Straße eingegeben!");
    $('#addr1').focus();
    return false;
  }

  if (plz == "") {
    alert("Du hast leider keine Postleitzahl eingegeben!");
    $('#plz').focus();
    return false;
  }

  if (ort == "") {
    alert("Du hast leider keinen Ort eingegeben!");
    $('#ort').focus();
    return false;
  }
  if (country == "") {
    alert("Du hast leider kein Land eingegeben!");
    $('#country').focus();
    return false;
  }
  if (wish1 == "") {
    alert("Du hast leider keinen Wunsch 1 eingegeben!");
    $('#wish1').focus();
    return false;
  }
  if (wish2 == "") {
    alert("Du hast leider keinen Wunsch 2 eingegeben!");
    $('#wish2').focus();
    return false;
  }
  if (wish3 == "") {
    alert("Du hast leider keinen Wunsch 3 eingegeben");
    $('#wish3').focus();
    return false;
  }
}
</script>

</head>

<body>
  <article class="container">
    <header class="head">
      <a href="./index.php"><img src="./img/nostern.gif" border="0" alt=""></a>
    </header>
    <?php include("nav.php");?>
    <section class="main">

<?php
include("static.php");
include("lanq.php");

#Ziehe Variablen aus HTTP_VARS
if(isset($post['datenein'])) $_SESSION["datenein"] = $post['datenein'];

if(isset($post['eintrag'])) eintrag();
elseif(isset($post['check'])) check();
elseif(isset($post['senden'])) senden();
else {eintrag();}



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

    #dateneinformular anzeigen
    echo <<<EINTRAG

      <form action="$PHP_SELF" method="post" onsubmit="return chkFormular()" name="Eintrag">
      <fieldset>
      <legend> Teil 1: Persönliche Daten </legend>
        <ul class="flex-outer">
        <li>
          <label>
            Nickname im Forum:
          </label>
          <input id='nick' type="text" name="datenein[]" size="45" maxlength="100" VALUE="$datenein[0]" readonly>
        </li>
        <li>
          <label for="emailaddr">
            E-Mail-Adresse:
          </label>
          <input id="emailaddr" type="email" name="datenein[]" size="45" maxlength="100" VALUE="$datenein[1]">
        </li>
        <li>
          <label for="name">
            Vor- und Nachname:
          </label>
          <input id="name" type="text" name="datenein[]" size="45" maxlength="100" VALUE="$datenein[2]">
        </li>
        <li>
          <label for="addr1">
            Straße und Hausnummer:
          </label>
          <input id="addr1" type="text" name="datenein[]" size="45" maxlength="100" VALUE="$datenein[3]">
        </li>
        <li>
          <label for="addr2">
            * Zus&auml;tzliches Adressfeld:
          </label>
          <input type="text" name="datenein[]" size="45" maxlength="100" VALUE="$datenein[4]">
        </li>
        <li>
          <label for="plz">
            Postleitzahl:
          </label>
          <input id="plz" type="number" name="datenein[]" size="45" maxlength="100" VALUE="$datenein[5]">
        </li>
        <li>
          <label for="ort">
            Wohnort:
          </label>
          <input id="ort" type="text" name="datenein[]" size="45" maxlength="100" VALUE="$datenein[6]">
        </li>
        <li>
          <label for="country">
            Land:
          </label>
          <input id="country" type="text" name="datenein[]" size="45" maxlength="100" VALUE="$datenein[7]">
        </li>
        </ul>
      </fieldset>
      <div>
        Wenn du eine alternative Adresse angeben willst, die dein Wichtel z.B. nach einem bestimmten Zeitpunkt verwenden soll, dann schreibe sie bitte in das Hinweisfeld unten. Wenn du unsicher bist, ob deine Adressangaben verst&auml;ndlich sind, kontaktiere bitte vor dem Eintragen den Weihnachtswichtel.<br>
      </div>

      <fieldset>
        <legend>Teil 2: Deine drei W&uuml;nsche</legend>
        <ul class="flex-outer">
          <li>
            <label for="wish1"> Wichtelwunsch 1</label>
            <textarea name="datenein[]" id="wish1" rows="10" cols="35">$datenein[8]</textarea>
          </li>
          <li>
            <label>* Schwierigkeitsgrad:</label>
            <select name="datenein[]" size="1">
             <option>$datenein[9]</option>
             <option>Egal</option>
             <option>Kleinigkeit</option>
             <option>Mittel</option>
             <option>Anspruchsvoll</option>
            </select>
          </li>
          <li>
            <label>* Kategorie:</label>
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
            </li>
          </ul>

          <ul class="flex-outer dividing-line">
            <li>
              <label>Wichtelwunsch 2</label>
              <textarea id="wish2" name="datenein[]" rows="10" cols="35">$datenein[11]</textarea>
            </li>
            <li>
              <label>* Schwierigkeitsgrad:</label>
              <select name="datenein[]" size="1">
                <option>$datenein[12]</option>
                <option>Egal</option>
                <option>Kleinigkeit</option>
                 <option>Mittel</option>
                <option>Anspruchsvoll</option>
              </select>
            </li>
            <li>
              <label>* Kategorie:</label>
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
            </li>
          </ul>
          <ul class="flex-outer dividing-line">
            <li>
              <label>Wichtelwunsch 3</label>
              <textarea id="wish3" name="datenein[]" rows="10" cols="35">$datenein[14]</textarea>
            </li>
            <li>
              <label>* Schwierigkeitsgrad:&nbsp;</label>
              <select name="datenein[]" size="1">
              <option>$datenein[15]</option>
              <option>Egal</option>
              <option>Kleinigkeit</option>
              <option>Mittel</option>
              <option>Anspruchsvoll</option>
              </select>
            </li>
            <li>
              <label>* Kategorie:&nbsp;</label>
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
            </li>
        </ul>
      </fieldset>
      <fieldset>
        <legend>Teil 3: Hinweise</legend>
        <p>
          Diese werden zu allen deinen Wünschen angezeigt.<br>
          Hier kannst du erwähnen, was deine generellen Stilvorlieben sind, alles, was deinem Wichtel helfen könnte, deine Wünsche besser einzuschätzen. Außerdem deine Maße, Kleidergröße, Schuhgröße, Kopfumfang (sofern nicht bereits bei den Wünschen angegeben), Allergien gegen Zutaten/Materialen/Haustiere, besondere Hinweise zu Adressierung/Versand, etc.<br>
          Schreib bitte nicht deinen Nick oder Namen in dieses Feld, da er sonst dem potentiellen Wichtel zu frühh verraten wird.<br>
          Wir können im Nachhinein keine Infos weiterleiten, also schreib hier bitte alles rein, was wichtig ist.
        </p>
        <ul class="flex-outer">
          <li>
            <textarea name="datenein[]" rows="10" cols="35">$datenein[17]</textarea>
          </li>
        </ul>
      </fieldset>

      <div>
        <ul class="flex-outer">
          <li>
            <input type="submit" name="check" value="Eintragen">
            <input type="reset" value=" L&ouml;schen ">
          </li>
        </ul>
        </form>
    </div>
EINTRAG;

} //function eintrag()

function check()
{
  $datenein = $_SESSION["datenein"];
  global $user;

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

        #datenein zum ueberpruefen ausgeben
        echo <<<EINTRAG
        <form action="$PHP_SELF" method="post" name="Eintrag">
        <table border="0">
        <tr><td width="100">Nick:</td><td>$datenein[0]</td></tr>
        <tr><td>Mail:</td><td>$datenein[1]</td></tr>
        <tr><td>Name:</td><td>$datenein[2]</td></tr>
        <tr><td>Straße:</td><td>$datenein[3]</td></tr>
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
  include("lanq.php");
  $datenein = $_SESSION["datenein"];
  global $user;

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
  $forum_id   = $user->data['user_id'];
  include("cfg.php");
  $db = mysql_connect($dbsrv,$dbuser,$dbpasswd);
  if (!$db) {
    die("Datebankverbindung schlug fehl: ". mysql_error());
    exit();
  } else {
    mysql_select_db($dbname);

    $query = mysql_query("SELECT wichtel_id, plz FROM wi_wichtel WHERE forum_id = '$forum_id'");
    while ($erg =@ mysql_fetch_array($query)) {
      $wichtel_id = $erg["wichtel_id"];
      $test = $erg["plz"];
    } //while ($erg =@ mysql_fetch_array($query))

    #Daten in DB-Schreiben
    if ($wichtel_id == 0) {
      #Schreibe User-Daten fuer neuen Wichtel
      $query = sprintf("INSERT INTO wi_wichtel (forum_id, nick, email, name, adresse, adrzusatz, plz, ort, land, notizen) VALUES ('$forum_id', '$nick', '$mail', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
        mysql_real_escape_string($name),
        mysql_real_escape_string($adresse),
        mysql_real_escape_string($adrzusatz),
        mysql_real_escape_string($plz),
        mysql_real_escape_string($ort),
        mysql_real_escape_string($land),
        mysql_real_escape_string($notizen)
      );

      $result = mysql_query($query);
      if (!$result) {
          $message  = 'Ungültige Abfrage: ' . mysql_error() . "\n";
          $message .= 'Gesamte Abfrage: ' . $query;
          die($message);
      }
      #Hole neue User-ID
      $query = mysql_query("SELECT wichtel_id FROM wi_wichtel WHERE forum_id = '$forum_id'");
      while ($erg =@ mysql_fetch_array($query)) {
        $wichtel_id = $erg["wichtel_id"];
      }
    } //if (!$wichtel_id)
    else {
      #Schreibe User-Daten fuer bekannten Wichtel
      $query = sprintf("UPDATE wi_wichtel SET name = '$name', adresse = '$adresse', adrzusatz = '$adrzusatz', plz = '$plz', ort = '$ort', land = '$land', notizen = '$notizen' WHERE wichtel_id = '$wichtel_id'");

      $result = mysql_query($query);
      if (!$result) {
          $message  = 'Ungültige Abfrage: ' . mysql_error() . "\n";
          $message .= 'Gesamte Abfrage: ' . $query;
          die($message);
      }
    } //else wichtel mit oder ohne wunsch

    // Bloddy Workaround gegen anonyme Wichtel
    if ($wichtel_id != 0) {
      $wuensche = array (
                1 => array('id' => $wichtel_id, 'wish' => $wunsch1, 'lvl' => $level1, 'art' => $art1),
                2 => array('id' => $wichtel_id, 'wish' => $wunsch2, 'lvl' => $level2, 'art' => $art2),
                3 => array('id' => $wichtel_id, 'wish' => $wunsch3, 'lvl' => $level3, 'art' => $art3)
              );

      foreach ($wuensche as $wunsch ) {
        // Führe Abfrage aus
        $query = sprintf("INSERT INTO wi_geschenk (wichtel_id, beschreibung,  level, art) VALUES (\"$wichtel_id\", '%s','%s', '%s');",
          mysql_real_escape_string($wunsch['wish']),
          mysql_real_escape_string($wunsch['lvl']),
          mysql_real_escape_string($wunsch['art'])
          );
        $result = mysql_query($query);

        // Prüfe Ergebnis
        // Dies zeigt die tatsächliche Abfrage, die an MySQL gesandt wurde und den
        // Fehler. Nützlich bei der Fehlersuche
        if (!$result) {
            $message  = 'Ungültige Abfrage: ' . mysql_error() . "\n";
            $message .= 'Gesamte Abfrage: ' . $query;
            die($message);
        }
      } // foreach eintragen
    } // nicht anonyme wichtel

    mysql_close(); // aktive verbindung schließen

    #User-Mail senden
    $mailto = $mail;
    $subject = "Hallo Wichtel ".$mail;
    $mail2="kri_zilla@yahoo.de";
    $header = "From: Weihnachtswichtel <kri_zilla@yahoo.de>";
    $eintragen_mail = str_replace ("_USERNAME_", $user->data['username'], $eintragen_mail);
    mail($mailto,$subject,$eintragen_mail,$header);
    mail($mail2,$subject,$eintragen_mail,$header);
    #Infotext anzeigen
    include('lanq.php');
    echo "<p><b>Hallo ".$user->data['username']."!</b></p>";
    echo $eintragen_ende;
  }^

} //function senden()

?>

<p><a href="index.php" class="main_link">Zur&uuml;ck zur Startseite</a></p>
</section>
</article>
</body>
</html>
