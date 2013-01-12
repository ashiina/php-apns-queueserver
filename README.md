APNS-QueueServer
================
APNS server with a queue system.
Please feel free to let me know (or just fork) if you find any bugs or improvements points.

Thanks, -ashiina (https://github.com/ashiina)

Requirements
-----------
1. PHP 5.0 or more
2. ApnsPHP (https://github.com/duccio/ApnsPHP)
3. Predis (https://github.com/nrk/predis)

Guide
-----------
### Install & Setup
1) Install ApnsPHP and Predis, and configure their path in the "require" line.
Libraries can be obtained from:
* https://github.com/duccio/ApnsPHP
* https://github.com/nrk/predis

2) Set up and configure the required certificates.
Refer to the page below for a guide:
* https://code.google.com/p/apns-php/wiki/CertificateCreation

### Usage
1) Start the server:
```
nohup php apnsQueueServer.php &
```

2) Add to the APNS queue with the following command:
```
RPUSH {QUEUE_KEY} {deviceToken}:{badgeNumber}:{text} 
```
Example
```
RPUSH list.apns.messagequeue AAAAAAAAAA:1:Hello+APNS+Push 
```

3) The server will automatically send the push notification.

License
----------
This library is released under the MIT license.



