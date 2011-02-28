<?php
	/*****************************************************************
	 *****************************************************************

	 Constants for the DCO helper classes (DCO Data Container Object)

	 @file xox/lib/forms/inc.dcoconstants.php

	 @created 25.08.2006 17:07
	 @version 25.08.2006 17:07

	 @see xox/lib/database/class.cdbobject.php
	 @see xox/lib/forms/class.dcoform.php
	 @see xox/lib/forms/class.dcolist.php

	 @author dvorhauer

	 *****************************************************************
	 *****************************************************************

	 XOX PHP Library 2.0
	 (c) 1997-2006 hexerei software creations

	 This library is not yet free software. If you are a member of the
	 hexerei software development team, you have a non-exclusive right
	 to use this library for projects that are either for the hexerei
	 or one of it's customers under the label of hexerei. You may also
	 use the library for your own purposes if hexerei has granted you
	 a license to do so.

	 If you have received a copy of this source without explicit right
	 or licence from hexerei, then you may not modify it or reuse it
	 in any form without prior notice to hexerei. Most likely you have
	 received a copy along with an implemented application from the
	 hexerei or one of its licensees and herby have no right to reuse,
	 modify or publish this code under any terms other than the rights
	 you have acquired for the given application.

	 This library is distributed in the hope that it will be useful,
	 but WITHOUT ANY WARRANTY; without even the implied warranty of
	 MERCHANTABILITY of FITNESS FOR A PARTICULAR PURPOSE.

	 Under no circumstances may you remove this header and or any
	 copyright notice which marks hexerei software creations as the
	 owner and author of this library and its sources.

	 Daniel Vorhauer
	 daniel@hexerei.net

	*/

	/*** data types *************************************************/

	define('DCO_DATA_TEXT', 		0);
	define('DCO_DATA_INTEGER',	1);
	define('DCO_DATA_NUMBER',		2);
	define('DCO_DATA_EMAIL', 		3);
	define('DCO_DATA_URL', 			4);
	define('DCO_DATA_DATE', 		5);
	define('DCO_DATA_BOOL', 		6);
	define('DCO_DATA_PLAIN', 		7);
	define('DCO_DATA_NULL', 		'__N_U_L_L__');

?>
