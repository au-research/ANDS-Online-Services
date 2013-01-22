<?php
/**
 * Crosswalk to transform records from an NHMRC-provided
 * .csv data file into RIFCS with activity and party records. 
 * 
 * Business rules were prepared by Amir Aryani (ANDS) in
 * Crosswalk-NHMRC-RIF-CS-1-4.doc. 
 *
 * All activies will be created with keys in the 
 * purl.org/au-research/grants/nhmrc/<grant ID>
 *
 * Associated parties will have random keys and will be linked 
 * to the appropriate collections by related object. 
 *
 * Note: THIS CLASS IS FOR MAPPING PARTIES. See alternative 
 *       class which maps activities to RIFCS. 
 *
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @created 21/01/2013
 */

class NHMRC_Parties_to_RIFCS extends Crosswalk
{

    const NHMRC_GROUP = 'National Health and Medical Research Council';
    const NHMRC_ORIGINATING_SOURCE = 'http://www.nhmrc.gov.au/';
    const NHMRC_GRANT_PREFIX = 'http://purl.org/au-research/grants/nhmrc/';
    const NHMRC_KEY_PREFIX = 'http://nhmrc.gov.au/person/';
    private $parsed_array = array();
    private $csv_headings = array();

	/**
	 * Identify this crosswalk (give a user-friendly name)
	 */
	public function identify()
	{
		return "NHMRC Party Data to RIFCS";
	}

    /**
     * Internal name for this metadataFormat
     */
    public function metadataFormat()
    {
        return "nhmrc_parties";
    }

    /**
     * Validate that this payload is valid CSV
     * Note: doesn't check that the fields exist/are correct
     */
 	public function validate($payload)
    {
        // Mac-PHP line endings bugfix:
        ini_set('auto_detect_line_endings',true);

    	$valid = true; 

        // Bizarrely, PHP doesn't support multiline in getcsv :-/
        foreach(explode("\n", $payload) AS $line)
        {
            $csv = str_getcsv($line);
            // Pop the weird blankspace element
            if (count($csv) > 0)
            {
                $this->parsed_array[] = $csv;
            }
        }
        if (count($this->parsed_array) == 0)
        {
            $valid = false;
        }

        return $valid;
    }


    /**
     * Do the transformation of the payload to RIFCS.
     *
     * Resultant RIFCS includes the native row (and headers) 
     * in a relatedInfo[type='nativeFormat'] element (which is
     * transformed away during the ingest process).
     *
     * @return string A valid RIFCS XML string (including wrapper)
     */
    public function payloadToRIFCS($payload)
    {
        unset($payload);
        $rifcs_elts = '';

        // First line has the column headings
        $this->csv_headings = array_shift($this->parsed_array);
        // Loop through each row, create a registry object for ecah
    	while($csv_values = array_shift($this->parsed_array))
        {
            // Map the column headings to each field, to simplify lookup
            $row = $this->mapCSVHeadings($csv_values);
            if (!isset($row['DW_INDIVIDUAL_ID'])) continue; //skip blank rows

            // Skip individuals related to old grants (these bounds represent records newer than 2009, inclusive)
            if (($row['GRANT_ID'] < 581510 || ($row['GRANT_ID'] >= 640353 && $row['GRANT_ID'] < 1000507))) continue; //skip

            /***
            * START BUILDING THE REGISTRY OBJECT
            ***/
            // Default group: <registryObject group="National Health and Medical Research Council">
            $registryObject = '<registryObject group="'. self::NHMRC_GROUP .'">' . NL;

            // Create the purl key: http://purl.org/au-research/grants/nhmrc/1501
            $registryObject .=  '<key>' . self::NHMRC_KEY_PREFIX . md5($row['DW_INDIVIDUAL_ID']). '</key>' . NL;
            $registryObject .=  '<originatingSource>'.self::NHMRC_ORIGINATING_SOURCE.'</originatingSource>' . NL;

            // It's an activity, duh? See activity_type business logic above
            $registryObject .=  '<party type="person">' . NL;

            // Only include the alternative name if it is different to the primary
            $registryObject .=      '<name type="primary">' . NL;
            $registryObject .=          '<namePart type="title">'.$row['TITLE'].'</namePart>' . NL;
            $registryObject .=          '<namePart type="family">'.$row['LAST_NAME'].'</namePart>' . NL;
            $registryObject .=          '<namePart type="given">'.$row['FIRST_NAME'].'</namePart>' . NL;
            $registryObject .=      '</name>' . NL;

            // Relate to the NHMRC's NLA key
            $registryObject .=      '<relatedObject>' . NL;
            $registryObject .=         '<key>'. self::NHMRC_GRANT_PREFIX . $row['GRANT_ID'] . '</key>' . NL;
            $registryObject .=         '<relation type="isParticipantIn" />' . NL;
            $registryObject .=      '</relatedObject>' . NL;

            // XXX: Relate to NLA identifier for GRANT_ADMIN_INSTITUTION

            // Include the native format
            $registryObject .=      '<relatedInfo type="'.NATIVE_HARVEST_FORMAT_TYPE.'">' . NL;
            $registryObject .=          '<identifier type="internal">'.$this->metadataFormat().'</identifier>' . NL;
            $registryObject .=          '<notes><![CDATA[' . NL;   
            // Hide the private DW_INDIVIDUAL_ID
            $csv_values[2] = md5($csv_values[2]);
            // Create the native format (csv) with prepended the column headings
            $registryObject .=              $this->wrapNativeFormat(array($this->csv_headings, $csv_values)) . NL;
            $registryObject .=          ']]></notes>' . NL;
            $registryObject .=      '</relatedInfo>' . NL;
            $registryObject .=    '</party>' . NL;
            $registryObject .='</registryObject>' . NL;

            $rifcs_elts .= $registryObject . NL;
        }

    	return wrapRegistryObjects($rifcs_elts);
    }

    /**
     * Map the column headings to csv fields, creating an 
     * associative array.
     *
     *
     * @return array (associative heading=>value)
     */
    private function mapCSVHeadings(array $csv_values)
    {
        $mapped_values = array();
       // var_dump($this->csv_headings);
        foreach($csv_values AS $idx => $csv_value)
        {
            $csv_value = htmlentities($csv_value);
            $heading = $this->csv_headings[$idx];
            $mapped_values[$heading] = $csv_value;
        }

        return $mapped_values;
    }

    /**
     * Emulate XSLT normalise-space() function.
     */
    private function normalise_space($string)
    {
        return trim(preg_replace("/\s+/", " ", $string));
    }


    /**
     * Wrap this format by simply including the header line and converting to CSV
     */
    public function wrapNativeFormat($payload)
    {
        $response = '';

        foreach($payload AS $row)
        {
            $response .= htmlentities($this->sputcsv($row));
        }

        return trim($response);
    }


    /** 
     * Write out an array to a STRING .csv (from http://php.net/manual/en/function.fputcsv.php) 
     */
    private function sputcsv($row, $delimiter = ',', $enclosure = '"', $eol = "\n")
    {
        static $fp = false;
        if ($fp === false)
        {
            $fp = fopen('php://temp', 'r+'); // see http://php.net/manual/en/wrappers.php.php - yes there are 2 '.php's on the end.
            // NB: anything you read/write to/from 'php://temp' is specific to this filehandle
        }
        else
        {
            rewind($fp);
        }
        
        if (fputcsv($fp, $row, $delimiter, $enclosure) === false)
        {
            return false;
        }
        
        rewind($fp);
        $csv = fgets($fp);
        
        if ($eol != PHP_EOL)
        {
            $csv = substr($csv, 0, (0 - strlen(PHP_EOL))) . $eol;
        }
        
        return $csv;
    }

}