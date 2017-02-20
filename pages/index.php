<?php

$location=@$_REQUEST['location'];

function pdo_sqlite(){
	$db_url="sqlite:../db.sqlite";
	return new PDO($db_url, "", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION] );
}

function pdo_postgres(){
	$db_url="pgsql:host=localhost;dbname=lines";
	return new PDO($db_url, "postgres", "postgres", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION] );
}

function db_type(){
	$db_type="postgres";
	return $db_type;
}
function pdo(){
	if(db_type()=="sqlite")
		return pdo_sqlite();
	else if(db_type()=="postgres")
		return pdo_postgres();
}

$location = explode("/", $location);
if($location==['']){
	
	header("Location: chat_client.html");

}elseif($location==['util','phpinfo']){

	phpinfo();
	
}elseif($location[0]=='chat'){
	$location=$location[1];
	if($location=='setup'){

    $pdo = pdo();

	if(db_type()=="sqlite")
    $sql = "CREATE TABLE IF NOT EXISTS chat_messages (
      id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
      message_text TEXT NOT NULL,
      sender TEXT NOT NULL,
      creation_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL )";

	else if(db_type()=="postgres")
    $sql = "CREATE TABLE IF NOT EXISTS chat_messages (
      id SERIAL PRIMARY KEY NOT NULL,
      message_text TEXT NOT NULL,
      sender TEXT NOT NULL,
      creation_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL )";
    
    $pdo->query($sql); // init db tables
    
    echo "chat_messages table is setup";
    
	}elseif($location=='list'){

	$pdo = pdo();
    
    $query = "SELECT * FROM (SELECT * FROM chat_messages ORDER BY creation_timestamp DESC LIMIT 15) AS res ORDER BY creation_timestamp ASC";
    $messages = $pdo->query($query); // list messages

    $output = '';

    foreach($messages as $message){
        $sender = htmlspecialchars($message["sender"]);
        $text = htmlspecialchars($message["message_text"]);
        $output .= $sender.": ".$text."<br>";
    }
    
    echo $output;
    
	}elseif($location=='send'){

    $pdo = pdo();
    
    $text = $pdo->quote($_REQUEST["message_text"]);
    $sender = $pdo->quote($_REQUEST["sender"]);    
    $sql = "INSERT INTO chat_messages (message_text, sender) VALUES ($text, $sender)";
    $pdo->query($sql); // insert new message
    
	}
}
