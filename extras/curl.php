#!/usr/bin/php5
<?php

/*Prueba de Autenticacion basica*/

$login    = 'vitronic';
$password = 'vitronic';
$url      = 'http://guachi/admin';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookies.txt');
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_USERPWD, "$login:$password");
$result = curl_exec($ch);
curl_close($ch);
echo($result);
