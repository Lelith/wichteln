<html>
<head>
<title>N&auml;hkromanten-Wichteln</title>
<meta charset="UTF-8">
<meta name="author" content="Systemhexe">
<link href="wicht.css" rel="stylesheet" type="text/css">
<style type="text/css">
<!--
body {
     margin-right:0px;
     margin-left:0px;
     margin-bottom:0px;
}
-->
</style>
</head>
<body>
<img style="position:absolute;top:0px;left:0px;z-index:-1;" src="noeckel.gif" width="135" height="135" border="0" alt="">
<img style="position:absolute;top:0px;right:0px;z-index:-1;" src="noecker.gif" width="135" height="135" border="0" alt="">
<br><br>
<div align="center">
<br><img src="wichteln.png" width="568" height="480" border="0" alt="">
<div style="width:70%; background: #fff; border-radius: 18px;"><br><br>
wir sind besonders gl&uuml;cklich euch auch dieses Jahr wieder zum Weihnachtswunschwichteln begr&uuml;&szlig;en zu k&ouml;nnen! Um herauszufinden was das Wichteln ist, wie es abl&auml;uft, wie ihr mitmachen k&ouml;nnt und was dabei zu beachten ist schaut bitte in die <a href="und-so-gehts.html" target="_blank">Regeln</a>, dort findet ihr Antworten auf diese und mehr Fragen. Bei aller gebotener Vorfreude, nehmt euch die Zeit das genau durchzulesen, dann l&auml;uft es sp&auml;ter viel entspannter. Um auf dem neusten Stand zu sein, schaut bitte auch rein wenn ihr bereits mitgemacht habt, Danke!<br>
<br>
Wenn einige oder alle Links unten nicht funktionieren liegt das daran, dass diese Funktionen nur zu bestimmten Zeiten aktiv sind. Genauere Infos zum Zeitplan findet ihr in den Regeln.<br>
<br>
Wenn ihr noch Fragen habt, oder wenn das Script doch noch nicht ganz glatt l&auml;uft, k&ouml;nnt ihr diese im Forum (<a target="_blank" href="https://www.naehkromanten.net/forum/viewtopic.php?f=21&t=68495">in diesem Thread</a>) stellen oder euch direkt an die <a target="_blank" href="https://www.naehkromanten.net/forum/memberlist.php?mode=viewprofile&u=10714">Weihnachtshexe</a> wenden.<br>
<br>Vielen Dank an redred und Natron, die das urspr&uuml;ngliche Script geschrieben haben und an Ravna f&uuml;r den Logoentwurf.
<br>Wir w&uuml;nschen Euch viel Spa&szlig; und Freude am Wichteln und eine sch&ouml;ne und kreative Vorweihnachtszeit!<br>
<br>
<b>Eure Weihnachtshexe und das N&auml;hkromanten-Team</b>

<br><br><br><br>
<img src="noherzkleindeko.gif" width="297" height="45" border="0" alt="">
<br><br>
</div>

<br><br>

<?php

include("static.php");

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


if ( $today<$eintragen_start ) {
echo <<<IMGMAP
     <map name="wichtelbutt">
        <area shape="RECT" coords="0,0,90,64" href="und-so-gehts.html"> <!--Regeln-->
     </map>
     <img hspace="50" src="nosternmini.gif" width="45" height="45" border="0" alt="*"><img src="buttons/map01.gif" width="684" height="65" border="0" usemap="#wichtelbutt"><img hspace="50" src="nosternmini.gif" width="45" height="45" border="0" alt="*">
IMGMAP;
} // if ( $today<$eintragen_start) )

elseif ( $today>=$eintragen_start && $today<$anfragen_start ) {
echo <<<IMGMAP
   <map name="wichtelbutt">
        <area shape="RECT" coords="0,0,90,64" href="und-so-gehts.html"> <!--Regeln-->
        <area shape="RECT" coords="107,0,215,64" href="ich-wuensche-mir.php"> <!--Wunsch eintragen-->
   </map>
   <img hspace="50" src="nosternmini.gif" width="45" height="45" border="0" alt="*"><img src="buttons/map02.gif" width="684" height="65" border="0" usemap="#wichtelbutt"><img hspace="50" src="nosternmini.gif" width="45" height="45" border="0" alt="*">
IMGMAP;
} // elseif ( $today>$eintragen_start) && $today<$anfragen_start) )

elseif ( $today>=$anfragen_start && $today<=$eintragen_ende ) {
echo <<<IMGMAP
   <map name="wichtelbutt">
        <area shape="RECT" coords="0,0,90,64" href="und-so-gehts.html"> <!--Regeln-->
        <area shape="RECT" coords="107,0,215,64" href="ich-wuensche-mir.php"> <!--Wunsch eintragen-->
        <area shape="RECT" coords="229,0,338,64" href="ich-will-was-basteln.php"> <!--Wunsch anfragen-->
        <area shape="RECT" coords="351,0,435,64" href="lass-mich-buergen.php"> <!--B�rgen-->
        <area shape="RECT" coords="446,0,555,64" href="ich-habe-was-verschickt.php"> <!--Versand best�tigen-->
        <area shape="RECT" coords="567,0,682,64" href="ich-habe-was-bekommen.php"> <!--Empfang best�tigen-->
   </map>
   <img hspace="50" src="nosternmini.gif" width="45" height="45" border="0" alt="*"><img src="buttons/map03.gif" width="684" height="65" border="0" usemap="#wichtelbutt"><img hspace="50" src="nosternmini.gif" width="45" height="45" border="0" alt="*">
IMGMAP;
} // elseif ( $today>$anfragen_start) && $today<$eintragen_ende) )

elseif ( $today>=$eintragen_ende && $today<=$anfragen_ende ) {
echo <<<IMGMAP
   <map name="wichtelbutt">
        <area shape="RECT" coords="0,0,90,64" href="und-so-gehts.html"> <!--Regeln-->
        <area shape="RECT" coords="229,0,338,64" href="ich-will-was-basteln.php"> <!--Wunsch anfragen-->
        <area shape="RECT" coords="351,0,435,64" href="lass-mich-buergen.php"> <!--B�rgen-->
        <area shape="RECT" coords="446,0,555,64" href="ich-habe-was-verschickt.php"> <!--Versand best�tigen-->
        <area shape="RECT" coords="567,0,682,64" href="ich-habe-was-bekommen.php"> <!--Empfang best�tigen-->
   </map>
   <img hspace="50" src="nosternmini.gif" width="45" height="45" border="0" alt="*"><img src="buttons/map04.gif" width="684" height="65" border="0" usemap="#wichtelbutt"><img hspace="50" src="nosternmini.gif" width="45" height="45" border="0" alt="*">
IMGMAP;
} // elseif ( $today>$eintragen_ende) && $today<$anfragen_ende) )

elseif ( $today>=$anfragen_ende && $today<=$senden_ende ) {
echo <<<IMGMAP
   <map name="wichtelbutt">
        <area shape="RECT" coords="0,0,90,64" href="und-so-gehts.html"> <!--Regeln-->
        <area shape="RECT" coords="446,0,555,64" href="ich-habe-was-verschickt.php"> <!--Versand best�tigen-->
        <area shape="RECT" coords="567,0,682,64" href="ich-habe-was-bekommen.php"> <!--Empfang best�tigen-->
   </map>
   <img hspace="50" src="nosternmini.gif" width="45" height="45" border="0" alt="*"><img src="buttons/map05.gif" width="684" height="65" border="0" usemap="#wichtelbutt"><img hspace="50" src="nosternmini.gif" width="45" height="45" border="0" alt="*">
IMGMAP;
} // elseif ( $today>$anfragen_ende) && $today<$senden_ende) )

elseif ( $today>=$senden_ende && $today<=$empfangen_ende ) {
echo <<<IMGMAP
   <map name="wichtelbutt">
        <area shape="RECT" coords="0,0,90,64" href="und-so-gehts.html"> <!--Regeln-->
        <area shape="RECT" coords="567,0,682,64" href="ich-habe-was-bekommen.php"> <!--Empfang best�tigen-->
   </map>
   <img hspace="50" src="nosternmini.gif" width="45" height="45" border="0" alt="*"><img src="buttons/map06.gif" width="684" height="65" border="0" usemap="#wichtelbutt"><img hspace="50" src="nosternmini.gif" width="45" height="45" border="0" alt="*">
IMGMAP;
} // elseif ( $today>$senden_ende) && $today<$empfangen_ende) )

else {
echo <<<IMGMAP
   <map name="wichtelbutt">
        <area shape="RECT" coords="0,0,90,64" href="und-so-gehts.html"> <!--Regeln-->
   </map>
   <img hspace="50" src="nosternmini.gif" width="45" height="45" border="0" alt="*"><img src="buttons/map01.gif" width="684" height="65" border="0" usemap="#wichtelbutt"><img hspace="50" src="nosternmini.gif" width="45" height="45" border="0" alt="*">
IMGMAP;
} // else

?>


<br><br><br>
<br><br>
</div>
<div style="position:absolute; z-index:-1; right:0px; width:100%;">
<div style="position:relative; z-index:-1;  background:url(nobortegross.gif) repeat-x; height:177px; width:100%;">&nbsp;</div>
<div style="position:relative; z-index:-2; bottom:0px; background: #ffffff; height:200px;">&nbsp;</div>
</div>

</body>
</html>
