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

# zeige nur für admin und für partner

$un=$user->data['username'];
if ($un=="Anonymous") $user_id=0;

$wichtel_id = $get['wichtel_id'];

$db = mysql_connect($dbsrv,$dbuser,$dbpasswd);
if (!$db) {
  die("Datebank verbindung schlug fehl: ". mysql_error());
} else {
  mysql_select_db($dbname);
  $query = "select * from wi_wichtel where wichtel_id = $wichtel_id";
  $result = mysql_query($query);

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
<p><a href="index.php">Zurück zur Startseite</a></p>
</head>

<body>
  <article class="container">
    <header class="head">
    <a href="./index.php"><img src="./img/nostern.gif" border="0" alt=""></a>

    <section class="main">
      <h2>wichtel</h2>

    </section>
    </article>
</body>
</html>
