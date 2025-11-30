<?php
// ======================================
// formulaire.php — Ajout / Modification d’une ville
// ======================================
require_once __DIR__ . "/php/validate.php";

$mode = isset($_GET['mode']) ? $_GET['mode'] : 'create';
$villeNom = isset($_GET['ville']) ? $_GET['ville'] : '';
$paysNom = isset($_GET['pays']) ? $_GET['pays'] : '';
$continentNom = isset($_GET['continent']) ? $_GET['continent'] : '';

$data = [
    'ville' => '',
    'pays' => '',
    'continent' => '',
    'descriptif' => '',
    'sites' => [],
    'hotels' => [],
    'restaurants' => [],
    'gares' => [],
    'aeroports' => []
];


// ======================================================================
// 🔄 MODE MODIFICATION — charger data/<Ville>.xml
// ======================================================================
if ($mode === 'edit' && $villeNom !== '') {
    $filePath = __DIR__ . "/data/" . $villeNom . ".xml";

    if (file_exists($filePath)) {
        $doc = new DOMDocument();
        $doc->load($filePath);
        $xp = new DOMXPath($doc);

        $data['ville'] = $villeNom;
        $data['pays'] = $paysNom;
        $data['continent'] = $continentNom;

        // Descriptif
        $n = $xp->query('/ville/descriptif')->item(0);
        if ($n)
            $data['descriptif'] = $n->textContent;

        // Sites
        foreach ($xp->query('/ville/sites/site') as $s) {
            $data['sites'][] = [
                'nom' => $s->getAttribute('nom'),
                'photo' => $s->getAttribute('photo')
            ];
        }

        // Hotels
        foreach ($xp->query('/ville/hotels/hotel') as $s) {
            $data['hotels'][] = $s->textContent;
        }
        foreach ($xp->query('/ville/restaurants/restaurant') as $s) {
            $data['restaurants'][] = $s->textContent;
        }
        foreach ($xp->query('/ville/gares/gare') as $s) {
            $data['gares'][] = $s->textContent;
        }
        foreach ($xp->query('/ville/aeroports/aeroport') as $s) {
            $data['aeroports'][] = $s->textContent;
        }
    }
}


// ======================================================================
// 📌 SAUVEGARDE — quand on clique “Enregistrer”
// ======================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $ville = trim($_POST['ville']);
    $pays = trim($_POST['pays']);
    $continent = trim($_POST['continent']);
    $descriptif = trim($_POST['descriptif']);

    // textarea → tableaux
    $sitesLines = array_filter(array_map('trim', explode("\n", $_POST['sites'])));
    $hotels = array_filter(array_map('trim', explode("\n", $_POST['hotels'])));
    $restaurants = array_filter(array_map('trim', explode("\n", $_POST['restaurants'])));
    $gares = array_filter(array_map('trim', explode("\n", $_POST['gares'])));
    $aeroports = array_filter(array_map('trim', explode("\n", $_POST['aeroports'])));

    // ======================================================================
    // 1) Créer le fichier individuel data/Ville.xml
    // ======================================================================
    $doc = new DOMDocument('1.0', 'UTF-8');
    $doc->formatOutput = true;

    $v = $doc->createElement('ville');
    $v->setAttribute('nom', $ville);
    $doc->appendChild($v);

    // Descriptif
    $desc = $doc->createElement('descriptif', $descriptif);
    $v->appendChild($desc);

    // Sites
    $sitesNode = $doc->createElement('sites');
    foreach ($sitesLines as $line) {
        $parts = array_map('trim', explode('|', $line));
        $sNom = $parts[0] ?? '';
        $sPhoto = $parts[1] ?? '';
        $siteEl = $doc->createElement('site');
        $siteEl->setAttribute('nom', $sNom);
        $siteEl->setAttribute('photo', $sPhoto);
        $sitesNode->appendChild($siteEl);
    }
    $v->appendChild($sitesNode);

    // + Hotels, Restaurants, Gares, Aéroports
    function addBlock($doc, $parent, $tagParent, $tagChild, $list)
    {
        $p = $doc->createElement($tagParent);
        foreach ($list as $item) {
            $p->appendChild($doc->createElement($tagChild, $item));
        }
        $parent->appendChild($p);
    }

    addBlock($doc, $v, 'hotels', 'hotel', $hotels);
    addBlock($doc, $v, 'restaurants', 'restaurant', $restaurants);
    addBlock($doc, $v, 'gares', 'gare', $gares);
    addBlock($doc, $v, 'aeroports', 'aeroport', $aeroports);

    // Sauvegarde fichier individuel
    $doc->save(__DIR__ . "/data/" . $ville . ".xml");

    // =========================
// Validate Ville.xsd
// =========================
    $villeXsd = __DIR__ . "/xsd/Ville.xsd";
    $villeXmlPath = __DIR__ . "/data/" . $ville . ".xml";

    if (!validateXmlWithXsd($villeXmlPath, $villeXsd)) {
        die("<h3 style='color:red'>❌ Le fichier $ville.xml ne respecte pas Ville.xsd</h3>");
    }


    // ======================================================================
// 2) Mise à jour du fichier Villes.xml
// ======================================================================
    $vPath = __DIR__ . "/data/Villes.xml";

    if (file_exists($vPath)) {
        $vd = new DOMDocument();
        $vd->load($vPath);
        $xp = new DOMXPath($vd);
        $root = $vd->documentElement;
    } else {
        $vd = new DOMDocument('1.0', 'UTF-8');
        $root = $vd->createElement('recherche');
        $vd->appendChild($root);

        // Ajoute <continents> obligatoire
        $root->appendChild($vd->createElement('continents'));
    }

    $xp = new DOMXPath($vd);

    /* ======================================================
       1) TROUVER ou CRÉER le CONTINENT
       ====================================================== */

    $continentNode = null;

    foreach ($xp->query("/recherche/continents/continent") as $c) {
        if (strtolower($c->getAttribute("nom")) === strtolower($continent)) {
            $continentNode = $c;
            break;
        }
    }

    if (!$continentNode) {
        // Trouver ID max existant
        $maxId = 0;
        foreach ($xp->query("/recherche/continents/continent") as $c) {
            $id = intval($c->getAttribute("no"));
            if ($id > $maxId)
                $maxId = $id;
        }

        $continentNode = $vd->createElement("continent");
        $continentNode->setAttribute("nom", $continent);
        $continentNode->setAttribute("no", $maxId + 1);

        $vd->getElementsByTagName("continents")[0]->appendChild($continentNode);
    }

    $continentId = $continentNode->getAttribute("no");


    /* ======================================================
       2) TROUVER ou CRÉER le PAYS
       pays.no = continent.no (OBLIGATOIRE selon l'énoncé)
       ====================================================== */

    $pNode = null;

    foreach ($xp->query("/recherche/pays") as $p) {
        if (strtolower($p->getAttribute("nom")) === strtolower($pays)) {
            $pNode = $p;
            break;
        }
    }

    if (!$pNode) {
        $pNode = $vd->createElement("pays");
        $pNode->setAttribute("nom", $pays);
        $pNode->setAttribute("no", $continentId); // ✔ lien continent/pays

        $pNode->appendChild($vd->createElement("villes"));
        $root->appendChild($pNode);
    } else {
        // ✔ correction de l'ID si nécessaire
        if ($pNode->getAttribute("no") != $continentId) {
            $pNode->setAttribute("no", $continentId);
        }
    }

    $villesNode = $xp->query("./villes", $pNode)->item(0);


    /* ======================================================
       3) AJOUT / MISE À JOUR DE LA VILLE
       ====================================================== */

    foreach ($xp->query("./ville", $villesNode) as $old) {
        if ($old->getAttribute("nom") === $ville) {
            $villesNode->removeChild($old);
        }
    }

    $vShort = $vd->createElement("ville");
    $vShort->setAttribute("nom", $ville);

    $sitesEl = $vd->createElement("sites");
    foreach ($sitesLines as $line) {
        $parts = array_map('trim', explode('|', $line));
        $sNom = $parts[0] ?? '';
        $sPhoto = $parts[1] ?? '';

        $sEl = $vd->createElement("site");
        $sEl->setAttribute("nom", $sNom);
        $sEl->setAttribute("photo", $sPhoto);
        $sitesEl->appendChild($sEl);
    }

    $vShort->appendChild($sitesEl);
    $villesNode->appendChild($vShort);

    $vd->formatOutput = true;
    $vd->save($vPath);

    // =========================
// Validate Villes.xsd
// =========================
    $villesXsd = __DIR__ . "/xsd/Villes.xsd";

    if (!validateXmlWithXsd($vPath, $villesXsd)) {
        die("<h3 style='color:red'>❌ Villes.xml ne respecte pas Villes.xsd</h3>");
    }


    // Redirection vers l'accueil
    header("Location: index.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Gestion ville</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <header class="site-header" style="background-image:url('images/header.jpg')">
        <h1>Gestion de Ville</h1>
    </header>

    <div class="container">
        <nav class="sidebar">
            <h2>Menu</h2>
            <p><a class="btn" href="index.php">Retour</a></p>
        </nav>

        <section class="main">

            <h2><?= $mode === 'edit' ? 'Modifier la ville' : 'Ajouter une ville' ?></h2>

            <form method="post">

                <div class="search-grid">

                    <div class="input-group">
                        <input class="floating" type="text" name="ville" placeholder=" "
                            value="<?= htmlspecialchars($data['ville']) ?>" required>
                        <label>Ville</label>
                    </div>

                    <div class="input-group">
                        <input class="floating" type="text" name="pays" placeholder=" "
                            value="<?= htmlspecialchars($data['pays']) ?>" required>
                        <label>Pays</label>
                    </div>

                    <div class="input-group">
                        <input class="floating" type="text" name="continent" placeholder=" "
                            value="<?= htmlspecialchars($data['continent']) ?>" required>
                        <label>Continent</label>
                    </div>
                </div>

                <div class="input-group">
                    <textarea class="floating" name="descriptif" placeholder=" "
                        required><?= htmlspecialchars($data['descriptif']) ?></textarea>
                    <label>Descriptif</label>
                </div>

                <div class="search-grid">

                    <div class="input-group">
                        <textarea class="floating" name="sites" placeholder=" " required><?php
                        foreach ($data['sites'] as $s)
                            echo $s['nom'] . "|" . $s['photo'] . "\n";
                        ?></textarea>
                        <label>Sites (nom | photo)</label>
                    </div>

                    <div class="input-group">
                        <textarea class="floating" name="hotels" placeholder=" "><?php
                        foreach ($data['hotels'] as $h)
                            echo $h . "\n";
                        ?></textarea>
                        <label>Hôtels</label>
                    </div>

                    <div class="input-group">
                        <textarea class="floating" name="restaurants" placeholder=" "><?php
                        foreach ($data['restaurants'] as $h)
                            echo $h . "\n";
                        ?></textarea>
                        <label>Restaurants</label>
                    </div>

                    <div class="input-group">
                        <textarea class="floating" name="gares" placeholder=" "><?php
                        foreach ($data['gares'] as $h)
                            echo $h . "\n";
                        ?></textarea>
                        <label>Gares</label>
                    </div>

                    <div class="input-group">
                        <textarea class="floating" name="aeroports" placeholder=" "><?php
                        foreach ($data['aeroports'] as $h)
                            echo $h . "\n";
                        ?></textarea>
                        <label>Aéroports</label>
                    </div>

                </div>

                <button type="submit">Enregistrer</button>

            </form>

        </section>

    </div>
</body>

</html>