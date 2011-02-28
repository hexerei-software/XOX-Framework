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

	<xsl:template match="table[.//text() or .//*[name(.)!='tr' and name(.)!='td' and name(.)!='th']]">
		<fo:block space-after="{$par.sep}">
			<fo:table-and-caption>
				<xsl:if test="caption[not(preceding-sibling::tr)]">
					<xsl:apply-templates select="caption"/>
				</xsl:if>
				<fo:table border-collapse="collapse">
					<xsl:attribute name="border-collapse">collapse</xsl:attribute>
					<xsl:call-template name="border-attributes"/>
					<xsl:if test="@width and not(boolean(number(@width)))">
						<xsl:attribute name="width">
							<xsl:value-of select="@width"/>
						</xsl:attribute>
					</xsl:if>
					<fo:table-body>
						<xsl:apply-templates select="tr"/>
					</fo:table-body>
				</fo:table>
				<xsl:if test="caption[preceding-sibling::tr]">
					<xsl:apply-templates select="caption"/>
				</xsl:if>
			</fo:table-and-caption>
		</fo:block>
		<xsl:if test="@caption">
			<fo:block space-before="5pt">
				<xsl:value-of select="@caption"/>
			</fo:block>
		</xsl:if>
	</xsl:template>

	
	<xsl:template match="tr[.//text() or .//*[name(.)!='td' and name(.)!='th']]">
		<fo:table-row>
			<xsl:apply-templates/>
		</fo:table-row>		
	</xsl:template>
	
	
	<xsl:template match="td|th">
		<fo:table-cell border-collapse="collapse">
			<xsl:call-template name="border-attributes"/>
			<xsl:if test="@colspan">
				<xsl:attribute name="number-columns-spanned">
					<xsl:value-of select="@colspan"/>
				</xsl:attribute>
			</xsl:if>						
			<xsl:if test="@align">
				<xsl:attribute name="text-align">
					<xsl:value-of select="@align"/>
				</xsl:attribute>
			</xsl:if>
			<xsl:if test="@bgcolor">
				<xsl:attribute name="background-color">
					<xsl:value-of select="@bgcolor"/>
				</xsl:attribute>
			</xsl:if>
			<xsl:if test="ancestor::table[1]/@cellpadding">
				<xsl:attribute name="padding">
					<xsl:choose>
						<xsl:when test="boolean(number(ancestor::table[1]/@cellpadding))">
							<xsl:value-of select="concat(ancestor::table[1]/@cellpadding, 'pt')"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="ancestor::table[1]/@cellpadding"/>
						</xsl:otherwise>
					</xsl:choose>					
				</xsl:attribute>
			</xsl:if>
			<fo:block>
				<xsl:apply-templates/>
			</fo:block>			
		</fo:table-cell>
	</xsl:template>
	
	<xsl:template match="td[not(.//text())] | th[not(.//text())]">
		<fo:table-cell>
			<fo:block/>
		</fo:table-cell>
	</xsl:template>
	
	<xsl:template match="table/caption">
		<fo:table-caption>
			<fo:block>
				<xsl:apply-templates/>
			</fo:block>
		</fo:table-caption>	
	</xsl:template>
	
	<xsl:template name="border-attributes">
		<xsl:variable name="width" select="(ancestor-or-self::*/@border)[1]"/>
		<xsl:if test="$width">
			<xsl:attribute name="border-style">solid</xsl:attribute>
			<xsl:attribute name="border">
				<xsl:value-of select="concat(mg:pt($width),'pt')"/>
			</xsl:attribute>
		</xsl:if>
	</xsl:template>	
</xsl:stylesheet>
