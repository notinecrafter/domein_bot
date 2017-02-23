<?php
//connect to db
try{
	$password = file_get_contents("password.txt");
	$conn = new PDO("mysql:host=localhost;dbname=Ictrek", 'ictrek', $password);
	echo "<p>connected to internal database</p>";
}catch(PDOException $e){
	echo "<br/>connection failed: ".$e->getMessage();
}

ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);
include("Telegram.php");
$bot_id = file_get_contents('token.txt');
$telegram = new Telegram($bot_id);
$text = strtolower($telegram->Text());
$chat_id = $telegram->ChatID();
$user_id = $telegram->UserID();
if (!$text){die();}

//start of code
//over
if (substr($text, 0, 5) === "/over" || substr($text, 0, 6) === "/about" || substr($text, 0, 4) === '/dev'){
	$telegram->sendMessage(array('chat_id' => $chat_id, 'text' => "Bot gemaakt door @notinecrafter (basis voor bot geript van maartenwut). Opmaak door @EenGebruikersnaam.\n\nMet /nieuw <domein> kan je je domein toevoegen, met /verwijder <domein> kan je hem weer verwijderen. Met /domeinen kan je alle domeinen zien. \n\nPraat met @notinecrafter als je directe toegang wilt tot de database."));
}

//registrations
else if(substr($text, 0, 17) === "/nieuw@domein_bot" || substr($text, 0, 6) === "/nieuw"){
	$domain = explode(" ", $text)[1];
	$user = $telegram->UserName();
	//validation
	if(count(explode("." ,$domain)) < 2){
		$telegram->sendMessage(array('chat_id' => $chat_id, 'text' => $domain." is geen domein gekkie"));
	}else{
		$stmt = $conn->prepare("INSERT INTO domain(domain, user) VALUES('$domain', '$user');");
		$stmt->execute();
		$telegram->sendMessage(array('chat_id' => $chat_id, 'text' => "$domain geregistreerd voor $user"));
	}
}

//retrieval
else if(substr($text, 0, 20) === "/domeinen@domein_bot" || substr($text, 0, 9) === "/domeinen"){
	$stmt = $conn->prepare("SELECT * FROM domain ORDER BY user ASC, domain ASC;");
	$stmt->execute();
	$domains = $stmt->fetchAll();

	$out = "";
	$user = null;
	foreach ($domains as $d) {
		if ($d["user"] !== $user) {
			$user = $d["user"];
			$out .= "*$user*\n";
		}
		$out .= $d["domain"] . "\n";
	}

	$telegram->sendMessage(array('chat_id' => $chat_id, 'text' => $out, 'parse_mode' => 'Markdown'));
}

//removal
else if (substr($text, 0, 21) === "/verwijder@domein_bot" || substr($text, 0, 10) === "/verwijder"){
	$domain = explode(" ", $text)[1];
	$user = $telegram->UserName();
	$stmt = $conn->prepare("DELETE FROM domain WHERE domain='$domain' AND user='$user';");
	$stmt->execute();
	$telegram->sendMessage(array('chat_id' => $chat_id, 'text' => "$domain van $user verwijderd"));
}
?>
