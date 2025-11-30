<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="html" encoding="UTF-8" indent="yes"/>

  <xsl:template match="/ville">
    <html>
      <head>
        <meta charset="UTF-8"/>
        <title><xsl:value-of select="@nom"/> - Guide de voyage</title>

        <!-- Feuille CSS globale -->
        <link rel="stylesheet" type="text/css" href="../css/style.css"/>

        <style>
          body { background:white !important; padding:20px; font-family:Arial; }
          h1 { text-align:center; margin-bottom:25px; }
          h2 { margin-top:25px; color:#2a4b8d; }
          .sites-grid { display:flex; flex-wrap:wrap; gap:20px; }
          .site-card { width:250px; padding:10px; border:1px solid #ddd; border-radius:10px; }
          .site-card img { width:100%; height:150px; object-fit:cover; border-radius:8px; }
          .colonne { margin-top:20px; }
          .actions { margin-top:40px; text-align:center; }
          .pdf-btn { padding:12px 20px; background:#0056b3; color:white; border-radius:8px;
                     text-decoration:none; cursor:pointer; }
        </style>

      </head>

      <body>

        <h1><xsl:value-of select="@nom"/></h1>

        <!-- DESCRIPTIF -->
        <h2>Descriptif</h2>
        <p><xsl:value-of select="descriptif"/></p>

        <!-- SITES -->
        <h2>Sites à visiter</h2>
        <div class="sites-grid">
          <xsl:for-each select="sites/site">
            <div class="site-card">

              <img>
                <xsl:attribute name="src">
                  <xsl:value-of select="@photo"/>
                </xsl:attribute>
                <xsl:attribute name="alt">
                  <xsl:value-of select="@nom"/>
                </xsl:attribute>
              </img>

              <h3><xsl:value-of select="@nom"/></h3>
            </div>
          </xsl:for-each>
        </div>

        <!-- HOTELS -->
        <h2>Hôtels</h2>
        <ul>
          <xsl:for-each select="hotels/hotel">
            <li><xsl:value-of select="."/></li>
          </xsl:for-each>
        </ul>

        <!-- RESTAURANTS -->
        <h2>Restaurants</h2>
        <ul>
          <xsl:for-each select="restaurants/restaurant">
            <li><xsl:value-of select="."/></li>
          </xsl:for-each>
        </ul>

        <!-- GARES -->
        <h2>Gares</h2>
        <ul>
          <xsl:for-each select="gares/gare">
            <li><xsl:value-of select="."/></li>
          </xsl:for-each>
        </ul>

        <!-- AEROPORTS -->
        <h2>Aéroports</h2>
        <ul>
          <xsl:for-each select="aeroports/aeroport">
            <li><xsl:value-of select="."/></li>
          </xsl:for-each>
        </ul>

        <!-- ACTIONS -->
        <div class="actions">
          <button class="pdf-btn" onclick="window.print()">📄 Exporter PDF</button>
          <br/><br/>
          <a class="pdf-btn" href="../index.php">🏠 Retour à l'accueil</a>
        </div>

      </body>
    </html>
  </xsl:template>

</xsl:stylesheet>
