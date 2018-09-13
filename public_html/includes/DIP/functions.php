<?php



function getDIPParamInfo($id_name, &$paramName, &$paramUnit, &$DBtable) {
	
    global $DB;
	
    $sth1 = $DB['DIP']->prepare("SELECT * FROM parameters WHERE id_name = '$id_name'");
    $sth1->execute();
    $res = $sth1->fetch();
	
    $paramName = $res["name"];
    $paramUnit = $res["unit"];
    $DBtable = $res["table_name"];
}


function getDataPointsFromDB($paramID, $t1, $t2) {
	
    global $DB;
    
    $datapoints = "";
    $paramName = "";
    $paramUnit = "";
    $DBtable = "";
	
    getDIPParamInfo($paramID, $paramName, $paramUnit, $DBtable);
	
    $sql = sprintf("SELECT timestamp, %s FROM %s WHERE timestamp > %d AND timestamp < %d", $paramID, $DBtable, $t1, $t2);
    $sth1 = $DB['DIP']->prepare($sql);
	//echo $sql;
    $sth1->execute();
    $values = $sth1->fetchAll();
    foreach($values as $val) {

        $time = $val[0]*1000;
        if($DBtable) $datapoints .= "{ x: ".$time.", y: ".$val[1]." }, ";
    }
    
    return $datapoints;
}

function getDataPointsFromDBAverage($paramID, $t1, $t2) {
	
    global $DB;
    
    $datapoints = "";
    $paramName = "";
    $paramUnit = "";
    $DBtable = "";
	
    getDIPParamInfo($paramID, $paramName, $paramUnit, $DBtable);
	
    $sql = sprintf("SELECT timestamp, %s FROM %s WHERE timestamp > %d AND timestamp < %d", $paramID, $DBtable, $t1, $t2);
    $sth1 = $DB['DIP']->prepare($sql);
	//echo $sql;
    $sth1->execute();
    $values = $sth1->fetchAll();
    $ret = 0.0;
    $points = 0;
    foreach($values as $val) {

		$ret += $val[1];
		$points++;
    }
    
    return $ret / $points;;
}

function getDIPParams() {
	
	global $dbhDIP;
	
	// Get parameters, grouped by table_name
	$sth1 = $dbhDIP->prepare("SELECT * FROM subscriptions GROUP BY table_name");
	$sth1->execute();
	$cats = $sth1->fetchAll();
	$params = array();
	$params['-'] = "None";
	foreach($cats as $cat) {

		$sth1 = $dbhDIP->prepare("SELECT * FROM subscriptions WHERE table_name = '".$cat['table_name']."'");
		$sth1->execute();
		$pars = $sth1->fetchAll();

		$params['NONE-'.$cat['table_name']] = $cat['category'];
		foreach($pars as $param) $params[$param["id_name"]] = $param["name"];
		//array_push($params, "");
	}
	array_pop($params); // remove last empty key
	
	return $params;
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

// From http://stackoverflow.com/questions/27078259/get-string-between-find-all-occurrences-php
function getContents($str, $startDelimiter, $endDelimiter) {
	
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

// Get single DIP value
function getValue($id_name, &$value, &$name, &$unit) {
	
    global $DB;
    $value = "n/a";
    $unit = "d";

    if(strpos($id_name, '[') !== false) { // mixed DIP ids --> formula
		
        $needed = array();
	$occurences = getContents($id_name, '[', ']'); // get all occurences between []
        foreach($occurences as $t) {
			
            getValue($t, $value, $name, $unit);
            $id_name = str_replace('['.$t.']', $value, $id_name);
        }
		
        $Cal = new Field_calculate();
        $value = $Cal->calculate($id_name);
    }
    else { // single DIP id
		
	$q = $DB['DIP']->prepare("SELECT * FROM parameters WHERE id_name = '".$id_name."' LIMIT 1");
	$q->execute();
	$res = $q->fetch();
	$name = $res['name'];
	$unit = $res['unit'];
	$table = $res['table_name'];
		
	$q = $DB['DIP']->prepare("SELECT ".$id_name." FROM ".$table." ORDER BY timestamp DESC LIMIT 1");
	$q->execute();
	$res = $q->fetch();
	$value = floatval($res[0]);
    }
}
