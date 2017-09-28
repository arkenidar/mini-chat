<?php

// - PDO

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
	$postgres_or_mysql = 'CREATE TABLE IF NOT EXISTS chat_messages (
      id SERIAL PRIMARY KEY NOT NULL,
      message_text TEXT NOT NULL,
      sender TEXT NOT NULL,
      creation_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL )';
	$setup_db_sql = [
	'sqlite' => 'CREATE TABLE IF NOT EXISTS chat_messages (
      id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
      message_text TEXT NOT NULL,
      sender TEXT NOT NULL,
      creation_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL )',
	'postgres' => $postgres_or_mysql,
    'mysql' => $postgres_or_mysql,
    ];
	$sql = $setup_db_sql[pdo_db_type];
	return $sql;
}

function pdo_execute($sql, $params = []) {
	$pdo = pdo();
	$stat = $pdo->prepare($sql);
	assert($stat);
	$res = $stat->execute($params);
	assert($res);
	return $stat;
}

// - location (routing requests)

switch( (string) @$_REQUEST['location'] ) {

// home page
// http://localhost:88
case '':
	header("Location: chat_client.html");
	break;

// list messages
// http://localhost:88/?location=/chat/list
case '/chat/list':
	// OUT: $messages
	$messages = pdo_execute('SELECT * FROM (SELECT * FROM chat_messages ORDER BY creation_timestamp DESC LIMIT 15) AS res ORDER BY creation_timestamp ASC');
	// - produce HTML output
	// IN: $messages OUT: $output
    $output = '';
    foreach($messages as $message) {
        $sender = htmlspecialchars($message['sender']);
        $text = htmlspecialchars($message['message_text']);
        $output .= "$sender: $text<br>";
    }
    echo $output;
	break;

// insert new message
// http://localhost:88/?location=/chat/send&message_text=text1&sender=sender1
case '/chat/send':
	// IN: $_REQUEST OUT: $message
	$message = ['text' => @$_REQUEST['message_text'], 'sender' => @$_REQUEST['sender']];
	// - SQL chat send
	// IN: $message
	pdo_execute('INSERT INTO chat_messages (message_text, sender) VALUES (:text, :sender)', $message);
	break;

// setup database tables
// http://localhost:88/?location=/util/db_setup
case '/util/db_setup':
    $pdo = pdo();
	$sql = pdo_setup_db_sql();
    $pdo->query($sql);
    echo 'DB tables are now setup.';
	break;

// http://localhost:88/?location=/util/phpinfo
case '/util/phpinfo':
	phpinfo();
	break;
}
