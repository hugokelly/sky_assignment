<?php
class Welcome_model extends CI_Model{
	
	function __construct(){
		parent::__construct();
		$this->load->database();
	}
	
	//insert function for the python alternative ingest function
	function ingest($now_unix, $row_count){
		$return = [];
		for($x = 0 ; $x < $row_count ; $x++){
			//calculate metrics
			$timestamp_unix = $now_unix - $x * 60;
			$data = [
				'timestamp' => $timestamp_unix,
				'cpuLoad' => random_int(0, 99) + (random_int(0, PHP_INT_MAX - 1) / PHP_INT_MAX),
				'concurrency' => random_int(0, 50000)
			];
			//actual insert and result collecting
			if($this->db->insert('cpu_log', $data)){
				$return[] = 'Inserted Row @ '.$timestamp_unix.' ('.date('Y/m/d H:i', $timestamp_unix).')';
			}else{
				$return[] = 'Failed Inserting Row @ '.$timestamp_unix.' ('.date('Y/m/d H:i', $timestamp_unix).')';
			}
		}
		return $return;
    }
    
}