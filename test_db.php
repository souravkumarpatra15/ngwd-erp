<?php
require 'vendor/autoload.php';
$db = \Config\Database::connect();
$rows = $db->query("SELECT * FROM whatsapp_conversations ORDER BY id DESC LIMIT 5")->getResultArray();
print_r($rows);
$rows2 = $db->query("SELECT * FROM whatsapp_messages ORDER BY id DESC LIMIT 5")->getResultArray();
print_r($rows2);