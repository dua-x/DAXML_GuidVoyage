<?php

// ==========================
// Charger Config.xml pour header et étudiant
// ==========================
$siteTitle = 'Guide de Voyage';
$siteTagline = 'Détail de la ville';
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

  $n = $xp->query('/config/title')->item(0);
  if ($n)
    $siteTitle = $n->textContent;

  $n = $xp->query('/config/tagline')->item(0);
  if ($n)
    $siteTagline = $n->textContent;

  $n = $xp->query('/config/background')->item(0);
  if ($n)
    $headerBg = $n->textContent;

  $tags = ['nom', 'prenom', 'specialite', 'section', 'groupe', 'mail'];
  foreach ($tags as $t) {
    $n = $xp->query('/config/nav/' . $t)->item(0);
    if ($n)
      $etu[$t] = $n->textContent;
  }
}

// ville.php : affiche le détail d'une ville à partir de data/<Ville>.xml

$villeNom = isset($_GET['nom']) ? $_GET['nom'] : '';

if ($villeNom === '') {
  $error = "Aucune ville demandée.";
} else {
  $filePath = __DIR__ . "/data/" . $villeNom . ".xml";
  if (!file_exists($filePath)) {
    $error = "Le fichier XML de la ville « $villeNom » n'existe pas.";
  } else {
    $doc = new DOMDocument();
    $doc->load($filePath);
    $xp = new DOMXPath($doc);

    // Descriptif
    $descriptif = '';
    $n = $xp->query('/ville/descriptif')->item(0);
    if ($n)
      $descriptif = $n->textContent;

    // Sites
    $sites = [];
    foreach ($xp->query('/ville/sites/site') as $s) {
      $sites[] = [
        'nom' => $s->getAttribute('nom'),
        'photo' => $s->getAttribute('photo')
      ];
    }

    // Fonction utilitaire
    $hotels = [];
    foreach ($xp->query('/ville/hotels/hotel') as $h) {
      $hotels[] = $h->textContent;
    }
    $restaurants = [];
    foreach ($xp->query('/ville/restaurants/restaurant') as $h) {
      $restaurants[] = $h->textContent;
    }
    $gares = [];
    foreach ($xp->query('/ville/gares/gare') as $h) {
      $gares[] = $h->textContent;
    }
    $aeroports = [];
    foreach ($xp->query('/ville/aeroports/aeroport') as $h) {
      $aeroports[] = $h->textContent;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Détail ville - <?= htmlspecialchars($villeNom) ?></title>
  <link rel="stylesheet" href="css/style.css">
  <script src="js/utils.js" defer></script>
</head>

<body>

  <!-- HEADER PRINCIPAL (Général du site) -->
  <header class="site-header" id="site-header" style="background-image:url('<?= htmlspecialchars($headerBg) ?>')">
    <h1 id="site-title"><?= htmlspecialchars($siteTitle) ?></h1>
    <h2 id="site-tagline"><?= htmlspecialchars($siteTagline) ?></h2>
  </header>

  <!-- CONTENU VILLE -->
  <div class="city-page">

    <header class="city-header">
      <h1><?= htmlspecialchars($villeNom) ?></h1>
      <p>Informations détaillées</p>
    </header>

    <!-- IMAGE PRINCIPALE SI DISPONIBLE -->
    <?php if (!empty($sites[0]['photo'])): ?>
      <img class="city-main-image" src="<?= htmlspecialchars($sites[0]['photo']) ?>"
        alt="<?= htmlspecialchars($villeNom) ?>">
    <?php endif; ?>

    <!-- DESCRIPTIF -->
    <div class="section-box">
      <h2>Descriptif</h2>
      <p><?= nl2br(htmlspecialchars($descriptif)) ?></p>
    </div>

    <!-- SITES -->
    <?php if ($sites): ?>
      <div class="section-box">
        <h2>Sites à visiter</h2>
        <div class="site-grid">
          <?php foreach ($sites as $s): ?>
            <div class="site-card2">
              <?php if ($s['photo']): ?>
                <img src="<?= htmlspecialchars($s['photo']) ?>" alt="">
              <?php endif; ?>
              <div><?= htmlspecialchars($s['nom']) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- HOTELS -->
    <?php if ($hotels): ?>
      <div class="section-box">
        <h2>Hôtels</h2>
        <ul class="simple-list2">
          <?php foreach ($hotels as $h): ?>
            <li><?= htmlspecialchars($h) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <!-- RESTAURANTS -->
    <?php if ($restaurants): ?>
      <div class="section-box">
        <h2>Restaurants</h2>
        <ul class="simple-list2">
          <?php foreach ($restaurants as $h): ?>
            <li><?= htmlspecialchars($h) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <!-- GARES -->
    <?php if ($gares): ?>
      <div class="section-box">
        <h2>Gares</h2>
        <ul class="simple-list2">
          <?php foreach ($gares as $h): ?>
            <li><?= htmlspecialchars($h) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <!-- AEROPORTS -->
    <?php if ($aeroports): ?>
      <div class="section-box">
        <h2>Aéroports</h2>
        <ul class="simple-list2">
          <?php foreach ($aeroports as $h): ?>
            <li><?= htmlspecialchars($h) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <!-- ACTIONS -->
    <div class="city-actions2">
      <a href="index.php" class="btn-light2">↩ Retour</a>

      <form method="get" action="ville_pdf.php">
        <input type="hidden" name="nom" value="<?= htmlspecialchars($villeNom) ?>">
        <button class="btn-primary2">📄 Export PDF</button>
      </form>
    </div>

  </div>

</body>

</html>