<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <!--
    @papaya:modules actionbox_searchbox
  -->

  <xsl:param name="PAGE_THEME_PATH" />
  <xsl:param name="PAGE_WEB_PATH" />

  <xsl:output method="xml" encoding="utf-8" standalone="no" indent="yes" omit-xml-declaration="yes" />

  <xsl:template match="/elastic-search-box">
    <xsl:variable name="dialog" select="dialog-box" />
    <xsl:variable name="suggest" select="suggest" />
    <form action="{$dialog/@action}" method="get" class="serviceNavSearch" data-suggest="{$suggest/@url}">
      <div class="serviceNavSearchHead"><xsl:text> </xsl:text></div>
      <div class="serviceNavSearchBody">
        <input type="text" name="{$dialog/field/input[@type='text']/@name}" />
        <input alt="Suche" type="image" src="{$PAGE_THEME_PATH}img/searchButton.png" />
        <button type="submit"><xsl:value-of select="$dialog/button[@type = 'submit']/text()" /></button>
      </div>
      <div class="serviceNavSearchFoot"><xsl:text> </xsl:text></div>
    </form>
  </xsl:template>

</xsl:stylesheet>
