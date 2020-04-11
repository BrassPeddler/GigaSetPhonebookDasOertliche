<?php

$addAreaCode = false;

#-----------------------------------------#
# Settings / Einstellungen                #
#-----------------------------------------#

# Add area code true/false (remove #)
# Vorwahl ergänzen ja/nein (# entfernen)
$addAreaCode = true;

# Insert your area code here
# Eigene Vorwahl hier eingeben
$areaCode = "07354";
#-----------------------------------------#


function addTagIfNotZero($tag, $str) {
	return $str != '0' ? '<'.$tag.'>'.$str.'</'.$tag.'>' : '';
}
function handlerData($content){
    preg_match('/var handlerData = \[\[(.*)\]\]/', $content, $handler_array);
    if(count($handler_array) != 0){
        $handler_array = explode("],[",$handler_array[1]);
        $cnt = count($handler_array);
        
		for($i=0;$i < $cnt; $i++){
            preg_match('/\'.*\',\'.*\',\'.*\',\'.*\',\'.*\',\'(.*)\',\'.*\', \'.*\', \'.*\', \'(.*)\', \'(.*)\', \'(.*)\', \'.*\', \'.*\', \'(\S*) (.*)\', \'.*\'/', $handler_array[$i], $handler_array[$i]);
        }
        
		$handler_array = itemData($handler_array, $content);
    }
    
    return $handler_array;
}

function itemData($array, $content) {
    preg_match('/var itemData = \[\[(.*)\]\]/', $content, $item_array);
    $item_array = explode("],[", $item_array[1]);
    $cnt = count($array);
    
    for($i=0; $i < $cnt; $i++){
        preg_match('/\'.*\[\'(.*)\'\],\'.*/', $item_array[$i], $mark);
        array_push($array[$i], preg_replace("/[^0-9]/", "", $mark[1]));
    }
    
    return $array;
}

function collectDataReverse($ph){
 $url ="http://www.dasoertliche.de/Controller?&form_name=search_inv&ph=$ph";
 return handlerData(file_get_contents($url));
}

function collectData($ln,$ct){
    $url ="http://www.dasoertliche.de/Controller?&form_name=search_nat&kw=$ln&ci=$ct";
	return handlerData(file_get_contents($url));
}

function createXMLList($data){
	$xml = '<?xml version="1.0" encoding="UTF-8"?>';
	$cnt = count($data);
	if($cnt == 0){
		$xml .= '
		<list response="get_list" type= "pb" notfound="hm" total="0" />';
	}
	else {
		$xml .= '
		<list response="get_list" type="pb" total="'.$cnt.'" first="1" last="'.$cnt.'">';
		for($i=0; $i < $cnt; $i++){
			$xml .= '
			<entry>
				'.addTagIfNotZero('ln', $data[$i][5]).'
				'.addTagIfNotZero('fn', $data[$i][6]).'
				'.addTagIfNotZero('zc', $data[$i][2]).'
				'.addTagIfNotZero('ct', $data[$i][1]).'
				'.addTagIfNotZero('st', $data[$i][3]).'
				'.addTagIfNotZero('nr', $data[$i][4]).'
				'.addTagIfNotZero('hm', $data[$i][7]).'
			</entry>';
		}
		$xml .= '</list>';
	}
	
	return $xml;
}

if(isset($_GET["hm"]) && $_GET["hm"] != "*") {
	$hm = $_GET["hm"]; 
	if ($addAreaCode && strncmp($hm,"0",1) != 0) {
		$hm = $areaCode.$hm;
	}
	$queryResult = collectDataReverse($hm);
}

if(isset($_GET["ln"]) && $_GET["ln"] != "*") {
	$queryResult = collectData($_GET["ln"],$_GET["ct"]);
}

header('Content-Type: application/xml');
print(createXMLList($queryResult));

?>