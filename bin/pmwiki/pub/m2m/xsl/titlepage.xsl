<?xml version="1.0" encoding="UTF-8"?>
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
	
	<xsl:template name="titlepage">
		<xsl:param name="doctitle"/>
		<xsl:param name="authors"/>
		<fo:flow flow-name="xsl-region-body">
			<fo:block text-align="center">
				<fo:block space-before="3cm" space-after="1cm" font-family="Arial" font-size="24">
					<xsl:apply-templates select="$doctitle"/>
				</fo:block>
				<fo:block>
					<xsl:apply-templates select="$authors"/>
				</fo:block>
			</fo:block>	
		</fo:flow>
	</xsl:template>	
</xsl:stylesheet>
