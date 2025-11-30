<?php
// ==========================
//  index.php – Accueil + Recherche
// ==========================

// ---------- 1) Charger Config.xml (titre, header, étudiant) ----------
$siteTitle = 'Guide de Voyage';
$siteTagline = 'Votre guide pour explorer le monde.';
$headerBg = 'images/header.jpg';

$etu = [
  'nom' => '',
  'prenom' => '',
  'specialite' => '',
  'section' => '',
  'groupe' => '',
  'mail' => '',
];

$configPath = __DIR__ . '/config/Config.xml';
if (file_exists($configPath)) {
  $config = new DOMDocument();
  $config->load($configPath);

  $xp = new DOMXPath($config);

  // <title>
  $n = $xp->query('/config/title')->item(0);
  if ($n)
    $siteTitle = $n->textContent;

  // <tagline> (si tu veux en ajouter un dans ton Config.xml)
  $n = $xp->query('/config/tagline')->item(0);
  if ($n)
    $siteTagline = $n->textContent;

  // <background>
  $n = $xp->query('/config/background')->item(0);
  if ($n)
    $headerBg = $n->textContent;

  // Données étudiant dans <nav>
  $map = [
    'nom' => 'nom',
    'prenom' => 'prenom',
    'specialite' => 'specialite',
    'section' => 'section',
    'groupe' => 'groupe',
    'mail' => 'mail',
  ];
  foreach ($map as $key => $tag) {
    $n = $xp->query('/config/nav/' . $tag)->item(0);
    if ($n)
      $etu[$key] = $n->textContent;
  }
}

// ---------- 2) Récupérer critères de recherche ----------
$continent = isset($_GET['continent']) ? trim($_GET['continent']) : '';
$pays = isset($_GET['pays']) ? trim($_GET['pays']) : '';
$ville = isset($_GET['ville']) ? trim($_GET['ville']) : '';
$site = isset($_GET['site']) ? trim($_GET['site']) : '';

// ---------- 3) Charger Villes.xml et filtrer ----------
$results = [];
$villesPath = __DIR__ . '/data/Villes.xml';

if (file_exists($villesPath)) {
  $doc = new DOMDocument();
  $doc->load($villesPath);
  $xp = new DOMXPath($doc);

  // --- Find real continent name by ID ---
  function getContinentNameByPaysId($xp, $continentId)
  {
    $res = $xp->query("/recherche/continents/continent[@no='$continentId']");
    if ($res->length > 0) {
      return $res->item(0)->getAttribute("nom");
    }
    return "";
  }

  $results = [];
  $paysNodes = $xp->query('/recherche/pays');

  foreach ($paysNodes as $pNode) {

    $paysNom = $pNode->getAttribute('nom');
    $continentId = $pNode->getAttribute('no');

    // Retrieve continent name automatically
    $continentName = getContinentNameByPaysId($xp, $continentId);

    // ---------- Filter Continent ----------
    $continentMatch = ($continent === '') ||
      stripos($continentName, $continent) === 0;

    if (!$continentMatch)
      continue;

    // ---------- Filter Country ----------
    $paysMatch = ($pays === '') ||
      stripos($paysNom, $pays) === 0;

    if (!$paysMatch)
      continue;

    // ---------- Get cities ----------
    $villeNodes = $xp->query('./villes/ville', $pNode);

    foreach ($villeNodes as $vNode) {

      $villeNom = $vNode->getAttribute('nom');

      // Filter city
      $villeMatch = ($ville === '') ||
        stripos($villeNom, $ville) === 0;

      if (!$villeMatch)
        continue;

      // ---------- Filter sites ----------
      $siteMatch = true;
      if ($site !== '') {
        $siteMatch = false;

        foreach ($xp->query('./sites/site', $vNode) as $sNode) {
          if (stripos($sNode->getAttribute('nom'), $site) === 0) {
            $siteMatch = true;
            break;
          }
        }
      }

      if ($siteMatch) {
        $results[] = [
          'pays' => $paysNom,
          'ville' => $villeNom,
          'continent' => $continentName
        ];
      }
    }
  }
}

?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($siteTitle) ?></title>
  <link rel="stylesheet" href="css/style.css">
</head>

<body>
  <header class="site-header" id="site-header" style="background-image: url('<?= htmlspecialchars($headerBg) ?>');">
    <h1 id="site-title"><?= htmlspecialchars($siteTitle) ?></h1>
    <h2 id="site-tagline"><?= htmlspecialchars($siteTagline) ?></h2>
  </header>

  <div class="container">
    <nav class="sidebar">
      <h2>Étudiant</h2>
      <p><strong>Nom :</strong> <span id="etudiant-nom"><?= htmlspecialchars($etu['nom']) ?></span></p>
      <p><strong>Prénom :</strong> <span id="etudiant-prenom"><?= htmlspecialchars($etu['prenom']) ?></span></p>
      <p><strong>Spécialité :</strong> <span id="etudiant-specialite"><?= htmlspecialchars($etu['specialite']) ?></span>
      </p>
      <p><strong>Section :</strong> <span id="etudiant-section"><?= htmlspecialchars($etu['section']) ?></span></p>
      <p><strong>Groupe :</strong> <span id="etudiant-groupe"><?= htmlspecialchars($etu['groupe']) ?></span></p>
      <p><strong>Mail :</strong> <span id="etudiant-mail"><?= htmlspecialchars($etu['mail']) ?></span></p>
      <p><a href="formulaire.php" class="btn">Ajouter Ville</a></p>
    </nav>

    <section class="main">
      <h2>Recherche</h2>
      <form id="search-form" method="get" action="index.php">
        <div class="search-grid">

          <div class="input-group">
            <input class="floating" type="text" id="continent" name="continent" placeholder=" "
              value="<?= htmlspecialchars($continent) ?>">
            <label for="continent">Continent</label>
          </div>

          <div class="input-group">
            <input class="floating" type="text" id="pays" name="pays" placeholder=" "
              value="<?= htmlspecialchars($pays) ?>">
            <label for="pays">Pays</label>
          </div>

          <div class="input-group">
            <input class="floating" type="text" id="ville" name="ville" placeholder=" "
              value="<?= htmlspecialchars($ville) ?>">
            <label for="ville">Ville</label>
          </div>

          <div class="input-group">
            <input class="floating" type="text" id="site" name="site" placeholder=" "
              value="<?= htmlspecialchars($site) ?>">
            <label for="site">Site</label>
          </div>

        </div>
        <button type="submit">Valider</button>
      </form>

      <div class="results">
        <h3>Résultat de la recherche</h3>
        <ul id="results-list">
          <?php if (empty($results)): ?>
            <li>Aucun résultat.</li>
          <?php else: ?>
            <?php foreach ($results as $r): ?>
              <li>
                <a class="city-link" href="ville.php?nom=<?= urlencode($r['ville']) ?>">
                  <?= htmlspecialchars($r['ville']) ?> (<?= htmlspecialchars($r['pays']) ?>)
                </a>

                <span class="icon-buttons">
                  <!-- Modifier -->
                  <span title="Modifier"
                    onclick="window.location.href='formulaire.php?mode=edit&amp;ville=<?= urlencode($r['ville']) ?>&amp;pays=<?= urlencode($r['pays']) ?>'">
                    ✏️
                  </span>
                  <!-- Supprimer -->
                  <span title="Supprimer"
                    onclick="if(confirm('Supprimer cette ville ?')) window.location.href='deleteVille.php?ville=<?= urlencode($r['ville']) ?>&amp;pays=<?= urlencode($r['pays']) ?>';">
                    🗑️
                  </span>
                </span>
              </li>
            <?php endforeach; ?>
          <?php endif; ?>
        </ul>
      </div>
    </section>
  </div>
</body>

</html>