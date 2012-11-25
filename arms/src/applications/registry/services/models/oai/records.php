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
		$args = array();
		$args['rawclause'] = array("registry_objects.status in" => "('published', 'deleted')");
		$args['clause'] = array();
		$args['wherein'] = false;
		if ($after)
		{
			$args["clause"]["registry_object_attributes.value >="] = $after->getTimestamp();
		}

		if ($before)
		{
			$args["clause"]["registry_object_attributes.value <="] = $before->getTimestamp();
		}

		if ($after or $before)
		{
			$args["clause"]["registry_object_attributes.attribute"] = "updated";
		}

		if ($set)
		{
			$args["wherein"] = $this->sets->getIDsForSet($set);
		}


		$count = $this->ro->_get(array(array('args' => $args,
						     'fn' => function($db, $args) {
							     $db->select("count(distinct(registry_objects.registry_object_id))")
								     ->from("registry_objects")
								     ->join("data_sources",
									    "data_sources.data_source_id = registry_objects.data_source_id",
									    "inner")
								     ->join("registry_object_attributes",
									    "registry_object_attributes.registry_object_id = registry_objects.registry_object_id",
									    "inner")
								     ->where($args['rawclause'], null, false)
								     ->where($args['clause']);
							     if ($args['wherein'])
							     {
								     $db->where_in("registry_objects.registry_object_id",
										   $args['wherein']);
							     }
							     return $db;
						     })),
					 false);

		if (!is_array($count))
		{
			throw new Oai_NoRecordsMatch_Exceptions();
		}
		else
		{
			$count = $count[0]["count(distinct(registry_objects.registry_object_id))"];
		}

		$records = $this->ro->_get(array(array('args' => $args,
						       'fn' => function($db, $args) {
							       $db->distinct()
								       ->select("registry_objects.registry_object_id")
								       ->from("registry_objects")
								       ->join("data_sources",
									      "data_sources.data_source_id = registry_objects.data_source_id",
									      "inner")
								       ->join("registry_object_attributes",
									      "registry_object_attributes.registry_object_id = registry_objects.registry_object_id",
									      "inner")
								       ->where($args['rawclause'], null, false)
								       ->where($args['clause']);
							       if ($args['wherein'])
							       {
								       $db->where_in("registry_objects.registry_object_id",
										     $args['wherein']);
							       }
							       $db->order_by("registry_objects.registry_object_id", "asc");
							       return $db;
						       })),
					   true,
					   100,
					   $start);
		if (isset($records))
		{
			foreach ($records as &$ro)
			{
				$ro = new _record($ro, $this->db);
				$ro->sets = $this->sets->get($ro->id);
			}
			return array('records' => $records,
			     'cursor' => $start + count($records),
			     'count' => $count);
		}
		else
		{
			return array('records' => 0,
				'cursor' => 0,
				'count' => 0);
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

					return new _record($rec, $this->db);
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
