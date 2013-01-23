<?php

if ($suggested_links_contents)
{
	$output = '';

	// Matching identifiers
	if ($suggested_links_contents['identifiers'] && $suggested_links_contents['identifiers']['count'] > 0)
	{
		$count_str = ($suggested_links_contents['identifiers']['count'] == 1 ? "record" : "records");
		$output .= '<h5><a id="ands_identifier_match" href="#">'.$suggested_links_contents['identifiers']['count'].' '.$count_str.'</a> with matching identifiers</h5>';
	}

	// Matching identifiers
	if ($suggested_links_contents['subjects'] && $suggested_links_contents['subjects']['count'] > 0)
	{
		$count_str = ($suggested_links_contents['subjects']['count'] == 1 ? "record" : "records");
		$output .= '<h5><a id="ands_subject_match" href="#">'.$suggested_links_contents['subjects']['count'].' '.$count_str.'</a> with matching subjects</h5>';
	}

	if ($output)
	{
		$output = "<h4>Internal Records</h4>"  .  $output;
		echo $output;
	}

}