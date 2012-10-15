<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class _record
{
	public $id;
	public $sets;
	private $header;
	private $db;
	private $_rec;

	/**
	 * @ignore
	 */
	public function __construct($registry_object, &$db)
	{
		$this->_rec = $registry_object;
		$this->id = $registry_object->registry_object_id;
		$this->db = $db;
	}

	public function is_deleted()
	{
		return strtolower($this->_rec->status) === "deleted";
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

	public function metadata($format, $nestlvl=0)
	{
		$lprefix = "";
		if ($nestlvl > 0)
		{
			foreach (range(0,$nestlvl) as $nest)
			{
				$lprefix .= "\t";
			}
		}
		$output = "";
		$data = false;
		switch($format)
		{
		case 'oai_dc':
			$data = $this->_rec->getOaidc();
			break;
		case 'rif':
			$data = $this->_rec->getRif();
			break;
		}
		if ($data)
		{
			foreach (explode("\n", $data) as $line)
			{
				if (empty($line))
				{
					continue;
				}
				$output .= $lprefix . $line . "\n";
			}
		}
		return $output;
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