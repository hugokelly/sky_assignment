<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	function __construct(){
		parent::__construct();
		$this->load->library('unit_test');
		$this->load->model('welcome_model');
	}

	public function index(){
		//example test
		$test = 1 + 1;
		$expected_result = 2;
		$test_name = 'Adds one plus one (example test)';
		$this->unit->run($test, $expected_result, $test_name);

		//report output
		echo $this->unit->report();
	}

}
