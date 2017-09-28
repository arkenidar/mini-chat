<?php

// supported DB types: 'sqlite', 'postgres', 'mysql'
define('pdo_db_type', 'mysql');

function pdo(){

	switch(pdo_db_type){

		case 'sqlite':
			$db_url = 'sqlite:../db.sqlite';
			$pdo = new PDO($db_url, "", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION] );
			break;

		case 'postgres':
			$db_url = 'pgsql:host=localhost; dbname=messaging';
			$pdo = new PDO($db_url, 'postgres', 'password', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION] );
			break;

		case 'mysql':
			$db_url = 'mysql:host=localhost; dbname=messaging';
			$pdo = new PDO($db_url, 'root', 'password', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION] );
			break;
	}

	return $pdo;
}

function pdo_setup_db_sql(){
	$setup_db_sql = [
	'sqlite' => 'CREATE TABLE IF NOT EXISTS chat_messages (
      id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
      message_text TEXT NOT NULL,
      sender TEXT NOT NULL,
      creation_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL )',

	'postgres' => 'CREATE TABLE IF NOT EXISTS chat_messages (
      id SERIAL PRIMARY KEY NOT NULL,
      message_text TEXT NOT NULL,
      sender TEXT NOT NULL,
      creation_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL )',

    'mysql' => 'CREATE TABLE IF NOT EXISTS chat_messages (
      id SERIAL PRIMARY KEY NOT NULL,
      message_text TEXT NOT NULL,
      sender TEXT NOT NULL,
      creation_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL )',

    ];
	$sql = $setup_db_sql[pdo_db_type];
	return $sql;
}

$location = @$_REQUEST['location'];

// http://localhost:88
if($location == ''){

	header("Location: chat_client.html");

// http://localhost:88/?location=chat/list
} elseif($location=='chat/list') {

	// list messages
	$pdo = pdo();
    $query = 'SELECT * FROM (SELECT * FROM chat_messages ORDER BY creation_timestamp DESC LIMIT 15) AS res ORDER BY creation_timestamp ASC';
    $messages = $pdo->query($query);

	// produce HTML output
    $output = '';
    foreach($messages as $message) {
        $sender = htmlspecialchars($message['sender']);
        $text = htmlspecialchars($message['message_text']);
        $output .= "$sender: $text<br>";
    }
    echo $output;

// http://localhost:88/?location=chat/send&message_text=text1&sender=sender1
} elseif($location == 'chat/send') {

	// insert new message
    $pdo = pdo();
    $sql = 'INSERT INTO chat_messages (message_text, sender) VALUES (:text, :sender)';
    $stat = $pdo->prepare($sql);
    $stat->execute(['text' => @$_REQUEST['message_text'], 'sender' => @$_REQUEST['sender']]);

// http://localhost:88/?location=util/db_setup
} elseif($location == 'util/db_setup') {

    $pdo = pdo();
	$sql = pdo_setup_db_sql(); // setup database tables
    $pdo->query($sql);
    echo 'DB tables are now setup.';

// http://localhost:88/?location=util/phpinfo
} elseif($location == 'util/phpinfo'){

	phpinfo();

}
