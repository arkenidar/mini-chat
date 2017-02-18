<?php

$location=@$_REQUEST['location'];
if($location=='')$location='/';

function pdo(){
	$dbfile="sqlite:../db.sqlite";
	return new PDO($dbfile, "", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION] );
}

if($location=='/'){
	
	header("Location: chat_client.html");
	
}elseif($location=='/chat/setup'){

    $pdo = pdo();

    $sql = "CREATE TABLE IF NOT EXISTS chat_messages (
      id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
      message_text TEXT NOT NULL,
      sender TEXT NOT NULL,
      creation_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL )";
    $pdo->query($sql); // init db tables
    
    echo "chat_messages table is setup";
    
}elseif($location=='/chat/list'){

	$pdo = pdo();
    
    $query = "SELECT * FROM chat_messages ORDER BY creation_timestamp DESC LIMIT 15";
    $query = "SELECT * FROM ($query) AS res ORDER BY creation_timestamp ASC";
    $messages = $pdo->query($query); // list messages

    $output = '';

    foreach($messages as $message){
        $sender = htmlspecialchars($message["sender"]);
        $text = htmlspecialchars($message["message_text"]);
        $image = substr($text, 0, 4)==="http"; // simple image handling stub
        $output .= $sender.": ".($image?"<img src=".$text.">":$text)."<br>";
    }
    
    echo $output;
    
}elseif($location=='/chat/send'){

    $pdo = pdo();
    
    $text = $pdo->quote($_REQUEST["message_text"]);
    $sender = $pdo->quote($_REQUEST["sender"]);    
    $sql = "INSERT INTO chat_messages (message_text, sender) VALUES ($text, $sender)";

    $pdo->query($sql); // insert new message
    
 }
