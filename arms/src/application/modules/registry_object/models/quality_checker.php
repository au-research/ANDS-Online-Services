<?php


class Quality_checker extends CI_Model {
			
			
	function get_quality_test_result($registry_object, $output_mode = 'xml')
	{
		$xslt_processor = QA_Transforms::get_qa_transformer();
		
		$dom = new DOMDocument();
		$dom->loadXML(wrap_xml($registry_object->getXML()));
		 
		$xslt_processor->setParameter('', 'dataSource', $registry_object->data_source_key);
		$xslt_processor->setParameter('', 'output', $output_mode);
		$xslt_processor->setParameter('', 'relatedObjectClassesStr', ""); // XXX: TODO!!!
		return $xslt_processor->transformToXML($dom);
	}
	
	function get_qa_level_test_result($registry_object)
	{
		$xslt_processor = QA_Transforms::get_qa_level_transformer();
		
		$dom = new DOMDocument();
		$dom->loadXML(wrap_xml($registry_object->getXML()));
		 
		$xslt_processor->setParameter('', 'relatedObjectClassesStr', ""); // XXX: TODO!!!
		return $xslt_processor->transformToXML($dom);
	}
	
	
	
	/*function runQualityCheck($rifcs, $objectClass, $dataSource, $output, $relatedObjectClassesStr='')
{
	global $qualityTestproc;
	$relRifcs = getRelatedXml($dataSource,$rifcs,$objectClass);
	$registryObjects = new DomDocument();
	$registryObjects->loadXML($relRifcs);
	$qualityTestproc->setParameter('', 'dataSource', $dataSource);
	$qualityTestproc->setParameter('', 'output', $output);
	$qualityTestproc->setParameter('', 'relatedObjectClassesStr', $relatedObjectClassesStr);
	$result = $qualityTestproc->transformToXML($registryObjects);
	return $result;
}
	 * */
			
			
	
	/**
	 * @ignore
	 */
	function __construct()
	{
		parent::__construct();
		include_once("_registry_object.php");
	}	
		
}

/* use static definitions to only load the transform 
 * XSLT once
 */
class QA_Transforms {
	static $qa_transformer = NULL;
	static $qa_level_transformer = NULL;
	
	static function get_qa_transformer()
	{
		if (is_null(self::$qa_transformer))
		{
			$rmdQualityTest = new DomDocument();
			$rmdQualityTest->load('application/modules/registry_object/quality_checks/quality_report.xsl');
			$qualityTestproc = new XSLTProcessor();
			$qualityTestproc->importStyleSheet($rmdQualityTest);
			self::$qa_transformer =	$qualityTestproc;
		}

		return self::$qa_transformer;
	}
	
	static function get_qa_level_transformer()
	{
		if (is_null(self::$qa_level_transformer))
		{
			$rmdQualityTest = new DomDocument();
			$rmdQualityTest->load('application/modules/registry_object/quality_checks/level_report.xsl');
			$qualityTestproc = new XSLTProcessor();
			$qualityTestproc->importStyleSheet($rmdQualityTest);
			self::$qa_level_transformer =	$qualityTestproc;
		}

		return self::$qa_level_transformer;
	}
}		