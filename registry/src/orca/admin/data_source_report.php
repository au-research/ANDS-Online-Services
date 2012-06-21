<?php
require '../../global_config.php';
require '../../_includes/init.php';
require '../orca_init.php';

$standalone = urldecode(getQueryValue('standalone'));
$data_source_key = urldecode(getQueryValue('data_source'));


if (!$data_source_key) { die("<font color='red'>Error: Must specify data_source parameter</font>"); }
$dataSource = getDataSources($data_source_key, null);
if(!$dataSource || !(userIsDataSourceRecordOwner($dataSource[0]['record_owner']) || userIsORCA_QA()) )
{
	die("<font color='red'>Error: Access Denied for Data Source/Data Source does not exist</font>");
}
$dataSource = array_pop($dataSource);

$drafts = getDraftsByDataSource($data_source_key);
$records = searchRegistry('', '', $data_source_key, null, null, null);


$approved_records = searchRegistry('', '', $data_source_key, null, null, null,'APPROVED');
if ($approved_records)
{
	array_merge($records, $approved_records);
}


foreach ($records AS &$record)
{
	$record = array_pop(getRegistryObject($record['registry_object_key']));
}




?>

<?php if ($standalone):?>
<html>
<head>
<link rel="stylesheet" href="<?php echo eAPP_ROOT; ?>orca/_styles/data_source_report.css" />
</head>
<body>
<?php endif; ?>


<div>
<?php
if (getQueryValue('type') == "quality"):
// Box Header
?>

	<div id="headerContainer">

		<div class="left">
			<h3>Data Source Quality Report</h3>
			<span id="dataSourceNameTitle"><?php echo $dataSource['title'];?></span><br/>
			<a href="<?php echo eAPP_ROOT; ?>orca/manage/my_records.php?data_source=<?php echo rawurlencode($dataSource['data_source_key']);?>"><i><?php echo $dataSource['data_source_key'];?></i></a>
		</div>

		<div class="right">
			<img src="<?php echo eAPP_ROOT; ?>_images/_logos/ands_logo_white_200px.jpg" alt="ANDS Logo - white background" />
		</div>

	</div>
	<br class="clear"/>
	<hr class="blackRule" />



	<?php
	if (count($records) == 0 && count($drafts) == 0)
	{
		echo "<h3><i>No records found!</i></h3>";
	}

	if (count($records) != 0)
	{
		echo "<h3>Published Records</h3>";
	}
	foreach($records AS $record):
	$metadata_required_errors = substr_count($record['quality_test_result'], "<span class=\"warning\">");
	$metadata_recommended_errors = substr_count($record['quality_test_result'], "<span class=\"info\">");
	$qa_string =  "<span class='metadatawarning'>" . $metadata_required_errors . " " . depluralise("required fields", $metadata_required_errors) . "  missing </span> " .
					" / <span class='metadatainfo'>" . $metadata_recommended_errors . " " . depluralise("recommended fields", $metadata_recommended_errors) . " missing </span>";
	?>
		<table class="qualityReportTable">

		<tr>
		<td style="width:100px; text-align:center;">
		<?php echo getRegistryObjectStatusSpan($record['status']);?><br/>
		<?php echo $record['registry_object_class']; ?>
		</td>
		<td class="regObjTitleInfo" style="width:350px;">
		<b><?php echo $record['display_title'];?></b><br/>
		<i><a href="<?php echo eAPP_ROOT . "orca/view.php?key=" . rawurlencode($record['registry_object_key']); ?>" target="_blank"><?php echo elipsesLimit($record['registry_object_key'], 60);?></a></i>
		</td>
		<td style="min-width:550px;">
		<b>Quality Level: <?php echo $record['quality_level'];?></b> ( <?php echo $qa_string;?>)<br/>
		<?php echo $record['quality_test_result'];?>
		</td>
		</tr>

		</table>
		<br/>
	<?php endforeach; ?>



	<?php
	if (count($drafts) != 0)
	{
		echo "<h3>Other Records</h3>";
	}
	foreach($drafts AS $draft):
	$metadata_required_errors = substr_count($draft['quality_test_result'], "<span class=\"warning\">");
	$metadata_recommended_errors = substr_count($draft['quality_test_result'], "<span class=\"info\">");
	$qa_string =  "<span class='metadatawarning'>" . $metadata_required_errors . " " . depluralise("required fields", $metadata_required_errors) . "  missing </span> " .
					" / <span class='metadatainfo'>" . $metadata_recommended_errors . " " . depluralise("recommended fields", $metadata_recommended_errors) . " missing </span>";
	?>
		<table class="qualityReportTable">

		<tr>
		<td style="width:100px; text-align:center;">
		<?php echo getRegistryObjectStatusSpan($draft['status']);?><br/>
		<?php echo $draft['class']; ?>
		</td>
		<td>
		<div class="regObjTitleInfo" style="width:350px;">
			<b><?php echo $draft['registry_object_title'];?></b><br/>
			<i><a href="<?php echo eAPP_ROOT . "orca/manage/add_".strtolower($draft['class'])."_registry_object.php?data_source=".rawurlencode($data_source_key)."&key=" . rawurlencode($draft['draft_key']); ?>" target="_blank"><?php echo elipsesLimit($draft['draft_key'], 60);?></a></i>
		</div>
		</td>
		<td style="min-width:550px;">
		<b>Quality Level: <?php echo $draft['quality_level'];?></b> ( <?php echo $qa_string;?>)<br/>
		<?php echo $draft['quality_test_result'];?>
		</td>
		</tr>

		</table>
		<br/>
	<?php endforeach; ?>

<?php
endif;
?>
</div>


<?php if ($standalone):?>
</body>
</html>
<?php endif; ?>