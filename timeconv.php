<html>
<head>
<meta name="author" content="Systemhexe">
<meta name="organization" content="N&auml;hkromanten">
<title>Time Conversion</title>
<style type="text/css"><!--a {text-decoration: none;}--></style>
<base target=_self>
</head>

<h1>N&auml;hkromanten Wichteln - Timeconversion</h1>

<?php
//echo "\$debug_start=".strtotime("20131101, 0000")."; //01.11.2013<br>"; // Start Debug
// timestamps ... + 31536000 ist + 1 jahr
echo "\$eintragen_start=".strtotime("20152210, 1000")."; //08.11.2015<br>"; // Start W�nsche
echo "\$eintragen_ende=".strtotime("20151122, 2359")."; //22.11.2015<br>"; // Ende W�nsche

echo "\$anfragen_start=".strtotime("20151118, 1200")."; //18.11.2015<br>"; // Start Anfragen
echo "\$anfragen_ende=".strtotime("20151129, 2359")."; //29.11.2015<br>"; // Fr�hstes Ende Anfragen
echo "\$anfragen_ende=".strtotime("20151206, 2359")."; //06.12.2015<br>"; // Sp�testes Ende Anfragen

echo "\$senden_start=".strtotime("20151115, 0000")."; //15.11.2015<br>"; // Start Senden
echo "\$senden_ende=".strtotime("20151213, 2359")."; //13.12.2015<br>"; // Ende Senden

echo "\$empfangen_start=".strtotime("20151115, 0000")."; //15.11.2015<br>"; // Start Empfangen
echo "\$empfangen_ende=".strtotime("20151222, 2359")."; //22.12.2015<br>"; // Ende Empfangen

echo "\$buergen_start=".strtotime("20151115, 0000")."; //15.11.2015<br>"; // Start B�rgen
echo "\$buergen_ende=".strtotime("20151129, 2359")."; //29.11.2015<br>"; // Ende B�rgen

?>

</body>
</html>