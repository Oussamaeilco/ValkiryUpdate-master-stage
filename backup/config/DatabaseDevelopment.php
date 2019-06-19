<?php

$ini = parse_ini_file(__DIR__ . '/../../mysql.ini');

$db = [
    'type' => 'mysql',
    'host' => 'localhost',
    'name' => 'valkiry_temp',
    'user' => $ini['user'],
    'pass' => $ini['pass']
];

return $db;
