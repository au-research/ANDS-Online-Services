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
require '../../_includes/init.php';
// Page processing
// -----------------------------------------------------------------------------

// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '../../_includes/header.php';
// BEGIN: Page Content
// =============================================================================
?>

<form id="testform2" action="formstylesampler.php" method="post">
<table class="formTable" summary="Form Title">
	<thead>
		<tr>
			<td></td>
			<td>Form Title</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td></td>
			<td class="errorText">Form errors found during validation.</td>
		</tr>
	</tbody>
	<tbody class="formFields">
		<tr>
			<td>Field Label:</td>
			<td><input type="text" size="30" maxlength="255" value="text input" /></td>
		</tr>
		<tr>
			<td>Field Label:</td>
			<td><input disabled="disabled" type="text" size="30" maxlength="255" value="disabled text input" /></td>
		</tr>
		<tr>
			<td class="errorText">* Longer Field Label:</td>
			<td><input type="text" size="40" maxlength="255" value="text input" /></td>
		</tr>
		<tr>
			<td>Select Option Groups:</td>
			<td>
				<select name="">
					<optgroup label="group 1">
						<option value="">1 option 1</option>
						<option value="">1 option 2</option>
						<option value="" selected="selected">1 option 3</option>
						<option value="">1 option 4</option>
						<option value="">1 option 5</option>
					</optgroup>
					<optgroup label="group 2">
						<option value="">2 option 1</option>
						<option value="">2 option 2</option>
						<option value="">2 option 3</option>
						<option value="">2 option 4</option>
						<option value="">2 option 5</option>
					</optgroup>
				</select>
			</td>
		</tr>
		<tr>
			<td>Select List:</td>
			<td>
				<select name="">
					<option value="">option 1</option>
					<option value="">option 2</option>
					<option value="">option 3</option>
					<option value="" selected="selected">option 4</option>
					<option value="">option 5</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>Radio Button Group:</td>
			<td>
				<input type="radio" name="group1" value="" /> option 1&nbsp;
				<input type="radio" name="group1" value="" checked="checked" /> option 2&nbsp;
				<input type="radio" name="group1" value="" /> option 3
			</td>
		</tr>
		<tr>
			<td>Multiple Select With Groups:</td>
			<td>
				<select name="" multiple="multiple">
					<optgroup label="group 1">
						<option value="">1 option 1</option>
						<option value="" selected="selected">1 option 2</option>
						<option value="">1 option 3</option>
					</optgroup>
					<optgroup label="group 2">
						<option value="">2 option 1</option>
						<option value="" selected="selected">2 option 2</option>
						<option value="" selected="selected">2 option 3</option>
					</optgroup>
				</select>
			</td>
		</tr>
		<tr>
			<td>Multiple Select:</td>
			<td>
				<select multiple="multiple">
					<option value="">option 1</option>
					<option value="" selected="selected">option 2</option>
					<option value="">option 3</option>
					<option value="" selected="selected">option 4</option>
					<option value="">option 5</option>
					<option value="">option 6</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>Checkboxes:</td>
			<td>
				<input type="checkbox" name="" value="" checked="checked" />option 1<br />
				<input type="checkbox" name="" value="" />option 2<br />
				<input type="checkbox" name="" value="" checked="checked" />option 3
			</td>
		</tr>
		<tr>
			<td>Checkbox:</td>
			<td><input type="checkbox" name="" checked="checked" /></td>
		</tr>
		<tr>
			<td>Field Label:</td>
			<td><textarea rows="5" cols="40">textarea</textarea></td>
		</tr>
		<tr>
			<td>Field Label:</td>
			<td><textarea rows="5" cols="40" class="readonly">read-only textarea</textarea></td>
		</tr>
	</tbody>
	<tbody>
		<tr>
			<td></td>
			<td><input type="button" name="Cancel" value="Cancel" />&nbsp;<input type="submit" name="Save" value="Save" /></td>
		</tr>
		<tr>
			<td></td>
			<td class="formNotes">Fields marked * are mandatory.<br />
			Any additional notes about the form would go here.</td>
		</tr>
	</tbody>
</table>
</form>
<br />


<form id="testform1" action="formstylesampler.php" method="post">
<table class="formTable" summary="Form Title">
	<thead>
		<tr>
			<td></td>
			<td>Form Title</td>
		</tr>
	</thead>
	<tbody class="formFields">
		<tr>
			<td>Record ID:</td>
			<td>1234567</td>
		</tr>
		<tr>
			<td>Record Name:</td>
			<td>Immutable textual data</td>
		</tr>
		<tr>
			<td><sup>1</sup> Field Label:</td>
			<td><input type="text" size="30" maxlength="255" value="text input" /></td>
		</tr>
		<tr>
			<td>Date (yyyy-mm-dd):</td>
			<td><input type="text" size="10" maxlength="10" value="2007-02-27" /></td>
		</tr>
		<tr>
			<td>* Longer Field Label:</td>
			<td><input type="text" size="40" maxlength="255" value="text input" /></td>
		</tr>
		<tr>
			<td>Field Label:</td>
			<td><textarea rows="5" cols="40">textarea</textarea></td>
		</tr>
	</tbody>
	<tbody>
		<tr>
			<td></td>
			<td><input type="button" name="Cancel" value="Cancel" />&nbsp;<input type="submit" name="Save" value="Save" /></td>
		</tr>
		<tr>
			<td></td>
			<td class="formNotes">Fields marked * are mandatory.<br />
			1. Notes regarding the field referencing this note.
			</td>
		</tr>
	</tbody>
</table>
</form>
<br />

<form id="testform" action="formstylesampler.php" method="post">
<table class="formTable" summary="Test Form">
	<thead>
		<tr>
			<td></td>
			<td>Test Form</td>
		</tr>
	</thead>
	<tbody class="formFields">
		<tr>
			<td>Text Field:</td>
			<td><input type="text" size="30" maxlength="255" name="textfield" id="datetimefield" value="<?php printSafe(getPostedValue('textfield')) ?>" /></td>
		</tr>
		<tr>
			<td>AU Date &amp; Time Field:</td>
			<td><?php drawDateTimeInput('AUdatetimefield', getPostedValue('AUdatetimefield'), eDCT_FORMAT_AU_DATETIME) ?></td>
		</tr>
		<tr>
			<td>US Date &amp; Time Field:</td>
			<td><?php drawDateTimeInput('USdatetimefield', getPostedValue('USdatetimefield'), eDCT_FORMAT_US_DATETIME) ?></td>
		</tr>
		<tr>
			<td>ISO8601 Date &amp; Time Field:</td>
			<td><?php drawDateTimeInput('ISOdatetimefield', getPostedValue('ISOdatetimefield'), eDCT_FORMAT_ISO8601_DATE_TIME) ?></td>
		</tr>
		<tr>
			<td>ISO 8601 Date &amp; UTC Time Field:</td>
			<td><?php drawDateTimeInput('ISOUTCdatetimefield', getPostedValue('ISOUTCdatetimefield'), eDCT_FORMAT_ISO8601_DATE_TIME_UTC) ?></td>
		</tr>
		<tr>
			<td>ISO 8601 Time Field:</td>
			<td><?php drawDateTimeInput('ISOtimefield', getPostedValue('ISOtimefield'), eDCT_FORMAT_ISO8601_TIME) ?></td>
		</tr>
		<tr>
			<td>ISO 8601 UTC Time Field:</td>
			<td><?php drawDateTimeInput('ISOUTCtimefield', getPostedValue('ISOUTCtimefield'), eDCT_FORMAT_ISO8601_TIME_UTC) ?></td>
		</tr>
		<tr>
			<td>ISO 8601 Date Field:</td>
			<td><?php drawDateTimeInput('ISO8601datefield', getPostedValue('ISO8601datefield'), eDCT_FORMAT_ISO8601_DATE) ?></td>
		</tr>
		<tr>
			<td>ISO 8601 Date Field:</td>
			<td><input type="text" size="20" maxlength="20" name="ISO8601datefield2" id="ISO8601datefield2" value="<?php printSafe(getPostedValue('ISO8601datefield2')) ?>" />
			<script type="text/javascript">dctGetDateTimeControl('ISO8601datefield2', DCT_FORMAT_ISO8601_DATE)</script></td>
		</tr>
		
		<tr>
			<td>Select List:</td>
			<td>
				<select name="selectlist">
					<?php setChosen('selectlist', 'Option One', gITEM_SELECT) ?>
					<option value="Option One"<?php print $gChosen ?>>option 1</option>
					<?php setChosen('selectlist', 'Option Two', gITEM_SELECT) ?>
					<option value="Option Two"<?php print $gChosen ?>>option 2</option>
					<?php setChosen('selectlist', 'Option Three', gITEM_SELECT) ?>
					<option value="Option Three"<?php print $gChosen ?>>option 3</option>
					<?php setChosen('selectlist', 'Option Four', gITEM_SELECT) ?>
					<option value="Option Four"<?php print $gChosen ?>>option 4</option>
					<?php setChosen('selectlist', 'Option Five', gITEM_SELECT) ?>
					<option value="Option Five"<?php print $gChosen ?>>option 5</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>Radio Button Group:</td>
			<td>
				<?php setChosen('radiogroup', 'Radio One', gITEM_CHECK) ?>
				<input type="radio" name="radiogroup" value="Radio One"<?php print $gChosen ?> />option 1&nbsp;
				<?php setChosen('radiogroup', 'Radio Two', gITEM_CHECK) ?>
				<input type="radio" name="radiogroup" value="Radio Two"<?php print $gChosen ?> />option 2&nbsp;
				<?php setChosen('radiogroup', 'Radio Three', gITEM_CHECK) ?>
				<input type="radio" name="radiogroup" value="Radio Three"<?php print $gChosen ?> />option 3
			</td>
		</tr>
		<tr>
			<td>Multiple Select:</td>
			<td>
				<select multiple="multiple" name="multipleselect[]">
					<?php setChosen('multipleselect', 'Multi One', gITEM_SELECT) ?>
					<option value="Multi One"<?php print $gChosen ?>>option 1</option>
					<?php setChosen('multipleselect', 'Multi Two', gITEM_SELECT) ?>
					<option value="Multi Two"<?php print $gChosen ?>>option 2</option>
					<?php setChosen('multipleselect', 'Multi Three', gITEM_SELECT) ?>
					<option value="Multi Three"<?php print $gChosen ?>>option 3</option>
					<?php setChosen('multipleselect', 'Multi Four', gITEM_SELECT) ?>
					<option value="Multi Four"<?php print $gChosen ?>>option 4</option>
					<?php setChosen('multipleselect', 'Multi Five', gITEM_SELECT) ?>
					<option value="Multi Five"<?php print $gChosen ?>>option 5</option>
					<?php setChosen('multipleselect', 'Multi Six', gITEM_SELECT) ?>
					<option value="Multi Six"<?php print $gChosen ?>>option 6</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>Checkboxes:</td>
			<td>
				<?php setChosen('checkbox', 'Check One', gITEM_CHECK) ?>
				<input type="checkbox" name="checkbox[]" value="Check One"<?php print $gChosen ?> />option 1<br />
				<?php setChosen('checkbox', 'Check Two', gITEM_CHECK) ?>
				<input type="checkbox" name="checkbox[]" value="Check Two"<?php print $gChosen ?> />option 2<br />
				<?php setChosen('checkbox', 'Check Three', gITEM_CHECK) ?>
				<input type="checkbox" name="checkbox[]" value="Check Three"<?php print $gChosen ?> />option 3
			</td>
		</tr>
		<tr>
			<td>Text Area:</td>
			<td><textarea rows="5" cols="30" name="textarea"><?php printSafe(getPostedValue('textarea')) ?></textarea></td>
		</tr>
	</tbody>
	<tbody>
		<tr>
			<td></td>
			<td><input type="submit" name="action" value="Save" /></td>
		</tr>
	</tbody>
</table>
</form>

<br />
<table class="recordTable" summary="Record Title">
	<thead>
		<tr>
			<td></td>
			<td>Record Title</td>
		</tr>
	</thead>
	<tbody class="recordFields">
		<tr>
			<td>Field Label:</td>
			<td>Field Value</td>
		</tr>
		<tr>
			<td>Longer Field Label:</td>
			<td>Longer Field value</td>
		</tr>
		<tr>
			<td>Field Label:</td>
			<td>Lorem ipsum dolor. Lorem ipsum dolor. Lorem ipsum dolor. Lorem ipsum dolor.
			Lorem ipsum dolor lorem ipsum dolor. Lorem ipsum dolor. Lorem ipsum dolor lorem ipsum dolor.
			Lorem ipsum dolor lorem ipsum dolor. Lorem ipsum dolor lorem ipsum dolor. 
			Lorem ipsum dolor lorem ipsum dolor. Lorem ipsum dolor lorem ipsum dolor. </td>
		</tr>
	</tbody>
	<tbody>
		<tr>
			<td></td>
			<td><input type="button" name="Edit" value="Edit" />&nbsp;<input type="button" name="Delete" value="Delete" /></td>
		</tr>
	</tbody>
</table>

<br />
<h3>Buttons</h3>
<p><input type="button" class="buttonSmall" name="Cancel" value="Cancel" />&nbsp;<input type="submit" class="buttonSmall" name="Save" value="Save" /> class="buttonSmall"</p>
<p><input type="button" name="Cancel" value="Cancel" />&nbsp;<input type="submit" name="Save" value="Save" /></p>
<p><input type="button" class="buttonLarge" name="Cancel" value="Cancel" />&nbsp;<input type="submit" class="buttonLarge" name="Save" value="Save" onclick="wcPleaseWait(true, 'Testing...')" /> class="buttonLarge"</p>
<p>Any "cancel" buttons are always first on the left, followed by options for affirmative action.</p>


<?php
// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '../../_includes/footer.php';
require '../../_includes/finish.php';
?>
