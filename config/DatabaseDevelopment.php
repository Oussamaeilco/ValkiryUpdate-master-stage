<?php

//$ini = parse_ini_file(__DIR__ . '/../../mysql.ini');
$ini=['user'=>'root','pass'=>''];
$db = [
    'type' => 'mysql',
    'host' => 'localhost',
    'name' => 'valkiry_temp',
    'user' => 'root',
    'pass' => ''
];

return $db;
