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
require '../../_includes/help_content_init.php';
?>
<h2>Data Source History Tool</h2>

<p><b>Purpose:</b></p>
<p>The Data Source History Tool allows Data Source Administrators to view the history of records in a Data Source for which they are the administrator, and to choose to restore a previous version of a record.  This is useful where a record has been accidentally deleted and needs to be recovered, or where a record has been in correctly edited and the old version needs to be restored.
Only Approved, Published and Deleted records can be restored.</p>

<p></p>

<p><b>Initial Display:</b></p>
<p>The tool will display all the records within the selected data source, 25 per page, with the most recent records listed first. The tool lists the title of the record, status, the number of versions created and when the record was last updated or deleted.</p>
<p></p>

<p><b>Viewing the History for a record:</b></p>
<p>Clicking on the magnifying glass icon in the right hand column of a record will show each saved version of the selected record, when each version was updated and by whom. This screen also allows you to create a new record based on a previous version, or on a deleted record.</p>
<p></p>

<p><b>RIF-CS record pane: </b></p>
<p>The <i>'RIF-CS View of Record'</i> pane will initially be empty. You can view the RIF-CS for the selected version of a record by clicking the green Arrow <img src="<?php echo eAPP_ROOT; ?>/orca/_images/arrow_top_right.png" width="12px" height="12px" alt="Green arrow" /> in the right-hand column for that version. </p>
<p></p>

<p><b>Recovering records: </b></p>
<p>You can create a new record based on a previous version of a record, or a deleted record, by clicking on the <i>'Recover Record'</i> <img src="<?php echo eAPP_ROOT; ?>/orca/_images/lifesaver.png" width="12px" height="12px" alt="Recover Record button" /> button next to the version of the record that you wish to recover. You will then get a dialog box, checking that you really want to recover this record.</p> 
<p>The system will then take you to the <i>'Manage My Records'</i> screen, where your recovered record will be listed as a Draft Record. The key of the recovered record will be prefixed with: <i>'RECOVERED_'</i></p>
<p>You will then be able to go into the record and change this key. </p>
<p></p>

<p>If your data source is flagged for Quality Assessment, the recovered record will need to move through the usual assessment workflow on the Manage My Records screen before it can be published.</p> 
<p></p>

<p></p>
<p>You can get more Help on using the Data Source History tool from your ANDS liaison officer, or <a href="mailto:services@ands.org.au">services@ands.org.au</a>.</p>
