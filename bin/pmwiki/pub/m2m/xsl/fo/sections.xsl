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
	
	<xsl:import href="page.xsl"/>
	<xsl:import href="params.xsl"/>
	
	<xsl:template match="section">
		<xsl:variable name="level" select="count(ancestor-or-self::section)"/>
		<xsl:comment> <xsl:value-of select="@wikipage"/>, Level <xsl:value-of select="$level"/> </xsl:comment>
		<xsl:choose>			
			<xsl:when test="$processing.mode != 'page' and not(ancestor::section) and $titlepage.create">				
				<!-- für Abschnitte der Ebene 1 immer eine neue page sequence erzeugen -->
				<fo:page-sequence master-reference="custom-format">
					<xsl:call-template name="static-content"/>
					<fo:flow flow-name="xsl-region-body" font-size="{$body.font.size}" id="{generate-id(*)}">
						<xsl:apply-templates select="title[1]" mode="section"/>												
						<xsl:apply-templates/>								
					</fo:flow>
				</fo:page-sequence>				
			</xsl:when>
			<xsl:otherwise>
				<fo:block>
					<xsl:if test="$processing.mode != 'page' and $section.pagebreak.level >= $level">
						<xsl:attribute name="page-break-before">always</xsl:attribute>
					</xsl:if>
					<xsl:apply-templates select="title[1]" mode="section"/>
					<xsl:apply-templates/>
				</fo:block>		
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<!-- Abschnittsüberschrift wird zusammen mit section verarbeitet, deshalb ist hier nichts zu tun -->
	<xsl:template match="section/title"/>
	
	<xsl:template match="section/title" mode="section">
		<xsl:variable name="level" select="count(ancestor-or-self::section)"/>
		<xsl:variable name="number">
			<xsl:number count="section" format="1.1" level="multiple"/>
			<xsl:if test="$level = 1">
				<xsl:text>.</xsl:text>
			</xsl:if>
			<xsl:text> </xsl:text>
		</xsl:variable>
		<xsl:variable name="scale">
			<xsl:choose>
				<xsl:when test="exsl:node-set($section.title.font.scale)/entry[@level=$level]">
					<xsl:value-of select="exsl:node-set($section.title.font.scale)/entry[@level=$level]"/>
				</xsl:when>
				<xsl:otherwise>1.0</xsl:otherwise>
			</xsl:choose>			
		</xsl:variable>				
		<fo:block>
			<fo:marker marker-class-name="section-title-{$level}">
				<xsl:value-of select="$number"/>
				<!--xsl:number count="section" format="1.1.1.1 " level="multiple"/-->
				<xsl:apply-templates/>
			</fo:marker>
		</fo:block>
		<fo:block  keep-with-next.within-page="always" 
			font-size="{mg:pt($body.font.size)*$scale}pt" 
			font-weight="bold" 
			id="{generate-id(..)}"
			margin-top="1cm"
			space-after="{$section.title.sep}">
			<xsl:value-of select="$number"/>
			<!--xsl:number count="section" format="1.1.1.1 " level="multiple"/-->			
			<xsl:apply-templates/>
		</fo:block>
	</xsl:template>
	
	<xsl:template match="title" mode="title">
		<xsl:apply-templates/>
	</xsl:template>
	
	<xsl:template match="h1|h2|h3|h4|h5|h6|h7|h8">
		<xsl:variable name="level" select="substring(name(.), 2)"/>
		<xsl:variable name="scale">
			<xsl:choose>
				<xsl:when test="exsl:node-set($section.title.font.scale)/entry[@level=$level]">
					<xsl:value-of select="exsl:node-set($section.title.font.scale)/entry[@level=$level]"/>
				</xsl:when>
				<xsl:otherwise>1.0</xsl:otherwise>
			</xsl:choose>			
		</xsl:variable>
		<fo:block font-size="{mg:pt($body.font.size)*$scale}pt"  font-weight="bold">
			<xsl:apply-templates/>
		</fo:block>
	</xsl:template>	
</xsl:stylesheet>
