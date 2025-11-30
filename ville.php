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
  <!-- On garde utils.js pour charger Config.xml et le header -->
  <script src="js/utils.js" defer></script>
</head>

<body>

  <header class="site-header" id="site-header" style="background-image:url('<?= htmlspecialchars($headerBg) ?>')">
    <h1 id="site-title"><?= htmlspecialchars($siteTitle) ?></h1>
    <h2 id="site-tagline"><?= htmlspecialchars($siteTagline) ?></h2>
  </header>


  <section class="main">
    <div class="city-container">

      <?php if (isset($error)): ?>
        <h2>Erreur</h2>
        <p><?= htmlspecialchars($error) ?></p>

      <?php else: ?>

        <h1 class="city-title"><?= htmlspecialchars($villeNom) ?></h1>

        <h3 class="section-title">Descriptif</h3>
        <p><?= nl2br(htmlspecialchars($descriptif)) ?></p>

        <?php if ($sites): ?>
          <h3 class="section-title">Sites à visiter</h3>
          <?php foreach ($sites as $s): ?>
            <div class="site-card">
              <?php if ($s['photo']): ?>
                <img class="city-image" src="<?= htmlspecialchars($s['photo']) ?>" alt="<?= htmlspecialchars($s['nom']) ?>">
              <?php endif; ?>

              <div>
                <strong><?= htmlspecialchars($s['nom']) ?></strong>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($hotels): ?>
          <h3 class="section-title">Hôtels</h3>
          <div class="list-block">
            <ul>
              <?php foreach ($hotels as $h): ?>
                <li><?= htmlspecialchars($h) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <?php if ($restaurants): ?>
          <h3 class="section-title">Restaurants</h3>
          <div class="list-block">
            <ul>
              <?php foreach ($restaurants as $h): ?>
                <li><?= htmlspecialchars($h) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <?php if ($gares): ?>
          <h3 class="section-title">Gares</h3>
          <div class="list-block">
            <ul>
              <?php foreach ($gares as $h): ?>
                <li><?= htmlspecialchars($h) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <?php if ($aeroports): ?>
          <h3 class="section-title">Aéroports</h3>
          <div class="list-block">
            <ul>
              <?php foreach ($aeroports as $h): ?>
                <li><?= htmlspecialchars($h) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <a href="index.php" class="back-btn">🏠 Retour à l'accueil</a>

      <?php endif; ?>

    </div>
  </section>

</body>

</html>