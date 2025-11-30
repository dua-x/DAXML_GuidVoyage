<?php
header("Content-Type: application/json; charset=UTF-8");

// Récupérer les données envoyées en JSON
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data["ville"])) {
    echo json_encode(["status" => "error", "message" => "Invalid data"]);
    exit;
}

$ville = $data["ville"];
$pays = $data["pays"];
$continent = $data["continent"];
$descriptif = $data["descriptif"];
$sites = $data["sites"];
$hotels = $data["hotels"];
$restaurants = $data["restaurants"];
$gares = $data["gares"];
$aeroports = $data["aeroports"];

// ---------------------------------------------------------------------------------
// 1. CRÉER LE FICHIER NomVille.xml
// ---------------------------------------------------------------------------------

$villeXml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$villeXml .= "<ville nom=\"$ville\">\n";
$villeXml .= "  <descriptif>$descriptif</descriptif>\n";
$villeXml .= "  <sites>\n";

foreach ($sites as $s) {
    $nom = htmlspecialchars($s["nom"]);
    $photo = htmlspecialchars($s["photo"]);
    $villeXml .= "    <site nom=\"$nom\" photo=\"$photo\" />\n";
}

$villeXml .= "  </sites>\n";

function addBlock($tagParent, $tagChild, $list)
{
    $xml = "  <$tagParent>\n";
    foreach ($list as $item) {
        $xml .= "    <$tagChild>$item</$tagChild>\n";
    }
    $xml .= "  </$tagParent>\n";
    return $xml;
}

$villeXml .= addBlock("hotels", "hotel", $hotels);
$villeXml .= addBlock("restaurants", "restaurant", $restaurants);
$villeXml .= addBlock("gares", "gare", $gares);
$villeXml .= addBlock("aeroports", "aeroport", $aeroports);

$villeXml .= "</ville>";

file_put_contents("data/$ville.xml", $villeXml);

// ---------------------------------------------------------------------------------
// 2. METTRE À JOUR Villes.xml
// ---------------------------------------------------------------------------------

$villesFile = "data/Villes.xml";

if (!file_exists($villesFile)) {
    file_put_contents($villesFile, "<?xml version=\"1.0\"?><recherche><pays></pays></recherche>");
}

$xml = simplexml_load_file($villesFile);

// Chercher le pays existant
$paysNode = null;

foreach ($xml->pays as $p) {
    if ((string) $p["nom"] === $pays)
        $paysNode = $p;
}

if (!$paysNode) {
    $paysNode = $xml->addChild("pays");
    $paysNode->addAttribute("nom", $pays);
    $paysNode->addChild("villes");
}

$villesList = $paysNode->villes;

// Supprimer ancienne entrée si existe
foreach ($villesList->ville as $v) {
    if ((string) $v["nom"] === $ville) {
        unset($v[0]);
    }
}

// Ajouter nouvelle version courte
$villeShort = $villesList->addChild("ville");
$villeShort->addAttribute("nom", $ville);
$villeShort->addAttribute("continent", $continent);

$sitesNode = $villeShort->addChild("sites");
foreach ($sites as $s) {
    $siteNode = $sitesNode->addChild("site");
    $siteNode->addAttribute("nom", $s["nom"]);
    $siteNode->addAttribute("photo", $s["photo"]);
}

$xml->asXML($villesFile);

echo json_encode(["status" => "success"]);
?>