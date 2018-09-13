<?php

class DIP {
	
	private $dbh;
	private $hostname = "128.141.143.223";
	private $user = "root";
	private $password = "UserlabDIP++";
	private $dbname = "dip";
	

	public function __construct() {
		
		
		$this->SQLconnect();
	}
	
	function __destruct() {
		

		$this->dbh = null;
	}
	
	private function SQLconnect() {
		
		try {

			$this->dbh = new PDO("mysql:host=".$this->hostname.";dbname=".$this->dbname, $this->user, $this->password);
		}
		catch(PDOException $e) {

			die("Database connection failed: ".$e->getMessage());
		}
	}

	public function getValue($id_name, &$value, &$name, &$unit) {
		

		$value = "n/a";
		$unit = "";

		if(strpos($id_name, '[') !== false) { // mixed DIP ids --> formula

			$needed = array();
			$occurences = $this->getContents($id_name, '[', ']'); // get all occurences between []
			foreach($occurences as $t) {

				$this->getValue($t, $value, $name, $unit);
				$id_name = str_replace('['.$t.']', $value, $id_name);
			}

			$Cal = new Field_calculate();
			$value = $Cal->calculate($id_name);
		}
		else { // single DIP id
			
			$q = $this->dbh->prepare("SELECT * FROM parameters WHERE id_name = '".$id_name."' LIMIT 1");
			$q->execute();
			$res = $q->fetch();
			$name = $res['name'];
			$unit = $res['unit'];
			$table = $res['table_name'];

			$q = $this->dbh->prepare("SELECT ".$id_name." FROM ".$table." ORDER BY timestamp DESC LIMIT 1");
			$q->execute();
			$res = $q->fetch();
			$value = floatval($res[0]);
		}
	}
	
	
	
	// From http://stackoverflow.com/questions/27078259/get-string-between-find-all-occurrences-php
	private function getContents($str, $startDelimiter, $endDelimiter) {

		$contents = array();
		$startDelimiterLength = strlen($startDelimiter);
		$endDelimiterLength = strlen($endDelimiter);
		$startFrom = $contentStart = $contentEnd = 0;
		while(false !== ($contentStart = strpos($str, $startDelimiter, $startFrom))) {
			$contentStart += $startDelimiterLength;
			$contentEnd = strpos($str, $endDelimiter, $contentStart);
			if(false === $contentEnd) {
			  break;
			}
			$contents[] = substr($str, $contentStart, $contentEnd - $contentStart);
			$startFrom = $contentEnd + $endDelimiterLength;
		}

		return $contents;
	}
	
	

	
	
}








// From: http://stackoverflow.com/questions/18880772/calculate-math-expression-from-a-string-using-eval
class Field_calculate {
    const PATTERN = '/(?:\-?\d+(?:\.?\d+)?[\+\-\*\/])+\-?\d+(?:\.?\d+)?/';

    const PARENTHESIS_DEPTH = 10;

    public function calculate($input){
        if(strpos($input, '+') != null || strpos($input, '-') != null || strpos($input, '/') != null || strpos($input, '*') != null){
            //  Remove white spaces and invalid math chars
            $input = str_replace(',', '.', $input);
            $input = preg_replace('[^0-9\.\+\-\*\/\(\)]', '', $input);

            //  Calculate each of the parenthesis from the top
            $i = 0;
            while(strpos($input, '(') || strpos($input, ')')){
                $input = preg_replace_callback('/\(([^\(\)]+)\)/', 'self::callback', $input);

                $i++;
                if($i > self::PARENTHESIS_DEPTH){
                    break;
                }
            }

            //  Calculate the result
            if(preg_match(self::PATTERN, $input, $match)){
                return $this->compute($match[0]);
            }

            return 0;
        }

        return $input;
    }

    private function compute($input){
        $compute = create_function('', 'return '.$input.';');

        return 0 + $compute();
    }

    private function callback($input){
        if(is_numeric($input[1])){
            return $input[1];
        }
        elseif(preg_match(self::PATTERN, $input[1], $match)){
            return $this->compute($match[0]);
        }

        return 0;
    }
}
