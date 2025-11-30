<?php
// deleteVille.php : supprime une ville du fichier global et le fichier individuel

$ville = isset($_GET['ville']) ? $_GET['ville'] : '';

if ($ville === '') {
    die("Ville manquante.");
}

// 1) Supprimer l'entrée dans Villes.xml
$villesPath = __DIR__ . "/data/Villes.xml";
if (file_exists($villesPath)) {
    $doc = new DOMDocument();
    $doc->load($villesPath);
    $xp = new DOMXPath($doc);

    // Toutes les <ville @nom="...">
    $nodes = $xp->query("//ville[@nom='$ville']");
    foreach ($nodes as $node) {
        $node->parentNode->removeChild($node);
    }

    $doc->formatOutput = true;
    $doc->save($villesPath);
}

// 2) Supprimer le fichier individuel
$fileVille = __DIR__ . "/data/" . $ville . ".xml";
if (file_exists($fileVille)) {
    unlink($fileVille);
}

// Retour à l'accueil
header("Location: index.php");
exit;
