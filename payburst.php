<?php
	include 'payburstfunctions.php'; 
	
	$recipientAddress = "";
	if (isset($_POST["burst_address"])){
		$recipientAddress = filter_input(INPUT_POST, 'burst_address', FILTER_SANITIZE_SPECIAL_CHARS);
	}
	
	$recipientAmount = "";
	if (isset($_POST["burst_amount"])){
		$recipientAmount = filter_input(INPUT_POST, 'burst_amount', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	}
	//echo "$recipientAmount";
	$refresh = "";
	
	if ($recipientAddress != "" and $recipientAmount != ""){
		
		$recipientAmount = str_replace(",", ".", $recipientAmount); //convert kommata to dot
		$recipientAmount = str_replace("-", "", $recipientAmount); //delete negative
		
		$data = array(
			'htrq' => "setPaymentRequest",
			'recipientAddress' => $recipientAddress,
			'recipientAmount' => $recipientAmount
		);


		//send data to process via post
		$data = http_build_query($data);
		$context = stream_context_create(
		array(
			'http' => array(
					'method' => 'POST',
					'header' => 'Content-Type: application/x-www-form-urlencoded',
					'content' => $data)
				)
			);

		$response = file_get_contents("http://127.0.0.1/payburst/payburstprocess.php", false, $context);
		
		$payticket = JSONDecode($response, "payticket");
		
		if ($payticket == "error"){
			$refresh = "error";
		} else {
			$refresh = "reload();";
		}
		
	}
	
?>

<html>
	<head>
	<?php 
		if ($refresh != "error" and $refresh != "" ){
	?>
		<script type="text/javascript">
		var timer;
		var resp;
		function reload() {
			var refresh;
			if (window.XMLHttpRequest) {
				refresh = new XMLHttpRequest();
				refresh.onreadystatechange = function () {
					if (refresh.readyState == XMLHttpRequest.DONE && refresh.status == 200) {
						resp = refresh.responseText;
						var obj = JSON.parse(resp);
						if (obj.status == "confirmed") {
							// redirect to OK-Page
							document.getElementById("paysatatus").innerHTML = "payment ok";
							//window.location.replace('https://your-ok-page.com');
						} else if (obj.status == "overpayed"){
							// redirect to OK-Page
							document.getElementById("paysatatus").innerHTML = "overpayed";
							//window.location.replace('https://your-ok-page.com');
						} else if (obj.status == "underpayed"){
							// redirect to fail-Page
							document.getElementById("paysatatus").innerHTML = "underpayed";
							//window.location.replace('https://your-fail-page.com');
						} else if (obj.status == "timeout"){
							// redirect to fail-page
							document.getElementById("paysatatus").innerHTML = "timeout";
							//window.location.replace('https://your-fail-page.com');
						} else {
							var date = new Date(null);
							date.setSeconds(obj.time);
							var timeString = date.toISOString().substr(14, 5);
							document.getElementById("paysatatus").innerHTML = obj.status + ", " + timeString;
						}
					}
				}
				refresh.open("POST", "http://127.0.0.1/payburst/payburstprocess.php", true);
				refresh.setRequestHeader("Content-type","application/x-www-form-urlencoded");
				refresh.send("htrq=getPayStatus&payticket=<?php echo $payticket; ?>");
			}
			timer = setTimeout ( "reload()", 3000 );
		}
		reload();
		</script>
	<?php
		}
	?>
	</head>
	
	<body>
	<?php
		if ($refresh != "error"){
			if($recipientAddress != "" and $recipientAmount != ""){
	?>
				<div id="info">Pay-Address: <?php echo "$recipientAddress"; ?><br>Pay-Amount: <?php echo "$recipientAmount"; ?></div>
	<?php
			}
	?>
		
		<div id="paysatatus">
		
			<form action="burstpay.php" method="post">
				<p style="position:relative;margin:6px 0px 0px 0px;padding:0px;font: normal normal normal 12px Arial;">
					New Recipient-Address:
					<input type="text" name="burst_address" size="6" maxlength="26" style="position:relative;left:0px;font: normal normal normal 12px Arial;width:200px;" >
				</p>
				<p style="position:relative;margin:6px 0px 0px 0px;padding:0px;font: normal normal normal 12px Arial;">
					Recipient-Amount:
					<input type="number" name="burst_amount" size="6" step="0.00000001" style="position:relative;left:0px;font: normal normal normal 12px Arial;width:100px;" >
				</p>
				<button type="submit" name="receive" value="1" style="position:relative;left:0px;font: normal normal bold 10px Arial;">Receive Burst</button>
				
			</form>
		
		</div>
	<?php
		} else {
	?>
			<div>something went wrong</div>
	<?php
		}
	?>
		
	</body>
	
</html>





