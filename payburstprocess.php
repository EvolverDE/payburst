<?php
	include 'payburstfunctions.php'; 

	$htrq = "";
	if (isset($_POST["htrq"])){
		$htrq = filter_input(INPUT_POST, 'htrq', FILTER_SANITIZE_SPECIAL_CHARS);
	}
	
	if ($htrq == ""){
		exit();		
	} else if ($htrq == "setPaymentRequest"){
		
		$recipientAddress = "";
		if (isset($_POST["recipientAddress"])){
			$recipientAddress = filter_input(INPUT_POST, 'recipientAddress', FILTER_SANITIZE_SPECIAL_CHARS);
		}
		
		
		$recipientAmount = "";
		if (isset($_POST["recipientAmount"])){
			$recipientAmount = filter_input(INPUT_POST, 'recipientAmount', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
		}
		
		$timestamp = time();
		$timestamp += 900;
		
		$opt =['salt' => hash('SHA1', $timestamp)];
		$payticket = password_hash($recipientAddress . $recipientAmount, PASSWORD_DEFAULT, $opt);
		
		$len = strlen($payticket);
		$len = $len / 2;
		
		$payticket = substr($payticket, $len, 8);
		
		$SQLsetOK = SetSQLValue("payments", "burstaddress, burstamount, payticket, timestamp, status", "'$recipientAddress', '$recipientAmount', '$payticket', '$timestamp', 'waiting'");
		
		if ($SQLsetOK == "OK"){
			echo "{\"payticket\":\"$payticket\"}";
		} else {
			echo "{\"payticket\":\"error\"}";
		}
		
	} else if ($htrq == "getPayStatus"){
		
		$payticket = "";
		if (isset($_POST["payticket"])){
			$payticket = filter_input(INPUT_POST, 'payticket', FILTER_SANITIZE_SPECIAL_CHARS);
		}
		
		$address = GetSQLValue("SELECT * FROM payments WHERE payticket='$payticket'", "burstaddress");
		$shouldNQTAmount = GetSQLValue("SELECT * FROM payments WHERE payticket='$payticket'", "burstamount");
		$shouldNQTAmount = AntiBurstDot($shouldNQTAmount);
		$timestamp = GetSQLValue("SELECT * FROM payments WHERE payticket='$payticket'", "timestamp");
		$differ = $timestamp - time();
		
		$data = array(
				'account' => $address
			);
		
		$unconfirmedNQTAmount = BurstRequest("getUnconfirmedTransactions", "POST", $data, "amountNQT");
		$isNQTAmount = BurstRequest("getAccount", "POST", $data, "balanceNQT");
		
		if($isNQTAmount != "not found"){
			if($isNQTAmount == $shouldNQTAmount){
				UpdSQLValue("payments", "status='confirmed'", "payticket='$payticket'");
				echo "{\"status\":\"confirmed\",\"time\":\"0\"}";
				exit();
			} else if($isNQTAmount > $shouldNQTAmount){
				UpdSQLValue("payments", "status='overpayed'", "payticket='$payticket'");
				echo "{\"status\":\"overpayed\",\"time\":\"0\"}";
				exit();
			} else {
				UpdSQLValue("payments", "status='underpayed'", "payticket='$payticket'");
				echo "{\"status\":\"underpayed\",\"time\":\"0\"}";
				exit();
			}
		}
		
		if ($timestamp < time()){
			UpdSQLValue("payments", "status='timeout'", "payticket='$payticket'");
			echo "{\"status\":\"timeout\",\"time\":\"0\"}";
			exit();
		}
		
		//let it refreshing...
		if ($unconfirmedNQTAmount == "not found"){
			echo "{\"status\":\"waiting\",\"time\":\"$differ\"}";
		} else {
			echo "{\"status\":\"unconfirmed\",\"time\":\"$differ\"}";
		}
	}
	
?>