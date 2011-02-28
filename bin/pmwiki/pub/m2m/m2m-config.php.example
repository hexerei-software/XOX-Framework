<? if (!defined('media2mult')) exit;
/******************************************************************************
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
*******************************************************************************/

require 'cmdlinetools.php';
global $M2MDir;

set_time_limit(0);

// commandline tools calles by media2mult
RegisterTool('batik-rasterizer', 'java -Djava.awt.headless=true -jar /opt/batik/batik-rasterizer.jar', '{-m $MIME} $IN');
RegisterTool('convert', 'convert', '{-scale $SCALE} {-density $DENSITY} {-transparent $TRANSPARENT} $IN $OUT');
RegisterTool('dvips', 'dvips', '{-E$EPS} -o$OUT $IN');	
RegisterTool('enscript', 'enscript', '--color --style=emacs --language=$OUTFMT {-E$LANG} -p -');
RegisterTool('epstopdf', 'epstopdf', '{--hires$HIRES} --outfile=$OUT $IN 2>&1');
RegisterTool('dos2unix', 'dos2unix', '-k $IN');
RegisterTool('fig2dev', 'fig2dev', '-L $FORMAT {$OPT} $IN $OUT');
RegisterTool('ffmpeg', 'ffmpeg', '{-itsoffset $TIME} -i $IN -vcodec $TARGETFORMAT -f rawvideo -vframes 1 $OUT 2>&1');
RegisterTool('fop', 'fop', '-fo $FO -$FORMAT $OUT');
RegisterTool('identify', 'identify', '{$OPT} $IN');
RegisterTool('gnuplot', '/usr/local/bin/gnuplot', '$SCRIPT');
RegisterTool('gs', 'gs', '-dNOPAUSE -dBATCH -dQUIET -sDEVICE=$DEVICE $IN 2>&1');
RegisterTool('latex', 'latex', '$TEX');
RegisterTool('mogrify', 'mogrify', '{-scale $SCALE} {-density $DENSITY} {-transparent $TRANSPARENT} $FILE');
RegisterTool('pdf2ps', 'pdf2ps', '$IN $OUT');
RegisterTool('ps2pdf', 'ps2pdf', '$IN $OUT');
RegisterTool('pdflatex', 'pdflatex', '"\nonstopmode\input $TEX"');
RegisterTool('ps2epsi', 'ps2epsi', '$IN $OUT');
RegisterTool('unzip', 'unzip', '-q -o -j $IN {$FILE}');
RegisterTool('wellformer', "$M2MDir/tools/wellformer", '$IN $OUT');
RegisterTool('zip-ls', 'zipinfo', '-1 $IN');
RegisterTool('xep', '/opt/XEP/xep', '{-fo $FO} -$FORMAT $OUT 2>&1');
RegisterTool('xmllint', 'xmllint', '{$OPT} $XML');
RegisterTool('xsltproc', 'xsltproc', '{-o $OUT} $STRINGPARAM $XSL $XML 2>&1');
RegisterTool('zip', 'zip');

// target formats to be accessible
$ConverterClassPrefixes = array('pdffo');

?>
