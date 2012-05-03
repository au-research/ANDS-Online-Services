<?php
/*
Copyright 2011 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*******************************************************************************/

class Menu_model extends CI_Model {
	
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Return an array of menu items (ordered and tree-structured) 
     * which are permitted by the activities array which is passed.
     * 
     * Some menu items are restricted by activity (so only users with
     * that permission can view the menu item and access the contents). 
     * 
     * @param $permittedActivities Array of activities which are permitted
     */
    function fetchMenu($permittedActivities)
    {
    	return $this->getMenuItemsByActivities($permittedActivities);
    }
    
    
    /*
     * Recursive function to only return menu items which are permitted (by the permitted activities array). 
     * If the activity of a parent menu is not permitted, then its children menu items will not be permitted either,
     * even if we have access to their relevant activity. 
     */
    private function getMenuItemsByActivities(array $permittedActivities = array(), $parent_menu_name=null)
    {
    	$ret = array();
		$menu_items = $this->getAllMenuItems($parent_menu_name);
       	foreach($menu_items->result() AS $menuItem)
    	{
    		if (is_null($menuItem->required_activity) || in_array($menuItem->required_activity, $permittedActivities))
    		{
	    		$ret[$menuItem->item_id] = array(
	    										'name'=>$menuItem->name,
	    										'link'=>$menuItem->link, 
	    										'child_items'=>array()
	    									);
	    	
	    		$ret[$menuItem->item_id]['child_items'] = $this->getMenuItemsByActivities($permittedActivities, $menuItem->item_id);
    		}					
    	}
    	
    	return $ret;
  		
    }
    
    
    /*
     * Get all menu items (taking a parent menu parameter to only return child items of that parent menu object)
     */
    private function getAllMenuItems($parentMenu)
    {    	
    	return $this->db->query("SELECT * FROM dba.tbl_menu_items WHERE parent_item_id = ? " . (is_null($parentMenu) ? "OR parent_item_id IS NULL " : "" ) . "ORDER BY \"order\" ASC", array($parentMenu));
    }
    
  
    

}