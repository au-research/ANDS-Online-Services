<?php
/** 
Copyright 2011 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
***************************************************************************
*
**/ 
?>
<p>
<?php
echo "<p style='margin:10px;border-top:1px dashed #333333;padding-left:10px' id='dashed'/>";
echo "<h3 style='margin:10px;padding-top:10px;'> <img src='".base_url()."img/icon/link_64.jpg' style='width:16px;'> External Websites:</h3>";
echo '<p><a href="javascript:void(0);" id="seeAlso_DataCiteNumFound"><span id="seealsodatacite-realnumfound">'.$numfound.'</span> Collections </a> from <a href="javascript:void(0);" id="seeAlso_dataciteInfo">DataCite</a></p>';

//echo 'Also related from Subjects: <a href="javascript:void(0);" id="seeAlso_subjectNumFound">'. $activityNumFound.' Collections</a>';
?>
</p>