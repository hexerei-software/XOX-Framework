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
	
	<xsl:template name="simple-page-master">
		<xsl:param name="name"/>
		<xsl:param name="top-extent"/>
		<fo:simple-page-master master-name="{$name}">
			<xsl:call-template name="page-size"/>
			<fo:region-body region-name="xsl-region-body">
				<xsl:attribute name="margin-left"><xsl:value-of select="$page.margin.left"/></xsl:attribute>
				<xsl:attribute name="margin-right"><xsl:value-of select="$page.margin.right"/></xsl:attribute>
				<xsl:attribute name="margin-top"><xsl:value-of select="$page.margin.top"/></xsl:attribute>
				<xsl:attribute name="margin-bottom"><xsl:value-of select="$page.margin.bottom"/></xsl:attribute>
			</fo:region-body>
			<xsl:if test="$top-extent">
				<fo:region-before extent="{$top-extent}"/>
			</xsl:if>			
		</fo:simple-page-master>
		
	</xsl:template>
	
	
	<xsl:template name="static-content">
		<!-- Seitenkopf -->
		<fo:static-content flow-name="xsl-region-before">
			<fo:table margin-left="2cm" margin-right="2cm">
				<fo:table-column column-width="90%" text-align="left"/>
				<fo:table-column column-width="10%" text-align="right"/>
				<fo:table-body>
					<fo:table-row border-bottom-width="0.5pt" border-bottom-style="solid" font-size="10pt">								
						<fo:table-cell>
							<!-- Titel der ersten Abschnittsebene ausgeben -->
							<fo:block margin-top="1cm">
								<fo:retrieve-marker retrieve-class-name="section-title-1" 
									retrieve-position="first-including-carryover" 
									retrieve-boundary="page-sequence"/>
							</fo:block>
						</fo:table-cell>
						<!-- Seitenzahl ausgeben -->
						<fo:table-cell>
							<fo:block margin-top="1cm" text-align="right">
								<fo:page-number/>
							</fo:block>
						</fo:table-cell>
					</fo:table-row>							
				</fo:table-body>
			</fo:table>					
		</fo:static-content>
		<!-- Seitenfuß -->
		<fo:static-content flow-name="xsl-region-after">
			<fo:block text-align="center" margin-top="5mm">
				<fo:page-number/>
			</fo:block>
		</fo:static-content>
		
		<!-- Aussehen der Fußnoten-Trennlinie definieren -->
		<fo:static-content flow-name="xsl-footnote-separator">
			<fo:block>
				<fo:leader leader-pattern="rule"	leader-length="33%"  rule-style="solid"	rule-thickness="0.5pt"/>
			</fo:block>
		</fo:static-content>
	</xsl:template>
	
	
	<xsl:template name="separate-section">
		<xsl:param name="section"/>
		<xsl:param name="separate"/>		
		<xsl:if test="$separate">
			<fo:page-sequence master-reference="custom-format">
				<xsl:call-template name="static-content"/>
				<fo:flow flow-name="xsl-region-body" font-size="{$body.font.size}" id="{generate-id($section)}">
					<xsl:apply-templates select="$section"/>
				</fo:flow>
			</fo:page-sequence>			
		</xsl:if>		
	</xsl:template>
	
	
	<xsl:template name="page-size">
		<xsl:choose>
			<xsl:when test="$page.format='custom'">				
				<xsl:attribute name="page-height">
					<xsl:choose>
						<xsl:when test="mg:pt($page.height)"><xsl:value-of select="mg:pt($page.height)"/></xsl:when>
						<xsl:otherwise>297mm</xsl:otherwise>
					</xsl:choose>					
				</xsl:attribute>
				<xsl:attribute name="page-width">
					<xsl:choose>
						<xsl:when test="mg:pt($page.width)"><xsl:value-of select="mg:pt($page.width)"/></xsl:when>
						<xsl:otherwise>210mm</xsl:otherwise>
					</xsl:choose>					
				</xsl:attribute>
			</xsl:when>
			<xsl:otherwise>
				<xsl:variable name="formats">
					<format name="A4" width="210mm" height="297mm"/>
					<format name="A5" width="148mm" height="210mm"/>
				</xsl:variable>					
				<xsl:variable name="page-format">
					<xsl:choose>
						<xsl:when test="exsl:node-set($formats)/format[@name=$page.format]">
							<xsl:copy-of select="exsl:node-set($formats)/format[@name=$page.format]"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:copy-of select="exsl:node-set($formats)/format[@name='A4']"/>
						</xsl:otherwise>
					</xsl:choose>					
				</xsl:variable>
				<xsl:attribute name="page-height">					
					<xsl:value-of select="exsl:node-set($page-format)/format/@height"/>
				</xsl:attribute>
				<xsl:attribute name="page-width">
					<xsl:value-of select="exsl:node-set($page-format)/format/@width"/>
				</xsl:attribute>
			</xsl:otherwise>
		</xsl:choose>		
	</xsl:template>	
</xsl:stylesheet>
