<?php

	// === COMMON IMAGE FUNCTIONS ====================================
	define ( "imgDefExt", "jpg" );
	define ( "imgTagThumb", "_t" );
	define ( "imgTagMedium", "_m" );
	define ( "imgTagSource", "_org" );

	function getImgSrc ( $array = "" )
	{
		global $web_img_upload_path;
		// return $array['filename'];
		// mydump ($array);
		if ( $array['filename'] == "" ) {
			if ( !is_array ( $array ) ) $array = array( "imgTag" => imgTagThumb, "type" => "article" );
			$array['filename'] = "archive/no_image_" . getLanguage() . ".gif";
		}
		
		// different types can have different settings
		switch ( strtolower ( $array['type'] ) ) {
			case tOffersType_Article:
			case "article":
			case tOffersType_Service:
			case "service":
				$addition = "";
				if ( strtolower ( substr ( $array["imgTag"], 0 , 6 ) ) == "imgtag" ) {
					$tag = $array["imgTag"];
					eval ( "\$addition = $tag;" );
				} else $addition = $array["imgTag"];
				$path = $web_img_upload_path;
				// add special file code for different size images in articles
				$file = $web_img_upload_path . "/" . ereg_replace ( "(.[a-z0-9]{2,5})$", $addition . "\\1", $array["filename"] );
				break;
			default:
				$path = $web_img_upload_path;
				$file = $path . "/" . $array["filename"];
		} // switch
		return $file ;
	} // function

/*****************************************************************
	ImageHandler class supplies you with all functionality needed to
	upload and resize images
*****************************************************************/
class ImageHandler
{
	function ImageHandler() {
	}

	// display upload form
	// usage
	// <form method=POST enctype="multipart/form-data" ACCEPT="image/*" action="images.php" >
	// echo displayUploadForm ($product)
	// <p><input type=submit name=Submit value="Aktualisieren" style="font-size: 10pt;"></p>
	// </form>
	function displayUploadForm ( $aImages ) // display upload form,
	{
		if ( !$aImages ) return false;
		
		$tablePre = "\n\n<!-- BEGIN UploadForm -->\n";
		$tablePre .= "<table border=\"0\" cellspacing=\"1\" cellpadding=\"3\" width=\"100%\">\n";

		$displayed = '';
		for ( $i = 0; $i < count($aImages); $i++ ) {
			$displayed .= "<tr><td style=\"font-size:8px;\">&nbsp;</td></tr><tr><td colspan=\"3\" style=\"background:black;color:white;font-weight:bold;\">Bild " . ( $i + 1 ) . "</td></tr><tr>\n";
			if ( isset ( $aImages[$i] ) ) {
				$image = &$aImages[$i];
				$src = getImgSrc ( array ( "filename" => $image, "imgTag" => imgTagThumb, "type" => 'article' ) );
				$displayed .= "
					<td valign=\"middle\" align=\"center\" class=TableElement><img src='$src' alt=\"$image->title_de\"></td>
					<td valign=\"top\">
						Bilddatei <br /><input type=text name=files[File$i][name] value ='$image->filename' size=\"36\" maxlength=255><br />
						Bild Tooltip<br />
						<img src=\"../../images/flag_de.gif\" width=\"20\" height=\"12\">&nbsp;<input type=text name=files[File$i][title_de] value ='$image->title_de' size=\"32\" maxlength=50><br />
						<img src=\"../../images/flag_en.gif\" width=\"20\" height=\"12\">&nbsp;<input type=text name=files[File$i][title_en] value ='$image->title_en' size=\"32\" maxlength=50><br />
					</td>
					<td valign=\"top\" >L&ouml;schen<br><input type=checkbox name=files[File$i][delete] > </td>
					";
			} else {
				$displayed .= "<td colspan=\"3\"><input type=\"file\" name=\"File$i\" length=\"27\"></td>\n";
			}
			$displayed .= "</tr>\n";
		} // for
		$tableSuf = "</table> \n";
		$tableSuf .= "<!-- END UploadForm -->\n";
		return $tablePre . $displayed . $tableSuf;
	} // function

}	// finish class ImageHandler

?>