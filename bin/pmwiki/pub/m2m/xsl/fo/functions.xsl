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
	
	<xsl:import href="params.xsl"/>
	
	<func:function name="mg:min">
		<xsl:param name="x"/>
		<xsl:param name="y"/>
		<xsl:choose>
			<xsl:when test="$x > $y">
				<func:result select="$y"/>
			</xsl:when>
			<xsl:otherwise>
				<func:result select="$x"/>
			</xsl:otherwise>
		</xsl:choose>		
	</func:function>
	
	<func:function name="mg:max">
		<xsl:param name="x"/>
		<xsl:param name="y"/>
		<xsl:choose>
			<xsl:when test="$x > $y">
				<func:result select="$x"/>
			</xsl:when>
			<xsl:otherwise>
				<func:result select="$y"/>
			</xsl:otherwise>
		</xsl:choose>		
	</func:function>
	
	<func:function name="mg:pt">		
		<xsl:param name="length"/>
		<!-- use the following DPI value when converting lengths given in pixel units -->
		<xsl:variable name="dpi" select="$units.dpi"/>
		<func:result>
			<xsl:choose>
				<!-- no unit given -->
				<xsl:when test="$length='0' or boolean(number($length))">					
					<xsl:value-of select="72 * $length div $dpi"/>
				</xsl:when>
				<xsl:when test="substring($length,1,1)!='0' and not(boolean(number(substring($length,1,1))))">
					<xsl:message>invalid length value: <xsl:value-of select="$length"/></xsl:message>
					<xsl:value-of select="0"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:variable name="value" select="number(substring($length, 1, string-length($length)-2))"/>
					<xsl:variable name="unit" select="substring($length, string-length($length)-1)"/>							
					<xsl:choose>
						<xsl:when test="$unit='pt'"><xsl:value-of select="$value"/></xsl:when>
						<xsl:when test="$unit='in'"><xsl:value-of select="72 * $value"/></xsl:when>
						<xsl:when test="$unit='mm'"><xsl:value-of select="72 * $value div 25.4"/></xsl:when>
						<xsl:when test="$unit='cm'"><xsl:value-of select="72 * $value div 2.54"/></xsl:when>						
						<xsl:when test="$unit='px'"><xsl:value-of select="72 * $value div $dpi"/></xsl:when>
						<xsl:otherwise>
							<xsl:message>unknown length unit: <xsl:value-of select="$unit"/></xsl:message>
							<xsl:text>0</xsl:text>
						</xsl:otherwise>
					</xsl:choose>				
				</xsl:otherwise>
			</xsl:choose>
		</func:result>		
	</func:function>
	
	<func:function name="mg:translate">
		<xsl:param name="label"/>
		<xsl:param name="lang"/>
		<func:result select="document(concat('../../language/',$lang,'.xml'))//entry[@label=$label]"/>
	</func:function>
</xsl:stylesheet>
