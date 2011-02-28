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
	
	<xsl:import href="functions.xsl"/>
	<xsl:import href="params.xsl"/>
	
	<xsl:template match="mediaobject">
		<fo:block>
			<xsl:apply-templates/>
		</fo:block>
		<xsl:if test="caption">
			<fo:block margin-bottom="5mm"><xsl:apply-templates select="caption/node()"/></fo:block>
		</xsl:if>				
	</xsl:template>

	<xsl:template match="mediaobject/caption"/>
	
	<xsl:template match="inlinemediaobject">		
		<xsl:apply-templates/>
	</xsl:template>
	
	<!-- ignore audio and videoobjects -->
	<xsl:template match="audioobject|videoobject"/>
	
	<xsl:template match="imageobject">
		<xsl:variable name="image" select="mg:select-image(imagedata, $target.format)"/>
		<xsl:choose>
			<xsl:when test="$image">
				<fo:external-graphic src="url({$image/@fileref})">
					<xsl:if test="$image/@baseline-shift">
						<xsl:attribute name="baseline-shift">
							<xsl:value-of select="$image/@baseline-shift"/>
						</xsl:attribute>
					</xsl:if>
					<xsl:call-template name="image-attributes">
						<xsl:with-param name="image" select="$image"/>
					</xsl:call-template>
				</fo:external-graphic>				
			</xsl:when>
			<xsl:otherwise>
				<fo:block>
					<fo:inline border-width="0.5pt" border-style="solid" background-color="red">
						<xsl:text > no suitable media object found </xsl:text>						
					</fo:inline>
				</fo:block>
				<xsl:message>warning: no suitable image found</xsl:message>
			</xsl:otherwise>
		</xsl:choose>		
	</xsl:template>
	
	<xsl:template name="image-attributes">
		<xsl:param name="image"/>
		<xsl:choose>
			<!-- explicit width/height given? -->
			<xsl:when test="$image/@width or $image/@height">
				<xsl:if test="$image/@width">
					<xsl:attribute name="content-width">
						<xsl:value-of select="mg:pt($image/@width)"/>
						<xsl:text>pt</xsl:text>
					</xsl:attribute>				
				</xsl:if>
				<xsl:if test="$image/@height">
					<xsl:attribute name="content-height">
						<xsl:value-of select="mg:pt($image/@height)"/>
						<xsl:text>pt</xsl:text>
					</xsl:attribute>
				</xsl:if>				
			</xsl:when>
			<!-- no width/height attributes given -->
			<xsl:otherwise>
				<xsl:variable name="width" select="mg:pt(string($image/@media-width))"/>
				<xsl:variable name="height" select="mg:pt(string($image/@media-height))"/>
				<xsl:call-template name="compute-size-attributes">
					<xsl:with-param name="image" select="$image"/>
					<xsl:with-param name="area-width" select="mg:pt(/*/@page-width)-mg:pt($page.margin.left)-mg:pt($page.margin.right)"/>
					<xsl:with-param name="area-height" select="mg:pt(/*/@page-height)-mg:pt($page.margin.top)-mg:pt($page.margin.bottom)"/>
				</xsl:call-template>
				
				<!--xsl:choose>					
					<xsl:when test="$width > mg:pt(/*/@page-width) or $height > mg:pt(/*/@page-height)">
						<xsl:if test="$width > mg:pt(/*/@page-width)">
							<xsl:variable name="text-width" select="mg:pt(/*/@page-width)-2*mg:pt(/*/@margin)"/>							
							<xsl:attribute name="content-width">
								<xsl:value-of select="0.9 * $text-width"/>
								<xsl:text>pt</xsl:text>
							</xsl:attribute>
						</xsl:if>
						<xsl:if test="$height > mg:pt(/*/@page-height)">
							<xsl:variable name="text-height" select="mg:pt(/*/@page-height)-2*mg:pt(/*/@margin)"/>					
							<xsl:attribute name="content-height">
								<xsl:value-of select="0.9 * $text-height"/>
								<xsl:text>pt</xsl:text>
							</xsl:attribute>
						</xsl:if>
					</xsl:when>
					<xsl:otherwise>
						<xsl:attribute name="content-width">							
							<xsl:value-of select="$width"/>
							<xsl:text>pt</xsl:text>
						</xsl:attribute>
					</xsl:otherwise>
				</xsl:choose-->
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template name="compute-size-attributes">
		<xsl:param name="image"/>
		<xsl:param name="area-width"/>
		<xsl:param name="area-height"/>
		<xsl:variable name="width" select="mg:pt(string($image/@media-width))"/>
		<xsl:variable name="height" select="mg:pt(string($image/@media-height))"/>
		<!-- Skalierungsfaktor -->
		<xsl:variable name="scale">
			<xsl:choose>			
				<xsl:when test="$width > $area-width or $height > $area-height">
					<xsl:value-of select="mg:min(0.9*$area-width div $width, 0.9*$area-height div $height)"/>					
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="1"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<!--xsl:message>file=<xsl:value-of select="$image/@fileref"/></xsl:message>
		<xsl:message>page-width=<xsl:value-of select="$area-width"/></xsl:message>
		<xsl:message>page-height=<xsl:value-of select="$area-height"/></xsl:message>
		<xsl:message>width=<xsl:value-of select="$width"/></xsl:message>
		<xsl:message>height=<xsl:value-of select="$height"/></xsl:message>
		<xsl:message>scale=<xsl:value-of select="$scale"/></xsl:message>
		<xsl:message>scaled-width=<xsl:value-of select="$width*$scale"/></xsl:message>
		<xsl:message>scaled-height=<xsl:value-of select="$height*$scale"/></xsl:message>
		<xsl:message>==============================================</xsl:message-->
		<xsl:attribute name="content-width">
			<xsl:value-of select="concat($width*$scale, 'pt')"/>
		</xsl:attribute>
		<xsl:attribute name="content-height">
			<xsl:value-of select="concat($height*$scale, 'pt')"/>
		</xsl:attribute>		
	</xsl:template>
	
	
	<func:function name="mg:select-image">
		<xsl:param name="imagedata"/>
		<xsl:param name="target-format"/>
		<xsl:variable name="assigns">
			<assign target-format='pdf'>
				<image-format>pdf</image-format>
				<image-format>png</image-format>
				<image-format>gif</image-format>
				<image-format>jpeg</image-format>
			</assign>
			<assign target-format='ps'>
				<image-format>eps</image-format>
				<image-format>png</image-format>
				<image-format>jpeg</image-format>
			</assign>
			<assign target-format='html'>
				<image-format>png</image-format>
				<image-format>jpeg</image-format>
			</assign>
		</xsl:variable>
		<xsl:variable name="assign" select="exsl:node-set($assigns)/assign[@target-format=$target-format]"/>
		<xsl:variable name="image" select="$imagedata[@role='fo' and @format=$assign/image-format]"/>
		<xsl:choose>
			<xsl:when test="$image">
				<func:result select="$image[1]"/>
			</xsl:when>
			<xsl:otherwise>
				<func:result select="''"/>				
			</xsl:otherwise>
		</xsl:choose>		
	</func:function>
	

</xsl:stylesheet>
