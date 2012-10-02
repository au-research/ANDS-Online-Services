<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class _record
{
	public $id;
	private $data;
	private $db;
	private $header;
	public $sets;
	private $status;

	/**
	 * @ignore
	 */
	public function __construct($id, $data, &$db, $status=null)
	{
		$this->id = $id;
		$this->data = $data;
		$this->db = $db;
		$this->status = $status;
	}


	public function status()
	{
		return $this->status;
	}


	public function header()
	{
		$this->header = array();
		$this->header['identifier'] = $this->identifier();
		$this->header['datestamp'] = $this->latest();
		if (isset($this->sets))
		{
			if (is_array($this->sets))
			{
				$this->header['sets'] = $this->sets;
			}
			else
			{
				$this->header['sets'] = array($this->sets);
			}
		}
		return $this->header;
	}

	/*
	 * Return an identifier template for this record. Needs to be passed through
	 * [s]printf, with the sole argument of the provider hostname. eg:
	 * `sprintf(_rec->identifier(), "ands.org.au");`
	 * @return an identifier template string
	 */
	public function identifier()
	{
		return sprintf("oai:%s::%d", "%s", $this->id);
	}

	/**
	 * Retrieve the latest timestamp for this record
	 */
	public function latest()
	{
		$created;
		$updated;
		//$deleted;

		foreach (array("created", "updated") as $type)
		{
			$query = $this->db->select_max("value")
				->get_where("registry_object_attributes",
					    array("registry_object_id" => $this->id,
						  "attribute" => $type));
			if ($query->num_rows() > 0)
			{
				$row = $query->result();
				$$type = $row[0]->value;
			}
		}
		return date('c', max(array($created, $updated)));
	}

}

?>