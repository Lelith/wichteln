<?php
#Beginne Session
session_start();
$post=$_POST;
$get = $_GET;
date_default_timezone_set('Europe/Berlin');

//$today="201511081200";
require_once("cfg.php");
require_once("static.php");
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

$wichtel_id = $get['wichtel_id'];
$geschenk_id = 0;
$allowed = false;

$db = mysql_connect($dbsrv,$dbuser,$dbpasswd);
if (!$db) {
  die("Datebank verbindung schlug fehl: ". mysql_error());
} else {
  mysql_select_db($dbname);

  # zeige nur für admin und für partner
  $partnerquery = "select geschenk_id from wi_geschenk where partner_id = '$user_id'";
  $partner_result = mysql_query($partnerquery);

  if(!$partner_result){
    $message  = 'Ungültige Abfrage: ' . mysql_error() . "\n";
    $message .= 'Gesamte Abfrage: ' . $partnerquery;
    die($message);
  }

  while ($row =@ mysql_fetch_array($partner_result)) {
    $geschenk_id = $row[geschenk_id];
  }

  if ($user_id == $admin || $user_id == $orgawichtel || $geschenk_id > 0) {
    //fetch all the data
    $query = "select * from wi_wichtel where wichtel_id = $wichtel_id";
    $result = mysql_query($query);

    if(!$result){
      $message  = 'Ungültige Abfrage: ' . mysql_error() . "\n";
      $message .= 'Gesamte Abfrage: ' . $query;
      die($message);
    }

    while ($row =@ mysql_fetch_array($result)) {
      $wichteldata = array('username' => $row['nick'], 'name' => $row['name'], 'adresse' => $row['adresse'], 'adrzusatz' => $row['adrzusatz'], 'plz' => $row['plz'], 'ort' => $row['ort'], 'land' => $row['land'], 'notizen' => $row['notizen'] );
    }

    if(geschenk_id > 0){
      $get_geschenk ="select geschenk_id, status, beschreibung from wi_geschenk where geschenk_id ='$geschenk_id'";
    } else {
      $get_geschenk = "select geschenk_id, status, beschreibung from wi_geschenk where wichtel_id ='$wichtel_id'";
    }
    $wuensche = mysql_query($get_geschenk);

    if(!$wuensche){
      $message  = 'Ungültige Abfrage: ' . mysql_error() . "\n";
      $message .= 'Gesamte Abfrage: ' . $get_geschenk;
      die($message);
    }

    while ($row =@ mysql_fetch_array($wuensche)) {
      $beschreibung = $row['beschreibung'];
      $beschreibung2 = substr($beschreibung, 0, 200);
      if (strlen($beschreibung)>200) $beschreibung2=$beschreibung2."...";
      $status = $geschenk_status[$row['status']];
      $wunschliste[$row['geschenk_id']] = array("beschreibung" => $row['beschreibung'], "status" => $row['status']);
    }
  } else {
    header("Location: was-ist-denn-hier-los.php?Grund=admin");
  }


  //suche wichtelwunsche und linke alle fuer admin oder einer fuer wichtelpartner


  mysql_close();

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
    <header class="head">
    <a href="./index.php"><img src="./img/nostern.gif" border="0" alt=""></a>

    <section class="main">
      <p><a href="index.php">Zurück zur Startseite</a></p>
      <h2>Wichtelinformationen</h2>
      <div class="infobox">

      <?php
      echo <<<EINTRAG
        <p><b>Usernickname:</b> $wichteldata[username]</p>
        <p><b>Adresse:</b><br>
        $wichteldata[name]<br>
        $wichteldata[adresse]</br>
        $wichteldata[adrzusatz]</br>
        $wichteldata[plz] $wichteldata[ort]</br>
        $wichteldata[land]</br>
        </p>
        <p> <b>Weitere Informationen</b> <br>
        $wichteldata[notizen]</br>
        </p>
EINTRAG;
      ?>
      </div>
      <div class="infobox">
        <h2>Wichtelwunsch</h2>
        <?php
        foreach($wunschliste as $i => $wunsch) {
          $beschreibung = $wunsch["beschreibung"];
          $status = $geschenk_status[$wunsch["status"]];
          echo <<<WUNSCH
          <p><a href="./wunsch.php?wunsch_id=$i">$beschreibung</a> Status: $status </p>
WUNSCH;
        }
        ?>
      </div>
    </section>
    </article>
</body>
</html>
