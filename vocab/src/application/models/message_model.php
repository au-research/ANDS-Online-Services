<?php
class Message_model extends CI_Model {

	
	
    function __construct()
    {
        parent::__construct();
    }

    /**
     * 
     * 
     * @param $role Role for which we fetch the menu 
     */
    function fetchMenu($permittedActivities)
    {
    	echo menuXmlResponse($this->getMenuItemsByActivities($permittedActivities));
    	
    }
    
    function fetchUnreadCount($role)
    {
    	$unread_messages = $this->db->get_where('dba.tbl_message_recipients', array('date_read'=>'0', 'role_id'=>$role));
    	return $unread_messages->num_rows();
    	
    }
    

    function fetchMessages($role, $start_index=0, $count=5)
    {
    	// Fetch unread messages for this role
    	$query = $this->db->query("SELECT message_id, date_read, date_sent, subject, message FROM dba.tbl_message_recipients rcpt JOIN dba.tbl_message_contents ct ON ct.content_id = rcpt.message_id WHERE rcpt.role_id = ? ORDER BY date_sent ASC LIMIT ? OFFSET ?", array($role, $count, $start_index));
    	$messages = $query->result();
    	
    	// Update the read status of new messages
    	$newly_read = array();
    	foreach ($messages AS $message)
    	{
    		if ($message->date_read == 0)
    		{
    			$newly_read[] = $message->message_id;
    		}
    	}
    
    	if (count($newly_read) > 0)
    	{
    		echo 'woo';
	    	$this->db->flush_cache();
	    	$this->db->where('role_id', $role);
	    	$this->db->where_in('message_id',$newly_read);
	    	$this->db->update('dba.tbl_message_recipients',array('date_read'=>time()));
    	}
    	
    	
    	// Return the message array
    	return $messages;
    	
    }
    

    

}