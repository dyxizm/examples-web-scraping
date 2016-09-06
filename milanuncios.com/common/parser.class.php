<?php


class parser
{
	
	private $config;	
	private $mysqli;	
	private $log = array();
	private $dateLog;

	
	function __construct(){	
		$this->config = require __DIR__ . '/../config.php';
	}


	public function run(){	
		foreach($this->config['module'] as $moduleName => $config)
		{
			if($config['on']){
				$module = new $moduleName;
				$module->run();	
				$this->logView($module->log);			
			}
		}
	}


	private function logView($logOut){
		echo nl2br("\ndate {$logOut['date']}, module {$logOut['module']}, all_processed_ad {$logOut['all_processed_ad']}, added {$logOut['added']}, already_added {$logOut['already_added']}, error {$logOut['error']}, time  {$logOut['time']}\n");		
	}


}
