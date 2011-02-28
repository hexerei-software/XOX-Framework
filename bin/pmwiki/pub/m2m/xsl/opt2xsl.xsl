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

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:output method="text" />
	
	<xsl:param name="selected-options" select="options"/>
	<xsl:param name="wikixml-xsl"/>
	
	<xsl:template match="/">		
		<xsl:text><![CDATA[<?xml version="1.0" encoding="iso-8859-1"?>
			<!-- This stylesheet was generated by media2mult. Don't modify it manually. -->
			<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
			<xsl:output method="xml" encoding="ISO-8859-1"/>			
		]]></xsl:text>
		<xsl:text>&lt;xsl:import href="</xsl:text>
		<xsl:value-of select="$wikixml-xsl"/>
		<xsl:text>"/>&#10;</xsl:text>
		<xsl:apply-templates/>
		<xsl:text>&lt;/xsl:stylesheet>&#10;</xsl:text>
	</xsl:template>
	
	<xsl:template match="text()"/>
	
	<xsl:template match="option">
		<xsl:variable name="name" select="@name"/>
		<xsl:variable name="selected-option" select="$selected-options//option[@name=$name]"/>
		<xsl:variable name="value">			
			<xsl:choose>
				<xsl:when test="$selected-option">
					<xsl:value-of select="$selected-option/value"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="value"/>
				</xsl:otherwise>
			</xsl:choose>		
		</xsl:variable>	
		<xsl:text>&lt;xsl:param name="</xsl:text>
		<xsl:value-of select="@name"/>
		<xsl:text>"></xsl:text>
		<xsl:value-of select="$value"/>
		<xsl:text>&lt;/xsl:param>&#10;</xsl:text>
	</xsl:template>
</xsl:stylesheet>