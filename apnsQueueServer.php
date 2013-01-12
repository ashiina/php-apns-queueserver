<?php
/**
 * apnsQueueServer.php
 * by ashiina
 * 
 * APNS server with a queue system.
 * The current system uses Redis for the queue, therefore Redis a Redis server is required.
 * 
 * ---------
 * Usage
 * ---------
 * 1) Install ApnsPHP and Predis, and configure their path in the "require" line.
 * Libraries can be obtained from:
 * https://github.com/duccio/ApnsPHP
 * https://github.com/nrk/predis
 *
 * 2) Set up and configure the required certificates.
 * Refer to the page below for a guide:
 * https://code.google.com/p/apns-php/wiki/CertificateCreation
 * 
 * 3) Start the server:
 * nohup php pushQueueServer.php &
 * 
 * 4) Add to the push queue with the following command:
 * RPUSH {QUEUE_KEY} {deviceToken}:{badgeNumber}:{text} 
 * 
 * The server will automatically send the push notification.
 * 
 * ---------
 * Additional Information
 * ---------
 * For more information, please check the github page:
 * https://github.com/ashiina/APNS-QueueServer
 * 
 * ---------
 * License
 * ---------
 * This software is released under the MIT license.
 * 
 */

// timezone
date_default_timezone_set('Asia/Tokyo');

// Report all PHP errors
error_reporting(E_ALL ^ E_NOTICE);

// load Redis
require '/path/to/nrk-predis-5859578/autoload.php';
Predis\Autoloader::register();
// connect to Redis server
$redis = new Predis\Client('tcp://127.0.0.1:6379');

// Redis queue key
$QUEUE_KEY = 'list.apns.messagequeue';

// enable log
$_ENABLE_LOG=true;

// path to log
$LOGPATH = '/path/to/logs/';

// Using Autoload all classes are loaded on-demand
require_once '/path/to/ApnsPHP/Autoload.php';

// Instanciate a new ApnsPHP_Push object
$server = new ApnsPHP_Push_Server(
	ApnsPHP_Abstract::ENVIRONMENT_SANDBOX,
	'/path/to/apns.pem'
);

// Set the Root Certificate Autority to verify the Apple remote peer
$server->setRootCertificationAuthority('/path/to/entrust_root_certification_authority.pem');

// Set the number of concurrent processes
$server->setProcesses(4);

// Starts the server forking the new processes
$server->start();
_pushLog(array(date('Y-m-d H:i:s'), 'STARTING SERVER'));

/*
 * Main server run loop
 */
while ($server->run()) {
	$date = date('Y-m-d H:i:s');

	// Check the error queue
	$aErrorQueue = $server->getErrors();
	if (!empty($aErrorQueue)) {
		var_dump($aErrorQueue);
	}

	// get latest queue
	list ($deviceToken, $badgeNum, $text) = popQueue();

	// push message if it has correct values
	if ($deviceToken && $badgeNum && $text) {
		// Instantiate a new Message with a single recipient
		$message = new ApnsPHP_Message($deviceToken);
		$message->setBadge((int)$badgeNum);
		$message->setSound();
		$message->setText(urldecode($text));

		// Add the message to the message queue
		$server->add($message);
		_pushLog(array(date('Y-m-d H:i:s'), $deviceToken, $badgeNum));
	}

	usleep(500000);
}

/*
 * Pop from queue.
 * Currently using redis
 */
function popQueue () {
	$queueRow = $redis->lpop($QUEUE_KEY);
	return explode(':', $queueRow);
}

/*
 * add to log.
 */
function _pushLog ($args) {
	if (!$_ENABLE_LOG) {
		return;
	}

	$fileFullPath = $LOGPATH .date("Y-m-d")."_push.log";

	$logMessage = implode("\t", $args);

	if ($FH = fopen($fileFullPath, 'a')) {
		if (fputs($FH, $logMessage."\n")) {
			echo "failed to put logfile\n";
		}
		fclose($FH);
	} else {
		echo "failed to open logfile\n";
	}
}




