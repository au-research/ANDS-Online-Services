<?php

function wrapRegistryObjects($xml)
{
	
	$return = "";
	$return .= '<registryObjects xmlns="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://ands.org.au/standards/rif-cs/registryObjects http://services.ands.org.au/documentation/rifcs/schema/registryObjects.xsd">' . NL; 
	$return .= $xml;
	$return .= '</registryObjects>';
	return $return;		
}

function stripXMLHeader($xml)
{
  return preg_replace("/<\?xml (.*)\?>/s", "", $xml);
}

function php2ini($array)
{
    $res = array();
    foreach($array as $key => $val)
    {
        if(is_array($val))
        {
            $res[] = "[$key]";
            foreach($val as $skey => $sval) $res[] = "$skey = ".(is_numeric($sval) ? $sval : '"'.$sval.'"');
        }
        else $res[] = "$key = ".(is_numeric($val) ? $val : '"'.$val.'"');
    }
    return implode("\r\n", $res);
}


/* Transform an array to XML */
function json_to_xml($obj){
  $str = "";
  if(is_null($obj))
  {
    return "<null/>";
  }
  elseif(is_array($obj)) 
  {
      //a list is a hash with 'simple' incremental keys
    $is_list = array_keys($obj) == array_keys(array_values($obj));
    if(!$is_list) {
      foreach($obj as $k=>$v)
        $str.="<$k>".json_to_xml($v)."</$k>".NL;
    } else {
      $str.= "<list>";
      foreach($obj as $v)
        $str.="<item>".json_to_xml($v)."</item>".NL;
      $str .= "</list>";
    }
    return $str;
  }
  elseif(is_string($obj))
  {
    return htmlspecialchars($obj) != $obj ? "<![CDATA[$obj]]>" : $obj;
  } 
  elseif(is_scalar($obj))
    return $obj;
  else
    throw new Exception("Unsupported type $obj");
}
	