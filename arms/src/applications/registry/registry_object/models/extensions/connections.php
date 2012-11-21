<?php


class Connections_Extension extends ExtensionBase
{
	function __construct($ro_pointer)
	{
		parent::__construct($ro_pointer);
	}		
	
	function getConnections()
	{
		$my_connections = array();

		// XXX: TODO: ADD THE LOGIC HERE, RETURN AN ARRAY OF array(registry_object_id=>(class=>$class, key=>$key, relationshipType=>$relationshipType)

		return $my_connections;
	}
	
}