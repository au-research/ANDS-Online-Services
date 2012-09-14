<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Login extends CI_Controller {

	/**
	 * Authentication Controller for this application.
	 *
	 * Allows other applications to authenticate users against the COSI
	 * user database system and retrieve their associated roles. 
	 * 
	 */
	public function index()
	{
		$this->load->model('user_model');
		var_dump($this->user_model->authenticate('Preprod','abc123', gCOSI_AUTH_METHOD_BUILT_IN));
	}

	
}