<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once('provider.php');
/**
 * OAI 'Identify' processor
 *
 *
 * @author Steven McPhillips <steven.mcphillips@gmail.com>
 * @package ands/services/oai
 *
 */
class Identify_provider extends Provider
{
	public function process($token=false)
	{
		return $token;
	}

}
?>
