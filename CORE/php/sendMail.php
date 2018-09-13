<?php

require_once 'functions.php';

db_connect();

sendMail($argv[1], $argv[2], setting("notification_addresses"));

?>
