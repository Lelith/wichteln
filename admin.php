<?php
#Beginne Session
session_start();
$post=$_POST;
date_default_timezone_set('Europe/Berlin');

$today=date(YmdHi); //$today="201611081200";

//$today="201511081200";


include("cfg.php");
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

# sortiere nicht admins aus

$un=$user->data['username'];
if ($un=="Anonymous") $user_id=0;


$db = mysql_connect($dbsrv,$dbuser,$dbpasswd);
if (!$db) {
  die("Datebankverbindung schlug fehl: ". mysql_error());
} else {
  mysql_select_db($dbname);
  $query1= "SELECT wichtel.wichtel_id, wichtel.nick from wi_wichtel as wichtel, wi_geschenk as geschenk where wichtel.wichtel_id = geschenk.wichtel_id group by wichtel.wichtel_id";

  $result = mysql_query($query1);
  if(!$result){
    $message  = 'Ungültige Abfrage: ' . mysql_error() . "\n";
    $message .= 'Gesamte Abfrage: ' . $query;
    die($message);
  }

  while ($row =@ mysql_fetch_array($result)) {
    $wichtel_id = $row['wichtel_id'];
    $wichtel_nick = $row['nick'];


    $query2 = "SELECT  geschenk.geschenk_id, geschenk.status, geschenk.partner_id from wi_wichtel as wichtel, wi_geschenk as geschenk where geschenk.wichtel_id = '$wichtel_id' group by geschenk_id";

    $res2 = mysql_query($query2);
    if(!$res2){
      $message  = 'Ungültige Abfrage: ' . mysql_error() . "\n";
      $message .= 'Gesamte Abfrage: ' . $query;
      die($message);
    }

    $partner_nick ='';
    while ($row =@ mysql_fetch_array($res2)) {
      $status = $row['status'];
      $curr_status = $row['status'];
      if ($curr_status>0 && $curr_status!=2 ) {
        $status = $row['status'];
        $partner_id = $row['partner_id'];
        $geschenk_id = $row['geschenk_id'];
        $partner_query = "SELECT wichtel.wichtel_id, wichtel.nick from wi_wichtel as wichtel, wi_geschenk as geschenk where wichtel.wichtel_id = '$partner_id' and geschenk.geschenk_id ='$geschenk_id' ";
        $res3 = mysql_query($partner_query);
        if(!$res3){
          $message  = 'Ungültige Abfrage: ' . mysql_error() . "\n";
          $message .= 'Gesamte Abfrage: ' . $query;
          die($message);
        }
        while ($row =@ mysql_fetch_array($res3)) {
          $partner_nick = $row['nick'];
        }
        $wichtelliste[$wichtel_id] = array('wichtel_name'=> $wichtel_nick, 'status' => $status, 'partner_id'=>$partner_id , 'partner_name' => $partner_nick );
      }
      elseif($curr_status == 0){
        $wichtelliste[$wichtel_id] = array('wichtel_name'=> $wichtel_nick, 'status' => $status, 'partner_id'=>'0', 'partner_name' => $partner_nick);
      }
    }
  }
  mysql_close();
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
<p><a href="index.php">Zurück zur Startseite</a></p>
</head>

<body>
  <article class="container">
    <header class="head">
    <a href="./index.php"><img src="./img/nostern.gif" border="0" alt=""></a>

    <section class="main">
      <h2>admin</h2>
      <table>
        <tr>
          <td>Wichtel</td>
          <td>Partner</td>
          <td>Status</td>
        </tr>
        <?php
          foreach($wichtelliste as $wichtel_id => $wichtel_data){
            $wichtel_name = $wichtel_data["wichtel_name"];
            $status = $geschenk_status[$wichtel_data["status"]];
            $partner_name = $wichtel_data["partner_name"];
            $partner_id =$wichtel_data["partner_id"];
            echo <<<EINTRAG
            <tr>
              <td><a href="./wichtel?wichtel_id=$wichtel_id">$wichtel_name</a></td>
              <td><a href="./wichtel?wichtel_id=$partner_id">$partner_name</a></td>
              <td>$status</td>
            </tr>
EINTRAG;
          }
        ?>
    </table>
    </section>
    </article>
</body>
</html>
