<?php
/*
Copyright 2009 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*******************************************************************************/
// Include required files and initialisation.
require '_includes/init.php';
// Page processing
// -----------------------------------------------------------------------------

// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
// Some of Liz comment
// BEGIN: Page Content
// =============================================================================
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>Google Maps : MyMaps GeometryControls Example</title>
<script type="text/javascript" src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAA5QHWkz5JBQBkwXKiTxRLmBR_kN55tNBTXH7k1-7mL-eHGd6xVBSD5mdiZlfzdUuCTP7rbywMuhhyOA"></script>
    <script src="orca/_javascript/geometrycontrols.js" type="text/javascript"></script>
    <script src="orca/_javascript/markercontrol.js" type="text/javascript"></script>
    <script src="orca/_javascript/polygoncontrol.js" type="text/javascript"></script>
    <script src="orca/_javascript/map_main.js" type="text/javascript"></script>
    <style type="text/css">
    	/* to be added to a central style sheet */
      .emmc-tooltip {
    		border: 1px solid #666666;
    		background-color: #ffffff;
    		color: #444445;
    		display:none;
        font-size:13px;
        padding:1px;
    	}
      
      /* Doesn't work in ie :(
       #msim-icons * img:hover {
        border-color:#3D69B1;
      }*/
    </style>
  </head>

  <body>

    <div id="map_canvas" style="width: 1200px; height: 500px"></div>
    <input type="button" id="autoSaveToggle" value="Turn AutoSave On" onclick="javascript:mockAutoSave(this);"/>
  </body>
</html>

<?php
// =============================================================================
// END: Page Content
// Complete the XHTML response.

require '_includes/finish.php';
?>
