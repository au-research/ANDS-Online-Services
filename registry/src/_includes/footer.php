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
?>
<!-- END: Page Content -->
		</div>
	</div>
</div>
<div class="marginLeftGreyBtm">
	<div id="footer">
		<?php printSafe(eCOPYRIGHT_NOTICE) ?>&nbsp;&nbsp;
		<?php print getActivityLink('aCOSI_VERSIONS') ?>
		<?php
			if( eCONTACT_EMAIL != '' )
			{
				print '<a href="mailto:'.esc(eCONTACT_EMAIL).'">'.esc(eCONTACT_NAME).'</a>';
			}
		?>
	</div>
</div>
</body>
</html>