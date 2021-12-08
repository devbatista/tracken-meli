<?php

$db = new PDO("pgsql:dbname=tracken;host:127.0.0.1", "postgres", "showdebola");

if($db) {
    echo 'conectado';
} else {
    echo 'não conectado';
}