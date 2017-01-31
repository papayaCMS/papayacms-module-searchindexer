<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<!--
  @papaya:modules PapayaModuleSearchIndexerResultPage
-->

<xsl:import href="page_main.xsl" />
<xsl:import href="../_lang/language.xsl" />
<xsl:import href="../_lang/datetime.xsl" />

<xsl:template name="content-area">
  <h1><xsl:value-of select="/page/content/topic/title" /></h1>
  <xsl:choose>
    <xsl:when test="search/error">
      <div>
        <p>Suche nach &quot;<xsl:value-of select="search/results/@term" />&quot;</p>
        <p class="error"><xsl:value-of select="search/error/text()" /></p>
      </div>
    </xsl:when>
    <xsl:otherwise>
      <xsl:for-each select="search/results">
        <p class="searchResultsHead">
          <xsl:value-of select="@start" /> - <xsl:value-of select="@end" /> von <xsl:value-of select="@total" /> Ergebnissen für <strong>"<xsl:value-of select="@term" />"</strong>
          <span>sortiert nach: <em>Relevanz</em></span>
        </p>

        <xsl:for-each select="result">
          <div>
            <xsl:attribute name="class">
              <xsl:choose>
                <xsl:when test="position()=count(../match)">article articleSearch articleLast</xsl:when>
                <xsl:otherwise>article articleSearch</xsl:otherwise>
              </xsl:choose>
            </xsl:attribute>
            <h2><a href="{@url}"><xsl:value-of select="@title" /></a></h2>
            <p>
              <xsl:value-of select="@content" disable-output-escaping="yes" />
            </p>
            <p>
              <a href="{@url}" class="more">Mehr...</a>
            </p>
            <!--
            <p class="searchResultsDateAndUrl">
              <xsl:call-template name="format-date">
                <xsl:with-param name="date" select="@published" />
              </xsl:call-template>
            </p>
             -->
          </div>
        </xsl:for-each>
      </xsl:for-each>
      <xsl:if test="search/paging">
        <div class="pager clearfix">
          <ul>
            <xsl:if test="search/paging/page[@type = 'previous']">
              <li class="back">
                <a href="{search/paging/page[@type = 'previous']/@href}">Zurück</a>
              </li>
            </xsl:if>
            <xsl:for-each select="search/paging/page[@type = 'page']">
              <li>
                <xsl:choose>
                  <xsl:when test="@current = 'true'">
                    <xsl:value-of select="position()"/>
                  </xsl:when>
                  <xsl:otherwise>
                    <a href="{@href}"><xsl:value-of select="position()" /></a>
                  </xsl:otherwise>
                </xsl:choose>
              </li>
            </xsl:for-each>
            <xsl:if test="search/paging/page[@type = 'next']">
              <li class="forward">
                <a href="{search/paging/page[@type = 'next']/@href}">Weiter</a>
              </li>
            </xsl:if>
          </ul>
        </div>
      </xsl:if>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

</xsl:stylesheet>
