# payburst
Mini Burstcoin Payment System
Author: evolver

What need to run
----------------
1. webserver with PHP and JSON support
2. a mySQL or mariaDB Database

DB structure
------------
1. a DataBase named 'payburst'
2. a table named 'payments'
3. a user named 'payburst' with password 'payburst' and all privileges on the 'payburst' database
4. tablecolumns of 'payments' table:

burstaddress	VARCHAR		length=26
burstamount		VARCHAR		length=50 (or more if you want to allow more)
payticket		VARCHAR		length=8
timestamp		VARCHAR		length=50
status			VARCHAR		length=20


installation
------------
1. change the URLs to yours in the 'payburstfunctions.php' file on line 6
2. change all the DB-Server connection infos to yours in the 'payburstfunctions.php' file on lines 104, 141, 170 and 200
3. change the URLs to yours in the 'payburst.php' on line 39 and 96
4. make sure you have a ok-page and a fail-page where to redirect in the  'payburst.php' on line 75, 79, 83, 87

How to use
----------
1. to test the payment system you can browse in your browser to the '/payburst.php' file
2. make sure you use a new and unknown BURST-Address which will be watched for a payment
3. enter a amount of Burst and click 'Receive Burst'
4. the system now looking for an incoming payment for 15 minutes and decides between 'confirmed', 'overpayed', 'underpayed', and 'timeout' and save it to the database
