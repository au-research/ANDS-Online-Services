<?php
/**
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
**/
?>
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends CI_Controller {

	public function index(){
		$this->load->model('Solr');
		$data['json'] = $this->Solr->getNCRISPartners();

		$this->load->library('user_agent');
		$data['user_agent']=$this->agent->browser();
		//echo $data['user_agent'];
		$data['activity_name'] = 'homepage';
		$this->load->view('home_page', $data);
	}

	public function about(){
		$this->load->library('user_agent');
		$data['user_agent']=$this->agent->browser();
		$this->load->view('content/about', $data);
	}

	public function disclaimer(){
		$this->load->library('user_agent');
		$data['user_agent']=$this->agent->browser();
		$this->load->view('content/disclaimer', $data);
	}

	public function help(){
		$this->load->library('user_agent');
		$data['user_agent']=$this->agent->browser();
		$data['activity_name'] = 'help';
		$this->load->view('content/help', $data);
	}

	public function contact(){
		$this->load->library('user_agent');
		$data['user_agent']=$this->agent->browser();
		$data['activity_name'] = 'contact';
		$this->load->view('content/contact_form', $data);
	}
	public function feeds(){
		$this->load->library('user_agent');
		$data['user_agent']=$this->agent->browser();
		$data['activity_name'] = 'feeds';
		$this->load->view('content/feeds', $data);
	}
	public function send(){
		$this->load->library('user_agent');
		$data['user_agent']=$this->agent->browser();
		$name = $this->input->post('name');
		$email = $this->input->post('email');
		$content = $this->input->post('content');

		$this->load->library('email');

		$this->email->from($email, $name);
		$this->email->to('services@ands.org.au');

		$this->email->subject('RDA Contact Us');
		$this->email->message($content);

		$this->email->send();

		echo '<b>Thank you for your response. Your message has been delivered successfully</b>';
	}

	public function homepage(){
		$this->load->model('Solr');
		$data['json'] = $this->Solr->getNCRISPartners();
		$data['activity_name'] = 'homepage';
		$this->load->view('home_page', $data);
	}

	public function sitemap(){

    	parse_str($_SERVER['QUERY_STRING'], $_GET);
    	$solr_url = $this->config->item('solr_url');
    	$ds = '';
    	if(isset($_GET['ds'])) $ds=$_GET['ds'];


    	if($ds==''){
			$fields = array(
				'q'=>'*:*','version'=>'2.2','start'=>0,'rows'=>100, 'wt'=>'json',
				'fl'=>'key'
			);
					/*prep*/
			$fields_string='';
	    	foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }//build the string
	    	$fields_string .= '&facet=true&facet.field=data_source_key';
	    	rtrim($fields_string,'&');

			//echo $solr_url.$fields_string;

			$ch = curl_init();
	    	//set the url, number of POST vars, POST data
			curl_setopt($ch,CURLOPT_URL,$solr_url.'select');//post to SOLR
			curl_setopt($ch,CURLOPT_POST,count($fields));//number of POST var
			curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);//post the field strings
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//return to variable
	    	$content = curl_exec($ch);//execute the curl

	    	//echo 'json received+<pre>'.$content.'</pre>';

	    	$res = json_decode($content);
	    	$dsfacet = $res->{'facet_counts'}->{'facet_fields'}->{'data_source_key'};

			header("Content-Type: text/xml");
			$this->output->set_content_type('text/xml');
			echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

			for($i=0;$i<sizeof($dsfacet);$i+=2){
				echo '<sitemap>';
				echo '<loc>'.base_url().'home/sitemap/?ds='.$dsfacet[$i].'</loc>';
				echo '<lastmod>'.date('Y-m-d').'</lastmod>';
				echo '</sitemap>';
			}

			echo '</sitemapindex>';
		}elseif($ds!=''){
			$q = '*:* +data_source_key:("'.$ds.'")';
			$q = urlencode($q);
			$fields = array(
				'q'=>$q,'version'=>'2.2','start'=>0,'rows'=>50000, 'wt'=>'json',
				'fl'=>'key, url_slug, date_modified'
			);
					/*prep*/
			$fields_string='';
	    	foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }//build the string
	    	rtrim($fields_string,'&');

			//echo $fields_string;

			$ch = curl_init();
	    	//set the url, number of POST vars, POST data
			curl_setopt($ch,CURLOPT_URL,$solr_url.'select');//post to SOLR
			curl_setopt($ch,CURLOPT_POST,count($fields));//number of POST var
			curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);//post the field strings
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//return to variable
	    	$content = curl_exec($ch);//execute the curl

	    	//echo 'json received+<pre>'.$content.'</pre>';

	    	$res = json_decode($content);
	    	$keys = $res->{'response'}->{'docs'};
	    	//var_dump($keys);

			header("Content-Type: text/xml");
			$this->output->set_content_type('text/xml');
			echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

			foreach($keys as $k){
				//var_dump($k);
				echo '<url>';
				if ($k->{'url_slug'})
				{
					echo '<loc>'.base_url().$k->{'url_slug'}.'</loc>';
				}
				else
				{
					echo '<loc>'.base_url().'view/?key='.urlencode($k->{'key'}).'</loc>';
				}
				echo '<changefreq>weekly</changefreq>';
				echo '<lastmod>'.date('Y-m-d', strtotime($k->{'date_modified'})).'</lastmod>';
				echo '</url>';
			}

			echo '</urlset>';
		}
	}

	public function notfound(){
		$this->load->library('user_agent');
		$data['user_agent']=$this->agent->browser();
		$data['message']='Page not found!';
		$this->load->view('layout',$data);
	}
}
?>