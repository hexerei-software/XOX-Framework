<?php
################################################################################
#                                      (c)2000-2001 Hexerei Software Creations #
#                                               http://www.hexerei-software.de #
#------------------------------------------------------------------------------#
# resultlist page                                                              #
#------------------------------------------------------------------------------#
# Author:   Daniel Vorhauer - dav                                              #
# Version:  26.04.2001                                                         #
################################################################################


//= CONSTANTS ==================================================================

//= DATA =======================================================================

//= CODE =======================================================================

  // query order (only for resultlist)
  /** /
  if (!isset($ob)) {
    if (!isset($mob)) {
      $ob = '+0';
    } else {
      $ob = $mob;
    }
  }
  $key = (int)substr($ob,1,1);
  $direction = ((substr($ob,0,1)=='-')?' DESC':'');
  $orderkeys = array ( 'producer','model','price','color' );
  $numorderkeys = 4;
  addPost('mob', $ob );

  // list of result id's
  $resids = "'0'";
  /**/

  // current selecte page
  if (!isset($current_page)) $current_page=(isset($last_page))?$last_page:1;
  if (!isset($max_results_per_page)) $max_results_per_page = 25;


  #
  # show page navigation
  #
  function showNavigation()
  {
    global $current_page, $num_results, $max_results_per_page;

    $pages = (int)($num_results / $max_results_per_page);
    if ( $num_results/$max_results_per_page-$pages > 0 ) $pages += 1;

    if ( $pages > 1 ) {

    ?><table border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td align="left" valign="top" width="60"><?php

      if ( $current_page > 1 )
        echo "<input class=\"button\" style=\"width:40px;height:20px;\" "
          ."type=\"submit\" name=\"command\" value=\"&lt;&lt;\" onClick=\"showPage(".($current_page-1).");\">";

        ?></td>
        <td valign="middle" align="center"><font style="font-size:12px;">
          <div align="center" style="font-size:12px;">&nbsp;&nbsp;&nbsp;<?php

            // draw pagelinks
            $linebreak = 10;
            if ( $pages > 40 ) {
              $linebreak = 20;
            } elseif ( $pages > 10 ) {
              $linebreak = (int)( $pages / 2 );
            }
            for ( $i=1; $i<=$pages && $i<100; $i++ ) {
              if ( $i==$current_page )
                echo '<b>'.substr('00'.$i,-2).'</b>';
              else
                echo '<a href="javascript:showPage('.$i.');">'.substr('00'.$i,-2).'</a>';
              echo '&nbsp;&nbsp;&nbsp;';
              if ($i%$linebreak==0 || $i==$pages) echo '<br>&nbsp;&nbsp;&nbsp;';
            }

          ?></div>
        </font></td>
        <td align="right" valign="top" width="60"><div align="right"><?php

      if ( $pages > $current_page )
        echo "<input class=\"button\" style=\"width:40px;height:20px;\" "
          ."type=\"submit\" name=\"command\" value=\"&gt;&gt;\" onClick=\"showPage(".($current_page+1).");\">";

        ?></div></td>
      </tr>
    </table>
  <?php
    }
  }  // showNavigation


  /** /

  # ----------------------------------------------------------------------
  # show results from search
  # ----------------------------------------------------------------------
  function showResults()
  {

    global $pages, $current_page, $num_results, $font_face;
    global $key, $direction, $page, $hpage, $rs, $usersql;
    global $hilite_style, $select_style;

    $hpage->SetStyle($hilite_style);
    $page->SetStyle($select_style);
    $resids = "'0'";


    #
    # show result list
    #

    ?><tr><td colspan="4"><hr style="color:#efefef;"></td></tr>
    <tr style="background:#efefef;"><td valign="middle">
      <a href="javascript:searchOrder('<?php echo (($key==0 && $direction=='')?'-':'+').'0'; ?>');"><?php
        if ($key==0) {
          if ($direction=='')  $hpage->Write('<img src="bilder/pfeil_o.gif" width="8" height="9" style="border:0;">&nbsp;<b>Hersteller</b>');
          else                $page->Write('<img src="bilder/pfeil_u.gif" width="8" height="9" style="border:0;">&nbsp;<b>Hersteller</b>');
        } else                $page->Write('Hersteller');
      ?></a>
    </td><td valign="middle">
      <a href="javascript:searchOrder('<?php echo (($key==1 && $direction=='')?'-':'+').'1'; ?>');"><?php
        if ($key==1) {
          if ($direction=='')  $hpage->Write('<img src="bilder/pfeil_o.gif" width="8" height="9" style="border:0;">&nbsp;<b>Modell</b>');
          else                $page->Write('<img src="bilder/pfeil_u.gif" width="8" height="9" style="border:0;">&nbsp;<b>Modell</b>');
        } else                $page->Write('Modell');
      ?></a>
    </td><td></td></tr>
    <tr style="background:#efefef;"><td valign="middle">
      <a href="javascript:searchOrder('<?php echo (($key==2 && $direction=='')?'-':'+').'2'; ?>');"><?php
        if ($key==2) {
          if ($direction=='')  $hpage->Write('<img src="bilder/pfeil_o.gif" width="8" height="9" style="border:0;">&nbsp;<b>Preis</b>');
          else                $page->Write('<img src="bilder/pfeil_u.gif" width="8" height="9" style="border:0;">&nbsp;<b>Preis</b>');
        } else                $page->Write('Preis');
      ?></a>
    </td><td valign="middle">
      <a href="javascript:searchOrder('<?php echo (($key==3 && $direction=='')?'-':'+').'3'; ?>');"><?php
        if ($key==3) {
          if ($direction=='')  $hpage->Write('<img src="bilder/pfeil_o.gif" width="8" height="9" style="border:0;">&nbsp;<b>Farbe</b>');
          else                $page->Write('<img src="bilder/pfeil_u.gif" width="8" height="9" style="border:0;">&nbsp;<b>Farbe</b>');
        } else                $page->Write('Farbe');
      ?></a>
    </td><td></td></tr>
    <tr><td colspan="4"><hr style="color:#efefef;"></td></tr><?php

    listResults( $rs );

  }
  /** /


  function listResults( $rs )
  /*****************************************************************
    dump results
                                                       21.03.2001 13:09
  *****************************************************************/
  /** /
  {
    global $hpage, $page, $resids, $bv, $bn, $optEuro, $euro_value;

    if ( $rs ) while ( $rs->getrow() ) {

        $resids .= ",'".$rs->field('id')."'";

      ?><tr><td colspan="2">
          <a href="javascript:showCar('<?php echo $rs->field('id'); ?>');"><?php

        // producer and model
        $hpage->Write('<b>'.$rs->field('producer').' / <i>'.$rs->field('model').'</i></b>');

          ?></a></td><td>&nbsp;&nbsp;&nbsp;<a href="javascript:showCar('<?php echo $rs->field('id'); ?>');">
          <font style="font-size:14px;" color="#00669a"><b>&gt;&gt;&gt;</b></font>
        </a></td>
      </tr>
      <tr>
        <td><font style="fon-size:14px;"><?php

        // price
        $price='Auf Anfrage';
        if ( $rs->field('price') > 0 ) {
          if ( isset($optEuro) && $optEuro ) {
            if ( $rs->field('offer') > 0 ) {
              $price = number_format(($rs->field('offer'))/$euro_value,2,',','.').'&nbsp;EURO';
            } else {
              $price = number_format(($rs->field('price'))/$euro_value,2,',','.').'&nbsp;EURO';
            }
          } else {
            if ( $rs->field('offer') > 0 ) {
              $price = number_format($rs->field('offer'),2,',','.').'&nbsp;<font style="font-size:12px;">DM</font>';
            } else {
              $price = number_format($rs->field('price'),2,',','.').'&nbsp;<font style="font-size:12px;">DM</font>';
            }
          }
        }
        $page->Write($price);

        ?></td>
        <td><?php

        $colorname = $rs->field('colorname');
        if (!empty($colorname)) $page->Write($colorname);

      ?></td><td></td></tr>
      <?php
        if ( isset($bv) ) {
      ?><tr><td colspan="4" align="right"><input style="background:#f0f0f0;color:#00669a;font-family:Arial,Helvetica,Geneva,Swiss,SunSans-Regular;font-size:14px;font-weight:bold;width:140px;" type="submit" name="ToggleNoted" value="Notieren" OnClick="toggleNoted('<?php echo $rs->field('id'); ?>');"></td></tr>
      <?php
        } elseif ( isset($bn) ) {
      ?><tr><td colspan="4" align="right"><input style="background:#f0f0f0;color:#00669a;font-family:Arial,Helvetica,Geneva,Swiss,SunSans-Regular;font-size:14px;font-weight:bold;width:140px;" type="submit" name="ToggleNoted" value="Notiz aufheben" OnClick="toggleNoted('<?php echo $rs->field('id'); ?>');"></td></tr>
      <?php
        }
      ?><tr><td colspan="4"><hr style="color:#efefef;"></td></tr><?php

   }

  }  // listResults
  /**/
