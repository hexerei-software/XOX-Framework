<?xml version="1.0" encoding="ISO-8859-1"?>
<!--***************************************************************************
** This file is part of the PMWiki extension media2mult.                     **
** Copyright (c) 2005-2008 Zentrum virtUOS, University of Osnabrück, Germany **
**                                                                           **
** This program is free software; you can redistribute it and/or             **
** modify it under the terms of the GNU General Public License               **
** as published by the Free Software Foundation; either version 2            **
** of the License, or (at your option) any later version.                    **
**                                                                           **
** This program is distributed in the hope that it will be useful,           **
** but WITHOUT ANY WARRANTY; without even the implied warranty of            **
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             **
** GNU General Public License for more details.                              **
**                                                                           **
** You should have received a copy of the GNU General Public License         **
** along with this program; if not, write to the Free Software               **
** Foundation, Inc., 51 Franklin Street, Fifth Floor,                        **
** Boston, MA 02110-1301, USA.                                               **
****************************************************************************-->

<xsl:stylesheet  version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:fo="http://www.w3.org/1999/XSL/Format"
	xmlns:exsl="http://exslt.org/common"
	xmlns:func="http://exslt.org/functions"	
	xmlns:mg="mg"
	extension-element-prefixes="exsl func">		
	
	<xsl:output method="xml"/>
	
	<xsl:template match="*">
		<!--xsl:message>unhandled element: <xsl:value-of select="name(.)"/></xsl:message-->
		<xsl:apply-templates/>
	</xsl:template>
	
	
	<!--xsl:include href="form.xsl"/-->
   <xsl:include href="images.xsl"/>
	<xsl:include href="lists.xsl"/>
	<xsl:include href="page.xsl"/>
	<xsl:include href="params.xsl"/>
	<xsl:include href="sections.xsl"/>
	<xsl:include href="styles.xsl"/>
	<xsl:include href="tables.xsl"/>
	<xsl:include href="titlepage.xsl"/>
	<xsl:include href="toc.xsl"/>
	
	<!--xsl:variable name="options" select="document('options.xml')/document-options"/-->
	
	
	<xsl:template match="/">		
		<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">			
			<!-- Aussehen der Seiten definieren -->
			<fo:layout-master-set>
				<xsl:call-template name="simple-page-master">
					<xsl:with-param name="name">empty</xsl:with-param>
				</xsl:call-template>
				
				<!-- erste Seite eines neuen Hauptabschnitts ohne Kopfzeile-->
				<xsl:call-template name="simple-page-master">
					<xsl:with-param name="name">first</xsl:with-param>
				</xsl:call-template>
				<xsl:call-template name="simple-page-master">
					<xsl:with-param name="name">rest</xsl:with-param>
					<xsl:with-param name="top-extent" select="$page.margin.top"/>
				</xsl:call-template>
				
				<fo:page-sequence-master master-name="custom-format">
					<fo:repeatable-page-master-alternatives>
						<fo:conditional-page-master-reference page-position="first" master-reference="first"/>
						<fo:conditional-page-master-reference page-position="last" master-reference="rest"/>
						<fo:conditional-page-master-reference page-position="rest" master-reference="rest"/>
					</fo:repeatable-page-master-alternatives>					
				</fo:page-sequence-master>				
			</fo:layout-master-set>			
			
			<xsl:if test="$bookmarks.create">
				<xsl:choose>
					<xsl:when test="/book/article">
						<xsl:call-template name="bookmarks-collection"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:call-template name="bookmarks"/>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:if>
			<xsl:apply-templates/>
		</fo:root>
	</xsl:template>
		
	
	<xsl:template match="book">
		<xsl:call-template name="titlehead"/>
		<xsl:choose>
			<xsl:when test="article">
				<xsl:call-template name="toc-collection"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:call-template name="toc"/>
			</xsl:otherwise>
		</xsl:choose>
		<xsl:apply-templates/>		
	</xsl:template>
	
	
	<xsl:template match="article">
		<xsl:choose>
			<!-- Titel, Inhaltsverzeichnis und erstes Kapitel auf separaten Seiten -->
			<xsl:when test="$processing.mode!='page' and $titlepage.create">
				<xsl:call-template name="titlepage">
					<xsl:with-param name="doctitle" select="title"/>
					<xsl:with-param name="authors" select="authors"/>
				</xsl:call-template>
			   <xsl:if test="preface">
			      <xsl:comment>Preface</xsl:comment>
			      <fo:page-sequence master-reference="custom-format">
   			      <xsl:call-template name="static-content"/>
   			      <fo:flow flow-name="xsl-region-body" font-size="{$body.font.size}">
   			         <xsl:call-template name="preface"/>
   			      </fo:flow>
   			   </fo:page-sequence>		
			   </xsl:if>
			   <xsl:comment>TOC</xsl:comment>
			   <fo:page-sequence master-reference="custom-format">
					<xsl:call-template name="static-content"/>
					<fo:flow flow-name="xsl-region-body" font-size="{$body.font.size}">
						<xsl:call-template name="toc"/>
					</fo:flow>
				</fo:page-sequence>
				<xsl:choose>
					<!-- alle Abschnitte fortlaufend hintereinander (ohne erzwungene Seitenumbrüche) -->
					<xsl:when test="0 >= $section.pagebreak.level">
						<fo:page-sequence master-reference="custom-format">
							<xsl:call-template name="static-content"/>
							<fo:flow flow-name="xsl-region-body" font-size="{$body.font.size}">
								<xsl:apply-templates select="section"/>
							</fo:flow>
						</fo:page-sequence>
					</xsl:when>
					<xsl:otherwise>
						<xsl:apply-templates select="section"/>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:when>
			<!-- Titel, Inhaltsverzeichnis und erstes Kapitel fortlaufend -->
			<xsl:otherwise>
				<fo:page-sequence master-reference="custom-format">
					<xsl:call-template name="static-content"/>
					<fo:flow flow-name="xsl-region-body" font-size="{$body.font.size}" id="{generate-id(*)}">
						<xsl:if test="not($titlepage.create) and not($processing.mode='page')">
							<xsl:call-template name="titlehead"/>
						</xsl:if>
					   <xsl:if test="preface">					      
					      <xsl:call-template name="preface"/>
					   </xsl:if>
						<xsl:if test="$processing.mode != 'page'">
							<xsl:call-template name="toc"/>			
						</xsl:if>
						<xsl:apply-templates/>
					</fo:flow>		
				</fo:page-sequence>				
			</xsl:otherwise>
		</xsl:choose>		
	</xsl:template>
	
	
	<xsl:template name="titlehead">
		<fo:block text-align="center" font-weight="bold" font-size="20pt" space-after="5mm">
			<xsl:apply-templates select="title" mode="title"/>
		</fo:block>
		<fo:block text-align="center" margin-bottom="1cm">
			<xsl:for-each select="authors/author">
				<xsl:apply-templates/>
				<xsl:if test="following-sibling::author">, </xsl:if>
			</xsl:for-each>
			<xsl:if test="institution">
				<fo:block>
					<xsl:apply-templates select="institution/node()"/>
				</fo:block>
			</xsl:if>
			<xsl:if test="abstract">
			   <xsl:comment>Abstract</xsl:comment>
				<fo:block margin-left="1cm" margin-right="1cm" text-align="justify" margin-top="1cm">
					<xsl:apply-templates select="abstract/node()"/>
				</fo:block>
			</xsl:if>
		</fo:block>		
	</xsl:template>
   
   <xsl:template name="preface">
      <xsl:variable name="title">
         <xsl:choose>
            <xsl:when test="preface[1]/@title"><xsl:value-of select="preface[1]/@title"/></xsl:when>
            <xsl:otherwise><xsl:value-of select="mg:translate('preface', $doc.language)"/></xsl:otherwise>
         </xsl:choose>
      </xsl:variable>      
      <fo:block font-size="18pt" font-weight="bold" margin-bottom="5mm">         
         <xsl:value-of select="$title"/>
      </fo:block>
      <fo:block margin-bottom="1cm">
         <xsl:apply-templates select="preface[1]/node()"/>
      </fo:block>
   </xsl:template>
	
	
	<xsl:template match="article/authors/author">
		<xsl:apply-templates/>
	</xsl:template>
	
	<!-- Diese Elemente werden vom Templates für das umgebenden Elements behandelt. -->
	<xsl:template match="title|authors|institution|abstract|preface"/>
	
	<xsl:template match="code//text()">
		<xsl:value-of select="."/>
	</xsl:template>
		
	
	<xsl:template match="br">
		<fo:block/>
	</xsl:template>
	
	<xsl:template match="p">
		<fo:block space-after="{mg:pt($par.sep)}pt" text-indent="{mg:pt($par.initial.indent)}pt">
			<xsl:if test="@style">
				<xsl:call-template name="extract-style-attributes">
					<xsl:with-param name="styles" select="@style"/>
				</xsl:call-template>
			</xsl:if>
			<xsl:apply-templates/>
		</fo:block>
	</xsl:template>
		
	
	<xsl:template match="p[@class='vspace' and not(node())]">
		<xsl:choose>
			<xsl:when test="node()">
				<fo:block margin-bottom="{$par.sep}">
					<xsl:apply-templates/>
				</fo:block>
			</xsl:when>
			<xsl:otherwise>
				<!-- bei mehreren aufeinanderfolgenden vspace-Anweisungen den Zeilenvorschub nur einmal erzeugen  -->
				<xsl:variable name="id" select="generate-id(.)"/>
				<xsl:if test="not(preceding-sibling::p[generate-id(following-sibling::*[1])=$id])">
					<fo:block margin-bottom="{$par.sep}"/>	
				</xsl:if>		
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	
	
	
	<xsl:template match="newpage">
		<fo:block break-before="page"/>
	</xsl:template>
	
	<xsl:template match="i|em">
		<fo:inline font-style="italic"><xsl:apply-templates/></fo:inline>
	</xsl:template>
	
	<xsl:template match="b|strong">
		<fo:inline font-weight="bold"><xsl:apply-templates/></fo:inline>
	</xsl:template>	
		
	<xsl:template match="ins">
		<fo:inline text-decoration="underline"><xsl:apply-templates/></fo:inline>
	</xsl:template>
	
	<xsl:template match="del">
		<fo:inline text-decoration="line-through"><xsl:apply-templates/></fo:inline>
	</xsl:template>
	
	<xsl:template match="small">
		<fo:inline font-size="80%"><xsl:apply-templates/></fo:inline>
	</xsl:template>
	
	<xsl:template match="big">
		<fo:inline font-size="120%"><xsl:apply-templates/></fo:inline>
	</xsl:template>
	
	<xsl:template match="sup">
		<fo:inline baseline-shift="super"><xsl:apply-templates/></fo:inline>
	</xsl:template>
	
	<xsl:template match="sub">
		<fo:inline baseline-shift="sub"><xsl:apply-templates/></fo:inline>
	</xsl:template>
	
	<xsl:template match="font[@color]">
		<fo:inline color="{@color}"><xsl:apply-templates/></fo:inline>
	</xsl:template>
	
   <xsl:template match="tt">
      <fo:inline font-family="Courier"><xsl:apply-templates/></fo:inline>
   </xsl:template>
	
	<xsl:template match="a">
		<xsl:apply-templates/>
	</xsl:template>
	
	<xsl:template match="a[@class='wikilink']">
		<xsl:variable name="page" select="substring-after(@href, 'n=')"/>
		<fo:basic-link internal-destination="{generate-id(//section[@wikipage=$page][1])}">
			<xsl:apply-templates/>
		</fo:basic-link>
	</xsl:template>
	
	
	<xsl:template match="a[(@class='urllink' or @class='external') and not(img) and not(mediaobject)]">
		<xsl:apply-templates/>
		<xsl:if test="@href != . and not(ancestor::span[contains(@class, 'frame')] or ancestor::footnote)">
			<xsl:call-template name="footnote">
				<xsl:with-param name="fntext" select="@href"/>
				<xsl:with-param name="link" select="@href"/>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>
	
	<!-- Fragezeichen-Links zum Anlegen neuer Wikiseiten unterdrücken -->
	<xsl:template match="a[@class='createlink']"/>
	
	<xsl:template match="span[@class='wikiword']">
		<xsl:apply-templates/>
	</xsl:template>
	
	
	<xsl:template match="hr">
		<fo:block text-align="center">
			<fo:leader leader-pattern="rule"	leader-length="33%"  rule-style="solid"	rule-thickness="0.5pt"/>
		</fo:block>
	</xsl:template>
	
	<!-- abgesetztes Bild -->
	<xsl:template match="img">
		<fo:block>
			<fo:external-graphic src="url({@src})"/>
		</fo:block>
	</xsl:template>
	
	
	<!-- Bild in laufender Zeile -->
	<xsl:template match="p/img">
		<fo:external-graphic src="url({@src})"/>
	</xsl:template>
	
	
	
	<xsl:template match="footnote">
		<xsl:call-template name="footnote">
			<xsl:with-param name="fntext" select="node()"/>
		</xsl:call-template>
	</xsl:template>
	
	<!-- Erzeugt eine Fußnote -->
	<xsl:template name="footnote">
		<xsl:param name="fntext"/>
		<xsl:param name="link" select="''"/>
		<xsl:variable name="number">
			<xsl:number count="footnote[mg:outside-float(.)]|a[@class='urllink'][mg:outside-float(.) and not(ancestor::footnote)]" format="1" level="any"/>
		</xsl:variable>
		<fo:footnote>			
			<fo:inline font-size="75%" baseline-shift="super">
				<fo:basic-link internal-destination="fn{$number}">
					<xsl:value-of select="$number"/>			
				</fo:basic-link>
			</fo:inline>
			<fo:footnote-body font-size="8pt">				
				<fo:block  text-align="left" font-weight="normal"  id="fn{$number}">
					<xsl:value-of select="$number"/>
					<xsl:text> </xsl:text>
					<xsl:apply-templates select="$fntext"/>
				</fo:block>				
			</fo:footnote-body>
		</fo:footnote>						
	</xsl:template>
	
	
	<xsl:template match="inlinemath">
		<xsl:choose>
			<xsl:when test="not(imageobject)">
				<xsl:message>warning: missing math image on wiki page <xsl:value-of select="ancestor::section[1]/@wikipage"/></xsl:message>
				<fo:block><fo:inline background-color="red"> missing math image </fo:inline></fo:block>
			</xsl:when>
			<xsl:otherwise>
				<xsl:apply-templates select="imageobject"/>
			</xsl:otherwise>
		</xsl:choose>		
	</xsl:template>
	
	
	<xsl:template match="math">
		<fo:block text-align="center">
			<xsl:choose>
				<xsl:when test="not(imageobject)">
					<xsl:message>warning: missing math image on wiki page <xsl:value-of select="ancestor::section[1]/@wikipage"/></xsl:message>
					<fo:inline background-color="red"> missing math image </fo:inline>
				</xsl:when>
				<xsl:otherwise>
					<xsl:apply-templates select="imageobject"/>
				</xsl:otherwise>
			</xsl:choose>
		</fo:block>
	</xsl:template>
	
	<xsl:template match="pre">
		<fo:block white-space-collapse="false" 
			white-space-treatment="preserve" 
			linefeed-treatment="preserve"
			font-size="8pt"
			font-family="Courier">
			<xsl:apply-templates/>
		</fo:block>
	</xsl:template>
	
	<xsl:template match="programlisting">		
		<fo:list-block background-color="#eeeeee"				
			white-space-collapse="false" 
			white-space-treatment="preserve" 				
			start-indent="5mm"
			end-indent="5mm"
			padding="3mm"
			font-size="8pt"
			font-family="Courier">
			<xsl:for-each select="line">
				<fo:list-item>
					<fo:list-item-label text-align="right" end-indent="label-end()">
						<fo:block color="#999999"><xsl:number/></fo:block>
					</fo:list-item-label>
					<fo:list-item-body text-align="left" start-indent="body-start()">
						<fo:block><xsl:apply-templates/></fo:block>
					</fo:list-item-body>
				</fo:list-item>
			</xsl:for-each>
		</fo:list-block>
	</xsl:template>
	
	<xsl:template match="code">
		<fo:inline white-space-collapse="false" white-space-treatment="preserve" font-family="Courier">
			<xsl:apply-templates/>
		</fo:inline>
	</xsl:template>
	
	<!--func:function name="mg:section-number">
		<xsl:param name="section"/>
		<func:result>			
			<xsl:if test="$section/ancestor::section">					
				<xsl:value-of select="mg:section-number($section/ancestor::section[1])"/>
				<xsl:text>.</xsl:text>									
			</xsl:if>
			<xsl:value-of select="count($section/preceding-sibling::section)+1"/>			
		</func:result>
	</func:function-->
	
	
	<func:function name="mg:sect-option">		
		<xsl:param name="path1"/>
		<xsl:param name="path2"/>
		<xsl:choose>
			<xsl:when test="$path1">
				<func:result select="$path1"/>
			</xsl:when>
			<xsl:otherwise>
				<func:result select="$path2"/>
			</xsl:otherwise>
		</xsl:choose>		
	</func:function>
</xsl:stylesheet>
