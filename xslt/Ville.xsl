<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="html" encoding="UTF-8" indent="yes"/>

  <xsl:template match="/ville">
    <html>
      <head>
        <title><xsl:value-of select="@nom"/> - Guide de voyage</title>
        <meta charset="UTF-8"/>
        <link rel="stylesheet" type="text/css" href="../css/style.css"/>
      </head>
      <body class="ville-page">
        <header>
          <h1><xsl:value-of select="@nom"/></h1>
        </header>

        <section class="descriptif">
          <h2>Descriptif</h2>
          <p><xsl:value-of select="descriptif"/></p>
        </section>

        <section class="sites">
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
        </section>

        <section class="listes">
          <div class="colonne">
            <h2>Hôtels</h2>
            <ul>
              <xsl:for-each select="hotels/hotel">
                <li><xsl:value-of select="."/></li>
              </xsl:for-each>
            </ul>
          </div>

          <div class="colonne">
            <h2>Restaurants</h2>
            <ul>
              <xsl:for-each select="restaurants/restaurant">
                <li><xsl:value-of select="."/></li>
              </xsl:for-each>
            </ul>
          </div>

          <div class="colonne">
            <h2>Gares</h2>
            <ul>
              <xsl:for-each select="gares/gare">
                <li><xsl:value-of select="."/></li>
              </xsl:for-each>
            </ul>
          </div>

          <div class="colonne">
            <h2>Aéroports</h2>
            <ul>
              <xsl:for-each select="aeroports/aeroport">
                <li><xsl:value-of select="."/></li>
              </xsl:for-each>
            </ul>
          </div>
        </section>

        <div class="actions-ville">
          <button onclick="window.print()">Exporter en PDF (impression)</button>
          <a href="../index.html">Retour à l'accueil</a>
        </div>
      </body>
    </html>
  </xsl:template>

</xsl:stylesheet>
