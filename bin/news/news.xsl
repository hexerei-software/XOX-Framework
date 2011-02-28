<?xml version="1.0" encoding="iso-8859-1" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:msxsl="urn:schemas-microsoft-com:xslt">

	<xsl:output method="html" encoding="iso-8859-1" />

	<xsl:param name="max_news_viewed" />
	<xsl:param name="news_id" />
	<xsl:param name="news_admin" />
	
<!-- wrap html and body tags -->
	<xsl:template match="/">
    <div class="acaption">
      <xsl:value-of select="/service/strings/string[@id='TITLE']" />
    </div>
    <div class="abox">
      <xsl:apply-templates select="service" />
    </div>
		<br></br>
	</xsl:template>

<!-- match our news collection -->
	<xsl:template match="service">

<!-- for each news item orderd by headline -->
		
		<xsl:choose>
			<xsl:when test="$news_id &gt; 0">
				<xsl:apply-templates select="news[@id=$news_id]" />
			</xsl:when>
			<xsl:otherwise>
				<xsl:choose>
					<xsl:when test="$max_news_viewed &gt; 0">
						<xsl:variable name="news_count" select="0" />
						<xsl:for-each select="news[position()&lt;$max_news_viewed]">
							<xsl:sort select="@stamp" order="descending" />
								<xsl:apply-templates select="." />
						</xsl:for-each>
					</xsl:when>
					<xsl:otherwise>
						<xsl:for-each select="news">
							<xsl:sort select="@stamp" order="descending" />
							<xsl:apply-templates select="." />
						</xsl:for-each>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:otherwise>
		</xsl:choose>

<!-- list news admin for contact -->
		<font face="arial" size="1">
			<xsl:apply-templates select="admin" />
			<xsl:text> - </xsl:text> 
			<xsl:value-of select="/service/strings/string[@id='LASTUPDATE']" />
			<xsl:text>: </xsl:text> 
			<xsl:value-of select="date" />
		</font>
	</xsl:template>

<!-- match our news -->
	<xsl:template match="news">

<!-- create link to news item -->
		<a>
			<xsl:attribute name="name">
				<xsl:value-of select="@id" />
			</xsl:attribute>
		</a>

		<table width="98%" cellspacing="0" cellpadding="0" border="0">

			<tr>
				<td style="font-weight:normal;font-size:10px;font-family:Arial,Helvetica,sans-serif;">
					<img src="images/text.gif" width="9" height="10" hspace="2" vspace="1" align="left"></img>
	
<!-- write creation date -->
					<xsl:text> </xsl:text>
					<xsl:value-of select="date" />

<!-- list authors for reply -->
			        <xsl:if test="author">
						<xsl:text> - </xsl:text>
						<xsl:apply-templates select="author" />
					</xsl:if>
		
				</td>
				<td align="right" style="font-weight:normal;font-size:12px;font-family:Arial,Helvetica,sans-serif;">
				<xsl:if test="$news_admin &gt; 0">
					<a>
						<xsl:attribute name="href">
							<xsl:text>index.php?p=</xsl:text>
							<xsl:value-of select="/service/language/@id" />
							<xsl:text>/0/0/0&amp;a=</xsl:text>
							<xsl:value-of select="@id" />
						</xsl:attribute>
						<xsl:text>&gt;&gt; </xsl:text>
						<xsl:value-of select="/service/strings/string[@id='EDIT']" />
					</a>
				</xsl:if>
				</td>
			</tr>
<!-- write headline -->
			<tr>
				<td colspan="2">
          <h2><xsl:value-of select="headline" /></h2>
				</td>
			</tr>

<!-- apply template for news item content -->
			<tr>
				<td style="font-weight:normal;font-size:14px;font-family:Arial,Helvetica,sans-serif;" colspan="2">
					<xsl:apply-templates select="content" />
				</td>
			</tr>
			<tr>
				<td style="font-weight:normal;font-size:12px;font-family:Arial,Helvetica,sans-serif;" colspan="2" align="right">
		<xsl:if test="false">
		<xsl:text>[ </xsl:text> 
		<xsl:value-of select="count(comment)" />
		<xsl:text> </xsl:text>
		<xsl:if test="count(comment)=1">
			<xsl:value-of select="/service/strings/string[@id='COMMENT']" />
		</xsl:if>
		<xsl:if test="count(comment)!=1">
			<xsl:value-of select="/service/strings/string[@id='COMMENTS']" />
		</xsl:if>
		<xsl:text> ]</xsl:text>
		</xsl:if> 
				</td>
			</tr>
		</table>

<!-- make some whitespace between news items -->
    <hr style="height:1px;color:#0099cf;" color="#0099cf"></hr>

	</xsl:template>

<!-- handle author -->
	<xsl:template match="author">
		<a>
			<xsl:attribute name="href">
				<xsl:text>mailto:</xsl:text>
				<xsl:value-of select="email" />
				<xsl:text>?subject=</xsl:text>
				<xsl:value-of select="../headline" />
			</xsl:attribute>

			<xsl:attribute name="title">
				<xsl:value-of select="/service/strings/string[@id='MAILTO']" />
				<xsl:text> </xsl:text>
				<xsl:value-of select="email" />
			</xsl:attribute>
			
			<xsl:value-of select="name" />
		</a>
	</xsl:template>

<!-- handle news item content -->
	<xsl:template match="content">
		<p><xsl:apply-templates /></p>
	</xsl:template>

<!-- handle news item content text -->
	<xsl:template match="text">
		<xsl:apply-templates /><br></br>
	</xsl:template>

<!-- handle news item content image -->
	<xsl:template match="image">
		<img border="0" hspace="5" vspace="5">
			<xsl:attribute name="src">
				<xsl:text>images/</xsl:text>
				<xsl:value-of select="@src" />
			</xsl:attribute>
			<xsl:attribute name="alt">
				<xsl:value-of select="text()" />
      </xsl:attribute>
			<xsl:choose>
				<xsl:when test="@align">
					<xsl:attribute name="align">
						<xsl:value-of select="@align" />
					</xsl:attribute>
				</xsl:when>
				<xsl:otherwise>
					<xsl:choose>
						<xsl:when test="../../@id mod 2=0">
							<xsl:attribute name="align">
								<xsl:text>left</xsl:text>
							</xsl:attribute>
							<xsl:attribute name="style">
								<xsl:text>margin-right:10px;</xsl:text>
							</xsl:attribute>
						</xsl:when>
						<xsl:otherwise>
							<xsl:attribute name="align">
								<xsl:text>right</xsl:text>
							</xsl:attribute>
							<xsl:attribute name="style">
								<xsl:text>margin-left:10px;</xsl:text>
							</xsl:attribute>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:otherwise>
			</xsl:choose>
			<xsl:if test="@width">
				<xsl:attribute name="width">
					<xsl:value-of select="@width" />
      	</xsl:attribute>
			</xsl:if>
			<xsl:if test="@height">
				<xsl:attribute name="height">
					<xsl:value-of select="@height" />
      	</xsl:attribute>
			</xsl:if>
		</img>
	</xsl:template>

<!-- handle news admin -->
	<xsl:template match="admin">
		<xsl:value-of select="/service/strings/string[@id='ADMIN']" />
		<xsl:text>: </xsl:text> 
		<a>
			<xsl:attribute name="href">
				<xsl:text>mailto:</xsl:text>
				<xsl:value-of select="email" />
			</xsl:attribute>
			
			<xsl:attribute name="title">
				<xsl:value-of select="/service/strings/string[@id='MAILTO']" />
				<xsl:text> </xsl:text>
				<xsl:value-of select="email" />
			</xsl:attribute>
			
			<xsl:value-of select="name" />
		</a>
	</xsl:template>

<!-- tool templates -->
	<xsl:template match="br">
		<br></br>
	</xsl:template>
	
	<xsl:template match="p">
		<p><xsl:apply-templates /></p>
	</xsl:template>
	
	<xsl:template match="b">
		<b style="color:#b5a350;"><xsl:apply-templates /></b>
	</xsl:template>
	
	<xsl:template match="i">
		<i><xsl:apply-templates /></i>
	</xsl:template>
	
	<xsl:template match="ol">
		<ul><xsl:apply-templates /></ul>
	</xsl:template>
	
	<xsl:template match="ul">
		<ul><xsl:apply-templates /></ul>
	</xsl:template>
	
	<xsl:template match="li">
		<li><xsl:apply-templates /></li>
	</xsl:template>
	
	<xsl:template match="a">
		<a style="font-weight:bold;color:#b5a350;">
			<xsl:attribute name="href">
				<xsl:value-of select="@href" />
			</xsl:attribute>
			<xsl:if test="@target">
				<xsl:attribute name="target">
					<xsl:value-of select="@target" />
				</xsl:attribute>
			</xsl:if>
			<xsl:if test="@title">
				<xsl:attribute name="title">
					<xsl:value-of select="@title" />
				</xsl:attribute>
			</xsl:if>
			<xsl:if test="not(@title)">
				<xsl:attribute name="title">
					<xsl:value-of select="text()" />
				</xsl:attribute>
			</xsl:if>
			<xsl:if test="@style">
				<xsl:attribute name="style">
					<xsl:value-of select="@style" />
				</xsl:attribute>
			</xsl:if>
			<xsl:apply-templates />
		</a>
	</xsl:template>
   
</xsl:stylesheet>