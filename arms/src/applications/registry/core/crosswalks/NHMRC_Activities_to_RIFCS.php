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
 * Note: THIS CLASS IS FOR MAPPING ACTIVITIES. See alternative 
 *       class which maps parties to RIFCS. 
 *
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @created 21/01/2013
 */

class NHMRC_Activities_to_RIFCS extends Crosswalk
{

    const NHMRC_GROUP = 'National Health and Medical Research Council';
    const NHMRC_ORIGINATING_SOURCE = 'http://www.nhmrc.gov.au/grants/research-funding-statistics-and-data';
    const NHMRC_KEY_PREFIX = 'http://purl.org/au-research/grants/nhmrc/';
    const NHMRC_PROGRAM_TYPE_GROUP = 'Infrastructure Support';
    const NHMRC_NLA_KEY = 'http://nla.gov.au/nla.party-616216';

    private $parsed_array = array();
    private $csv_headings = array();

	/**
	 * Identify this crosswalk (give a user-friendly name)
	 */
	public function identify()
	{
		return "NHMRC Grant Data to RIFCS";
	}

    /**
     * Internal name for this metadataFormat
     */
    public function metadataFormat()
    {
        return "nhmrc_activities";
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
            if (!isset($row['MAIN_FUNDING_GROUP'])) continue; //skip blank rows

            // If MAIN_FUNDING_GROUP == 'Infrastructure Support', then "program", else "project"
            $activity_type = ($row['MAIN_FUNDING_GROUP'] == self::NHMRC_PROGRAM_TYPE_GROUP ? "program" : "project");

            $primary_name = $this->normalise_space($row['SIMPLIFIED_TITLE']);
            $alternative_name = $this->normalise_space($row['SCIENTIFIC_TITLE']);

            $description = htmlentities("
                            Chief Investigator(s): {$row['CIA_NAME']}

                            Total Grant Budget: \$AUD {$row['TOTAL_GRANT_BUDGET']}

                            Application Year: {$row['APPLICATION_YEAR']}
                            Start Year: {$row['START_YR']}
                            End Year: {$row['END_YR']}

                            Main Funding Group: {$row['MAIN_FUNDING_GROUP']}
                            Grant Type (Funding Scheme): {$row['GRANT_TYPE']}
                            Grant Sub Type: {$row['SUB_TYPE']}
            ");

            /***
            * START BUILDING THE REGISTRY OBJECT
            ***/
            // Default group: <registryObject group="National Health and Medical Research Council">
            $registryObject = '<registryObject group="'. self::NHMRC_GROUP .'">' . NL;

            // Create the purl key: http://purl.org/au-research/grants/nhmrc/1501
            $registryObject .=  '<key>' . self::NHMRC_KEY_PREFIX . $row['GRANT_ID']. '</key>' . NL;
            $registryObject .=  '<originatingSource>'.self::NHMRC_ORIGINATING_SOURCE.'</originatingSource>' . NL;

            // It's an activity, duh? See activity_type business logic above
            $registryObject .=  '<activity type="'.$activity_type.'">' . NL;

            // Identifier is the same purl as our key
            $registryObject .=      '<identifier type="purl">' . self::NHMRC_KEY_PREFIX . $row['GRANT_ID'] . '</identifier>';
            
            // Only include the alternative name if it is different to the primary
            $registryObject .=      '<name type="primary"><namePart>'.$primary_name.'</namePart></name>' . NL;
            if($primary_name != $alternative_name) {
            $registryObject .=      '<name type="alternative"><namePart>'.$alternative_name.'</namePart></name>' . NL;
            }

            // The string created above
            $registryObject .=      '<description type="notes">'.trim($description).'</description>' . NL;

            // Include our subjects
            $registryObject .=      '<subject type="local">'.$row['BROAD_RESEARCH_AREA'].'</subject>' . NL;
            $registryObject .=      '<subject type="anzsrc-for">'.$row['FOR_CATEGORY'].'</subject>' . NL;
            $registryObject .=      '<subject type="anzsrc-for">'.$row['FIELD_OF_RESEARCH'].'</subject>' . NL;

            // And any keywords as local subjects
            foreach ($row['keywords'] AS $kw)
            {
                if ($kw != '')
                {
                    $registryObject .= '<subject type="local">'.$kw.'</subject>' . NL;
                }
            }

            // Relate to the NHMRC's NLA key
            $registryObject .=      '<relatedObject>' . NL;
            $registryObject .=         '<key>'. self::NHMRC_NLA_KEY . '</key>' . NL;
            $registryObject .=         '<relation type="isFundedBy" />' . NL;
            $registryObject .=      '</relatedObject>' . NL;

            // XXX: Relate to NLA identifier for GRANT_ADMIN_INSTITUTION

            // Include the native format
            $registryObject .=      '<relatedInfo type="'.NATIVE_HARVEST_FORMAT_TYPE.'">' . NL;
            $registryObject .=          '<identifier type="internal">'.$this->metadataFormat().'</identifier>' . NL;
            $registryObject .=          '<notes><![CDATA[' . NL;   
            // Create the native format (csv) with prepended the column headings
            $registryObject .=              $this->wrapNativeFormat(array($this->csv_headings, $csv_values)) . NL;
            $registryObject .=          ']]></notes>' . NL;
            $registryObject .=      '</relatedInfo>' . NL;
            $registryObject .=    '</activity>' . NL;
            $registryObject .='</registryObject>' . NL;

            $rifcs_elts .= $registryObject . NL;
        }

    	return wrapRegistryObjects($rifcs_elts);
    }

    /**
     * Map the column headings to csv fields, creating an 
     * associative array.
     *
     * Special 1-to-many headings (such as funding years
     * and keywords) are mapped into a second dimension.
     *
     * @return array (associative heading=>value)
     */
    private function mapCSVHeadings(array $csv_values)
    {
        $year_array = array();
        $keyword_array = array();
        $mapped_values = array();
       // var_dump($this->csv_headings);
        foreach($csv_values AS $idx => $csv_value)
        {
            $csv_value = htmlentities($csv_value);
            $heading = $this->csv_headings[$idx];
            if (strpos($heading, 'YR_') === 0)
            {
                $year_array[$heading] = $csv_value;
            }
            else if (strpos($heading,'RESEARCH_KW_') === 0)
            {
                $keyword_array[$heading] = $csv_value;
            }
            else
            {
                $mapped_values[$heading] = $csv_value;
            }
        }

        $mapped_values['keywords'] = $keyword_array;
        $mapped_values['year_funding'] = $year_array;

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
        if (is_array($payload))
        {
            foreach($payload AS $row)
            {
                $response .= htmlentities($this->sputcsv($row));
            }
        }
        else
        {
            return html_entity_decode($payload);
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