<?php
    function conectar(){
        $host = 'db';
        $db   = 'cca92205_marketplus_db';
        $user = 'cca92205_marketplus';
        $pass = 'V!mkkhcaYr49'; 
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }


?>