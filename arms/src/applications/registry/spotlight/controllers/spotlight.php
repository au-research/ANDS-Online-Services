<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Spotlight extends MX_Controller {

	function index(){
		$data['js_lib'] = array('core');
		$data['scripts'] = array('spotlight');
		$data['title'] = 'Spotlight CMS';
		$data['less'] = array('spotlight');

		$data['file'] = $this->read();
		$data['items'] = $data['file']['items'];

		$this->load->view('spotlight_cms', $data);
	}

	function read(){
		$this->load->helper('file');
		$file = read_file('./applications/registry/spotlight/assets/spotlight.json');
		//$file = file_get_contents(asset_url('spotlight.json'));
		return json_decode($file,true);
	}

	function write($data){
		$this->load->helper('file');
		write_file('./applications/registry/spotlight/assets/spotlight.json', $data, 'w');
		return true;
	}

	function save($id){
		//var_dump($this->input->post());
		$obj = array(
			'id'=>$id,
			'title'=>$this->input->post('title'),
			'url'=>$this->input->post('url'),
			'img_url'=>$this->input->post('img_url'),
			'content'=>$this->input->post('content'),
		);
		$file = $this->read();
		$items = $file['items'];
		$new_file = array('items'=>array());
		foreach($items as $i){
			if($i['id']==$id){
				$new_file['items'][] = $obj;
			}else{
				$new_file['items'][] = $this->getID($i['id'], $items);
			}
		}
		$this->write(json_encode($new_file));
	}

	function delete($id){
		$file = $this->read();
		$items = $file['items'];
		$new_file = array('items'=>array());
		foreach($items as $i){
			if($i['id']!=$id){
				$new_file['items'][] = $this->getID($i['id'], $items);
			}
		}
		var_dump($new_file);
		$this->write(json_encode($new_file));
	}

	function saveOrder(){
		$new_order = $this->input->post('data');
		$file = $this->read();
		$items = $file['items'];

		$new_file = array('items'=>array());
		foreach($new_order as $o){
			if($item = $this->getID($o, $items)) $new_file['items'][] = $item;
		}
		$this->write(json_encode($new_file));
	}

	function add(){
		$file = $this->read();
		$items = $file['items'];
		$new_file = array('items'=>array());

		$largest_id = 0;
		foreach($items as $i){
			$new_file['items'][] = $this->getID($i['id'], $items);
			if($i['id']>$largest_id) $largest_id = $i['id'];
		}

		$obj = array(
			'id'=>$largest_id+1,
			'title'=>$this->input->post('title'),
			'url'=>$this->input->post('url'),
			'img_url'=>$this->input->post('img_url'),
			'content'=>$this->input->post('content'),
		);
		$new_file['items'][] = $obj;

		$this->write(json_encode($new_file));
	}

	function getID($id, $items){
		foreach($items as $i){
			if($i['id']==$id) return $i;
		}
		return false;
	}
}
	