<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * OAI Provider: Records model
 *
 * OAI Records are sources from registry_objects
 *
 *
 * @author Steven McPhillips <steven.mcphillips@gmail.com>
 * @package ands/services/oai
 *
 */
class Records extends CI_Model
{

	public function get($set,
			    $after=false,
			    $before=false,
			    $start=0)
	{
		$this->load->model('oai/Sets', 'sets');
		$this->load->model('registry_object/Registry_objects', 'ro');
		$records = array();
		$rawclause = array("registry_objects.status in" => "('approved', 'deleted')");
		$clause = array();
		if ($after)
		{
			$clause["registry_object_attributes.value >="] = $after->getTimestamp();
		}

		if ($before)
		{
			$clause["registry_object_attributes.value <="] = $before->getTimestamp();
		}

		if ($after or $before)
		{
			$clause["registry_object_attributes.attribute"] = "updated";
		}

		if ($set)
		{
			$from_ids = $this->sets->getIDsForSet($set);
		}
		else
		{
			$from_ids = false;
		}

		$ro_response = $this->ro->get($clause,
					      $from_ids,
					      100,
					      $start,
					      'registry_objects.registry_object_id',
					      'asc',
					      $rawclause);

		$count = $ro_response['count'];
		foreach ($ro_response['rows'] as $ro)
		{
			$record = new _record($ro->registry_object_id,
					      null,
					      $this->db,
					      $ro->status);
			$record->sets = $this->sets->get($record->id);
			$records[] = $record;
		}
		if (count($records) == 0)
		{
		    throw new Oai_NoRecordsMatch_Exceptions();
		}
		else
		{
		    return array('records' => $records,
				 'cursor' => $start + count($records),
				 'count' => $count);
		}
	}

	/**
	 * Get the OAI sets associated with this record ID
	 * @param an OAI identifier
	 * @return an array of `_set`s
	 */
	public function sets($ident)
	{
		$record = $this->identify($ident);
		return $record->sets();
	}

	/**
	 * Find the earliest record: used for the `Identify` verb
	 * @return the oldest known `created` timestamp in ISO8601 format.
	 */
	public function earliest()
	{
		try
		{
			$oldest = $this->db->select_min("value")
				->get_where("registry_object_attributes",
					    array("attribute" => "created"))->row()->value;
		}
		catch (Exception $e)
		{
			$oldest = gmdate('Y-m-d\TH:i:s\+\Z', gmmktime());
		}

		return gmdate('Y-m-d\TH:i:s\+\Z', $oldest);

	}

	/**
	 * Retrieve a record specified by an OAI identifier
	 * @param the OAI identifier
	 * @return a `_record`
	 * @throw "bad identifier" Exception if `$ident` doesn't yeild a valid id
	 * @throw "record not found" Exception if `$ident` id doesn't yeild a record
	 */
	public function identify($ident)
	{
		#ident looks like 'oai:[host]::id'
		if (!preg_match('/^oai:.*?::[0-9]+/', $ident))
		{
			throw new Oai_BadArgument_Exceptions("malformed identifier");
		}
		$ident = explode("::", $ident);
		try
		{
			if (count($ident) < 2)
			{
				throw new Exception;
			}
			else
			{
				$id = (int)$ident[1];
				$query = $this->db->get_where("registry_objects",
							      array("registry_object_id" => $id));
				if ($query->num_rows > 0)
				{
					$rec = $query->row();

					return new _record($id,
							   $rec,
							   $this->db,
							   $rec->status);
				}
				else
				{
					throw new Oai_NoRecordsMatch_Exceptions("record not found");
				}
			}
		}
		catch (OAI_Exceptions $e)
		{
			throw $e;
		}
		catch (Exception $ee)
		{
			throw new Oai_BadArgument_Exceptions("bad identifier");
		}
	}

	public function __construct()
	{
		parent::__construct();
		include_once("_record.php");
	}
}
?>
