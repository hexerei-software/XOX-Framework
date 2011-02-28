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

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:param name="body.font.family">Arial</xsl:param>
	<xsl:param name="body.font.size">10pt</xsl:param>
	<xsl:param name="bookmarks.create">1</xsl:param>
	<xsl:param name="doc.language">deutsch</xsl:param>	
	<xsl:param name="page.margin.left">2cm</xsl:param>
	<xsl:param name="page.margin.right">2cm</xsl:param>
	<xsl:param name="page.margin.top">2cm</xsl:param>
	<xsl:param name="page.margin.bottom">2cm</xsl:param>
	<xsl:param name="page.width">210mm</xsl:param>
	<xsl:param name="page.height">297mm</xsl:param>
	<xsl:param name="page.format">A4</xsl:param>
	<xsl:param name="par.initial.indent">0cm</xsl:param>
	<xsl:param name="par.sep">5mm</xsl:param>
	<xsl:param name="processing.date"></xsl:param>
	<xsl:param name="processing.mode">page</xsl:param>
	<xsl:param name="section.pagebreak.level">1</xsl:param>
	<xsl:param name="section.title.font.scale">
		<entry level="1">2.0</entry>
		<entry level="2">1.8</entry>
		<entry level="3">1.6</entry>
		<entry level="4">1.4</entry>
		<entry level="5">1.2</entry>		
	</xsl:param>	
	<xsl:param name="section.title.sep">5mm</xsl:param>
	<xsl:param name="target.format">pdf</xsl:param>
	<xsl:param name="titlepage.create">0</xsl:param>
	<xsl:param name="toc.create">1</xsl:param>
	<xsl:param name="toc.depth">3</xsl:param>
	<!--xsl:param name="toc.on-separate-page">0</xsl:param-->
	<xsl:param name="units.dpi">200</xsl:param>
</xsl:stylesheet>
