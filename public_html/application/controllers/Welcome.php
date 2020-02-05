<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model('welcome_model');
	}

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index(){
		$this->load->view('welcome');
	}

	//ingest function here (alternative to python script) generates 5 minutes of data with random cpuLoad & concurrency
	public function ingest(){
		$now_timestamp = time();
		//tell the model when to start from and how many rows to insert
		if($results = $this->welcome_model->ingest($now_timestamp, 5)){
			foreach($results as $result){
				echo $result.'<br />';
			}
		//something went wrong
		}else{
			echo 'Failed ingesting data';
		}
	}
}
