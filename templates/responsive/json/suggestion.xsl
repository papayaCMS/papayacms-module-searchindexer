<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns="http://www.w3.org/1999/xhtml"
>
  <xsl:output method="text" encoding="UTF-8" media-type="text/plain"/>

  <xsl:template match="/">

    <xsl:choose>
      <xsl:when test="/page/content/topic/suggestion/results/@content != ''">
        <xsl:value-of select="/page/content/topic/suggestion/results/@content" />
      </xsl:when>
      <xsl:otherwise>
        <xsl:text>[]</xsl:text>
      </xsl:otherwise>
    </xsl:choose>

  </xsl:template>

</xsl:stylesheet>