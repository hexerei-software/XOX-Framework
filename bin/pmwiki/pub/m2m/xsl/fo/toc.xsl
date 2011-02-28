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

<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:fo="http://www.w3.org/1999/XSL/Format"
	xmlns:exsl="http://exslt.org/common"
	xmlns:func="http://exslt.org/functions"
	xmlns:mg="mg"
	extension-element-prefixes="exsl func">
	
	<xsl:import href="params.xsl"/>
	
	<xsl:template name="toc-collection">
		<xsl:variable name="title" select="mg:translate('toc', $doc.language)"/>
		<fo:block font-size="18pt" font-weight="bold" margin-bottom="5mm">
			<xsl:value-of select="$title"/>
		</fo:block>
		<fo:table table-layout="fixed" margin-bottom="1cm">
			<fo:table-column column-width="90%" text-align="left"/>
			<fo:table-column column-width="10%" text-align="right"/>
			<fo:table-body>
				<xsl:for-each select="//article">
					<fo:table-row>						
						<!-- verlinkte Abschnittsnummer und - titel ausgeben -->
						<fo:table-cell>								
							<fo:block text-align-last="justify">									
								<fo:basic-link internal-destination="{generate-id(.)}">									
									<xsl:apply-templates select="title[1]" mode="title"/>
									<!-- Tabellenzelle mit punktierter Linie auffüllen -->
									<fo:leader leader-pattern="dots" keep-with-next.within-line="always"/>
								</fo:basic-link>
							</fo:block>						
						</fo:table-cell>
						<!-- verlinkte Seitenzahl ausgeben -->
						<fo:table-cell>								
							<fo:block>
								<fo:basic-link internal-destination="{generate-id(.)}">
									<fo:page-number-citation ref-id="{generate-id(.)}"/>
								</fo:basic-link>
							</fo:block>
						</fo:table-cell>
					</fo:table-row>						
				</xsl:for-each>
			</fo:table-body>
		</fo:table>
	</xsl:template>
	
	<!-- Inhaltsverzeichnis erstellen -->
	<xsl:template name="toc">		
		<xsl:if test="//section">
			<xsl:variable name="title" select="mg:translate('toc', $doc.language)"/>
			<xsl:variable name="bold-chapters" select="boolean(//section/section)"/>
			<xsl:comment> Inhaltsverzeichnis </xsl:comment>
			<!-- Text für Seitenköpfe setzen -->
			<fo:block keep-with-next.within-page="always">
				<fo:marker marker-class-name="section-title-1">
					<xsl:value-of select="$title"/>
				</fo:marker>
			</fo:block>
			<!-- Inhaltsverzeichnis ausgeben -->
			<fo:block>				
				<fo:block font-size="18pt" font-weight="bold" margin-bottom="5mm">
					<xsl:value-of select="$title"/>
				</fo:block>
				<fo:table table-layout="fixed" margin-bottom="1cm">
					<fo:table-column column-width="210mm - 4cm - 3*5pt" text-align="left"/>
					<fo:table-column column-width="3*5pt" text-align="right"/>
					<fo:table-body>
						<!-- Abschnitte bis zur Tiefe $toc.depth auflisten -->
						<xsl:for-each select="//section[$toc.depth >= count(ancestor-or-self::section)]">
							<xsl:variable name="level" select="count(ancestor-or-self::section)"/>
							<xsl:variable name="number">
								<xsl:number format="1.1" level="multiple"/>
								<xsl:if test="$level = 1">
									<xsl:text>.</xsl:text>
								</xsl:if>
								<xsl:text> </xsl:text>
							</xsl:variable>

							<fo:table-row>
								<xsl:if test="$bold-chapters and not(ancestor::section)">
									<xsl:attribute name="font-weight">bold</xsl:attribute>								
								</xsl:if>		
								<!-- verlinkte Abschnittsnummer und - titel ausgeben -->
								<fo:table-cell>								
									<fo:block text-align-last="justify" margin-left="{18*count(ancestor::section)}pt">
										<xsl:if test="$bold-chapters and not(ancestor::section)">
											<xsl:attribute name="margin-top">10pt</xsl:attribute>
										</xsl:if>
										<fo:basic-link internal-destination="{generate-id(.)}">
											<!--xsl:number level="multiple" format="1.1.1.1.1 "/-->
											<xsl:value-of select="$number"/>
											<xsl:apply-templates select="title[1]" mode="title"/>
											<!-- Tabellenzelle mit punktierter Linie auffüllen -->
											<fo:leader leader-pattern="dots" keep-with-next.within-line="always"/>
										</fo:basic-link>
									</fo:block>						
								</fo:table-cell>
								<!-- verlinkte Seitenzahl ausgeben -->
								<fo:table-cell>								
									<fo:block text-align="right">
										<xsl:if test="$bold-chapters and not(ancestor::section)">
											<xsl:attribute name="margin-top">10pt</xsl:attribute>
										</xsl:if>
										<fo:basic-link internal-destination="{generate-id(.)}">
											<fo:page-number-citation ref-id="{generate-id(.)}"/>
										</fo:basic-link>
									</fo:block>
								</fo:table-cell>
							</fo:table-row>						
						</xsl:for-each>
					</fo:table-body>
				</fo:table>
			</fo:block>
		</xsl:if>
	</xsl:template>
	
		
	<xsl:template name="bookmarks-collection">
		<fo:bookmark-tree>
			<fo:bookmark internal-destination="{generate-id(/book)}" starting-state="show">
				<fo:bookmark-title>
					<xsl:value-of select="/book/title"/>
				</fo:bookmark-title>
				<xsl:for-each select="/book/article">
					<fo:bookmark internal-destination="{generate-id(.)}" starting-state="show">
						<fo:bookmark-title>							
							<xsl:apply-templates select="title[1]" mode="title"/>
						</fo:bookmark-title>
						<xsl:apply-templates select="section" mode="bookmark"/>
					</fo:bookmark>
				</xsl:for-each>				
			</fo:bookmark>
		</fo:bookmark-tree>			
	</xsl:template>
	
	<!-- PDF-Bookmarks erstellen -->
	<xsl:template name="bookmarks">
		<xsl:if test="//section">
			<fo:bookmark-tree>
				<fo:bookmark internal-destination="{generate-id(/*)}" starting-state="show">
					<fo:bookmark-title>
						<xsl:value-of select="/*/title"/>
					</fo:bookmark-title>
					<xsl:apply-templates select="/*/section" mode="bookmark"/>
				</fo:bookmark>
			</fo:bookmark-tree>			
		</xsl:if>
	</xsl:template>
	
	<xsl:template match="section" mode="bookmark">
		<xsl:variable name="level" select="count(ancestor-or-self::section)"/>
		<xsl:variable name="number">
			<xsl:number format="1.1" level="multiple"/>
			<xsl:if test="$level = 1">
				<xsl:text>.</xsl:text>
			</xsl:if>
			<xsl:text> </xsl:text>
		</xsl:variable>

		<fo:bookmark internal-destination="{generate-id(.)}" starting-state="show">
			<fo:bookmark-title>
				<xsl:value-of select="$number"/>
				<!--xsl:number format="1.1.1.1.1 " level="multiple"/-->
				<xsl:apply-templates select="title[1]" mode="title"/>
			</fo:bookmark-title>
			<xsl:apply-templates select="section" mode="bookmark"/>
		</fo:bookmark>
	</xsl:template>
	
</xsl:stylesheet>
