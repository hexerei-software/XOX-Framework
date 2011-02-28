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
	<xsl:import href="styles.xsl"/>
	
	<xsl:template match="ol|ul">
		<fo:list-block provisional-label-separation="8pt" space-after="{$par.sep}">
			<xsl:attribute name="provisional-distance-between-starts">
				<xsl:choose>
					<xsl:when test="name()='ol'">20pt</xsl:when>
					<xsl:otherwise>10pt</xsl:otherwise>
				</xsl:choose>				
			</xsl:attribute>
			<xsl:apply-templates/>
		</fo:list-block>		
	</xsl:template>	
	
	<xsl:template match="ol/li">
		<fo:list-item>
			<fo:list-item-label end-indent="label-end()">
				<!--fo:block text-align="right"><xsl:value-of select="count(preceding-sibling::li)+1"/>.</fo:block-->
				<fo:block text-align="right"><xsl:number format="1."/></fo:block>
			</fo:list-item-label>
			<fo:list-item-body start-indent="body-start()">
				<fo:block>
					<xsl:if test="@style">
						<xsl:call-template name="extract-style-attributes">
							<xsl:with-param name="styles" select="@style"/>
						</xsl:call-template>
					</xsl:if>
					<xsl:apply-templates/>
				</fo:block>				
			</fo:list-item-body>			
		</fo:list-item>	
	</xsl:template>
	
	<xsl:template match="ul/li">		
		<fo:list-item>
			<fo:list-item-label end-indent="label-end()">
				<fo:block>&#x2022;</fo:block>
			</fo:list-item-label>
			<fo:list-item-body start-indent="body-start()">
				<fo:block>
					<xsl:if test="@style">
						<xsl:call-template name="extract-style-attributes">
							<xsl:with-param name="styles" select="@style"/>
						</xsl:call-template>
					</xsl:if>
					<xsl:apply-templates/>
				</fo:block>				
			</fo:list-item-body>			
		</fo:list-item>	
	</xsl:template>
	
	
	<!-- Definitionslisten -->
	
	<xsl:template match="dl">		
		<xsl:apply-templates/>
	</xsl:template>
	
	<xsl:template match="dt">		
		<fo:block>
			<xsl:apply-templates/>
		</fo:block>		
	</xsl:template>
	
	
	<!-- alleinstehende <dd> ohne vorangehendes <dt> -->
	<xsl:template match="dd">
		<fo:block margin-left="1cm">						
			<xsl:apply-templates/>
		</fo:block>
	</xsl:template>
</xsl:stylesheet>
