<?php
function initRoles()
{
	$CI =& get_instance();
	$CI->load->library('session');
	if (!$CI->session->userdata(AUTH_ROLE_ARRAY))
	{
		$CI->session->set_userdata(AUTH_ROLE_ARRAY, array(AUTH_ROLE_DEFAULT_ATTRIBUTE));
	}
}
initRoles();

function appendRoles(array $role_array)
{
	$CI =& get_instance();
	$CI->load->library('session');
	if ($CI->session->userdata(AUTH_ROLE_ARRAY))
	{
		$CI->session->set_userdata(AUTH_ROLE_ARRAY, array_merge($role_array,$CI->session->userdata(AUTH_ROLE_ARRAY)));
	}
	else
	{
		$CI->session->set_userdata(AUTH_ROLE_ARRAY, $role_array);
	}
}


function hasRole($rolename)
{
	$CI =& get_instance();
	$user_roles = $CI->session->userdata(AUTH_ROLE_ARRAY);
	if ($user_roles)
	{
		if (in_array($rolename, $user_roles))
		{
			return true;
		}
	}
	return false;
}


function isLoggedIn()
{
	return hasRole('AUTHENTICATED_USER');
	
}

function loggedInName()
{
	$CI =& get_instance();
	if ($CI->session->userdata('AUTH_USER_NAME'))
	{
		return $CI->session->userdata('AUTH_USER_NAME');
	}
	else
	{
		return "unnamed user";	
	}
}

function loggedInUserID()
{
	$CI =& get_instance();
	if ($CI->session->userdata('AUTH_USER_ID'))
	{
		return $CI->session->userdata('AUTH_USER_ID');
	}
	else
	{
		throw new Exception("Requested User ID for non-authenticated user");
	}
}

function loggedInUserPrefix()
{
	$id = loggedInUserID();
	return substr($id,0, strpos($id, '::'));
}

	