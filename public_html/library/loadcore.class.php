<?php

class LoadCore {
	
	private $q; // page
	private $params = array(); // all params
	
	
	public function __construct($params) {

		
	}
	
	private function load() {
		
		global $conn;
		
		// First check that file exists in root folder
		if(file_exists( ROOT . DS . 'application' . DS . strtolower($this->q) . '.php' )) {
			
			require_once ( ROOT . DS . 'application' . DS . strtolower($this->q) . '.php' );
		}
		// if nout found, it might be a plugin: /plugins/pluginname/pluginname/.php
		elseif(is_dir( ROOT . DS . 'plugins' . DS . strtolower($this->q) )) {
			loadPlugin($this->q);
		}
		// else load index
		else require_once ( ROOT . DS . 'application' . DS . 'index.php' );
		
	}
	
	public function __destruct() {
		
		require_once( ROOT . DS . 'config' . DS . 'html' . DS . 'footer.php' ); // require footer
	}
}