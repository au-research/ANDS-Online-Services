<?php

/* use static definitions to only load the transform 
 * XSLT once
 */
class Transforms {
	static $qa_transformer = NULL;
	static $qa_level_transformer = NULL;
	static $extrif_to_solr_transformer = NULL;
	static $extrif_to_html_transformer = NULL;
	static $extrif_to_form_transformer = NULL;
	static $feed_to_rif_transformer = NULL;
	
	static function get_qa_transformer()
	{
		if (is_null(self::$qa_transformer))
		{
			$rmdQualityTest = new DomDocument();
			$rmdQualityTest->load('application/modules/registry_object/transforms/quality_report.xsl');
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
			$rmdQualityTest->load('application/modules/registry_object/transforms/level_report.xsl');
			$qualityTestproc = new XSLTProcessor();
			$qualityTestproc->importStyleSheet($rmdQualityTest);
			self::$qa_level_transformer =	$qualityTestproc;
		}

		return self::$qa_level_transformer;
	}
	
	static function get_extrif_to_solr_transformer()
	{
		if (is_null(self::$extrif_to_solr_transformer))
		{
			$extRifToSOLR = new DomDocument();
			$extRifToSOLR->load('application/modules/registry_object/transforms/extrif_to_solr.xsl');
			$extRifToSOLRproc = new XSLTProcessor();
			$extRifToSOLRproc->importStyleSheet($extRifToSOLR);
			self::$extrif_to_solr_transformer =	$extRifToSOLRproc;
		}

		return self::$extrif_to_solr_transformer;
	}
	
	static function get_extrif_to_html_transformer()
	{
		if (is_null(self::$extrif_to_html_transformer))
		{
			$extRifToHtml = new DomDocument();
			$extRifToHtml->load('application/modules/registry_object/transforms/extrif_to_html.xsl');
			$extRifToHtmlproc = new XSLTProcessor();
			$extRifToHtmlproc->importStyleSheet($extRifToHtml);
			self::$extrif_to_html_transformer =	$extRifToHtmlproc;
		}

		return self::$extrif_to_html_transformer;
	}
	
	static function get_extrif_to_form_transformer()
	{
		if (is_null(self::$extrif_to_form_transformer))
		{
			$extRifToForm = new DomDocument();
			$extRifToForm->load('application/modules/registry_object/transforms/extrif_to_form.xsl');
			$extRifToFormproc = new XSLTProcessor();
			$extRifToFormproc->importStyleSheet($extRifToForm);
			self::$extrif_to_form_transformer =	$extRifToFormproc;
		}

		return self::$extrif_to_form_transformer;
	}
	
	static function get_feed_to_rif_transformer()
	{
		if (is_null(self::$feed_to_rif_transformer))
		{
			$getRifFromFeed = new DomDocument();
			$getRifFromFeed->load('application/modules/registry_object/transforms/extract_rif_from_feed.xsl');
			$getRifFromFeedproc = new XSLTProcessor();
			$getRifFromFeedproc->importStyleSheet($getRifFromFeed);
			self::$feed_to_rif_transformer =	$getRifFromFeedproc;
		}

		return self::$get_feed_to_rif_transformer;
	}
	
}		