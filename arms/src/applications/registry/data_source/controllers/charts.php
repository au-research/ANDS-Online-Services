<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Data Source Charts controller
 * 
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @see ands/datasource/_data_source
 * @package ands/datasource
 * 
 */
class Charts extends MX_Controller {

	private $max_chart_rows = 10;

	/**
	 * 
	 * 
	 * 
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param 
	 * @return [JSON] output
	 */
	public function getRegistryObjectProgression($id)
	{
		$chart_data = array();

		$this->load->model("data_sources","ds");
		// xxx: ACL
		$dataSource = $this->ds->getByID($id);//get everything

		if ($dataSource)
		{
			/* Iteratively get chart data, first try  */ 
			// Assume daily entries, if too many, expand to monthly 
			$date_format = "%b"; // i.e. 4-Jan (formats: http://www.jqplot.com/docs/files/plugins/jqplot-dateAxisRenderer-js.html)
			$query =  $this->aggregateRecordCreatedProgression($dataSource, ONE_DAY);
			// Condense the chart if too many individual values...
			if ($query->num_rows() > $this->max_chart_rows)
			{
				$date_format = "%b-%y"; // i.e. Jan-13
				$query = $this->aggregateRecordCreatedProgression($dataSource, ONE_MONTH);
			}

			// First entry when the DS was created (val = 0)
			if($dataSource->created)
			{
				$chart_data[] = array(date(DATE_RFC822, $dataSource->created), 0);
			}

			// Loop through the individual results
			$cumulative_count = 0;
			foreach ($query->result_array() AS $result)
			{
				$cumulative_count += (int)$result['count'];
				$chart_data[] = array(date(DATE_RFC822, $result['datestamp']), $cumulative_count );
			}

			// If we (still) have too many rows, just start the chart 
			// from the height max_chart_rows away from now
			if (count($chart_data) > $this->max_chart_rows+1)
			{
				$chart_data = array_slice($chart_data, -$this->max_chart_rows);
			}

			echo json_encode(array("table"=>$chart_data, "formatString" => $date_format));

		}
		else
		{
			throw new Exception ("Invalid data source ID.");
		}
	}

	

	private function aggregateRecordCreatedProgression($_data_source, $interval)
	{
	
		return $query = $this->db->query("SELECT COUNT(*) AS count, FLOOR(value/".$interval.")*".$interval." AS datestamp 
										FROM registry_objects NATURAL JOIN registry_object_attributes 
										WHERE data_source_id = ".$_data_source->id." AND attribute='created' GROUP BY datestamp;");
	}



	/**
	 * Get a list of data sources
	 * 
	 * 
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param [INT] page
	 * @todo ACL on which data source you have access to, error handling
	 * @return [JSON] results of the search
	 */
	public function getDataSources($page=1){
		//$this->output->enable_profiler(TRUE);
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$jsonData = array();
		$jsonData['status'] = 'OK';

		$this->load->model("data_sources","ds");

		//Limit and Offset calculated based on the page
		$limit = 16;
		$offset = ($page-1) * $limit;

		$dataSources = $this->ds->getAll($limit, $offset);

		$this->load->model("registry_object/registry_objects", "ro");

		$items = array();
		foreach($dataSources as $ds){
			$item = array();
			$item['title'] = $ds->title;
			$item['id'] = $ds->id;

			$item['counts'] = array();
			foreach ($this->ro->valid_status AS $status){
				if($ds->getAttribute("count_$status")>0){
					array_push($item['counts'], array('status' => $status, 'count' =>$ds->getAttribute("count_$status")));
				}
			}
			array_push($items, $item);
		}
		
		$jsonData['items'] = $items;
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}

	
	/**
	 * @ignore
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
}
