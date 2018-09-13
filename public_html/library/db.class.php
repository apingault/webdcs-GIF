<?php

class Db {
    
	private $_dbHandle;
    private $_result;
	private $table;

    /** Connects to database **/

    function connect($address, $account, $pwd, $name) {
        
		$this->_dbHandle = @mysql_connect($address, $account, $pwd);
        
		if ($this->_dbHandle != 0) {
           
		    if (mysql_select_db($name, $this->_dbHandle)) {
               
				$this->query("SET NAMES utf8");
			    return 1;
            }
            else {
                
				return 0;
            }
        }
        else {
            
			return 0;
        }
    }

    /** Disconnects from database **/

    function disconnect() {
        
		if (@mysql_close($this->_dbHandle) != 0) {
           
		    return 1;
        }  
		else {
            
			return 0;
        }
    }
	
	function query($query, $singleResult = 0) {

		$this->_result = mysql_query($query, $this->_dbHandle);
		return $this->_result;
	}


    function getError() {
        
		return mysql_error($this->_dbHandle);
    }
	
	function __destruct() {
	
		echo (DEVELOPMENT_ENVIRONMENT == TRUE ) ? $this->getError() : NULL;	
	}
}
