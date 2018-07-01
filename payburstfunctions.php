
<?php
	
	function BurstRequest($requestTyp, $method, $TypArray, $var){
		
		$url = 'http://127.0.0.1:8125/burst'; //the Burst-Wallet-Address
		$data = "";
		
		$response = "error";
		
		$data = array(
			'requestType' => $requestTyp
		);
		
		$data = array_merge($data, $TypArray);

		if ($method == "POST"){
		//Make POST request
			$data = http_build_query($data);
			$context = stream_context_create(array(
				'http' => array(
					'method' => "$method",
					'header' => 'Content-Type: application/x-www-form-urlencoded',
					'content' => $data)
				)
			);
			$response = file_get_contents($url, false, $context);
		} else {
		// Make GET request
			$data = http_build_query($data, '', '&');
			$response = file_get_contents($url."?".$data, false);
		}
		
		if ($response == "error"){
			return "error";
		} else {
			
			if($var == ""){
				return $response;
			} else {
				return JSONDecode($response, $var);
			}
			
		}
		
	}
	
	
	function JSONDecode($input, $var){
		$varar = json_decode($input, true);
		$value = recursively($varar, $var);
		return $value;
	}
	
	
	function recursively($input, $var){
		$value = "";
		$arykeys = array();
		
		if (gettype($input) === gettype(array()) ){
			$arykeys = array_keys($input);
		} else {
			$arykeys[] = $input;			
		}
		
		
		for ($i = 0 ; $i < count($arykeys) ; $i++){
			
			$value = $arykeys[$i];
			$valuetyp = $input[$value];
			
			
			if (gettype($valuetyp) === gettype(array()) ){
				$value = recursively($valuetyp, $var);
				
				if($value == "not found"){
					//$value = "not found";
				} else {
					break;
				}
				
			} else {
				if($value === $var){
					$value = $valuetyp;
					break;
				} else {
					$value = "not found";
				}
			}
		}
		
		if ($value == ""){
			return "not found";
		} else {
			return $value;
		}
		
	}
	
	
	function GetSQLValue($SQLCommand, $Col){
		
		//DB-Connection
		$server = "127.0.0.1";
		$user = "payburst";
		$pw = "payburst";
		$db = "payburst";
		
			
		if ($SQLConnection = mysqli_connect ($server, $user, $pw, $db)){
			//Connection OK
		} else {
			//Connection error
			return "error";
		}
	
		
		if($SQLReader = mysqli_query($SQLConnection, $SQLCommand)){
			//Query OK
			
			if($row = mysqli_fetch_object($SQLReader)){
				$db_item = $row->$Col;
				mysqli_close($SQLConnection);
				return $db_item;
			} else {
				mysqli_close($SQLConnection);
				return "error";
			}
			
		} else {
			//Query error
			mysqli_close($SQLConnection);
			return "error";
		}
	}
	
	
	function SetSQLValue($table, $vars, $vals){
		
		//DB-Connection
		$server = "127.0.0.1";
		$user = "payburst";
		$pw = "payburst";
		$db = "payburst";
		
		if ($SQLConnection = mysqli_connect ($server, $user, $pw, $db)){
			//Connection OK
		} else {
			//Connection error
			return "error";
		}
	
		$SQLCommand = "INSERT INTO $table ($vars) VALUES ($vals);";
		
		if (mysqli_query ($SQLConnection, $SQLCommand)){
			//Query OK
			mysqli_close($SQLConnection);
			return "OK";
		} else {
			//Query error
			mysqli_close($SQLConnection);
			return "error";
		}		
	}
	
	
	function UpdSQLValue($table, $set, $where){
		
		//DB-Connection
		$server = "127.0.0.1";
		$user = "payburst";
		$pw = "payburst";
		$db = "payburst";
		
		if ($SQLConnection = mysqli_connect ($server, $user, $pw, $db)){
			//Connection OK
		} else {
			//Connection error
			return "error";
		}
	
		$SQLCommand = "UPDATE $table SET $set WHERE $where;";
				
		if (mysqli_query ($SQLConnection, $SQLCommand)){
			//Query OK
			mysqli_close($SQLConnection);
			return "OK";
		} else {
			//Query error
			mysqli_close($SQLConnection);
			return "error";
		}
		
	}
	
	
	function DelSQLItem($table, $where){
		
		//DB-Connection
		$server = "127.0.0.1";
		$user = "payburst";
		$pw = "payburst";
		$db = "payburst";
		
		$SQLConnection = mysqli_connect ($server, $user, $pw, $db);
	
		$SQLCommand = "DELETE FROM $table WHERE $where;";
				
		if (mysqli_query ($SQLConnection, $SQLCommand)){
			
		} else {
			mysqli_close($SQLConnection);
			return "db_error";
		}
		
		mysqli_close($SQLConnection);
		return "1";
	}
	
	
	function AntiBurstDot($input){
		$input = str_replace(",", ".", $input);
		$kommaidx = strpos($input, ".");
		
		if ($kommaidx == false){
			$input = $input . "00000000";
			return $input;
		}
		
		$nkstr = substr($input, $kommaidx);
		$len = strlen($nkstr);
		
		if($len > 9){
			
			$len = $len - 9;
			
			$input = substr($input, 0, -$len);
			
			$input = str_replace(".", "", $input);
			return $input;
		}
		
		$cnt = 9 - $len;
		
		for ($i = 0 ; $i < $cnt ; $i++){
			$input .= "0";
		}
		
		$input = str_replace(".", "", $input);
		
		return $input;
		
	}
	
	function BurstDot($input){
		
		//return gettype($input);
		
		if ($input == "not found"){
			return $input;
		}
		
		$len = strlen($input);
		
		if ($len < 8){
			for ($i = 8 - $len ; $i != 0 ; $i--){
				$input = "0" . $input;
			}
			$input = "0." . $input;
		} else {
			
			if ($len == 8){
				$input = "0." . $input;
			} else {
				$dot = $len - 8;
				
				$input = substr_replace($input, ".", $dot, 1);
				
			}
			
		}
		
		return $input;
	}
	
?>