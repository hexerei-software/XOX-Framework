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
	
	<xsl:import href="page.xsl"/>
	<xsl:import href="params.xsl"/>
	
	<xsl:template name="titlepage">
		<xsl:param name="doctitle"/>
		<xsl:param name="authors"/>
		<xsl:comment> Titelseite </xsl:comment>
		<fo:page-sequence master-reference="custom-format">
			<xsl:call-template name="static-content"/>
			<fo:flow flow-name="xsl-region-body">
				<fo:block text-align="center">
					<fo:block margin-top="3cm" space-after="2cm" font-family="Helvetica" font-size="26pt">
						<xsl:apply-templates select="$doctitle/text()"/>
					</fo:block>
					<fo:block font-size="16pt">
						<xsl:for-each select="$authors/author">							
							<xsl:value-of select="."/>
							<xsl:if test="following-sibling::author">
								<xsl:text>, </xsl:text>
							</xsl:if>
						</xsl:for-each>						
					</fo:block>			
					<fo:block margin-top="5cm">
						<xsl:value-of select="$processing.date"/>
					</fo:block>
				</fo:block>	
			</fo:flow>
		</fo:page-sequence>
	</xsl:template>	
</xsl:stylesheet>
