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
	xmlns:exsl="http://exslt.org/common"
	xmlns:func="http://exslt.org/functions"	
	xmlns:mg="mg"
	extension-element-prefixes="exsl func"
	exclude-result-prefixes="mg">	
	
	<xsl:param name="split-level" select="0"/>
	
	<xsl:output method="xml" 
		encoding="ISO-8859-1" 
		doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"
		doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>
	
	<xsl:template match="/">
		<html>
			<head>
				<style type="text/css" media="all">
					@import "pub/skins/gemini/layout-main.css";
					@import "pub/skins/gemini/layout-smallheader.css";
					@import "pub/skins/gemini/rb-narrow.css";
				</style>
			</head>
			<body>
				<xsl:apply-templates/>
			</body>
		</html>
	</xsl:template>
	
	<xsl:template match="*">
		<xsl:copy>
			<xsl:copy-of select="@*"/>
			<xsl:apply-templates select="node()"/>
		</xsl:copy>
	</xsl:template>
	
	<xsl:template match="section">
		<xsl:element name="h{count(ancestor-or-self::section)}">
			<xsl:number format="1.1.1.1 " level="multiple"/>
			<xsl:apply-templates select="title[1]"/>
		</xsl:element>
		<xsl:apply-templates/>
	</xsl:template>
	
	<xsl:template match="title">
		<xsl:apply-templates/>
	</xsl:template>
	
	<xsl:template match="programlisting">
		<table bgcolor="#eeeeee" cellpadding="3">
			<tr>
				<td>
					<pre>
						<table>
							<xsl:for-each select="line">
								<tr>
									<td align="right"><font color="#999999"><xsl:number/></font>&#160;</td>
									<td><xsl:apply-templates/></td></tr>
							</xsl:for-each>
						</table>
					</pre>
				</td>
			</tr>
		</table>
	</xsl:template>
	
</xsl:stylesheet>
