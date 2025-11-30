<?php
// ville_pdf.php

$villeNom = isset($_GET['nom']) ? $_GET['nom'] : '';

if ($villeNom === '') {
    die("Aucune ville fournie.");
}

$xmlPath = __DIR__ . "/data/" . $villeNom . ".xml";
$xslPath = __DIR__ . "/xsl/ville.xsl";

if (!file_exists($xmlPath)) {
    die("Fichier XML introuvable : $xmlPath");
}
if (!file_exists($xslPath)) {
    die("Fichier XSL introuvable : $xslPath");
}

// Charger XML
$xml = new DOMDocument();
$xml->load($xmlPath);

// Charger XSL
$xsl = new DOMDocument();
$xsl->load($xslPath);

// Appliquer XSLT
$proc = new XSLTProcessor();
$proc->importStylesheet($xsl);

$html = $proc->transformToXML($xml);

// Envoyer au navigateur
header("Content-Type: text/html; charset=UTF-8");
echo $html;
