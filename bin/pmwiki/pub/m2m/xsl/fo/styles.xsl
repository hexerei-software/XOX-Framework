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
	
	<!-- Liefert true, wenn sich $elem innerhalb eines fließenden Blocks befindet. -->
	<func:function name="mg:inside-float">
		<xsl:param name="elem"/>
		<func:result select="boolean($elem/ancestor::span[contains(@class, 'float')])"/>
	</func:function>

	<!-- Liefert true, wenn sich $elem außerhalb eines fließenden Blocks befindet. -->
	<func:function name="mg:outside-float">
		<xsl:param name="elem"/>
		<func:result select="not($elem/ancestor::span[contains(@class, 'float')])"/>
	</func:function>
	
	
	<xsl:template match="span[@class]">
		<xsl:choose>
			<xsl:when test="contains(@class, 'rfloat')">
				<fo:block intrusion-displace="auto">
					<fo:float float="right">					
						<fo:block>
							<xsl:apply-templates/>
						</fo:block>					
					</fo:float>
				</fo:block>		
			</xsl:when>
			<xsl:when test="contains(@class, 'lfloat')">
				<fo:block intrusion-displace="auto">
					<fo:float float="left">					
						<fo:block>
							<xsl:apply-templates/>
						</fo:block>					
					</fo:float>
				</fo:block>		
			</xsl:when>
			<xsl:otherwise>
				<xsl:apply-templates/>
			</xsl:otherwise>
		</xsl:choose>		
	</xsl:template>
	
		
	<xsl:template match="div[@style]">
		<fo:block>
			<xsl:call-template name="extract-style-attributes">
				<xsl:with-param name="styles" select="@style"/>
			</xsl:call-template>
			<xsl:apply-templates/>
		</fo:block>
	</xsl:template>
	
	<xsl:template match="span[@style]">
		<!--xsl:variable name="elem-type">
			<xsl:choose>
				<xsl:when test="ancestor::p">fo:inline</xsl:when>
				<xsl:otherwise>fo:block</xsl:otherwise>
			</xsl:choose>			
		</xsl:variable-->
		<!--xsl:element name="{$elem-type}"-->
		<fo:inline>
			<xsl:call-template name="extract-style-attributes">
				<xsl:with-param name="styles" select="@style"/>
			</xsl:call-template>
			<xsl:apply-templates/>
		</fo:inline>
	</xsl:template>
	
	<xsl:template name="extract-style-attributes">
		<xsl:param name="styles"/>
		<xsl:variable name="current-style">
			<xsl:choose>
				<xsl:when test="contains($styles,';')">
					<xsl:value-of select="substring-before($styles, ';')"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="$styles"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<xsl:if test="normalize-space($current-style) != ''">					
			<xsl:call-template name="handle-style">
				<xsl:with-param name="style" select="$current-style"/>
			</xsl:call-template>
			<xsl:if test="contains($styles, ';')">				
				<xsl:call-template name="extract-style-attributes">
					<xsl:with-param name="styles" select="substring-after($styles, ';')"/>
				</xsl:call-template>	
			</xsl:if>
		</xsl:if>
	</xsl:template>

	
	<xsl:template name="handle-style">
		<xsl:param name="style"/>
		<xsl:variable name="style-name" select="normalize-space(substring-before($style,':'))"/>
		<xsl:variable name="style-value" select="normalize-space(substring-after($style,':'))"/>
		<xsl:choose>
			<xsl:when test="$style-name=''">
				<xsl:message>unsupported style: <xsl:value-of select="$style"/></xsl:message>
			</xsl:when>
			<xsl:when test="$style-name='display'"/>
			<xsl:otherwise>
				<xsl:attribute name="{$style-name}">
					<xsl:value-of select="$style-value"/>
				</xsl:attribute>
			</xsl:otherwise>
		</xsl:choose>		
	</xsl:template>
	
	
	<xsl:template match="div[not(@*)]">
		<xsl:choose>
			<xsl:when test="ancestor::p">
				<xsl:apply-templates/>
			</xsl:when>
			<xsl:otherwise>
				<fo:block>	<xsl:apply-templates/></fo:block>
			</xsl:otherwise>
		</xsl:choose>		
	</xsl:template>
	
	<xsl:template match="div[@class='indent']">
		<fo:block margin-left="16pt">
			<xsl:apply-templates/>
		</fo:block>
	</xsl:template>
	
	<xsl:template match="div[@class='outdent']">
		<fo:block start-indent="16pt" text-indent="-16pt">
			<xsl:apply-templates/>
		</fo:block>
	</xsl:template>	
</xsl:stylesheet>
